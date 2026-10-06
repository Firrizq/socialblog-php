<?php require_once __DIR__ . '/../templates/header.php'; ?>

<!-- Distraction-Free Editor Overlay -->
<div class="fixed inset-0 z-[100] bg-surface overflow-y-auto flex flex-col w-full h-full">
    
    <!-- Substack-Style Sticky Top Bar -->
    <div class="sticky top-0 z-[110] bg-surface border-b border-outline-variant/30 px-4 sm:px-6 h-[72px] flex items-center justify-between shrink-0">
        <!-- Left: Back -->
        <div class="flex-1 flex items-center">
            <button type="button" onclick="document.body.style.overflow='auto'; history.back();" class="flex items-center gap-2 text-on-surface-variant hover:text-on-surface font-title-md text-[15px] transition-colors group">
                <span class="material-symbols-outlined text-[22px] group-hover:-translate-x-1 transition-transform">arrow_back</span>
                Back
            </button>
        </div>

        <!-- Center: Desktop Toolbar Container -->
        <div id="toolbar-container" class="hidden lg:flex justify-center items-center w-full max-w-5xl px-2">
            <!-- Editorial tools injected here -->
        </div>

        <!-- Right: Actions -->
        <div class="flex-1 flex items-center justify-end gap-2 sm:gap-3">
            <span id="word-count-badge" class="hidden md:flex items-center text-xs font-title-md text-on-surface-variant mr-3"><span id="draft-status" class="mr-2 text-primary font-bold"></span><span id="word-count-text">0 words</span></span>
            <button type="button" onclick="saveDraft()" class="hidden sm:inline-flex btn-secondary px-4 py-2 text-sm">Save Draft</button>
            <button type="button" id="preview-btn" class="hidden sm:inline-flex btn-secondary px-4 py-2 text-sm">Preview</button>
            <button type="submit" form="story-form" id="publish-btn" onclick="if(document.getElementById('post-status')) document.getElementById('post-status').value = 'published';" class="btn-primary px-5 py-2 text-sm">
                <span id="publish-btn-spinner" class="material-symbols-outlined text-[18px] animate-spin hidden">sync</span>
                <span id="publish-btn-text">Save Changes</span>
            </button>
        </div>
    </div>

    <!-- Mobile Toolbar Container -->
    <div id="mobile-toolbar-container" class="lg:hidden border-b border-outline-variant/30 bg-surface-container-lowest sticky top-[72px] z-[105] w-full overflow-x-auto hide-scrollbar"></div>

    <!-- Editor Canvas -->
    <div class="flex-1 w-full max-w-3xl mx-auto px-5 sm:px-8 pt-16 pb-32">
        <form id="story-form" action="<?= BASEURL ?>/post/update/<?= (int)($post['id'] ?? 0) ?>" method="POST" enctype="multipart/form-data" class="flex flex-col">
            <input type="hidden" name="post_type" value="story">
            <input type="hidden" name="status" id="post-status" value="<?= htmlspecialchars($post['status'] ?? 'published') ?>">
            
            <!-- Substack Style Title & Subtitle -->
            <textarea name="title" id="title-input" placeholder="Title" class="w-full bg-transparent border-none p-0 focus:ring-0 text-4xl sm:text-[48px] font-bold text-on-surface mb-4 placeholder:text-on-surface-variant/30 resize-none overflow-hidden editorial-font" rows="1" required><?= htmlspecialchars($post['title'] ?? '') ?></textarea>
            
            <textarea name="subtitle" id="subtitle-input" placeholder="Add a subtitle..." class="w-full bg-transparent border-none p-0 focus:ring-0 text-xl sm:text-[22px] text-on-surface-variant mb-12 placeholder:text-on-surface-variant/40 resize-none overflow-hidden editorial-font" rows="1"></textarea>

            <?php 
            $existingCover = '';
            if(!empty($post['cover_image'])) {
                $decoded = json_decode((string)$post['cover_image'], true);
                $imgs = is_array($decoded) ? $decoded : array_filter(explode(',', (string)$post['cover_image']));
                $existingCover = !empty($imgs) ? trim($imgs[0]) : '';
            }
            $existingExt = strtolower(pathinfo((string)$existingCover, PATHINFO_EXTENSION));
            $isExistingVideo = in_array($existingExt, ['mp4', 'webm', 'ogg', 'mov'], true);
            $existingUrl = !empty($existingCover) ? (str_starts_with($existingCover, 'http') ? $existingCover : BASEURL . htmlspecialchars($existingCover)) : '';
            ?>
            <!-- Dedicated Cover Media Uploader -->
            <div id="cover-image-container" class="mb-10 w-full flex flex-col items-start transition-all duration-200">
                <input type="file" name="images[]" id="cover-image-input" accept="image/*, video/mp4, video/webm, video/ogg" class="hidden">
                <!-- Flag to tell backend if the existing cover was kept or removed -->
                <input type="hidden" name="existing_cover" id="existing_cover" value="<?= htmlspecialchars($existingCover) ?>">
                
                <button type="button" id="add-cover-btn" class="<?= $existingCover ? 'hidden' : 'flex' ?> btn-secondary py-2 px-5 text-sm border-dashed mb-2 group">
                    <span class="material-symbols-outlined text-[20px] group-hover:scale-110 transition-transform">perm_media</span> Add cover media
                </button>

                <div id="cover-preview-wrapper" class="<?= $existingCover ? 'block' : 'hidden' ?> relative w-full group mt-2">
                    <img id="cover-preview-img" src="<?= (!$isExistingVideo && $existingUrl) ? $existingUrl : '' ?>" class="<?= (!$isExistingVideo && $existingUrl) ? '' : 'hidden' ?> w-full h-auto max-h-[500px] object-cover rounded-xl border border-outline-variant/30">
                    <video id="cover-preview-video" controls src="<?= ($isExistingVideo && $existingUrl) ? $existingUrl : '' ?>" class="<?= ($isExistingVideo && $existingUrl) ? '' : 'hidden' ?> w-full rounded-xl max-h-96 object-contain bg-black border border-outline-variant/30"></video>
                    <button type="button" id="remove-cover-btn" class="absolute top-4 right-4 w-9 h-9 rounded-xl bg-surface/90 backdrop-blur text-error flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-surface border border-outline-variant/40 shadow-sm z-10" title="Remove Media">
                        <span class="material-symbols-outlined text-[18px]">delete</span>
                    </button>
                </div>

                <!-- Live Video Compression Progress Card -->
                <div id="cover-compression-card" class="hidden w-full mt-3 p-4 rounded-xl bg-surface-container-high border border-outline-variant/40 shadow-sm transition-all">
                    <div class="flex items-center justify-between gap-3 mb-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span id="compress-icon" class="material-symbols-outlined text-primary text-[22px] animate-spin shrink-0">sync</span>
                            <div class="truncate">
                                <div id="compress-title" class="text-sm font-semibold text-on-surface">Optimizing video for web...</div>
                                <div id="compress-subtitle" class="text-xs text-on-surface-variant truncate">Compressing to 720p H.264 · 1 Mbps</div>
                            </div>
                        </div>
                        <span id="compress-percent-badge" class="text-sm font-bold text-primary font-mono shrink-0">0%</span>
                    </div>
                    <div class="w-full bg-surface-container-highest rounded-full h-2 overflow-hidden">
                        <div id="compress-progress-bar" class="bg-primary h-2 rounded-full transition-all duration-300" style="width: 0%;"></div>
                    </div>
                    <div id="compress-result" class="hidden mt-2 pt-2 border-t border-outline-variant/20 text-xs flex items-center justify-between text-on-surface-variant">
                        <span id="compress-stats"></span>
                        <span class="text-primary font-medium flex items-center gap-1">
                            <span class="material-symbols-outlined text-[16px]">check_circle</span> Ready to save
                        </span>
                    </div>
                </div>
            </div>

            <!-- Editorial Editor Area -->
            <div id="editor-container" class="w-full"></div>
            <input type="hidden" name="content" id="content">
        </form>
    </div>
</div>

<!-- Functional Preview Modal -->
<div id="preview-modal" class="fixed inset-0 z-[200] bg-surface overflow-y-auto hidden flex-col transition-opacity">
    <div class="sticky top-0 bg-surface/90 backdrop-blur border-b border-outline-variant/30 px-4 h-[68px] flex items-center justify-between z-10 shrink-0">
        <div class="font-title-md font-bold text-on-surface">Preview Mode</div>
        <button type="button" id="close-preview-btn" class="btn-secondary px-5 py-2 text-sm">Close Preview</button>
    </div>
    <div class="max-w-2xl mx-auto w-full px-5 py-16">
        <h1 id="preview-title" class="text-4xl sm:text-[44px] font-black text-on-surface tracking-tight mb-4 leading-[1.2] editorial-font"></h1>
        <h2 id="preview-subtitle" class="text-xl sm:text-[22px] text-on-surface-variant mb-8 editorial-font leading-relaxed"></h2>
        <img id="preview-cover-image" src="" class="hidden w-full h-auto max-h-[500px] object-cover rounded-xl border border-outline-variant/30 mb-10">
        <video id="preview-cover-video" controls class="hidden w-full rounded-xl max-h-96 object-contain bg-black mb-10 border border-outline-variant/30"></video>
        <div class="w-full h-px bg-outline-variant/40 mb-10"></div>
        <div id="preview-content" class="font-body-md text-on-surface text-[19px] leading-[1.8] editorial-font break-words"></div>
    </div>
</div>

<style>
    body { overflow: hidden !important; }
    .editorial-font { font-family: ui-serif, Georgia, Cambria, "Times New Roman", Times, serif !important; }
    textarea:focus { outline: none; box-shadow: none; }

    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<!-- Editorial WYSIWYG Editor Script -->
<script src="<?= BASEURL ?>/js/editorial-editor.js"></script>

<!-- FFmpeg.wasm for Client-Side Video Compression -->
<script src="https://cdn.jsdelivr.net/npm/@ffmpeg/ffmpeg@0.11.6/dist/ffmpeg.min.js" crossorigin="anonymous"></script>
<script src="<?= BASEURL ?>/js/video-compressor.js"></script>

<script>
    let editor;

    document.addEventListener("DOMContentLoaded", function() {
        const BASE_URL = '<?= BASEURL ?>';

        const autoResize = (el) => { el.style.height = 'auto'; el.style.height = el.scrollHeight + 'px'; };
        ['title-input', 'subtitle-input'].forEach(id => {
            const el = document.getElementById(id);
            if(el) { el.addEventListener('input', () => autoResize(el)); autoResize(el); }
        });

        // Initialize Custom Editorial Editor
        function initEditorialEditor() {
            if (typeof EditorialEditor === 'undefined') {
                console.warn('[EditorialEditor] Script still loading, retrying in 50ms...');
                setTimeout(initEditorialEditor, 50);
                return;
            }

            // Extract Subtitle from existing HTML content
            const rawContent = <?= json_encode($post['content'] ?? '') ?>;
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = rawContent;
            const subtitleEl = tempDiv.querySelector('.story-subtitle');
            if (subtitleEl && tempDiv.firstElementChild === subtitleEl) {
                document.getElementById('subtitle-input').value = subtitleEl.innerText;
                autoResize(document.getElementById('subtitle-input'));
                subtitleEl.remove();
            }

            editor = new EditorialEditor({
                container: '#editor-container',
                toolbarContainer: '#toolbar-container',
                mobileToolbarContainer: '#mobile-toolbar-container',
                placeholder: 'Tell your story...',
                initialContent: tempDiv.innerHTML,
                uploadUrl: `${BASE_URL}/upload/image`,
                onTextChange: (html, text, stats) => {
                    const contentInput = document.getElementById('content');
                    if (contentInput) contentInput.value = html;
                    const wc = document.getElementById('word-count-text');
                    if (wc) wc.textContent = `${stats.words} words · ${stats.readingTime} min read`;
                }
            });
        }

        initEditorialEditor();

        window.saveDraft = function() {
            const titleEl = document.getElementById('title-input');
            const title = titleEl ? titleEl.value.trim() : '';
            if (!title) {
                if (typeof showToast === 'function') {
                    showToast('Please provide at least a title to save draft', 'error');
                }
                if (titleEl) titleEl.focus();
                return;
            }
            const statusInput = document.getElementById('post-status');
            if (statusInput) statusInput.value = 'draft';
            const storyForm = document.getElementById('story-form');
            if (storyForm) {
                storyForm.requestSubmit ? storyForm.requestSubmit() : storyForm.submit();
            }
        };

        // Form Submit Logic (Injecting subtitle back in)
        document.getElementById('story-form').addEventListener('submit', function(e) {
            if (isCompressing) {
                e.preventDefault();
                showToast('Please wait for video compression to finish before saving.', 'error');
                return;
            }

            let html = editor ? editor.getHTML() : '';
            const subtitle = document.getElementById('subtitle-input').value.trim();
            const statusInput = document.getElementById('post-status');
            const isDraft = statusInput && statusInput.value === 'draft';
            
            if (!isDraft && (!editor || html === '<p><br></p>' || html.trim() === '')) {
                e.preventDefault();
                showToast('Content cannot be empty', 'error');
                return;
            }

            if (subtitle !== '') {
                html = `<h2 class="story-subtitle editorial-font" style="font-size: 22px; color: rgb(var(--color-on-surface-variant)); margin-bottom: 24px; line-height: 1.6;">${subtitle}</h2>` + html;
            }
            document.getElementById('content').value = html;
            document.body.style.overflow = 'auto';
        });

        // Media Upload & Dynamic Preview Logic
        const coverInput = document.getElementById('cover-image-input');
        const addCoverBtn = document.getElementById('add-cover-btn');
        const coverPreviewWrapper = document.getElementById('cover-preview-wrapper');
        const coverPreviewImg = document.getElementById('cover-preview-img');
        const coverPreviewVideo = document.getElementById('cover-preview-video');
        const removeCoverBtn = document.getElementById('remove-cover-btn');

        // Live Compression UI Elements
        const compCard = document.getElementById('cover-compression-card');
        const compIcon = document.getElementById('compress-icon');
        const compTitle = document.getElementById('compress-title');
        const compSubtitle = document.getElementById('compress-subtitle');
        const compPct = document.getElementById('compress-percent-badge');
        const compBar = document.getElementById('compress-progress-bar');
        const compResult = document.getElementById('compress-result');
        const compStats = document.getElementById('compress-stats');

        const publishBtn = document.getElementById('publish-btn');
        const publishSpinner = document.getElementById('publish-btn-spinner');
        const publishText = document.getElementById('publish-btn-text');

        let currentObjectUrl = null;
        let isCurrentMediaVideo = <?= $isExistingVideo ? 'true' : 'false' ?>;
        let isCompressing = false;

        function setCompressingState(compressing, pct = 0) {
            isCompressing = compressing;
            if (publishBtn) {
                if (compressing) {
                    publishBtn.disabled = true;
                    publishBtn.classList.add('opacity-75', 'cursor-not-allowed');
                    if (publishSpinner) publishSpinner.classList.remove('hidden');
                    if (publishText) publishText.textContent = `Compressing video... ${pct}%`;
                } else {
                    publishBtn.disabled = false;
                    publishBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                    if (publishSpinner) publishSpinner.classList.add('hidden');
                    if (publishText) publishText.textContent = 'Save Changes';
                }
            }
        }

        function updateMediaPreview(file) {
            if (!file) return;

            if (currentObjectUrl) {
                URL.revokeObjectURL(currentObjectUrl);
                currentObjectUrl = null;
            }

            // Check client-side file size constraint (200MB)
            const MAX_FILE_SIZE = 200 * 1024 * 1024;
            if (file.size > MAX_FILE_SIZE) {
                const mb = (file.size / (1024 * 1024)).toFixed(1);
                const msg = `File is too large (${mb}MB). Maximum allowed limit is 200MB.`;
                showToast(msg, 'error');
                coverInput.value = '';
                return;
            }

            currentObjectUrl = URL.createObjectURL(file);
            isCurrentMediaVideo = (file.type && file.type.startsWith('video/')) || /\.(mp4|webm|ogg|mov)$/i.test(file.name || '');

            if (isCurrentMediaVideo) {
                coverPreviewImg.src = '';
                coverPreviewImg.classList.add('hidden');
                coverPreviewVideo.src = currentObjectUrl;
                coverPreviewVideo.classList.remove('hidden');
            } else {
                coverPreviewVideo.src = '';
                coverPreviewVideo.classList.add('hidden');
                coverPreviewImg.src = currentObjectUrl;
                coverPreviewImg.classList.remove('hidden');
            }

            coverPreviewWrapper.classList.remove('hidden');
            coverPreviewWrapper.classList.add('block');
            addCoverBtn.classList.remove('flex');
            addCoverBtn.classList.add('hidden');
        }

        async function handleCoverMediaSelection(file) {
            if (!file) return;

            updateMediaPreview(file);

            const isVideo = (file.type && file.type.startsWith('video/')) || /\.(mp4|webm|ogg|mov|mkv)$/i.test(file.name || '');
            if (!isVideo) {
                if (compCard) compCard.classList.add('hidden');
                return;
            }

            if (compCard) {
                compCard.classList.remove('hidden');
                compResult.classList.add('hidden');
                compIcon.classList.add('animate-spin');
                compIcon.textContent = 'sync';
                compIcon.classList.remove('text-green-500', 'text-amber-500');
                compIcon.classList.add('text-primary');
                compTitle.textContent = 'Optimizing video for web...';
                const origSizeStr = window.VideoCompressor ? window.VideoCompressor.formatBytes(file.size) : `${(file.size / (1024 * 1024)).toFixed(1)} MB`;
                compSubtitle.textContent = `Original: ${origSizeStr} · Rescaling to 720p HD`;
                compPct.textContent = '0%';
                compBar.style.width = '0%';
            }

            setCompressingState(true, 0);

            try {
                if (!window.VideoCompressor) {
                    throw new Error('Video compressor library not loaded');
                }

                const compressedFile = await window.VideoCompressor.compress(file, {
                    onProgress: (pct) => {
                        setCompressingState(true, pct);
                        if (compPct) compPct.textContent = `${pct}%`;
                        if (compBar) compBar.style.width = `${pct}%`;
                    },
                    onStatus: (status) => {
                        if (compSubtitle && status) compSubtitle.textContent = status;
                    }
                });

                setCompressingState(false);

                if (compressedFile && compressedFile !== file) {
                    try {
                        const dt = new DataTransfer();
                        dt.items.add(compressedFile);
                        coverInput.files = dt.files;
                    } catch (dtErr) {
                        console.warn('DataTransfer input replacement error:', dtErr);
                    }

                    updateMediaPreview(compressedFile);

                    const origMb = window.VideoCompressor.formatBytes(file.size);
                    const compMb = window.VideoCompressor.formatBytes(compressedFile.size);
                    const savedPct = Math.round((1 - compressedFile.size / file.size) * 100);

                    if (compCard) {
                        compIcon.classList.remove('animate-spin', 'text-primary');
                        compIcon.classList.add('text-green-500');
                        compIcon.textContent = 'check_circle';
                        compTitle.textContent = 'Video compression complete!';
                        compSubtitle.textContent = `Optimized for fast playback and instant saving`;
                        compPct.textContent = '100%';
                        compBar.style.width = '100%';
                        compResult.classList.remove('hidden');
                        compStats.textContent = `${origMb} → ${compMb} (${savedPct}% saved)`;
                    }

                    showToast(`Video compressed: ${origMb} → ${compMb} (${savedPct}% saved)!`, 'success');
                } else {
                    if (compCard) {
                        compIcon.classList.remove('animate-spin');
                        compIcon.textContent = 'check_circle';
                        compTitle.textContent = 'Video already optimal';
                        compSubtitle.textContent = `Using original file`;
                        compPct.textContent = '100%';
                        compBar.style.width = '100%';
                    }
                }
            } catch (err) {
                console.error('Video compression error:', err);

                // 1. Revert UI state immediately
                setCompressingState(false);

                // 2. CRITICAL: Clear file input & previews to abort upload and prevent sending raw file
                coverInput.value = '';
                if (currentObjectUrl) {
                    URL.revokeObjectURL(currentObjectUrl);
                    currentObjectUrl = null;
                }
                isCurrentMediaVideo = false;
                coverPreviewImg.src = '';
                coverPreviewImg.classList.add('hidden');
                coverPreviewVideo.src = '';
                coverPreviewVideo.classList.add('hidden');
                coverPreviewWrapper.classList.remove('block');
                coverPreviewWrapper.classList.add('hidden');
                addCoverBtn.classList.remove('hidden');
                addCoverBtn.classList.add('flex');

                if (compCard) compCard.classList.add('hidden');

                // 3. Show requested Toast error
                showToast("Compression failed. The video may be too large for browser compression.", "error");
            }
        }

        if(addCoverBtn && coverInput) {
            addCoverBtn.addEventListener('click', () => coverInput.click());
            coverInput.addEventListener('change', function() {
                if(this.files && this.files[0]) {
                    handleCoverMediaSelection(this.files[0]);
                }
            });
            removeCoverBtn.addEventListener('click', () => {
                coverInput.value = '';
                if (currentObjectUrl) {
                    URL.revokeObjectURL(currentObjectUrl);
                    currentObjectUrl = null;
                }
                isCurrentMediaVideo = false;
                coverPreviewImg.src = '';
                coverPreviewImg.classList.add('hidden');
                coverPreviewVideo.src = '';
                coverPreviewVideo.classList.add('hidden');
                const existingCoverInput = document.getElementById('existing_cover');
                if (existingCoverInput) existingCoverInput.value = '';
                coverPreviewWrapper.classList.remove('block');
                coverPreviewWrapper.classList.add('hidden');
                addCoverBtn.classList.remove('hidden');
                addCoverBtn.classList.add('flex');

                if (compCard) compCard.classList.add('hidden');
                setCompressingState(false);
            });
        }

        // Preview Modal Logic (including Cover Image or Video)
        const previewModal = document.getElementById('preview-modal');
        const previewCoverEl = document.getElementById('preview-cover-image');
        const previewCoverVid = document.getElementById('preview-cover-video');

        document.getElementById('preview-btn').addEventListener('click', () => {
            document.getElementById('preview-title').innerText = document.getElementById('title-input').value || 'Untitled';
            document.getElementById('preview-subtitle').innerText = document.getElementById('subtitle-input').value;
            
            const activeCoverSrc = currentObjectUrl || (!coverPreviewImg.classList.contains('hidden') ? coverPreviewImg.getAttribute('src') : (!coverPreviewVideo.classList.contains('hidden') ? coverPreviewVideo.getAttribute('src') : ''));
            
            if (activeCoverSrc) {
                if (isCurrentMediaVideo) {
                    previewCoverVid.src = activeCoverSrc;
                    previewCoverVid.classList.remove('hidden');
                    previewCoverEl.src = '';
                    previewCoverEl.classList.add('hidden');
                } else {
                    previewCoverEl.src = activeCoverSrc;
                    previewCoverEl.classList.remove('hidden');
                    previewCoverVid.src = '';
                    previewCoverVid.classList.add('hidden');
                }
            } else {
                previewCoverEl.src = '';
                previewCoverEl.classList.add('hidden');
                previewCoverVid.src = '';
                previewCoverVid.classList.add('hidden');
            }

            document.getElementById('preview-content').innerHTML = editor ? editor.getHTML() : '';
            previewModal.classList.remove('hidden');
            previewModal.classList.add('flex');
        });
        document.getElementById('close-preview-btn').addEventListener('click', () => {
            previewModal.classList.add('hidden');
            previewModal.classList.remove('flex');
            if (previewCoverVid) {
                previewCoverVid.pause();
            }
        });

        // Restore body scroll on exit
        window.addEventListener('beforeunload', () => {
            document.body.style.overflow = 'auto';
        });
        window.addEventListener('popstate', () => {
            document.body.style.overflow = 'auto';
        });

        // LocalStorage Auto-Save
        const draftKey = 'blogggle_draft_' + (window.location.pathname.includes('edit') ? <?= $post['id'] ?? '0' ?> : 'new');
        const draftStatus = document.getElementById('draft-status');
        
        // Only load draft on create, or if we want to prompt on edit (simplified for auto-save)
        if(window.location.pathname.includes('create') && localStorage.getItem(draftKey)) {
            try {
                const savedData = JSON.parse(localStorage.getItem(draftKey));
                if (savedData && (savedData.title || savedData.content)) {
                    if (typeof window.showConfirmDialog === 'function') {
                        window.showConfirmDialog({
                            title: 'Restore Draft',
                            message: 'We found an unsaved draft. Would you like to restore it?',
                            confirmText: 'Restore',
                            cancelText: 'Discard',
                            isDanger: false,
                            onConfirm: () => {
                                if (document.getElementById('title-input')) document.getElementById('title-input').value = savedData.title || '';
                                if (document.getElementById('subtitle-input')) document.getElementById('subtitle-input').value = savedData.subtitle || '';
                                if (editor && savedData.content) editor.setHTML(savedData.content);
                                if (typeof showToast === 'function') showToast('Draft restored', 'info');
                            },
                            onCancel: () => {
                                localStorage.removeItem(draftKey);
                            }
                        });
                    }
                }
            } catch (e) {
                localStorage.removeItem(draftKey);
            }
        }

        setInterval(() => {
            const title = document.getElementById('title-input').value;
            const subtitle = document.getElementById('subtitle-input').value;
            const content = editor ? editor.getHTML() : '';
            if(title.trim() !== '' || (editor && editor.getText().trim().length > 0)) {
                localStorage.setItem(draftKey, JSON.stringify({title, subtitle, content}));
                if (draftStatus) {
                    draftStatus.textContent = 'Saved locally';
                    setTimeout(() => { draftStatus.textContent = ''; }, 3000);
                }
            }
        }, 10000); // Auto-save every 10 seconds

        // Clear draft on successful submit
        document.getElementById('story-form').addEventListener('submit', () => {
            localStorage.removeItem(draftKey);
        });

        // 3. Drag and Drop Cover Image
        const dropZone = document.getElementById('cover-image-container');
        if(dropZone && coverInput) {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, preventDefaults, false);
            });
            function preventDefaults(e) { e.preventDefault(); e.stopPropagation(); }
            
            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => dropZone.classList.add('opacity-50'), false);
            });
            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => dropZone.classList.remove('opacity-50'), false);
            });
            
            dropZone.addEventListener('drop', (e) => {
                let dt = e.dataTransfer;
                let files = dt.files;
                if(files && files[0] && (files[0].type.startsWith('image/') || files[0].type.startsWith('video/'))) {
                    const file = files[0];
                    if (files.length > 1 && typeof showToast === 'function') {
                        showToast('Only 1 cover media file allowed. Selected ' + file.name, 'info');
                    }
                    try {
                        const newDt = new DataTransfer();
                        newDt.items.add(file);
                        coverInput.files = newDt.files;
                    } catch (e) {
                        coverInput.files = files;
                    }
                    handleCoverMediaSelection(file);
                }
            }, false);
        }
    });
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
