<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * UserModel
 *
 * Model para gerenciar usuários da plataforma DoarFazBem
 * Responsável por: cadastro, autenticação, validações
 */
class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    // Campos permitidos para inserção/atualização
    protected $allowedFields    = [
        'name',
        'email',
        'phone',
        'cpf',
        'password',      // Será convertido para password_hash pelo callback
        'password_hash',
        'google_id',
        'asaas_customer_id',
        'asaas_wallet_id',
        'asaas_account_id',
        'email_verified',
        'role',
        'avatar',
        'last_login',
        'reset_token',
        'reset_token_expiry',
        'birth_date',
        'postal_code',
        'address',
        'address_number',
        'address_complement',
        'province',
        'city',
        'state',
        'status',
        'suspension_reason',
        'suspended_at',
        'suspended_by'
    ];

    // Timestamps automáticos
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Regras de Validação
    protected $validationRules = [
        'name'     => [
            'rules'  => 'required|min_length[3]|max_length[255]',
            'errors' => [
                'required'   => 'O nome é obrigatório.',
                'min_length' => 'O nome deve ter pelo menos 3 caracteres.',
                'max_length' => 'O nome não pode ter mais de 255 caracteres.'
            ]
        ],
        'email'    => [
            'rules'  => 'required|valid_email|is_unique[users.email,id,{id}]',
            'errors' => [
                'required'    => 'O email é obrigatório.',
                'valid_email' => 'Por favor, insira um email válido.',
                'is_unique'   => 'Este email já está cadastrado.'
            ]
        ],
        'phone'    => [
            'rules'  => 'permit_empty|regex_match[/^\d{10,11}$/]',
            'errors' => [
                'regex_match' => 'Telefone inválido. Use formato: 11987654321'
            ]
        ],
        'cpf'      => [
            'rules'  => 'permit_empty|regex_match[/^\d{3}\.\d{3}\.\d{3}\-\d{2}$/]|is_unique[users.cpf,id,{id}]',
            'errors' => [
                'regex_match' => 'CPF inválido. Use formato: 123.456.789-00',
                'is_unique'   => 'Este CPF já está cadastrado.'
            ]
        ],
        'password' => [
            'rules'  => 'required|min_length[8]',
            'errors' => [
                'required'   => 'A senha é obrigatória.',
                'min_length' => 'A senha deve ter pelo menos 8 caracteres.'
            ]
        ]
    ];

    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks para eventos
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['hashPassword'];
    protected $beforeUpdate   = ['hashPassword'];

    /**
     * Faz hash da senha antes de salvar no banco
     *
     * @param array $data
     * @return array
     */
    protected function hashPassword(array $data)
    {
        if (!isset($data['data']['password'])) {
            return $data;
        }

        // Gera hash bcrypt da senha
        $data['data']['password_hash'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);

        // Remove o campo 'password' (só salvamos o hash)
        unset($data['data']['password']);

        return $data;
    }

    /**
     * Verifica se a senha está correta
     *
     * @param string $password Senha em texto plano
     * @param string $hash Hash armazenado no banco
     * @return bool
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Busca usuário por email
     *
     * @param string $email
     * @return array|null
     */
    public function getUserByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Busca usuário por CPF
     *
     * @param string $cpf
     * @return array|null
     */
    public function getUserByCPF(string $cpf): ?array
    {
        return $this->where('cpf', $cpf)->first();
    }

    /**
     * Verifica se o email já está cadastrado
     *
     * @param string $email
     * @param int|null $excludeId ID para excluir da verificação (útil em updates)
     * @return bool
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $builder = $this->where('email', $email);

        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Verifica se o CPF já está cadastrado
     *
     * @param string $cpf
     * @param int|null $excludeId ID para excluir da verificação
     * @return bool
     */
    public function cpfExists(string $cpf, ?int $excludeId = null): bool
    {
        $builder = $this->where('cpf', $cpf);

        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Marca o email do usuário como verificado
     *
     * @param int $userId
     * @return bool
     */
    public function verifyEmail(int $userId): bool
    {
        return $this->update($userId, ['email_verified' => true]);
    }

    /**
     * Atualiza a senha do usuário
     *
     * @param int $userId
     * @param string $newPassword
     * @return bool
     */
    public function updatePassword(int $userId, string $newPassword): bool
    {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($userId, ['password_hash' => $passwordHash]);
    }

    /**
     * Busca todos os administradores
     *
     * @return array
     */
    public function getAdmins(): array
    {
        return $this->where('role', 'admin')->findAll();
    }

    /**
     * Promove usuário a administrador
     *
     * @param int $userId
     * @return bool
     */
    public function promoteToAdmin(int $userId): bool
    {
        return $this->update($userId, ['role' => 'admin']);
    }

    /**
     * Remove privilégios de administrador
     *
     * @param int $userId
     * @return bool
     */
    public function demoteFromAdmin(int $userId): bool
    {
        return $this->update($userId, ['role' => 'user']);
    }

    /**
     * Valida CPF (algoritmo oficial)
     *
     * @param string $cpf CPF formatado (123.456.789-00) ou não
     * @return bool
     */
    public static function validateCPF(string $cpf): bool
    {
        // Remove caracteres não numéricos
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        // Verifica se tem 11 dígitos
        if (strlen($cpf) != 11) {
            return false;
        }

        // Verifica se todos os dígitos são iguais (ex: 111.111.111-11)
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        // Validação dos dígitos verificadores
        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }

    /**
     * Formata CPF para o padrão 123.456.789-00
     *
     * @param string $cpf
     * @return string
     */
    public static function formatCPF(string $cpf): string
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        return substr($cpf, 0, 3) . '.' .
               substr($cpf, 3, 3) . '.' .
               substr($cpf, 6, 3) . '-' .
               substr($cpf, 9, 2);
    }

    /**
     * Formata telefone para o padrão (11) 98765-4321
     *
     * @param string $phone
     * @return string
     */
    public static function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($phone) == 11) {
            return '(' . substr($phone, 0, 2) . ') ' .
                   substr($phone, 2, 5) . '-' .
                   substr($phone, 7, 4);
        } elseif (strlen($phone) == 10) {
            return '(' . substr($phone, 0, 2) . ') ' .
                   substr($phone, 2, 4) . '-' .
                   substr($phone, 6, 4);
        }

        return $phone;
    }

    /**
     * Suspende um usuário
     *
     * @param int $userId
     * @param string $reason
     * @param int $suspendedBy Admin ID
     * @return bool
     */
    public function suspendUser(int $userId, string $reason, int $suspendedBy): bool
    {
        return $this->update($userId, [
            'status' => 'suspended',
            'suspension_reason' => $reason,
            'suspended_at' => date('Y-m-d H:i:s'),
            'suspended_by' => $suspendedBy
        ]);
    }

    /**
     * Bane um usuário permanentemente
     *
     * @param int $userId
     * @param string $reason
     * @param int $suspendedBy Admin ID
     * @return bool
     */
    public function banUser(int $userId, string $reason, int $suspendedBy): bool
    {
        return $this->update($userId, [
            'status' => 'banned',
            'suspension_reason' => $reason,
            'suspended_at' => date('Y-m-d H:i:s'),
            'suspended_by' => $suspendedBy
        ]);
    }

    /**
     * Reativa um usuário suspenso/banido
     *
     * @param int $userId
     * @return bool
     */
    public function reactivateUser(int $userId): bool
    {
        return $this->update($userId, [
            'status' => 'active',
            'suspension_reason' => null,
            'suspended_at' => null,
            'suspended_by' => null
        ]);
    }

    /**
     * Verifica se o usuário está ativo
     *
     * @param int $userId
     * @return bool
     */
    public function isActive(int $userId): bool
    {
        $user = $this->find($userId);
        if (!$user) return false;
        return ($user['status'] ?? 'active') === 'active';
    }

    /**
     * Verifica se o usuário pode fazer login
     *
     * @param int $userId
     * @return array ['allowed' => bool, 'reason' => string]
     */
    public function canLogin(int $userId): array
    {
        $user = $this->find($userId);
        if (!$user) {
            return ['allowed' => false, 'reason' => 'Usuário não encontrado.'];
        }

        $status = $user['status'] ?? 'active';

        if ($status === 'suspended') {
            return [
                'allowed' => false,
                'reason' => 'Sua conta foi suspensa. Motivo: ' . ($user['suspension_reason'] ?? 'Não informado')
            ];
        }

        if ($status === 'banned') {
            return [
                'allowed' => false,
                'reason' => 'Sua conta foi banida permanentemente. Motivo: ' . ($user['suspension_reason'] ?? 'Não informado')
            ];
        }

        return ['allowed' => true, 'reason' => ''];
    }
}
