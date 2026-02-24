<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRaffleWinnersTable extends Migration
{
    public function up()
    {
        // Tabela unificada de ganhadores - para transparência total
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'raffle_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'winner_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'winner_email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'winner_phone' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            // Tipo do prêmio: main (principal), ranking (top compradores), special (cota premiada)
            'prize_type' => [
                'type' => 'ENUM',
                'constraint' => ['main', 'ranking', 'special'],
            ],
            // Número sorteado/comprado (para main e special)
            'winning_number' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            // Posição no ranking (1, 2, 3 para tipo ranking)
            'ranking_position' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
            ],
            // Quantidade de números comprados (para ranking)
            'total_numbers_bought' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            // Nome do prêmio (ex: "PIX Instantâneo", "1º Lugar - Maior Comprador")
            'prize_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            // Valor do prêmio
            'prize_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
            ],
            // Status do pagamento
            'payment_status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'processing', 'paid', 'failed'],
                'default' => 'pending',
            ],
            'paid_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            // ID do pagamento no gateway (Asaas/MercadoPago)
            'payment_gateway_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            // Notificações
            'notification_sent' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'notification_sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            // Verificação pública
            'verification_code' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'Código para verificação pública do resultado',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('raffle_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('prize_type');
        $this->forge->addKey('payment_status');
        $this->forge->addKey('verification_code');
        $this->forge->addForeignKey('raffle_id', 'raffles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('raffle_winners');
    }

    public function down()
    {
        $this->forge->dropTable('raffle_winners');
    }
}
