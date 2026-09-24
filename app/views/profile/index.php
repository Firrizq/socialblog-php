<?php 
require_once __DIR__ . '/../templates/header.php'; 
$user = $data['profile_user'] ?? null;
?>

<div class="flex flex-col w-full pb-20">
    <?php if (!$user): ?>
        <!-- User Not Found State -->
        <div class="text-center p-12 bg-surface-container-low border-b border-outline-variant/30">
            <span class="material-symbols-outlined text-outline text-5xl mb-3">person_off</span>
            <h1 class="font-title-md text-2xl font-bold text-on-surface">User Not Found</h1>
            <p class="font-body-md text-on-surface-variant text-sm mt-1 mb-6">
                The author profile you are looking for does not exist or may have been removed.
            </p>
            <a href="<?= BASEURL ?>/home" class="inline-flex items-center gap-2 px-space-md py-space-xs rounded-full bg-primary-container text-on-primary-container font-label-md hover:bg-primary transition-colors font-semibold shadow-sm">
                <span class="material-symbols-outlined text-base">west</span>
                <span>Back to Community Feed</span>
            </a>
        </div>
    <?php else: ?>
        <!-- Top Sticky Subheader (Twitter/X style) -->
        <div class="sticky top-0 z-30 bg-surface/90 backdrop-blur-md px-4 py-2 border-b border-outline-variant/30 flex items-center gap-6">
            <a href="<?= BASEURL ?>/home" class="w-9 h-9 rounded-full hover:bg-surface-container text-on-surface-variant hover:text-on-surface flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined text-xl">arrow_back</span>
            </a>
            <div class="min-w-0">
                <h1 class="font-title-md text-lg font-bold text-on-surface leading-tight truncate">
                    <?= htmlspecialchars($user['username']) ?>
                </h1>
                <p class="font-caption text-xs text-on-surface-variant">
                    <?= count($data['posts']) ?> <?= count($data['posts']) === 1 ? 'post' : 'posts' ?>
                </p>
            </div>
        </div>

        <!-- 1. Full-width Banner Image Area -->
        <div class="w-full h-48 sm:h-52 bg-surface-container-high relative overflow-hidden">
            <?php if (!empty($user['banner_picture'])): ?>
                <img src="<?= BASEURL ?><?= htmlspecialchars($user['banner_picture']) ?>" alt="Banner" class="w-full h-full object-cover">
            <?php else: ?>
                <!-- Default stylish Obsidian Emerald gradient banner -->
                <div class="w-full h-full bg-gradient-to-r from-surface-container-lowest via-surface-container-high to-surface-container relative">
                    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-primary/10 via-transparent to-transparent"></div>
                </div>
            <?php endif; ?>
        </div>

        <!-- 2. Profile Info Area (Overlapping Avatar & Action Button) -->
        <div class="px-4 sm:px-6">
            <div class="flex items-end justify-between -mt-16 sm:-mt-20 mb-3">
                <!-- Overlapping Avatar with thick border matching background -->
                <div class="relative w-28 h-28 sm:w-32 sm:h-32 rounded-full border-4 border-surface bg-surface-container-high overflow-hidden shrink-0 shadow-xl flex items-center justify-center">
                    <?php if (!empty($user['profile_picture'])): ?>
                        <img src="<?= BASEURL ?><?= htmlspecialchars($user['profile_picture']) ?>" alt="<?= htmlspecialchars($user['username']) ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="w-full h-full bg-primary flex items-center justify-center font-bold text-4xl sm:text-5xl text-on-primary select-none">
                            <?= strtoupper(substr($user['username'] ?? 'U', 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Action Button: Edit Profile vs Follow -->
                <div class="pb-1">
                    <?php if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$user['id']): ?>
                        <a href="<?= BASEURL ?>/profile/edit" class="px-5 py-1.5 rounded-full border border-outline-variant font-title-md text-sm font-semibold text-on-surface hover:bg-surface-container hover:border-primary transition-all">Edit profile</a>
                    <?php else: ?>
                        <?php $isFollowing = !empty($data['is_following']); ?>
                        <button class="btn-follow <?= $isFollowing ? 'px-5 py-1.5 rounded-full border border-outline-variant font-title-md text-sm font-semibold text-on-surface hover:border-error hover:text-error hover:bg-error-container/20 transition-all' : 'px-6 py-1.5 rounded-full bg-primary-container text-on-primary-container hover:bg-primary font-title-md text-sm font-semibold transition-all shadow-[0_0_0_1px_rgba(16,185,129,0.3)] active:scale-95' ?>" data-id="<?= (int)$user['id'] ?>">
                            <?= $isFollowing ? 'Following' : 'Follow' ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- User Names -->
            <div class="mt-1">
                <h2 class="font-headline-sm text-xl sm:text-2xl font-bold text-on-surface leading-tight tracking-tight">
                    <?= htmlspecialchars($user['username']) ?>
                </h2>
                <p class="font-body-md text-sm text-on-surface-variant font-normal">
                    @<?= htmlspecialchars($user['username']) ?>
                </p>
            </div>

            <!-- Bio -->
            <?php if (!empty($user['bio'])): ?>
                <p class="font-body-md text-sm text-on-surface mt-3 leading-relaxed">
                    <?= nl2br(htmlspecialchars($user['bio'])) ?>
                </p>
            <?php else: ?>
                <p class="font-body-md text-sm text-on-surface-variant/70 italic mt-3">
                    Writer and community contributor on Blogggle.
                </p>
            <?php endif; ?>

            <!-- Metadata Row (Location, Links, Joined Date) -->
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 mt-3.5 text-xs text-on-surface-variant font-caption">
                <?php if (!empty($user['location'])): ?>
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-base">location_on</span>
                        <span><?= htmlspecialchars($user['location']) ?></span>
                    </span>
                <?php endif; ?>
                
                <?php if (!empty($user['profile_link'])): ?>
                    <a href="<?= htmlspecialchars($user['profile_link']) ?>" target="_blank" rel="noopener noreferrer" class="flex items-center gap-1 text-primary hover:underline">
                        <span class="material-symbols-outlined text-base">link</span>
                        <span class="truncate max-w-[180px]"><?= htmlspecialchars(parse_url($user['profile_link'], PHP_URL_HOST) ?: $user['profile_link']) ?></span>
                    </a>
                <?php endif; ?>

                <?php if (!empty($user['tipping_link'])): ?>
                    <a href="<?= htmlspecialchars($user['tipping_link']) ?>" target="_blank" rel="noopener noreferrer" class="flex items-center gap-1 text-primary hover:underline">
                        <span class="material-symbols-outlined text-base">volunteer_activism</span>
                        <span>Support Author</span>
                    </a>
                <?php endif; ?>

                <span class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-base">calendar_month</span>
                    <span>Joined <?= date('F Y', strtotime($user['created_at'])) ?></span>
                </span>
            </div>

            <!-- Stats (Following and Followers) -->
            <div class="flex items-center gap-5 mt-3.5 text-sm pb-2">
                <div class="flex items-center gap-1">
                    <span class="font-bold text-on-surface font-title-md"><?= (int)($user['following_count'] ?? 0) ?></span>
                    <span class="text-on-surface-variant font-body-md text-xs sm:text-sm">Following</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="font-bold text-on-surface font-title-md" id="profile-follower-count"><?= (int)($user['follower_count'] ?? 0) ?></span>
                    <span class="text-on-surface-variant font-body-md text-xs sm:text-sm">Followers</span>
                </div>
            </div>
        </div>

        <!-- 3. Sticky Tab Navigation (Posts, Replies, Media) -->
        <div class="sticky top-12 z-20 bg-surface/90 backdrop-blur-md border-b border-outline-variant/30 flex text-center mt-2">
            <button class="flex-1 py-3.5 font-title-md text-sm text-on-surface hover:bg-surface-container-low/50 transition-colors relative font-semibold flex items-center justify-center">
                <span>Posts</span>
                <span class="absolute bottom-0 left-1/4 right-1/4 h-1 bg-primary rounded-full shadow-[0_0_8px_rgba(78,222,163,0.8)]"></span>
            </button>
            <button class="flex-1 py-3.5 font-title-md text-sm text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low/50 transition-colors font-medium">
                <span>Replies</span>
            </button>
            <button class="flex-1 py-3.5 font-title-md text-sm text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low/50 transition-colors font-medium">
                <span>Media</span>
            </button>
        </div>

        <!-- 4. Published Stories Stream (Border divide Twitter/X style) -->
        <?php if (!empty($data['posts']) && is_array($data['posts'])): ?>
            <div class="divide-y divide-outline-variant/30 flex flex-col">
                <?php foreach ($data['posts'] as $post): ?>
                    <article class="p-4 sm:p-5 hover:bg-surface-container-low/40 transition-colors flex flex-col gap-3">
                        <!-- Meta: Avatar, Name, Handle, Date, Read time -->
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center font-bold text-primary shrink-0 overflow-hidden">
                                    <?php if (!empty($user['profile_picture'])): ?>
                                        <img src="<?= BASEURL ?><?= htmlspecialchars($user['profile_picture']) ?>" alt="<?= htmlspecialchars($post['username'] ?? '') ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <?= htmlspecialchars(substr($post['username'] ?? 'U', 0, 1)) ?>
                                    <?php endif; ?>
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <span class="font-title-md text-on-surface font-semibold truncate"><?= htmlspecialchars($post['username'] ?? 'Anonymous') ?></span>
                                        <span class="font-caption text-on-surface-variant whitespace-nowrap">@<?= htmlspecialchars($post['username'] ?? 'anon') ?></span>
                                        <span class="font-caption text-on-surface-variant whitespace-nowrap">· <?= date('M j', strtotime($post['created_at'])) ?></span>
                                    </div>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-full bg-surface-container font-caption text-on-surface-variant text-xs"><?= $post['read_time_minutes'] ?? 1 ?> min read</span>
                        </div>

                        <!-- Content -->
                        <a href="<?= BASEURL ?>/post/detail/<?= (int)$post['id'] ?>" class="flex flex-col gap-1 cursor-pointer group text-decoration-none">
                            <?php if (!empty($post['title'])): ?>
                                <h3 class="font-headline-sm text-on-surface group-hover:text-primary transition-colors tracking-tight text-lg sm:text-xl font-bold">
                                    <?= htmlspecialchars($post['title']) ?>
                                </h3>
                            <?php endif; ?>
                            <div class="font-body-md text-on-surface-variant leading-relaxed text-sm sm:text-base line-clamp-3">
                                <?= strip_tags((string)($post['content'] ?? '')) ?>
                            </div>
                        </a>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-between pt-1 text-on-surface-variant max-w-md">
                            <a href="<?= BASEURL ?>/post/detail/<?= (int)$post['id'] ?>" class="flex items-center gap-1.5 hover:text-primary transition-colors">
                                <span class="material-symbols-outlined text-lg">chat_bubble</span>
                                <span class="font-caption text-xs"><?= $post['comment_count'] ?? 0 ?></span>
                            </a>
                            <button class="flex items-center gap-1.5 hover:text-primary transition-colors">
                                <span class="material-symbols-outlined text-lg">sync_alt</span>
                                <span class="font-caption text-xs"><?= $post['repost_count'] ?? 0 ?></span>
                            </button>
                            <?php 
                                $isLiked = in_array((int)$post['id'], $data['liked_posts'] ?? []);
                                $isBookmarked = in_array((int)$post['id'], $data['bookmarked_posts'] ?? []);
                            ?>
                            <button class="btn-like flex items-center gap-1.5 transition-colors <?= $isLiked ? 'text-primary' : 'hover:text-primary' ?> active:scale-95" data-id="<?= (int)$post['id'] ?>" title="Like">
                                <span class="material-symbols-outlined text-lg" style="font-variation-settings: 'FILL' <?= $isLiked ? 1 : 0 ?>;">favorite</span>
                                <span class="like-count font-caption text-xs font-semibold"><?= (int)($post['like_count'] ?? 0) ?></span>
                            </button>
                            <button class="btn-bookmark transition-colors <?= $isBookmarked ? 'text-primary' : 'hover:text-primary' ?> active:scale-95" data-id="<?= (int)$post['id'] ?>" title="Bookmark">
                                <span class="material-symbols-outlined text-lg" style="font-variation-settings: 'FILL' <?= $isBookmarked ? 1 : 0 ?>;">bookmark</span>
                            </button>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="text-center p-12 bg-transparent">
                <span class="material-symbols-outlined text-outline text-4xl mb-2">article</span>
                <h3 class="font-title-md text-base font-bold text-on-surface">No posts yet</h3>
                <p class="font-body-md text-on-surface-variant text-xs mt-1">
                    <?= (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$user['id']) 
                        ? "You haven't written any stories yet. Share your perspectives!" 
                        : "When @{$user['username']} publishes posts, they'll show up here." ?>
                </p>
                <?php if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$user['id']): ?>
                    <div class="mt-4">
                        <a href="<?= BASEURL ?>/post/create" class="inline-flex items-center gap-1.5 px-space-md py-space-xs rounded-full bg-primary-container text-on-primary-container font-label-md text-sm font-semibold hover:bg-primary transition-all">
                            <span class="material-symbols-outlined text-base">edit</span>
                            <span>Write a Story</span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
    /* Quill content formatting inside profile */
    .quill-content p { margin-bottom: 0.75rem; }
    .quill-content a { color: #4edea3; text-decoration: underline; }
    .quill-content strong { color: #dae2fd; }
    .quill-content blockquote { border-left: 3px solid #10b981; padding-left: 1rem; margin: 1rem 0; font-style: italic; }
</style>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
