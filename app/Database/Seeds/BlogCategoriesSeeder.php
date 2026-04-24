<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seeder das categorias padrão do blog DoarFazBem
 */
class BlogCategoriesSeeder extends Seeder
{
  public function run()
  {
    $categories = [
      [
        'name' => 'Doações e Solidariedade',
        'slug' => 'doacoes-e-solidariedade',
        'description' => 'Artigos sobre o poder das doações e como a solidariedade transforma vidas',
        'icon' => 'fas fa-hand-holding-heart',
        'meta_description' => 'Artigos sobre doações, solidariedade e como ajudar o próximo. Dicas e histórias inspiradoras.',
        'sort_order' => 1,
        'is_active' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
      ],
      [
        'name' => 'Histórias de Sucesso',
        'slug' => 'historias-de-sucesso',
        'description' => 'Campanhas que atingiram sua meta e mudaram vidas',
        'icon' => 'fas fa-trophy',
        'meta_description' => 'Conheça histórias reais de campanhas que alcançaram suas metas e transformaram vidas.',
        'sort_order' => 2,
        'is_active' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
      ],
      [
        'name' => 'Transparência',
        'slug' => 'transparencia',
        'description' => 'Como garantimos que cada centavo chegue ao destino certo',
        'icon' => 'fas fa-shield-alt',
        'meta_description' => 'Saiba como o DoarFazBem garante transparência total em todas as campanhas e doações.',
        'sort_order' => 3,
        'is_active' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
      ],
      [
        'name' => 'Dicas para Campanhas',
        'slug' => 'dicas-para-campanhas',
        'description' => 'Aprenda a criar campanhas de sucesso e alcançar sua meta',
        'icon' => 'fas fa-lightbulb',
        'meta_description' => 'Dicas práticas para criar campanhas de arrecadação de sucesso. Aprenda a engajar doadores.',
        'sort_order' => 4,
        'is_active' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
      ],
      [
        'name' => 'Impacto Social',
        'slug' => 'impacto-social',
        'description' => 'O impacto das doações na sociedade brasileira',
        'icon' => 'fas fa-globe-americas',
        'meta_description' => 'Descubra o impacto social das doações e como o crowdfunding solidário transforma comunidades.',
        'sort_order' => 5,
        'is_active' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
      ],
      [
        'name' => 'Notícias',
        'slug' => 'noticias',
        'description' => 'Novidades do DoarFazBem e do mundo da filantropia',
        'icon' => 'fas fa-newspaper',
        'meta_description' => 'Últimas notícias sobre crowdfunding, filantropia e novidades da plataforma DoarFazBem.',
        'sort_order' => 6,
        'is_active' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
      ],
      [
        'name' => 'Rifas e Sorteios',
        'slug' => 'rifas-e-sorteios',
        'description' => 'Tudo sobre rifas solidárias e sorteios beneficentes',
        'icon' => 'fas fa-ticket-alt',
        'meta_description' => 'Artigos sobre rifas solidárias, sorteios beneficentes e como arrecadar com criatividade.',
        'sort_order' => 7,
        'is_active' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
      ],
    ];

    // Verificar se já existem categorias
    $existing = $this->db->table('blog_categories')->countAll();
    if ($existing > 0) {
      echo "Categorias do blog já existem. Pulando seeder.\n";
      return;
    }

    $this->db->table('blog_categories')->insertBatch($categories);
    echo count($categories) . " categorias do blog criadas com sucesso!\n";
  }
}
