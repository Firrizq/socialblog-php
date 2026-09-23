<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="flex flex-col w-full px-4 sm:px-6 py-2">
    <!-- Feed Header Navigation Tabs -->
    <div class="sticky top-16 z-30 bg-surface/90 backdrop-blur-md pb-space-xs pt-space-xs mb-space-lg flex items-center justify-between">
        <nav class="flex items-center gap-space-lg">
            <button class="relative pb-space-sm font-title-md text-primary transition-colors flex items-center gap-space-xs">
                <span>For You</span>
                <span class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary rounded-full shadow-[0_0_8px_rgba(78,222,163,0.6)]"></span>
            </button>
            <button class="relative pb-space-sm font-title-md text-on-surface-variant hover:text-on-surface transition-colors flex items-center gap-space-xs">
                <span>Following</span>
            </button>
        </nav>
        <div class="flex items-center gap-space-xs">
            <button class="w-9 h-9 rounded-full bg-surface-container hover:bg-surface-container-high text-on-surface-variant hover:text-primary flex items-center justify-center transition-all"><span class="material-symbols-outlined text-xl">refresh</span></button>
        </div>
    </div>

    <!-- Quick Composer Strip -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <section class="bg-surface-container-low rounded-xl p-space-md mb-space-lg shadow-md hover:shadow-xl transition-shadow relative overflow-hidden cursor-text" onclick="window.location.href='<?= BASEURL ?>/post/create'">
        <div class="flex items-start gap-space-md">
            <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center font-bold text-on-primary shrink-0">
                <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
            </div>
            <div class="flex-1 min-w-0 pt-2">
                <p class="text-on-surface-variant font-body-md">Share a perspective, insight, or draft snippet...</p>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Feed Post Stream -->
    <div class="flex flex-col gap-space-lg">
        <?php if (!empty($data['posts']) && is_array($data['posts'])): ?>
            <?php foreach ($data['posts'] as $post): ?>
                
                <!-- Dynamic Card -->
                <article class="bg-surface-container-low rounded-xl p-space-lg shadow-md hover:bg-surface-container transition-colors flex flex-col gap-space-md">
                    <!-- Header Meta -->
                    <div class="flex items-center justify-between gap-space-md">
                        <div class="flex items-center gap-space-sm min-w-0">
                            <a href="<?= BASEURL ?>/profile/user/<?= urlencode($post['username'] ?? '') ?>" class="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center font-bold text-primary shrink-0 hover:ring-2 hover:ring-primary transition-all">
                                <?= htmlspecialchars(substr($post['username'] ?? 'U', 0, 1)) ?>
                            </a>
                            <div class="flex flex-col min-w-0">
                                <div class="flex items-center gap-1 min-w-0">
                                    <a href="<?= BASEURL ?>/profile/user/<?= urlencode($post['username'] ?? '') ?>" class="font-title-md text-on-surface hover:text-primary transition-colors truncate">
                                        <?= htmlspecialchars($post['username'] ?? 'Anonymous') ?>
                                    </a>
                                    <span class="font-caption text-on-surface-variant whitespace-nowrap"> • <?= date('M j, Y', strtotime($post['created_at'])) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-space-xs">
                            <span class="px-space-sm py-0.5 rounded-full bg-surface-container font-caption text-on-surface-variant"><?= $post['read_time_minutes'] ?? 1 ?> min read</span>
                        </div>
                    </div>

                    <!-- Content -->
                    <a href="<?= BASEURL ?>/post/detail/<?= (int)$post['id'] ?>" class="flex flex-col gap-space-xs cursor-pointer group text-decoration-none">
                        <?php if(!empty($post['title'])): ?>
                            <h2 class="font-headline-sm text-on-surface group-hover:text-primary transition-colors tracking-tight">
                                <?= htmlspecialchars($post['title']) ?>
                            </h2>
                        <?php endif; ?>
                        
                        <div class="font-body-md text-on-surface-variant leading-relaxed quill-content">
                            <!-- Raw HTML dari editor Quill -->
                            <?= $post['content'] ?>
                        </div>
                    </a>

                    <!-- Actions -->
                    <div class="flex items-center justify-between pt-space-xs text-on-surface-variant">
                        <a href="<?= BASEURL ?>/post/detail/<?= (int)$post['id'] ?>" class="flex items-center gap-1.5 hover:text-on-surface transition-colors">
                            <span class="material-symbols-outlined text-lg">chat_bubble</span>
                            <span class="font-caption text-caption"><?= $post['comment_count'] ?? 0 ?></span>
                        </a>
                        <button class="flex items-center gap-1.5 hover:text-primary transition-colors">
                            <span class="material-symbols-outlined text-lg">sync_alt</span>
                            <span class="font-caption text-caption"><?= $post['repost_count'] ?? 0 ?></span>
                        </button>
                        <button class="flex items-center gap-1.5 text-primary transition-colors">
                            <span class="material-symbols-outlined text-lg" style="font-variation-settings: 'FILL' 1;">favorite</span>
                            <span class="font-caption text-caption font-semibold"><?= $post['like_count'] ?? 0 ?></span>
                        </button>
                        <button class="hover:text-primary transition-colors">
                            <span class="material-symbols-outlined text-lg">bookmark</span>
                        </button>
                    </div>
                </article>

            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center p-10 bg-surface-container-low rounded-xl">
                <h2 class="text-xl font-bold text-on-surface">Belum ada postingan</h2>
                <p class="text-on-surface-variant mt-2">Jadilah yang pertama membuat cerita!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    /* Mengatasi gaya dasar Quill HTML di Feed */
    .quill-content p { margin-bottom: 0.75rem; }
    .quill-content a { color: #4edea3; text-decoration: underline; }
    .quill-content strong { color: #dae2fd; }
    .quill-content blockquote { border-left: 3px solid #10b981; padding-left: 1rem; margin: 1rem 0; font-style: italic; }
</style>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>