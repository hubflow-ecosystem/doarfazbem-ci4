<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model para gerenciar subcontas Asaas dos criadores de campanhas
 */
class AsaasAccount extends Model
{
    protected $table = 'asaas_accounts';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'user_id',
        'asaas_account_id',
        'asaas_wallet_id',
        'account_status',
        'cpf_cnpj',
        'phone',
        'mobile_phone',
        'address',
        'address_number',
        'complement',
        'province',
        'postal_code',
        'api_response',
        // Campos legados (compatibilidade)
        'asaas_id',
        'wallet_id',
        'api_key',
        'status',
    ];

    protected $validationRules = [
        'user_id' => 'required|integer',
    ];

    protected $validationMessages = [
        'user_id' => [
            'required' => 'ID do usuário é obrigatório',
            'integer' => 'ID do usuário deve ser um número',
        ],
        'asaas_account_id' => [
            'required' => 'ID da conta Asaas é obrigatório',
        ],
        'cpf_cnpj' => [
            'required' => 'CPF/CNPJ é obrigatório',
        ],
    ];

    // Relacionamentos

    /**
     * Retorna o usuário dono desta subconta
     */
    public function getUser(int $accountId)
    {
        $account = $this->find($accountId);
        if (!$account) {
            return null;
        }

        $userModel = new \App\Models\User();
        return $userModel->find($account['user_id']);
    }

    /**
     * Busca subconta por user_id
     */
    public function getByUserId(int $userId): ?array
    {
        $account = $this->where('user_id', $userId)->first();

        if ($account) {
            // Normaliza nomes de campos (compatibilidade com dados antigos)
            if (empty($account['asaas_account_id']) && !empty($account['asaas_id'])) {
                $account['asaas_account_id'] = $account['asaas_id'];
            }
            if (empty($account['asaas_wallet_id']) && !empty($account['wallet_id'])) {
                $account['asaas_wallet_id'] = $account['wallet_id'];
            }
            if (empty($account['account_status']) && !empty($account['status'])) {
                $account['account_status'] = $account['status'];
            }
        }

        return $account;
    }

    /**
     * Busca subconta pelo ID do Asaas
     */
    public function getByAsaasAccountId(string $asaasAccountId): ?array
    {
        return $this->where('asaas_account_id', $asaasAccountId)->first();
    }

    /**
     * Busca subconta pelo Wallet ID
     */
    public function getByWalletId(string $walletId): ?array
    {
        return $this->where('asaas_wallet_id', $walletId)->first();
    }

    /**
     * Verifica se usuário já possui subconta ativa
     */
    public function userHasActiveAccount(int $userId): bool
    {
        $account = $this->where('user_id', $userId)
            ->where('account_status', 'active')
            ->first();

        return !empty($account);
    }

    /**
     * Atualiza status da subconta
     */
    public function updateStatus(int $accountId, string $status): bool
    {
        if (!in_array($status, ['pending', 'active', 'blocked', 'inactive'])) {
            return false;
        }

        return $this->update($accountId, ['account_status' => $status]);
    }

    /**
     * Retorna subcontas por status
     */
    public function getByStatus(string $status): array
    {
        return $this->where('account_status', $status)->findAll();
    }

    /**
     * Retorna subcontas pendentes
     */
    public function getPendingAccounts(): array
    {
        return $this->getByStatus('pending');
    }

    /**
     * Retorna subcontas ativas
     */
    public function getActiveAccounts(): array
    {
        return $this->getByStatus('active');
    }

    /**
     * Retorna subcontas bloqueadas
     */
    public function getBlockedAccounts(): array
    {
        return $this->getByStatus('blocked');
    }

    /**
     * Desserializa JSON da API response
     */
    public function getApiResponseDecoded(int $accountId): ?array
    {
        $account = $this->find($accountId);
        if (!$account || empty($account['api_response'])) {
            return null;
        }

        return json_decode($account['api_response'], true);
    }

    /**
     * Formata CPF/CNPJ removendo caracteres especiais
     */
    public static function cleanCpfCnpj(string $cpfCnpj): string
    {
        return preg_replace('/[^0-9]/', '', $cpfCnpj);
    }

    /**
     * Valida CPF
     */
    public static function validateCpf(string $cpf): bool
    {
        $cpf = self::cleanCpfCnpj($cpf);

        if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
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
     * Valida CNPJ
     */
    public static function validateCnpj(string $cnpj): bool
    {
        $cnpj = self::cleanCpfCnpj($cnpj);

        if (strlen($cnpj) != 14 || preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        for ($i = 0, $j = 5, $sum = 0; $i < 12; $i++) {
            $sum += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $remainder = $sum % 11;
        if ($cnpj[12] != ($remainder < 2 ? 0 : 11 - $remainder)) {
            return false;
        }

        for ($i = 0, $j = 6, $sum = 0; $i < 13; $i++) {
            $sum += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $remainder = $sum % 11;

        return $cnpj[13] == ($remainder < 2 ? 0 : 11 - $remainder);
    }

    /**
     * Valida CPF ou CNPJ
     */
    public static function validateCpfCnpj(string $cpfCnpj): bool
    {
        $clean = self::cleanCpfCnpj($cpfCnpj);
        $length = strlen($clean);

        if ($length === 11) {
            return self::validateCpf($clean);
        } elseif ($length === 14) {
            return self::validateCnpj($clean);
        }

        return false;
    }

    /**
     * Formata CPF para exibição (xxx.xxx.xxx-xx)
     */
    public static function formatCpf(string $cpf): string
    {
        $cpf = self::cleanCpfCnpj($cpf);
        return substr($cpf, 0, 3) . '.' .
               substr($cpf, 3, 3) . '.' .
               substr($cpf, 6, 3) . '-' .
               substr($cpf, 9, 2);
    }

    /**
     * Formata CNPJ para exibição (xx.xxx.xxx/xxxx-xx)
     */
    public static function formatCnpj(string $cnpj): string
    {
        $cnpj = self::cleanCpfCnpj($cnpj);
        return substr($cnpj, 0, 2) . '.' .
               substr($cnpj, 2, 3) . '.' .
               substr($cnpj, 5, 3) . '/' .
               substr($cnpj, 8, 4) . '-' .
               substr($cnpj, 12, 2);
    }

    /**
     * Formata CPF ou CNPJ automaticamente
     */
    public static function formatCpfCnpj(string $cpfCnpj): string
    {
        $clean = self::cleanCpfCnpj($cpfCnpj);
        $length = strlen($clean);

        if ($length === 11) {
            return self::formatCpf($clean);
        } elseif ($length === 14) {
            return self::formatCnpj($clean);
        }

        return $cpfCnpj;
    }
}
