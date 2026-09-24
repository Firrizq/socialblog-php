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
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

    <!-- Tailwind Config dari Google Stitch -->
    <style>
        @layer base{html,body{margin:0;padding:0;}body{overscroll-behavior:none;}main>:first-child{margin-top:0!important;}main>:last-child{margin-bottom:0!important;}}
        ::-webkit-scrollbar{display:none;}
        /* Penyesuaian Quill untuk Dark Mode Obsidian Emerald */
        .ql-toolbar.ql-snow { background: #171f33; border-color: rgba(60, 74, 66, 0.5) !important; border-top-left-radius: 0.75rem; border-top-right-radius: 0.75rem; }
        .ql-container.ql-snow { background: #060e20; border-color: rgba(60, 74, 66, 0.5) !important; border-bottom-left-radius: 0.75rem; border-bottom-right-radius: 0.75rem; color: #dae2fd; font-family: 'Inter', sans-serif; font-size: 1rem; min-height: 260px; }
        .ql-snow .ql-stroke { stroke: #bbcabf !important; }
        .ql-snow .ql-fill { fill: #bbcabf !important; }
        .ql-snow .ql-picker { color: #bbcabf !important; }
        .ql-snow .ql-picker-options { background-color: #171f33 !important; border-color: rgba(60, 74, 66, 0.5) !important; }
        .ql-snow.ql-toolbar button:hover .ql-stroke, .ql-snow.ql-toolbar button:focus .ql-stroke, .ql-snow.ql-toolbar button.ql-active .ql-stroke { stroke: #4edea3 !important; }
        .ql-snow.ql-toolbar button:hover .ql-fill, .ql-snow.ql-toolbar button:focus .ql-fill, .ql-snow.ql-toolbar button.ql-active .ql-fill { fill: #4edea3 !important; }
        .ql-editor.ql-blank::before { color: #86948a !important; font-style: normal; }
    </style>
    <script src="https://cdn.tailwindcss.com"></script>
    <script id="tailwind-config">
tailwind.config = {
    "darkMode": "class",
    "theme": {
        "extend": {
            "colors": {
                "on-error": "#690005", "inverse-primary": "#006c49", "tertiary-fixed-dim": "#68dba9", "tertiary-fixed": "#85f8c4", "tertiary-container": "#3eb686", "surface-container-lowest": "#060e20", "on-background": "#dae2fd", "secondary-fixed": "#d5e3fd", "primary-container": "#10b981", "on-surface": "#dae2fd", "background": "#0b1326", "surface-dim": "#0b1326", "secondary-fixed-dim": "#b9c7e0", "on-tertiary": "#003825", "primary-fixed-dim": "#4edea3", "on-secondary-container": "#abb9d2", "error-container": "#93000a", "surface-bright": "#31394d", "error": "#ffb4ab", "primary": "#4edea3", "surface-variant": "#2d3449", "on-tertiary-container": "#00422c", "on-tertiary-fixed": "#002114", "on-primary-fixed-variant": "#005236", "surface": "#0b1326", "inverse-surface": "#dae2fd", "on-error-container": "#ffdad6", "surface-container-high": "#222a3d", "surface-container-low": "#131b2e", "on-surface-variant": "#bbcabf", "on-tertiary-fixed-variant": "#005137", "outline-variant": "#3c4a42", "tertiary": "#68dba9", "secondary": "#b9c7e0", "outline": "#86948a", "inverse-on-surface": "#283044", "on-secondary": "#233144", "secondary-container": "#3c4a5e", "on-primary-container": "#00422b", "on-primary": "#003824", "on-secondary-fixed-variant": "#3a485c", "surface-tint": "#4edea3", "surface-container": "#171f33", "on-primary-fixed": "#002113", "surface-container-highest": "#2d3449", "primary-fixed": "#6ffbbe", "on-secondary-fixed": "#0d1c2f"
            },
            "borderRadius": {
                "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"
            },
            "spacing": {
                "space-sm": "0.5rem", "space-xs": "0.25rem", "margin": "2rem", "gutter": "1.5rem", "space-md": "1rem", "space-xl": "2.5rem", "space-lg": "1.5rem"
            },
            "fontFamily": {
                "body-lg": ["Inter"], "title-md": ["Inter"], "headline-sm": ["Inter"], "display-mobile": ["Inter"], "body-md": ["Inter"], "display": ["Inter"], "headline-lg": ["Inter"], "headline-md": ["Inter"], "caption": ["Inter"], "headline-lg-mobile": ["Inter"], "label-md": ["Inter"]
            },
            "fontSize": {
                "body-lg": ["18px", {"lineHeight": "30px", "letterSpacing": "-0.005em", "fontWeight": "400"}], 
                "title-md": ["16px", {"lineHeight": "24px", "letterSpacing": "-0.005em", "fontWeight": "600"}], 
                "headline-sm": ["20px", {"lineHeight": "28px", "letterSpacing": "-0.01em", "fontWeight": "600"}], 
                "display-mobile": ["30px", {"lineHeight": "38px", "letterSpacing": "-0.02em", "fontWeight": "700"}], 
                "body-md": ["15px", {"lineHeight": "24px", "letterSpacing": "0em", "fontWeight": "400"}], 
                "display": ["40px", {"lineHeight": "48px", "letterSpacing": "-0.025em", "fontWeight": "700"}], 
                "headline-lg": ["32px", {"lineHeight": "40px", "letterSpacing": "-0.02em", "fontWeight": "600"}], 
                "headline-md": ["24px", {"lineHeight": "32px", "letterSpacing": "-0.015em", "fontWeight": "600"}], 
                "caption": ["12px", {"lineHeight": "16px", "letterSpacing": "0.015em", "fontWeight": "400"}], 
                "headline-lg-mobile": ["24px", {"lineHeight": "32px", "letterSpacing": "-0.015em", "fontWeight": "600"}], 
                "label-md": ["13px", {"lineHeight": "18px", "letterSpacing": "0.01em", "fontWeight": "500"}]
            }
        }
    }
};
    </script>
</head>
<body class="bg-surface text-on-surface min-h-screen">
    
    <!-- Top Header -->
    <header class="fixed top-0 left-0 right-0 h-16 bg-surface-container-low/95 backdrop-blur-xl border-b border-outline-variant/30 z-50 flex items-center justify-between px-gutter">
        <div class="flex items-center gap-space-sm">
            <span class="material-symbols-outlined text-primary text-3xl">edit_square</span>
            <span class="font-title-md text-title-md text-on-surface tracking-tight">Blogggle</span>
        </div>
        <div class="flex items-center gap-gutter">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a class="inline-flex items-center gap-space-xs px-space-md py-space-xs rounded-full bg-primary-container text-on-primary-container font-label-md text-label-md hover:bg-primary transition-colors shadow-sm" href="<?= BASEURL ?>/post/create">
                    <span class="material-symbols-outlined text-base">edit</span><span>Write</span>
                </a>
                <div class="flex items-center gap-space-sm pl-space-xs border-l border-outline-variant/40">
                    <a href="<?= BASEURL ?>/profile" class="w-8 h-8 rounded-full overflow-hidden flex items-center justify-center bg-primary font-bold text-xs text-on-primary shrink-0 hover:ring-2 hover:ring-primary transition-all">
                        <?php if (!empty($_SESSION['profile_picture'])): ?>
                            <img src="<?= BASEURL ?><?= htmlspecialchars($_SESSION['profile_picture']) ?>" alt="<?= htmlspecialchars($_SESSION['username']) ?>" class="w-full h-full object-cover rounded-full">
                        <?php else: ?>
                            <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                        <?php endif; ?>
                    </a>
                    <a href="<?= BASEURL ?>/auth/logout" class="text-error hover:text-error-container text-sm font-medium">Logout</a>
                </div>
            <?php else: ?>
                <a href="<?= BASEURL ?>/auth" class="text-primary hover:text-primary-fixed font-medium text-sm">Sign In</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Left Sidebar -->
    <aside class="fixed left-0 top-16 bottom-0 w-60 bg-surface-container-low border-r border-outline-variant/30 z-40 hidden md:flex flex-col justify-between p-gutter">
        <div class="flex flex-col gap-space-lg">
            <?php
            $currentUrl = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
            if (empty($currentUrl) && isset($_SERVER['REQUEST_URI'])) {
                $currentUrl = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '', '/');
            }
            $currentUrl = $currentUrl ?: 'home';
            $urlParts = explode('/', $currentUrl);
            $activePage = strtolower($urlParts[0] ?? 'home');

            $activeNav = "flex items-center gap-space-md px-space-md py-space-sm transition-colors bg-surface-container text-primary font-title-md rounded-xl border border-outline-variant/40";
            $inactiveNav = "flex items-center gap-space-md px-space-md py-space-sm rounded-xl text-on-surface-variant hover:bg-surface-container hover:text-on-surface transition-colors font-title-md border border-transparent";
            ?>
            <nav class="flex flex-col gap-space-xs">
                <a class="<?= ($activePage === 'home' || $activePage === '') ? $activeNav : $inactiveNav ?>" href="<?= BASEURL ?>/home">
                    <span class="material-symbols-outlined text-xl">home</span><span>Home</span>
                </a>
                <a class="<?= ($activePage === 'explore') ? $activeNav : $inactiveNav ?>" href="#">
                    <span class="material-symbols-outlined text-xl">explore</span><span>Explore</span>
                </a>
                <a class="<?= ($activePage === 'bookmarks') ? $activeNav : $inactiveNav ?>" href="<?= BASEURL ?>/bookmarks">
                    <span class="material-symbols-outlined text-xl">bookmark</span><span>Bookmarks</span>
                </a>
                <a class="<?= ($activePage === 'profile') ? $activeNav : $inactiveNav ?>" href="<?= BASEURL ?>/profile">
                    <span class="material-symbols-outlined text-xl">account_circle</span><span>Profile</span>
                </a>
            </nav>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a class="flex items-center justify-center gap-space-sm w-full py-space-sm px-space-md rounded-xl bg-primary-container text-on-primary-container font-title-md hover:bg-primary transition-all shadow-[0_0_0_1px_rgba(16,185,129,0.3)]" href="<?= BASEURL ?>/post/create">
                    <span class="material-symbols-outlined text-xl">edit_note</span><span>New Story</span>
                </a>
            <?php endif; ?>
        </div>
        
        <?php if (isset($_SESSION['user_id'])): ?>
        <a href="<?= BASEURL ?>/profile" class="flex items-center gap-space-sm p-space-sm rounded-xl bg-surface-container border border-outline-variant/20 hover:border-primary/40 transition-colors">
            <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center font-bold text-on-primary overflow-hidden shrink-0">
                <?php if (!empty($_SESSION['profile_picture'])): ?>
                    <img src="<?= BASEURL ?><?= htmlspecialchars($_SESSION['profile_picture']) ?>" alt="<?= htmlspecialchars($_SESSION['username']) ?>" class="w-full h-full object-cover rounded-full">
                <?php else: ?>
                    <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                <?php endif; ?>
            </div>
            <div class="flex flex-col min-w-0 flex-1">
                <span class="font-label-md text-label-md text-on-surface truncate"><?= htmlspecialchars($_SESSION['username']) ?></span>
                <span class="font-caption text-caption text-on-surface-variant truncate">User</span>
            </div>
        </a>
        <?php endif; ?>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="md:pl-60 pt-16">
        <div class="max-w-7xl mx-auto flex flex-col xl:flex-row justify-between">
            <!-- TAHAP 1: BAGIAN TENGAH (Feed / Profile / Content) -->
            <main class="flex-1 min-w-0 max-w-3xl mx-auto w-full border-x border-outline-variant/30 min-h-screen">