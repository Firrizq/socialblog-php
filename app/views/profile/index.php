<?php 
require_once __DIR__ . '/../templates/header.php'; 
$user = $data['profile_user'] ?? $data['user'] ?? null;
$data['user'] = $user;
$profileUsername = $user['username'] ?? $data['profile_user']['username'] ?? '';
?>

<div class="flex flex-col w-full pb-20">
    <?php if (!$user): ?>
        <!-- User Not Found State -->
        <div class="text-center p-12 bg-surface-container-low border border-outline-variant/30 rounded-2xl my-4">
            <span class="material-symbols-outlined text-outline text-5xl mb-3">person_off</span>
            <h1 class="font-title-md text-2xl font-bold text-on-surface">User Not Found</h1>
            <p class="font-body-md text-on-surface-variant text-sm mt-1 mb-6">
                The author profile you are looking for does not exist or may have been removed.
            </p>
            <a href="<?= BASEURL ?>/home" class="btn-primary px-5 py-2 text-sm">
                <span class="material-symbols-outlined text-base">west</span>
                <span>Back to Community Feed</span>
            </a>
        </div>
    <?php else: ?>
        <!-- Sticky Subheader -->
        <div class="sticky top-0 z-30 bg-surface/90 backdrop-blur-md px-4 py-2.5 border-b border-outline-variant/30 flex items-center gap-4">
            <button onclick="history.back()" class="w-9 h-9 rounded-full hover:bg-surface-container text-on-surface-variant hover:text-on-surface flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined text-xl">arrow_back</span>
            </button>
            <div class="min-w-0">
                <h1 class="font-title-md text-lg font-bold text-on-surface leading-tight truncate">
                    <?= htmlspecialchars($data['user']['name'] ?? $data['user']['username'] ?? 'Profile') ?>
                </h1>
                <p class="font-caption text-xs text-on-surface-variant">
                    <?php if (($data['active_tab'] ?? 'posts') === 'replies'): ?>
                        <?= count($data['replies'] ?? []) ?> <?= count($data['replies'] ?? []) === 1 ? 'reply' : 'replies' ?>
                    <?php elseif (($data['active_tab'] ?? 'posts') === 'media'): ?>
                        <?= count($data['media_posts'] ?? []) ?> <?= count($data['media_posts'] ?? []) === 1 ? 'media item' : 'media items' ?>
                    <?php elseif (($data['active_tab'] ?? 'posts') === 'reposts'): ?>
                        <?= count($data['posts'] ?? []) ?> <?= count($data['posts'] ?? []) === 1 ? 'repost' : 'reposts' ?>
                    <?php else: ?>
                        <?= count($data['posts'] ?? []) ?> <?= count($data['posts'] ?? []) === 1 ? 'post' : 'posts' ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <!-- 1. Hero Section -->
        <div class="bg-surface-container-lowest border-x border-t border-outline-variant/30 rounded-t-2xl overflow-hidden mt-2 relative">
            <!-- Banner (Gradient Fallback) -->
            <div class="h-32 sm:h-48 w-full bg-surface-container-high relative">
                <?php if (!empty($data['user']['banner_picture'])): ?>
                    <img src="<?= BASEURL ?><?= htmlspecialchars($data['user']['banner_picture']) ?>" class="w-full h-full object-cover">
                <?php else: ?>
                    <div class="w-full h-full bg-gradient-to-r from-primary/20 to-primary/5"></div>
                <?php endif; ?>
            </div>

            <!-- Avatar & Actions Row -->
            <div class="px-4 sm:px-5 relative flex justify-end items-start pt-3">
                <!-- Overlapping Avatar -->
                <div class="absolute -top-12 sm:-top-16 left-4 sm:left-5 w-24 h-24 sm:w-32 sm:h-32 rounded-full border-4 border-surface-container-lowest bg-surface-container-high overflow-hidden shrink-0 z-10 shadow-lg">
                    <?php if (!empty($data['user']['profile_picture'])): ?>
                        <img src="<?= BASEURL ?><?= htmlspecialchars($data['user']['profile_picture']) ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center font-bold text-primary text-3xl sm:text-4xl bg-surface-container-low select-none">
                            <?= htmlspecialchars(substr($data['user']['username'] ?? 'U', 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Edit Profile Button / Follow Button -->
                <?php if (!empty($data['is_owner']) || (isset($_SESSION['active_user_id']) && (int)$_SESSION['active_user_id'] === (int)($data['user']['id'] ?? 0)) || (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)($data['user']['id'] ?? 0))): ?>
                    <a href="<?= BASEURL ?>/profile/edit" class="btn-secondary px-5 py-1.5 text-xs rounded-full font-bold relative z-10">
                        Edit profile
                    </a>
                <?php elseif (!empty($data['user']['id'])): ?>
                    <?php $isFollowing = !empty($data['is_following']); ?>
                    <button id="profile-follow-btn" 
                            type="button" 
                            class="follow-btn btn-follow btn-pill-follow <?= $isFollowing ? 'following' : '' ?> text-sm px-6 py-1.5 relative z-10" 
                            data-user-id="<?= (int)$data['user']['id'] ?>" 
                            data-id="<?= (int)$data['user']['id'] ?>" 
                            data-scope="profile">
                        <?= $isFollowing ? 'Following' : 'Follow' ?>
                    </button>
                <?php endif; ?>
            </div>

            <!-- Identity & Bio -->
            <div class="px-4 sm:px-5 mt-2 sm:mt-4 pb-4">
                <h1 class="text-xl sm:text-2xl font-black text-on-surface leading-tight tracking-tight">
                    <?= htmlspecialchars($data['user']['name'] ?? $data['user']['username'] ?? 'Anonymous') ?>
                </h1>
                <p class="text-on-surface-variant font-body-md text-sm sm:text-[15px]">
                    @<?= htmlspecialchars($data['user']['username'] ?? 'anon') ?>
                </p>
                
                <?php if (!empty($data['user']['bio'])): ?>
                    <div class="mt-3 text-on-surface font-body-md text-[15px] leading-relaxed">
                        <?= htmlspecialchars($data['user']['bio']) ?>
                    </div>
                <?php endif; ?>

                <!-- Metadata Row 1: Date, Location, Link -->
                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 mt-3 text-on-surface-variant font-body-md text-sm">
                    <div class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px] opacity-80">calendar_month</span>
                        <span>Joined <?= date('F Y', strtotime($data['user']['created_at'] ?? 'now')) ?></span>
                    </div>
                    
                    <?php if (!empty($data['user']['location'])): ?>
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px] opacity-80">location_on</span>
                            <span><?= htmlspecialchars($data['user']['location']) ?></span>
                        </div>
                    <?php endif; ?>

                    <?php 
                        $website = !empty($data['user']['website']) ? $data['user']['website'] : ($data['user']['profile_link'] ?? '');
                    ?>
                    <?php if (!empty($website)): ?>
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px] opacity-80">link</span>
                            <a href="<?= htmlspecialchars(str_starts_with($website, 'http') ? $website : 'https://' . $website) ?>" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline">
                                <?= htmlspecialchars(str_replace(['http://', 'https://', 'www.'], '', $website)) ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($data['user']['tipping_link'])): ?>
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px] opacity-80">volunteer_activism</span>
                            <a href="<?= htmlspecialchars(str_starts_with($data['user']['tipping_link'], 'http') ? $data['user']['tipping_link'] : 'https://' . $data['user']['tipping_link']) ?>" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline">
                                Support
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Metadata Row 2: Following / Followers -->
                <div class="flex flex-wrap items-center gap-4 mt-3 text-sm text-on-surface-variant font-body-md">
                    <button type="button" onclick="openFollowModal('following', '<?= htmlspecialchars($profileUsername) ?>')" class="hover:underline flex gap-1 cursor-pointer">
                        <span class="font-bold text-on-surface"><?= number_format($data['user']['following_count'] ?? 0) ?></span> Following
                    </button>
                    <button type="button" onclick="openFollowModal('followers', '<?= htmlspecialchars($profileUsername) ?>')" class="hover:underline flex gap-1 cursor-pointer">
                        <span class="font-bold text-on-surface" id="profile-follower-count"><?= number_format($data['user']['follower_count'] ?? 0) ?></span> Followers
                    </button>
                </div>
            </div>

            <!-- Tabs Navigation (Classic Full-Width Border) -->
            <?php 
                $activeTab = $data['active_tab'] ?? 'posts';
                $profileUsername = $user['username'] ?? $data['profile_user']['username'] ?? '';
            ?>
            <div class="flex border-b border-outline-variant/30 w-full mt-2">
                <a href="<?= BASEURL ?>/<?= urlencode($profileUsername) ?>?tab=posts" class="flex-1 flex justify-center hover:bg-surface-container-low transition-colors cursor-pointer group">
                    <div class="w-full text-center py-3.5 font-title-md text-[15px] <?= $activeTab === 'posts' ? 'font-bold text-on-surface border-b-4 border-primary' : 'font-medium text-on-surface-variant border-b-4 border-transparent group-hover:text-on-surface' ?> transition-colors">
                        Posts
                    </div>
                </a>
                <a href="<?= BASEURL ?>/<?= urlencode($profileUsername) ?>?tab=replies" class="flex-1 flex justify-center hover:bg-surface-container-low transition-colors cursor-pointer group">
                    <div class="w-full text-center py-3.5 font-title-md text-[15px] <?= $activeTab === 'replies' ? 'font-bold text-on-surface border-b-4 border-primary' : 'font-medium text-on-surface-variant border-b-4 border-transparent group-hover:text-on-surface' ?> transition-colors">
                        Replies
                    </div>
                </a>
                <a href="<?= BASEURL ?>/<?= urlencode($profileUsername) ?>?tab=reposts" class="flex-1 flex justify-center hover:bg-surface-container-low transition-colors cursor-pointer group">
                    <div class="w-full text-center py-3.5 font-title-md text-[15px] <?= $activeTab === 'reposts' ? 'font-bold text-on-surface border-b-4 border-primary' : 'font-medium text-on-surface-variant border-b-4 border-transparent group-hover:text-on-surface' ?> transition-colors">
                        Reposts
                    </div>
                </a>
                <a href="<?= BASEURL ?>/<?= urlencode($profileUsername) ?>?tab=media" class="flex-1 flex justify-center hover:bg-surface-container-low transition-colors cursor-pointer group">
                    <div class="w-full text-center py-3.5 font-title-md text-[15px] <?= $activeTab === 'media' ? 'font-bold text-on-surface border-b-4 border-primary' : 'font-medium text-on-surface-variant border-b-4 border-transparent group-hover:text-on-surface' ?> transition-colors">
                        Media
                    </div>
                </a>
                <?php if (!empty($data['is_owner'])): ?>
                    <a href="<?= BASEURL ?>/<?= urlencode($profileUsername) ?>?tab=drafts" class="flex-1 flex justify-center hover:bg-surface-container-low transition-colors cursor-pointer group">
                        <div class="w-full text-center py-3.5 font-title-md text-[15px] <?= $activeTab === 'drafts' ? 'font-bold text-on-surface border-b-4 border-primary' : 'font-medium text-on-surface-variant border-b-4 border-transparent group-hover:text-on-surface' ?> transition-colors flex items-center justify-center gap-1.5">
                            <span>Drafts</span>
                            <?php if(!empty($data['drafts']) && count($data['drafts']) > 0): ?>
                                <span class="px-1.5 py-0.5 rounded-full bg-surface-container text-xs font-semibold"><?= count($data['drafts']) ?></span>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($activeTab === 'replies'): ?>
            <!-- Replies Stream (Twitter-Style Reply Thread) -->
            <div class="flex flex-col divide-y divide-outline-variant/30 border-b border-outline-variant/30">
                <?php if (!empty($data['replies']) && is_array($data['replies'])): ?>
                    <?php foreach ($data['replies'] as $reply): ?>
                        <?php
                            $replyPostType = strtolower($reply['post_type'] ?? 'story');
                            $replyPostUid = !empty($reply['post_uid']) ? $reply['post_uid'] : $reply['post_id'];
                            $replyAuthorUrl = BASEURL . '/' . urlencode($reply['author_username'] ?? '');
                            $replierUrl = BASEURL . '/' . urlencode($reply['replier_username'] ?? '');
                            $replyPostUrl = BASEURL . '/' . urlencode($reply['author_username'] ?? '') . '/' . $replyPostType . '/' . $replyPostUid;
                            $replyCommentUrl = !empty($reply['uid']) ? (BASEURL . '/' . urlencode($reply['replier_username'] ?? '') . '/comment/' . $reply['uid']) : $replyPostUrl;
                        ?>
                        <article class="p-4 sm:p-5 hover:bg-surface-container-lowest/40 transition-colors flex flex-col cursor-pointer" onclick="if(!event.target.closest('a') && !event.target.closest('button')) window.location.href='<?= $replyCommentUrl ?>';">
                            <!-- Replying Context Banner -->
                            <div class="flex items-center gap-1.5 text-xs font-semibold text-on-surface-variant mb-2 ml-10 sm:ml-16">
                                <span class="material-symbols-outlined text-[15px] text-primary">reply</span>
                                <span>Replying to</span>
                                <a href="<?= $replyAuthorUrl ?>" class="font-bold text-primary hover:underline relative z-10" onclick="event.stopPropagation();">
                                    @<?= htmlspecialchars($reply['author_username'] ?? 'author') ?>
                                </a>
                            </div>

                            <div class="flex gap-3 sm:gap-4 w-full">
                                <!-- Left Column: Replier Avatar -->
                                <div class="shrink-0">
                                    <a href="<?= $replierUrl ?>" class="block w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-surface-container-high flex items-center justify-center font-bold text-primary hover:opacity-80 transition-opacity overflow-hidden relative z-10" title="View Profile" onclick="event.stopPropagation();">
                                        <?php if (!empty($reply['replier_profile_picture'])): ?>
                                            <img src="<?= BASEURL ?><?= htmlspecialchars($reply['replier_profile_picture']) ?>" alt="<?= htmlspecialchars($reply['replier_username'] ?? '') ?>" class="w-full h-full object-cover rounded-full avatar-img">
                                        <?php else: ?>
                                            <?= htmlspecialchars(substr($reply['replier_username'] ?? 'U', 0, 1)) ?>
                                        <?php endif; ?>
                                    </a>
                                </div>

                                <!-- Right Column: Reply Content & Quoted Reference Card -->
                                <div class="flex-1 min-w-0 flex flex-col">
                                    <!-- Replier Header (Name, Username, Time) -->
                                    <div class="flex items-center justify-between gap-2 mb-1">
                                        <div class="flex items-center gap-1.5 min-w-0 flex-wrap text-[15px]">
                                            <a href="<?= $replierUrl ?>" class="font-title-md font-bold text-on-surface hover:underline truncate relative z-10" onclick="event.stopPropagation();">
                                                <?= htmlspecialchars($reply['replier_name'] ?? $reply['replier_username'] ?? 'Anonymous') ?>
                                            </a>
                                            <span class="font-body-md text-on-surface-variant truncate">@<?= htmlspecialchars($reply['replier_username'] ?? 'anon') ?></span>
                                            <span class="text-on-surface-variant font-bold">·</span>
                                            <time class="timeago font-body-md text-on-surface-variant hover:underline relative z-10" datetime="<?= date('c', strtotime($reply['created_at'])) ?>"></time>
                                        </div>
                                    </div>

                                    <!-- User's Reply Text -->
                                    <div class="font-body-md text-on-surface text-[15px] leading-relaxed whitespace-pre-line mb-2">
                                        <?= preg_replace('/(^|>|\s)#([a-zA-Z_][a-zA-Z0-9_]*)/', '$1<a href="' . BASEURL . '/explore/tag/$2" class="text-primary font-semibold hover:underline relative z-10" onclick="event.stopPropagation();">#$2</a>', htmlspecialchars((string)($reply['comment'] ?? ''))) ?>
                                    </div>

                                    <!-- Embedded Quoted / Reference Card of Original Post -->
                                    <div class="rounded-2xl border border-outline-variant/40 hover:border-primary/40 bg-surface-container-lowest/70 hover:bg-surface-container-low/40 p-3 sm:p-3.5 transition-all relative z-10 cursor-pointer flex gap-3 items-center justify-between mt-1 mb-2" onclick="event.stopPropagation(); window.location.href='<?= $replyPostUrl ?>';">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-1">
                                                <div class="w-5 h-5 rounded-full bg-surface-container-high flex items-center justify-center font-bold text-[10px] text-primary overflow-hidden shrink-0">
                                                    <?php if (!empty($reply['author_profile_picture'])): ?>
                                                        <img src="<?= BASEURL ?><?= htmlspecialchars($reply['author_profile_picture']) ?>" alt="<?= htmlspecialchars($reply['author_username'] ?? '') ?>" class="w-full h-full object-cover rounded-full avatar-img">
                                                    <?php else: ?>
                                                        <?= htmlspecialchars(substr($reply['author_username'] ?? 'U', 0, 1)) ?>
                                                    <?php endif; ?>
                                                </div>
                                                <span class="font-bold text-xs text-on-surface truncate"><?= htmlspecialchars($reply['author_name'] ?? $reply['author_username'] ?? 'Author') ?></span>
                                                <span class="text-xs text-on-surface-variant truncate">@<?= htmlspecialchars($reply['author_username'] ?? 'author') ?></span>
                                                <span class="text-on-surface-variant text-xs">·</span>
                                                <time class="timeago text-xs text-on-surface-variant" datetime="<?= date('c', strtotime($reply['post_created_at'])) ?>"></time>
                                            </div>
                                            <?php if (!empty($reply['post_title'])): ?>
                                                <h4 class="font-title-md font-bold text-sm text-on-surface line-clamp-1 mb-0.5">
                                                    <?= htmlspecialchars($reply['post_title']) ?>
                                                </h4>
                                            <?php endif; ?>
                                            <p class="font-body-md text-xs sm:text-[13px] text-on-surface-variant line-clamp-2 leading-relaxed">
                                                <?= htmlspecialchars(strip_tags((string)($reply['post_content'] ?? ''))) ?>
                                            </p>
                                        </div>

                                        <?php
                                            $origThumb = '';
                                            if (!empty($reply['post_cover_image'])) {
                                                $origDecoded = json_decode($reply['post_cover_image'], true);
                                                if (is_array($origDecoded) && !empty($origDecoded)) {
                                                    $origThumb = $origDecoded[0];
                                                } else {
                                                    $origThumb = explode(',', $reply['post_cover_image'])[0];
                                                }
                                            } elseif (!empty($reply['post_content']) && preg_match('/<img[^>]+src=[\'"]([^\'"]+)[\'"]/i', $reply['post_content'], $origMatch)) {
                                                $origThumb = $origMatch[1];
                                            }
                                        ?>
                                        <?php if (!empty($origThumb)): ?>
                                            <?php
                                                $origThumbSrc = (str_starts_with($origThumb, 'http://') || str_starts_with($origThumb, 'https://'))
                                                    ? $origThumb
                                                    : ((str_starts_with($origThumb, '/uploads/') || str_starts_with($origThumb, 'uploads/'))
                                                        ? (BASEURL . '/' . ltrim($origThumb, '/'))
                                                        : (BASEURL . (str_starts_with($origThumb, '/') ? $origThumb : '/uploads/' . ltrim($origThumb, '/'))));
                                                $thumbExt = strtolower(pathinfo((string)$origThumb, PATHINFO_EXTENSION));
                                                $videoExts = ['mp4', 'webm', 'ogg', 'mov'];
                                                $isThumbVideo = in_array($thumbExt, $videoExts, true);
                                            ?>
                                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl overflow-hidden shrink-0 bg-surface-container-high border border-outline-variant/30 flex items-center justify-center bg-black">
                                                <?php if ($isThumbVideo): ?>
                                                    <video src="<?= htmlspecialchars($origThumbSrc) ?>" class="w-full h-full object-cover" muted playsinline></video>
                                                <?php else: ?>
                                                    <img src="<?= htmlspecialchars($origThumbSrc) ?>" alt="Attachment" class="w-full h-full object-cover">
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Bottom Action Bar / View Conversation -->
                                    <div class="flex items-center gap-4 mt-1 text-on-surface-variant text-xs font-medium">
                                        <a href="<?= $replyCommentUrl ?>" class="hover:text-primary flex items-center gap-1.5 transition-colors relative z-10" onclick="event.stopPropagation();">
                                            <span class="material-symbols-outlined text-[16px]">chat_bubble_outline</span>
                                            <span>View conversation</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Empty State for Replies -->
                    <div class="text-center p-12 bg-transparent flex flex-col items-center">
                        <span class="material-symbols-outlined text-4xl text-outline mb-2">chat_bubble_outline</span>
                        <h2 class="text-xl font-bold text-on-surface">No replies yet</h2>
                        <p class="text-on-surface-variant mt-2 text-sm max-w-sm">
                            When @<?= htmlspecialchars($profileUsername ?? 'user') ?> replies to stories or notes, they will appear here.
                        </p>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($activeTab === 'media'): ?>
            <!-- Media Tab Grid (Instagram/Twitter Media Grid) -->
            <div class="p-3 sm:p-4 border-b border-outline-variant/30">
                <?php if (!empty($data['media_posts']) && is_array($data['media_posts'])): ?>
                    <div class="grid grid-cols-3 gap-1 sm:gap-2">
                        <?php foreach ($data['media_posts'] as $post): ?>
                            <?php
                                $mediaImgs = [];
                                if (!empty($post['cover_image'])) {
                                    $decoded = json_decode($post['cover_image'], true);
                                    if (is_array($decoded) && !empty($decoded)) {
                                        $mediaImgs = array_values(array_filter($decoded));
                                    } elseif (is_string($post['cover_image'])) {
                                        $mediaImgs = array_values(array_filter(explode(',', $post['cover_image'])));
                                    }
                                }
                                if (empty($mediaImgs) && !empty($post['content']) && preg_match_all('/<img[^>]+src=[\'"]([^\'"]+)[\'"]/i', $post['content'], $matches)) {
                                    $mediaImgs = array_values(array_filter($matches[1]));
                                }

                                $firstImg = !empty($mediaImgs) ? $mediaImgs[0] : '';
                                if (empty($firstImg)) continue;

                                $fullFirstImg = (str_starts_with($firstImg, 'http://') || str_starts_with($firstImg, 'https://'))
                                    ? $firstImg
                                    : ((str_starts_with($firstImg, '/uploads/') || str_starts_with($firstImg, 'uploads/'))
                                        ? (BASEURL . '/' . ltrim($firstImg, '/'))
                                        : (BASEURL . (str_starts_with($firstImg, '/') ? $firstImg : '/uploads/' . ltrim($firstImg, '/'))));
                                $firstExt = strtolower(pathinfo((string)$firstImg, PATHINFO_EXTENSION));
                                $videoExts = ['mp4', 'webm', 'ogg', 'mov'];
                                $isMediaVideo = in_array($firstExt, $videoExts, true);

                                $fullImgs = array_map(function($img) {
                                    $img = trim($img);
                                    if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) return $img;
                                    if (str_starts_with($img, '/uploads/') || str_starts_with($img, 'uploads/')) return BASEURL . '/' . ltrim($img, '/');
                                    return BASEURL . (str_starts_with($img, '/') ? $img : '/uploads/' . ltrim($img, '/'));
                                }, $mediaImgs);

                                $imgCount = count($fullImgs);
                                $imgJson = htmlspecialchars(json_encode($fullImgs), ENT_QUOTES, 'UTF-8');
                                $mediaPostType = strtolower($post['post_type'] ?? $post['type'] ?? 'story');
                                $mediaPostUid = !empty($post['uid']) ? $post['uid'] : $post['id'];
                                $postDetailUrl = BASEURL . '/' . urlencode($post['username'] ?? $profileUsername) . '/' . $mediaPostType . '/' . $mediaPostUid;
                            ?>
                            <div class="group relative aspect-square bg-surface-container-high rounded-xl overflow-hidden cursor-pointer shadow-sm hover:shadow-md transition-all">
                                <a href="<?= $postDetailUrl ?>" class="block w-full h-full relative">
                                    <?php if ($isMediaVideo): ?>
                                        <video src="<?= htmlspecialchars($fullFirstImg) ?>" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" muted playsinline preload="metadata"></video>
                                        <div class="absolute top-2 right-2 w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-black/60 backdrop-blur-sm text-white flex items-center justify-center shadow-md pointer-events-none">
                                            <span class="material-symbols-outlined text-[14px] sm:text-[16px]">videocam</span>
                                        </div>
                                    <?php else: ?>
                                        <img src="<?= htmlspecialchars($fullFirstImg) ?>" alt="<?= htmlspecialchars($post['title'] ?? 'Media') ?>" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy">
                                    <?php endif; ?>
                                    
                                    <!-- Multi-image Indicator -->
                                    <?php if ($imgCount > 1): ?>
                                        <div class="absolute top-2 right-2 w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-black/60 backdrop-blur-sm text-white flex items-center justify-center shadow-md pointer-events-none">
                                            <span class="material-symbols-outlined text-[14px] sm:text-[16px]">collections</span>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Story Indicator -->
                                    <?php if (($post['post_type'] ?? 'story') === 'story'): ?>
                                        <div class="absolute top-2 left-2 px-1.5 py-0.5 rounded bg-black/60 backdrop-blur-sm text-white font-caption text-[10px] flex items-center gap-0.5 pointer-events-none">
                                            <span class="material-symbols-outlined text-[11px]">auto_stories</span>
                                            <span class="hidden sm:inline">Story</span>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Dark Overlay with Counts on Hover -->
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center gap-3 sm:gap-6 text-white font-bold text-xs sm:text-sm pointer-events-none">
                                        <div class="flex items-center gap-1 drop-shadow">
                                            <span class="material-symbols-outlined text-[16px] sm:text-[18px]" style="font-variation-settings: 'FILL' 1;">favorite</span>
                                            <span><?= number_format((int)($post['like_count'] ?? 0)) ?></span>
                                        </div>
                                        <div class="flex items-center gap-1 drop-shadow">
                                            <span class="material-symbols-outlined text-[16px] sm:text-[18px]">chat_bubble</span>
                                            <span><?= number_format((int)($post['comment_count'] ?? 0)) ?></span>
                                        </div>
                                    </div>
                                </a>

                                <!-- Quick Lightbox Button -->
                                <button type="button" class="absolute bottom-2 right-2 w-8 h-8 rounded-full bg-black/60 hover:bg-black/80 backdrop-blur-sm text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity z-20" title="Expand View" onclick="event.preventDefault(); event.stopPropagation(); window.openLightboxGallery && openLightboxGallery(<?= $imgJson ?>, 0, '<?= $postDetailUrl ?>');">
                                    <span class="material-symbols-outlined text-[18px]">fullscreen</span>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <!-- Empty State for Media -->
                    <div class="text-center p-12 bg-transparent flex flex-col items-center">
                        <span class="material-symbols-outlined text-4xl text-outline mb-2">photo_library</span>
                        <h2 class="text-xl font-bold text-on-surface">@<?= htmlspecialchars($profileUsername ?? 'user') ?> hasn't posted any media yet</h2>
                        <p class="text-on-surface-variant mt-2 text-sm max-w-sm">
                            Photos and media attached to stories or notes will appear here.
                        </p>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($activeTab === 'drafts' && !empty($data['is_owner'])): ?>
            <!-- Drafts Stream (Story drafts owned by user) -->
            <div class="flex flex-col divide-y divide-outline-variant/30 border-b border-outline-variant/30">
                <?php if (!empty($data['drafts']) && is_array($data['drafts'])): ?>
                    <?php foreach ($data['drafts'] as $draft): ?>
                        <?php
                            $draftId = (int)$draft['id'];
                            $draftEditUrl = BASEURL . '/post/edit/' . $draftId;
                        ?>
                        <article class="p-4 sm:p-6 hover:bg-surface-container-lowest/40 transition-colors flex flex-col justify-between gap-3 group">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="px-2 py-0.5 rounded-md bg-amber-500/10 text-amber-500 font-bold text-[11px] tracking-wider uppercase border border-amber-500/20">Draft</span>
                                        <span class="text-xs text-on-surface-variant">Last edited <?= !empty($draft['updated_at']) ? date('M j, Y \a\t g:i A', strtotime($draft['updated_at'])) : date('M j, Y', strtotime($draft['created_at'])) ?></span>
                                    </div>
                                    <h3 class="font-headline-sm text-lg sm:text-xl font-bold text-on-surface line-clamp-2 mb-1.5">
                                        <a href="<?= $draftEditUrl ?>" class="hover:text-primary transition-colors">
                                            <?= htmlspecialchars(!empty($draft['title']) ? $draft['title'] : 'Untitled Draft') ?>
                                        </a>
                                    </h3>
                                    <p class="font-body-md text-sm text-on-surface-variant line-clamp-2">
                                        <?= htmlspecialchars(mb_substr(strip_tags($draft['content'] ?? ''), 0, 160)) ?>
                                    </p>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <a href="<?= $draftEditUrl ?>" class="btn-primary px-4 py-1.5 text-xs font-semibold flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[16px]">edit</span>
                                        <span>Edit</span>
                                    </a>
                                    <form action="<?= BASEURL ?>/post/delete/<?= $draftId ?>" method="POST" class="m-0" data-confirm="Delete this draft permanently? This action cannot be undone." data-confirm-title="Delete Draft" data-confirm-btn="Delete">
                                        <button type="submit" class="p-1.5 rounded-full text-on-surface-variant hover:text-error hover:bg-error/10 transition-colors" title="Delete Draft">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center p-12 bg-transparent flex flex-col items-center">
                        <span class="material-symbols-outlined text-4xl text-outline mb-2">edit_note</span>
                        <h2 class="text-xl font-bold text-on-surface">No drafts saved</h2>
                        <p class="text-on-surface-variant mt-2 text-sm max-w-sm">
                            Unpublished stories saved as draft will be kept here privately for editing and publishing.
                        </p>
                        <a href="<?= BASEURL ?>/post/create" class="mt-4 px-5 py-2 rounded-full bg-primary text-on-primary font-title-md text-sm font-bold shadow-sm hover:opacity-90 transition-opacity">
                            Write a New Story
                        </a>
                    </div>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <!-- Feed Post Stream (Posts & Reposts) -->
            <div class="flex flex-col divide-y divide-outline-variant/30 border-b border-outline-variant/30">
                <?php if (!empty($data['posts']) && is_array($data['posts'])): ?>
                <?php foreach ($data['posts'] as $post): ?>
                    <?php
                        $postType = strtolower($post['post_type'] ?? $post['type'] ?? 'story');
                        $postUid = !empty($post['uid']) ? $post['uid'] : $post['id'];
                        $postAuthor = !empty($post['username']) ? $post['username'] : $profileUsername;
                        $postUrl = BASEURL . '/' . urlencode($postAuthor) . '/' . $postType . '/' . $postUid;
                        $authorUrl = BASEURL . '/' . urlencode($postAuthor);
                        $repostAuthorUrl = !empty($post['repost_username']) ? BASEURL . '/' . urlencode($post['repost_username']) : '';
                    ?>
                    <!-- Asymmetrical Post Row -->
                    <article class="p-4 sm:p-5 hover:bg-surface-container-lowest/40 transition-colors flex flex-col cursor-pointer" onclick="if(!event.target.closest('a') && !event.target.closest('button')) window.location.href='<?= $postUrl ?>';">
                        
                        <?php if (!empty($post['repost_user_id'])): ?>
                            <div class="flex items-center gap-2 text-xs font-semibold text-on-surface-variant mb-2 ml-10 sm:ml-16">
                                <span class="material-symbols-outlined text-[16px] text-emerald-500">sync_alt</span>
                                <?php if (!empty($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$post['repost_user_id']): ?>
                                    <span>You reposted</span>
                                <?php else: ?>
                                    <a href="<?= $repostAuthorUrl ?>" class="hover:underline font-bold text-on-surface relative z-10" onclick="event.stopPropagation();">
                                        <?= htmlspecialchars($post['repost_name'] ?? $post['repost_username'] ?? 'Someone') ?>
                                    </a>
                                    <span>reposted</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="flex gap-3 sm:gap-4 w-full">
                            <!-- Left Column: Avatar -->
                            <div class="shrink-0">
                            <a href="<?= $authorUrl ?>" class="avatar-link profile-hover-trigger block w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-surface-container-high flex items-center justify-center font-bold text-primary hover:opacity-80 transition-opacity overflow-hidden relative z-10" data-username="<?= htmlspecialchars($post['username'] ?? '') ?>" title="View Profile">
                                <?php if (!empty($post['profile_picture'])): ?>
                                    <img src="<?= BASEURL ?><?= htmlspecialchars($post['profile_picture']) ?>" alt="<?= htmlspecialchars($post['username'] ?? '') ?>" class="w-full h-full object-cover rounded-full avatar-img">
                                <?php else: ?>
                                    <?= htmlspecialchars(substr($post['username'] ?? 'U', 0, 1)) ?>
                                <?php endif; ?>
                            </a>
                        </div>

                        <!-- Right Column: Content -->
                        <div class="flex-1 min-w-0 flex flex-col">
                            
                            <!-- Header (Name, Username, Time, Options) -->
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <div class="flex items-center gap-1.5 min-w-0 flex-wrap text-[15px]">
                                    <a href="<?= $authorUrl ?>" class="font-title-md font-bold text-on-surface hover:underline truncate relative z-10">
                                        <?= htmlspecialchars($post['name'] ?? $post['username'] ?? 'Anonymous') ?>
                                    </a>
                                    <span class="font-body-md text-on-surface-variant truncate">@<?= htmlspecialchars($post['username'] ?? 'anon') ?></span>
                                    <span class="text-on-surface-variant font-bold">·</span>
                                    <time class="timeago font-body-md text-on-surface-variant hover:underline relative z-10" datetime="<?= date('c', strtotime($post['created_at'])) ?>"></time>
                                    
                                    <?php if(($post['post_type'] ?? 'story') === 'story'): ?>
                                        <span class="hidden sm:inline-flex ml-1 px-1.5 py-0.5 rounded border border-outline-variant/40 font-caption text-[11px] text-on-surface-variant items-center gap-1">
                                            <span class="material-symbols-outlined text-[12px]">auto_stories</span>
                                            <?= $post['read_time_minutes'] ?? 1 ?> min read
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Dropdown Options -->
                                <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
                                    <div class="relative dropdown-container z-20 shrink-0">
                                        <button type="button" onclick="toggleMenu(event, 'menu-<?= $post['id'] ?>')" class="text-on-surface-variant hover:text-primary w-8 h-8 rounded-full hover:bg-primary/10 transition-colors flex items-center justify-center -mr-2">
                                            <span class="material-symbols-outlined text-[18px]">more_horiz</span>
                                        </button>
                                        <div id="menu-<?= $post['id'] ?>" class="hidden absolute right-0 top-full mt-1 w-36 bg-surface-container-high border border-outline-variant/30 rounded-xl shadow-xl z-[60] overflow-hidden flex flex-col py-1">
                                            <a href="<?= BASEURL ?>/post/edit/<?= $post['id'] ?>" class="px-4 py-2 text-sm text-on-surface font-title-md hover:bg-surface-container flex items-center gap-3 transition-colors"><span class="material-symbols-outlined text-base">edit</span> Edit</a>
                                            <form action="<?= BASEURL ?>/post/delete/<?= $post['id'] ?>" method="POST" class="m-0 p-0" data-confirm="Are you sure you want to delete this post? This action cannot be undone." data-confirm-title="Delete Post" data-confirm-btn="Delete">
                                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-error font-title-md hover:bg-error-container/20 flex items-center gap-3 transition-colors"><span class="material-symbols-outlined text-base">delete</span> Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Content Body -->
                            <?php if(($post['post_type'] ?? 'story') === 'story'): ?>
                                <!-- Story Cinematic Card -->
                                <div class="mt-1 mb-1 flex flex-col rounded-2xl border border-outline-variant/40 overflow-hidden group hover:border-primary/40 transition-colors bg-surface-container-lowest relative z-10">
                                    <?php 
                                    $cover = '';
                                    if(!empty($post['cover_image'])) {
                                        $decoded = json_decode($post['cover_image'], true);
                                        $imgs = is_array($decoded) ? $decoded : array_filter(explode(',', $post['cover_image']));
                                        $cover = !empty($imgs) ? trim($imgs[0]) : '';
                                    }
                                    if (empty($cover) && preg_match('/<img[^>]+src="([^">]+)"/i', $post['content'] ?? '', $matches)) {
                                        $cover = $matches[1];
                                        if (str_starts_with($cover, BASEURL)) $cover = substr($cover, strlen(BASEURL));
                                    }
                                    ?>
                                    <?php if(!empty($cover)): ?>
                                        <?php
                                            $ext = strtolower(pathinfo((string)$cover, PATHINFO_EXTENSION));
                                            $videoExts = ['mp4', 'webm', 'ogg', 'mov'];
                                            $isVideo = in_array($ext, $videoExts, true);
                                            $mediaSrc = (str_starts_with($cover, 'http://') || str_starts_with($cover, 'https://'))
                                                ? $cover
                                                : ((str_starts_with($cover, '/uploads/') || str_starts_with($cover, 'uploads/'))
                                                    ? BASEURL . '/' . ltrim($cover, '/')
                                                    : BASEURL . (str_starts_with($cover, '/') ? $cover : '/uploads/' . ltrim($cover, '/')));
                                            $mimeType = match($ext) {
                                                'webm' => 'video/webm',
                                                'ogg' => 'video/ogg',
                                                default => 'video/mp4'
                                            };
                                        ?>
                                        <?php if($isVideo): ?>
                                            <div class="relative w-full border-b border-outline-variant/30 overflow-hidden bg-black" onclick="event.stopPropagation();">
                                                <video controls class="w-full rounded-xl max-h-96 object-contain bg-black">
                                                    <source src="<?= htmlspecialchars($mediaSrc) ?>" type="<?= $mimeType ?>">
                                                    Your browser does not support the video tag.
                                                </video>
                                            </div>
                                        <?php else: ?>
                                            <div class="relative w-full aspect-[16/9] sm:aspect-video border-b border-outline-variant/30 overflow-hidden bg-surface-container-high">
                                                <img src="<?= htmlspecialchars($mediaSrc) ?>" class="post-media-image w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out" alt="Story Cover">
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <div class="p-3.5 sm:p-4 flex flex-col gap-1.5 bg-surface-container-lowest group-hover:bg-surface-container-low/30 transition-colors">
                                        <?php if(!empty($post['title'])): ?>
                                            <h2 class="font-serif text-lg sm:text-xl font-bold text-on-surface tracking-tight line-clamp-2">
                                                <?= htmlspecialchars($post['title']) ?>
                                            </h2>
                                        <?php endif; ?>
                                        <?php 
                                            // Extract Subtitle explicitly
                                            $excerpt = '';
                                            if (preg_match('/<h2[^>]*class="[^"]*story-subtitle[^"]*"[^>]*>(.*?)<\/h2>/is', $post['content'] ?? '', $matches)) {
                                                $excerpt = trim(strip_tags($matches[1]));
                                            }
                                            // Fallback: If no subtitle, safely get the first bit of text with proper spacing
                                            if (empty($excerpt)) {
                                                $cleanText = str_replace(['<p>', '<br>', '</div>', '</li>', '</h1>', '</h2>', '</h3>'], ' ', (string)($post['content'] ?? ''));
                                                $excerpt = trim(strip_tags($cleanText));
                                            }
                                        ?>
                                        <?php if(!empty($excerpt)): ?>
                                            <div class="font-body-md text-on-surface-variant text-[14px] sm:text-[15px] line-clamp-2 leading-relaxed">
                                                <?= htmlspecialchars($excerpt) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- Note Text & Grid -->
                                <div class="font-body-md text-on-surface text-[15px] leading-relaxed whitespace-pre-line mb-2">
                                    <?= preg_replace('/(^|>|\s)#([a-zA-Z_][a-zA-Z0-9_]*)/', '$1<a href="' . BASEURL . '/explore/tag/$2" class="text-primary font-semibold hover:underline relative z-10" onclick="event.stopPropagation();">#$2</a>', strip_tags((string)($post['content'] ?? ''))) ?>
                                </div>
                                <?php if(!empty($post['cover_image'])): ?>
                                    <?php 
                                    $decoded = json_decode($post['cover_image'], true);
                                    $imgs = is_array($decoded) ? $decoded : array_filter(explode(',', $post['cover_image']));
                                    $imgs = array_slice($imgs, 0, 4);
                                    $imgCount = count($imgs);
                                    $imgJson = htmlspecialchars(json_encode(array_values($imgs)), ENT_QUOTES, 'UTF-8');
                                    ?>
                                    <div class="mt-1 mb-1 grid <?= $imgCount === 1 ? 'grid-cols-1' : 'grid-cols-2' ?> gap-1 sm:gap-1.5 rounded-2xl overflow-hidden border border-outline-variant/30 relative z-10">
                                        <?php foreach($imgs as $idx => $img): ?>
                                            <?php 
                                            $noteFile = trim((string)$img);
                                            $noteExt = strtolower(pathinfo($noteFile, PATHINFO_EXTENSION));
                                            $videoExts = ['mp4', 'webm', 'ogg', 'mov'];
                                            $noteIsVideo = in_array($noteExt, $videoExts, true);
                                            $noteSrc = (str_starts_with($noteFile, 'http://') || str_starts_with($noteFile, 'https://')) 
                                                ? $noteFile 
                                                : ((str_starts_with($noteFile, '/uploads/') || str_starts_with($noteFile, 'uploads/')) 
                                                    ? (BASEURL . '/' . ltrim($noteFile, '/')) 
                                                    : (BASEURL . (str_starts_with($noteFile, '/') ? $noteFile : '/uploads/' . ltrim($noteFile, '/'))));
                                            ?>
                                            <?php if($noteIsVideo): ?>
                                                <div class="w-full bg-black rounded-xl overflow-hidden <?= ($imgCount === 3 && $idx === 0) ? 'row-span-2' : '' ?>" onclick="event.stopPropagation();">
                                                    <video controls class="w-full rounded-xl max-h-96 object-contain bg-black">
                                                        <source src="<?= htmlspecialchars($noteSrc) ?>" type="video/<?= $noteExt === 'webm' ? 'webm' : ($noteExt === 'ogg' ? 'ogg' : 'mp4') ?>">
                                                        Your browser does not support the video tag.
                                                    </video>
                                                </div>
                                            <?php else: ?>
                                                <img src="<?= htmlspecialchars($noteSrc) ?>" class="post-media-image w-full h-full object-cover cursor-pointer hover:opacity-90 transition-opacity <?= ($imgCount === 3 && $idx === 0) ? 'row-span-2' : '' ?> <?= $imgCount > 1 ? 'aspect-[4/3] sm:aspect-video' : 'max-h-[500px]' ?>" alt="Attachment" onclick="event.stopPropagation(); window.openLightboxGallery && openLightboxGallery(<?= $imgJson ?>, <?= $idx ?>, '<?= $postUrl ?>')">
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>

                            <!-- Action Bar (Twitter/X style hit targets) -->
                            <div class="flex items-center justify-between mt-2 max-w-md text-on-surface-variant relative z-10 -ml-2">
                                <a href="<?= $postUrl ?>#discussion" class="group flex items-center gap-1 hover:text-primary transition-colors">
                                    <div class="w-8 h-8 rounded-full group-hover:bg-primary/10 flex items-center justify-center transition-colors">
                                        <span class="material-symbols-outlined text-[18px]">chat_bubble</span>
                                    </div>
                                    <span class="font-body-md text-xs"><?= $post['comment_count'] ?? 0 ?></span>
                                </a>
                                <?php 
                                     $isLiked = in_array((int)$post['id'], $data['liked_posts'] ?? []);
                                     $isBookmarked = in_array((int)$post['id'], $data['bookmarked_posts'] ?? []);
                                     $isReposted = in_array((int)$post['id'], $data['reposted_posts'] ?? []) || !empty($post['is_reposted']);
                                ?>
                                <button type="button" class="btn-repost group flex items-center gap-1 transition-colors <?= $isReposted ? 'text-emerald-500' : 'hover:text-emerald-500' ?> active:scale-95" data-id="<?= (int)$post['id'] ?>" title="Repost">
                                    <div class="w-8 h-8 rounded-full group-hover:bg-emerald-500/10 flex items-center justify-center transition-colors">
                                        <span class="material-symbols-outlined text-[18px] <?= $isReposted ? 'font-bold' : '' ?>">sync_alt</span>
                                    </div>
                                    <span class="repost-count font-body-md text-xs"><?= (int)($post['repost_count'] ?? 0) ?></span>
                                </button>
                                <button type="button" class="btn-like group flex items-center gap-1 transition-colors <?= $isLiked ? 'text-error' : 'hover:text-error' ?> active:scale-95" data-id="<?= (int)$post['id'] ?>" title="Like">
                                    <div class="w-8 h-8 rounded-full group-hover:bg-error/10 flex items-center justify-center transition-colors">
                                        <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' <?= $isLiked ? 1 : 0 ?>;">favorite</span>
                                    </div>
                                    <span class="like-count font-body-md text-xs"><?= (int)($post['like_count'] ?? 0) ?></span>
                                </button>
                                <button type="button" class="btn-bookmark group flex items-center gap-1 transition-colors <?= $isBookmarked ? 'text-primary' : 'hover:text-primary' ?> active:scale-95" data-id="<?= (int)$post['id'] ?>" title="Bookmark">
                                    <div class="w-8 h-8 rounded-full group-hover:bg-primary/10 flex items-center justify-center transition-colors">
                                        <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' <?= $isBookmarked ? 1 : 0 ?>;">bookmark</span>
                                    </div>
                                    <span class="bookmark-count font-body-md text-xs"><?= (int)($post['bookmark_count'] ?? 0) ?></span>
                                </button>
                                <button type="button" class="group flex items-center transition-colors hover:text-primary" onclick="event.stopPropagation(); navigator.clipboard.writeText('<?= $postUrl ?>'); showToast('Link copied to clipboard!', 'success');">
                                    <div class="w-8 h-8 rounded-full group-hover:bg-primary/10 flex items-center justify-center transition-colors">
                                        <span class="material-symbols-outlined text-[18px]">share</span>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Empty State Handling -->
            <div class="text-center p-12 bg-transparent flex flex-col items-center">
                <?php if(($data['active_tab'] ?? 'posts') === 'reposts'): ?>
                    <span class="material-symbols-outlined text-4xl text-outline mb-2">sync_alt</span>
                    <h2 class="text-xl font-bold text-on-surface">No reposts yet</h2>
                    <p class="text-on-surface-variant mt-2 text-sm max-w-sm">
                        When @<?= htmlspecialchars($profileUsername ?? 'user') ?> reposts stories or notes, they will appear here.
                    </p>
                <?php else: ?>
                    <span class="material-symbols-outlined text-4xl text-outline mb-2">article</span>
                    <h2 class="text-xl font-bold text-on-surface">No stories found</h2>
                    <p class="text-on-surface-variant mt-2 text-sm max-w-sm">
                        Check back later or be the first to share a perspective!
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Followers / Following Modal -->
<div id="follow-modal" class="fixed inset-0 z-[150] bg-black/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-surface-container-low border border-outline-variant/30 rounded-2xl w-full max-w-md max-h-[80vh] flex flex-col shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <!-- Modal Header -->
        <div class="px-5 py-4 border-b border-outline-variant/30 flex items-center justify-between">
            <h3 id="follow-modal-title" class="font-title-md text-base font-bold text-on-surface">Followers</h3>
            <button type="button" onclick="closeFollowModal()" class="w-8 h-8 rounded-full hover:bg-surface-container text-on-surface-variant hover:text-on-surface flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>
        <!-- Modal User List -->
        <div id="follow-modal-list" class="p-2 overflow-y-auto flex-1 divide-y divide-outline-variant/20 min-h-[160px]">
            <!-- Injected via JavaScript -->
        </div>
    </div>
</div>

<script>
async function openFollowModal(type, username) {
    const modal = document.getElementById('follow-modal');
    const title = document.getElementById('follow-modal-title');
    const list = document.getElementById('follow-modal-list');
    if (!modal || !list) return;

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    title.textContent = (type === 'followers') ? 'Followers' : 'Following';
    list.innerHTML = `
        <div class="flex items-center justify-center py-12 text-on-surface-variant">
            <span class="material-symbols-outlined text-2xl animate-spin">progress_activity</span>
        </div>
    `;

    try {
        const res = await fetch(`<?= BASEURL ?>/profile/${type}/${encodeURIComponent(username)}`, {
            headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();

        if (data.success && data.users && data.users.length > 0) {
            const currentUserId = <?= isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0 ?>;
            list.innerHTML = data.users.map(u => {
                const userUrl = `<?= BASEURL ?>/${encodeURIComponent(u.username)}`;
                const initial = (u.username || 'U').charAt(0).toUpperCase();
                const avatarHtml = u.profile_picture 
                    ? `<img src="<?= BASEURL ?>${u.profile_picture}" class="w-full h-full object-cover">`
                    : `<span>${initial}</span>`;
                
                const isCurrent = currentUserId === parseInt(u.id);
                const isFollowed = Boolean(parseInt(u.is_following));
                const followBtn = isCurrent ? '' : `
                    <button type="button" class="btn-follow btn-pill-follow ${isFollowed ? 'following' : ''} text-xs shrink-0" data-user-id="${u.id}">
                        ${isFollowed ? 'Following' : 'Follow'}
                    </button>
                `;

                return `
                    <div class="p-3 flex items-center justify-between gap-3 hover:bg-surface-container-high/40 transition-colors rounded-xl">
                        <a href="${userUrl}" class="flex items-center gap-3 min-w-0 flex-1">
                            <div class="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center font-bold text-primary overflow-hidden shrink-0">
                                ${avatarHtml}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-title-md font-bold text-on-surface text-sm truncate hover:underline">${u.name || u.username}</p>
                                <p class="font-body-md text-xs text-on-surface-variant truncate">@${u.username}</p>
                                ${u.bio ? `<p class="font-body-md text-xs text-on-surface-variant/80 truncate mt-0.5">${u.bio}</p>` : ''}
                            </div>
                        </a>
                        ${followBtn}
                    </div>
                `;
            }).join('');
        } else {
            list.innerHTML = `
                <div class="text-center py-12 text-on-surface-variant font-body-md text-sm">
                    No ${type === 'followers' ? 'followers' : 'following'} yet.
                </div>
            `;
        }
    } catch (err) {
        list.innerHTML = `
            <div class="text-center py-8 text-error font-body-md text-sm">
                Failed to load ${type}.
            </div>
        `;
    }
}

function closeFollowModal() {
    const modal = document.getElementById('follow-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

// Close on backdrop click
document.getElementById('follow-modal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeFollowModal();
    }
});
</script>

<style>
    /* Quill content formatting inside profile */
    .quill-content p { margin-bottom: 0.75rem; }
    .quill-content a { color: #10b981; text-decoration: underline; }
    .dark .quill-content a { color: #4edea3; }
    .quill-content strong { color: #dae2fd; }
    .quill-content blockquote { border-left: 3px solid #10b981; padding-left: 1rem; margin: 1rem 0; font-style: italic; }
</style>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
