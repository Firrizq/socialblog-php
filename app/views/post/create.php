<?php require_once __DIR__ . '/../templates/header.php'; ?>

<!-- Zen Mode Distraction-Free Form Wrapper -->
<form id="createPostForm" action="<?= BASEURL ?>/post/create" method="POST" class="flex flex-col w-full min-h-screen">
    
    <!-- 2. Sticky Custom Toolbar & Top Actions -->
    <div class="sticky top-16 z-40 bg-surface/95 backdrop-blur-md border-b border-outline-variant/30 py-3 px-4 sm:px-6 mb-8 flex items-center justify-between gap-4">
        <!-- Left / Center: Quill Custom Toolbar Container -->
        <div class="flex items-center gap-2 min-w-0 flex-1 overflow-x-auto no-scrollbar py-0.5">
            <a href="<?= BASEURL ?>/home" class="w-8 h-8 rounded-full hover:bg-surface-container text-on-surface-variant hover:text-on-surface flex items-center justify-center transition-colors shrink-0 mr-1" title="Cancel & Return">
                <span class="material-symbols-outlined text-lg">close</span>
            </a>

            <!-- Quill Toolbar Mounted Here -->
            <div id="custom-toolbar" class="ql-toolbar ql-snow flex items-center flex-wrap gap-1">
                <!-- Undo / Redo -->
                <span class="ql-formats">
                    <button class="ql-undo" type="button" title="Undo">
                        <svg viewBox="0 0 18 18">
                            <polygon class="ql-fill ql-stroke" points="6 10 4 12 2 10 6 10"></polygon>
                            <path class="ql-stroke" d="M8.09,13.91A4.6,4.6,0,0,0,9,14,5,5,0,1,0,4,9"></path>
                        </svg>
                    </button>
                    <button class="ql-redo" type="button" title="Redo">
                        <svg viewBox="0 0 18 18">
                            <polygon class="ql-fill ql-stroke" points="12 10 14 12 16 10 12 10"></polygon>
                            <path class="ql-stroke" d="M9.91,13.91A4.6,4.6,0,0,1,9,14a5,5,0,1,1,5-5"></path>
                        </svg>
                    </button>
                </span>

                <!-- Headings -->
                <span class="ql-formats">
                    <select class="ql-header">
                        <option value="1">Heading 1</option>
                        <option value="2">Heading 2</option>
                        <option value="3">Heading 3</option>
                        <option selected>Normal</option>
                    </select>
                </span>

                <!-- Inline Styles -->
                <span class="ql-formats">
                    <button class="ql-bold" type="button" title="Bold"></button>
                    <button class="ql-italic" type="button" title="Italic"></button>
                    <button class="ql-underline" type="button" title="Underline"></button>
                    <button class="ql-strike" type="button" title="Strikethrough"></button>
                </span>

                <!-- Blocks -->
                <span class="ql-formats">
                    <button class="ql-blockquote" type="button" title="Quote"></button>
                    <button class="ql-code-block" type="button" title="Code Block"></button>
                </span>

                <!-- Lists -->
                <span class="ql-formats">
                    <button class="ql-list" value="ordered" type="button" title="Numbered List"></button>
                    <button class="ql-list" value="bullet" type="button" title="Bullet List"></button>
                </span>

                <!-- Media & Links -->
                <span class="ql-formats">
                    <button class="ql-link" type="button" title="Insert Link"></button>
                    <button class="ql-image" type="button" title="Insert Image"></button>
                    <button class="ql-video" type="button" title="Insert Video"></button>
                </span>

                <!-- Clean -->
                <span class="ql-formats">
                    <button class="ql-clean" type="button" title="Clear Formatting"></button>
                </span>
            </div>
        </div>

        <!-- Right: Action Buttons -->
        <div class="flex items-center gap-3 shrink-0">
            <a href="<?= BASEURL ?>/home" class="text-sm font-medium text-on-surface-variant hover:text-on-surface transition-colors px-2 py-1.5 rounded-lg hover:bg-surface-container">Cancel</a>
            <button type="submit" name="action" value="draft" class="text-sm font-medium text-primary hover:text-primary-fixed transition-colors px-3 py-1.5 rounded-lg hover:bg-surface-container border border-primary/30 active:scale-95">Save Draft</button>
            <button type="submit" name="action" value="publish" class="inline-flex items-center gap-1.5 px-5 py-2 rounded-full bg-primary-container text-on-primary-container font-label-md text-sm font-semibold hover:bg-primary transition-all shadow-[0_0_0_1px_rgba(16,185,129,0.3)] active:scale-95">
                <span class="material-symbols-outlined text-base">send</span><span>Publish</span>
            </button>
        </div>
    </div>

    <!-- Error Alert Banner (if validation fails) -->
    <?php if (!empty($data['error'])): ?>
        <div class="max-w-3xl mx-auto w-full px-6 sm:px-10 mb-6">
            <div class="p-4 rounded-xl bg-error-container/25 border border-error/40 text-error text-sm flex items-start gap-2.5">
                <span class="material-symbols-outlined text-lg shrink-0 mt-0.5">error</span>
                <span><?= htmlspecialchars($data['error']) ?></span>
            </div>
        </div>
    <?php endif; ?>

    <!-- 1 & 3. Zen Canvas Area (Centered, borderless, seamless Substack/Notion writing experience) -->
    <div class="max-w-3xl mx-auto w-full px-6 sm:px-10 py-4 sm:py-8 flex-1 flex flex-col">
        <!-- Integrated Massive Borderless Title Input -->
        <input 
            type="text" 
            id="title" 
            name="title" 
            value="<?= htmlspecialchars($data['post_title'] ?? '') ?>" 
            placeholder="Title" 
            required 
            autofocus
            class="text-4xl sm:text-5xl font-bold bg-transparent border-none outline-none focus:ring-0 w-full mb-6 text-on-surface placeholder:text-outline/40 leading-tight p-0"
        >

        <!-- Quill.js Editor Mounting Canvas -->
        <div id="editor" class="flex-1 font-body-lg text-lg text-on-surface leading-relaxed"></div>

        <!-- Hidden Input for Form Submission -->
        <textarea name="content" id="hiddenArea" style="display:none;"></textarea>
    </div>
</form>

<!-- 4. Advanced Quill.js Stripped Theme Styling -->
<style>
    /* Strip default Quill boxed borders and backgrounds */
    .ql-toolbar.ql-snow {
        border: none !important;
        background: transparent !important;
        padding: 0 !important;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.25rem;
    }

    .ql-container.ql-snow {
        border: none !important;
        background: transparent !important;
        padding: 0 !important;
        font-family: 'Inter', sans-serif !important;
        font-size: 1.125rem !important;
        color: #dae2fd !important;
    }

    .ql-editor {
        padding: 0 !important;
        min-height: 480px;
        font-size: 1.125rem !important;
        line-height: 1.8 !important;
        color: #dae2fd !important;
    }

    .ql-editor.ql-blank::before {
        left: 0 !important;
        right: 0 !important;
        font-style: normal !important;
        color: rgba(134, 148, 138, 0.4) !important;
        font-size: 1.125rem !important;
        font-family: 'Inter', sans-serif !important;
    }

    /* Obsidian Emerald Icon and Dropdown Colors */
    .ql-snow .ql-stroke {
        stroke: #bbcabf !important;
        transition: stroke 0.15s ease;
    }
    .ql-snow .ql-fill {
        fill: #bbcabf !important;
        transition: fill 0.15s ease;
    }
    .ql-snow .ql-picker {
        color: #bbcabf !important;
        font-size: 0.875rem !important;
    }
    .ql-snow .ql-picker-label {
        padding: 0.25rem 0.5rem !important;
        border-radius: 0.375rem !important;
        border: 1px solid transparent !important;
    }
    .ql-snow .ql-picker-label:hover {
        background-color: rgba(34, 42, 61, 0.8) !important;
        color: #4edea3 !important;
    }
    .ql-snow .ql-picker-options {
        background-color: #171f33 !important;
        border: 1px solid rgba(60, 74, 66, 0.5) !important;
        border-radius: 0.5rem !important;
        padding: 0.5rem !important;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5) !important;
    }
    .ql-snow .ql-picker-item:hover,
    .ql-snow .ql-picker-item.ql-selected {
        color: #4edea3 !important;
    }
    .ql-snow.ql-toolbar button {
        border-radius: 0.375rem !important;
        padding: 4px !important;
        width: 30px !important;
        height: 30px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        transition: all 0.15s ease !important;
    }
    .ql-snow.ql-toolbar button:hover,
    .ql-snow.ql-toolbar button:focus,
    .ql-snow.ql-toolbar button.ql-active {
        background-color: rgba(34, 42, 61, 0.9) !important;
    }
    .ql-snow.ql-toolbar button:hover .ql-stroke,
    .ql-snow.ql-toolbar button:focus .ql-stroke,
    .ql-snow.ql-toolbar button.ql-active .ql-stroke {
        stroke: #4edea3 !important;
    }
    .ql-snow.ql-toolbar button:hover .ql-fill,
    .ql-snow.ql-toolbar button:focus .ql-fill,
    .ql-snow.ql-toolbar button.ql-active .ql-fill {
        fill: #4edea3 !important;
    }

    /* Subdued divider between toolbar toolsets */
    .ql-formats {
        display: inline-flex !important;
        align-items: center !important;
        margin-right: 0.35rem !important;
        padding-right: 0.35rem !important;
        border-right: 1px solid rgba(60, 74, 66, 0.3) !important;
    }
    .ql-formats:last-child {
        border-right: none !important;
        margin-right: 0 !important;
        padding-right: 0 !important;
    }

    /* In-editor Zen typography preview */
    .ql-editor h1 { font-size: 2rem !important; font-weight: 700 !important; color: #dae2fd !important; margin: 1.75rem 0 0.75rem 0 !important; }
    .ql-editor h2 { font-size: 1.5rem !important; font-weight: 700 !important; color: #dae2fd !important; margin: 1.5rem 0 0.5rem 0 !important; }
    .ql-editor h3 { font-size: 1.25rem !important; font-weight: 600 !important; color: #dae2fd !important; margin: 1.25rem 0 0.5rem 0 !important; }
    .ql-editor p { margin-bottom: 1.15rem !important; }
    .ql-editor blockquote { border-left: 3px solid #10b981 !important; padding-left: 1rem !important; margin: 1.5rem 0 !important; font-style: italic !important; color: #bbcabf !important; }
    .ql-editor pre.ql-syntax { background: #060e20 !important; border: 1px solid rgba(60, 74, 66, 0.4) !important; border-radius: 0.5rem !important; padding: 1rem !important; color: #4edea3 !important; }
    .ql-editor a { color: #4edea3 !important; text-decoration: underline !important; }
    .ql-editor ul, .ql-editor ol { margin-left: 1.5rem !important; margin-bottom: 1.15rem !important; }
    .ql-editor li { margin-bottom: 0.35rem !important; }
</style>

<!-- Initialize Quill.js & Form Sync -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toolbar configuration array
    const toolbarOptions = [
        ['undo', 'redo'],
        [{ 'header': [1, 2, 3, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        ['blockquote', 'code-block'],
        [{ 'list': 'ordered' }, { 'list': 'bullet' }],
        ['link', 'image', 'video'],
        ['clean']
    ];

    // Initialize Quill with custom sticky toolbar container
    const quill = new Quill('#editor', {
        theme: 'snow',
        placeholder: 'Tell your story...',
        modules: {
            toolbar: {
                container: '#custom-toolbar',
                handlers: {
                    undo: function() {
                        this.quill.history.undo();
                    },
                    redo: function() {
                        this.quill.history.redo();
                    }
                }
            },
            history: {
                delay: 1000,
                maxStack: 100,
                userOnly: true
            }
        }
    });

    // Restore existing content if reloaded after validation error
    const existingContent = <?= json_encode($data['content'] ?? '') ?>;
    if (existingContent) {
        quill.root.innerHTML = existingContent;
    }

    // Sync innerHTML to hidden textarea before form submission
    const form = document.getElementById('createPostForm');
    form.addEventListener('submit', function() {
        const hiddenArea = document.getElementById('hiddenArea');
        hiddenArea.value = quill.root.innerHTML;
    });
});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
