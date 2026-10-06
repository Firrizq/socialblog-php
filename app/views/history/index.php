<?php require_once __DIR__ . '/../templates/header.php'; ?>

<?php
$activeTab = $data['active_tab'] ?? 'history';
$unfinishedStories = $data['unfinished_stories'] ?? [];
$finishedStories = $data['finished_stories'] ?? [];
$bookmarkedPosts = $data['bookmarked_posts_list'] ?? [];
$likedPosts = $data['liked_posts_list'] ?? [];
?>

<div class="flex flex-col w-full px-4 sm:px-6 py-2">
    <!-- Hub Sticky Header -->
    <div class="sticky top-16 z-30 bg-surface/90 backdrop-blur-md pt-space-xs pb-0 mb-space-md border-b border-outline-variant/30">
        <div class="flex items-center justify-between pb-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary shrink-0">
                    <span class="material-symbols-outlined text-2xl">auto_stories</span>
                </div>
                <div>
                    <h1 class="font-headline-sm text-xl sm:text-2xl font-black text-on-surface tracking-tight">Your Library</h1>
                    <p class="font-caption text-xs text-on-surface-variant">Track your reading progress, saved stories, and favorites</p>
                </div>
            </div>
        </div>

        <!-- 3-Tab Navigation (Equal Width, Precision Border) -->
        <div class="flex w-full mt-1 -mb-px">
            <!-- Tab 1: History -->
            <button type="button" onclick="switchLibraryTab('history')" id="tab-btn-history" class="tab-btn flex-1 flex justify-center py-3 font-title-md text-[15px] cursor-pointer transition-colors border-b-4 <?= $activeTab === 'history' ? 'border-primary font-bold text-on-surface' : 'border-transparent font-medium text-on-surface-variant hover:text-on-surface' ?>">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[19px]">history</span>
                    <span>History</span>
                    <?php if(count($data['reading_history'] ?? []) > 0): ?>
                        <span class="px-1.5 py-0.5 rounded-full bg-surface-container font-caption text-[11px] text-on-surface-variant"><?= count($data['reading_history']) ?></span>
                    <?php endif; ?>
                </span>
            </button>

            <!-- Tab 2: Bookmarks -->
            <button type="button" onclick="switchLibraryTab('bookmarks')" id="tab-btn-bookmarks" class="tab-btn flex-1 flex justify-center py-3 font-title-md text-[15px] cursor-pointer transition-colors border-b-4 <?= $activeTab === 'bookmarks' ? 'border-primary font-bold text-on-surface' : 'border-transparent font-medium text-on-surface-variant hover:text-on-surface' ?>">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[19px]">bookmark</span>
                    <span>Bookmarks</span>
                    <?php if(count($bookmarkedPosts) > 0): ?>
                        <span class="px-1.5 py-0.5 rounded-full bg-surface-container font-caption text-[11px] text-on-surface-variant"><?= count($bookmarkedPosts) ?></span>
                    <?php endif; ?>
                </span>
            </button>

            <!-- Tab 3: Likes -->
            <button type="button" onclick="switchLibraryTab('likes')" id="tab-btn-likes" class="tab-btn flex-1 flex justify-center py-3 font-title-md text-[15px] cursor-pointer transition-colors border-b-4 <?= $activeTab === 'likes' ? 'border-primary font-bold text-on-surface' : 'border-transparent font-medium text-on-surface-variant hover:text-on-surface' ?>">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[19px]">favorite</span>
                    <span>Likes</span>
                    <?php if(count($likedPosts) > 0): ?>
                        <span class="px-1.5 py-0.5 rounded-full bg-surface-container font-caption text-[11px] text-on-surface-variant"><?= count($likedPosts) ?></span>
                    <?php endif; ?>
                </span>
            </button>
        </div>
    </div>

    <!-- ================= TAB 1: HISTORY (CONTINUE READING & FINISHED) ================= -->
    <div id="tab-pane-history" class="tab-pane <?= $activeTab === 'history' ? '' : 'hidden' ?> flex flex-col mt-2">
        <?php if (empty($unfinishedStories) && empty($finishedStories)): ?>
            <div class="flex flex-col items-center justify-center p-12 text-center bg-surface-container-lowest border border-outline-variant/30 rounded-2xl my-6">
                <div class="w-16 h-16 rounded-full bg-surface-container-high flex items-center justify-center text-on-surface-variant mb-4">
                    <span class="material-symbols-outlined text-3xl">menu_book</span>
                </div>
                <h2 class="font-title-md text-lg font-bold text-on-surface mb-1">Your reading history is empty</h2>
                <p class="font-body-md text-sm text-on-surface-variant max-w-sm mb-6">
                    As you read articles on Blogggle, your progress will automatically be saved here so you can pick up where you left off.
                </p>
                <a href="<?= BASEURL ?>/home" class="px-5 py-2 rounded-full bg-primary text-on-primary font-title-md text-sm font-semibold hover:opacity-90 transition-opacity">
                    Explore Stories
                </a>
            </div>
        <?php else: ?>

            <!-- Continue Reading Section (Unfinished, progress < 100%) -->
            <?php if (!empty($unfinishedStories)): ?>
                <div class="mb-8">
                    <div class="flex items-center justify-between pb-2 mb-2 border-b border-outline-variant/20">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-[20px]">play_circle</span>
                            <h2 class="font-title-md text-base sm:text-lg font-bold text-on-surface">Continue Reading</h2>
                        </div>
                        <span class="text-xs font-semibold text-on-surface-variant px-2 py-0.5 rounded-full bg-surface-container">
                            <?= count($unfinishedStories) ?> in progress
                        </span>
                    </div>

                    <div class="flex flex-col divide-y divide-outline-variant/30 border-y border-outline-variant/30">
                        <?php foreach ($unfinishedStories as $post): ?>
                            <?php renderLibraryPostRow($post, $data, true); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Finished Stories Section (progress >= 100%) -->
            <?php if (!empty($finishedStories)): ?>
                <div class="mb-8">
                    <div class="flex items-center justify-between pb-2 mb-2 border-b border-outline-variant/20">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-[20px]">check_circle</span>
                            <h2 class="font-title-md text-base sm:text-lg font-bold text-on-surface">Finished Stories</h2>
                        </div>
                        <span class="text-xs font-semibold text-on-surface-variant px-2 py-0.5 rounded-full bg-surface-container">
                            <?= count($finishedStories) ?> completed
                        </span>
                    </div>

                    <div class="flex flex-col divide-y divide-outline-variant/30 border-y border-outline-variant/30">
                        <?php foreach ($finishedStories as $post): ?>
                            <?php renderLibraryPostRow($post, $data, false, true); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>

    <!-- ================= TAB 2: BOOKMARKS ================= -->
    <div id="tab-pane-bookmarks" class="tab-pane <?= $activeTab === 'bookmarks' ? '' : 'hidden' ?> flex flex-col mt-2">
        <?php if (!empty($bookmarkedPosts)): ?>
            <div class="flex flex-col divide-y divide-outline-variant/30 border-y border-outline-variant/30">
                <?php foreach ($bookmarkedPosts as $post): ?>
                    <?php renderLibraryPostRow($post, $data); ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="flex flex-col items-center justify-center p-12 text-center bg-surface-container-lowest border border-outline-variant/30 rounded-2xl my-6">
                <div class="w-16 h-16 rounded-full bg-surface-container-high flex items-center justify-center text-on-surface-variant mb-4">
                    <span class="material-symbols-outlined text-3xl">bookmark_border</span>
                </div>
                <h2 class="font-title-md text-lg font-bold text-on-surface mb-1">No bookmarked stories</h2>
                <p class="font-body-md text-sm text-on-surface-variant max-w-sm mb-6">
                    Click the bookmark icon on any story or note in your feed to save it for reading later.
                </p>
                <a href="<?= BASEURL ?>/home" class="px-5 py-2 rounded-full bg-primary text-on-primary font-title-md text-sm font-semibold hover:opacity-90 transition-opacity">
                    Discover Stories
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- ================= TAB 3: LIKES ================= -->
    <div id="tab-pane-likes" class="tab-pane <?= $activeTab === 'likes' ? '' : 'hidden' ?> flex flex-col mt-2">
        <?php if (!empty($likedPosts)): ?>
            <div class="flex flex-col divide-y divide-outline-variant/30 border-y border-outline-variant/30">
                <?php foreach ($likedPosts as $post): ?>
                    <?php renderLibraryPostRow($post, $data); ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="flex flex-col items-center justify-center p-12 text-center bg-surface-container-lowest border border-outline-variant/30 rounded-2xl my-6">
                <div class="w-16 h-16 rounded-full bg-surface-container-high flex items-center justify-center text-on-surface-variant mb-4">
                    <span class="material-symbols-outlined text-3xl">favorite_border</span>
                </div>
                <h2 class="font-title-md text-lg font-bold text-on-surface mb-1">No liked stories yet</h2>
                <p class="font-body-md text-sm text-on-surface-variant max-w-sm mb-6">
                    Stories you favorite will appear here so you can revisit your favorite perspectives.
                </p>
                <a href="<?= BASEURL ?>/home" class="px-5 py-2 rounded-full bg-primary text-on-primary font-title-md text-sm font-semibold hover:opacity-90 transition-opacity">
                    Explore Feed
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
/**
 * Helper to render an asymmetrical card row inside the Library hub
 */
function renderLibraryPostRow(array $post, array $data, bool $showProgressBar = false, bool $isCompleted = false): void
{
    $progress = (int)($post['progress'] ?? 0);
    $isLiked = in_array((int)$post['id'], $data['liked_posts'] ?? []);
    $isBookmarked = in_array((int)$post['id'], $data['bookmarked_posts'] ?? []);
    $isReposted = in_array((int)$post['id'], $data['reposted_posts'] ?? []) || !empty($post['is_reposted']);
    $postType = strtolower($post['post_type'] ?? $post['type'] ?? 'story');
    $postUid = !empty($post['uid']) ? $post['uid'] : $post['id'];
    $postUrl = BASEURL . '/' . urlencode($post['username'] ?? '') . '/' . $postType . '/' . $postUid;
    $authorUrl = BASEURL . '/' . urlencode($post['username'] ?? '');
    ?>
    <article class="p-4 sm:p-5 hover:bg-surface-container-lowest/40 transition-colors flex gap-3 sm:gap-4 cursor-pointer" onclick="if(!event.target.closest('a') && !event.target.closest('button')) window.location.href='<?= $postUrl ?>';">
        <!-- Left: Avatar -->
        <div class="shrink-0">
            <a href="<?= $authorUrl ?>" class="avatar-link profile-hover-trigger block w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-surface-container-high flex items-center justify-center font-bold text-primary hover:opacity-80 transition-opacity overflow-hidden relative z-10" data-username="<?= htmlspecialchars($post['username'] ?? '') ?>" title="View Profile">
                <?php if (!empty($post['profile_picture'])): ?>
                    <img src="<?= BASEURL ?><?= htmlspecialchars($post['profile_picture']) ?>" alt="<?= htmlspecialchars($post['username'] ?? '') ?>" class="w-full h-full object-cover rounded-full avatar-img">
                <?php else: ?>
                    <?= htmlspecialchars(substr($post['username'] ?? 'U', 0, 1)) ?>
                <?php endif; ?>
            </a>
        </div>

        <!-- Right: Content -->
        <div class="flex-1 min-w-0 flex flex-col">
            <!-- Author Header -->
            <div class="flex items-center justify-between gap-2 mb-1">
                <div class="flex items-center gap-1.5 min-w-0 flex-wrap text-[15px]">
                    <a href="<?= $authorUrl ?>" class="font-title-md font-bold text-on-surface hover:underline truncate relative z-10">
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

                    <?php if($isCompleted || $progress >= 100): ?>
                        <span class="ml-1 inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary/10 text-primary font-caption text-[11px] font-bold">
                            <span class="material-symbols-outlined text-[13px]">check_circle</span> Finished
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Dropdown Menu -->
                <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
                    <div class="relative dropdown-container z-20 shrink-0">
                        <button type="button" onclick="toggleMenu(event, 'menu-<?= $post['id'] ?>')" class="text-on-surface-variant hover:text-primary w-8 h-8 rounded-full hover:bg-primary/10 transition-colors flex items-center justify-center -mr-2">
                            <span class="material-symbols-outlined text-[18px]">more_horiz</span>
                        </button>
                        <div id="menu-<?= $post['id'] ?>" class="hidden absolute right-0 top-full mt-1 w-36 bg-surface-container-high border border-outline-variant/30 rounded-xl shadow-xl z-[60] overflow-hidden flex flex-col py-1">
                            <a href="<?= BASEURL ?>/post/edit/<?= $post['id'] ?>" class="px-4 py-2 text-sm text-on-surface font-title-md hover:bg-surface-container flex items-center gap-3 transition-colors"><span class="material-symbols-outlined text-base">edit</span> Edit</a>
                            <form action="<?= BASEURL ?>/post/delete/<?= $post['id'] ?>" method="POST" class="m-0 p-0" data-confirm="Are you sure you want to delete this post? This action cannot be undone." data-confirm-title="Delete Post" data-confirm-btn="Delete">
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-error font-title-md hover:bg-error-container/20 flex items-center gap-3 transition-colors"><span class="material-symbols-outlined text-base">delete</span> Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Reading Progress Bar (Continue Reading Mode) & Exact Timestamp -->
            <?php 
                $historyDateRaw = $post['history_date'] ?? $post['last_read_at'] ?? null;
            ?>
            <?php if (!empty($historyDateRaw)): ?>
                <?php $dateFormatted = date('M d, Y', strtotime((string)$historyDateRaw)); ?>
                <div class="mt-1 mb-2 flex flex-col">
                    <?php if ($progress < 100): ?>
                        <div class="flex items-center gap-3">
                            <div class="flex-1 h-1.5 bg-surface-container rounded-full overflow-hidden">
                                <div class="bg-primary h-full rounded-full transition-all duration-300" style="width: <?= $progress ?>%;"></div>
                            </div>
                            <span class="font-caption text-xs font-bold text-primary shrink-0"><?= $progress ?>% read</span>
                        </div>
                    <?php endif; ?>
                    <div class="text-xs text-on-surface-variant/80 dark:text-slate-400 mt-1">
                        <?php if ($progress >= 100): ?>
                            finished on <?= $dateFormatted ?>
                        <?php else: ?>
                            last read on <?= $dateFormatted ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif($showProgressBar && $progress > 0): ?>
                <div class="mt-1 mb-2.5 flex items-center gap-3">
                    <div class="flex-1 h-1.5 bg-surface-container rounded-full overflow-hidden">
                        <div class="bg-primary h-full rounded-full transition-all duration-300" style="width: <?= $progress ?>%;"></div>
                    </div>
                    <span class="font-caption text-xs font-bold text-primary shrink-0"><?= $progress ?>% read</span>
                </div>
            <?php endif; ?>

            <!-- Story Cinematic Card / Note -->
            <?php if(($post['post_type'] ?? 'story') === 'story'): ?>
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
                    <?php if(!empty($cover)): ?>
                        <?php
                            $ext = strtolower(pathinfo((string)$cover, PATHINFO_EXTENSION));
                            $videoExts = ['mp4', 'webm', 'ogg', 'mov'];
                            $isVideo = in_array($ext, $videoExts, true);
                            $mediaSrc = (str_starts_with($cover, 'http://') || str_starts_with($cover, 'https://'))
                                ? $cover
                                : ((str_starts_with($cover, '/uploads/') || str_starts_with($cover, 'uploads/'))
                                    ? BASEURL . '/' . ltrim($cover, '/')
                                    : BASEURL . (str_starts_with($cover, '/') ? $cover : '/uploads/' . ltrim($cover, '/')));
                            $mimeType = match($ext) {
                                'webm' => 'video/webm',
                                'ogg' => 'video/ogg',
                                default => 'video/mp4'
                            };
                        ?>
                        <?php if($isVideo): ?>
                            <div class="relative w-full border-b border-outline-variant/30 overflow-hidden bg-black" onclick="event.stopPropagation();">
                                <video controls class="w-full rounded-xl max-h-96 object-contain bg-black">
                                    <source src="<?= htmlspecialchars($mediaSrc) ?>" type="<?= $mimeType ?>">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        <?php else: ?>
                            <div class="relative w-full aspect-[16/9] sm:aspect-video border-b border-outline-variant/30 overflow-hidden bg-surface-container-high">
                                <img src="<?= htmlspecialchars($mediaSrc) ?>" class="post-media-image w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out" alt="Story Cover">
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <div class="p-3.5 sm:p-4 flex flex-col gap-1.5 bg-surface-container-lowest group-hover:bg-surface-container-low/30 transition-colors">
                        <?php if(!empty($post['title'])): ?>
                            <h2 class="font-serif text-lg sm:text-xl font-bold text-on-surface tracking-tight line-clamp-2">
                                <?= htmlspecialchars($post['title']) ?>
                            </h2>
                        <?php endif; ?>
                        <?php 
                            $excerpt = '';
                            if (preg_match('/<h2[^>]*class="[^"]*story-subtitle[^"]*"[^>]*>(.*?)<\/h2>/is', $post['content'] ?? '', $matches)) {
                                $excerpt = trim(strip_tags($matches[1]));
                            }
                            if (empty($excerpt)) {
                                $cleanText = str_replace(['<p>', '<br>', '</div>', '</li>', '</h1>', '</h2>', '</h3>'], ' ', (string)($post['content'] ?? ''));
                                $excerpt = trim(strip_tags($cleanText));
                            }
                        ?>
                        <?php if(!empty($excerpt)): ?>
                            <div class="font-body-md text-on-surface-variant text-[14px] sm:text-[15px] line-clamp-2 leading-relaxed">
                                <?= htmlspecialchars($excerpt) ?>
                            </div>
                        <?php endif; ?>
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
                            <?php 
                            $noteFile = trim((string)$img);
                            $noteExt = strtolower(pathinfo($noteFile, PATHINFO_EXTENSION));
                            $videoExts = ['mp4', 'webm', 'ogg', 'mov'];
                            $noteIsVideo = in_array($noteExt, $videoExts, true);
                            $noteSrc = (str_starts_with($noteFile, 'http://') || str_starts_with($noteFile, 'https://')) 
                                ? $noteFile 
                                : ((str_starts_with($noteFile, '/uploads/') || str_starts_with($noteFile, 'uploads/')) 
                                    ? (BASEURL . '/' . ltrim($noteFile, '/')) 
                                    : (BASEURL . (str_starts_with($noteFile, '/') ? $noteFile : '/uploads/' . ltrim($noteFile, '/'))));
                            ?>
                            <?php if($noteIsVideo): ?>
                                <div class="w-full bg-black rounded-xl overflow-hidden <?= ($imgCount === 3 && $idx === 0) ? 'row-span-2' : '' ?>" onclick="event.stopPropagation();">
                                    <video controls class="w-full rounded-xl max-h-96 object-contain bg-black">
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
            <?php endif; ?>

            <!-- Action Bar (Twitter/X Style with Bookmark Count) -->
            <div class="flex items-center justify-between mt-2 max-w-md text-on-surface-variant relative z-10 -ml-2">
                <a href="<?= $postUrl ?>" class="group flex items-center gap-1 hover:text-primary transition-colors">
                    <div class="w-8 h-8 rounded-full group-hover:bg-primary/10 flex items-center justify-center transition-colors">
                        <span class="material-symbols-outlined text-[18px]">chat_bubble</span>
                    </div>
                    <span class="font-body-md text-xs"><?= $post['comment_count'] ?? 0 ?></span>
                </a>
                <button type="button" class="btn-repost group flex items-center gap-1 transition-colors <?= $isReposted ? 'text-emerald-500' : 'hover:text-emerald-500' ?> active:scale-95" data-id="<?= (int)$post['id'] ?>" title="Repost">
                    <div class="w-8 h-8 rounded-full group-hover:bg-emerald-500/10 flex items-center justify-center transition-colors">
                        <span class="material-symbols-outlined text-[18px] <?= $isReposted ? 'font-bold' : '' ?>">sync_alt</span>
                    </div>
                    <span class="repost-count font-body-md text-xs"><?= (int)($post['repost_count'] ?? 0) ?></span>
                </button>
                <button type="button" class="btn-like group flex items-center gap-1 transition-colors <?= $isLiked ? 'text-error' : 'hover:text-error' ?> active:scale-95" data-id="<?= (int)$post['id'] ?>" title="Like">
                    <div class="w-8 h-8 rounded-full group-hover:bg-error/10 flex items-center justify-center transition-colors">
                        <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' <?= $isLiked ? 1 : 0 ?>;">favorite</span>
                    </div>
                    <span class="like-count font-body-md text-xs"><?= (int)($post['like_count'] ?? 0) ?></span>
                </button>
                <button type="button" class="btn-bookmark group flex items-center gap-1 transition-colors <?= $isBookmarked ? 'text-primary' : 'hover:text-primary' ?> active:scale-95" data-id="<?= (int)$post['id'] ?>" title="Bookmark">
                    <div class="w-8 h-8 rounded-full group-hover:bg-primary/10 flex items-center justify-center transition-colors">
                        <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' <?= $isBookmarked ? 1 : 0 ?>;">bookmark</span>
                    </div>
                    <span class="bookmark-count font-body-md text-xs"><?= (int)($post['bookmark_count'] ?? 0) ?></span>
                </button>
                <button type="button" class="group flex items-center transition-colors hover:text-primary" onclick="event.stopPropagation(); navigator.clipboard.writeText('<?= $postUrl ?>'); showToast('Link copied to clipboard!', 'success');">
                    <div class="w-8 h-8 rounded-full group-hover:bg-primary/10 flex items-center justify-center transition-colors">
                        <span class="material-symbols-outlined text-[18px]">share</span>
                    </div>
                </button>
            </div>
        </div>
    </article>
<?php } ?>

<script>
function switchLibraryTab(tabName) {
    // Hide all tab panes
    document.querySelectorAll('.tab-pane').forEach(el => el.classList.add('hidden'));
    
    // Show selected pane
    const targetPane = document.getElementById('tab-pane-' + tabName);
    if(targetPane) targetPane.classList.remove('hidden');

    // Update tab button styles
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('border-primary', 'font-bold', 'text-on-surface');
        btn.classList.add('border-transparent', 'font-medium', 'text-on-surface-variant');
    });

    const activeBtn = document.getElementById('tab-btn-' + tabName);
    if(activeBtn) {
        activeBtn.classList.add('border-primary', 'font-bold', 'text-on-surface');
        activeBtn.classList.remove('border-transparent', 'font-medium', 'text-on-surface-variant');
    }

    // Update URL query parameter without page reload
    const url = new URL(window.location);
    url.searchParams.set('tab', tabName);
    window.history.pushState({}, '', url);
}
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
