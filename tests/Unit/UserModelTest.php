<?php

namespace Tests\Unit;

use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * UserModelTest
 *
 * Testes unitários para o modelo de usuários
 *
 * Execução:
 * php spark test --group unit
 * php spark test tests/Unit/UserModelTest.php
 */
class UserModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $model;
    protected $seed = 'TestUsersSeeder';

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new UserModel();
    }

    /**
     * @group unit
     * @group user
     */
    public function testCanCreateUser()
    {
        $userData = [
            'name' => 'Teste Usuário',
            'email' => 'teste@example.com',
            'password' => 'senha12345',
            'role' => 'user'
        ];

        $userId = $this->model->insert($userData);

        $this->assertIsNumeric($userId);
        $this->seeInDatabase('users', ['email' => 'teste@example.com']);
    }

    /**
     * @group unit
     * @group user
     */
    public function testPasswordIsHashedAutomatically()
    {
        $plainPassword = 'senha12345';

        $userData = [
            'name' => 'Teste Hash',
            'email' => 'hash@example.com',
            'password' => $plainPassword,
            'role' => 'user'
        ];

        $userId = $this->model->insert($userData);
        $user = $this->model->find($userId);

        // Senha não deve estar em texto plano
        $this->assertNotEquals($plainPassword, $user['password_hash']);

        // Hash deve ser bcrypt
        $this->assertStringStartsWith('$2y$', $user['password_hash']);
    }

    /**
     * @group unit
     * @group user
     */
    public function testCannotCreateDuplicateEmail()
    {
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);

        $userData1 = [
            'name' => 'Usuário 1',
            'email' => 'duplicado@example.com',
            'password' => 'senha123',
            'role' => 'user'
        ];

        $userData2 = [
            'name' => 'Usuário 2',
            'email' => 'duplicado@example.com', // Email duplicado
            'password' => 'senha456',
            'role' => 'user'
        ];

        $this->model->insert($userData1);
        $this->model->insert($userData2); // Deve falhar
    }

    /**
     * @group unit
     * @group user
     */
    public function testCanValidateCPF()
    {
        // CPF válido
        $this->assertTrue(UserModel::validateCPF('123.456.789-09'));
        $this->assertTrue(UserModel::validateCPF('12345678909')); // Sem formatação

        // CPF inválido
        $this->assertFalse(UserModel::validateCPF('123.456.789-00'));
        $this->assertFalse(UserModel::validateCPF('111.111.111-11')); // Todos iguais
        $this->assertFalse(UserModel::validateCPF('12345')); // Muito curto
    }

    /**
     * @group unit
     * @group user
     */
    public function testCanFormatCPF()
    {
        $cpf = '12345678909';
        $formatted = UserModel::formatCPF($cpf);

        $this->assertEquals('123.456.789-09', $formatted);
    }

    /**
     * @group unit
     * @group user
     */
    public function testCanFormatPhone()
    {
        // Celular (11 dígitos)
        $phone1 = '11987654321';
        $formatted1 = UserModel::formatPhone($phone1);
        $this->assertEquals('(11) 98765-4321', $formatted1);

        // Fixo (10 dígitos)
        $phone2 = '1132123456';
        $formatted2 = UserModel::formatPhone($phone2);
        $this->assertEquals('(11) 3212-3456', $formatted2);
    }

    /**
     * @group unit
     * @group user
     */
    public function testCanVerifyEmail()
    {
        $userData = [
            'name' => 'Teste Verify',
            'email' => 'verify@example.com',
            'password' => 'senha123',
            'role' => 'user',
            'email_verified' => 0
        ];

        $userId = $this->model->insert($userData);

        // Email não verificado inicialmente
        $user = $this->model->find($userId);
        $this->assertEquals(0, $user['email_verified']);

        // Verificar email
        $this->model->verifyEmail($userId);

        // Email deve estar verificado
        $user = $this->model->find($userId);
        $this->assertEquals(1, $user['email_verified']);
    }

    /**
     * @group unit
     * @group user
     */
    public function testCanUpdatePassword()
    {
        $userData = [
            'name' => 'Teste Update Pass',
            'email' => 'updatepass@example.com',
            'password' => 'senhaantiga',
            'role' => 'user'
        ];

        $userId = $this->model->insert($userData);
        $oldUser = $this->model->find($userId);
        $oldHash = $oldUser['password_hash'];

        // Atualizar senha
        $newPassword = 'senhanova123';
        $this->model->updatePassword($userId, $newPassword);

        $newUser = $this->model->find($userId);
        $newHash = $newUser['password_hash'];

        // Hash deve ser diferente
        $this->assertNotEquals($oldHash, $newHash);

        // Nova senha deve ser verificável
        $this->assertTrue(password_verify($newPassword, $newHash));
    }

    /**
     * @group unit
     * @group user
     */
    public function testCanPromoteToAdmin()
    {
        $userData = [
            'name' => 'Teste Admin',
            'email' => 'admin@example.com',
            'password' => 'senha123',
            'role' => 'user' // Começa como user
        ];

        $userId = $this->model->insert($userData);

        // Promover a admin
        $this->model->promoteToAdmin($userId);

        $user = $this->model->find($userId);
        $this->assertEquals('admin', $user['role']);
    }

    /**
     * @group unit
     * @group user
     */
    public function testCanDemoteFromAdmin()
    {
        $userData = [
            'name' => 'Teste Demote',
            'email' => 'demote@example.com',
            'password' => 'senha123',
            'role' => 'admin'
        ];

        $userId = $this->model->insert($userData);

        // Rebaixar para user
        $this->model->demoteFromAdmin($userId);

        $user = $this->model->find($userId);
        $this->assertEquals('user', $user['role']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
