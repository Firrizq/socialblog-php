<?php
// Ensure PHP uses the correct local timezone to match the database
date_default_timezone_set('Asia/Jakarta');

if (!function_exists('timeAgo')) {
    function timeAgo($timestamp) {
        $time = strtotime($timestamp);
        $diff = time() - $time;

        // Catch negative differences or immediate posts
        if ($diff < 1) return 'now';
        if ($diff < 60) return $diff . 's';
        if ($diff < 3600) return floor($diff / 60) . 'm';
        if ($diff < 86400) return floor($diff / 3600) . 'h';
        
        return date('Y', $time) === date('Y') ? date('M j', $time) : date('M j, Y', $time);
    }
}

require_once dirname(__DIR__, 2) . '/models/Notification_model.php';
$unreadNotifCount = (isset($_SESSION['user_id'])) ? (new Notification_model())->getUnreadCount((int)$_SESSION['user_id']) : 0;

// Active User Extraction for multi-account support
$activeUserId = $_SESSION['active_user_id'] ?? ($_SESSION['user_id'] ?? null);
$activeUser = null;
if (!empty($activeUserId) && !empty($_SESSION['accounts'])) {
    $activeUser = $_SESSION['accounts'][$activeUserId] 
        ?? $_SESSION['accounts'][(string)$activeUserId] 
        ?? $_SESSION['accounts'][(int)$activeUserId] 
        ?? null;
}
if (!$activeUser && !empty($_SESSION['user_id'])) {
    $activeUser = [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['name'] ?? $_SESSION['username'] ?? 'User',
        'username' => $_SESSION['username'] ?? 'user',
        'avatar' => $_SESSION['avatar'] ?? $_SESSION['profile_picture'] ?? null,
        'profile_picture' => $_SESSION['profile_picture'] ?? $_SESSION['avatar'] ?? null
    ];
}
?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= htmlspecialchars($data['title'] ?? 'Blogggle') ?></title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/> 
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <!-- Quill.js CSS -->
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.6/dist/quill.snow.css" rel="stylesheet" crossorigin="anonymous">

    <style>
        @layer base {
            html, body { margin: 0; padding: 0; }
            body { overscroll-behavior: none; }
            main > :first-child { margin-top: 0 !important; }
            main > :last-child { margin-bottom: 0 !important; }
            
            /* Light Theme (Editorial & Clean) */
            :root {
                --color-primary: 16 185 129; /* #10b981 */
                --color-primary-container: 209 250 229; /* #d1fae5 */
                --color-on-primary-container: 6 95 70; /* #065f46 */
                --color-surface: 255 255 255; /* #ffffff */
                --color-surface-container-lowest: 255 255 255; /* #ffffff */
                --color-surface-container-low: 248 250 252; /* #f8fafc */
                --color-surface-container: 241 245 249; /* #f1f5f9 */
                --color-surface-container-high: 226 232 240; /* #e2e8f0 */
                --color-on-surface: 15 23 42; /* #0f172a */
                --color-on-surface-variant: 71 85 105; /* #475569 */
                --color-outline-variant: 226 232 240; /* #e2e8f0 */
                --color-error: 239 68 68; /* #ef4444 */
                --color-error-container: 254 226 226; /* #fee2e2 */
            }

            /* Dark Theme (Obsidian Emerald) */
            .dark {
                --color-primary: 78 222 163; /* #4edea3 */
                --color-primary-container: 16 185 129; /* #10b981 */
                --color-on-primary-container: 0 66 43; /* #00422b */
                --color-surface: 11 19 38; /* #0b1326 */
                --color-surface-container-lowest: 6 14 32; /* #060e20 */
                --color-surface-container-low: 19 27 46; /* #131b2e */
                --color-surface-container: 23 31 51; /* #171f33 */
                --color-surface-container-high: 34 42 61; /* #222a3d */
                --color-on-surface: 218 226 253; /* #dae2fd */
                --color-on-surface-variant: 187 202 191; /* #bbcabf */
                --color-outline-variant: 60 74 66; /* #3c4a42 */
                --color-error: 255 180 171; /* #ffb4ab */
                --color-error-container: 147 0 10; /* #93000a */
            }
        }
        ::-webkit-scrollbar { display: none; }
        
        /* Quill Dark/Light Mode Adjustments */
        .ql-toolbar.ql-snow { background: rgb(var(--color-surface-container-low)); border-color: rgb(var(--color-outline-variant)) !important; border-top-left-radius: 1rem; border-top-right-radius: 1rem; padding: 0.75rem !important; }
        .ql-container.ql-snow { background: rgb(var(--color-surface-container-lowest)); border-color: rgb(var(--color-outline-variant)) !important; border-bottom-left-radius: 1rem; border-bottom-right-radius: 1rem; color: rgb(var(--color-on-surface)); font-family: 'Inter', sans-serif; font-size: 1.125rem; min-height: 300px; }
        .ql-snow .ql-stroke { stroke: rgb(var(--color-on-surface-variant)) !important; }
        .ql-snow .ql-fill { fill: rgb(var(--color-on-surface-variant)) !important; }
        .ql-snow .ql-picker { color: rgb(var(--color-on-surface-variant)) !important; }
        .ql-snow .ql-picker-options { background-color: rgb(var(--color-surface-container-high)) !important; border-color: rgb(var(--color-outline-variant)) !important; }
        .ql-snow.ql-toolbar button:hover .ql-stroke, .ql-snow.ql-toolbar button:focus .ql-stroke, .ql-snow.ql-toolbar button.ql-active .ql-stroke { stroke: rgb(var(--color-primary)) !important; }
        .ql-snow.ql-toolbar button:hover .ql-fill, .ql-snow.ql-toolbar button:focus .ql-fill, .ql-snow.ql-toolbar button.ql-active .ql-fill { fill: rgb(var(--color-primary)) !important; }
        .ql-editor.ql-blank::before { color: rgb(var(--color-on-surface-variant) / 0.5) !important; font-style: normal !important; }
    </style>
    <meta name="theme-color" content="#ffffff" id="meta-theme-color">

    <script>
        const savedTheme = localStorage.getItem('blogggle-theme') || 'system';
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (savedTheme === 'dark' || (savedTheme === 'system' && prefersDark)) {
            document.documentElement.classList.add('dark');
            const metaTheme = document.getElementById('meta-theme-color');
            if(metaTheme) metaTheme.setAttribute('content', '#0b1326');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <script src="https://cdn.tailwindcss.com"></script>
    <script id="tailwind-config">
        tailwind.config = {
            "darkMode": "class",
            "theme": {
                "extend": {
                    "colors": {
                        "primary": "rgb(var(--color-primary) / <alpha-value>)",
                        "primary-container": "rgb(var(--color-primary-container) / <alpha-value>)",
                        "on-primary-container": "rgb(var(--color-on-primary-container) / <alpha-value>)",
                        "surface": "rgb(var(--color-surface) / <alpha-value>)",
                        "surface-container-lowest": "rgb(var(--color-surface-container-lowest) / <alpha-value>)",
                        "surface-container-low": "rgb(var(--color-surface-container-low) / <alpha-value>)",
                        "surface-container": "rgb(var(--color-surface-container) / <alpha-value>)",
                        "surface-container-high": "rgb(var(--color-surface-container-high) / <alpha-value>)",
                        "on-surface": "rgb(var(--color-on-surface) / <alpha-value>)",
                        "on-surface-variant": "rgb(var(--color-on-surface-variant) / <alpha-value>)",
                        "outline-variant": "rgb(var(--color-outline-variant) / <alpha-value>)",
                        "error": "rgb(var(--color-error) / <alpha-value>)",
                        "error-container": "rgb(var(--color-error-container) / <alpha-value>)"
                    },
                    "spacing": { "space-sm": "0.5rem", "space-xs": "0.25rem", "gutter": "1.5rem", "space-md": "1rem", "space-lg": "1.5rem" },
                    "fontFamily": {
                        "body-lg": ["Inter", "sans-serif"], "title-md": ["Inter", "sans-serif"], "headline-sm": ["Inter", "sans-serif"], "body-md": ["Inter", "sans-serif"], "headline-lg": ["Inter", "sans-serif"], "caption": ["Inter", "sans-serif"], "label-md": ["Inter", "sans-serif"]
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-surface text-on-surface min-h-screen">

    <!-- Top Header -->
    <header class="fixed top-0 left-0 right-0 h-16 bg-surface-container-low/95 backdrop-blur-xl border-b border-outline-variant/30 z-50 flex items-center justify-between px-gutter">
        <div class="flex items-center gap-space-sm">
            <span class="material-symbols-outlined text-primary text-3xl">edit_square</span>
            <span class="font-title-md text-title-md text-on-surface tracking-tight">Blogggle</span>
        </div>
        <div class="flex items-center gap-2 sm:gap-3">
            <!-- Theme Switcher -->
            <div class="relative dropdown-container">
                <button type="button" onclick="toggleMenu(event, 'theme-menu')" class="w-9 h-9 rounded-full bg-surface border border-outline-variant/50 text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low flex items-center justify-center transition-all shadow-sm" id="theme-toggle-btn" title="Theme Settings">
                    <span class="material-symbols-outlined text-[18px]" id="theme-icon">dark_mode</span>
                </button>
                <div id="theme-menu" class="hidden absolute right-0 top-full mt-2 w-36 bg-surface-container-low border border-outline-variant/30 rounded-xl shadow-xl z-[60] overflow-hidden flex flex-col py-1.5">
                    <button onclick="setTheme('light')" class="w-full text-left px-4 py-2.5 text-sm text-on-surface font-title-md hover:bg-surface-container flex items-center gap-3 transition-colors group">
                        <span class="material-symbols-outlined text-[18px] text-on-surface-variant group-hover:text-primary">light_mode</span> Light
                    </button>
                    <button onclick="setTheme('dark')" class="w-full text-left px-4 py-2.5 text-sm text-on-surface font-title-md hover:bg-surface-container flex items-center gap-3 transition-colors group">
                        <span class="material-symbols-outlined text-[18px] text-on-surface-variant group-hover:text-primary">dark_mode</span> Dark
                    </button>
                    <button onclick="setTheme('system')" class="w-full text-left px-4 py-2.5 text-sm text-on-surface font-title-md hover:bg-surface-container flex items-center gap-3 transition-colors group">
                        <span class="material-symbols-outlined text-[18px] text-on-surface-variant group-hover:text-primary">desktop_windows</span> System
                    </button>
                </div>
            </div>

            <?php if (!empty($activeUser['id'])): ?>
                <!-- Write Button: Editorial Style (Inverted Contrast) -->
                <a class="hidden sm:inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-on-surface text-surface font-title-md text-sm hover:opacity-80 transition-opacity shadow-sm ml-1" href="<?= BASEURL ?>/post/create">
                    <span class="material-symbols-outlined text-[18px]">edit_square</span><span>Write</span>
                </a>
                
                <div class="h-5 w-px bg-outline-variant/60 mx-1 hidden sm:block"></div>

                <!-- Profile Avatar -->
                <?php 
                    $userAvatar = !empty($activeUser['avatar']) ? $activeUser['avatar'] : (!empty($activeUser['profile_picture']) ? $activeUser['profile_picture'] : null);
                    $initial = strtoupper(substr($activeUser['username'] ?? 'U', 0, 1));
                ?>
                <a href="<?= BASEURL ?>/profile" class="w-9 h-9 rounded-full overflow-hidden flex items-center justify-center bg-surface-container-high border border-outline-variant/50 font-bold text-xs text-on-surface shrink-0 hover:ring-2 hover:ring-primary transition-all">
                    <?php if (!empty($userAvatar)): ?>
                        <img src="<?= BASEURL ?><?= htmlspecialchars($userAvatar) ?>" alt="<?= htmlspecialchars($activeUser['username'] ?? '') ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="w-full h-full rounded-full bg-slate-700 text-white flex items-center justify-center font-bold uppercase text-xs">
                            <?= htmlspecialchars($initial) ?>
                        </div>
                    <?php endif; ?>
                </a>
                
                <!-- Logout: Subtle Icon Button -->
                <a href="<?= BASEURL ?>/auth/logout" class="hidden sm:flex w-9 h-9 rounded-full items-center justify-center text-on-surface-variant hover:text-error hover:bg-error-container/20 transition-colors" title="Logout">
                    <span class="material-symbols-outlined text-[20px]">logout</span>
                </a>
            <?php else: ?>
                <a href="<?= BASEURL ?>/auth" class="px-4 py-1.5 rounded-full bg-on-surface text-surface font-title-md text-sm hover:opacity-80 transition-opacity shadow-sm ml-2">Sign In</a>
            <?php endif; ?>
        </div>

        <script>
            function updateThemeIcon(theme) {
                const icon = document.getElementById('theme-icon');
                if (!icon) return;
                if (theme === 'light') icon.textContent = 'light_mode';
                else if (theme === 'dark') icon.textContent = 'dark_mode';
                else icon.textContent = 'desktop_windows';
            }

            function applyTheme(theme) {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const isDark = theme === 'dark' || (theme === 'system' && prefersDark);
                if (isDark) {
                    document.documentElement.classList.add('dark');
                    const metaEl = document.getElementById('meta-theme-color');
                    if (metaEl) metaEl.setAttribute('content', '#0b1326');
                } else {
                    document.documentElement.classList.remove('dark');
                    const metaEl = document.getElementById('meta-theme-color');
                    if (metaEl) metaEl.setAttribute('content', '#ffffff');
                }
            }

            window.setTheme = function(theme) {
                localStorage.setItem('blogggle-theme', theme);
                applyTheme(theme);
                updateThemeIcon(theme);
                const menu = document.getElementById('theme-menu');
                if(menu) menu.classList.add('hidden');
            };

            // Cross-tab synchronization
            window.addEventListener('storage', (e) => {
                if (e.key === 'blogggle-theme') {
                    applyTheme(e.newValue);
                    updateThemeIcon(e.newValue);
                }
            });

            // Listen for OS system theme changes
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                if (localStorage.getItem('blogggle-theme') === 'system' || !localStorage.getItem('blogggle-theme')) {
                    applyTheme('system');
                }
            });

            // Init icon on load
            updateThemeIcon(localStorage.getItem('blogggle-theme') || 'system');

            // Global Non-Blocking Toast Notification System (Twitter/Substack Style)
            window.showToast = function(message, type = 'success') {
                if (!message) return;

                let container = document.getElementById('toast-container');
                if (!container) {
                    container = document.createElement('div');
                    container.id = 'toast-container';
                    container.className = 'fixed bottom-5 left-1/2 -translate-x-1/2 sm:left-auto sm:right-6 sm:translate-x-0 z-[9999] flex flex-col gap-2.5 pointer-events-none max-w-[90vw] sm:max-w-md w-max';
                    document.body.appendChild(container);
                }

                const toast = document.createElement('div');
                const isError = type === 'error';
                const isInfo = type === 'info';
                const isWarning = type === 'warning';

                let iconName = 'check_circle';
                let iconColor = 'text-primary';
                let bgClasses = 'bg-surface-container-high/95 text-on-surface border-outline-variant/40';

                if (isError) {
                    iconName = 'error';
                    iconColor = 'text-error';
                    bgClasses = 'bg-surface-container-high/95 text-on-surface border-error/40';
                } else if (isInfo) {
                    iconName = 'info';
                    iconColor = 'text-sky-400';
                    bgClasses = 'bg-surface-container-high/95 text-on-surface border-sky-500/30';
                } else if (isWarning) {
                    iconName = 'warning';
                    iconColor = 'text-amber-400';
                    bgClasses = 'bg-surface-container-high/95 text-on-surface border-amber-500/30';
                }

                toast.className = `pointer-events-auto flex items-center gap-2.5 px-4 py-3 rounded-2xl shadow-2xl backdrop-blur-xl border font-title-md text-sm transition-all duration-300 ease-out transform translate-y-4 opacity-0 scale-95 cursor-pointer select-none ${bgClasses}`;
                
                toast.innerHTML = `
                    <span class="material-symbols-outlined text-[20px] shrink-0 ${iconColor}" style="font-variation-settings: 'FILL' 1;">${iconName}</span>
                    <span class="leading-snug break-words">${message}</span>
                    <button type="button" class="ml-2 -mr-1 text-on-surface-variant hover:text-on-surface transition-colors shrink-0" aria-label="Dismiss">
                        <span class="material-symbols-outlined text-base">close</span>
                    </button>
                `;

                container.appendChild(toast);

                // Animate in
                requestAnimationFrame(() => {
                    toast.classList.remove('translate-y-4', 'opacity-0', 'scale-95');
                    toast.classList.add('translate-y-0', 'opacity-100', 'scale-100');
                });

                // Auto dismiss after 3 seconds
                let dismissed = false;
                const dismiss = () => {
                    if (dismissed) return;
                    dismissed = true;
                    toast.classList.remove('translate-y-0', 'opacity-100', 'scale-100');
                    toast.classList.add('translate-y-2', 'opacity-0', 'scale-95');
                    setTimeout(() => toast.remove(), 250);
                };

                toast.addEventListener('click', dismiss);
                setTimeout(dismiss, 3000);
            };

            function showToast(message, type = 'success') {
                window.showToast(message, type);
            }

            // Route any native alert() popups to floating toast system
            window.alert = function(message) {
                if (!message) return;
                const msgStr = String(message);
                const isErr = /error|failed|invalid|too large|required|cannot/i.test(msgStr);
                window.showToast(msgStr, isErr ? 'error' : 'info');
            };

            // Trigger PHP Session Flash Messages as floating toasts on page load
            <?php if (!empty($_SESSION['flash_message'])): ?>
                window.addEventListener('DOMContentLoaded', () => {
                    window.showToast(<?= json_encode($_SESSION['flash_message']) ?>, <?= json_encode($_SESSION['flash_type'] ?? 'success') ?>);
                });
                <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
            <?php endif; ?>
            <?php if (!empty($_SESSION['flash_success'])): ?>
                window.addEventListener('DOMContentLoaded', () => {
                    window.showToast(<?= json_encode($_SESSION['flash_success']) ?>, 'success');
                });
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>
            <?php if (!empty($_SESSION['flash_error'])): ?>
                window.addEventListener('DOMContentLoaded', () => {
                    window.showToast(<?= json_encode($_SESSION['flash_error']) ?>, 'error');
                });
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>
        </script>
    </header>

    <!-- Left Navigation Sidebar -->
    <?php require __DIR__ . '/sidebar.php'; ?>

    <!-- Main Content Wrapper -->
    <div class="md:pl-60 pt-16">
        <div class="max-w-7xl mx-auto flex flex-col xl:flex-row justify-between">
            <!-- TAHAP 1: BAGIAN TENGAH (Feed / Profile / Content) -->
            <main class="flex-1 min-w-0 max-w-3xl mx-auto w-full border-x border-outline-variant/30 min-h-screen">