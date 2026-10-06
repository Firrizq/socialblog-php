<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="flex flex-col w-full px-4 sm:px-6 py-2">
    <!-- Explore Page Sticky Header -->
    <div class="sticky top-16 z-30 bg-surface/90 backdrop-blur-md pb-space-xs pt-space-xs mb-space-lg flex items-center justify-between border-b border-outline-variant/30">
        <div class="flex items-center gap-space-sm py-2">
            <div class="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                <span class="material-symbols-outlined text-xl"><?= !empty($data['keyword']) ? 'search' : 'explore' ?></span>
            </div>
            <div>
                <h1 class="font-headline-sm text-lg sm:text-xl font-bold text-on-surface tracking-tight">
                    <?= !empty($data['keyword']) ? 'Search results for: ' . htmlspecialchars($data['keyword']) : 'Explore Popular Stories' ?>
                </h1>
                <p class="font-caption text-xs text-on-surface-variant">
                    <?= !empty($data['keyword']) ? 'Stories and authors matching your query' : 'Notable stories and trending perspectives across Blogggle' ?>
                </p>
            </div>
        </div>
        <div class="flex items-center gap-space-xs flex-wrap">
            <?php if (!empty($data['authors']) && is_array($data['authors'])): ?>
                <span class="px-space-md py-1 rounded-full bg-primary/10 text-primary border border-primary/20 font-caption text-xs font-semibold">
                    <?= count($data['authors']) ?> <?= count($data['authors']) === 1 ? 'author' : 'authors' ?>
                </span>
            <?php endif; ?>
            <?php if (!empty($data['posts']) && is_array($data['posts'])): ?>
                <span class="px-space-md py-1 rounded-full bg-surface-container font-caption text-on-surface-variant text-xs font-semibold">
                    <?= count($data['posts']) ?> <?= count($data['posts']) === 1 ? 'story' : 'stories' ?>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($data['keyword']) && !empty($data['authors']) && is_array($data['authors'])): ?>
        <!-- Unified Search: Matching Authors Section -->
        <section class="mb-5 p-4 sm:p-5 rounded-2xl bg-surface-container-low border border-outline-variant/30 shadow-sm">
            <div class="flex items-center justify-between mb-3.5 pb-2.5 border-b border-outline-variant/20">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-xl">group</span>
                    <h2 class="font-title-md font-bold text-sm sm:text-base text-on-surface">Authors & Creators</h2>
                </div>
                <span class="font-caption text-xs text-on-surface-variant font-medium">
                    <?= count($data['authors']) ?> <?= count($data['authors']) === 1 ? 'author' : 'authors' ?>
                </span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <?php foreach ($data['authors'] as $author): ?>
                    <?php
                        $authorProfileUrl = BASEURL . '/' . urlencode($author['username']);
                        $isCurrentAuthUser = isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$author['id'];
                    ?>
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-surface-container-lowest border border-outline-variant/30 hover:border-outline-variant/60 hover:shadow-2xs transition-all">
                        <a href="<?= $authorProfileUrl ?>" class="w-11 h-11 rounded-full overflow-hidden bg-surface-container-high border border-outline-variant/40 shrink-0 flex items-center justify-center font-bold text-primary text-sm group">
                            <?php if (!empty($author['profile_picture'])): ?>
                                <img src="<?= BASEURL ?><?= htmlspecialchars($author['profile_picture']) ?>" alt="<?= htmlspecialchars($author['username']) ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <?= strtoupper(substr($author['username'] ?? 'U', 0, 1)) ?>
                            <?php endif; ?>
                        </a>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <a href="<?= $authorProfileUrl ?>" class="min-w-0 group">
                                    <h3 class="font-title-md font-bold text-sm text-on-surface truncate group-hover:text-primary transition-colors leading-tight">
                                        <?= htmlspecialchars($author['name'] ?? $author['username']) ?>
                                    </h3>
                                    <p class="font-caption text-xs text-on-surface-variant truncate">
                                        @<?= htmlspecialchars($author['username']) ?>
                                    </p>
                                </a>
                                <?php if (!$isCurrentAuthUser): ?>
                                    <button type="button" 
                                            class="follow-btn btn-follow btn-pill-follow <?= !empty($author['is_following']) ? 'following' : '' ?> text-xs shrink-0" 
                                            data-user-id="<?= (int)$author['id'] ?>">
                                        <?= !empty($author['is_following']) ? 'Following' : 'Follow' ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($author['bio'])): ?>
                                <p class="font-body-md text-xs text-on-surface-variant line-clamp-2 mt-1.5 leading-relaxed">
                                    <?= htmlspecialchars($author['bio']) ?>
                                </p>
                            <?php endif; ?>
                            <div class="flex items-center gap-3 mt-2 text-[11px] font-caption text-on-surface-variant">
                                <span><strong class="text-on-surface font-semibold"><?= number_format((int)($author['follower_count'] ?? 0)) ?></strong> followers</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Feed Post Stream -->
    <div class="flex flex-col gap-4 sm:gap-5 mt-1">
        <?php if (!empty($data['keyword']) && !empty($data['authors']) && !empty($data['posts'])): ?>
            <div class="flex items-center gap-2 pt-2 pb-1">
                <span class="material-symbols-outlined text-primary text-xl">auto_stories</span>
                <h2 class="font-title-md font-bold text-sm sm:text-base text-on-surface">Stories & Notes</h2>
            </div>
        <?php endif; ?>
        <?php if (!empty($data['posts']) && is_array($data['posts'])): ?>
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
                ?>

                <?php if ($postType === 'story'): ?>
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
            <?php if (!empty($data['keyword']) && !empty($data['authors'])): ?>
                <div class="text-center py-10 px-4 bg-surface-container-lowest/40 border border-outline-variant/30 rounded-2xl flex flex-col items-center">
                    <span class="material-symbols-outlined text-3xl text-outline mb-1.5">article</span>
                    <h3 class="text-base font-bold font-title-md text-on-surface">No stories found</h3>
                    <p class="text-on-surface-variant text-xs mt-1 max-w-sm">
                        No stories or notes matched "<?= htmlspecialchars($data['keyword']) ?>", but matching authors are listed above.
                    </p>
                </div>
            <?php else: ?>
                <div class="text-center p-12 bg-surface-container-lowest/50 border border-outline-variant/40 rounded-2xl flex flex-col items-center">
                    <span class="material-symbols-outlined text-4xl text-outline mb-2"><?= !empty($data['keyword']) ? 'search_off' : 'article' ?></span>
                    <h2 class="text-xl font-bold font-serif text-on-surface"><?= !empty($data['keyword']) ? 'No results found' : 'No stories found' ?></h2>
                    <p class="text-on-surface-variant mt-2 text-sm max-w-sm">
                        <?= !empty($data['keyword']) ? 'No stories or authors matched "' . htmlspecialchars($data['keyword']) . '". Try searching for different keywords or topics.' : 'Check back later or be the first to share a perspective!' ?>
                    </p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
