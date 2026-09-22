<?php
/**
 * Single Template for Client Galleries Custom Post Type
 * Redesigned for a 100% database-free premium static deployment on Vercel/Netlify!
 * Uses HTML templates, client-side LocalStorage guards, and SHA1 hash comparisons in the browser.
 * Keeps private photo collections secure and locked behind password/payment screens natively!
 *
 * @package Studio_Photography
 */
get_header();

// Fetch gallery settings
$gallery_id = get_the_ID();
$booking_id = get_post_meta($gallery_id, '_gallery_booking_id', true);
$payment_status = get_post_meta($gallery_id, '_gallery_payment_status', true) ?: 'paid';
$gdrive_folder = get_post_meta($gallery_id, '_gallery_gdrive_folder', true);
$mega_folder = get_post_meta($gallery_id, '_gallery_mega_folder', true);

if (!empty($booking_id)) {
    $total_amount = floatval(get_post_meta($booking_id, '_booking_amount_total', true));
    $deposit_paid = floatval(get_post_meta($booking_id, '_booking_amount_deposit', true));
    $balance_amount = $total_amount - $deposit_paid;
    
    $client_email = get_post_meta($booking_id, '_booking_client_email', true);
    $client_name = get_post_meta($booking_id, '_booking_client_name', true);
} else {
    $balance_amount = floatval(get_post_meta($gallery_id, '_gallery_balance_amount', true));
    $client_email = get_post_meta($gallery_id, '_gallery_client_email', true) ?: 'billing@whytecreatives.local';
    $client_name = get_the_title();
}

// Paystack API settings
$paystack_key = get_option('studio_paystack_public_key');
$paystack_enabled = get_option('studio_paystack_enabled');

// ── GALLERY EXPIRY CHECK (Pixieset-style automatic expiration) ──
$expiry_state = studio_photography_gallery_expiry_state($gallery_id);
$expiry_admin_preview = current_user_can('manage_options');
if ($expiry_state['expires'] && $expiry_state['expired']) {
    // One-time "gallery expired" email to the business inbox (fires on the first visit after expiry — no cron needed!)
    if (get_post_meta($gallery_id, '_gallery_expiry_notified', true) !== 'yes') {
        update_post_meta($gallery_id, '_gallery_expiry_notified', 'yes');
        studio_photography_send_gallery_expired_email($gallery_id, $expiry_state['date']);
    }

    // Clients get the friendly expiry screen — admins bypass it and keep full access
    if (!$expiry_admin_preview) {
        ?>
        <div class="pt-32 pb-16 px-6 bg-slate-50 min-h-screen flex items-center justify-center select-none">
            <div class="max-w-md w-full p-8 bg-white border border-slate-200 rounded-2xl shadow-xl text-center">
                <div class="h-16 w-16 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center mx-auto mb-6">
                    <i data-lucide="calendar-clock" class="h-8 w-8"></i>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider text-amber-600 bg-amber-50 px-3 py-1 rounded-full">Gallery Expired</span>
                <h1 class="text-3xl font-extrabold text-slate-900 mt-4 mb-2">This Gallery Has Expired</h1>
                <p class="text-slate-500 text-sm mb-2 leading-relaxed">This private photo gallery was available until <strong><?php echo esc_html(date('F j, Y', strtotime($expiry_state['date']))); ?></strong> and has now automatically expired.</p>
                <p class="text-slate-400 text-xs mb-8 leading-relaxed">Need more time or want to re-download your photos? Contact the studio and we'll gladly extend access for you.</p>
                <div class="space-y-3">
                    <a href="mailto:<?php echo esc_attr(studio_photography_get_business_email()); ?>" class="btn btn-primary w-full py-3 flex items-center justify-center gap-2"><i data-lucide="mail" class="h-4 w-4"></i> Contact the Studio</a>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="h-11 px-6 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center gap-2 text-xs uppercase tracking-widest font-bold transition-all">Back to Homepage</a>
                </div>
            </div>
        </div>
        <script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>
        <?php
        get_footer();
        return;
    }
}

// Determine cloud folder label dynamically based on URL provider
$cloud_folder_label = "Open Cloud Folder";
if (!empty($gdrive_folder)) {
    if (strpos($gdrive_folder, 'pixieset.com') !== false) {
        $cloud_folder_label = "Open Pixieset Collection";
    } elseif (strpos($gdrive_folder, 'drive.google.com') !== false) {
        $cloud_folder_label = "Open Google Drive Folder";
    } elseif (strpos($gdrive_folder, 'dropbox.com') !== false) {
        $cloud_folder_label = "Open Dropbox Folder";
    } elseif (strpos($gdrive_folder, 'onedrive') !== false) {
        $cloud_folder_label = "Open OneDrive Folder";
    }
}

// ── COMPILE GALLERY GRID ITEMS ──
$grid_items = array();
$local_attachments_count = 0;

// Detect the active cover photo FIRST so it can be excluded from the photo grid (no duplicates!)
$manual_cover_url = get_post_meta(get_the_ID(), '_gallery_cover_url', true);
$cover_attachment_id = 0;
if (!empty($manual_cover_url)) {
    // Personalised cover chosen via the "Gallery Cover Photo" box in the admin editor
    $cover_attachment_id = (int) attachment_url_to_postid($manual_cover_url);
} elseif (has_post_thumbnail()) {
    // Featured image doubles as the cinematic hero cover, so don't repeat it in the grid either
    $cover_attachment_id = (int) get_post_thumbnail_id(get_the_ID());
}

// Extract the cover's Google Drive file ID (if it's a Drive photo) so the SAME image
// never appears twice — once as the hero and again as a grid tile (true de-duplication!)
$cover_drive_id = '';
if (!empty($manual_cover_url)) {
    if (preg_match('/\/file\/d\/([a-zA-Z0-9_-]+)/', $manual_cover_url, $cdm)) $cover_drive_id = $cdm[1];
    elseif (preg_match('/[?&]id=([a-zA-Z0-9_-]+)/', $manual_cover_url, $cdm)) $cover_drive_id = $cdm[1];
    elseif (preg_match('/googleusercontent\.com\/d\/([a-zA-Z0-9_-]+)/', $manual_cover_url, $cdm)) $cover_drive_id = $cdm[1];
}

// A. Pull WordPress locally uploaded attachments
$local_images = get_attached_media('image', get_the_ID());
if (!empty($local_images)) {
    foreach ($local_images as $img_id => $img_post) {
        // Skip the cover photo — it already shines as the hero banner, must not duplicate inside the grid!
        if ($cover_attachment_id && (int) $img_id === $cover_attachment_id) continue;
        $full_res_url = wp_get_attachment_url($img_id);
        if (!empty($manual_cover_url) && $full_res_url === $manual_cover_url) continue;
        $grid_items[] = array(
            'url' => $full_res_url,
            'thumbnail' => wp_get_attachment_image_url($img_id, 'large'),
            // Route through the secure download proxy so every single-photo download is recorded + notified live!
            'download_url' => add_query_arg(array(
                'studio_download_photo' => 1,
                'gallery_id' => get_the_ID(),
                'attachment_id' => $img_id
            ), home_url('/')),
            'title' => $img_post->post_title
        );
        $local_attachments_count++;
    }
}

// B. Parse and integrate S3 Public URLs or Google Drive individual files URLs!
$cloud_urls_raw = get_post_meta(get_the_ID(), '_gallery_cloud_urls', true);
if (!empty($cloud_urls_raw)) {
    $urls = array_filter(array_map('trim', explode("\n", str_replace("\r", "", $cloud_urls_raw))));
    foreach ($urls as $url) {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            // Skip if this cloud URL is the manually chosen cover (matched by URL or Drive file ID — no duplicates!)
            if (!empty($manual_cover_url) && $url === $manual_cover_url) continue;
            if (!empty($cover_drive_id) && strpos($url, $cover_drive_id) !== false) continue;
            $direct_view_url = studio_photography_get_google_drive_direct_url($url);
            
            $direct_dl_url = $url;
            if (strpos($url, 'drive.google.com') !== false) {
                if (preg_match('/\/file\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
                    $direct_dl_url = 'https://drive.google.com/uc?export=download&id=' . $matches[1];
                } elseif (preg_match('/id=([a-zA-Z0-9_-]+)/', $url, $matches)) {
                    $direct_dl_url = 'https://drive.google.com/uc?export=download&id=' . $matches[1];
                }
            }
            
            $grid_items[] = array(
                'url' => $direct_view_url,
                'thumbnail' => $direct_view_url,
                'download_url' => $direct_dl_url,
                'title' => basename($url)
            );
        }
    }
}

// C. Parse public Google Drive Folder and stream all files dynamically (No API Keys required!)
if (!empty($gdrive_folder) && strpos($gdrive_folder, 'drive.google.com') !== false) {
    $folder_id = '';
    if (preg_match('/\/folders\/([a-zA-Z0-9_-]+)/', $gdrive_folder, $matches)) {
        $folder_id = $matches[1];
    } elseif (preg_match('/id=([a-zA-Z0-9_-]+)/', $gdrive_folder, $matches)) {
        $folder_id = $matches[1];
    }
    
    if (!empty($folder_id)) {
        $transient_key = 'studio_gdrive_folder_' . $folder_id;
        $bypass_cache = current_user_can('manage_options') || isset($_GET['clear_cache']);
        
        $folder_files = $bypass_cache ? false : get_transient($transient_key);
        $diagnostic_info = array();
        
        if ($folder_files === false) {
            // Fresh scrape via the SHARED parser (identical to the one the ZIP builder uses!)
            $fetched = studio_photography_fetch_gdrive_folder_files($folder_id);
            $folder_files = $fetched['files'];
            $diagnostic_info = $fetched['diag'];

            if (!empty($folder_files)) {
                // Cache the folder listing for only 1 minute so added/removed Google Drive photos reflect in near real-time for clients!
                set_transient($transient_key, $folder_files, MINUTE_IN_SECONDS);
                // Keep the last known good listing as a safety net in case a fresh scrape ever comes back empty
                update_option('studio_gdrive_folder_backup_' . $folder_id, $folder_files, false);
            } else {
                // Fresh scrape failed/empty — fall back to the last known good listing (retry at most once per minute)
                $backup_files = get_option('studio_gdrive_folder_backup_' . $folder_id);
                if (!empty($backup_files)) {
                    $folder_files = $backup_files;
                    set_transient($transient_key, $backup_files, MINUTE_IN_SECONDS);
                }
            }
        }
        
        if (!empty($folder_files)) {
            // Normalize every Drive entry so the grid ALWAYS has a valid thumbnail + download URL
            foreach ($folder_files as $ff) {
                // The cover photo shows as the hero banner — skip it here so it never duplicates in the grid
                if (!empty($cover_drive_id) && strpos((string) $ff['url'], $cover_drive_id) !== false) continue;
                $grid_items[] = array(
                    'url' => isset($ff['url']) ? $ff['url'] : $ff['download_url'],
                    // ⚡ Speed: always serve an 800px tile for the grid (works even for old cached listings)
                    'thumbnail' => studio_photography_sized_image_url(!empty($ff['thumbnail']) ? $ff['thumbnail'] : (!empty($ff['url']) ? $ff['url'] : $ff['download_url']), 800),
                    'download_url' => $ff['download_url'],
                    'title' => $ff['title']
                );
            }
        }
    }
}

// 📊 TOTAL PHOTO COUNT: the cover photo is PART of the collection!
// It displays once (as the hero) and is never duplicated in the grid — but it COUNTS.
$cover_is_collection_photo = (!empty($manual_cover_url) || $cover_attachment_id > 0) ? 1 : 0;
$display_photo_count = count($grid_items) + $cover_is_collection_photo;

// D. Select cinematic cover backdrop (Manual Cover Photo → Featured Image → First Photo → Fallback)
$cover_image = '';
if (!empty($manual_cover_url)) {
    // Manually chosen cover photo (URL or Google Drive link — converted + sized automatically!)
    $cover_image = studio_photography_media_url($manual_cover_url, 2000);
} elseif (has_post_thumbnail()) {
    $cover_image = get_the_post_thumbnail_url(get_the_ID(), 'full');
} elseif (!empty($grid_items)) {
    $cover_image = $grid_items[0]['url'];
} else {
    $cover_image = 'https://images.unsplash.com/photo-1492691527719-9d1e07e534b4?auto=format&fit=crop&q=80&w=2000';
}

// E. Pixieset-style cover FOCAL POINT (which part of the photo stays perfectly in frame on any screen size)
$cover_object_position = '50% 50%';
$raw_focal = get_post_meta(get_the_ID(), '_gallery_cover_focal', true);
if (!empty($raw_focal) && preg_match('/^(\d{1,3}(?:\.\d+)?)%\s+(\d{1,3}(?:\.\d+)?)%$/', $raw_focal, $focal_match)) {
    $cover_object_position = min(100, (float) $focal_match[1]) . '% ' . min(100, (float) $focal_match[3]) . '%';
}

// F. Pixieset-style custom gallery TYPOGRAPHY (optional font chosen in the admin editor)
$gallery_font = get_post_meta(get_the_ID(), '_gallery_font', true);
$gallery_fonts_map = studio_photography_get_gallery_fonts();
if (!empty($gallery_font) && isset($gallery_fonts_map[$gallery_font]) && !empty($gallery_fonts_map[$gallery_font]['gq'])) {
    echo '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=' . esc_attr($gallery_fonts_map[$gallery_font]['gq']) . '&display=swap">';
    echo '<style> body.single-client_gallery h1, body.single-client_gallery h2, body.single-client_gallery h3, body.single-client_gallery .font-serif { font-family: \'' . esc_attr($gallery_font) . '\', Georgia, serif !important; } </style>';
}

// G. CONTENT PROTECTION STYLES — watermark overlays for unpaid previews, anti-select/drag/print CSS
$wm_text = get_bloginfo('name') . ' • PREVIEW';
$wm_svg = "<svg xmlns='http://www.w3.org/2000/svg' width='280' height='200'><text x='140' y='100' transform='rotate(-28 140 100)' text-anchor='middle' font-family='Georgia, serif' font-size='17' font-weight='bold' fill='rgba(255,255,255,0.30)'>" . esc_html($wm_text) . "</text></svg>";
$wm_uri = 'data:image/svg+xml;base64,' . base64_encode($wm_svg);
echo '<style>
#unlocked-gallery-content { -webkit-user-select: none; user-select: none; -webkit-touch-callout: none; }
#unlocked-gallery-content img { -webkit-user-drag: none; }
.watermark-overlay { position: absolute; inset: 0; pointer-events: none; z-index: 5; display: none; background-repeat: repeat; }
.gallery-previews-locked .watermark-overlay, body.gallery-downloads-locked .watermark-overlay { display: block; background-image: url("' . $wm_uri . '"); }
.gallery-previews-locked a[download], body.gallery-downloads-locked a[download] { opacity: 0.45; cursor: not-allowed; filter: grayscale(0.4); }
#gallery-balance-banner.pulse-once { animation: studioPulse 1.2s ease; }
@keyframes studioPulse { 0%, 100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); } 50% { box-shadow: 0 0 0 10px rgba(245, 158, 11, 0.45); } }
body.gallery-print-blocked #unlocked-gallery-content { display: none !important; }
</style>';

// Security definitions enqueued to Javascript
global $post;
$post_has_password = !empty($post->post_password);
$pass_hash = $post_has_password ? sha1($post->post_password) : '';
$has_balance = ($balance_amount > 0 && $payment_status === 'unpaid');

// ── SERVER-RENDERED INITIAL VISIBILITY (fail-safe: the gallery is NEVER blank, even if JavaScript fails!) ──
// No password → photos render immediately. Password set → lock screen renders immediately.
// Unpaid balance → watermark + payment banner render server-side. JS only ENHANCES from here.
$initial_lock_class = $post_has_password ? '' : 'hidden';
$initial_content_class = $post_has_password ? 'hidden' : '';
$initial_content_lock = $has_balance ? ' gallery-previews-locked' : '';
$initial_banner_class = $has_balance ? '' : 'hidden';
?>

<!-- Load Paystack Inline JavaScript Library -->
<script src="https://js.paystack.co/v1/inline.js"></script>

<div class="min-h-screen bg-white">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        
        <?php if ($expiry_state['expires'] && $expiry_state['expired'] && $expiry_admin_preview) : ?>
            <!-- ADMIN-ONLY EXPIRY WARNING (clients never see this) -->
            <div class="bg-amber-50 border-b border-amber-200 text-amber-800 text-xs font-medium px-6 py-3 flex items-center justify-center gap-2">
                <i data-lucide="calendar-clock" class="h-4 w-4"></i>
                <span>This gallery expired on <?php echo esc_html(date('M d, Y', strtotime($expiry_state['date']))); ?> — clients now see the expiry screen. You're viewing it as an admin.</span>
                <a class="underline font-bold" href="<?php echo esc_url(admin_url('post.php?post=' . $gallery_id . '&action=edit')); ?>">Change expiry</a>
            </div>
        <?php endif; ?>
        
        
        <!-- STEP 1: PASSWORD LOCKED PORTAL (Client-Side Static Fallback!) -->
        <div id="portal-password-lock" class="<?php echo $initial_lock_class; ?> pt-32 pb-16 px-6 bg-slate-50 min-h-screen flex items-center justify-center select-none">
            <div class="max-w-md w-full p-8 bg-white border border-slate-200 rounded-2xl shadow-xl text-center">
                <div class="h-14 w-14 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-6">
                    <i data-lucide="lock" class="h-6 w-6"></i>
                </div>
                <h2 class="text-2xl font-bold text-slate-900 mb-2">Protected Client Gallery</h2>
                <p class="text-slate-500 text-sm mb-6 leading-relaxed">This is a secure, private client gallery. Please enter your unguessable access password below to unlock your photos.</p>
                
                <form id="gallery-password-form" class="space-y-4">
                    <input type="password" id="gallery_password" placeholder="Enter password" class="input text-center py-3" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 12px;" required autocomplete="current-password">
                    <button type="submit" class="btn btn-primary w-full py-3">Unlock Gallery</button>
                </form>
                <p id="password-error" class="text-red-500 text-xs mt-3 hidden font-medium">⚠️ Incorrect password. Please try again.</p>
            </div>
        </div>
        
        <!-- STEP 2: BALANCE SETTLEMENT LOCK SCREEN (Client-Side Static Fallback!) -->
        <div id="portal-balance-lock" class="hidden pt-32 pb-16 px-6 bg-slate-50 min-h-screen flex items-center justify-center select-none">
            <div class="max-w-xl w-full p-8 bg-white border border-slate-200 rounded-2xl shadow-xl text-center">
                <div class="h-16 w-16 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-6 animate-pulse">
                    <i data-lucide="shield-alert" class="h-8 w-8"></i>
                </div>
                
                <span class="text-xs font-bold uppercase tracking-wider text-orange-600 bg-orange-50 px-3 py-1 rounded-full">Final Settlement Pending</span>
                
                <h2 class="text-3xl font-extrabold text-slate-900 mt-4 mb-2">Your Private Gallery is Ready!</h2>
                <p class="text-slate-500 text-sm mb-6 leading-relaxed">
                    We have finished processing and editing your high-resolution photos! To unlock your secure gallery, view the grid, and download your final copies, please settle the outstanding remaining balance below.
                </p>
                
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-5 mb-8 text-left text-sm space-y-3">
                    <div class="flex justify-between border-b border-slate-100 pb-2">
                        <span class="text-slate-500 font-medium">Session:</span>
                        <span class="font-bold text-slate-900"><?php the_title(); ?></span>
                    </div>
                    <?php if (!empty($booking_id)) : ?>
                        <div class="flex justify-between border-b border-slate-100 pb-2">
                            <span class="text-slate-500 font-medium">Customer:</span>
                            <span class="font-semibold text-slate-900"><?php echo esc_html($client_name); ?></span>
                        </div>
                        <div class="flex justify-between border-b border-slate-100 pb-2">
                            <span class="text-slate-500 font-medium">Package Total:</span>
                            <span class="font-medium text-slate-600">GH₵ <?php echo number_format($total_amount, 2); ?></span>
                        </div>
                        <div class="flex justify-between border-b border-slate-100 pb-2">
                            <span class="text-slate-500 font-medium">Deposit Paid:</span>
                            <span class="font-medium text-green-600">- GH₵ <?php echo number_format($deposit_paid, 2); ?></span>
                        </div>
                    <?php else : ?>
                        <div class="flex justify-between border-b border-slate-100 pb-2">
                            <span class="text-slate-500 font-medium">Delivery Type:</span>
                            <span class="font-semibold text-slate-900">Secure Online Client Gallery</span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="flex justify-between pt-1">
                        <span class="text-slate-700 font-bold text-base">Remaining Balance:</span>
                        <span class="font-extrabold text-slate-900 text-lg">GH₵ <?php echo number_format($balance_amount, 2); ?></span>
                    </div>
                </div>
                
                <?php if ($paystack_enabled === 'yes' && !empty($paystack_key)) : ?>
                    <button
                        id="pay-balance-btn"
                        type="button"
                        data-pay-balance
                        class="btn btn-primary w-full py-3.5 text-base font-semibold shadow-md flex items-center justify-center gap-2 hover:bg-slate-800 transition-all"
                    >
                        <i data-lucide="credit-card" class="h-5 w-5"></i>
                        Pay Balance via Paystack — GH₵ <?php echo number_format($balance_amount, 2); ?>
                    </button>
                <?php else : ?>
                    <div class="bg-red-50 border border-red-100 text-red-700 p-4 rounded-xl text-xs text-left leading-relaxed">
                        <span class="font-bold block mb-1">⚠️ Gateway Configuration Offline:</span>
                        Paystack Checkout is currently disabled or has not been configured with an API Key in the Studio Dashboard. Please contact the photographer to activate payments or complete checkout.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- STEP 3: PIXIESET PREMIUM GALLERY EXPERIENCE (server-rendered visibility, JS-enhanced) -->
        <div id="unlocked-gallery-content" class="<?php echo trim($initial_content_class . $initial_content_lock); ?>">
            <!-- ⏳ BALANCE BANNER (preview mode: gallery visible, downloads locked until settled) -->
            <div id="gallery-balance-banner" class="<?php echo $initial_banner_class; ?> sticky top-20 z-40 mx-auto max-w-3xl mt-6 px-4">
                <div class="rounded-2xl border border-amber-200 bg-amber-50/95 backdrop-blur shadow-lg p-4 sm:p-5 flex flex-col sm:flex-row items-center gap-4">
                    <div class="h-11 w-11 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                        <i data-lucide="lock" class="h-5 w-5"></i>
                    </div>
                    <div class="text-center sm:text-left flex-1">
                        <p class="font-bold text-slate-900 text-sm">🎉 Your photos are ready — previews unlocked!</p>
                        <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">Settle the remaining balance of <strong>GH₵ <?php echo number_format($balance_amount, 2); ?></strong> to unlock full-resolution downloads (single photos + full ZIP). Previews are watermarked until then.</p>
                    </div>
                    <?php if ($paystack_enabled === 'yes' && !empty($paystack_key)) : ?>
                        <button type="button" data-pay-balance class="h-11 px-6 rounded-full bg-slate-900 hover:bg-slate-800 text-white flex items-center justify-center gap-2 text-xs uppercase tracking-widest font-bold shadow-md shrink-0">
                            <i data-lucide="credit-card" class="h-4 w-4"></i> Pay GH₵ <?php echo number_format($balance_amount, 2); ?>
                        </button>
                    <?php else : ?>
                        <a href="mailto:<?php echo esc_attr(studio_photography_get_business_email()); ?>" class="h-11 px-6 rounded-full bg-slate-900 hover:bg-slate-800 text-white flex items-center justify-center gap-2 text-xs uppercase tracking-widest font-bold shadow-md shrink-0">
                            <i data-lucide="mail" class="h-4 w-4"></i> Contact Studio
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- 1. Cinematic Full-Bleed Hero Banner Cover (100vh) -->
            <section class="relative h-screen w-full overflow-hidden flex items-end justify-center bg-black">
                <img 
                    src="<?php echo esc_url($cover_image); ?>" 
                    alt="<?php echo esc_attr(get_the_title()); ?>" 
                    class="absolute inset-0 h-full w-full object-cover opacity-85 select-none"
                    style="object-position: <?php echo esc_attr($cover_object_position); ?>;"
                    fetchpriority="high"
                >
                <div class="absolute inset-0 bg-gradient-to-b from-black/20 via-black/10 to-black/80"></div>
                <div class="watermark-overlay"></div>
                
                <div class="relative z-10 text-center text-white pb-20 px-6 max-w-4xl select-none">
                    <span class="text-xs uppercase tracking-[0.3em] font-light text-slate-200">Exclusive Client Preview</span>
                    <h1 class="mt-4 text-4xl sm:text-6xl lg:text-7xl font-light tracking-tight font-serif text-slate-100">
                        <?php the_title(); ?>
                    </h1>
                    <div class="mt-4 flex items-center justify-center gap-2 text-sm text-slate-300 font-light flex-wrap">
                        <span>Delivered: <?php echo get_the_date('M d, Y'); ?></span>
                        <span>•</span>
                        <span><?php echo intval($display_photo_count); ?> Images</span>
                        <?php if ($expiry_state['expires'] && !$expiry_state['expired']) : ?>
                            <span>•</span>
                            <span class="<?php echo $expiry_state['days_left'] <= 7 ? 'text-amber-300 font-medium' : ''; ?>">Available until <?php echo esc_html(date('M d, Y', strtotime($expiry_state['date']))); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <button 
                        id="scroll-to-photos" 
                        class="mt-10 inline-flex h-11 px-6 items-center justify-center rounded-full border border-white/40 bg-white/10 hover:bg-white hover:text-black transition-all gap-2 text-xs uppercase tracking-widest backdrop-blur-xs font-semibold"
                    >
                        View Collection
                        <i data-lucide="chevron-down" class="h-4 w-4 animate-bounce"></i>
                    </button>
                </div>
            </section>

            <!-- 2. Minimalist Gallery Control Hub & Package Download Area -->
            <section id="collection-grid-anchor" class="py-12 bg-white select-none border-b border-slate-100">
                <div class="mx-auto max-w-7xl px-6 flex flex-col items-center text-center">
                    <?php if (get_the_content()) : ?>
                        <div class="prose max-w-2xl text-slate-500 font-light leading-relaxed mb-6 text-sm">
                            <?php the_content(); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Collection Global Download Buttons -->
                    <div class="flex flex-wrap justify-center gap-4">
                        <?php
                        $zip_download = get_post_meta(get_the_ID(), '_gallery_zip_download', true);

                        // Offer the auto-built server ZIP whenever there is anything to pack:
                        // locally uploaded photos AND/OR Google Drive / cloud photos (all fetched & zipped on the fly!)
                        $remote_photo_count = count($grid_items) - $local_attachments_count;
                        if (empty($zip_download) && ($local_attachments_count > 0 || $remote_photo_count > 0)) {
                            $zip_download = add_query_arg(array(
                                'studio_download_zip' => 1,
                                'gallery_id' => get_the_ID()
                            ), home_url('/'));
                        }
                        
                        if (!empty($zip_download)) :
                        ?>
                            <a href="<?php echo esc_url($zip_download); ?>" download id="download-zip-btn" data-dl-title="Full Collection (ZIP)" class="h-11 px-6 rounded-full bg-slate-900 hover:bg-slate-800 text-white flex items-center gap-2 text-xs uppercase tracking-widest font-semibold transition-all shadow-md">
                                <i data-lucide="download" class="h-4 w-4"></i>
                                Download Full Collection (ZIP)
                            </a>
                        <?php endif; ?>

                        <?php /* CLOUD DELIVERY BUTTONS - rendered ONLY after balance settlement
                                 (never inside the unpaid preview, so full-res files stay locked) */ ?>
                        <?php if (!$has_balance) : ?>
                            <?php if (!empty($mega_folder)) : ?>
                                <a href="<?php echo esc_url($mega_folder); ?>" target="_blank" rel="noopener" download data-dl-title="MEGA Cloud Folder" class="h-11 px-6 rounded-full bg-red-600 hover:bg-red-500 text-white flex items-center gap-2 text-xs uppercase tracking-widest font-semibold transition-all shadow-md">
                                    <i data-lucide="cloud" class="h-4 w-4"></i>
                                    Open MEGA Cloud Folder
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($gdrive_folder)) : ?>
                                <a href="<?php echo esc_url($gdrive_folder); ?>" target="_blank" rel="noopener" download data-dl-title="Cloud Folder" class="h-11 px-6 rounded-full bg-white border border-slate-300 hover:border-slate-900 text-slate-800 flex items-center gap-2 text-xs uppercase tracking-widest font-semibold transition-all shadow-md">
                                    <i data-lucide="folder-open" class="h-4 w-4"></i>
                                    <?php echo esc_html($cloud_folder_label); ?>
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <!-- 3. Pixieset-Style Justified Masonry Grid (Edge-to-Edge Fluid columns - 2 Columns on Mobile!) -->
            <section class="py-6 bg-white min-h-screen">
                <?php if (!empty($grid_items)) : ?>
                    <div class="columns-2 md:columns-3 lg:columns-4 gap-2 md:gap-3 px-2 md:px-6">
                        <?php foreach ($grid_items as $index => $item) : ?>
                            <div class="break-inside-avoid mb-2 md:mb-3 relative overflow-hidden rounded-lg bg-slate-50 shadow-xs transition-all hover:shadow-lg group">
                                <img
                                    src="<?php echo esc_url($item['thumbnail']); ?>"
                                    alt="<?php echo esc_attr($item['title']); ?>"
                                    loading="lazy" decoding="async"
                                    class="w-full h-auto object-cover cursor-pointer select-none img-trigger-lightbox"
                                    data-index="<?php echo $index; ?>"
                                >
                                <div class="watermark-overlay"></div>
                                
                                <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100 flex items-end justify-between p-4 pointer-events-none">
                                    <span class="text-white text-xs font-light tracking-wide truncate pr-4"><?php echo esc_html(wp_trim_words($item['title'], 4)); ?></span>
                                    
                                    <div class="flex items-center gap-2 pointer-events-auto">
                                        <button 
                                            class="h-9 w-9 bg-white/95 hover:bg-white text-slate-800 rounded-full flex items-center justify-center shadow-lg transition-transform hover:scale-105 btn-trigger-lightbox"
                                            data-index="<?php echo $index; ?>"
                                            title="View Slideshow"
                                        >
                                            <i data-lucide="expand" class="h-4 w-4 text-slate-800"></i>
                                        </button>
                                        
                                        <a 
                                            href="<?php echo esc_url($item['download_url']); ?>" 
                                            download 
                                            target="_blank"
                                            data-dl-title="<?php echo esc_attr($item['title']); ?>"
                                            class="h-9 w-9 bg-white/95 hover:bg-white text-slate-800 rounded-full flex items-center justify-center shadow-lg transition-transform hover:scale-105" 
                                            title="Download Photo"
                                        >
                                            <i data-lucide="download" class="h-4 w-4 text-slate-800"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <div class="max-w-md mx-auto p-12 text-center bg-white border border-dashed border-slate-200 rounded-2xl my-12">
                        <i data-lucide="image" class="mx-auto h-12 w-12 text-slate-300 mb-3"></i>
                        <h3 class="text-lg font-bold text-slate-700">No Photos Linked Yet</h3>
                        <p class="text-sm text-slate-400 mt-1 mb-6">To populate this private gallery, upload images into this post editor, paste a Google Drive, Pixieset, or Dropbox link, or list individual share links.</p>
                        <div class="max-w-xs mx-auto text-xs text-slate-400 bg-slate-50 p-3 rounded-lg text-left">
                            <span class="font-bold block text-slate-600 mb-1">💡 Quick Setup Guide:</span>
                            Upload your client's photoshoot files directly into this WordPress Client Gallery post!
                        </div>
                    </div>
                <?php endif; ?>
            </section>

            <!-- 4. IMMERSIVE CLIENT LIGHTBOX GALLERY SLIDER -->
            <div id="gallery-lightbox" class="fixed inset-0 z-50 bg-black flex flex-col justify-between p-4 opacity-0 pointer-events-none transition-opacity duration-300 select-none">
                <div class="watermark-overlay" style="z-index: 20;"></div>
                <div class="flex items-center justify-between text-white p-2 sm:px-6 relative" style="z-index: 30;">
                    <span id="lightbox-counter" class="text-sm font-light tracking-widest">1 of 12</span>
                    <div class="flex items-center gap-5">
                        <a id="lightbox-download" href="#" download target="_blank" class="hover:text-slate-300 transition-colors" title="Download High-Res">
                            <i data-lucide="download" class="h-6 w-6"></i>
                        </a>
                        <button id="lightbox-close" class="hover:text-red-400 transition-colors" title="Close Slideshow">
                            <i data-lucide="x" class="h-6 w-6"></i>
                        </button>
                    </div>
                </div>
                
                <div class="flex-1 flex items-center justify-center relative px-2 sm:px-12">
                    <button id="lightbox-prev" class="absolute left-2 sm:left-6 text-white/50 hover:text-white bg-white/5 hover:bg-white/15 h-12 w-12 rounded-full flex items-center justify-center transition-all" title="Previous Image">
                        <i data-lucide="chevron-left" class="h-6 w-6"></i>
                    </button>
                    
                    <img id="lightbox-img" src="" alt="Gallery Preview" class="max-h-[82vh] max-w-full object-contain rounded-sm shadow-2xl transition-all duration-300 select-none">
                    
                    <button id="lightbox-next" class="absolute right-2 sm:right-6 text-white/50 hover:text-white bg-white/5 hover:bg-white/15 h-12 w-12 rounded-full flex items-center justify-center transition-all" title="Next Image">
                        <i data-lucide="chevron-right" class="h-6 w-6"></i>
                    </button>
                </div>
                
                <div id="lightbox-title" class="text-center text-white/70 text-sm font-light py-4 tracking-wide truncate max-w-xl mx-auto">
                    Image Name
                </div>
            </div>
        </div>

        <!-- Dynamic Security Engine (Saves Passwords & Payments in Browser LocalStorage!) -->
        <script>
            window.gallerySecurity = {
                id: <?php echo $gallery_id; ?>,
                hasPassword: <?php echo $post_has_password ? 'true' : 'false'; ?>,
                passwordHash: "<?php echo esc_js($pass_hash); ?>",
                hasBalance: <?php echo $has_balance ? 'true' : 'false'; ?>,
                balance: <?php echo $balance_amount; ?>,
                paymentStatus: "<?php echo esc_js($payment_status); ?>",
                isAdmin: <?php echo current_user_can('manage_options') ? 'true' : 'false'; ?>
            };

            // ── LIVE DOWNLOAD TRACKER ──
            // Local/proxy downloads are logged server-side automatically; external links
            // (Google Drive / S3 / override ZIP) are tracked via this instant AJAX beacon.
            window.studioDLT = {
                ajaxUrl: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
                nonce: '<?php echo wp_create_nonce('studio_dl_track'); ?>',
                nonceCheck: '<?php echo wp_create_nonce('studio_dl_check'); ?>',
                galleryId: <?php echo (int) $gallery_id; ?>
            };
            document.addEventListener('click', function(e) {
                const link = e.target.closest('a[download]');
                if (!link || !window.studioDLT) return;
                const href = link.href || '';
                const isZip = (link.id === 'download-zip-btn');
                // Same-origin links stream through our server and are recorded there — only beacon external files
                if (href.indexOf(location.origin) === 0 || href.charAt(0) === '/') return;

                const payload = new URLSearchParams({
                    action: 'studio_track_download',
                    nonce: window.studioDLT.nonce,
                    gallery_id: window.studioDLT.galleryId,
                    type: isZip ? 'zip' : 'single',
                    item: link.getAttribute('data-dl-title') || (isZip ? 'Full Collection (ZIP)' : 'Photo')
                });
                if (navigator.sendBeacon) {
                    navigator.sendBeacon(window.studioDLT.ajaxUrl, payload);
                } else {
                    fetch(window.studioDLT.ajaxUrl, { method: 'POST', body: payload, keepalive: true, credentials: 'same-origin' }).catch(function(){});
                }
            }, true);

            // ── BLANK-PAGE FAIL-SAFE ──
            // If the gallery engine hasn't revealed anything within 2.5s (a JS error anywhere),
            // reveal the server-appropriate state so the page is NEVER blank.
            setTimeout(function() {
                const c = document.getElementById('unlocked-gallery-content');
                const p = document.getElementById('portal-password-lock');
                const revealed = (c && !c.classList.contains('hidden')) || (p && !p.classList.contains('hidden'));
                if (revealed) return;
                console.warn('[Studio] Gallery engine did not initialize in time — revealing fallback view.');
                if (window.gallerySecurity && window.gallerySecurity.hasPassword && p) {
                    p.classList.remove('hidden');
                } else if (c) {
                    c.classList.remove('hidden');
                }
            }, 2500);

            document.addEventListener('DOMContentLoaded', function() {
                // Scroll helper
                const scrollBtn = document.getElementById('scroll-to-photos');
                if (scrollBtn) {
                    scrollBtn.addEventListener('click', function() {
                        document.getElementById('collection-grid-anchor').scrollIntoView({ behavior: 'smooth' });
                    });
                }

                // ── SECURITY DECRYPTION & LOCALSTORAGE VALIDATIONS ──
                const security = window.gallerySecurity;
                if (!security) return;

                const galleryId = security.id;
                const passForm = document.getElementById('gallery-password-form');
                const passInput = document.getElementById('gallery_password');
                const passError = document.getElementById('password-error');
                
                const passLockDiv = document.getElementById('portal-password-lock');
                const balLockDiv = document.getElementById('portal-balance-lock');
                const contentDiv = document.getElementById('unlocked-gallery-content');

                // Browser Native SHA1 Encryption Helper
                async function sha1(str) {
                    let buffer = new TextEncoder("utf-8").encode(str);
                    const hash = await crypto.subtle.digest("SHA-1", buffer);
                    return Array.from(new Uint8Array(hash)).map(b => b.toString(16).padStart(2, '0')).join('');
                }

                async function verifySecurity() {
                    // 1. Validate Password Guard
                    if (security.hasPassword) {
                        const savedPass = localStorage.getItem('gallery_pass_' + galleryId);
                        let isUnlocked = false;

                        if (savedPass) {
                            const hashed = await sha1(savedPass);
                            if (hashed === security.passwordHash) {
                                isUnlocked = true;
                            }
                        }

                        if (!isUnlocked) {
                            passLockDiv.classList.remove('hidden');
                            balLockDiv.classList.add('hidden');
                            contentDiv.classList.add('hidden');
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                            return;
                        }
                    }

                    // 2. Balance Guard → PREVIEW MODE: gallery is VISIBLE with watermarked previews, downloads locked until settled
                    //    (studio admins skip the lock entirely so the photographer can always test & download)
                    if (security.hasBalance && !security.isAdmin) {
                        const isPaid = localStorage.getItem('gallery_paid_' + galleryId) === 'yes' || security.paymentStatus === 'paid';

                        if (!isPaid) {
                            passLockDiv.classList.add('hidden');
                            balLockDiv.classList.add('hidden');
                            contentDiv.classList.remove('hidden');
                            contentDiv.classList.add('gallery-previews-locked');
                            window.studioDownloadsLocked = true;
                            const banner = document.getElementById('gallery-balance-banner');
                            if (banner) banner.classList.remove('hidden');
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                            window.dispatchEvent(new Event('resize'));
                            return;
                        }
                    }

                    // 3. Reveal Grid and Collection Header! (paid / no balance)
                    passLockDiv.classList.add('hidden');
                    balLockDiv.classList.add('hidden');
                    contentDiv.classList.remove('hidden');
                    contentDiv.classList.remove('gallery-previews-locked');
                    window.studioDownloadsLocked = false;
                    const paidBanner = document.getElementById('gallery-balance-banner');
                    if (paidBanner) paidBanner.classList.add('hidden');
                    
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                    window.dispatchEvent(new Event('resize'));
                }

                // Password Form Verification Trigger
                if (passForm) {
                    passForm.addEventListener('submit', async function(e) {
                        e.preventDefault();
                        const entered = passInput.value.trim();
                        const hashed = await sha1(entered);

                        if (hashed === security.passwordHash) {
                            localStorage.setItem('gallery_pass_' + galleryId, entered);
                            passError.classList.add('hidden');
                            verifySecurity();
                        } else {
                            passError.classList.remove('hidden');
                            passInput.focus();
                        }
                    });
                }

                // Balance Paystack Payment Integration (binds to every Pay-Balance button: lock screen + sticky banner)
                function bindPayBalanceButton(payBtn) {
                    payBtn.addEventListener('click', function() {
                        const originalHtml = payBtn.innerHTML;
                        payBtn.disabled = true;
                        payBtn.innerHTML = '<i data-lucide="loader" class="h-5 w-5 animate-spin mr-2 inline"></i> Opening Paystack Secure Gateway...';
                        if (typeof lucide !== 'undefined') lucide.createIcons();

                        let handler = PaystackPop.setup({
                            key: "<?php echo esc_js($paystack_key); ?>",
                            email: "<?php echo esc_js($client_email); ?>",
                            amount: Math.round(<?php echo $balance_amount; ?> * 100),
                            currency: 'GHS',
                            ref: 'BAL-' + galleryId + '-' + Date.now(),
                            metadata: {
                                custom_fields: [
                                    { display_name: "Gallery Name", variable_name: "gallery_name", value: "<?php echo esc_js(get_the_title()); ?>" },
                                    { display_name: "Gallery ID", variable_name: "gallery_id", value: galleryId }
                                ]
                            },
                            callback: function(response) {
                                // Direct client-side unlock backup (instantly unlocks client screen)
                                localStorage.setItem('gallery_paid_' + galleryId, 'yes');

                                // Notify WordPress database via secure AJAX
                                const formData = new FormData();
                                formData.append('action', 'settle_gallery_balance');
                                formData.append('gallery_id', galleryId);
                                formData.append('paystack_reference', response.reference);

                                fetch("/wp-admin/admin-ajax.php", {
                                    method: 'POST',
                                    body: formData,
                                    credentials: 'same-origin' // passes InfinityFree security check!
                                })
                                .then(() => { window.location.reload(); })
                                .catch(() => { window.location.reload(); });
                            },
                            onClose: function() {
                                alert('Payment cancelled. Settle outstanding balance to unlock your photos.');
                                payBtn.disabled = false;
                                payBtn.innerHTML = originalHtml;
                                if (typeof lucide !== 'undefined') lucide.createIcons();
                            }
                        });
                        handler.openIframe();
                    });
                }
                document.querySelectorAll('[data-pay-balance]').forEach(bindPayBalanceButton);

                // ── DOWNLOAD FLOW (simple & bulletproof) ──
                // Clicks are native downloads. If the server must refuse (unpaid/expired/empty),
                // it redirects back here with ?studio_dl_error=... and we explain it on screen —
                // never a raw HTML page saved as "download.html".
                window.studioDownloadsLocked = window.studioDownloadsLocked || false;
                function surfacePaymentBanner() {
                    const banner = document.getElementById('gallery-balance-banner');
                    if (!banner) return;
                    banner.classList.remove('hidden');
                    banner.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    banner.classList.add('pulse-once');
                    setTimeout(() => banner.classList.remove('pulse-once'), 1300);
                }
                document.addEventListener('click', function(e) {
                    const link = e.target.closest('a[download]');
                    if (!link) return;

                    // Unpaid preview mode — block instantly with the payment banner
                    if (window.studioDownloadsLocked) {
                        e.preventDefault();
                        e.stopPropagation();
                        surfacePaymentBanner();
                        studioToast('🔒 Settle the outstanding balance to unlock downloads');
                        return;
                    }

                    // Friendly note while the server assembles the full collection ZIP
                    if (link.id === 'download-zip-btn') {
                        studioToast('📦 Preparing your collection — the first download builds it, next time is instant!');
                    }
                }, true);

                // Server refusal landing (e.g. page was stale after payment/expiry) → explain nicely
                const dlErr = new URLSearchParams(window.location.search).get('studio_dl_error');
                if (dlErr) {
                    window.history.replaceState({}, '', window.location.pathname); // clean the URL
                    if (dlErr === 'balance') {
                        surfacePaymentBanner();
                        studioToast('🔒 Settle the outstanding balance to unlock downloads');
                    } else if (dlErr === 'expired') {
                        studioToast('⏳ This gallery has expired — downloads are closed');
                    } else if (dlErr === 'empty') {
                        studioToast('📦 No photos could be packed for this gallery yet — please try again');
                    }
                }

                // 📸 Screenshot attempt on an UNPAID gallery → bring up the payment banner instantly!
                document.addEventListener('studio:screenshot-attempt', function() {
                    if (!window.gallerySecurity || !window.gallerySecurity.hasBalance) return;
                    const isPaid = localStorage.getItem('gallery_paid_' + galleryId) === 'yes' || window.gallerySecurity.paymentStatus === 'paid';
                    if (isPaid) return;
                    surfacePaymentBanner();
                    studioToast('📸 Screenshots are disabled — pay your balance to unlock clean downloads');
                });

                // ── CONTENT PROTECTION: right-click, drag, save, print, screenshot deterrence ──
                function studioToast(msg) {
                    let t = document.getElementById('studio-toast');
                    if (!t) {
                        t = document.createElement('div');
                        t.id = 'studio-toast';
                        t.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%) translateY(20px);background:#0f172a;color:#fff;padding:12px 20px;border-radius:9999px;font-size:13px;font-weight:600;z-index:99999;opacity:0;transition:all .3s;pointer-events:none;box-shadow:0 10px 25px rgba(0,0,0,.4);max-width:90vw;text-align:center;';
                        document.body.appendChild(t);
                    }
                    t.textContent = msg;
                    t.style.opacity = '1';
                    t.style.transform = 'translateX(-50%) translateY(0)';
                    clearTimeout(t._h);
                    t._h = setTimeout(() => {
                        t.style.opacity = '0';
                        t.style.transform = 'translateX(-50%) translateY(20px)';
                    }, 2600);
                }
                window.studioToast = studioToast;

                // Block right-click / long-press context menus on photos
                document.getElementById('unlocked-gallery-content').addEventListener('contextmenu', function(e) {
                    if (e.target.closest('img')) e.preventDefault();
                });
                // Block dragging images out of the page
                document.addEventListener('dragstart', function(e) {
                    if (e.target && e.target.tagName === 'IMG') e.preventDefault();
                });
                // Block Ctrl/Cmd+S (save) and Ctrl/Cmd+P (print)
                document.addEventListener('keydown', function(e) {
                    const k = (e.key || '').toLowerCase();
                    if ((e.ctrlKey || e.metaKey) && (k === 's' || k === 'p')) {
                        e.preventDefault();
                        studioToast('🔒 Saving & printing are disabled on this gallery');
                    }
                });
                // PrintScreen key: instantly clear the clipboard and warn
                document.addEventListener('keyup', function(e) {
                    if (e.key === 'PrintScreen') {
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText('').catch(function() {});
                        }
                        studioToast('📸 Screenshots are disabled — these photos are protected');
                    }
                });
                // Hide all content if the browser print dialog is triggered by any other means
                window.addEventListener('beforeprint', function() { document.body.classList.add('gallery-print-blocked'); });
                window.addEventListener('afterprint', function() { document.body.classList.remove('gallery-print-blocked'); });

                // Initialize Security Verify on Load
                verifySecurity();


                // ── IMMERSIVE LIGHTBOX SLIDER CONTROLLER ──
                const galleryData = <?php echo json_encode($grid_items); ?>;
                if (galleryData.length === 0) return;

                const lightbox = document.getElementById('gallery-lightbox');
                const lbImg = document.getElementById('lightbox-img');
                const lbCounter = document.getElementById('lightbox-counter');
                const lbDownload = document.getElementById('lightbox-download');
                const lbTitle = document.getElementById('lightbox-title');
                const lbClose = document.getElementById('lightbox-close');
                const lbPrev = document.getElementById('lightbox-prev');
                const lbNext = document.getElementById('lightbox-next');

                let currentIndex = 0;

                function openLightbox(index) {
                    currentIndex = parseInt(index);
                    updateLightboxContent();
                    lightbox.classList.remove('pointer-events-none', 'opacity-0');
                    lightbox.classList.add('opacity-100');
                    document.body.style.overflow = 'hidden';
                }

                function closeLightbox() {
                    lightbox.classList.add('pointer-events-none', 'opacity-0');
                    lightbox.classList.remove('opacity-100');
                    document.body.style.overflow = '';
                }

                function updateLightboxContent() {
                    const item = galleryData[currentIndex];
                    lbImg.classList.add('scale-95', 'opacity-50');
                    
                    setTimeout(() => {
                        lbImg.src = item.url;
                        lbImg.alt = item.title;
                        lbCounter.textContent = `${currentIndex + 1} of ${galleryData.length}`;
                        lbDownload.href = item.download_url;
                        lbDownload.setAttribute('data-dl-title', item.title);
                        lbTitle.textContent = item.title;
                        lbImg.classList.remove('scale-95', 'opacity-50');
                    }, 100);
                }

                function nextImage() {
                    currentIndex = (currentIndex + 1) % galleryData.length;
                    updateLightboxContent();
                }

                function prevImage() {
                    currentIndex = (currentIndex - 1 + galleryData.length) % galleryData.length;
                    updateLightboxContent();
                }

                // Bind Grid Items
                document.querySelectorAll('.img-trigger-lightbox, .btn-trigger-lightbox').forEach(el => {
                    el.addEventListener('click', function(e) {
                        e.preventDefault();
                        const index = this.getAttribute('data-index');
                        openLightbox(index);
                    });
                });

                // Lightbox controls
                lbClose.addEventListener('click', closeLightbox);
                lbNext.addEventListener('click', nextImage);
                lbPrev.addEventListener('click', prevImage);

                // Keyboard Navigation
                document.addEventListener('keydown', function(e) {
                    if (lightbox.classList.contains('opacity-100')) {
                        if (e.key === 'ArrowRight') nextImage();
                        else if (e.key === 'ArrowLeft') prevImage();
                        else if (e.key === 'Escape') closeLightbox();
                    }
                });

                // Swipe Gestures for Mobile Touch
                let startX = 0;
                lightbox.addEventListener('touchstart', e => { startX = e.touches[0].clientX; }, {passive: true});
                lightbox.addEventListener('touchend', e => {
                    let diffX = e.changedTouches[0].clientX - startX;
                    if (Math.abs(diffX) > 60) {
                        if (diffX > 0) prevImage(); // Swipe Right
                        else nextImage(); // Swipe Left
                    }
                }, {passive: true});
            });
        </script>

        <!-- Dynamic Diagnostics Comment Block (Visible to Admin in HTML Source) -->
        <?php if (current_user_can('manage_options')) : ?>
            <!-- 
            === GOOGLE DRIVE FOLDER DIAGNOSTICS ===
            Folder Link: <?php echo esc_html($gdrive_folder); ?>
            Extracted Folder ID: <?php echo esc_html($folder_id); ?>
            Cache Status: <?php echo $bypass_cache ? "BYPASSED" : "LOADED"; ?>
            <?php if (!empty($diagnostic_info)) : ?>
                <?php if (isset($diagnostic_info['error'])) : ?>
                    CURL Connection Error: <?php echo esc_html($diagnostic_info['error']); ?>
                <?php else : ?>
                    HTTP Status Code: <?php echo intval($diagnostic_info['status_code']); ?>
                    HTML Length: <?php echo intval($diagnostic_info['html_length']); ?> characters
                    Total Google file IDs scanned: <?php echo intval($diagnostic_info['raw_matches_count']); ?>
                    Filtered Image files loaded in grid: <?php echo intval($diagnostic_info['filtered_files_count']); ?>
                <?php endif; ?>
            <?php else : ?>
                Loaded from Transient Cache: <?php echo count($folder_files); ?> items cached.
            <?php endif; ?>
            ========================================
            -->
        <?php endif; ?>

    <?php endwhile; endif; ?>
</div>

<?php
get_footer();
?>
