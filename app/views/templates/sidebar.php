<?php
// Ensure session is initialized
if (class_exists('Controller')) {
    Controller::initSession();
}

$accounts = $_SESSION['accounts'] ?? [];
$activeUserId = $_SESSION['active_user_id'] ?? ($_SESSION['user_id'] ?? null);

$activeUser = null;
if (!empty($activeUserId) && !empty($accounts)) {
    $activeUser = $accounts[$activeUserId] 
        ?? $accounts[(string)$activeUserId] 
        ?? $accounts[(int)$activeUserId] 
        ?? null;
}

if (!$activeUser && !empty($_SESSION['user_id'])) {
    $activeUser = [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? '',
        'name' => $_SESSION['name'] ?? $_SESSION['username'] ?? '',
        'avatar' => $_SESSION['avatar'] ?? $_SESSION['profile_picture'] ?? null,
        'profile_picture' => $_SESSION['profile_picture'] ?? $_SESSION['avatar'] ?? null
    ];
}

$activeUserId = (int)($activeUser['id'] ?? 0);
$activeUsername = $activeUser['username'] ?? '';
$activeName = $activeUser['name'] ?? $activeUsername;
$activeAvatar = !empty($activeUser['avatar']) ? $activeUser['avatar'] : (!empty($activeUser['profile_picture']) ? $activeUser['profile_picture'] : null);
$activeInitial = strtoupper(substr($activeUsername ?: 'U', 0, 1));

$currentUrl = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
if (empty($currentUrl) && isset($_SERVER['REQUEST_URI'])) {
    $currentUrl = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '', '/');
}
$currentUrl = $currentUrl ?: 'home';
$urlParts = explode('/', $currentUrl);
$activePage = strtolower($urlParts[0] ?? 'home');

$activeNav = "flex items-center gap-space-md px-space-md py-space-sm transition-colors bg-surface-container text-primary font-title-md rounded-xl border border-outline-variant/40";
$inactiveNav = "flex items-center gap-space-md px-space-md py-space-sm rounded-xl text-on-surface-variant hover:bg-surface-container hover:text-on-surface transition-colors font-title-md border border-transparent";
$unreadCount = $unreadNotifCount ?? ((isset($_SESSION['user_id'])) ? (new Notification_model())->getUnreadCount((int)$_SESSION['user_id']) : 0);
?>

<!-- Left Sidebar (Desktop / Tablet) -->
<aside class="fixed left-0 top-16 bottom-0 w-60 bg-surface-container-low border-r border-outline-variant/30 z-40 hidden md:flex flex-col justify-between p-gutter">
    <div class="flex flex-col gap-space-lg">
        <nav class="flex flex-col gap-space-xs">
            <a class="<?= ($activePage === 'home' || $activePage === '') ? $activeNav : $inactiveNav ?>" href="<?= BASEURL ?>/home">
                <span class="material-symbols-outlined text-xl">home</span><span>Home</span>
            </a>
            <a class="<?= ($activePage === 'explore') ? $activeNav : $inactiveNav ?>" href="<?= BASEURL ?>/explore">
                <span class="material-symbols-outlined text-xl">explore</span><span>Explore</span>
            </a>
            <a class="<?= ($activePage === 'history' || $activePage === 'bookmarks') ? $activeNav : $inactiveNav ?>" href="<?= BASEURL ?>/history">
                <span class="material-symbols-outlined text-xl">history</span><span>History</span>
            </a>
            <a class="<?= ($activePage === 'notifications') ? $activeNav : $inactiveNav ?>" href="<?= BASEURL ?>/notifications">
                <span class="material-symbols-outlined text-xl">notifications</span>
                <span>Notifications</span>
                <?php if($unreadCount > 0): ?>
                    <span class="ml-auto flex items-center justify-center min-w-[22px] h-[22px] px-1.5 bg-error text-on-error font-bold text-[11px] rounded-full shadow-sm">
                        <?= $unreadCount > 99 ? '99+' : $unreadCount ?>
                    </span>
                <?php endif; ?>
            </a>
            <?php 
                $isProfileActive = ($activePage === 'profile') || (!empty($activeUsername) && strtolower($activePage) === strtolower($activeUsername));
            ?>
            <a class="<?= $isProfileActive ? $activeNav : $inactiveNav ?>" href="<?= BASEURL ?>/profile">
                <span class="material-symbols-outlined text-xl">account_circle</span><span>Profile</span>
            </a>
        </nav>
        <?php if (!empty($activeUserId)): ?>
            <a class="flex items-center justify-center gap-space-sm w-full py-space-sm px-space-md rounded-xl bg-primary-container text-on-primary-container font-title-md hover:bg-primary transition-all shadow-[0_0_0_1px_rgba(16,185,129,0.3)]" href="<?= BASEURL ?>/post/create">
                <span class="material-symbols-outlined text-xl">edit_note</span><span>New Story</span>
            </a>
        <?php endif; ?>
    </div>

    <!-- Twitter-style Account Switcher Pill & Dropdown -->
    <?php if (!empty($activeUserId)): ?>
    <div class="relative w-full" id="account-switcher-container">
        <!-- Floating Dropdown Popup (Opens upwards, exactly like Twitter/X) -->
        <div id="account-switcher-dropdown" 
             class="hidden absolute bottom-full left-0 mb-3 w-[270px] bg-surface-container-low/95 backdrop-blur-xl border border-outline-variant/40 rounded-2xl shadow-2xl shadow-black/30 z-50 overflow-hidden py-2 transition-all duration-150 ease-out origin-bottom-left">
            
            <!-- Logged-in Accounts List -->
            <div class="flex flex-col max-h-60 overflow-y-auto divide-y divide-outline-variant/10">
                <?php foreach ($accounts as $accId => $acc): ?>
                    <?php 
                        $isCurrentActive = ((int)$acc['id'] === $activeUserId);
                        $accName = $acc['name'] ?? $acc['username'];
                        $accUsername = $acc['username'] ?? '';
                        $accAvatar = !empty($acc['avatar']) ? $acc['avatar'] : (!empty($acc['profile_picture']) ? $acc['profile_picture'] : null);
                        $accInitial = strtoupper(substr($accUsername ?: 'U', 0, 1));
                    ?>
                    <?php if ($isCurrentActive): ?>
                        <!-- Active Account: Highlighted with checkmark -->
                        <div class="flex items-center gap-3 px-3.5 py-2.5 bg-surface-container/60 cursor-default select-none">
                            <div class="w-9 h-9 rounded-full overflow-hidden shrink-0 ring-2 ring-primary/40 flex items-center justify-center">
                                <?php if (!empty($accAvatar)): ?>
                                    <img src="<?= BASEURL ?><?= htmlspecialchars($accAvatar) ?>" alt="<?= htmlspecialchars($accUsername) ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full rounded-full bg-slate-700 text-white flex items-center justify-center font-bold uppercase text-xs">
                                        <?= htmlspecialchars($accInitial) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex flex-col min-w-0 flex-1">
                                <span class="font-title-md text-sm font-bold text-on-surface truncate leading-tight"><?= htmlspecialchars($accName) ?></span>
                                <span class="font-caption text-xs text-on-surface-variant truncate leading-tight">@<?= htmlspecialchars($accUsername) ?></span>
                            </div>
                            <span class="material-symbols-outlined text-primary text-xl font-bold shrink-0" title="Currently Active">check</span>
                        </div>
                    <?php else: ?>
                        <!-- Other Logged-in Account: Click to switch -->
                        <a href="<?= BASEURL ?>/auth/switchAccount/<?= (int)$acc['id'] ?>" 
                           class="flex items-center gap-3 px-3.5 py-2.5 hover:bg-surface-container transition-colors group">
                            <div class="w-9 h-9 rounded-full overflow-hidden shrink-0 group-hover:ring-2 group-hover:ring-primary/40 transition-all flex items-center justify-center">
                                <?php if (!empty($accAvatar)): ?>
                                    <img src="<?= BASEURL ?><?= htmlspecialchars($accAvatar) ?>" alt="<?= htmlspecialchars($accUsername) ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full rounded-full bg-slate-700 text-white flex items-center justify-center font-bold uppercase text-xs">
                                        <?= htmlspecialchars($accInitial) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex flex-col min-w-0 flex-1">
                                <span class="font-title-md text-sm font-medium text-on-surface group-hover:text-primary transition-colors truncate leading-tight"><?= htmlspecialchars($accName) ?></span>
                                <span class="font-caption text-xs text-on-surface-variant truncate leading-tight">@<?= htmlspecialchars($accUsername) ?></span>
                            </div>
                            <span class="material-symbols-outlined text-outline group-hover:text-primary text-lg opacity-0 group-hover:opacity-100 transition-opacity shrink-0">sync_alt</span>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- Divider -->
            <div class="h-px bg-outline-variant/30 my-1.5 mx-2"></div>

            <!-- Add an existing account -->
            <a href="<?= BASEURL ?>/auth?add=1" 
               class="flex items-center gap-3 px-4 py-2.5 text-sm font-title-md text-on-surface hover:bg-surface-container transition-colors group">
                <span class="material-symbols-outlined text-[20px] text-on-surface-variant group-hover:text-primary transition-colors">person_add</span>
                <span class="group-hover:text-primary transition-colors">Add an existing account</span>
            </a>

            <!-- Log out @[active_username] -->
            <a href="<?= BASEURL ?>/auth/logout" 
               class="flex items-center gap-3 px-4 py-2.5 text-sm font-title-md text-on-surface hover:bg-error-container/20 hover:text-error transition-colors group">
                <span class="material-symbols-outlined text-[20px] text-on-surface-variant group-hover:text-error transition-colors">logout</span>
                <span class="truncate">Log out @<?= htmlspecialchars($activeUsername) ?></span>
            </a>
        </div>

        <!-- Twitter-style Trigger Button -->
        <button id="account-switcher-trigger" 
                type="button" 
                aria-haspopup="true"
                aria-expanded="false"
                class="w-full flex items-center gap-space-sm p-2 rounded-2xl bg-surface-container border border-outline-variant/20 hover:border-primary/40 hover:bg-surface-container-high transition-all text-left group cursor-pointer focus:outline-none focus:ring-1 focus:ring-primary/40">
            <div class="w-9 h-9 rounded-full overflow-hidden shrink-0 shadow-sm flex items-center justify-center">
                <?php if (!empty($activeAvatar)): ?>
                    <img src="<?= BASEURL ?><?= htmlspecialchars($activeAvatar) ?>" alt="<?= htmlspecialchars($activeUsername) ?>" class="w-full h-full object-cover">
                <?php else: ?>
                    <div class="w-full h-full rounded-full bg-slate-700 text-white flex items-center justify-center font-bold uppercase text-xs">
                        <?= htmlspecialchars($activeInitial) ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="flex flex-col min-w-0 flex-1">
                <span class="font-title-md text-xs sm:text-sm font-semibold text-on-surface truncate leading-tight"><?= htmlspecialchars($activeName) ?></span>
                <span class="font-caption text-xs text-on-surface-variant truncate leading-tight">@<?= htmlspecialchars($activeUsername) ?></span>
            </div>
            <span class="material-symbols-outlined text-outline text-xl group-hover:text-on-surface transition-colors shrink-0">more_horiz</span>
        </button>
    </div>

    <!-- Toggle Script for Account Switcher Dropdown -->
    <script>
    (function() {
        const trigger = document.getElementById('account-switcher-trigger');
        const dropdown = document.getElementById('account-switcher-dropdown');
        const container = document.getElementById('account-switcher-container');

        if (!trigger || !dropdown) return;

        function openDropdown() {
            dropdown.classList.remove('hidden');
            trigger.setAttribute('aria-expanded', 'true');
        }

        function closeDropdown() {
            dropdown.classList.add('hidden');
            trigger.setAttribute('aria-expanded', 'false');
        }

        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            if (dropdown.classList.contains('hidden')) {
                openDropdown();
            } else {
                closeDropdown();
            }
        });

        // Close on clicking outside
        document.addEventListener('click', function(e) {
            if (container && !container.contains(e.target)) {
                closeDropdown();
            }
        });

        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !dropdown.classList.contains('hidden')) {
                closeDropdown();
            }
        });
    })();
    </script>
    <?php endif; ?>
</aside>
