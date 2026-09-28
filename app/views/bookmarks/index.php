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

    <!-- Feed Post Stream -->
    <div class="flex flex-col divide-y divide-outline-variant/30 border-y border-outline-variant/30 mt-2">
        <?php if (!empty($data['posts']) && is_array($data['posts'])): ?>
            <?php foreach ($data['posts'] as $post): ?>
                <!-- Asymmetrical Post Row -->
                <article class="p-4 sm:p-5 hover:bg-surface-container-lowest/40 transition-colors flex gap-3 sm:gap-4 cursor-pointer" onclick="if(!event.target.closest('a') && !event.target.closest('button')) window.location.href='<?= BASEURL ?>/post/detail/<?= (int)$post['id'] ?>';">
                    
                    <!-- Left Column: Avatar -->
                    <div class="shrink-0">
                        <a href="<?= BASEURL ?>/profile/user/<?= urlencode($post['username'] ?? '') ?>" class="block w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-surface-container-high flex items-center justify-center font-bold text-primary hover:opacity-80 transition-opacity overflow-hidden relative z-10" title="View Profile">
                            <?php if (!empty($post['profile_picture'])): ?>
                                <img src="<?= BASEURL ?><?= htmlspecialchars($post['profile_picture']) ?>" alt="<?= htmlspecialchars($post['username'] ?? '') ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <?= htmlspecialchars(substr($post['username'] ?? 'U', 0, 1)) ?>
                            <?php endif; ?>
                        </a>
                    </div>

                    <!-- Right Column: Content -->
                    <div class="flex-1 min-w-0 flex flex-col">
                        
                        <!-- Header (Name, Username, Time, Options) -->
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <div class="flex items-center gap-1.5 min-w-0 flex-wrap text-[15px]">
                                <a href="<?= BASEURL ?>/profile/user/<?= urlencode($post['username'] ?? '') ?>" class="font-title-md font-bold text-on-surface hover:underline truncate relative z-10">
                                    <?= htmlspecialchars($post['name'] ?? $post['username'] ?? 'Anonymous') ?>
                                </a>
                                <span class="font-body-md text-on-surface-variant truncate">@<?= htmlspecialchars($post['username'] ?? 'anon') ?></span>
                                <span class="text-on-surface-variant font-bold">·</span>
                                <time class="timeago font-body-md text-on-surface-variant hover:underline relative z-10" datetime="<?= date('c', strtotime($post['created_at'])) ?>"></time>
                                
                                <?php if(($post['post_type'] ?? 'story') === 'story'): ?>
                                    <span class="hidden sm:inline-flex ml-1 px-1.5 py-0.5 rounded border border-outline-variant/40 font-caption text-[11px] text-on-surface-variant items-center gap-1">
                                        <span class="material-symbols-outlined text-[12px]">auto_stories</span>
                                        <?= $post['read_time_minutes'] ?? 1 ?> min read
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Dropdown Options -->
                            <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
                                <div class="relative dropdown-container z-20 shrink-0">
                                    <button type="button" onclick="toggleMenu(event, 'menu-<?= $post['id'] ?>')" class="text-on-surface-variant hover:text-primary w-8 h-8 rounded-full hover:bg-primary/10 transition-colors flex items-center justify-center -mr-2">
                                        <span class="material-symbols-outlined text-[18px]">more_horiz</span>
                                    </button>
                                    <div id="menu-<?= $post['id'] ?>" class="hidden absolute right-0 top-full mt-1 w-36 bg-surface-container-high border border-outline-variant/30 rounded-xl shadow-xl z-[60] overflow-hidden flex flex-col py-1">
                                        <a href="<?= BASEURL ?>/post/edit/<?= $post['id'] ?>" class="px-4 py-2 text-sm text-on-surface font-title-md hover:bg-surface-container flex items-center gap-3 transition-colors"><span class="material-symbols-outlined text-base">edit</span> Edit</a>
                                        <form action="<?= BASEURL ?>/post/delete/<?= $post['id'] ?>" method="POST" class="m-0 p-0" onsubmit="return confirm('Are you sure you want to delete this?');">
                                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-error font-title-md hover:bg-error-container/20 flex items-center gap-3 transition-colors"><span class="material-symbols-outlined text-base">delete</span> Delete</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Content Body -->
                        <?php if(($post['post_type'] ?? 'story') === 'story'): ?>
                            <!-- Story Cinematic Card -->
                            <div class="mt-1 mb-1 flex flex-col rounded-2xl border border-outline-variant/40 overflow-hidden group hover:border-primary/40 transition-colors bg-surface-container-lowest relative z-10">
                                <?php 
                                $cover = '';
                                if(!empty($post['cover_image'])) {
                                    $decoded = json_decode($post['cover_image'], true);
                                    $imgs = is_array($decoded) ? $decoded : array_filter(explode(',', $post['cover_image']));
                                    $cover = !empty($imgs) ? trim($imgs[0]) : '';
                                }
                                if (empty($cover) && preg_match('/<img[^>]+src="([^">]+)"/i', $post['content'] ?? '', $matches)) {
                                    $cover = $matches[1];
                                    if (str_starts_with($cover, BASEURL)) $cover = substr($cover, strlen(BASEURL));
                                }
                                ?>
                                <?php if($cover): ?>
                                    <div class="relative w-full aspect-[16/9] sm:aspect-video border-b border-outline-variant/30 overflow-hidden bg-surface-container-high">
                                        <img src="<?= BASEURL ?><?= htmlspecialchars($cover) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out" alt="Story Cover">
                                    </div>
                                <?php endif; ?>
                                <div class="p-3.5 sm:p-4 flex flex-col gap-1 bg-surface-container-lowest group-hover:bg-surface-container-low/30 transition-colors">
                                    <?php if(!empty($post['title'])): ?>
                                        <h2 class="font-title-md text-base sm:text-lg font-bold text-on-surface tracking-tight line-clamp-2">
                                            <?= htmlspecialchars($post['title']) ?>
                                        </h2>
                                    <?php endif; ?>
                                    <div class="font-body-md text-on-surface-variant text-[14px] sm:text-[15px] line-clamp-2 leading-relaxed">
                                        <?= strip_tags((string)($post['content'] ?? '')) ?>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Note Text & Grid -->
                            <div class="font-body-md text-on-surface text-[15px] leading-relaxed whitespace-pre-line mb-2">
                                <?= preg_replace('/(^|>|\s)#([a-zA-Z_][a-zA-Z0-9_]*)/', '$1<a href="' . BASEURL . '/explore/tag/$2" class="text-primary font-semibold hover:underline relative z-10" onclick="event.stopPropagation();">#$2</a>', strip_tags((string)($post['content'] ?? ''))) ?>
                            </div>
                            <?php if(!empty($post['cover_image'])): ?>
                                <?php 
                                $decoded = json_decode($post['cover_image'], true);
                                $imgs = is_array($decoded) ? $decoded : array_filter(explode(',', $post['cover_image']));
                                $imgs = array_slice($imgs, 0, 4);
                                $imgCount = count($imgs);
                                $imgJson = htmlspecialchars(json_encode(array_values($imgs)), ENT_QUOTES, 'UTF-8');
                                ?>
                                <div class="mt-1 mb-1 grid <?= $imgCount === 1 ? 'grid-cols-1' : 'grid-cols-2' ?> gap-1 sm:gap-1.5 rounded-2xl overflow-hidden border border-outline-variant/30 relative z-10">
                                    <?php foreach($imgs as $idx => $img): ?>
                                        <img src="<?= BASEURL ?><?= htmlspecialchars(trim($img)) ?>" class="w-full h-full object-cover cursor-pointer hover:opacity-90 transition-opacity <?= ($imgCount === 3 && $idx === 0) ? 'row-span-2' : '' ?> <?= $imgCount > 1 ? 'aspect-[4/3] sm:aspect-video' : 'max-h-[500px]' ?>" alt="Attachment" onclick="event.stopPropagation(); window.openLightboxGallery && openLightboxGallery(<?= $imgJson ?>, <?= $idx ?>, '<?= BASEURL ?>/post/detail/<?= (int)$post['id'] ?>')">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <!-- Action Bar (Twitter/X style hit targets) -->
                        <div class="flex items-center justify-between mt-2 max-w-md text-on-surface-variant relative z-10 -ml-2">
                            <a href="<?= BASEURL ?>/post/detail/<?= (int)$post['id'] ?>" class="group flex items-center gap-1 hover:text-primary transition-colors">
                                <div class="w-8 h-8 rounded-full group-hover:bg-primary/10 flex items-center justify-center transition-colors">
                                    <span class="material-symbols-outlined text-[18px]">chat_bubble</span>
                                </div>
                                <span class="font-body-md text-xs"><?= $post['comment_count'] ?? 0 ?></span>
                            </a>
                            <button type="button" class="group flex items-center gap-1 hover:text-primary transition-colors" onclick="event.stopPropagation();">
                                <div class="w-8 h-8 rounded-full group-hover:bg-primary/10 flex items-center justify-center transition-colors">
                                    <span class="material-symbols-outlined text-[18px]">sync_alt</span>
                                </div>
                                <span class="font-body-md text-xs"><?= $post['repost_count'] ?? 0 ?></span>
                            </button>
                            <?php 
                                $isLiked = in_array((int)$post['id'], $data['liked_posts'] ?? []);
                                $isBookmarked = in_array((int)$post['id'], $data['bookmarked_posts'] ?? []);
                            ?>
                            <button type="button" class="btn-like group flex items-center gap-1 transition-colors <?= $isLiked ? 'text-error' : 'hover:text-error' ?> active:scale-95" data-id="<?= (int)$post['id'] ?>" title="Like" onclick="event.stopPropagation();">
                                <div class="w-8 h-8 rounded-full group-hover:bg-error/10 flex items-center justify-center transition-colors">
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' <?= $isLiked ? 1 : 0 ?>;">favorite</span>
                                </div>
                                <span class="like-count font-body-md text-xs"><?= (int)($post['like_count'] ?? 0) ?></span>
                            </button>
                            <button type="button" class="btn-bookmark group flex items-center transition-colors <?= $isBookmarked ? 'text-primary' : 'hover:text-primary' ?> active:scale-95" data-id="<?= (int)$post['id'] ?>" title="Bookmark" onclick="event.stopPropagation();">
                                <div class="w-8 h-8 rounded-full group-hover:bg-primary/10 flex items-center justify-center transition-colors">
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' <?= $isBookmarked ? 1 : 0 ?>;">bookmark</span>
                                </div>
                            </button>
                            <button type="button" class="group flex items-center transition-colors hover:text-primary" onclick="event.stopPropagation(); navigator.clipboard.writeText('<?= BASEURL ?>/post/detail/<?= (int)$post['id'] ?>'); showToast('Link copied to clipboard!', 'success');">
                                <div class="w-8 h-8 rounded-full group-hover:bg-primary/10 flex items-center justify-center transition-colors">
                                    <span class="material-symbols-outlined text-[18px]">share</span>
                                </div>
                            </button>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Empty State Handling -->
            <div class="text-center p-12 bg-transparent flex flex-col items-center">
                <span class="material-symbols-outlined text-4xl text-outline mb-2">article</span>
                <h2 class="text-xl font-bold text-on-surface">No stories found</h2>
                <p class="text-on-surface-variant mt-2 text-sm max-w-sm">
                    Check back later or be the first to share a perspective!
                </p>
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
