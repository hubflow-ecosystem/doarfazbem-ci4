<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\AutoLinkerService;

/**
 * InternalLinksController - Gerenciamento de linkagem interna automática
 *
 * Admin pode criar regras keyword→URL que são automaticamente
 * aplicadas no conteúdo dos artigos do blog em render time.
 */
class InternalLinksController extends BaseController
{
  private $db;

  public function __construct()
  {
    $this->db = db_connect();
  }

  /**
   * Lista todas as regras de linkagem
   * GET /admin/seo/internal-links
   */
  public function index()
  {
    $perPage = 50;
    $page = max(1, (int)($this->request->getGet('page') ?? 1));

    $builder = $this->db->table('seo_link_rules');

    // Filtros
    $search = $this->request->getGet('q');
    if ($search) {
      $builder->groupStart()
        ->like('keyword', $search)
        ->orLike('target_url', $search)
      ->groupEnd();
    }

    $status = $this->request->getGet('status');
    if ($status !== null && $status !== '') {
      $builder->where('is_active', (int)$status);
    }

    $total = $builder->countAllResults(false);
    $rules = $builder->orderBy('priority', 'ASC')
      ->orderBy('keyword', 'ASC')
      ->limit($perPage, ($page - 1) * $perPage)
      ->get()->getResultArray();

    // Categorias do blog para o select de escopo
    $categories = $this->db->table('blog_categories')
      ->where('is_active', 1)
      ->orderBy('sort_order', 'ASC')
      ->get()->getResultArray();

    // Stats
    $stats = [
      'total' => $this->db->table('seo_link_rules')->countAll(),
      'active' => $this->db->table('seo_link_rules')->where('is_active', 1)->countAllResults(),
      'total_hits' => $this->db->table('seo_link_rules')->selectSum('hit_count')->get()->getRow()->hit_count ?? 0,
    ];

    return view('admin/seo/internal-links', [
      'title' => 'Linkagem Interna — Admin',
      'rules' => $rules,
      'categories' => $categories,
      'stats' => $stats,
      'filters' => ['q' => $search, 'status' => $status],
      'currentPage' => $page,
      'totalPages' => ceil($total / $perPage),
    ]);
  }

  /**
   * Salvar regra (criar ou editar)
   * POST /admin/seo/internal-links/save
   */
  public function save()
  {
    $id = $this->request->getPost('id');
    $keyword = trim($this->request->getPost('keyword') ?? '');
    $targetUrl = trim($this->request->getPost('target_url') ?? '');

    if (empty($keyword) || empty($targetUrl)) {
      return redirect()->back()->with('error', 'Palavra-chave e URL de destino são obrigatórios.');
    }

    $data = [
      'keyword' => $keyword,
      'keyword_hash' => md5(mb_strtolower($keyword, 'UTF-8')),
      'target_url' => $targetUrl,
      'target_label' => $this->request->getPost('target_label') ?: null,
      'open_new_tab' => (int)($this->request->getPost('open_new_tab') ?? 0),
      'case_sensitive' => (int)($this->request->getPost('case_sensitive') ?? 0),
      'match_whole_word' => (int)($this->request->getPost('match_whole_word') ?? 1),
      'priority' => (int)($this->request->getPost('priority') ?? 100),
      'category_scope' => $this->request->getPost('category_scope') ?: null,
      'is_active' => (int)($this->request->getPost('is_active') ?? 1),
      'updated_at' => date('Y-m-d H:i:s'),
    ];

    // Verificar duplicata
    $existing = $this->db->table('seo_link_rules')
      ->where('keyword_hash', $data['keyword_hash'])
      ->get()->getRowArray();

    if ($existing && (!$id || $existing['id'] != $id)) {
      return redirect()->back()->with('error', 'Já existe uma regra para esta palavra-chave.');
    }

    if ($id) {
      $this->db->table('seo_link_rules')->where('id', $id)->update($data);
    } else {
      $data['created_at'] = date('Y-m-d H:i:s');
      $this->db->table('seo_link_rules')->insert($data);
    }

    AutoLinkerService::clearCache();
    return redirect()->to('/admin/seo/internal-links')->with('success', 'Regra salva com sucesso.');
  }

  /**
   * Excluir regra
   * POST /admin/seo/internal-links/delete/:id
   */
  public function delete($id)
  {
    $this->db->table('seo_link_rules')->where('id', $id)->delete();
    AutoLinkerService::clearCache();
    return redirect()->to('/admin/seo/internal-links')->with('success', 'Regra excluída.');
  }

  /**
   * Ativar/desativar regra via AJAX
   * POST /admin/seo/internal-links/toggle/:id
   */
  public function toggleActive($id)
  {
    $rule = $this->db->table('seo_link_rules')->where('id', $id)->get()->getRowArray();
    if (!$rule) {
      return $this->response->setJSON(['success' => false, 'message' => 'Regra não encontrada']);
    }

    $newStatus = $rule['is_active'] ? 0 : 1;
    $this->db->table('seo_link_rules')->where('id', $id)->update([
      'is_active' => $newStatus,
      'updated_at' => date('Y-m-d H:i:s'),
    ]);

    AutoLinkerService::clearCache();
    return $this->response->setJSON(['success' => true, 'is_active' => $newStatus]);
  }

  /**
   * Preview de auto-linking via AJAX
   * POST /admin/seo/internal-links/preview
   */
  public function previewContent()
  {
    $text = $this->request->getPost('text') ?? '';
    if (empty($text)) {
      return $this->response->setJSON(['success' => false, 'html' => '']);
    }

    // Wrap em parágrafo se texto simples
    if (strpos($text, '<') === false) {
      $text = '<p>' . nl2br(esc($text)) . '</p>';
    }

    AutoLinkerService::clearCache(); // Forçar recarga das regras
    $autoLinker = new AutoLinkerService();
    $processed = $autoLinker->processContent($text);

    return $this->response->setJSON([
      'success' => true,
      'html' => $processed,
    ]);
  }

  /**
   * Listar mapeamentos de categoria
   * GET /admin/seo/internal-links/category-map
   */
  public function categoryMap()
  {
    $maps = $this->db->table('seo_link_category_map')
      ->orderBy('blog_category_slug', 'ASC')
      ->get()->getResultArray();

    $blogCategories = $this->db->table('blog_categories')
      ->where('is_active', 1)
      ->orderBy('sort_order', 'ASC')
      ->get()->getResultArray();

    return view('admin/seo/category-map', [
      'title' => 'Mapeamento de Categorias — Admin',
      'maps' => $maps,
      'blogCategories' => $blogCategories,
    ]);
  }

  /**
   * Salvar mapeamento de categoria
   * POST /admin/seo/internal-links/category-map/save
   */
  public function saveCategoryMap()
  {
    $id = $this->request->getPost('id');
    $data = [
      'blog_category_slug' => $this->request->getPost('blog_category_slug'),
      'entity_type' => $this->request->getPost('entity_type'),
      'entity_value' => $this->request->getPost('entity_value'),
      'widget_label' => $this->request->getPost('widget_label') ?: null,
      'widget_limit' => (int)($this->request->getPost('widget_limit') ?? 3),
      'is_active' => (int)($this->request->getPost('is_active') ?? 1),
      'updated_at' => date('Y-m-d H:i:s'),
    ];

    if ($id) {
      $this->db->table('seo_link_category_map')->where('id', $id)->update($data);
    } else {
      $data['created_at'] = date('Y-m-d H:i:s');
      $this->db->table('seo_link_category_map')->insert($data);
    }

    return redirect()->to('/admin/seo/internal-links/category-map')->with('success', 'Mapeamento salvo.');
  }
}
