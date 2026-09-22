<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['title'] ?? 'Social Blog') ?></title>
    
    <!-- Quill.js CSS CDN -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        body {
            background-color: #0f172a;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Navbar */
        .navbar {
            background-color: #1e293b;
            border-bottom: 1px solid #334155;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .nav-wrapper {
            max-width: 960px;
            margin: 0 auto;
            padding: 0.85rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .nav-brand {
            font-size: 1.25rem;
            font-weight: 700;
            color: #38bdf8;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            list-style: none;
        }

        .nav-link {
            color: #cbd5e1;
            text-decoration: none;
            font-size: 0.925rem;
            font-weight: 500;
            transition: color 0.2s;
        }

        .nav-link:hover {
            color: #38bdf8;
        }

        .btn-nav {
            background-color: #3b82f6;
            color: #ffffff !important;
            padding: 0.45rem 0.9rem;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.2s;
        }

        .btn-nav:hover {
            background-color: #2563eb;
        }

        .btn-logout {
            background-color: #ef4444;
            color: #ffffff !important;
            padding: 0.45rem 0.9rem;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.2s;
        }

        .btn-logout:hover {
            background-color: #dc2626;
        }

        .user-greeting {
            color: #94a3b8;
            font-size: 0.875rem;
        }

        .user-greeting strong {
            color: #e2e8f0;
        }

        /* Main Content Container */
        .container {
            max-width: 860px;
            width: 100%;
            margin: 0 auto;
            padding: 2rem 1.25rem;
            flex: 1;
        }

        /* Global Card & Feed Styles */
        .card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 1.75rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2), 0 2px 4px -2px rgba(0, 0, 0, 0.2);
        }

        /* Quill Dark Mode Adjustments */
        .ql-toolbar.ql-snow {
            background: #1e293b;
            border-color: #334155 !important;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        .ql-container.ql-snow {
            background: #0f172a;
            border-color: #334155 !important;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
            color: #f8fafc;
            font-size: 1rem;
            min-height: 220px;
        }

        .ql-snow .ql-stroke {
            stroke: #cbd5e1 !important;
        }

        .ql-snow .ql-fill {
            fill: #cbd5e1 !important;
        }

        .ql-snow .ql-picker {
            color: #cbd5e1 !important;
        }

        .ql-snow .ql-picker-options {
            background-color: #1e293b !important;
            border-color: #334155 !important;
        }
    </style>
</head>
<body>

    <!-- Top Navbar -->
    <nav class="navbar">
        <div class="nav-wrapper">
            <a href="<?= BASEURL ?>/home" class="nav-brand">
                <span>✍️</span> SocialBlog
            </a>
            <ul class="nav-links">
                <li><a href="<?= BASEURL ?>/home" class="nav-link">Home</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="<?= BASEURL ?>/post/create" class="btn-nav">+ Create Post</a></li>
                    <li class="user-greeting">Hi, <strong>@<?= htmlspecialchars($_SESSION['username'] ?? '') ?></strong></li>
                    <li><a href="<?= BASEURL ?>/auth/logout" class="btn-logout">Logout</a></li>
                <?php else: ?>
                    <li><a href="<?= BASEURL ?>/auth" class="btn-nav">Login</a></li>
                    <li><a href="<?= BASEURL ?>/auth/register" class="nav-link">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Content Container Start -->
    <div class="container">
