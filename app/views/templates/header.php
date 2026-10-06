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
    
    <!-- Fonts & Icons: Editorial Serif (Newsreader) & Modern Clean UI (Plus Jakarta Sans, Inter) -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,400..700;1,6..72,400..700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/> 
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

    <style>
        @layer base {
            html, body { margin: 0; padding: 0; }
            body { 
                overscroll-behavior: none;
                font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, sans-serif;
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
            }
            main > :first-child { margin-top: 0 !important; }
            main > :last-child { margin-bottom: 0 !important; }
            
            /* Warm Independent Press Theme (Paper & Ink) */
            :root {
                --color-primary: 15 118 110; /* #0f766e warm spruce emerald */
                --color-primary-container: 204 251 241; /* #ccfbf1 warm mint */
                --color-on-primary: 255 255 255; /* #ffffff crisp clean white on spruce emerald */
                --color-on-primary-container: 17 94 89; /* #115e59 */
                --color-surface: 251 249 245; /* #fbf9f5 warm alabaster/paper */
                --color-surface-container-lowest: 255 255 255; /* #ffffff clean floating card */
                --color-surface-container-low: 245 242 235; /* #f5f2eb warm ivory surface */
                --color-surface-container: 238 233 223; /* #eee9df warm stone container */
                --color-surface-container-high: 228 221 209; /* #e4ddd1 tactile hover */
                --color-on-surface: 28 25 23; /* #1c1918 rich book ink */
                --color-on-surface-variant: 87 83 78; /* #57534e warm charcoal stone */
                --color-outline-variant: 230 224 213; /* #e6e0d5 warm deckle border */
                --color-error: 225 29 72; /* #e11d48 warm crimson */
                --color-error-container: 255 228 230; /* #ffe4e6 */
            }

            /* Nocturne Paper / Obsidian Ink Theme */
            .dark {
                --color-primary: 52 211 153; /* #34d399 radiant jade */
                --color-primary-container: 6 78 59; /* #064e3b deep forest */
                --color-on-primary: 13 14 16; /* #0d0e10 deep obsidian ink on radiant jade */
                --color-on-primary-container: 167 243 208; /* #a7f3d0 */
                --color-surface: 19 20 23; /* #131417 warm nocturne obsidian */
                --color-surface-container-lowest: 13 14 16; /* #0d0e10 deep card */
                --color-surface-container-low: 26 27 31; /* #1a1b1f elevated card */
                --color-surface-container: 34 35 41; /* #222329 warm charcoal */
                --color-surface-container-high: 46 48 56; /* #2e3038 */
                --color-on-surface: 244 242 237; /* #f4f2ed warm candlelight text */
                --color-on-surface-variant: 168 162 153; /* #a8a299 warm mineral stone */
                --color-outline-variant: 48 50 58; /* #30323a subtle graphite border */
                --color-error: 251 113 133; /* #fb7185 */
                --color-error-container: 136 19 55; /* #881337 */
            }
        }
        ::-webkit-scrollbar { display: none; }
        
        /* Modern Typography Helpers */
        .text-wrap-balance { text-wrap: balance; }
        .text-wrap-pretty { text-wrap: pretty; }
        .font-serif, .font-display, .font-editorial { font-family: 'Newsreader', Georgia, serif; }
        
        /* ================= Editorial WYSIWYG Editor Styles ================= */
        .editorial-editor-canvas {
            font-family: 'Newsreader', Georgia, serif;
            font-size: 1.25rem;
            line-height: 1.85;
            color: rgb(var(--color-on-surface));
            word-break: break-word;
        }
        .editorial-editor-canvas:focus {
            outline: none;
        }
        .editorial-editor-canvas p {
            margin-bottom: 1.5rem;
            text-wrap: pretty;
        }
        .editorial-editor-canvas h1 {
            font-size: 2.25rem;
            font-weight: 800;
            line-height: 1.25;
            margin-top: 2rem;
            margin-bottom: 1rem;
            color: rgb(var(--color-on-surface));
        }
        .editorial-editor-canvas h2 {
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1.3;
            margin-top: 1.75rem;
            margin-bottom: 0.75rem;
            color: rgb(var(--color-on-surface));
        }
        .editorial-editor-canvas h3 {
            font-size: 1.4rem;
            font-weight: 600;
            line-height: 1.35;
            margin-top: 1.5rem;
            margin-bottom: 0.5rem;
            color: rgb(var(--color-on-surface));
        }
        .editorial-editor-canvas blockquote {
            border-left: 3px solid rgb(var(--color-primary));
            padding-left: 1.25rem;
            margin: 1.5rem 0;
            font-style: italic;
            color: rgb(var(--color-on-surface-variant));
        }
        .editorial-editor-canvas pre, .editorial-code-block {
            background-color: rgb(var(--color-surface-container-high));
            border: 1px solid rgb(var(--color-outline-variant) / 0.5);
            border-radius: 0.75rem;
            padding: 1rem 1.25rem;
            margin: 1.5rem 0;
            overflow-x: auto;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.95rem;
            line-height: 1.6;
        }
        .editorial-editor-canvas code:not(pre code) {
            background-color: rgb(var(--color-surface-container-high));
            padding: 0.2rem 0.4rem;
            border-radius: 0.375rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.9em;
        }
        .editorial-editor-canvas ul {
            list-style-type: disc;
            margin-left: 1.75rem;
            margin-bottom: 1.5rem;
        }
        .editorial-editor-canvas ol {
            list-style-type: decimal;
            margin-left: 1.75rem;
            margin-bottom: 1.5rem;
        }
        .editorial-editor-canvas li {
            margin-bottom: 0.35rem;
        }
        .editorial-editor-canvas a {
            color: rgb(var(--color-primary));
            text-decoration: underline;
            text-underline-offset: 3px;
        }
        .editorial-editor-canvas hr {
            border: none;
            border-top: 1px solid rgb(var(--color-outline-variant) / 0.6);
            margin: 2.5rem 0;
        }
        /* Empty placeholder */
        .editorial-editor-canvas[data-placeholder]:empty::before,
        .editorial-editor-canvas[data-placeholder] > p:only-child:empty::before {
            content: attr(data-placeholder);
            color: rgb(var(--color-on-surface-variant) / 0.35);
            pointer-events: none;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.15rem;
            font-style: normal;
        }
        /* Drag-over state */
        .editorial-dragover {
            outline: 2px dashed rgb(var(--color-primary)) !important;
            outline-offset: 8px;
            border-radius: 0.75rem;
        }

        /* Unified Editorial Article Content formatting (Detail & Feed views) */
        .editorial-content, .quill-content {
            font-family: 'Newsreader', Georgia, serif;
        }
        .editorial-content p, .quill-content p { margin-bottom: 1.5rem; text-wrap: pretty; }
        .editorial-content h1, .quill-content h1 { font-size: 2.25rem; font-weight: 800; line-height: 1.25; margin-top: 2.25rem; margin-bottom: 1rem; }
        .editorial-content h2, .quill-content h2 { font-size: 1.75rem; font-weight: 700; line-height: 1.3; margin-top: 1.75rem; margin-bottom: 0.75rem; }
        .editorial-content h3, .quill-content h3 { font-size: 1.4rem; font-weight: 600; line-height: 1.35; margin-top: 1.5rem; margin-bottom: 0.5rem; }
        .editorial-content blockquote, .quill-content blockquote { border-left: 3px solid rgb(var(--color-primary)); padding-left: 1.25rem; margin: 1.5rem 0; font-style: italic; color: rgb(var(--color-on-surface-variant)); }
        .editorial-content pre, .quill-content pre { background-color: rgb(var(--color-surface-container-high)); border: 1px solid rgb(var(--color-outline-variant) / 0.5); border-radius: 0.75rem; padding: 1rem 1.25rem; margin: 1.5rem 0; overflow-x: auto; font-family: ui-monospace, monospace; font-size: 0.95rem; }
        .editorial-content code:not(pre code), .quill-content code:not(pre code) { background-color: rgb(var(--color-surface-container-high)); padding: 0.2rem 0.4rem; border-radius: 0.375rem; font-family: ui-monospace, monospace; font-size: 0.9em; }
        .editorial-content ul, .quill-content ul { list-style-type: disc; margin-left: 1.75rem; margin-bottom: 1.5rem; }
        .editorial-content ol, .quill-content ol { list-style-type: decimal; margin-left: 1.75rem; margin-bottom: 1.5rem; }
        .editorial-content li, .quill-content li { margin-bottom: 0.35rem; }
        .editorial-content a, .quill-content a { color: rgb(var(--color-primary)); text-decoration: underline; text-underline-offset: 3px; }
        .editorial-content figure, .quill-content figure { margin: 2rem 0; }
        .editorial-content img, .quill-content img { border-radius: 1rem; max-width: 100%; height: auto; }
        .editorial-content video, .quill-content video { border-radius: 1rem; max-width: 100%; height: auto; }
        .editorial-content hr, .quill-content hr { border: none; border-top: 1px solid rgb(var(--color-outline-variant) / 0.6); margin: 2.5rem 0; }

        /* ================= Editorial Button Design System ================= */
        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
            font-weight: 600;
            border-radius: 0.75rem; /* rounded-xl (12px squircle) */
            background-color: rgb(var(--color-primary));
            color: rgb(var(--color-on-primary));
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05), inset 0 1px 0 0 rgba(255, 255, 255, 0.15);
            transition: all 180ms cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer;
            user-select: none;
            text-decoration: none;
            border: 1px solid transparent;
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px -2px rgba(var(--color-primary), 0.38);
            filter: brightness(1.06);
        }
        .btn-primary:active {
            transform: translateY(0) scale(0.98);
        }
        .btn-primary:disabled, .btn-primary.disabled {
            opacity: 0.5;
            pointer-events: none;
            transform: none;
            box-shadow: none;
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
            font-weight: 600;
            border-radius: 0.75rem;
            background-color: rgb(var(--color-surface-container-lowest));
            color: rgb(var(--color-on-surface));
            border: 1px solid rgb(var(--color-outline-variant) / 0.8);
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03);
            transition: all 180ms cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer;
            user-select: none;
            text-decoration: none;
        }
        .btn-secondary:hover {
            background-color: rgb(var(--color-surface-container-low));
            border-color: rgb(var(--color-primary) / 0.4);
            color: rgb(var(--color-primary));
            transform: translateY(-0.5px);
        }
        .btn-secondary:active {
            transform: translateY(0) scale(0.98);
            background-color: rgb(var(--color-surface-container));
        }
        .btn-secondary:disabled, .btn-secondary.disabled {
            opacity: 0.5;
            pointer-events: none;
        }

        .btn-ghost {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
            font-weight: 500;
            border-radius: 0.75rem;
            background-color: transparent;
            color: rgb(var(--color-on-surface-variant));
            transition: all 150ms ease;
            cursor: pointer;
            user-select: none;
            text-decoration: none;
        }
        .btn-ghost:hover {
            background-color: rgb(var(--color-surface-container-high) / 0.6);
            color: rgb(var(--color-on-surface));
        }
        .btn-ghost:active {
            transform: scale(0.97);
        }

        .btn-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
            font-weight: 600;
            border-radius: 9999px; /* rounded-full */
            transition: all 150ms cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer;
            user-select: none;
            text-decoration: none;
        }

        .btn-pill-follow {
            padding: 0.375rem 0.95rem;
            font-size: 0.8125rem;
            line-height: 1.15;
            border-radius: 9999px;
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
            font-weight: 600;
            transition: all 150ms cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer;
            user-select: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.04);
        }
        .btn-pill-follow:not(.following) {
            background-color: rgb(var(--color-on-surface));
            color: rgb(var(--color-surface));
            border: 1px solid transparent;
        }
        .btn-pill-follow:not(.following):hover {
            opacity: 0.88;
            transform: translateY(-0.5px);
        }
        .btn-pill-follow.following {
            background-color: transparent;
            color: rgb(var(--color-on-surface));
            border: 1px solid rgb(var(--color-outline-variant));
        }
        .btn-pill-follow.following:hover {
            background-color: rgb(var(--color-error-container) / 0.18);
            border-color: rgb(var(--color-error) / 0.5);
            color: rgb(var(--color-error));
        }
        .btn-pill-follow:active {
            transform: scale(0.97);
        }

        .btn-destructive {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
            font-weight: 600;
            border-radius: 0.75rem;
            background-color: rgb(var(--color-error-container) / 0.2);
            color: rgb(var(--color-error));
            border: 1px solid rgb(var(--color-error) / 0.25);
            transition: all 180ms cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer;
            user-select: none;
            text-decoration: none;
        }
        .btn-destructive:hover {
            background-color: rgb(var(--color-error));
            color: #ffffff;
            border-color: rgb(var(--color-error));
            box-shadow: 0 4px 12px -2px rgba(var(--color-error), 0.35);
            transform: translateY(-0.5px);
        }
        .btn-destructive:active {
            transform: translateY(0) scale(0.98);
        }
    </style>
    <meta name="theme-color" content="#fbf9f5" id="meta-theme-color">

    <script>
        const savedTheme = localStorage.getItem('blogggle-theme') || 'system';
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (savedTheme === 'dark' || (savedTheme === 'system' && prefersDark)) {
            document.documentElement.classList.add('dark');
            const metaTheme = document.getElementById('meta-theme-color');
            if(metaTheme) metaTheme.setAttribute('content', '#131417');
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
                        "on-primary": "rgb(var(--color-on-primary) / <alpha-value>)",
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
                        "serif": ["Newsreader", "Georgia", "Cambria", "serif"],
                        "display": ["Newsreader", "Georgia", "serif"],
                        "editorial": ["Newsreader", "Georgia", "serif"],
                        "sans": ["Plus Jakarta Sans", "Inter", "sans-serif"],
                        "body-lg": ["Inter", "sans-serif"],
                        "title-md": ["Plus Jakarta Sans", "Inter", "sans-serif"],
                        "headline-sm": ["Newsreader", "Georgia", "serif"],
                        "body-md": ["Inter", "sans-serif"],
                        "headline-lg": ["Newsreader", "Georgia", "serif"],
                        "caption": ["Plus Jakarta Sans", "Inter", "sans-serif"],
                        "label-md": ["Plus Jakarta Sans", "Inter", "sans-serif"]
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-surface text-on-surface min-h-screen selection:bg-primary/20 selection:text-primary">

    <!-- Top Header (Frosted Glass & Literary Brand Mark) -->
    <header class="fixed top-0 left-0 right-0 h-16 bg-surface/90 backdrop-blur-xl border-b border-outline-variant/40 z-50 flex items-center justify-between px-gutter transition-colors duration-200">
        <a href="<?= BASEURL ?>/home" class="flex items-center gap-2.5 group select-none">
            <div class="w-9 h-9 rounded-xl bg-primary/10 border border-primary/25 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-surface transition-all duration-200 shadow-sm">
                <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">auto_stories</span>
            </div>
            <div class="flex items-baseline gap-1.5">
                <span class="font-serif text-[26px] font-bold text-on-surface tracking-tight group-hover:text-primary transition-colors leading-none">Blogggle</span>
                <span class="hidden sm:inline-block px-1.5 py-0.5 text-[9px] font-extrabold uppercase tracking-widest text-primary bg-primary/10 border border-primary/20 rounded-md font-sans">Press</span>
            </div>
        </a>
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
                <!-- Write Button: Editorial Spruce Primary -->
                <a class="hidden sm:inline-flex btn-primary px-3.5 py-1.5 text-xs font-semibold ml-1" href="<?= BASEURL ?>/post/create">
                    <span class="material-symbols-outlined text-[17px]">edit_square</span><span>Write</span>
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
                <a href="<?= BASEURL ?>/auth" class="btn-primary px-4 py-1.5 text-xs font-semibold ml-2">Sign In</a>
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