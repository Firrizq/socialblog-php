</main>

            <!-- TAHAP 3: Right Sidebar -->
            <aside class="hidden xl:block w-80 sticky top-16 h-[calc(100vh-4rem)] p-gutter border-l border-outline-variant/30 overflow-y-auto">
                <?php
                require_once __DIR__ . '/../../models/User.php';
                require_once __DIR__ . '/../../models/Post_model.php';
                $currentUserId = $_SESSION['user_id'] ?? 0;
                $suggestedWriters = (new User())->getSuggestedWriters((int)$currentUserId);
                $popularPosts = array_slice((new Post_model())->getPopularPosts(), 0, 4);
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
                                <a href="<?= BASEURL ?>/post/detail/<?= $popPost['id'] ?>" class="flex flex-col gap-1 p-3 rounded-xl bg-surface-container hover:bg-surface-container-high transition-colors group">
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
                            <?php foreach($suggestedWriters as $writer): ?>
                                <div class="flex items-center justify-between">
                                    <a href="<?= BASEURL ?>/profile/user/<?= urlencode($writer['username']) ?>" class="flex items-center gap-space-sm min-w-0 group hover:opacity-80 transition-opacity">
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
                                    <button class="btn-follow px-space-sm py-space-xs rounded-full bg-surface-container border border-outline-variant text-on-surface hover:border-primary hover:text-primary font-caption text-xs transition-colors shrink-0" data-id="<?= $writer['id'] ?>">Follow</button>
                                </div>
                            <?php endforeach; ?>
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
                        const icon = bookmarkBtn.querySelector('.material-symbols-outlined');

                        if (data.status === 'bookmarked') {
                            bookmarkBtn.classList.add('text-primary');
                            bookmarkBtn.classList.remove('text-on-surface-variant');
                            if (icon) {
                                icon.style.fontVariationSettings = "'FILL' 1";
                            }
                        } else {
                            bookmarkBtn.classList.remove('text-primary');
                            bookmarkBtn.classList.add('text-on-surface-variant');
                            if (icon) {
                                icon.style.fontVariationSettings = "'FILL' 0";
                            }
                        }

                        // Sync any duplicate bookmark buttons
                        document.querySelectorAll(`.btn-bookmark[data-id="${postId}"]`).forEach(btn => {
                            if (btn !== bookmarkBtn) {
                                const otherIcon = btn.querySelector('.material-symbols-outlined');
                                if (data.status === 'bookmarked') {
                                    btn.classList.add('text-primary');
                                    btn.classList.remove('text-on-surface-variant');
                                    if (otherIcon) otherIcon.style.fontVariationSettings = "'FILL' 1";
                                } else {
                                    btn.classList.remove('text-primary');
                                    btn.classList.add('text-on-surface-variant');
                                    if (otherIcon) otherIcon.style.fontVariationSettings = "'FILL' 0";
                                }
                            }
                        });
                    }
                } catch (err) {
                    console.error('Bookmark error:', err);
                } finally {
                    bookmarkBtn.disabled = false;
                }
                return;
            }

            // 3. Handle Follow (.btn-follow)
            const followBtn = e.target.closest('.btn-follow');
            if (followBtn) {
                e.preventDefault();
                e.stopPropagation();

                const userId = followBtn.dataset.id;
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

                        const applyFollowStyle = (btn, state) => {
                            if (state) {
                                btn.textContent = 'Following';
                                btn.className = 'btn-follow px-5 py-1.5 rounded-full border border-outline-variant font-title-md text-sm font-semibold text-on-surface hover:border-error hover:text-error hover:bg-error-container/20 transition-all';
                            } else {
                                btn.textContent = 'Follow';
                                btn.className = 'btn-follow px-6 py-1.5 rounded-full bg-primary-container text-on-primary-container hover:bg-primary font-title-md text-sm font-semibold transition-all shadow-[0_0_0_1px_rgba(16,185,129,0.3)] active:scale-95';
                            }
                        };

                        applyFollowStyle(followBtn, isFollowed);

                        document.querySelectorAll(`.btn-follow[data-id="${userId}"]`).forEach(btn => {
                            if (btn !== followBtn) {
                                applyFollowStyle(btn, isFollowed);
                            }
                        });

                        // Update follower count on profile header if present
                        const followerCountEl = document.getElementById('profile-follower-count');
                        if (followerCountEl && typeof data.follower_count !== 'undefined') {
                            followerCountEl.textContent = data.follower_count;
                        }
                    }
                } catch (err) {
                    console.error('Follow error:', err);
                } finally {
                    followBtn.disabled = false;
                }
                return;
            }
        });
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
            // Remove the sticky top header ("<- Story")
            const topBar = mainContent.querySelector('.sticky.top-0');
            if (topBar) topBar.remove();
            
            // COMPLETELY remove the image grids and standalone cover images from the sidebar
            mainContent.querySelectorAll('div.grid').forEach(grid => {
                if(grid.querySelector('img')) grid.remove();
            });
            mainContent.querySelectorAll('img').forEach(im => {
                if(im.classList.contains('object-cover') && !im.classList.contains('rounded-full')) {
                    const wrapper = im.closest('div.mt-2.mb-3') || im.closest('div.mt-1.mb-2');
                    if (wrapper) wrapper.remove();
                    else im.remove();
                }
            });
            
            sidebar.innerHTML = mainContent.innerHTML;
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
                    if (window.location.href.includes(detailLink) || window.location.pathname.includes('/post/detail/')) {
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
                    const linkTag = article.querySelector('a[href*="/post/detail/"]');
                    if (linkTag) detailLink = linkTag.href;
                    else if (window.location.href.includes('/post/detail/')) detailLink = window.location.href;
                } else if (quillContent) {
                    if (window.location.href.includes('/post/detail/')) detailLink = window.location.href;
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
</body>
</html>