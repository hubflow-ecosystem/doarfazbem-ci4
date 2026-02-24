<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="bg-gray-50 py-12">
    <div class="container-custom max-w-4xl">
        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-sm p-8 mb-8">
            <h1 class="text-4xl font-black text-gray-900 mb-4">Política de Privacidade</h1>
            <p class="text-gray-600">
                Última atualização: <?= date('d/m/Y') ?>
            </p>
            <p class="text-gray-600 mt-4">
                A DoarFazBem valoriza e respeita a privacidade de todos os usuários. Esta política descreve como coletamos, usamos, armazenamos e protegemos seus dados pessoais, em conformidade com a Lei Geral de Proteção de Dados (LGPD - Lei 13.709/2018).
            </p>
        </div>

        <!-- Content -->
        <div class="bg-white rounded-2xl shadow-sm p-8 prose prose-lg max-w-none">

            <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">1. Dados que Coletamos</h2>

            <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-3">1.1 Dados Fornecidos por Você</h3>
            <ul class="list-disc pl-6 text-gray-700 mb-4 space-y-2">
                <li><strong>Cadastro:</strong> Nome, email, telefone, CPF/CNPJ, senha</li>
                <li><strong>Campanhas:</strong> Título, descrição, categoria, imagens, vídeos, localização</li>
                <li><strong>Doações:</strong> Valor, forma de pagamento, dados bancários (processados por terceiros)</li>
                <li><strong>Perfil:</strong> Foto, biografia, redes sociais</li>
            </ul>

            <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-3">1.2 Dados Coletados Automaticamente</h3>
            <ul class="list-disc pl-6 text-gray-700 mb-4 space-y-2">
                <li><strong>Navegação:</strong> Endereço IP, tipo de navegador, páginas visitadas, tempo de permanência</li>
                <li><strong>Dispositivo:</strong> Sistema operacional, resolução de tela, modelo do dispositivo</li>
                <li><strong>Cookies:</strong> Preferências do usuário, sessão, analytics</li>
                <li><strong>Localização:</strong> Geolocalização aproximada (com seu consentimento)</li>
            </ul>

            <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">2. Como Usamos Seus Dados</h2>
            <p class="text-gray-700 mb-4">
                Utilizamos seus dados para:
            </p>
            <ul class="list-disc pl-6 text-gray-700 mb-4 space-y-2">
                <li><strong>Fornecer o serviço:</strong> Criar, gerenciar e divulgar campanhas</li>
                <li><strong>Processar doações:</strong> Intermediar transações financeiras</li>
                <li><strong>Comunicação:</strong> Enviar notificações, atualizações e avisos importantes</li>
                <li><strong>Segurança:</strong> Prevenir fraudes, abusos e atividades ilegais</li>
                <li><strong>Melhorias:</strong> Analisar uso da plataforma para aprimorar serviços</li>
                <li><strong>Marketing:</strong> Enviar novidades e promoções (com seu consentimento)</li>
                <li><strong>Conformidade Legal:</strong> Cumprir obrigações legais e regulatórias</li>
            </ul>

            <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">3. Compartilhamento de Dados</h2>
            <p class="text-gray-700 mb-4">
                Compartilhamos seus dados apenas quando necessário:
            </p>
            <ul class="list-disc pl-6 text-gray-700 mb-4 space-y-2">
                <li><strong>Gateway de Pagamento:</strong> Asaas (para processar doações)</li>
                <li><strong>Serviços de Email:</strong> Para envio de notificações</li>
                <li><strong>Analytics:</strong> Google Analytics (dados anonimizados)</li>
                <li><strong>Autoridades:</strong> Quando exigido por lei ou ordem judicial</li>
            </ul>
            <p class="text-gray-700 mb-4">
                <strong>Não vendemos</strong> seus dados pessoais para terceiros.
            </p>

            <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">4. Armazenamento e Segurança</h2>

            <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-3">4.1 Localização</h3>
            <p class="text-gray-700 mb-4">
                Seus dados são armazenados em servidores localizados no Brasil, em conformidade com a LGPD.
            </p>

            <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-3">4.2 Medidas de Segurança</h3>
            <ul class="list-disc pl-6 text-gray-700 mb-4 space-y-2">
                <li>Criptografia de dados sensíveis (SSL/TLS)</li>
                <li>Senhas armazenadas com hash (bcrypt)</li>
                <li>Acesso restrito aos dados por colaboradores autorizados</li>
                <li>Monitoramento de atividades suspeitas</li>
                <li>Backups regulares</li>
                <li>Firewalls e sistemas de proteção</li>
            </ul>

            <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-3">4.3 Retenção de Dados</h3>
            <p class="text-gray-700 mb-4">
                Mantemos seus dados pelo tempo necessário para:
            </p>
            <ul class="list-disc pl-6 text-gray-700 mb-4 space-y-2">
                <li>Fornecer nossos serviços</li>
                <li>Cumprir obrigações legais (5 anos para dados fiscais)</li>
                <li>Resolver disputas</li>
                <li>Após solicitação de exclusão, dados são anonimizados ou deletados em até 30 dias</li>
            </ul>

            <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">5. Seus Direitos (LGPD)</h2>
            <p class="text-gray-700 mb-4">
                Você tem direito a:
            </p>
            <ul class="list-disc pl-6 text-gray-700 mb-4 space-y-2">
                <li><strong>Acesso:</strong> Solicitar cópia dos seus dados</li>
                <li><strong>Correção:</strong> Atualizar dados incorretos ou incompletos</li>
                <li><strong>Exclusão:</strong> Solicitar remoção de dados (exceto quando houver obrigação legal de retenção)</li>
                <li><strong>Portabilidade:</strong> Receber seus dados em formato estruturado</li>
                <li><strong>Revogação:</strong> Retirar consentimento para processamento de dados</li>
                <li><strong>Oposição:</strong> Opor-se ao processamento de dados</li>
                <li><strong>Informação:</strong> Saber com quem seus dados foram compartilhados</li>
            </ul>
            <p class="text-gray-700 mb-4">
                Para exercer seus direitos, entre em contato: <strong>privacidade@doarfazbem.com.br</strong>
            </p>

            <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">6. Cookies</h2>
            <p class="text-gray-700 mb-4">
                Utilizamos cookies para:
            </p>
            <ul class="list-disc pl-6 text-gray-700 mb-4 space-y-2">
                <li><strong>Essenciais:</strong> Manter sua sessão ativa, preferências de idioma</li>
                <li><strong>Funcionais:</strong> Lembrar suas preferências</li>
                <li><strong>Analytics:</strong> Entender como você usa a plataforma (Google Analytics)</li>
                <li><strong>Marketing:</strong> Personalizar anúncios (requer consentimento)</li>
            </ul>
            <p class="text-gray-700 mb-4">
                Você pode gerenciar cookies nas configurações do seu navegador.
            </p>

            <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">7. Privacidade de Menores</h2>
            <p class="text-gray-700 mb-4">
                Nossa plataforma não é direcionada a menores de 18 anos. Se tomarmos conhecimento de que coletamos dados de menores sem autorização legal, deletaremos imediatamente.
            </p>

            <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">8. Alterações na Política</h2>
            <p class="text-gray-700 mb-4">
                Podemos atualizar esta política periodicamente. Notificaremos sobre mudanças significativas por email ou aviso na plataforma.
            </p>

            <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">9. Encarregado de Dados (DPO)</h2>
            <p class="text-gray-700 mb-4">
                Para questões sobre privacidade e proteção de dados:
            </p>
            <ul class="list-none text-gray-700 mb-4 space-y-2">
                <li><strong>Email:</strong> dpo@doarfazbem.com.br</li>
                <li><strong>Telefone:</strong> (11) 99999-9999</li>
            </ul>

            <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">10. Autoridade Nacional (ANPD)</h2>
            <p class="text-gray-700 mb-4">
                Se suas preocupações não forem resolvidas, você pode contatar a Autoridade Nacional de Proteção de Dados:
            </p>
            <ul class="list-none text-gray-700 mb-4 space-y-2">
                <li><strong>Site:</strong> <a href="https://www.gov.br/anpd" target="_blank" class="text-primary-600 hover:underline">www.gov.br/anpd</a></li>
            </ul>

            <div class="bg-blue-50 border-l-4 border-blue-500 p-6 mt-8">
                <h3 class="text-xl font-semibold text-blue-900 mb-3">📧 Contato</h3>
                <p class="text-blue-800">
                    Dúvidas sobre esta política? Entre em contato:
                </p>
                <ul class="list-none text-blue-800 mt-3 space-y-1">
                    <li><strong>Email:</strong> privacidade@doarfazbem.com.br</li>
                    <li><strong>Telefone:</strong> (11) 99999-9999</li>
                </ul>
            </div>

        </div>

        <!-- Voltar -->
        <div class="text-center mt-8">
            <a href="<?= base_url('/') ?>" class="btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i> Voltar para Home
            </a>
        </div>

    </div>
</div>

<?= $this->endSection() ?>
