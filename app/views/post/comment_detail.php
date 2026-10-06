<?php 
require_once __DIR__ . '/../templates/header.php'; 

$comment = $data['comment'] ?? null;
$post = $data['post'] ?? null;
$parentComment = $data['parent_comment'] ?? null;
$replies = $data['replies'] ?? [];
?>

<div class="flex flex-col w-full pb-20">
    <!-- Top Sticky Bar -->
    <div class="sticky top-0 z-30 bg-surface/90 backdrop-blur-md px-4 py-2.5 border-b border-outline-variant/30 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3 min-w-0">
            <?php 
                $postType = strtolower($post['post_type'] ?? $post['type'] ?? 'story');
                $postUid = !empty($post['uid']) ? $post['uid'] : ($post['id'] ?? '');
                $postAuthor = $post['username'] ?? '';
                $postDetailUrl = $post ? (BASEURL . '/' . urlencode($postAuthor) . '/' . $postType . '/' . $postUid) : (BASEURL . '/home');

                $commentUid = !empty($comment['uid']) ? $comment['uid'] : ($comment['id'] ?? '');
                $currentCommentDetailUrl = $comment ? (BASEURL . '/' . urlencode($comment['username'] ?? '') . '/comment/' . $commentUid) : BASEURL . '/home';

                $backUrl = $postDetailUrl;
                if ($parentComment) {
                    $parentUid = !empty($parentComment['uid']) ? $parentComment['uid'] : $parentComment['id'];
                    $backUrl = BASEURL . '/' . urlencode($parentComment['username'] ?? '') . '/comment/' . $parentUid;
                }
            ?>
            <a href="<?= $backUrl ?>" class="w-9 h-9 rounded-full hover:bg-surface-container text-on-surface-variant hover:text-on-surface flex items-center justify-center transition-colors shrink-0" title="Go back">
                <span class="material-symbols-outlined text-xl">arrow_back</span>
            </a>
            <div class="flex flex-col min-w-0">
                <h1 class="font-title-md text-base sm:text-lg font-bold text-on-surface leading-tight truncate">
                    Thread
                </h1>
                <?php if ($post): ?>
                    <span class="font-caption text-xs text-on-surface-variant truncate">
                        Under "<?= htmlspecialchars($post['title'] ?? 'Story') ?>"
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($post): ?>
            <a href="<?= $postDetailUrl ?>" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-surface-container text-on-surface-variant hover:text-primary hover:border-primary/40 border border-outline-variant/30 font-caption text-xs transition-colors shrink-0">
                <span class="material-symbols-outlined text-sm">auto_stories</span>
                <span>Original Story</span>
            </a>
        <?php endif; ?>
    </div>

    <?php if (!$comment): ?>
        <!-- Comment Not Found State -->
        <div class="text-center p-12 bg-surface-container-low border-b border-outline-variant/30 my-4 mx-4 sm:mx-6 rounded-2xl">
            <span class="material-symbols-outlined text-outline text-5xl mb-3">chat_error</span>
            <h2 class="font-title-md text-2xl font-bold text-on-surface">Thread Not Found</h2>
            <p class="font-body-md text-on-surface-variant text-sm mt-1 mb-6">
                This comment may have been removed or the link is invalid.
            </p>
            <a href="<?= BASEURL ?>/home" class="btn-primary px-5 py-2 text-sm">
                <span class="material-symbols-outlined text-base">west</span>
                <span>Return to Feed</span>
            </a>
        </div>
    <?php else: ?>

        <!-- Post Reference Context Banner -->
        <?php if ($post): ?>
            <div class="p-4 sm:p-5 pb-0">
                <a href="<?= $postDetailUrl ?>" class="flex items-center justify-between p-3.5 rounded-xl bg-surface-container-low/70 hover:bg-surface-container border border-outline-variant/30 transition-all group">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-7 h-7 rounded-lg bg-primary/10 border border-primary/20 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-primary text-base">auto_stories</span>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="font-caption text-xs text-on-surface-variant">In response to post</span>
                            <span class="font-title-md text-xs sm:text-sm text-on-surface font-semibold truncate group-hover:text-primary transition-colors">
                                <?= htmlspecialchars($post['title'] ?? 'Story') ?>
                            </span>
                        </div>
                    </div>
                    <span class="text-primary font-caption text-xs flex items-center gap-1 shrink-0 ml-2">
                        <span>Read</span>
                        <span class="material-symbols-outlined text-xs">arrow_forward</span>
                    </span>
                </a>
            </div>
        <?php endif; ?>

        <!-- Parent Comment Thread Context (Connected by vertical line) -->
        <?php if ($parentComment): ?>
            <?php
                $parentUid = !empty($parentComment['uid']) ? $parentComment['uid'] : $parentComment['id'];
                $parentCommentUrl = BASEURL . '/' . urlencode($parentComment['username'] ?? '') . '/comment/' . $parentUid;
                $parentAuthorUrl = BASEURL . '/' . urlencode($parentComment['username'] ?? '');
            ?>
            <div class="px-4 sm:px-6 pt-4">
                <div class="relative flex gap-3.5 items-start">
                    <!-- Vertical Connecting Thread Line -->
                    <div class="absolute left-5 top-12 bottom-0 w-0.5 bg-outline-variant/40 -mb-4"></div>

                    <!-- Parent Comment Avatar -->
                    <a href="<?= $parentAuthorUrl ?>" class="avatar-link w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center font-bold text-primary text-sm shrink-0 overflow-hidden relative z-10 ring-2 ring-surface hover:ring-primary transition-all" title="View Profile">
                        <?php if (!empty($parentComment['profile_picture'])): ?>
                            <img src="<?= BASEURL ?><?= htmlspecialchars($parentComment['profile_picture']) ?>" alt="<?= htmlspecialchars($parentComment['username'] ?? '') ?>" class="w-full h-full object-cover rounded-full avatar-img">
                        <?php else: ?>
                            <?= strtoupper(substr($parentComment['username'] ?? 'U', 0, 1)) ?>
                        <?php endif; ?>
                    </a>

                    <!-- Parent Comment Details -->
                    <div class="flex-1 min-w-0 pb-4">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <a href="<?= $parentAuthorUrl ?>" class="font-title-md text-sm text-on-surface font-semibold hover:text-primary transition-colors truncate">
                                    <?= htmlspecialchars($parentComment['name'] ?? $parentComment['username'] ?? 'Anonymous') ?>
                                </a>
                                <span class="font-caption text-xs text-on-surface-variant">@<?= htmlspecialchars($parentComment['username'] ?? 'anon') ?></span>
                                <span class="font-caption text-xs text-on-surface-variant">
                                    · <time class="timeago" datetime="<?= date('c', strtotime($parentComment['created_at'])) ?>"></time>
                                </span>
                            </div>
                            <a href="<?= $parentCommentUrl ?>" class="text-xs text-primary hover:underline font-caption flex items-center gap-1">
                                <span>Show earlier thread</span>
                                <span class="material-symbols-outlined text-xs">north</span>
                            </a>
                        </div>
                        <p class="font-body-md text-sm text-on-surface-variant mt-1 line-clamp-3 leading-relaxed whitespace-pre-line break-words">
                            <?= preg_replace('/(^|>|\s)#([a-zA-Z_][a-zA-Z0-9_]*)/', '$1<a href="' . BASEURL . '/explore/tag/$2" class="text-primary font-semibold hover:underline">#$2</a>', htmlspecialchars($parentComment['comment'] ?? '')) ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Focused Comment (Prominent View) -->
        <article class="p-4 sm:p-6 border-b border-outline-variant/30 flex flex-col gap-4">
            <!-- Author Header -->
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <a href="<?= BASEURL ?>/<?= urlencode($comment['username'] ?? '') ?>" class="avatar-link w-12 h-12 rounded-full bg-surface-container-high flex items-center justify-center font-bold text-primary text-base shrink-0 overflow-hidden ring-2 ring-primary/20 hover:ring-primary transition-all relative z-10" title="View Profile">
                        <?php if (!empty($comment['profile_picture'])): ?>
                            <img src="<?= BASEURL ?><?= htmlspecialchars($comment['profile_picture']) ?>" alt="<?= htmlspecialchars($comment['username'] ?? '') ?>" class="w-full h-full object-cover rounded-full avatar-img">
                        <?php else: ?>
                            <?= strtoupper(substr($comment['username'] ?? 'U', 0, 1)) ?>
                        <?php endif; ?>
                    </a>
                    <div class="flex flex-col min-w-0">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <a href="<?= BASEURL ?>/<?= urlencode($comment['username'] ?? '') ?>" class="font-title-md text-base text-on-surface font-bold hover:text-primary transition-colors truncate">
                                <?= htmlspecialchars($comment['name'] ?? $comment['username'] ?? 'Anonymous') ?>
                            </a>
                            <?php if ($post && ($comment['user_id'] == $post['user_id'])): ?>
                                <span class="px-2 py-0.5 rounded-full bg-primary-container/20 text-primary border border-primary/30 font-caption text-xs font-semibold">
                                    Author
                                </span>
                            <?php endif; ?>
                        </div>
                        <span class="font-caption text-xs text-on-surface-variant">
                            @<?= htmlspecialchars($comment['username'] ?? 'anon') ?>
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="navigator.clipboard.writeText(window.location.href); showToast('Thread link copied!');" class="p-2 rounded-full hover:bg-surface-container text-on-surface-variant hover:text-primary transition-colors" title="Share thread">
                        <span class="material-symbols-outlined text-lg">share</span>
                    </button>
                </div>
            </div>

            <!-- Replying To Context Tag -->
            <?php if ($parentComment): ?>
                <div class="text-xs font-caption text-on-surface-variant flex items-center gap-1 -mt-1">
                    <span>Replying to</span>
                    <a href="<?= BASEURL ?>/<?= urlencode($parentComment['username'] ?? '') ?>" class="text-primary hover:underline font-semibold">
                        @<?= htmlspecialchars($parentComment['username'] ?? '') ?>
                    </a>
                </div>
            <?php elseif ($post): ?>
                <div class="text-xs font-caption text-on-surface-variant flex items-center gap-1 -mt-1">
                    <span>Replying to story by</span>
                    <a href="<?= BASEURL ?>/<?= urlencode($post['username'] ?? '') ?>" class="text-primary hover:underline font-semibold">
                        @<?= htmlspecialchars($post['username'] ?? '') ?>
                    </a>
                </div>
            <?php endif; ?>

            <!-- Focused Comment Large Text -->
            <div class="font-body-lg text-lg sm:text-xl text-on-surface leading-relaxed whitespace-pre-line py-2 break-words">
                <?= preg_replace('/(^|>|\s)#([a-zA-Z_][a-zA-Z0-9_]*)/', '$1<a href="' . BASEURL . '/explore/tag/$2" class="text-primary font-semibold hover:underline">#$2</a>', htmlspecialchars($comment['comment'] ?? '')) ?>
            </div>

            <!-- Comment Full Timestamp -->
            <div class="text-xs text-on-surface-variant font-caption flex items-center gap-2 pt-1 border-t border-outline-variant/20">
                <span class="material-symbols-outlined text-sm">schedule</span>
                <span><time class="timeago" datetime="<?= date('c', strtotime($comment['created_at'])) ?>"></time></span>
            </div>

            <!-- Metrics Bar -->
            <div class="flex items-center gap-6 py-2.5 border-y border-outline-variant/30 text-on-surface-variant text-sm font-caption">
                <div>
                    <span class="font-bold text-on-surface"><?= count($replies) ?></span>
                    <span>Replies</span>
                </div>
                <div>
                    <span class="font-bold text-on-surface"><?= (int)($comment['like_count'] ?? 0) ?></span>
                    <span>Likes</span>
                </div>
            </div>

            <!-- Interaction Buttons Bar -->
            <?php 
                $isCommentLiked = !empty($data['is_comment_liked']);
            ?>
            <div class="flex items-center justify-around py-1 text-on-surface-variant">
                <button type="button" onclick="document.getElementById('thread-reply-input').focus();" class="flex items-center gap-1.5 hover:text-primary transition-colors text-xs font-caption py-1.5 px-3 rounded-full hover:bg-surface-container">
                    <span class="material-symbols-outlined text-lg">reply</span>
                    <span>Reply</span>
                </button>
                <button type="button" class="btn-like-comment flex items-center gap-1.5 <?= $isCommentLiked ? 'text-error' : 'hover:text-error' ?> transition-colors text-xs font-caption py-1.5 px-3 rounded-full hover:bg-surface-container active:scale-95" data-id="<?= (int)$comment['id'] ?>" title="Like">
                    <span class="material-symbols-outlined text-lg" style="font-variation-settings: 'FILL' <?= $isCommentLiked ? 1 : 0 ?>;">favorite</span>
                    <span>Like</span>
                    <span class="comment-like-count font-semibold"><?= (int)($comment['like_count'] ?? 0) > 0 ? (int)$comment['like_count'] : '' ?></span>
                </button>
                <button type="button" onclick="navigator.clipboard.writeText(window.location.href); showToast('Thread link copied!');" class="flex items-center gap-1.5 hover:text-primary transition-colors text-xs font-caption py-1.5 px-3 rounded-full hover:bg-surface-container">
                    <span class="material-symbols-outlined text-lg">share</span>
                    <span>Share</span>
                </button>
                <?php if (isset($_SESSION['user_id']) && (!empty($comment['user_id']) && $_SESSION['user_id'] == $comment['user_id'] || !empty($post['user_id']) && $_SESSION['user_id'] == $post['user_id'])): ?>
                    <form action="<?= BASEURL ?>/post/deleteComment/<?= (int)$comment['id'] ?>" method="POST" class="m-0" data-confirm="Are you sure you want to delete this comment? This action cannot be undone." data-confirm-title="Delete Comment" data-confirm-btn="Delete">
                        <button type="submit" class="flex items-center gap-1.5 hover:text-error transition-colors text-xs font-caption py-1.5 px-3 rounded-full hover:bg-surface-container" title="Delete Comment">
                            <span class="material-symbols-outlined text-lg">delete</span>
                            <span>Delete</span>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </article>

        <!-- Reply Form Below Focused Comment -->
        <section class="p-4 sm:p-6 border-b border-outline-variant/30 bg-surface-container-lowest/50">
            <?php if (isset($_SESSION['user_id'])): ?>
                <form action="<?= BASEURL ?>/post/comment/<?= (int)($post['id'] ?? $comment['post_id']) ?>" method="POST" class="flex gap-3.5 items-start">
                    <input type="hidden" name="parent_id" value="<?= (int)$comment['id'] ?>">
                    <input type="hidden" name="redirect_to" value="<?= $currentCommentDetailUrl ?>">
                    
                    <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center font-bold text-on-primary text-base shrink-0 shadow-inner">
                        <?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div class="flex-1 flex flex-col gap-3">
                        <textarea 
                            name="comment" 
                            id="thread-reply-input" 
                            rows="3" 
                            placeholder="Write your reply to @<?= htmlspecialchars($comment['username'] ?? '') ?>..." 
                            required 
                            class="w-full bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-3 font-body-md text-on-surface text-sm placeholder:text-outline/60 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all resize-y"
                        ></textarea>
                        <div class="flex items-center justify-between">
                            <span class="font-caption text-xs text-on-surface-variant">
                                Replying to <span class="text-primary font-semibold">@<?= htmlspecialchars($comment['username'] ?? '') ?></span>
                            </span>
                            <button 
                                type="submit" 
                                class="btn-primary px-5 py-2 text-xs flex items-center gap-1.5"
                            >
                                <span class="material-symbols-outlined text-base">send</span>
                                <span>Reply</span>
                            </button>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <!-- Prompt for Guests -->
                <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant/30 text-center text-sm text-on-surface-variant flex flex-col sm:flex-row items-center justify-between gap-3">
                    <span>Sign in to reply to @<?= htmlspecialchars($comment['username'] ?? '') ?></span>
                    <a href="<?= BASEURL ?>/auth" class="btn-primary px-4 py-1.5 text-xs shrink-0">
                        Sign in to reply
                    </a>
                </div>
            <?php endif; ?>
        </section>

        <!-- Direct Replies Stream -->
        <section class="p-4 sm:p-6 flex flex-col gap-4">
            <div class="flex items-center justify-between pb-2 border-b border-outline-variant/20">
                <h2 class="font-title-md text-base sm:text-lg font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-xl">forum</span>
                    <span>Replies (<?= count($replies) ?>)</span>
                </h2>
            </div>

            <?php if (!empty($replies)): ?>
                <div class="divide-y divide-outline-variant/20 flex flex-col">
                    <?php foreach ($replies as $reply): ?>
                        <?php
                            $replyUid = !empty($reply['uid']) ? $reply['uid'] : $reply['id'];
                            $replyAuthorUrl = BASEURL . '/' . urlencode($reply['username'] ?? '');
                            $replyDetailUrl = BASEURL . '/' . urlencode($reply['username'] ?? '') . '/comment/' . $replyUid;
                        ?>
                        <div class="py-4 flex flex-col gap-2" id="reply-<?= $reply['id'] ?>">
                            <div class="flex gap-3.5 items-start">
                                <!-- Reply Author Avatar -->
                                <a href="<?= $replyAuthorUrl ?>" class="avatar-link w-9 h-9 rounded-full bg-surface-container-high flex items-center justify-center font-bold text-primary text-xs shrink-0 overflow-hidden hover:ring-2 hover:ring-primary transition-all relative z-10" title="View Profile">
                                    <?php if (!empty($reply['profile_picture'])): ?>
                                        <img src="<?= BASEURL ?><?= htmlspecialchars($reply['profile_picture']) ?>" alt="<?= htmlspecialchars($reply['username'] ?? '') ?>" class="w-full h-full object-cover rounded-full avatar-img">
                                    <?php else: ?>
                                        <?= strtoupper(substr($reply['username'] ?? 'U', 0, 1)) ?>
                                    <?php endif; ?>
                                </a>

                                <!-- Reply Content & Meta -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-1.5 min-w-0 flex-wrap">
                                            <a href="<?= $replyAuthorUrl ?>" class="font-title-md text-sm text-on-surface font-semibold hover:text-primary transition-colors truncate">
                                                <?= htmlspecialchars($reply['name'] ?? $reply['username'] ?? 'Anonymous') ?>
                                            </a>
                                            <span class="font-caption text-xs text-on-surface-variant">@<?= htmlspecialchars($reply['username'] ?? 'anon') ?></span>
                                            <a href="<?= $replyDetailUrl ?>" class="font-caption text-xs text-on-surface-variant hover:text-primary transition-colors">
                                                · <time class="timeago" datetime="<?= date('c', strtotime($reply['created_at'])) ?>"></time>
                                            </a>
                                        </div>
                                        <a href="<?= $replyDetailUrl ?>" class="text-on-surface-variant hover:text-primary p-1 rounded-full hover:bg-surface-container transition-colors" title="Focus this thread">
                                            <span class="material-symbols-outlined text-base">open_in_new</span>
                                        </a>
                                    </div>

                                    <p class="font-body-md text-sm text-on-surface mt-1.5 leading-relaxed whitespace-pre-line break-words">
                                        <?= preg_replace('/(^|>|\s)#([a-zA-Z_][a-zA-Z0-9_]*)/', '$1<a href="' . BASEURL . '/explore/tag/$2" class="text-primary font-semibold hover:underline">#$2</a>', htmlspecialchars($reply['comment'] ?? '')) ?>
                                    </p>

                                    <!-- Reply Action Toolbar -->
                                    <div class="flex items-center gap-4 mt-2">
                                        <button 
                                            type="button" 
                                            onclick="toggleNestedReply(<?= $reply['id'] ?>)" 
                                            class="inline-flex items-center gap-1 text-xs text-on-surface-variant hover:text-primary transition-colors py-1 active:scale-95"
                                        >
                                            <span class="material-symbols-outlined text-base">reply</span>
                                            <span>Reply</span>
                                        </button>

                                        <?php 
                                            $isReplyLiked = in_array((int)$reply['id'], $data['liked_comment_ids'] ?? [], true);
                                        ?>
                                        <button 
                                            type="button" 
                                            class="btn-like-comment inline-flex items-center gap-1 text-xs <?= $isReplyLiked ? 'text-error' : 'text-on-surface-variant hover:text-error' ?> transition-colors py-1 active:scale-95" 
                                            data-id="<?= (int)$reply['id'] ?>" 
                                            title="Like"
                                        >
                                            <span class="material-symbols-outlined text-base" style="font-variation-settings: 'FILL' <?= $isReplyLiked ? 1 : 0 ?>;">favorite</span>
                                            <span class="comment-like-count"><?= (int)($reply['like_count'] ?? 0) ?></span>
                                        </button>

                                        <a 
                                            href="<?= $replyDetailUrl ?>" 
                                            class="inline-flex items-center gap-1 text-xs text-on-surface-variant hover:text-primary transition-colors py-1"
                                        >
                                            <span class="material-symbols-outlined text-base">forum</span>
                                            <span>View thread<?= !empty($reply['reply_count']) ? ' (' . (int)$reply['reply_count'] . ')' : '' ?></span>
                                        </a>

                                        <?php if (isset($_SESSION['user_id']) && (!empty($reply['user_id']) && $_SESSION['user_id'] == $reply['user_id'] || !empty($post['user_id']) && $_SESSION['user_id'] == $post['user_id'])): ?>
                                            <form action="<?= BASEURL ?>/post/deleteComment/<?= (int)$reply['id'] ?>" method="POST" class="m-0 ml-auto" data-confirm="Are you sure you want to delete this reply? This action cannot be undone." data-confirm-title="Delete Reply" data-confirm-btn="Delete">
                                                <button type="submit" class="inline-flex items-center gap-1 text-xs text-on-surface-variant hover:text-error transition-colors py-1" title="Delete Reply">
                                                    <span class="material-symbols-outlined text-base">delete</span>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Hidden Nested Reply Form -->
                                    <div id="nested-reply-form-<?= $reply['id'] ?>" class="hidden mt-3 pt-3 border-t border-outline-variant/20">
                                        <?php if (isset($_SESSION['user_id'])): ?>
                                            <form action="<?= BASEURL ?>/post/comment/<?= (int)($post['id'] ?? $comment['post_id']) ?>" method="POST" class="flex flex-col gap-2.5 bg-surface-container-low/70 p-3 rounded-xl border border-outline-variant/30">
                                                <input type="hidden" name="parent_id" value="<?= $reply['id'] ?>">
                                                <input type="hidden" name="redirect_to" value="<?= $currentCommentDetailUrl ?>">
                                                <textarea 
                                                    name="comment" 
                                                    rows="2" 
                                                    placeholder="Replying to @<?= htmlspecialchars($reply['username'] ?? '') ?>..." 
                                                    required 
                                                    class="w-full bg-surface-container-lowest border border-outline-variant/40 rounded-lg p-2.5 font-body-md text-on-surface text-xs sm:text-sm placeholder:text-outline/60 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all resize-y"
                                                ></textarea>
                                                <div class="flex items-center justify-end gap-2">
                                                    <button 
                                                        type="button" 
                                                        onclick="toggleNestedReply(<?= $reply['id'] ?>)" 
                                                        class="btn-ghost px-3 py-1 text-xs"
                                                    >
                                                        Cancel
                                                    </button>
                                                    <button 
                                                        type="submit" 
                                                        class="btn-primary px-4 py-1.5 text-xs flex items-center gap-1"
                                                    >
                                                        <span class="material-symbols-outlined text-sm">reply</span>
                                                        <span>Reply</span>
                                                    </button>
                                                </div>
                                            </form>
                                        <?php else: ?>
                                            <div class="p-3 rounded-lg bg-surface-container-lowest border border-outline-variant/20 text-xs text-on-surface-variant flex items-center justify-between">
                                                <span>Sign in to reply to @<?= htmlspecialchars($reply['username'] ?? '') ?></span>
                                                <a href="<?= BASEURL ?>/auth" class="text-primary hover:underline font-semibold">Sign In</a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Empty Replies State -->
                <div class="text-center p-8 bg-surface-container-low/40 rounded-xl border border-outline-variant/20">
                    <span class="material-symbols-outlined text-outline text-3xl mb-1">chat</span>
                    <p class="font-title-md text-sm font-semibold text-on-surface">No replies yet</p>
                    <p class="font-body-md text-on-surface-variant text-xs mt-0.5">Be the first to reply to this thread.</p>
                </div>
            <?php endif; ?>
        </section>

    <?php endif; ?>
</div>

<script>
/**
 * Toggle visibility of nested inline reply forms
 */
function toggleNestedReply(id) {
    const formEl = document.getElementById('nested-reply-form-' + id);
    if (!formEl) return;
    
    if (formEl.classList.contains('hidden')) {
        formEl.classList.remove('hidden');
        const textarea = formEl.querySelector('textarea');
        if (textarea) textarea.focus();
    } else {
        formEl.classList.add('hidden');
    }
}
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
