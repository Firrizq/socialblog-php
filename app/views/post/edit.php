<?php require_once __DIR__ . '/../templates/header.php'; ?>

<!-- Standard Layout Story Editor (Nested in Main Column with Sidebars) -->
<div class="flex flex-col w-full pb-20">
    
    <!-- Sticky Top Header within standard column -->
    <div class="sticky top-16 z-30 bg-surface/90 backdrop-blur-md border-b border-outline-variant/30 px-4 sm:px-6 py-3 flex items-center justify-between gap-3">
        <!-- Left: Back Navigation & Status -->
        <div class="flex items-center gap-3 min-w-0">
            <a href="<?= ($data['post']['status'] ?? '') === 'draft' ? BASEURL . '/profile' : BASEURL . '/post/detail/' . (int)$data['post']['id'] ?>" class="w-8 h-8 rounded-full hover:bg-surface-container text-on-surface-variant hover:text-on-surface flex items-center justify-center transition-colors shrink-0" title="Return">
                <span class="material-symbols-outlined text-lg">arrow_back</span>
            </a>
            <div class="min-w-0">
                <h1 class="font-title-md text-base sm:text-lg font-bold text-on-surface truncate">Edit Story</h1>
                <p class="font-caption text-xs text-on-surface-variant">
                    Status: <span class="capitalize font-semibold <?= ($data['post']['status'] ?? '') === 'draft' ? 'text-amber-500' : 'text-primary' ?>"><?= htmlspecialchars($data['post']['status'] ?? 'published') ?></span>
                </p>
            </div>
        </div>

        <!-- Right: Actions (Preview, Save Draft, Publish / Save Changes) -->
        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
            <!-- Preview Button -->
            <button type="button" id="previewBtn" class="hidden sm:block px-4 py-1.5 rounded-full bg-surface-container border border-outline-variant/50 text-on-surface font-title-md text-sm hover:bg-surface-container-high transition-colors shadow-sm">
                Preview
            </button>
            
            <!-- Save Draft Button -->
            <button type="button" id="saveDraftBtn" class="px-4 py-1.5 rounded-full bg-surface-container border border-outline-variant/50 text-on-surface font-title-md text-sm hover:bg-surface-container-high transition-colors shadow-sm flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px] text-on-surface-variant">draft</span>
                <span>Save Draft</span>
            </button>

            <!-- Publish / Save Changes Button -->
            <button type="submit" form="editPostForm" name="action" value="publish" class="px-5 py-1.5 rounded-full bg-primary text-on-primary font-title-md text-sm hover:opacity-90 transition-opacity shadow-sm font-semibold">
                <?= ($data['post']['status'] ?? '') === 'draft' ? 'Publish' : 'Save Changes' ?>
            </button>
        </div>
    </div>

    <!-- Quill Toolbar Container (Sticky below subheader) -->
    <div id="toolbar-container" class="border-b border-outline-variant/30 bg-surface-container-lowest/80 backdrop-blur-md sticky top-[118px] sm:top-[122px] z-20 px-3 sm:px-6 py-2 overflow-x-auto no-scrollbar flex items-center">
        <!-- Quill tools injected here -->
    </div>

    <!-- Error Alert Banner (if validation fails) -->
    <?php if (!empty($data['error'])): ?>
        <div class="px-4 sm:px-6 mt-4">
            <div class="p-4 rounded-xl bg-error-container/25 border border-error/40 text-error text-sm flex items-start gap-2.5">
                <span class="material-symbols-outlined text-lg shrink-0 mt-0.5">error</span>
                <span><?= htmlspecialchars($data['error']) ?></span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Form & Editor Canvas -->
    <form id="editPostForm" action="<?= BASEURL ?>/post/edit/<?= (int)$data['post']['id'] ?>" method="POST" class="flex flex-col px-4 sm:px-6 py-6">
        <input type="hidden" name="action" id="actionInput" value="publish">

        <!-- Title Input (Serif Typography matching Create View) -->
        <input 
            type="text" 
            name="title" 
            id="titleInput" 
            value="<?= htmlspecialchars($data['post']['title'] ?? '') ?>" 
            placeholder="Title" 
            class="w-full bg-transparent border-none p-0 focus:ring-0 text-3xl sm:text-4xl font-bold text-on-surface mb-6 placeholder:text-on-surface-variant/40" 
            style="font-family: ui-serif, Georgia, Cambria, 'Times New Roman', Times, serif; line-height: 1.2;" 
            required 
            autofocus
        >

        <!-- Quill Editor Area -->
        <div id="editor-container" class="w-full min-h-[50vh]"></div>
        <input type="hidden" name="content" id="content">
    </form>
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

<!-- Quill Theme Styling -->
<style>
    /* Strip default Quill boxed borders and backgrounds */
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
        font-size: 1.25rem !important;
        line-height: 1.8 !important;
        color: rgb(var(--color-on-surface)) !important;
        padding: 0 !important;
    }
    .ql-editor {
        padding: 0 !important;
        min-height: 480px;
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

<!-- Initialize Quill in Edit View -->
<script>
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

        // Precise Toolbar Configuration matching Create View
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

        const quill = new Quill('#editor-container', {
            theme: 'snow',
            placeholder: 'Edit your story...',
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

        // Relocate toolbar inside toolbar-container
        const generatedToolbar = document.querySelector('.ql-toolbar');
        const targetContainer = document.querySelector('#toolbar-container');
        if (generatedToolbar && targetContainer) {
            targetContainer.appendChild(generatedToolbar);
        }

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

        // Populate existing story content
        const existingContent = <?= json_encode($data['post']['content'] ?? '') ?>;
        if (existingContent) {
            quill.root.innerHTML = existingContent;
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

        // Save Draft Button handler
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
                document.getElementById('editPostForm').submit();
            });
        }

        // Form Submit (Save Changes / Publish) handler
        document.getElementById('editPostForm').addEventListener('submit', function(e) {
            const html = quill.root.innerHTML;
            if (html === '<p><br></p>' || quill.getText().trim().length === 0) {
                e.preventDefault();
                if (window.showToast) showToast('Story content cannot be empty', 'error');
                else alert('Story content cannot be empty');
                return;
            }
            document.getElementById('content').value = html;
        });
    });
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
