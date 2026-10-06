<?php
/**
 * Profile Hover Card Partial
 * Renders a sleek Twitter/X-style hover preview card for an author.
 *
 * Variables passed from Profile::hoverCard:
 * @var array $user
 * @var bool $is_following
 * @var bool $is_owner
 * @var int|null $current_user_id
 */
$banner = !empty($user['banner_picture']) ? $user['banner_picture'] : null;
$avatar = !empty($user['profile_picture']) ? $user['profile_picture'] : null;
$initial = strtoupper(substr($user['username'] ?? 'U', 0, 1));
$profileUrl = BASEURL . '/' . urlencode($user['username'] ?? '');
?>
<div class="hover-card-popover w-72 rounded-2xl shadow-2xl bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/30 dark:border-slate-700/80 overflow-hidden text-left font-body-md select-none transition-all duration-200">
    <!-- 1. Banner Section -->
    <div class="h-20 w-full bg-surface-container-high relative overflow-hidden">
        <?php if ($banner): ?>
            <img src="<?= BASEURL ?><?= htmlspecialchars($banner) ?>" class="w-full h-full object-cover" alt="Banner">
        <?php else: ?>
            <div class="w-full h-full bg-gradient-to-r from-primary/30 via-primary/15 to-surface-container-high"></div>
        <?php endif; ?>
    </div>

    <!-- 2. Avatar & Follow Button Row -->
    <div class="px-4 relative flex justify-between items-end -mt-7 mb-2">
        <!-- Overlapping Avatar -->
        <a href="<?= $profileUrl ?>" class="w-14 h-14 rounded-full border-2 border-surface-container-lowest dark:border-slate-900 bg-surface-container-high overflow-hidden shrink-0 shadow-md relative z-10 block hover:opacity-90 transition-opacity">
            <?php if ($avatar): ?>
                <img src="<?= BASEURL ?><?= htmlspecialchars($avatar) ?>" class="w-full h-full object-cover" alt="Avatar">
            <?php else: ?>
                <div class="w-full h-full flex items-center justify-center font-bold text-primary text-xl bg-surface-container-low select-none">
                    <?= htmlspecialchars($initial) ?>
                </div>
            <?php endif; ?>
        </a>

        <!-- Follow / Action Button -->
        <div class="relative z-10 pt-2">
            <?php if (!empty($is_owner)): ?>
                <a href="<?= BASEURL ?>/profile/edit" class="btn-secondary px-3.5 py-1 rounded-full text-xs font-semibold">
                    Edit profile
                </a>
            <?php else: ?>
                <button type="button" 
                        class="follow-btn btn-follow btn-pill-follow <?= !empty($is_following) ? 'following' : '' ?> text-xs shrink-0" 
                        data-user-id="<?= (int)$user['id'] ?>" 
                        data-id="<?= (int)$user['id'] ?>" 
                        data-scope="card">
                    <?= !empty($is_following) ? 'Following' : 'Follow' ?>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- 3. Identity, Bio & Community Stats -->
    <div class="px-4 pb-4 flex flex-col gap-2">
        <!-- Name & @username -->
        <div class="min-w-0">
            <a href="<?= $profileUrl ?>" class="font-title-md font-bold text-sm text-on-surface hover:underline line-clamp-1 block leading-tight">
                <?= htmlspecialchars($user['name'] ?? $user['username'] ?? 'Anonymous') ?>
            </a>
            <span class="font-caption text-xs text-on-surface-variant line-clamp-1 block">
                @<?= htmlspecialchars($user['username'] ?? 'anon') ?>
            </span>
        </div>

        <!-- Bio (Clamped to 2-3 lines) -->
        <?php if (!empty($user['bio'])): ?>
            <p class="text-xs text-on-surface-variant font-body-md line-clamp-3 leading-relaxed whitespace-pre-line">
                <?= htmlspecialchars($user['bio']) ?>
            </p>
        <?php else: ?>
            <p class="text-xs text-on-surface-variant/60 font-body-md italic">
                No bio provided yet.
            </p>
        <?php endif; ?>

        <!-- Following & Followers Stats -->
        <div class="flex items-center gap-4 pt-1 text-xs border-t border-outline-variant/20 mt-1">
            <a href="<?= $profileUrl ?>" class="hover:underline flex items-center gap-1 text-on-surface-variant">
                <span class="font-bold text-on-surface"><?= number_format((int)($user['following_count'] ?? 0)) ?></span>
                <span>Following</span>
            </a>
            <a href="<?= $profileUrl ?>" class="hover:underline flex items-center gap-1 text-on-surface-variant">
                <span class="font-bold text-on-surface card-follower-count"><?= number_format((int)($user['follower_count'] ?? 0)) ?></span>
                <span>Followers</span>
            </a>
        </div>
    </div>
</div>
