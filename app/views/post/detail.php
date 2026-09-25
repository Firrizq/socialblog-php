<?php 
require_once __DIR__ . '/../templates/header.php'; 
$post = $data['post'] ?? null;
$comments = $data['comments'] ?? [];

$totalComments = 0;
$countNodes = function($tree) use (&$countNodes, &$totalComments) {
    foreach ($tree as $node) {
        $totalComments++;
        if (!empty($node['replies'])) {
            $countNodes($node['replies']);
        }
    }
};
$countNodes($comments);
?>


<div class="flex flex-col w-full pb-20">
    <!-- Top Sticky Bar -->
    <div class="sticky top-0 z-30 bg-surface/90 backdrop-blur-md px-4 py-2.5 border-b border-outline-variant/30 flex items-center gap-4">
        <a href="<?= BASEURL ?>/home" class="w-9 h-9 rounded-full hover:bg-surface-container text-on-surface-variant hover:text-on-surface flex items-center justify-center transition-colors">
            <span class="material-symbols-outlined text-xl">arrow_back</span>
        </a>
        <h1 class="font-title-md text-lg font-bold text-on-surface leading-tight truncate">
            Story
        </h1>
    </div>

    <?php if (!$post): ?>
        <!-- Post Not Found State -->
        <div class="text-center p-12 bg-surface-container-low border-b border-outline-variant/30 my-4">
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
        <!-- Single Post Expansive Article -->
        <article class="p-4 sm:p-7 border-b border-outline-variant/30 flex flex-col gap-4">
            <!-- Author Header -->
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <a href="<?= BASEURL ?>/profile/user/<?= urlencode($post['username'] ?? '') ?>" class="w-12 h-12 rounded-full bg-surface-container-high flex items-center justify-center font-bold text-primary text-lg shrink-0 overflow-hidden hover:ring-2 hover:ring-primary transition-all">
                        <?php if (!empty($post['profile_picture'])): ?>
                            <img src="<?= BASEURL ?><?= htmlspecialchars($post['profile_picture']) ?>" alt="<?= htmlspecialchars($post['username'] ?? '') ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <?= strtoupper(substr($post['username'] ?? 'U', 0, 1)) ?>
                        <?php endif; ?>
                    </a>
                    <div class="flex flex-col min-w-0">
                        <a href="<?= BASEURL ?>/profile/user/<?= urlencode($post['username'] ?? '') ?>" class="font-title-md text-base text-on-surface font-bold hover:text-primary transition-colors truncate">
                            <?= htmlspecialchars($post['name'] ?? $post['username'] ?? 'Anonymous') ?>
                        </a>
                        <span class="font-caption text-xs text-on-surface-variant">
                            @<?= htmlspecialchars($post['username'] ?? 'anon') ?>
                        </span>
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

            <!-- Story Title -->
            <?php if (!empty($post['title'])): ?>
                <h1 class="font-headline-lg text-2xl sm:text-3xl font-bold text-on-surface tracking-tight leading-snug pt-1">
                    <?= htmlspecialchars($post['title']) ?>
                </h1>
            <?php endif; ?>

            <!-- Story Date Timestamp -->
            <div class="text-xs text-on-surface-variant pb-1 flex items-center gap-1.5 font-caption">
                <span>Published on <?= date('F j, Y · g:i A', strtotime($post['created_at'])) ?></span>
            </div>

            <!-- Post Rich Content (Quill Rendered) -->
            <div class="font-body-md text-on-surface leading-relaxed quill-content text-base sm:text-lg py-2">
                <?= preg_replace('/(^|>|\s)#([a-zA-Z_][a-zA-Z0-9_]*)/', '$1<a href="' . BASEURL . '/explore/tag/$2" class="text-primary font-semibold hover:underline">#$2</a>', $post['content'] ?? '') ?>
            </div>
            <?php if(!empty($post['cover_image'])): ?>
                <?php 
                $decoded = json_decode($post['cover_image'], true);
                $imgs = is_array($decoded) ? $decoded : array_filter(explode(',', $post['cover_image']));
                $imgs = array_slice($imgs, 0, 4);
                $imgCount = count($imgs);
                ?>
                <div class="mt-2 mb-4 grid <?= $imgCount === 1 ? 'grid-cols-1' : 'grid-cols-2' ?> gap-1.5 rounded-2xl overflow-hidden border border-outline-variant/30">
                    <?php foreach($imgs as $idx => $img): ?>
                        <img src="<?= BASEURL ?><?= htmlspecialchars(trim($img)) ?>" class="w-full h-full object-cover cursor-pointer hover:opacity-90 transition-opacity <?= ($imgCount === 3 && $idx === 0) ? 'row-span-2' : '' ?> <?= $imgCount > 1 ? 'aspect-[4/3] sm:aspect-video' : 'max-h-[600px]' ?>" alt="Attachment" onclick="event.stopPropagation(); window.openLightbox && openLightbox(this.src, null)">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Action Metrics Bar -->
            <div class="flex items-center justify-between pt-4 mt-2 border-t border-outline-variant/30 text-on-surface-variant text-sm">
                <div class="flex items-center gap-6">
                    <?php 
                        $isLiked = !empty($data['is_liked']); 
                        $isBookmarked = !empty($data['is_bookmarked']);
                    ?>
                    <button class="btn-like flex items-center gap-1.5 <?= $isLiked ? 'text-primary' : 'hover:text-primary' ?> transition-colors active:scale-95" data-id="<?= (int)$post['id'] ?>" title="Like">
                        <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' <?= $isLiked ? 1 : 0 ?>;">favorite</span>
                        <span class="like-count font-caption text-xs font-semibold"><?= (int)($post['like_count'] ?? 0) ?></span>
                    </button>
                    <div class="flex items-center gap-1.5 hover:text-on-surface transition-colors">
                        <span class="material-symbols-outlined text-xl">chat_bubble</span>
                        <span class="font-caption text-xs"><?= $totalComments ?></span>
                    </div>
                    <button class="flex items-center gap-1.5 hover:text-primary transition-colors">
                        <span class="material-symbols-outlined text-xl">sync_alt</span>
                        <span class="font-caption text-xs"><?= $post['repost_count'] ?? 0 ?></span>
                    </button>
                </div>

                <div class="flex items-center gap-3">
                    <button class="btn-bookmark <?= $isBookmarked ? 'text-primary' : 'hover:text-primary' ?> transition-colors active:scale-95" data-id="<?= (int)$post['id'] ?>" title="Bookmark">
                        <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' <?= $isBookmarked ? 1 : 0 ?>;">bookmark</span>
                    </button>
                    <button class="hover:text-primary transition-colors" title="Share" onclick="navigator.clipboard.writeText(window.location.href); alert('Link copied to clipboard!');">
                        <span class="material-symbols-outlined text-xl">share</span>
                    </button>
                </div>
            </div>
        </article>

        <!-- Comments / Discussion Section -->
        <section class="p-4 sm:p-7 flex flex-col gap-6">
            <div class="flex items-center justify-between">
                <h2 class="font-title-md text-lg font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-xl">forum</span>
                    <span>Discussion (<?= $totalComments ?>)</span>
                </h2>
            </div>

            <!-- "Leave a Comment" Form (Visible if Logged In) -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <form action="<?= BASEURL ?>/post/comment/<?= (int)$post['id'] ?>" method="POST" class="flex gap-3.5 items-start bg-surface-container-low border border-outline-variant/30 rounded-2xl p-4 sm:p-5 shadow-sm">
                    <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center font-bold text-on-primary text-base shrink-0 shadow-inner">
                        <?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div class="flex-1 flex flex-col gap-3">
                        <textarea 
                            name="comment" 
                            rows="3" 
                            placeholder="Write a thoughtful response..." 
                            required 
                            class="w-full bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-3 font-body-md text-on-surface text-sm placeholder:text-outline/60 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all resize-y"
                        ></textarea>
                        <div class="flex justify-end">
                            <button 
                                type="submit" 
                                class="inline-flex items-center gap-1.5 px-5 py-2 rounded-full bg-primary-container text-on-primary-container font-label-md text-sm font-semibold hover:bg-primary transition-all shadow-[0_0_0_1px_rgba(16,185,129,0.3)] active:scale-95"
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
                    <span>Join the discussion to share your thoughts with the community.</span>
                    <a href="<?= BASEURL ?>/auth" class="px-4 py-1.5 rounded-full bg-primary-container text-on-primary-container font-semibold hover:bg-primary transition-colors text-xs shrink-0">
                        Sign in to reply
                    </a>
                </div>
            <?php endif; ?>

            <!-- Comments Stream -->
            <?php if (!empty($comments)): ?>
                <?php
                /**
                 * Recursive helper to render comment items and their nested replies
                 */
                $renderCommentItem = function($comment, $post, $depth = 0) use (&$renderCommentItem) {
                    $avatarSize = $depth === 0 ? 'w-10 h-10 text-sm' : 'w-8 h-8 text-xs';
                    $hasReplies = !empty($comment['replies']);
                ?>
                    <div class="py-3.5 sm:py-4 flex flex-col gap-2 group/comment" id="comment-<?= $comment['id'] ?>">
                        <div class="flex gap-3 items-start">
                            <!-- Avatar -->
                            <a href="<?= BASEURL ?>/profile/user/<?= urlencode($comment['username'] ?? '') ?>" class="<?= $avatarSize ?> rounded-full bg-surface-container-high flex items-center justify-center font-bold text-primary shrink-0 overflow-hidden hover:ring-2 hover:ring-primary transition-all">
                                <?php if (!empty($comment['profile_picture'])): ?>
                                    <img src="<?= BASEURL ?><?= htmlspecialchars($comment['profile_picture']) ?>" alt="<?= htmlspecialchars($comment['username'] ?? '') ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <?= strtoupper(substr($comment['username'] ?? 'U', 0, 1)) ?>
                                <?php endif; ?>
                            </a>

                            <!-- Comment Content & Actions -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-1.5 min-w-0 flex-wrap">
                                        <a href="<?= BASEURL ?>/profile/user/<?= urlencode($comment['username'] ?? '') ?>" class="font-title-md text-sm text-on-surface font-semibold hover:text-primary transition-colors truncate">
                                            <?= htmlspecialchars($comment['name'] ?? $comment['username'] ?? 'Anonymous') ?>
                                        </a>
                                        <span class="font-caption text-xs text-on-surface-variant">@<?= htmlspecialchars($comment['username'] ?? 'anon') ?></span>
                                        <a href="<?= BASEURL ?>/post/commentDetail/<?= $comment['id'] ?>" class="font-caption text-xs text-on-surface-variant hover:text-primary transition-colors" title="View thread detail">
                                            · <?= date('M j, Y · g:i A', strtotime($comment['created_at'])) ?>
                                        </a>
                                    </div>
                                    <a href="<?= BASEURL ?>/post/commentDetail/<?= $comment['id'] ?>" class="text-on-surface-variant hover:text-primary p-1 rounded-full hover:bg-surface-container transition-colors" title="Focus thread">
                                        <span class="material-symbols-outlined text-base">open_in_new</span>
                                    </a>
                                </div>

                                <p class="font-body-md text-sm text-on-surface mt-1.5 leading-relaxed whitespace-pre-line break-words">
                                    <?= preg_replace('/(^|>|\s)#([a-zA-Z_][a-zA-Z0-9_]*)/', '$1<a href="' . BASEURL . '/explore/tag/$2" class="text-primary font-semibold hover:underline" onclick="event.stopPropagation();">#$2</a>', htmlspecialchars($comment['comment'] ?? '')) ?>
                                </p>

                                <!-- Comment Actions Toolbar -->
                                <div class="flex items-center gap-4 mt-2">
                                    <!-- Inline Reply Button -->
                                    <button 
                                        type="button" 
                                        onclick="toggleReplyForm(<?= $comment['id'] ?>)" 
                                        class="inline-flex items-center gap-1.5 text-xs text-on-surface-variant hover:text-primary transition-colors py-1 active:scale-95"
                                    >
                                        <span class="material-symbols-outlined text-base">reply</span>
                                        <span>Reply</span>
                                    </button>

                                    <!-- View Thread Link -->
                                    <a 
                                        href="<?= BASEURL ?>/post/commentDetail/<?= $comment['id'] ?>" 
                                        class="inline-flex items-center gap-1 text-xs text-on-surface-variant hover:text-primary transition-colors py-1"
                                    >
                                        <span class="material-symbols-outlined text-base">forum</span>
                                        <span>View thread<?= ($hasReplies || !empty($comment['reply_count'])) ? ' (' . ($hasReplies ? count($comment['replies']) : (int)$comment['reply_count']) . ')' : '' ?></span>
                                    </a>
                                </div>

                                <!-- Hidden Inline Reply Form -->
                                <div id="reply-form-<?= $comment['id'] ?>" class="hidden mt-3 pt-3 border-t border-outline-variant/20">
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <form action="<?= BASEURL ?>/post/comment/<?= (int)$post['id'] ?>" method="POST" class="flex flex-col gap-2.5 bg-surface-container-low/70 p-3 sm:p-4 rounded-xl border border-outline-variant/30">
                                            <input type="hidden" name="parent_id" value="<?= $comment['id'] ?>">
                                            <textarea 
                                                name="comment" 
                                                rows="2" 
                                                placeholder="Replying to @<?= htmlspecialchars($comment['username'] ?? '') ?>..." 
                                                required 
                                                class="w-full bg-surface-container-lowest border border-outline-variant/40 rounded-lg p-2.5 font-body-md text-on-surface text-xs sm:text-sm placeholder:text-outline/60 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all resize-y"
                                            ></textarea>
                                            <div class="flex items-center justify-end gap-2">
                                                <button 
                                                    type="button" 
                                                    onclick="toggleReplyForm(<?= $comment['id'] ?>)" 
                                                    class="px-3 py-1 rounded-full text-xs text-on-surface-variant hover:text-on-surface hover:bg-surface-container transition-colors"
                                                >
                                                    Cancel
                                                </button>
                                                <button 
                                                    type="submit" 
                                                    class="inline-flex items-center gap-1 px-4 py-1.5 rounded-full bg-primary-container text-on-primary-container font-label-md text-xs font-semibold hover:bg-primary transition-all shadow-sm active:scale-95"
                                                >
                                                    <span class="material-symbols-outlined text-sm">reply</span>
                                                    <span>Reply</span>
                                                </button>
                                            </div>
                                        </form>
                                    <?php else: ?>
                                        <div class="p-3 rounded-lg bg-surface-container-lowest border border-outline-variant/20 text-xs text-on-surface-variant flex items-center justify-between">
                                            <span>Sign in to reply to @<?= htmlspecialchars($comment['username'] ?? '') ?></span>
                                            <a href="<?= BASEURL ?>/auth" class="text-primary hover:underline font-semibold">Sign In</a>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Threaded Replies (Nested Hierarchy) -->
                                <?php if ($hasReplies): ?>
                                    <div class="ml-4 sm:ml-7 pl-3 sm:pl-4 border-l-2 border-outline-variant/30 flex flex-col divide-y divide-outline-variant/20 mt-3">
                                        <?php foreach ($comment['replies'] as $reply): ?>
                                            <?php $renderCommentItem($reply, $post, $depth + 1); ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php }; ?>

                <div class="divide-y divide-outline-variant/20 flex flex-col">
                    <?php foreach ($comments as $comment): ?>
                        <?php $renderCommentItem($comment, $post, 0); ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Empty Comments State -->
                <div class="text-center p-8 bg-surface-container-low/40 rounded-xl border border-outline-variant/20">
                    <span class="material-symbols-outlined text-outline text-3xl mb-1">chat</span>
                    <p class="font-title-md text-sm font-semibold text-on-surface">No responses yet</p>
                    <p class="font-body-md text-on-surface-variant text-xs mt-0.5">Be the first to share your thoughts on this story.</p>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</div>

<script>
/**
 * Toggle visibility of inline reply form on comments
 */
function toggleReplyForm(commentId) {
    const form = document.getElementById('reply-form-' + commentId);
    if (!form) return;
    
    if (form.classList.contains('hidden')) {
        form.classList.remove('hidden');
        const textarea = form.querySelector('textarea');
        if (textarea) {
            textarea.focus();
        }
    } else {
        form.classList.add('hidden');
    }
}
</script>

<style>
    /* Quill content formatting inside post detail */
    .quill-content p { margin-bottom: 1rem; }
    .quill-content h1, .quill-content h2, .quill-content h3 { color: #dae2fd; font-weight: 700; margin-top: 1.5rem; margin-bottom: 0.75rem; }
    .quill-content a { color: #4edea3; text-decoration: underline; }
    .quill-content strong { color: #dae2fd; }
    .quill-content blockquote { border-left: 3px solid #10b981; padding-left: 1rem; margin: 1.25rem 0; font-style: italic; color: #bbcabf; }
    .quill-content pre { background: #060e20; border: 1px solid rgba(60, 74, 66, 0.4); border-radius: 0.5rem; padding: 1rem; overflow-x: auto; font-family: monospace; color: #4edea3; margin: 1.25rem 0; }
    .quill-content ul, .quill-content ol { margin-left: 1.75rem; margin-bottom: 1rem; }
    .quill-content li { margin-bottom: 0.35rem; }
</style>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
