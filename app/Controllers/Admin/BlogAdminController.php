<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BlogPostModel;
use App\Models\BlogCategoryModel;

/**
 * BlogAdminController - Editor de Blog estilo WordPress para SuperAdmin
 *
 * Permite criar, editar, excluir artigos com editor rico.
 * SEO/GEO/metatags/schemas são aplicados automaticamente.
 */
class BlogAdminController extends BaseController
{
  protected BlogPostModel $postModel;
  protected BlogCategoryModel $categoryModel;

  public function __construct()
  {
    $this->postModel = new BlogPostModel();
    $this->categoryModel = new BlogCategoryModel();
    helper(['seo', 'blog']);
  }

  /**
   * Listar todos os artigos
   * GET /admin/blog
   */
  public function index()
  {
    $filters = [
      'status' => $this->request->getGet('status'),
      'category_id' => $this->request->getGet('category_id'),
      'search' => $this->request->getGet('q'),
    ];

    $posts = $this->postModel->getAllForAdmin($filters);
    $categories = $this->categoryModel->getActive();

    return view('admin/blog/index', [
      'title' => 'Blog — Gerenciar Artigos',
      'posts' => $posts,
      'categories' => $categories,
      'filters' => $filters,
    ]);
  }

  /**
   * Formulário de criar artigo
   * GET /admin/blog/create
   */
  public function create()
  {
    $categories = $this->categoryModel->getActive();
    return view('admin/blog/editor', [
      'title' => 'Novo Artigo — Blog',
      'post' => null,
      'categories' => $categories,
      'isEdit' => false,
    ]);
  }

  /**
   * Salvar novo artigo
   * POST /admin/blog/create
   */
  public function store()
  {
    $data = $this->getPostData();

    // Gerar slug
    $data['slug'] = $this->postModel->generateUniqueSlug($data['title']);

    // Calcular tempo de leitura
    $data['reading_time'] = reading_time($data['content']);

    // Auto-gerar excerpt se vazio
    if (empty($data['excerpt'])) {
      $data['excerpt'] = blog_excerpt($data['content'], 200);
    }

    // Auto-gerar meta tags se vazios
    if (empty($data['meta_title'])) {
      $data['meta_title'] = $data['title'] . ' | Blog DoarFazBem';
    }
    if (empty($data['meta_description'])) {
      $data['meta_description'] = blog_excerpt($data['content'], 160);
    }

    // Upload de imagem destaque
    $data = $this->handleImageUpload($data);

    // Definir data de publicação
    if ($data['status'] === 'published' && empty($data['published_at'])) {
      $data['published_at'] = date('Y-m-d H:i:s');
    }

    // Author
    $data['author_id'] = session()->get('user_id');
    $data['author_name'] = session()->get('user_name') ?? 'DoarFazBem';
    $data['source'] = 'manual';

    $id = $this->postModel->insert($data);

    if ($id) {
      return redirect()->to('/admin/blog/edit/' . $id)->with('success', 'Artigo criado com sucesso!');
    }

    return redirect()->back()->withInput()->with('error', 'Erro ao criar artigo.');
  }

  /**
   * Formulário de editar artigo
   * GET /admin/blog/edit/:id
   */
  public function edit($id)
  {
    $post = $this->postModel->find($id);
    if (!$post) return redirect()->to('/admin/blog')->with('error', 'Artigo não encontrado');

    $categories = $this->categoryModel->getActive();
    return view('admin/blog/editor', [
      'title' => 'Editar: ' . $post['title'],
      'post' => $post,
      'categories' => $categories,
      'isEdit' => true,
    ]);
  }

  /**
   * Atualizar artigo
   * POST /admin/blog/update/:id
   */
  public function update($id)
  {
    $post = $this->postModel->find($id);
    if (!$post) return redirect()->to('/admin/blog')->with('error', 'Artigo não encontrado');

    $data = $this->getPostData();

    // Atualizar slug se título mudou
    if ($data['title'] !== $post['title']) {
      $data['slug'] = $this->postModel->generateUniqueSlug($data['title']);
    }

    $data['reading_time'] = reading_time($data['content']);

    if (empty($data['meta_title'])) {
      $data['meta_title'] = $data['title'] . ' | Blog DoarFazBem';
    }
    if (empty($data['meta_description'])) {
      $data['meta_description'] = blog_excerpt($data['content'], 160);
    }

    $data = $this->handleImageUpload($data);

    // Se está publicando pela primeira vez
    if ($data['status'] === 'published' && $post['status'] !== 'published' && empty($data['published_at'])) {
      $data['published_at'] = date('Y-m-d H:i:s');
    }

    $this->postModel->update($id, $data);
    return redirect()->to('/admin/blog/edit/' . $id)->with('success', 'Artigo atualizado!');
  }

  /**
   * Excluir artigo (soft delete)
   * POST /admin/blog/delete/:id
   */
  public function delete($id)
  {
    $this->postModel->delete($id);
    return redirect()->to('/admin/blog')->with('success', 'Artigo movido para lixeira');
  }

  /**
   * Duplicar artigo
   * POST /admin/blog/duplicate/:id
   */
  public function duplicate($id)
  {
    $post = $this->postModel->find($id);
    if (!$post) return redirect()->to('/admin/blog')->with('error', 'Artigo não encontrado');

    unset($post['id'], $post['created_at'], $post['updated_at'], $post['deleted_at']);
    $post['title'] = $post['title'] . ' (cópia)';
    $post['slug'] = $this->postModel->generateUniqueSlug($post['title']);
    $post['status'] = 'draft';
    $post['published_at'] = null;
    $post['views_count'] = 0;

    $newId = $this->postModel->insert($post);
    return redirect()->to('/admin/blog/edit/' . $newId)->with('success', 'Artigo duplicado');
  }

  /**
   * Upload de imagem via AJAX (para o editor)
   * POST /admin/blog/upload-image
   */
  public function uploadImage()
  {
    $file = $this->request->getFile('image');
    if (!$file || !$file->isValid()) {
      return $this->response->setJSON(['success' => false, 'message' => 'Arquivo inválido']);
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    if (!in_array($file->getMimeType(), $allowedTypes)) {
      return $this->response->setJSON(['success' => false, 'message' => 'Tipo de arquivo não permitido']);
    }

    $uploadDir = FCPATH . 'uploads/blog/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $newName = date('Y-m') . '-' . $file->getRandomName();
    $file->move($uploadDir, $newName);

    $url = base_url('uploads/blog/' . $newName);
    return $this->response->setJSON([
      'success' => true,
      'url' => $url,
      'filename' => $newName,
    ]);
  }

  /**
   * Gerenciar categorias
   * GET /admin/blog/categories
   */
  public function categories()
  {
    $categories = $this->categoryModel->getWithPostCount();
    return view('admin/blog/categories', [
      'title' => 'Categorias do Blog',
      'categories' => $categories,
    ]);
  }

  /**
   * Salvar categoria
   * POST /admin/blog/categories/save
   */
  public function saveCategory()
  {
    $id = (int)$this->request->getPost('id');
    $data = [
      'name' => $this->request->getPost('name'),
      'slug' => generate_slug($this->request->getPost('name')),
      'description' => $this->request->getPost('description'),
      'icon' => $this->request->getPost('icon'),
      'color' => $this->request->getPost('color'),
      'parent_id' => $this->request->getPost('parent_id') ?: null,
      'meta_title' => $this->request->getPost('meta_title'),
      'meta_description' => $this->request->getPost('meta_description'),
      'sort_order' => (int)($this->request->getPost('sort_order') ?? 0),
      'is_active' => 1,
    ];

    if ($id > 0) {
      $this->categoryModel->update($id, $data);
    } else {
      $this->categoryModel->insert($data);
    }

    return redirect()->to('/admin/blog/categories')->with('success', 'Categoria salva');
  }

  // =========================================================================
  // HELPERS PRIVADOS
  // =========================================================================

  private function getPostData(): array
  {
    return [
      'title' => $this->request->getPost('title'),
      'excerpt' => $this->request->getPost('excerpt'),
      'content' => $this->request->getPost('content'),
      'category_id' => $this->request->getPost('category_id') ?: null,
      'status' => $this->request->getPost('status') ?? 'draft',
      'published_at' => $this->request->getPost('published_at') ?: null,
      'scheduled_at' => $this->request->getPost('scheduled_at') ?: null,
      'meta_title' => $this->request->getPost('meta_title'),
      'meta_description' => $this->request->getPost('meta_description'),
      'meta_keywords' => $this->request->getPost('meta_keywords'),
      'image_alt' => $this->request->getPost('image_alt'),
      'image_caption' => $this->request->getPost('image_caption'),
      'image_credit' => $this->request->getPost('image_credit'),
      'tags' => $this->request->getPost('tags') ? json_encode(array_map('trim', explode(',', $this->request->getPost('tags')))) : null,
      'is_featured' => $this->request->getPost('is_featured') ? 1 : 0,
      'allow_comments' => $this->request->getPost('allow_comments') ? 1 : 0,
    ];
  }

  private function handleImageUpload(array $data): array
  {
    $file = $this->request->getFile('featured_image');
    if ($file && $file->isValid()) {
      $uploadDir = FCPATH . 'uploads/blog/';
      if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
      $newName = date('Y-m') . '-' . $file->getRandomName();
      $file->move($uploadDir, $newName);
      $data['featured_image'] = base_url('uploads/blog/' . $newName);
    }
    return $data;
  }
}
