<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="flex flex-col w-full px-4 sm:px-6 py-2">
    <!-- Bookmarks Page Sticky Header -->
    <div class="sticky top-16 z-30 bg-surface/90 backdrop-blur-md pb-space-xs pt-space-xs mb-space-lg flex items-center justify-between border-b border-outline-variant/30">
        <div class="flex items-center gap-space-sm py-2">
            <div class="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' 1;">bookmark</span>
            </div>
            <div>
                <h1 class="font-headline-sm text-lg sm:text-xl font-bold text-on-surface tracking-tight">Saved Stories</h1>
                <p class="font-caption text-xs text-on-surface-variant">Stories and insights you saved for later</p>
            </div>
        </div>
        <?php if (!empty($data['posts'])): ?>
            <div class="flex items-center gap-space-xs">
                <span class="px-space-md py-1 rounded-full bg-surface-container font-caption text-on-surface-variant text-xs font-semibold">
                    <?= count($data['posts']) ?> <?= count($data['posts']) === 1 ? 'saved story' : 'saved stories' ?>
                </span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bookmarked Posts Stream -->
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
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <a href="<?= BASEURL ?>/profile/user/<?= urlencode($post['username'] ?? '') ?>" class="font-title-md text-on-surface hover:text-primary transition-colors truncate">
                                        <?= htmlspecialchars($post['name'] ?? $post['username'] ?? 'Anonymous') ?>
                                    </a>
                                    <span class="font-caption text-on-surface-variant truncate">@<?= htmlspecialchars($post['username'] ?? 'anon') ?></span>
                                    <span class="font-caption text-on-surface-variant whitespace-nowrap"> • <?= date('M j, Y', strtotime($post['created_at'])) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                          <span class="px-space-sm py-0.5 rounded-full bg-surface-container font-caption text-on-surface-variant"><?= $post['read_time_minutes'] ?? 1 ?> min read</span>
                          <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
                            <div class="relative dropdown-container">
                              <button type="button" onclick="toggleMenu(event, 'menu-<?= $post['id'] ?>')" class="text-on-surface-variant hover:text-primary p-1.5 rounded-full hover:bg-surface-container transition-colors flex items-center justify-center">
                                <span class="material-symbols-outlined text-lg">more_horiz</span>
                              </button>
                              <div id="menu-<?= $post['id'] ?>" class="hidden absolute right-0 top-full mt-1 w-40 bg-surface-container-high border border-outline-variant/30 rounded-xl shadow-xl z-50 overflow-hidden flex flex-col py-1">
                                <a href="<?= BASEURL ?>/post/edit/<?= $post['id'] ?>" class="px-4 py-2.5 text-sm text-on-surface font-title-md hover:bg-surface-container flex items-center gap-3 transition-colors"><span class="material-symbols-outlined text-lg">edit</span> Edit Story</a>
                                <form action="<?= BASEURL ?>/post/delete/<?= $post['id'] ?>" method="POST" class="m-0 p-0" onsubmit="return confirm('Are you sure you want to delete this story?');">
                                  <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-error font-title-md hover:bg-error-container/20 flex items-center gap-3 transition-colors"><span class="material-symbols-outlined text-lg">delete</span> Delete</button>
                                </form>
                              </div>
                            </div>
                          <?php endif; ?>
                        </div>
                    </div>

                    <!-- Content -->
                    <div onclick="if(!event.target.closest('a')) window.location.href='<?= BASEURL ?>/post/detail/<?= (int)$post['id'] ?>';" class="flex flex-col gap-space-xs cursor-pointer group text-decoration-none">
                        <?php if(!empty($post['title'])): ?>
                            <a href="<?= BASEURL ?>/post/detail/<?= (int)$post['id'] ?>" class="font-headline-sm text-on-surface group-hover:text-primary transition-colors tracking-tight">
                                <?= htmlspecialchars($post['title']) ?>
                            </a>
                        <?php endif; ?>
                        
                        <?php if(!empty($post['cover_image'])): ?>
                          <div class="mt-2 mb-3">
                            <img src="<?= BASEURL ?><?= htmlspecialchars($post['cover_image']) ?>" class="w-full rounded-xl border border-outline-variant/30 object-cover max-h-[400px]" alt="Attachment">
                          </div>
                        <?php endif; ?>

                        <div class="font-body-md text-on-surface-variant leading-relaxed text-sm sm:text-base line-clamp-3">
                            <?= preg_replace('/(^|>|\s)#([a-zA-Z_][a-zA-Z0-9_]*)/', '$1<a href="' . BASEURL . '/explore/tag/$2" class="text-primary font-semibold hover:underline relative z-10" onclick="event.stopPropagation();">#$2</a>', strip_tags((string)($post['content'] ?? ''))) ?>
                        </div>
                    </div>

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
            <!-- Styled Empty State -->
            <div class="text-center p-12 bg-surface-container-low rounded-2xl border border-outline-variant/30 flex flex-col items-center justify-center gap-3 my-6 shadow-sm">
                <div class="w-16 h-16 rounded-full bg-surface-container flex items-center justify-center text-outline mb-2">
                    <span class="material-symbols-outlined text-3xl">bookmark_border</span>
                </div>
                <h2 class="font-title-md text-xl font-bold text-on-surface">No bookmarks yet</h2>
                <p class="font-body-md text-on-surface-variant text-sm max-w-sm">
                    Save stories you want to read later by clicking the bookmark icon on any post.
                </p>
                <a href="<?= BASEURL ?>/home" class="mt-4 inline-flex items-center gap-2 px-space-lg py-2.5 rounded-full bg-primary-container text-on-primary-container font-title-md text-sm hover:bg-primary transition-all font-semibold shadow-[0_0_0_1px_rgba(16,185,129,0.3)] active:scale-95">
                    <span class="material-symbols-outlined text-base">explore</span>
                    <span>Explore Blogggle</span>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    /* Quill content styles */
    .quill-content p { margin-bottom: 0.75rem; }
    .quill-content a { color: #4edea3; text-decoration: underline; }
    .quill-content strong { color: #dae2fd; }
    .quill-content blockquote { border-left: 3px solid #10b981; padding-left: 1rem; margin: 1rem 0; font-style: italic; }
</style>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
