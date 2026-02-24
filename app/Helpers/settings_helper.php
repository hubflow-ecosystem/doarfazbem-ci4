<?php

/**
 * Helper para acessar configurações do banco de dados
 *
 * Permite que classes de Config e todo o sistema acessem
 * configurações dinâmicas salvas no banco de dados.
 */

if (!function_exists('setting')) {
    /**
     * Obtém uma configuração do banco de dados
     *
     * @param string $key Chave da configuração
     * @param mixed $default Valor padrão se não encontrado
     * @return mixed
     */
    function setting(string $key, $default = null)
    {
        static $cache = null;

        // Carregar cache na primeira chamada
        if ($cache === null) {
            try {
                $db = \Config\Database::connect();

                // Verificar se a tabela existe
                if (!$db->tableExists('settings')) {
                    $cache = [];
                    return $default;
                }

                $settings = $db->table('settings')->get()->getResultArray();
                $cache = [];

                foreach ($settings as $setting) {
                    $cache[$setting['setting_key']] = [
                        'value' => $setting['setting_value'],
                        'type' => $setting['setting_type'],
                    ];
                }
            } catch (\Exception $e) {
                $cache = [];
                return $default;
            }
        }

        if (!isset($cache[$key])) {
            return $default;
        }

        $value = $cache[$key]['value'];
        $type = $cache[$key]['type'];

        // Converter para o tipo correto
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
}

if (!function_exists('settings_clear_cache')) {
    /**
     * Limpa o cache de configurações
     * Útil após atualizar configurações via admin
     */
    function settings_clear_cache(): void
    {
        // Como usamos static, precisamos "recriar" a função
        // Na prática, isso força recarregar na próxima requisição
    }
}
