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
            <!-- Quill tools injected here -->
        </div>

        <!-- Right: Actions (Draft, Preview, Publish) -->
        <div class="flex-1 flex items-center justify-end gap-2 sm:gap-3">
            <span id="word-count-badge" class="hidden md:flex items-center text-xs font-title-md text-on-surface-variant mr-3"><span id="draft-status" class="mr-2 text-primary font-bold"></span><span id="word-count-text">0 words</span></span>
            <button type="button" onclick="saveDraft()" class="hidden sm:block px-4 py-2 rounded-full text-on-surface-variant font-title-md text-sm hover:bg-surface-container-low transition-colors">Draft</button>
            <button type="submit" form="story-form" id="publish-btn" class="px-5 py-2 rounded-full bg-primary text-on-primary font-title-md text-sm hover:opacity-90 transition-opacity shadow-sm font-bold flex items-center gap-2">
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
            
            <!-- Substack Style Title & Subtitle -->
            <textarea name="title" id="title-input" placeholder="Title" class="w-full bg-transparent border-none p-0 focus:ring-0 text-4xl sm:text-[48px] font-bold text-on-surface mb-4 placeholder:text-on-surface-variant/30 resize-none overflow-hidden editorial-font" rows="1" required></textarea>
            
            <textarea name="subtitle" id="subtitle-input" placeholder="Add a subtitle..." class="w-full bg-transparent border-none p-0 focus:ring-0 text-xl sm:text-[22px] text-on-surface-variant mb-12 placeholder:text-on-surface-variant/40 resize-none overflow-hidden editorial-font" rows="1"></textarea>

            <!-- Dedicated Cover Media Uploader -->
            <div id="cover-image-container" class="mb-10 w-full flex flex-col items-start">
                <!-- File input allowing images and videos up to 200MB -->
                <input type="file" name="images[]" id="cover-image-input" accept="image/png, image/jpeg, image/gif, video/mp4, video/webm, video/quicktime, video/ogg" class="hidden">
                
                <button type="button" id="add-cover-btn" class="flex items-center gap-2 text-on-surface-variant hover:text-on-surface transition-colors font-title-md text-sm py-2 px-5 rounded-full border border-outline-variant/60 hover:bg-surface-container-low border-dashed mb-2 group">
                    <span class="material-symbols-outlined text-[20px] group-hover:scale-110 transition-transform">perm_media</span> Add cover media
                </button>

                <div id="cover-preview-wrapper" class="hidden relative w-full group mt-2">
                    <img id="cover-preview-img" src="" class="hidden w-full h-auto max-h-[500px] object-cover rounded-xl border border-outline-variant/30">
                    <video id="cover-preview-video" controls class="hidden w-full rounded-xl max-h-96 object-contain bg-black border border-outline-variant/30"></video>
                    <button type="button" id="remove-cover-btn" class="absolute top-4 right-4 w-10 h-10 rounded-full bg-surface/80 backdrop-blur text-on-surface flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-surface border border-outline-variant/30 shadow-sm z-10" title="Remove Media">
                        <span class="material-symbols-outlined text-[20px]">delete</span>
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
                            <span class="material-symbols-outlined text-[16px]">check_circle</span> Ready to publish
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quill Editor Area -->
            <div id="editor-container" class="w-full"></div>
            <input type="hidden" name="content" id="content">
        </form>
    </div>
</div>

<!-- Functional Preview Modal -->
<div id="preview-modal" class="fixed inset-0 z-[200] bg-surface overflow-y-auto hidden flex-col transition-opacity">
    <div class="sticky top-0 bg-surface/90 backdrop-blur border-b border-outline-variant/30 px-4 h-[68px] flex items-center justify-between z-10">
        <div class="font-title-md font-bold text-on-surface">Preview Mode</div>
        <button type="button" id="close-preview-btn" class="px-5 py-2 rounded-full bg-on-surface text-surface font-title-md text-sm hover:opacity-80 transition-opacity">Close Preview</button>
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

    /* Clean WYSIWYG Toolbar overrides */
    .ql-toolbar.ql-snow {
        border: none !important;
        background: transparent !important;
        padding: 8px 0 !important;
        display: flex;
        flex-wrap: nowrap;
        align-items: center;
        width: max-content;
    }
    .ql-toolbar.ql-snow .ql-formats { margin-right: 12px !important; display: flex; align-items: center;}
    
    /* Toolbar Icons Customization */
    .ql-snow .ql-stroke { stroke: rgb(var(--color-on-surface-variant)) !important; stroke-width: 1.5 !important; }
    .ql-snow .ql-fill { fill: rgb(var(--color-on-surface-variant)) !important; }
    .ql-snow .ql-picker { color: rgb(var(--color-on-surface-variant)) !important; font-family: 'Inter', sans-serif !important; font-weight: 500;}
    .ql-snow.ql-toolbar button:hover .ql-stroke, .ql-snow.ql-toolbar button.ql-active .ql-stroke, .ql-snow .ql-picker-label:hover .ql-stroke { stroke: rgb(var(--color-on-surface)) !important; }
    .ql-snow.ql-toolbar button:hover .ql-fill, .ql-snow.ql-toolbar button.ql-active .ql-fill, .ql-snow .ql-picker-label:hover .ql-fill { fill: rgb(var(--color-on-surface)) !important; }
    .ql-snow .ql-picker-options { background-color: rgb(var(--color-surface-container-high)) !important; border: 1px solid rgb(var(--color-outline-variant)) !important; border-radius: 8px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
    
    /* Editor Canvas Substack Typography */
    .ql-container.ql-snow {
        border: none !important;
        background: transparent !important;
        font-family: ui-serif, Georgia, Cambria, "Times New Roman", Times, serif !important;
        font-size: 20px !important; 
        line-height: 1.8 !important;
        color: rgb(var(--color-on-surface)) !important;
        padding: 0 !important;
    }
    .ql-editor { padding: 0 !important; min-height: 50vh; overflow-y: visible !important; }
    .ql-editor.ql-blank::before {
        left: 0 !important; font-style: normal !important;
        color: rgb(var(--color-on-surface-variant) / 0.3) !important;
    }
    /* Hide scrollbar for mobile toolbar */
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

    /* Subtitle rendering inside content */
    .story-subtitle { font-size: 22px; color: rgb(var(--color-on-surface-variant)); margin-bottom: 24px; line-height: 1.6; }
</style>

<!-- FFmpeg.wasm for Client-Side Video Compression -->
<script src="https://cdn.jsdelivr.net/npm/@ffmpeg/ffmpeg@0.11.6/dist/ffmpeg.min.js" crossorigin="anonymous"></script>
<script src="<?= BASEURL ?>/js/video-compressor.js"></script>

<!-- Initialize Quill and Interactions -->
<script>
    let quill;

    window.saveDraft = function() {
        const titleEl = document.getElementById('title-input');
        const subtitleEl = document.getElementById('subtitle-input');
        const title = titleEl ? titleEl.value : '';
        const subtitle = subtitleEl ? subtitleEl.value : '';
        const content = quill ? quill.root.innerHTML : '';
        localStorage.setItem('blogggle_story_draft', JSON.stringify({ title, subtitle, content, time: Date.now() }));
        showToast('Saved to Drafts!', 'success');
    };

    document.addEventListener("DOMContentLoaded", function() {
        const BASE_URL = '<?= BASEURL ?>';

        // Auto-resize Title & Subtitle textareas
        const autoResize = (el) => {
            el.style.height = 'auto';
            el.style.height = el.scrollHeight + 'px';
        };
        ['title-input', 'subtitle-input'].forEach(id => {
            const el = document.getElementById(id);
            if(el) {
                el.addEventListener('input', () => autoResize(el));
                autoResize(el); // Init
            }
        });

        // Add custom SVG icons for Undo/Redo to Quill
        const icons = Quill.import('ui/icons');
        icons['undo'] = '<svg viewBox="0 0 18 18"><path class="ql-fill" d="M10.5,4A5.5,5.5,0,0,0,5,9.5H3L6,13l3-3.5H7A3.5,3.5,0,0,1,10.5,6a3.5,3.5,0,0,1,3.5,3.5A3.5,3.5,0,0,1,10.5,13V15A5.5,5.5,0,0,0,10.5,4Z"/></svg>';
        icons['redo'] = '<svg viewBox="0 0 18 18"><path class="ql-fill" d="M7.5,4A5.5,5.5,0,0,1,13,9.5h2L12,13,9,9.5h2A3.5,3.5,0,0,0,7.5,6a3.5,3.5,0,0,0-3.5,3.5A3.5,3.5,0,0,0,7.5,13V15A5.5,5.5,0,0,1,7.5,4Z"/></svg>';

        // Image/Media upload handler for Quill Editor
        async function uploadImageToServer(file) {
            if (!file) return;

            const MAX_FILE_SIZE = 200 * 1024 * 1024;
            if (file.size > MAX_FILE_SIZE) {
                const mb = (file.size / (1024 * 1024)).toFixed(1);
                showToast(`File is too large (${mb}MB). Maximum allowed is 200MB.`, 'error');
                return;
            }

            let fileToUpload = file;
            const isVideo = (file.type && file.type.startsWith('video/')) || /\.(mp4|webm|ogg|mov|mkv)$/i.test(file.name || '');

            // Compress video on-the-fly via FFmpeg.wasm before sending AJAX upload
            if (isVideo) {
                if (!window.VideoCompressor) {
                    showToast("Compression failed. The video may be too large for browser compression.", "error");
                    return; // ABORT completely!
                }

                try {
                    showToast('Compressing video before upload...', 'info');
                    setCompressingState(true, 0);
                    fileToUpload = await window.VideoCompressor.compress(file, {
                        onProgress: (pct) => setCompressingState(true, pct),
                        onStatus: (status) => console.log('[Editor Video]', status)
                    });
                } catch (compressErr) {
                    console.error('Editor video compression failed:', compressErr);
                    setCompressingState(false);
                    // ABORT completely: Do NOT append raw file to FormData or proceed to upload!
                    showToast("Compression failed. The video may be too large for browser compression.", "error");
                    return;
                } finally {
                    setCompressingState(false);
                }
            }

            const formData = new FormData();
            formData.append('image', fileToUpload);
            formData.append('media', fileToUpload);

            try {
                const res = await fetch(`${BASE_URL}/upload/image`, {
                    method: 'POST',
                    body: formData
                });
                const rawText = await res.text();
                let data;
                try {
                    data = JSON.parse(rawText);
                } catch (parseErr) {
                    const snippet = rawText.replace(/<[^>]*>/g, '').trim().substring(0, 120);
                    throw new Error(snippet || `Server error (${res.status} ${res.statusText})`);
                }

                if (res.ok && data.success && data.url) {
                    const range = quill.getSelection(true);
                    quill.insertEmbed(range.index, isVideo ? 'video' : 'image', BASE_URL + data.url);
                    quill.setSelection(range.index + 1);
                    showToast('Media inserted into story!', 'success');
                } else {
                    showToast(data.message || `Upload failed (${res.status})`, 'error');
                }
            } catch (err) {
                console.error('Media upload error:', err);
                showToast(err.message || 'Failed to upload media', 'error');
            } finally {
                setCompressingState(false);
            }
        }

        function mediaHandler() {
            const input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/png, image/jpeg, image/gif, video/mp4, video/webm, video/quicktime, video/ogg');
            input.click();

            input.onchange = async () => {
                const file = input.files[0];
                if (file) await uploadImageToServer(file);
            };
        }

        // Define Comprehensive Toolbar Options
        const toolbarOptions = {
            container: [
                ['undo', 'redo'],
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                ['bold', 'italic', 'strike', 'code'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'script': 'sub'}, { 'script': 'super' }],
                ['link', 'image', 'video', 'blockquote'],
                [{ 'list': 'bullet' }, { 'list': 'ordered' }, { 'align': [] }]
            ],
            handlers: {
                'undo': function() { this.quill.history.undo(); },
                'redo': function() { this.quill.history.redo(); },
                'image': mediaHandler,
                'video': mediaHandler
            }
        };

        quill = new Quill('#editor-container', {
            theme: 'snow',
            placeholder: 'Tell your story...',
            modules: {
                toolbar: toolbarOptions,
                history: { delay: 1000, maxStack: 100, userOnly: true }
            }
        });

        // Relocate Toolbar based on viewport
        const isMobile = window.innerWidth < 1024;
        const toolbarTarget = isMobile ? '#mobile-toolbar-container' : '#toolbar-container';
        const generatedToolbar = document.querySelector('.ql-toolbar');
        const targetContainer = document.querySelector(toolbarTarget);
        if(generatedToolbar && targetContainer) {
            targetContainer.appendChild(generatedToolbar);
        }

        // Handle window resize for toolbar
        window.addEventListener('resize', function() {
            const currentTarget = window.innerWidth < 1024 
                ? document.querySelector('#mobile-toolbar-container') 
                : document.querySelector('#toolbar-container');
            if (generatedToolbar && currentTarget && generatedToolbar.parentElement !== currentTarget) {
                currentTarget.appendChild(generatedToolbar);
            }
        });

        // Paste event listener for images
        quill.root.addEventListener('paste', function(e) {
            const clipboardData = e.clipboardData || window.clipboardData;
            if (clipboardData && clipboardData.items) {
                for (let i = 0; i < clipboardData.items.length; i++) {
                    if (clipboardData.items[i].type.indexOf('image') !== -1) {
                        e.preventDefault();
                        const file = clipboardData.items[i].getAsFile();
                        uploadImageToServer(file);
                        break;
                    }
                }
            }
        });

        // Restore draft from localStorage if available and fields are empty
        const savedDraftRaw = localStorage.getItem('blogggle_story_draft');
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
                    quill.root.innerHTML = savedDraft.content;
                }
            } catch (err) {
                console.error('Error loading saved draft:', err);
            }
        }

        // Form Submit Logic (Injecting subtitle)
        document.getElementById('story-form').addEventListener('submit', function(e) {
            if (isCompressing) {
                e.preventDefault();
                showToast('Please wait for video compression to finish before publishing.', 'error');
                return;
            }

            let html = quill.root.innerHTML;
            const subtitle = document.getElementById('subtitle-input').value.trim();
            
            if (html === '<p><br></p>' || html.trim() === '') {
                e.preventDefault();
                showToast('Content cannot be empty', 'error');
                return;
            }

            if (subtitle !== '') {
                html = `<h2 class="story-subtitle editorial-font" style="font-size: 22px; color: rgb(var(--color-on-surface-variant)); margin-bottom: 24px; line-height: 1.6;">${subtitle}</h2>` + html;
            }
            document.getElementById('content').value = html;
            localStorage.removeItem('blogggle_story_draft');
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
        let isCurrentMediaVideo = false;
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
                if (typeof showToast === 'function') {
                    showToast(msg, 'error');
                } else {
                    alert(msg);
                }
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
                    // Modern DataTransfer API to seamlessly replace the input files list
                    try {
                        const dt = new DataTransfer();
                        dt.items.add(compressedFile);
                        coverInput.files = dt.files;
                    } catch (dtErr) {
                        console.warn('DataTransfer input replacement error:', dtErr);
                    }

                    // Update video preview to compressed output
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
            
            if (currentObjectUrl) {
                if (isCurrentMediaVideo) {
                    previewCoverVid.src = currentObjectUrl;
                    previewCoverVid.classList.remove('hidden');
                    previewCoverEl.src = '';
                    previewCoverEl.classList.add('hidden');
                } else {
                    previewCoverEl.src = currentObjectUrl;
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

            document.getElementById('preview-content').innerHTML = quill.root.innerHTML;
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

        // 1. Live Word Count
        quill.on('text-change', () => {
            const text = quill.getText().trim();
            const words = text.length > 0 ? text.split(/\s+/).length : 0;
            const readTime = Math.max(1, Math.ceil(words / 200));
            document.getElementById('word-count-text').textContent = `${words} words · ${readTime} min read`;
        });

        // 2. LocalStorage Auto-Save
        const draftKey = 'blogggle_draft_' + (window.location.pathname.includes('edit') ? <?= $post['id'] ?? '0' ?> : 'new');
        const draftStatus = document.getElementById('draft-status');
        
        // Only load draft on create, or if we want to prompt on edit (simplified for auto-save)
        if(window.location.pathname.includes('create') && localStorage.getItem(draftKey)) {
            const savedData = JSON.parse(localStorage.getItem(draftKey));
            if(confirm('We found an unsaved draft. Would you like to restore it?')) {
                document.getElementById('title-input').value = savedData.title || '';
                document.getElementById('subtitle-input').value = savedData.subtitle || '';
                quill.clipboard.dangerouslyPasteHTML(savedData.content || '');
            } else {
                localStorage.removeItem(draftKey);
            }
        }

        setInterval(() => {
            const title = document.getElementById('title-input').value;
            const subtitle = document.getElementById('subtitle-input').value;
            const content = quill.root.innerHTML;
            if(title.trim() !== '' || quill.getText().trim().length > 0) {
                localStorage.setItem(draftKey, JSON.stringify({title, subtitle, content}));
                draftStatus.textContent = 'Saved locally';
                setTimeout(() => { draftStatus.textContent = ''; }, 3000);
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
                    coverInput.files = files;
                    handleCoverMediaSelection(files[0]);
                }
            }, false);
        }
    });
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
