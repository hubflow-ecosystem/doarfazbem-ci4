<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Faker\Factory;

/**
 * TestUsersSeeder
 *
 * Cria usuários de teste para desenvolvimento e testes automatizados
 *
 * Uso:
 * php spark db:seed TestUsersSeeder
 */
class TestUsersSeeder extends Seeder
{
    public function run()
    {
        $faker = Factory::create('pt_BR');

        // Limpar usuários de teste anteriores (opcional)
        $this->db->table('users')
            ->where('email LIKE', '%@test.doarfazbem.local')
            ->delete();

        echo "\n🧪 Criando usuários de teste...\n\n";

        // 1. Admin de teste
        $adminData = [
            'name' => 'Admin Teste',
            'email' => 'admin@test.doarfazbem.local',
            'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
            'phone' => '11999999999',
            'cpf' => '123.456.789-00',
            'role' => 'admin',
            'email_verified' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->db->table('users')->insert($adminData);
        echo "✅ Admin criado: admin@test.doarfazbem.local (senha: admin123)\n";

        // 2. Usuário comum para criar campanhas
        $campaignCreatorData = [
            'name' => 'João Silva Criador',
            'email' => 'criador@test.doarfazbem.local',
            'password_hash' => password_hash('teste123', PASSWORD_DEFAULT),
            'phone' => '11988888888',
            'cpf' => '987.654.321-00',
            'role' => 'user',
            'email_verified' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->db->table('users')->insert($campaignCreatorData);
        echo "✅ Criador de campanhas: criador@test.doarfazbem.local (senha: teste123)\n";

        // 3. Doador VIP (fará várias doações)
        $donorVipData = [
            'name' => 'Maria Santos Doadora',
            'email' => 'doadora@test.doarfazbem.local',
            'password_hash' => password_hash('teste123', PASSWORD_DEFAULT),
            'phone' => '11977777777',
            'cpf' => '111.222.333-44',
            'role' => 'user',
            'email_verified' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->db->table('users')->insert($donorVipData);
        echo "✅ Doador VIP: doadora@test.doarfazbem.local (senha: teste123)\n";

        // 4. Criar 20 usuários aleatórios com Faker
        echo "\n📦 Criando 20 usuários aleatórios...\n";

        for ($i = 1; $i <= 20; $i++) {
            $cpf = $this->generateCPF();

            $userData = [
                'name' => $faker->name,
                'email' => "user{$i}@test.doarfazbem.local",
                'password_hash' => password_hash('teste123', PASSWORD_DEFAULT),
                'phone' => preg_replace('/\D/', '', $faker->cellphone),
                'cpf' => $cpf,
                'role' => 'user',
                'email_verified' => $faker->boolean(80) ? 1 : 0, // 80% verificados
                'created_at' => $faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->db->table('users')->insert($userData);
        }

        echo "✅ 20 usuários aleatórios criados (user1 a user20@test.doarfazbem.local)\n";
        echo "   Senha para todos: teste123\n";

        // 5. Usuários especiais para testes específicos

        // Usuário com Google OAuth
        $googleUserData = [
            'name' => 'Google User Test',
            'email' => 'google@test.doarfazbem.local',
            'password_hash' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
            'google_id' => '123456789012345678901',
            'avatar' => 'https://lh3.googleusercontent.com/a/default-user',
            'role' => 'user',
            'email_verified' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->db->table('users')->insert($googleUserData);
        echo "✅ Usuário Google OAuth: google@test.doarfazbem.local\n";

        // Usuário sem email verificado
        $unverifiedUserData = [
            'name' => 'Usuário Não Verificado',
            'email' => 'nao-verificado@test.doarfazbem.local',
            'password_hash' => password_hash('teste123', PASSWORD_DEFAULT),
            'phone' => '11966666666',
            'cpf' => '555.666.777-88',
            'role' => 'user',
            'email_verified' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->db->table('users')->insert($unverifiedUserData);
        echo "✅ Usuário não verificado: nao-verificado@test.doarfazbem.local (senha: teste123)\n";

        echo "\n✨ Total: 24 usuários de teste criados com sucesso!\n";
        echo "\n📋 RESUMO:\n";
        echo "   - 1 Admin\n";
        echo "   - 1 Criador de Campanhas\n";
        echo "   - 1 Doador VIP\n";
        echo "   - 20 Usuários Aleatórios\n";
        echo "   - 1 Usuário Google OAuth\n";
        echo "   - 1 Usuário Não Verificado\n";
        echo "\n🔑 Senha padrão: teste123\n";
        echo "🔑 Senha admin: admin123\n\n";
    }

    /**
     * Gera um CPF válido aleatório
     */
    private function generateCPF(): string
    {
        $n1 = rand(0, 9);
        $n2 = rand(0, 9);
        $n3 = rand(0, 9);
        $n4 = rand(0, 9);
        $n5 = rand(0, 9);
        $n6 = rand(0, 9);
        $n7 = rand(0, 9);
        $n8 = rand(0, 9);
        $n9 = rand(0, 9);

        $d1 = $n9 * 2 + $n8 * 3 + $n7 * 4 + $n6 * 5 + $n5 * 6 + $n4 * 7 + $n3 * 8 + $n2 * 9 + $n1 * 10;
        $d1 = 11 - ($d1 % 11);
        if ($d1 >= 10) {
            $d1 = 0;
        }

        $d2 = $d1 * 2 + $n9 * 3 + $n8 * 4 + $n7 * 5 + $n6 * 6 + $n5 * 7 + $n4 * 8 + $n3 * 9 + $n2 * 10 + $n1 * 11;
        $d2 = 11 - ($d2 % 11);
        if ($d2 >= 10) {
            $d2 = 0;
        }

        return sprintf('%d%d%d.%d%d%d.%d%d%d-%d%d', $n1, $n2, $n3, $n4, $n5, $n6, $n7, $n8, $n9, $d1, $d2);
    }
}
