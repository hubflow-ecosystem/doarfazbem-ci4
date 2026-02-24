<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Gerenciar Usuarios</h1>
                    <p class="mt-2 text-gray-600">Visualize e gerencie todos os usuarios da plataforma</p>
                </div>
                <a href="<?= base_url('admin/dashboard') ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar ao Dashboard
                </a>
            </div>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-r-lg">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                <span class="text-green-700"><?= session()->getFlashdata('success') ?></span>
            </div>
        </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                <span class="text-red-700"><?= session()->getFlashdata('error') ?></span>
            </div>
        </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <a href="<?= base_url('admin/users?status=all') ?>"
               class="bg-white rounded-lg shadow p-6 hover:shadow-md transition <?= ($current_status ?? 'all') === 'all' ? 'ring-2 ring-primary-500' : '' ?>">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-full p-3">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total</p>
                        <p class="text-2xl font-semibold text-gray-900"><?= $stats['total'] ?? count($users) ?></p>
                    </div>
                </div>
            </a>

            <a href="<?= base_url('admin/users?status=active') ?>"
               class="bg-white rounded-lg shadow p-6 hover:shadow-md transition <?= ($current_status ?? 'all') === 'active' ? 'ring-2 ring-green-500' : '' ?>">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-full p-3">
                        <i class="fas fa-user-check text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Ativos</p>
                        <p class="text-2xl font-semibold text-green-600"><?= $stats['active'] ?? 0 ?></p>
                    </div>
                </div>
            </a>

            <a href="<?= base_url('admin/users?status=suspended') ?>"
               class="bg-white rounded-lg shadow p-6 hover:shadow-md transition <?= ($current_status ?? 'all') === 'suspended' ? 'ring-2 ring-yellow-500' : '' ?>">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-full p-3">
                        <i class="fas fa-user-clock text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Suspensos</p>
                        <p class="text-2xl font-semibold text-yellow-600"><?= $stats['suspended'] ?? 0 ?></p>
                    </div>
                </div>
            </a>

            <a href="<?= base_url('admin/users?status=banned') ?>"
               class="bg-white rounded-lg shadow p-6 hover:shadow-md transition <?= ($current_status ?? 'all') === 'banned' ? 'ring-2 ring-red-500' : '' ?>">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-100 rounded-full p-3">
                        <i class="fas fa-user-slash text-red-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Banidos</p>
                        <p class="text-2xl font-semibold text-red-600"><?= $stats['banned'] ?? 0 ?></p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Users Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Lista de Usuarios</h3>
                    <div class="relative">
                        <input type="text" id="searchUsers" placeholder="Buscar usuario..."
                               class="pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campanhas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cadastro</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="usersTable">
                        <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-gray-50 user-row">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <?php if (!empty($user['avatar'])): ?>
                                            <img class="h-10 w-10 rounded-full object-cover" src="<?= $user['avatar'] ?>" alt="">
                                        <?php else: ?>
                                            <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center">
                                                <span class="text-primary-600 font-medium text-sm">
                                                    <?= strtoupper(substr($user['name'], 0, 2)) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 user-name"><?= esc($user['name']) ?></div>
                                        <div class="text-sm text-gray-500">ID: <?= $user['id'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 user-email"><?= esc($user['email']) ?></div>
                                <?php if (!empty($user['google_id'])): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fab fa-google mr-1"></i> Google
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if (in_array($user['role'] ?? '', ['admin', 'superadmin'])): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        <i class="fas fa-shield-alt mr-1"></i> Admin
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <i class="fas fa-user mr-1"></i> Usuario
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $status = $user['status'] ?? 'active';
                                $statusClass = match($status) {
                                    'active' => 'bg-green-100 text-green-800',
                                    'suspended' => 'bg-yellow-100 text-yellow-800',
                                    'banned' => 'bg-red-100 text-red-800',
                                    default => 'bg-green-100 text-green-800'
                                };
                                $statusIcon = match($status) {
                                    'active' => 'fa-check-circle',
                                    'suspended' => 'fa-pause-circle',
                                    'banned' => 'fa-ban',
                                    default => 'fa-check-circle'
                                };
                                $statusLabel = match($status) {
                                    'active' => 'Ativo',
                                    'suspended' => 'Suspenso',
                                    'banned' => 'Banido',
                                    default => 'Ativo'
                                };
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClass ?>">
                                    <i class="fas <?= $statusIcon ?> mr-1"></i> <?= $statusLabel ?>
                                </span>
                                <?php if (!empty($user['suspension_reason']) && $status !== 'active'): ?>
                                    <button type="button" onclick="showReason('<?= esc($user['suspension_reason'], 'js') ?>')"
                                            class="ml-1 text-gray-400 hover:text-gray-600" title="Ver motivo">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= $user['campaigns_count'] ?? 0 ?> campanhas
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <?php
                                    $userStatus = $user['status'] ?? 'active';
                                    $isAdmin = in_array($user['role'] ?? '', ['admin', 'superadmin']);
                                    ?>

                                    <?php if (!$isAdmin): ?>
                                        <?php if ($userStatus === 'active'): ?>
                                            <!-- Suspender -->
                                            <button type="button" onclick="openSuspendModal(<?= $user['id'] ?>, '<?= esc($user['name'], 'js') ?>')"
                                                    class="text-yellow-600 hover:text-yellow-900 p-1" title="Suspender">
                                                <i class="fas fa-pause-circle"></i>
                                            </button>
                                            <!-- Banir -->
                                            <button type="button" onclick="openBanModal(<?= $user['id'] ?>, '<?= esc($user['name'], 'js') ?>')"
                                                    class="text-red-600 hover:text-red-900 p-1" title="Banir">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        <?php elseif ($userStatus === 'suspended'): ?>
                                            <!-- Reativar -->
                                            <form action="<?= base_url('admin/users/reactivate/' . $user['id']) ?>" method="POST" class="inline">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="text-green-600 hover:text-green-900 p-1" title="Reativar"
                                                        onclick="return confirm('Reativar este usuario?')">
                                                    <i class="fas fa-play-circle"></i>
                                                </button>
                                            </form>
                                            <!-- Banir permanentemente -->
                                            <button type="button" onclick="openBanModal(<?= $user['id'] ?>, '<?= esc($user['name'], 'js') ?>')"
                                                    class="text-red-600 hover:text-red-900 p-1" title="Banir permanentemente">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        <?php elseif ($userStatus === 'banned'): ?>
                                            <!-- Reativar (remover ban) -->
                                            <form action="<?= base_url('admin/users/reactivate/' . $user['id']) ?>" method="POST" class="inline">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="text-green-600 hover:text-green-900 p-1" title="Remover ban"
                                                        onclick="return confirm('Remover o banimento deste usuario?')">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs">Admin</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (empty($users)): ?>
            <div class="text-center py-12">
                <i class="fas fa-users text-gray-300 text-5xl mb-4"></i>
                <p class="text-gray-500">Nenhum usuario encontrado</p>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- Modal Suspender -->
<div id="suspendModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
        <form id="suspendForm" method="POST">
            <?= csrf_field() ?>
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-pause-circle text-yellow-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Suspender Usuario</h3>
                        <p class="text-sm text-gray-500" id="suspendUserName"></p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Motivo da suspensao *</label>
                    <textarea name="reason" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                              placeholder="Explique o motivo da suspensao..." required></textarea>
                    <p class="text-xs text-gray-500 mt-1">O usuario sera notificado por email.</p>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 rounded-b-xl">
                <button type="button" onclick="closeSuspendModal()" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                    Suspender
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Banir -->
<div id="banModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
        <form id="banForm" method="POST">
            <?= csrf_field() ?>
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-ban text-red-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Banir Usuario Permanentemente</h3>
                        <p class="text-sm text-gray-500" id="banUserName"></p>
                    </div>
                </div>

                <div class="bg-red-50 border-l-4 border-red-500 p-3 mb-4 rounded-r">
                    <p class="text-sm text-red-700">
                        <strong>Atencao:</strong> Esta acao banira o usuario permanentemente. Ele nao podera mais acessar a plataforma.
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Motivo do banimento *</label>
                    <textarea name="reason" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:border-transparent"
                              placeholder="Explique o motivo do banimento..." required></textarea>
                    <p class="text-xs text-gray-500 mt-1">O usuario sera notificado por email.</p>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 rounded-b-xl">
                <button type="button" onclick="closeBanModal()" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Banir Permanentemente
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Busca de usuarios
document.getElementById('searchUsers').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('.user-row');

    rows.forEach(row => {
        const name = row.querySelector('.user-name').textContent.toLowerCase();
        const email = row.querySelector('.user-email').textContent.toLowerCase();

        if (name.includes(searchTerm) || email.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Modal Suspender
function openSuspendModal(userId, userName) {
    document.getElementById('suspendForm').action = '<?= base_url('admin/users/suspend/') ?>' + userId;
    document.getElementById('suspendUserName').textContent = userName;
    document.getElementById('suspendModal').classList.remove('hidden');
}

function closeSuspendModal() {
    document.getElementById('suspendModal').classList.add('hidden');
    document.querySelector('#suspendForm textarea[name="reason"]').value = '';
}

// Modal Banir
function openBanModal(userId, userName) {
    document.getElementById('banForm').action = '<?= base_url('admin/users/ban/') ?>' + userId;
    document.getElementById('banUserName').textContent = userName;
    document.getElementById('banModal').classList.remove('hidden');
}

function closeBanModal() {
    document.getElementById('banModal').classList.add('hidden');
    document.querySelector('#banForm textarea[name="reason"]').value = '';
}

// Mostrar motivo
function showReason(reason) {
    alert('Motivo: ' + reason);
}

// Fechar modais com ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeSuspendModal();
        closeBanModal();
    }
});
</script>

<?= $this->endSection() ?>
