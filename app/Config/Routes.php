<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// =====================================================
// ROTAS PÚBLICAS
// =====================================================

// Homepage
$routes->get('/', 'Home::index');

// Páginas Institucionais
$routes->get('anuncie-conosco', 'Pages::anuncieConosco');
$routes->get('doe-para-plataforma', 'Pages::doeParaPlataforma');
$routes->get('sobre', 'Pages::sobre');
$routes->get('como-funciona', 'Pages::comoFunciona');
$routes->get('termos', 'Pages::termos');
$routes->get('privacidade', 'Pages::privacidade');

// =====================================================
// ROTAS DE AUTENTICAÇÃO
// =====================================================

// Registro
$routes->get('register', 'AuthController::register');
$routes->post('register', 'AuthController::doRegister');

// Login
$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::doLogin');

// Logout
$routes->get('logout', 'AuthController::logout');

// Recuperação de Senha
$routes->get('forgot-password', 'AuthController::forgotPassword');
$routes->post('forgot-password', 'AuthController::sendResetLink');
$routes->get('reset-password/(:any)', 'AuthController::resetPassword/$1');
$routes->post('reset-password', 'AuthController::doResetPassword');

// Google OAuth
$routes->get('auth/google', 'AuthController::googleLogin');
$routes->get('auth/google/callback', 'AuthController::googleCallback');

// =====================================================
// ROTAS DE CAMPANHAS (Públicas)
// =====================================================

// Listar campanhas
$routes->get('campaigns', 'Campaign::index');

// =====================================================
// ROTAS PROTEGIDAS DE CAMPANHAS (Requerem autenticação)
// =====================================================

// Criar campanha (DEVE VIR ANTES da rota genérica campaigns/(:segment))
$routes->get('campaigns/create', 'Campaign::create', ['filter' => 'auth']);
$routes->post('campaigns/create', 'Campaign::store', ['filter' => 'auth']);

// Editar campanha
$routes->get('campaigns/edit/(:num)', 'Campaign::edit/$1', ['filter' => 'auth']);
$routes->post('campaigns/update/(:num)', 'Campaign::update/$1', ['filter' => 'auth']);

// Deletar campanha
$routes->post('campaigns/delete/(:num)', 'Campaign::delete/$1', ['filter' => 'auth']);

// Pausar, Reativar e Encerrar campanha
$routes->post('campaigns/pause/(:num)', 'Campaign::pause/$1', ['filter' => 'auth']);
$routes->post('campaigns/resume/(:num)', 'Campaign::resume/$1', ['filter' => 'auth']);
$routes->post('campaigns/end/(:num)', 'Campaign::end/$1', ['filter' => 'auth']);

// Atualizações e Comentários de Campanhas
$routes->post('campaigns/(:num)/update', 'CampaignInteractionController::createUpdate/$1', ['filter' => 'auth']);
$routes->post('campaigns/update/(:num)/edit', 'CampaignInteractionController::editUpdate/$1', ['filter' => 'auth']);
$routes->post('campaigns/update/(:num)/delete', 'CampaignInteractionController::deleteUpdate/$1', ['filter' => 'auth']);
$routes->post('campaigns/(:num)/comment', 'CampaignInteractionController::addComment/$1');
$routes->post('campaigns/comment/(:num)/delete', 'CampaignInteractionController::deleteComment/$1', ['filter' => 'auth']);
$routes->post('campaigns/comment/(:num)/report', 'CampaignInteractionController::reportComment/$1');

// Gerenciamento de Recompensas
$routes->get('campaigns/(:num)/rewards', 'Campaign::rewards/$1', ['filter' => 'auth']);
$routes->post('campaigns/(:num)/rewards/add', 'Campaign::addReward/$1', ['filter' => 'auth']);
$routes->post('campaigns/rewards/(:num)/update', 'Campaign::updateReward/$1', ['filter' => 'auth']);
$routes->post('campaigns/rewards/(:num)/delete', 'Campaign::deleteReward/$1', ['filter' => 'auth']);

// Gerenciamento de Mídia
$routes->get('campaigns/(:num)/media', 'Campaign::media/$1', ['filter' => 'auth']);
$routes->post('campaigns/(:num)/media/add', 'Campaign::addMedia/$1', ['filter' => 'auth']);
$routes->post('campaigns/media/(:num)/delete', 'Campaign::deleteMedia/$1', ['filter' => 'auth']);
$routes->post('campaigns/media/(:num)/primary', 'Campaign::setPrimaryMedia/$1', ['filter' => 'auth']);

// Highlights (Por que apoiar?)
$routes->post('campaigns/(:num)/highlights', 'Campaign::updateHighlights/$1', ['filter' => 'auth']);

// =====================================================
// ROTAS PÚBLICAS DE CAMPANHAS (Rotas genéricas por último)
// =====================================================

// Doar para campanha
$routes->get('donate/(:segment)', 'Payment::donate/$1');

// Ver campanha individual (DEVE VIR POR ÚLTIMO - rota genérica)
$routes->get('campaigns/(:segment)', 'Campaign::show/$1');

// =====================================================
// ROTAS DE PAGAMENTO (LEGADO - MANTER COMPATIBILIDADE)
// =====================================================

// Processar pagamento
$routes->post('payment/process', 'Payment::process');
$routes->get('payment/pix/(:num)', 'Payment::pix/$1');
$routes->get('payment/boleto/(:num)', 'Payment::boleto/$1');
$routes->get('payment/success/(:num)', 'Payment::success/$1');
$routes->get('payment/check-status/(:num)', 'Payment::checkStatus/$1');

// =====================================================
// ROTAS DE DOAÇÕES (NOVO SISTEMA ASAAS)
// =====================================================

// Checkout de doação (DEVE VIR ANTES da rota genérica de campanha)
$routes->get('campaigns/(:num)/donate', 'Donation::checkout/$1');

// Lista de doadores da campanha (para o criador)
$routes->get('campaigns/(:num)/donors', 'Campaign::donors/$1', ['filter' => 'auth']);

// Processar doação
$routes->post('donations/process', 'Donation::process');

// Páginas de pagamento
$routes->get('donations/pix/(:num)', 'Donation::pix/$1');
$routes->get('donations/boleto/(:num)', 'Donation::boleto/$1');
$routes->get('donations/credit-card/(:num)', 'Donation::creditCard/$1');
$routes->get('donations/success/(:num)', 'Donation::success/$1');

// Processar pagamento com cartão
$routes->post('donations/process-card', 'Donation::processCard');

// Verificar status do PIX (AJAX)
$routes->get('donations/pix-status/(:num)', 'Donation::pixStatus/$1');

// =====================================================
// RECIBOS E COMPROVANTES
// =====================================================

// Recibo de doação (HTML para impressão/PDF)
$routes->get('receipt/donation/(:num)', 'ReceiptController::donation/$1');
$routes->get('receipt/donation/pdf/(:num)', 'ReceiptController::downloadPdf/$1');

// Recibo de compra de rifa
$routes->get('receipt/raffle/(:num)', 'ReceiptController::raffle/$1');

// =====================================================
// WEBHOOK ASAAS
// =====================================================

// Receber notificações do Asaas (usa WebhookController com idempotencia e HMAC)
$routes->post('webhook/asaas', 'WebhookController::asaas');

// =====================================================
// SISTEMA DE RIFAS - NÚMEROS DA SORTE
// =====================================================

// Página principal da rifa ativa
$routes->get('rifas', 'RaffleController::index');
$routes->get('numeros-da-sorte', 'RaffleController::index');

// Histórico público de rifas (DEVE VIR ANTES da rota genérica)
$routes->get('rifas/historico', 'RaffleController::history');
$routes->get('rifas/verificar', 'RaffleController::verifyPrize');
$routes->get('rifas/meus-numeros', 'RaffleController::myNumbers', ['filter' => 'auth']);
$routes->get('rifas/historico/(:segment)', 'RaffleController::historyDetail/$1');

// Processar compra de cotas
$routes->post('rifas/comprar', 'RaffleController::purchase');

// Página de pagamento (aguardando PIX)
$routes->get('rifas/pagamento/(:num)', 'RaffleController::payment/$1');

// Página de sucesso
$routes->get('rifas/sucesso/(:num)', 'RaffleController::success/$1');

// Verificar status de pagamento (AJAX)
$routes->get('rifas/status/(:num)', 'RaffleController::checkPaymentStatus/$1');

// Webhook Mercado Pago (Rifas)
$routes->post('webhook/mercadopago/rifas', 'RaffleController::webhook');

// Simular pagamento (apenas em desenvolvimento)
$routes->get('rifas/simular-pagamento/(:num)', 'RaffleController::simulatePayment/$1');

// Ver rifa específica (ROTA GENÉRICA - SEMPRE POR ÚLTIMO)
$routes->get('rifas/(:segment)', 'RaffleController::show/$1');

// =====================================================
// ROTAS PROTEGIDAS (Requerem autenticação)
// =====================================================

// Dashboard
$routes->get('dashboard', 'DashboardController::index', ['filter' => 'auth']);
$routes->get('dashboard/analytics', 'DashboardController::analytics', ['filter' => 'auth']);
$routes->get('dashboard/my-campaigns', 'DashboardController::myCampaigns', ['filter' => 'auth']);
$routes->get('dashboard/my-donations', 'DashboardController::myDonations', ['filter' => 'auth']);
$routes->get('dashboard/notifications', 'NotificationController::preferences', ['filter' => 'auth']);
$routes->post('dashboard/notifications/update', 'NotificationController::updatePreferences', ['filter' => 'auth']);

// Completar Perfil (para criar conta Asaas)
$routes->get('dashboard/complete-profile', 'Dashboard::completeProfile', ['filter' => 'auth']);
$routes->post('dashboard/complete-profile', 'Dashboard::saveProfile', ['filter' => 'auth']);

// Sistema de Saques (Withdrawals)
$routes->get('dashboard/withdrawals', 'WithdrawalController::index', ['filter' => 'auth']);
$routes->get('dashboard/withdrawals/request', 'WithdrawalController::request', ['filter' => 'auth']);
$routes->post('dashboard/withdrawals/store', 'WithdrawalController::store', ['filter' => 'auth']);
$routes->post('dashboard/withdrawals/cancel/(:num)', 'WithdrawalController::cancel/$1', ['filter' => 'auth']);

// Perfil
$routes->get('profile', 'User::profile', ['filter' => 'auth']);
$routes->post('profile/update', 'User::updateProfile', ['filter' => 'auth']);
$routes->post('profile/change-password', 'User::changePassword', ['filter' => 'auth']);

// =====================================================
// NOTIFICAÇÕES (Unsubscribe público + API)
// =====================================================

// Unsubscribe (público - não requer auth)
$routes->get('notifications/unsubscribe/(:any)', 'NotificationController::unsubscribe/$1');
$routes->post('notifications/unsubscribe', 'NotificationController::confirmUnsubscribe');

// API para salvar token Firebase (requer auth)
$routes->post('api/fcm/save-token', 'NotificationController::savePushToken', ['filter' => 'auth']);

// AJAX endpoints para dashboard (requer auth)
$routes->post('dashboard/notifications/unsubscribe', 'NotificationController::unsubscribeCampaign', ['filter' => 'auth']);
$routes->post('dashboard/notifications/unsubscribe-all', 'NotificationController::unsubscribeAll', ['filter' => 'auth']);

// =====================================================
// ROTAS ADMINISTRATIVAS (Apenas admin/superadmin)
// =====================================================

$routes->group('admin', ['filter' => 'admin'], function($routes) {
    // Super Admin Dashboard
    $routes->get('dashboard', 'AdminController::dashboard');

    // Gerenciar Campanhas
    $routes->get('campaigns', 'AdminController::campaigns');

    // Gerenciar Usuários
    $routes->get('users', 'AdminController::users');
    $routes->post('users/suspend/(:num)', 'AdminController::suspendUser/$1');
    $routes->post('users/ban/(:num)', 'AdminController::banUser/$1');
    $routes->post('users/reactivate/(:num)', 'AdminController::reactivateUser/$1');

    // Gerenciar Doações
    $routes->get('donations', 'AdminController::donations');

    // Relatórios
    $routes->get('reports', 'AdminController::reports');

    // Logs de Auditoria
    $routes->get('audit-logs', 'AdminController::auditLogs');

    // Ações de Campanhas
    $routes->get('/', 'AdminController::dashboard');
    $routes->post('campaigns/approve/(:num)', 'AdminController::approveCampaign/$1');
    $routes->post('campaigns/reject/(:num)', 'AdminController::rejectCampaign/$1');

    // ===============================
    // GERENCIAMENTO DE RIFAS
    // ===============================
    $routes->get('raffles', 'AdminController::raffles');
    $routes->get('raffles/create', 'AdminController::createRaffle');
    $routes->post('raffles/create', 'AdminController::createRaffle');
    $routes->get('raffles/edit/(:num)', 'AdminController::editRaffle/$1');
    $routes->post('raffles/edit/(:num)', 'AdminController::editRaffle/$1');
    $routes->post('raffles/activate/(:num)', 'AdminController::activateRaffle/$1');
    $routes->post('raffles/pause/(:num)', 'AdminController::pauseRaffle/$1');
    $routes->post('raffles/complete/(:num)', 'AdminController::completeRaffle/$1');
    $routes->get('raffles/(:num)/packages', 'AdminController::rafflePackages/$1');
    $routes->post('raffles/(:num)/packages', 'AdminController::rafflePackages/$1');
    $routes->get('raffles/(:num)/prizes', 'AdminController::raffleSpecialPrizes/$1');
    $routes->post('raffles/(:num)/prizes', 'AdminController::raffleSpecialPrizes/$1');
    $routes->get('raffles/(:num)/purchases', 'AdminController::rafflePurchases/$1');
    $routes->get('raffles/draw/(:num)', 'AdminController::drawRaffle/$1');
    $routes->post('raffles/simulate-payment/(:num)', 'AdminController::simulateRafflePayment/$1');

    // ===============================
    // SISTEMA DE BACKUP
    // ===============================
    $routes->get('backup', 'AdminController::backup');
    $routes->post('backup/run', 'AdminController::runBackup');
    $routes->post('backup/auth-code', 'AdminController::authCode');
    $routes->post('backup/disconnect-drive', 'AdminController::disconnectDrive');
    $routes->get('backup/download/(:any)', 'AdminController::downloadBackup/$1');
    $routes->post('backup/delete', 'AdminController::deleteBackup');
    $routes->post('backup/upload-drive', 'AdminController::uploadToDrive');
    $routes->post('backup/settings', 'AdminController::saveBackupSettings');

    // ===============================
    // CONFIGURAÇÕES DA PLATAFORMA
    // ===============================
    $routes->get('settings', 'AdminController::settings');
    $routes->post('settings/save', 'AdminController::saveSettings');

    // ===============================
    // MODERAÇÃO DE COMENTÁRIOS
    // ===============================
    $routes->get('comments', 'AdminController::comments');
    $routes->post('comments/approve/(:num)', 'AdminController::approveComment/$1');
    $routes->post('comments/reject/(:num)', 'AdminController::rejectComment/$1');
    $routes->post('comments/delete/(:num)', 'AdminController::deleteCommentAdmin/$1');
    $routes->post('comments/bulk-approve', 'AdminController::bulkApproveComments');
    $routes->post('comments/bulk-reject', 'AdminController::bulkRejectComments');

    // ===============================
    // RELATÓRIOS E EXPORTAÇÃO
    // ===============================
    $routes->get('reports', 'AdminController::reports');
    $routes->get('export/donations', 'AdminController::exportDonations');
    $routes->get('export/campaigns', 'AdminController::exportCampaigns');
    $routes->get('export/users', 'AdminController::exportUsers');
    $routes->get('export/withdrawals', 'AdminController::exportWithdrawals');
    $routes->get('export/raffles', 'AdminController::exportRaffles');

    // ===============================
    // GERENCIAMENTO DE SAQUES
    // ===============================
    $routes->get('withdrawals', 'WithdrawalController::adminIndex');
    $routes->get('withdrawals/(:num)', 'WithdrawalController::adminDetail/$1');
    $routes->post('withdrawals/approve/(:num)', 'WithdrawalController::approve/$1');
    $routes->post('withdrawals/reject/(:num)', 'WithdrawalController::reject/$1');
});

// =====================================================
// WEBHOOKS (Sem filtro de autenticação - ja definidos acima)
// =====================================================
// Nota: webhook/asaas esta definido na linha 161 usando WebhookController

// =====================================================
// TESTE DE EMAIL (APENAS EM DESENVOLVIMENTO)
// =====================================================

$routes->get('test-email', function() {
    // SEGURANÇA: Apenas em ambiente de desenvolvimento
    if (ENVIRONMENT !== 'development') {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    $email = \Config\Services::email();

    $email->setFrom('contato@doarfazbem.com.br', 'DoarFazBem');
    $email->setTo('doarfazbem.com.br@gmail.com');
    $email->setSubject('Teste de Email - DoarFazBem');
    $email->setMessage('<h1 style="color: #10B981;">Email funcionando!</h1>');

    if ($email->send()) {
        echo '<h2 style="color: green;">Email enviado com sucesso!</h2>';
    } else {
        echo '<h2 style="color: red;">Erro ao enviar email</h2>';
        // NÃO mostrar credenciais
    }
});
