<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seeder de regras de linkagem interna para o conteúdo programático.
 * Cross-links entre pilares, campanhas, rifas e páginas institucionais.
 */
class SeoMarketingLinksSeeder extends Seeder
{
  public function run()
  {
    $db = \Config\Database::connect();
    $rules = $this->getRules();
    $inserted = 0;

    foreach ($rules as $rule) {
      // Evitar duplicatas pelo hash
      $hash = md5(strtolower($rule['keyword']));
      $exists = $db->table('seo_link_rules')->where('keyword_hash', $hash)->countAllResults();
      if ($exists > 0) continue;

      $rule['keyword_hash'] = $hash;
      $rule['is_active'] = 1;
      $rule['match_whole_word'] = 1;
      $rule['case_sensitive'] = 0;
      $rule['open_new_tab'] = 0;
      $rule['priority'] = $rule['priority'] ?? 100;
      $rule['created_at'] = date('Y-m-d H:i:s');

      $db->table('seo_link_rules')->insert($rule);
      $inserted++;
    }

    echo "{$inserted} regras de linkagem inseridas de " . count($rules) . " planejadas.\n";
  }

  private function getRules(): array
  {
    return [
      // === LINKS PARA ARTIGOS PILAR (alta prioridade) ===
      ['keyword' => 'doações online', 'target_url' => '/blog/como-fazer-doacoes-online-com-seguranca', 'target_label' => 'Guia de Doações Online', 'priority' => 200],
      ['keyword' => 'doar online', 'target_url' => '/blog/como-fazer-doacoes-online-com-seguranca', 'target_label' => 'como doar online', 'priority' => 200],
      ['keyword' => 'como doar', 'target_url' => '/blog/como-fazer-doacoes-online-com-seguranca', 'target_label' => 'guia de como doar', 'priority' => 190],
      ['keyword' => 'criar campanha', 'target_url' => '/blog/como-criar-campanha-de-doacao-guia-definitivo', 'target_label' => 'guia para criar campanha', 'priority' => 200],
      ['keyword' => 'campanha de doação', 'target_url' => '/blog/como-criar-campanha-de-doacao-guia-definitivo', 'target_label' => 'como criar campanha de doação', 'priority' => 190],
      ['keyword' => 'rifa solidária', 'target_url' => '/blog/rifa-solidaria-guia-completo', 'target_label' => 'guia de rifa solidária', 'priority' => 200],
      ['keyword' => 'números da sorte', 'target_url' => '/rifas', 'target_label' => 'rifas ativas', 'priority' => 180],
      ['keyword' => 'transparência', 'target_url' => '/blog/transparencia-em-doacoes-fator-numero-um', 'target_label' => 'transparência em doações', 'priority' => 190],
      ['keyword' => 'pequenas doações', 'target_url' => '/blog/poder-pequenas-doacoes-10-reais-transforma-vidas', 'target_label' => 'o poder das pequenas doações', 'priority' => 190],
      ['keyword' => 'histórias de doação', 'target_url' => '/blog/historias-campanhas-doacao-mudaram-vidas', 'target_label' => 'histórias inspiradoras', 'priority' => 180],

      // === LINKS PARA ARTIGOS SATÉLITE ===
      ['keyword' => 'doação PIX', 'target_url' => '/blog/doacao-via-pix-como-funciona', 'target_label' => 'como doar via PIX', 'priority' => 150],
      ['keyword' => 'PIX solidário', 'target_url' => '/blog/doacao-via-pix-como-funciona', 'target_label' => 'doação via PIX', 'priority' => 140],
      ['keyword' => 'imposto de renda', 'target_url' => '/blog/deduzir-doacoes-imposto-de-renda', 'target_label' => 'deduzir no IR', 'priority' => 150],
      ['keyword' => 'deduzir doação', 'target_url' => '/blog/deduzir-doacoes-imposto-de-renda', 'target_label' => 'como deduzir doações', 'priority' => 150],
      ['keyword' => 'doação recorrente', 'target_url' => '/blog/doacao-recorrente-por-que-doar-todo-mes', 'target_label' => 'doação recorrente', 'priority' => 140],
      ['keyword' => 'golpe doação', 'target_url' => '/blog/golpes-em-doacoes-online-como-se-proteger', 'target_label' => 'como evitar golpes', 'priority' => 150],
      ['keyword' => 'crowdfunding', 'target_url' => '/blog/crowdfunding-no-brasil-o-que-e-como-funciona', 'target_label' => 'crowdfunding no Brasil', 'priority' => 160],
      ['keyword' => 'financiamento coletivo', 'target_url' => '/blog/crowdfunding-no-brasil-o-que-e-como-funciona', 'target_label' => 'financiamento coletivo', 'priority' => 150],
      ['keyword' => 'vaquinha online', 'target_url' => '/blog/vaquinha-online-como-criar', 'target_label' => 'como criar vaquinha', 'priority' => 160],
      ['keyword' => 'ONG confiável', 'target_url' => '/blog/doar-para-ongs-como-escolher-organizacao-confiavel', 'target_label' => 'como escolher ONG', 'priority' => 140],
      ['keyword' => 'doação anônima', 'target_url' => '/blog/doacao-anonima-como-doar-sem-aparecer', 'target_label' => 'doação anônima', 'priority' => 130],
      ['keyword' => 'storytelling', 'target_url' => '/blog/storytelling-campanhas-doacao', 'target_label' => 'storytelling para campanhas', 'priority' => 130],
      ['keyword' => 'divulgar campanha', 'target_url' => '/blog/divulgar-campanha-doacao-redes-sociais', 'target_label' => 'divulgar nas redes sociais', 'priority' => 140],
      ['keyword' => 'campanha médica', 'target_url' => '/blog/campanha-medica-arrecadar-tratamento-saude', 'target_label' => 'campanha médica', 'priority' => 140],
      ['keyword' => 'campanha animal', 'target_url' => '/blog/campanha-animais-resgate-tratamento', 'target_label' => 'campanha para animais', 'priority' => 130],
      ['keyword' => 'prestação de contas', 'target_url' => '/blog/prestacao-contas-campanhas-template', 'target_label' => 'prestação de contas', 'priority' => 140],
      ['keyword' => 'rifa legal', 'target_url' => '/blog/rifa-e-legal-legislacao-brasil', 'target_label' => 'legislação de rifas', 'priority' => 150],
      ['keyword' => 'prêmios rifa', 'target_url' => '/blog/premios-para-rifa-ideias', 'target_label' => 'ideias de prêmios', 'priority' => 140],
      ['keyword' => 'vender rifa WhatsApp', 'target_url' => '/blog/vender-numeros-rifa-whatsapp', 'target_label' => 'vender pelo WhatsApp', 'priority' => 130],
      ['keyword' => 'rifa celular', 'target_url' => '/blog/rifa-de-celular-como-criar', 'target_label' => 'rifa de celular', 'priority' => 130],
      ['keyword' => 'sorteio transparente', 'target_url' => '/blog/sorteio-transparente-rifa-ao-vivo', 'target_label' => 'sorteio ao vivo', 'priority' => 130],
      ['keyword' => 'plataforma doação', 'target_url' => '/blog/plataformas-doacao-comparativo-taxas', 'target_label' => 'comparativo de plataformas', 'priority' => 140],
      ['keyword' => 'OSCIP', 'target_url' => '/blog/oscip-ong-instituto-diferencas', 'target_label' => 'diferenças OSCIP/ONG', 'priority' => 120],
      ['keyword' => 'nota fiscal doação', 'target_url' => '/blog/nota-fiscal-doacao-quando-como-emitir', 'target_label' => 'nota fiscal de doação', 'priority' => 120],
      ['keyword' => 'LGPD', 'target_url' => '/blog/privacidade-campanhas-protecao-dados-doadores', 'target_label' => 'LGPD e doações', 'priority' => 120],
      ['keyword' => 'ESG', 'target_url' => '/blog/doacao-empresas-esg-responsabilidade-social', 'target_label' => 'ESG e doações', 'priority' => 120],
      ['keyword' => 'Giving Tuesday', 'target_url' => '/blog/dia-de-doar-giving-tuesday', 'target_label' => 'Dia de Doar', 'priority' => 110],
      ['keyword' => 'ODS', 'target_url' => '/blog/ods-doacoes-objetivos-desenvolvimento-sustentavel', 'target_label' => 'ODS e doações', 'priority' => 110],
      ['keyword' => 'doação aniversário', 'target_url' => '/blog/doacao-aniversario-trocar-presentes-doacoes', 'target_label' => 'doação de aniversário', 'priority' => 110],

      // === LINKS PARA PÁGINAS INSTITUCIONAIS ===
      ['keyword' => 'DoarFazBem', 'target_url' => '/sobre', 'target_label' => 'sobre o DoarFazBem', 'priority' => 250],
      ['keyword' => 'como funciona', 'target_url' => '/como-funciona', 'target_label' => 'como funciona', 'priority' => 200],
      ['keyword' => 'campanhas ativas', 'target_url' => '/campaigns', 'target_label' => 'ver campanhas ativas', 'priority' => 180],
      ['keyword' => 'rifas ativas', 'target_url' => '/rifas', 'target_label' => 'ver rifas ativas', 'priority' => 170],
    ];
  }
}
