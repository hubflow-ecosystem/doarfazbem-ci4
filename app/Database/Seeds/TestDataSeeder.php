<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run()
    {
        // Limpar dados existentes
        echo "\n🧹 Limpando dados existentes...\n";
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->db->query('TRUNCATE TABLE donations');
        $this->db->query('TRUNCATE TABLE campaigns');
        $this->db->query('TRUNCATE TABLE users');
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
        echo "✅ Dados antigos removidos!\n\n";

        // 1. Criar Usuários de Teste
        echo "📝 Criando usuários...\n";
        $this->createUsers();
        echo "✅ 10 usuários criados!\n\n";

        // 2. Criar Campanhas de Teste
        echo "📝 Criando campanhas...\n";
        $this->createCampaigns();
        echo "✅ 20 campanhas criadas!\n\n";

        // 3. Criar Doações de Teste
        echo "📝 Criando doações...\n";
        $this->createDonations();
        echo "✅ 50 doações criadas!\n\n";

        echo "\n✅ Dados de teste criados com sucesso!\n\n";
        echo "📊 Resumo:\n";
        echo "- 10 usuários criados\n";
        echo "- 20 campanhas criadas (todas as categorias)\n";
        echo "- 50 doações criadas (vários métodos de pagamento)\n\n";
        echo "🔐 Credenciais de teste:\n";
        echo "Admin: admin@doarfazbem.test / senha123\n";
        echo "Criador 1: joao.silva@example.com / senha123\n";
        echo "Criador 2: maria.santos@example.com / senha123\n\n";
    }

    private function createUsers()
    {
        $users = [
            // Admin
            [
                'name' => 'Administrador DoarFazBem',
                'email' => 'admin@doarfazbem.test',
                'password_hash' => password_hash('senha123', PASSWORD_DEFAULT),
                'phone' => '(11) 99999-9999',
                'role' => 'admin',
                'email_verified' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // Criadores
            [
                'name' => 'João Pedro Silva',
                'email' => 'joao.silva@example.com',
                'password_hash' => password_hash('senha123', PASSWORD_DEFAULT),
                'phone' => '(11) 98765-4321',
                'email_verified' => true,
                'created_at' => date('Y-m-d H:i:s', strtotime('-30 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Maria Santos Oliveira',
                'email' => 'maria.santos@example.com',
                'password_hash' => password_hash('senha123', PASSWORD_DEFAULT),
                'phone' => '(21) 97654-3210',
                'email_verified' => true,
                'created_at' => date('Y-m-d H:i:s', strtotime('-25 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Carlos Eduardo Lima',
                'email' => 'carlos.lima@example.com',
                'password_hash' => password_hash('senha123', PASSWORD_DEFAULT),
                'phone' => '(31) 96543-2109',
                'email_verified' => true,
                'created_at' => date('Y-m-d H:i:s', strtotime('-20 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Ana Paula Costa',
                'email' => 'ana.costa@example.com',
                'password_hash' => password_hash('senha123', PASSWORD_DEFAULT),
                'phone' => '(41) 95432-1098',
                'email_verified' => true,
                'created_at' => date('Y-m-d H:i:s', strtotime('-15 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Ricardo Mendes Souza',
                'email' => 'ricardo.mendes@example.com',
                'password_hash' => password_hash('senha123', PASSWORD_DEFAULT),
                'phone' => '(51) 94321-0987',
                'email_verified' => true,
                'created_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // Doadores (sem campanhas)
            [
                'name' => 'Juliana Ferreira',
                'email' => 'juliana.ferreira@example.com',
                'password_hash' => password_hash('senha123', PASSWORD_DEFAULT),
                'phone' => '(61) 93210-9876',
                'email_verified' => true,
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Fernando Alves',
                'email' => 'fernando.alves@example.com',
                'password_hash' => password_hash('senha123', PASSWORD_DEFAULT),
                'phone' => '(71) 92109-8765',
                'email_verified' => true,
                'created_at' => date('Y-m-d H:i:s', strtotime('-4 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Patricia Rodrigues',
                'email' => 'patricia.rodrigues@example.com',
                'password_hash' => password_hash('senha123', PASSWORD_DEFAULT),
                'phone' => '(81) 91098-7654',
                'email_verified' => true,
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Roberto Castro',
                'email' => 'roberto.castro@example.com',
                'password_hash' => password_hash('senha123', PASSWORD_DEFAULT),
                'phone' => '(85) 90987-6543',
                'email_verified' => true,
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $builder = $this->db->table('users');
        foreach ($users as $user) {
            $builder->insert($user);
        }
    }

    private function createCampaigns()
    {
        $campaigns = [
            // MÉDICAS (TAXA ZERO)
            [
                'user_id' => 2,
                'title' => 'Tratamento de Câncer de Mama para Maria',
                'slug' => 'tratamento-cancer-mama-maria',
                'description' => 'Maria, 45 anos, mãe de 3 filhos, foi diagnosticada com câncer de mama e precisa urgentemente iniciar o tratamento. Os custos com quimioterapia, medicamentos e cirurgia são muito altos e a família não tem condições de arcar sozinha.',
                // 'story' => 'Maria sempre foi uma mulher batalhadora, trabalhadora e dedicada à sua família. Aos 45 anos, após sentir um nódulo durante o auto-exame, procurou ajuda médica e recebeu o diagnóstico que mudou sua vida: câncer de mama. O tratamento precisa começar urgentemente, mas os custos são altíssimos. Estamos fazendo esta campanha para ajudar Maria a vencer essa batalha e voltar a abraçar seus filhos sem dor.',
                'goal_amount' => 80000.00,
                'category' => 'medica',
                'status' => 'active',
                'image' => null,
                'video_url' => null,
                'end_date' => date('Y-m-d', strtotime('+60 days')),
                'city' => 'São Paulo',
                'state' => 'SP',
                'created_at' => date('Y-m-d H:i:s', strtotime('-15 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'user_id' => 3,
                'title' => 'Cirurgia Cardíaca Urgente para João',
                'slug' => 'cirurgia-cardiaca-urgente-joao',
                'description' => 'João, 38 anos, pai de 2 crianças, precisa de uma cirurgia cardíaca urgente. Ele sofreu um infarto e os médicos indicaram cirurgia imediata.',
                // 'story' => 'João sempre trabalhou muito para sustentar sua família. Recentemente sofreu um infarto grave e precisa de cirurgia urgente. Sem plano de saúde e com recursos limitados, a família busca ajuda para salvar sua vida.',
                'goal_amount' => 120000.00,
                'category' => 'medica',
                'status' => 'active',
                'end_date' => date('Y-m-d', strtotime('+45 days')),
                'city' => 'Rio de Janeiro',
                'state' => 'RJ',
                'created_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'user_id' => 4,
                'title' => 'Remédios para Diabetes de Dona Antônia',
                'slug' => 'remedios-diabetes-dona-antonia',
                'description' => 'Dona Antônia, 72 anos, diabética há 20 anos, precisa de ajuda para comprar insulina e medicamentos mensais.',
                // 'story' => 'Dona Antônia é uma senhora querida por toda a vizinhança. Aposentada com salário mínimo, não consegue arcar com os custos dos medicamentos para diabetes. Precisamos ajudá-la a ter uma vida digna.',
                'goal_amount' => 5000.00,
                'category' => 'medica',
                'status' => 'active',
                'end_date' => date('Y-m-d', strtotime('+90 days')),
                'city' => 'Belo Horizonte',
                'state' => 'MG',
                'created_at' => date('Y-m-d H:i:s', strtotime('-20 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],

            // SOCIAIS (TAXA ZERO)
            [
                'user_id' => 5,
                'title' => 'Reconstrução de Casa Destruída por Enchente',
                'slug' => 'reconstrucao-casa-enchente',
                'description' => 'Família de 6 pessoas perdeu tudo em enchente. Casa completamente destruída, móveis e roupas perdidos.',
                // 'story' => 'A família Silva morava em uma casa simples às margens do rio. Com as fortes chuvas, a enchente destruiu completamente sua casa. Perderam tudo: móveis, roupas, documentos. Agora estão morando de favor e precisam reconstruir suas vidas.',
                'goal_amount' => 40000.00,
                'category' => 'social',
                'status' => 'active',
                'end_date' => date('Y-m-d', strtotime('+60 days')),
                'city' => 'Petrópolis',
                'state' => 'RJ',
                'created_at' => date('Y-m-d H:i:s', strtotime('-12 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'user_id' => 2,
                'title' => 'ONG Lar dos Animais - Resgate e Cuidados',
                'slug' => 'ong-lar-animais-resgate',
                'description' => 'ONG que cuida de 150 animais resgatados precisa de ajuda urgente para ração, medicamentos e reformas.',
                // 'story' => 'A ONG Lar dos Animais resgata, cuida e busca lares para animais abandonados. Com 150 animais sob cuidados, os custos são altíssimos. Precisamos de ajuda para continuar salvando vidas.',
                'goal_amount' => 25000.00,
                'category' => 'social',
                'status' => 'active',
                'end_date' => date('Y-m-d', strtotime('+120 days')),
                'city' => 'São Paulo',
                'state' => 'SP',
                'created_at' => date('Y-m-d H:i:s', strtotime('-8 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'user_id' => 3,
                'title' => 'Reforma de Creche Comunitária',
                'slug' => 'reforma-creche-comunitaria',
                'description' => 'Creche que atende 80 crianças carentes precisa urgentemente de reformas no telhado e pintura.',
                // 'story' => 'A Creche São Francisco atende gratuitamente 80 crianças de famílias carentes. O prédio está em situação precária, com infiltrações e telhado danificado. Precisamos reformar para garantir a segurança das crianças.',
                'goal_amount' => 30000.00,
                'category' => 'social',
                'status' => 'active',
                'end_date' => date('Y-m-d', strtotime('+90 days')),
                'city' => 'Salvador',
                'state' => 'BA',
                'created_at' => date('Y-m-d H:i:s', strtotime('-18 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],

            // EDUCAÇÃO (1%)
            [
                'user_id' => 4,
                'title' => 'Intercâmbio para Estudar Medicina na Argentina',
                'slug' => 'intercambio-medicina-argentina',
                'description' => 'Pedro, 19 anos, foi aprovado em Medicina na Argentina mas precisa de ajuda para custear os estudos.',
                // 'story' => 'Pedro sempre sonhou em ser médico. Estudou muito e foi aprovado na Universidade de Buenos Aires. Porém, sua família não tem condições de pagar os custos do intercâmbio. Com sua ajuda, Pedro poderá realizar seu sonho de se formar médico.',
                'goal_amount' => 60000.00,
                'category' => 'educacao',
                'status' => 'active',
                'end_date' => date('Y-m-d', strtotime('+30 days')),
                'city' => 'Curitiba',
                'state' => 'PR',
                'created_at' => date('Y-m-d H:i:s', strtotime('-7 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'user_id' => 5,
                'title' => 'Curso de Programação Full Stack',
                'slug' => 'curso-programacao-full-stack',
                'description' => 'Juliana, 25 anos, quer fazer transição de carreira para TI mas não tem recursos para o curso.',
                // 'story' => 'Juliana trabalha como recepcionista e sonha em mudar de área. Descobriu a programação e se apaixonou, mas os cursos são caros. Com sua ajuda, ela poderá fazer o curso Full Stack e conquistar uma carreira melhor.',
                'goal_amount' => 8000.00,
                'category' => 'educacao',
                'status' => 'active',
                'end_date' => date('Y-m-d', strtotime('+45 days')),
                'city' => 'Brasília',
                'state' => 'DF',
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],

            // NEGÓCIO (1%)
            [
                'user_id' => 6,
                'title' => 'Marmitas Saudáveis - Startup de Alimentação',
                'slug' => 'marmitas-saudaveis-startup',
                'description' => 'Casal quer abrir negócio de marmitas saudáveis no bairro. Precisam de equipamentos e alvará.',
                // 'story' => 'Carlos e Beatriz são nutricionistas e querem empreender. O projeto é oferecer marmitas saudáveis e acessíveis para trabalhadores da região. Precisam de freezers, fogão industrial e capital de giro inicial.',
                'goal_amount' => 15000.00,
                'category' => 'negocio',
                'status' => 'active',
                'end_date' => date('Y-m-d', strtotime('+60 days')),
                'city' => 'Porto Alegre',
                'state' => 'RS',
                'created_at' => date('Y-m-d H:i:s', strtotime('-9 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'user_id' => 2,
                'title' => 'Loja de Roupas Plus Size Online',
                'slug' => 'loja-roupas-plus-size',
                'description' => 'Amanda quer abrir loja online de roupas plus size com designs exclusivos e inclusivos.',
                // 'story' => 'Amanda sempre sofreu para encontrar roupas bonitas em seu tamanho. Decidiu empreender criando uma marca plus size com designs modernos e inclusivos. Precisa de ajuda para o estoque inicial e site.',
                'goal_amount' => 20000.00,
                'category' => 'negocio',
                'status' => 'active',
                'end_date' => date('Y-m-d', strtotime('+45 days')),
                'city' => 'Fortaleza',
                'state' => 'CE',
                'created_at' => date('Y-m-d H:i:s', strtotime('-6 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],

            // CRIATIVA (1%)
            [
                'user_id' => 3,
                'title' => 'Gravação do Primeiro CD Independente',
                'slug' => 'gravacao-primeiro-cd-independente',
                'description' => 'Banda de rock independente quer gravar primeiro álbum profissional em estúdio.',
                // 'story' => 'A banda Os Sonhadores toca há 5 anos em bares e eventos locais. Agora querem dar o próximo passo: gravar um álbum profissional com 12 músicas autorais. Precisam de ajuda para pagar estúdio, mixagem e prensagem.',
                'goal_amount' => 25000.00,
                'category' => 'criativa',
                'status' => 'active',
                'end_date' => date('Y-m-d', strtotime('+90 days')),
                'city' => 'Recife',
                'state' => 'PE',
                'created_at' => date('Y-m-d H:i:s', strtotime('-11 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'user_id' => 4,
                'title' => 'Curta-Metragem sobre Cultura Nordestina',
                'slug' => 'curta-metragem-cultura-nordestina',
                'description' => 'Diretor quer produzir curta-metragem sobre as tradições do sertão nordestino.',
                // 'story' => 'Rodrigo é cineasta e quer contar histórias sobre sua terra. O projeto é um curta de 20 minutos mostrando as tradições, música e luta do povo nordestino. Precisamos de equipamentos, equipe e pós-produção.',
                'goal_amount' => 18000.00,
                'category' => 'criativa',
                'status' => 'active',
                'end_date' => date('Y-m-d', strtotime('+75 days')),
                'city' => 'Natal',
                'state' => 'RN',
                'created_at' => date('Y-m-d H:i:s', strtotime('-13 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],

            // SOCIAL - Esportes (adaptado para categoria existente)
            [
                'user_id' => 5,
                'title' => 'Atleta de Judô vai para Campeonato Mundial',
                'slug' => 'atleta-judo-campeonato-mundial',
                'description' => 'Lucas, 17 anos, foi convocado para representar o Brasil no Mundial de Judô mas precisa de ajuda.',
                // 'story' => 'Lucas treina judô desde os 7 anos e é campeão brasileiro. Foi convocado para o Mundial no Japão mas não tem recursos para passagens, hospedagem e equipamentos. Vamos ajudá-lo a representar o Brasil!',
                'goal_amount' => 35000.00,
                'category' => 'social',
                'status' => 'active',
                'end_date' => date('Y-m-d', strtotime('+30 days')),
                'city' => 'Manaus',
                'state' => 'AM',
                'created_at' => date('Y-m-d H:i:s', strtotime('-4 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'user_id' => 6,
                'title' => 'Reformar Quadra de Vôlei Comunitária',
                'slug' => 'reformar-quadra-volei-comunitaria',
                'description' => 'Projeto social de vôlei que atende 50 crianças precisa reformar quadra danificada.',
                // 'story' => 'O Projeto Vôlei para Todos oferece aulas gratuitas de vôlei para crianças carentes. A quadra está em péssimas condições e precisamos reformá-la para continuar atendendo as crianças com segurança.',
                'goal_amount' => 12000.00,
                'category' => 'social',
                'status' => 'active',
                'end_date' => date('Y-m-d', strtotime('+60 days')),
                'city' => 'Goiânia',
                'state' => 'GO',
                'created_at' => date('Y-m-d H:i:s', strtotime('-16 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],

            // Mais campanhas para diversificar
            [
                'user_id' => 2,
                'title' => 'Tratamento de Leucemia Infantil',
                'slug' => 'tratamento-leucemia-infantil',
                'description' => 'Gabriel, 8 anos, luta contra leucemia e precisa de transplante de medula urgente.',
                // 'story' => 'Gabriel é um menino alegre e cheio de vida. Aos 8 anos foi diagnosticado com leucemia e precisa urgentemente de transplante de medula. Os custos são altíssimos e a família não tem recursos.',
                'goal_amount' => 150000.00,
                'category' => 'medica',
                'status' => 'active',
                'end_date' => date('Y-m-d', strtotime('+45 days')),
                'city' => 'São Paulo',
                'state' => 'SP',
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'user_id' => 3,
                'title' => 'Cesta Básica para Famílias Carentes',
                'slug' => 'cesta-basica-familias-carentes',
                'description' => 'Igreja distribui cestas básicas para 100 famílias mensalmente. Precisamos de ajuda para continuar.',
                // 'story' => 'Nossa igreja mantém um projeto social que distribui cestas básicas para 100 famílias em situação de vulnerabilidade. Com o aumento dos preços, precisamos de ajuda para continuar esse trabalho.',
                'goal_amount' => 10000.00,
                'category' => 'social',
                'status' => 'active',
                'end_date' => date('Y-m-d', strtotime('+90 days')),
                'city' => 'Belém',
                'state' => 'PA',
                'created_at' => date('Y-m-d H:i:s', strtotime('-14 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'user_id' => 4,
                'title' => 'Material Escolar para 50 Crianças',
                'slug' => 'material-escolar-50-criancas',
                'description' => 'Comunidade quer garantir material escolar completo para todas as crianças carentes da região.',
                // 'story' => 'Nossa comunidade identificou 50 crianças cujas famílias não têm condições de comprar material escolar. Queremos garantir que todas tenham cadernos, lápis, mochilas e uniformes para estudar com dignidade.',
                'goal_amount' => 5000.00,
                'category' => 'educacao',
                'status' => 'active',
                'end_date' => date('Y-m-d', strtotime('+25 days')),
                'city' => 'Aracaju',
                'state' => 'SE',
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'user_id' => 5,
                'title' => 'Coffee Shop Artesanal no Bairro',
                'slug' => 'coffee-shop-artesanal-bairro',
                'description' => 'Barista quer abrir café especializado com grãos de pequenos produtores brasileiros.',
                // 'story' => 'Sou barista há 10 anos e quero realizar o sonho de ter meu próprio café. A proposta é valorizar pequenos produtores brasileiros, oferecendo cafés especiais e ambiente acolhedor.',
                'goal_amount' => 35000.00,
                'category' => 'negocio',
                'status' => 'active',
                'end_date' => date('Y-m-d', strtotime('+60 days')),
                'city' => 'Florianópolis',
                'state' => 'SC',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'user_id' => 6,
                'title' => 'Livro de Poesias Autorais',
                'slug' => 'livro-poesias-autorais',
                'description' => 'Poeta quer publicar primeiro livro com 100 poesias sobre amor, vida e esperança.',
                // 'story' => 'Escrevo poesias há 15 anos e finalmente reuni 100 poemas que falam sobre amor, vida, esperança e superação. Quero publicar este livro de forma independente e levar minha arte para mais pessoas.',
                'goal_amount' => 8000.00,
                'category' => 'criativa',
                'status' => 'active',
                'end_date' => date('Y-m-d', strtotime('+120 days')),
                'city' => 'João Pessoa',
                'state' => 'PB',
                'created_at' => date('Y-m-d H:i:s', strtotime('-17 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $builder = $this->db->table('campaigns');
        foreach ($campaigns as $campaign) {
            $builder->insert($campaign);
        }
    }

    private function createDonations()
    {
        // Buscar IDs reais das campanhas criadas
        $campaigns = $this->db->table('campaigns')->select('id')->get()->getResult();
        $campaignIds = array_map(fn($c) => $c->id, $campaigns);

        if (empty($campaignIds)) {
            echo "⚠️  Nenhuma campanha encontrada! Pulando criação de doações.\n";
            return;
        }

        echo "   Campanhas disponíveis: " . count($campaignIds) . " (IDs: " . implode(', ', $campaignIds) . ")\n";

        // Nomes de doadores brasileiros
        $donorNames = [
            'José Carlos da Silva', 'Maria Aparecida Santos', 'Antonio Oliveira Lima',
            'Francisca Souza Costa', 'Paulo Roberto Alves', 'Ana Paula Ferreira',
            'João Batista Rodrigues', 'Mariana Silva Mendes', 'Pedro Henrique Castro',
            'Juliana Martins Souza', 'Ricardo Almeida Pinto', 'Camila Pereira Santos',
            'Fernando Augusto Lima', 'Patricia Oliveira Costa', 'Marcos Vinicius Silva',
            'Beatriz Helena Souza', 'Rafael Santos Alves', 'Amanda Carolina Ferreira',
            'Gustavo Henrique Lima', 'Larissa Cristina Santos', 'Bruno César Oliveira',
            'Vanessa Rodrigues Silva', 'Thiago Souza Costa', 'Isabela Martins Lima',
            'Leonardo Ferreira Santos', 'Bianca Alves Rodrigues'
        ];

        $paymentMethods = ['pix', 'credit_card', 'boleto'];
        $donations = [];

        // Criar 50 doações distribuídas entre as campanhas
        for ($i = 0; $i < 50; $i++) {
            $campaignId = $campaignIds[array_rand($campaignIds)]; // Usar IDs reais
            $amount = [10, 20, 30, 50, 100, 150, 200, 500, 1000][rand(0, 8)];
            $paymentMethod = $paymentMethods[rand(0, 2)];
            $donorName = $donorNames[rand(0, count($donorNames) - 1)];
            $donorEmail = strtolower(str_replace(' ', '.', $donorName)) . '@example.com';

            $donations[] = [
                'campaign_id' => $campaignId,
                'user_id' => rand(1, 10), // Pode ser null para doações anônimas
                'donor_name' => $donorName,
                'donor_email' => $donorEmail,
                'amount' => $amount,
                'platform_fee' => 0, // Calculado depois
                'payment_gateway_fee' => $amount * 0.0399, // 3.99%
                'net_amount' => $amount - ($amount * 0.0399),
                'payment_method' => $paymentMethod,
                'asaas_payment_id' => 'pay_' . uniqid(),
                'status' => ['confirmed', 'received'][rand(0, 1)],
                'is_anonymous' => rand(0, 10) > 7 ? 1 : 0, // 30% de chance de ser anônimo
                'message' => rand(0, 10) > 6 ? 'Que Deus abençoe! Força!' : null,
                'paid_at' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 30) . ' days')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 30) . ' days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        $builder = $this->db->table('donations');
        foreach ($donations as $donation) {
            $builder->insert($donation);
        }
    }
}
