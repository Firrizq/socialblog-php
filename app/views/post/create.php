<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div style="margin-bottom: 1.5rem;">
        <h1 style="font-size: 1.75rem; color: #ffffff; margin-bottom: 0.25rem;">Create a New Post</h1>
        <p style="color: #94a3b8; font-size: 0.9rem;">Share your thoughts, tutorials, or updates with the community</p>
    </div>

    <?php if (!empty($data['error'])): ?>
        <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #fca5a5; padding: 0.75rem 1rem; border-radius: 8px; font-size: 0.875rem; margin-bottom: 1.5rem;">
            <?= htmlspecialchars($data['error']) ?>
        </div>
    <?php endif; ?>

    <form id="createPostForm" action="<?= BASEURL ?>/post/create" method="POST">
        <!-- Post Title Input -->
        <div style="margin-bottom: 1.25rem;">
            <label for="title" style="display: block; font-size: 0.875rem; font-weight: 500; color: #cbd5e1; margin-bottom: 0.5rem;">
                Post Title
            </label>
            <input 
                type="text" 
                id="title" 
                name="title" 
                value="<?= htmlspecialchars($data['post_title'] ?? '') ?>" 
                placeholder="Give your post a catchy title..." 
                required 
                autofocus
                style="width: 100%; background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 0.75rem 1rem; font-size: 1rem; color: #f8fafc; outline: none;"
            >
        </div>

        <!-- Quill.js Editor Container -->
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #cbd5e1; margin-bottom: 0.5rem;">
                Post Content
            </label>
            <div id="editor"></div>
            
            <!-- Hidden textarea to capture Quill HTML on submit -->
            <textarea name="content" id="hiddenArea" style="display:none;"></textarea>
        </div>

        <!-- Submit & Actions -->
        <div style="display: flex; gap: 1rem; justify-content: flex-end; align-items: center;">
            <a href="<?= BASEURL ?>/home" style="color: #94a3b8; text-decoration: none; font-size: 0.9rem;">Cancel</a>
            <button 
                type="submit" 
                style="background: #3b82f6; color: #ffffff; border: none; border-radius: 8px; padding: 0.75rem 1.75rem; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background-color 0.2s;"
            >
                Publish Post
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
            placeholder: 'Write your story here with rich formatting...',
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
