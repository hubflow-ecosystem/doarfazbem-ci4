<?php

namespace App\Libraries;

/**
 * AutoLinkerService - Linkagem automática de keywords no conteúdo HTML
 *
 * Processa conteúdo HTML e substitui a primeira ocorrência de cada keyword
 * por um link para a URL configurada. Usa DOMDocument para segurança
 * (não linka dentro de <a>, <script>, <code>, <pre>, <h1-h3>).
 */
class AutoLinkerService
{
  private const CACHE_KEY = 'autolinker_rules';
  private const CACHE_TTL = 3600; // 1 hora

  private array $rules = [];
  private int $maxLinks;

  public function __construct(int $maxLinks = 10)
  {
    $this->maxLinks = $maxLinks;
    $this->rules = $this->loadRules();
  }

  /**
   * Processa conteúdo HTML e insere links automáticos
   *
   * @param string $html Conteúdo HTML do artigo
   * @param string|null $categorySlug Slug da categoria do blog (para filtrar regras por escopo)
   * @return string HTML com links inseridos
   */
  public function processContent(string $html, ?string $categorySlug = null): string
  {
    if (empty($html) || empty($this->rules)) {
      return $html;
    }

    // Filtrar regras por escopo de categoria
    $rules = $this->filterRulesByScope($this->rules, $categorySlug);
    if (empty($rules)) {
      return $html;
    }

    $linkedKeywords = [];
    $linkCount = 0;

    // Wrap para DOMDocument não adicionar html/body/doctype
    $wrapped = '<div id="__autolinker__">' . $html . '</div>';

    $dom = new \DOMDocument('1.0', 'UTF-8');
    libxml_use_internal_errors(true);
    $dom->loadHTML(
      '<?xml encoding="UTF-8">' . $wrapped,
      LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();

    $xpath = new \DOMXPath($dom);

    // Buscar nós de texto que NÃO estejam dentro de tags proibidas
    $textNodes = $xpath->query(
      '//div[@id="__autolinker__"]//text()' .
      '[not(ancestor::a)]' .
      '[not(ancestor::script)]' .
      '[not(ancestor::style)]' .
      '[not(ancestor::code)]' .
      '[not(ancestor::pre)]' .
      '[not(ancestor::h1)]' .
      '[not(ancestor::h2)]' .
      '[not(ancestor::h3)]'
    );

    if (!$textNodes || $textNodes->length === 0) {
      return $html;
    }

    // Coletar nós para processar (não modificar durante iteração)
    $nodesToProcess = [];
    foreach ($textNodes as $node) {
      $nodesToProcess[] = $node;
    }

    foreach ($nodesToProcess as $textNode) {
      if ($linkCount >= $this->maxLinks) break;
      if (!$textNode->parentNode) continue;

      $text = $textNode->nodeValue;
      if (mb_strlen(trim($text)) < 3) continue;

      foreach ($rules as $rule) {
        if ($linkCount >= $this->maxLinks) break;

        $kwLower = mb_strtolower($rule['keyword'], 'UTF-8');
        if (isset($linkedKeywords[$kwLower])) continue;

        $pattern = $this->buildPattern($rule);
        if (!preg_match($pattern, $text, $matches, PREG_OFFSET_CAPTURE)) continue;

        // Dividir texto: antes | match | depois
        $matchText = $matches[0][0];
        $matchOffset = $matches[0][1];
        $beforeText = mb_substr($text, 0, mb_strlen(substr($text, 0, $matchOffset), 'UTF-8'), 'UTF-8');
        $afterText = mb_substr($text, mb_strlen($beforeText, 'UTF-8') + mb_strlen($matchText, 'UTF-8'), null, 'UTF-8');

        // Criar fragmento: texto_antes + <a> + texto_depois
        $parent = $textNode->parentNode;

        if (!empty($beforeText)) {
          $parent->insertBefore($dom->createTextNode($beforeText), $textNode);
        }

        $anchor = $dom->createElement('a');
        $anchor->setAttribute('href', $rule['target_url']);
        $anchor->setAttribute('class', 'text-emerald-600 hover:text-emerald-700 underline decoration-dotted');
        if (!empty($rule['target_label'])) {
          $anchor->setAttribute('title', $rule['target_label']);
        }
        if (!empty($rule['open_new_tab'])) {
          $anchor->setAttribute('target', '_blank');
          $anchor->setAttribute('rel', 'noopener');
        }
        $anchor->appendChild($dom->createTextNode($matchText));
        $parent->insertBefore($anchor, $textNode);

        if (!empty($afterText)) {
          $parent->insertBefore($dom->createTextNode($afterText), $textNode);
        }

        $parent->removeChild($textNode);

        $linkedKeywords[$kwLower] = true;
        $linkCount++;

        // Incrementar hit_count da regra (async, não bloqueia)
        $this->incrementHitCount($rule['id']);

        break; // Próximo textNode (este já foi substituído)
      }
    }

    // Extrair conteúdo do wrapper
    $wrapper = $dom->getElementById('__autolinker__');
    if (!$wrapper) return $html;

    $result = '';
    foreach ($wrapper->childNodes as $child) {
      $result .= $dom->saveHTML($child);
    }

    return $result;
  }

  /**
   * Constrói o regex pattern para a keyword
   */
  private function buildPattern(array $rule): string
  {
    $kw = preg_quote($rule['keyword'], '/');
    $flags = ($rule['case_sensitive'] ?? 0) ? 'u' : 'iu';

    if ($rule['match_whole_word'] ?? 1) {
      // Word boundary com suporte a acentos portugueses
      return '/(?<![a-záàâãéèêíïóôõöúüçñ\w])' . $kw . '(?![a-záàâãéèêíïóôõöúüçñ\w])/' . $flags;
    }

    return '/' . $kw . '/' . $flags;
  }

  /**
   * Filtra regras por escopo de categoria
   */
  private function filterRulesByScope(array $rules, ?string $categorySlug): array
  {
    if ($categorySlug === null) return $rules;

    return array_filter($rules, function ($rule) use ($categorySlug) {
      // Regras globais (sem escopo) sempre aplicam
      if (empty($rule['category_scope'])) return true;
      // Regras com escopo só aplicam na categoria correspondente
      return $rule['category_scope'] === $categorySlug;
    });
  }

  /**
   * Carrega regras do banco com cache
   */
  private function loadRules(): array
  {
    $cached = cache(self::CACHE_KEY);
    if ($cached !== null && $cached !== false) return $cached;

    try {
      $db = db_connect();
      if (!in_array('seo_link_rules', $db->listTables())) return [];

      $rules = $db->table('seo_link_rules')
        ->where('is_active', 1)
        ->orderBy('priority', 'ASC')
        ->orderBy('CHAR_LENGTH(keyword)', 'DESC') // Keywords maiores têm precedência
        ->get()
        ->getResultArray();
    } catch (\Throwable $e) {
      return [];
    }

    cache()->save(self::CACHE_KEY, $rules, self::CACHE_TTL);
    return $rules;
  }

  /**
   * Incrementa contador de hits da regra
   */
  private function incrementHitCount(int $ruleId): void
  {
    try {
      db_connect()->table('seo_link_rules')
        ->where('id', $ruleId)
        ->set('hit_count', 'hit_count + 1', false)
        ->update();
    } catch (\Throwable $e) {
      // Silencioso - não impactar o rendering
    }
  }

  /**
   * Limpa o cache de regras (chamar após salvar/deletar no admin)
   */
  public static function clearCache(): void
  {
    cache()->delete(self::CACHE_KEY);
  }
}
