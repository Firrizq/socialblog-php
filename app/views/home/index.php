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
    <section class="bg-surface-container-low rounded-xl p-space-md mb-space-lg shadow-md hover:shadow-xl transition-shadow relative overflow-hidden cursor-text" onclick="openNoteModal()">
        <div class="flex items-start gap-space-md">
            <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center font-bold text-on-primary shrink-0 overflow-hidden">
                <?php if (!empty($_SESSION['profile_picture'])): ?>
                    <img src="<?= BASEURL ?><?= htmlspecialchars($_SESSION['profile_picture']) ?>" alt="<?= htmlspecialchars($_SESSION['username']) ?>" class="w-full h-full object-cover rounded-full">
                <?php else: ?>
                    <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                <?php endif; ?>
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
                            <a href="<?= BASEURL ?>/profile/user/<?= urlencode($post['username'] ?? '') ?>" class="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center font-bold text-primary shrink-0 hover:ring-2 hover:ring-primary transition-all overflow-hidden">
                                <?php if (!empty($post['profile_picture'])): ?>
                                    <img src="<?= BASEURL ?><?= htmlspecialchars($post['profile_picture']) ?>" alt="<?= htmlspecialchars($post['username'] ?? '') ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <?= htmlspecialchars(substr($post['username'] ?? 'U', 0, 1)) ?>
                                <?php endif; ?>
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
                        
                        <div class="font-body-md text-on-surface-variant leading-relaxed text-sm sm:text-base line-clamp-3">
                            <?= strip_tags((string)($post['content'] ?? '')) ?>
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
                        <?php 
                            $isLiked = in_array((int)$post['id'], $data['liked_posts'] ?? []);
                            $isBookmarked = in_array((int)$post['id'], $data['bookmarked_posts'] ?? []);
                        ?>
                        <button class="btn-like flex items-center gap-1.5 transition-colors <?= $isLiked ? 'text-primary' : 'hover:text-primary' ?> active:scale-95" data-id="<?= (int)$post['id'] ?>" title="Like">
                            <span class="material-symbols-outlined text-lg" style="font-variation-settings: 'FILL' <?= $isLiked ? 1 : 0 ?>;">favorite</span>
                            <span class="like-count font-caption text-caption font-semibold"><?= (int)($post['like_count'] ?? 0) ?></span>
                        </button>
                        <button class="btn-bookmark transition-colors <?= $isBookmarked ? 'text-primary' : 'hover:text-primary' ?> active:scale-95" data-id="<?= (int)$post['id'] ?>" title="Bookmark">
                            <span class="material-symbols-outlined text-lg" style="font-variation-settings: 'FILL' <?= $isBookmarked ? 1 : 0 ?>;">bookmark</span>
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

<!-- Short-form Note Modal (Substack / Twitter Style) -->
<?php if (isset($_SESSION['user_id'])): ?>
<div id="noteModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4" onclick="if(event.target === this) closeNoteModal();">
    <div class="w-full max-w-lg bg-surface-container-highest border border-outline-variant/30 rounded-2xl p-5 shadow-2xl relative">
        <form action="<?= BASEURL ?>/post/createNote" method="POST" class="flex flex-col gap-4">
            <!-- Modal Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center font-bold text-on-primary shrink-0 shadow-inner overflow-hidden">
                        <?php if (!empty($_SESSION['profile_picture'])): ?>
                            <img src="<?= BASEURL ?><?= htmlspecialchars($_SESSION['profile_picture']) ?>" alt="<?= htmlspecialchars($_SESSION['username'] ?? '') ?>" class="w-full h-full object-cover rounded-full">
                        <?php else: ?>
                            <?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <span class="font-bold text-on-surface text-base">
                        <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-on-surface-variant hover:text-primary transition-colors cursor-pointer">Drafts</span>
                    <button type="button" onclick="closeNoteModal()" class="w-7 h-7 rounded-full hover:bg-surface-container text-on-surface-variant hover:text-on-surface flex items-center justify-center transition-colors ml-1" title="Close">
                        <span class="material-symbols-outlined text-base">close</span>
                    </button>
                </div>
            </div>

            <!-- Modal Body (Transparent Textarea) -->
            <div>
                <textarea 
                    name="content" 
                    id="noteModalTextarea"
                    rows="4" 
                    placeholder="What's on your mind?" 
                    required 
                    class="bg-transparent border-none outline-none focus:ring-0 text-on-surface text-lg placeholder:text-outline-variant resize-none w-full p-0 leading-relaxed"
                ></textarea>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between pt-2 border-t border-outline-variant/20">
                <!-- Left side: Dummy Material Icons -->
                <div class="flex items-center gap-1.5 sm:gap-2 text-outline">
                    <button type="button" class="p-1 rounded-full hover:bg-surface-container hover:text-primary transition-colors" title="Add Image">
                        <span class="material-symbols-outlined text-xl">image</span>
                    </button>
                    <button type="button" class="p-1 rounded-full hover:bg-surface-container hover:text-primary transition-colors" title="Add Video">
                        <span class="material-symbols-outlined text-xl">videocam</span>
                    </button>
                    <button type="button" class="p-1 rounded-full hover:bg-surface-container hover:text-primary transition-colors" title="Add Emoji">
                        <span class="material-symbols-outlined text-xl">mood</span>
                    </button>
                    <button type="button" class="p-1 rounded-full hover:bg-surface-container hover:text-primary transition-colors" title="Schedule">
                        <span class="material-symbols-outlined text-xl">calendar_month</span>
                    </button>
                </div>

                <!-- Right side: Cancel & Post Buttons -->
                <div class="flex items-center gap-2">
                    <button 
                        type="button" 
                        onclick="closeNoteModal()" 
                        class="text-sm font-medium text-on-surface-variant hover:text-on-surface transition-colors px-3 py-1.5 rounded-lg hover:bg-surface-container"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        class="bg-primary-container text-on-primary-container hover:bg-primary rounded-full px-6 py-2 font-bold shadow-md transition-all active:scale-95 text-sm"
                    >
                        Post
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
/**
 * Short-form Note Modal controls
 */
function openNoteModal() {
    const modal = document.getElementById('noteModal');
    if (!modal) return;
    modal.classList.remove('hidden');
    const textarea = document.getElementById('noteModalTextarea');
    if (textarea) {
        setTimeout(() => textarea.focus(), 50);
    }
}

function closeNoteModal() {
    const modal = document.getElementById('noteModal');
    if (!modal) return;
    modal.classList.add('hidden');
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeNoteModal();
    }
});
</script>

<style>
    /* Mengatasi gaya dasar Quill HTML di Feed */
    .quill-content p { margin-bottom: 0.75rem; }
    .quill-content a { color: #4edea3; text-decoration: underline; }
    .quill-content strong { color: #dae2fd; }
    .quill-content blockquote { border-left: 3px solid #10b981; padding-left: 1rem; margin: 1rem 0; font-style: italic; }
</style>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>