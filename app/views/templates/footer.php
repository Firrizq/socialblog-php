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
                <div class="flex flex-col gap-space-lg">
                    <form action="<?= BASEURL ?>/explore" method="GET" class="relative flex items-center w-full">
                        <span class="material-symbols-outlined absolute left-space-md text-outline text-lg pointer-events-none">search</span>
                        <input name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" class="w-full pl-10 pr-space-md py-space-xs bg-surface-container-lowest border border-outline-variant/40 rounded-full font-body-md text-on-surface placeholder:text-outline focus:outline-none focus:border-primary-container focus:ring-1 focus:ring-primary-container" placeholder="Search Blogggle..." type="search"/>
                    </form>
                    
                    <div class="flex flex-col gap-space-md">
                        <span class="font-title-md text-on-surface">Popular Stories</span>
                        <div class="flex flex-col gap-2">
                            <?php foreach($popularPosts as $popPost): ?>
                                <?php
                                    $popType = strtolower($popPost['post_type'] ?? $popPost['type'] ?? 'story');
                                    $popUid = !empty($popPost['uid']) ? $popPost['uid'] : $popPost['id'];
                                    $popUrl = BASEURL . '/' . urlencode($popPost['username']) . '/' . $popType . '/' . $popUid;
                                ?>
                                <a href="<?= $popUrl ?>" class="flex flex-col gap-1 p-3 rounded-xl bg-surface-container hover:bg-surface-container-high transition-colors group">
                                    <span class="font-title-md text-sm text-on-surface group-hover:text-primary transition-colors line-clamp-2"><?= htmlspecialchars($popPost['title']) ?></span>
                                    <span class="font-caption text-xs text-on-surface-variant">by @<?= htmlspecialchars($popPost['username']) ?> · <?= $popPost['read_time_minutes'] ?> min read</span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="flex flex-col gap-space-md">
                        <span class="font-title-md text-on-surface">Popular Tags</span>
                        <div class="flex flex-wrap gap-2">
                            <?php 
                            $popularTags = ['Technology', 'Life', 'Design', 'Programming', 'Writing'];
                            foreach($popularTags as $popTag): 
                            ?>
                                <a href="<?= BASEURL ?>/explore/tag/<?= urlencode($popTag) ?>" class="px-3 py-1.5 rounded-full bg-surface-container hover:bg-surface-container-high border border-outline-variant/30 text-on-surface-variant hover:text-primary hover:border-primary/50 text-xs font-semibold transition-all">
                                    #<?= htmlspecialchars($popTag) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="flex flex-col gap-space-md">
                        <span class="font-title-md text-on-surface">Suggested Writers</span>
                        <div class="flex flex-col gap-space-md">
                            <?php if (!empty($suggestedWriters)): ?>
                                <?php foreach($suggestedWriters as $writer): ?>
                                    <?php 
                                        $isWriterFollowing = false;
                                        if ($currentUserId > 0) {
                                            $isWriterFollowing = $interactionModel->isFollowing((int)$currentUserId, (int)$writer['id']);
                                        }
                                    ?>
                                    <div class="flex items-center justify-between">
                                        <a href="<?= BASEURL ?>/<?= urlencode($writer['username']) ?>" class="flex items-center gap-space-sm min-w-0 group hover:opacity-80 transition-opacity">
                                            <div class="w-8 h-8 rounded-full bg-surface-container-high flex items-center justify-center font-bold text-primary shrink-0 overflow-hidden">
                                                <?php if (!empty($writer['profile_picture'])): ?>
                                                    <img src="<?= BASEURL ?><?= htmlspecialchars($writer['profile_picture']) ?>" alt="avatar" class="w-full h-full object-cover">
                                                <?php else: ?>
                                                    <?= strtoupper(substr($writer['username'], 0, 1)) ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex flex-col min-w-0">
                                                <p class="font-title-md text-sm text-on-surface leading-tight truncate group-hover:text-primary transition-colors"><?= htmlspecialchars($writer['username']) ?></p>
                                                <p class="font-caption text-xs text-on-surface-variant truncate max-w-[120px]"><?= htmlspecialchars($writer['bio'] ?: 'Community Writer') ?></p>
                                            </div>
                                        </a>
                                        <button type="button" 
                                                class="follow-btn btn-follow px-space-sm py-space-xs rounded-full border font-caption text-xs transition-colors shrink-0 <?= $isWriterFollowing ? 'border-outline-variant bg-surface text-on-surface hover:border-error hover:text-error hover:bg-error-container/20' : 'bg-surface-container border-outline-variant text-on-surface hover:border-primary hover:text-primary' ?>" 
                                                data-user-id="<?= (int)$writer['id'] ?>" 
                                                data-id="<?= (int)$writer['id'] ?>" 
                                                data-scope="sidebar">
                                            <?= $isWriterFollowing ? 'Following' : 'Follow' ?>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="font-caption text-xs text-on-surface-variant">No suggestions available.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </aside>
            <!-- Akhir dari max-w-7xl -->
        </div>
    <!-- Akhir dari md:pl-60 -->
    </div>

    <!-- Quill.js JS Script diletakkan di footer agar editor bisa jalan -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

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
                                followBtn.className = 'follow-btn btn-follow px-5 py-1.5 rounded-full border border-outline-variant/50 font-title-md text-sm font-bold text-on-surface hover:border-error hover:text-error hover:bg-error-container/20 transition-all shadow-sm relative z-10';
                            } else {
                                followBtn.textContent = 'Follow';
                                followBtn.className = 'follow-btn btn-follow px-6 py-1.5 rounded-full bg-on-surface text-surface hover:opacity-80 font-title-md text-sm font-bold transition-all shadow-sm relative z-10';
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
                                followBtn.className = 'follow-btn btn-follow px-4 py-1.5 rounded-full border border-outline-variant/50 bg-surface text-on-surface hover:border-error hover:text-error hover:bg-error-container/20 font-caption text-xs font-bold transition-all shadow-sm shrink-0';
                            } else {
                                followBtn.textContent = 'Follow';
                                followBtn.className = 'follow-btn btn-follow px-4 py-1.5 rounded-full bg-on-surface text-surface hover:opacity-85 font-caption text-xs font-bold transition-all shadow-sm shrink-0';
                            }

                            const cardFollowerCount = followBtn.closest('.hover-card-popover, #profile-hover-card-popover')?.querySelector('.card-follower-count');
                            if (cardFollowerCount && typeof data.follower_count !== 'undefined') {
                                cardFollowerCount.textContent = Number(data.follower_count).toLocaleString();
                            }
                        } else {
                            // Scoped to the specific clicked sidebar button
                            if (isFollowed) {
                                followBtn.textContent = 'Following';
                                followBtn.className = 'follow-btn btn-follow px-space-sm py-space-xs rounded-full border border-outline-variant bg-surface text-on-surface hover:border-error hover:text-error hover:bg-error-container/20 font-caption text-xs transition-colors shrink-0';
                            } else {
                                followBtn.textContent = 'Follow';
                                followBtn.className = 'follow-btn btn-follow px-space-sm py-space-xs rounded-full bg-surface-container border border-outline-variant text-on-surface hover:border-primary hover:text-primary font-caption text-xs transition-colors shrink-0';
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
        article img:not(.rounded-full), .quill-content img { cursor: pointer; transition: opacity 0.2s; }
        article img:not(.rounded-full):hover, .quill-content img:hover { opacity: 0.85; }
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
        const prevBtn = document.getElementById('lightboxPrev');
        const nextBtn = document.getElementById('lightboxNext');
        
        if (lightboxImagesArray.length > 0) {
            img.src = lightboxImagesArray[lightboxCurrentIndex];
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

    function processSidebarContent(mainContent, sidebar) {
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
                            const commentInput = sidebar.querySelector('#lightbox-comment-input') || sidebar.querySelector('textarea[name="content"]');
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
            const commentInput = mainContent.querySelector('#comment-input') || mainContent.querySelector('textarea[name="content"]') || mainContent.querySelector('input[name="content"]');
            if (commentInput) {
                let textarea = commentInput;
                if (commentInput.tagName === 'INPUT') {
                    textarea = document.createElement('textarea');
                    textarea.name = 'content';
                    textarea.required = true;
                    commentInput.parentNode.replaceChild(textarea, commentInput);
                }
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
                            processSidebarContent(clonedMain, sidebar);
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
                        processSidebarContent(mainContent, sidebar);
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
        if (lightbox && !lightbox.classList.contains('hidden')) {
            lightbox.classList.add('opacity-0');
            img.classList.remove('scale-100');
            img.classList.add('scale-95');
            setTimeout(() => {
                lightbox.classList.add('hidden');
                img.src = '';
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

    // Intercept Clicks on Post Images
    document.addEventListener('click', function(e) {
        if (e.target.tagName === 'IMG') {
            if (e.target.classList.contains('rounded-full') || e.target.closest('#noteImagePreviewContainer')) return;
            
            const article = e.target.closest('article');
            const quillContent = e.target.closest('.quill-content');
            
            if (article || quillContent) {
                e.preventDefault();
                e.stopPropagation();
                
                let imagesToLoad = [];
                let startIdx = 0;
                
                // Check if part of a grid
                const gridContainer = e.target.closest('div.grid');
                if (gridContainer) {
                    const gridImgs = Array.from(gridContainer.querySelectorAll('img'));
                    imagesToLoad = gridImgs.map(im => im.src);
                    startIdx = gridImgs.indexOf(e.target);
                } else {
                    imagesToLoad = [e.target.src];
                }
                
                let detailLink = null;
                if (article) {
                    const linkTag = article.querySelector('a[href*="/note/"], a[href*="/story/"], a[href*="/post/detail/"]');
                    if (linkTag) detailLink = linkTag.href;
                    else if (window.location.href.includes('/post/detail/') || /\/[^\/]+\/(note|story)\/\d{12}/.test(window.location.pathname)) detailLink = window.location.href;
                } else if (quillContent) {
                    if (window.location.href.includes('/post/detail/') || /\/[^\/]+\/(note|story)\/\d{12}/.test(window.location.pathname)) detailLink = window.location.href;
                }
                
                openLightboxGallery(imagesToLoad, startIdx, detailLink);
            }
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
</body>
</html>