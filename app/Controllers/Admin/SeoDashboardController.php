<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * SeoDashboardController - Painel SEO SuperAdmin DoarFazBem
 *
 * Monitor 404, Redirects, FAQs, Configurações SEO
 */
class SeoDashboardController extends BaseController
{
  private $db;

  public function __construct()
  {
    $this->db = db_connect();
  }

  /**
   * Dashboard SEO
   * GET /admin/seo
   */
  public function index()
  {
    $stats = $this->getStats();
    return view('admin/seo/dashboard', [
      'title' => 'SEO Dashboard — DoarFazBem',
      'stats' => $stats,
    ]);
  }

  /**
   * Monitor de 404s
   * GET /admin/seo/404
   */
  public function monitor404()
  {
    $status = $this->request->getGet('status') ?? 'pending';
    $page = max(1, (int)($this->request->getGet('page') ?? 1));
    $perPage = 50;

    $query = $this->db->table('seo_404_log')->orderBy('hit_count', 'DESC')->orderBy('last_seen_at', 'DESC');
    if ($status !== 'all') $query->where('status', $status);

    $total = (clone $query)->countAllResults(false);
    $items = $query->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();

    return view('admin/seo/404-monitor', [
      'title' => 'Monitor 404 — SEO',
      'items' => $items,
      'total' => $total,
      'current_page' => $page,
      'per_page' => $perPage,
      'total_pages' => ceil($total / $perPage),
      'current_status' => $status,
    ]);
  }

  /**
   * Ação sobre 404 (AJAX)
   */
  public function action404()
  {
    $id = (int)$this->request->getPost('id');
    $action = $this->request->getPost('action');
    $destUrl = $this->request->getPost('destination_url');
    $notes = $this->request->getPost('notes');

    if (!$id || !in_array($action, ['ignore', 'redirect', 'pending'])) {
      return $this->response->setJSON(['success' => false, 'message' => 'Parâmetros inválidos']);
    }

    $statusMap = ['ignore' => 'ignored', 'redirect' => 'redirected', 'pending' => 'pending'];
    $now = date('Y-m-d H:i:s');

    $this->db->table('seo_404_log')->where('id', $id)->update([
      'status' => $statusMap[$action],
      'redirect_to' => $action === 'redirect' ? $destUrl : null,
      'notes' => $notes,
      'updated_at' => $now,
    ]);

    // Criar redirect se necessário
    if ($action === 'redirect' && $destUrl) {
      $row = $this->db->table('seo_404_log')->where('id', $id)->get()->getRowArray();
      if ($row) {
        $urlHash = md5($row['url']);
        $existing = $this->db->table('seo_redirects')->where('source_url_hash', $urlHash)->get()->getRowArray();
        if (!$existing) {
          $this->db->table('seo_redirects')->insert([
            'source_url' => $row['url'], 'source_url_hash' => $urlHash,
            'destination_url' => $destUrl, 'redirect_type' => 301,
            'match_type' => 'exact', 'priority' => 100, 'is_active' => 1,
            'notes' => 'Criado a partir de 404', 'created_at' => $now, 'updated_at' => $now,
          ]);
        }
      }
    }

    return $this->response->setJSON(['success' => true, 'message' => 'Ação aplicada']);
  }

  /**
   * CRUD de Redirects
   * GET /admin/seo/redirects
   */
  public function redirects()
  {
    $page = max(1, (int)($this->request->getGet('page') ?? 1));
    $perPage = 50;
    $total = $this->db->table('seo_redirects')->countAll();
    $items = $this->db->table('seo_redirects')
      ->orderBy('is_active', 'DESC')->orderBy('hit_count', 'DESC')
      ->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();

    return view('admin/seo/redirects', [
      'title' => 'Redirects SEO', 'items' => $items,
      'total' => $total, 'current_page' => $page,
      'per_page' => $perPage, 'total_pages' => ceil($total / $perPage),
    ]);
  }

  /**
   * Salvar redirect
   */
  public function saveRedirect()
  {
    $id = (int)$this->request->getPost('id');
    $data = [
      'source_url' => trim($this->request->getPost('source_url')),
      'source_url_hash' => md5(trim($this->request->getPost('source_url'))),
      'destination_url' => trim($this->request->getPost('destination_url')),
      'redirect_type' => (int)($this->request->getPost('redirect_type') ?? 301),
      'match_type' => $this->request->getPost('match_type') ?? 'exact',
      'is_active' => (int)($this->request->getPost('is_active') ?? 1),
      'notes' => $this->request->getPost('notes'),
      'updated_at' => date('Y-m-d H:i:s'),
    ];

    if (empty($data['source_url']) || empty($data['destination_url'])) {
      return $this->response->setJSON(['success' => false, 'message' => 'URLs obrigatórias']);
    }

    if ($id > 0) {
      $this->db->table('seo_redirects')->where('id', $id)->update($data);
    } else {
      $data['created_at'] = date('Y-m-d H:i:s');
      $this->db->table('seo_redirects')->insert($data);
    }

    return $this->response->setJSON(['success' => true, 'message' => 'Redirect salvo']);
  }

  /**
   * FAQs SEO
   * GET /admin/seo/faqs
   */
  public function faqs()
  {
    $pageType = $this->request->getGet('page_type') ?? '';
    $query = $this->db->table('seo_faqs')->orderBy('page_type', 'ASC')->orderBy('sort_order', 'ASC');
    if ($pageType) $query->where('page_type', $pageType);
    $items = $query->get()->getResultArray();

    return view('admin/seo/faqs', [
      'title' => 'FAQs SEO', 'items' => $items, 'current_type' => $pageType,
    ]);
  }

  /**
   * Salvar FAQ
   */
  public function saveFaq()
  {
    $id = (int)$this->request->getPost('id');
    $data = [
      'page_type' => $this->request->getPost('page_type'),
      'page_identifier' => $this->request->getPost('page_identifier') ?? 'global',
      'question' => $this->request->getPost('question'),
      'answer' => $this->request->getPost('answer'),
      'sort_order' => (int)($this->request->getPost('sort_order') ?? 0),
      'is_active' => 1,
      'updated_at' => date('Y-m-d H:i:s'),
    ];

    if ($id > 0) {
      $this->db->table('seo_faqs')->where('id', $id)->update($data);
    } else {
      $data['created_at'] = date('Y-m-d H:i:s');
      $this->db->table('seo_faqs')->insert($data);
    }

    return redirect()->to('/admin/seo/faqs')->with('success', 'FAQ salva');
  }

  // =========================================================================
  // STATS
  // =========================================================================

  private function getStats(): array
  {
    $stats = [
      'total_404' => 0, 'pending_404' => 0, 'total_redirects' => 0,
      'active_redirects' => 0, 'total_faqs' => 0,
      'blog_posts' => 0, 'published_posts' => 0,
      'total_link_rules' => 0, 'active_link_rules' => 0,
    ];

    try {
      if (in_array('seo_404_log', $this->db->listTables())) {
        $stats['total_404'] = $this->db->table('seo_404_log')->countAll();
        $stats['pending_404'] = $this->db->table('seo_404_log')->where('status', 'pending')->countAllResults();
      }
      if (in_array('seo_redirects', $this->db->listTables())) {
        $stats['total_redirects'] = $this->db->table('seo_redirects')->countAll();
        $stats['active_redirects'] = $this->db->table('seo_redirects')->where('is_active', 1)->countAllResults();
      }
      if (in_array('seo_faqs', $this->db->listTables())) {
        $stats['total_faqs'] = $this->db->table('seo_faqs')->where('is_active', 1)->countAllResults();
      }
      if (in_array('blog_posts', $this->db->listTables())) {
        $stats['blog_posts'] = $this->db->table('blog_posts')->where('deleted_at IS NULL')->countAllResults();
        $stats['published_posts'] = $this->db->table('blog_posts')->where('status', 'published')->where('deleted_at IS NULL')->countAllResults();
      }
      if (in_array('seo_link_rules', $this->db->listTables())) {
        $stats['total_link_rules'] = $this->db->table('seo_link_rules')->countAll();
        $stats['active_link_rules'] = $this->db->table('seo_link_rules')->where('is_active', 1)->countAllResults();
      }
    } catch (\Throwable $e) {}

    return $stats;
  }
}
