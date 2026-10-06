<?php 
require_once __DIR__ . '/../templates/header.php'; 
$user = $data['user'] ?? [];
$currentUsername = $user['username'] ?? '';
$profileUrl = BASEURL . '/' . urlencode($currentUsername ?: 'profile');
$bannerPos = $user['banner_position'] ?? '50%';
$bannerPosNumeric = (int)filter_var($bannerPos, FILTER_SANITIZE_NUMBER_INT);
if ($bannerPosNumeric < 0 || $bannerPosNumeric > 100) {
    $bannerPosNumeric = 50;
}
?>

<div class="max-w-xl mx-auto py-8 px-4 sm:px-6 w-full">
    <!-- Header with Back Arrow and Title -->
    <div class="flex items-center gap-3.5 mb-6 pb-4 border-b border-outline-variant/30">
        <a href="<?= $profileUrl ?>" class="w-9 h-9 rounded-full bg-surface-container hover:bg-surface-container-high text-on-surface-variant hover:text-on-surface flex items-center justify-center transition-colors" title="Back to profile">
            <span class="material-symbols-outlined text-xl">arrow_back</span>
        </a>
        <div>
            <h1 class="font-title-md text-2xl font-bold text-on-surface tracking-tight">Edit Profile</h1>
            <p class="font-caption text-xs text-on-surface-variant mt-0.5">Customize your public presence, handle, and imagery</p>
        </div>
    </div>

    <!-- Edit Profile Form Card -->
    <div class="bg-surface-container-low border border-outline-variant/30 rounded-2xl p-6 sm:p-8 shadow-xl backdrop-blur-md">
        
        <!-- Flash Notifications -->
        <?php if (!empty($data['success'])): ?>
            <div class="mb-5 p-3.5 rounded-xl bg-primary/10 border border-primary/30 text-primary text-sm flex items-start gap-2.5 animate-fadeIn">
                <span class="material-symbols-outlined text-lg shrink-0 mt-0.5">check_circle</span>
                <span><?= htmlspecialchars($data['success']) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($data['error'])): ?>
            <div class="mb-5 p-3.5 rounded-xl bg-error-container/25 border border-error/40 text-error text-sm flex items-start gap-2.5 animate-fadeIn">
                <span class="material-symbols-outlined text-lg shrink-0 mt-0.5">error</span>
                <span><?= htmlspecialchars($data['error']) ?></span>
            </div>
        <?php endif; ?>

        <form id="editProfileForm" action="<?= BASEURL ?>/profile/update" method="POST" enctype="multipart/form-data" class="space-y-6">
            
            <!-- 1. Profile Banner Section -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <label class="block font-label-md text-xs uppercase tracking-wider text-on-surface-variant font-medium">
                        Profile Banner
                    </label>
                    <span class="font-caption text-[11px] text-on-surface-variant">Recommended 16:9 or 3:1 (Max 10MB)</span>
                </div>

                <div class="w-full h-36 rounded-xl border border-outline-variant/40 overflow-hidden bg-surface-container-high relative group shadow-inner">
                    <?php if (!empty($user['banner_picture'])): ?>
                        <img id="bannerPreview" 
                             src="<?= BASEURL ?><?= htmlspecialchars($user['banner_picture']) ?>" 
                             alt="Banner" 
                             class="w-full h-full object-cover transition-[object-position] duration-75"
                             style="object-position: center <?= htmlspecialchars($bannerPos) ?>;">
                    <?php else: ?>
                        <div id="bannerFallback" class="w-full h-full bg-gradient-to-r from-surface-container-lowest via-surface-container-high to-surface-container relative flex items-center justify-center">
                            <span class="material-symbols-outlined text-outline text-3xl">panorama</span>
                        </div>
                        <img id="bannerPreview" 
                             src="" 
                             alt="Banner Preview" 
                             class="w-full h-full object-cover hidden transition-[object-position] duration-75"
                             style="object-position: center 50%;">
                    <?php endif; ?>

                    <!-- Overlay hover controls -->
                    <div class="absolute inset-0 bg-surface/70 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2.5">
                        <label for="bannerInput" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-surface-container border border-outline-variant/60 text-on-surface hover:text-primary font-label-md text-xs cursor-pointer transition-all shadow-md">
                            <span class="material-symbols-outlined text-base">add_photo_alternate</span>
                            <span id="bannerLabelText"><?= !empty($user['banner_picture']) ? 'Change Banner' : 'Upload Banner' ?></span>
                        </label>
                        <?php if (!empty($user['banner_picture'])): ?>
                            <a href="<?= BASEURL ?>/profile/removeBanner" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-surface-container border border-error/50 text-error hover:bg-error-container/20 font-label-md text-xs transition-all shadow-md">
                                <span class="material-symbols-outlined text-base">delete</span>
                                <span>Remove</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Hidden file input & Mobile button fallback -->
                <div class="flex items-center justify-between pt-0.5">
                    <input type="file" name="banner" id="bannerInput" accept="image/png, image/jpeg, image/jpg, image/webp, image/gif" class="hidden">
                    <div class="flex sm:hidden items-center gap-2">
                        <label for="bannerInput" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-surface-container border border-outline-variant/50 text-xs text-on-surface cursor-pointer">
                            <span class="material-symbols-outlined text-sm">add_photo_alternate</span>
                            <span><?= !empty($user['banner_picture']) ? 'Change' : 'Upload' ?></span>
                        </label>
                        <?php if (!empty($user['banner_picture'])): ?>
                            <a href="<?= BASEURL ?>/profile/removeBanner" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-error/50 text-error text-xs">
                                <span class="material-symbols-outlined text-sm">delete</span>
                                <span>Remove</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Hidden Input for Form Submission -->
                <input type="hidden" name="banner_position" id="bannerPositionInput" value="<?= htmlspecialchars($bannerPos) ?>">

                <!-- Banner Position Controls Box -->
                <div id="bannerPositionControl" class="<?= empty($user['banner_picture']) ? 'hidden' : '' ?> bg-surface-container/60 border border-outline-variant/30 rounded-xl p-3.5 space-y-2.5 transition-all">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-medium text-on-surface flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm text-primary">vertical_align_center</span>
                            <span>Banner Vertical Alignment</span>
                        </span>
                        <span id="bannerPositionBadge" class="font-mono text-[11px] px-2.5 py-0.5 rounded-full bg-surface-container-high text-primary font-semibold border border-primary/20">
                            <?= htmlspecialchars($bannerPos) ?>
                        </span>
                    </div>

                    <!-- Range Slider -->
                    <div class="flex items-center gap-3">
                        <span class="text-[11px] text-on-surface-variant font-medium shrink-0">Top (0%)</span>
                        <input 
                            type="range" 
                            id="bannerPositionSlider" 
                            min="0" 
                            max="100" 
                            value="<?= $bannerPosNumeric ?>" 
                            class="w-full h-1.5 bg-surface-container-highest rounded-lg appearance-none cursor-pointer accent-primary focus:outline-none"
                        >
                        <span class="text-[11px] text-on-surface-variant font-medium shrink-0">Bottom (100%)</span>
                    </div>

                    <!-- Presets & Tips -->
                    <div class="flex items-center justify-between gap-2 pt-1 border-t border-outline-variant/20">
                        <span class="text-[11px] text-on-surface-variant">Presets:</span>
                        <div class="flex items-center gap-1.5">
                            <button type="button" class="banner-preset-btn px-2.5 py-1 text-[11px] font-medium rounded-lg bg-surface-container hover:bg-surface-container-high border border-outline-variant/40 text-on-surface transition-colors" data-pos="0%">Top</button>
                            <button type="button" class="banner-preset-btn px-2.5 py-1 text-[11px] font-medium rounded-lg bg-surface-container hover:bg-surface-container-high border border-outline-variant/40 text-on-surface transition-colors" data-pos="50%">Center</button>
                            <button type="button" class="banner-preset-btn px-2.5 py-1 text-[11px] font-medium rounded-lg bg-surface-container hover:bg-surface-container-high border border-outline-variant/40 text-on-surface transition-colors" data-pos="100%">Bottom</button>
                        </div>
                    </div>
                    <p class="text-[11px] text-on-surface-variant/80 italic">Drag slider or tap presets to adjust vertical alignment. Preview updates in real time.</p>
                </div>
            </div>

            <!-- 2. Profile Picture (Avatar) Input -->
            <div class="space-y-2 pt-3 border-t border-outline-variant/20">
                <label class="block font-label-md text-xs uppercase tracking-wider text-on-surface-variant font-medium">
                    Profile Picture (Avatar)
                </label>
                <div class="flex items-center gap-4">
                    <div class="w-20 h-20 rounded-full border-2 border-outline-variant/50 overflow-hidden bg-surface-container-high shrink-0 flex items-center justify-center relative shadow-md">
                        <?php if (!empty($user['profile_picture'])): ?>
                            <img id="avatarPreview" src="<?= BASEURL ?><?= htmlspecialchars($user['profile_picture']) ?>" alt="Avatar" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div id="avatarFallback" class="w-full h-full bg-primary flex items-center justify-center font-bold text-3xl text-on-primary select-none">
                                <?= strtoupper(substr($currentUsername ?: 'U', 0, 1)) ?>
                            </div>
                            <img id="avatarPreview" src="" alt="Avatar Preview" class="w-full h-full object-cover hidden">
                        <?php endif; ?>
                    </div>
                    <div class="flex-1">
                        <input type="file" name="avatar" id="avatarInput" accept="image/png, image/jpeg, image/jpg, image/webp, image/gif" class="hidden">
                        <label for="avatarInput" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-surface-container border border-outline-variant/50 hover:border-primary/50 text-on-surface hover:text-primary font-label-md text-xs cursor-pointer transition-all shadow-sm">
                            <span class="material-symbols-outlined text-base">photo_camera</span>
                            <span id="avatarLabelText"><?= !empty($user['profile_picture']) ? 'Change Avatar' : 'Upload New Avatar' ?></span>
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

            <!-- 3. Handle / Username Input -->
            <div class="space-y-1.5 pt-3 border-t border-outline-variant/20">
                <div class="flex items-center justify-between">
                    <label for="username" class="block font-label-md text-xs uppercase tracking-wider text-on-surface-variant font-medium">
                        Username / Handle <span class="text-error">*</span>
                    </label>
                    <span class="font-caption text-[11px] text-on-surface-variant">Unique web identifier</span>
                </div>
                <div class="relative flex items-center">
                    <span class="absolute left-3.5 text-on-surface-variant/80 font-bold text-sm pointer-events-none select-none">@</span>
                    <input 
                        type="text" 
                        name="username" 
                        id="username" 
                        maxlength="30"
                        minlength="3"
                        pattern="[a-zA-Z0-9_]{3,30}"
                        value="<?= htmlspecialchars($currentUsername) ?>" 
                        placeholder="yourhandle"
                        required
                        autocomplete="off"
                        spellcheck="false"
                        class="w-full pl-8 pr-4 py-2.5 bg-surface-container-lowest border border-outline-variant/40 rounded-xl font-mono text-on-surface text-sm placeholder:text-outline/60 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                    >
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 pt-0.5">
                    <p class="font-caption text-[11px] text-on-surface-variant">
                        Profile URL: <span class="text-primary font-mono select-all"><?= BASEURL ?>/@<span id="handlePreview"><?= htmlspecialchars($currentUsername) ?></span></span>
                    </p>
                    <span id="handleValidationHint" class="font-caption text-[11px] text-on-surface-variant">3–30 characters, letters, numbers & _</span>
                </div>
            </div>

            <!-- 4. Display Name Input -->
            <div class="space-y-1.5 pt-2 border-t border-outline-variant/20">
                <div class="flex items-center justify-between">
                    <label for="name" class="block font-label-md text-xs uppercase tracking-wider text-on-surface-variant font-medium">
                        Display Name
                    </label>
                    <span class="font-caption text-[11px] text-on-surface-variant"><span id="nameCharCount">0</span>/50</span>
                </div>
                <div class="relative flex items-center">
                    <span class="material-symbols-outlined absolute left-3.5 text-outline text-lg pointer-events-none">badge</span>
                    <input 
                        type="text" 
                        name="name" 
                        id="name" 
                        maxlength="50"
                        value="<?= htmlspecialchars($user['name'] ?? $currentUsername) ?>" 
                        placeholder="Your full or display name"
                        class="w-full pl-11 pr-4 py-2.5 bg-surface-container-lowest border border-outline-variant/40 rounded-xl font-body-md text-on-surface text-sm placeholder:text-outline/60 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                    >
                </div>
            </div>

            <!-- 5. Bio Textarea -->
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

            <!-- 6. Location Input -->
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
                        maxlength="100"
                        value="<?= htmlspecialchars($user['location'] ?? '') ?>" 
                        placeholder="e.g. San Francisco, CA or Remote"
                        class="w-full pl-11 pr-4 py-2.5 bg-surface-container-lowest border border-outline-variant/40 rounded-xl font-body-md text-on-surface text-sm placeholder:text-outline/60 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                    >
                </div>
            </div>

            <!-- 7. Profile Link Input -->
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
                        maxlength="255"
                        value="<?= htmlspecialchars($user['profile_link'] ?? '') ?>" 
                        placeholder="https://yourwebsite.com"
                        class="w-full pl-11 pr-4 py-2.5 bg-surface-container-lowest border border-outline-variant/40 rounded-xl font-body-md text-on-surface text-sm placeholder:text-outline/60 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                    >
                </div>
            </div>

            <!-- 8. Tipping Link Input -->
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
                        maxlength="255"
                        value="<?= htmlspecialchars($user['tipping_link'] ?? '') ?>" 
                        placeholder="https://buymeacoffee.com/username"
                        class="w-full pl-11 pr-4 py-2.5 bg-surface-container-lowest border border-outline-variant/40 rounded-xl font-body-md text-on-surface text-sm placeholder:text-outline/60 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                    >
                </div>
            </div>

            <!-- Form Actions Footer -->
            <div class="pt-6 border-t border-outline-variant/30 flex items-center justify-end gap-3">
                <a 
                    href="<?= $profileUrl ?>" 
                    class="btn-secondary px-5 py-2.5 text-sm"
                >
                    Cancel
                </a>
                <button 
                    type="submit" 
                    id="submitProfileBtn"
                    class="btn-primary px-6 py-2.5 text-sm flex items-center gap-2"
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
    // 1. Bio Character Counter
    const bioInput = document.getElementById('bio');
    const bioCount = document.getElementById('bioCharCount');
    if (bioInput && bioCount) {
        const updateBioCount = () => {
            bioCount.textContent = bioInput.value.length;
        };
        bioInput.addEventListener('input', updateBioCount);
        updateBioCount();
    }

    // 2. Name Character Counter
    const nameInput = document.getElementById('name');
    const nameCount = document.getElementById('nameCharCount');
    if (nameInput && nameCount) {
        const updateNameCount = () => {
            nameCount.textContent = nameInput.value.length;
        };
        nameInput.addEventListener('input', updateNameCount);
        updateNameCount();
    }

    // 3. Username / Handle Live Validation and Preview
    const usernameInput = document.getElementById('username');
    const handlePreview = document.getElementById('handlePreview');
    const handleHint = document.getElementById('handleValidationHint');
    const submitBtn = document.getElementById('submitProfileBtn');
    const originalUsername = <?= json_encode(strtolower($currentUsername)) ?>;

    if (usernameInput) {
        const validateUsername = () => {
            let val = usernameInput.value.trim();
            // Automatically strip leading @ if user typed it
            if (val.startsWith('@')) {
                val = val.substring(1);
                usernameInput.value = val;
            }
            handlePreview.textContent = val || '...';

            const regex = /^[a-zA-Z0-9_]{3,30}$/;
            if (!val) {
                if (handleHint) {
                    handleHint.textContent = 'Username is required.';
                    handleHint.className = 'font-caption text-[11px] text-error font-medium';
                }
                usernameInput.classList.add('border-error');
                if (submitBtn) submitBtn.disabled = true;
                return false;
            } else if (!regex.test(val)) {
                if (handleHint) {
                    if (val.length < 3) {
                        handleHint.textContent = 'Handle must be at least 3 characters.';
                    } else if (val.length > 30) {
                        handleHint.textContent = 'Handle cannot exceed 30 characters.';
                    } else {
                        handleHint.textContent = 'Only letters, numbers, and underscores allowed.';
                    }
                    handleHint.className = 'font-caption text-[11px] text-error font-medium';
                }
                usernameInput.classList.add('border-error');
                if (submitBtn) submitBtn.disabled = true;
                return false;
            } else {
                if (handleHint) {
                    if (val.toLowerCase() !== originalUsername) {
                        handleHint.textContent = 'Handle valid. Will be updated upon saving.';
                        handleHint.className = 'font-caption text-[11px] text-primary font-medium';
                    } else {
                        handleHint.textContent = 'Current handle (3–30 characters, letters, numbers & _)';
                        handleHint.className = 'font-caption text-[11px] text-on-surface-variant';
                    }
                }
                usernameInput.classList.remove('border-error');
                if (submitBtn) submitBtn.disabled = false;
                return true;
            }
        };

        usernameInput.addEventListener('input', validateUsername);
        validateUsername();
    }

    // 4. Banner Live Preview & Position Controller
    const bannerInput = document.getElementById('bannerInput');
    const bannerPreview = document.getElementById('bannerPreview');
    const bannerFallback = document.getElementById('bannerFallback');
    const bannerLabelText = document.getElementById('bannerLabelText');
    const bannerPositionInput = document.getElementById('bannerPositionInput');
    const bannerPositionSlider = document.getElementById('bannerPositionSlider');
    const bannerPositionBadge = document.getElementById('bannerPositionBadge');
    const bannerPositionControl = document.getElementById('bannerPositionControl');
    const presetButtons = document.querySelectorAll('.banner-preset-btn');

    const setBannerPosition = (val) => {
        let percentNum = parseInt(val, 10);
        if (isNaN(percentNum)) percentNum = 50;
        percentNum = Math.max(0, Math.min(100, percentNum));
        const formatted = percentNum + '%';

        if (bannerPositionInput) bannerPositionInput.value = formatted;
        if (bannerPositionSlider) bannerPositionSlider.value = percentNum;
        if (bannerPositionBadge) bannerPositionBadge.textContent = formatted;
        if (bannerPreview) {
            bannerPreview.style.objectPosition = `center ${formatted}`;
        }
    };

    if (bannerPositionSlider) {
        bannerPositionSlider.addEventListener('input', (e) => {
            setBannerPosition(e.target.value);
        });
    }

    presetButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const pos = btn.getAttribute('data-pos');
            setBannerPosition(pos);
        });
    });

    if (bannerInput) {
        bannerInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file && file.size > 10 * 1024 * 1024) {
                if (typeof showToast === 'function') {
                    showToast('File is too large! Maximum allowed size is 10MB.', 'error');
                } else {
                    alert('File is too large! Maximum allowed size is 10MB.');
                }
                e.target.value = '';
                return;
            }
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    if (bannerPreview) {
                        bannerPreview.src = event.target.result;
                        bannerPreview.classList.remove('hidden');
                        bannerPreview.style.objectPosition = `center ${bannerPositionInput ? bannerPositionInput.value : '50%'}`;
                    }
                    if (bannerFallback) bannerFallback.classList.add('hidden');
                    if (bannerLabelText) {
                        bannerLabelText.textContent = file.name.length > 18 ? file.name.substring(0, 15) + '...' : file.name;
                    }
                    if (bannerPositionControl) {
                        bannerPositionControl.classList.remove('hidden');
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // 5. Avatar Live Preview
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');
    const avatarFallback = document.getElementById('avatarFallback');
    const avatarLabelText = document.getElementById('avatarLabelText');

    if (avatarInput) {
        avatarInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file && file.size > 10 * 1024 * 1024) {
                if (typeof showToast === 'function') {
                    showToast('File is too large! Maximum allowed size is 10MB.', 'error');
                } else {
                    alert('File is too large! Maximum allowed size is 10MB.');
                }
                e.target.value = '';
                return;
            }
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    if (avatarPreview) {
                        avatarPreview.src = event.target.result;
                        avatarPreview.classList.remove('hidden');
                    }
                    if (avatarFallback) avatarFallback.classList.add('hidden');
                    if (avatarLabelText) {
                        avatarLabelText.textContent = file.name.length > 18 ? file.name.substring(0, 15) + '...' : file.name;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
