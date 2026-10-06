</main>

            <!-- TAHAP 3: Right Sidebar -->
            <aside class="hidden xl:block w-80 sticky top-16 h-[calc(100vh-4rem)] p-gutter border-l border-outline-variant/30 overflow-y-auto">
                <?php
                require_once __DIR__ . '/../../models/User.php';
                require_once __DIR__ . '/../../models/Post_model.php';
                require_once __DIR__ . '/../../models/Interaction_model.php';
                $currentUserId = (int)($_SESSION['active_user_id'] ?? $_SESSION['user_id'] ?? 0);
                $suggestedWriters = (new User())->getSuggestedWriters($currentUserId);
                $popularPosts = array_slice((new Post_model())->getPopularPosts(), 0, 4);
                $interactionModel = new Interaction_model();
                ?>
                <div class="flex flex-col gap-6">
                    <!-- Search Input -->
                    <form action="<?= BASEURL ?>/explore" method="GET" class="relative flex items-center w-full">
                        <span class="material-symbols-outlined absolute left-3.5 text-on-surface-variant text-lg pointer-events-none">search</span>
                        <input name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" class="w-full pl-10 pr-4 py-2 bg-surface-container-lowest border border-outline-variant/60 rounded-full font-sans text-sm text-on-surface placeholder:text-on-surface-variant/60 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary shadow-2xs transition-all" placeholder="Search dispatches & topics..." type="search"/>
                    </form>
                    
                    <!-- Popular Stories / Curated Dispatch -->
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between">
                            <span class="font-serif font-bold text-base text-on-surface tracking-tight">Top Stories</span>
                            <a href="<?= BASEURL ?>/explore" class="text-xs font-sans text-primary hover:underline">Explore all</a>
                        </div>
                        <div class="flex flex-col gap-2.5">
                            <?php foreach($popularPosts as $popPost): ?>
                                <?php
                                    $popType = strtolower($popPost['post_type'] ?? $popPost['type'] ?? 'story');
                                    $popUid = !empty($popPost['uid']) ? $popPost['uid'] : $popPost['id'];
                                    $popUrl = BASEURL . '/' . urlencode($popPost['username']) . '/' . $popType . '/' . $popUid;
                                ?>
                                <a href="<?= $popUrl ?>" class="flex flex-col gap-1 p-3 rounded-xl bg-surface-container-lowest border border-outline-variant/40 hover:border-primary/40 hover:bg-surface-container-low/60 transition-all group shadow-2xs">
                                    <span class="font-serif text-[15px] font-bold text-on-surface group-hover:text-primary transition-colors line-clamp-2 leading-snug"><?= htmlspecialchars($popPost['title']) ?></span>
                                    <div class="flex items-center gap-1.5 font-sans text-xs text-on-surface-variant mt-0.5">
                                        <span>@<?= htmlspecialchars($popPost['username']) ?></span>
                                        <span class="text-on-surface-variant/40">·</span>
                                        <span><?= $popPost['read_time_minutes'] ?? 1 ?> min read</span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Popular Tags -->
                    <div class="flex flex-col gap-3">
                        <span class="font-serif font-bold text-base text-on-surface tracking-tight">Curated Topics</span>
                        <div class="flex flex-wrap gap-2">
                            <?php 
                            $popularTags = ['Technology', 'Culture', 'Design', 'Programming', 'Essays'];
                            foreach($popularTags as $popTag): 
                            ?>
                                <a href="<?= BASEURL ?>/explore/tag/<?= urlencode($popTag) ?>" class="px-3 py-1.5 rounded-full bg-surface-container-lowest border border-outline-variant/40 text-on-surface-variant hover:border-primary/60 hover:text-primary hover:bg-primary/5 text-xs font-medium transition-all shadow-2xs">
                                    #<?= htmlspecialchars($popTag) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Suggested Writers -->
                    <div class="flex flex-col gap-3">
                        <span class="font-serif font-bold text-base text-on-surface tracking-tight">Contributing Writers</span>
                        <div class="flex flex-col gap-2">
                            <?php if (!empty($suggestedWriters)): ?>
                                <?php foreach($suggestedWriters as $writer): ?>
                                    <?php 
                                        $isWriterFollowing = false;
                                        if ($currentUserId > 0) {
                                            $isWriterFollowing = $interactionModel->isFollowing((int)$currentUserId, (int)$writer['id']);
                                        }
                                    ?>
                                    <div class="flex items-center justify-between gap-2 p-2 rounded-xl hover:bg-surface-container-lowest/80 transition-colors">
                                        <a href="<?= BASEURL ?>/<?= urlencode($writer['username']) ?>" class="flex items-center gap-2.5 min-w-0 group">
                                            <div class="w-8 h-8 rounded-full bg-surface-container-high border border-outline-variant/40 flex items-center justify-center font-bold text-primary shrink-0 overflow-hidden text-xs">
                                                <?php if (!empty($writer['profile_picture'])): ?>
                                                    <img src="<?= BASEURL ?><?= htmlspecialchars($writer['profile_picture']) ?>" alt="avatar" class="w-full h-full object-cover">
                                                <?php else: ?>
                                                    <?= strtoupper(substr($writer['username'], 0, 1)) ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex flex-col min-w-0">
                                                <p class="font-sans font-bold text-xs text-on-surface leading-tight truncate group-hover:text-primary transition-colors"><?= htmlspecialchars($writer['name'] ?? $writer['username']) ?></p>
                                                <p class="font-sans text-[11px] text-on-surface-variant truncate max-w-[120px]">@<?= htmlspecialchars($writer['username']) ?></p>
                                            </div>
                                        </a>
                                        <button type="button" 
                                                class="follow-btn btn-follow btn-pill-follow <?= $isWriterFollowing ? 'following' : '' ?> text-xs shrink-0" 
                                                data-user-id="<?= (int)$writer['id'] ?>" 
                                                data-id="<?= (int)$writer['id'] ?>" 
                                                data-scope="sidebar">
                                            <?= $isWriterFollowing ? 'Following' : 'Follow' ?>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="font-sans text-xs text-on-surface-variant">No suggestions available.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Independent Press Colophon -->
                    <div class="pt-4 border-t border-outline-variant/30 flex flex-col gap-1.5 text-xs text-on-surface-variant/70 font-sans">
                        <div class="flex flex-wrap gap-x-3 gap-y-1">
                            <a href="<?= BASEURL ?>/explore" class="hover:underline">Explore</a>
                            <a href="<?= BASEURL ?>/home" class="hover:underline">Feed</a>
                            <a href="<?= BASEURL ?>/bookmarks" class="hover:underline">Reading List</a>
                        </div>
                        <p class="text-[11px] text-on-surface-variant/50 mt-1">Blogggle Press · An independent publishing collective</p>
                    </div>
                </div>
            </aside>
            <!-- Akhir dari max-w-7xl -->
        </div>
    <!-- Akhir dari md:pl-60 -->
    </div>

    <!-- Quill.js JS Script diletakkan di footer agar editor bisa jalan -->
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.6/dist/quill.min.js" crossorigin="anonymous"></script>

    <!-- FFmpeg.wasm & Client-Side Video Compressor -->
    <script src="https://cdn.jsdelivr.net/npm/@ffmpeg/ffmpeg@0.11.6/dist/ffmpeg.min.js" crossorigin="anonymous"></script>
    <script src="<?= BASEURL ?>/js/video-compressor.js"></script>

    <!-- AJAX Interactions Script: Likes, Bookmarks, and Follows -->
    <script>
    (function() {
        const BASE_URL = '<?= BASEURL ?>';

        document.addEventListener('click', async function(e) {
            // 1. Handle Like (.btn-like)
            const likeBtn = e.target.closest('.btn-like');
            if (likeBtn) {
                e.preventDefault();
                e.stopPropagation();

                const postId = likeBtn.dataset.id;
                if (!postId) return;

                likeBtn.disabled = true;

                try {
                    const response = await fetch(`${BASE_URL}/action/like/${postId}`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (response.status === 401) {
                        window.location.href = `${BASE_URL}/auth`;
                        return;
                    }

                    const data = await response.json();

                    if (data.success) {
                        const icon = likeBtn.querySelector('.material-symbols-outlined');
                        const countSpan = likeBtn.querySelector('.like-count');

                        if (data.status === 'liked') {
                            likeBtn.classList.add('text-primary');
                            likeBtn.classList.remove('text-on-surface-variant');
                            if (icon) {
                                icon.style.fontVariationSettings = "'FILL' 1";
                            }
                        } else {
                            likeBtn.classList.remove('text-primary');
                            likeBtn.classList.add('text-on-surface-variant');
                            if (icon) {
                                icon.style.fontVariationSettings = "'FILL' 0";
                            }
                        }

                        if (countSpan && typeof data.count !== 'undefined') {
                            countSpan.textContent = data.count;
                        }

                        // Sync any duplicate like buttons on the same page
                        document.querySelectorAll(`.btn-like[data-id="${postId}"]`).forEach(btn => {
                            if (btn !== likeBtn) {
                                const otherIcon = btn.querySelector('.material-symbols-outlined');
                                const otherCount = btn.querySelector('.like-count');
                                if (data.status === 'liked') {
                                    btn.classList.add('text-primary');
                                    btn.classList.remove('text-on-surface-variant');
                                    if (otherIcon) otherIcon.style.fontVariationSettings = "'FILL' 1";
                                } else {
                                    btn.classList.remove('text-primary');
                                    btn.classList.add('text-on-surface-variant');
                                    if (otherIcon) otherIcon.style.fontVariationSettings = "'FILL' 0";
                                }
                                if (otherCount && typeof data.count !== 'undefined') {
                                    otherCount.textContent = data.count;
                                }
                            }
                        });
                    }
                } catch (err) {
                    console.error('Like error:', err);
                } finally {
                    likeBtn.disabled = false;
                }
                return;
            }

            // 1b. Handle Comment Like (.btn-like-comment)
            const commentLikeBtn = e.target.closest('.btn-like-comment');
            if (commentLikeBtn) {
                e.preventDefault();
                e.stopPropagation();

                const commentId = commentLikeBtn.dataset.id;
                if (!commentId) return;

                commentLikeBtn.disabled = true;

                try {
                    const response = await fetch(`${BASE_URL}/action/likeComment/${commentId}`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (response.status === 401) {
                        window.location.href = `${BASE_URL}/auth`;
                        return;
                    }

                    const data = await response.json();

                    if (data.success) {
                        const icon = commentLikeBtn.querySelector('.material-symbols-outlined');
                        const countSpan = commentLikeBtn.querySelector('.comment-like-count');

                        if (data.status === 'liked') {
                            commentLikeBtn.classList.add('text-error');
                            commentLikeBtn.classList.remove('text-on-surface-variant');
                            if (icon) {
                                icon.style.fontVariationSettings = "'FILL' 1";
                            }
                        } else {
                            commentLikeBtn.classList.remove('text-error');
                            commentLikeBtn.classList.add('text-on-surface-variant');
                            if (icon) {
                                icon.style.fontVariationSettings = "'FILL' 0";
                            }
                        }

                        if (countSpan && typeof data.count !== 'undefined') {
                            countSpan.textContent = data.count;
                        }

                        // Sync any duplicate comment like buttons for the same comment
                        document.querySelectorAll(`.btn-like-comment[data-id="${commentId}"]`).forEach(btn => {
                            if (btn !== commentLikeBtn) {
                                const otherIcon = btn.querySelector('.material-symbols-outlined');
                                const otherCount = btn.querySelector('.comment-like-count');
                                if (data.status === 'liked') {
                                    btn.classList.add('text-error');
                                    btn.classList.remove('text-on-surface-variant');
                                    if (otherIcon) otherIcon.style.fontVariationSettings = "'FILL' 1";
                                } else {
                                    btn.classList.remove('text-error');
                                    btn.classList.add('text-on-surface-variant');
                                    if (otherIcon) otherIcon.style.fontVariationSettings = "'FILL' 0";
                                }
                                if (otherCount && typeof data.count !== 'undefined') {
                                    otherCount.textContent = data.count;
                                }
                            }
                        });
                    }
                } catch (err) {
                    console.error('Comment like error:', err);
                } finally {
                    commentLikeBtn.disabled = false;
                }
                return;
            }

            // 2. Handle Bookmark (.btn-bookmark)
            const bookmarkBtn = e.target.closest('.btn-bookmark');
            if (bookmarkBtn) {
                e.preventDefault();
                e.stopPropagation();

                const postId = bookmarkBtn.dataset.id;
                if (!postId) return;

                bookmarkBtn.disabled = true;

                try {
                    const response = await fetch(`${BASE_URL}/action/bookmark/${postId}`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (response.status === 401) {
                        window.location.href = `${BASE_URL}/auth`;
                        return;
                    }

                    const data = await response.json();

                    if (data.success) {
                        const isBookmarked = data.status === 'bookmarked';
                        const icon = bookmarkBtn.querySelector('.material-symbols-outlined');

                        if (isBookmarked) {
                            bookmarkBtn.classList.add('text-primary');
                            bookmarkBtn.classList.remove('hover:text-primary');
                            bookmarkBtn.classList.remove('text-on-surface-variant');
                            if (icon) {
                                icon.style.fontVariationSettings = "'FILL' 1";
                            }
                            if (typeof showToast === 'function') showToast('Saved to bookmarks', 'success');
                        } else {
                            bookmarkBtn.classList.remove('text-primary');
                            bookmarkBtn.classList.add('hover:text-primary');
                            if (icon) {
                                icon.style.fontVariationSettings = "'FILL' 0";
                            }
                            if (typeof showToast === 'function') showToast('Removed from bookmarks', 'info');

                            // Dim post card if currently on the Bookmarks page
                            if (window.location.pathname.includes('/bookmarks') || window.location.search.includes('tab=bookmarks')) {
                                const card = bookmarkBtn.closest('article');
                                if (card) {
                                    card.style.transition = 'opacity 0.3s ease, filter 0.3s ease';
                                    card.style.opacity = '0.35';
                                    card.style.filter = 'grayscale(0.7)';
                                }
                            }
                        }

                        const countSpan = bookmarkBtn.querySelector('.bookmark-count');
                        if (countSpan && typeof data.count !== 'undefined') {
                            countSpan.textContent = data.count;
                        }

                        // Sync any duplicate bookmark buttons
                        document.querySelectorAll(`.btn-bookmark[data-id="${postId}"]`).forEach(btn => {
                            if (btn !== bookmarkBtn) {
                                const otherIcon = btn.querySelector('.material-symbols-outlined');
                                const otherCount = btn.querySelector('.bookmark-count');
                                if (isBookmarked) {
                                    btn.classList.add('text-primary');
                                    btn.classList.remove('hover:text-primary');
                                    btn.classList.remove('text-on-surface-variant');
                                    if (otherIcon) otherIcon.style.fontVariationSettings = "'FILL' 1";
                                } else {
                                    btn.classList.remove('text-primary');
                                    btn.classList.add('hover:text-primary');
                                    if (otherIcon) otherIcon.style.fontVariationSettings = "'FILL' 0";
                                }
                                if (otherCount && typeof data.count !== 'undefined') {
                                    otherCount.textContent = data.count;
                                }
                            }
                        });
                    } else if (data.message) {
                        if (typeof showToast === 'function') showToast(data.message, 'error');
                    }
                } catch (err) {
                    console.error('Bookmark error:', err);
                    showToast(err.message || 'Bookmark action failed', 'error');
                } finally {
                    bookmarkBtn.disabled = false;
                }
                return;
            }

            // 3. Handle Repost (.btn-repost, .repost-btn)
            const repostBtn = e.target.closest('.btn-repost, .repost-btn');
            if (repostBtn) {
                e.preventDefault();
                e.stopPropagation();

                const postId = repostBtn.dataset.id;
                if (!postId) return;

                repostBtn.disabled = true;

                try {
                    const response = await fetch(`${BASE_URL}/repost/toggle/${postId}`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (response.status === 401) {
                        window.location.href = `${BASE_URL}/auth`;
                        return;
                    }

                    const data = await response.json();

                    if (data.status === 'success' || data.success) {
                        const isReposted = !!data.is_reposted;
                        const newCount = typeof data.repost_count !== 'undefined' ? data.repost_count : (data.count ?? 0);

                        const updateButtonUI = (btn, active, count) => {
                            const icon = btn.querySelector('.material-symbols-outlined');
                            const countSpan = btn.querySelector('.repost-count');

                            if (active) {
                                btn.classList.add('text-emerald-500');
                                btn.classList.remove('hover:text-emerald-500');
                                btn.classList.remove('hover:text-primary');
                                if (icon) icon.classList.add('font-bold');
                            } else {
                                btn.classList.remove('text-emerald-500');
                                btn.classList.add('hover:text-emerald-500');
                                if (icon) icon.classList.remove('font-bold');
                            }

                            if (countSpan && typeof count !== 'undefined') {
                                countSpan.textContent = count;
                            }
                        };

                        updateButtonUI(repostBtn, isReposted, newCount);

                        // Sync any duplicate repost buttons for this post on the page
                        document.querySelectorAll(`.btn-repost[data-id="${postId}"], .repost-btn[data-id="${postId}"]`).forEach(btn => {
                            if (btn !== repostBtn) {
                                updateButtonUI(btn, isReposted, newCount);
                            }
                        });

                        if (typeof showToast === 'function') {
                            showToast(isReposted ? 'Reposted to your profile' : 'Removed repost', isReposted ? 'success' : 'info');
                        }
                    } else if (data.message) {
                        if (typeof showToast === 'function') showToast(data.message, 'error');
                    }
                } catch (err) {
                    console.error('Repost error:', err);
                    showToast(err.message || 'Repost action failed', 'error');
                } finally {
                    repostBtn.disabled = false;
                }
                return;
            }

            // 4. Handle Follow (.btn-follow / .follow-btn)
            const followBtn = e.target.closest('.btn-follow, .follow-btn');
            if (followBtn) {
                e.preventDefault();
                e.stopPropagation();

                const userId = followBtn.dataset.userId || followBtn.dataset.id;
                if (!userId) return;

                followBtn.disabled = true;

                try {
                    const response = await fetch(`${BASE_URL}/action/follow/${userId}`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (response.status === 401) {
                        window.location.href = `${BASE_URL}/auth`;
                        return;
                    }

                    const data = await response.json();

                    if (data.success) {
                        const isFollowed = (data.status === 'followed');
                        const isProfileScope = (followBtn.dataset.scope === 'profile' || followBtn.id === 'profile-follow-btn');

                        // ONLY toggle the exact button that was clicked
                        if (isProfileScope) {
                            if (isFollowed) {
                                followBtn.textContent = 'Following';
                                followBtn.className = 'follow-btn btn-follow btn-pill-follow following text-sm px-6 py-1.5 relative z-10';
                            } else {
                                followBtn.textContent = 'Follow';
                                followBtn.className = 'follow-btn btn-follow btn-pill-follow text-sm px-6 py-1.5 relative z-10';
                            }

                            // Update follower count on profile header ONLY when following/unfollowing via profile hero button
                            const followerCountEl = document.getElementById('profile-follower-count');
                            if (followerCountEl && typeof data.follower_count !== 'undefined') {
                                followerCountEl.textContent = Number(data.follower_count).toLocaleString();
                            }
                        } else if (followBtn.dataset.scope === 'card') {
                            // Scoped to hover card
                            if (isFollowed) {
                                followBtn.textContent = 'Following';
                                followBtn.className = 'follow-btn btn-follow btn-pill-follow following text-xs shrink-0';
                            } else {
                                followBtn.textContent = 'Follow';
                                followBtn.className = 'follow-btn btn-follow btn-pill-follow text-xs shrink-0';
                            }

                            const cardFollowerCount = followBtn.closest('.hover-card-popover, #profile-hover-card-popover')?.querySelector('.card-follower-count');
                            if (cardFollowerCount && typeof data.follower_count !== 'undefined') {
                                cardFollowerCount.textContent = Number(data.follower_count).toLocaleString();
                            }
                        } else {
                            // Scoped to the specific clicked sidebar button or modal list
                            if (isFollowed) {
                                followBtn.textContent = 'Following';
                                followBtn.className = 'follow-btn btn-follow btn-pill-follow following text-xs shrink-0';
                            } else {
                                followBtn.textContent = 'Follow';
                                followBtn.className = 'follow-btn btn-follow btn-pill-follow text-xs shrink-0';
                            }
                        }

                        // Invalidate hover card cache so future hovers fetch updated state
                        if (typeof window.profileHoverCardInvalidate === 'function') {
                            window.profileHoverCardInvalidate(userId);
                        }

                        // Modern non-blocking feedback
                        showToast(isFollowed ? 'Following author' : 'Unfollowed author', isFollowed ? 'success' : 'info');
                    } else if (data.message) {
                        showToast(data.message, 'error');
                    }
                } catch (err) {
                    console.error('Follow error:', err);
                    showToast(err.message || 'Network error occurred', 'error');
                } finally {
                    followBtn.disabled = false;
                }
                return;
            }
        }, true);
    })();
    </script>

    <!-- Image Lightbox Styles -->
    <style>
        .post-media-image, .quill-content img { cursor: pointer; transition: opacity 0.2s; }
        .post-media-image:hover, .quill-content img:hover { opacity: 0.85; }
    </style>

    <!-- Split-Screen Image Lightbox Modal (Twitter/X Style Gallery) -->
    <div id="imageLightbox" class="fixed inset-0 z-[100] hidden bg-black/95 backdrop-blur-md flex flex-col md:flex-row opacity-0 transition-opacity duration-300">
        <!-- Close Button -->
        <button type="button" onclick="closeLightbox()" class="absolute top-4 sm:top-6 left-4 sm:left-6 w-10 h-10 bg-white/10 hover:bg-white/25 text-white rounded-full flex items-center justify-center backdrop-blur-md transition-colors z-[110]" title="Close (Esc)">
            <span class="material-symbols-outlined text-xl">close</span>
        </button>

        <!-- Left: Image Gallery Area -->
        <div class="flex-1 flex items-center justify-center p-4 sm:p-12 relative h-[60vh] md:h-full group" onclick="closeLightbox()">
            <!-- Prev Button -->
            <button type="button" id="lightboxPrev" onclick="navigateLightbox(-1); event.stopPropagation();" class="absolute left-2 sm:left-6 top-1/2 -translate-y-1/2 w-10 h-10 sm:w-12 sm:h-12 bg-white/10 hover:bg-white/25 text-white rounded-full flex items-center justify-center backdrop-blur-md transition-colors z-[110] hidden" title="Previous (Left Arrow)">
                <span class="material-symbols-outlined text-2xl">chevron_left</span>
            </button>
            
            <img id="lightboxImage" src="" alt="Expanded Image" class="max-w-full max-h-full object-contain shadow-2xl scale-95 transition-transform duration-300 cursor-default" onclick="event.stopPropagation();">
            <video id="lightboxVideo" src="" controls playsinline class="max-w-full max-h-full object-contain shadow-2xl scale-95 transition-transform duration-300 cursor-default hidden" onclick="event.stopPropagation();"></video>
            
            <!-- Next Button -->
            <button type="button" id="lightboxNext" onclick="navigateLightbox(1); event.stopPropagation();" class="absolute right-2 sm:right-6 top-1/2 -translate-y-1/2 w-10 h-10 sm:w-12 sm:h-12 bg-white/10 hover:bg-white/25 text-white rounded-full flex items-center justify-center backdrop-blur-md transition-colors z-[110] hidden" title="Next (Right Arrow)">
                <span class="material-symbols-outlined text-2xl">chevron_right</span>
            </button>
        </div>

        <!-- Right: Context/Sidebar Area -->
        <div id="lightboxSidebar" class="w-full md:w-[400px] lg:w-[480px] bg-surface border-t md:border-t-0 md:border-l border-outline-variant/30 h-[40vh] md:h-full overflow-y-auto flex flex-col z-[105] shadow-[0_-10px_30px_rgba(0,0,0,0.5)] md:shadow-[-10px_0_30px_rgba(0,0,0,0.5)]">
            <div class="flex flex-col h-full bg-surface" id="lightboxSidebarContent">
                <!-- Dynamic context content injected via JS -->
            </div>
        </div>
    </div>

    <!-- Global Dropdown & Lightbox Gallery Logic -->
    <script>
    function toggleMenu(event, menuId) {
        event.preventDefault();
        event.stopPropagation();
        document.querySelectorAll('.dropdown-container > div[id^="menu-"]').forEach(el => {
            if (el.id !== menuId) el.classList.add('hidden');
        });
        const menu = document.getElementById(menuId);
        if (menu) menu.classList.toggle('hidden');
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown-container')) {
            document.querySelectorAll('.dropdown-container > div[id^="menu-"]').forEach(el => el.classList.add('hidden'));
        }
    });

    window.toggleReplyForm = window.toggleReplyForm || function(commentId) {
        const form = document.getElementById('reply-form-' + commentId);
        if (form) {
            form.classList.toggle('hidden');
            if(!form.classList.contains('hidden')) form.querySelector('textarea')?.focus();
        }
    };

    window.toggleNestedReply = window.toggleNestedReply || function(id) {
        const form = document.getElementById('nested-reply-form-' + id);
        if (form) {
            form.classList.toggle('hidden');
            if(!form.classList.contains('hidden')) form.querySelector('textarea')?.focus();
        }
    };

    // Lightbox Gallery State
    let lightboxImagesArray = [];
    let lightboxCurrentIndex = 0;

    function updateLightboxGalleryUI() {
        const img = document.getElementById('lightboxImage');
        const vid = document.getElementById('lightboxVideo');
        const prevBtn = document.getElementById('lightboxPrev');
        const nextBtn = document.getElementById('lightboxNext');
        
        if (lightboxImagesArray.length > 0) {
            const currentMedia = lightboxImagesArray[lightboxCurrentIndex] || '';
            const isVideo = /\.(mp4|webm|ogg|mov)$/i.test(currentMedia.split('?')[0]);

            if (isVideo) {
                if (img) img.classList.add('hidden');
                if (vid) {
                    vid.src = currentMedia;
                    vid.classList.remove('hidden');
                }
            } else {
                if (vid) {
                    vid.pause();
                    vid.src = '';
                    vid.classList.add('hidden');
                }
                if (img) {
                    img.src = currentMedia;
                    img.classList.remove('hidden');
                }
            }
        }

        if (lightboxImagesArray.length > 1) {
            prevBtn.classList.toggle('hidden', lightboxCurrentIndex === 0);
            nextBtn.classList.toggle('hidden', lightboxCurrentIndex === lightboxImagesArray.length - 1);
        } else {
            prevBtn.classList.add('hidden');
            nextBtn.classList.add('hidden');
        }
    }

    window.navigateLightbox = function(direction) {
        lightboxCurrentIndex += direction;
        if (lightboxCurrentIndex < 0) lightboxCurrentIndex = 0;
        if (lightboxCurrentIndex >= lightboxImagesArray.length) lightboxCurrentIndex = lightboxImagesArray.length - 1;
        updateLightboxGalleryUI();
    };

    function processSidebarContent(mainContent, sidebar, detailLink = null) {
        if (mainContent) {
            // Remove the sticky top header ("<- Story") and back navigation
            const topBar = mainContent.querySelector('.sticky.top-0');
            if (topBar) topBar.remove();
            
            mainContent.querySelectorAll('button').forEach(btn => {
                if (btn.innerText.includes('Back') || btn.getAttribute('onclick')?.includes('history.back')) {
                    const backWrapper = btn.closest('div.mb-8') || btn.closest('div');
                    if (backWrapper) backWrapper.remove();
                }
            });

            // Adjust main container for comfortable sidebar padding
            const container = mainContent.querySelector('.max-w-2xl') || mainContent.firstElementChild;
            if (container) {
                container.className = 'w-full p-4 sm:p-5 pb-16';
            }

            // Adjust Post Title for compact sidebar
            const titleEl = mainContent.querySelector('h1');
            if (titleEl) {
                titleEl.className = 'text-xl sm:text-2xl font-black text-on-surface tracking-tight mb-4 leading-snug';
            }

            // Adjust author meta margin
            const authorMeta = mainContent.querySelector('article > div.flex.items-center.justify-between');
            if (authorMeta) {
                authorMeta.classList.remove('mb-10');
                authorMeta.classList.add('mb-4');
            }
            
            // COMPLETELY remove the image grids and standalone cover images from the sidebar
            mainContent.querySelectorAll('div.grid').forEach(grid => {
                if (grid.querySelector('img')) grid.remove();
            });
            mainContent.querySelectorAll('img').forEach(im => {
                if (im.classList.contains('object-cover') && !im.classList.contains('rounded-full')) {
                    const wrapper = im.closest('div.mt-4.mb-10') || im.closest('div.mt-2.mb-3') || im.closest('div.mt-1.mb-2');
                    if (wrapper) wrapper.remove();
                    else im.remove();
                }
            });

            // 1. Update the Post Content Container:
            const contentContainer = mainContent.querySelector('.quill-content') || mainContent.querySelector('article > div.font-body-md');
            if (contentContainer) {
                contentContainer.id = 'lightbox-post-content';
                contentContainer.className = 'font-body-md text-on-surface text-[15px] sm:text-[16px] leading-[1.7] whitespace-pre-line mb-6 break-words quill-content';
            }

            // 2. Update the Action Bar (Like, Comment, Repost, Bookmark, Share):
            const actionBar = mainContent.querySelector('article .border-y') || mainContent.querySelector('.border-y.border-outline-variant\\/40');
            if (actionBar) {
                actionBar.className = 'flex items-center justify-between py-3 border-y border-outline-variant/40 mb-6 text-on-surface-variant';
                actionBar.querySelectorAll('button, a').forEach(btn => {
                    const icon = btn.querySelector('.material-symbols-outlined');
                    if (icon && !btn.querySelector('.w-8')) {
                        const wrapper = document.createElement('div');
                        wrapper.className = 'w-8 h-8 rounded-full hover:bg-surface-container flex items-center justify-center transition-colors';
                        icon.parentNode.insertBefore(wrapper, icon);
                        wrapper.appendChild(icon);
                    }
                    if (btn.querySelector('.material-symbols-outlined')?.textContent.trim() === 'chat_bubble') {
                        btn.onclick = () => {
                            const commentInput = sidebar.querySelector('#lightbox-comment-input') || sidebar.querySelector('textarea[name="comment"]') || sidebar.querySelector('textarea[name="content"]');
                            if (commentInput) commentInput.focus();
                        };
                    }
                });
            }

            // 3. Refine the Discussion Section:
            const discussionHeader = mainContent.querySelector('section h3');
            if (discussionHeader) {
                discussionHeader.className = 'font-title-md font-bold text-lg text-on-surface mb-4';
            }

            // Upgrade comment input box to textarea with sleek reply button
            const commentInput = mainContent.querySelector('#comment-input') || mainContent.querySelector('textarea[name="comment"]') || mainContent.querySelector('textarea[name="content"]') || mainContent.querySelector('input[name="content"]');
            if (commentInput) {
                let textarea = commentInput;
                if (commentInput.tagName === 'INPUT') {
                    textarea = document.createElement('textarea');
                    commentInput.parentNode.replaceChild(textarea, commentInput);
                }
                textarea.name = 'comment';
                textarea.id = 'lightbox-comment-input';
                textarea.rows = 2;
                textarea.className = 'w-full bg-surface-container-lowest border border-outline-variant/50 rounded-xl p-3 text-on-surface text-sm font-body-md focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all resize-none';
                textarea.placeholder = 'What are your thoughts?';

                const submitBtn = textarea.closest('form')?.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.textContent = 'Reply';
                    submitBtn.className = 'px-4 py-1.5 rounded-full bg-primary text-on-primary font-title-md text-xs sm:text-sm hover:opacity-90 transition-opacity font-medium shadow-sm';
                }
            }
            
            sidebar.innerHTML = mainContent.innerHTML;
            if (window.updateTimeAgo) window.updateTimeAgo();

            // Intercept comment form submission to prevent full-page reload
            const commentForm = sidebar.querySelector('form[action*="/post/comment/"]');
            if (commentForm) {
                commentForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const submitBtn = commentForm.querySelector('button[type="submit"]');
                    const originalBtnText = submitBtn ? submitBtn.textContent : '';
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.textContent = 'Posting...';
                    }

                    try {
                        const formData = new FormData(commentForm);
                        const res = await fetch(commentForm.action, {
                            method: 'POST',
                            body: formData,
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const data = await res.json().catch(() => ({}));
                        if (res.ok) {
                            if (window.showToast) {
                                showToast(data.message || 'Comment posted successfully!', 'success');
                            }
                            const input = commentForm.querySelector('#lightbox-comment-input') || commentForm.querySelector('textarea[name="comment"]');
                            if (input) input.value = '';

                            if (detailLink) {
                                try {
                                    const reloadRes = await fetch(detailLink);
                                    const reloadHtml = await reloadRes.text();
                                    const reloadDoc = new DOMParser().parseFromString(reloadHtml, 'text/html');
                                    const newMain = reloadDoc.querySelector('main');
                                    if (newMain) processSidebarContent(newMain, sidebar, detailLink);
                                } catch (_) {}
                            }
                        } else {
                            if (window.showToast) {
                                showToast(data.message || 'Failed to post comment.', 'error');
                            }
                        }
                    } catch (err) {
                        if (window.showToast) {
                            showToast('Error submitting comment.', 'error');
                        }
                    } finally {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.textContent = originalBtnText;
                        }
                    }
                });
            }
        } else {
            sidebar.innerHTML = '<div class="p-8 text-center text-error">Failed to load content.</div>';
        }
    }

    async function openLightboxGallery(imagesArray, startIndex, detailLink) {
        lightboxImagesArray = imagesArray;
        lightboxCurrentIndex = startIndex;
        
        const lightbox = document.getElementById('imageLightbox');
        const img = document.getElementById('lightboxImage');
        const sidebar = document.getElementById('lightboxSidebarContent');
        const sidebarContainer = document.getElementById('lightboxSidebar');
        
        if (lightbox && img) {
            updateLightboxGalleryUI();
            
            lightbox.classList.remove('hidden');
            void lightbox.offsetWidth; // Trigger reflow
            lightbox.classList.remove('opacity-0');
            img.classList.remove('scale-95');
            img.classList.add('scale-100');
            document.body.style.overflow = 'hidden'; // Prevent scrolling
            
            if (sidebar) {
                sidebarContainer.classList.remove('hidden');
                
                if (detailLink) {
                    // Instant load if already on Detail Page
                    if ((detailLink && window.location.href.includes(detailLink)) || window.location.pathname.includes('/post/detail/') || /\/[^\/]+\/(note|story)\/\d{12}/.test(window.location.pathname)) {
                        const mainDOM = document.querySelector('main');
                        if (mainDOM) {
                            const clonedMain = mainDOM.cloneNode(true);
                            processSidebarContent(clonedMain, sidebar, detailLink);
                            return;
                        }
                    }
                    
                    // Fetch via AJAX for Feed
                    sidebar.innerHTML = `
                        <div class="flex-1 flex flex-col items-center justify-center text-on-surface-variant h-full py-20">
                            <span class="material-symbols-outlined animate-spin text-4xl mb-4">progress_activity</span>
                            <p class="font-title-md text-sm">Loading discussion...</p>
                        </div>
                    `;
                    
                    try {
                        const response = await fetch(detailLink);
                        const html = await response.text();
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const mainContent = doc.querySelector('main');
                        processSidebarContent(mainContent, sidebar, detailLink);
                    } catch (err) {
                        sidebar.innerHTML = '<div class="p-8 text-center text-error">Network error. Failed to load discussion.</div>';
                    }
                } else {
                    sidebarContainer.classList.add('hidden');
                }
            }
        }
    }

    function closeLightbox() {
        const lightbox = document.getElementById('imageLightbox');
        const img = document.getElementById('lightboxImage');
        const vid = document.getElementById('lightboxVideo');
        if (lightbox && !lightbox.classList.contains('hidden')) {
            lightbox.classList.add('opacity-0');
            if (img) {
                img.classList.remove('scale-100');
                img.classList.add('scale-95');
            }
            if (vid) {
                vid.pause();
                vid.src = '';
                vid.classList.add('hidden');
            }
            setTimeout(() => {
                lightbox.classList.add('hidden');
                if (img) img.src = '';
                lightboxImagesArray = [];
                document.body.style.overflow = '';
            }, 300);
        }
    }

    document.addEventListener('keydown', function(e) {
        const lightbox = document.getElementById('imageLightbox');
        if (lightbox && !lightbox.classList.contains('hidden')) {
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowLeft') navigateLightbox(-1);
            if (e.key === 'ArrowRight') navigateLightbox(1);
        }
    });

    // Intercept Clicks ONLY on Post Media Images (strictly ignore avatars and profile pictures)
    document.addEventListener('click', function(e) {
        const target = e.target;
        if (!target || target.tagName !== 'IMG') return;

        // 1. Explicitly ignore avatars and profile elements
        if (
            target.classList.contains('avatar-img') ||
            target.classList.contains('rounded-full') ||
            target.closest('.rounded-full') ||
            target.closest('.profile-hover-trigger') ||
            target.closest('a[title="View Profile"]') ||
            target.closest('.avatar-container') ||
            target.closest('#noteImagePreviewContainer')
        ) {
            return; // Allow standard link navigation to user profile
        }

        // 2. Only proceed for actual post media images or embedded quill images
        const isPostMedia = target.classList.contains('post-media-image');
        const quillContent = target.closest('.quill-content');

        if (isPostMedia || quillContent) {
            e.preventDefault();
            e.stopPropagation();

            let imagesToLoad = [];
            let startIdx = 0;

            // Check if part of a grid
            const gridContainer = target.closest('div.grid');
            if (gridContainer) {
                const gridImgs = Array.from(gridContainer.querySelectorAll('img.post-media-image, img:not(.avatar-img):not(.rounded-full)'));
                imagesToLoad = gridImgs.map(im => im.src);
                startIdx = gridImgs.indexOf(target);
            } else {
                imagesToLoad = [target.src];
            }

            let detailLink = null;
            const article = target.closest('article');
            if (article) {
                const linkTag = article.querySelector('a[href*="/note/"], a[href*="/story/"], a[href*="/post/detail/"]');
                if (linkTag) detailLink = linkTag.href;
                else if (window.location.href.includes('/post/detail/') || /\/[^\/]+\/(note|story)\/\d{12}/.test(window.location.pathname)) detailLink = window.location.href;
            } else if (quillContent) {
                if (window.location.href.includes('/post/detail/') || /\/[^\/]+\/(note|story)\/\d{12}/.test(window.location.pathname)) detailLink = window.location.href;
            }

            openLightboxGallery(imagesToLoad, Math.max(0, startIdx), detailLink);
        }
    });

    // Backward compatibility bridge for inline onclick handlers
    window.openLightbox = function(src, detailLink) {
        let imagesToLoad = [src];
        let startIdx = 0;
        const clickedImg = Array.from(document.querySelectorAll('img')).find(im => im.src === src);
        if (clickedImg) {
            const gridContainer = clickedImg.closest('div.grid');
            if (gridContainer) {
                const gridImgs = Array.from(gridContainer.querySelectorAll('img'));
                imagesToLoad = gridImgs.map(im => im.src);
                startIdx = gridImgs.indexOf(clickedImg);
                if (startIdx === -1) startIdx = 0;
            }
        }
        openLightboxGallery(imagesToLoad, startIdx, detailLink);
    };
    </script>

    <!-- Global Keyboard Shortcuts -->
    <script>
    document.addEventListener('keydown', function(e) {
        // Check if the user is typing inside a textarea
        if (e.target.tagName === 'TEXTAREA') {
            // Check for Enter key + either Ctrl (Windows/Linux) or Meta (Command on Mac)
            if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
                e.preventDefault(); // Prevent any accidental newline
                
                const form = e.target.closest('form');
                if (form) {
                    // Find the primary submit button (either the "Publish" button with value="publish" or a generic submit button)
                    const submitBtn = form.querySelector('button[value="publish"]') || form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.click(); // Trigger click to ensure the button's name/value is passed securely
                    }
                }
            }
        }
    });
    </script>

    <!-- Global Relative Time Updater -->
    <script>
    function updateTimeAgo() {
        const elements = document.querySelectorAll('.timeago');
        const now = new Date();
        elements.forEach(el => {
            const date = new Date(el.getAttribute('datetime'));
            const diffSeconds = Math.floor((now - date) / 1000);

            if (diffSeconds < 1) {
                el.textContent = 'now';
            } else if (diffSeconds < 60) {
                el.textContent = diffSeconds + 's';
            } else if (diffSeconds < 3600) {
                el.textContent = Math.floor(diffSeconds / 60) + 'm';
            } else if (diffSeconds < 86400) {
                el.textContent = Math.floor(diffSeconds / 3600) + 'h';
            } else {
                const options = { month: 'short', day: 'numeric' };
                if (date.getFullYear() !== now.getFullYear()) options.year = 'numeric';
                el.textContent = date.toLocaleDateString('en-US', options);
            }
        });
    }
    // Run immediately, then update continuously every minute
    updateTimeAgo();
    setInterval(updateTimeAgo, 60000);
    </script>

    <!-- Fallback Toast Definition if not already loaded from header -->
    <script>
    if (typeof window.showToast !== 'function') {
        window.showToast = function(message, type = 'success') {
            if (!message) return;
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.className = 'fixed bottom-5 left-1/2 -translate-x-1/2 sm:left-auto sm:right-6 sm:translate-x-0 z-[9999] flex flex-col gap-2.5 pointer-events-none max-w-[90vw] sm:max-w-md w-max';
                document.body.appendChild(container);
            }
            const toast = document.createElement('div');
            const isError = type === 'error';
            const isInfo = type === 'info';
            const isWarning = type === 'warning';
            let iconName = isError ? 'error' : (isInfo ? 'info' : (isWarning ? 'warning' : 'check_circle'));
            let iconColor = isError ? 'text-error' : (isInfo ? 'text-sky-400' : (isWarning ? 'text-amber-400' : 'text-primary'));
            let bgClasses = isError ? 'border-error/40' : (isInfo ? 'border-sky-500/30' : (isWarning ? 'border-amber-500/30' : 'border-outline-variant/40'));

            toast.className = `pointer-events-auto flex items-center gap-2.5 px-4 py-3 rounded-2xl shadow-2xl backdrop-blur-xl border font-title-md text-sm transition-all duration-300 ease-out transform translate-y-4 opacity-0 scale-95 cursor-pointer select-none bg-surface-container-high/95 text-on-surface ${bgClasses}`;
            toast.innerHTML = `
                <span class="material-symbols-outlined text-[20px] shrink-0 ${iconColor}" style="font-variation-settings: 'FILL' 1;">${iconName}</span>
                <span class="leading-snug break-words">${message}</span>
                <button type="button" class="ml-2 -mr-1 text-on-surface-variant hover:text-on-surface transition-colors shrink-0" aria-label="Dismiss">
                    <span class="material-symbols-outlined text-base">close</span>
                </button>
            `;
            container.appendChild(toast);
            requestAnimationFrame(() => {
                toast.classList.remove('translate-y-4', 'opacity-0', 'scale-95');
                toast.classList.add('translate-y-0', 'opacity-100', 'scale-100');
            });
            let dismissed = false;
            const dismiss = () => {
                if (dismissed) return;
                dismissed = true;
                toast.classList.remove('translate-y-0', 'opacity-100', 'scale-100');
                toast.classList.add('translate-y-2', 'opacity-0', 'scale-95');
                setTimeout(() => toast.remove(), 250);
            };
            toast.addEventListener('click', dismiss);
            setTimeout(dismiss, 3000);
        };
        window.showToast = window.showToast;
    }
    </script>

    <!-- ================= Twitter/X-Style Profile Hover Card Module ================= -->
    <script>
    (function() {
        'use strict';

        const HOVER_DELAY = 400; // ms debounce before displaying card
        const HIDE_DELAY = 300;  // ms grace period before hiding card

        let hoverTimer = null;
        let hideTimer = null;
        let activeTrigger = null;
        let currentUsername = null;
        const cache = new Map(); // username -> HTML string
        const userToUsername = new Map(); // userId -> username

        // Global cache invalidation (e.g. after following/unfollowing)
        window.profileHoverCardInvalidate = function(identifier) {
            if (!identifier) return;
            if (cache.has(identifier)) {
                cache.delete(identifier);
            }
            if (userToUsername.has(String(identifier))) {
                const uname = userToUsername.get(String(identifier));
                cache.delete(uname);
            }
        };

        function getPopover() {
            let pop = document.getElementById('profile-hover-card-popover');
            if (!pop) {
                pop = document.createElement('div');
                pop.id = 'profile-hover-card-popover';
                pop.className = 'fixed z-[9999] pointer-events-auto transition-all duration-200 ease-out opacity-0 scale-95 pointer-events-none';
                document.body.appendChild(pop);

                // Keep card open when cursor moves into the card itself
                pop.addEventListener('mouseenter', () => {
                    clearTimeout(hideTimer);
                });

                pop.addEventListener('mouseleave', () => {
                    startHideTimer();
                });
            }
            return pop;
        }

        function positionPopover(trigger, popover) {
            if (!trigger || !popover) return;
            const triggerRect = trigger.getBoundingClientRect();
            const popoverRect = popover.getBoundingClientRect();

            const cardWidth = popoverRect.width || 288;
            const cardHeight = popoverRect.height || 260;
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;

            // Vertical: check available space below vs above
            const spaceBelow = viewportHeight - triggerRect.bottom;
            const spaceAbove = triggerRect.top;

            let top;
            if (spaceBelow < (cardHeight + 16) && spaceAbove > (cardHeight + 16)) {
                // Place above trigger
                top = triggerRect.top - cardHeight - 8;
            } else {
                // Place below trigger
                top = triggerRect.bottom + 8;
            }

            // Horizontal: align with trigger left, constrained within viewport margins
            let left = triggerRect.left;
            if (left + cardWidth > viewportWidth - 16) {
                left = viewportWidth - cardWidth - 16;
            }
            if (left < 16) {
                left = 16;
            }

            popover.style.top = `${Math.round(top)}px`;
            popover.style.left = `${Math.round(left)}px`;
        }

        async function showHoverCard(trigger) {
            const rawUsername = trigger.dataset.username || trigger.getAttribute('data-hovercard-user');
            if (!rawUsername) return;
            const username = rawUsername.replace(/^@/, '').trim();
            if (!username) return;

            activeTrigger = trigger;
            currentUsername = username;
            const popover = getPopover();

            // Render from cache immediately if available
            if (cache.has(username)) {
                popover.innerHTML = cache.get(username);
                renderCard(trigger, popover);
                return;
            }

            // Skeleton loading state
            popover.innerHTML = `
                <div class="hover-card-popover w-72 rounded-2xl shadow-2xl bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/30 dark:border-slate-700/80 p-5 flex items-center justify-center min-h-[140px]">
                    <div class="w-6 h-6 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
                </div>
            `;
            renderCard(trigger, popover);

            try {
                const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '';
                const response = await fetch(`${baseUrl}/profile/hoverCard/${encodeURIComponent(username)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) throw new Error('Failed to load hover card');

                const html = await response.text();
                cache.set(username, html);

                // If user is still hovering over this trigger
                if (activeTrigger === trigger && currentUsername === username) {
                    popover.innerHTML = html;
                    
                    // Track userId -> username for cache invalidation
                    const btn = popover.querySelector('.btn-follow');
                    if (btn && btn.dataset.userId) {
                        userToUsername.set(String(btn.dataset.userId), username);
                    }

                    positionPopover(trigger, popover);
                }
            } catch (err) {
                if (activeTrigger === trigger) {
                    hideHoverCard();
                }
            }
        }

        function renderCard(trigger, popover) {
            popover.classList.remove('pointer-events-none');
            popover.style.visibility = 'hidden';
            popover.style.display = 'block';

            positionPopover(trigger, popover);

            popover.style.visibility = 'visible';
            requestAnimationFrame(() => {
                popover.classList.remove('opacity-0', 'scale-95');
                popover.classList.add('opacity-100', 'scale-100');
            });
        }

        function startHideTimer() {
            clearTimeout(hideTimer);
            hideTimer = setTimeout(() => {
                hideHoverCard();
            }, HIDE_DELAY);
        }

        function hideHoverCard() {
            clearTimeout(hoverTimer);
            activeTrigger = null;
            currentUsername = null;
            const popover = document.getElementById('profile-hover-card-popover');
            if (popover) {
                popover.classList.remove('opacity-100', 'scale-100');
                popover.classList.add('opacity-0', 'scale-95', 'pointer-events-none');
            }
        }

        // Global Event Delegation for hover triggers
        document.addEventListener('mouseover', (e) => {
            const trigger = e.target.closest('.profile-hover-trigger, [data-hovercard-user]');
            if (!trigger) return;

            // Ignore hovers originating inside the card itself
            if (trigger.closest('#profile-hover-card-popover')) return;

            clearTimeout(hideTimer);

            // If already hovering the same trigger and card is visible
            if (activeTrigger === trigger && document.getElementById('profile-hover-card-popover')?.classList.contains('opacity-100')) {
                return;
            }

            clearTimeout(hoverTimer);
            hoverTimer = setTimeout(() => {
                showHoverCard(trigger);
            }, HOVER_DELAY);
        });

        document.addEventListener('mouseout', (e) => {
            const trigger = e.target.closest('.profile-hover-trigger, [data-hovercard-user]');
            if (!trigger) return;

            // Ignore when cursor moves to child elements of the trigger
            if (e.relatedTarget && trigger.contains(e.relatedTarget)) return;

            // Ignore when cursor moves into the popover card
            const popover = document.getElementById('profile-hover-card-popover');
            if (popover && e.relatedTarget && popover.contains(e.relatedTarget)) return;

            clearTimeout(hoverTimer);
            startHideTimer();
        });

        // Reposition on window scroll/resize if active
        window.addEventListener('scroll', () => {
            if (activeTrigger) {
                const popover = document.getElementById('profile-hover-card-popover');
                if (popover && popover.classList.contains('opacity-100')) {
                    const rect = activeTrigger.getBoundingClientRect();
                    // If scrolled out of viewport, dismiss
                    if (rect.bottom < 0 || rect.top > window.innerHeight) {
                        hideHoverCard();
                    } else {
                        positionPopover(activeTrigger, popover);
                    }
                }
            }
        }, { passive: true });

        window.addEventListener('resize', () => {
            if (activeTrigger) {
                const popover = document.getElementById('profile-hover-card-popover');
                if (popover && popover.classList.contains('opacity-100')) {
                    positionPopover(activeTrigger, popover);
                }
            }
        }, { passive: true });

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') hideHoverCard();
        });
    })();
    </script>

    <!-- Global Reusable Confirmation Dialog Modal (Replaces browser confirm() with sleek Tailwind UI) -->
    <div id="global-confirm-modal" class="fixed inset-0 z-[250] bg-black/60 backdrop-blur-sm hidden items-center justify-center p-4 transition-opacity duration-200 opacity-0 pointer-events-none" aria-modal="true" role="dialog">
        <div id="global-confirm-card" class="bg-surface-container-low border border-outline-variant/30 rounded-2xl w-full max-w-sm sm:max-w-md p-6 flex flex-col gap-4 shadow-2xl transform scale-95 transition-all duration-200">
            <div class="flex items-start gap-4">
                <div id="global-confirm-icon-container" class="w-12 h-12 rounded-full flex items-center justify-center shrink-0 bg-error/10 text-error">
                    <span id="global-confirm-icon" class="material-symbols-outlined text-2xl">delete</span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 id="global-confirm-title" class="font-title-md text-lg font-bold text-on-surface leading-tight">Confirm Action</h3>
                    <p id="global-confirm-message" class="font-body-md text-sm text-on-surface-variant mt-1.5 leading-relaxed break-words">Are you sure you want to proceed?</p>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 mt-2">
                <button type="button" id="global-confirm-cancel-btn" class="btn-secondary px-5 py-2 text-sm">
                    Cancel
                </button>
                <button type="button" id="global-confirm-action-btn" class="btn-destructive px-5 py-2 text-sm">
                    Delete
                </button>
            </div>
        </div>
    </div>

    <!-- Global Confirmation Dialog Module & Form Delegation -->
    <script>
    (function() {
        let confirmCleanup = null;

        window.showConfirmDialog = function(options = {}) {
            return new Promise((resolve) => {
                const modal = document.getElementById('global-confirm-modal');
                const card = document.getElementById('global-confirm-card');
                const titleEl = document.getElementById('global-confirm-title');
                const msgEl = document.getElementById('global-confirm-message');
                const iconContainer = document.getElementById('global-confirm-icon-container');
                const iconEl = document.getElementById('global-confirm-icon');
                const cancelBtn = document.getElementById('global-confirm-cancel-btn');
                const actionBtn = document.getElementById('global-confirm-action-btn');

                if (!modal) {
                    const fallback = window.confirm(options.message || 'Are you sure?');
                    if (fallback && typeof options.onConfirm === 'function') options.onConfirm();
                    if (!fallback && typeof options.onCancel === 'function') options.onCancel();
                    resolve(fallback);
                    return;
                }

                // Close any open dropdown menus
                document.querySelectorAll('.dropdown-container > div[id^="menu-"]').forEach(el => el.classList.add('hidden'));

                const title = options.title || 'Are you sure?';
                const message = options.message || 'This action cannot be undone.';
                const confirmText = options.confirmText || 'Confirm';
                const cancelText = options.cancelText || 'Cancel';
                const isDanger = options.isDanger !== false;

                titleEl.textContent = title;
                msgEl.textContent = message;
                cancelBtn.textContent = cancelText;
                actionBtn.textContent = confirmText;

                if (isDanger) {
                    iconContainer.className = 'w-12 h-12 rounded-full flex items-center justify-center shrink-0 bg-error/10 text-error';
                    iconEl.textContent = 'delete';
                    actionBtn.className = 'btn-destructive px-5 py-2 text-sm';
                } else {
                    iconContainer.className = 'w-12 h-12 rounded-full flex items-center justify-center shrink-0 bg-primary/10 text-primary';
                    iconEl.textContent = 'help';
                    actionBtn.className = 'btn-primary px-5 py-2 text-sm';
                }

                if (confirmCleanup) {
                    confirmCleanup();
                }

                const closeDialog = (confirmed) => {
                    modal.classList.add('opacity-0', 'pointer-events-none');
                    card.classList.remove('scale-100');
                    card.classList.add('scale-95');
                    setTimeout(() => {
                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                    }, 200);

                    if (confirmCleanup) {
                        confirmCleanup();
                        confirmCleanup = null;
                    }

                    if (confirmed) {
                        if (typeof options.onConfirm === 'function') options.onConfirm();
                        resolve(true);
                    } else {
                        if (typeof options.onCancel === 'function') options.onCancel();
                        resolve(false);
                    }
                };

                const onCancelClick = (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    closeDialog(false);
                };

                const onConfirmClick = (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    closeDialog(true);
                };

                const onKeydown = (e) => {
                    if (e.key === 'Escape') {
                        closeDialog(false);
                    }
                };

                const onBackdropClick = (e) => {
                    if (e.target === modal) {
                        closeDialog(false);
                    }
                };

                cancelBtn.addEventListener('click', onCancelClick);
                actionBtn.addEventListener('click', onConfirmClick);
                document.addEventListener('keydown', onKeydown);
                modal.addEventListener('click', onBackdropClick);

                confirmCleanup = () => {
                    cancelBtn.removeEventListener('click', onCancelClick);
                    actionBtn.removeEventListener('click', onConfirmClick);
                    document.removeEventListener('keydown', onKeydown);
                    modal.removeEventListener('click', onBackdropClick);
                };

                modal.classList.remove('hidden');
                modal.classList.add('flex');
                void modal.offsetWidth;
                modal.classList.remove('opacity-0', 'pointer-events-none');
                card.classList.remove('scale-95');
                card.classList.add('scale-100');
                actionBtn.focus();
            });
        };

        // Form Submit Interceptor for declarative data-confirm attributes
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (!form || !form.hasAttribute('data-confirm')) return;

            if (form._isConfirmedSubmission) {
                delete form._isConfirmedSubmission;
                return; // Let native submit event continue
            }

            e.preventDefault();
            e.stopPropagation();

            const message = form.getAttribute('data-confirm');
            const title = form.getAttribute('data-confirm-title') || 'Confirm Action';
            const confirmText = form.getAttribute('data-confirm-btn') || 'Delete';
            const cancelText = form.getAttribute('data-confirm-cancel') || 'Cancel';
            const isDanger = form.getAttribute('data-confirm-danger') !== 'false';
            const submitter = e.submitter;

            window.showConfirmDialog({
                title: title,
                message: message,
                confirmText: confirmText,
                cancelText: cancelText,
                isDanger: isDanger,
                onConfirm: () => {
                    form._isConfirmedSubmission = true;
                    if (typeof form.requestSubmit === 'function') {
                        if (submitter) {
                            form.requestSubmit(submitter);
                        } else {
                            form.requestSubmit();
                        }
                    } else {
                        form.submit();
                    }
                }
            });
        }, true);
    })();
    </script>
</body>
</html>