<?php

/**
 * Helper do Blog para DoarFazBem
 */

if (!function_exists('format_blog_date')) {
  /**
   * Formata data do blog em português
   */
  function format_blog_date(?string $date): string
  {
    if (empty($date)) return '';
    $months = [
      1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril',
      5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto',
      9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro',
    ];
    $ts = strtotime($date);
    $day = date('d', $ts);
    $month = $months[(int)date('n', $ts)];
    $year = date('Y', $ts);
    return "{$day} de {$month} de {$year}";
  }
}

if (!function_exists('blog_excerpt')) {
  /**
   * Gera excerpt de conteúdo
   */
  function blog_excerpt(string $content, int $length = 160): string
  {
    $text = strip_tags($content);
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . '...';
  }
}

if (!function_exists('blog_image_url')) {
  /**
   * Retorna URL da imagem do blog (com fallback)
   */
  function blog_image_url(?string $image): string
  {
    if (empty($image)) {
      return base_url('assets/images/blog-default.jpg');
    }
    if (str_starts_with($image, 'http')) {
      return $image;
    }
    return base_url('uploads/blog/' . $image);
  }
}

if (!function_exists('blog_status_badge')) {
  /**
   * Badge HTML para status do post
   */
  function blog_status_badge(string $status): string
  {
    $badges = [
      'draft' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Rascunho</span>',
      'published' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Publicado</span>',
      'scheduled' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Agendado</span>',
    ];
    return $badges[$status] ?? $badges['draft'];
  }
}
