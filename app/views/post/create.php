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
            <button type="button" onclick="saveDraft()" class="hidden sm:block px-4 py-2 rounded-full text-on-surface-variant font-title-md text-sm hover:bg-surface-container-low transition-colors">Draft</button>
            <button type="button" id="preview-btn" class="px-4 py-2 rounded-full bg-surface-container-lowest border border-outline-variant/50 text-on-surface font-title-md text-sm hover:bg-surface-container-low transition-colors shadow-sm">Preview</button>
            <button type="submit" form="story-form" class="px-5 py-2 rounded-full bg-primary text-on-primary font-title-md text-sm hover:opacity-90 transition-opacity shadow-sm font-bold">Publish</button>
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

            <!-- Dedicated Cover Image Uploader -->
            <div id="cover-image-container" class="mb-10 w-full flex flex-col items-start">
                <!-- Fallback to images[] array which standard backend controllers use for uploads -->
                <input type="file" name="images[]" id="cover-image-input" accept="image/*" class="hidden">
                
                <button type="button" id="add-cover-btn" class="flex items-center gap-2 text-on-surface-variant hover:text-on-surface transition-colors font-title-md text-sm py-2 px-5 rounded-full border border-outline-variant/60 hover:bg-surface-container-low border-dashed mb-2 group">
                    <span class="material-symbols-outlined text-[20px] group-hover:scale-110 transition-transform">image</span> Add cover image
                </button>

                <div id="cover-preview-wrapper" class="hidden relative w-full group mt-2">
                    <img id="cover-preview-img" src="" class="w-full h-auto max-h-[500px] object-cover rounded-xl border border-outline-variant/30">
                    <button type="button" id="remove-cover-btn" class="absolute top-4 right-4 w-10 h-10 rounded-full bg-surface/80 backdrop-blur text-on-surface flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-surface border border-outline-variant/30 shadow-sm" title="Remove Cover Image">
                        <span class="material-symbols-outlined text-[20px]">delete</span>
                    </button>
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
        if (typeof showToast === 'function') {
            showToast('Saved to Drafts!', 'success');
        } else {
            alert('Saved to Drafts!');
        }
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

        // Image upload handler
        async function uploadImageToServer(file) {
            if (!file || !file.type.startsWith('image/')) return;
            const formData = new FormData();
            formData.append('image', file);

            try {
                const res = await fetch(`${BASE_URL}/upload/image`, {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.success && data.url) {
                    const range = quill.getSelection(true);
                    quill.insertEmbed(range.index, 'image', BASE_URL + data.url);
                    quill.setSelection(range.index + 1);
                } else {
                    if (typeof showToast === 'function') showToast(data.message || 'Image upload failed', 'error');
                    else alert(data.message || 'Image upload failed');
                }
            } catch (err) {
                console.error('Image upload error:', err);
                if (typeof showToast === 'function') showToast('Failed to upload image', 'error');
            }
        }

        function imageHandler() {
            const input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
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
                'image': imageHandler
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
            let html = quill.root.innerHTML;
            const subtitle = document.getElementById('subtitle-input').value.trim();
            
            if (html === '<p><br></p>' || html.trim() === '') {
                e.preventDefault();
                if(typeof showToast === 'function') showToast('Content cannot be empty', 'error');
                else alert('Content cannot be empty');
                return;
            }

            if (subtitle !== '') {
                html = `<h2 class="story-subtitle editorial-font" style="font-size: 22px; color: rgb(var(--color-on-surface-variant)); margin-bottom: 24px; line-height: 1.6;">${subtitle}</h2>` + html;
            }
            document.getElementById('content').value = html;
            localStorage.removeItem('blogggle_story_draft');
            document.body.style.overflow = 'auto';
        });

        // Cover Image Upload Logic
        const coverInput = document.getElementById('cover-image-input');
        const addCoverBtn = document.getElementById('add-cover-btn');
        const coverPreviewWrapper = document.getElementById('cover-preview-wrapper');
        const coverPreviewImg = document.getElementById('cover-preview-img');
        const removeCoverBtn = document.getElementById('remove-cover-btn');

        if(addCoverBtn && coverInput) {
            addCoverBtn.addEventListener('click', () => coverInput.click());
            coverInput.addEventListener('change', function(e) {
                if(this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        coverPreviewImg.src = e.target.result;
                        coverPreviewWrapper.classList.remove('hidden');
                        coverPreviewWrapper.classList.add('block');
                        addCoverBtn.classList.remove('flex');
                        addCoverBtn.classList.add('hidden');
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            });
            removeCoverBtn.addEventListener('click', () => {
                coverInput.value = '';
                coverPreviewImg.src = '';
                coverPreviewWrapper.classList.remove('block');
                coverPreviewWrapper.classList.add('hidden');
                addCoverBtn.classList.remove('hidden');
                addCoverBtn.classList.add('flex');
            });
        }

        // Preview Modal Logic (including Cover Image)
        const previewModal = document.getElementById('preview-modal');
        document.getElementById('preview-btn').addEventListener('click', () => {
            document.getElementById('preview-title').innerText = document.getElementById('title-input').value || 'Untitled';
            document.getElementById('preview-subtitle').innerText = document.getElementById('subtitle-input').value;
            
            const coverSrc = coverPreviewImg.getAttribute('src');
            const previewCoverEl = document.getElementById('preview-cover-image');
            if (coverSrc && coverSrc !== '') {
                previewCoverEl.src = coverSrc;
                previewCoverEl.classList.remove('hidden');
            } else {
                previewCoverEl.src = '';
                previewCoverEl.classList.add('hidden');
            }

            document.getElementById('preview-content').innerHTML = quill.root.innerHTML;
            previewModal.classList.remove('hidden');
            previewModal.classList.add('flex');
        });

        document.getElementById('close-preview-btn').addEventListener('click', () => {
            previewModal.classList.add('hidden');
            previewModal.classList.remove('flex');
        });

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
