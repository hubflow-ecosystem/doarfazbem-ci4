<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBirthDateProvinceToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'birth_date' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'cpf'
            ],
            'province' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'address_complement'
            ],
        ];

        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('users', [
            'birth_date',
            'province'
        ]);
    }
}
