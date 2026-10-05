<?php 
require_once __DIR__ . '/../templates/header.php'; 
$post = $data['post'] ?? null;
$comments = $data['comments'] ?? [];
?>

<div id="reading-progress" class="fixed top-0 left-0 h-1 bg-primary z-[120] w-0 transition-all duration-150"></div>
<div class="max-w-2xl mx-auto w-full px-4 sm:px-0 pb-20 pt-4">
    <!-- Back Navigation -->
    <div class="mb-8">
        <button onclick="history.back()" class="flex items-center gap-2 text-on-surface-variant hover:text-primary transition-colors font-title-md text-sm group">
            <span class="material-symbols-outlined text-lg group-hover:-translate-x-1 transition-transform">arrow_back</span>
            Back
        </button>
    </div>

    <?php if (!$post): ?>
        <div class="text-center p-12 bg-surface-container-low border border-outline-variant/30 rounded-2xl my-4">
            <span class="material-symbols-outlined text-outline text-5xl mb-3">article_off</span>
            <h2 class="font-title-md text-2xl font-bold text-on-surface">Story Not Found</h2>
            <p class="font-body-md text-on-surface-variant text-sm mt-1 mb-6">
                This post might have been removed or is no longer available.
            </p>
            <a href="<?= BASEURL ?>/home" class="inline-flex items-center gap-2 px-space-md py-space-xs rounded-full bg-primary-container text-on-primary-container font-label-md hover:bg-primary transition-colors font-semibold shadow-sm">
                <span class="material-symbols-outlined text-base">west</span>
                <span>Return to Feed</span>
            </a>
        </div>
    <?php else: ?>
        <article>
            <!-- Title (If Story) -->
            <?php if(!empty($post['title'])): ?>
                <h1 class="text-3xl sm:text-[40px] font-black text-on-surface tracking-tight mb-8 leading-[1.2]">
                    <?= htmlspecialchars($post['title'] ?? '') ?>
                </h1>
            <?php endif; ?>

            <!-- Compact Author Metadata -->
            <div class="flex items-center justify-between mb-10">
                <div class="flex items-center gap-3.5">
                    <a href="<?= BASEURL ?>/<?= urlencode($post['username'] ?? '') ?>" class="profile-hover-trigger avatar-link w-12 h-12 rounded-full overflow-hidden bg-surface-container-high border border-outline-variant/50 shrink-0 hover:ring-2 hover:ring-primary transition-all relative z-10" data-username="<?= htmlspecialchars($post['username'] ?? '') ?>" title="View Profile">
                        <?php if (!empty($post['profile_picture'])): ?>
                            <img src="<?= BASEURL ?><?= htmlspecialchars($post['profile_picture'] ?? '') ?>" class="w-full h-full object-cover rounded-full avatar-img">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center font-bold text-primary text-lg">
                                <?= htmlspecialchars(substr($post['username'] ?? 'U', 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </a>
                    <div class="flex flex-col justify-center">
                        <a href="<?= BASEURL ?>/<?= urlencode($post['username'] ?? '') ?>" class="profile-hover-trigger font-title-md font-bold text-on-surface hover:underline text-base" data-username="<?= htmlspecialchars($post['username'] ?? '') ?>">
                            <?= htmlspecialchars($post['name'] ?? $post['username'] ?? 'Anonymous') ?>
                        </a>
                        <div class="flex items-center gap-1.5 font-body-md text-on-surface-variant text-[13px] sm:text-sm mt-0.5 flex-wrap">
                            <?php if(($post['post_type'] ?? 'story') === 'story'): ?>
                                <span><?= $post['read_time_minutes'] ?? 1 ?> min read</span>
                                <span class="font-black text-[10px]">·</span>
                            <?php endif; ?>
                            <span><?= !empty($post['created_at']) ? date('M j, Y', strtotime($post['created_at'])) : '' ?></span>
                        </div>
                    </div>
                </div>

                <!-- Post Options -->
                <?php if(isset($_SESSION['user_id']) && !empty($post['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
                    <div class="relative dropdown-container">
                        <button type="button" onclick="toggleMenu(event, 'menu-detail-<?= (int)($post['id'] ?? 0) ?>')" class="text-on-surface-variant hover:text-on-surface w-9 h-9 rounded-full hover:bg-surface-container transition-colors flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px]">more_horiz</span>
                        </button>
                        <div id="menu-detail-<?= (int)($post['id'] ?? 0) ?>" class="hidden absolute right-0 top-full mt-2 w-40 bg-surface-container-low border border-outline-variant/30 rounded-xl shadow-xl z-50 overflow-hidden flex flex-col py-1.5">
                            <a href="<?= BASEURL ?>/post/edit<?= ($post['post_type']??'') === 'note' ? '_note' : '' ?>/<?= (int)($post['id'] ?? 0) ?>" class="px-4 py-2.5 text-sm text-on-surface font-title-md hover:bg-surface-container flex items-center gap-3 transition-colors"><span class="material-symbols-outlined text-[18px]">edit</span> Edit</a>
                            <form action="<?= BASEURL ?>/post/delete/<?= (int)($post['id'] ?? 0) ?>" method="POST" class="m-0 p-0" onsubmit="return confirm('Are you sure you want to delete this?');">
                                <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-error font-title-md hover:bg-error-container/20 flex items-center gap-3 transition-colors"><span class="material-symbols-outlined text-[18px]">delete</span> Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Dedicated Hero Cover Media (Image or Video) -->
            <?php 
                $detailMedia = !empty($post['media']) ? $post['media'] : ($post['cover_image'] ?? '');
                $decoded = json_decode((string)$detailMedia, true);
                $coverPath = is_array($decoded) && !empty($decoded) ? $decoded[0] : (strpos((string)$detailMedia, ',') ? explode(',', (string)$detailMedia)[0] : (string)$detailMedia);
                $coverPath = trim((string)$coverPath);

                $detailExt = strtolower(pathinfo($coverPath, PATHINFO_EXTENSION));
                $videoExtensions = ['mp4', 'webm', 'ogg', 'mov'];
                $isVideo = in_array($detailExt, $videoExtensions, true);

                $mediaUrl = '';
                if (!empty($coverPath)) {
                    if (str_starts_with($coverPath, 'http://') || str_starts_with($coverPath, 'https://')) {
                        $mediaUrl = $coverPath;
                    } elseif (str_starts_with($coverPath, '/uploads/') || str_starts_with($coverPath, 'uploads/')) {
                        $mediaUrl = BASEURL . '/' . ltrim($coverPath, '/');
                    } else {
                        $mediaUrl = BASEURL . '/uploads/' . ltrim($coverPath, '/');
                    }
                }
                $mimeType = match($detailExt) {
                    'webm' => 'video/webm',
                    'ogg' => 'video/ogg',
                    default => 'video/mp4'
                };
            ?>
            <?php if(($post['post_type'] ?? 'story') === 'story' && !empty($coverPath)): ?>
                <div class="w-full mb-10">
                    <?php if($isVideo): ?>
                        <video controls class="w-full rounded-xl max-h-96 object-contain bg-black">
                            <source src="<?= htmlspecialchars($mediaUrl) ?>" type="<?= $mimeType ?>">
                            Your browser does not support the video tag.
                        </video>
                    <?php else: ?>
                        <div class="w-full aspect-[16/9] sm:aspect-video rounded-2xl overflow-hidden border border-outline-variant/30">
                            <img src="<?= htmlspecialchars($mediaUrl) ?>" class="post-media-image w-full h-full object-cover cursor-pointer" alt="Story Cover">
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Content Body (Immersive Typography) -->
            <div class="font-body-md text-on-surface text-[17px] sm:text-[20px] leading-[1.7] sm:leading-[1.8] whitespace-pre-line mb-10 overflow-hidden break-words quill-content">
                <?php if(($post['post_type'] ?? 'story') === 'note'): ?>
                    <?= preg_replace('/(^|>|\s)#([a-zA-Z_][a-zA-Z0-9_]*)/', '$1<a href="' . BASEURL . '/explore/tag/$2" class="text-primary font-semibold hover:underline">#$2</a>', strip_tags((string)($post['content'] ?? ''))) ?>
                <?php else: ?>
                    <?= $post['content'] ?? '' ?>
                <?php endif; ?>
            </div>

            <!-- Media for Notes -->
            <?php if(($post['post_type'] ?? 'story') === 'note' && !empty($post['cover_image'])): ?>
                <?php 
                $decoded = json_decode((string)$post['cover_image'], true);
                $imgs = is_array($decoded) ? $decoded : array_filter(explode(',', (string)$post['cover_image']));
                $imgCount = count($imgs);
                $imgJson = htmlspecialchars(json_encode(array_values($imgs)), ENT_QUOTES, 'UTF-8');
                ?>
                <div class="mt-4 mb-10 grid <?= $imgCount === 1 ? 'grid-cols-1' : 'grid-cols-2' ?> gap-2 rounded-2xl overflow-hidden border border-outline-variant/30">
                    <?php foreach($imgs as $idx => $img): ?>
                        <?php 
                        $noteItem = trim((string)$img);
                        $noteExt = strtolower(pathinfo($noteItem, PATHINFO_EXTENSION));
                        $noteIsVideo = in_array($noteExt, ['mp4', 'webm', 'ogg', 'mov'], true);
                        $noteUrl = (str_starts_with($noteItem, 'http://') || str_starts_with($noteItem, 'https://'))
                            ? $noteItem
                            : ((str_starts_with($noteItem, '/uploads/') || str_starts_with($noteItem, 'uploads/')) 
                                ? (BASEURL . '/' . ltrim($noteItem, '/')) 
                                : (BASEURL . '/uploads/' . ltrim($noteItem, '/')));
                        ?>
                        <?php if($noteIsVideo): ?>
                            <div class="w-full bg-black rounded-xl overflow-hidden <?= ($imgCount === 3 && $idx === 0) ? 'row-span-2' : '' ?>">
                                <video controls class="w-full rounded-xl max-h-96 object-contain bg-black">
                                    <source src="<?= htmlspecialchars($noteUrl) ?>" type="video/<?= $noteExt === 'webm' ? 'webm' : ($noteExt === 'ogg' ? 'ogg' : 'mp4') ?>">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        <?php else: ?>
                            <img src="<?= htmlspecialchars($noteUrl) ?>" class="post-media-image w-full h-full object-cover cursor-pointer hover:opacity-90 transition-opacity <?= ($imgCount === 3 && $idx === 0) ? 'row-span-2' : '' ?> <?= $imgCount > 1 ? 'aspect-[4/3] sm:aspect-video' : 'max-h-[600px] w-full' ?>" alt="Attachment" onclick="window.openLightboxGallery && openLightboxGallery(<?= $imgJson ?>, <?= $idx ?>)">
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Editorial Action Bar (Bordered) -->
            <div class="flex items-center justify-between py-3 border-y border-outline-variant/40 mb-12 text-on-surface-variant">
                <div class="flex items-center gap-6">
                    <?php 
                         $isLiked = in_array((int)($post['id'] ?? 0), $data['liked_posts'] ?? []) || !empty($data['is_liked']);
                    ?>
                    <button type="button" class="btn-like group flex items-center gap-2 transition-colors <?= $isLiked ? 'text-error' : 'hover:text-error' ?> active:scale-95" data-id="<?= (int)($post['id'] ?? 0) ?>" title="Like">
                        <span class="material-symbols-outlined text-[22px]" style="font-variation-settings: 'FILL' <?= $isLiked ? 1 : 0 ?>;">favorite</span>
                        <span class="like-count font-body-md text-sm"><?= (int)($post['like_count'] ?? 0) ?></span>
                    </button>
                    <button type="button" class="group flex items-center gap-2 hover:text-primary transition-colors" onclick="document.getElementById('comment-input').focus();">
                        <span class="material-symbols-outlined text-[22px]">chat_bubble</span>
                        <span class="font-body-md text-sm"><?= (int)($post['comment_count'] ?? 0) ?></span>
                    </button>
                    <?php $isReposted = !empty($data['is_reposted']) || in_array((int)($post['id'] ?? 0), $data['reposted_posts'] ?? []); ?>
                    <button type="button" class="btn-repost group flex items-center gap-2 transition-colors <?= $isReposted ? 'text-emerald-500' : 'hover:text-emerald-500' ?> active:scale-95" data-id="<?= (int)($post['id'] ?? 0) ?>" title="Repost">
                        <span class="material-symbols-outlined text-[22px] <?= $isReposted ? 'font-bold' : '' ?>">sync_alt</span>
                        <span class="repost-count font-body-md text-sm"><?= (int)($post['repost_count'] ?? 0) ?></span>
                    </button>
                </div>
                
                <div class="flex items-center gap-4">
                    <?php $isBookmarked = in_array((int)($post['id'] ?? 0), $data['bookmarked_posts'] ?? []) || !empty($data['is_bookmarked']); ?>
                    <button type="button" class="btn-bookmark group flex items-center gap-2 transition-colors <?= $isBookmarked ? 'text-primary' : 'hover:text-primary' ?> active:scale-95" data-id="<?= (int)($post['id'] ?? 0) ?>" title="Bookmark">
                        <span class="material-symbols-outlined text-[22px]" style="font-variation-settings: 'FILL' <?= $isBookmarked ? 1 : 0 ?>;">bookmark</span>
                        <span class="bookmark-count font-body-md text-sm"><?= (int)($post['bookmark_count'] ?? 0) ?></span>
                    </button>
                    <button type="button" class="group flex items-center transition-colors hover:text-primary" onclick="navigator.clipboard.writeText(window.location.href); showToast('Link copied to clipboard!', 'success');">
                        <span class="material-symbols-outlined text-[22px]">share</span>
                    </button>
                </div>
            </div>
        </article>

        <!-- Discussion Section -->
        <section class="mt-8">
            <h3 class="font-title-md font-bold text-xl text-on-surface mb-6">Discussion (<?= (int)($post['comment_count'] ?? 0) ?>)</h3>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="flex gap-4 mb-10">
                    <div class="w-10 h-10 rounded-full overflow-hidden bg-surface-container-high border border-outline-variant/30 shrink-0">
                        <?php if (!empty($_SESSION['profile_picture'])): ?>
                            <img src="<?= BASEURL ?><?= htmlspecialchars($_SESSION['profile_picture'] ?? '') ?>" class="w-full h-full object-cover rounded-full avatar-img">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center font-bold text-primary text-sm">
                                <?= htmlspecialchars(substr($_SESSION['username'] ?? 'U', 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1">
                        <form action="<?= BASEURL ?>/post/comment/<?= (int)($post['id'] ?? 0) ?>" method="POST" class="m-0 flex flex-col gap-3">
                            <textarea id="comment-input" name="content" rows="3" class="w-full bg-surface-container-lowest border border-outline-variant/50 rounded-xl p-4 text-on-surface font-body-md focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all resize-none" placeholder="What are your thoughts?" required></textarea>
                            <div class="flex justify-end">
                                <button type="submit" class="px-5 py-2 rounded-full bg-primary text-on-primary font-title-md text-sm hover:opacity-90 transition-opacity">Respond</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="mb-10 p-6 rounded-xl border border-outline-variant/30 bg-surface-container-lowest text-center">
                    <p class="text-on-surface-variant font-body-md mb-3">Sign in to join the conversation.</p>
                    <a href="<?= BASEURL ?>/auth" class="inline-block px-5 py-2 rounded-full bg-on-surface text-surface font-title-md text-sm hover:opacity-80 transition-opacity">Sign In</a>
                </div>
            <?php endif; ?>

            <!-- Comments Loop -->
            <div class="flex flex-col gap-6">
                <?php if (!empty($data['comments'])): ?>
                    <?php foreach ($data['comments'] as $comment): ?>
                        <?php
                            $commentAuthorUrl = BASEURL . '/' . urlencode($comment['username'] ?? '');
                            $commentUid = !empty($comment['uid']) ? $comment['uid'] : $comment['id'];
                            $commentDetailUrl = BASEURL . '/' . urlencode($comment['username'] ?? '') . '/comment/' . $commentUid;
                        ?>
                        <div class="flex gap-3 sm:gap-4 group">
                            <a href="<?= $commentAuthorUrl ?>" class="profile-hover-trigger avatar-link w-10 h-10 rounded-full overflow-hidden bg-surface-container-high border border-outline-variant/30 shrink-0 relative z-10" data-username="<?= htmlspecialchars($comment['username'] ?? '') ?>" title="View Profile">
                                <?php if (!empty($comment['profile_picture'])): ?>
                                    <img src="<?= BASEURL ?><?= htmlspecialchars($comment['profile_picture'] ?? '') ?>" class="w-full h-full object-cover rounded-full avatar-img">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center font-bold text-primary text-sm">
                                        <?= htmlspecialchars(substr($comment['username'] ?? 'U', 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                            </a>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-2 text-sm">
                                        <a href="<?= $commentAuthorUrl ?>" class="profile-hover-trigger font-title-md font-bold text-on-surface hover:underline" data-username="<?= htmlspecialchars($comment['username'] ?? '') ?>">
                                            <?= htmlspecialchars($comment['name'] ?? $comment['username'] ?? 'Anonymous') ?>
                                        </a>
                                        <span class="text-on-surface-variant">·</span>
                                        <time class="timeago text-on-surface-variant text-xs" datetime="<?= !empty($comment['created_at']) ? date('c', strtotime($comment['created_at'])) : '' ?>"></time>
                                    </div>
                                </div>
                                <div class="font-body-md text-on-surface text-[15px] leading-relaxed mb-2 whitespace-pre-line">
                                    <?= htmlspecialchars($comment['comment'] ?? $comment['content'] ?? '') ?>
                                </div>
                                <div class="flex items-center gap-4 text-on-surface-variant">
                                    <button type="button" class="flex items-center gap-1.5 hover:text-primary transition-colors text-xs font-title-md" onclick="window.location.href='<?= $commentDetailUrl ?>'">
                                        <span class="material-symbols-outlined text-[18px]">reply</span> Reply (<?= (int)($comment['reply_count'] ?? 0) ?>)
                                    </button>
                                    <?php if(isset($_SESSION['user_id']) && !empty($comment['user_id']) && $_SESSION['user_id'] == $comment['user_id']): ?>
                                        <form action="<?= BASEURL ?>/post/deleteComment/<?= (int)($comment['id'] ?? 0) ?>" method="POST" class="m-0" onsubmit="return confirm('Delete this comment?');">
                                            <button type="submit" class="flex items-center gap-1.5 hover:text-error transition-colors text-xs font-title-md opacity-0 group-hover:opacity-100 focus:opacity-100">
                                                <span class="material-symbols-outlined text-[16px]">delete</span>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-8 text-on-surface-variant font-body-md">
                        No responses yet. Be the first to share your thoughts!
                    </div>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<style>
    /* Quill content formatting inside post detail */
    .quill-content p { margin-bottom: 1.25rem; }
    .quill-content h1, .quill-content h2, .quill-content h3 { font-weight: 700; margin-top: 1.75rem; margin-bottom: 0.75rem; }
    .quill-content a { color: #10b981; text-decoration: underline; }
    .dark .quill-content a { color: #4edea3; }
    .quill-content blockquote { border-left: 3px solid #10b981; padding-left: 1rem; margin: 1.25rem 0; font-style: italic; opacity: 0.85; }
    .quill-content pre { background: rgba(0,0,0,0.1); border: 1px solid rgba(128,128,128,0.2); border-radius: 0.5rem; padding: 1rem; overflow-x: auto; font-family: monospace; margin: 1.25rem 0; }
    .quill-content ul, .quill-content ol { margin-left: 1.75rem; margin-bottom: 1.25rem; }
    .quill-content li { margin-bottom: 0.35rem; }
    .quill-content img { border-radius: 1rem; margin: 1.5rem 0; max-width: 100%; height: auto; }
</style>

<script>
    (function() {
        const progressBar = document.getElementById('reading-progress');
        const postId = <?= (int)($post['id'] ?? 0) ?>;
        const isLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
        let maxProgress = <?= isset($data['reading_progress']) ? (int)$data['reading_progress'] : 0 ?>;
        let lastReportedProgress = maxProgress;
        let reportTimer = null;

        function reportProgress(progress) {
            if (!isLoggedIn || postId <= 0 || progress <= lastReportedProgress) return;
            lastReportedProgress = progress;

            fetch('<?= BASEURL ?>/history/progress/' + postId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ progress: progress })
            }).catch(function(err) {
                console.debug('Failed to report reading progress:', err);
            });
        }

        window.addEventListener('scroll', () => {
            const winScroll = document.documentElement.scrollTop || document.body.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            if (height > 0) {
                const scrolled = Math.min(100, Math.max(0, Math.round((winScroll / height) * 100)));
                if (progressBar) {
                    progressBar.style.width = scrolled + '%';
                }

                if (isLoggedIn && scrolled > maxProgress) {
                    maxProgress = scrolled;
                    clearTimeout(reportTimer);
                    reportTimer = setTimeout(() => {
                        reportProgress(maxProgress);
                    }, 1200);
                }
            }
        }, { passive: true });

        // Flush progress when navigating away
        window.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'hidden' && isLoggedIn && maxProgress > lastReportedProgress) {
                try {
                    navigator.sendBeacon(
                        '<?= BASEURL ?>/history/progress/' + postId,
                        new Blob([JSON.stringify({ progress: maxProgress })], { type: 'application/json' })
                    );
                    lastReportedProgress = maxProgress;
                } catch (e) {}
            }
        });
    })();
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
