<?php require_once __DIR__ . '/../templates/header.php'; ?>

<!-- Distraction-Free Editor Overlay -->
<div class="fixed inset-0 z-[100] bg-surface overflow-y-auto flex flex-col">
    
    <!-- Substack-Style Sticky Top Bar -->
    <div class="sticky top-0 z-[110] bg-surface border-b border-outline-variant/30 px-4 sm:px-6 h-[68px] flex items-center justify-between gap-4">
        <!-- Left: Back / Saved Status -->
        <div class="flex-1 flex items-center min-w-0">
            <button type="button" onclick="handleExitEditor()" class="flex items-center gap-2 text-on-surface-variant hover:text-on-surface font-title-md text-sm transition-colors group">
                <span class="material-symbols-outlined text-[20px] group-hover:-translate-x-1 transition-transform">chevron_left</span>
                <span class="hidden sm:flex items-center gap-1.5">
                    <span id="saveStatusDot" class="w-2 h-2 rounded-full bg-primary transition-colors"></span>
                    <span id="saveStatusText" class="text-xs text-on-surface-variant font-medium">Saved</span>
                </span>
            </button>
        </div>

        <!-- Center: Toolbar Container (Desktop) -->
        <div id="toolbar-container" class="hidden lg:flex justify-center items-center overflow-x-auto no-scrollbar py-1">
            <!-- Quill tools injected here -->
        </div>

        <!-- Right: Actions (Preview, Draft, Publish) -->
        <div class="flex-1 flex items-center justify-end gap-2 sm:gap-3 shrink-0">
            <!-- Preview Button -->
            <button type="button" id="previewBtn" class="hidden sm:block px-4 py-1.5 rounded-full bg-surface-container-lowest border border-outline-variant/50 text-on-surface font-title-md text-sm hover:bg-surface-container-low transition-colors shadow-sm">
                Preview
            </button>
            
            <!-- Draft Button -->
            <button type="button" id="saveDraftBtn" class="px-4 py-1.5 rounded-full bg-surface-container-lowest border border-outline-variant/50 text-on-surface font-title-md text-sm hover:bg-surface-container-low transition-colors shadow-sm flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px] text-on-surface-variant">draft</span>
                <span>Draft</span>
            </button>
            
            <!-- Publish / Continue Button -->
            <button type="submit" form="story-form" name="action" value="publish" class="px-5 py-1.5 rounded-full bg-primary text-on-primary font-title-md text-sm hover:opacity-90 transition-opacity shadow-sm font-semibold">
                Publish
            </button>
        </div>
    </div>

    <!-- Mobile Toolbar Container (Screens < 1024px) -->
    <div id="mobile-toolbar-container" class="lg:hidden border-b border-outline-variant/30 bg-surface-container-lowest sticky top-[68px] z-[105] px-3 py-2 overflow-x-auto no-scrollbar flex items-center"></div>

    <!-- Error Alert Banner (if validation fails) -->
    <?php if (!empty($data['error'])): ?>
        <div class="max-w-3xl mx-auto w-full px-5 sm:px-8 mt-6">
            <div class="p-4 rounded-xl bg-error-container/25 border border-error/40 text-error text-sm flex items-start gap-2.5">
                <span class="material-symbols-outlined text-lg shrink-0 mt-0.5">error</span>
                <span><?= htmlspecialchars($data['error']) ?></span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Editor Canvas -->
    <div class="flex-1 w-full max-w-3xl mx-auto px-5 sm:px-8 pt-10 pb-32">
        <form id="story-form" action="<?= BASEURL ?>/post/create" method="POST" enctype="multipart/form-data" class="flex flex-col">
            <input type="hidden" name="post_type" value="story">
            <input type="hidden" name="action" id="actionInput" value="publish">
            
            <!-- Substack Style Title (Directly above Quill canvas) -->
            <input 
                type="text" 
                name="title" 
                id="titleInput" 
                value="<?= htmlspecialchars($data['post_title'] ?? '') ?>" 
                placeholder="Title" 
                class="w-full bg-transparent border-none p-0 focus:ring-0 text-4xl sm:text-[44px] font-bold text-on-surface mb-6 placeholder:text-on-surface-variant/40" 
                style="font-family: ui-serif, Georgia, Cambria, 'Times New Roman', Times, serif; line-height: 1.2;" 
                required 
                autofocus
            >

            <!-- Quill Editor Area -->
            <div id="editor-container" class="w-full min-h-[50vh]"></div>
            <input type="hidden" name="content" id="content">
        </form>
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="fixed inset-0 z-[150] bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-surface border border-outline-variant/30 rounded-2xl max-w-3xl w-full max-h-[90vh] flex flex-col overflow-hidden shadow-2xl">
        <div class="p-4 border-b border-outline-variant/30 flex items-center justify-between">
            <span class="font-title-md font-bold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-xl">visibility</span>
                Story Preview
            </span>
            <button type="button" onclick="document.getElementById('previewModal').classList.add('hidden')" class="w-8 h-8 rounded-full hover:bg-surface-container flex items-center justify-center text-on-surface-variant hover:text-on-surface transition-colors">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        </div>
        <div class="p-6 sm:p-10 overflow-y-auto flex-1">
            <h1 id="previewTitle" class="text-3xl sm:text-4xl font-bold text-on-surface mb-6 font-serif"></h1>
            <div id="previewContent" class="font-serif text-lg leading-relaxed text-on-surface ql-editor !p-0"></div>
        </div>
    </div>
</div>

<!-- Essential Styles for Substack Vibe -->
<style>
    /* Hide the global body scroll to prevent double scrollbars */
    body { overflow: hidden; }

    /* Override Quill's Default Boxy Styling */
    .ql-toolbar.ql-snow {
        border: none !important;
        background: transparent !important;
        padding: 0 !important;
        display: flex;
        align-items: center;
        flex-wrap: nowrap;
        gap: 0.25rem;
    }

    .ql-formats {
        display: inline-flex !important;
        align-items: center !important;
        margin-right: 0.4rem !important;
        padding-right: 0.4rem !important;
        border-right: 1px solid rgb(var(--color-outline-variant) / 0.5) !important;
    }
    .ql-formats:last-child {
        border-right: none !important;
        margin-right: 0 !important;
        padding-right: 0 !important;
    }

    /* Toolbar Icons Customization */
    .ql-snow .ql-stroke { stroke: rgb(var(--color-on-surface-variant)) !important; stroke-width: 1.5 !important; }
    .ql-snow .ql-fill { fill: rgb(var(--color-on-surface-variant)) !important; }
    .ql-snow .ql-picker { color: rgb(var(--color-on-surface-variant)) !important; font-family: 'Inter', sans-serif !important; font-weight: 500; font-size: 0.875rem !important; }
    
    .ql-snow.ql-toolbar button {
        border-radius: 0.375rem !important;
        padding: 4px !important;
        width: 28px !important;
        height: 28px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        transition: all 0.15s ease !important;
    }
    .ql-snow.ql-toolbar button:hover, .ql-snow.ql-toolbar button.ql-active {
        background-color: rgb(var(--color-surface-container-low)) !important;
    }
    .ql-snow.ql-toolbar button:hover .ql-stroke, .ql-snow.ql-toolbar button.ql-active .ql-stroke {
        stroke: rgb(var(--color-primary)) !important;
    }
    .ql-snow.ql-toolbar button:hover .ql-fill, .ql-snow.ql-toolbar button.ql-active .ql-fill {
        fill: rgb(var(--color-primary)) !important;
    }

    /* Picker Dropdowns (Header, Color, Align) */
    .ql-snow .ql-picker-options {
        background-color: rgb(var(--color-surface-container-lowest)) !important;
        border: 1px solid rgb(var(--color-outline-variant) / 0.6) !important;
        border-radius: 0.75rem !important;
        padding: 0.5rem !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.25) !important;
        z-index: 120 !important;
    }
    .ql-snow .ql-picker-item {
        color: rgb(var(--color-on-surface-variant)) !important;
        transition: color 0.15s ease !important;
    }
    .ql-snow .ql-picker-item:hover, .ql-snow .ql-picker-item.ql-selected {
        color: rgb(var(--color-primary)) !important;
    }

    /* Custom Header labels in picker */
    .ql-snow .ql-picker.ql-header .ql-picker-label[data-value="1"]::before,
    .ql-snow .ql-picker.ql-header .ql-picker-item[data-value="1"]::before { content: 'Heading 1' !important; }
    .ql-snow .ql-picker.ql-header .ql-picker-label[data-value="2"]::before,
    .ql-snow .ql-picker.ql-header .ql-picker-item[data-value="2"]::before { content: 'Heading 2' !important; }
    .ql-snow .ql-picker.ql-header .ql-picker-label[data-value="3"]::before,
    .ql-snow .ql-picker.ql-header .ql-picker-item[data-value="3"]::before { content: 'Heading 3' !important; }
    .ql-snow .ql-picker.ql-header .ql-picker-label[data-value="4"]::before,
    .ql-snow .ql-picker.ql-header .ql-picker-item[data-value="4"]::before { content: 'Heading 4' !important; }
    .ql-snow .ql-picker.ql-header .ql-picker-label[data-value="5"]::before,
    .ql-snow .ql-picker.ql-header .ql-picker-item[data-value="5"]::before { content: 'Heading 5' !important; }
    .ql-snow .ql-picker.ql-header .ql-picker-label[data-value="6"]::before,
    .ql-snow .ql-picker.ql-header .ql-picker-item[data-value="6"]::before { content: 'Heading 6' !important; }
    .ql-snow .ql-picker.ql-header .ql-picker-label:not([data-value])::before,
    .ql-snow .ql-picker.ql-header .ql-picker-item:not([data-value])::before { content: 'Normal text' !important; }

    /* Editor Canvas Substack Typography */
    .ql-container.ql-snow {
        border: none !important;
        background: transparent !important;
        font-family: ui-serif, Georgia, Cambria, "Times New Roman", Times, serif !important;
        font-size: 1.25rem !important; /* 20px reading size */
        line-height: 1.8 !important;
        color: rgb(var(--color-on-surface)) !important;
        padding: 0 !important;
    }
    .ql-editor {
        padding: 0 !important;
        min-height: 50vh;
        overflow-y: visible !important;
    }
    .ql-editor.ql-blank::before {
        left: 0 !important;
        font-style: normal !important;
        color: rgb(var(--color-on-surface-variant) / 0.4) !important;
        font-family: ui-serif, Georgia, Cambria, "Times New Roman", Times, serif !important;
    }

    .ql-editor h1 { font-size: 2.25rem !important; font-weight: 700 !important; margin: 2rem 0 1rem 0 !important; line-height: 1.3 !important; }
    .ql-editor h2 { font-size: 1.75rem !important; font-weight: 700 !important; margin: 1.75rem 0 0.75rem 0 !important; line-height: 1.3 !important; }
    .ql-editor h3 { font-size: 1.5rem !important; font-weight: 600 !important; margin: 1.5rem 0 0.5rem 0 !important; line-height: 1.3 !important; }
    .ql-editor h4 { font-size: 1.25rem !important; font-weight: 600 !important; margin: 1.25rem 0 0.5rem 0 !important; line-height: 1.3 !important; }
    .ql-editor h5 { font-size: 1.125rem !important; font-weight: 600 !important; margin: 1rem 0 0.5rem 0 !important; line-height: 1.3 !important; }
    .ql-editor h6 { font-size: 1rem !important; font-weight: 600 !important; margin: 1rem 0 0.5rem 0 !important; line-height: 1.3 !important; }
    .ql-editor blockquote { border-left: 3px solid rgb(var(--color-primary)) !important; padding-left: 1.25rem !important; margin: 1.75rem 0 !important; font-style: italic !important; color: rgb(var(--color-on-surface-variant)) !important; }
    .ql-editor img { max-width: 100% !important; border-radius: 0.5rem !important; margin: 1.5rem auto !important; display: block !important; }
    .ql-editor pre.ql-syntax, .ql-editor pre { background: rgb(var(--color-surface-container-high)) !important; border-radius: 0.5rem !important; padding: 1rem !important; font-family: monospace !important; font-size: 0.95rem !important; }
    .ql-editor a { color: rgb(var(--color-primary)) !important; text-decoration: underline !important; }
    .ql-editor code { background: rgb(var(--color-surface-container-high)) !important; padding: 0.2rem 0.4rem !important; border-radius: 0.25rem !important; font-size: 0.9em !important; }
</style>

<!-- Initialize Quill & Editor Functionality -->
<script>
    let quill;
    const STORAGE_KEY = 'blogggle_story_draft';

    function handleExitEditor() {
        // Save current draft state to localStorage before exiting
        saveDraftToLocalStorage();
        document.body.style.overflow = 'auto';
        history.back();
    }

    function saveDraftToLocalStorage() {
        if (!quill) return;
        const title = document.getElementById('titleInput').value.trim();
        const content = quill.root.innerHTML;
        const textLen = quill.getText().trim().length;

        if (title.length > 0 || textLen > 0) {
            localStorage.setItem(STORAGE_KEY, JSON.stringify({
                title: title,
                content: content,
                updated_at: Date.now()
            }));
            const dot = document.getElementById('saveStatusDot');
            const text = document.getElementById('saveStatusText');
            if (dot) dot.className = 'w-2 h-2 rounded-full bg-primary transition-colors';
            if (text) text.textContent = 'Saved';
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        const BASE_URL = '<?= BASEURL ?>';

        // Register custom icons in Quill
        if (window.Quill) {
            const icons = Quill.import('ui/icons');
            icons['undo'] = '<svg viewBox="0 0 18 18"><polygon class="ql-fill ql-stroke" points="6 10 4 12 2 10 6 10"></polygon><path class="ql-stroke" d="M8.09,13.91A4.6,4.6,0,0,0,9,14,5,5,0,1,0,4,9"></path></svg>';
            icons['redo'] = '<svg viewBox="0 0 18 18"><polygon class="ql-fill ql-stroke" points="12 10 14 12 16 10 12 10"></polygon><path class="ql-stroke" d="M9.91,13.91A4.6,4.6,0,0,1,9,14a5,5,0,1,1,5-5"></path></svg>';
            icons['audio'] = '<svg viewBox="0 0 18 18"><polygon class="ql-stroke" points="3 6 3 12 7 12 12 16 12 2 7 6 3 6"></polygon><path class="ql-stroke" d="M14 6.5a4 4 0 0 1 0 5"></path><path class="ql-stroke" d="M16 4a7 7 0 0 1 0 10"></path></svg>';
        }

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
                    if (window.showToast) showToast(data.message || 'Image upload failed', 'error');
                    else alert(data.message || 'Image upload failed');
                }
            } catch (err) {
                console.error('Image upload error:', err);
                if (window.showToast) showToast('Failed to upload image', 'error');
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

        // Audio handler
        function audioHandler() {
            const url = prompt('Enter Audio URL (e.g. MP3 link, podcast, or embed stream):');
            if (!url) return;
            const range = quill.getSelection(true);
            const cleanUrl = url.trim();
            if (cleanUrl.match(/\.(mp3|wav|ogg|m4a)($|\?)/i)) {
                quill.clipboard.dangerouslyPasteHTML(range.index, `<p><audio controls src="${cleanUrl}" style="max-width:100%; margin: 1rem 0;"></audio></p>`);
            } else {
                quill.insertText(range.index, `🎧 Audio Stream: ${cleanUrl}\n`, { link: cleanUrl, bold: true });
            }
            quill.setSelection(range.index + 1);
        }

        // Precisely configured toolbar options per specifications:
        // 1. Undo, Redo
        // 2. Style dropdown (Normal text, H1-H6)
        // 3. Bold, Italic, Strikethrough, Code, Text Color, Highlight Color, Superscript, Subscript
        // 4. Link, Image, Audio, Video, Quote
        // 5. Bullet list, Numbered list
        // 6. Align dropdown (Left, Center, Right, Justify)
        const toolbarOptions = [
            ['undo', 'redo'],
            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
            ['bold', 'italic', 'strike', 'code'],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'script': 'sub' }, { 'script': 'super' }],
            ['link', 'image', 'audio', 'video', 'blockquote'],
            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
            [{ 'align': [] }]
        ];

        // Determine toolbar container
        const isDesktop = window.innerWidth >= 1024;
        const initialTarget = isDesktop ? '#toolbar-container' : '#mobile-toolbar-container';

        quill = new Quill('#editor-container', {
            theme: 'snow',
            placeholder: 'Start writing your story...',
            modules: {
                toolbar: {
                    container: toolbarOptions,
                    handlers: {
                        undo: function() { this.quill.history.undo(); },
                        redo: function() { this.quill.history.redo(); },
                        image: imageHandler,
                        audio: audioHandler
                    }
                },
                history: {
                    delay: 1000,
                    maxStack: 100,
                    userOnly: true
                }
            }
        });

        // Relocate generated toolbar to our custom top bar
        const generatedToolbar = document.querySelector('.ql-toolbar');
        const targetContainer = document.querySelector(initialTarget);
        if (generatedToolbar && targetContainer) {
            targetContainer.appendChild(generatedToolbar);
        }

        // Responsive resize listener
        window.addEventListener('resize', function() {
            const currentTarget = window.innerWidth >= 1024 
                ? document.querySelector('#toolbar-container') 
                : document.querySelector('#mobile-toolbar-container');
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

        // Auto-Save Mechanism (localStorage debounced)
        let saveTimeout = null;
        function triggerAutoSave() {
            const dot = document.getElementById('saveStatusDot');
            const text = document.getElementById('saveStatusText');
            if (dot) dot.className = 'w-2 h-2 rounded-full bg-amber-400 transition-colors';
            if (text) text.textContent = 'Saving...';

            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                saveDraftToLocalStorage();
            }, 800);
        }

        document.getElementById('titleInput').addEventListener('input', triggerAutoSave);
        quill.on('text-change', triggerAutoSave);

        // Restore draft: check server error content first, then localStorage
        const serverContent = <?= json_encode($data['content'] ?? '') ?>;
        if (serverContent) {
            quill.root.innerHTML = serverContent;
        } else {
            const savedDraftRaw = localStorage.getItem(STORAGE_KEY);
            if (savedDraftRaw) {
                try {
                    const savedDraft = JSON.parse(savedDraftRaw);
                    const titleInput = document.getElementById('titleInput');
                    if (!titleInput.value && savedDraft.title) {
                        titleInput.value = savedDraft.title;
                    }
                    if (savedDraft.content && savedDraft.content !== '<p><br></p>') {
                        quill.root.innerHTML = savedDraft.content;
                        if (window.showToast) {
                            showToast('Restored unsaved draft from your last session', 'info');
                        }
                    }
                } catch (e) {
                    console.error('Error restoring draft:', e);
                }
            }
        }

        // Preview Button handler
        const previewBtn = document.getElementById('previewBtn');
        const previewModal = document.getElementById('previewModal');
        if (previewBtn && previewModal) {
            previewBtn.addEventListener('click', function() {
                const titleVal = document.getElementById('titleInput').value.trim() || 'Untitled Story';
                document.getElementById('previewTitle').textContent = titleVal;
                document.getElementById('previewContent').innerHTML = quill.root.innerHTML;
                previewModal.classList.remove('hidden');
            });
        }

        // Draft Button handler
        const saveDraftBtn = document.getElementById('saveDraftBtn');
        if (saveDraftBtn) {
            saveDraftBtn.addEventListener('click', function() {
                const titleInput = document.getElementById('titleInput');
                if (!titleInput.value.trim()) {
                    titleInput.value = 'Untitled Draft';
                }
                const html = quill.root.innerHTML;
                if (html === '<p><br></p>' || quill.getText().trim().length === 0) {
                    if (window.showToast) showToast('Please write some content before saving draft', 'error');
                    else alert('Please write some content before saving draft');
                    return;
                }

                document.getElementById('actionInput').value = 'draft';
                document.getElementById('content').value = html;
                localStorage.removeItem(STORAGE_KEY);
                document.getElementById('story-form').submit();
            });
        }

        // Form Submit (Publish) handler
        document.getElementById('story-form').addEventListener('submit', function(e) {
            const html = quill.root.innerHTML;
            if (html === '<p><br></p>' || quill.getText().trim().length === 0) {
                e.preventDefault();
                if (window.showToast) showToast('Story content cannot be empty', 'error');
                else alert('Story content cannot be empty');
                return;
            }
            document.getElementById('content').value = html;
            localStorage.removeItem(STORAGE_KEY);
        });

        // Restore body scroll when navigating away
        window.addEventListener('beforeunload', function() {
            saveDraftToLocalStorage();
            document.body.style.overflow = 'auto';
        });
        window.addEventListener('popstate', function() {
            document.body.style.overflow = 'auto';
        });
    });
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
