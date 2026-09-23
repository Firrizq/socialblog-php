<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="flex flex-col w-full px-4 sm:px-6 py-4 pb-16">
    <!-- Top Action Bar -->
    <div class="flex items-center justify-between mb-6 pt-2">
        <div class="flex items-center gap-2">
            <a href="<?= BASEURL ?>/home" class="w-9 h-9 rounded-full bg-surface-container hover:bg-surface-container-high text-on-surface-variant hover:text-primary flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined text-xl">arrow_back</span>
            </a>
            <div>
                <h1 class="font-title-md text-xl font-bold text-on-surface tracking-tight">Draft a Story</h1>
                <p class="font-caption text-xs text-on-surface-variant">Write and format your article with rich typography</p>
            </div>
        </div>

        <button 
            type="button" 
            onclick="document.getElementById('createPostForm').requestSubmit()" 
            class="hidden sm:inline-flex items-center gap-1.5 px-5 py-2 rounded-full bg-primary-container text-on-primary-container font-label-md text-sm font-semibold hover:bg-primary transition-all shadow-[0_0_0_1px_rgba(16,185,129,0.3)] active:scale-[0.99]"
        >
            <span class="material-symbols-outlined text-base">send</span>
            <span>Publish</span>
        </button>
    </div>

    <!-- Error Alert -->
    <?php if (!empty($data['error'])): ?>
        <div class="mb-5 p-3.5 rounded-xl bg-error-container/25 border border-error/40 text-error text-sm flex items-start gap-2.5">
            <span class="material-symbols-outlined text-lg shrink-0 mt-0.5">error</span>
            <span><?= htmlspecialchars($data['error']) ?></span>
        </div>
    <?php endif; ?>

    <!-- Post Creation Form -->
    <form id="createPostForm" action="<?= BASEURL ?>/post/create" method="POST" class="flex flex-col gap-6">
        <!-- Title & Content Card -->
        <div class="bg-surface-container-low border border-outline-variant/30 rounded-2xl p-5 sm:p-7 shadow-md flex flex-col gap-5">
            <!-- Title Input -->
            <div>
                <label for="title" class="block font-label-md text-xs uppercase tracking-wider text-on-surface-variant mb-2 font-medium">
                    Story Title
                </label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    value="<?= htmlspecialchars($data['post_title'] ?? '') ?>" 
                    placeholder="Enter an intriguing title..." 
                    required 
                    autofocus
                    class="w-full px-4 py-3 bg-surface-container-lowest border border-outline-variant/40 rounded-xl font-title-md text-on-surface text-lg sm:text-xl font-bold placeholder:text-outline/50 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                >
            </div>

            <!-- Quill.js Rich Editor -->
            <div>
                <label class="block font-label-md text-xs uppercase tracking-wider text-on-surface-variant mb-2 font-medium">
                    Content Body
                </label>
                
                <!-- Quill Container -->
                <div class="rounded-xl overflow-hidden border border-outline-variant/40 focus-within:border-primary focus-within:ring-1 focus-within:ring-primary transition-all">
                    <div id="editor"></div>
                </div>

                <!-- Hidden Input for Form Submission -->
                <textarea name="content" id="hiddenArea" style="display:none;"></textarea>
            </div>
        </div>

        <!-- Mobile / Bottom Action Bar -->
        <div class="flex items-center justify-between pt-2">
            <a href="<?= BASEURL ?>/home" class="text-on-surface-variant hover:text-on-surface text-sm font-medium transition-colors">
                Discard draft
            </a>
            <button 
                type="submit" 
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-primary-container text-on-primary-container font-label-md text-sm font-semibold hover:bg-primary transition-all shadow-[0_0_0_1px_rgba(16,185,129,0.3)] active:scale-[0.99]"
            >
                <span class="material-symbols-outlined text-base">send</span>
                <span>Publish Story</span>
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>

<!-- Initialize Quill.js & Form Sync -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const quill = new Quill('#editor', {
            theme: 'snow',
            placeholder: 'Tell your story... Use rich text, code snippets, lists or quotes.',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    ['link', 'clean']
                ]
            }
        });

        // Restore content if reloaded after validation error
        const existingContent = <?= json_encode($data['content'] ?? '') ?>;
        if (existingContent) {
            quill.root.innerHTML = existingContent;
        }

        // On submit, copy innerHTML into hidden textarea
        const form = document.getElementById('createPostForm');
        form.onsubmit = function() {
            const hiddenArea = document.getElementById('hiddenArea');
            hiddenArea.value = quill.root.innerHTML;
        };
    });
</script>
