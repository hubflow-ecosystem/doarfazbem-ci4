<?php

/**
 * Helper para verificação de reCAPTCHA Enterprise
 */

if (!function_exists('verify_recaptcha')) {
    /**
     * Verifica token do reCAPTCHA v3
     *
     * @param string $token Token retornado pelo reCAPTCHA no front-end
     * @param string $action Ação esperada (ex: 'register', 'create_campaign', 'donate')
     * @param float|null $minScore Score mínimo aceito (0.0 a 1.0). Null usa o padrão do config
     * @return bool True se verificação passou
     */
    function verify_recaptcha(string $token, string $action = 'submit', ?float $minScore = null): bool
    {
        $google = config('Google');

        // Se não tiver secret key configurada, retornar true (modo dev)
        if (empty($google->recaptchaSecretKey)) {
            log_message('warning', 'reCAPTCHA: Secret key não configurada - modo dev ativo');
            return true;
        }

        if (empty($token)) {
            log_message('warning', 'reCAPTCHA: Token vazio');
            return false;
        }

        $secretKey = $google->recaptchaSecretKey;
        $threshold = $minScore ?? $google->recaptchaScoreThreshold;

        // Chamada para API do reCAPTCHA
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = [
            'secret' => $secretKey,
            'response' => $token,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? null
        ];

        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => http_build_query($data),
                'timeout' => 10
            ]
        ];

        try {
            $context = stream_context_create($options);
            $response = @file_get_contents($url, false, $context);

            if ($response === false) {
                log_message('error', 'reCAPTCHA: Falha ao conectar com API');
                return true; // Em caso de erro na API, deixar passar (evitar bloquear usuários)
            }

            $result = json_decode($response, true);

            // Log para debug
            log_message('info', 'reCAPTCHA: ' . json_encode([
                'success' => $result['success'] ?? false,
                'score' => $result['score'] ?? 0,
                'action' => $result['action'] ?? 'unknown',
                'expected_action' => $action
            ]));

            // Verificações
            if (!isset($result['success']) || !$result['success']) {
                log_message('warning', 'reCAPTCHA: Verificação falhou - ' . json_encode($result['error-codes'] ?? []));
                return false;
            }

            // Verificar score
            $score = $result['score'] ?? 0;
            if ($score < $threshold) {
                log_message('warning', "reCAPTCHA: Score muito baixo ($score < $threshold)");
                return false;
            }

            // Verificar action
            if (isset($result['action']) && $result['action'] !== $action) {
                log_message('warning', "reCAPTCHA: Action incorreta (esperado: $action, recebido: {$result['action']})");
                return false;
            }

            return true;

        } catch (\Exception $e) {
            log_message('error', 'reCAPTCHA: Exceção - ' . $e->getMessage());
            return true; // Em caso de erro, deixar passar
        }
    }
}

if (!function_exists('recaptcha_script')) {
    /**
     * Retorna o script do reCAPTCHA para incluir no HTML
     *
     * @return string Script HTML
     */
    function recaptcha_script(): string
    {
        $google = config('Google');

        if (empty($google->recaptchaSiteKey)) {
            return '<!-- reCAPTCHA: Site key não configurada -->';
        }

        $siteKey = $google->recaptchaSiteKey;

        return <<<HTML
        <script src="https://www.google.com/recaptcha/api.js?render={$siteKey}"></script>
        HTML;
    }
}

if (!function_exists('recaptcha_execute')) {
    /**
     * Retorna JavaScript para executar reCAPTCHA em um formulário
     *
     * @param string $formId ID do formulário
     * @param string $action Nome da ação
     * @param string $tokenFieldId ID do campo hidden onde será colocado o token
     * @return string JavaScript
     */
    function recaptcha_execute(string $formId, string $action, string $tokenFieldId = 'recaptcha_token'): string
    {
        $google = config('Google');

        if (empty($google->recaptchaSiteKey)) {
            return '<!-- reCAPTCHA: Site key não configurada -->';
        }

        $siteKey = $google->recaptchaSiteKey;

        return <<<JS
        <script>
        grecaptcha.ready(function() {
            document.getElementById('{$formId}').addEventListener('submit', function(e) {
                e.preventDefault();

                // Se já tem token, pode enviar
                if (document.getElementById('{$tokenFieldId}').value) {
                    e.target.submit();
                    return;
                }

                // Executar reCAPTCHA
                grecaptcha.execute('{$siteKey}', {action: '{$action}'})
                    .then(function(token) {
                        document.getElementById('{$tokenFieldId}').value = token;
                        e.target.submit();
                    })
                    .catch(function(error) {
                        console.error('reCAPTCHA error:', error);
                        alert('Erro na verificação de segurança. Por favor, recarregue a página.');
                    });
            });
        });
        </script>
        JS;
    }
}

if (!function_exists('recaptcha_field')) {
    /**
     * Retorna campo hidden para armazenar token do reCAPTCHA
     *
     * @param string $fieldId ID do campo
     * @return string HTML
     */
    function recaptcha_field(string $fieldId = 'recaptcha_token'): string
    {
        return '<input type="hidden" name="recaptcha_token" id="' . $fieldId . '" value="">';
    }
}

if (!function_exists('recaptcha_badge_hide')) {
    /**
     * CSS para esconder o badge do reCAPTCHA (use apenas se mostrar aviso)
     *
     * @return string CSS
     */
    function recaptcha_badge_hide(): string
    {
        return <<<CSS
        <style>
        .grecaptcha-badge {
            visibility: hidden;
        }
        </style>
        CSS;
    }
}
