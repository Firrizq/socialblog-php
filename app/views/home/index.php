<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="flex flex-col w-full px-4 sm:px-6 py-2">
    <!-- Feed Header Navigation Tabs -->
    <div class="sticky top-16 z-30 bg-surface/90 backdrop-blur-md pb-space-xs pt-space-xs mb-space-lg flex items-center justify-between">
        <nav class="flex items-center gap-space-lg">
            <a href="<?= BASEURL ?>/home?feed=for-you" class="relative pb-space-sm font-title-md <?= ($data['feed_type'] !== 'following') ? 'text-primary' : 'text-on-surface-variant hover:text-on-surface' ?> transition-colors flex items-center gap-space-xs">
                <span>For You</span>
                <?php if($data['feed_type'] !== 'following'): ?><span class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary rounded-full shadow-[0_0_8px_rgba(78,222,163,0.6)]"></span><?php endif; ?>
            </a>
            <a href="<?= BASEURL ?>/home?feed=following" class="relative pb-space-sm font-title-md <?= ($data['feed_type'] === 'following') ? 'text-primary' : 'text-on-surface-variant hover:text-on-surface' ?> transition-colors flex items-center gap-space-xs">
                <span>Following</span>
                <?php if($data['feed_type'] === 'following'): ?><span class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary rounded-full shadow-[0_0_8px_rgba(78,222,163,0.6)]"></span><?php endif; ?>
            </a>
        </nav>
        <div class="flex items-center gap-space-xs">
            <button type="button" onclick="const icon = this.querySelector('span'); if(icon) icon.classList.add('animate-spin'); location.reload();" class="w-9 h-9 rounded-full bg-surface-container hover:bg-surface-container-high text-on-surface-variant hover:text-primary flex items-center justify-center transition-all" title="Refresh Feed"><span class="material-symbols-outlined text-xl">refresh</span></button>
        </div>
    </div>

    <!-- Writer's Desk Quick Composer -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <section class="bg-surface-container-lowest border border-outline-variant/60 rounded-2xl p-4 sm:p-5 mb-5 shadow-xs hover:border-primary/40 transition-all">
        <div class="flex items-start gap-3.5 cursor-text" onclick="openNoteModal()">
            <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center font-bold text-on-primary shrink-0 overflow-hidden shadow-inner">
                <?php if (!empty($_SESSION['profile_picture'])): ?>
                    <img src="<?= BASEURL ?><?= htmlspecialchars($_SESSION['profile_picture']) ?>" alt="<?= htmlspecialchars($_SESSION['username']) ?>" class="w-full h-full object-cover rounded-full">
                <?php else: ?>
                    <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                <?php endif; ?>
            </div>
            <div class="flex-1 min-w-0 pt-2">
                <p class="text-on-surface-variant font-sans text-[15px]">Write a quick dispatch, reflection, or note...</p>
            </div>
        </div>
        
        <div class="flex items-center justify-between mt-3 pt-3 border-t border-outline-variant/30">
            <div class="flex items-center gap-1 sm:gap-1.5 text-on-surface-variant">
                <button type="button" onclick="openNoteModal(); setTimeout(() => document.getElementById('noteImageInput')?.click(), 100);" class="flex items-center gap-1.5 px-3 py-1.5 rounded-full hover:bg-surface-container hover:text-primary text-xs font-medium transition-colors" title="Add Media">
                    <span class="material-symbols-outlined text-lg">image</span>
                    <span class="hidden sm:inline">Media</span>
                </button>
                <button type="button" onclick="openNoteModal();" class="flex items-center gap-1.5 px-3 py-1.5 rounded-full hover:bg-surface-container hover:text-primary text-xs font-medium transition-colors" title="Topic / Hashtag">
                    <span class="material-symbols-outlined text-lg">tag</span>
                    <span class="hidden sm:inline">Topic</span>
                </button>
            </div>
            <div class="flex items-center gap-2">
                <a href="<?= BASEURL ?>/post/create" class="btn-secondary px-3.5 py-1.5 text-xs">
                    <span class="material-symbols-outlined text-[16px]">history_edu</span>
                    <span>Write Story</span>
                </a>
                <button type="button" onclick="openNoteModal()" class="btn-primary px-4 py-1.5 text-xs">
                    Post Note
                </button>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Feed Post Stream -->
    <div class="flex flex-col gap-4 sm:gap-5 mt-1">
        <?php if (!empty($data['posts']) && is_array($data['posts'])): ?>
            <?php 
                $heroRendered = false; 
            ?>
            <?php foreach ($data['posts'] as $post): ?>
                <?php 
                    $postType = strtolower($post['post_type'] ?? $post['type'] ?? 'story');
                    $postUid = !empty($post['uid']) ? $post['uid'] : $post['id'];
                    $postAuthor = $post['username'] ?? 'user';
                    $postUrl = BASEURL . '/' . $postAuthor . '/' . $postType . '/' . $postUid;
                    $authorUrl = BASEURL . '/' . urlencode($postAuthor);

                    // Media extraction
                    $cover = '';
                    if (!empty($post['media'])) {
                        $cover = $post['media'];
                    } elseif (!empty($post['cover_image'])) {
                        $decoded = json_decode((string)$post['cover_image'], true);
                        $imgs = is_array($decoded) ? $decoded : array_filter(explode(',', (string)$post['cover_image']));
                        $cover = !empty($imgs) ? trim($imgs[0]) : '';
                    }
                    if (empty($cover) && preg_match('/<(?:img|video|source)[^>]+src="([^">]+)"/i', $post['content'] ?? '', $matches)) {
                        $cover = $matches[1];
                        if (str_starts_with($cover, BASEURL)) $cover = substr($cover, strlen(BASEURL));
                    }

                    $ext = strtolower(pathinfo((string)$cover, PATHINFO_EXTENSION));
                    $videoExtensions = ['mp4', 'webm', 'ogg', 'mov'];
                    $isVideo = in_array($ext, $videoExtensions, true);

                    $mediaSrc = '';
                    if (!empty($cover)) {
                        if (str_starts_with($cover, 'http://') || str_starts_with($cover, 'https://')) {
                            $mediaSrc = $cover;
                        } elseif (str_starts_with($cover, '/uploads/') || str_starts_with($cover, 'uploads/')) {
                            $mediaSrc = BASEURL . '/' . ltrim($cover, '/');
                        } else {
                            $mediaSrc = BASEURL . '/uploads/' . ltrim($cover, '/');
                        }
                    }
                    $mimeType = match($ext) {
                        'webm' => 'video/webm',
                        'ogg' => 'video/ogg',
                        default => 'video/mp4'
                    };

                    // Excerpt extraction
                    $excerpt = '';
                    if (preg_match('/<h2[^>]*class="[^"]*story-subtitle[^"]*"[^>]*>(.*?)<\/h2>/is', $post['content'] ?? '', $subMatches)) {
                        $excerpt = trim(strip_tags($subMatches[1]));
                    }
                    if (empty($excerpt)) {
                        $cleanText = str_replace(['<p>', '<br>', '</div>', '</li>', '</h1>', '</h2>', '</h3>'], ' ', (string)($post['content'] ?? ''));
                        $excerpt = trim(strip_tags($cleanText));
                    }

                    $isLiked = in_array((int)$post['id'], $data['liked_posts'] ?? []);
                    $isBookmarked = in_array((int)$post['id'], $data['bookmarked_posts'] ?? []);
                    $isReposted = in_array((int)$post['id'], $data['reposted_posts'] ?? []) || !empty($post['is_reposted']);

                    $isHeroCandidate = (!$heroRendered && ($data['feed_type'] ?? '') !== 'following' && $postType === 'story' && empty($post['repost_user_id']));
                    if ($isHeroCandidate) {
                        $heroRendered = true;
                    }
                ?>

                <?php if ($isHeroCandidate): ?>
                    <!-- FEATURED EDITORIAL HERO CARD -->
                    <article class="p-5 sm:p-7 bg-surface-container-lowest border border-outline-variant/80 rounded-2xl shadow-xs hover:border-primary/50 hover:shadow-md transition-all flex flex-col cursor-pointer group relative" onclick="if(!event.target.closest('a') && !event.target.closest('button')) window.location.href='<?= $postUrl ?>';">
                        
                        <!-- Top Featured Badge & Reading Time -->
                        <div class="flex items-center justify-between mb-4">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-semibold tracking-wider uppercase font-sans">
                                <span class="material-symbols-outlined text-[15px]">auto_stories</span>
                                Featured Story
                            </span>
                            <span class="font-sans text-xs text-on-surface-variant font-medium">
                                <?= $post['read_time_minutes'] ?? 1 ?> min read
                            </span>
                        </div>

                        <!-- Author Header -->
                        <div class="flex items-center justify-between gap-2 mb-3.5">
                            <div class="flex items-center gap-3 min-w-0">
                                <a href="<?= $authorUrl ?>" class="profile-hover-trigger avatar-link block w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-surface-container-high flex items-center justify-center font-bold text-primary hover:opacity-80 transition-opacity overflow-hidden relative z-10" data-username="<?= htmlspecialchars($post['username'] ?? '') ?>" title="View Profile">
                                    <?php if (!empty($post['profile_picture'])): ?>
                                        <img src="<?= BASEURL ?><?= htmlspecialchars($post['profile_picture']) ?>" alt="<?= htmlspecialchars($post['username'] ?? '') ?>" class="w-full h-full object-cover rounded-full avatar-img">
                                    <?php else: ?>
                                        <?= htmlspecialchars(substr($post['username'] ?? 'U', 0, 1)) ?>
                                    <?php endif; ?>
                                </a>
                                <div class="flex flex-col min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <a href="<?= $authorUrl ?>" class="profile-hover-trigger font-title-md font-bold text-on-surface hover:underline truncate relative z-10 text-[15px]" data-username="<?= htmlspecialchars($post['username'] ?? '') ?>">
                                            <?= htmlspecialchars($post['name'] ?? $post['username'] ?? 'Anonymous') ?>
                                        </a>
                                        <span class="text-on-surface-variant/60">·</span>
                                        <time class="timeago font-body-md text-on-surface-variant text-xs hover:underline relative z-10" datetime="<?= date('c', strtotime($post['created_at'])) ?>"></time>
                                    </div>
                                    <span class="font-body-md text-on-surface-variant text-xs">@<?= htmlspecialchars($post['username'] ?? 'anon') ?></span>
                                </div>
                            </div>

                            <!-- Dropdown Options -->
                            <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
                                <div class="relative dropdown-container z-20 shrink-0">
                                    <button type="button" onclick="toggleMenu(event, 'menu-hero-<?= $post['id'] ?>')" class="text-on-surface-variant hover:text-primary w-8 h-8 rounded-full hover:bg-primary/10 transition-colors flex items-center justify-center">
                                        <span class="material-symbols-outlined text-[18px]">more_horiz</span>
                                    </button>
                                    <div id="menu-hero-<?= $post['id'] ?>" class="hidden absolute right-0 top-full mt-1 w-36 bg-surface-container-high border border-outline-variant/30 rounded-xl shadow-xl z-[60] overflow-hidden flex flex-col py-1">
                                        <a href="<?= BASEURL ?>/post/edit/<?= $post['id'] ?>" class="px-4 py-2 text-sm text-on-surface font-title-md hover:bg-surface-container flex items-center gap-3 transition-colors"><span class="material-symbols-outlined text-base">edit</span> Edit</a>
                                        <form action="<?= BASEURL ?>/post/delete/<?= $post['id'] ?>" method="POST" class="m-0 p-0" data-confirm="Are you sure you want to delete this story? This action cannot be undone." data-confirm-title="Delete Story" data-confirm-btn="Delete">
                                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-error font-title-md hover:bg-error-container/20 flex items-center gap-3 transition-colors"><span class="material-symbols-outlined text-base">delete</span> Delete</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Hero Headline -->
                        <?php if(!empty($post['title'])): ?>
                            <h2 class="font-serif text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight text-on-surface leading-[1.22] group-hover:text-primary transition-colors text-wrap-balance mb-3">
                                <?= htmlspecialchars($post['title']) ?>
                            </h2>
                        <?php endif; ?>

                        <!-- Hero Excerpt -->
                        <?php if(!empty($excerpt)): ?>
                            <p class="font-sans text-on-surface-variant text-base sm:text-[17px] leading-relaxed line-clamp-3 mb-4 font-normal">
                                <?= htmlspecialchars($excerpt) ?>
                            </p>
                        <?php endif; ?>

                        <!-- Hero Media -->
                        <?php if(!empty($cover)): ?>
                            <?php if($isVideo): ?>
                                <div class="relative w-full rounded-xl overflow-hidden border border-outline-variant/30 bg-black mb-4" onclick="event.stopPropagation();">
                                    <video controls class="w-full rounded-xl max-h-[440px] object-contain bg-black">
                                        <source src="<?= htmlspecialchars($mediaSrc) ?>" type="<?= $mimeType ?>">
                                        Your browser does not support the video tag.
                                    </video>
                                </div>
                            <?php else: ?>
                                <div class="relative w-full aspect-[16/9] sm:aspect-[21/9] rounded-xl overflow-hidden border border-outline-variant/40 mb-4 bg-surface-container-high">
                                    <img src="<?= htmlspecialchars($mediaSrc) ?>" class="post-media-image w-full h-full object-cover group-hover:scale-[1.02] transition-transform duration-700 ease-out" alt="Featured Story Cover">
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <!-- Hero Action Bar -->
                        <div class="flex items-center justify-between pt-3 border-t border-outline-variant/30 text-on-surface-variant relative z-10">
                            <div class="flex items-center gap-6">
                                <a href="<?= $postUrl ?>#discussion" class="group/btn flex items-center gap-1.5 hover:text-primary transition-colors">
                                    <span class="material-symbols-outlined text-[19px]">chat_bubble</span>
                                    <span class="font-body-md text-xs"><?= $post['comment_count'] ?? 0 ?></span>
                                </a>
                                <button type="button" class="btn-repost group/btn flex items-center gap-1.5 transition-colors <?= $isReposted ? 'text-emerald-500' : 'hover:text-emerald-500' ?> active:scale-95" data-id="<?= (int)$post['id'] ?>" title="Repost">
                                    <span class="material-symbols-outlined text-[19px] <?= $isReposted ? 'font-bold' : '' ?>">sync_alt</span>
                                    <span class="repost-count font-body-md text-xs"><?= (int)($post['repost_count'] ?? 0) ?></span>
                                </button>
                                <button type="button" class="btn-like group/btn flex items-center gap-1.5 transition-colors <?= $isLiked ? 'text-error' : 'hover:text-error' ?> active:scale-95" data-id="<?= (int)$post['id'] ?>" title="Like">
                                    <span class="material-symbols-outlined text-[19px]" style="font-variation-settings: 'FILL' <?= $isLiked ? 1 : 0 ?>;">favorite</span>
                                    <span class="like-count font-body-md text-xs"><?= (int)($post['like_count'] ?? 0) ?></span>
                                </button>
                            </div>
                            <div class="flex items-center gap-3">
                                <button type="button" class="btn-bookmark group/btn flex items-center gap-1.5 transition-colors <?= $isBookmarked ? 'text-primary' : 'hover:text-primary' ?> active:scale-95" data-id="<?= (int)$post['id'] ?>" title="Bookmark">
                                    <span class="material-symbols-outlined text-[19px]" style="font-variation-settings: 'FILL' <?= $isBookmarked ? 1 : 0 ?>;">bookmark</span>
                                    <span class="bookmark-count font-body-md text-xs"><?= (int)($post['bookmark_count'] ?? 0) ?></span>
                                </button>
                                <button type="button" class="hover:text-primary transition-colors p-1" onclick="event.stopPropagation(); navigator.clipboard.writeText('<?= $postUrl ?>'); showToast('Link copied to clipboard!', 'success');" title="Share">
                                    <span class="material-symbols-outlined text-[19px]">share</span>
                                </button>
                            </div>
                        </div>
                    </article>

                <?php elseif ($postType === 'story'): ?>
                    <!-- MAGAZINE STORY CARD -->
                    <article class="p-4 sm:p-5 bg-surface-container-lowest border border-outline-variant/40 hover:border-primary/40 rounded-2xl transition-all shadow-2xs hover:shadow-xs flex flex-col cursor-pointer group relative" onclick="if(!event.target.closest('a') && !event.target.closest('button')) window.location.href='<?= $postUrl ?>';">
                        
                        <?php if (!empty($post['repost_user_id'])): ?>
                            <div class="flex items-center gap-1.5 text-xs font-semibold text-on-surface-variant mb-2.5 pb-2 border-b border-outline-variant/20">
                                <span class="material-symbols-outlined text-[15px] text-emerald-500">sync_alt</span>
                                <?php if (!empty($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$post['repost_user_id']): ?>
                                    <span>You reposted</span>
                                <?php else: ?>
                                    <a href="<?= BASEURL ?>/<?= urlencode($post['repost_username'] ?? '') ?>" class="profile-hover-trigger hover:underline font-bold text-on-surface relative z-10" data-username="<?= htmlspecialchars($post['repost_username'] ?? '') ?>" onclick="event.stopPropagation();">
                                        <?= htmlspecialchars($post['repost_name'] ?? $post['repost_username'] ?? 'Someone') ?>
                                    </a>
                                    <span>reposted</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Author & Meta Header -->
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <div class="flex items-center gap-2.5 min-w-0 flex-wrap text-sm">
                                <a href="<?= $authorUrl ?>" class="profile-hover-trigger avatar-link block w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-surface-container-high flex items-center justify-center font-bold text-primary hover:opacity-80 transition-opacity overflow-hidden relative z-10" data-username="<?= htmlspecialchars($post['username'] ?? '') ?>" title="View Profile">
                                    <?php if (!empty($post['profile_picture'])): ?>
                                        <img src="<?= BASEURL ?><?= htmlspecialchars($post['profile_picture']) ?>" alt="<?= htmlspecialchars($post['username'] ?? '') ?>" class="w-full h-full object-cover rounded-full avatar-img">
                                    <?php else: ?>
                                        <?= htmlspecialchars(substr($post['username'] ?? 'U', 0, 1)) ?>
                                    <?php endif; ?>
                                </a>
                                <a href="<?= $authorUrl ?>" class="profile-hover-trigger font-title-md font-bold text-on-surface hover:underline truncate relative z-10" data-username="<?= htmlspecialchars($post['username'] ?? '') ?>">
                                    <?= htmlspecialchars($post['name'] ?? $post['username'] ?? 'Anonymous') ?>
                                </a>
                                <span class="text-on-surface-variant/60">·</span>
                                <time class="timeago font-body-md text-on-surface-variant text-xs hover:underline relative z-10" datetime="<?= date('c', strtotime($post['created_at'])) ?>"></time>
                                
                                <span class="inline-flex ml-auto sm:ml-1 px-2 py-0.5 rounded-full bg-surface-container-low border border-outline-variant/30 text-[11px] font-medium text-on-surface-variant items-center gap-1">
                                    <span class="material-symbols-outlined text-[12px]">auto_stories</span>
                                    <?= $post['read_time_minutes'] ?? 1 ?> min read
                                </span>
                            </div>

                            <!-- Dropdown Options -->
                            <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
                                <div class="relative dropdown-container z-20 shrink-0">
                                    <button type="button" onclick="toggleMenu(event, 'menu-<?= $post['id'] ?>')" class="text-on-surface-variant hover:text-primary w-8 h-8 rounded-full hover:bg-primary/10 transition-colors flex items-center justify-center -mr-1">
                                        <span class="material-symbols-outlined text-[18px]">more_horiz</span>
                                    </button>
                                    <div id="menu-<?= $post['id'] ?>" class="hidden absolute right-0 top-full mt-1 w-36 bg-surface-container-high border border-outline-variant/30 rounded-xl shadow-xl z-[60] overflow-hidden flex flex-col py-1">
                                        <a href="<?= BASEURL ?>/post/edit/<?= $post['id'] ?>" class="px-4 py-2 text-sm text-on-surface font-title-md hover:bg-surface-container flex items-center gap-3 transition-colors"><span class="material-symbols-outlined text-base">edit</span> Edit</a>
                                        <form action="<?= BASEURL ?>/post/delete/<?= $post['id'] ?>" method="POST" class="m-0 p-0" data-confirm="Are you sure you want to delete this story? This action cannot be undone." data-confirm-title="Delete Story" data-confirm-btn="Delete">
                                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-error font-title-md hover:bg-error-container/20 flex items-center gap-3 transition-colors"><span class="material-symbols-outlined text-base">delete</span> Delete</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Story Title & Excerpt -->
                        <div class="mt-1 mb-2.5 flex flex-col gap-1.5">
                            <?php if(!empty($post['title'])): ?>
                                <h2 class="font-serif text-xl sm:text-2xl font-bold text-on-surface tracking-tight leading-snug group-hover:text-primary transition-colors text-wrap-balance">
                                    <?= htmlspecialchars($post['title']) ?>
                                </h2>
                            <?php endif; ?>
                            <?php if(!empty($excerpt)): ?>
                                <p class="font-sans text-on-surface-variant text-[14px] sm:text-[15px] line-clamp-2 leading-relaxed">
                                    <?= htmlspecialchars($excerpt) ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Media Cover -->
                        <?php if(!empty($cover)): ?>
                            <?php if($isVideo): ?>
                                <div class="relative w-full rounded-xl overflow-hidden border border-outline-variant/30 bg-black mb-3" onclick="event.stopPropagation();">
                                    <video controls class="w-full rounded-xl max-h-96 object-contain bg-black">
                                        <source src="<?= htmlspecialchars($mediaSrc) ?>" type="<?= $mimeType ?>">
                                        Your browser does not support the video tag.
                                    </video>
                                </div>
                            <?php else: ?>
                                <div class="relative w-full aspect-[16/9] sm:aspect-[2/1] rounded-xl overflow-hidden border border-outline-variant/30 mb-3 bg-surface-container-high">
                                    <img src="<?= htmlspecialchars($mediaSrc) ?>" class="post-media-image w-full h-full object-cover group-hover:scale-[1.02] transition-transform duration-700 ease-out" alt="Story Cover">
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <!-- Action Bar -->
                        <div class="flex items-center justify-between pt-2 border-t border-outline-variant/20 text-on-surface-variant relative z-10">
                            <div class="flex items-center gap-6">
                                <a href="<?= $postUrl ?>#discussion" class="group/btn flex items-center gap-1.5 hover:text-primary transition-colors">
                                    <span class="material-symbols-outlined text-[18px]">chat_bubble</span>
                                    <span class="font-body-md text-xs"><?= $post['comment_count'] ?? 0 ?></span>
                                </a>
                                <button type="button" class="btn-repost group/btn flex items-center gap-1.5 transition-colors <?= $isReposted ? 'text-emerald-500' : 'hover:text-emerald-500' ?> active:scale-95" data-id="<?= (int)$post['id'] ?>" title="Repost">
                                    <span class="material-symbols-outlined text-[18px] <?= $isReposted ? 'font-bold' : '' ?>">sync_alt</span>
                                    <span class="repost-count font-body-md text-xs"><?= (int)($post['repost_count'] ?? 0) ?></span>
                                </button>
                                <button type="button" class="btn-like group/btn flex items-center gap-1.5 transition-colors <?= $isLiked ? 'text-error' : 'hover:text-error' ?> active:scale-95" data-id="<?= (int)$post['id'] ?>" title="Like">
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' <?= $isLiked ? 1 : 0 ?>;">favorite</span>
                                    <span class="like-count font-body-md text-xs"><?= (int)($post['like_count'] ?? 0) ?></span>
                                </button>
                            </div>
                            <div class="flex items-center gap-3">
                                <button type="button" class="btn-bookmark group/btn flex items-center gap-1.5 transition-colors <?= $isBookmarked ? 'text-primary' : 'hover:text-primary' ?> active:scale-95" data-id="<?= (int)$post['id'] ?>" title="Bookmark">
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' <?= $isBookmarked ? 1 : 0 ?>;">bookmark</span>
                                    <span class="bookmark-count font-body-md text-xs"><?= (int)($post['bookmark_count'] ?? 0) ?></span>
                                </button>
                                <button type="button" class="hover:text-primary transition-colors p-1" onclick="event.stopPropagation(); navigator.clipboard.writeText('<?= $postUrl ?>'); showToast('Link copied to clipboard!', 'success');" title="Share">
                                    <span class="material-symbols-outlined text-[18px]">share</span>
                                </button>
                            </div>
                        </div>
                    </article>

                <?php else: ?>
                    <!-- DISPATCH INDEX CARD (NOTE) -->
                    <article class="p-4 sm:p-5 bg-surface-container-low/40 border border-outline-variant/40 hover:bg-surface-container-low/75 hover:border-primary/30 rounded-2xl transition-all shadow-2xs flex flex-col cursor-pointer group relative" onclick="if(!event.target.closest('a') && !event.target.closest('button')) window.location.href='<?= $postUrl ?>';">
                        
                        <?php if (!empty($post['repost_user_id'])): ?>
                            <div class="flex items-center gap-1.5 text-xs font-semibold text-on-surface-variant mb-2.5 pb-2 border-b border-outline-variant/20">
                                <span class="material-symbols-outlined text-[15px] text-emerald-500">sync_alt</span>
                                <?php if (!empty($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$post['repost_user_id']): ?>
                                    <span>You reposted</span>
                                <?php else: ?>
                                    <a href="<?= BASEURL ?>/<?= urlencode($post['repost_username'] ?? '') ?>" class="profile-hover-trigger hover:underline font-bold text-on-surface relative z-10" data-username="<?= htmlspecialchars($post['repost_username'] ?? '') ?>" onclick="event.stopPropagation();">
                                        <?= htmlspecialchars($post['repost_name'] ?? $post['repost_username'] ?? 'Someone') ?>
                                    </a>
                                    <span>reposted</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Note Header -->
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <div class="flex items-center gap-2.5 min-w-0 flex-wrap text-sm">
                                <a href="<?= $authorUrl ?>" class="profile-hover-trigger avatar-link block w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-surface-container-high flex items-center justify-center font-bold text-primary hover:opacity-80 transition-opacity overflow-hidden relative z-10" data-username="<?= htmlspecialchars($post['username'] ?? '') ?>" title="View Profile">
                                    <?php if (!empty($post['profile_picture'])): ?>
                                        <img src="<?= BASEURL ?><?= htmlspecialchars($post['profile_picture']) ?>" alt="<?= htmlspecialchars($post['username'] ?? '') ?>" class="w-full h-full object-cover rounded-full avatar-img">
                                    <?php else: ?>
                                        <?= htmlspecialchars(substr($post['username'] ?? 'U', 0, 1)) ?>
                                    <?php endif; ?>
                                </a>
                                <a href="<?= $authorUrl ?>" class="profile-hover-trigger font-title-md font-bold text-on-surface hover:underline truncate relative z-10" data-username="<?= htmlspecialchars($post['username'] ?? '') ?>">
                                    <?= htmlspecialchars($post['name'] ?? $post['username'] ?? 'Anonymous') ?>
                                </a>
                                <span class="font-body-md text-on-surface-variant text-xs">@<?= htmlspecialchars($post['username'] ?? 'anon') ?></span>
                                <span class="text-on-surface-variant/60">·</span>
                                <time class="timeago font-body-md text-on-surface-variant text-xs hover:underline relative z-10" datetime="<?= date('c', strtotime($post['created_at'])) ?>"></time>
                                
                                <span class="inline-flex ml-auto sm:ml-1 px-2 py-0.5 rounded-full bg-surface-container-high text-[11px] font-medium text-on-surface-variant items-center gap-1">
                                    <span class="material-symbols-outlined text-[12px]">chat</span>
                                    Note
                                </span>
                            </div>

                            <!-- Dropdown Options -->
                            <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
                                <div class="relative dropdown-container z-20 shrink-0">
                                    <button type="button" onclick="toggleMenu(event, 'menu-<?= $post['id'] ?>')" class="text-on-surface-variant hover:text-primary w-8 h-8 rounded-full hover:bg-primary/10 transition-colors flex items-center justify-center -mr-1">
                                        <span class="material-symbols-outlined text-[18px]">more_horiz</span>
                                    </button>
                                    <div id="menu-<?= $post['id'] ?>" class="hidden absolute right-0 top-full mt-1 w-36 bg-surface-container-high border border-outline-variant/30 rounded-xl shadow-xl z-[60] overflow-hidden flex flex-col py-1">
                                        <a href="<?= BASEURL ?>/post/edit_note/<?= $post['id'] ?>" class="px-4 py-2 text-sm text-on-surface font-title-md hover:bg-surface-container flex items-center gap-3 transition-colors"><span class="material-symbols-outlined text-base">edit</span> Edit</a>
                                        <form action="<?= BASEURL ?>/post/delete/<?= $post['id'] ?>" method="POST" class="m-0 p-0" data-confirm="Are you sure you want to delete this note? This action cannot be undone." data-confirm-title="Delete Note" data-confirm-btn="Delete">
                                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-error font-title-md hover:bg-error-container/20 flex items-center gap-3 transition-colors"><span class="material-symbols-outlined text-base">delete</span> Delete</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Note Body -->
                        <div class="font-sans text-on-surface text-[15px] sm:text-base leading-relaxed whitespace-pre-line mt-1.5 mb-3">
                            <?= preg_replace('/(^|>|\s)#([a-zA-Z_][a-zA-Z0-9_]*)/', '$1<a href="' . BASEURL . '/explore/tag/$2" class="text-primary font-semibold hover:underline relative z-10" onclick="event.stopPropagation();">#$2</a>', strip_tags((string)($post['content'] ?? ''))) ?>
                        </div>

                        <!-- Note Media Gallery -->
                        <?php if(!empty($post['cover_image'])): ?>
                            <?php 
                            $decoded = json_decode((string)$post['cover_image'], true);
                            $imgs = is_array($decoded) ? $decoded : array_filter(explode(',', (string)$post['cover_image']));
                            $imgs = array_slice($imgs, 0, 4);
                            $imgCount = count($imgs);
                            $imgJson = htmlspecialchars(json_encode(array_values($imgs)), ENT_QUOTES, 'UTF-8');
                            ?>
                            <div class="mt-1 mb-3 grid <?= $imgCount === 1 ? 'grid-cols-1' : 'grid-cols-2' ?> gap-1.5 rounded-xl overflow-hidden border border-outline-variant/30 relative z-10">
                                <?php foreach($imgs as $idx => $img): ?>
                                    <?php 
                                    $noteFile = trim((string)$img);
                                    $noteExt = strtolower(pathinfo($noteFile, PATHINFO_EXTENSION));
                                    $noteIsVideo = in_array($noteExt, ['mp4', 'webm', 'ogg', 'mov'], true);
                                    $noteSrc = (str_starts_with($noteFile, 'http://') || str_starts_with($noteFile, 'https://')) 
                                        ? $noteFile 
                                        : ((str_starts_with($noteFile, '/uploads/') || str_starts_with($noteFile, 'uploads/')) 
                                            ? (BASEURL . '/' . ltrim($noteFile, '/')) 
                                            : (BASEURL . '/uploads/' . ltrim($noteFile, '/')));
                                    ?>
                                    <?php if($noteIsVideo): ?>
                                        <div class="w-full bg-black rounded-lg overflow-hidden <?= ($imgCount === 3 && $idx === 0) ? 'row-span-2' : '' ?>" onclick="event.stopPropagation();">
                                            <video controls class="w-full rounded-lg max-h-96 object-contain bg-black">
                                                <source src="<?= htmlspecialchars($noteSrc) ?>" type="video/<?= $noteExt === 'webm' ? 'webm' : ($noteExt === 'ogg' ? 'ogg' : 'mp4') ?>">
                                                Your browser does not support the video tag.
                                            </video>
                                        </div>
                                    <?php else: ?>
                                        <img src="<?= htmlspecialchars($noteSrc) ?>" class="post-media-image w-full h-full object-cover cursor-pointer hover:opacity-90 transition-opacity <?= ($imgCount === 3 && $idx === 0) ? 'row-span-2' : '' ?> <?= $imgCount > 1 ? 'aspect-[4/3] sm:aspect-video' : 'max-h-[500px]' ?>" alt="Attachment" onclick="event.stopPropagation(); window.openLightboxGallery && openLightboxGallery(<?= $imgJson ?>, <?= $idx ?>, '<?= $postUrl ?>')">
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Action Bar -->
                        <div class="flex items-center justify-between pt-2 border-t border-outline-variant/20 text-on-surface-variant relative z-10">
                            <div class="flex items-center gap-6">
                                <a href="<?= $postUrl ?>#discussion" class="group/btn flex items-center gap-1.5 hover:text-primary transition-colors">
                                    <span class="material-symbols-outlined text-[18px]">chat_bubble</span>
                                    <span class="font-body-md text-xs"><?= $post['comment_count'] ?? 0 ?></span>
                                </a>
                                <button type="button" class="btn-repost group/btn flex items-center gap-1.5 transition-colors <?= $isReposted ? 'text-emerald-500' : 'hover:text-emerald-500' ?> active:scale-95" data-id="<?= (int)$post['id'] ?>" title="Repost">
                                    <span class="material-symbols-outlined text-[18px] <?= $isReposted ? 'font-bold' : '' ?>">sync_alt</span>
                                    <span class="repost-count font-body-md text-xs"><?= (int)($post['repost_count'] ?? 0) ?></span>
                                </button>
                                <button type="button" class="btn-like group/btn flex items-center gap-1.5 transition-colors <?= $isLiked ? 'text-error' : 'hover:text-error' ?> active:scale-95" data-id="<?= (int)$post['id'] ?>" title="Like">
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' <?= $isLiked ? 1 : 0 ?>;">favorite</span>
                                    <span class="like-count font-body-md text-xs"><?= (int)($post['like_count'] ?? 0) ?></span>
                                </button>
                            </div>
                            <div class="flex items-center gap-3">
                                <button type="button" class="btn-bookmark group/btn flex items-center gap-1.5 transition-colors <?= $isBookmarked ? 'text-primary' : 'hover:text-primary' ?> active:scale-95" data-id="<?= (int)$post['id'] ?>" title="Bookmark">
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' <?= $isBookmarked ? 1 : 0 ?>;">bookmark</span>
                                    <span class="bookmark-count font-body-md text-xs"><?= (int)($post['bookmark_count'] ?? 0) ?></span>
                                </button>
                                <button type="button" class="hover:text-primary transition-colors p-1" onclick="event.stopPropagation(); navigator.clipboard.writeText('<?= $postUrl ?>'); showToast('Link copied to clipboard!', 'success');" title="Share">
                                    <span class="material-symbols-outlined text-[18px]">share</span>
                                </button>
                            </div>
                        </div>
                    </article>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Empty State Handling -->
            <div class="text-center p-12 bg-surface-container-lowest/50 border border-outline-variant/40 rounded-2xl flex flex-col items-center">
                <span class="material-symbols-outlined text-4xl text-outline mb-2">article</span>
                <h2 class="text-xl font-bold font-serif text-on-surface">No stories found</h2>
                <p class="text-on-surface-variant mt-2 text-sm max-w-sm">
                    Check back later or be the first to share a perspective!
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Short-form Note Modal (Substack / Twitter Style) -->
<?php if (isset($_SESSION['user_id'])): ?>
<div id="noteModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4" onclick="if(event.target === this) closeNoteModal();">
    <div class="w-full max-w-lg bg-surface-container-lowest border border-outline-variant/60 rounded-2xl p-5 sm:p-6 shadow-2xl relative">
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
                    <a href="<?= BASEURL ?>/profile?tab=drafts" class="text-xs font-semibold text-on-surface-variant hover:text-primary transition-colors">Drafts</a>
                    <button type="button" onclick="closeNoteModal()" class="w-7 h-7 rounded-full hover:bg-surface-container text-on-surface-variant hover:text-on-surface flex items-center justify-center transition-colors ml-1" title="Close">
                        <span class="material-symbols-outlined text-base">close</span>
                    </button>
                </div>
            </div>

            <!-- Modal Body (Transparent Textarea) -->
            <div>                <textarea 
                    name="content" 
                    id="noteModalTextarea" 
                    rows="4" 
                    placeholder="What's on your mind?" 
                    required 
                    class="bg-transparent border-none outline-none focus:ring-0 text-on-surface text-lg placeholder:text-outline-variant resize-none w-full p-0 leading-relaxed"
                ></textarea>
                <input type="file" id="noteImageInput" accept="image/png, image/jpeg, image/gif, video/mp4, video/webm, video/quicktime, video/ogg" class="hidden" multiple>
                <input type="hidden" name="cover_image" id="noteCoverImageInput" value="">
                <div id="noteImagePreviewContainer" class="hidden relative mt-3 w-full"></div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between pt-2 border-t border-outline-variant/20">
                <!-- Left side: Media Icons -->
                <div class="flex items-center gap-1.5 sm:gap-2 text-outline">
                    <button type="button" onclick="document.getElementById('noteImageInput').click()" class="p-1 rounded-full hover:bg-surface-container hover:text-primary transition-colors" title="Add Image">
                        <span class="material-symbols-outlined text-xl">image</span>
                    </button>
                    <button type="button" onclick="document.getElementById('noteImageInput').click()" class="p-1 rounded-full hover:bg-surface-container hover:text-primary transition-colors" title="Add Video">
                        <span class="material-symbols-outlined text-xl">videocam</span>
                    </button>
                    <div class="relative">
                        <button type="button" onclick="toggleEmojiPicker(event)" class="p-1 rounded-full hover:bg-surface-container hover:text-primary transition-colors" title="Add Emoji">
                            <span class="material-symbols-outlined text-xl">mood</span>
                        </button>
                        <!-- Native Emoji Popover -->
                        <div id="emoji-picker-popover" class="hidden absolute bottom-full left-0 mb-2 p-2.5 bg-surface-container-high/95 backdrop-blur-md border border-outline-variant/40 rounded-2xl shadow-xl z-50 w-64 max-h-48 overflow-y-auto grid grid-cols-6 gap-1 select-none">
                        </div>
                    </div>
                    <button type="button" onclick="showToast('Scheduled publishing coming soon!', 'info')" class="p-1 rounded-full opacity-40 hover:opacity-100 hover:bg-surface-container hover:text-primary transition-all cursor-not-allowed" title="Schedule (Coming soon)">
                        <span class="material-symbols-outlined text-xl">calendar_month</span>
                    </button>
                </div>

                <!-- Right side: Cancel, Draft & Post Buttons -->
                <div class="flex items-center gap-2">
                    <button type="button" onclick="closeNoteModal()" class="btn-ghost px-3.5 py-1.5 text-xs">Cancel</button>
                    <button type="submit" name="action" value="draft" class="btn-secondary px-3.5 py-1.5 text-xs">Draft</button>
                    <button type="submit" name="action" value="publish" class="btn-primary px-5 py-1.5 text-xs">Post</button>
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

const popularEmojis = ['😀','😂','🤣','😍','🥳','😎','🤔','👍','🙌','🔥','✨','🎉','🚀','❤️','💯','💡','👏','🙏','☕','🍕','🌱','💪','📚','📝'];

function toggleEmojiPicker(e) {
    e.stopPropagation();
    const popover = document.getElementById('emoji-picker-popover');
    if (!popover) return;
    if (popover.classList.contains('hidden')) {
        if (!popover.dataset.loaded) {
            popover.innerHTML = popularEmojis.map(emoji => `
                <button type="button" onclick="insertEmoji('${emoji}')" class="w-8 h-8 rounded-lg hover:bg-surface-container-highest flex items-center justify-center text-lg hover:scale-110 transition-transform">
                    ${emoji}
                </button>
            `).join('');
            popover.dataset.loaded = 'true';
        }
        popover.classList.remove('hidden');
    } else {
        popover.classList.add('hidden');
    }
}

function insertEmoji(emoji) {
    const textarea = document.getElementById('noteModalTextarea');
    if (!textarea) return;
    const start = textarea.selectionStart ?? textarea.value.length;
    const end = textarea.selectionEnd ?? textarea.value.length;
    textarea.value = textarea.value.substring(0, start) + emoji + textarea.value.substring(end);
    textarea.selectionStart = textarea.selectionEnd = start + emoji.length;
    textarea.focus();
    const popover = document.getElementById('emoji-picker-popover');
    if (popover) popover.classList.add('hidden');
}

// Close emoji picker on outside click
document.addEventListener('click', function(e) {
    const popover = document.getElementById('emoji-picker-popover');
    if (popover && !popover.classList.contains('hidden') && !e.target.closest('#emoji-picker-popover') && !e.target.closest('[title="Add Emoji"]')) {
        popover.classList.add('hidden');
    }
});

const BASE_URL = '<?= BASEURL ?>';

let uploadedNoteImages = [];

// Pre-fill if editing (only needed in edit_note.php, but safe to include globally)
const hiddenInputEl = document.getElementById('noteCoverImageInput');
if (hiddenInputEl && hiddenInputEl.value) {
    try { uploadedNoteImages = JSON.parse(hiddenInputEl.value); } 
    catch(e) { uploadedNoteImages = hiddenInputEl.value.split(',').filter(Boolean); }
    if(uploadedNoteImages.length > 0) renderNoteImagePreviews();
}

async function uploadNoteImage(file) {
    if (!file) return;

    // Check client-side 200MB limit
    const MAX_FILE_SIZE = 200 * 1024 * 1024;
    if (file.size > MAX_FILE_SIZE) {
        const mb = (file.size / (1024 * 1024)).toFixed(1);
        showToast(`File "${file.name}" is too large (${mb}MB). Maximum allowed is 200MB.`, 'error');
        return;
    }

    const isMedia = file.type.startsWith('image/') || file.type.startsWith('video/') || /\.(mp4|webm|ogg|mov)$/i.test(file.name);
    if (!isMedia) {
        showToast(`Invalid file type for "${file.name}". Please upload an image or video.`, 'error');
        return;
    }

    if (uploadedNoteImages.length >= 4) {
        showToast('Maksimal 4 file media diperbolehkan.', 'error');
        return;
    }

    let fileToUpload = file;
    const isVideo = file.type.startsWith('video/') || /\.(mp4|webm|ogg|mov)$/i.test(file.name);
    if (isVideo) {
        if (!window.VideoCompressor) {
            console.warn('VideoCompressor not available, proceeding with direct upload.');
            fileToUpload = file;
        } else {
            const postBtn = document.querySelector('#noteModal button[type="submit"][value="publish"]') || 
                             document.querySelector('#noteModal button[type="submit"]') ||
                             document.querySelector('#notePostBtn');
            const origBtnText = postBtn ? postBtn.textContent : 'Post';
            try {
                if (postBtn) {
                    postBtn.disabled = true;
                    postBtn.classList.add('opacity-75');
                }
                showToast('Optimizing video before upload...', 'info');
                fileToUpload = await window.VideoCompressor.compress(file, {
                    onProgress: (pct) => {
                        if (postBtn) postBtn.textContent = `Optimizing ${pct}%`;
                    }
                });
            } catch (err) {
                console.warn('Note video compression error, falling back to direct upload:', err);
                fileToUpload = file;
            } finally {
                if (postBtn) {
                    postBtn.disabled = false;
                    postBtn.classList.remove('opacity-75');
                    postBtn.textContent = origBtnText;
                }
            }
        }
    }

    const formData = new FormData();
    formData.append('media', fileToUpload);
    formData.append('image', fileToUpload);

    try {
        const res = await fetch(`${BASE_URL}/upload/image`, { method: 'POST', body: formData });
        const rawText = await res.text();
        let data;
        try {
            data = JSON.parse(rawText);
        } catch (parseErr) {
            const snippet = rawText.replace(/<[^>]*>/g, '').trim().substring(0, 120);
            throw new Error(snippet || `Server error (${res.status} ${res.statusText})`);
        }

        if (res.ok && data.success && data.url) {
            uploadedNoteImages.push(data.url);
            renderNoteImagePreviews();
            showToast('Media uploaded successfully!', 'success');
        } else {
            showToast(data.message || `Media upload failed (${res.status})`, 'error');
        }
    } catch (err) {
        console.error('Upload error:', err);
        showToast(err.message || 'Failed to upload media. Please try again.', 'error');
    }
}

function renderNoteImagePreviews() {
    const container = document.getElementById('noteImagePreviewContainer');
    const hiddenInput = document.getElementById('noteCoverImageInput');
    if (!container || !hiddenInput) return;
    hiddenInput.value = uploadedNoteImages.length > 0 ? JSON.stringify(uploadedNoteImages) : '';
    if (uploadedNoteImages.length === 0) {
        container.innerHTML = '';
        container.classList.add('hidden');
        return;
    }
    container.classList.remove('hidden');
    let html = `<div class="grid ${uploadedNoteImages.length === 1 ? 'grid-cols-1' : 'grid-cols-2'} gap-2">`;
    uploadedNoteImages.forEach((url, idx) => {
        const isVid = /\.(mp4|webm|ogg)$/i.test(url);
        html += `<div class="relative">
            ${isVid ? `<video src="${BASE_URL + url}" controls class="w-full h-32 object-contain rounded-xl border border-outline-variant/30 bg-black"></video>` : `<img src="${BASE_URL + url}" class="w-full h-32 object-cover rounded-xl border border-outline-variant/30">`}
            <button type="button" onclick="removeNoteImage(${idx})" class="absolute top-1 right-1 w-6 h-6 bg-black/70 text-white rounded-full flex items-center justify-center hover:bg-error transition-colors"><span class="material-symbols-outlined text-[14px]">close</span></button>
        </div>`;
    });
    html += '</div>';
    container.innerHTML = html;
}

// Ensure this is attached to window so inline onclick works
window.removeNoteImage = function(idx) {
    uploadedNoteImages.splice(idx, 1);
    renderNoteImagePreviews();
}

const noteImageInput = document.getElementById('noteImageInput');
if (noteImageInput) {
    noteImageInput.addEventListener('change', async function() {
        if (this.files) {
            for(let i=0; i<this.files.length; i++) {
                await uploadNoteImage(this.files[i]);
            }
        }
        this.value = ''; // Reset input
    });
}

const noteTextarea = document.getElementById('noteModalTextarea') || document.getElementById('noteEditTextarea');
if (noteTextarea) {
    noteTextarea.addEventListener('paste', async function(e) {
        const clipboardData = e.clipboardData || window.clipboardData;
        if (clipboardData && clipboardData.items) {
            for (let i = 0; i < clipboardData.items.length; i++) {
                if (clipboardData.items[i].type.indexOf('image') !== -1) {
                    e.preventDefault();
                    await uploadNoteImage(clipboardData.items[i].getAsFile());
                }
            }
        }
    });
}
</script>

<style>
    /* Mengatasi gaya dasar Quill HTML di Feed */
    .quill-content p { margin-bottom: 0.75rem; }
    .quill-content a { color: #4edea3; text-decoration: underline; }
    .quill-content strong { color: #dae2fd; }
    .quill-content blockquote { border-left: 3px solid #10b981; padding-left: 1rem; margin: 1rem 0; font-style: italic; }
</style>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>