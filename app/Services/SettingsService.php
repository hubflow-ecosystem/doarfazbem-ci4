<?php

namespace App\Services;

use Config\Database;

/**
 * Serviço centralizado de configurações
 * Carrega e salva configurações do banco de dados
 */
class SettingsService
{
    protected $db;
    protected static ?array $cache = null;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Obtém uma configuração pelo key
     */
    public function get(string $key, $default = null)
    {
        $this->loadCache();

        if (!isset(self::$cache[$key])) {
            return $default;
        }

        return $this->castValue(
            self::$cache[$key]['setting_value'],
            self::$cache[$key]['setting_type']
        );
    }

    /**
     * Obtém todas as configurações de um grupo
     */
    public function getGroup(string $group): array
    {
        $this->loadCache();

        $result = [];
        foreach (self::$cache as $key => $setting) {
            if ($setting['setting_group'] === $group) {
                $result[$key] = $this->castValue(
                    $setting['setting_value'],
                    $setting['setting_type']
                );
            }
        }

        return $result;
    }

    /**
     * Obtém todas as configurações
     */
    public function getAll(): array
    {
        $this->loadCache();

        $result = [];
        foreach (self::$cache as $key => $setting) {
            $result[$key] = $this->castValue(
                $setting['setting_value'],
                $setting['setting_type']
            );
        }

        return $result;
    }

    /**
     * Obtém todas as configurações agrupadas
     */
    public function getAllGrouped(): array
    {
        $this->loadCache();

        $result = [];
        foreach (self::$cache as $key => $setting) {
            $group = $setting['setting_group'];
            if (!isset($result[$group])) {
                $result[$group] = [];
            }
            $result[$group][$key] = [
                'value' => $this->castValue($setting['setting_value'], $setting['setting_type']),
                'type' => $setting['setting_type'],
                'description' => $setting['description'] ?? '',
            ];
        }

        return $result;
    }

    /**
     * Define uma configuração
     */
    public function set(string $key, $value, ?string $type = null): bool
    {
        $this->loadCache();

        // Converter valor para string
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
            $type = $type ?? 'bool';
        } elseif (is_array($value)) {
            $value = json_encode($value);
            $type = $type ?? 'json';
        } else {
            $value = (string) $value;
        }

        $data = [
            'setting_value' => $value,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($type) {
            $data['setting_type'] = $type;
        }

        if (isset(self::$cache[$key])) {
            // Update
            $result = $this->db->table('settings')
                ->where('setting_key', $key)
                ->update($data);
        } else {
            // Insert
            $data['setting_key'] = $key;
            $data['setting_group'] = 'custom';
            $data['setting_type'] = $type ?? 'string';
            $result = $this->db->table('settings')->insert($data);
        }

        // Limpar cache
        self::$cache = null;

        return $result !== false;
    }

    /**
     * Define múltiplas configurações
     */
    public function setMany(array $settings): bool
    {
        foreach ($settings as $key => $value) {
            $this->set($key, $value);
        }
        return true;
    }

    /**
     * Salva configurações de um formulário POST
     */
    public function saveFromPost(array $postData, array $boolFields = []): bool
    {
        foreach ($postData as $key => $value) {
            // Ignorar campos especiais
            if (in_array($key, ['csrf_test_name', 'submit'])) {
                continue;
            }

            // Tratar checkboxes
            if (in_array($key, $boolFields)) {
                $value = !empty($value) ? '1' : '0';
            }

            $this->set($key, $value);
        }

        // Tratar checkboxes não marcados (não vêm no POST)
        foreach ($boolFields as $field) {
            if (!isset($postData[$field])) {
                $this->set($field, '0', 'bool');
            }
        }

        return true;
    }

    /**
     * Carrega cache do banco
     */
    protected function loadCache(): void
    {
        if (self::$cache !== null) {
            return;
        }

        try {
            $settings = $this->db->table('settings')->get()->getResultArray();

            self::$cache = [];
            foreach ($settings as $setting) {
                self::$cache[$setting['setting_key']] = $setting;
            }
        } catch (\Exception $e) {
            // Tabela pode não existir ainda
            self::$cache = [];
        }
    }

    /**
     * Limpa o cache
     */
    public function clearCache(): void
    {
        self::$cache = null;
    }

    /**
     * Converte valor para o tipo correto
     */
    protected function castValue($value, string $type)
    {
        switch ($type) {
            case 'int':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'bool':
                return $value === '1' || $value === 'true' || $value === true;
            case 'json':
            case 'array':
                $decoded = json_decode($value, true);
                return is_array($decoded) ? $decoded : [];
            default:
                return $value;
        }
    }

    /**
     * Helper estático para obter configuração
     */
    public static function setting(string $key, $default = null)
    {
        $service = new self();
        return $service->get($key, $default);
    }
}
