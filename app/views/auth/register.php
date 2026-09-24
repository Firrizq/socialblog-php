<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= htmlspecialchars($data['title'] ?? 'Create Account - Blogggle') ?></title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/> 
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

    <style>
        @layer base{html,body{margin:0;padding:0;}body{overscroll-behavior:none;}}
        ::-webkit-scrollbar{display:none;}
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
<body class="bg-surface text-on-surface min-h-screen flex items-center justify-center p-4 selection:bg-primary-container selection:text-on-primary-container">

    <div class="w-full max-w-md">
        <!-- Brand Header -->
        <div class="flex items-center justify-center gap-2 mb-8">
            <a href="<?= BASEURL ?>/home" class="flex items-center gap-2 text-decoration-none group">
                <span class="material-symbols-outlined text-primary text-3xl transition-transform group-hover:scale-105">edit_square</span>
                <span class="font-title-md text-2xl text-on-surface tracking-tight font-bold">Blogggle</span>
            </a>
        </div>

        <!-- Auth Card -->
        <div class="bg-surface-container-low border border-outline-variant/30 rounded-2xl p-7 sm:p-9 shadow-xl backdrop-blur-md">
            
            <div class="mb-6 text-center">
                <h1 class="font-title-md text-2xl font-bold text-on-surface tracking-tight">Create an Account</h1>
                <p class="font-body-md text-on-surface-variant text-sm mt-1">Start publishing stories & perspectives</p>
            </div>

            <!-- Error Notification -->
            <?php if (!empty($data['error'])): ?>
                <div class="mb-5 p-3.5 rounded-xl bg-error-container/25 border border-error/40 text-error text-sm flex items-start gap-2.5">
                    <span class="material-symbols-outlined text-lg shrink-0 mt-0.5">error</span>
                    <span><?= htmlspecialchars($data['error']) ?></span>
                </div>
            <?php endif; ?>

            <form action="<?= BASEURL ?>/auth/register" method="POST" class="space-y-4">
                <!-- Username Input -->
                <div>
                    <label for="username" class="block font-label-md text-xs uppercase tracking-wider text-on-surface-variant mb-1.5 font-medium">
                        Username
                    </label>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3.5 text-outline text-lg pointer-events-none">alternate_email</span>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            value="<?= htmlspecialchars($data['username'] ?? '') ?>" 
                            placeholder="johndoe" 
                            required 
                            autofocus
                            class="w-full pl-11 pr-4 py-2.5 bg-surface-container-lowest border border-outline-variant/40 rounded-xl font-body-md text-on-surface text-sm placeholder:text-outline/60 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                        >
                    </div>
                </div>

                <!-- Email Input -->
                <div>
                    <label for="email" class="block font-label-md text-xs uppercase tracking-wider text-on-surface-variant mb-1.5 font-medium">
                        Email Address
                    </label>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3.5 text-outline text-lg pointer-events-none">mail</span>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="<?= htmlspecialchars($data['email'] ?? '') ?>" 
                            placeholder="you@blogggle.com" 
                            required 
                            class="w-full pl-11 pr-4 py-2.5 bg-surface-container-lowest border border-outline-variant/40 rounded-xl font-body-md text-on-surface text-sm placeholder:text-outline/60 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                        >
                    </div>
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block font-label-md text-xs uppercase tracking-wider text-on-surface-variant mb-1.5 font-medium">
                        Password
                    </label>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3.5 text-outline text-lg pointer-events-none">lock</span>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="At least 6 characters" 
                            required 
                            class="w-full pl-11 pr-4 py-2.5 bg-surface-container-lowest border border-outline-variant/40 rounded-xl font-body-md text-on-surface text-sm placeholder:text-outline/60 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                        >
                    </div>
                </div>

                <!-- Confirm Password Input -->
                <div>
                    <label for="confirm_password" class="block font-label-md text-xs uppercase tracking-wider text-on-surface-variant mb-1.5 font-medium">
                        Confirm Password
                    </label>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3.5 text-outline text-lg pointer-events-none">lock_reset</span>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            placeholder="Repeat password" 
                            required 
                            class="w-full pl-11 pr-4 py-2.5 bg-surface-container-lowest border border-outline-variant/40 rounded-xl font-body-md text-on-surface text-sm placeholder:text-outline/60 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                        >
                    </div>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="w-full mt-2 py-3 px-4 rounded-xl bg-primary-container text-on-primary-container font-title-md font-semibold text-sm hover:bg-primary transition-all shadow-[0_0_0_1px_rgba(16,185,129,0.3)] flex items-center justify-center gap-2 active:scale-[0.99]"
                >
                    <span>Sign Up</span>
                    <span class="material-symbols-outlined text-lg">check</span>
                </button>
            </form>

            <!-- Card Footer -->
            <div class="mt-6 pt-5 border-t border-outline-variant/30 text-center">
                <p class="font-body-md text-sm text-on-surface-variant">
                    Already have an account? 
                    <a href="<?= BASEURL ?>/auth" class="text-primary hover:text-primary-fixed font-semibold transition-colors ml-1">
                        Sign in here
                    </a>
                </p>
            </div>
        </div>

        <!-- Back to feed link -->
        <div class="text-center mt-6">
            <a href="<?= BASEURL ?>/home" class="inline-flex items-center gap-1.5 text-xs text-on-surface-variant hover:text-on-surface transition-colors">
                <span class="material-symbols-outlined text-base">west</span>
                <span>Back to Community Feed</span>
            </a>
        </div>
    </div>

</body>
</html>
