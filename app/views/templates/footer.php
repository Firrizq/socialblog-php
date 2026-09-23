</main>

            <!-- TAHAP 3: Right Sidebar -->
            <aside class="hidden xl:block w-80 sticky top-16 h-[calc(100vh-4rem)] p-gutter border-l border-outline-variant/30 overflow-y-auto">
                <div class="flex flex-col gap-space-lg">
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-space-md text-outline text-lg">search</span>
                        <input class="w-full pl-10 pr-space-md py-space-xs bg-surface-container-lowest border border-outline-variant/40 rounded-full font-body-md text-on-surface placeholder:text-outline focus:outline-none focus:border-primary-container focus:ring-1 focus:ring-primary-container" placeholder="Search EmeraldInk..." type="text"/>
                    </div>
                    
                    <div class="flex flex-col gap-space-md">
                        <span class="font-title-md text-on-surface">Trending Topics</span>
                        <div class="flex flex-wrap gap-space-xs">
                            <a class="px-space-md py-space-xs rounded-full bg-surface-container border border-outline-variant/30 text-on-surface-variant font-caption hover:border-primary hover:text-primary transition-colors" href="#">#PHP Native</a>
                            <a class="px-space-md py-space-xs rounded-full bg-surface-container border border-outline-variant/30 text-on-surface-variant font-caption hover:border-primary hover:text-primary transition-colors" href="#">#WebArchitecture</a>
                            <a class="px-space-md py-space-xs rounded-full bg-surface-container border border-outline-variant/30 text-on-surface-variant font-caption hover:border-primary hover:text-primary transition-colors" href="#">#Minimalism</a>
                        </div>
                    </div>
                    
                    <div class="flex flex-col gap-space-md">
                        <span class="font-title-md text-on-surface">Suggested Writers</span>
                        <div class="flex flex-col gap-space-md">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-space-sm">
                                    <div class="w-8 h-8 rounded-full bg-surface-container-high flex items-center justify-center font-label-md text-primary">AL</div>
                                    <div>
                                        <p class="font-label-md text-on-surface leading-tight">Arthur Lyra</p>
                                        <p class="font-caption text-on-surface-variant">Essays &amp; Systems</p>
                                    </div>
                                </div>
                                <button class="px-space-sm py-space-xs rounded-full bg-surface-container border border-outline-variant text-on-surface hover:border-primary hover:text-primary font-caption transition-colors">Follow</button>
                            </div>
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
</body>
</html>