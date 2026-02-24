<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAsaasAccountsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'asaas_account_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'comment' => 'ID da subconta no Asaas',
            ],
            'asaas_wallet_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'Wallet ID da subconta',
            ],
            'account_status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'active', 'blocked', 'inactive'],
                'default' => 'pending',
            ],
            'cpf_cnpj' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'phone' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'mobile_phone' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'address' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'address_number' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'complement' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'province' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'postal_code' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
            ],
            'api_response' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON completo da resposta da API Asaas',
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
        $this->forge->addKey('user_id');
        $this->forge->addUniqueKey('asaas_account_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('asaas_accounts');
    }

    public function down()
    {
        $this->forge->dropTable('asaas_accounts');
    }
}
