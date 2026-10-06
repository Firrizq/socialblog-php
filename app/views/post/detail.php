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
            <a href="<?= BASEURL ?>/home" class="btn-primary px-5 py-2 text-sm">
                <span class="material-symbols-outlined text-base">west</span>
                <span>Return to Feed</span>
            </a>
        </div>
    <?php else: ?>
        <article>
            <!-- Title (If Story) -->
            <?php if(!empty($post['title'])): ?>
                <h1 class="font-serif text-3xl sm:text-4xl md:text-5xl font-bold text-on-surface tracking-tight leading-[1.18] text-wrap-balance mb-6">
                    <?= htmlspecialchars($post['title'] ?? '') ?>
                </h1>
            <?php endif; ?>

            <!-- Editorial Author Metadata -->
            <div class="flex items-center justify-between mb-8 pb-6 border-b border-outline-variant/30">
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
                        <div class="flex items-center gap-2">
                            <a href="<?= BASEURL ?>/<?= urlencode($post['username'] ?? '') ?>" class="profile-hover-trigger font-title-md font-bold text-on-surface hover:underline text-base" data-username="<?= htmlspecialchars($post['username'] ?? '') ?>">
                                <?= htmlspecialchars($post['name'] ?? $post['username'] ?? 'Anonymous') ?>
                            </a>
                            <span class="text-on-surface-variant font-body-md text-sm">@<?= htmlspecialchars($post['username'] ?? 'anon') ?></span>
                        </div>
                        <div class="flex items-center gap-2 font-sans text-on-surface-variant text-xs sm:text-sm mt-0.5 flex-wrap">
                            <?php if(($post['post_type'] ?? 'story') === 'story'): ?>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-surface-container border border-outline-variant/30 text-xs font-medium text-primary">
                                    <span class="material-symbols-outlined text-xs">auto_stories</span>
                                    <?= $post['read_time_minutes'] ?? 1 ?> min read
                                </span>
                                <span class="text-on-surface-variant/40">·</span>
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
                            <form action="<?= BASEURL ?>/post/delete/<?= (int)($post['id'] ?? 0) ?>" method="POST" class="m-0 p-0" data-confirm="Are you sure you want to delete this post? This action cannot be undone." data-confirm-title="Delete Post" data-confirm-btn="Delete">
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
        <section id="discussion" class="mt-12 pt-8 border-t border-outline-variant/30">
            <h3 class="font-serif font-bold text-2xl text-on-surface mb-6 flex items-center gap-2.5">
                <span>Discussion</span>
                <span class="text-xs font-sans font-semibold px-2.5 py-0.5 rounded-full bg-surface-container border border-outline-variant/40 text-on-surface-variant"><?= (int)($post['comment_count'] ?? 0) ?></span>
            </h3>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="flex gap-3.5 mb-10 p-4 sm:p-5 bg-surface-container-lowest border border-outline-variant/50 rounded-2xl shadow-xs">
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
                            <textarea id="comment-input" name="comment" rows="3" class="w-full bg-surface-container-low/40 border border-outline-variant/40 rounded-xl p-3.5 text-on-surface font-sans text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all resize-none placeholder:text-outline" placeholder="Add to the discussion..." required></textarea>
                            <div class="flex justify-end">
                                <button type="submit" class="btn-primary px-5 py-2 text-xs">Respond</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="mb-10 p-6 rounded-2xl border border-outline-variant/40 bg-surface-container-lowest text-center">
                    <p class="text-on-surface-variant font-sans text-sm mb-3">Sign in to join the conversation.</p>
                    <a href="<?= BASEURL ?>/auth" class="btn-primary px-5 py-2 text-xs">Sign In</a>
                </div>
            <?php endif; ?>

            <!-- Comments Loop -->
            <div class="flex flex-col gap-4">
                <?php if (!empty($data['comments'])): ?>
                    <?php foreach ($data['comments'] as $comment): ?>
                        <?php
                            $commentAuthorUrl = BASEURL . '/' . urlencode($comment['username'] ?? '');
                            $commentUid = !empty($comment['uid']) ? $comment['uid'] : $comment['id'];
                            $commentDetailUrl = BASEURL . '/' . urlencode($comment['username'] ?? '') . '/comment/' . $commentUid;
                        ?>
                        <div class="flex gap-3 sm:gap-4 p-4 rounded-xl bg-surface-container-lowest/60 border border-outline-variant/30 hover:border-outline-variant/60 transition-colors group">
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
                                    <?php 
                                        $isCommentLiked = in_array((int)$comment['id'], $data['liked_comment_ids'] ?? [], true);
                                    ?>
                                    <button type="button" class="btn-like-comment flex items-center gap-1.5 <?= $isCommentLiked ? 'text-error' : 'hover:text-error' ?> transition-colors text-xs font-title-md active:scale-95" data-id="<?= (int)$comment['id'] ?>" title="Like">
                                        <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' <?= $isCommentLiked ? 1 : 0 ?>;">favorite</span>
                                        <span class="comment-like-count"><?= (int)($comment['like_count'] ?? 0) ?></span>
                                    </button>
                                    <?php if(isset($_SESSION['user_id']) && (!empty($comment['user_id']) && $_SESSION['user_id'] == $comment['user_id'] || !empty($post['user_id']) && $_SESSION['user_id'] == $post['user_id'])): ?>
                                        <form action="<?= BASEURL ?>/post/deleteComment/<?= (int)($comment['id'] ?? 0) ?>" method="POST" class="m-0 ml-auto" data-confirm="Are you sure you want to delete this comment? This action cannot be undone." data-confirm-title="Delete Comment" data-confirm-btn="Delete">
                                            <button type="submit" class="flex items-center gap-1.5 hover:text-error transition-colors text-xs font-title-md opacity-0 group-hover:opacity-100 focus:opacity-100" title="Delete Comment">
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
    /* Quill editorial content formatting inside post detail */
    .quill-content {
        font-family: 'Newsreader', Georgia, serif;
        font-size: 1.18rem;
        line-height: 1.88;
        color: rgb(var(--color-on-surface));
    }
    .quill-content p { margin-bottom: 1.5rem; text-wrap: pretty; }
    .quill-content h1, .quill-content h2, .quill-content h3 {
        font-family: 'Newsreader', Georgia, serif;
        font-weight: 700;
        letter-spacing: -0.02em;
        line-height: 1.25;
        margin-top: 2.25rem;
        margin-bottom: 1rem;
        color: rgb(var(--color-on-surface));
    }
    .quill-content h1 { font-size: 2.1rem; }
    .quill-content h2 { font-size: 1.7rem; }
    .quill-content h3 { font-size: 1.35rem; }
    .quill-content .story-subtitle {
        font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
        font-size: 1.25rem;
        line-height: 1.6;
        color: rgb(var(--color-on-surface-variant));
        font-weight: 400;
        margin-top: -0.5rem;
        margin-bottom: 1.75rem;
    }
    .quill-content a {
        color: rgb(var(--color-primary));
        text-decoration: underline;
        text-underline-offset: 3px;
        transition: opacity 0.15s ease;
    }
    .quill-content a:hover { opacity: 0.8; }
    .quill-content blockquote {
        border-left: 3.5px solid rgb(var(--color-primary));
        padding: 0.85rem 1.35rem;
        margin: 2.25rem 0;
        font-style: italic;
        font-size: 1.25rem;
        line-height: 1.7;
        background: rgba(var(--color-primary), 0.05);
        border-radius: 0 0.75rem 0.75rem 0;
        color: rgb(var(--color-on-surface));
    }
    .quill-content pre {
        background: rgb(var(--color-surface-container-low));
        border: 1px solid rgba(var(--color-outline-variant), 0.5);
        border-radius: 0.75rem;
        padding: 1.25rem;
        overflow-x: auto;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.92rem;
        line-height: 1.6;
        margin: 1.75rem 0;
    }
    .quill-content code {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        background: rgb(var(--color-surface-container-high));
        padding: 0.2rem 0.4rem;
        border-radius: 0.35rem;
        font-size: 0.88em;
    }
    .quill-content pre code { background: transparent; padding: 0; border-radius: 0; }
    .quill-content ul, .quill-content ol { margin-left: 2rem; margin-bottom: 1.5rem; }
    .quill-content li { margin-bottom: 0.4rem; }
    .quill-content img {
        border-radius: 1rem;
        margin: 2rem 0;
        max-width: 100%;
        height: auto;
        border: 1px solid rgba(var(--color-outline-variant), 0.4);
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
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
