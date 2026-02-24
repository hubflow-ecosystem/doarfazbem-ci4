<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAddressFieldsToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'postal_code' => [
                'type' => 'VARCHAR',
                'constraint' => 9,
                'null' => true,
                'after' => 'cpf'
            ],
            'address' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'postal_code'
            ],
            'address_number' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'after' => 'address'
            ],
            'address_complement' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'address_number'
            ],
            'city' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'address_complement'
            ],
            'state' => [
                'type' => 'CHAR',
                'constraint' => 2,
                'null' => true,
                'after' => 'city'
            ],
        ];

        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('users', [
            'postal_code',
            'address',
            'address_number',
            'address_complement',
            'city',
            'state'
        ]);
    }
}
