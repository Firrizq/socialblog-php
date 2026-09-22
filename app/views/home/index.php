<?php require_once __DIR__ . '/../templates/header.php'; ?>

<!-- Feed Header / Actions Banner -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 1.85rem; font-weight: 700; color: #ffffff;">Community Feed</h1>
        <p style="color: #94a3b8; font-size: 0.95rem; margin-top: 0.25rem;">Discover recent articles and updates from authors</p>
    </div>
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="<?= BASEURL ?>/post/create" class="btn-nav" style="padding: 0.65rem 1.25rem; font-size: 0.95rem;">
            + Write a Post
        </a>
    <?php endif; ?>
</div>

<!-- Feed Loop -->
<?php if (!empty($data['posts']) && is_array($data['posts'])): ?>
    <?php foreach ($data['posts'] as $post): ?>
        <article class="card" style="transition: border-color 0.2s;">
            <!-- Post Meta (Author & Timestamp) -->
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; border-bottom: 1px solid #334155; padding-bottom: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.65rem;">
                    <div style="width: 34px; height: 34px; border-radius: 50%; background: #3b82f6; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.9rem; color: #ffffff; text-transform: uppercase;">
                        <?= htmlspecialchars(substr($post['username'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div>
                        <div style="font-weight: 600; font-size: 0.95rem; color: #f1f5f9;">
                            @<?= htmlspecialchars($post['username'] ?? 'Anonymous') ?>
                        </div>
                    </div>
                </div>
                <time style="font-size: 0.8rem; color: #64748b;">
                    <?= date('M j, Y • g:i A', strtotime($post['created_at'])) ?>
                </time>
            </div>

            <!-- Post Title -->
            <h2 style="font-size: 1.45rem; font-weight: 700; color: #ffffff; margin-bottom: 1rem; line-height: 1.3;">
                <?= htmlspecialchars($post['title'] ?? '') ?>
            </h2>

            <!-- Post Content (Rendered from Quill.js HTML) -->
            <div class="post-content" style="color: #cbd5e1; font-size: 1rem; line-height: 1.7; word-break: break-word;">
                <?= $post['content'] ?>
            </div>
        </article>
    <?php endforeach; ?>
<?php else: ?>
    <!-- Empty State -->
    <div class="card" style="text-align: center; padding: 4rem 2rem;">
        <div style="font-size: 3rem; margin-bottom: 1rem;">📝</div>
        <h2 style="font-size: 1.35rem; color: #ffffff; margin-bottom: 0.5rem;">No posts published yet</h2>
        <p style="color: #94a3b8; font-size: 0.95rem; max-width: 440px; margin: 0 auto 1.75rem auto;">
            Be the first person to share an article or thoughts on the platform!
        </p>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="<?= BASEURL ?>/post/create" class="btn-nav" style="padding: 0.75rem 1.5rem; font-size: 0.95rem;">
                Create the First Post
            </a>
        <?php else: ?>
            <a href="<?= BASEURL ?>/auth/login" class="btn-nav" style="padding: 0.75rem 1.5rem; font-size: 0.95rem;">
                Sign In to Publish
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- Styling for Rich Post Content -->
<style>
    .post-content p {
        margin-bottom: 0.9rem;
    }
    .post-content h1, .post-content h2, .post-content h3 {
        color: #f8fafc;
        margin-top: 1.25rem;
        margin-bottom: 0.6rem;
    }
    .post-content blockquote {
        border-left: 3px solid #3b82f6;
        padding-left: 1rem;
        margin: 1rem 0;
        color: #94a3b8;
        font-style: italic;
    }
    .post-content pre {
        background: #0f172a;
        border: 1px solid #334155;
        border-radius: 6px;
        padding: 0.75rem 1rem;
        overflow-x: auto;
        font-family: monospace;
        color: #38bdf8;
        margin: 1rem 0;
    }
    .post-content ul, .post-content ol {
        margin-left: 1.5rem;
        margin-bottom: 0.9rem;
    }
    .post-content li {
        margin-bottom: 0.25rem;
    }
    .post-content a {
        color: #38bdf8;
        text-decoration: underline;
    }
</style>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
