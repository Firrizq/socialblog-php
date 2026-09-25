<?php require_once __DIR__ . '/../templates/header.php'; ?>

<!-- Sticky Top Bar -->
<div class="sticky top-16 z-30 bg-surface/90 backdrop-blur-md px-4 py-3 border-b border-outline-variant/30 flex items-center gap-4">
    <a href="<?= BASEURL ?>/home" class="w-9 h-9 rounded-full hover:bg-surface-container text-on-surface-variant hover:text-on-surface flex items-center justify-center transition-colors" title="Back to Home">
        <span class="material-symbols-outlined text-xl">arrow_back</span>
    </a>
    <h1 class="font-title-md text-lg font-bold text-on-surface leading-tight">
        Edit Note
    </h1>
</div>

<!-- Centered Card Container -->
<div class="max-w-2xl mx-auto py-8 px-4 w-full">
    <?php if (!empty($data['error'])): ?>
        <div class="p-4 rounded-xl bg-error-container/25 border border-error/40 text-error text-sm flex items-start gap-2.5 mb-6">
            <span class="material-symbols-outlined text-lg shrink-0 mt-0.5">error</span>
            <span><?= htmlspecialchars($data['error']) ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= BASEURL ?>/post/edit/<?= $data['post']['id'] ?>" class="flex flex-col gap-5 bg-surface-container-low border border-outline-variant/30 rounded-2xl p-5 sm:p-6 shadow-xl">
        <!-- Author Info Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center font-bold text-on-primary shrink-0 shadow-inner overflow-hidden">
                    <?php if (!empty($_SESSION['profile_picture'])): ?>
                        <img src="<?= BASEURL ?><?= htmlspecialchars($_SESSION['profile_picture']) ?>" alt="<?= htmlspecialchars($_SESSION['username'] ?? '') ?>" class="w-full h-full object-cover rounded-full">
                    <?php else: ?>
                        <?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <div class="flex flex-col">
                    <span class="font-bold text-on-surface text-base leading-tight">
                        <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
                    </span>
                    <span class="text-xs text-on-surface-variant font-caption">
                        Editing Note
                    </span>
                </div>
            </div>
            <?php if (($data['post']['status'] ?? '') === 'draft'): ?>
                <span class="px-2.5 py-0.5 rounded-full bg-error-container/20 text-error border border-error/30 font-caption text-xs font-semibold">Draft</span>
            <?php endif; ?>
        </div>

        <!-- Textarea with Cleaned Linebreaks & Image Attachment Container -->
        <div class="w-full">
            <textarea name="content" id="noteEditTextarea" rows="6" class="w-full bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-4 font-body-md text-on-surface text-lg focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all resize-y placeholder:text-outline-variant leading-relaxed" placeholder="What's on your mind?" required><?= htmlspecialchars(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $data['post']['content'] ?? ''))) ?></textarea>
            
            <input type="file" id="noteImageInput" accept="image/*" class="hidden" multiple>
            <input type="hidden" name="cover_image" id="noteCoverImageInput" value="<?= htmlspecialchars($data['post']['cover_image'] ?? '') ?>">
            <div id="noteImagePreviewContainer" class="hidden relative mt-3 w-full"></div>
        </div>

        <!-- Footer Section -->
        <div class="border-t border-outline-variant/30 pt-4 flex items-center justify-between">
            <button type="button" onclick="document.getElementById('noteImageInput').click()" class="flex items-center gap-1.5 text-on-surface-variant hover:text-primary transition-colors text-sm px-2.5 py-1.5 rounded-lg hover:bg-surface-container" title="Add Image">
                <span class="material-symbols-outlined text-xl">image</span>
                <span>Attach Image</span>
            </button>
            <div class="flex items-center gap-3">
                <a href="<?= ($data['post']['status'] ?? '') === 'draft' ? BASEURL . '/profile' : BASEURL . '/home' ?>" class="text-sm font-medium text-on-surface-variant hover:text-on-surface transition-colors px-3 py-2 rounded-lg hover:bg-surface-container">Cancel</a>
                <button type="submit" name="action" value="draft" class="text-sm font-medium text-primary hover:text-primary-fixed transition-colors px-4 py-2 rounded-lg hover:bg-surface-container border border-primary/30 active:scale-95">Save Draft</button>
                <button type="submit" name="action" value="publish" class="bg-primary-container text-on-primary-container hover:bg-primary rounded-full px-6 py-2 font-bold shadow-md transition-all active:scale-95 text-sm">Update</button>
            </div>
        </div>
    </form>
</div>

<script>
const BASE_URL = '<?= BASEURL ?>';

let uploadedNoteImages = [];

// Pre-fill if editing (only needed in edit_note.php, but safe to include globally)
const hiddenInputEl = document.getElementById('noteCoverImageInput');
if (hiddenInputEl && hiddenInputEl.value) {
    try { uploadedNoteImages = JSON.parse(hiddenInputEl.value); } 
    catch(e) { uploadedNoteImages = hiddenInputEl.value.split(',').filter(Boolean); }
    if(uploadedNoteImages.length > 0) renderNoteImagePreviews();
}

async function uploadNoteImage(file) {
    if (!file || !file.type.startsWith('image/')) return;
    if (uploadedNoteImages.length >= 4) {
        alert('Maksimal 4 gambar diperbolehkan.');
        return;
    }
    const formData = new FormData();
    formData.append('image', file);
    try {
        const res = await fetch(`${BASE_URL}/upload/image`, { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success && data.url) {
            uploadedNoteImages.push(data.url);
            renderNoteImagePreviews();
        } else { alert(data.message || 'Image upload failed'); }
    } catch (err) { alert('Failed to upload image. Please try again.'); }
}

function renderNoteImagePreviews() {
    const container = document.getElementById('noteImagePreviewContainer');
    const hiddenInput = document.getElementById('noteCoverImageInput');
    if (!container || !hiddenInput) return;
    hiddenInput.value = uploadedNoteImages.length > 0 ? JSON.stringify(uploadedNoteImages) : '';
    if (uploadedNoteImages.length === 0) {
        container.innerHTML = '';
        container.classList.add('hidden');
        return;
    }
    container.classList.remove('hidden');
    let html = `<div class="grid ${uploadedNoteImages.length === 1 ? 'grid-cols-1' : 'grid-cols-2'} gap-2">`;
    uploadedNoteImages.forEach((url, idx) => {
        html += `<div class="relative">
            <img src="${BASE_URL + url}" class="w-full h-32 object-cover rounded-xl border border-outline-variant/30">
            <button type="button" onclick="removeNoteImage(${idx})" class="absolute top-1 right-1 w-6 h-6 bg-black/70 text-white rounded-full flex items-center justify-center hover:bg-error transition-colors"><span class="material-symbols-outlined text-[14px]">close</span></button>
        </div>`;
    });
    html += '</div>';
    container.innerHTML = html;
}

// Ensure this is attached to window so inline onclick works
window.removeNoteImage = function(idx) {
    uploadedNoteImages.splice(idx, 1);
    renderNoteImagePreviews();
}

const noteImageInput = document.getElementById('noteImageInput');
if (noteImageInput) {
    noteImageInput.addEventListener('change', async function() {
        if (this.files) {
            for(let i=0; i<this.files.length; i++) {
                await uploadNoteImage(this.files[i]);
            }
        }
        this.value = ''; // Reset input
    });
}

const noteTextarea = document.getElementById('noteModalTextarea') || document.getElementById('noteEditTextarea');
if (noteTextarea) {
    noteTextarea.addEventListener('paste', async function(e) {
        const clipboardData = e.clipboardData || window.clipboardData;
        if (clipboardData && clipboardData.items) {
            for (let i = 0; i < clipboardData.items.length; i++) {
                if (clipboardData.items[i].type.indexOf('image') !== -1) {
                    e.preventDefault();
                    await uploadNoteImage(clipboardData.items[i].getAsFile());
                }
            }
        }
    });
}
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
