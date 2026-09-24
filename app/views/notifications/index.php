<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="flex flex-col w-full px-4 sm:px-6 py-2">
    <!-- Notifications Page Sticky Header -->
    <div class="sticky top-16 z-30 bg-surface/90 backdrop-blur-md pb-space-xs pt-space-xs mb-space-lg flex items-center justify-between border-b border-outline-variant/30">
        <div class="flex items-center gap-space-sm py-2">
            <div class="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                <span class="material-symbols-outlined text-xl">notifications</span>
            </div>
            <div>
                <h1 class="font-headline-sm text-lg sm:text-xl font-bold text-on-surface tracking-tight">Notifications</h1>
                <p class="font-caption text-xs text-on-surface-variant">Stay updated on interactions with you and your stories</p>
            </div>
        </div>
        <?php if (!empty($data['notifications'])): ?>
            <div class="flex items-center gap-space-xs">
                <span class="px-space-md py-1 rounded-full bg-surface-container font-caption text-on-surface-variant text-xs font-semibold">
                    <?= count($data['notifications']) ?> <?= count($data['notifications']) === 1 ? 'notification' : 'notifications' ?>
                </span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Notifications List -->
    <div class="flex flex-col">
        <?php if (!empty($data['notifications']) && is_array($data['notifications'])): ?>
            <div class="bg-surface-container-low rounded-2xl border border-outline-variant/30 overflow-hidden shadow-sm flex flex-col">
                <?php foreach ($data['notifications'] as $notif): ?>
                    <?php
                        $actionText = '';
                        $icon = 'notifications';
                        $iconColor = 'text-primary';
                        $link = BASEURL . '/home';

                        switch ($notif['type']) {
                            case 'like_post':
                                $actionText = 'liked your post';
                                $icon = 'favorite';
                                $iconColor = 'text-error';
                                $link = !empty($notif['reference_id']) ? BASEURL . '/post/detail/' . (int)$notif['reference_id'] : BASEURL . '/home';
                                break;
                            case 'follow':
                                $actionText = 'started following you';
                                $icon = 'person_add';
                                $iconColor = 'text-primary';
                                $link = BASEURL . '/profile/user/' . urlencode($notif['actor_username'] ?? '');
                                break;
                            case 'comment':
                                $actionText = 'commented on your story';
                                $icon = 'chat_bubble';
                                $iconColor = 'text-tertiary';
                                $link = !empty($notif['reference_id']) ? BASEURL . '/post/detail/' . (int)$notif['reference_id'] : BASEURL . '/home';
                                break;
                            case 'reply':
                                $actionText = 'replied to your comment';
                                $icon = 'reply';
                                $iconColor = 'text-tertiary';
                                $link = !empty($notif['reference_id']) ? BASEURL . '/post/detail/' . (int)$notif['reference_id'] : BASEURL . '/home';
                                break;
                            case 'like_comment':
                                $actionText = 'liked your comment';
                                $icon = 'favorite';
                                $iconColor = 'text-error';
                                $link = !empty($notif['reference_id']) ? BASEURL . '/post/detail/' . (int)$notif['reference_id'] : BASEURL . '/home';
                                break;
                            case 'repost':
                                $actionText = 'reposted your story';
                                $icon = 'sync_alt';
                                $iconColor = 'text-primary';
                                $link = !empty($notif['reference_id']) ? BASEURL . '/post/detail/' . (int)$notif['reference_id'] : BASEURL . '/home';
                                break;
                            default:
                                $actionText = 'interacted with your content';
                                $icon = 'notifications';
                                $iconColor = 'text-primary';
                                $link = BASEURL . '/home';
                                break;
                        }
                    ?>
                    <a href="<?= $link ?>" class="p-4 sm:p-5 border-b border-outline-variant/20 hover:bg-surface-container-high/40 transition-colors flex items-center gap-4">
                        <!-- Left Icon: Avatar with Action Badge -->
                        <div class="relative shrink-0 w-11 h-11">
                            <div class="w-11 h-11 rounded-full bg-surface-container-high flex items-center justify-center font-bold text-primary shrink-0 overflow-hidden shadow-sm">
                                <?php if (!empty($notif['actor_profile_picture'])): ?>
                                    <img src="<?= BASEURL ?><?= htmlspecialchars($notif['actor_profile_picture']) ?>" alt="<?= htmlspecialchars($notif['actor_username'] ?? '') ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <?= strtoupper(substr($notif['actor_username'] ?? 'U', 0, 1)) ?>
                                <?php endif; ?>
                            </div>
                            <span class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-surface-container-highest border border-surface flex items-center justify-center <?= $iconColor ?> shadow-sm">
                                <span class="material-symbols-outlined text-[13px] leading-none" style="font-variation-settings: 'FILL' 1;"><?= $icon ?></span>
                            </span>
                        </div>

                        <!-- Notification Content -->
                        <div class="flex-1 min-w-0 flex flex-col gap-0.5">
                            <p class="font-body-md text-on-surface-variant text-sm sm:text-base leading-snug">
                                <span class="font-title-md font-bold text-on-surface hover:text-primary transition-colors"><?= htmlspecialchars($notif['actor_username'] ?? 'Someone') ?></span>
                                <span> <?= $actionText ?></span>
                            </p>
                            <span class="text-xs text-on-surface-variant font-caption"><?= date('M j, Y · g:i A', strtotime($notif['created_at'])) ?></span>
                        </div>

                        <span class="material-symbols-outlined text-outline-variant text-base shrink-0">chevron_right</span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Styled Empty State -->
            <div class="text-center p-12 bg-surface-container-low rounded-2xl border border-outline-variant/30 flex flex-col items-center justify-center gap-3 my-6 shadow-sm">
                <div class="w-16 h-16 rounded-full bg-surface-container flex items-center justify-center text-outline mb-2">
                    <span class="material-symbols-outlined text-3xl">notifications_off</span>
                </div>
                <h2 class="font-title-md text-xl font-bold text-on-surface">No notifications yet</h2>
                <p class="font-body-md text-on-surface-variant text-sm max-w-sm">
                    When someone likes your stories, leaves a comment, or follows you, you'll see it here.
                </p>
                <a href="<?= BASEURL ?>/home" class="mt-4 inline-flex items-center gap-2 px-space-lg py-2.5 rounded-full bg-primary-container text-on-primary-container font-title-md text-sm hover:bg-primary transition-all font-semibold shadow-[0_0_0_1px_rgba(16,185,129,0.3)] active:scale-95">
                    <span class="material-symbols-outlined text-base">explore</span>
                    <span>Explore Blogggle</span>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
