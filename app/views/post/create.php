<?php require_once __DIR__ . '/../templates/header.php'; ?>

<!-- Distraction-Free Editor Overlay -->
<div class="fixed inset-0 z-[100] bg-surface overflow-y-auto flex flex-col w-full h-full">
    
    <!-- Substack-Style Sticky Top Bar -->
    <div class="sticky top-0 z-[110] bg-surface border-b border-outline-variant/30 px-4 sm:px-8 h-[72px] flex items-center justify-between shrink-0">
        <!-- Left: Back -->
        <div class="flex-1 flex items-center">
            <button type="button" onclick="document.body.style.overflow='auto'; history.back();" class="flex items-center gap-2 text-on-surface-variant hover:text-on-surface font-title-md text-[15px] transition-colors group">
                <span class="material-symbols-outlined text-[22px] group-hover:-translate-x-1 transition-transform">arrow_back</span>
                Back
            </button>
        </div>

        <!-- Center: Desktop Toolbar Container -->
        <div id="toolbar-container" class="hidden lg:flex justify-center items-center w-full max-w-4xl px-4">
            <!-- Editorial tools injected here -->
        </div>

        <!-- Right: Actions (Draft, Preview, Publish) -->
        <div class="flex-1 flex items-center justify-end gap-2 sm:gap-3">
            <span id="word-count-badge" class="hidden md:flex items-center text-xs font-title-md text-on-surface-variant mr-3"><span id="draft-status" class="mr-2 text-primary font-bold"></span><span id="word-count-text">0 words</span></span>
            <button type="button" onclick="saveDraft()" class="hidden sm:inline-flex btn-secondary px-4 py-2 text-sm">Draft</button>
            <button type="button" id="preview-btn" class="hidden sm:inline-flex btn-secondary px-4 py-2 text-sm">Preview</button>
            <button type="submit" form="story-form" id="publish-btn" onclick="if(document.getElementById('post-status')) document.getElementById('post-status').value = 'published';" class="btn-primary px-5 py-2 text-sm">
                <span id="publish-btn-spinner" class="material-symbols-outlined text-[18px] animate-spin hidden">sync</span>
                <span id="publish-btn-text">Publish</span>
            </button>
        </div>
    </div>

    <!-- Mobile Toolbar Container -->
    <div id="mobile-toolbar-container" class="lg:hidden border-b border-outline-variant/30 bg-surface-container-lowest sticky top-[72px] z-[105] w-full overflow-x-auto hide-scrollbar"></div>

    <!-- Editor Canvas -->
    <div class="flex-1 w-full max-w-3xl mx-auto px-5 sm:px-8 pt-16 pb-32">
        <form id="story-form" action="<?= BASEURL ?>/post/store" method="POST" enctype="multipart/form-data" class="flex flex-col">
            <input type="hidden" name="post_type" value="story">
            <input type="hidden" name="status" id="post-status" value="published">
            
            <!-- Substack Style Title & Subtitle -->
            <textarea name="title" id="title-input" placeholder="Title" class="w-full bg-transparent border-none p-0 focus:ring-0 text-4xl sm:text-[48px] font-bold text-on-surface mb-4 placeholder:text-on-surface-variant/30 resize-none overflow-hidden editorial-font" rows="1" required></textarea>
            
            <textarea name="subtitle" id="subtitle-input" placeholder="Add a subtitle..." class="w-full bg-transparent border-none p-0 focus:ring-0 text-xl sm:text-[22px] text-on-surface-variant mb-12 placeholder:text-on-surface-variant/40 resize-none overflow-hidden editorial-font" rows="1"></textarea>

            <!-- Dedicated Cover Media Uploader / Dropzone -->
            <div id="cover-image-container" class="mb-10 w-full flex flex-col items-start transition-all duration-200">
                <!-- File input allowing exactly one image or video -->
                <input type="file" name="images[]" id="cover-image-input" accept="image/*, video/mp4, video/webm, video/ogg" class="hidden">
                
                <button type="button" id="add-cover-btn" class="btn-secondary py-2 px-5 text-sm border-dashed mb-2 group">
                    <span class="material-symbols-outlined text-[20px] group-hover:scale-110 transition-transform">perm_media</span>
                    <span>Add cover media</span>
                </button>

                <div id="cover-preview-wrapper" class="hidden relative w-full group mt-2 rounded-2xl overflow-hidden border border-outline-variant/30 bg-surface-container-high transition-all">
                    <img id="cover-preview-img" src="" class="hidden w-full h-auto max-h-[500px] object-cover" alt="Cover Preview">
                    <video id="cover-preview-video" controls class="hidden w-full rounded-xl max-h-96 object-contain bg-black"></video>
                    <div class="absolute top-4 right-4 flex items-center gap-2 opacity-0 group-hover:opacity-100 focus-within:opacity-100 transition-opacity z-10">
                        <button type="button" id="change-cover-btn" class="px-3.5 py-1.5 rounded-xl bg-surface/90 backdrop-blur text-on-surface font-title-md text-xs flex items-center gap-1.5 hover:bg-surface border border-outline-variant/40 shadow-sm transition-all">
                            <span class="material-symbols-outlined text-[16px]">swap_horiz</span> Change
                        </button>
                        <button type="button" id="remove-cover-btn" class="w-8 h-8 rounded-xl bg-surface/90 backdrop-blur text-error flex items-center justify-center hover:bg-surface border border-outline-variant/40 shadow-sm transition-all" title="Remove Media">
                            <span class="material-symbols-outlined text-[18px]">delete</span>
                        </button>
                    </div>
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
                            <span class="material-symbols-outlined text-[16px]">check_circle</span> Ready to publish
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
    <div class="sticky top-0 bg-surface/90 backdrop-blur border-b border-outline-variant/30 px-4 h-[68px] flex items-center justify-between z-10">
        <div class="font-title-md font-bold text-on-surface">Preview Mode</div>
        <button type="button" id="close-preview-btn" class="btn-secondary px-5 py-2 text-sm">Close Preview</button>
    </div>
    <div class="max-w-2xl mx-auto w-full px-5 py-16">
        <h1 id="preview-title" class="text-4xl sm:text-[44px] font-black text-on-surface tracking-tight mb-4 leading-[1.2] editorial-font"></h1>
        <h2 id="preview-subtitle" class="text-xl sm:text-[22px] text-on-surface-variant mb-8 editorial-font leading-relaxed"></h2>
        <img id="preview-cover-image" src="" class="hidden w-full h-auto max-h-[500px] object-cover rounded-xl border border-outline-variant/30 mb-10">
        <video id="preview-cover-video" controls class="hidden w-full rounded-xl max-h-96 object-contain bg-black mb-10 border border-outline-variant/30"></video>
        <!-- Divider -->
        <div class="w-full h-px bg-outline-variant/40 mb-10"></div>
        <div id="preview-content" class="font-body-md text-on-surface text-[19px] leading-[1.8] editorial-font break-words"></div>
    </div>
</div>

<!-- Essential Styles for Substack Vibe -->
<style>
    /* Global Lock for Overlay */
    body { overflow: hidden !important; }

    /* Editorial Font Stack */
    .editorial-font {
        font-family: ui-serif, Georgia, Cambria, "Times New Roman", Times, serif !important;
    }

    /* Auto-resizing textarea resets */
    textarea:focus { outline: none; box-shadow: none; }

    /* Hide scrollbar for mobile toolbar */
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

    /* Subtitle rendering inside content */
    .story-subtitle { font-size: 22px; color: rgb(var(--color-on-surface-variant)); margin-bottom: 24px; line-height: 1.6; }
</style>

<!-- Editorial WYSIWYG Editor Script -->
<script src="<?= BASEURL ?>/js/editorial-editor.js"></script>

<!-- FFmpeg.wasm for Client-Side Video Compression -->
<script src="https://cdn.jsdelivr.net/npm/@ffmpeg/ffmpeg@0.11.6/dist/ffmpeg.min.js" crossorigin="anonymous"></script>
<script src="<?= BASEURL ?>/js/video-compressor.js"></script>

<!-- Initialize Editorial Editor and Interactions -->
<script>
    let editor;

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

    document.addEventListener("DOMContentLoaded", function() {
        const BASE_URL = '<?= BASEURL ?>';

        // 1. Auto-resize Title & Subtitle textareas
        const autoResize = (el) => {
            el.style.height = 'auto';
            el.style.height = el.scrollHeight + 'px';
        };
        ['title-input', 'subtitle-input'].forEach(id => {
            const el = document.getElementById(id);
            if(el) {
                el.addEventListener('input', () => autoResize(el));
                autoResize(el);
            }
        });

        // 2. Cover Media Upload & Drag-and-Drop Dropzone Logic
        let currentObjectUrl = null;
        let isCurrentMediaVideo = false;
        let isCompressing = false;

        const coverInput = document.getElementById('cover-image-input');
        const addCoverBtn = document.getElementById('add-cover-btn');
        const changeCoverBtn = document.getElementById('change-cover-btn');
        const removeCoverBtn = document.getElementById('remove-cover-btn');
        const dropZone = document.getElementById('cover-image-container');
        const coverPreviewWrapper = document.getElementById('cover-preview-wrapper');
        const coverPreviewImg = document.getElementById('cover-preview-img');
        const coverPreviewVideo = document.getElementById('cover-preview-video');

        // Compression UI Elements
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
                    if (publishText) publishText.textContent = 'Publish';
                }
            }
        }

        function updateMediaPreview(file) {
            if (!file) return;

            // Revoke previous object URL to prevent memory leaks
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
                if (coverInput) coverInput.value = '';
                return;
            }

            currentObjectUrl = URL.createObjectURL(file);
            isCurrentMediaVideo = (file.type && file.type.startsWith('video/')) || /\.(mp4|webm|ogg|mov)$/i.test(file.name || '');

            if (isCurrentMediaVideo) {
                if (coverPreviewImg) {
                    coverPreviewImg.src = '';
                    coverPreviewImg.classList.add('hidden');
                }
                if (coverPreviewVideo) {
                    coverPreviewVideo.src = currentObjectUrl;
                    coverPreviewVideo.classList.remove('hidden');
                }
            } else {
                if (coverPreviewVideo) {
                    coverPreviewVideo.src = '';
                    coverPreviewVideo.classList.add('hidden');
                }
                if (coverPreviewImg) {
                    coverPreviewImg.src = currentObjectUrl;
                    coverPreviewImg.classList.remove('hidden');
                }
            }

            if (coverPreviewWrapper) {
                coverPreviewWrapper.classList.remove('hidden');
                coverPreviewWrapper.classList.add('block');
            }
            if (addCoverBtn) {
                addCoverBtn.classList.remove('flex');
                addCoverBtn.classList.add('hidden');
            }
        }

        async function handleCoverMediaSelection(file) {
            if (!file) return;

            // Immediate preview for snappy user feedback
            updateMediaPreview(file);

            const isVideo = (file.type && file.type.startsWith('video/')) || /\.(mp4|webm|ogg|mov|mkv)$/i.test(file.name || '');
            if (!isVideo) {
                if (compCard) compCard.classList.add('hidden');
                return;
            }

            // If video, run client-side FFmpeg compression
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
                        if (coverInput) coverInput.files = dt.files;
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
                        compSubtitle.textContent = `Optimized for fast playback and instant publishing`;
                        compPct.textContent = '100%';
                        compBar.style.width = '100%';
                        compResult.classList.remove('hidden');
                        compStats.textContent = `${origMb} → ${compMb} (${savedPct}% saved)`;
                    }

                    if (typeof showToast === 'function') {
                        showToast(`Video compressed: ${origMb} → ${compMb} (${savedPct}% saved)!`, 'success');
                    }
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
                setCompressingState(false);

                if (coverInput) coverInput.value = '';
                if (currentObjectUrl) {
                    URL.revokeObjectURL(currentObjectUrl);
                    currentObjectUrl = null;
                }
                isCurrentMediaVideo = false;
                if (coverPreviewImg) {
                    coverPreviewImg.src = '';
                    coverPreviewImg.classList.add('hidden');
                }
                if (coverPreviewVideo) {
                    coverPreviewVideo.src = '';
                    coverPreviewVideo.classList.add('hidden');
                }
                if (coverPreviewWrapper) {
                    coverPreviewWrapper.classList.remove('block');
                    coverPreviewWrapper.classList.add('hidden');
                }
                if (addCoverBtn) {
                    addCoverBtn.classList.remove('hidden');
                    addCoverBtn.classList.add('flex');
                }
                if (compCard) compCard.classList.add('hidden');

                if (typeof showToast === 'function') {
                    showToast("Compression failed. The video may be too large for browser compression.", "error");
                }
            }
        }

        function initCoverMediaUploader() {
            if (!coverInput) return;

            // Link Add Cover Media Button to hidden input
            if (addCoverBtn) {
                addCoverBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    coverInput.click();
                });
            }

            // Link Change Cover Button to hidden input
            if (changeCoverBtn) {
                changeCoverBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    coverInput.click();
                });
            }

            // File input change handler (always ensures only ONE file)
            coverInput.addEventListener('change', function() {
                if (this.files && this.files.length > 0) {
                    const file = this.files[0];
                    if (this.files.length > 1) {
                        try {
                            const dt = new DataTransfer();
                            dt.items.add(file);
                            this.files = dt.files;
                        } catch (e) {}
                        if (typeof showToast === 'function') {
                            showToast('Only 1 cover media file allowed. Selected ' + file.name, 'info');
                        }
                    }
                    handleCoverMediaSelection(file);
                }
            });

            // Remove Cover Button handler
            if (removeCoverBtn) {
                removeCoverBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    coverInput.value = '';
                    if (currentObjectUrl) {
                        URL.revokeObjectURL(currentObjectUrl);
                        currentObjectUrl = null;
                    }
                    isCurrentMediaVideo = false;
                    if (coverPreviewImg) {
                        coverPreviewImg.src = '';
                        coverPreviewImg.classList.add('hidden');
                    }
                    if (coverPreviewVideo) {
                        coverPreviewVideo.src = '';
                        coverPreviewVideo.classList.add('hidden');
                    }
                    if (coverPreviewWrapper) {
                        coverPreviewWrapper.classList.remove('block');
                        coverPreviewWrapper.classList.add('hidden');
                    }
                    if (addCoverBtn) {
                        addCoverBtn.classList.remove('hidden');
                        addCoverBtn.classList.add('flex');
                    }
                    if (compCard) compCard.classList.add('hidden');
                    setCompressingState(false);
                });
            }

            // Drag and drop event listeners on dropzone container
            if (dropZone) {
                function preventDefaults(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, preventDefaults, false);
                });

                ['dragenter', 'dragover'].forEach(eventName => {
                    dropZone.addEventListener(eventName, () => {
                        dropZone.classList.add('ring-2', 'ring-primary', 'bg-primary/5', 'rounded-2xl');
                        if (addCoverBtn && !addCoverBtn.classList.contains('hidden')) {
                            addCoverBtn.classList.add('border-primary', 'text-primary', 'bg-primary/10');
                        }
                    }, false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, () => {
                        dropZone.classList.remove('ring-2', 'ring-primary', 'bg-primary/5', 'rounded-2xl');
                        if (addCoverBtn) {
                            addCoverBtn.classList.remove('border-primary', 'text-primary', 'bg-primary/10');
                        }
                    }, false);
                });

                dropZone.addEventListener('drop', (e) => {
                    const dt = e.dataTransfer;
                    if (!dt || !dt.files || dt.files.length === 0) return;

                    // Enforce ONLY ONE file: take the first file
                    const file = dt.files[0];
                    const isImg = file.type.startsWith('image/') || /\.(jpe?g|png|gif|webp)$/i.test(file.name);
                    const isVid = file.type.startsWith('video/') || /\.(mp4|webm|ogg|mov)$/i.test(file.name);

                    if (!isImg && !isVid) {
                        showToast('Please upload an image or video file.', 'error');
                        return;
                    }

                    if (dt.files.length > 1 && typeof showToast === 'function') {
                        showToast('Only 1 cover media file allowed. Selected ' + file.name, 'info');
                    }

                    // Sync single file to coverInput
                    try {
                        const newDt = new DataTransfer();
                        newDt.items.add(file);
                        coverInput.files = newDt.files;
                    } catch (dtErr) {
                        console.warn('DataTransfer sync error:', dtErr);
                    }

                    handleCoverMediaSelection(file);
                }, false);
            }
        }

        initCoverMediaUploader();

        // 3. Initialize Custom Editorial Editor
        function initEditorialEditor() {
            if (typeof EditorialEditor === 'undefined') {
                console.warn('[EditorialEditor] Script still loading, retrying in 50ms...');
                setTimeout(initEditorialEditor, 50);
                return;
            }

            editor = new EditorialEditor({
                container: '#editor-container',
                toolbarContainer: '#toolbar-container',
                mobileToolbarContainer: '#mobile-toolbar-container',
                placeholder: 'Tell your story...',
                uploadUrl: `${BASE_URL}/upload/image`,
                onTextChange: (html, text, stats) => {
                    const contentInput = document.getElementById('content');
                    if (contentInput) contentInput.value = html;
                    const wc = document.getElementById('word-count-text');
                    if (wc) wc.textContent = `${stats.words} words · ${stats.readingTime} min read`;
                }
            });

            // Restore draft from localStorage if available
            const savedDraftRaw = localStorage.getItem('blogggle_story_draft') || localStorage.getItem('blogggle_draft_new');
            if (savedDraftRaw) {
                try {
                    const savedDraft = JSON.parse(savedDraftRaw);
                    const titleEl = document.getElementById('title-input');
                    const subtitleEl = document.getElementById('subtitle-input');
                    if (titleEl && !titleEl.value && savedDraft.title) {
                        titleEl.value = savedDraft.title;
                        autoResize(titleEl);
                    }
                    if (subtitleEl && !subtitleEl.value && savedDraft.subtitle) {
                        subtitleEl.value = savedDraft.subtitle;
                        autoResize(subtitleEl);
                    }
                    if (savedDraft.content && savedDraft.content !== '<p><br></p>') {
                        editor.setHTML(savedDraft.content);
                    }
                } catch (err) {
                    console.error('Error loading saved draft:', err);
                }
            }
        }

        initEditorialEditor();

        // 5. Form Submit Logic (Injecting subtitle into content)
        const storyForm = document.getElementById('story-form');
        if (storyForm) {
            storyForm.addEventListener('submit', function(e) {
                if (isCompressing) {
                    e.preventDefault();
                    if (typeof showToast === 'function') {
                        showToast('Please wait for video compression to finish before publishing.', 'error');
                    }
                    return;
                }

                let html = editor ? editor.getHTML() : '';
                const subtitleInput = document.getElementById('subtitle-input');
                const subtitle = subtitleInput ? subtitleInput.value.trim() : '';
                const statusInput = document.getElementById('post-status');
                const isDraft = statusInput && statusInput.value === 'draft';
                
                if (!isDraft && (!editor || html === '<p><br></p>' || html.trim() === '')) {
                    e.preventDefault();
                    if (typeof showToast === 'function') {
                        showToast('Content cannot be empty', 'error');
                    }
                    return;
                }

                if (subtitle !== '') {
                    html = `<h2 class="story-subtitle editorial-font" style="font-size: 22px; color: rgb(var(--color-on-surface-variant)); margin-bottom: 24px; line-height: 1.6;">${subtitle}</h2>` + html;
                }
                const contentInput = document.getElementById('content');
                if (contentInput) contentInput.value = html;

                localStorage.removeItem('blogggle_story_draft');
                localStorage.removeItem('blogggle_draft_new');
                document.body.style.overflow = 'auto';
            });
        }

        // 6. Preview Modal Logic
        const previewBtn = document.getElementById('preview-btn');
        const previewModal = document.getElementById('preview-modal');
        const closePreviewBtn = document.getElementById('close-preview-btn');
        const previewCoverEl = document.getElementById('preview-cover-image');
        const previewCoverVid = document.getElementById('preview-cover-video');

        if (previewBtn && previewModal) {
            previewBtn.addEventListener('click', () => {
                const titleInput = document.getElementById('title-input');
                const subtitleInput = document.getElementById('subtitle-input');
                const prevTitle = document.getElementById('preview-title');
                const prevSub = document.getElementById('preview-subtitle');
                const prevContent = document.getElementById('preview-content');

                if (prevTitle) prevTitle.innerText = (titleInput && titleInput.value.trim()) || 'Untitled';
                if (prevSub) prevSub.innerText = (subtitleInput && subtitleInput.value.trim()) || '';

                if (currentObjectUrl) {
                    if (isCurrentMediaVideo) {
                        if (previewCoverVid) {
                            previewCoverVid.src = currentObjectUrl;
                            previewCoverVid.classList.remove('hidden');
                        }
                        if (previewCoverEl) {
                            previewCoverEl.src = '';
                            previewCoverEl.classList.add('hidden');
                        }
                    } else {
                        if (previewCoverEl) {
                            previewCoverEl.src = currentObjectUrl;
                            previewCoverEl.classList.remove('hidden');
                        }
                        if (previewCoverVid) {
                            previewCoverVid.src = '';
                            previewCoverVid.classList.add('hidden');
                        }
                    }
                } else {
                    if (previewCoverEl) {
                        previewCoverEl.src = '';
                        previewCoverEl.classList.add('hidden');
                    }
                    if (previewCoverVid) {
                        previewCoverVid.src = '';
                        previewCoverVid.classList.add('hidden');
                    }
                }

                if (prevContent && editor) {
                    prevContent.innerHTML = editor.getHTML();
                }

                previewModal.classList.remove('hidden');
                previewModal.classList.add('flex');
            });
        }

        if (closePreviewBtn && previewModal) {
            closePreviewBtn.addEventListener('click', () => {
                previewModal.classList.add('hidden');
                previewModal.classList.remove('flex');
                if (previewCoverVid) previewCoverVid.pause();
            });
        }

        // 7. Auto-Save Draft to LocalStorage every 10s
        const draftKey = 'blogggle_draft_new';
        const draftStatus = document.getElementById('draft-status');

        setInterval(() => {
            const titleInput = document.getElementById('title-input');
            const subtitleInput = document.getElementById('subtitle-input');
            const title = titleInput ? titleInput.value : '';
            const subtitle = subtitleInput ? subtitleInput.value : '';
            const content = editor ? editor.getHTML() : '';

            if (title.trim() !== '' || (editor && editor.getText().trim().length > 0)) {
                localStorage.setItem(draftKey, JSON.stringify({title, subtitle, content}));
                if (draftStatus) {
                    draftStatus.textContent = 'Saved locally';
                    setTimeout(() => { draftStatus.textContent = ''; }, 3000);
                }
            }
        }, 10000);

        // Restore body scroll on exit
        window.addEventListener('beforeunload', () => {
            document.body.style.overflow = 'auto';
        });
        window.addEventListener('popstate', () => {
            document.body.style.overflow = 'auto';
        });
    });
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
