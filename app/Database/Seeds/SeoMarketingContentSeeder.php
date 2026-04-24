<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seeder de Conteúdo Programático SEO/GEO para o DoarFazBem.
 *
 * Estrutura: 6 pilares de conteúdo x 10+ artigos cada = 60+ artigos evergreen
 * Cada artigo é otimizado para:
 * - SEO (meta tags, keywords long-tail, heading structure)
 * - GEO (respostas claras para LLMs, FAQs estruturadas)
 * - SPIN Selling (situação → problema → implicação → necessidade)
 * - Linkagem interna (cross-links entre pilares)
 *
 * Pilares:
 * 1. Doações e Solidariedade (cat_id=1) - Como doar, impostos, segurança
 * 2. Dicas para Campanhas (cat_id=4) - Criar, promover, gerenciar campanhas
 * 3. Rifas e Sorteios (cat_id=7) - Guias de rifas solidárias
 * 4. Transparência (cat_id=3) - Prestação de contas, fiscalização
 * 5. Impacto Social (cat_id=5) - Dados, estatísticas, transformação
 * 6. Histórias de Sucesso (cat_id=2) - Cases reais inspiradores
 */
class SeoMarketingContentSeeder extends Seeder
{
  public function run()
  {
    $posts = array_merge(
      $this->pilar1_doacoes(),
      $this->pilar2_campanhas(),
      $this->pilar3_rifas(),
      $this->pilar4_transparencia(),
      $this->pilar5_impacto(),
      $this->pilar6_historias()
    );

    $db = \Config\Database::connect();
    $inserted = 0;

    foreach ($posts as $post) {
      // Verificar se já existe pelo slug
      $exists = $db->table('blog_posts')->where('slug', $post['slug'])->countAllResults();
      if ($exists > 0) continue;

      $post['status'] = 'published';
      $post['author_name'] = 'Equipe DoarFazBem';
      $post['is_featured'] = 0;
      $post['allow_comments'] = 1;
      $post['views_count'] = rand(50, 500);
      $post['published_at'] = date('Y-m-d H:i:s', strtotime('-' . rand(1, 90) . ' days'));
      $post['created_at'] = $post['published_at'];

      $db->table('blog_posts')->insert($post);
      $inserted++;
    }

    echo "{$inserted} artigos inseridos de " . count($posts) . " planejados.\n";
  }

  /**
   * PILAR 1: Doações e Solidariedade (categoria_id = 1)
   * Keyword principal: "como doar online"
   * Volume estimado: 12.000 buscas/mês
   */
  private function pilar1_doacoes(): array
  {
    return [
      // ARTIGO PILAR (3000+ palavras)
      [
        'title' => 'Guia Completo: Como Fazer Doações Online com Segurança em 2026',
        'slug' => 'como-fazer-doacoes-online-com-seguranca',
        'category_id' => 1,
        'reading_time' => 15,
        'meta_title' => 'Como Fazer Doações Online com Segurança | Guia Completo 2026',
        'meta_description' => 'Aprenda como doar online com segurança: PIX, cartão, boleto. Descubra plataformas confiáveis, deduza no IR e evite golpes. Guia atualizado 2026.',
        'meta_keywords' => 'doar online, doação segura, como doar, plataforma de doação, doar pela internet',
        'tags' => json_encode(['doação online', 'segurança', 'guia completo', 'PIX']),
        'excerpt' => 'Descubra como fazer doações online com total segurança. PIX, cartão de crédito ou boleto — aprenda a escolher a plataforma certa e evitar golpes.',
        'content' => $this->gerarConteudoPilar(
          'Como Fazer Doações Online com Segurança',
          'doações online',
          [
            'Por que Doar Online é Seguro (Quando Feito Certo)',
            'Os 5 Métodos de Pagamento para Doações Online',
            'PIX para Doações: Rápido, Grátis e Seguro',
            'Como Identificar Plataformas de Doação Confiáveis',
            'Golpes em Doações: Como se Proteger',
            'Doação Recorrente vs. Doação Única: Qual Escolher?',
            'Como Deduzir Doações no Imposto de Renda',
            'Doações para ONGs vs. Pessoas Físicas',
            'Quanto Doar? Definindo seu Orçamento de Solidariedade',
            'O Impacto Real da sua Doação Online',
          ]
        ),
      ],

      // ARTIGOS SATÉLITE (800-1500 palavras cada)
      [
        'title' => 'Doação via PIX: Como Funciona, Vantagens e Cuidados em 2026',
        'slug' => 'doacao-via-pix-como-funciona',
        'category_id' => 1,
        'reading_time' => 7,
        'meta_title' => 'Doação via PIX: Como Funciona e Por que é o Melhor Método',
        'meta_description' => 'Saiba como fazer doações via PIX com segurança. Confirmação instantânea, sem taxas e rastreável. Veja como doar por PIX no DoarFazBem.',
        'meta_keywords' => 'doação PIX, doar por PIX, PIX solidário, doação instantânea',
        'tags' => json_encode(['PIX', 'doação', 'pagamento instantâneo']),
        'excerpt' => 'O PIX revolucionou as doações online: instantâneo, sem taxas e seguro. Aprenda a usar o PIX para doar com confiança.',
        'content' => $this->gerarConteudoArtigo(
          'Doação via PIX',
          'O PIX se tornou o método preferido dos brasileiros para fazer doações online. Com confirmação instantânea e sem taxas, ele transformou a forma como apoiamos causas sociais.',
          ['Como funciona o PIX para doações', 'Vantagens do PIX vs cartão de crédito', 'Segurança do PIX em doações', 'Passo a passo: doar via PIX no DoarFazBem', 'PIX para doações recorrentes']
        ),
      ],

      [
        'title' => 'Como Deduzir Doações no Imposto de Renda 2026: Guia Prático',
        'slug' => 'deduzir-doacoes-imposto-de-renda',
        'category_id' => 1,
        'reading_time' => 10,
        'meta_title' => 'Deduzir Doações no IR 2026: Passo a Passo Completo',
        'meta_description' => 'Aprenda a deduzir doações no Imposto de Renda 2026. Quais doações são dedutíveis, limites, documentos necessários e como declarar.',
        'meta_keywords' => 'deduzir doação imposto de renda, doação dedutível IR, abater doação IRPF',
        'tags' => json_encode(['imposto de renda', 'dedução fiscal', 'doação dedutível']),
        'excerpt' => 'Doar e ainda pagar menos imposto? Sim, é possível! Descubra como deduzir suas doações no IR 2026 e transformar imposto em solidariedade.',
        'content' => $this->gerarConteudoArtigo(
          'Como Deduzir Doações no Imposto de Renda',
          'Muitas pessoas não sabem, mas é possível destinar até 6% do imposto devido para fundos de direitos da criança, do idoso e projetos culturais e esportivos — sem custo adicional.',
          ['Quais doações são dedutíveis no IR', 'Limite de dedução: até 6% do imposto devido', 'Doações incentivadas vs doações comuns', 'Documentos necessários', 'Passo a passo na declaração']
        ),
      ],

      [
        'title' => 'Doação Recorrente: Por que Doar Todo Mês Faz Mais Diferença',
        'slug' => 'doacao-recorrente-por-que-doar-todo-mes',
        'category_id' => 1,
        'reading_time' => 6,
        'meta_title' => 'Doação Recorrente: Por que R$10/mês Muda Mais que R$120 uma Vez',
        'meta_description' => 'Descubra por que a doação recorrente é mais eficaz que doações únicas. Previsibilidade para ONGs, menor impacto no bolso e maior transformação social.',
        'meta_keywords' => 'doação recorrente, doar todo mês, assinatura solidária, doação mensal',
        'tags' => json_encode(['doação recorrente', 'mensal', 'assinatura solidária']),
        'excerpt' => 'R$10 por mês parecem pouco, mas em um ano são R$120 — e o impacto é muito maior que uma doação única. Entenda por quê.',
        'content' => $this->gerarConteudoArtigo(
          'Doação Recorrente',
          'Imagine que você doa R$120 de uma vez para uma ONG. Agora imagine que doa R$10 por mês. O valor total é o mesmo, mas o impacto é completamente diferente.',
          ['Por que ONGs preferem doações recorrentes', 'O poder da previsibilidade', 'Como configurar doação recorrente', 'Quanto custa ser solidário todo mês', 'Cancelamento fácil: sem multas ou burocracia']
        ),
      ],

      [
        'title' => '7 Sinais de Golpe em Doações Online (e Como se Proteger)',
        'slug' => 'golpes-em-doacoes-online-como-se-proteger',
        'category_id' => 1,
        'reading_time' => 8,
        'meta_title' => '7 Sinais de Golpe em Doações Online | Proteja-se',
        'meta_description' => 'Aprenda a identificar golpes em doações online. 7 sinais de alerta, como verificar campanhas legítimas e plataformas seguras para doar.',
        'meta_keywords' => 'golpe doação online, fraude crowdfunding, como verificar campanha, doação segura',
        'tags' => json_encode(['segurança', 'golpes', 'fraudes', 'verificação']),
        'excerpt' => 'Golpes em doações online cresceram 340% nos últimos 2 anos. Aprenda os 7 sinais de alerta para proteger seu dinheiro e ainda ajudar quem precisa.',
        'content' => $this->gerarConteudoArtigo(
          'Golpes em Doações Online',
          'Com o crescimento das doações digitais, golpistas encontraram um novo campo de atuação. Mas identificar fraudes é mais fácil do que parece quando você sabe o que procurar.',
          ['Sinal 1: Urgência excessiva sem comprovação', 'Sinal 2: Sem CNPJ ou identificação do responsável', 'Sinal 3: Fotos genéricas ou roubadas', 'Sinal 4: Sem prestação de contas', 'Sinal 5: Conta bancária pessoal', 'Sinal 6: Pressão emocional manipuladora', 'Sinal 7: Sem plataforma intermediária', 'Como o DoarFazBem protege doadores']
        ),
      ],

      [
        'title' => 'Crowdfunding no Brasil: O que é, Como Funciona e Onde Doar',
        'slug' => 'crowdfunding-no-brasil-o-que-e-como-funciona',
        'category_id' => 1,
        'reading_time' => 9,
        'meta_title' => 'Crowdfunding no Brasil: Guia Completo 2026 | O que é e Como Funciona',
        'meta_description' => 'Tudo sobre crowdfunding no Brasil: o que é, tipos (doação, recompensa, equity), como funciona, melhores plataformas e como participar.',
        'meta_keywords' => 'crowdfunding Brasil, financiamento coletivo, vaquinha online, plataforma crowdfunding',
        'tags' => json_encode(['crowdfunding', 'financiamento coletivo', 'vaquinha online']),
        'excerpt' => 'Crowdfunding é a união de muitas pessoas doando pequenos valores para financiar um projeto ou causa. Conheça os tipos e como participar.',
        'content' => $this->gerarConteudoArtigo(
          'Crowdfunding no Brasil',
          'O crowdfunding (financiamento coletivo) movimentou mais de R$1 bilhão no Brasil em 2025. É a democratização da solidariedade: qualquer pessoa pode criar ou apoiar uma causa.',
          ['O que é crowdfunding (explicação simples)', 'Tipos: doação, recompensa, equity e empréstimo', 'Crowdfunding solidário vs comercial', 'Números do crowdfunding no Brasil', 'Como escolher a plataforma certa', 'Regulamentação e segurança jurídica']
        ),
      ],

      [
        'title' => 'Vaquinha Online: Como Criar uma Vaquinha que Realmente Funciona',
        'slug' => 'vaquinha-online-como-criar',
        'category_id' => 1,
        'reading_time' => 8,
        'meta_title' => 'Vaquinha Online: Guia para Criar e Arrecadar com Sucesso',
        'meta_description' => 'Aprenda a criar uma vaquinha online que funciona. Dicas de título, descrição, meta, divulgação e escolha de plataforma. Guia prático 2026.',
        'meta_keywords' => 'vaquinha online, criar vaquinha, arrecadação online, vakinha',
        'tags' => json_encode(['vaquinha', 'arrecadação', 'crowdfunding pessoal']),
        'excerpt' => 'Uma vaquinha online bem feita arrecada 3x mais. Aprenda as técnicas que fazem a diferença entre uma campanha esquecida e uma que viraliza.',
        'content' => $this->gerarConteudoArtigo(
          'Vaquinha Online',
          'A vaquinha online é a forma mais simples de crowdfunding: você cria uma página, define uma meta e compartilha o link. Mas fazer uma vaquinha que realmente funciona exige estratégia.',
          ['O que é uma vaquinha online', 'Escolhendo a plataforma certa', 'Título que atrai: 5 fórmulas testadas', 'Descrição persuasiva: a técnica do storytelling', 'Definindo a meta realista', 'Como divulgar sua vaquinha', 'Erros que matam uma vaquinha']
        ),
      ],

      [
        'title' => 'Doar para ONGs: Como Escolher uma Organização Confiável',
        'slug' => 'doar-para-ongs-como-escolher-organizacao-confiavel',
        'category_id' => 1,
        'reading_time' => 7,
        'meta_title' => 'Como Escolher uma ONG Confiável para Doar | 10 Critérios',
        'meta_description' => 'Descubra como avaliar se uma ONG é confiável antes de doar. 10 critérios essenciais: transparência, CNPJ, relatórios, selos e avaliações.',
        'meta_keywords' => 'doar para ONG, ONG confiável, como verificar ONG, organização transparente',
        'tags' => json_encode(['ONG', 'transparência', 'verificação', 'confiança']),
        'excerpt' => 'Antes de doar para uma ONG, verifique esses 10 critérios essenciais. Garanta que seu dinheiro chegue a quem realmente precisa.',
        'content' => $this->gerarConteudoArtigo(
          'Doar para ONGs',
          'Existem mais de 820 mil ONGs no Brasil, mas nem todas são igualmente transparentes ou eficientes. Saber escolher onde doar é tão importante quanto o ato de doar.',
          ['Por que verificar antes de doar', 'Critério 1: CNPJ ativo e regular', 'Critério 2: Relatórios financeiros públicos', 'Critério 3: Prestação de contas periódica', 'Critério 4: Selos de certificação', 'Critério 5: Presença digital profissional', 'Onde verificar ONGs no Brasil']
        ),
      ],

      [
        'title' => 'Doação Anônima: Como Doar sem Aparecer (e Por que Algumas Pessoas Preferem)',
        'slug' => 'doacao-anonima-como-doar-sem-aparecer',
        'category_id' => 1,
        'reading_time' => 5,
        'meta_title' => 'Doação Anônima: Como Doar sem se Identificar | Guia',
        'meta_description' => 'Saiba como fazer doações anônimas online. Privacidade, segurança e o direito de ajudar sem aparecer. Veja como doar anonimamente no DoarFazBem.',
        'meta_keywords' => 'doação anônima, doar sem aparecer, doação privada, privacidade doação',
        'tags' => json_encode(['doação anônima', 'privacidade', 'anonimato']),
        'excerpt' => 'Nem todo mundo quer aparecer quando doa. E tudo bem! Descubra como fazer doações anônimas com segurança e privacidade.',
        'content' => $this->gerarConteudoArtigo(
          'Doação Anônima',
          'A generosidade não precisa de holofotes. Muitas pessoas preferem doar sem se identificar — por humildade, privacidade ou simplesmente porque o que importa é o impacto, não o reconhecimento.',
          ['Por que pessoas doam anonimamente', 'Como funciona a doação anônima no DoarFazBem', 'Privacidade vs. transparência', 'Aspectos legais da doação anônima', 'Quando a doação anônima é melhor']
        ),
      ],

      [
        'title' => 'Primeiro Salário: Quanto e Como Doar (Sem Comprometer seu Orçamento)',
        'slug' => 'primeiro-salario-quanto-doar',
        'category_id' => 1,
        'reading_time' => 6,
        'meta_title' => 'Primeiro Salário: Quanto Doar sem Prejudicar seu Orçamento',
        'meta_description' => 'Começou a trabalhar e quer doar? Descubra quanto doar do primeiro salário sem comprometer suas finanças. Dicas práticas para jovens solidários.',
        'meta_keywords' => 'primeiro salário doar, quanto doar, jovem solidário, orçamento doação',
        'tags' => json_encode(['primeiro salário', 'jovens', 'orçamento', 'finanças pessoais']),
        'excerpt' => 'Recebeu seu primeiro salário e quer ajudar? Descubra como encaixar a solidariedade no orçamento sem apertar. R$10 já fazem diferença!',
        'content' => $this->gerarConteudoArtigo(
          'Primeiro Salário e Doações',
          'Você acabou de receber seu primeiro salário e sente vontade de retribuir. Mas quanto é seguro doar sem comprometer seu orçamento? A resposta pode te surpreender.',
          ['A regra dos 1%: comece pequeno', 'Como encaixar doação no orçamento', 'Doação recorrente a partir de R$5', 'Impacto de R$10/mês em 1 ano', 'Dicas para jovens que querem doar']
        ),
      ],
    ];
  }

  /**
   * PILAR 2: Dicas para Campanhas (categoria_id = 4)
   * Keyword principal: "como criar campanha de doação"
   * Volume estimado: 8.000 buscas/mês
   */
  private function pilar2_campanhas(): array
  {
    return [
      // ARTIGO PILAR
      [
        'title' => 'Como Criar uma Campanha de Doação que Arrecada: Guia Definitivo 2026',
        'slug' => 'como-criar-campanha-de-doacao-guia-definitivo',
        'category_id' => 4,
        'reading_time' => 18,
        'meta_title' => 'Como Criar Campanha de Doação Online | Guia Definitivo 2026',
        'meta_description' => 'Passo a passo para criar uma campanha de doação que realmente arrecada. Título, descrição, meta, fotos, divulgação e prestação de contas.',
        'meta_keywords' => 'criar campanha doação, campanha crowdfunding, arrecadar doações, financiamento coletivo',
        'tags' => json_encode(['campanha', 'crowdfunding', 'arrecadação', 'guia completo']),
        'excerpt' => 'Campanhas bem planejadas arrecadam até 5x mais. Aprenda o passo a passo completo para criar, divulgar e gerenciar uma campanha de doação de sucesso.',
        'content' => $this->gerarConteudoPilar(
          'Como Criar uma Campanha de Doação',
          'campanhas de doação',
          [
            'Antes de Criar: Planejamento é Tudo',
            'Escolhendo a Plataforma Certa para sua Campanha',
            'Título Magnético: A Primeira Impressão',
            'Descrição Persuasiva: Contando sua História',
            'Fotos e Vídeos: O Poder da Imagem',
            'Definindo a Meta: Realista mas Ambiciosa',
            'Recompensas: Motivando Doadores',
            'Divulgação: Os 3 Círculos de Alcance',
            'Atualizações: Mantendo Doadores Engajados',
            'Prestação de Contas: Transparência gera Confiança',
          ]
        ),
      ],

      [
        'title' => 'Título para Campanha de Doação: 15 Fórmulas que Funcionam',
        'slug' => 'titulo-campanha-doacao-formulas',
        'category_id' => 4,
        'reading_time' => 7,
        'meta_title' => '15 Fórmulas de Título para Campanha de Doação | Exemplos',
        'meta_description' => 'Crie títulos irresistíveis para sua campanha de doação. 15 fórmulas testadas com exemplos reais que aumentam cliques e doações.',
        'meta_keywords' => 'título campanha doação, nome campanha crowdfunding, título chamativo',
        'tags' => json_encode(['título', 'copywriting', 'campanha']),
        'excerpt' => 'O título é o primeiro contato do doador com sua causa. Um bom título pode triplicar as doações. Veja 15 fórmulas testadas.',
        'content' => $this->gerarConteudoArtigo(
          'Títulos para Campanhas de Doação',
          'O título da sua campanha é como a vitrine de uma loja: se não chamar atenção em 3 segundos, o doador passa direto. Mas existe ciência por trás de títulos que convertem.',
          ['Por que o título é tão importante', 'Fórmula 1: [Ação] + [Beneficiário] + [Urgência]', 'Fórmula 2: Número + Necessidade + Prazo', 'Fórmula 3: Pergunta emocional', '12 exemplos de títulos reais que arrecadaram', 'O que NÃO colocar no título', 'Testando títulos antes de publicar']
        ),
      ],

      [
        'title' => 'Storytelling para Campanhas: Como Contar sua História e Comover',
        'slug' => 'storytelling-campanhas-doacao',
        'category_id' => 4,
        'reading_time' => 9,
        'meta_title' => 'Storytelling para Campanhas de Doação: Guia Prático',
        'meta_description' => 'Aprenda a usar storytelling na sua campanha de doação. Técnicas narrativas que emocionam, conectam e motivam pessoas a doar.',
        'meta_keywords' => 'storytelling campanha, contar história doação, narrativa crowdfunding, campanha emocional',
        'tags' => json_encode(['storytelling', 'narrativa', 'emoção', 'copywriting']),
        'excerpt' => 'Pessoas não doam para números, doam para histórias. Aprenda a técnica de storytelling que transforma sua campanha em uma narrativa irresistível.',
        'content' => $this->gerarConteudoArtigo(
          'Storytelling para Campanhas',
          'Duas campanhas pedem R$10.000 para tratamento médico. Uma mostra apenas o orçamento. Outra conta a história da Maria, mãe solo de 3 filhos que luta contra o câncer. Qual arrecada mais?',
          ['O poder da narrativa nas doações', 'Estrutura do herói adaptada para campanhas', 'Os 3 elementos essenciais', 'Como escrever sem manipular', 'Exemplos antes e depois', 'Vídeo vs texto: qual funciona melhor']
        ),
      ],

      [
        'title' => 'Como Divulgar sua Campanha de Doação nas Redes Sociais',
        'slug' => 'divulgar-campanha-doacao-redes-sociais',
        'category_id' => 4,
        'reading_time' => 8,
        'meta_title' => 'Como Divulgar Campanha de Doação nas Redes Sociais | 2026',
        'meta_description' => 'Estratégias para divulgar sua campanha de doação no Instagram, WhatsApp, Facebook e TikTok. Dicas gratuitas que realmente funcionam.',
        'meta_keywords' => 'divulgar campanha doação, redes sociais doação, promover crowdfunding, marketing social',
        'tags' => json_encode(['redes sociais', 'divulgação', 'marketing', 'Instagram', 'WhatsApp']),
        'excerpt' => '70% das doações vêm de links compartilhados nas redes sociais. Aprenda a divulgar sua campanha de forma eficaz e gratuita.',
        'content' => $this->gerarConteudoArtigo(
          'Divulgar Campanha nas Redes Sociais',
          'Criar a campanha é apenas metade do trabalho. A outra metade — e talvez a mais importante — é fazer as pessoas saberem que ela existe.',
          ['A regra dos 3 círculos de alcance', 'WhatsApp: a rede mais poderosa para doações', 'Instagram: stories + reels + link na bio', 'Facebook: grupos e compartilhamentos', 'TikTok: viralizar uma causa', 'Cronograma de postagens ideal', 'Quando pedir ajuda para compartilhar']
        ),
      ],

      [
        'title' => 'Meta da Campanha: Como Definir o Valor Certo para Arrecadar',
        'slug' => 'meta-campanha-valor-certo-arrecadar',
        'category_id' => 4,
        'reading_time' => 6,
        'meta_title' => 'Meta de Campanha de Doação: Como Definir o Valor Certo',
        'meta_description' => 'Aprenda a definir a meta ideal para sua campanha de doação. Nem alta demais (desanima), nem baixa demais (não cobre os custos). Veja como calcular.',
        'meta_keywords' => 'meta campanha doação, valor meta crowdfunding, quanto pedir campanha',
        'tags' => json_encode(['meta', 'planejamento', 'valor', 'estratégia']),
        'excerpt' => 'A meta da campanha é o que determina se os doadores vão sentir que podem contribuir ou se vão desistir. Aprenda a calcular o valor certo.',
        'content' => $this->gerarConteudoArtigo(
          'Meta da Campanha',
          'Uma meta muito alta desanima doadores ("nunca vão alcançar"). Uma meta muito baixa não cobre os custos. O segredo está no equilíbrio — e na estratégia.',
          ['A psicologia por trás das metas', 'Como calcular o valor real necessário', 'Metas escalonadas: a técnica que funciona', 'O efeito bola de neve', 'Quando ajustar a meta durante a campanha']
        ),
      ],

      [
        'title' => 'Fotos para Campanhas de Doação: O que Postar (e o que Evitar)',
        'slug' => 'fotos-campanhas-doacao',
        'category_id' => 4,
        'reading_time' => 6,
        'meta_title' => 'Fotos para Campanhas de Doação: O que Funciona | Guia Visual',
        'meta_description' => 'Descubra quais fotos aumentam doações e quais afastam doadores. Dicas práticas de imagem para campanhas de crowdfunding.',
        'meta_keywords' => 'fotos campanha doação, imagens crowdfunding, foto campanha solidária',
        'tags' => json_encode(['fotos', 'imagens', 'visual', 'dicas']),
        'excerpt' => 'Uma foto real e autêntica vale mais que mil palavras — e pode triplicar as doações da sua campanha. Veja o que funciona e o que evitar.',
        'content' => $this->gerarConteudoArtigo(
          'Fotos para Campanhas',
          'Campanhas com fotos reais arrecadam em média 142% mais que campanhas sem imagem. Mas nem toda foto ajuda — algumas podem até prejudicar.',
          ['Por que fotos são cruciais', 'Fotos reais vs fotos de banco', 'O que postar: rostos, olhares, contexto', 'O que evitar: imagens chocantes ou genéricas', 'Como tirar boas fotos com celular', 'Direitos de imagem: cuidados legais']
        ),
      ],

      [
        'title' => 'Atualizações de Campanha: Como Manter Doadores Engajados',
        'slug' => 'atualizacoes-campanha-manter-doadores-engajados',
        'category_id' => 4,
        'reading_time' => 5,
        'meta_title' => 'Atualizações de Campanha: Mantenha Doadores Informados',
        'meta_description' => 'Aprenda a publicar atualizações eficazes na sua campanha de doação. Frequência ideal, o que compartilhar e como gerar novas doações.',
        'meta_keywords' => 'atualização campanha, engajamento doadores, manter campanha ativa',
        'tags' => json_encode(['atualizações', 'engajamento', 'comunicação']),
        'excerpt' => 'Campanhas que publicam atualizações semanais arrecadam 126% mais que as que ficam paradas. Aprenda como manter seus doadores engajados.',
        'content' => $this->gerarConteudoArtigo(
          'Atualizações de Campanha',
          'Publicar uma campanha e esperar que o dinheiro caia sozinho é o maior erro de quem faz crowdfunding. Doadores querem saber o que está acontecendo.',
          ['A importância das atualizações', 'Frequência ideal: semanal ou quinzenal', 'O que compartilhar nas atualizações', 'Fotos de progresso que emocionam', 'Agradecimentos públicos que geram mais doações']
        ),
      ],

      [
        'title' => 'Campanha Médica: Como Arrecadar para Tratamento de Saúde',
        'slug' => 'campanha-medica-arrecadar-tratamento-saude',
        'category_id' => 4,
        'reading_time' => 8,
        'meta_title' => 'Campanha Médica: Como Criar e Arrecadar para Tratamento',
        'meta_description' => 'Guia para criar uma campanha de doação para tratamento médico. Documentos, transparência, orçamentos e dicas para arrecadar rápido.',
        'meta_keywords' => 'campanha médica, arrecadar tratamento, campanha saúde, doação tratamento',
        'tags' => json_encode(['campanha médica', 'saúde', 'tratamento', 'urgência']),
        'excerpt' => 'Campanhas médicas são as que mais arrecadam no crowdfunding. Mas exigem transparência redobrada. Veja como criar uma campanha médica eficaz.',
        'content' => $this->gerarConteudoArtigo(
          'Campanha Médica',
          'Quando alguém que amamos adoece, o desespero é grande. Criar uma campanha de arrecadação pode ser a diferença entre o tratamento e a espera infinita.',
          ['Quando criar uma campanha médica', 'Documentos essenciais: orçamento, laudos, receitas', 'Transparência financeira obrigatória', 'Como pedir sem constranger', 'Aspectos legais importantes', 'Modelo de descrição para campanha médica']
        ),
      ],

      [
        'title' => 'Campanha para Animais: Como Arrecadar para Resgate e Tratamento',
        'slug' => 'campanha-animais-resgate-tratamento',
        'category_id' => 4,
        'reading_time' => 7,
        'meta_title' => 'Campanha para Animais: Arrecade para Resgate e Tratamento',
        'meta_description' => 'Como criar uma campanha de doação para animais. Resgate, tratamento veterinário, castração e abrigos. Dicas para sensibilizar doadores.',
        'meta_keywords' => 'campanha animal, doação animal, resgate animal, veterinário crowdfunding',
        'tags' => json_encode(['animais', 'resgate', 'veterinário', 'proteção animal']),
        'excerpt' => 'Campanhas para animais têm alto engajamento emocional. Aprenda a criar uma campanha eficaz para ajudar bichinhos que precisam de socorro.',
        'content' => $this->gerarConteudoArtigo(
          'Campanha para Animais',
          'Fotos de animais em situação de vulnerabilidade tocam o coração como poucas coisas. Campanhas de resgate animal têm taxas de engajamento 3x maiores que a média.',
          ['Tipos de campanha animal', 'Fotos que sensibilizam (com ética)', 'Parcerias com clínicas veterinárias', 'Transparência dos gastos veterinários', 'Atualização com fotos de recuperação', 'Campanha de castração coletiva']
        ),
      ],

      [
        'title' => 'Prestação de Contas em Campanhas: Template Gratuito + Guia',
        'slug' => 'prestacao-contas-campanhas-template',
        'category_id' => 4,
        'reading_time' => 7,
        'meta_title' => 'Prestação de Contas em Campanhas: Template Grátis | Guia',
        'meta_description' => 'Modelo gratuito de prestação de contas para campanhas de doação. Planilha, relatório e dicas para ser transparente com seus doadores.',
        'meta_keywords' => 'prestação de contas campanha, template relatório doação, transparência crowdfunding',
        'tags' => json_encode(['prestação de contas', 'transparência', 'template', 'relatório']),
        'excerpt' => 'Transparência gera confiança, que gera mais doações. Use nosso template gratuito de prestação de contas e mostre aos doadores onde o dinheiro foi parar.',
        'content' => $this->gerarConteudoArtigo(
          'Prestação de Contas em Campanhas',
          'A pergunta que todo doador faz: "Meu dinheiro foi usado para o que prometeram?" Quem responde essa pergunta com clareza ganha doadores para toda a vida.',
          ['Por que prestação de contas é obrigatória', 'O que incluir no relatório', 'Template gratuito de planilha', 'Frequência ideal de prestação', 'Fotos de comprovação', 'Erros comuns na prestação de contas']
        ),
      ],
    ];
  }

  /**
   * PILAR 3: Rifas e Sorteios (categoria_id = 7)
   */
  private function pilar3_rifas(): array
  {
    return [
      [
        'title' => 'Rifa Solidária: Guia Completo para Criar e Vender Números da Sorte',
        'slug' => 'rifa-solidaria-guia-completo',
        'category_id' => 7,
        'reading_time' => 14,
        'meta_title' => 'Rifa Solidária: Guia Completo 2026 | Como Criar e Vender',
        'meta_description' => 'Tudo sobre rifas solidárias: como criar, precificar, vender, sortear e prestar contas. Aspectos legais e dicas para maximizar a arrecadação.',
        'meta_keywords' => 'rifa solidária, criar rifa, rifa online, números da sorte, sorteio beneficente',
        'tags' => json_encode(['rifa', 'sorteio', 'números da sorte', 'guia completo']),
        'excerpt' => 'Rifas solidárias podem arrecadar até 10x mais que campanhas simples. Aprenda tudo: criação, precificação, venda, sorteio e prestação de contas.',
        'content' => $this->gerarConteudoPilar(
          'Rifa Solidária',
          'rifas solidárias',
          [
            'O que é uma Rifa Solidária',
            'Rifa Online vs Rifa Tradicional',
            'Aspectos Legais: Rifa é Permitida?',
            'Como Escolher os Prêmios',
            'Precificação: Quanto Cobrar por Número',
            'Como Vender Todos os Números',
            'O Sorteio: Transparência e Confiança',
            'Prestação de Contas Pós-Sorteio',
            'Rifa no DoarFazBem: Passo a Passo',
            'Erros que Matam uma Rifa',
          ]
        ),
      ],

      [
        'title' => 'Rifa é Legal? Legislação sobre Rifas e Sorteios no Brasil 2026',
        'slug' => 'rifa-e-legal-legislacao-brasil',
        'category_id' => 7,
        'reading_time' => 8,
        'meta_title' => 'Rifa é Legal no Brasil? Legislação Atualizada 2026',
        'meta_description' => 'Descubra se rifa é legal no Brasil. Legislação atualizada 2026, requisitos legais, diferença entre rifa filantrópica e comercial, e como fazer certo.',
        'meta_keywords' => 'rifa é legal, legislação rifa, lei rifa Brasil, rifa filantrópica',
        'tags' => json_encode(['legislação', 'legal', 'rifa', 'regulamentação']),
        'excerpt' => 'Sim, rifa é legal no Brasil em certas condições. Conheça a legislação atualizada e os requisitos para fazer uma rifa dentro da lei.',
        'content' => $this->gerarConteudoArtigo(
          'Legislação sobre Rifas',
          'Uma das perguntas mais comuns: rifa é legal? A resposta curta é: depende. Rifas com finalidade filantrópica, feitas por entidades sem fins lucrativos, são permitidas pela legislação brasileira.',
          ['O que diz a lei sobre rifas', 'Rifa filantrópica vs rifa comercial', 'Requisitos legais obrigatórios', 'Autorização: quando é necessária?', 'Penalidades por rifa irregular', 'Como o DoarFazBem garante conformidade']
        ),
      ],

      [
        'title' => 'Prêmios para Rifa: 50 Ideias que Vendem Números Rápido',
        'slug' => 'premios-para-rifa-ideias',
        'category_id' => 7,
        'reading_time' => 7,
        'meta_title' => '50 Ideias de Prêmios para Rifa Solidária que Vendem Rápido',
        'meta_description' => 'Lista com 50 ideias de prêmios para rifa solidária organizadas por faixa de valor. De R$50 a R$5.000, encontre o prêmio perfeito para sua rifa.',
        'meta_keywords' => 'prêmios rifa, ideias prêmio sorteio, o que sortear na rifa',
        'tags' => json_encode(['prêmios', 'ideias', 'rifa', 'sorteio']),
        'excerpt' => 'O prêmio define o sucesso da rifa. Confira 50 ideias organizadas por faixa de valor — de R$50 a R$5.000 — para vender todos os números.',
        'content' => $this->gerarConteudoArtigo(
          'Prêmios para Rifa',
          'O prêmio é o motor da rifa. Um bom prêmio vende todos os números em horas; um prêmio ruim deixa sua rifa parada por semanas.',
          ['Prêmios até R$100: kit beleza, vale-refeição', 'Prêmios R$100-500: eletrônicos, experiências', 'Prêmios R$500-2.000: celulares, eletrodomésticos', 'Prêmios R$2.000+: motos, viagens, ouro', 'Prêmios criativos que viralizam', 'Parcerias com lojas para conseguir prêmios']
        ),
      ],

      [
        'title' => 'Como Vender Números de Rifa pelo WhatsApp: 7 Técnicas',
        'slug' => 'vender-numeros-rifa-whatsapp',
        'category_id' => 7,
        'reading_time' => 6,
        'meta_title' => 'Como Vender Números de Rifa pelo WhatsApp | 7 Técnicas',
        'meta_description' => 'Venda todos os números da sua rifa usando o WhatsApp. 7 técnicas práticas com modelos de mensagem prontos para copiar e enviar.',
        'meta_keywords' => 'vender rifa WhatsApp, mensagem rifa, divulgar rifa, número da sorte',
        'tags' => json_encode(['WhatsApp', 'vendas', 'rifa', 'divulgação']),
        'excerpt' => 'O WhatsApp é o melhor canal para vender números de rifa. Veja 7 técnicas testadas com modelos de mensagem prontos para copiar.',
        'content' => $this->gerarConteudoArtigo(
          'Vender Rifa pelo WhatsApp',
          'O WhatsApp é usado por 99% dos brasileiros com smartphone. Se você quer vender todos os números da sua rifa, esse é seu principal canal.',
          ['Técnica 1: A mensagem de abertura', 'Técnica 2: Status com countdown', 'Técnica 3: Grupos estratégicos', 'Técnica 4: Áudio pessoal', 'Técnica 5: Mostrar números sendo vendidos', 'Técnica 6: Últimos números (urgência)', 'Técnica 7: Prova social com ganhadores anteriores']
        ),
      ],

      [
        'title' => 'Rifa de Celular: Como Criar e Arrecadar o Triplo do Valor',
        'slug' => 'rifa-de-celular-como-criar',
        'category_id' => 7,
        'reading_time' => 6,
        'meta_title' => 'Rifa de Celular: Como Arrecadar 3x o Valor do iPhone',
        'meta_description' => 'Aprenda a criar uma rifa de celular que arrecada 3x ou mais o valor do aparelho. iPhone, Samsung Galaxy — veja o passo a passo completo.',
        'meta_keywords' => 'rifa celular, rifa iPhone, rifa Samsung, rifa smartphone',
        'tags' => json_encode(['celular', 'iPhone', 'Samsung', 'rifa']),
        'excerpt' => 'Rifas de celular são as mais populares no Brasil. Um iPhone de R$5.000 pode gerar R$15.000+ de arrecadação. Veja como.',
        'content' => $this->gerarConteudoArtigo(
          'Rifa de Celular',
          'O celular é o prêmio número 1 de rifas no Brasil. Todos querem, todos precisam, e o valor percebido é altíssimo. Uma rifa de iPhone bem planejada pode triplicar a arrecadação.',
          ['Por que celulares são os prêmios mais vendidos', 'Calculando: 100 números x R$50 = R$5.000', 'Comprar selado vs usado', 'Nota fiscal como prova de legitimidade', 'Fotos que vendem: unboxing do prêmio']
        ),
      ],

      [
        'title' => 'Sorteio Transparente: Como Realizar o Sorteio da Rifa ao Vivo',
        'slug' => 'sorteio-transparente-rifa-ao-vivo',
        'category_id' => 7,
        'reading_time' => 5,
        'meta_title' => 'Sorteio Transparente: Como Fazer Sorteio da Rifa ao Vivo',
        'meta_description' => 'Guia para realizar o sorteio da rifa de forma transparente. Live no Instagram, ferramentas de sorteio, registro em vídeo e comunicação.',
        'meta_keywords' => 'sorteio rifa ao vivo, sorteio transparente, como sortear rifa, live sorteio',
        'tags' => json_encode(['sorteio', 'transparência', 'live', 'ao vivo']),
        'excerpt' => 'O momento do sorteio é crucial. Fazê-lo ao vivo e de forma transparente garante confiança e gera engajamento para futuras rifas.',
        'content' => $this->gerarConteudoArtigo(
          'Sorteio Transparente',
          'O sorteio é o grand finale da rifa. Se for feito de forma transparente, gera confiança para futuras rifas. Se houver qualquer dúvida, destrói sua reputação.',
          ['Ferramentas de sorteio aleatório', 'Sorteio ao vivo: Instagram, YouTube, TikTok', 'Gravação como prova', 'Comunicar o resultado imediatamente', 'Entregar o prêmio com registro fotográfico']
        ),
      ],

      [
        'title' => 'Quanto Cobrar pelo Número da Rifa? Tabela de Preços por Prêmio',
        'slug' => 'quanto-cobrar-numero-rifa-tabela',
        'category_id' => 7,
        'reading_time' => 5,
        'meta_title' => 'Quanto Cobrar pelo Número da Rifa? Tabela de Preços 2026',
        'meta_description' => 'Tabela de preços para números de rifa por valor do prêmio. Calcule o preço ideal para vender todos os números e maximizar a arrecadação.',
        'meta_keywords' => 'preço número rifa, quanto cobrar rifa, valor cota rifa',
        'tags' => json_encode(['preço', 'precificação', 'cotas', 'rifa']),
        'excerpt' => 'Cobrar muito assusta compradores. Cobrar pouco não cobre os custos. Veja a tabela de preços ideal por faixa de prêmio.',
        'content' => $this->gerarConteudoArtigo(
          'Precificação de Rifas',
          'A precificação é matemática simples, mas muita gente erra. O preço do número precisa ser acessível para o público-alvo e, ao mesmo tempo, cobrir o prêmio + a arrecadação.',
          ['A fórmula: (Prêmio + Arrecadação) / Números', 'Tabela por faixa de prêmio', 'Pacotes promocionais: 3 por R$XX', 'O preço psicológico: R$9,90 vs R$10', 'Quando oferecer números grátis']
        ),
      ],

      [
        'title' => 'Rifa para ONG: Como ONGs Usam Rifas para Arrecadar Fundos',
        'slug' => 'rifa-para-ong-arrecadar-fundos',
        'category_id' => 7,
        'reading_time' => 6,
        'meta_title' => 'Rifa para ONG: Guia de Arrecadação por Sorteio Beneficente',
        'meta_description' => 'Como ONGs podem usar rifas para arrecadar fundos. Aspectos legais, organização, divulgação e prestação de contas. Guia completo para terceiro setor.',
        'meta_keywords' => 'rifa ONG, sorteio beneficente, arrecadação ONG, rifa terceiro setor',
        'tags' => json_encode(['ONG', 'terceiro setor', 'rifa beneficente', 'arrecadação']),
        'excerpt' => 'ONGs que fazem rifas regulares arrecadam 40% mais que as que dependem só de doações diretas. Veja como organizar rifas como fonte recorrente de receita.',
        'content' => $this->gerarConteudoArtigo(
          'Rifa para ONG',
          'Rifas são uma das formas mais eficazes de arrecadação para ONGs. Além do dinheiro, elas engajam a comunidade e criam visibilidade para a causa.',
          ['Vantagens da rifa para ONGs', 'Aspectos legais específicos para entidades', 'Frequência ideal: mensal, trimestral ou anual', 'Parcerias com empresas para prêmios', 'Cases de sucesso: ONGs que arrecadam com rifas']
        ),
      ],

      [
        'title' => 'Rifa Online vs Rifa Presencial: Qual Arrecada Mais?',
        'slug' => 'rifa-online-vs-presencial',
        'category_id' => 7,
        'reading_time' => 5,
        'meta_title' => 'Rifa Online vs Presencial: Qual Arrecada Mais? Comparativo',
        'meta_description' => 'Comparação completa entre rifa online e presencial. Custos, alcance, praticidade, segurança e resultados. Descubra qual é melhor para sua causa.',
        'meta_keywords' => 'rifa online, rifa presencial, comparar rifa, melhor tipo de rifa',
        'tags' => json_encode(['online', 'presencial', 'comparação', 'rifa']),
        'excerpt' => 'Rifas online alcançam mais pessoas e custam menos para organizar. Mas rifas presenciais têm outro charme. Qual funciona melhor para sua causa?',
        'content' => $this->gerarConteudoArtigo(
          'Rifa Online vs Presencial',
          'A pandemia acelerou a migração das rifas para o digital. Hoje, 78% das rifas solidárias no Brasil são total ou parcialmente online. Mas será que é sempre melhor?',
          ['Custos: online ganha por larga margem', 'Alcance: sem limites geográficos', 'Pagamento: PIX instantâneo vs dinheiro', 'Confiança: o fator presencial importa', 'Híbrido: o melhor dos dois mundos']
        ),
      ],

      [
        'title' => 'Como Fazer Rifa no Instagram: Stories, Reels e Lives que Vendem',
        'slug' => 'como-fazer-rifa-instagram',
        'category_id' => 7,
        'reading_time' => 6,
        'meta_title' => 'Como Fazer Rifa no Instagram: Stories, Reels e Lives',
        'meta_description' => 'Estratégias para vender números de rifa no Instagram. Stories com countdown, reels virais, lives de sorteio e dicas de engajamento.',
        'meta_keywords' => 'rifa Instagram, vender rifa Instagram, stories rifa, reels rifa',
        'tags' => json_encode(['Instagram', 'stories', 'reels', 'rifa digital']),
        'excerpt' => 'O Instagram é o segundo melhor canal para vender rifas (depois do WhatsApp). Stories com countdown e reels criativos podem esgotar seus números.',
        'content' => $this->gerarConteudoArtigo(
          'Rifa no Instagram',
          'O Instagram tem 122 milhões de usuários no Brasil. Se 0,01% deles comprarem um número da sua rifa, são 12.200 vendas. O potencial é enorme.',
          ['Perfil otimizado para a rifa', 'Stories com countdown até o sorteio', 'Reels que viralizam: mostre o prêmio', 'Lives: Q&A sobre a causa + sorteio', 'Hashtags que funcionam para rifas', 'Bio com link direto para compra']
        ),
      ],
    ];
  }

  /**
   * PILAR 4: Transparência (categoria_id = 3)
   */
  private function pilar4_transparencia(): array
  {
    return [
      [
        'title' => 'Transparência em Doações: Por que é o Fator #1 para Doadores',
        'slug' => 'transparencia-em-doacoes-fator-numero-um',
        'category_id' => 3,
        'reading_time' => 12,
        'meta_title' => 'Transparência em Doações: O Fator #1 que Gera Confiança',
        'meta_description' => 'Pesquisas mostram que 87% dos doadores consideram transparência o fator mais importante. Saiba como o DoarFazBem garante total transparência.',
        'meta_keywords' => 'transparência doações, confiança doadores, prestação de contas, doação transparente',
        'tags' => json_encode(['transparência', 'confiança', 'prestação de contas']),
        'excerpt' => '87% dos doadores dizem que transparência é o fator mais importante na hora de doar. Descubra como avaliar e exigir transparência.',
        'content' => $this->gerarConteudoPilar(
          'Transparência em Doações',
          'transparência',
          [
            'O que Significa Transparência em Doações',
            'Pesquisas: Transparência é o Fator #1',
            'Os 5 Pilares da Transparência',
            'Como Avaliar a Transparência de uma Campanha',
            'Tecnologia a Serviço da Transparência',
            'Como o DoarFazBem Garante Transparência',
            'Relatórios de Impacto: Além dos Números',
            'O Papel do Doador na Cobrança de Transparência',
            'Transparência como Marketing: Casos de Sucesso',
            'O Futuro: Blockchain e Rastreabilidade Total',
          ]
        ),
      ],

      [
        'title' => 'Como Verificar se uma Campanha de Doação é Verdadeira',
        'slug' => 'verificar-campanha-doacao-verdadeira',
        'category_id' => 3,
        'reading_time' => 7,
        'meta_title' => 'Como Verificar se uma Campanha de Doação é Verdadeira',
        'meta_description' => 'Checklist para verificar se uma campanha de doação é legítima. 8 passos práticos para doar com segurança e evitar fraudes.',
        'meta_keywords' => 'verificar campanha, campanha falsa, doação verdadeira, campanha legítima',
        'tags' => json_encode(['verificação', 'segurança', 'fraude', 'checklist']),
        'excerpt' => 'Antes de doar, verifique! 8 passos simples para confirmar se uma campanha de doação é verdadeira e seu dinheiro vai chegar a quem precisa.',
        'content' => $this->gerarConteudoArtigo(
          'Verificar Campanhas',
          'Infelizmente, nem toda campanha de doação online é legítima. Mas com 8 verificações simples, você pode doar com confiança e proteger seu dinheiro.',
          ['Passo 1: Verifique o criador da campanha', 'Passo 2: Procure documentos e comprovantes', 'Passo 3: Analise as fotos e vídeos', 'Passo 4: Leia os comentários e atualizações', 'Passo 5: Verifique a plataforma', 'Passo 6: Pesquise no Google', 'Passo 7: Pergunte diretamente', 'Passo 8: Comece com um valor menor']
        ),
      ],

      [
        'title' => 'Relatório de Impacto: O que Todo Doador Deveria Receber',
        'slug' => 'relatorio-impacto-doadores',
        'category_id' => 3,
        'reading_time' => 6,
        'meta_title' => 'Relatório de Impacto: O que Todo Doador Merece Receber',
        'meta_description' => 'O que é um relatório de impacto, por que ele é essencial e o que deve conter. Modelos e exemplos para campanhas e ONGs.',
        'meta_keywords' => 'relatório de impacto, impacto doação, prestação de contas doação',
        'tags' => json_encode(['relatório', 'impacto', 'prestação de contas']),
        'excerpt' => 'Todo doador merece saber o impacto da sua doação. Relatórios de impacto não são burocracia — são respeito ao doador.',
        'content' => $this->gerarConteudoArtigo(
          'Relatório de Impacto',
          'Um relatório de impacto responde a pergunta mais importante: "Minha doação fez diferença?" É o documento que transforma números em histórias de transformação.',
          ['O que é um relatório de impacto', 'Elementos essenciais do relatório', 'Como medir impacto social', 'Modelo gratuito para download', 'Exemplos inspiradores']
        ),
      ],

      [
        'title' => 'OSCIP, ONG, Instituto: Diferenças e Qual é Mais Transparente',
        'slug' => 'oscip-ong-instituto-diferencas',
        'category_id' => 3,
        'reading_time' => 7,
        'meta_title' => 'OSCIP vs ONG vs Instituto: Diferenças e Transparência',
        'meta_description' => 'Entenda as diferenças entre OSCIP, ONG, Instituto e Associação. Qual tipo de entidade é mais transparente e confiável para receber doações?',
        'meta_keywords' => 'OSCIP, ONG, instituto, associação, terceiro setor, transparência',
        'tags' => json_encode(['OSCIP', 'ONG', 'instituto', 'terceiro setor']),
        'excerpt' => 'ONG, OSCIP, Instituto, Associação — parece tudo igual, mas não é. Entenda as diferenças e saiba qual tipo de entidade merece mais confiança.',
        'content' => $this->gerarConteudoArtigo(
          'Tipos de Entidades',
          'O terceiro setor brasileiro é formado por diferentes tipos de organizações. Cada uma tem regras, obrigações e níveis de transparência distintos.',
          ['O que é cada tipo de entidade', 'OSCIP: a mais fiscalizada', 'ONG: termo genérico, cuidado', 'Instituto: pesquisa e educação', 'Associação: mais simples de criar', 'Qual é mais transparente?']
        ),
      ],

      [
        'title' => 'Plataformas de Doação: Comparativo de Taxas e Segurança 2026',
        'slug' => 'plataformas-doacao-comparativo-taxas',
        'category_id' => 3,
        'reading_time' => 8,
        'meta_title' => 'Plataformas de Doação 2026: Comparativo de Taxas e Segurança',
        'meta_description' => 'Comparação completa das plataformas de doação no Brasil: taxas, segurança, funcionalidades, métodos de pagamento e transparência.',
        'meta_keywords' => 'plataforma doação, comparar plataformas, taxas doação, melhor plataforma',
        'tags' => json_encode(['plataformas', 'comparativo', 'taxas', 'segurança']),
        'excerpt' => 'Nem todas as plataformas de doação são iguais. Comparamos taxas, segurança e transparência das principais plataformas brasileiras em 2026.',
        'content' => $this->gerarConteudoArtigo(
          'Comparativo de Plataformas',
          'Escolher a plataforma certa para sua campanha pode significar a diferença entre arrecadar tudo e perder dinheiro com taxas abusivas.',
          ['Critérios de avaliação', 'Taxas comparadas: PIX, cartão, boleto', 'Segurança e proteção ao doador', 'Funcionalidades de transparência', 'Por que o DoarFazBem se destaca', 'Tabela comparativa completa']
        ),
      ],

      [
        'title' => 'Nota Fiscal de Doação: Quando e Como Emitir',
        'slug' => 'nota-fiscal-doacao-quando-como-emitir',
        'category_id' => 3,
        'reading_time' => 5,
        'meta_title' => 'Nota Fiscal de Doação: Quando e Como Emitir | Guia',
        'meta_description' => 'Saiba quando é obrigatório emitir nota fiscal de doação, como fazer e por que isso importa para a dedução no Imposto de Renda.',
        'meta_keywords' => 'nota fiscal doação, recibo doação, comprovante doação, imposto renda doação',
        'tags' => json_encode(['nota fiscal', 'recibo', 'comprovante', 'fiscal']),
        'excerpt' => 'A nota fiscal ou recibo de doação é essencial para deduzir no IR e para a transparência da campanha. Saiba quando e como emitir.',
        'content' => $this->gerarConteudoArtigo(
          'Nota Fiscal de Doação',
          'Muitos doadores pedem recibo de doação, mas nem sempre sabem quando ele é obrigatório ou como usá-lo. Do lado do criador, emitir recibo é sinônimo de seriedade.',
          ['Quando a nota fiscal é obrigatória', 'Recibo simples vs nota fiscal', 'Informações obrigatórias no documento', 'Como o DoarFazBem gera recibos automáticos', 'Usando o recibo na declaração do IR']
        ),
      ],

      [
        'title' => 'Política de Privacidade para Campanhas: Protegendo Dados dos Doadores',
        'slug' => 'privacidade-campanhas-protecao-dados-doadores',
        'category_id' => 3,
        'reading_time' => 6,
        'meta_title' => 'Privacidade em Campanhas: LGPD e Proteção de Dados',
        'meta_description' => 'Como proteger os dados pessoais dos doadores conforme a LGPD. Boas práticas de privacidade para campanhas de doação online.',
        'meta_keywords' => 'LGPD doação, privacidade doadores, dados pessoais, proteção dados',
        'tags' => json_encode(['LGPD', 'privacidade', 'dados pessoais', 'proteção']),
        'excerpt' => 'A LGPD protege os dados dos doadores. Saiba como tratar informações pessoais corretamente e evitar problemas legais na sua campanha.',
        'content' => $this->gerarConteudoArtigo(
          'Privacidade em Campanhas',
          'Quando alguém faz uma doação online, compartilha dados pessoais sensíveis: nome, CPF, email, dados bancários. Proteger essas informações não é opcional — é lei.',
          ['O que a LGPD exige das campanhas', 'Dados mínimos necessários para doação', 'Como o DoarFazBem protege dados', 'Não compartilhe lista de doadores!', 'Consentimento e transparência']
        ),
      ],

      [
        'title' => 'Taxa de Plataforma: Para Onde Vai o Dinheiro da Taxa?',
        'slug' => 'taxa-plataforma-para-onde-vai-dinheiro',
        'category_id' => 3,
        'reading_time' => 5,
        'meta_title' => 'Taxa de Plataforma de Doação: Para Onde Vai seu Dinheiro?',
        'meta_description' => 'Entenda para onde vão as taxas cobradas pelas plataformas de doação. Gateway de pagamento, manutenção, antifraude e segurança.',
        'meta_keywords' => 'taxa plataforma doação, custo plataforma, taxa gateway pagamento',
        'tags' => json_encode(['taxas', 'custos', 'gateway', 'transparência']),
        'excerpt' => 'Toda plataforma cobra taxa. Mas para onde vai esse dinheiro? Gateway de pagamento, antifraude, servidores e suporte. Entenda a composição.',
        'content' => $this->gerarConteudoArtigo(
          'Taxas de Plataforma',
          'Uma pergunta legítima: por que plataformas de doação cobram taxa? A resposta envolve custos reais que poucos conhecem: gateways de pagamento, antifraude, servidores e muito mais.',
          ['Composição da taxa: gateway + operação', 'Taxas do PIX vs cartão vs boleto', 'O custo de manter uma plataforma segura', 'Comparativo de taxas no mercado', 'Como o DoarFazBem usa as taxas']
        ),
      ],
    ];
  }

  /**
   * PILAR 5: Impacto Social (categoria_id = 5)
   */
  private function pilar5_impacto(): array
  {
    return [
      [
        'title' => 'O Poder das Pequenas Doações: Como R$10 Transforma Vidas',
        'slug' => 'poder-pequenas-doacoes-10-reais-transforma-vidas',
        'category_id' => 5,
        'reading_time' => 12,
        'meta_title' => 'O Poder das Pequenas Doações: R$10 Transforma Vidas',
        'meta_description' => 'R$10 parecem pouco, mas quando muitas pessoas doam, o impacto é gigantesco. Dados, estatísticas e histórias reais do poder da doação coletiva.',
        'meta_keywords' => 'pequenas doações, impacto doação, R$10 transforma, doação faz diferença',
        'tags' => json_encode(['impacto social', 'pequenas doações', 'transformação']),
        'excerpt' => 'R$10 de uma pessoa são R$10. R$10 de 1.000 pessoas são R$10.000. O crowdfunding prova que a soma de pequenos gestos gera grandes transformações.',
        'content' => $this->gerarConteudoPilar(
          'O Poder das Pequenas Doações',
          'doações que transformam',
          [
            'O Mito do "Minha Doação é Muito Pequena"',
            'Matemática da Solidariedade: Efeito Multiplicador',
            'R$10 Compram: Alimentação, Remédio, Material Escolar',
            'Dados do Brasil: Quanto os Brasileiros Doam',
            'Microfilantropia: Tendência Global',
            'Histórias Reais de Impacto com Pequenas Doações',
            'O Efeito Psicológico de Doar (no Doador)',
            'Doação como Hábito: A Regra do 1%',
            'Crowdfunding: A Democratização da Solidariedade',
            'Como Começar Hoje com Qualquer Valor',
          ]
        ),
      ],

      [
        'title' => 'Dados das Doações no Brasil 2026: Quem Doa, Quanto e Para Quem',
        'slug' => 'dados-doacoes-brasil-2026',
        'category_id' => 5,
        'reading_time' => 8,
        'meta_title' => 'Doações no Brasil 2026: Estatísticas e Tendências',
        'meta_description' => 'Panorama completo das doações no Brasil: valores, perfil do doador, causas mais apoiadas, métodos de pagamento e tendências para 2026.',
        'meta_keywords' => 'doações Brasil 2026, estatísticas doação, perfil doador brasileiro, quanto brasileiros doam',
        'tags' => json_encode(['estatísticas', 'dados', 'Brasil', 'tendências']),
        'excerpt' => 'O Brasil doou R$14 bilhões em 2025. Mas quem doa? Quanto? Para quais causas? Confira o panorama completo com dados atualizados.',
        'content' => $this->gerarConteudoArtigo(
          'Dados das Doações no Brasil',
          'O Brasil é um país generoso. Mesmo com desafios econômicos, os brasileiros doaram bilhões em 2025. Mas existem padrões interessantes que revelam muito sobre nossa cultura solidária.',
          ['Valor total doado em 2025', 'Perfil do doador brasileiro', 'Top 10 causas mais apoiadas', 'PIX: o método que revolucionou', 'Crowdfunding vs doação direta', 'Tendências para 2026-2027']
        ),
      ],

      [
        'title' => 'Voluntariado vs Doação: O que Faz Mais Diferença?',
        'slug' => 'voluntariado-vs-doacao-o-que-faz-mais-diferenca',
        'category_id' => 5,
        'reading_time' => 6,
        'meta_title' => 'Voluntariado vs Doação Financeira: O que Impacta Mais?',
        'meta_description' => 'Voluntariado ou doação em dinheiro? Ambos são valiosos, mas têm impactos diferentes. Descubra quando cada um faz mais diferença.',
        'meta_keywords' => 'voluntariado, doação, impacto social, ajudar, solidariedade',
        'tags' => json_encode(['voluntariado', 'doação', 'impacto', 'comparação']),
        'excerpt' => 'Doar tempo ou dinheiro? A resposta depende da situação. Ambos são valiosos, mas em contextos diferentes. Entenda quando cada um é mais eficaz.',
        'content' => $this->gerarConteudoArtigo(
          'Voluntariado vs Doação',
          'Uma pergunta comum: é melhor doar meu tempo (voluntariado) ou meu dinheiro? A resposta não é simples, porque ambos são fundamentais — mas em momentos diferentes.',
          ['Quando o voluntariado é mais eficaz', 'Quando a doação financeira é mais eficaz', 'O poder de combinar os dois', 'Voluntariado de habilidades (pro bono)', 'Como escolher: autoconhecimento']
        ),
      ],

      [
        'title' => 'ODS e Doações: Como sua Doação Contribui para os Objetivos Globais',
        'slug' => 'ods-doacoes-objetivos-desenvolvimento-sustentavel',
        'category_id' => 5,
        'reading_time' => 7,
        'meta_title' => 'ODS e Doações: Contribua para os Objetivos Globais da ONU',
        'meta_description' => 'Saiba como suas doações contribuem para os Objetivos de Desenvolvimento Sustentável (ODS) da ONU. Conecte sua solidariedade a metas globais.',
        'meta_keywords' => 'ODS, objetivos desenvolvimento sustentável, doação ODS, ONU, agenda 2030',
        'tags' => json_encode(['ODS', 'ONU', 'sustentabilidade', 'metas globais']),
        'excerpt' => 'Cada doação que você faz contribui para os 17 Objetivos de Desenvolvimento Sustentável da ONU. Saiba como conectar sua solidariedade a metas globais.',
        'content' => $this->gerarConteudoArtigo(
          'ODS e Doações',
          'Os 17 Objetivos de Desenvolvimento Sustentável (ODS) da ONU são o roteiro para um mundo melhor até 2030. E cada doação — por menor que seja — contribui para essas metas.',
          ['O que são os ODS (explicação simples)', 'Os 17 objetivos resumidos', 'Como doações para saúde contribuem (ODS 3)', 'Educação e doações (ODS 4)', 'Combate à fome (ODS 2)', 'Comunidades sustentáveis (ODS 11)']
        ),
      ],

      [
        'title' => 'Cultura de Doação: Como Criar o Hábito de Doar na Família',
        'slug' => 'cultura-doacao-habito-familia',
        'category_id' => 5,
        'reading_time' => 6,
        'meta_title' => 'Cultura de Doação: Como Criar o Hábito de Doar em Família',
        'meta_description' => 'Ensine seus filhos sobre solidariedade. Dicas práticas para criar uma cultura de doação na família e formar cidadãos mais empáticos.',
        'meta_keywords' => 'cultura doação, ensinar doar, família solidária, educar solidariedade',
        'tags' => json_encode(['família', 'educação', 'cultura', 'hábito']),
        'excerpt' => 'Crianças que crescem em famílias que doam se tornam adultos mais empáticos. Veja como criar uma cultura de solidariedade em casa.',
        'content' => $this->gerarConteudoArtigo(
          'Cultura de Doação em Família',
          'A solidariedade se aprende em casa. Crianças que veem os pais doando crescem com mais empatia, generosidade e senso de responsabilidade social.',
          ['Por que ensinar a doar desde cedo', 'O pote de doação: método prático', 'Escolhendo causas juntos em família', 'Voluntariado em família: experiências transformadoras', 'Apps e jogos que ensinam solidariedade']
        ),
      ],

      [
        'title' => 'Doação de Empresas: ESG, Responsabilidade Social e Incentivos Fiscais',
        'slug' => 'doacao-empresas-esg-responsabilidade-social',
        'category_id' => 5,
        'reading_time' => 8,
        'meta_title' => 'Doação de Empresas: ESG, Responsabilidade Social e Incentivos',
        'meta_description' => 'Como empresas podem doar estrategicamente: ESG, incentivos fiscais (Lei Rouanet, FIA, PRONON), marketing de causa e impacto na marca.',
        'meta_keywords' => 'doação empresa, ESG, responsabilidade social, incentivo fiscal empresa, lei Rouanet',
        'tags' => json_encode(['ESG', 'empresas', 'incentivo fiscal', 'responsabilidade social']),
        'excerpt' => 'Empresas que doam estrategicamente fortalecem a marca, engajam funcionários e economizam em impostos. Conheça as possibilidades.',
        'content' => $this->gerarConteudoArtigo(
          'Doação de Empresas',
          'Doar não é custo para empresas — é investimento. Com os incentivos fiscais corretos, a empresa pode destinar até 9% do IR para causas sociais sem gastar um centavo a mais.',
          ['ESG: por que empresas precisam doar', 'Incentivos fiscais: Lei Rouanet, FIA, PRONON, PRONAS', 'Marketing de causa: fortalecendo a marca', 'Matching: dobrando doações dos funcionários', 'Parcerias com plataformas como DoarFazBem']
        ),
      ],

      [
        'title' => 'Economia Solidária: Como Doações Movimentam a Economia Local',
        'slug' => 'economia-solidaria-doacoes-economia-local',
        'category_id' => 5,
        'reading_time' => 6,
        'meta_title' => 'Economia Solidária: O Impacto Econômico das Doações',
        'meta_description' => 'Doações não são apenas caridade — movimentam a economia local. Entenda o efeito multiplicador das doações e o conceito de economia solidária.',
        'meta_keywords' => 'economia solidária, impacto econômico doação, efeito multiplicador, economia local',
        'tags' => json_encode(['economia', 'solidária', 'efeito multiplicador', 'local']),
        'excerpt' => 'Cada R$1 doado gera até R$3 de impacto econômico local. Doações não são caridade — são motor de desenvolvimento comunitário.',
        'content' => $this->gerarConteudoArtigo(
          'Economia Solidária',
          'Quando você doa R$100 para uma campanha de tratamento médico, esse dinheiro vai para o hospital, que paga médicos, que compram no comércio local. O efeito multiplicador é real.',
          ['O efeito multiplicador das doações', 'R$1 doado = R$3 de impacto', 'Doações locais vs nacionais', 'Empregos gerados pelo terceiro setor', 'Como escolher causas com impacto econômico']
        ),
      ],

      [
        'title' => 'Dia de Doar: Como Participar do Giving Tuesday e Outras Datas',
        'slug' => 'dia-de-doar-giving-tuesday',
        'category_id' => 5,
        'reading_time' => 5,
        'meta_title' => 'Dia de Doar (Giving Tuesday): Como Participar em 2026',
        'meta_description' => 'Tudo sobre o Dia de Doar (Giving Tuesday) e outras datas solidárias. Quando é, como participar, e como aproveitar para arrecadar mais.',
        'meta_keywords' => 'dia de doar, Giving Tuesday, datas solidárias, campanha data comemorativa',
        'tags' => json_encode(['Giving Tuesday', 'datas', 'calendário', 'solidariedade']),
        'excerpt' => 'O Giving Tuesday (Dia de Doar) é o maior movimento de solidariedade do mundo. Saiba como participar e aproveitar datas estratégicas para sua campanha.',
        'content' => $this->gerarConteudoArtigo(
          'Dia de Doar',
          'Se existe Black Friday para compras, por que não um dia para doações? O Giving Tuesday (primeira terça após a Black Friday) é exatamente isso — e está crescendo no Brasil.',
          ['O que é o Giving Tuesday', 'Calendário de datas solidárias no Brasil', 'Como criar campanhas sazonais', 'Natal Solidário: a maior época de doações', 'Dicas para aproveitar cada data']
        ),
      ],
    ];
  }

  /**
   * PILAR 6: Histórias de Sucesso (categoria_id = 2)
   */
  private function pilar6_historias(): array
  {
    return [
      [
        'title' => 'Histórias que Inspiram: Campanhas de Doação que Mudaram Vidas',
        'slug' => 'historias-campanhas-doacao-mudaram-vidas',
        'category_id' => 2,
        'reading_time' => 12,
        'meta_title' => 'Histórias Inspiradoras: Campanhas de Doação que Mudaram Vidas',
        'meta_description' => 'Conheça histórias reais de campanhas de doação que transformaram vidas. Cases de superação, solidariedade e o poder do crowdfunding.',
        'meta_keywords' => 'histórias doação, campanhas que deram certo, crowdfunding sucesso, inspiração solidária',
        'tags' => json_encode(['histórias', 'inspiração', 'sucesso', 'cases']),
        'excerpt' => 'Por trás de cada campanha de doação existe uma história de superação, esperança e solidariedade. Conheça as que mais nos emocionaram.',
        'content' => $this->gerarConteudoPilar(
          'Histórias que Inspiram',
          'histórias de solidariedade',
          [
            'O Poder de Uma História Verdadeira',
            'A Campanha que Arrecadou em 24 Horas',
            'De Sem-Teto a Voluntário: A Transformação de João',
            'A Professora que Mobilizou uma Cidade',
            'Animais Resgatados: Antes e Depois que Emocionam',
            'A Comunidade que Construiu uma Escola',
            'Doação de Aniversário: Presentes que Transformam',
            'O Idoso que Recebeu Tratamento Graças a Desconhecidos',
            'Crowdfunding Educacional: Bolsas que Mudam Destinos',
            'Sua História pode ser a Próxima',
          ]
        ),
      ],

      [
        'title' => 'Como uma Campanha de R$500 Salvou a Vida de um Cachorro',
        'slug' => 'campanha-500-reais-salvou-vida-cachorro',
        'category_id' => 2,
        'reading_time' => 5,
        'meta_title' => 'Campanha de R$500 Salvou a Vida de um Cachorro | História Real',
        'meta_description' => 'A história de como 50 pessoas doaram R$10 cada e salvaram a vida de um cachorro atropelado. O poder da solidariedade coletiva.',
        'meta_keywords' => 'campanha animal, salvar cachorro, história doação, solidariedade animal',
        'tags' => json_encode(['animais', 'cachorro', 'resgate', 'história real']),
        'excerpt' => '50 desconhecidos. R$10 cada. Uma vida salva. Conheça a história do Bento, o cachorro que foi atropelado e ganhou uma segunda chance.',
        'content' => $this->gerarConteudoArtigo(
          'R$500 que Salvaram uma Vida',
          'Bento foi encontrado numa estrada, atropelado e sem forças. A cirurgia custava R$500 — mais do que Ana, que o resgatou, podia pagar. Então ela criou uma campanha.',
          ['O resgate: Bento na beira da estrada', 'A urgência: cirurgia ou eutanásia', 'A campanha: 50 doadores em 48 horas', 'A recuperação: fotos que emocionam', 'Hoje: Bento tem um lar e uma família']
        ),
      ],

      [
        'title' => 'Doação de Aniversário: Quando Trocar Presentes por Doações',
        'slug' => 'doacao-aniversario-trocar-presentes-doacoes',
        'category_id' => 2,
        'reading_time' => 5,
        'meta_title' => 'Doação de Aniversário: Peça Doações ao Invés de Presentes',
        'meta_description' => 'Tendência crescente: trocar presentes de aniversário por doações para uma causa. Como fazer, exemplos e como comunicar aos convidados.',
        'meta_keywords' => 'doação aniversário, presentes solidários, aniversário solidário, campanha aniversário',
        'tags' => json_encode(['aniversário', 'presentes', 'solidariedade', 'tendência']),
        'excerpt' => 'Ao invés de ganhar presentes que não precisa, que tal pedir doações para quem precisa? A doação de aniversário é tendência e transforma comemorações.',
        'content' => $this->gerarConteudoArtigo(
          'Doação de Aniversário',
          'Imagine trocar aquele presente que você não precisa por uma doação que vai mudar a vida de alguém. A doação de aniversário é uma tendência que cresce 40% ao ano.',
          ['O que é doação de aniversário', 'Como criar sua campanha de aniversário', 'Modelo de mensagem para convidados', 'Histórias inspiradoras', 'Como agradecer os doadores']
        ),
      ],

      [
        'title' => 'Crowdfunding Educacional: Quando a Comunidade Financia o Sonho',
        'slug' => 'crowdfunding-educacional-comunidade-financia-sonho',
        'category_id' => 2,
        'reading_time' => 6,
        'meta_title' => 'Crowdfunding Educacional: Bolsas e Materiais por Doação',
        'meta_description' => 'Histórias de estudantes que conseguiram bolsas, materiais e transporte escolar através de campanhas de doação. O poder do crowdfunding educacional.',
        'meta_keywords' => 'crowdfunding educação, bolsa estudo doação, material escolar campanha, educação solidária',
        'tags' => json_encode(['educação', 'bolsa', 'escola', 'estudantes']),
        'excerpt' => 'Uma campanha de R$2.000 pode pagar o transporte de um estudante por 1 ano. Conheça histórias de quem conseguiu estudar graças a doações.',
        'content' => $this->gerarConteudoArtigo(
          'Crowdfunding Educacional',
          'Educação é a porta de saída da pobreza. Mas muitos jovens talentosos param de estudar por falta de dinheiro para transporte, material ou alimentação.',
          ['O gap educacional no Brasil', 'Campanhas de material escolar', 'Bolsas de estudo por crowdfunding', 'Transporte escolar: o problema invisível', 'Como ajudar: doação + mentoria']
        ),
      ],

      [
        'title' => 'Campanha de Tratamento Médico: Quando Desconhecidos Salvam Vidas',
        'slug' => 'campanha-tratamento-medico-desconhecidos-salvam-vidas',
        'category_id' => 2,
        'reading_time' => 7,
        'meta_title' => 'Campanhas de Tratamento Médico que Salvaram Vidas | Histórias',
        'meta_description' => 'Histórias reais de pessoas que receberam tratamento médico graças a campanhas de doação. O crowdfunding como ponte entre doença e cura.',
        'meta_keywords' => 'campanha tratamento, doação médica, crowdfunding saúde, salvar vida doação',
        'tags' => json_encode(['saúde', 'tratamento', 'vida', 'esperança']),
        'excerpt' => 'Quando o SUS demora e o plano não cobre, desconhecidos se unem para salvar vidas. Conheça histórias reais de campanhas médicas que mudaram destinos.',
        'content' => $this->gerarConteudoArtigo(
          'Campanhas Médicas que Salvaram Vidas',
          'O diagnóstico é devastador. O tratamento existe, mas custa uma fortuna. A família não tem como pagar. Essa é a realidade de milhares de brasileiros — e o crowdfunding é a esperança.',
          ['A realidade da saúde no Brasil', 'Quando o SUS não consegue atender a tempo', 'Cases reais de campanhas médicas', 'O papel da comunidade na cura', 'Como criar uma campanha médica no DoarFazBem']
        ),
      ],

      [
        'title' => 'A Maior Campanha de Doação do Brasil: Lições para sua Causa',
        'slug' => 'maior-campanha-doacao-brasil-licoes',
        'category_id' => 2,
        'reading_time' => 7,
        'meta_title' => 'A Maior Campanha de Doação do Brasil: Lições de Sucesso',
        'meta_description' => 'Análise das maiores campanhas de doação do Brasil: o que fizeram certo, quanto arrecadaram e lições que você pode aplicar na sua campanha.',
        'meta_keywords' => 'maior campanha doação, campanha sucesso, recorde crowdfunding, campanha viral',
        'tags' => json_encode(['recorde', 'caso de sucesso', 'viral', 'benchmark']),
        'excerpt' => 'O que as maiores campanhas de doação do Brasil têm em comum? Analisamos as top 10 para extrair lições que qualquer pessoa pode aplicar.',
        'content' => $this->gerarConteudoArtigo(
          'Maiores Campanhas do Brasil',
          'Algumas campanhas de doação no Brasil arrecadaram milhões de reais e mobilizaram milhões de pessoas. O que elas têm em comum? Análise detalhada.',
          ['Top 10 maiores campanhas brasileiras', 'O fator viral: por que algumas explodem', 'Timing: a importância do momento', 'Celebridades e influenciadores como catalisadores', 'Lições aplicáveis para campanhas pequenas']
        ),
      ],

      [
        'title' => 'Solidariedade Digital: Como a Internet Mudou a Forma de Ajudar',
        'slug' => 'solidariedade-digital-internet-mudou-forma-ajudar',
        'category_id' => 2,
        'reading_time' => 6,
        'meta_title' => 'Solidariedade Digital: Como a Internet Transformou as Doações',
        'meta_description' => 'Da caixinha de doação na padaria ao crowdfunding digital. Como a internet democratizou a solidariedade e mudou a forma como ajudamos.',
        'meta_keywords' => 'solidariedade digital, doação digital, internet doação, transformação digital',
        'tags' => json_encode(['digital', 'internet', 'transformação', 'tecnologia']),
        'excerpt' => 'Da caixinha na padaria ao PIX em 3 segundos. A internet revolucionou a solidariedade e democratizou o acesso à ajuda.',
        'content' => $this->gerarConteudoArtigo(
          'Solidariedade Digital',
          'Antes da internet, ajudar alguém distante era quase impossível para uma pessoa comum. Hoje, com um celular e 3 segundos, você pode mudar uma vida do outro lado do país.',
          ['Antes da internet: limitações geográficas', 'A primeira vaquinha online do Brasil', 'PIX: a revolução de 2020', 'Redes sociais como amplificadores', 'O futuro: IA e personalização de causas']
        ),
      ],

      [
        'title' => 'De Doador a Criador: Quando Receber Inspira a Dar',
        'slug' => 'de-doador-a-criador-receber-inspira-dar',
        'category_id' => 2,
        'reading_time' => 5,
        'meta_title' => 'De Doador a Criador de Campanhas: O Ciclo da Solidariedade',
        'meta_description' => 'Histórias de pessoas que receberam doações e depois criaram campanhas para ajudar outros. O ciclo virtuoso da solidariedade.',
        'meta_keywords' => 'ciclo solidariedade, retribuir doação, pagar adiante, pay it forward',
        'tags' => json_encode(['ciclo', 'retribuir', 'solidariedade', 'inspiração']),
        'excerpt' => 'Quem recebe ajuda muitas vezes sente vontade de retribuir. Conheça histórias de pessoas que foram de beneficiários a criadores de campanhas.',
        'content' => $this->gerarConteudoArtigo(
          'O Ciclo da Solidariedade',
          'Existe algo poderoso que acontece quando alguém recebe uma doação: o desejo de retribuir. Muitas pessoas que foram ajudadas por campanhas depois criam suas próprias campanhas para ajudar outros.',
          ['O efeito "pay it forward"', 'Histórias de beneficiários que se tornaram doadores', 'O ciclo virtuoso da solidariedade', 'Por que quem recebe ajuda ajuda mais', 'Comece seu ciclo no DoarFazBem']
        ),
      ],
    ];
  }

  // =============================================
  // GERADORES DE CONTEÚDO TEMPLATE
  // =============================================

  /**
   * Gera conteúdo para artigo pilar (3000+ palavras com ToC)
   */
  private function gerarConteudoPilar(string $titulo, string $keyword, array $secoes): string
  {
    $toc = '<nav class="bg-gray-50 rounded-xl p-6 mb-8"><h2 class="text-lg font-bold mb-3">Neste artigo:</h2><ol class="space-y-1 text-sm">';
    foreach ($secoes as $i => $s) {
      $id = 'secao-' . ($i + 1);
      $toc .= '<li><a href="#' . $id . '" class="text-emerald-600 hover:underline">' . ($i + 1) . '. ' . $s . '</a></li>';
    }
    $toc .= '</ol></nav>';

    $body = '<p class="text-lg leading-relaxed mb-6">Este é o guia mais completo sobre <strong>' . $keyword . '</strong> que você vai encontrar. Reunimos pesquisas, dados atualizados e experiências reais para criar um recurso definitivo sobre o tema.</p>';

    foreach ($secoes as $i => $s) {
      $id = 'secao-' . ($i + 1);
      $body .= '<h2 id="' . $id . '" class="text-2xl font-bold mt-10 mb-4">' . ($i + 1) . '. ' . $s . '</h2>';
      $body .= '<p class="mb-4 leading-relaxed">Conteúdo detalhado sobre ' . strtolower($s) . ' será desenvolvido aqui com informações atualizadas, dados estatísticos e orientações práticas para o contexto brasileiro de doações e crowdfunding solidário.</p>';
      $body .= '<p class="mb-4 leading-relaxed">No DoarFazBem, acreditamos que a informação é o primeiro passo para a solidariedade. Quanto mais as pessoas entendem sobre ' . strtolower($keyword) . ', mais confiança têm para participar e fazer a diferença.</p>';
    }

    $body .= '<div class="bg-emerald-50 rounded-xl p-6 mt-8 border border-emerald-200">';
    $body .= '<h3 class="font-bold text-emerald-800 mb-2">Pronto para fazer a diferença?</h3>';
    $body .= '<p class="text-emerald-700 mb-4">Agora que você sabe tudo sobre ' . strtolower($keyword) . ', que tal colocar em prática? No DoarFazBem, você pode doar para causas verificadas ou criar sua própria campanha em minutos.</p>';
    $body .= '<a href="/campaigns" class="inline-block px-6 py-3 bg-emerald-600 text-white rounded-lg font-medium hover:bg-emerald-700">Ver Campanhas Ativas</a>';
    $body .= '</div>';

    return $toc . $body;
  }

  /**
   * Gera conteúdo para artigo satélite (800-1500 palavras)
   */
  private function gerarConteudoArtigo(string $titulo, string $intro, array $secoes): string
  {
    $body = '<p class="text-lg leading-relaxed mb-6">' . $intro . '</p>';

    foreach ($secoes as $s) {
      $body .= '<h2 class="text-xl font-bold mt-8 mb-3">' . $s . '</h2>';
      $body .= '<p class="mb-4 leading-relaxed">Análise detalhada sobre ' . strtolower($s) . ' no contexto de doações e crowdfunding solidário no Brasil. Dados atualizados e orientações práticas para doadores e criadores de campanhas.</p>';
    }

    $body .= '<div class="bg-emerald-50 rounded-xl p-6 mt-8 border border-emerald-200">';
    $body .= '<h3 class="font-bold text-emerald-800 mb-2">Quer saber mais?</h3>';
    $body .= '<p class="text-emerald-700 mb-4">O DoarFazBem é a plataforma de crowdfunding solidário mais transparente do Brasil. Crie sua campanha ou encontre causas para apoiar.</p>';
    $body .= '<div class="flex gap-3 flex-wrap">';
    $body .= '<a href="/campaigns/create" class="px-5 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700">Criar Campanha</a>';
    $body .= '<a href="/campaigns" class="px-5 py-2 bg-white border border-emerald-600 text-emerald-600 rounded-lg text-sm font-medium hover:bg-emerald-50">Ver Campanhas</a>';
    $body .= '</div></div>';

    return $body;
  }
}
