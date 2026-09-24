<?php 
require_once __DIR__ . '/../templates/header.php'; 
$user = $data['user'] ?? [];
?>

<div class="max-w-xl mx-auto py-8 px-4 sm:px-6 w-full">
    <!-- Header with Back Arrow and Title -->
    <div class="flex items-center gap-3.5 mb-6 pb-4 border-b border-outline-variant/30">
        <a href="<?= BASEURL ?>/profile" class="w-9 h-9 rounded-full bg-surface-container hover:bg-surface-container-high text-on-surface-variant hover:text-on-surface flex items-center justify-center transition-colors">
            <span class="material-symbols-outlined text-xl">arrow_back</span>
        </a>
        <div>
            <h1 class="font-title-md text-2xl font-bold text-on-surface tracking-tight">Edit Profile</h1>
            <p class="font-caption text-xs text-on-surface-variant mt-0.5">Manage your public information and media</p>
        </div>
    </div>

    <!-- Edit Profile Form Card -->
    <div class="bg-surface-container-low border border-outline-variant/30 rounded-2xl p-6 sm:p-8 shadow-xl backdrop-blur-md">
        
        <!-- Error Notification -->
        <?php if (!empty($data['error'])): ?>
            <div class="mb-5 p-3.5 rounded-xl bg-error-container/25 border border-error/40 text-error text-sm flex items-start gap-2.5">
                <span class="material-symbols-outlined text-lg shrink-0 mt-0.5">error</span>
                <span><?= htmlspecialchars($data['error']) ?></span>
            </div>
        <?php endif; ?>

        <form action="<?= BASEURL ?>/profile/update" method="POST" enctype="multipart/form-data" class="space-y-6">
            
            <!-- 1. Profile Banner Input -->
            <div class="space-y-2">
                <label class="block font-label-md text-xs uppercase tracking-wider text-on-surface-variant font-medium">
                    Profile Banner
                </label>
                <div class="w-full h-32 rounded-xl border border-outline-variant/40 overflow-hidden bg-surface-container-high relative group">
                    <?php if (!empty($user['banner_picture'])): ?>
                        <img id="bannerPreview" src="<?= BASEURL ?><?= htmlspecialchars($user['banner_picture']) ?>" alt="Banner" class="w-full h-full object-cover">
                    <?php else: ?>
                        <div id="bannerFallback" class="w-full h-full bg-gradient-to-r from-surface-container-lowest via-surface-container-high to-surface-container relative flex items-center justify-center">
                            <span class="material-symbols-outlined text-outline text-3xl">panorama</span>
                        </div>
                        <img id="bannerPreview" src="" alt="Banner Preview" class="w-full h-full object-cover hidden">
                    <?php endif; ?>
                    <div class="absolute inset-0 bg-surface/70 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                        <label for="bannerInput" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-surface-container border border-outline-variant/60 text-on-surface hover:text-primary font-label-md text-xs cursor-pointer transition-all shadow-md">
                            <span class="material-symbols-outlined text-base">add_photo_alternate</span>
                            <span id="bannerLabelText">Change Banner</span>
                        </label>
                    </div>
                </div>
                <div class="flex items-center justify-between pt-1">
                    <input type="file" name="banner" id="bannerInput" accept="image/png, image/jpeg, image/jpg, image/webp, image/gif" class="hidden">
                    <label for="bannerInput" class="sm:hidden inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-surface-container border border-outline-variant/50 text-xs text-on-surface cursor-pointer">
                        <span class="material-symbols-outlined text-sm">add_photo_alternate</span>
                        <span>Upload Banner</span>
                    </label>
                    <p class="font-caption text-[11px] text-on-surface-variant">Recommended: 16:9 or 3:1 landscape header image. Max 10MB.</p>
                </div>
            </div>

            <!-- 2. Profile Picture (Avatar) Input -->
            <div class="space-y-2 pt-2 border-t border-outline-variant/20">
                <label class="block font-label-md text-xs uppercase tracking-wider text-on-surface-variant font-medium">
                    Profile Picture (Avatar)
                </label>
                <div class="flex items-center gap-4">
                    <div class="w-20 h-20 rounded-full border-2 border-outline-variant/50 overflow-hidden bg-surface-container-high shrink-0 flex items-center justify-center relative shadow-md">
                        <?php if (!empty($user['profile_picture'])): ?>
                            <img id="avatarPreview" src="<?= BASEURL ?><?= htmlspecialchars($user['profile_picture']) ?>" alt="Avatar" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div id="avatarFallback" class="w-full h-full bg-primary flex items-center justify-center font-bold text-3xl text-on-primary select-none">
                                <?= strtoupper(substr($user['username'] ?? 'U', 0, 1)) ?>
                            </div>
                            <img id="avatarPreview" src="" alt="Avatar Preview" class="w-full h-full object-cover hidden">
                        <?php endif; ?>
                    </div>
                    <div class="flex-1">
                        <input type="file" name="avatar" id="avatarInput" accept="image/png, image/jpeg, image/jpg, image/webp, image/gif" class="hidden">
                        <label for="avatarInput" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-surface-container border border-outline-variant/50 hover:border-primary/50 text-on-surface hover:text-primary font-label-md text-xs cursor-pointer transition-all shadow-sm">
                            <span class="material-symbols-outlined text-base">photo_camera</span>
                            <span id="avatarLabelText">Upload New Avatar</span>
                        </label>
                        <?php if (!empty($user['profile_picture'])): ?>
                            <a href="<?= BASEURL ?>/profile/removeAvatar" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-error/50 text-error hover:bg-error-container/20 font-label-md text-xs transition-all shadow-sm ml-2">
                                <span class="material-symbols-outlined text-base">delete</span>
                                <span>Remove</span>
                            </a>
                        <?php endif; ?>
                        <p class="font-caption text-[11px] text-on-surface-variant mt-1.5">Recommended: Square JPG, PNG, or WebP. Max 10MB.</p>
                    </div>
                </div>
            </div>

            <!-- 3. Bio Textarea -->
            <div class="space-y-1.5 pt-2 border-t border-outline-variant/20">
                <div class="flex items-center justify-between">
                    <label for="bio" class="block font-label-md text-xs uppercase tracking-wider text-on-surface-variant font-medium">
                        Bio
                    </label>
                    <span class="font-caption text-[11px] text-on-surface-variant"><span id="bioCharCount">0</span>/250</span>
                </div>
                <textarea 
                    name="bio" 
                    id="bio" 
                    rows="3" 
                    maxlength="250" 
                    placeholder="Tell the community about yourself and your perspectives..."
                    class="w-full px-4 py-2.5 bg-surface-container-lowest border border-outline-variant/40 rounded-xl font-body-md text-on-surface text-sm placeholder:text-outline/60 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all resize-none leading-relaxed"
                ><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
            </div>

            <!-- 4. Location Input -->
            <div class="space-y-1.5">
                <label for="location" class="block font-label-md text-xs uppercase tracking-wider text-on-surface-variant font-medium">
                    Location
                </label>
                <div class="relative flex items-center">
                    <span class="material-symbols-outlined absolute left-3.5 text-outline text-lg pointer-events-none">location_on</span>
                    <input 
                        type="text" 
                        name="location" 
                        id="location" 
                        value="<?= htmlspecialchars($user['location'] ?? '') ?>" 
                        placeholder="e.g. San Francisco, CA or Remote"
                        class="w-full pl-11 pr-4 py-2.5 bg-surface-container-lowest border border-outline-variant/40 rounded-xl font-body-md text-on-surface text-sm placeholder:text-outline/60 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                    >
                </div>
            </div>

            <!-- 5. Profile Link Input -->
            <div class="space-y-1.5">
                <label for="profile_link" class="block font-label-md text-xs uppercase tracking-wider text-on-surface-variant font-medium">
                    Profile Website / Link
                </label>
                <div class="relative flex items-center">
                    <span class="material-symbols-outlined absolute left-3.5 text-outline text-lg pointer-events-none">link</span>
                    <input 
                        type="url" 
                        name="profile_link" 
                        id="profile_link" 
                        value="<?= htmlspecialchars($user['profile_link'] ?? '') ?>" 
                        placeholder="https://yourwebsite.com"
                        class="w-full pl-11 pr-4 py-2.5 bg-surface-container-lowest border border-outline-variant/40 rounded-xl font-body-md text-on-surface text-sm placeholder:text-outline/60 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                    >
                </div>
            </div>

            <!-- 6. Tipping Link Input -->
            <div class="space-y-1.5">
                <label for="tipping_link" class="block font-label-md text-xs uppercase tracking-wider text-on-surface-variant font-medium">
                    Tipping / Support Link
                </label>
                <div class="relative flex items-center">
                    <span class="material-symbols-outlined absolute left-3.5 text-outline text-lg pointer-events-none">volunteer_activism</span>
                    <input 
                        type="url" 
                        name="tipping_link" 
                        id="tipping_link" 
                        value="<?= htmlspecialchars($user['tipping_link'] ?? '') ?>" 
                        placeholder="https://buymeacoffee.com/username"
                        class="w-full pl-11 pr-4 py-2.5 bg-surface-container-lowest border border-outline-variant/40 rounded-xl font-body-md text-on-surface text-sm placeholder:text-outline/60 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                    >
                </div>
            </div>

            <!-- Form Actions Footer -->
            <div class="pt-6 border-t border-outline-variant/30 flex items-center justify-end gap-3">
                <a 
                    href="<?= BASEURL ?>/profile" 
                    class="px-5 py-2.5 rounded-xl border border-outline-variant font-title-md text-sm font-semibold text-on-surface-variant hover:text-on-surface hover:bg-surface-container transition-all"
                >
                    Cancel
                </a>
                <button 
                    type="submit" 
                    class="px-6 py-2.5 rounded-xl bg-primary-container text-on-primary-container font-title-md text-sm font-semibold hover:bg-primary transition-all shadow-[0_0_0_1px_rgba(16,185,129,0.3)] active:scale-95 flex items-center gap-2"
                >
                    <span class="material-symbols-outlined text-lg">check</span>
                    <span>Save Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Bio character counter
    const bioInput = document.getElementById('bio');
    const bioCount = document.getElementById('bioCharCount');
    if (bioInput && bioCount) {
        const updateCount = () => {
            bioCount.textContent = bioInput.value.length;
        };
        bioInput.addEventListener('input', updateCount);
        updateCount();
    }

    // Avatar live preview
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');
    const avatarFallback = document.getElementById('avatarFallback');
    const avatarLabelText = document.getElementById('avatarLabelText');

    if (avatarInput) {
        avatarInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file && file.size > 10 * 1024 * 1024) {
                alert('File is too large! Maximum allowed size is 10MB.');
                e.target.value = '';
                return;
            }
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    avatarPreview.src = event.target.result;
                    avatarPreview.classList.remove('hidden');
                    if (avatarFallback) avatarFallback.classList.add('hidden');
                    if (avatarLabelText) {
                        avatarLabelText.textContent = file.name.length > 18 ? file.name.substring(0, 15) + '...' : file.name;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Banner live preview
    const bannerInput = document.getElementById('bannerInput');
    const bannerPreview = document.getElementById('bannerPreview');
    const bannerFallback = document.getElementById('bannerFallback');
    const bannerLabelText = document.getElementById('bannerLabelText');

    if (bannerInput) {
        bannerInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file && file.size > 10 * 1024 * 1024) {
                alert('File is too large! Maximum allowed size is 10MB.');
                e.target.value = '';
                return;
            }
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    bannerPreview.src = event.target.result;
                    bannerPreview.classList.remove('hidden');
                    if (bannerFallback) bannerFallback.classList.add('hidden');
                    if (bannerLabelText) {
                        bannerLabelText.textContent = file.name.length > 18 ? file.name.substring(0, 15) + '...' : file.name;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
