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
                $backUrl = $post ? BASEURL . '/post/detail/' . (int)$post['id'] : BASEURL . '/home';
                if ($parentComment) {
                    $backUrl = BASEURL . '/post/commentDetail/' . (int)$parentComment['id'];
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
            <a href="<?= BASEURL ?>/post/detail/<?= (int)$post['id'] ?>" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-surface-container text-on-surface-variant hover:text-primary hover:border-primary/40 border border-outline-variant/30 font-caption text-xs transition-colors shrink-0">
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
            <a href="<?= BASEURL ?>/home" class="inline-flex items-center gap-2 px-space-md py-space-xs rounded-full bg-primary-container text-on-primary-container font-label-md hover:bg-primary transition-colors font-semibold shadow-sm">
                <span class="material-symbols-outlined text-base">west</span>
                <span>Return to Feed</span>
            </a>
        </div>
    <?php else: ?>

        <!-- Post Reference Context Banner -->
        <?php if ($post): ?>
            <div class="p-4 sm:p-5 pb-0">
                <a href="<?= BASEURL ?>/post/detail/<?= (int)$post['id'] ?>" class="flex items-center justify-between p-3.5 rounded-xl bg-surface-container-low/70 hover:bg-surface-container border border-outline-variant/30 transition-all group">
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
            <div class="px-4 sm:px-6 pt-4">
                <div class="relative flex gap-3.5 items-start">
                    <!-- Vertical Connecting Thread Line -->
                    <div class="absolute left-5 top-12 bottom-0 w-0.5 bg-outline-variant/40 -mb-4"></div>

                    <!-- Parent Comment Avatar -->
                    <a href="<?= BASEURL ?>/profile/user/<?= urlencode($parentComment['username'] ?? '') ?>" class="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center font-bold text-primary text-sm shrink-0 overflow-hidden relative z-10 ring-2 ring-surface hover:ring-primary transition-all">
                        <?php if (!empty($parentComment['profile_picture'])): ?>
                            <img src="<?= BASEURL ?><?= htmlspecialchars($parentComment['profile_picture']) ?>" alt="<?= htmlspecialchars($parentComment['username'] ?? '') ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <?= strtoupper(substr($parentComment['username'] ?? 'U', 0, 1)) ?>
                        <?php endif; ?>
                    </a>

                    <!-- Parent Comment Details -->
                    <div class="flex-1 min-w-0 pb-4">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <a href="<?= BASEURL ?>/profile/user/<?= urlencode($parentComment['username'] ?? '') ?>" class="font-title-md text-sm text-on-surface font-semibold hover:text-primary transition-colors truncate">
                                    <?= htmlspecialchars($parentComment['name'] ?? $parentComment['username'] ?? 'Anonymous') ?>
                                </a>
                                <span class="font-caption text-xs text-on-surface-variant">@<?= htmlspecialchars($parentComment['username'] ?? 'anon') ?></span>
                                <span class="font-caption text-xs text-on-surface-variant">
                                    · <?= date('M j, Y', strtotime($parentComment['created_at'])) ?>
                                </span>
                            </div>
                            <a href="<?= BASEURL ?>/post/commentDetail/<?= $parentComment['id'] ?>" class="text-xs text-primary hover:underline font-caption flex items-center gap-1">
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
                    <a href="<?= BASEURL ?>/profile/user/<?= urlencode($comment['username'] ?? '') ?>" class="w-12 h-12 rounded-full bg-surface-container-high flex items-center justify-center font-bold text-primary text-base shrink-0 overflow-hidden ring-2 ring-primary/20 hover:ring-primary transition-all">
                        <?php if (!empty($comment['profile_picture'])): ?>
                            <img src="<?= BASEURL ?><?= htmlspecialchars($comment['profile_picture']) ?>" alt="<?= htmlspecialchars($comment['username'] ?? '') ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <?= strtoupper(substr($comment['username'] ?? 'U', 0, 1)) ?>
                        <?php endif; ?>
                    </a>
                    <div class="flex flex-col min-w-0">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <a href="<?= BASEURL ?>/profile/user/<?= urlencode($comment['username'] ?? '') ?>" class="font-title-md text-base text-on-surface font-bold hover:text-primary transition-colors truncate">
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
                    <a href="<?= BASEURL ?>/profile/user/<?= urlencode($parentComment['username'] ?? '') ?>" class="text-primary hover:underline font-semibold">
                        @<?= htmlspecialchars($parentComment['username'] ?? '') ?>
                    </a>
                </div>
            <?php elseif ($post): ?>
                <div class="text-xs font-caption text-on-surface-variant flex items-center gap-1 -mt-1">
                    <span>Replying to story by</span>
                    <a href="<?= BASEURL ?>/profile/user/<?= urlencode($post['username'] ?? '') ?>" class="text-primary hover:underline font-semibold">
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
                <span><?= date('g:i A · F j, Y', strtotime($comment['created_at'])) ?></span>
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
            <div class="flex items-center justify-around py-1 text-on-surface-variant">
                <button type="button" onclick="document.getElementById('thread-reply-input').focus();" class="flex items-center gap-1.5 hover:text-primary transition-colors text-xs font-caption py-1.5 px-3 rounded-full hover:bg-surface-container">
                    <span class="material-symbols-outlined text-lg">reply</span>
                    <span>Reply</span>
                </button>
                <button type="button" class="flex items-center gap-1.5 hover:text-primary transition-colors text-xs font-caption py-1.5 px-3 rounded-full hover:bg-surface-container">
                    <span class="material-symbols-outlined text-lg">favorite</span>
                    <span>Like</span>
                </button>
                <button type="button" onclick="navigator.clipboard.writeText(window.location.href); showToast('Thread link copied!');" class="flex items-center gap-1.5 hover:text-primary transition-colors text-xs font-caption py-1.5 px-3 rounded-full hover:bg-surface-container">
                    <span class="material-symbols-outlined text-lg">share</span>
                    <span>Share</span>
                </button>
            </div>
        </article>

        <!-- Reply Form Below Focused Comment -->
        <section class="p-4 sm:p-6 border-b border-outline-variant/30 bg-surface-container-lowest/50">
            <?php if (isset($_SESSION['user_id'])): ?>
                <form action="<?= BASEURL ?>/post/comment/<?= (int)($post['id'] ?? $comment['post_id']) ?>" method="POST" class="flex gap-3.5 items-start">
                    <input type="hidden" name="parent_id" value="<?= (int)$comment['id'] ?>">
                    <input type="hidden" name="redirect_to" value="<?= BASEURL ?>/post/commentDetail/<?= (int)$comment['id'] ?>">
                    
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
                    <span>Sign in to reply to @<?= htmlspecialchars($comment['username'] ?? '') ?></span>
                    <a href="<?= BASEURL ?>/auth" class="px-4 py-1.5 rounded-full bg-primary-container text-on-primary-container font-semibold hover:bg-primary transition-colors text-xs shrink-0">
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
                        <div class="py-4 flex flex-col gap-2" id="reply-<?= $reply['id'] ?>">
                            <div class="flex gap-3.5 items-start">
                                <!-- Reply Author Avatar -->
                                <a href="<?= BASEURL ?>/profile/user/<?= urlencode($reply['username'] ?? '') ?>" class="w-9 h-9 rounded-full bg-surface-container-high flex items-center justify-center font-bold text-primary text-xs shrink-0 overflow-hidden hover:ring-2 hover:ring-primary transition-all">
                                    <?php if (!empty($reply['profile_picture'])): ?>
                                        <img src="<?= BASEURL ?><?= htmlspecialchars($reply['profile_picture']) ?>" alt="<?= htmlspecialchars($reply['username'] ?? '') ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <?= strtoupper(substr($reply['username'] ?? 'U', 0, 1)) ?>
                                    <?php endif; ?>
                                </a>

                                <!-- Reply Content & Meta -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-1.5 min-w-0 flex-wrap">
                                            <a href="<?= BASEURL ?>/profile/user/<?= urlencode($reply['username'] ?? '') ?>" class="font-title-md text-sm text-on-surface font-semibold hover:text-primary transition-colors truncate">
                                                <?= htmlspecialchars($reply['name'] ?? $reply['username'] ?? 'Anonymous') ?>
                                            </a>
                                            <span class="font-caption text-xs text-on-surface-variant">@<?= htmlspecialchars($reply['username'] ?? 'anon') ?></span>
                                            <a href="<?= BASEURL ?>/post/commentDetail/<?= $reply['id'] ?>" class="font-caption text-xs text-on-surface-variant hover:text-primary transition-colors">
                                                · <?= date('M j, Y · g:i A', strtotime($reply['created_at'])) ?>
                                            </a>
                                        </div>
                                        <a href="<?= BASEURL ?>/post/commentDetail/<?= $reply['id'] ?>" class="text-on-surface-variant hover:text-primary p-1 rounded-full hover:bg-surface-container transition-colors" title="Focus this thread">
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

                                        <a 
                                            href="<?= BASEURL ?>/post/commentDetail/<?= $reply['id'] ?>" 
                                            class="inline-flex items-center gap-1 text-xs text-on-surface-variant hover:text-primary transition-colors py-1"
                                        >
                                            <span class="material-symbols-outlined text-base">forum</span>
                                            <span>View thread<?= !empty($reply['reply_count']) ? ' (' . (int)$reply['reply_count'] . ')' : '' ?></span>
                                        </a>
                                    </div>

                                    <!-- Hidden Nested Reply Form -->
                                    <div id="nested-reply-form-<?= $reply['id'] ?>" class="hidden mt-3 pt-3 border-t border-outline-variant/20">
                                        <?php if (isset($_SESSION['user_id'])): ?>
                                            <form action="<?= BASEURL ?>/post/comment/<?= (int)($post['id'] ?? $comment['post_id']) ?>" method="POST" class="flex flex-col gap-2.5 bg-surface-container-low/70 p-3 rounded-xl border border-outline-variant/30">
                                                <input type="hidden" name="parent_id" value="<?= $reply['id'] ?>">
                                                <input type="hidden" name="redirect_to" value="<?= BASEURL ?>/post/commentDetail/<?= (int)$comment['id'] ?>">
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

<!-- Simple Toast Notification -->
<div id="thread-toast" class="fixed bottom-6 right-6 z-50 transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none bg-surface-container-high border border-primary/40 text-on-surface px-4 py-2.5 rounded-xl shadow-lg flex items-center gap-2 text-xs font-title-md">
    <span class="material-symbols-outlined text-primary text-base">check_circle</span>
    <span id="toast-message">Notification</span>
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

/**
 * Toast popup notification helper
 */
function showToast(msg) {
    const toast = document.getElementById('thread-toast');
    const msgEl = document.getElementById('toast-message');
    if (!toast || !msgEl) return;
    
    msgEl.textContent = msg;
    toast.classList.remove('translate-y-20', 'opacity-0');
    toast.classList.add('translate-y-0', 'opacity-100');
    
    setTimeout(() => {
        toast.classList.remove('translate-y-0', 'opacity-100');
        toast.classList.add('translate-y-20', 'opacity-0');
    }, 2500);
}
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
