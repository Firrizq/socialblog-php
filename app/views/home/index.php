<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['title'] ?? 'Social Blog') ?></title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 2.5rem;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.3);
            text-align: center;
        }
        .badge {
            display: inline-block;
            background: #3b82f6;
            color: #ffffff;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1rem;
        }
        h1 {
            font-size: 1.875rem;
            margin-bottom: 0.75rem;
            color: #ffffff;
        }
        p {
            color: #94a3b8;
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        .status-box {
            background: #0f172a;
            border-radius: 8px;
            padding: 1rem;
            text-align: left;
            font-family: monospace;
            font-size: 0.875rem;
            color: #38bdf8;
            border: 1px solid #1e293b;
        }
    </style>
</head>
<body>
    <div class="card">
        <span class="badge">MVC Initialized</span>
        <h1><?= htmlspecialchars($data['title'] ?? 'Social Blog') ?></h1>
        <p><?= htmlspecialchars($data['message'] ?? '') ?></p>
        <div class="status-box">
            <div>✓ Core Router (App.php) active</div>
            <div>✓ Base Controller (Controller.php) active</div>
            <div>✓ Database Wrapper (Database.php) ready</div>
            <div>✓ Environment: <?= htmlspecialchars(BASEURL) ?></div>
        </div>

        <div style="margin-top: 1.75rem; display: flex; gap: 0.75rem; justify-content: center;">
            <?php if (isset($_SESSION['user_id'])): ?>
                <span style="display: flex; align-items: center; color: #a5b4fc; font-size: 0.95rem;">
                    Hello, <strong>&nbsp;<?= htmlspecialchars($_SESSION['username'] ?? '') ?></strong>!
                </span>
                <a href="<?= BASEURL ?>/auth/logout" style="background: #ef4444; color: #fff; padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.875rem; font-weight: 600;">Logout</a>
            <?php else: ?>
                <a href="<?= BASEURL ?>/auth" style="background: #3b82f6; color: #fff; padding: 0.5rem 1.25rem; border-radius: 6px; text-decoration: none; font-size: 0.875rem; font-weight: 600;">Sign In</a>
                <a href="<?= BASEURL ?>/auth/register" style="background: #334155; color: #fff; padding: 0.5rem 1.25rem; border-radius: 6px; text-decoration: none; font-size: 0.875rem; font-weight: 600;">Create Account</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
