<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="flex flex-col w-full px-4 sm:px-6 py-2">
    <!-- Feed Header Navigation Tabs -->
    <div class="sticky top-16 z-30 bg-surface/90 backdrop-blur-md pb-space-xs pt-space-xs mb-space-lg flex items-center justify-between">
        <nav class="flex items-center gap-space-lg">
            <a href="<?= BASEURL ?>/home?feed=for-you" class="relative pb-space-sm font-title-md <?= ($data['feed_type'] !== 'following') ? 'text-primary' : 'text-on-surface-variant hover:text-on-surface' ?> transition-colors flex items-center gap-space-xs">
                <span>For You</span>
                <?php if($data['feed_type'] !== 'following'): ?><span class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary rounded-full shadow-[0_0_8px_rgba(78,222,163,0.6)]"></span><?php endif; ?>
            </a>
            <a href="<?= BASEURL ?>/home?feed=following" class="relative pb-space-sm font-title-md <?= ($data['feed_type'] === 'following') ? 'text-primary' : 'text-on-surface-variant hover:text-on-surface' ?> transition-colors flex items-center gap-space-xs">
                <span>Following</span>
                <?php if($data['feed_type'] === 'following'): ?><span class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary rounded-full shadow-[0_0_8px_rgba(78,222,163,0.6)]"></span><?php endif; ?>
            </a>
        </nav>
        <div class="flex items-center gap-space-xs">
            <button class="w-9 h-9 rounded-full bg-surface-container hover:bg-surface-container-high text-on-surface-variant hover:text-primary flex items-center justify-center transition-all"><span class="material-symbols-outlined text-xl">refresh</span></button>
        </div>
    </div>

    <!-- Quick Composer Strip -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <section class="bg-surface-container-low rounded-xl p-space-md mb-space-lg shadow-md hover:shadow-xl transition-shadow relative overflow-hidden cursor-text" onclick="openNoteModal()">
        <div class="flex items-start gap-space-md">
            <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center font-bold text-on-primary shrink-0 overflow-hidden">
                <?php if (!empty($_SESSION['profile_picture'])): ?>
                    <img src="<?= BASEURL ?><?= htmlspecialchars($_SESSION['profile_picture']) ?>" alt="<?= htmlspecialchars($_SESSION['username']) ?>" class="w-full h-full object-cover rounded-full">
                <?php else: ?>
                    <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                <?php endif; ?>
            </div>
            <div class="flex-1 min-w-0 pt-2">
                <p class="text-on-surface-variant font-body-md">Share a perspective, insight, or draft snippet...</p>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Feed Post Stream -->
    <div class="flex flex-col gap-space-lg">
        <?php if (!empty($data['posts']) && is_array($data['posts'])): ?>
            <?php foreach ($data['posts'] as $post): ?>
                
                <!-- Dynamic Card -->
                <article class="bg-surface-container-low rounded-xl p-space-lg shadow-md hover:bg-surface-container transition-colors flex flex-col gap-space-md">
                    <!-- Header Meta -->
                    <div class="flex items-center justify-between gap-space-md">
                        <div class="flex items-center gap-space-sm min-w-0">
                            <a href="<?= BASEURL ?>/profile/user/<?= urlencode($post['username'] ?? '') ?>" class="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center font-bold text-primary shrink-0 hover:ring-2 hover:ring-primary transition-all overflow-hidden">
                                <?php if (!empty($post['profile_picture'])): ?>
                                    <img src="<?= BASEURL ?><?= htmlspecialchars($post['profile_picture']) ?>" alt="<?= htmlspecialchars($post['username'] ?? '') ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <?= htmlspecialchars(substr($post['username'] ?? 'U', 0, 1)) ?>
                                <?php endif; ?>
                            </a>
                            <div class="flex flex-col min-w-0">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <a href="<?= BASEURL ?>/profile/user/<?= urlencode($post['username'] ?? '') ?>" class="font-title-md text-on-surface hover:text-primary transition-colors truncate">
                                        <?= htmlspecialchars($post['name'] ?? $post['username'] ?? 'Anonymous') ?>
                                    </a>
                                    <span class="font-caption text-on-surface-variant truncate">@<?= htmlspecialchars($post['username'] ?? 'anon') ?></span>
                                    <span class="font-caption text-on-surface-variant whitespace-nowrap"> • <?= date('M j, Y', strtotime($post['created_at'])) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                          <?php if(($post['post_type'] ?? 'story') === 'story'): ?>
                              <span class="px-space-sm py-0.5 rounded-full bg-surface-container font-caption text-on-surface-variant whitespace-nowrap"><?= $post['read_time_minutes'] ?? 1 ?> min read</span>
                          <?php endif; ?>
                          <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
                            <div class="relative dropdown-container">
                              <button type="button" onclick="toggleMenu(event, 'menu-<?= $post['id'] ?>')" class="text-on-surface-variant hover:text-primary p-1.5 rounded-full hover:bg-surface-container transition-colors flex items-center justify-center">
                                <span class="material-symbols-outlined text-lg">more_horiz</span>
                              </button>
                              <div id="menu-<?= $post['id'] ?>" class="hidden absolute right-0 top-full mt-1 w-40 bg-surface-container-high border border-outline-variant/30 rounded-xl shadow-xl z-50 overflow-hidden flex flex-col py-1">
                                <a href="<?= BASEURL ?>/post/edit/<?= $post['id'] ?>" class="px-4 py-2.5 text-sm text-on-surface font-title-md hover:bg-surface-container flex items-center gap-3 transition-colors"><span class="material-symbols-outlined text-lg">edit</span> Edit Story</a>
                                <form action="<?= BASEURL ?>/post/delete/<?= $post['id'] ?>" method="POST" class="m-0 p-0" onsubmit="return confirm('Are you sure you want to delete this story?');">
                                  <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-error font-title-md hover:bg-error-container/20 flex items-center gap-3 transition-colors"><span class="material-symbols-outlined text-lg">delete</span> Delete</button>
                                </form>
                              </div>
                            </div>
                          <?php endif; ?>
                        </div>
                    </div>

                    <!-- Content -->
                    <?php if(($post['post_type'] ?? 'story') === 'story'): ?>
                        <!-- Story Card Layout (Article Preview) -->
                        <div onclick="if(!event.target.closest('a') && !event.target.closest('button')) window.location.href='<?= BASEURL ?>/post/detail/<?= (int)$post['id'] ?>';" class="mt-2 flex flex-col rounded-2xl border border-outline-variant/30 overflow-hidden cursor-pointer group hover:border-primary/50 transition-colors bg-surface-container-lowest">
                            <?php 
                            $cover = '';
                            if(!empty($post['cover_image'])) {
                                $decoded = json_decode($post['cover_image'], true);
                                $imgs = is_array($decoded) ? $decoded : array_filter(explode(',', $post['cover_image']));
                                $cover = !empty($imgs) ? trim($imgs[0]) : '';
                            }
                            ?>
                            <?php if($cover): ?>
                                <div class="relative w-full h-48 sm:h-64 border-b border-outline-variant/30 overflow-hidden bg-surface-container-high">
                                    <img src="<?= BASEURL ?><?= htmlspecialchars($cover) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="Story Cover">
                                </div>
                            <?php endif; ?>
                            <div class="p-4 flex flex-col gap-1.5 bg-surface-container-lowest group-hover:bg-surface-container-low transition-colors">
                                <div class="flex items-center gap-2 mb-1">
                                    <div class="w-5 h-5 rounded-full bg-primary/20 flex items-center justify-center overflow-hidden shrink-0">
                                        <?php if (!empty($post['profile_picture'])): ?>
                                            <img src="<?= BASEURL ?><?= htmlspecialchars($post['profile_picture']) ?>" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <span class="text-[10px] font-bold text-primary"><?= htmlspecialchars(substr($post['username'] ?? 'U', 0, 1)) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="font-caption text-xs text-on-surface-variant font-medium truncate"><?= htmlspecialchars($post['name'] ?? $post['username']) ?></span>
                                </div>
                                <?php if(!empty($post['title'])): ?>
                                    <h2 class="font-title-md text-base sm:text-lg font-bold text-on-surface tracking-tight line-clamp-2">
                                        <?= htmlspecialchars($post['title']) ?>
                                    </h2>
                                <?php endif; ?>
                                <div class="font-body-md text-on-surface-variant text-sm line-clamp-2 mt-0.5">
                                    <?= strip_tags((string)($post['content'] ?? '')) ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Note Layout (Text Top, Image Grid Bottom) -->
                        <div onclick="if(!event.target.closest('a') && !event.target.closest('button')) window.location.href='<?= BASEURL ?>/post/detail/<?= (int)$post['id'] ?>';" class="flex flex-col gap-space-xs cursor-pointer group text-decoration-none">
                            <div class="font-body-md text-on-surface-variant leading-relaxed text-sm sm:text-base whitespace-pre-line mb-1">
                                <?= preg_replace('/(^|>|\s)#([a-zA-Z_][a-zA-Z0-9_]*)/', '$1<a href="' . BASEURL . '/explore/tag/$2" class="text-primary font-semibold hover:underline relative z-10" onclick="event.stopPropagation();">#$2</a>', strip_tags((string)($post['content'] ?? ''))) ?>
                            </div>
                            <?php if(!empty($post['cover_image'])): ?>
                                <?php 
                                $decoded = json_decode($post['cover_image'], true);
                                $imgs = is_array($decoded) ? $decoded : array_filter(explode(',',$post['cover_image']));
                                $imgs = array_slice($imgs, 0, 4);
                                $imgCount = count($imgs);
                                $imgJson = htmlspecialchars(json_encode(array_values($imgs)), ENT_QUOTES, 'UTF-8');
                                ?>
                                <div class="mt-1 mb-2 grid <?= $imgCount === 1 ? 'grid-cols-1' : 'grid-cols-2' ?> gap-1 rounded-2xl overflow-hidden border border-outline-variant/30 relative z-0">
                                    <?php foreach($imgs as $idx =>$img): ?>
                                        <img src="<?= BASEURL ?><?= htmlspecialchars(trim($img)) ?>" class="w-full h-full object-cover cursor-pointer hover:opacity-90 transition-opacity <?= ($imgCount === 3 &&$idx === 0) ? 'row-span-2' : '' ?> <?= $imgCount > 1 ? 'aspect-[4/3] sm:aspect-video' : 'max-h-[500px]' ?>" alt="Attachment" onclick="event.stopPropagation(); window.openLightboxGallery && openLightboxGallery(<?= $imgJson ?>, <?= $idx ?>, '<?= BASEURL ?>/post/detail/<?= (int)$post['id'] ?>')">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Actions -->
                    <div class="flex items-center justify-between pt-space-xs text-on-surface-variant">
                        <a href="<?= BASEURL ?>/post/detail/<?= (int)$post['id'] ?>" class="flex items-center gap-1.5 hover:text-on-surface transition-colors">
                            <span class="material-symbols-outlined text-lg">chat_bubble</span>
                            <span class="font-caption text-caption"><?= $post['comment_count'] ?? 0 ?></span>
                        </a>
                        <button class="flex items-center gap-1.5 hover:text-primary transition-colors">
                            <span class="material-symbols-outlined text-lg">sync_alt</span>
                            <span class="font-caption text-caption"><?= $post['repost_count'] ?? 0 ?></span>
                        </button>
                        <?php 
                            $isLiked = in_array((int)$post['id'], $data['liked_posts'] ?? []);
                            $isBookmarked = in_array((int)$post['id'], $data['bookmarked_posts'] ?? []);
                        ?>
                        <button class="btn-like flex items-center gap-1.5 transition-colors <?= $isLiked ? 'text-primary' : 'hover:text-primary' ?> active:scale-95" data-id="<?= (int)$post['id'] ?>" title="Like">
                            <span class="material-symbols-outlined text-lg" style="font-variation-settings: 'FILL' <?= $isLiked ? 1 : 0 ?>;">favorite</span>
                            <span class="like-count font-caption text-caption font-semibold"><?= (int)($post['like_count'] ?? 0) ?></span>
                        </button>
                        <button class="btn-bookmark transition-colors <?= $isBookmarked ? 'text-primary' : 'hover:text-primary' ?> active:scale-95" data-id="<?= (int)$post['id'] ?>" title="Bookmark">
                            <span class="material-symbols-outlined text-lg" style="font-variation-settings: 'FILL' <?= $isBookmarked ? 1 : 0 ?>;">bookmark</span>
                        </button>
                    </div>
                </article>

            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center p-12 bg-surface-container-low rounded-xl border border-outline-variant/30 flex flex-col items-center">
                <span class="material-symbols-outlined text-4xl text-outline mb-2">article</span>
                <h2 class="text-xl font-bold text-on-surface">No stories found</h2>
                <p class="text-on-surface-variant mt-2 text-sm max-w-sm">
                    <?= ($data['feed_type'] === 'following') ? "The writers you follow haven't published anything recently. Discover new voices in the 'For You' tab." : "Be the first to share a perspective with the community!" ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Short-form Note Modal (Substack / Twitter Style) -->
<?php if (isset($_SESSION['user_id'])): ?>
<div id="noteModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4" onclick="if(event.target === this) closeNoteModal();">
    <div class="w-full max-w-lg bg-surface-container-highest border border-outline-variant/30 rounded-2xl p-5 shadow-2xl relative">
        <form action="<?= BASEURL ?>/post/createNote" method="POST" class="flex flex-col gap-4">
            <!-- Modal Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center font-bold text-on-primary shrink-0 shadow-inner overflow-hidden">
                        <?php if (!empty($_SESSION['profile_picture'])): ?>
                            <img src="<?= BASEURL ?><?= htmlspecialchars($_SESSION['profile_picture']) ?>" alt="<?= htmlspecialchars($_SESSION['username'] ?? '') ?>" class="w-full h-full object-cover rounded-full">
                        <?php else: ?>
                            <?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <span class="font-bold text-on-surface text-base">
                        <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <a href="<?= BASEURL ?>/profile" class="text-xs font-semibold text-on-surface-variant hover:text-primary transition-colors">Drafts</a>
                    <button type="button" onclick="closeNoteModal()" class="w-7 h-7 rounded-full hover:bg-surface-container text-on-surface-variant hover:text-on-surface flex items-center justify-center transition-colors ml-1" title="Close">
                        <span class="material-symbols-outlined text-base">close</span>
                    </button>
                </div>
            </div>

            <!-- Modal Body (Transparent Textarea) -->
            <div>
                <textarea 
                    name="content" 
                    id="noteModalTextarea"
                    rows="4" 
                    placeholder="What's on your mind?" 
                    required 
                    class="bg-transparent border-none outline-none focus:ring-0 text-on-surface text-lg placeholder:text-outline-variant resize-none w-full p-0 leading-relaxed"
                ></textarea>
                <input type="file" id="noteImageInput" accept="image/*" class="hidden" multiple>
                <input type="hidden" name="cover_image" id="noteCoverImageInput" value="">
                <div id="noteImagePreviewContainer" class="hidden relative mt-3 w-full"></div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between pt-2 border-t border-outline-variant/20">
                <!-- Left side: Dummy Material Icons -->
                <div class="flex items-center gap-1.5 sm:gap-2 text-outline">
                    <button type="button" onclick="document.getElementById('noteImageInput').click()" class="p-1 rounded-full hover:bg-surface-container hover:text-primary transition-colors" title="Add Image">
                        <span class="material-symbols-outlined text-xl">image</span>
                    </button>
                    <button type="button" class="p-1 rounded-full hover:bg-surface-container hover:text-primary transition-colors" title="Add Video">
                        <span class="material-symbols-outlined text-xl">videocam</span>
                    </button>
                    <button type="button" class="p-1 rounded-full hover:bg-surface-container hover:text-primary transition-colors" title="Add Emoji">
                        <span class="material-symbols-outlined text-xl">mood</span>
                    </button>
                    <button type="button" class="p-1 rounded-full hover:bg-surface-container hover:text-primary transition-colors" title="Schedule">
                        <span class="material-symbols-outlined text-xl">calendar_month</span>
                    </button>
                </div>

                <!-- Right side: Cancel & Post Buttons -->
                <div class="flex items-center gap-2">
                    <button type="button" onclick="closeNoteModal()" class="text-sm font-medium text-on-surface-variant hover:text-on-surface transition-colors px-3 py-1.5 rounded-lg hover:bg-surface-container">Cancel</button>
                    <button type="submit" name="action" value="draft" class="text-sm font-medium text-primary hover:text-primary-fixed transition-colors px-3 py-1.5 rounded-lg hover:bg-surface-container border border-primary/30 active:scale-95">Draft</button>
                    <button type="submit" name="action" value="publish" class="bg-primary-container text-on-primary-container hover:bg-primary rounded-full px-6 py-2 font-bold shadow-md transition-all active:scale-95 text-sm">Post</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
/**
 * Short-form Note Modal controls
 */
function openNoteModal() {
    const modal = document.getElementById('noteModal');
    if (!modal) return;
    modal.classList.remove('hidden');
    const textarea = document.getElementById('noteModalTextarea');
    if (textarea) {
        setTimeout(() => textarea.focus(), 50);
    }
}

function closeNoteModal() {
    const modal = document.getElementById('noteModal');
    if (!modal) return;
    modal.classList.add('hidden');
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeNoteModal();
    }
});

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

<style>
    /* Mengatasi gaya dasar Quill HTML di Feed */
    .quill-content p { margin-bottom: 0.75rem; }
    .quill-content a { color: #4edea3; text-decoration: underline; }
    .quill-content strong { color: #dae2fd; }
    .quill-content blockquote { border-left: 3px solid #10b981; padding-left: 1rem; margin: 1rem 0; font-style: italic; }
</style>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>