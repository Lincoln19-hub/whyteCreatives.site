<?php
/**
 * Studio Photography Theme Functions
 *
 * @package Studio_Photography
 */

// ── 1. THEME SETUP & ENQUEUES ───────────────────────────────────────────────

function studio_photography_setup() {
    // Enable support for Post Thumbnails/Featured Images
    add_theme_support('post-thumbnails');
    
    // Enable support for Custom Logo upload inside Customizer
    add_theme_support('custom-logo', array(
        'height'      => 60,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ));
    
    // Register Navigation Menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'studio-photography'),
    ));
}
add_action('after_setup_theme', 'studio_photography_setup');

function studio_photography_scripts() {
    // Enqueue main stylesheet
    wp_enqueue_style('studio-photography-style', get_stylesheet_uri(), array(), '1.0.0');
}
add_action('wp_enqueue_scripts', 'studio_photography_scripts');


// ── 2. LIMIT ADMIN ACCESS EXCLUSIVELY TO THE PHOTOGRAPHER (ADMIN) ────────────

/**
 * Hide the WordPress Admin Bar on the frontend for any user who is not an administrator (the photographer).
 */
function studio_photography_disable_admin_bar_for_clients() {
    if (!current_user_can('manage_options')) {
        show_admin_bar(false);
    }
}
add_action('after_setup_theme', 'studio_photography_disable_admin_bar_for_clients');

/**
 * Block non-administrator users (clients/subscribers) from accessing the WP-Admin backend.
 * If they try to go to /wp-admin/, they are seamlessly redirected to the homepage.
 * Bypasses AJAX requests so checkout booking still completes successfully!
 */
function studio_photography_restrict_admin_backend_access() {
    if (is_admin() && !current_user_can('manage_options') && !(defined('DOING_AJAX') && DOING_AJAX)) {
        wp_redirect(home_url());
        exit;
    }
}
add_action('admin_init', 'studio_photography_restrict_admin_backend_access');


// ── 3. REGISTER CUSTOM POST TYPES & TAXONOMIES (100% EDITABLE CATEGORIES!) ───

function studio_photography_register_cpts() {
    // 1. Sessions CPT
    register_post_type('session', array(
        'labels' => array(
            'name' => __('Sessions', 'studio-photography'),
            'singular_name' => __('Session', 'studio-photography'),
            'add_new_item' => __('Add New Session', 'studio-photography'),
            'edit_item' => __('Edit Session', 'studio-photography'),
            'all_items' => __('All Sessions', 'studio-photography'),
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'thumbnail', 'excerpt'),
        'menu_icon' => 'dashicons-camera',
        'rewrite' => array('slug' => 'sessions'),
        'show_in_rest' => true,
    ));

    // 2. Session Categories Taxonomy
    register_taxonomy('session_category', 'session', array(
        'labels' => array(
            'name' => __('Session Categories', 'studio-photography'),
            'singular_name' => __('Session Category', 'studio-photography'),
            'search_items' => __('Search Categories', 'studio-photography'),
            'all_items' => __('All Categories', 'studio-photography'),
            'parent_item' => __('Parent Category', 'studio-photography'),
            'parent_item_colon' => __('Parent Category:', 'studio-photography'),
            'edit_item' => __('Edit Category', 'studio-photography'),
            'update_item' => __('Update Category', 'studio-photography'),
            'add_new_item' => __('Add New Category', 'studio-photography'),
            'new_item_name' => __('New Category Name', 'studio-photography'),
            'menu_name' => __('Categories', 'studio-photography'),
        ),
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'session-category'),
        'show_in_rest' => true,
    ));

    // 3. Packages CPT
    register_post_type('package', array(
        'labels' => array(
            'name' => __('Packages', 'studio-photography'),
            'singular_name' => __('Package', 'studio-photography'),
            'add_new_item' => __('Add New Package', 'studio-photography'),
            'edit_item' => __('Edit Package', 'studio-photography'),
            'all_items' => __('All Packages', 'studio-photography'),
        ),
        'public' => true,
        'has_archive' => false,
        'supports' => array('title', 'editor', 'excerpt'),
        'menu_icon' => 'dashicons-groups',
        'show_in_rest' => true,
    ));

    // 4. Client Galleries CPT (Private Delivery)
    register_post_type('client_gallery', array(
        'labels' => array(
            'name' => __('Client Galleries', 'studio-photography'),
            'singular_name' => __('Client Gallery', 'studio-photography'),
            'add_new_item' => __('Create New Gallery', 'studio-photography'),
            'edit_item' => __('Edit Gallery', 'studio-photography'),
            'all_items' => __('All Galleries', 'studio-photography'),
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'thumbnail'),
        'menu_icon' => 'dashicons-images-alt2',
        'rewrite' => array('slug' => 'client-gallery'),
        'show_in_rest' => true,
    ));

    // 5. Bookings CPT
    register_post_type('booking', array(
        'labels' => array(
            'name' => __('Bookings', 'studio-photography'),
            'singular_name' => __('Booking', 'studio-photography'),
            'add_new_item' => __('Add New Booking', 'studio-photography'),
            'edit_item' => __('Edit Booking', 'studio-photography'),
            'all_items' => __('All Bookings', 'studio-photography'),
        ),
        'public' => false,
        'show_ui' => true,
        'supports' => array('title'),
        'menu_icon' => 'dashicons-calendar-alt',
    ));

    // 6. Invoices CPT
    register_post_type('invoice', array(
        'labels' => array(
            'name' => __('Invoices', 'studio-photography'),
            'singular_name' => __('Invoice', 'studio-photography'),
            'add_new_item' => __('Add New Invoice', 'studio-photography'),
            'edit_item' => __('Edit Invoice', 'studio-photography'),
            'all_items' => __('All Invoices', 'studio-photography'),
        ),
        'public' => false,
        'show_ui' => true,
        'supports' => array('title'),
        'menu_icon' => 'dashicons-media-text',
    ));

    // 7. Portfolio Galleries CPT (public sample-work showcase pulled live from Google Drive!)
    register_post_type('portfolio_gallery', array(
        'labels' => array(
            'name' => __('Portfolio Galleries', 'studio-photography'),
            'singular_name' => __('Portfolio Gallery', 'studio-photography'),
            'add_new_item' => __('Add Portfolio Gallery', 'studio-photography'),
            'edit_item' => __('Edit Portfolio Gallery', 'studio-photography'),
            'all_items' => __('All Portfolio Galleries', 'studio-photography'),
        ),
        'public' => true,
        'has_archive' => false,
        'supports' => array('title', 'thumbnail'),
        'menu_icon' => 'dashicons-portfolio',
        'rewrite' => array('slug' => 'portfolio-gallery'),
        'show_in_rest' => true,
    ));

    // Portfolio categories: Weddings, Events, Portraits, etc. (admins can add more freely!)
    register_taxonomy('portfolio_category', 'portfolio_gallery', array(
        'labels' => array(
            'name' => __('Portfolio Categories', 'studio-photography'),
            'singular_name' => __('Portfolio Category', 'studio-photography'),
            'add_new_item' => __('Add New Portfolio Category', 'studio-photography'),
        ),
        'hierarchical' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
    ));
}
add_action('init', 'studio_photography_register_cpts');


// ── 4. METABOXES & CUSTOM FIELDS (NATIVE PHP IMPLEMENTATION) ─────────────────

function studio_photography_add_meta_boxes() {
    add_meta_box('session_details', __('Session Options', 'studio-photography'), 'studio_photography_session_metabox_html', 'session', 'normal', 'high');
    add_meta_box('package_details', __('Package Options', 'studio-photography'), 'studio_photography_package_metabox_html', 'package', 'normal', 'high');
    add_meta_box('booking_details', __('Booking Records', 'studio-photography'), 'studio_photography_booking_metabox_html', 'booking', 'normal', 'high');
    add_meta_box('invoice_details', __('Invoice Details', 'studio-photography'), 'studio_photography_invoice_metabox_html', 'invoice', 'normal', 'high');
    
    // Client Gallery Settings Metaboxes
    add_meta_box('gallery_cover_photo', __('🖼️ Gallery Cover Photo (Hero Banner)', 'studio-photography'), 'studio_photography_gallery_cover_metabox_html', 'client_gallery', 'side', 'high');
    add_meta_box('gallery_typography', __('🔤 Gallery Font Style', 'studio-photography'), 'studio_photography_gallery_typography_metabox_html', 'client_gallery', 'side', 'high');
    add_meta_box('gallery_expiry', __('⏳ Gallery Expiry Date', 'studio-photography'), 'studio_photography_gallery_expiry_metabox_html', 'client_gallery', 'side', 'high');
    add_meta_box('gallery_details', __('Gallery Download Link (Optional)', 'studio-photography'), 'studio_photography_gallery_metabox_html', 'client_gallery', 'side', 'default');
    add_meta_box('gallery_payment_details', __('Paystack Balance Lock Settings', 'studio-photography'), 'studio_photography_gallery_payment_metabox_html', 'client_gallery', 'normal', 'high');
    add_meta_box('gallery_s3_details', __('Google Drive / S3 / Cloud Bucket Integration', 'studio-photography'), 'studio_photography_gallery_s3_metabox_html', 'client_gallery', 'normal', 'high');
    
    // Portfolio Gallery (Google Drive) Metabox
    add_meta_box('portfolio_details', __('📂 Google Drive Portfolio Settings', 'studio-photography'), 'studio_photography_portfolio_metabox_html', 'portfolio_gallery', 'normal', 'high');

}
add_action('add_meta_boxes', 'studio_photography_add_meta_boxes');

// A. Session Metabox HTML
function studio_photography_session_metabox_html($post) {
    wp_nonce_field('session_meta_nonce_action', 'session_meta_nonce');
    
    // Crucial to load WordPress native media uploader and picker assets dynamically!
    wp_enqueue_media();
    
    $featured = get_post_meta($post->ID, '_session_featured', true) ?: 'no';
    $display_order = get_post_meta($post->ID, '_session_display_order', true) ?: 0;
    $active = get_post_meta($post->ID, '_session_active', true) ?: 'yes';
    $image_url = get_post_meta($post->ID, '_session_image', true);
    
    // Fetch gallery image IDs list (comma-separated string)
    $gallery_ids_raw = get_post_meta($post->ID, '_session_gallery_ids', true);
    $gallery_ids = !empty($gallery_ids_raw) ? array_filter(array_map('intval', explode(',', $gallery_ids_raw))) : array();
    ?>
    <table class="form-table">
        <tr>
            <th><label for="session_display_order"><?php _e('Display Order', 'studio-photography'); ?></label></th>
            <td><input type="number" id="session_display_order" name="session_display_order" value="<?php echo esc_attr($display_order); ?>" class="small-text"></td>
        </tr>
        <tr>
            <th><label><?php _e('Featured?', 'studio-photography'); ?></label></th>
            <td>
                <label><input type="radio" name="session_featured" value="yes" <?php checked($featured, 'yes'); ?>> <?php _e('Yes', 'studio-photography'); ?></label> &nbsp;&nbsp;
                <label><input type="radio" name="session_featured" value="no" <?php checked($featured, 'no'); ?>> <?php _e('No', 'studio-photography'); ?></label>
            </td>
        </tr>
        <tr>
            <th><label><?php _e('Is Active?', 'studio-photography'); ?></label></th>
            <td>
                <label><input type="radio" name="session_active" value="yes" <?php checked($active, 'yes'); ?>> <?php _e('Active', 'studio-photography'); ?></label> &nbsp;&nbsp;
                <label><input type="radio" name="session_active" value="no" <?php checked($active, 'no'); ?>> <?php _e('Inactive', 'studio-photography'); ?></label>
            </td>
        </tr>
        <tr>
            <th><label for="session_image"><?php _e('Primary Cover Image URL', 'studio-photography'); ?></label></th>
            <td>
                <input type="text" id="session_image" name="session_image" value="<?php echo esc_url_raw($image_url); ?>" class="large-text">
                <p class="description"><?php _e('Paste an image URL, or set a Featured Image on the right sidebar to use as the cover of this session.', 'studio-photography'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="session_image_urls"><?php _e('📂 Google Drive Folder / Image URLs', 'studio-photography'); ?></label></th>
            <td>
                <textarea id="session_image_urls" name="session_image_urls" rows="3" class="large-text" placeholder="Paste a Google Drive FOLDER link — every image inside loads automatically!&#10;https://drive.google.com/drive/folders/EXAMPLE?usp=sharing&#10;(individual image URLs also work, one per line)"><?php echo esc_textarea(get_post_meta($post->ID, '_session_image_urls', true)); ?></textarea>
                <p class="description"><?php _e('Optional: paste a public Google Drive <strong>folder</strong> link and every image inside it is added to this session\'s homepage slideshow automatically — no uploading, one link does it all. (Individual image URLs also work, one per line.)', 'studio-photography'); ?></p>
            </td>
        </tr>
        <tr>
            <th><?php _e('🎯 Cover Focus Point', 'studio-photography'); ?></th>
            <td>
                <!-- Hidden input storing the chosen focal point (e.g. "35% 60%") -->
                <input type="hidden" id="session_focal" name="session_focal" value="<?php echo esc_attr(get_post_meta($post->ID, '_session_focal', true)); ?>">

                <!-- Live cover preview with Pixieset-style focal point picker -->
                <div id="session-cover-preview" title="Click the photo to set the focus point" style="position: relative; width: 260px; max-width: 100%; aspect-ratio: 4 / 3; border: 1px dashed #c7d2fe; border-radius: 8px; overflow: hidden; background: #f8fafc; margin-bottom: 8px; display: flex; align-items: center; justify-content: center; cursor: crosshair;">
                    <?php
                    // Smart thumbnail: show the EXACT image the site will use as this session's cover
                    // (cover URL → Featured Image → first gallery upload → first Drive-folder photo)
                    // Drive share links are converted to direct CDN URLs — otherwise the <img> renders broken!
                    $preview_src = studio_photography_media_url($image_url, 600) ?: get_the_post_thumbnail_url($post->ID, 'large');
                    if (empty($preview_src) && !empty($gallery_ids)) {
                        $preview_src = wp_get_attachment_image_url(reset($gallery_ids), 'large');
                    }
                    if (empty($preview_src)) {
                        $extra_raw_check = get_post_meta($post->ID, '_session_image_urls', true);
                        if (!empty($extra_raw_check)) {
                            foreach (array_filter(array_map('trim', explode("\n", str_replace("\r", "", $extra_raw_check)))) as $extra_line_check) {
                                if (strpos($extra_line_check, '/folders/') !== false) {
                                    // Whole Drive folder → use its first photo
                                    $folder_preview = studio_photography_get_drive_folder_images($extra_line_check);
                                    if (!empty($folder_preview)) {
                                        $preview_src = studio_photography_sized_image_url($folder_preview[0]['url'], 600);
                                        break;
                                    }
                                } elseif (filter_var($extra_line_check, FILTER_VALIDATE_URL)) {
                                    // Individual image URL (Drive file links converted automatically)
                                    $candidate = studio_photography_media_url($extra_line_check, 600);
                                    if (!empty($candidate)) { $preview_src = $candidate; break; }
                                }
                            }
                        }
                    }
                    ?>
                    <?php if (!empty($preview_src)) : ?>
                        <img src="<?php echo esc_url($preview_src); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        <span id="session-focal-marker" style="position: absolute; width: 18px; height: 18px; margin: -9px 0 0 -9px; border: 2px solid #ffffff; border-radius: 50%; background: rgba(37, 99, 235, 0.85); box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.6), 0 1px 4px rgba(0,0,0,0.5); pointer-events: none;"></span>
                    <?php else : ?>
                        <span style="color: #94a3b8; font-size: 12px; text-align: center; padding: 10px;">No image yet — add a cover URL, Featured Image, gallery upload or Drive folder link.</span>
                    <?php endif; ?>
                </div>

                <button type="button" id="reset-session-focal-btn" class="button button-small"><?php _e('🎯 Center Focus', 'studio-photography'); ?></button>
                <p class="description" style="margin-top: 6px;"><?php _e('Click the photo to choose which part stays perfectly in frame on the homepage slideshow and booking page cards. The thumbnail always shows the image your visitors will see (cover URL, Featured Image, first gallery upload, or first Drive-folder photo).', 'studio-photography'); ?></p>
            </td>
        </tr>
    </table>
    
    <!-- DEDICATED MULTI-IMAGE GALLERY UPLOADER -->
    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
        <h4 style="margin: 0 0 10px 0; color: #1e3a8a; font-weight: bold; font-size: 13px; display: flex; align-items: center; gap: 6px;">
            <span class="dashicons dashicons-images-alt2" style="font-size: 18px; width: 18px; height: 18px; margin-top: -2px;"></span> 
            <?php _e('📸 Interactive Session Gallery (Up to 10 Images)', 'studio-photography'); ?>
        </h4>
        <p class="description" style="margin-bottom: 15px; line-height: 1.5; max-width: 900px;">
            <?php _e('Choose or upload up to 10 high-resolution pictures for this session. These images will render as a gorgeous fullscreen swipeable slideshow when clients click on this session card on your homepage!', 'studio-photography'); ?>
        </p>
        
        <!-- Hidden input storing comma-separated attachment IDs -->
        <input type="hidden" id="session_gallery_ids" name="session_gallery_ids" value="<?php echo esc_attr($gallery_ids_raw); ?>">
        
        <!-- Thumbnails Grid -->
        <div id="session-gallery-preview" style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 15px;">
            <?php
            foreach ($gallery_ids as $img_id) {
                $thumb = wp_get_attachment_image_url($img_id, 'thumbnail');
                if ($thumb) {
                    echo '<div class="gallery-image-wrapper" data-id="' . $img_id . '" style="position: relative; width: 80px; height: 80px; border: 1px solid #ddd; border-radius: 6px; overflow: hidden; background: #eee; cursor: pointer;">';
                    echo '<img src="' . esc_url($thumb) . '" style="width: 100%; height: 100%; object-fit: cover;">';
                    echo '<button type="button" class="remove-gallery-img" style="position: absolute; top: 2px; right: 2px; background: rgba(220, 38, 38, 0.85); color: #fff; border: none; border-radius: 50%; width: 18px; height: 18px; font-size: 10px; line-height: 16px; text-align: center; cursor: pointer; font-weight: bold; padding: 0;">&times;</button>';
                    echo '</div>';
                }
            }
            ?>
        </div>
        
        <!-- Action Buttons -->
        <button type="button" id="manage-session-gallery-btn" class="button button-primary" style="display: inline-flex; align-items: center; gap: 5px;">
            <span class="dashicons dashicons-admin-media" style="font-size: 16px; width: 16px; height: 16px; margin-top: 2px;"></span>
            <?php _e('Manage Gallery Images', 'studio-photography'); ?>
        </button>
        <button type="button" id="clear-session-gallery-btn" class="button button-secondary" style="margin-left: 5px; color: #dc2626; border-color: #fca5a5;">
            <?php _e('Clear All', 'studio-photography'); ?>
        </button>
    </div>

    <!-- WordPress Media Uploader JavaScript Integration -->
    <script>
    jQuery(document).ready(function($) {
        let frame;
        const $hiddenInput = $('#session_gallery_ids');
        const $previewContainer = $('#session-gallery-preview');
        const $manageBtn = $('#manage-session-gallery-btn');
        const $clearBtn = $('#clear-session-gallery-btn');

        // Manage Images Click
        $manageBtn.on('click', function(e) {
            e.preventDefault();

            // If frame already exists, open it
            if (frame) {
                frame.open();
                return;
            }

            // Create wp.media frame
            frame = wp.media({
                title: '<?php echo esc_js(__('Select or Upload Session Gallery Images (Up to 10)', 'studio-photography')); ?>',
                button: {
                    text: '<?php echo esc_js(__('Add to Session Gallery', 'studio-photography')); ?>'
                },
                multiple: true
            });

            // On selection select
            frame.on('select', function() {
                const selection = frame.state().get('selection');
                let ids = $hiddenInput.val() ? $hiddenInput.val().split(',').filter(Boolean) : [];
                
                selection.each(function(attachment) {
                    const id = attachment.id;
                    const url = attachment.attributes.sizes && attachment.attributes.sizes.thumbnail ? attachment.attributes.sizes.thumbnail.url : attachment.attributes.url;
                    
                    if (ids.indexOf(String(id)) === -1 && ids.length < 10) {
                        ids.push(String(id));
                        
                        // Append thumbnail markup
                        $previewContainer.append(`
                            <div class="gallery-image-wrapper" data-id="${id}" style="position: relative; width: 80px; height: 80px; border: 1px solid #ddd; border-radius: 6px; overflow: hidden; background: #eee; cursor: pointer;">
                                <img src="${url}" style="width: 100%; height: 100%; object-fit: cover;">
                                <button type="button" class="remove-gallery-img" style="position: absolute; top: 2px; right: 2px; background: rgba(220, 38, 38, 0.85); color: #fff; border: none; border-radius: 50%; width: 18px; height: 18px; font-size: 10px; line-height: 16px; text-align: center; cursor: pointer; font-weight: bold; padding: 0;">&times;</button>
                            </div>
                        `);
                    }
                });

                $hiddenInput.val(ids.join(','));
            });

            frame.open();
        });

        // Remove Single Image click
        $previewContainer.on('click', '.remove-gallery-img', function(e) {
            e.preventDefault();
            const $wrapper = $(this).closest('.gallery-image-wrapper');
            const id = String($wrapper.data('id'));
            let ids = $hiddenInput.val().split(',').filter(Boolean);
            
            ids = ids.filter(item => item !== id);
            $hiddenInput.val(ids.join(','));
            $wrapper.fadeOut(200, function() { $(this).remove(); });
        });

        // Clear All
        $clearBtn.on('click', function(e) {
            e.preventDefault();
            if (confirm('<?php echo esc_js(__('Are you sure you want to clear all gallery images?', 'studio-photography')); ?>')) {
                $hiddenInput.val('');
                $previewContainer.fadeOut(200, function() { $(this).empty().show(); });
            }
        });

        // ── 🎯 PIXIESET-STYLE COVER FOCUS POINT PICKER ──
        const $focalInput = $('#session_focal');
        const $coverPreview = $('#session-cover-preview');
        const $coverUrlInput = $('#session_image');
        const focalMarkerHtml = '<span id="session-focal-marker" style="position: absolute; width: 18px; height: 18px; margin: -9px 0 0 -9px; border: 2px solid #ffffff; border-radius: 50%; background: rgba(37, 99, 235, 0.85); box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.6), 0 1px 4px rgba(0,0,0,0.5); pointer-events: none;"></span>';

        function currentSessionFocal() {
            const val = String($focalInput.val() || '').trim();
            const m = val.match(/^(\d+(?:\.\d+)?)%\s+(\d+(?:\.\d+)?)%$/);
            return m ? [parseFloat(m[1]), parseFloat(m[2])] : [50, 50];
        }

        function applySessionFocal() {
            const f = currentSessionFocal();
            const img = $coverPreview.find('img')[0];
            if (!img) {
                $coverPreview.find('#session-focal-marker').hide();
                return;
            }
            img.style.objectPosition = f[0] + '% ' + f[1] + '%';
            if (!$coverPreview.find('#session-focal-marker').length) {
                $coverPreview.append(focalMarkerHtml);
            }
            $coverPreview.find('#session-focal-marker').css({ left: f[0] + '%', top: f[1] + '%' }).show();
        }

        function renderSessionCover(url) {
            if (url) {
                $coverPreview.html('<img src="' + url + '" style="width: 100%; height: 100%; object-fit: cover;">' + focalMarkerHtml);
                applySessionFocal();
            } else {
                $coverPreview.html('<span style="color: #94a3b8; font-size: 12px; text-align: center; padding: 10px;">No image yet — add a cover URL, Featured Image, gallery upload or Drive folder link.</span>');
            }
        }

        // Live-refresh the preview when the cover URL input changes
        $coverUrlInput.on('change input', function() { renderSessionCover($(this).val().trim()); });

        // Click to set the focal point (maps the click to true image coordinates)
        $coverPreview.on('click', function(e) {
            const img = $coverPreview.find('img')[0];
            if (!img || !img.naturalWidth || !img.naturalHeight) return;

            const rect = img.getBoundingClientRect();
            const W = rect.width, H = rect.height;
            const w = img.naturalWidth, h = img.naturalHeight;
            const scale = Math.max(W / w, H / h); // object-fit: cover scale

            const cur = currentSessionFocal();
            const offsetX = (W - w * scale) * (cur[0] / 100);
            const offsetY = (H - h * scale) * (cur[1] / 100);

            let fx = ((e.clientX - rect.left - offsetX) / scale) / w * 100;
            let fy = ((e.clientY - rect.top - offsetY) / scale) / h * 100;

            fx = Math.max(0, Math.min(100, Math.round(fx * 10) / 10));
            fy = Math.max(0, Math.min(100, Math.round(fy * 10) / 10));

            $focalInput.val(fx + '% ' + fy + '%');
            applySessionFocal();
        });

        // Reset back to the exact center
        $('#reset-session-focal-btn').on('click', function(e) {
            e.preventDefault();
            $focalInput.val('');
            applySessionFocal();
        });
    });
    </script>
    <?php
}

// B. Package Metabox HTML (Gorgeously Simplified!)
function studio_photography_package_metabox_html($post) {
    wp_nonce_field('package_meta_nonce_action', 'package_meta_nonce');
    
    $session_id = get_post_meta($post->ID, '_package_session_id', true);
    $price = get_post_meta($post->ID, '_package_price', true);
    $deposit_pct = get_post_meta($post->ID, '_package_deposit_percentage', true) ?: 50;
    $reschedule_allowed = get_post_meta($post->ID, '_package_reschedule_allowed', true) ?: 'yes';
    $active = get_post_meta($post->ID, '_package_active', true) ?: 'yes';

    $features_raw = get_post_meta($post->ID, '_package_features', true);
    $features_str = is_array($features_raw) ? implode("\n", $features_raw) : $features_raw;

    $sessions = get_posts(array('post_type' => 'session', 'numberposts' => -1, 'post_status' => 'publish'));
    ?>
    <table class="form-table">
        <tr>
            <th><label for="package_session_id"><?php _e('Associated Session *', 'studio-photography'); ?></label></th>
            <td>
                <select id="package_session_id" name="package_session_id" required class="regular-text">
                    <option value=""><?php _e('Select Session', 'studio-photography'); ?></option>
                    <?php foreach ($sessions as $s) : ?>
                        <option value="<?php echo esc_attr($s->ID); ?>" <?php selected($session_id, $s->ID); ?>><?php echo esc_html($s->post_title); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="package_price"><?php _e('Price (GHS) *', 'studio-photography'); ?></label></th>
            <td><input type="number" step="0.01" min="0" id="package_price" name="package_price" value="<?php echo esc_attr($price); ?>" class="regular-text" required></td>
        </tr>
        <tr>
            <th><label for="package_deposit_percentage"><?php _e('Deposit Percentage (%)', 'studio-photography'); ?></label></th>
            <td><input type="number" min="0" max="100" id="package_deposit_percentage" name="package_deposit_percentage" value="<?php echo esc_attr($deposit_pct); ?>" class="small-text"></td>
        </tr>
        <tr>
            <th><label><?php _e('Rescheduling Allowed?', 'studio-photography'); ?></label></th>
            <td>
                <label><input type="radio" name="package_reschedule_allowed" value="yes" <?php checked($reschedule_allowed, 'yes'); ?>> <?php _e('Yes', 'studio-photography'); ?></label> &nbsp;&nbsp;
                <label><input type="radio" name="package_reschedule_allowed" value="no" <?php checked($reschedule_allowed, 'no'); ?>> <?php _e('No', 'studio-photography'); ?></label>
            </td>
        </tr>
        <tr>
            <th><label><?php _e('Is Active?', 'studio-photography'); ?></label></th>
            <td>
                <label><input type="radio" name="package_active" value="yes" <?php checked($active, 'yes'); ?>> <?php _e('Active', 'studio-photography'); ?></label> &nbsp;&nbsp;
                <label><input type="radio" name="package_active" value="no" <?php checked($active, 'no'); ?>> <?php _e('Inactive', 'studio-photography'); ?></label>
            </td>
        </tr>
        <tr>
            <th><label for="package_features"><?php _e('Bullet Points / Features List', 'studio-photography'); ?></label></th>
            <td>
                <textarea id="package_features" name="package_features" rows="6" class="large-text" placeholder="e.g.&#10;1 Hour Session&#10;1 Outfit Change&#10;10 High-Res Edited Images&#10;Private Client Gallery" style="width: 100%;"><?php echo esc_textarea($features_str); ?></textarea>
                <p class="description"><?php _e('Type your package bullet features (enter <strong>one item per line</strong>). These will render as styled checklist points inside your website pricing cards automatically!', 'studio-photography'); ?></p>
            </td>
        </tr>
    </table>
    <?php
}

// C. Booking Metabox HTML
function studio_photography_booking_metabox_html($post) {
    wp_nonce_field('booking_meta_nonce_action', 'booking_meta_nonce');
    
    $client_name = get_post_meta($post->ID, '_booking_client_name', true);
    $client_email = get_post_meta($post->ID, '_booking_client_email', true);
    $client_phone = get_post_meta($post->ID, '_booking_client_phone', true);
    $event_date = get_post_meta($post->ID, '_booking_event_date', true);
    $location = get_post_meta($post->ID, '_booking_location', true);
    $session_id = get_post_meta($post->ID, '_booking_session_id', true);
    $package_id = get_post_meta($post->ID, '_booking_package_id', true);
    $status = get_post_meta($post->ID, '_booking_status', true) ?: 'pending';
    $amount_total = get_post_meta($post->ID, '_booking_amount_total', true);
    $amount_deposit = get_post_meta($post->ID, '_booking_amount_deposit', true);
    $paystack_ref = get_post_meta($post->ID, '_booking_paystack_reference', true);
    $discount = get_post_meta($post->ID, '_booking_discount', true) ?: 0.00;

    $session_post = get_post($session_id);
    $package_post = get_post($package_id);
    ?>
    <table class="form-table">
        <tr>
            <th><label><?php _e('Booking Status', 'studio-photography'); ?></label></th>
            <td>
                <select name="booking_status" class="regular-text">
                    <option value="pending" <?php selected($status, 'pending'); ?>><?php _e('Pending', 'studio-photography'); ?></option>
                    <option value="confirmed" <?php selected($status, 'confirmed'); ?>><?php _e('Confirmed', 'studio-photography'); ?></option>
                    <option value="completed" <?php selected($status, 'completed'); ?>><?php _e('Completed', 'studio-photography'); ?></option>
                    <option value="cancelled" <?php selected($status, 'cancelled'); ?>><?php _e('Cancelled', 'studio-photography'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label><?php _e('Customer Name', 'studio-photography'); ?></label></th>
            <td><input type="text" name="booking_client_name" value="<?php echo esc_attr($client_name); ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label><?php _e('Customer Email', 'studio-photography'); ?></label></th>
            <td><input type="email" name="booking_client_email" value="<?php echo esc_attr($client_email); ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label><?php _e('Customer Phone', 'studio-photography'); ?></label></th>
            <td><input type="text" name="booking_client_phone" value="<?php echo esc_attr($client_phone); ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label><?php _e('Shoot Date', 'studio-photography'); ?></label></th>
            <td><input type="date" name="booking_event_date" value="<?php echo esc_attr($event_date); ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label><?php _e('Shoot Location', 'studio-photography'); ?></label></th>
            <td><input type="text" name="booking_location" value="<?php echo esc_attr($location); ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label><?php _e('Session Selected', 'studio-photography'); ?></label></th>
            <td>
                <select name="booking_session_id" class="regular-text">
                    <option value=""><?php _e('Select Session Type', 'studio-photography'); ?></option>
                    <?php
                    $sessions = get_posts(array('post_type' => 'session', 'numberposts' => -1, 'post_status' => 'publish'));
                    foreach ($sessions as $s) {
                        echo '<option value="' . esc_attr($s->ID) . '" ' . selected($session_id, $s->ID, false) . '>' . esc_html($s->post_title) . '</option>';
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><label><?php _e('Package Selected', 'studio-photography'); ?></label></th>
            <td>
                <select name="booking_package_id" class="regular-text">
                    <option value=""><?php _e('Select Pricing Package', 'studio-photography'); ?></option>
                    <?php
                    $packages = get_posts(array('post_type' => 'package', 'numberposts' => -1, 'post_status' => 'publish'));
                    foreach ($packages as $p) {
                        echo '<option value="' . esc_attr($p->ID) . '" ' . selected($package_id, $p->ID, false) . '>' . esc_html($p->post_title) . '</option>';
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="booking_amount_total"><?php _e('Total Price (GHS)', 'studio-photography'); ?></label></th>
            <td><input type="number" step="0.01" min="0" id="booking_amount_total" name="booking_amount_total" value="<?php echo esc_attr($amount_total); ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label for="booking_discount"><?php _e('Discount Applied (GHS)', 'studio-photography'); ?></label></th>
            <td>
                <input type="number" step="0.01" min="0" id="booking_discount" name="booking_discount" value="<?php echo esc_attr($discount); ?>" class="regular-text">
                <p class="description"><?php _e('Enter a custom GHS discount amount (e.g. 50.00). The system will automatically subtract this discount from their final invoice total and remaining balance!', 'studio-photography'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="booking_amount_deposit"><?php _e('Deposit Required (GHS)', 'studio-photography'); ?></label></th>
            <td>
                <input type="number" step="0.01" min="0" id="booking_amount_deposit" name="booking_amount_deposit" value="<?php echo esc_attr($amount_deposit); ?>" class="regular-text">
                <?php if ($paystack_ref) : ?>
                    <p style="color: #16a34a; margin-top: 5px;"><strong><?php _e('✓ Paystack Reference:', 'studio-photography'); ?></strong> <?php echo esc_html($paystack_ref); ?> (PAID)</p>
                <?php endif; ?>
            </td>
        </tr>
    </table>
    <?php
}

// D. Invoice Metabox HTML
function studio_photography_invoice_metabox_html($post) {
    wp_nonce_field('invoice_meta_nonce_action', 'invoice_meta_nonce');
    wp_enqueue_media(); // native media uploader for the invoice logo
    
    $inv_num = get_post_meta($post->ID, '_invoice_number', true);
    if (empty($inv_num) && !empty($post->post_title) && strpos($post->post_title, 'Auto Draft') === false) {
        $inv_num = $post->post_title;
    }
    
    $client_name = get_post_meta($post->ID, '_invoice_client_name', true);
    $total = get_post_meta($post->ID, '_invoice_total', true);
    $status = get_post_meta($post->ID, '_invoice_status', true) ?: 'unpaid';
    $due_date = get_post_meta($post->ID, '_invoice_due_date', true);
    $paystack_ref = get_post_meta($post->ID, '_invoice_paystack_reference', true);
    ?>
    <table class="form-table">
        <tr>
            <th><label><?php _e('Invoice ID / Code', 'studio-photography'); ?></label></th>
            <td><input type="text" name="invoice_number" value="<?php echo esc_attr($inv_num); ?>" class="regular-text" readonly></td>
        </tr>
        <tr>
            <th><label><?php _e('Client / Payer', 'studio-photography'); ?></label></th>
            <td><input type="text" name="invoice_client_name" value="<?php echo esc_attr($client_name); ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label><?php _e('Total Amount (GHS)', 'studio-photography'); ?></label></th>
            <td><input type="number" step="0.01" name="invoice_total" value="<?php echo esc_attr($total); ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label><?php _e('Invoice Status', 'studio-photography'); ?></label></th>
            <td>
                <select name="invoice_status" class="regular-text">
                    <option value="unpaid" <?php selected($status, 'unpaid'); ?>><?php _e('Unpaid', 'studio-photography'); ?></option>
                    <option value="paid" <?php selected($status, 'paid'); ?>><?php _e('Paid', 'studio-photography'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label><?php _e('Due Date', 'studio-photography'); ?></label></th>
            <td><input type="date" name="invoice_due_date" value="<?php echo esc_attr($due_date); ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label><?php _e('Shoot Date', 'studio-photography'); ?></label></th>
            <td><input type="date" name="invoice_event_date" value="<?php echo esc_attr(get_post_meta($post->ID, '_invoice_event_date', true)); ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label><?php _e('Shoot Location', 'studio-photography'); ?></label></th>
            <td><input type="text" name="invoice_shoot_location" value="<?php echo esc_attr(get_post_meta($post->ID, '_invoice_shoot_location', true)); ?>" class="regular-text" placeholder="e.g. Labadi Beach, Accra"></td>
        </tr>
        <tr>
            <th><label><?php _e('Shoot / Session Type', 'studio-photography'); ?></label></th>
            <td><input type="text" name="invoice_shoot_type" value="<?php echo esc_attr(get_post_meta($post->ID, '_invoice_shoot_type', true)); ?>" class="regular-text" placeholder="e.g. Outdoor Portrait"></td>
        </tr>
        <tr>
            <th><label><?php _e('🖼️ Invoice Logo (High-Res)', 'studio-photography'); ?></label></th>
            <td>
                <?php $inv_logo_id = (int) get_post_meta($post->ID, '_invoice_logo_id', true); ?>
                <input type="hidden" name="invoice_logo_id" id="invoice-logo-id" value="<?php echo esc_attr($inv_logo_id); ?>">
                <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                    <div id="invoice-logo-preview" style="width: 150px; height: 84px; border: 1px dashed #c7d2fe; border-radius: 8px; background: #f8fafc; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                        <?php if ($inv_logo_id) : $inv_logo_url = wp_get_attachment_image_url($inv_logo_id, 'medium'); ?>
                            <img src="<?php echo esc_url($inv_logo_url); ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                        <?php else : ?>
                            <span style="color: #94a3b8; font-size: 11px;">No invoice logo — site logo is used</span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <button type="button" id="invoice-logo-upload" class="button">Upload / Choose Logo</button>
                        <button type="button" id="invoice-logo-remove" class="button" style="color: #dc2626; border-color: #fca5a5; <?php echo $inv_logo_id ? '' : 'display:none;'; ?>">Remove</button>
                        <p class="description" style="max-width: 320px; margin-top: 4px;">Upload a high-resolution logo for this invoice's receipt &amp; PDF. Falls back to your site logo.</p>
                    </div>
                </div>
                <script>
                jQuery(function($) {
                    var frame;
                    $('#invoice-logo-upload').on('click', function(e) {
                        e.preventDefault();
                        if (frame) { frame.open(); return; }
                        frame = wp.media({ title: 'Choose Invoice Logo (High-Res)', button: { text: 'Use as Invoice Logo' }, library: { type: 'image' }, multiple: false });
                        frame.on('select', function() {
                            var att = frame.state().get('selection').first().toJSON();
                            $('#invoice-logo-id').val(att.id);
                            var thumb = (att.sizes && att.sizes.medium) ? att.sizes.medium.url : att.url;
                            $('#invoice-logo-preview').html('<img src="' + thumb + '" style="max-width:100%;max-height:100%;object-fit:contain;">');
                            $('#invoice-logo-remove').show();
                        });
                        frame.open();
                    });
                    $('#invoice-logo-remove').on('click', function(e) {
                        e.preventDefault();
                        $('#invoice-logo-id').val('');
                        $('#invoice-logo-preview').html('<span style="color:#94a3b8;font-size:11px;">No invoice logo — site logo is used</span>');
                        $(this).hide();
                    });
                });
                </script>
            </td>
        </tr>
        <tr>
            <th><label><?php _e('Package Name (Custom)', 'studio-photography'); ?></label></th>
            <td>
                <input type="text" name="invoice_package_name" value="<?php echo esc_attr(get_post_meta($post->ID, '_invoice_package_name', true)); ?>" class="regular-text" placeholder="e.g. Premium Bridal Special">
                <p class="description"><?php _e('Type any package name — it appears on the printed invoice even if it is not in your saved Packages list.', 'studio-photography'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label><?php _e('Notes for the Client', 'studio-photography'); ?></label></th>
            <td><textarea name="invoice_shoot_notes" rows="3" class="large-text" style="resize: vertical;" placeholder="Any special arrangement, add-ons or message to display on the invoice…"><?php echo esc_textarea(get_post_meta($post->ID, '_invoice_shoot_notes', true)); ?></textarea></td>
        </tr>
        <?php if ($paystack_ref) : ?>
            <tr>
                <th><label><?php _e('Paystack Payment Ref', 'studio-photography'); ?></label></th>
                <td><span style="color: #16a34a; font-weight: bold;"><?php echo esc_html($paystack_ref); ?></span></td>
            </tr>
        <?php endif; ?>
    </table>

    <!-- 🧾 ITEMIZED SERVICES BUILDER (WP-ADMIN) -->
    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
        <h4 style="margin: 0 0 6px 0; color: #1e3a8a; font-weight: bold; font-size: 13px;"><?php _e('🧾 Invoice Items (Add Each Service)', 'studio-photography'); ?></h4>
        <p class="description" style="margin-bottom: 12px;">Add each service the client is billed for. The Total Amount above auto-calculates as you type — and the printed receipt shows every line.</p>
        <input type="hidden" name="invoice_items_json" id="wpinv-items-json" value="">
        <div id="wpinv-items-list" style="display: flex; flex-direction: column; gap: 8px; max-width: 760px;">
            <?php $existing_items = get_post_meta($post->ID, '_invoice_items', true); ?>
            <?php if (!empty($existing_items) && is_array($existing_items)) : foreach ($existing_items as $it) : ?>
                <div class="wpinv-row" style="display: flex; gap: 8px; align-items: center;">
                    <input type="text" class="wpinv-desc regular-text" style="flex: 1; min-width: 0;" placeholder="e.g. 2-Hour Outdoor Portrait Session" value="<?php echo esc_attr($it['desc']); ?>">
                    <input type="number" class="wpinv-qty small-text" min="1" step="1" value="<?php echo esc_attr($it['qty']); ?>" title="Qty" style="width: 60px; text-align: center;">
                    <input type="number" class="wpinv-price small-text" min="0" step="0.01" value="<?php echo esc_attr($it['price']); ?>" placeholder="0.00" title="Unit price (GHS)" style="width: 90px; text-align: right;">
                    <button type="button" class="button wpinv-remove" style="color: #dc2626; border-color: #fca5a5;" title="Remove">&times;</button>
                </div>
            <?php endforeach; endif; ?>
        </div>
        <button type="button" id="wpinv-add-item" class="button" style="margin-top: 10px; border-style: dashed !important;">+ Add Item</button>
    </div>

    <script>
    jQuery(function($) {
        var list = $('#wpinv-items-list');
        var jsonField = $('#wpinv-items-json');

        function collect() {
            var items = [];
            list.find('.wpinv-row').each(function() {
                items.push({
                    d: $(this).find('.wpinv-desc').val().trim(),
                    q: parseInt($(this).find('.wpinv-qty').val(), 10) || 1,
                    p: parseFloat($(this).find('.wpinv-price').val()) || 0
                });
            });
            return items;
        }
        function recalc() {
            var items = collect(), total = 0;
            $.each(items, function(i, it) { total += it.q * it.p; });
            jsonField.val(JSON.stringify(items));
            if (items.length > 0) $('input[name="invoice_total"]').val(total.toFixed(2));
        }

        $('#wpinv-add-item').on('click', function(e) {
            e.preventDefault();
            var row = $('<div class="wpinv-row" style="display: flex; gap: 8px; align-items: center;"></div>');
            row.append('<input type="text" class="wpinv-desc regular-text" style="flex: 1; min-width: 0;" placeholder="e.g. 2-Hour Outdoor Portrait Session">');
            row.append('<input type="number" class="wpinv-qty small-text" min="1" step="1" value="1" title="Qty" style="width: 60px; text-align: center;">');
            row.append('<input type="number" class="wpinv-price small-text" min="0" step="0.01" placeholder="0.00" title="Unit price (GHS)" style="width: 90px; text-align: right;">');
            row.append('<button type="button" class="button wpinv-remove" style="color: #dc2626; border-color: #fca5a5;" title="Remove">&times;</button>');
            list.append(row);
            row.find('.wpinv-desc').focus();
            recalc();
        });

        list.on('input', '.wpinv-row input', recalc);
        list.on('click', '.wpinv-remove', function() {
            $(this).closest('.wpinv-row').fadeOut(150, function() { $(this).remove(); recalc(); });
        });

        recalc(); // sync hidden JSON with any pre-existing rows on load
    });
    </script>
    <?php
}

// E. Client Gallery Download Link Side Metabox HTML
function studio_photography_gallery_metabox_html($post) {
    wp_nonce_field('gallery_meta_nonce_action', 'gallery_meta_nonce');
    $zip_download = get_post_meta($post->ID, '_gallery_zip_download', true);
    ?>
    <div style="padding: 5px 0;">
        <label for="gallery_zip_download" style="font-weight: bold; display: block; margin-bottom: 5px;"><?php _e('ZIP Download Override URL', 'studio-photography'); ?></label>
        <input type="text" id="gallery_zip_download" name="gallery_zip_download" value="<?php echo esc_attr($zip_download); ?>" style="width: 100%; padding: 6px;" placeholder="e.g. Google Drive custom zip URL">
        <p class="description" style="margin-top: 5px;"><?php _e('Leave blank to let WordPress automatically generate and compile a ZIP file of all uploaded photos on-the-fly!', 'studio-photography'); ?></p>
    </div>
    <?php
}

// E2. Client Gallery Cover Photo Metabox HTML (Manual Hero Banner Override!)
function studio_photography_gallery_cover_metabox_html($post) {
    wp_nonce_field('gallery_cover_meta_nonce_action', 'gallery_cover_meta_nonce');

    // Crucial to load WordPress native media uploader and picker assets dynamically!
    wp_enqueue_media();

    $cover_url = get_post_meta($post->ID, '_gallery_cover_url', true);
    $cover_focal = get_post_meta($post->ID, '_gallery_cover_focal', true);
    $featured_cover = get_the_post_thumbnail_url($post->ID, 'full');
    // Convert Drive share links to direct CDN URLs so the preview <img> always renders!
    $effective_cover = !empty($cover_url) ? studio_photography_media_url($cover_url, 600) : $featured_cover;
    $focal_parts = (!empty($cover_focal) && preg_match('/^(\d{1,3}(?:\.\d+)?)%\s+(\d{1,3}(?:\.\d+)?)%$/', $cover_focal, $focal_match)) ? array($focal_match[1] . '%', $focal_match[2] . '%') : array('50%', '50%');
    ?>
    <div style="padding: 5px 0;">
        <!-- Hidden inputs storing the chosen cover image URL + focal point -->
        <input type="hidden" id="gallery_cover_url" name="gallery_cover_url" value="<?php echo esc_url($cover_url); ?>">
        <input type="hidden" id="gallery_cover_focal" name="gallery_cover_focal" value="<?php echo esc_attr($cover_focal); ?>">

        <!-- Live Cover Preview (16:9 cinematic crop) with Pixieset-style Focal Point picker -->
        <div id="gallery-cover-preview" data-fallback="<?php echo esc_url((string) $featured_cover); ?>" title="Click the photo to set the focal point" style="position: relative; width: 100%; aspect-ratio: 16 / 9; border: 1px dashed #c7d2fe; border-radius: 8px; overflow: hidden; background: #f8fafc; margin-bottom: 10px; display: flex; align-items: center; justify-content: center; cursor: crosshair;">
            <?php if (!empty($effective_cover)) : ?>
                <img src="<?php echo esc_url($effective_cover); ?>" style="width: 100%; height: 100%; object-fit: cover; object-position: <?php echo esc_attr($focal_parts[0] . ' ' . $focal_parts[1]); ?>;">
                <span id="gallery-cover-focal-marker" style="position: absolute; left: <?php echo esc_attr($focal_parts[0]); ?>; top: <?php echo esc_attr($focal_parts[1]); ?>; width: 18px; height: 18px; margin: -9px 0 0 -9px; border: 2px solid #ffffff; border-radius: 50%; background: rgba(37, 99, 235, 0.85); box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.6), 0 1px 4px rgba(0,0,0,0.5); pointer-events: none;"></span>
            <?php else : ?>
                <span style="color: #94a3b8; font-size: 12px; text-align: center; padding: 10px;">No cover set yet.<br>Featured image or the first photo will be used.</span>
            <?php endif; ?>
        </div>

        <!-- Paste-friendly source: any image URL — including Google Drive share links! -->
        <input type="text" id="gallery-cover-url-input" placeholder="…or paste an image URL / Google Drive file link" value="<?php echo esc_url($cover_url); ?>" style="width: 100%; margin-bottom: 8px; box-sizing: border-box;">

        <!-- 🎯 Pixieset-style focal point helper -->
        <p style="margin: 0 0 8px 0; font-size: 12px; color: #1e3a8a; font-weight: 600;"><?php _e('🎯 Focal Point: click the photo above to choose which part stays perfectly in frame on phones & wide screens.', 'studio-photography'); ?></p>

        <!-- Action Buttons -->
        <button type="button" id="set-gallery-cover-btn" class="button button-primary" style="display: inline-flex; align-items: center; gap: 5px;">
            <span class="dashicons dashicons-format-image" style="font-size: 16px; width: 16px; height: 16px; margin-top: 3px;"></span>
            <?php echo !empty($cover_url) ? esc_html__('Change Cover Photo', 'studio-photography') : esc_html__('Set Cover Photo', 'studio-photography'); ?>
        </button>
        <button type="button" id="remove-gallery-cover-btn" class="button button-link-delete" style="margin-left: 5px; <?php echo empty($cover_url) ? 'display:none;' : ''; ?>">
            <?php _e('Remove', 'studio-photography'); ?>
        </button>
        <button type="button" id="reset-cover-focal-btn" class="button" style="margin-left: 5px;">
            <?php _e('🎯 Center Focus', 'studio-photography'); ?>
        </button>

        <p class="description" style="margin-top: 8px; line-height: 1.5;">
            <?php _e('Manually choose the big cinematic cover banner shown at the top of this client\'s private gallery page. This overrides the Featured Image and the first gallery photo. The cover is automatically excluded from the photo grid and the downloadable ZIP — no duplicates!', 'studio-photography'); ?>
        </p>
    </div>

    <!-- WordPress Media Uploader JavaScript Integration -->
    <script>
    jQuery(document).ready(function($) {
        let coverFrame;
        const $hiddenInput = $('#gallery_cover_url');
        const $focalInput = $('#gallery_cover_focal');
        const $preview = $('#gallery-cover-preview');
        const $setBtn = $('#set-gallery-cover-btn');
        const $removeBtn = $('#remove-gallery-cover-btn');
        const $resetFocalBtn = $('#reset-cover-focal-btn');
        const fallbackUrl = String($preview.data('fallback') || '');
        const $urlInput = $('#gallery-cover-url-input');
        function toDirectUrl(u) {
            u = String(u || '');
            const m = u.match(/drive\.google\.com[^\s]*?(?:\/file\/d\/|[?&]id=)([a-zA-Z0-9_-]+)/);
            return m ? 'https://lh3.googleusercontent.com/d/' + m[1] : u;
        }
        $urlInput.on('change input', function() {
            const v = $(this).val().trim();
            $hiddenInput.val(v);
            renderPreview(toDirectUrl(v));
        });
        const markerHtml = '<span id="gallery-cover-focal-marker" style="position: absolute; width: 18px; height: 18px; margin: -9px 0 0 -9px; border: 2px solid #ffffff; border-radius: 50%; background: rgba(37, 99, 235, 0.85); box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.6), 0 1px 4px rgba(0,0,0,0.5); pointer-events: none;"></span>';

        function currentFocal() {
            const val = String($focalInput.val() || '').trim();
            const m = val.match(/^(\d+(?:\.\d+)?)%\s+(\d+(?:\.\d+)?)%$/);
            return m ? [parseFloat(m[1]), parseFloat(m[2])] : [50, 50];
        }

        function applyFocal() {
            const f = currentFocal();
            const pos = f[0] + '% ' + f[1] + '%';
            const img = $preview.find('img')[0];
            if (!img) {
                $preview.find('#gallery-cover-focal-marker').hide();
                return;
            }
            img.style.objectPosition = pos;
            if (!$preview.find('#gallery-cover-focal-marker').length) {
                $preview.append(markerHtml);
            }
            $preview.find('#gallery-cover-focal-marker').css({ left: f[0] + '%', top: f[1] + '%' }).show();
        }

        function renderPreview(url) {
            const shown = url || fallbackUrl;
            if (shown) {
                $preview.html('<img src="' + shown + '" style="width: 100%; height: 100%; object-fit: cover;">' + markerHtml);
                $setBtn.html('<span class="dashicons dashicons-format-image" style="font-size: 16px; width: 16px; height: 16px; margin-top: 3px;"></span> Change Cover Photo');
                $removeBtn.show();
                applyFocal();
            } else {
                $preview.html('<span style="color: #94a3b8; font-size: 12px; text-align: center; padding: 10px;">No cover set yet.<br>Featured image or the first photo will be used.</span>');
                $setBtn.html('<span class="dashicons dashicons-format-image" style="font-size: 16px; width: 16px; height: 16px; margin-top: 3px;"></span> Set Cover Photo');
                $removeBtn.hide();
            }
        }

        // 🎯 Pixieset-style focal point: click the preview to choose which part stays in focus!
        $preview.on('click', function(e) {
            const img = $preview.find('img')[0];
            if (!img || !img.naturalWidth || !img.naturalHeight) return;

            const rect = img.getBoundingClientRect();
            const W = rect.width, H = rect.height;
            const w = img.naturalWidth, h = img.naturalHeight;
            const scale = Math.max(W / w, H / h); // object-fit: cover scale

            // Account for the current object-position offset of the cover-scaled image
            const cur = currentFocal();
            const offsetX = (W - w * scale) * (cur[0] / 100);
            const offsetY = (H - h * scale) * (cur[1] / 100);

            // Map the clicked pixel to true image percentage coordinates
            const clickX = e.clientX - rect.left;
            const clickY = e.clientY - rect.top;
            let fx = ((clickX - offsetX) / scale) / w * 100;
            let fy = ((clickY - offsetY) / scale) / h * 100;

            fx = Math.max(0, Math.min(100, Math.round(fx * 10) / 10));
            fy = Math.max(0, Math.min(100, Math.round(fy * 10) / 10));

            $focalInput.val(fx + '% ' + fy + '%');
            applyFocal();
        });

        // Reset focal point back to the exact center
        $resetFocalBtn.on('click', function(e) {
            e.preventDefault();
            $focalInput.val('');
            applyFocal();
        });

        // Open the native WP media library picker
        $setBtn.on('click', function(e) {
            e.preventDefault();

            if (coverFrame) {
                coverFrame.open();
                return;
            }

            coverFrame = wp.media({
                title: '<?php echo esc_js(__('Choose Gallery Cover Photo', 'studio-photography')); ?>',
                button: { text: '<?php echo esc_js(__('Use as Cover', 'studio-photography')); ?>' },
                library: { type: 'image' },
                multiple: false
            });

            coverFrame.on('select', function() {
                const attachment = coverFrame.state().get('selection').first().toJSON();
                // Prefer the full-size URL so the 100vh hero banner stays sharp!
                const url = attachment.sizes && attachment.sizes.full ? attachment.sizes.full.url : attachment.url;
                $hiddenInput.val(url);
                $urlInput.val(url);
                renderPreview(url);
            });

            coverFrame.open();
        });

        // Clear the manual cover (falls back to featured image → first photo)
        $removeBtn.on('click', function(e) {
            e.preventDefault();
            $hiddenInput.val('');
            renderPreview('');
        });
    });
    </script>
    <?php
}

// E2b. Get a session's cover focus point for object-position CSS (defaults to center)
function studio_photography_get_session_focal($session_id) {
    $focal = get_post_meta(intval($session_id), '_session_focal', true);
    return (!empty($focal) && preg_match('/^\d{1,3}(\.\d+)?%\s+\d{1,3}(\.\d+)?%$/', $focal)) ? $focal : 'center';
}

// E3. Curated Gallery Font List (Pixieset-style typography picker!)
function studio_photography_get_gallery_fonts() {

    return array(
        ''                   => array('label' => '🎨 Theme Default (Elegant Serif)', 'gq' => ''),
        'Playfair Display'   => array('label' => 'Playfair Display — Elegant Serif', 'gq' => 'Playfair+Display:wght@400;500;600;700'),
        'Cormorant Garamond' => array('label' => 'Cormorant Garamond — Classic Romance', 'gq' => 'Cormorant+Garamond:wght@300;400;500;600'),
        'Italiana'           => array('label' => 'Italiana — High Fashion', 'gq' => 'Italiana'),
        'Marcellus'          => array('label' => 'Marcellus — Timeless Classic', 'gq' => 'Marcellus'),
        'Josefin Sans'       => array('label' => 'Josefin Sans — Modern Minimal', 'gq' => 'Josefin+Sans:wght@300;400;600'),
        'Poppins'            => array('label' => 'Poppins — Clean Contemporary', 'gq' => 'Poppins:wght@300;400;500;600'),
        'Montserrat'         => array('label' => 'Montserrat — Bold Modern', 'gq' => 'Montserrat:wght@300;400;500;600'),
        'Great Vibes'        => array('label' => 'Great Vibes — Signature Script', 'gq' => 'Great+Vibes'),
        'Dancing Script'     => array('label' => 'Dancing Script — Handwritten Charm', 'gq' => 'Dancing+Script:wght@400;500;600'),
    );
}

// E4. Client Gallery Typography Metabox HTML (Pixieset-style font picker!)
function studio_photography_gallery_typography_metabox_html($post) {
    wp_nonce_field('gallery_typography_nonce_action', 'gallery_typography_nonce');

    $gallery_font = get_post_meta($post->ID, '_gallery_font', true);
    $fonts = studio_photography_get_gallery_fonts();

    // Build one combined Google Fonts URL so the admin picker can live-preview every option
    $gq_parts = array();
    foreach ($fonts as $family => $data) {
        if (!empty($data['gq'])) {
            $gq_parts[] = 'family=' . $data['gq'];
        }
    }
    $fonts_css_url = 'https://fonts.googleapis.com/css2?' . implode('&', $gq_parts) . '&display=swap';
    ?>
    <link rel="stylesheet" href="<?php echo esc_url($fonts_css_url); ?>">
    <div style="padding: 5px 0;">
        <label for="gallery_font" style="font-weight: bold; display: block; margin-bottom: 5px;"><?php _e('Gallery Font Style', 'studio-photography'); ?></label>
        <select id="gallery_font" name="gallery_font" style="width: 100%; padding: 6px;">
            <?php foreach ($fonts as $family => $data) : ?>
                <option value="<?php echo esc_attr($family); ?>" <?php selected($gallery_font, $family); ?>><?php echo esc_html($data['label']); ?></option>
            <?php endforeach; ?>
        </select>

        <!-- Live preview of the chosen typography on a cinematic dark backdrop -->
        <div style="margin-top: 10px; padding: 14px 10px; border: 1px dashed #e2e8f0; border-radius: 8px; background: #0f172a; text-align: center;">
            <span id="gallery-font-preview" style="color: #f1f5f9; font-size: 20px; line-height: 1.4; display: block;"><?php echo esc_html(get_the_title($post->ID) ?: 'Sarah & John — Wedding Gallery'); ?></span>
            <span style="color: #94a3b8; font-size: 10px; letter-spacing: 0.25em; text-transform: uppercase; display: block; margin-top: 6px;">Exclusive Client Preview</span>
        </div>

        <p class="description" style="margin-top: 8px; line-height: 1.5;">
            <?php _e('Choose the typography used for this client\'s private gallery page (gallery title & headings). "Theme Default" keeps the studio\'s elegant serif look.', 'studio-photography'); ?>
        </p>
    </div>
    <script>
    jQuery(document).ready(function($) {
        function refreshFontPreview() {
            const family = $('#gallery_font').val();
            $('#gallery-font-preview').css('font-family', family ? "'" + family + "', Georgia, serif" : '');
        }
        $('#gallery_font').on('change', refreshFontPreview);
        refreshFontPreview();
    });
    </script>
    <?php
}

// E5. Client Gallery Expiry Date Metabox HTML (Pixieset-style automatic expiration!)
function studio_photography_gallery_expiry_metabox_html($post) {
    wp_nonce_field('gallery_expiry_nonce_action', 'gallery_expiry_nonce');

    $expiry_date = get_post_meta($post->ID, '_gallery_expiry_date', true);
    $state = studio_photography_gallery_expiry_state($post->ID);
    ?>
    <div style="padding: 5px 0;">
        <label for="gallery_expiry_date" style="font-weight: bold; display: block; margin-bottom: 5px;"><?php _e('Gallery Expiry Date', 'studio-photography'); ?></label>
        <input type="date" id="gallery_expiry_date" name="gallery_expiry_date" value="<?php echo esc_attr($expiry_date); ?>" style="width: 100%; padding: 6px;">

        <!-- Quick presets -->
        <div style="display: flex; flex-wrap: wrap; gap: 5px; margin-top: 8px;">
            <button type="button" class="button button-small studio-expiry-preset" data-days="30">+30 days</button>
            <button type="button" class="button button-small studio-expiry-preset" data-days="90">+90 days</button>
            <button type="button" class="button button-small studio-expiry-preset" data-days="365">+1 year</button>
            <button type="button" class="button button-small studio-expiry-preset" data-days="">No expiry</button>
        </div>

        <!-- Live status -->
        <p id="gallery-expiry-status" style="margin: 10px 0 0 0; font-size: 12px; font-weight: 600;">
            <?php
            if ($state['expires']) {
                $nice = date('M d, Y', strtotime($state['date']));
                if ($state['expired']) {
                    echo '<span style="color: #dc2626;">⛔ Expired on ' . esc_html($nice) . ' — clients see the expiry screen.</span>';
                } elseif ($state['days_left'] === 0) {
                    echo '<span style="color: #b45309;">⏳ Expires TODAY at midnight.</span>';
                } elseif ($state['days_left'] <= 7) {
                    echo '<span style="color: #b45309;">⏳ Expires in ' . intval($state['days_left']) . ' days (' . esc_html($nice) . ').</span>';
                } else {
                    echo '<span style="color: #16a34a;">✅ Active until ' . esc_html($nice) . ' (' . intval($state['days_left']) . ' days left).</span>';
                }
            } else {
                echo '<span style="color: #64748b;">♾️ No expiry set — this gallery stays online forever.</span>';
            }
            ?>
        </p>

        <p class="description" style="margin-top: 8px; line-height: 1.5;">
            <?php _e('The gallery stays open through the expiry date, then locks automatically: clients see a friendly "gallery expired" screen and all downloads (single photos + ZIP) are disabled. You still have full access as an admin. Leave empty for no expiry.', 'studio-photography'); ?>
        </p>
    </div>
    <script>
    jQuery(document).ready(function($) {
        const $input = $('#gallery_expiry_date');
        $('.studio-expiry-preset').on('click', function(e) {
            e.preventDefault();
            const days = String($(this).data('days') || '');
            if (days === '') { $input.val(''); return; }
            const d = new Date(Date.now() + parseInt(days, 10) * 86400000);
            const pad = n => String(n).padStart(2, '0');
            $input.val(d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()));
        });
    });
    </script>
    <?php
}

// F. Client Gallery Paystack Balance Settings Metabox HTML (AUTOMATED SYNC IMPLEMENTATION!)
function studio_photography_gallery_payment_metabox_html($post) {
    wp_nonce_field('gallery_payment_meta_nonce_action', 'gallery_payment_meta_nonce');
    
    $booking_id = get_post_meta($post->ID, '_gallery_booking_id', true);
    $payment_status = get_post_meta($post->ID, '_gallery_payment_status', true) ?: 'unpaid';
    $manual_balance = get_post_meta($post->ID, '_gallery_balance_amount', true) ?: '0.00';
    $manual_email = get_post_meta($post->ID, '_gallery_client_email', true);
    $paystack_ref = get_post_meta($post->ID, '_gallery_paystack_ref', true);

    // Fetch all active bookings in the system to list them in the dropdown
    $bookings = get_posts(array(
        'post_type' => 'booking',
        'numberposts' => -1,
        'post_status' => 'publish'
    ));
    ?>
    <table class="form-table">
        <tr>
            <th><label for="gallery_booking_id"><?php _e('Sync with Client Booking', 'studio-photography'); ?></label></th>
            <td>
                <select id="gallery_booking_id" name="gallery_booking_id" class="regular-text" style="max-width: 100%;">
                    <option value=""><?php _e('Do Not Sync (Manual Override Below)', 'studio-photography'); ?></option>
                    <?php foreach ($bookings as $b) : 
                        $client = get_post_meta($b->ID, '_booking_client_name', true);
                        $date = get_post_meta($b->ID, '_booking_event_date', true);
                        $formatted_date = $date ? date('M d, Y', strtotime($date)) : 'Date unset';
                        ?>
                        <option value="<?php echo esc_attr($b->ID); ?>" <?php selected($booking_id, $b->ID); ?>>
                            <?php echo esc_html($client . ' - ' . $b->post_title . ' (' . $formatted_date . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?php _e('Select the customer\'s booking. The system will automatically fetch their email, calculate their remaining balance (Total - Deposit), and lock their gallery until they pay.', 'studio-photography'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label><?php _e('Balance Payment Status', 'studio-photography'); ?></label></th>
            <td>
                <select name="gallery_payment_status" class="regular-text">
                    <option value="unpaid" <?php selected($payment_status, 'unpaid'); ?>><?php _e('Unpaid (Locks gallery unless balance is 0.00)', 'studio-photography'); ?></option>
                    <option value="paid" <?php selected($payment_status, 'paid'); ?>><?php _e('Paid (Unlocked immediately)', 'studio-photography'); ?></option>
                </select>
            </td>
        </tr>
    </table>

    <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 8px; margin-top: 15px;">
        <h4 style="margin: 0 0 10px 0; font-weight: bold;"><?php _e('Manual Override (Optional)', 'studio-photography'); ?></h4>
        <p class="description" style="margin-bottom: 10px;"><?php _e('Only use these fields if you are NOT syncing with an existing booking above.', 'studio-photography'); ?></p>
        
        <table class="form-table" style="margin-top: 0; width: 100%;">
            <tr>
                <th style="padding: 5px 0; width: 30%;"><label for="gallery_balance_amount"><?php _e('Manual Balance (GHS)', 'studio-photography'); ?></label></th>
                <td><input type="number" step="0.01" min="0" id="gallery_balance_amount" name="gallery_balance_amount" value="<?php echo esc_attr($manual_balance); ?>" class="regular-text" style="width: 100%;"></td>
            </tr>
            <tr>
                <th style="padding: 5px 0; width: 30%;"><label for="gallery_client_email"><?php _e('Manual Client Email', 'studio-photography'); ?></label></th>
                <td><input type="email" id="gallery_client_email" name="gallery_client_email" value="<?php echo esc_attr($manual_email); ?>" class="regular-text" placeholder="client@email.com" style="width: 100%;"></td>
            </tr>
        </table>
    </div>

    <?php if (!empty($paystack_ref)) : ?>
        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
            <p><strong><?php _e('Balance Transaction Reference:', 'studio-photography'); ?></strong> <span style="color: #16a34a; font-weight: bold; background: #f0fdf4; padding: 4px 10px; border-radius: 6px; border: 1px solid #bbf7d0; font-family: monospace; font-size: 11px;"><?php echo esc_html($paystack_ref); ?></span> (PAID)</p>
        </div>
    <?php endif; ?>
    <?php
}

// G. Client Gallery AWS S3 / Cloud Bucket Textarea Metabox HTML
function studio_photography_gallery_s3_metabox_html($post) {
    wp_nonce_field('gallery_s3_meta_nonce_action', 'gallery_s3_meta_nonce');
    $cloud_urls = get_post_meta($post->ID, '_gallery_cloud_urls', true);
    $gdrive_folder = get_post_meta($post->ID, '_gallery_gdrive_folder', true);
    ?>
    <div style="padding: 5px 0; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 20px;">
        <label for="gallery_gdrive_folder" style="font-weight: bold; display: block; margin-bottom: 5px; color: #1e3a8a;"><?php _e('📂 Google Drive Shared Folder Link', 'studio-photography'); ?></label>
        <input type="text" id="gallery_gdrive_folder" name="gallery_gdrive_folder" value="<?php echo esc_attr($gdrive_folder); ?>" style="width: 100%; padding: 8px;" placeholder="e.g. https://drive.google.com/drive/folders/XYZ?usp=sharing">
        <p class="description" style="margin-top: 5px;"><?php _e('Optional: Paste a shared Google Drive folder link. Once the client pays their GHS balance, an "Open Google Drive Folder" button will unlock on their screen!', 'studio-photography'); ?></p>
        <p class="description" style="margin-top: 4px; color: #16a34a;"><?php _e('⚡ Photo changes in your Drive folder sync automatically within ~1 minute. Need it instantly? Just click "Update" on this gallery and the next visitor sees the fresh list immediately.', 'studio-photography'); ?></p>
    </div>

    <div style="padding: 5px 0; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 20px;">
        <label for="gallery_mega_folder" style="font-weight: bold; display: block; margin-bottom: 5px; color: #d32f2f;"><?php _e('🧡 MEGA Cloud Folder Link (Alternative Delivery)', 'studio-photography'); ?></label>
        <input type="text" id="gallery_mega_folder" name="gallery_mega_folder" value="<?php echo esc_attr(get_post_meta($post->ID, '_gallery_mega_folder', true)); ?>" style="width: 100%; padding: 8px;" placeholder="e.g. https://mega.nz/folder/AbCdEfGh# decryption-key-here">
        <p class="description" style="margin-top: 5px; line-height: 1.5;"><?php _e('Optional alternative to Google Drive: paste a MEGA folder link (include the decryption key in the URL!). Once the client settles their balance, an "Open MEGA Cloud Folder" button unlocks on their gallery — photos open on mega.nz at full MEGA speed. Note: MEGA links are end-to-end encrypted, so these photos display on mega.nz itself, not inside the gallery grid.', 'studio-photography'); ?></p>
    </div>

    <div style="padding: 5px 0;">
        <label for="gallery_cloud_urls" style="font-weight: bold; display: block; margin-bottom: 5px;"><?php _e('📸 Individual Google Drive / S3 Image URLs (For Grid Display)', 'studio-photography'); ?></label>
        <textarea id="gallery_cloud_urls" name="gallery_cloud_urls" rows="6" style="width: 100%; font-family: monospace; font-size: 12px; padding: 8px;" placeholder="e.g.&#10;https://drive.google.com/file/d/FILE_ID/view?usp=sharing&#10;https://my-bucket.s3.amazonaws.com/wedding/DSC_001.jpg"><?php echo esc_textarea($cloud_urls); ?></textarea>
        <p class="description" style="margin-top: 8px; leading-relaxed: true;"><?php _e('Optional: Paste individual public Google Drive file sharing links OR AWS S3 public image URLs here (enter <strong>one URL per line</strong>).<br>If left blank, you can upload images directly into WordPress, and your clients can still view and download them as a complete server-packaged ZIP file generated on-the-fly!', 'studio-photography'); ?></p>
    </div>
    <?php
}

// G2. Portfolio Gallery (Google Drive) Metabox HTML
function studio_photography_portfolio_metabox_html($post) {
    wp_nonce_field('portfolio_meta_nonce_action', 'portfolio_meta_nonce');
    $gdrive_folder = get_post_meta($post->ID, '_portfolio_gdrive_folder', true);
    $cover_url = get_post_meta($post->ID, '_portfolio_cover_url', true);
    ?>
    <table class="form-table">
        <tr>
            <th><label for="portfolio_gdrive_folder"><?php _e('📂 Google Drive Folder Link', 'studio-photography'); ?></label></th>
            <td>
                <input type="text" id="portfolio_gdrive_folder" name="portfolio_gdrive_folder" value="<?php echo esc_attr($gdrive_folder); ?>" class="large-text" placeholder="e.g. https://drive.google.com/drive/folders/XYZ?usp=sharing">
                <p class="description"><?php _e('Paste a PUBLIC shared Google Drive folder link containing this collection\'s photos. They are pulled automatically — no uploading needed!', 'studio-photography'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="portfolio_cover_url"><?php _e('🖼️ Cover Image URL (Optional)', 'studio-photography'); ?></label></th>
            <td>
                <input type="text" id="portfolio_cover_url" name="portfolio_cover_url" value="<?php echo esc_attr($cover_url); ?>" class="large-text" placeholder="Leave blank to auto-use the first photo in the folder">
                <p class="description"><?php _e('Optional: paste an image URL to use as the card/hero cover. Defaults to the first Google Drive photo.', 'studio-photography'); ?></p>
            </td>
        </tr>
    </table>
    <p class="description" style="margin-top: 10px; color: #16a34a; font-weight: 600;"><?php _e('⚡ Set the category (Weddings, Events, Portraits...) in the "Portfolio Categories" panel so visitors can filter this collection on the portfolio page.', 'studio-photography'); ?></p>
    <?php
}


// Save Custom Fields
function studio_photography_save_metaboxes($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    // Invalidate the cached "Full Collection ZIP" whenever a client gallery is saved
    // (new photos, Drive folder changes, cover swaps → the archive rebuilds on the next download)
    if (get_post_type($post_id) === 'client_gallery') {
        $studio_upload = wp_upload_dir();
        @unlink(trailingslashit($studio_upload['basedir']) . 'studio-zips/gallery-' . $post_id . '.zip');
        delete_post_meta($post_id, '_gallery_zip_cache_sig');
    }

    if (isset($_POST['session_display_order'])) update_post_meta($post_id, '_session_display_order', intval($_POST['session_display_order']));
    if (isset($_POST['session_featured'])) update_post_meta($post_id, '_session_featured', sanitize_text_field($_POST['session_featured']));
    if (isset($_POST['session_active'])) update_post_meta($post_id, '_session_active', sanitize_text_field($_POST['session_active']));
    if (isset($_POST['session_image'])) update_post_meta($post_id, '_session_image', esc_url_raw($_POST['session_image']));
    if (isset($_POST['session_gallery_ids'])) update_post_meta($post_id, '_session_gallery_ids', sanitize_text_field($_POST['session_gallery_ids']));

    // Save extra session gallery images (Drive FOLDER links and/or image URLs, one per line)
    if (isset($_POST['session_image_urls'])) {
        $url_lines = array_filter(array_map('trim', explode("\n", str_replace("\r", "", $_POST['session_image_urls']))));
        update_post_meta($post_id, '_session_image_urls', implode("\n", array_map('esc_url_raw', $url_lines)));

        // REAL-TIME SYNC: invalidate cached Drive folder listings so changes show up instantly
        foreach ($url_lines as $line_check) {
            if (strpos($line_check, '/folders/') !== false && preg_match('/\/folders\/([a-zA-Z0-9_-]+)/', $line_check, $fcm)) {
                delete_transient('studio_folder_imgs_' . $fcm[1]);
            }
        }
    }

    // Save the Pixieset-style session cover focus point (e.g. "35% 60%")
    if (isset($_POST['session_focal'])) {
        $session_focal = sanitize_text_field($_POST['session_focal']);
        update_post_meta($post_id, '_session_focal', preg_match('/^\d{1,3}(\.\d+)?%\s+\d{1,3}(\.\d+)?%$/', $session_focal) ? $session_focal : '');
    }

    if (isset($_POST['package_meta_nonce']) && wp_verify_nonce($_POST['package_meta_nonce'], 'package_meta_nonce_action')) {
        if (isset($_POST['package_session_id'])) update_post_meta($post_id, '_package_session_id', intval($_POST['package_session_id']));
        if (isset($_POST['package_price'])) update_post_meta($post_id, '_package_price', floatval($_POST['package_price']));
        if (isset($_POST['package_duration'])) update_post_meta($post_id, '_package_duration', sanitize_text_field($_POST['package_duration']));
        if (isset($_POST['package_max_people'])) update_post_meta($post_id, '_package_max_people', intval($_POST['package_max_people']));
        if (isset($_POST['package_edited_photos'])) update_post_meta($post_id, '_package_edited_photos', intval($_POST['package_edited_photos']));
        if (isset($_POST['package_outfit_changes'])) update_post_meta($post_id, '_package_outfit_changes', intval($_POST['package_outfit_changes']));
        if (isset($_POST['package_locations'])) update_post_meta($post_id, '_package_locations', intval($_POST['package_locations']));
        if (isset($_POST['package_delivery_time'])) update_post_meta($post_id, '_package_delivery_time', sanitize_text_field($_POST['package_delivery_time']));
        if (isset($_POST['package_deposit_percentage'])) update_post_meta($post_id, '_package_deposit_percentage', intval($_POST['package_deposit_percentage']));
        if (isset($_POST['package_reschedule_allowed'])) update_post_meta($post_id, '_package_reschedule_allowed', sanitize_text_field($_POST['package_reschedule_allowed']));
        if (isset($_POST['package_reschedule_hours'])) update_post_meta($post_id, '_package_reschedule_hours', intval($_POST['package_reschedule_hours']));
        if (isset($_POST['package_display_order'])) update_post_meta($post_id, '_package_display_order', intval($_POST['package_display_order']));
        if (isset($_POST['package_active'])) update_post_meta($post_id, '_package_active', sanitize_text_field($_POST['package_active']));

        update_post_meta($post_id, '_package_online_gallery', isset($_POST['package_online_gallery']) ? 'yes' : 'no');
        update_post_meta($post_id, '_package_raw_images', isset($_POST['package_raw_images']) ? 'yes' : 'no');
        update_post_meta($post_id, '_package_printing', isset($_POST['package_printing']) ? 'yes' : 'no');
        update_post_meta($post_id, '_package_transportation', isset($_POST['package_transportation']) ? 'yes' : 'no');
        update_post_meta($post_id, '_package_drone_coverage', isset($_POST['package_drone_coverage']) ? 'yes' : 'no');
        update_post_meta($post_id, '_package_priority_editing', isset($_POST['package_priority_editing']) ? 'yes' : 'no');

        if (isset($_POST['package_features'])) {
            $lines = array_filter(array_map('trim', explode("\n", $_POST['package_features'])));
            update_post_meta($post_id, '_package_features', $lines);
        }
    }

    if (isset($_POST['booking_meta_nonce']) && wp_verify_nonce($_POST['booking_meta_nonce'], 'booking_meta_nonce_action')) {
        if (isset($_POST['booking_status'])) update_post_meta($post_id, '_booking_status', sanitize_text_field($_POST['booking_status']));
        if (isset($_POST['booking_client_name'])) update_post_meta($post_id, '_booking_client_name', sanitize_text_field($_POST['booking_client_name']));
        if (isset($_POST['booking_client_email'])) update_post_meta($post_id, '_booking_client_email', sanitize_email($_POST['booking_client_email']));
        if (isset($_POST['booking_client_phone'])) update_post_meta($post_id, '_booking_client_phone', sanitize_text_field($_POST['booking_client_phone']));
        if (isset($_POST['booking_event_date'])) update_post_meta($post_id, '_booking_event_date', sanitize_text_field($_POST['booking_event_date']));
        if (isset($_POST['booking_location'])) update_post_meta($post_id, '_booking_location', sanitize_text_field($_POST['booking_location']));
        
        // Save newly editable Session, Package, and Pricing metadata fields inside WP-Admin!
        if (isset($_POST['booking_session_id'])) update_post_meta($post_id, '_booking_session_id', intval($_POST['booking_session_id']));
        if (isset($_POST['booking_package_id'])) update_post_meta($post_id, '_booking_package_id', intval($_POST['booking_package_id']));
        
        $base_total = isset($_POST['booking_amount_total']) ? floatval($_POST['booking_amount_total']) : 0.00;
        $discount = isset($_POST['booking_discount']) ? floatval($_POST['booking_discount']) : 0.00;
        $final_total = $base_total - $discount;
        
        update_post_meta($post_id, '_booking_amount_total', $base_total);
        update_post_meta($post_id, '_booking_amount_deposit', isset($_POST['booking_amount_deposit']) ? floatval($_POST['booking_amount_deposit']) : 0.00);
        update_post_meta($post_id, '_booking_discount', $discount);
        
        // Real-Time Invoicing Synchronization! Updates the linked invoice totals in real-time!
        $invoices = get_posts(array(
            'post_type' => 'invoice',
            'meta_key' => '_invoice_booking_id',
            'meta_value' => $post_id,
            'numberposts' => 1
        ));
        if (!empty($invoices)) {
            update_post_meta($invoices[0]->ID, '_invoice_total', $final_total);
            update_post_meta($invoices[0]->ID, '_invoice_discount_amount', $discount);
        }
    }

    if (isset($_POST['invoice_meta_nonce']) && wp_verify_nonce($_POST['invoice_meta_nonce'], 'invoice_meta_nonce_action')) {
        if (isset($_POST['invoice_client_name'])) update_post_meta($post_id, '_invoice_client_name', sanitize_text_field($_POST['invoice_client_name']));
        if (isset($_POST['invoice_total'])) update_post_meta($post_id, '_invoice_total', floatval($_POST['invoice_total']));
        if (isset($_POST['invoice_status'])) update_post_meta($post_id, '_invoice_status', sanitize_text_field($_POST['invoice_status']));
        if (isset($_POST['invoice_due_date'])) update_post_meta($post_id, '_invoice_due_date', sanitize_text_field($_POST['invoice_due_date']));
        
        // Shoot details + itemized services (WP-admin invoice editor)
        if (isset($_POST['invoice_event_date'])) update_post_meta($post_id, '_invoice_event_date', sanitize_text_field($_POST['invoice_event_date']));
        if (isset($_POST['invoice_shoot_location'])) update_post_meta($post_id, '_invoice_shoot_location', sanitize_text_field($_POST['invoice_shoot_location']));
        if (isset($_POST['invoice_shoot_type'])) update_post_meta($post_id, '_invoice_shoot_type', sanitize_text_field($_POST['invoice_shoot_type']));
        if (isset($_POST['invoice_package_name'])) update_post_meta($post_id, '_invoice_package_name', sanitize_text_field($_POST['invoice_package_name']));
        if (isset($_POST['invoice_logo_id'])) {
            $inv_logo = intval($_POST['invoice_logo_id']);
            if ($inv_logo > 0) update_post_meta($post_id, '_invoice_logo_id', $inv_logo);
            else delete_post_meta($post_id, '_invoice_logo_id');
        }
        if (isset($_POST['invoice_shoot_notes'])) update_post_meta($post_id, '_invoice_shoot_notes', sanitize_textarea_field($_POST['invoice_shoot_notes']));

        if (isset($_POST['invoice_items_json'])) {
            $admin_items = array();
            $decoded_items = json_decode(wp_unslash($_POST['invoice_items_json']), true);
            if (is_array($decoded_items)) {
                foreach ($decoded_items as $ai) {
                    $ad = sanitize_text_field(isset($ai['d']) ? $ai['d'] : '');
                    $aq = max(1, intval(isset($ai['q']) ? $ai['q'] : 1));
                    $ap = max(0, floatval(isset($ai['p']) ? $ai['p'] : 0));
                    if ($ad !== '' || $ap > 0) $admin_items[] = array('desc' => $ad, 'qty' => $aq, 'price' => $ap);
                }
            }
            if (!empty($admin_items)) update_post_meta($post_id, '_invoice_items', $admin_items);
            else delete_post_meta($post_id, '_invoice_items');
        }

        // Automatically save the invoice number based on the auto-generated Title or manual POST!
        $inv_title = get_the_title($post_id);
        $final_inv_num = isset($_POST['invoice_number']) ? sanitize_text_field($_POST['invoice_number']) : (!empty($inv_title) ? $inv_title : 'INV-' . date('Ymd') . '-' . rand(100, 999));
        update_post_meta($post_id, '_invoice_number', $final_inv_num);
    }

    if (isset($_POST['gallery_meta_nonce']) && wp_verify_nonce($_POST['gallery_meta_nonce'], 'gallery_meta_nonce_action')) {
        if (isset($_POST['gallery_zip_download'])) {
            update_post_meta($post_id, '_gallery_zip_download', esc_url_raw($_POST['gallery_zip_download']));
        }
    }

    // Save the manually chosen Gallery Cover Photo (Hero Banner override)
    if (isset($_POST['gallery_cover_meta_nonce']) && wp_verify_nonce($_POST['gallery_cover_meta_nonce'], 'gallery_cover_meta_nonce_action')) {
        if (isset($_POST['gallery_cover_url'])) {
            update_post_meta($post_id, '_gallery_cover_url', esc_url_raw($_POST['gallery_cover_url']));
        }

        // Save the Pixieset-style cover focal point (e.g. "35% 60%") — which part of the photo stays in focus on any screen
        $cover_focal = isset($_POST['gallery_cover_focal']) ? sanitize_text_field($_POST['gallery_cover_focal']) : '';
        if (preg_match('/^\d{1,3}(\.\d+)?%\s+\d{1,3}(\.\d+)?%$/', $cover_focal)) {
            update_post_meta($post_id, '_gallery_cover_focal', $cover_focal);
        } else {
            update_post_meta($post_id, '_gallery_cover_focal', '');
        }
    }

    // Save the gallery typography font choice
    if (isset($_POST['gallery_typography_nonce']) && wp_verify_nonce($_POST['gallery_typography_nonce'], 'gallery_typography_nonce_action')) {
        $chosen_font = isset($_POST['gallery_font']) ? sanitize_text_field($_POST['gallery_font']) : '';
        $valid_fonts = studio_photography_get_gallery_fonts();
        update_post_meta($post_id, '_gallery_font', isset($valid_fonts[$chosen_font]) ? $chosen_font : '');
    }

    // Save the gallery expiry date (Pixieset-style automatic expiration)
    if (isset($_POST['gallery_expiry_nonce']) && wp_verify_nonce($_POST['gallery_expiry_nonce'], 'gallery_expiry_nonce_action')) {
        $raw_expiry = isset($_POST['gallery_expiry_date']) ? sanitize_text_field($_POST['gallery_expiry_date']) : '';
        if (!empty($raw_expiry) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw_expiry) && strtotime($raw_expiry)) {
            update_post_meta($post_id, '_gallery_expiry_date', $raw_expiry);
        } else {
            update_post_meta($post_id, '_gallery_expiry_date', '');
        }
        // Reset the one-time expiry notification flag when the date is moved forward or cleared
        $new_state = studio_photography_gallery_expiry_state($post_id);
        if (!$new_state['expired']) {
            delete_post_meta($post_id, '_gallery_expiry_notified');
        }
    }

    if (isset($_POST['gallery_payment_meta_nonce']) && wp_verify_nonce($_POST['gallery_payment_meta_nonce'], 'gallery_payment_meta_nonce_action')) {
        update_post_meta($post_id, '_gallery_booking_id', isset($_POST['gallery_booking_id']) ? intval($_POST['gallery_booking_id']) : '');
        update_post_meta($post_id, '_gallery_payment_status', sanitize_text_field($_POST['gallery_payment_status']));
        update_post_meta($post_id, '_gallery_balance_amount', isset($_POST['gallery_balance_amount']) ? floatval($_POST['gallery_balance_amount']) : 0.00);
        update_post_meta($post_id, '_gallery_client_email', isset($_POST['gallery_client_email']) ? sanitize_email($_POST['gallery_client_email']) : '');
    }

    if (isset($_POST['gallery_s3_meta_nonce']) && wp_verify_nonce($_POST['gallery_s3_meta_nonce'], 'gallery_s3_meta_nonce_action')) {
        if (isset($_POST['gallery_cloud_urls'])) {
            $urls = array_filter(array_map('trim', explode("\n", $_POST['gallery_cloud_urls'])));
            $urls_clean = array_map('esc_url_raw', $urls);
            update_post_meta($post_id, '_gallery_cloud_urls', implode("\n", $urls_clean));
        } else {
            update_post_meta($post_id, '_gallery_cloud_urls', '');
        }
        if (isset($_POST['gallery_gdrive_folder'])) {
            update_post_meta($post_id, '_gallery_gdrive_folder', esc_url_raw($_POST['gallery_gdrive_folder']));
        if (isset($_POST['gallery_mega_folder'])) {
            update_post_meta($post_id, '_gallery_mega_folder', esc_url_raw($_POST['gallery_mega_folder']));
        }

            // REAL-TIME SYNC: invalidate the cached Google Drive folder listing whenever this gallery is saved,
            // so newly added/removed photos in the Drive folder show up on the client's very next visit!
            $fresh_folder = esc_url_raw($_POST['gallery_gdrive_folder']);
            if (strpos($fresh_folder, 'drive.google.com') !== false) {
                $fresh_folder_id = '';
                if (preg_match('/\/folders\/([a-zA-Z0-9_-]+)/', $fresh_folder, $folder_matches)) {
                    $fresh_folder_id = $folder_matches[1];
                } elseif (preg_match('/id=([a-zA-Z0-9_-]+)/', $fresh_folder, $folder_matches)) {
                    $fresh_folder_id = $folder_matches[1];
                }
                if (!empty($fresh_folder_id)) {
                    delete_transient('studio_gdrive_folder_' . $fresh_folder_id);
                }
            }
        }
    }


    // Save Portfolio Gallery (Google Drive) settings + invalidate its cached Drive listing
    if (isset($_POST['portfolio_meta_nonce']) && wp_verify_nonce($_POST['portfolio_meta_nonce'], 'portfolio_meta_nonce_action')) {
        if (isset($_POST['portfolio_gdrive_folder'])) update_post_meta($post_id, '_portfolio_gdrive_folder', esc_url_raw($_POST['portfolio_gdrive_folder']));
        if (isset($_POST['portfolio_cover_url'])) update_post_meta($post_id, '_portfolio_cover_url', esc_url_raw($_POST['portfolio_cover_url']));

        $fresh_folder = isset($_POST['portfolio_gdrive_folder']) ? esc_url_raw($_POST['portfolio_gdrive_folder']) : '';
        if (strpos($fresh_folder, 'drive.google.com') !== false && preg_match('/(?:\/folders\/|id=)([a-zA-Z0-9_-]+)/', $fresh_folder, $pfm)) {
            delete_transient('studio_portfolio_folder_' . $pfm[1]);
        }
    }
}
add_action('save_post', 'studio_photography_save_metaboxes');


// ── 5. CUSTOM TABLE COLUMNS FOR CLIENT GALLERIES (GORGEOUS OVERVIEW) ──────────

add_filter('manage_client_gallery_posts_columns', function($columns) {
    $columns['gallery_booking_sync'] = __('Synced Booking', 'studio-photography');
    $columns['gallery_balance'] = __('Outstanding Balance', 'studio-photography');
    $columns['gallery_payment_status'] = __('Payment Status', 'studio-photography');
    $columns['gallery_ref'] = __('Payment Reference', 'studio-photography');
    return $columns;
});
add_action('manage_client_gallery_posts_custom_column', function($column, $post_id) {
    if ($column === 'gallery_booking_sync') {
        $booking_id = get_post_meta($post_id, '_gallery_booking_id', true);
        if (!empty($booking_id)) {
            $booking = get_post($booking_id);
            echo $booking ? esc_html($booking->post_title) : '<span style="color: #ef4444;">' . __('Missing Booking', 'studio-photography') . '</span>';
        } else {
            echo '<span style="color: #94a3b8; font-style: italic;">' . __('Manual Override', 'studio-photography') . '</span>';
        }
    } elseif ($column === 'gallery_balance') {
        $booking_id = get_post_meta($post_id, '_gallery_booking_id', true);
        if (!empty($booking_id)) {
            $tot = floatval(get_post_meta($booking_id, '_booking_amount_total', true));
            $dep = floatval(get_post_meta($booking_id, '_booking_amount_deposit', true));
            $bal = $tot - $dep;
            echo '<strong>GHS ' . number_format($bal, 2) . '</strong> <span style="font-size: 10px; color: #94a3b8; block;">(' . __('Automated Sync', 'studio-photography') . ')</span>';
        } else {
            $bal = floatval(get_post_meta($post_id, '_gallery_balance_amount', true));
            echo '<strong>GHS ' . number_format($bal, 2) . '</strong> <span style="font-size: 10px; color: #94a3b8; block;">(' . __('Manual Value', 'studio-photography') . ')</span>';
        }
    } elseif ($column === 'gallery_payment_status') {
        $booking_id = get_post_meta($post_id, '_gallery_booking_id', true);
        $status = get_post_meta($post_id, '_gallery_payment_status', true) ?: 'paid';
        
        if (!empty($booking_id)) {
            $tot = floatval(get_post_meta($booking_id, '_booking_amount_total', true));
            $dep = floatval(get_post_meta($booking_id, '_booking_amount_deposit', true));
            $bal = $tot - $dep;
            if ($bal <= 0) {
                echo '<span class="badge" style="background: #f0fdf4; color: #16a34a; padding: 2px 8px; border-radius: 99px; font-weight: bold;">FREE / FULLY PAID</span>';
                return;
            }
        }
        
        echo $status === 'paid' 
            ? '<span class="badge" style="background: #f0fdf4; color: #16a34a; padding: 2px 8px; border-radius: 99px; font-weight: bold;">PAID</span>' 
            : '<span class="badge" style="background: #fee2e2; color: #b91c1c; padding: 2px 8px; border-radius: 99px; font-weight: bold;">UNPAID</span>';
    } elseif ($column === 'gallery_ref') {
        $ref = get_post_meta($post_id, '_gallery_paystack_ref', true);
        echo esc_html($ref ?: '—');
    }
}, 10, 2);


// ── 5.5 CUSTOM TABLE COLUMNS FOR PACKAGES CPT (WP-ADMIN OVERVIEW) ──────────────

add_filter('manage_package_posts_columns', function($columns) {
    $columns['package_session'] = __('Associated Session', 'studio-photography');
    $columns['package_price_col'] = __('Package Price', 'studio-photography');
    $columns['package_active_col'] = __('Status', 'studio-photography');
    return $columns;
});

add_action('manage_package_posts_custom_column', function($column, $post_id) {
    if ($column === 'package_session') {
        $sess_id = get_post_meta($post_id, '_package_session_id', true);
        if ($sess_id) {
            $sess = get_post($sess_id);
            echo $sess ? esc_html($sess->post_title) : '—';
        } else {
            echo '—';
        }
    } elseif ($column === 'package_price_col') {
        $price = floatval(get_post_meta($post_id, '_package_price', true));
        echo '<strong>GH₵ ' . number_format($price, 2) . '</strong>';
    } elseif ($column === 'package_active_col') {
        $active = get_post_meta($post_id, '_package_active', true) ?: 'yes';
        echo $active === 'yes' 
            ? '<span style="color: #16a34a; font-weight: bold;">' . __('Active', 'studio-photography') . '</span>' 
            : '<span style="color: #94a3b8; font-style: italic;">' . __('Inactive', 'studio-photography') . '</span>';
    }
}, 10, 2);


// ── 5.6 CUSTOM TABLE COLUMNS FOR INVOICES CPT (WP-ADMIN OVERVIEW) ──────────────

add_filter('manage_invoice_posts_columns', function($columns) {
    $columns['invoice_client'] = __('Client / Payer', 'studio-photography');
    $columns['invoice_total_col'] = __('Invoice Total', 'studio-photography');
    $columns['invoice_status_col'] = __('Payment Status', 'studio-photography');
    return $columns;
});

add_action('manage_invoice_posts_custom_column', function($column, $post_id) {
    if ($column === 'invoice_client') {
        $client = get_post_meta($post_id, '_invoice_client_name', true);
        echo esc_html($client ?: '—');
    } elseif ($column === 'invoice_total_col') {
        $total = floatval(get_post_meta($post_id, '_invoice_total', true));
        echo '<strong>GH₵ ' . number_format($total, 2) . '</strong>';
    } elseif ($column === 'invoice_status_col') {
        $status = get_post_meta($post_id, '_invoice_status', true) ?: 'unpaid';
        echo $status === 'paid' 
            ? '<span class="badge" style="background: #f0fdf4; color: #16a34a; padding: 2px 8px; border-radius: 99px; font-weight: bold; font-size: 10px;">' . __('PAID', 'studio-photography') . '</span>' 
            : '<span class="badge" style="background: #fee2e2; color: #b91c1c; padding: 2px 8px; border-radius: 99px; font-weight: bold; font-size: 10px;">' . __('UNPAID', 'studio-photography') . '</span>';
    }
}, 10, 2);


// ── 5.7 CUSTOM TABLE COLUMNS FOR BOOKINGS CPT (WP-ADMIN OVERVIEW) ──────────────

add_filter('manage_booking_posts_columns', function($columns) {
    $columns['booking_client'] = __('Customer Name', 'studio-photography');
    $columns['booking_event_date_col'] = __('Shoot Date', 'studio-photography');
    $columns['booking_total_col'] = __('Total Budget', 'studio-photography');
    $columns['booking_status_col'] = __('Status', 'studio-photography');
    return $columns;
});

add_action('manage_booking_posts_custom_column', function($column, $post_id) {
    if ($column === 'booking_client') {
        $client = get_post_meta($post_id, '_booking_client_name', true);
        echo esc_html($client ?: '—');
    } elseif ($column === 'booking_event_date_col') {
        $date = get_post_meta($post_id, '_booking_event_date', true);
        echo esc_html($date ? date('M d, Y', strtotime($date)) : '—');
    } elseif ($column === 'booking_total_col') {
        $total = floatval(get_post_meta($post_id, '_booking_amount_total', true));
        echo '<strong>GH₵ ' . number_format($total, 2) . '</strong>';
    } elseif ($column === 'booking_status_col') {
        $status = get_post_meta($post_id, '_booking_status', true) ?: 'pending';
        $badge_colors = 'background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;';
        if ($status === 'confirmed') $badge_colors = 'background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe;';
        elseif ($status === 'completed') $badge_colors = 'background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0;';
        elseif ($status === 'cancelled') $badge_colors = 'background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;';
        elseif ($status === 'pending') $badge_colors = 'background: #fffbeb; color: #d97706; border: 1px solid #fde68a;';
        echo '<span class="badge" style="' . $badge_colors . ' padding: 2px 8px; border-radius: 99px; font-weight: bold; font-size: 10px;">' . strtoupper($status) . '</span>';
    }
}, 10, 2);


// ── 6. AJAX GALLERY BALANCE SETTLEMENT CALLBACK ──────────────────────────────

function studio_photography_handle_gallery_balance_settlement() {
    $gallery_id = isset($_POST['gallery_id']) ? intval($_POST['gallery_id']) : 0;
    $paystack_ref = isset($_POST['paystack_reference']) ? sanitize_text_field($_POST['paystack_reference']) : '';

    if (empty($gallery_id) || empty($paystack_ref)) {
        wp_send_json_error(__('Missing required settlement parameters.', 'studio-photography'));
    }

    // 1. Update gallery status to PAID
    update_post_meta($gallery_id, '_gallery_payment_status', 'paid');
    update_post_meta($gallery_id, '_gallery_paystack_ref', $paystack_ref);

    // 2. Fetch linked booking details
    $booking_id = get_post_meta($gallery_id, '_gallery_booking_id', true);
    if (!empty($booking_id)) {
        update_post_meta($booking_id, '_booking_status', 'completed');
        update_post_meta($booking_id, '_booking_paystack_reference_balance', $paystack_ref);
        
        $balance_val = floatval(get_post_meta($booking_id, '_booking_amount_total', true)) - floatval(get_post_meta($booking_id, '_booking_amount_deposit', true));
        $client_email = get_post_meta($booking_id, '_booking_client_email', true);
    } else {
        $balance_val = floatval(get_post_meta($gallery_id, '_gallery_balance_amount', true));
        $client_email = get_post_meta($gallery_id, '_gallery_client_email', true) ?: 'client@email.com';
    }

    // 3. Insert a secure settlement Invoice for accounting
    $settlement_code = 'BAL-SET-' . $gallery_id;
    $invoice_id = wp_insert_post(array(
        'post_title' => $settlement_code . ' [' . $client_email . ']',
        'post_type' => 'invoice',
        'post_status' => 'publish',
    ));
    if ($invoice_id && !is_wp_error($invoice_id)) {
        update_post_meta($invoice_id, '_invoice_number', $settlement_code);
        update_post_meta($invoice_id, '_invoice_client_name', esc_html__('Gallery Balance Settlement', 'studio-photography'));
        update_post_meta($invoice_id, '_invoice_total', $balance_val);
        update_post_meta($invoice_id, '_invoice_status', 'paid');
        update_post_meta($invoice_id, '_invoice_due_date', date('Y-m-d'));
        update_post_meta($invoice_id, '_invoice_paystack_reference', $paystack_ref);
    }

    wp_send_json_success(__('Outstanding gallery balance settled securely! Photos unlocked.', 'studio-photography'));
}
add_action('wp_ajax_settle_gallery_balance', 'studio_photography_handle_gallery_balance_settlement');
add_action('wp_ajax_nopriv_settle_gallery_balance', 'studio_photography_handle_gallery_balance_settlement');


// Helper to retrieve business email dynamically with support for Customizer override and a hardcoded fallback
function studio_photography_get_receipt_logo_html($invoice_id = 0) {
    // 1st priority: the high-res logo manually uploaded on THIS invoice
    $logo_id = 0;
    if (!empty($invoice_id)) {
        $logo_id = (int) get_post_meta(intval($invoice_id), '_invoice_logo_id', true);
    }
    // 2nd priority: the site logo — at FULL resolution so the invoice print stays sharp
    if (empty($logo_id)) {
        $logo_id = get_theme_mod('custom_logo');
    }
    if (!empty($logo_id)) {
        $logo_url = wp_get_attachment_image_url($logo_id, 'full');
        if (!empty($logo_url)) {
            return '<img src="' . esc_url($logo_url) . '" alt="' . esc_attr(get_bloginfo('name')) . '" style="height: 52px; width: auto; max-width: 180px; object-fit: contain;">';
        }
    }
    // No logo set yet — keep the classic camera icon placeholder
    return '<div class="h-10 w-10 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center"><i data-lucide="camera" class="h-5 w-5"></i></div>';
}

function studio_photography_get_business_location() {
    return get_theme_mod('studio_business_location', 'Ghana');
}

function studio_photography_get_business_email() {
    return get_theme_mod('studio_business_email', 'whytecobby@gmail.com');
}


// ── 7. RESTORED: AJAX BOOKING FORM HANDLER (100% OPERATIONAL RECOVERY!) ──────

function studio_photography_handle_ajax_booking() {
    try {
        $session_id = isset($_POST['session_id']) ? intval($_POST['session_id']) : 0;
        $package_id = isset($_POST['package_id']) ? intval($_POST['package_id']) : 0;
        $client_name = isset($_POST['client_name']) ? sanitize_text_field($_POST['client_name']) : '';
        $client_email = isset($_POST['client_email']) ? sanitize_email($_POST['client_email']) : '';
        $client_phone = isset($_POST['client_phone']) ? sanitize_text_field($_POST['client_phone']) : '';
        $event_date = isset($_POST['event_date']) ? sanitize_text_field($_POST['event_date']) : '';
        $event_location = isset($_POST['event_location']) ? sanitize_text_field($_POST['event_location']) : '';
        $paystack_reference = isset($_POST['paystack_reference']) ? sanitize_text_field($_POST['paystack_reference']) : '';

        // Verification guard
        if (empty($session_id) || empty($package_id) || empty($client_name) || empty($client_email) || empty($client_phone) || empty($event_date) || empty($event_location)) {
            wp_send_json_error(__('Missing parameters! Please complete all required checkout fields.', 'studio-photography'));
        }

        $package = get_post($package_id);
        if (!$package || $package->post_type !== 'package') {
            wp_send_json_error(__('The requested package selection is invalid or missing in local database.', 'studio-photography'));
        }

        // Compute prioritized delivery speed surcharge dynamically based on chosen delivery date!
        $photo_delivery_date = isset($_POST['photo_delivery_date']) ? sanitize_text_field($_POST['photo_delivery_date']) : '';
        $surcharge_pct = 0;
        
        if (!empty($event_date) && !empty($photo_delivery_date)) {
            $date1 = new DateTime($event_date);
            $date2 = new DateTime($photo_delivery_date);
            
            if ($date2 >= $date1) {
                $diff_days = $date2->diff($date1)->days;
                if ($diff_days >= 0 && $diff_days <= 2) {
                    $surcharge_pct = 40; // Next-Day/Rush (+40%) for 0 to 2 days
                } elseif ($diff_days <= 5) {
                    $surcharge_pct = 35; // Express/Priority (+35%) for 3 to 5 days
                }
            }
        }
        
        $base_price = floatval(get_post_meta($package_id, '_package_price', true));
        $surcharge_amount = $base_price * ($surcharge_pct / 100);
        $total_price = $base_price + $surcharge_amount;
        
        $deposit_pct = intval(get_post_meta($package_id, '_package_deposit_percentage', true) ?: 50);
        $deposit_amount = $total_price * ($deposit_pct / 100);

        $delivery_speed = 'standard';
        if ($surcharge_pct === 40) {
            $delivery_speed = 'rush';
        } elseif ($surcharge_pct === 35) {
            $delivery_speed = 'priority';
        }

        $booking_status = 'confirmed'; // Automatically confirm all bookings (both Paystack & WhatsApp) instantly upon submission!

        // Insert booking
        $booking_id = wp_insert_post(array(
            'post_title' => $client_name . ' - ' . $package->post_title,
            'post_type' => 'booking',
            'post_status' => 'publish',
        ));

        if (!$booking_id || is_wp_error($booking_id)) {
            throw new Exception(__('Failed to insert booking post into MySQL database.', 'studio-photography'));
        }

        // Save Booking Meta Records
        update_post_meta($booking_id, '_booking_client_name', $client_name);
        update_post_meta($booking_id, '_booking_client_email', $client_email);
        update_post_meta($booking_id, '_booking_client_phone', $client_phone);
        update_post_meta($booking_id, '_booking_event_date', $event_date);
        update_post_meta($booking_id, '_booking_photo_delivery_date', $photo_delivery_date);
        update_post_meta($booking_id, '_booking_location', $event_location);
        update_post_meta($booking_id, '_booking_session_id', $session_id);
        update_post_meta($booking_id, '_booking_package_id', $package_id);
        update_post_meta($booking_id, '_booking_status', $booking_status);
        update_post_meta($booking_id, '_booking_delivery_surcharge', $surcharge_amount);
        update_post_meta($booking_id, '_booking_delivery_speed', $delivery_speed);
        update_post_meta($booking_id, '_booking_delivery_surcharge', $surcharge_amount);
        update_post_meta($booking_id, '_booking_amount_total', $total_price);
        update_post_meta($booking_id, '_booking_amount_deposit', $deposit_amount);

        if (!empty($paystack_reference)) {
            update_post_meta($booking_id, '_booking_paystack_reference', $paystack_reference);
        }

        // Insert Invoice
        $inv_code = 'INV-' . date('Y') . '-' . sprintf('%03d', $booking_id);
        $invoice_id = wp_insert_post(array(
            'post_title' => $inv_code . ' [' . $client_name . ']',
            'post_type' => 'invoice',
            'post_status' => 'publish',
        ));

        if ($invoice_id && !is_wp_error($invoice_id)) {
            $invoice_status = !empty($paystack_reference) ? 'paid' : 'unpaid';

            update_post_meta($invoice_id, '_invoice_number', $inv_code);
            update_post_meta($invoice_id, '_invoice_booking_id', $booking_id);
            update_post_meta($invoice_id, '_invoice_client_name', $client_name);
            update_post_meta($invoice_id, '_invoice_total', $total_price);
            update_post_meta($invoice_id, '_invoice_status', $invoice_status);
            update_post_meta($invoice_id, '_invoice_due_date', date('Y-m-d', strtotime('+3 days')));

            if (!empty($paystack_reference)) {
                update_post_meta($invoice_id, '_invoice_paystack_reference', $paystack_reference);
            }
        }

        // Send automated notification emails (to Admin & Client)
        $admin_email = studio_photography_get_business_email();
        $site_name = get_bloginfo('name');
        
        $session_post = get_post($session_id);
        $session_title = $session_post ? $session_post->post_title : __('N/A', 'studio-photography');
        $package_post = get_post($package_id);
        $package_title = $package_post ? $package_post->post_title : __('N/A', 'studio-photography');
        
        $receipt_url = home_url('/?studio_booking_receipt=1&booking_id=' . $booking_id);
        $admin_edit_url = admin_url('post.php?post=' . $booking_id . '&action=edit');
        
        // Format prices
        $formatted_total = 'GH₵ ' . number_format($total_price, 2);
        $formatted_deposit = 'GH₵ ' . number_format($deposit_amount, 2);
        $formatted_balance = 'GH₵ ' . number_format($total_price - $deposit_amount, 2);
        $payment_method = !empty($paystack_reference) ? sprintf(__('Paystack Deposit Paid (%s)', 'studio-photography'), $paystack_reference) : __('Manual Settlement / Pending', 'studio-photography');
        
        // Subject line
        $subject = sprintf('[%s] %s: %s - %s', $site_name, !empty($paystack_reference) ? __('New Deposit Booking Confirmed', 'studio-photography') : __('New Manual Booking Created', 'studio-photography'), $client_name, $package_title);
        
        // HTML Body content
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        $body = "
        <html>
        <body style=\"font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f8fafc; padding: 30px; margin: 0; color: #1e293b;\">
            <div style=\"max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);\">
                <div style=\"background: #0f172a; padding: 30px; text-align: center; color: #ffffff;\">
                    <h2 style=\"margin: 0; font-size: 20px; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase;\">{$site_name}</h2>
                    <p style=\"margin: 5px 0 0 0; font-size: 14px; color: #94a3b8;\">" . __('Booking Confirmation & Receipt Summary', 'studio-photography') . "</p>
                </div>
                
                <div style=\"padding: 30px;\">
                    <p style=\"margin-top: 0; font-size: 15px; line-height: 1.6; color: #334155;\">" . __('Hello, a new shoot session has been successfully booked through your online portal. Below are the complete itemized transaction details:', 'studio-photography') . "</p>
                    
                    <!-- Client Details Card -->
                    <div style=\"background: #f1f5f9; border-radius: 8px; padding: 20px; margin-bottom: 25px;\">
                        <h3 style=\"margin-top: 0; margin-bottom: 12px; font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em; color: #475569; border-bottom: 1px solid #cbd5e1; padding-bottom: 6px;\">" . __('Client Details', 'studio-photography') . "</h3>
                        <table style=\"width: 100%; font-size: 14px; line-height: 1.5;\">
                            <tr><td style=\"font-weight: bold; width: 35%; color: #64748b;\">" . __('Name:', 'studio-photography') . "</td><td style=\"color: #0f172a;\">" . esc_html($client_name) . "</td></tr>
                            <tr><td style=\"font-weight: bold; color: #64748b;\">" . __('Email:', 'studio-photography') . "</td><td style=\"color: #0f172a;\">" . esc_html($client_email) . "</td></tr>
                            <tr><td style=\"font-weight: bold; color: #64748b;\">" . __('Phone Number:', 'studio-photography') . "</td><td style=\"color: #0f172a;\">" . esc_html($client_phone) . "</td></tr>
                        </table>
                    </div>

                    <!-- Session Details Card -->
                    <div style=\"background: #f1f5f9; border-radius: 8px; padding: 20px; margin-bottom: 25px;\">
                        <h3 style=\"margin-top: 0; margin-bottom: 12px; font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em; color: #475569; border-bottom: 1px solid #cbd5e1; padding-bottom: 6px;\">" . __('Session & Package Details', 'studio-photography') . "</h3>
                        <table style=\"width: 100%; font-size: 14px; line-height: 1.5;\">
                            <tr><td style=\"font-weight: bold; width: 35%; color: #64748b;\">" . __('Session Type:', 'studio-photography') . "</td><td style=\"color: #0f172a;\">" . esc_html($session_title) . "</td></tr>
                            <tr><td style=\"font-weight: bold; color: #64748b;\">" . __('Selected Package:', 'studio-photography') . "</td><td style=\"color: #0f172a;\">" . esc_html($package_title) . "</td></tr>
                            <tr><td style=\"font-weight: bold; color: #64748b;\">" . __('Shoot Date:', 'studio-photography') . "</td><td style=\"color: #0f172a; font-weight: bold;\">" . esc_html($event_date) . "</td></tr>
                            <tr><td style=\"font-weight: bold; color: #64748b;\">" . __('Delivery Speed:', 'studio-photography') . "</td><td style=\"color: #0f172a;\">" . esc_html($photo_delivery_date) . " (" . esc_html(ucfirst($delivery_speed)) . ")</td></tr>
                            <tr><td style=\"font-weight: bold; color: #64748b;\">" . __('Shoot Location:', 'studio-photography') . "</td><td style=\"color: #0f172a;\">" . esc_html($event_location) . "</td></tr>
                        </table>
                    </div>

                    <!-- Financial Summary Card -->
                    <div style=\"background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 20px; margin-bottom: 30px;\">
                        <h3 style=\"margin-top: 0; margin-bottom: 12px; font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em; color: #475569; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px;\">" . __('Financial Breakdown', 'studio-photography') . "</h3>
                        <table style=\"width: 100%; font-size: 14px; line-height: 1.6;\">
                            <tr><td style=\"color: #64748b;\">" . __('Base Package Price:', 'studio-photography') . "</td><td style=\"text-align: right; color: #0f172a;\">GH₵ " . number_format($base_price, 2) . "</td></tr>
                            " . ($surcharge_amount > 0 ? "<tr><td style=\"color: #b45309;\">" . __('Rush Delivery Surcharge:', 'studio-photography') . "</td><td style=\"text-align: right; color: #b45309; font-weight: bold;\">+GH₵ " . number_format($surcharge_amount, 2) . "</td></tr>" : "") . "
                            <tr style=\"font-weight: bold; border-top: 1px solid #e2e8f0; padding-top: 5px;\"><td style=\"color: #0f172a;\">" . __('Total Final Price:', 'studio-photography') . "</td><td style=\"text-align: right; color: #0f172a;\">{$formatted_total}</td></tr>
                            <tr style=\"color: #16a34a; font-weight: bold;\"><td>" . __('Deposit Paid (' . $deposit_pct . '%):', 'studio-photography') . "</td><td style=\"text-align: right;\">{$formatted_deposit}</td></tr>
                            <tr style=\"font-weight: bold; border-top: 1px double #e2e8f0; padding-top: 5px;\"><td style=\"color: #e11d48;\">" . __('Outstanding Balance Due:', 'studio-photography') . "</td><td style=\"text-align: right; color: #e11d48;\">{$formatted_balance}</td></tr>
                            <tr><td style=\"color: #64748b; font-size: 12px;\" colspan=\"2\"><br><strong>" . __('Payment Reference:', 'studio-photography') . "</strong> {$payment_method}</td></tr>
                        </table>
                    </div>

                    <!-- Call To Action Buttons -->
                    <div style=\"text-align: center; margin-bottom: 15px;\">
                        <a href=\"{$receipt_url}\" target=\"_blank\" style=\"display: inline-block; background-color: #0f172a; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px;\">" . __('View & Print Secure Receipt', 'studio-photography') . "</a>
                        
                        <a href=\"{$admin_edit_url}\" target=\"_blank\" style=\"display: inline-block; background-color: #64748b; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px; margin-left: 10px;\">" . __('Open in WordPress Admin', 'studio-photography') . "</a>
                    </div>
                </div>
                
                <div style=\"background: #f1f5f9; padding: 20px; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0;\">
                    <p style=\"margin: 0;\">" . sprintf(__('Thank you for choosing %s! We look forward to capturing beautiful moments.', 'studio-photography'), $site_name) . "</p>
                    <p style=\"margin: 5px 0 0 0;\">© " . date('Y') . " {$site_name}. " . __('All rights reserved.', 'studio-photography') . "</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // 1. Send copy to Admin
        if (!empty($admin_email)) {
            wp_mail($admin_email, $subject, $body, $headers);
        }
        
        // 2. Send copy to Client
        if (!empty($client_email)) {
            $client_subject = sprintf(__('Your Booking Confirmation & Receipt - %s', 'studio-photography'), $site_name);
            wp_mail($client_email, $client_subject, $body, $headers);
        }

        wp_send_json_success(array(
            'booking_id' => $booking_id,
            'invoice_id' => $invoice_id,
            'invoice_number' => $inv_code
        ));

    } catch (Exception $e) {
        wp_send_json_error($e->getMessage());
    }
}
add_action('wp_ajax_submit_studio_booking', 'studio_photography_handle_ajax_booking');
add_action('wp_ajax_nopriv_submit_studio_booking', 'studio_photography_handle_ajax_booking');


// ── 8. SECURE DYNAMIC BRANDEED CLIENT RECEIPT GENERATOR ─────────────────────

function studio_photography_render_secure_client_receipt() {
    if (isset($_GET['studio_receipt']) && $_GET['studio_receipt'] == 1) {
        $booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
        $invoice_id = isset($_GET['invoice_id']) ? intval($_GET['invoice_id']) : 0;
        
        $client_name = '';
        $client_email = '';
        $client_phone = '—';
        $event_date = '';
        $location = 'Secure Online Delivery';
        $amount_total = 0.00;
        $amount_deposit = 0.00;
        $paystack_ref = '';
        $status = 'pending';
        $invoice_num = 'INV-000';
        $session = null;
        $package = null;
        $base_price = 0.00;
        $surcharge_amount = 0.00;
        $discount = 0.00;

        if ($invoice_id) {
            $invoice = get_post($invoice_id);
            if (!$invoice || $invoice->post_type !== 'invoice') {
                wp_die(__('The requested invoice record does not exist in our database.', 'studio-photography'));
            }
            $invoice_num = get_post_meta($invoice_id, '_invoice_number', true) ?: 'INV-' . $invoice_id;
            $client_name = get_post_meta($invoice_id, '_invoice_client_name', true);
            $client_email = get_post_meta($invoice_id, '_invoice_client_email', true) ?: 'client@email.com';
            $event_date = get_post_meta($invoice_id, '_invoice_event_date', true) ?: get_the_date('Y-m-d', $invoice_id);
            $amount_total = floatval(get_post_meta($invoice_id, '_invoice_total', true));
            $paystack_ref = get_post_meta($invoice_id, '_invoice_paystack_reference', true);
            $inv_status_raw = get_post_meta($invoice_id, '_invoice_status', true);
            $status = ($inv_status_raw === 'paid') ? 'completed' : 'pending';
            
            $discount = floatval(get_post_meta($invoice_id, '_invoice_discount_amount', true));
            $package_id = get_post_meta($invoice_id, '_invoice_package_id', true);
            if ($package_id) {
                $package = get_post($package_id);
                $session_id = $package ? get_post_meta($package_id, '_package_session_id', true) : 0;
                $session = get_post($session_id);
                $surcharge_amount = floatval(get_post_meta($invoice_id, '_invoice_surcharge_amount', true));
                $base_price = floatval(get_post_meta($invoice_id, '_invoice_base_price', true)) ?: ($amount_total - $surcharge_amount);
            } else {
                $base_price = $amount_total;
            }
        } elseif ($booking_id) {
            $booking = get_post($booking_id);
            if (!$booking || $booking->post_type !== 'booking') {
                wp_die(__('The requested booking record does not exist in our database.', 'studio-photography'));
            }

            $client_name = get_post_meta($booking_id, '_booking_client_name', true);
            $client_email = get_post_meta($booking_id, '_booking_client_email', true);
            $client_phone = get_post_meta($booking_id, '_booking_client_phone', true) ?: '—';
            $event_date = get_post_meta($booking_id, '_booking_event_date', true);
            $location = get_post_meta($booking_id, '_booking_location', true) ?: 'Studio';
            $amount_total = floatval(get_post_meta($booking_id, '_booking_amount_total', true));
            $amount_deposit = floatval(get_post_meta($booking_id, '_booking_amount_deposit', true));
            $paystack_ref = get_post_meta($booking_id, '_booking_paystack_reference', true);
            $status = get_post_meta($booking_id, '_booking_status', true) ?: 'pending';

            $session_id = get_post_meta($booking_id, '_booking_session_id', true);
            $package_id = get_post_meta($booking_id, '_booking_package_id', true);
            $session = get_post($session_id);
            $package = get_post($package_id);

            $invoice_num = 'INV-' . date('Y') . '-' . sprintf('%03d', $booking_id);
            $surcharge_amount = floatval(get_post_meta($booking_id, '_booking_delivery_surcharge', true));
            $discount = floatval(get_post_meta($booking_id, '_booking_discount', true));
            $base_price = $amount_total - $surcharge_amount;
        } else {
            wp_die(__('Missing reference parameters for receipt generation.', 'studio-photography'));
        }

        $site_name = get_bloginfo('name');

        // Fetch all active packages for the current Session Type to display comparison table on invoice
        $all_session_packages = array();
        if ($session_id) {
            $packages_query = new WP_Query(array(
                'post_type' => 'package',
                'posts_per_page' => -1,
                'meta_key' => '_package_session_id',
                'meta_value' => $session_id,
                'orderby' => 'meta_value_num',
                'order' => 'ASC'
            ));
            if ($packages_query->have_posts()) {
                while ($packages_query->have_posts()) {
                    $packages_query->the_post();
                    $p_id = get_the_ID();
                    
                    $features_raw = get_post_meta($p_id, '_package_features', true);
                    $features = array();
                    if (!empty($features_raw)) {
                        if (is_array($features_raw)) {
                            $features = $features_raw;
                        } else {
                            $features = array_map('trim', explode("\n", str_replace("\r", "", $features_raw)));
                        }
                    }
                    
                    $all_session_packages[] = array(
                        'id' => $p_id,
                        'name' => get_the_title(),
                        'price' => floatval(get_post_meta($p_id, '_package_price', true)),
                        'duration' => get_post_meta($p_id, '_package_duration', true) ?: '1 hour',
                        'edited_photos' => intval(get_post_meta($p_id, '_package_edited_photos', true)) ?: 10,
                        'features' => $features
                    );
                }
                wp_reset_postdata();
            }
        }
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Receipt_<?php echo esc_attr($invoice_num); ?></title>
            <script src="https://cdn.tailwindcss.com"></script>
            <script src="https://unpkg.com/lucide@latest"></script>
            <style>
                @media print {
                    .no-print { display: none !important; }
                    body { background: white !important; padding: 0 !important; }
                    .print-card { border: none !important; box-shadow: none !important; padding: 0 !important; }
                }
            </style>
        </head>
        <body class="bg-slate-50 min-h-screen py-12 px-4 flex flex-col items-center">
            
            <div class="max-w-2xl w-full mb-6 flex justify-between items-center no-print">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-900 font-medium font-semibold no-underline">
                    <i data-lucide="arrow-left" class="h-4 w-4"></i> <?php _e('Return to Website', 'studio-photography'); ?>
                </a>
                <button onclick="window.print();" class="inline-flex items-center gap-2 bg-slate-900 text-white rounded-lg px-4 py-2 text-sm font-semibold hover:bg-slate-800 shadow-sm transition-all">
                    <i data-lucide="printer" class="h-4 w-4"></i> <?php _e('Print / Save PDF', 'studio-photography'); ?>
                </button>
            </div>

            <div class="max-w-2xl w-full bg-white border border-slate-200 rounded-2xl shadow-lg p-8 print-card">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-slate-100 pb-6 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="studio-receipt-logo">
                            <?php echo studio_photography_get_receipt_logo_html(isset($invoice_id) ? $invoice_id : 0); ?>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-slate-900"><?php echo esc_html($site_name); ?></h2>
                            <p class="text-xs text-slate-400"><?php _e('Professional Photography Studio', 'studio-photography'); ?></p>
                        </div>
                    </div>
                    <div class="mt-4 md:mt-0 text-right">
                        <span class="text-xs font-bold uppercase tracking-wider text-green-600 bg-green-50 px-2.5 py-1 rounded-full"><?php _e('Payment Successful', 'studio-photography'); ?></span>
                        <p class="text-sm text-slate-900 font-bold mt-2.5"><?php _e('Receipt No:', 'studio-photography'); ?> <?php echo esc_html($invoice_num); ?></p>
                        <p class="text-xs text-slate-400 mt-1"><?php _e('Date:', 'studio-photography'); ?> <?php echo date('F d, Y'); ?></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 text-sm">
                    <div>
                        <h4 class="font-bold text-slate-400 uppercase text-xs mb-2"><?php _e('Billed To', 'studio-photography'); ?></h4>
                        <p class="font-bold text-slate-800 text-base mb-1"><?php echo esc_html($client_name); ?></p>
                        <p class="text-slate-500 mb-0.5"><?php echo esc_html($client_phone); ?></p>
                        <p class="text-slate-500"><?php echo esc_html($client_email); ?></p>
                    </div>
                    <div class="md:text-right">
                        <h4 class="font-bold text-slate-400 uppercase text-xs mb-2"><?php _e('Issued By', 'studio-photography'); ?></h4>
                        <p class="font-bold text-slate-800 text-base mb-1"><?php echo esc_html($site_name); ?></p>
                        <p class="text-slate-500 mb-0.5"><?php echo esc_html(studio_photography_get_business_email()); ?></p>
                        <p class="text-slate-500"><?php echo esc_html(studio_photography_get_business_location()); ?></p>
                    </div>
                </div>

                <table class="w-full text-left text-sm mb-8">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 uppercase text-xs">
                            <th class="py-3 font-semibold"><?php _e('Photography Service & Package', 'studio-photography'); ?></th>
                            <th class="py-3 text-right font-semibold"><?php _e('Shoot Date', 'studio-photography'); ?></th>
                            <th class="py-3 text-right font-semibold"><?php _e('Cedi Total', 'studio-photography'); ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="text-slate-800">
                            <td class="py-4 font-bold">
                                <?php echo $session ? esc_html($session->post_title) : esc_html__('Custom Shoot', 'studio-photography'); ?>
                                <span class="block text-xs text-slate-400 font-normal mt-1"><?php _e('Tier:', 'studio-photography'); ?> <?php echo $package ? esc_html($package->post_title) : esc_html__('Custom Pack', 'studio-photography'); ?> • <?php echo esc_html($location); ?></span>
                            </td>
                            <td class="py-4 text-right text-slate-600 font-medium"><?php echo esc_html(date('M d, Y', strtotime($event_date))); ?></td>
                            <td class="py-4 text-right font-bold text-slate-900">GH₵ <?php echo number_format($amount_total, 2); ?></td>
                        </tr>
                    </tbody>
                </table>

                <div class="border-t border-slate-100 pt-5 mb-8 flex justify-end">
                    <div class="w-full md:w-1/2 text-sm space-y-2.5">
                        <div class="flex justify-between text-slate-500">
                            <span><?php _e('Subtotal Price:', 'studio-photography'); ?></span>
                            <span class="font-medium text-slate-900">GH₵ <?php echo number_format($amount_total, 2); ?></span>
                        </div>
                        
                        <?php if ($discount > 0) : ?>
                            <div class="flex justify-between text-red-600 font-semibold bg-red-50/50 p-2.5 rounded-lg border border-red-100/30">
                                <span class="flex items-center gap-1">
                                    <i data-lucide="tag" class="h-4 w-4"></i>
                                    <?php _e('Discount Applied:', 'studio-photography'); ?>
                                </span>
                                <span>-GH₵ <?php echo number_format($discount, 2); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="flex justify-between text-green-600 font-semibold bg-green-50/50 p-2.5 rounded-lg border border-green-100/50">
                            <span class="flex items-center gap-1">
                                <i data-lucide="check-circle" class="h-4 w-4"></i>
                                <?php echo $status === 'completed' ? esc_html__('Total Settled:', 'studio-photography') : esc_html__('Deposit Paid via Paystack:', 'studio-photography'); ?>
                            </span>
                            <span>GH₵ <?php echo $status === 'completed' ? number_format($amount_total - $discount, 2) : number_format($amount_deposit, 2); ?></span>
                        </div>

                        <div class="flex justify-between text-slate-700 font-medium border-t border-slate-100 pt-2.5">
                            <span><?php _e('Outstanding Balance Due:', 'studio-photography'); ?></span>
                            <span class="font-bold text-slate-900">
                                GH₵ <?php echo $status === 'completed' ? '0.00' : number_format($amount_total - $discount - $amount_deposit, 2); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-4 text-xs text-slate-500 leading-relaxed mb-6">
                    <span class="font-bold block text-slate-700 mb-1 flex items-center gap-1"><i data-lucide="lock" class="h-3.5 w-3.5 text-blue-600"></i> <?php _e('Secure Electronic Transaction Log:', 'studio-photography'); ?></span>
                    <?php if (!empty($paystack_ref)) : ?>
                        <?php _e('This transaction was authenticated and processed successfully by Paystack. Secure Transaction ID Reference:', 'studio-photography'); ?> <strong><?php echo esc_html($paystack_ref); ?></strong>.
                    <?php else : ?>
                        <?php _e('This booking was registered manually inside the Studio Dashboard by the administrator.', 'studio-photography'); ?>
                    <?php endif; ?>
                </div>

                <p class="text-center text-slate-400 text-xs mt-12 border-t pt-4 leading-relaxed"><?php _e('Thank you for choosing Whyte Creatives! We look forward to capturing your beautiful story.', 'studio-photography'); ?><br>© <?php echo date('Y'); ?> <?php echo esc_html($site_name); ?>. <?php _e('All rights reserved.', 'studio-photography'); ?></p>
            </div>

            <script>
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
                window.onload = function() {
                    window.print();
                }
            </script>
        </body>
        </html>
        <?php
        exit;
    }
}
add_action('template_redirect', 'studio_photography_render_secure_client_receipt');


// ── 8.5 DEDICATED BOOKING CONFIRMATION & VALUE SUMMARY RECEIPT ────────────────

function studio_photography_render_secure_booking_receipt() {
    if (isset($_GET['studio_booking_receipt']) && $_GET['studio_booking_receipt'] == 1) {
        $booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
        
        if (empty($booking_id)) {
            wp_die(__('Missing booking parameters for confirmation generation.', 'studio-photography'));
        }

        $booking = get_post($booking_id);
        if (!$booking || $booking->post_type !== 'booking') {
            wp_die(__('The requested booking record does not exist in our database.', 'studio-photography'));
        }

        $client_name = get_post_meta($booking_id, '_booking_client_name', true);
        $client_email = get_post_meta($booking_id, '_booking_client_email', true);
        $client_phone = get_post_meta($booking_id, '_booking_client_phone', true) ?: '—';
        $event_date = get_post_meta($booking_id, '_booking_event_date', true);
        $location = get_post_meta($booking_id, '_booking_location', true) ?: 'Studio';
        $amount_total = floatval(get_post_meta($booking_id, '_booking_amount_total', true));
        $amount_deposit = floatval(get_post_meta($booking_id, '_booking_amount_deposit', true));
        $paystack_ref = get_post_meta($booking_id, '_booking_paystack_reference', true);
        $status = get_post_meta($booking_id, '_booking_status', true) ?: 'pending';

        $session_id = get_post_meta($booking_id, '_booking_session_id', true);
        $package_id = get_post_meta($booking_id, '_booking_package_id', true);
        $session = get_post($session_id);
        $package = get_post($package_id);

        $site_name = get_bloginfo('name');
        $invoice_num = 'INV-' . date('Y') . '-' . sprintf('%03d', $booking_id);
        $surcharge_amount = floatval(get_post_meta($booking_id, '_booking_delivery_surcharge', true));
        $base_price = $amount_total - $surcharge_amount;

        // Fetch all active packages for this Session to display comparison grid on the booking receipt
        $all_session_packages = array();
        if ($session_id) {
            $packages_query = new WP_Query(array(
                'post_type' => 'package',
                'posts_per_page' => -1,
                'meta_key' => '_package_session_id',
                'meta_value' => $session_id,
                'orderby' => 'meta_value_num',
                'order' => 'ASC'
            ));
            if ($packages_query->have_posts()) {
                while ($packages_query->have_posts()) {
                    $packages_query->the_post();
                    $p_id = get_the_ID();
                    
                    $features_raw = get_post_meta($p_id, '_package_features', true);
                    $features = array();
                    if (!empty($features_raw)) {
                        if (is_array($features_raw)) {
                            $features = $features_raw;
                        } else {
                            $features = array_map('trim', explode("\n", str_replace("\r", "", $features_raw)));
                        }
                    }
                    
                    $all_session_packages[] = array(
                        'id' => $p_id,
                        'name' => get_the_title(),
                        'price' => floatval(get_post_meta($p_id, '_package_price', true)),
                        'duration' => get_post_meta($p_id, '_package_duration', true) ?: '1 hour',
                        'edited_photos' => intval(get_post_meta($p_id, '_package_edited_photos', true)) ?: 10,
                        'features' => $features
                    );
                }
                wp_reset_postdata();
            }
        }
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>BookingConfirmation_<?php echo esc_attr($invoice_num); ?></title>
            <script src="https://cdn.tailwindcss.com"></script>
            <script src="https://unpkg.com/lucide@latest"></script>
            <style>
                @media print {
                    .no-print { display: none !important; }
                    body { background: white !important; padding: 0 !important; }
                    .print-card { border: none !important; box-shadow: none !important; padding: 0 !important; }
                }
            </style>
        </head>
        <body class="bg-slate-50 min-h-screen py-12 px-4 flex flex-col items-center">
            
            <div class="max-w-3xl w-full mb-6 flex justify-between items-center no-print">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-900 font-medium font-semibold no-underline">
                    <i data-lucide="arrow-left" class="h-4 w-4"></i> <?php _e('Return to Website', 'studio-photography'); ?>
                </a>
                <button onclick="window.print();" class="inline-flex items-center gap-2 bg-slate-900 text-white rounded-lg px-4 py-2 text-sm font-semibold hover:bg-slate-800 shadow-sm transition-all">
                    <i data-lucide="printer" class="h-4 w-4"></i> <?php _e('Print Confirmation', 'studio-photography'); ?>
                </button>
            </div>

            <div class="max-w-3xl w-full bg-white border border-slate-200 rounded-2xl shadow-lg p-8 print-card">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-slate-100 pb-6 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="studio-receipt-logo">
                            <?php echo studio_photography_get_receipt_logo_html(isset($invoice_id) ? $invoice_id : 0); ?>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-slate-900"><?php echo esc_html($site_name); ?></h2>
                            <p class="text-xs text-slate-400"><?php _e('Professional Photography Studio', 'studio-photography'); ?></p>
                        </div>
                    </div>
                    <div class="mt-4 md:mt-0 text-right">
                        <span class="text-xs font-bold uppercase tracking-wider text-blue-600 bg-blue-50 px-2.5 py-1 rounded-full"><?php _e('Booking Confirmed', 'studio-photography'); ?></span>
                        <p class="text-sm text-slate-900 font-bold mt-2.5"><?php _e('Booking Ref:', 'studio-photography'); ?> <?php echo esc_html($invoice_num); ?></p>
                        <p class="text-xs text-slate-400 mt-1"><?php _e('Date:', 'studio-photography'); ?> <?php echo date('F d, Y'); ?></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 text-sm">
                    <div>
                        <h4 class="font-bold text-slate-400 uppercase text-xs mb-2"><?php _e('Client Details', 'studio-photography'); ?></h4>
                        <p class="font-bold text-slate-800 text-base mb-1"><?php echo esc_html($client_name); ?></p>
                        <p class="text-slate-500 mb-0.5"><?php echo esc_html($client_phone); ?></p>
                        <p class="text-slate-500"><?php echo esc_html($client_email); ?></p>
                    </div>
                    <div class="md:text-right">
                        <h4 class="font-bold text-slate-400 uppercase text-xs mb-2"><?php _e('Studio Details', 'studio-photography'); ?></h4>
                        <p class="font-bold text-slate-800 text-base mb-1"><?php echo esc_html($site_name); ?></p>
                        <p class="text-slate-500 mb-0.5"><?php echo esc_html(studio_photography_get_business_email()); ?></p>
                        <p class="text-slate-500"><?php echo esc_html(studio_photography_get_business_location()); ?></p>
                    </div>
                </div>

                <!-- Main Booking Receipt -->
                <table class="w-full text-left text-sm mb-8">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 uppercase text-xs">
                            <th class="py-3 font-semibold"><?php _e('Photography Service & Package', 'studio-photography'); ?></th>
                            <th class="py-3 text-right font-semibold"><?php _e('Shoot Date', 'studio-photography'); ?></th>
                            <th class="py-3 text-right font-semibold"><?php _e('Cedi Total', 'studio-photography'); ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="text-slate-800">
                            <td class="py-4 font-bold">
                                <?php echo $session ? esc_html($session->post_title) : esc_html__('Custom Shoot', 'studio-photography'); ?>
                                <span class="block text-xs text-slate-400 font-normal mt-1">
                                    <?php _e('Tier:', 'studio-photography'); ?> <?php echo $package ? esc_html($package->post_title) : esc_html__('Custom Pack', 'studio-photography'); ?> 
                                    <?php if ($location !== 'Secure Online Delivery') : ?>• <?php echo esc_html($location); ?><?php endif; ?>
                                </span>
                            </td>
                            <td class="py-4 text-right text-slate-600 font-medium"><?php echo !empty($event_date) ? esc_html(date('M d, Y', strtotime($event_date))) : esc_html__('Unset', 'studio-photography'); ?></td>
                            <td class="py-4 text-right font-bold text-slate-900">GH₵ <?php echo number_format($base_price, 2); ?></td>
                        </tr>
                        <?php if ($surcharge_amount > 0) : ?>
                            <tr class="text-slate-800">
                                <td class="py-4 font-medium text-amber-600 flex items-center gap-1">
                                    <i data-lucide="sparkles" class="h-3.5 w-3.5 inline"></i>
                                    <?php _e('Priority Delivery Surcharge (Rush)', 'studio-photography'); ?>
                                </td>
                                <td class="py-4 text-right text-slate-400 font-normal">—</td>
                                <td class="py-4 text-right font-bold text-amber-600">+GH₵ <?php echo number_format($surcharge_amount, 2); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="border-t border-slate-100 pt-5 mb-8 flex justify-end">
                    <div class="w-full md:w-1/2 text-sm space-y-2.5">
                        <div class="flex justify-between text-slate-500">
                            <span><?php _e('Subtotal Price:', 'studio-photography'); ?></span>
                            <span class="font-medium text-slate-900">GH₵ <?php echo number_format($amount_total, 2); ?></span>
                        </div>
                        
                        <div class="flex justify-between text-green-600 font-semibold bg-green-50/50 p-2.5 rounded-lg border border-green-100/50">
                            <span class="flex items-center gap-1">
                                <i data-lucide="check-circle" class="h-4 w-4"></i>
                                <?php echo $status === 'completed' ? esc_html__('Total Settled:', 'studio-photography') : esc_html__('Deposit Paid via Paystack:', 'studio-photography'); ?>
                            </span>
                            <span>GH₵ <?php echo $status === 'completed' ? number_format($amount_total, 2) : number_format($amount_deposit, 2); ?></span>
                        </div>

                        <div class="flex justify-between text-slate-700 font-medium border-t border-slate-100 pt-2.5">
                            <span><?php _e('Outstanding Balance Due:', 'studio-photography'); ?></span>
                            <span class="font-bold text-slate-900 font-sans">
                                GH₵ <?php echo $status === 'completed' ? '0.00' : number_format($amount_total - $amount_deposit, 2); ?>
                            </span>
                        </div>
                    </div>
                </div>



                <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-4 text-xs text-slate-500 leading-relaxed mb-6">
                    <span class="font-bold block text-slate-700 mb-1 flex items-center gap-1"><i data-lucide="lock" class="h-3.5 w-3.5 text-blue-600"></i> <?php _e('Secure Electronic Transaction Log:', 'studio-photography'); ?></span>
                    <?php if (!empty($paystack_ref)) : ?>
                        <?php _e('This transaction was authenticated and processed successfully by Paystack. Secure Transaction ID Reference:', 'studio-photography'); ?> <strong><?php echo esc_html($paystack_ref); ?></strong>.
                    <?php else : ?>
                        <?php _e('This booking was registered manually inside the Studio Dashboard by the administrator.', 'studio-photography'); ?>
                    <?php endif; ?>
                </div>

                <p class="text-center text-slate-400 text-xs mt-12 border-t pt-4 leading-relaxed"><?php _e('Thank you for choosing Whyte Creatives! We look forward to capturing your beautiful story.', 'studio-photography'); ?><br>© <?php echo date('Y'); ?> <?php echo esc_html($site_name); ?>. <?php _e('All rights reserved.', 'studio-photography'); ?></p>
            </div>

            <script>
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            </script>
        </body>
        </html>
        <?php
        exit;
    }
}
add_action('template_redirect', 'studio_photography_render_secure_booking_receipt');


// ── 9. PHP HELPER TO TRANSLATE GOOGLE DRIVE SHARE LINKS TO DIRECT IMAGE URLS ──

function studio_photography_get_google_drive_direct_url($url) {
    if (strpos($url, 'drive.google.com') !== false) {
        // Handle standard share links: drive.google.com/file/d/FILE_ID/view?usp=sharing
        if (preg_match('/\/file\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return 'https://lh3.googleusercontent.com/d/' . $matches[1];
        } 
        // Handle open?id=FILE_ID format links
        elseif (preg_match('/id=([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return 'https://lh3.googleusercontent.com/d/' . $matches[1];
        }
    }
    return $url; // return original S3 URL if not google drive
}


// ── 10. DYNAMIC SERVER-PACKAGED GALLERY ZIP GENERATOR (NO THIRD-PARTY!) ──────

// Fetch + parse a public Google Drive folder listing (no API keys needed!). Shared by the
// gallery grid AND the ZIP builder so both always see identical file lists.
// Strategy 1: the stable "embeddedfolderview" listing endpoint. Strategy 2 (fallback): the regular folder page.
function studio_photography_fetch_gdrive_folder_files($folder_id) {
    $result = array('files' => array(), 'diag' => array());
    $ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36';

    $non_image_types = array('.pdf', '.zip', '.rar', '.mus', '.docx', '.xlsx', '.mp3', '.mp4', ' pdf', ' binary');
    $seen_ids = array();
    $file_index = 1;

    $add_file = function ($id, $title) use (&$result, &$seen_ids, &$file_index, $non_image_types) {
        if (empty($id) || in_array($id, $seen_ids)) return;
        $seen_ids[] = $id;

        $clean_title = preg_replace('/\s+(Binary|PDF|Image|Video|Archive|Document)$/i', '', $title);
        $clean_title = preg_replace('/\.(jpg|jpeg|png|webp|gif)$/i', '', $clean_title);

        $title_lower = strtolower($title);
        foreach ($non_image_types as $type) {
            if (strpos($title_lower, $type) !== false) return; // skip non-images
        }

        $direct_view = 'https://lh3.googleusercontent.com/d/' . $id;
        $result['files'][] = array(
            'url' => $direct_view,
            'thumbnail' => studio_photography_sized_image_url($direct_view, 800),
            'download_url' => 'https://drive.google.com/uc?export=download&id=' . $id,
            'title' => $clean_title ?: ('Photo ' . sprintf('%02d', $file_index))
        );
        $file_index++;
    };

    // ── Strategy 1: embeddedfolderview (Google's stable lightweight folder listing) ──
    $response = wp_remote_get('https://drive.google.com/embeddedfolderview?id=' . $folder_id . '#list', array(
        'user-agent' => $ua, 'timeout' => 15, 'sslverify' => false
    ));
    if (!is_wp_error($response)) {
        $html = wp_remote_retrieve_body($response);
        $result['diag']['embedded_status'] = wp_remote_retrieve_response_code($response);
        $result['diag']['embedded_html_length'] = strlen($html);

        if (preg_match_all('/id="entry-([a-zA-Z0-9_-]{25,})"[\s\S]{0,800}?flip-entry-title">([^<]*)</', $html, $m_entries, PREG_SET_ORDER)) {
            foreach ($m_entries as $m) {
                $add_file($m[1], html_entity_decode($m[2], ENT_QUOTES, 'UTF-8'));
            }
        }
    } else {
        $result['diag']['embedded_error'] = $response->get_error_message();
    }

    // ── Strategy 2 (fallback): the regular folder page scrape ──
    if (empty($result['files'])) {
        $response = wp_remote_get('https://drive.google.com/drive/folders/' . $folder_id, array(
            'user-agent' => $ua, 'timeout' => 15, 'sslverify' => false
        ));
        if (is_wp_error($response)) {
            $result['diag']['error'] = $response->get_error_message();
            return $result;
        }

        $html = wp_remote_retrieve_body($response);
        $result['diag']['status_code'] = wp_remote_retrieve_response_code($response);
        $result['diag']['html_length'] = strlen($html);

        if (preg_match_all('/data-id="([a-zA-Z0-9_-]{25,50})"[^>]*?data-tooltip="([^"]+)"/', $html, $matches_state, PREG_SET_ORDER)) {
            foreach ($matches_state as $m) {
                $add_file($m[1], html_entity_decode($m[2], ENT_QUOTES, 'UTF-8'));
            }
        }
        if (preg_match_all('/\/file\/d\/([a-zA-Z0-9_-]{28,45})\b/', $html, $matches_url)) {
            foreach (array_unique($matches_url[1]) as $id) {
                $add_file($id, 'Photo ' . sprintf('%02d', $file_index));
            }
        }
    }

    $result['diag']['raw_matches_count'] = count($seen_ids);
    $result['diag']['filtered_files_count'] = count($result['files']);

    return $result;
}

// Resolve every remote photo of a gallery (individual cloud URLs + whole Drive folders)
// into direct-download URLs the server can fetch — powers the "Full Collection ZIP".
function studio_photography_get_remote_gallery_photo_urls($gallery_id) {
    $urls = array();
    $added_keys = array();

    // A. Individual cloud URLs (S3 / Drive file links / any direct image URL)
    $cloud_raw = get_post_meta($gallery_id, '_gallery_cloud_urls', true);
    if (!empty($cloud_raw)) {
        $lines = array_filter(array_map('trim', explode("\n", str_replace("\r", "", $cloud_raw))));
        $i = 0;
        foreach ($lines as $line) {
            $i++;
            if (!filter_var($line, FILTER_VALIDATE_URL)) continue;

            $fetch_url = $line;
            $name = basename(parse_url($line, PHP_URL_PATH));
            if (strpos($line, 'drive.google.com') !== false) {
                $gid = '';
                if (preg_match('/\/file\/d\/([a-zA-Z0-9_-]+)/', $line, $m)) $gid = $m[1];
                elseif (preg_match('/id=([a-zA-Z0-9_-]+)/', $line, $m)) $gid = $m[1];
                if (!empty($gid)) {
                    $fetch_url = 'https://lh3.googleusercontent.com/d/' . $gid; // direct CDN link — much faster than the redirecting download URL
                    $name = 'drive_' . $gid . '.jpg';
                }
            }
            if (empty($name) || strpos($name, '.') === false) $name = 'photo_' . sprintf('%03d', $i) . '.jpg';

            if (!isset($added_keys[$fetch_url])) {
                $added_keys[$fetch_url] = true;
                $urls[] = array('url' => $fetch_url, 'name' => $name);
            }
        }
    }

    // B. Whole public Google Drive folders (uses the same 1-minute cache as the gallery grid)
    $gdrive_folder = get_post_meta($gallery_id, '_gallery_gdrive_folder', true);
    if (!empty($gdrive_folder) && strpos($gdrive_folder, 'drive.google.com') !== false) {
        $folder_id = '';
        if (preg_match('/\/folders\/([a-zA-Z0-9_-]+)/', $gdrive_folder, $m)) $folder_id = $m[1];
        elseif (preg_match('/id=([a-zA-Z0-9_-]+)/', $gdrive_folder, $m)) $folder_id = $m[1];

        if (!empty($folder_id)) {
            $folder_files = get_transient('studio_gdrive_folder_' . $folder_id);
            if ($folder_files === false) {
                $fetched = studio_photography_fetch_gdrive_folder_files($folder_id);
                $folder_files = $fetched['files'];
                if (!empty($folder_files)) {
                    set_transient('studio_gdrive_folder_' . $folder_id, $folder_files, MINUTE_IN_SECONDS);
                }
            }
            if (!empty($folder_files)) {
                foreach ($folder_files as $idx => $f) {
                    if (!isset($added_keys[$f['download_url']])) {
                        $added_keys[$f['download_url']] = true;
                        $safe_name = sanitize_title($f['title'] ?: ('photo-' . sprintf('%02d', $idx + 1)));
                        $urls[] = array('url' => $f['url'], 'name' => $safe_name . '.jpg'); // direct lh3 CDN link — no redirect hop
                    }
                }
            }
        }
    }

    return $urls;
}

// Send the client back to their gallery with a friendly on-screen message instead of a raw
// wp_die() HTML page (which browsers would otherwise save as "download.html")
function studio_photography_redirect_download_refusal($gallery_id, $code) {
    $url = get_permalink(intval($gallery_id));
    if (empty($url) || is_wp_error($url)) $url = home_url('/');
    wp_safe_redirect(add_query_arg('studio_dl_error', $code, $url));
    exit;
}

// Collect the downloadable local attachments for a gallery (excludes the active cover photo)
function studio_photography_get_downloadable_local_attachments($gallery_id) {
    // ALL attached images — the cover photo is PART of the delivered collection,
    // so the ZIP and the displayed photo count always match (no duplicates in the grid itself).
    $images = get_attached_media('image', $gallery_id);
    return empty($images) ? array() : $images;
}

function studio_photography_handle_server_gallery_zip_generation() {
    if (isset($_GET['studio_download_zip']) && $_GET['studio_download_zip'] == 1) {
        $gallery_id = isset($_GET['gallery_id']) ? intval($_GET['gallery_id']) : 0;

        if (empty($gallery_id)) {
            wp_die(__('Missing gallery parameter for ZIP download compilation.', 'studio-photography'));
        }

        // Verify gallery unlock state (Security Guard!)
        $balance_amount = floatval(get_post_meta($gallery_id, '_gallery_balance_amount', true));
        $payment_status = get_post_meta($gallery_id, '_gallery_payment_status', true) ?: 'paid';
        
        $booking_id = get_post_meta($gallery_id, '_gallery_booking_id', true);
        if (!empty($booking_id)) {
            $total_amount = floatval(get_post_meta($booking_id, '_booking_amount_total', true));
            $deposit_paid = floatval(get_post_meta($booking_id, '_booking_amount_deposit', true));
            $balance_amount = $total_amount - $deposit_paid;
        }

        if ($balance_amount > 0 && $payment_status === 'unpaid' && !current_user_can('manage_options')) {
            studio_photography_redirect_download_refusal($gallery_id, 'balance');
        }

        $gallery = get_post($gallery_id);
        if (!$gallery || $gallery->post_type !== 'client_gallery') {
            wp_die(__('The requested client gallery record does not exist in our database.', 'studio-photography'));
        }

        // Gallery expiry guard (Pixieset-style automatic expiration!)
        $expiry_state = studio_photography_gallery_expiry_state($gallery_id);
        if ($expiry_state['expires'] && $expiry_state['expired'] && !current_user_can('manage_options')) {
            studio_photography_redirect_download_refusal($gallery_id, 'expired');
        }

        // ── ZIP CACHE (FAST PATH FIRST — zero network lookups before the cache check!) ──
        $images = studio_photography_get_downloadable_local_attachments($gallery_id);

        $upload_info = wp_upload_dir();
        $zip_dir = trailingslashit($upload_info['basedir']) . 'studio-zips';
        if (!is_dir($zip_dir)) { wp_mkdir_p($zip_dir); }
        if (!file_exists($zip_dir . '/index.php')) { @file_put_contents($zip_dir . '/index.php', '<?php // Silence is golden.'); }

        $cache_path = $zip_dir . '/gallery-' . $gallery_id . '.zip';

        // Signature built from CHEAP post meta only — a cache hit never touches Google Drive
        $cache_sig = md5(implode('|', array(
            $gallery->post_modified,
            implode(',', array_keys($images)),
            md5((string) get_post_meta($gallery_id, '_gallery_cloud_urls', true)),
            (string) get_post_meta($gallery_id, '_gallery_gdrive_folder', true),
            (string) get_post_meta($gallery_id, '_gallery_cover_url', true),
            (string) get_post_thumbnail_id($gallery_id)
        )));
        $cached_sig = get_post_meta($gallery_id, '_gallery_zip_cache_sig', true);
        $cache_ok = (file_exists($cache_path) && $cached_sig === $cache_sig && (time() - (int) filemtime($cache_path)) < 12 * HOUR_IN_SECONDS && filesize($cache_path) > 0);

        $clean_title = sanitize_title($gallery->post_title);
        $download_name = $clean_title . '_highres_photos.zip';

        if ($cache_ok) {
            // 🚀 INSTANT PATH — stream the ready archive, then send the notification email after
            $photo_count = max(1, intval(get_post_meta($gallery_id, '_gallery_zip_cache_count', true)));
            $event = studio_photography_record_download_event($gallery_id, 'zip', sprintf(__('Full Collection ZIP (%d photos)', 'studio-photography'), $photo_count), true);

            if (ob_get_level()) { ob_end_clean(); }
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $download_name . '"');
            header('Content-Length: ' . filesize($cache_path));
            header('Pragma: no-cache');
            header('Expires: 0');
            readfile($cache_path);

            // Mail AFTER the bytes are delivered — email latency never delays the download
            if ($event) { studio_photography_send_download_alert_email($event, 1); }
            exit;
        }

        // ── SLOW PATH — resolve remote photos (network) and build the archive ──
        $remote_photos = studio_photography_get_remote_gallery_photo_urls($gallery_id);
        if (empty($images) && empty($remote_photos)) {
            studio_photography_redirect_download_refusal($gallery_id, 'empty');
        }

        // Verify server supports ZipArchive (only needed when building a fresh archive)
        if (!class_exists('ZipArchive')) {
            wp_die(__('Server Error: Your hosting server does not have the PHP ZipArchive extension enabled. Please contact your host, or use external zip download links.', 'studio-photography'));
        }

        $zip = new ZipArchive();
        if ($zip->open($cache_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            wp_die(__('Server Error: Could not compile ZIP archive file on the disk.', 'studio-photography'));
        }

        // Loop and add locally attached high-resolution images
        $files_added = 0;
        $temp_files = array();
        foreach ($images as $img_id => $img_post) {
            $file_path = get_attached_file($img_id); // Get absolute local server file path!
            if ($file_path && file_exists($file_path)) {
                $zip->addFile($file_path, basename($file_path));
                $files_added++;
            }
        }

        // Stream remote photos (Google Drive / S3 / cloud URLs) straight into the archive
        if (!empty($remote_photos)) {
            @set_time_limit(600); // big remote galleries take a moment to fetch (cached afterwards!)
            $remote_index = 0;
            foreach ($remote_photos as $rp) {
                $remote_index++;
                if ($remote_index > 150) break; // per-archive safety cap

                $resp = wp_remote_get($rp['url'], array(
                    'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
                    'timeout' => 20,
                    'sslverify' => false
                ));
                if (is_wp_error($resp) || wp_remote_retrieve_response_code($resp) !== 200) continue;

                $body = wp_remote_retrieve_body($resp);
                if (empty($body) || strlen($body) < 1024) continue;

                // Only pack real images (JPEG/PNG/GIF/WebP) — skips HTML error/confirm pages
                if (!preg_match('/^(\xFF\xD8\xFF|\x89PNG|GIF8|RIFF)/', substr($body, 0, 4))) continue;

                $tmp = tempnam(sys_get_temp_dir(), 'studzip_');
                file_put_contents($tmp, $body);
                $zip->addFile($tmp, sprintf('%03d_%s', $files_added + 1, $rp['name']));
                $temp_files[] = $tmp;
                $files_added++;
            }
        }

        $zip->close();

        // Remove downloaded temp files now that the archive is sealed
        foreach ($temp_files as $tmp) { @unlink($tmp); }

        if ($files_added === 0) {
            @unlink($cache_path);
            studio_photography_redirect_download_refusal($gallery_id, 'empty');
        }

        update_post_meta($gallery_id, '_gallery_zip_cache_sig', $cache_sig);
        update_post_meta($gallery_id, '_gallery_zip_cache_count', $files_added);

        $event = studio_photography_record_download_event($gallery_id, 'zip', sprintf(__('Full Collection ZIP (%d photos)', 'studio-photography'), $files_added), true);

        // Clear output buffering to prevent corrupt downloads
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Stream ZIP directly to client browser
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $download_name . '"');
        header('Content-Length: ' . filesize($cache_path));
        header('Pragma: no-cache');
        header('Expires: 0');
        readfile($cache_path);

        // Mail AFTER the bytes are delivered — email latency never delays the download
        if ($event) { studio_photography_send_download_alert_email($event, 1); }
        exit;
    }
}
add_action('template_redirect', 'studio_photography_handle_server_gallery_zip_generation');


// ── 10A. GALLERY EXPIRY SYSTEM (PIXIESET-STYLE AUTOMATIC EXPIRATION) ────────

// Compute the expiry state of a client gallery: expires?/expired?/days left
function studio_photography_gallery_expiry_state($gallery_id) {
    $state = array('expires' => false, 'expired' => false, 'date' => '', 'days_left' => 0);

    $raw = get_post_meta(intval($gallery_id), '_gallery_expiry_date', true);
    if (empty($raw) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw) || !strtotime($raw)) {
        return $state;
    }

    $today = current_time('Y-m-d');
    $days_left = (int) floor((strtotime($raw) - strtotime($today)) / DAY_IN_SECONDS);

    $state['expires'] = true;
    $state['date'] = $raw;
    $state['days_left'] = $days_left;
    // The gallery stays open THROUGH the expiry date itself, then locks automatically
    $state['expired'] = ($days_left < 0);

    return $state;
}

// One-time email to the business when a gallery expires (fires on the first visit after expiry — no cron needed!)
function studio_photography_send_gallery_expired_email($gallery_id, $expiry_date) {
    $to = studio_photography_get_business_email();
    if (empty($to)) return;

    $site_name = get_bloginfo('name');
    $gallery_title = get_the_title($gallery_id);

    $booking_id = get_post_meta($gallery_id, '_gallery_booking_id', true);
    $client_name = !empty($booking_id) ? (get_post_meta($booking_id, '_booking_client_name', true) ?: $gallery_title) : $gallery_title;

    $subject = sprintf('[%s] ⏳ Client Gallery Expired: %s', $site_name, $gallery_title);
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $gallery_link = get_permalink($gallery_id);
    $edit_link = admin_url('post.php?post=' . intval($gallery_id) . '&action=edit');

    $body = "
    <html>
    <body style=\"font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f8fafc; padding: 30px; margin: 0; color: #1e293b;\">
        <div style=\"max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;\">
            <div style=\"background: #0f172a; padding: 24px; text-align: center; color: #ffffff;\">
                <h2 style=\"margin: 0; font-size: 20px; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase;\">{$site_name}</h2>
                <p style=\"margin: 5px 0 0 0; font-size: 14px; color: #94a3b8;\">Client Gallery Automatically Expired</p>
            </div>
            <div style=\"padding: 30px;\">
                <p style=\"margin-top: 0; font-size: 15px; line-height: 1.6; color: #334155;\">
                    The private gallery for <strong style=\"color: #0f172a;\">" . esc_html($client_name) . "</strong> reached its expiry date on <strong>" . esc_html(date('F j, Y', strtotime($expiry_date))) . "</strong> and has now locked automatically.
                </p>
                <div style=\"background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 16px 20px; margin-bottom: 25px; font-size: 13px; line-height: 1.7; color: #92400e;\">
                    <strong>What clients now see:</strong> an elegant \"This Gallery Has Expired\" screen with a contact button. All photo downloads and the full ZIP are disabled server-side.
                </div>
                <div style=\"text-align: center;\">
                    <a href=\"{$edit_link}\" target=\"_blank\" style=\"display: inline-block; background-color: #0f172a; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px;\">Open Gallery Settings (Extend / Change Expiry)</a>
                    <a href=\"{$gallery_link}\" target=\"_blank\" style=\"display: inline-block; background-color: #64748b; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px; margin-left: 10px;\">Preview as Admin</a>
                </div>
            </div>
            <div style=\"background: #f1f5f9; padding: 18px; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0;\">
                <p style=\"margin: 0;\">You still have full access as the studio owner — only clients are locked out.</p>
            </div>
        </div>
    </body>
    </html>
    ";

    wp_mail($to, $subject, $body, $headers);
}

// ── 10B. LIVE DOWNLOAD NOTIFICATIONS (SINGLE PHOTOS + FULL COLLECTION ZIP) ──

// ── 10B3. SPEED: width-sized Google-hosted image variants (lh3 resizes on the fly!) ──
// Grids/cards fetch small ~800px tiles instead of full-resolution photos (megabytes → kilobytes).
// Lightboxes keep the full-resolution URL for maximum quality.
function studio_photography_sized_image_url($url, $width = 800) {
    $url = (string) $url;
    if (strpos($url, 'googleusercontent.com/') === false) return $url; // only Google image CDN supports this
    if (preg_match('/=w\d+(-h\d+)*(-[a-z]+)*$/', $url)) return $url;  // already sized
    return $url . '=w' . intval($width);
}

// ── 10B4. UNIVERSAL MEDIA URL: paste any image URL — normal Google Drive share links
// (drive.google.com/file/d/…/view) are converted to direct-render CDN URLs automatically.
function studio_photography_media_url($url, $width = 0) {
    $url = studio_photography_get_google_drive_direct_url(trim((string) $url));
    if ($width > 0) $url = studio_photography_sized_image_url($url, $width);
    return $url;
}

// ── 10B5. Expand a whole Google Drive FOLDER link into its image list (cached, shared) ──
function studio_photography_get_drive_folder_images($folder_url, $cache_ttl = 21600) {
    $folder_id = '';
    if (preg_match('/\/folders\/([a-zA-Z0-9_-]+)/', (string) $folder_url, $m)) $folder_id = $m[1];
    elseif (preg_match('/id=([a-zA-Z0-9_-]+)/', (string) $folder_url, $m)) $folder_id = $m[1];
    if (empty($folder_id)) return array();

    $key = 'studio_folder_imgs_' . $folder_id;
    $files = get_transient($key);
    if ($files === false) {
        $fetched = studio_photography_fetch_gdrive_folder_files($folder_id);
        $files = $fetched['files'];
        if (!empty($files)) {
            set_transient($key, $files, $cache_ttl);
            update_option('studio_folder_imgs_backup_' . $folder_id, $files, false);
        } else {
            $backup = get_option('studio_folder_imgs_backup_' . $folder_id);
            $files = !empty($backup) ? $backup : array();
        }
    }
    return is_array($files) ? $files : array();
}

// ── 10C. PORTFOLIO GALLERY HELPERS (public sample work pulled from Google Drive) ──

// Get a portfolio gallery's photos from its Google Drive folder (cached 1 hour for public traffic)
function studio_photography_get_portfolio_photos($post_id, $bypass_cache = false) {
    $folder = get_post_meta(intval($post_id), '_portfolio_gdrive_folder', true);
    if (empty($folder) || strpos($folder, 'drive.google.com') === false) return array();

    $folder_id = '';
    if (preg_match('/\/folders\/([a-zA-Z0-9_-]+)/', $folder, $m)) $folder_id = $m[1];
    elseif (preg_match('/id=([a-zA-Z0-9_-]+)/', $folder, $m)) $folder_id = $m[1];
    if (empty($folder_id)) return array();

    $key = 'studio_portfolio_folder_' . $folder_id;
    $files = $bypass_cache ? false : get_transient($key);
    if ($files === false) {
        $fetched = studio_photography_fetch_gdrive_folder_files($folder_id);
        $files = $fetched['files'];
        if (!empty($files)) {
            // Portfolio folders rarely change — cache the listing for a full hour for fast public browsing
            set_transient($key, $files, HOUR_IN_SECONDS);
            update_option('studio_portfolio_backup_' . $folder_id, $files, false);
        } else {
            $backup = get_option('studio_portfolio_backup_' . $folder_id);
            $files = !empty($backup) ? $backup : array();
        }
    }
    return is_array($files) ? $files : array();
}

// Find the site's portfolio page URL (any page using the Portfolio template) for nav links
function studio_photography_get_portfolio_url() {
    $pages = get_pages(array(
        'meta_key' => '_wp_page_template',
        'meta_value' => 'template-portfolio.php',
        'number' => 1,
        'sort_column' => 'ID',
    ));
    if (!empty($pages)) return get_permalink($pages[0]->ID);
    return home_url('/#gallery');
}

// Quick lock check so every download path shares the same security gate
function studio_photography_gallery_is_locked($gallery_id) {
    $balance_amount = floatval(get_post_meta($gallery_id, '_gallery_balance_amount', true));
    $payment_status = get_post_meta($gallery_id, '_gallery_payment_status', true) ?: 'paid';

    $booking_id = get_post_meta($gallery_id, '_gallery_booking_id', true);
    if (!empty($booking_id)) {
        $total_amount = floatval(get_post_meta($booking_id, '_booking_amount_total', true));
        $deposit_paid = floatval(get_post_meta($booking_id, '_booking_amount_deposit', true));
        $balance_amount = $total_amount - $deposit_paid;
    }

    return ($balance_amount > 0 && $payment_status === 'unpaid');
}

// Instant live email alert to the business inbox
function studio_photography_send_download_alert_email($event, $photos_count = 1) {
    $to = studio_photography_get_business_email();
    if (empty($to)) return;

    $site_name = get_bloginfo('name');
    $is_zip = ($event['type'] === 'zip');
    $what = $is_zip
        ? __('downloaded the FULL COLLECTION ZIP', 'studio-photography')
        : sprintf(__('downloaded %d photo(s)', 'studio-photography'), $photos_count);

    $subject = sprintf('[%s] 📸 Download Alert: %s — %s', $site_name, $event['client_name'], $is_zip ? 'Full ZIP Collection' : 'Photo Downloads');
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $gallery_link = get_permalink($event['gallery_id']);

    $body = "
    <html>
    <body style=\"font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f8fafc; padding: 30px; margin: 0; color: #1e293b;\">
        <div style=\"max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;\">
            <div style=\"background: #0f172a; padding: 24px; text-align: center; color: #ffffff;\">
                <h2 style=\"margin: 0; font-size: 20px; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase;\">{$site_name}</h2>
                <p style=\"margin: 5px 0 0 0; font-size: 14px; color: #94a3b8;\">Live Gallery Download Notification</p>
            </div>
            <div style=\"padding: 30px;\">
                <p style=\"margin-top: 0; font-size: 15px; line-height: 1.6; color: #334155;\">
                    <strong style=\"color: #0f172a;\">" . esc_html($event['client_name']) . "</strong> {$what} from their private gallery <strong>\"" . esc_html($event['gallery_title']) . "\"</strong>.
                </p>
                <div style=\"background: #f1f5f9; border-radius: 8px; padding: 20px; margin-bottom: 25px;\">
                    <table style=\"width: 100%; font-size: 14px; line-height: 1.6;\">
                        <tr><td style=\"font-weight: bold; width: 35%; color: #64748b;\">Client:</td><td style=\"color: #0f172a;\">" . esc_html($event['client_name']) . "</td></tr>
                        <tr><td style=\"font-weight: bold; color: #64748b;\">Gallery:</td><td style=\"color: #0f172a;\">" . esc_html($event['gallery_title']) . "</td></tr>
                        <tr><td style=\"font-weight: bold; color: #64748b;\">Downloaded:</td><td style=\"color: #0f172a;\">" . esc_html($is_zip ? 'Full Collection (ZIP)' : $event['item']) . "</td></tr>
                        <tr><td style=\"font-weight: bold; color: #64748b;\">When:</td><td style=\"color: #0f172a;\">" . esc_html($event['time']) . "</td></tr>
                    </table>
                </div>
                <div style=\"text-align: center;\">
                    <a href=\"{$gallery_link}\" target=\"_blank\" style=\"display: inline-block; background-color: #0f172a; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px;\">Open Client Gallery</a>
                </div>
            </div>
            <div style=\"background: #f1f5f9; padding: 18px; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0;\">
                <p style=\"margin: 0;\">Real-time download tracking powered by {$site_name}.</p>
            </div>
        </div>
    </body>
    </html>
    ";

    wp_mail($to, $subject, $body, $headers);
}

// Central recorder: logs the event + fires the live email (throttled for singles)
function studio_photography_record_download_event($gallery_id, $type = 'single', $item_title = '', $defer_email = false) {
    $gallery_id = intval($gallery_id);
    $gallery = get_post($gallery_id);
    if (!$gallery || $gallery->post_type !== 'client_gallery') return;

    // Resolve the client identity the same way the gallery page does
    $booking_id = get_post_meta($gallery_id, '_gallery_booking_id', true);
    if (!empty($booking_id)) {
        $client_name = get_post_meta($booking_id, '_booking_client_name', true) ?: get_the_title($gallery_id);
    } else {
        $client_name = get_post_meta($gallery_id, '_gallery_client_email', true) ?: get_the_title($gallery_id);
    }

    $event = array(
        'id' => 0,
        'time' => current_time('mysql'),
        'gallery_id' => $gallery_id,
        'gallery_title' => get_the_title($gallery_id),
        'client_name' => $client_name,
        'type' => ($type === 'zip') ? 'zip' : 'single',
        'item' => sanitize_text_field($item_title),
    );

    // Sequential monotonic ID so the dashboard can count unseen events reliably
    $seq = (int) get_option('studio_dl_feed_seq', 0) + 1;
    update_option('studio_dl_feed_seq', $seq, false);
    $event['id'] = $seq;

    // Global live feed (newest first, capped at 100)
    $feed = get_option('studio_download_feed', array());
    array_unshift($feed, $event);
    update_option('studio_download_feed', array_slice($feed, 0, 100), false);

    // Per-gallery counters (visible proof of delivery on every gallery post)
    update_post_meta($gallery_id, '_gallery_download_count', intval(get_post_meta($gallery_id, '_gallery_download_count', true)) + 1);
    $recent = get_post_meta($gallery_id, '_gallery_download_events', true) ?: array();
    array_unshift($recent, array('time' => $event['time'], 'type' => $event['type'], 'item' => $event['item']));
    update_post_meta($gallery_id, '_gallery_download_events', array_slice($recent, 0, 30));

    // Live email alerts: ZIP = always instant. Singles = instant, then batched (max 1 email per gallery per 10 minutes)
    if ($type === 'zip') {
        if ($defer_email) return $event; // caller sends the email AFTER streaming — mail latency never delays a download
        studio_photography_send_download_alert_email($event, 1);
    } else {
        $state = get_option('studio_dl_mail_state_' . $gallery_id, array('last' => 0, 'pending' => 0));
        $pending = max(1, intval($state['pending']) + 1);
        if (time() - intval($state['last']) >= 10 * MINUTE_IN_SECONDS) {
            studio_photography_send_download_alert_email($event, $pending);
            update_option('studio_dl_mail_state_' . $gallery_id, array('last' => time(), 'pending' => 0), false);
        } else {
            update_option('studio_dl_mail_state_' . $gallery_id, array('last' => intval($state['last']), 'pending' => $pending), false);
        }
    }
}

// Secure single-photo download proxy: verifies unlock state, records the event, then streams the file
function studio_photography_handle_single_photo_download() {
    if (isset($_GET['studio_download_photo']) && $_GET['studio_download_photo'] == 1) {
        $gallery_id = isset($_GET['gallery_id']) ? intval($_GET['gallery_id']) : 0;
        $attachment_id = isset($_GET['attachment_id']) ? intval($_GET['attachment_id']) : 0;

        $gallery = get_post($gallery_id);
        if (!$gallery || $gallery->post_type !== 'client_gallery') {
            wp_die(__('The requested client gallery record does not exist.', 'studio-photography'));
        }
        if (studio_photography_gallery_is_locked($gallery_id) && !current_user_can('manage_options')) {
            studio_photography_redirect_download_refusal($gallery_id, 'balance');
        }

        // Gallery expiry guard (Pixieset-style automatic expiration!)
        $expiry_state = studio_photography_gallery_expiry_state($gallery_id);
        if ($expiry_state['expires'] && $expiry_state['expired'] && !current_user_can('manage_options')) {
            studio_photography_redirect_download_refusal($gallery_id, 'expired');
        }

        $attachment = get_post($attachment_id);
        if (!$attachment || $attachment->post_type !== 'attachment' || intval($attachment->post_parent) !== $gallery_id) {
            wp_die(__('Invalid photo request for this gallery.', 'studio-photography'));
        }

        $file_path = get_attached_file($attachment_id);
        if (!$file_path || !file_exists($file_path)) {
            wp_die(__('The requested photo file could not be found on the server.', 'studio-photography'));
        }

        // 📸 Record the live download event + fire notifications BEFORE streaming
        studio_photography_record_download_event($gallery_id, 'single', $attachment->post_title ?: basename($file_path));

        if (ob_get_level()) ob_end_clean();
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
        header('Content-Length: ' . filesize($file_path));
        header('Pragma: no-cache');
        header('Expires: 0');
        readfile($file_path);
        exit;
    }
}
add_action('template_redirect', 'studio_photography_handle_single_photo_download');

// AJAX beacon for external downloads (Google Drive / S3 / override ZIP links that bypass our server)
function studio_photography_ajax_track_download() {
    check_ajax_referer('studio_dl_track', 'nonce');

    $gallery_id = isset($_POST['gallery_id']) ? intval($_POST['gallery_id']) : 0;
    $gallery = get_post($gallery_id);
    if (!$gallery || $gallery->post_type !== 'client_gallery') {
        wp_send_json_error(__('Invalid gallery.', 'studio-photography'));
    }
    if (studio_photography_gallery_is_locked($gallery_id)) {
        wp_send_json_error(__('Gallery is locked.', 'studio-photography'));
    }

    // Gallery expiry guard (Pixieset-style automatic expiration!)
    $expiry_state = studio_photography_gallery_expiry_state($gallery_id);
    if ($expiry_state['expires'] && $expiry_state['expired'] && !current_user_can('manage_options')) {
        wp_send_json_error(__('Gallery expired.', 'studio-photography'));
    }

    $type = (isset($_POST['type']) && $_POST['type'] === 'zip') ? 'zip' : 'single';
    $item = isset($_POST['item']) ? sanitize_text_field($_POST['item']) : '';

    studio_photography_record_download_event($gallery_id, $type, $item);
    wp_send_json_success(__('Download recorded.', 'studio-photography'));
}
add_action('wp_ajax_studio_track_download', 'studio_photography_ajax_track_download');
add_action('wp_ajax_nopriv_studio_track_download', 'studio_photography_ajax_track_download');

// Pre-flight access check: the client asks BEFORE engaging a download so refusals arrive as
// friendly JSON (banner + toast) instead of a raw HTML error page saved as "download.html"
function studio_photography_ajax_check_download_access() {
    check_ajax_referer('studio_dl_check', 'nonce');

    $gallery_id = isset($_POST['gallery_id']) ? intval($_POST['gallery_id']) : 0;
    $type = (isset($_POST['type']) && $_POST['type'] === 'zip') ? 'zip' : 'single';

    $gallery = get_post($gallery_id);
    if (!$gallery || $gallery->post_type !== 'client_gallery') {
        wp_send_json_success(array('allowed' => false, 'reason' => 'invalid_gallery'));
    }

    $is_admin = current_user_can('manage_options');

    if (!$is_admin && studio_photography_gallery_is_locked($gallery_id)) {
        wp_send_json_success(array('allowed' => false, 'reason' => 'balance'));
    }

    $expiry_state = studio_photography_gallery_expiry_state($gallery_id);
    if (!$is_admin && $expiry_state['expires'] && $expiry_state['expired']) {
        wp_send_json_success(array('allowed' => false, 'reason' => 'expired'));
    }

    if ($type === 'zip') {
        // Can the server build this ZIP? (override URL, local uploads, OR Drive/cloud photos)
        $override = get_post_meta($gallery_id, '_gallery_zip_download', true);
        $has_local = !empty(studio_photography_get_downloadable_local_attachments($gallery_id));
        $has_remote_meta = !empty(get_post_meta($gallery_id, '_gallery_cloud_urls', true))
            || strpos((string) get_post_meta($gallery_id, '_gallery_gdrive_folder', true), 'drive.google.com') !== false;
        if (empty($override) && !$has_local && !$has_remote_meta) {
            wp_send_json_success(array('allowed' => false, 'reason' => 'no_local_photos'));
        }
    }

    wp_send_json_success(array('allowed' => true));
}
add_action('wp_ajax_studio_check_download_access', 'studio_photography_ajax_check_download_access');
add_action('wp_ajax_nopriv_studio_check_download_access', 'studio_photography_ajax_check_download_access');

// Dashboard live feed polling (admins only)
function studio_photography_ajax_poll_download_feed() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Unauthorized.', 'studio-photography'));
    }

    $feed = get_option('studio_download_feed', array());
    $last_seen = intval(get_user_meta(get_current_user_id(), 'studio_dl_feed_last_seen', true));

    $unread = 0;
    foreach ($feed as $evt) {
        if (intval($evt['id']) > $last_seen) $unread++;
    }

    $events = array();
    foreach (array_slice($feed, 0, 25) as $evt) {
        $events[] = array(
            'id' => intval($evt['id']),
            'ts' => strtotime($evt['time']),
            'client' => $evt['client_name'],
            'gallery' => $evt['gallery_title'],
            'type' => $evt['type'],
            'item' => $evt['item'],
            'seen' => intval($evt['id']) <= $last_seen,
        );
    }

    wp_send_json_success(array('events' => $events, 'unread' => $unread));
}
add_action('wp_ajax_studio_poll_download_feed', 'studio_photography_ajax_poll_download_feed');

function studio_photography_ajax_mark_downloads_seen() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Unauthorized.', 'studio-photography'));
    }
    update_user_meta(get_current_user_id(), 'studio_dl_feed_last_seen', (int) get_option('studio_dl_feed_seq', 0));
    wp_send_json_success(__('Marked as read.', 'studio-photography'));
}
add_action('wp_ajax_studio_mark_downloads_seen', 'studio_photography_ajax_mark_downloads_seen');


// ── WSOD SELF-DIAGNOSTICS: if a PHP fatal error ever occurs, show the real error on screen ──
// instead of a silent white screen, so problems can be pinpointed and fixed instantly.
register_shutdown_function(function() {
    $e = error_get_last();
    if ($e && in_array($e['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR), true)) {
        $msg = $e['message'] . ' — ' . $e['file'] . ' on line ' . $e['line'];
        error_log('[Studio Theme Fatal] ' . $msg);

        // Clear half-sent output so the diagnostic box is readable
        while (ob_get_level()) { @ob_end_clean(); }
        http_response_code(500);
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Studio Theme Diagnostic</title></head><body style="background:#f8fafc;font-family:ui-monospace,Monaco,Consolas,monospace;padding:24px;">';
        echo '<div style="background:#fef2f2;border:2px solid #dc2626;color:#7f1d1d;font-size:13px;padding:20px;margin:0 auto;border-radius:10px;max-width:900px;line-height:1.7;">';
        echo '<strong style="font-size:15px;">⚠️ Studio Theme caught a PHP error</strong><br><br>';
        echo '<code style="color:#b91c1c;">' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</code><br><br>';
        echo 'Send a screenshot of this red box to your developer — it tells them exactly what to fix. Your site data is safe.';
        echo '</div></body></html>';
    }
});


// ── 11B. SITE-WIDE SCREENSHOT & CONTENT PROTECTION ENGINE ───────────────────
// Cloaks all images the instant a screenshot action is detected (PrintScreen, Win+Shift+S,
// Cmd+Shift+3/4/5), wipes the clipboard, blocks right-click/drag/save/print, and blurs
// content whenever the window loses focus (Alt-Tab to a screenshot tool).
// NOTE: OS-level capture (phone photo of the screen, some snipping tools) is beyond any
// website's reach — this is the maximum protection a browser can offer.
function studio_photography_site_protection_css() {
    if (is_admin() || current_user_can('manage_options')) return;
    ?>
    <style>
    img { -webkit-user-drag: none; -webkit-touch-callout: none; user-select: none; -webkit-user-select: none; }
    body.studio-cloak img { filter: blur(22px) grayscale(1) brightness(0.2) !important; transition: filter 0.05s linear; }
    </style>
    <?php
}
add_action('wp_head', 'studio_photography_site_protection_css');

function studio_photography_site_protection_js() {
    if (is_admin() || current_user_can('manage_options')) return;
    ?>
    <script>
    (function() {
      try {
        let cloakTimer = null;

        function cloak(ms) {
            document.body.classList.add('studio-cloak');
            clearTimeout(cloakTimer);
            cloakTimer = setTimeout(function() { document.body.classList.remove('studio-cloak'); }, ms || 1500);
        }
        function uncloak() {
            clearTimeout(cloakTimer);
            document.body.classList.remove('studio-cloak');
        }
        function toast(msg) {
            if (window.studioToast) { window.studioToast(msg); return; } // reuse the gallery toast when present
            let t = document.getElementById('studio-site-toast');
            if (!t) {
                t = document.createElement('div');
                t.id = 'studio-site-toast';
                t.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%) translateY(20px);background:#0f172a;color:#fff;padding:12px 20px;border-radius:9999px;font-size:13px;font-weight:600;z-index:99999;opacity:0;transition:all .3s;pointer-events:none;box-shadow:0 10px 25px rgba(0,0,0,.4);max-width:90vw;text-align:center;';
                document.body.appendChild(t);
            }
            t.textContent = msg;
            t.style.opacity = '1';
            t.style.transform = 'translateX(-50%) translateY(0)';
            clearTimeout(t._h);
            t._h = setTimeout(function() {
                t.style.opacity = '0';
                t.style.transform = 'translateX(-50%) translateY(20px)';
            }, 2600);
        }
        function wipeClipboard() {
            try { if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText('').catch(function() {}); } catch (err) {}
        }

        // Block right-click / long-press context menus on photos
        document.addEventListener('contextmenu', function(e) {
            if (e.target && e.target.tagName === 'IMG') {
                e.preventDefault();
                toast('🔒 Right-click is disabled on photos');
            }
        });

        // Block dragging images out of the page
        document.addEventListener('dragstart', function(e) {
            if (e.target && e.target.tagName === 'IMG') e.preventDefault();
        });

        // Keyboard interception: save, print, and every common screenshot hotkey
        document.addEventListener('keydown', function(e) {
            const k = (e.key || '').toLowerCase();

            // PrintScreen pressed → cloak BEFORE the capture lands, then wipe the clipboard
            if (e.key === 'PrintScreen') {
                cloak(1800);
                wipeClipboard();
                toast('📸 Screenshots are disabled on this site');
                document.dispatchEvent(new CustomEvent('studio:screenshot-attempt'));
                return;
            }

            // Win+Shift+S (Snipping), Cmd/Ctrl+Shift+3/4/5 (macOS screenshots)
            if ((e.metaKey || e.ctrlKey) && e.shiftKey && ['s', '3', '4', '5'].indexOf(k) !== -1) {
                cloak(2500);
                wipeClipboard();
                toast('📸 Screenshots are disabled on this site');
                e.preventDefault();
                document.dispatchEvent(new CustomEvent('studio:screenshot-attempt'));
                return;
            }

            // Ctrl/Cmd+S (save) and Ctrl/Cmd+P (print)
            if ((e.metaKey || e.ctrlKey) && (k === 's' || k === 'p')) {
                e.preventDefault();
                toast(k === 'p' ? '🔒 Printing is disabled on this site' : '🔒 Saving is disabled on this site');
            }
        });

        // PrintScreen released → wipe the clipboard again (captures land on keyup)
        document.addEventListener('keyup', function(e) {
            if (e.key === 'PrintScreen') {
                wipeClipboard();
                cloak(600);
            }
        });

        // Blur content whenever the window loses focus or the tab is hidden
        // (Alt-Tab to a screenshot tool, switching apps, second monitor workflows)
        window.addEventListener('blur', function() { cloak(1400); });
        document.addEventListener('visibilitychange', function() { if (document.hidden) cloak(100); });
        window.addEventListener('focus', uncloak);
      } catch (err) {
        if (window.console && console.error) console.error('[Studio Protection] skipped:', err);
      }
    })();
    </script>
    <?php
}
add_action('wp_footer', 'studio_photography_site_protection_js');


// ── 12. PREMIUM WORDPRESS CUSTOMIZER FOR VISUAL PAGE EDITING ─────────────────

function studio_photography_customizer_settings($wp_customize) {
    
    // ── LOGO POSITIONING RANGE SLIDER (SITE IDENTITY) ──
    $wp_customize->add_setting('studio_logo_margin_left', array(
        'default' => 0,
        'sanitize_callback' => 'intval',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('studio_logo_margin_left', array(
        'label' => __('Move Logo with Mouse (Left / Right Slider)', 'studio-photography'),
        'description' => __('Drag the slider left or right with your mouse to visually position your logo perfectly with the site content in real-time.', 'studio-photography'),
        'section' => 'title_tagline',
        'type' => 'range',
        'input_attrs' => array(
            'min' => -50,
            'max' => 150,
            'step' => 1,
        ),
    ));

    // ── LOGO RESIZE SLIDER (SITE IDENTITY) ──
    $wp_customize->add_setting('studio_logo_height', array(
        'default' => 40,
        'sanitize_callback' => 'intval',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('studio_logo_height', array(
        'label' => __('Logo Size (Height Slider)', 'studio-photography'),
        'description' => __('Drag to resize your navbar logo in real-time. The width adjusts automatically to keep your logo perfectly proportioned.', 'studio-photography'),
        'section' => 'title_tagline',
        'type' => 'range',
        'input_attrs' => array(
            'min' => 24,
            'max' => 120,
            'step' => 1,
        ),
    ));

    // ── SECTION 1: HERO SECTION CUSTOMIZER ──────────────────
    $wp_customize->add_section('studio_hero_section', array(
        'title' => __('Hero Section', 'studio-photography'),
        'description' => __('Customize the homepage Hero header text, buttons, and graphics visually.', 'studio-photography'),
        'priority' => 20,
    ));

    $wp_customize->add_setting('studio_hero_pre', array(
        'default' => 'Capturing Moments, Creating Memories',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('studio_hero_pre', array(
        'label' => __('Hero Small Tagline', 'studio-photography'),
        'section' => 'studio_hero_section',
        'type' => 'text',
    ));

    $wp_customize->add_setting('studio_hero_title', array(
        'default' => 'Timeless Photography for Your Most Important Days',
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    $wp_customize->add_control('studio_hero_title', array(
        'label' => __('Hero Main Title (supports html)', 'studio-photography'),
        'section' => 'studio_hero_section',
        'type' => 'textarea',
    ));

    $wp_customize->add_setting('studio_hero_desc', array(
        'default' => "We specialize in capturing the essence of life's most precious moments with elegance, artistry, and attention to detail. From intimate portraits to grand celebrations, every frame tells your story.",
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    $wp_customize->add_control('studio_hero_desc', array(
        'label' => __('Hero Subtext', 'studio-photography'),
        'section' => 'studio_hero_section',
        'type' => 'textarea',
    ));

    $wp_customize->add_setting('studio_hero_btn1_text', array(
        'default' => 'Book a Session',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('studio_hero_btn1_text', array(
        'label' => __('Button 1 Text', 'studio-photography'),
        'section' => 'studio_hero_section',
        'type' => 'text',
    ));

    $wp_customize->add_setting('studio_hero_btn2_text', array(
        'default' => 'View My Work',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('studio_hero_btn2_text', array(
        'label' => __('Button 2 Text', 'studio-photography'),
        'section' => 'studio_hero_section',
        'type' => 'text',
    ));

    $wp_customize->add_setting('studio_hero_btn2_link', array(
        'default' => '#gallery',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control('studio_hero_btn2_link', array(
        'label' => __('Button 2 Link (e.g., #gallery or your Instagram URL)', 'studio-photography'),
        'section' => 'studio_hero_section',
        'type' => 'text',
    ));

    $wp_customize->add_setting('studio_hero_image', array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'studio_hero_image', array(
        'label' => __('Hero Portrait Graphics', 'studio-photography'),
        'section' => 'studio_hero_section',
    )));


    // ── SECTION 3: ABOUT SECTION CUSTOMIZER ──────────────────
    $wp_customize->add_section('studio_about_section', array(
        'title' => __('About Section Settings', 'studio-photography'),
        'description' => __('Customize the "About the Studio" section shown on your homepage live!', 'studio-photography'),
        'priority' => 30,
    ));

    $wp_customize->add_setting('studio_about_title', array(
        'default' => 'Where Art Meets Authenticity',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('studio_about_title', array(
        'label' => __('About Title', 'studio-photography'),
        'section' => 'studio_about_section',
        'type' => 'text',
    ));

    $wp_customize->add_setting('studio_about_description', array(
        'default' => "With over 15 years of experience in professional photography, we've dedicated ourselves to capturing the authentic moments that matter most. Our approach combines technical expertise with artistic direction to create images that transcend time.\n\nEvery project is treated as a unique collaboration. We listen to your vision, understand your story, and deliver photographs that exceed expectations.",
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    $wp_customize->add_control('studio_about_description', array(
        'label' => __('About Description Paragraph', 'studio-photography'),
        'section' => 'studio_about_section',
        'type' => 'textarea',
    ));

    $wp_customize->add_setting('studio_about_image', array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'studio_about_image', array(
        'label' => __('About Side Image', 'studio-photography'),
        'section' => 'studio_about_section',
    )));

    $wp_customize->add_setting('studio_about_points', array(
        'default' => "Award-winning photography team\nState-of-the-art equipment & editing\nFast turnaround with online gallery\nPersonalized consultation for every client",
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    $wp_customize->add_control('studio_about_points', array(
        'label' => __('About Bullet Points (one item per line)', 'studio-photography'),
        'section' => 'studio_about_section',
        'type' => 'textarea',
    ));


    // ── SECTION 4: BOOKING PAGE CUSTOMIZER ──────────────────
    $wp_customize->add_section('studio_booking_section', array(
        'title' => __('Booking Page Customizer', 'studio-photography'),
        'description' => __('Customize every single heading, step label, and success text on your checkout booking page visually.', 'studio-photography'),
        'priority' => 35,
    ));

    // Booking Main Title
    $wp_customize->add_setting('studio_booking_title', array('default' => 'Book Your Session', 'sanitize_callback' => 'sanitize_text_field'));
    $wp_customize->add_control('studio_booking_title', array('label' => __('Booking Main Heading', 'studio-photography'), 'section' => 'studio_booking_section', 'type' => 'text'));

    // Booking Subtitle
    $wp_customize->add_setting('studio_booking_subtitle', array('default' => 'Choose your photography session type and select a package', 'sanitize_callback' => 'sanitize_text_field'));
    $wp_customize->add_control('studio_booking_subtitle', array('label' => __('Booking Sub-Heading', 'studio-photography'), 'section' => 'studio_booking_section', 'type' => 'text'));

    // Step 1 Title
    $wp_customize->add_setting('studio_booking_step1', array('default' => 'Select Session Type', 'sanitize_callback' => 'sanitize_text_field'));
    $wp_customize->add_control('studio_booking_step1', array('label' => __('Step 1 Heading', 'studio-photography'), 'section' => 'studio_booking_section', 'type' => 'text'));

    // Step 2 Title
    $wp_customize->add_setting('studio_booking_step2', array('default' => 'Select Package', 'sanitize_callback' => 'sanitize_text_field'));
    $wp_customize->add_control('studio_booking_step2', array('label' => __('Step 2 Heading', 'studio-photography'), 'section' => 'studio_booking_section', 'type' => 'text'));

    // Step 3 Title
    $wp_customize->add_setting('studio_booking_step3', array('default' => 'Your Booking Details', 'sanitize_callback' => 'sanitize_text_field'));
    $wp_customize->add_control('studio_booking_step3', array('label' => __('Step 3 Heading', 'studio-photography'), 'section' => 'studio_booking_section', 'type' => 'text'));

    // Sidebar Summary Header
    $wp_customize->add_setting('studio_booking_sidebar_header', array('default' => 'Booking Summary', 'sanitize_callback' => 'sanitize_text_field'));
    $wp_customize->add_control('studio_booking_sidebar_header', array('label' => __('Sidebar Title', 'studio-photography'), 'section' => 'studio_booking_section', 'type' => 'text'));

    // Sidebar Empty Description
    $wp_customize->add_setting('studio_booking_sidebar_empty', array('default' => 'Select a session and package to see your booking summary', 'sanitize_callback' => 'sanitize_textarea_field'));
    $wp_customize->add_control('studio_booking_sidebar_empty', array('label' => __('Sidebar Empty Placeholder', 'studio-photography'), 'section' => 'studio_booking_section', 'type' => 'textarea'));

    // Success Modal Title
    $wp_customize->add_setting('studio_booking_success_title', array('default' => 'Booking Confirmed!', 'sanitize_callback' => 'sanitize_text_field'));
    $wp_customize->add_control('studio_booking_success_title', array('label' => __('Success Popup Heading', 'studio-photography'), 'section' => 'studio_booking_section', 'type' => 'text'));

    // Success Modal Message
    $wp_customize->add_setting('studio_booking_success_msg', array('default' => 'Thank you! Your photography booking has been registered successfully. A team member will reach out to you shortly to finalize.', 'sanitize_callback' => 'sanitize_textarea_field'));
    $wp_customize->add_control('studio_booking_success_msg', array('label' => __('Success Popup Message', 'studio-photography'), 'section' => 'studio_booking_section', 'type' => 'textarea'));

    // WhatsApp Business Phone Number Setting
    $wp_customize->add_setting('studio_whatsapp_phone', array('default' => '233241234567', 'sanitize_callback' => 'sanitize_text_field'));
    $wp_customize->add_control('studio_whatsapp_phone', array('label' => __('WhatsApp Business Phone (e.g. 233241234567, no + or spaces)', 'studio-photography'), 'section' => 'studio_booking_section', 'type' => 'text'));

    // Business Email Setting (whytecobby@gmail.com override)
    $wp_customize->add_setting('studio_business_email', array('default' => 'whytecobby@gmail.com', 'sanitize_callback' => 'sanitize_email'));
    $wp_customize->add_control('studio_business_email', array('label' => __('Business Email Address', 'studio-photography'), 'section' => 'studio_booking_section', 'type' => 'text'));
    $wp_customize->add_setting('studio_business_location', array('default' => 'Ghana', 'sanitize_callback' => 'sanitize_text_field'));
    $wp_customize->add_control('studio_business_location', array('label' => __('Business Location (shown on invoices, e.g. Kumasi, Ghana)', 'studio-photography'), 'section' => 'studio_booking_section', 'type' => 'text'));


    // ── SECTION 5: CALL TO ACTION (CTA) CUSTOMIZER ──────────────────
    $wp_customize->add_section('studio_cta_section', array(
        'title' => __('Call to Action (CTA) Section', 'studio-photography'),
        'description' => __('Change the footer invitation section banner text and graphic links.', 'studio-photography'),
        'priority' => 40,
    ));

    $wp_customize->add_setting('studio_cta_pre', array('default' => 'Ready to Capture Your Story?', 'sanitize_callback' => 'sanitize_text_field'));
    $wp_customize->add_control('studio_cta_pre', array('label' => __('CTA Small Tagline', 'studio-photography'), 'section' => 'studio_cta_section', 'type' => 'text'));

    $wp_customize->add_setting('studio_cta_title', array('default' => "Let's Create Something Beautiful", 'sanitize_callback' => 'sanitize_text_field'));
    $wp_customize->add_control('studio_cta_title', array('label' => __('CTA Main Title', 'studio-photography'), 'section' => 'studio_cta_section', 'type' => 'text'));

    $wp_customize->add_setting('studio_cta_desc', array('default' => 'Book a consultation with our team today and let us bring your vision to life.', 'sanitize_callback' => 'sanitize_textarea_field'));
    $wp_customize->add_control('studio_cta_desc', array('label' => __('CTA Description Text', 'studio-photography'), 'section' => 'studio_cta_section', 'type' => 'textarea'));

    $wp_customize->add_setting('studio_cta_btn1_text', array('default' => 'Book a Session', 'sanitize_callback' => 'sanitize_text_field'));
    $wp_customize->add_control('studio_cta_btn1_text', array('label' => __('CTA Button 1 Label', 'studio-photography'), 'section' => 'studio_cta_section', 'type' => 'text'));

    $wp_customize->add_setting('studio_cta_btn2_text', array('default' => 'View Portfolio', 'sanitize_callback' => 'sanitize_text_field'));
    $wp_customize->add_control('studio_cta_btn2_text', array('label' => __('CTA Button 2 Label', 'studio-photography'), 'section' => 'studio_cta_section', 'type' => 'text'));

    $wp_customize->add_setting('studio_cta_btn2_link', array('default' => '#gallery', 'sanitize_callback' => 'esc_url_raw'));
    $wp_customize->add_control('studio_cta_btn2_link', array('label' => __('CTA Button 2 Link (e.g., #gallery or your Instagram URL)', 'studio-photography'), 'section' => 'studio_cta_section', 'type' => 'text'));


    // ── SECTION 6: FOOTER BRANING & TEXT CUSTOMIZER ──────────────────
    $wp_customize->add_section('studio_footer_section', array(
        'title' => __('Footer Settings', 'studio-photography'),
        'description' => __('Customize the footer brand description, columns headers, and copyright notice visually.', 'studio-photography'),
        'priority' => 50,
    ));

    $wp_customize->add_setting('studio_footer_brand_desc', array(
        'default' => "Capturing life's most precious moments with elegance, artistry, and attention to detail.",
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    $wp_customize->add_control('studio_footer_brand_desc', array(
        'label' => __('Footer Brand Tagline', 'studio-photography'),
        'section' => 'studio_footer_section',
        'type' => 'textarea',
    ));

    $wp_customize->add_setting('studio_footer_col1_title', array('default' => 'Navigate', 'sanitize_callback' => 'sanitize_text_field'));
    $wp_customize->add_control('studio_footer_col1_title', array('label' => __('Column 1 Title', 'studio-photography'), 'section' => 'studio_footer_section', 'type' => 'text'));

    $wp_customize->add_setting('studio_footer_col2_title', array('default' => 'Services', 'sanitize_callback' => 'sanitize_text_field'));
    $wp_customize->add_control('studio_footer_col2_title', array('label' => __('Column 2 Title', 'studio-photography'), 'section' => 'studio_footer_section', 'type' => 'text'));

    $wp_customize->add_setting('studio_footer_copyright', array('default' => 'All rights reserved.', 'sanitize_callback' => 'sanitize_text_field'));
    $wp_customize->add_control('studio_footer_copyright', array('label' => __('Copyright Notice Text', 'studio-photography'), 'section' => 'studio_footer_section', 'type' => 'text'));
}
add_action('customize_register', 'studio_photography_customizer_settings');


// ── 12. BULLETPROOF BOOKING PAGE URL FINDER HELPER ───────────────────────────

function studio_photography_get_booking_url() {
    $pages = get_pages(array(
        'meta_key' => '_wp_page_template',
        'meta_value' => 'template-booking.php',
        'number' => 1
    ));
    if (!empty($pages)) {
        return get_permalink($pages[0]->ID);
    }
    
    $book_page = get_page_by_path('book');
    if ($book_page) {
        return get_permalink($book_page->ID);
    }
    
    $book_page2 = get_page_by_path('book-a-session');
    if ($book_page2) {
        return get_permalink($book_page2->ID);
    }

    return home_url('/book');
}


// ── 13. THEME SWITCH / ACTIVATION SEED HANDLER ───────────────────────────────

function studio_photography_theme_activation() {
    $book_page = get_page_by_path('book');
    if (!$book_page) {
        $page_id = wp_insert_post(array(
            'post_title' => 'Book a Session',
            'post_name' => 'book',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_content' => '',
        ));
        if ($page_id) {
            update_post_meta($page_id, '_wp_page_template', 'template-booking.php');
        }
    } else {
        update_post_meta($book_page->ID, '_wp_page_template', 'template-booking.php');
    }

    // Auto-create Studio Dashboard page on theme activation!
    $dashboard_page = get_page_by_path('dashboard');
    if (!$dashboard_page) {
        $dash_id = wp_insert_post(array(
            'post_title' => 'Studio Dashboard',
            'post_name' => 'dashboard',
            'post_status' => 'publish', // publish (Public) is required so our custom login page can load for logged-out users!
            'post_type' => 'page',
            'post_content' => '',
        ));
        if ($dash_id) {
            update_post_meta($dash_id, '_wp_page_template', 'template-dashboard.php');
        }
    } else {
        update_post_meta($dashboard_page->ID, '_wp_page_template', 'template-dashboard.php');
        
        // Ensure existing page is updated to publish status so the custom login template works!
        wp_update_post(array(
            'ID' => $dashboard_page->ID,
            'post_status' => 'publish'
        ));
    }

    $existing_galleries = get_posts(array('post_type' => 'client_gallery', 'numberposts' => 1));
    if (empty($existing_galleries)) {
        $gallery_id = wp_insert_post(array(
            'post_title' => 'Sarah & James - Private Wedding Preview',
            'post_content' => 'Welcome to your private wedding gallery! Inside you will find selected previews from your beautiful ceremony. Use the button on the top right to download the entire set of high-resolution edits.',
            'post_type' => 'client_gallery',
            'post_status' => 'publish',
            'post_password' => '123456',
        ));
    }

    flush_rewrite_rules();

    // Seed native categories first!
    $seeded_categories = array(
        'Portrait' => 'portrait',
        'Wedding' => 'wedding',
        'Graduation' => 'graduation',
        'Family' => 'family',
        'Corporate' => 'corporate'
    );
    
    $term_map = array();
    foreach ($seeded_categories as $name => $slug) {
        $term = wp_insert_term($name, 'session_category', array('slug' => $slug));
        if (!is_wp_error($term) && isset($term['term_id'])) {
            $term_map[$slug] = $term['term_id'];
        } else {
            $existing_term = get_term_by('slug', $slug, 'session_category');
            if ($existing_term) {
                $term_map[$slug] = $existing_term->term_id;
            }
        }
    }

    $existing_sessions = get_posts(array(
        'post_type' => 'session',
        'numberposts' => 1,
    ));

    if (empty($existing_sessions)) {
        $sessions_to_seed = array(
            'outdoor-portrait' => array(
                'title' => 'Outdoor Portrait',
                'description' => 'Beautiful outdoor portrait sessions in natural light settings. Perfect for individuals, couples, or small groups.',
                'category_slug' => 'portrait',
                'featured' => 'yes',
                'display_order' => 1,
                'packages' => array(
                    array(
                        'name' => 'Basic',
                        'desc' => 'Perfect for a quick outdoor shoot with natural light.',
                        'price' => 300,
                        'duration' => '1 hour',
                        'max_people' => 1,
                        'edited_photos' => 10,
                        'outfit_changes' => 1,
                        'locations' => 1,
                        'delivery_time' => '5 Days',
                        'deposit' => 50,
                        'order' => 1,
                        'gallery' => 'yes',
                        'features' => array('1 Outfit', '1 Location', '10 Edited Images', 'High Resolution Delivery', 'Online Gallery')
                    ),
                    array(
                        'name' => 'Standard',
                        'desc' => 'Extended outdoor session with more variety.',
                        'price' => 500,
                        'duration' => '2 hours',
                        'max_people' => 2,
                        'edited_photos' => 25,
                        'outfit_changes' => 2,
                        'locations' => 2,
                        'delivery_time' => '5 Days',
                        'deposit' => 50,
                        'order' => 2,
                        'gallery' => 'yes',
                        'printing' => 'yes',
                        'features' => array('2 Outfits', '2 Locations', '25 Edited Images', 'High Resolution', 'Online Gallery', '5 Printed Photos')
                    ),
                    array(
                        'name' => 'Premium',
                        'desc' => 'The ultimate outdoor photography experience.',
                        'price' => 800,
                        'duration' => '3 hours',
                        'max_people' => 4,
                        'edited_photos' => 50,
                        'outfit_changes' => 3,
                        'locations' => 3,
                        'delivery_time' => '3 Days',
                        'deposit' => 50,
                        'order' => 3,
                        'gallery' => 'yes',
                        'raw' => 'yes',
                        'printing' => 'yes',
                        'transport' => 'yes',
                        'drone' => 'yes',
                        'priority' => 'yes',
                        'features' => array('3 Outfits', '3 Locations', '50 Edited Images', 'All Raw Images', 'Drone Coverage', 'Priority Editing', 'Transportation')
                    )
                )
            ),
            'studio-portrait' => array(
                'title' => 'Studio Portrait',
                'description' => 'Professional studio portrait sessions with controlled lighting and backdrops. Ideal for headshots and formal portraits.',
                'category_slug' => 'portrait',
                'featured' => 'yes',
                'display_order' => 2,
                'packages' => array(
                    array(
                        'name' => 'Basic',
                        'desc' => 'Simple studio session with one backdrop.',
                        'price' => 250,
                        'duration' => '1 hour',
                        'max_people' => 1,
                        'edited_photos' => 8,
                        'outfit_changes' => 1,
                        'delivery_time' => '5 Days',
                        'deposit' => 50,
                        'order' => 1,
                        'gallery' => 'yes',
                        'features' => array('1 Outfit', '1 Backdrop', '8 Edited Images', 'High Resolution', 'Online Gallery')
                    ),
                    array(
                        'name' => 'Premium',
                        'desc' => 'Full studio experience with professional styling.',
                        'price' => 600,
                        'duration' => '2 hours',
                        'max_people' => 2,
                        'edited_photos' => 30,
                        'outfit_changes' => 3,
                        'delivery_time' => '3 Days',
                        'deposit' => 50,
                        'order' => 2,
                        'gallery' => 'yes',
                        'raw' => 'yes',
                        'printing' => 'yes',
                        'priority' => 'yes',
                        'features' => array('3 Outfits', 'Unlimited Backdrops', '30 Edited Images', 'All Raw Images', '10 Printed Photos', 'Priority Editing')
                    )
                )
            ),
            'wedding' => array(
                'title' => 'Wedding Coverage',
                'description' => 'Comprehensive wedding photography coverage from preparation to reception. Capture every magical moment.',
                'category_slug' => 'wedding',
                'featured' => 'yes',
                'display_order' => 3,
                'packages' => array(
                    array(
                        'name' => 'Silver',
                        'desc' => 'Essential wedding coverage for intimate ceremonies.',
                        'price' => 2000,
                        'duration' => 'Half Day',
                        'max_people' => 50,
                        'edited_photos' => 100,
                        'delivery_time' => '14 Days',
                        'deposit' => 50,
                        'order' => 1,
                        'gallery' => 'yes',
                        'features' => array('Half Day Coverage', '1 Photographer', '100 Edited Images', 'Online Gallery', 'Ceremony & Reception')
                    ),
                    array(
                        'name' => 'Gold',
                        'desc' => 'Comprehensive wedding photography.',
                        'price' => 3500,
                        'duration' => 'Full Day',
                        'max_people' => 150,
                        'edited_photos' => 250,
                        'delivery_time' => '14 Days',
                        'deposit' => 50,
                        'order' => 2,
                        'gallery' => 'yes',
                        'printing' => 'yes',
                        'transport' => 'yes',
                        'features' => array('Full Day Coverage', '2 Photographers', '250 Edited Images', 'Photo Album', 'Transportation', 'Pre-Wedding Shoot')
                    ),
                    array(
                        'name' => 'Platinum',
                        'desc' => 'The ultimate wedding photography package.',
                        'price' => 5500,
                        'duration' => 'Full Day',
                        'max_people' => 300,
                        'edited_photos' => 500,
                        'delivery_time' => '7 Days',
                        'deposit' => 40,
                        'order' => 3,
                        'gallery' => 'yes',
                        'raw' => 'yes',
                        'printing' => 'yes',
                        'transport' => 'yes',
                        'drone' => 'yes',
                        'priority' => 'yes',
                        'features' => array('Full Day Coverage', '3 Photographers', '500+ Edited Images', 'All Raw Images', 'Premium Album', 'Drone Coverage', 'Same-Day Highlights')
                    )
                )
            ),
            'graduation' => array(
                'title' => 'Graduation Portrait',
                'description' => 'Celebrate your academic achievement with professional graduation photos.',
                'category_slug' => 'graduation',
                'featured' => 'no',
                'display_order' => 4,
                'packages' => array(
                    array(
                        'name' => 'Basic',
                        'desc' => 'Quick graduation photo session.',
                        'price' => 200,
                        'duration' => '1 hour',
                        'max_people' => 1,
                        'edited_photos' => 10,
                        'delivery_time' => '3 Days',
                        'deposit' => 50,
                        'order' => 1,
                        'gallery' => 'yes',
                        'features' => array('1 Outfit', '10 Edited Images', 'Cap & Gown Photos', 'Online Gallery')
                    ),
                    array(
                        'name' => 'Premium',
                        'desc' => 'Complete graduation photography package.',
                        'price' => 400,
                        'duration' => '2 hours',
                        'max_people' => 5,
                        'edited_photos' => 30,
                        'outfit_changes' => 2,
                        'locations' => 2,
                        'delivery_time' => '3 Days',
                        'deposit' => 50,
                        'order' => 2,
                        'gallery' => 'yes',
                        'printing' => 'yes',
                        'priority' => 'yes',
                        'features' => array('2 Outfits', '2 Locations', '30 Edited Images', 'Family Group Photos', '5 Printed Photos')
                    )
                )
            )
        );

        foreach ($sessions_to_seed as $slug => $sdata) {
            $sess_id = wp_insert_post(array(
                'post_title' => $sdata['title'],
                'post_name' => $slug,
                'post_content' => $sdata['description'],
                'post_type' => 'session',
                'post_status' => 'publish',
            ));

            if ($sess_id && !is_wp_error($sess_id)) {
                // Assign taxonomy category terms dynamically
                if (isset($term_map[$sdata['category_slug']])) {
                    wp_set_post_terms($sess_id, array($term_map[$sdata['category_slug']]), 'session_category');
                }

                update_post_meta($sess_id, '_session_featured', $sdata['featured']);
                update_post_meta($sess_id, '_session_display_order', $sdata['display_order']);
                update_post_meta($sess_id, '_session_active', 'yes');

                $fallback_image = get_template_directory_uri() . '/assets/images/hero-portrait.webp';
                update_post_meta($sess_id, '_session_image', $fallback_image);

                foreach ($sdata['packages'] as $pdata) {
                    $pkg_id = wp_insert_post(array(
                        'post_title' => $pdata['name'],
                        'post_excerpt' => $pdata['desc'],
                        'post_type' => 'package',
                        'post_status' => 'publish',
                    ));

                    if ($pkg_id && !is_wp_error($pkg_id)) {
                        update_post_meta($pkg_id, '_package_session_id', $sess_id);
                        update_post_meta($pkg_id, '_package_price', floatval($pdata['price']));
                        update_post_meta($pkg_id, '_package_duration', $pdata['duration']);
                        update_post_meta($pkg_id, '_package_max_people', intval($pdata['max_people']));
                        update_post_meta($pkg_id, '_package_edited_photos', intval($pdata['edited_photos']));
                        update_post_meta($pkg_id, '_package_outfit_changes', isset($pdata['outfit_changes']) ? intval($pdata['outfit_changes']) : 1);
                        update_post_meta($pkg_id, '_package_locations', isset($pdata['locations']) ? intval($pdata['locations']) : 1);
                        update_post_meta($pkg_id, '_package_delivery_time', $pdata['delivery_time']);
                        update_post_meta($pkg_id, '_package_deposit_percentage', intval($pdata['deposit']));
                        update_post_meta($pkg_id, '_package_display_order', intval($pdata['order']));
                        update_post_meta($pkg_id, '_package_active', 'yes');
                        update_post_meta($pkg_id, '_package_reschedule_allowed', 'yes');
                        update_post_meta($pkg_id, '_package_reschedule_hours', 48);

                        update_post_meta($pkg_id, '_package_online_gallery', isset($_POST['online_gallery']) ? 'yes' : 'no');
                        update_post_meta($pkg_id, '_package_raw_images', isset($_POST['raw_images']) ? 'yes' : 'no');
                        update_post_meta($pkg_id, '_package_printing', isset($_POST['printing']) ? 'yes' : 'no');
                        update_post_meta($pkg_id, '_package_transportation', isset($_POST['transportation']) ? 'yes' : 'no');
                        update_post_meta($pkg_id, '_package_drone_coverage', isset($_POST['drone_coverage']) ? 'yes' : 'no');
                        update_post_meta($pkg_id, '_package_priority_editing', isset($_POST['priority_editing']) ? 'yes' : 'no');

                        update_post_meta($pkg_id, '_package_features', $pdata['features']);
                    }
                }
            }
        }

        $demo_bookings = array(
            array(
                'name' => 'Sarah & James',
                'email' => 'sarah.mensah@email.com',
                'phone' => '+233 24 123 4567',
                'date' => '2026-08-15',
                'location' => 'La Palm Royal Beach Hotel, Accra',
                'status' => 'confirmed',
                'total' => 5000,
                'deposit' => 2500,
            ),
            array(
                'name' => 'Kofi Asante',
                'email' => 'kofi.asante@company.com',
                'phone' => '+233 20 987 6543',
                'date' => '2026-07-20',
                'location' => 'Kempinski Hotel, Accra',
                'status' => 'pending',
                'total' => 2000,
                'deposit' => 1000,
                ),
            array(
                'name' => 'Ama Serwaa',
                'email' => 'ama.serwaa@email.com',
                'phone' => '+233 27 555 1234',
                'date' => '2026-07-10',
                'location' => 'In Studio, Osu',
                'status' => 'completed',
                'total' => 500,
                'deposit' => 250,
            )
        );

        foreach ($demo_bookings as $booking) {
            $bk_id = wp_insert_post(array(
                'post_title' => $booking['name'] . ' - Seeded Booking',
                'post_type' => 'booking',
                'post_status' => 'publish',
            ));

            if ($bk_id && !is_wp_error($bk_id)) {
                update_post_meta($bk_id, '_booking_client_name', $booking['name']);
                update_post_meta($bk_id, '_booking_client_email', $booking['email']);
                update_post_meta($bk_id, '_booking_client_phone', $booking['phone']);
                update_post_meta($bk_id, '_booking_event_date', $booking['date']);
                update_post_meta($bk_id, '_booking_location', $booking['location']);
                update_post_meta($bk_id, '_booking_status', $booking['status']);
                update_post_meta($bk_id, '_booking_amount_total', $booking['total']);
                update_post_meta($bk_id, '_booking_amount_deposit', $booking['deposit']);

                $inv_code = 'INV-2026-00' . $bk_id;
                $inv_id = wp_insert_post(array(
                    'post_title' => $inv_code . ' [' . $booking['name'] . ']',
                    'post_type' => 'invoice',
                    'post_status' => 'publish',
                ));
                if ($inv_id && !is_wp_error($inv_id)) {
                    update_post_meta($inv_id, '_invoice_number', $inv_code);
                    update_post_meta($inv_id, '_invoice_booking_id', $bk_id);
                    update_post_meta($inv_id, '_invoice_client_name', $booking['name']);
                    update_post_meta($inv_id, '_invoice_total', $booking['total']);
                    update_post_meta($inv_id, '_invoice_status', $booking['status'] === 'pending' ? 'unpaid' : 'paid');
                    update_post_meta($inv_id, '_invoice_due_date', date('Y-m-d', strtotime($booking['date'] . ' - 3 days')));
                }
            }
        }
    }
}
add_action('after_switch_theme', 'studio_photography_theme_activation');


// ── 14. FRONTEND CUSTOM ADMIN DASHBOARD AJAX ACTIONS ────────────────────────

function studio_photography_admin_update_booking_status() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Unauthorized administrative access.', 'studio-photography'));
    }
    
    $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
    $new_status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
    
    if ($booking_id && in_array($new_status, array('pending', 'confirmed', 'completed', 'cancelled'))) {
        update_post_meta($booking_id, '_booking_status', $new_status);
        
        // If booking is marked completed, automatically update any linked invoice to paid!
        if ($new_status === 'completed') {
            $invoices = get_posts(array(
                'post_type' => 'invoice',
                'meta_key' => '_invoice_booking_id',
                'meta_value' => $booking_id,
                'numberposts' => 1
            ));
            if (!empty($invoices)) {
                update_post_meta($invoices[0]->ID, '_invoice_status', 'paid');
            }
        }
        
        wp_send_json_success(__('Booking status updated successfully.', 'studio-photography'));
    }
    
    wp_send_json_error(__('Invalid booking parameter details.', 'studio-photography'));
}
add_action('wp_ajax_admin_update_booking_status', 'studio_photography_admin_update_booking_status');


function studio_photography_admin_mark_invoice_paid() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Unauthorized administrative access.', 'studio-photography'));
    }
    
    $invoice_id = isset($_POST['invoice_id']) ? intval($_POST['invoice_id']) : 0;
    
    if ($invoice_id) {
        update_post_meta($invoice_id, '_invoice_status', 'paid');
        wp_send_json_success(__('Invoice marked as fully paid.', 'studio-photography'));
    }
    
    wp_send_json_error(__('Invalid invoice parameter details.', 'studio-photography'));
}
add_action('wp_ajax_admin_mark_invoice_paid', 'studio_photography_admin_mark_invoice_paid');


// ── 15. HEADLESS SAAS MANAGEMENT AJAX ACTIONS ───────────────────────────────

function studio_photography_admin_create_client_gallery() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Unauthorized.', 'studio-photography'));
    }
    
    $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
    $gdrive_folder = isset($_POST['gdrive_folder']) ? esc_url_raw($_POST['gdrive_folder']) : '';
    $password = isset($_POST['password']) ? sanitize_text_field($_POST['password']) : '';
    $balance = isset($_POST['balance']) ? floatval($_POST['balance']) : 0.00;
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    
    if (empty($title)) {
        wp_send_json_error(__('Gallery title is required.', 'studio-photography'));
    }
    
    $post_id = wp_insert_post(array(
        'post_title' => $title,
        'post_type' => 'client_gallery',
        'post_status' => 'publish',
        'post_password' => $password
    ));
    
    if ($post_id && !is_wp_error($post_id)) {
        update_post_meta($post_id, '_gallery_gdrive_folder', $gdrive_folder);
        update_post_meta($post_id, '_gallery_balance_amount', $balance);
        update_post_meta($post_id, '_gallery_client_email', $email);
        update_post_meta($post_id, '_gallery_payment_status', $balance > 0 ? 'unpaid' : 'paid');
        
        wp_send_json_success(array('post_id' => $post_id));
    }
    
    wp_send_json_error(__('Failed to create client gallery.', 'studio-photography'));
}
add_action('wp_ajax_admin_create_client_gallery', 'studio_photography_admin_create_client_gallery');


function studio_photography_admin_create_session() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Unauthorized.', 'studio-photography'));
    }
    
    $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
    $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
    $image_url = isset($_POST['image_url']) ? esc_url_raw($_POST['image_url']) : '';
    
    if (empty($title)) {
        wp_send_json_error(__('Session name is required.', 'studio-photography'));
    }
    
    $post_id = wp_insert_post(array(
        'post_title' => $title,
        'post_content' => $description,
        'post_type' => 'session',
        'post_status' => 'publish'
    ));
    
    if ($post_id && !is_wp_error($post_id)) {
        update_post_meta($post_id, '_session_active', 'yes');
        update_post_meta($post_id, '_session_featured', 'no');
        update_post_meta($post_id, '_session_display_order', 0);
        update_post_meta($post_id, '_session_image', $image_url);
        
        if (!empty($category)) {
            $term = term_exists($category, 'session_category');
            if (!$term) {
                $term = wp_insert_term($category, 'session_category');
            }
            if (!is_wp_error($term) && isset($term['term_id'])) {
                wp_set_post_terms($post_id, array(intval($term['term_id'])), 'session_category');
            }
        }
        
        wp_send_json_success(array('post_id' => $post_id));
    }
    
    wp_send_json_error(__('Failed to create session.', 'studio-photography'));
}
add_action('wp_ajax_admin_create_session', 'studio_photography_admin_create_session');


function studio_photography_admin_create_package() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Unauthorized.', 'studio-photography'));
    }
    
    $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
    $session_id = isset($_POST['session_id']) ? intval($_POST['session_id']) : 0;
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0.00;
    $duration = isset($_POST['duration']) ? sanitize_text_field($_POST['duration']) : '1 hour';
    $edited_photos = isset($_POST['edited_photos']) ? intval($_POST['edited_photos']) : 10;
    $max_people = isset($_POST['max_people']) ? intval($_POST['max_people']) : 1;
    $outfit_changes = isset($_POST['outfit_changes']) ? intval($_POST['outfit_changes']) : 1;
    $locations = isset($_POST['locations']) ? intval($_POST['locations']) : 1;
    $features_raw = isset($_POST['features']) ? sanitize_textarea_field($_POST['features']) : '';
    
    if (empty($title) || empty($session_id)) {
        wp_send_json_error(__('Package name and linked Session are required.', 'studio-photography'));
    }
    
    $post_id = wp_insert_post(array(
        'post_title' => $title,
        'post_type' => 'package',
        'post_status' => 'publish'
    ));
    
    if ($post_id && !is_wp_error($post_id)) {
        update_post_meta($post_id, '_package_session_id', $session_id);
        update_post_meta($post_id, '_package_price', $price);
        update_post_meta($post_id, '_package_duration', $duration);
        update_post_meta($post_id, '_package_edited_photos', $edited_photos);
        update_post_meta($post_id, '_package_max_people', $max_people);
        update_post_meta($post_id, '_package_outfit_changes', $outfit_changes);
        update_post_meta($post_id, '_package_locations', $locations);
        update_post_meta($post_id, '_package_active', 'yes');
        update_post_meta($post_id, '_package_deposit_percentage', 50);
        update_post_meta($post_id, '_package_reschedule_allowed', 'yes');
        update_post_meta($post_id, '_package_reschedule_hours', 48);
        update_post_meta($post_id, '_package_delivery_time', '5 Days');
        
        $features = array_filter(array_map('trim', explode("\n", str_replace("\r", "", $features_raw))));
        update_post_meta($post_id, '_package_features', $features);
        
        wp_send_json_success(array('post_id' => $post_id));
    }
    
    wp_send_json_error(__('Failed to create package.', 'studio-photography'));
}
add_action('wp_ajax_admin_create_package', 'studio_photography_admin_create_package');


function studio_photography_admin_delete_post() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Unauthorized.', 'studio-photography'));
    }
    
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    
    if ($post_id) {
        $post = get_post($post_id);
        if ($post && in_array($post->post_type, array('booking', 'invoice', 'session', 'package', 'client_gallery'))) {
            wp_delete_post($post_id, true); // force delete from database!
            wp_send_json_success(__('Post deleted permanently.', 'studio-photography'));
        }
    }
    
    wp_send_json_error(__('Invalid post ID or permission denied.', 'studio-photography'));
}
add_action('wp_ajax_admin_delete_post', 'studio_photography_admin_delete_post');


// ── 16. MANUALLY CREATE BOOKING FROM DASHBOARD AJAX ACTION ───────────────────

function studio_photography_admin_create_booking() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Unauthorized.', 'studio-photography'));
    }
    
    $client_name = isset($_POST['client_name']) ? sanitize_text_field($_POST['client_name']) : '';
    $client_email = isset($_POST['client_email']) ? sanitize_email($_POST['client_email']) : '';
    $client_phone = isset($_POST['client_phone']) ? sanitize_text_field($_POST['client_phone']) : '';
    $session_id = isset($_POST['session_id']) ? intval($_POST['session_id']) : 0;
    $package_id_raw = isset($_POST['package_id']) ? sanitize_text_field($_POST['package_id']) : '';
    $event_date = isset($_POST['event_date']) ? sanitize_text_field($_POST['event_date']) : '';
    $event_location = isset($_POST['event_location']) ? sanitize_text_field($_POST['event_location']) : '';
    $total_budget = isset($_POST['total_budget']) ? floatval($_POST['total_budget']) : 0.00;
    $deposit_paid = isset($_POST['deposit_paid']) ? floatval($_POST['deposit_paid']) : 0.00;
    
    if (empty($client_name) || empty($client_email) || empty($event_date) || empty($package_id_raw)) {
        wp_send_json_error(__('Client details, event date, and pricing package are required.', 'studio-photography'));
    }
    
    $package_title = 'Custom Package';
    $package_id = 0;
    if ($package_id_raw !== 'custom') {
        $package_id = intval($package_id_raw);
        $package = get_post($package_id);
        if ($package) {
            $package_title = $package->post_title;
        }
    }
    
    // 1. Create Booking Post
    $booking_id = wp_insert_post(array(
        'post_title' => $client_name . ' - ' . $package_title,
        'post_type' => 'booking',
        'post_status' => 'publish'
    ));
    
    if ($booking_id && !is_wp_error($booking_id)) {
        update_post_meta($booking_id, '_booking_client_name', $client_name);
        update_post_meta($booking_id, '_booking_client_email', $client_email);
        update_post_meta($booking_id, '_booking_client_phone', $client_phone);
        update_post_meta($booking_id, '_booking_event_date', $event_date);
        update_post_meta($booking_id, '_booking_location', $event_location);
        update_post_meta($booking_id, '_booking_session_id', $session_id);
        update_post_meta($booking_id, '_booking_package_id', $package_id);
        update_post_meta($booking_id, '_booking_amount_total', $total_budget);
        update_post_meta($booking_id, '_booking_amount_deposit', $deposit_paid);
        update_post_meta($booking_id, '_booking_status', 'confirmed');
        
        // 2. Auto-generate corresponding Invoice!
        $inv_code = 'INV-' . date('Y') . '-' . sprintf('%03d', $booking_id);
        $invoice_id = wp_insert_post(array(
            'post_title' => $inv_code . ' [' . $client_name . ']',
            'post_type' => 'invoice',
            'post_status' => 'publish',
        ));
        
        if ($invoice_id && !is_wp_error($invoice_id)) {
            update_post_meta($invoice_id, '_invoice_number', $inv_code);
            update_post_meta($invoice_id, '_invoice_booking_id', $booking_id);
            update_post_meta($invoice_id, '_invoice_client_name', $client_name);
            update_post_meta($invoice_id, '_invoice_total', $total_budget);
            update_post_meta($invoice_id, '_invoice_status', 'unpaid');
            update_post_meta($invoice_id, '_invoice_due_date', date('Y-m-d', strtotime($event_date . ' - 3 days')));
        }
        
        wp_send_json_success(array('booking_id' => $booking_id));
    }
    
    wp_send_json_error(__('Failed to manually register booking.', 'studio-photography'));
}
add_action('wp_ajax_admin_create_booking', 'studio_photography_admin_create_booking');


// ── 17. MANUALLY CREATE STANDALONE INVOICE AJAX ACTION ──────────────────────

// ── 17. MANUALLY CREATE STANDALONE INVOICE AJAX ACTION ──────────────────────

function studio_photography_admin_create_invoice() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Unauthorized.', 'studio-photography'));
    }
    
    $client_name = isset($_POST['client_name']) ? sanitize_text_field($_POST['client_name']) : '';
    $client_email = isset($_POST['client_email']) ? sanitize_email($_POST['client_email']) : '';
    $invoice_title = isset($_POST['invoice_title']) ? sanitize_text_field($_POST['invoice_title']) : '';
    $due_date = isset($_POST['due_date']) ? sanitize_text_field($_POST['due_date']) : '';
    
    $package_id = isset($_POST['package_id']) ? intval($_POST['package_id']) : 0;
    $event_date = isset($_POST['event_date']) ? sanitize_text_field($_POST['event_date']) : '';
    $photo_delivery_date = isset($_POST['photo_delivery_date']) ? sanitize_text_field($_POST['photo_delivery_date']) : '';
    $shoot_location = isset($_POST['shoot_location']) ? sanitize_text_field($_POST['shoot_location']) : '';
    $shoot_type = isset($_POST['shoot_type']) ? sanitize_text_field($_POST['shoot_type']) : '';
    $shoot_notes = isset($_POST['shoot_notes']) ? sanitize_textarea_field($_POST['shoot_notes']) : '';

    // Parse the itemized services built in the dashboard "Issue Invoice" builder
    $invoice_items = array();
    if (!empty($_POST['items_json'])) {
        $decoded = json_decode(wp_unslash($_POST['items_json']), true);
        if (is_array($decoded)) {
            foreach ($decoded as $it) {
                $d = sanitize_text_field(isset($it['d']) ? $it['d'] : '');
                $q = max(1, intval(isset($it['q']) ? $it['q'] : 1));
                $p = max(0, floatval(isset($it['p']) ? $it['p'] : 0));
                if ($d !== '' || $p > 0) $invoice_items[] = array('desc' => $d, 'qty' => $q, 'price' => $p);
            }
        }
    }
    
    if (empty($client_name) || empty($due_date)) {
        wp_send_json_error(__('Client Name and Due Date are required.', 'studio-photography'));
    }
    
    // Dynamic pricing calculations (Surcharges and packages)
    $base_price = 0.00;
    $surcharge_amount = 0.00;
    $surcharge_pct = 0;
    $total_price = isset($_POST['total_amount']) ? floatval($_POST['total_amount']) : 0.00;
    
    if ($package_id) {
        $package = get_post($package_id);
        if ($package && $package->post_type === 'package') {
            $base_price = floatval(get_post_meta($package_id, '_package_price', true));
            
            if (!empty($event_date) && !empty($photo_delivery_date)) {
                $date1 = new DateTime($event_date);
                $date2 = new DateTime($photo_delivery_date);
                
                if ($date2 >= $date1) {
                    $diff_days = $date2->diff($date1)->days;
                    if ($diff_days >= 0 && $diff_days <= 2) {
                        $surcharge_pct = 40; // 0 to 2 days (+40%)
                    } elseif ($diff_days <= 5) {
                        $surcharge_pct = 35; // 3 to 5 days (+35%)
                    }
                }
            }
            $surcharge_amount = $base_price * ($surcharge_pct / 100);
            $total_price = $base_price + $surcharge_amount;
        }
    }
    
    // Itemized services drive the total (server-authoritative math, no client-side trust)
    if (!empty($invoice_items) && !$package_id) {
        $items_total = 0.00;
        foreach ($invoice_items as $it) $items_total += $it['qty'] * $it['price'];
        $base_price = $items_total;
        $total_price = $items_total;
    }

    // Auto-generate invoice code if left blank
    $inv_code = !empty($invoice_title) ? $invoice_title : ('INV-' . date('Ymd') . '-' . rand(100, 999));
    
    $invoice_id = wp_insert_post(array(
        'post_title' => $inv_code . ' [' . $client_name . ']',
        'post_type' => 'invoice',
        'post_status' => 'publish'
    ));
    
    if ($invoice_id && !is_wp_error($invoice_id)) {
        update_post_meta($invoice_id, '_invoice_number', $inv_code);
        update_post_meta($invoice_id, '_invoice_client_name', $client_name);
        update_post_meta($invoice_id, '_invoice_total', $total_price);
        update_post_meta($invoice_id, '_invoice_status', 'unpaid');
        update_post_meta($invoice_id, '_invoice_due_date', $due_date);
        update_post_meta($invoice_id, '_invoice_shoot_location', $shoot_location);
        update_post_meta($invoice_id, '_invoice_shoot_type', $shoot_type);
        update_post_meta($invoice_id, '_invoice_shoot_notes', $shoot_notes);
        if (!empty($invoice_items)) update_post_meta($invoice_id, '_invoice_items', $invoice_items);
        
        // Save package/surcharge meta for itemized invoice receipts!
        if ($package_id) {
            update_post_meta($invoice_id, '_invoice_package_id', $package_id);
            update_post_meta($invoice_id, '_invoice_event_date', $event_date);
            update_post_meta($invoice_id, '_invoice_photo_delivery_date', $photo_delivery_date);
            update_post_meta($invoice_id, '_invoice_base_price', $base_price);
            update_post_meta($invoice_id, '_invoice_surcharge_amount', $surcharge_amount);
        }
        
        if (!empty($client_email)) {
            update_post_meta($invoice_id, '_invoice_client_email', $client_email);
        }
        
        wp_send_json_success(array('invoice_id' => $invoice_id));
    }
    
    wp_send_json_error(__('Failed to create manual invoice.', 'studio-photography'));
}
add_action('wp_ajax_admin_create_invoice', 'studio_photography_admin_create_invoice');


// ── 18. AUTOMATIC PAYSTACK-STYLE INVOICE NUMBER GENERATOR FOR WP-ADMIN ────────

// Pre-fill the title input field inside WP-Admin "Add New Invoice" page
add_filter('default_title', function($title, $post) {
    if ($post->post_type === 'invoice') {
        // Formats as Paystack sequential merchant code: INV-YYYYMMDD-RAND
        $title = 'INV-' . date('Ymd') . '-' . rand(100, 999);
    }
    return $title;
}, 10, 2);

// Force-compile empty title to Paystack-style code on draft/publish save
add_filter('wp_insert_post_data', function($data, $postarr) {
    if ($data['post_type'] === 'invoice') {
        $title = trim($data['post_title']);
        if (empty($title) || strpos(strtolower($title), 'auto draft') !== false || strpos(strtolower($title), 'draft') !== false) {
            $data['post_title'] = 'INV-' . date('Ymd') . '-' . rand(100, 999);
        }
    }
    return $data;
}, 10, 2);


// ── 19. AJAX GET RECEIPT HTML FOR FRONTEND DASHBOARD POPUPS ──────────────────

function studio_photography_handle_get_receipt_html() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Unauthorized.', 'studio-photography'));
    }

    $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
    $invoice_id = isset($_POST['invoice_id']) ? intval($_POST['invoice_id']) : 0;
    $is_booking_receipt = isset($_POST['is_booking_receipt']) && $_POST['is_booking_receipt'] == 1;

    wp_send_json_success(array('html' => studio_photography_build_receipt_html($booking_id, $invoice_id, $is_booking_receipt)));
}

function studio_photography_build_receipt_html($booking_id, $invoice_id, $is_booking_receipt) {
    
    $client_name = '';
    $client_email = '';
    $client_phone = '—';
    $event_date = '';
    $location = 'Secure Online Delivery';
    $amount_total = 0.00;
    $amount_deposit = 0.00;
    $paystack_ref = '';
    $status = 'pending';
    $invoice_num = 'INV-000';
    $session = null;
    $package = null;
    $base_price = 0.00;
    $surcharge_amount = 0.00;
    $custom_package_name = '';

    if ($invoice_id && !$is_booking_receipt) {
        $invoice = get_post($invoice_id);
        if (!$invoice || $invoice->post_type !== 'invoice') {
            wp_send_json_error(__('Invoice not found.', 'studio-photography'));
        }
        $invoice_num = get_post_meta($invoice_id, '_invoice_number', true) ?: 'INV-' . $invoice_id;
        $client_name = get_post_meta($invoice_id, '_invoice_client_name', true);
        $client_email = get_post_meta($invoice_id, '_invoice_client_email', true) ?: 'client@email.com';
        $event_date = get_post_meta($invoice_id, '_invoice_event_date', true) ?: get_the_date('Y-m-d', $invoice_id);
        $amount_total = floatval(get_post_meta($invoice_id, '_invoice_total', true));
        $paystack_ref = get_post_meta($invoice_id, '_invoice_paystack_reference', true);
        $inv_status_raw = get_post_meta($invoice_id, '_invoice_status', true);
        $status = ($inv_status_raw === 'paid') ? 'completed' : 'pending';
        
        $package_id = get_post_meta($invoice_id, '_invoice_package_id', true);
        if ($package_id) {
            $package = get_post($package_id);
            $session_id = $package ? get_post_meta($package_id, '_package_session_id', true) : 0;
            $session = get_post($session_id);
            $surcharge_amount = floatval(get_post_meta($invoice_id, '_invoice_surcharge_amount', true));
            $base_price = floatval(get_post_meta($invoice_id, '_invoice_base_price', true)) ?: ($amount_total - $surcharge_amount);
        } else {
            $base_price = $amount_total;
        }

        $custom_package_name = get_post_meta($invoice_id, '_invoice_package_name', true);
        // Manual-invoice extras: itemized services + shoot details
        $invoice_items = get_post_meta($invoice_id, '_invoice_items', true);
        $shoot_location = get_post_meta($invoice_id, '_invoice_shoot_location', true);
        $shoot_type = get_post_meta($invoice_id, '_invoice_shoot_type', true);
        $shoot_notes = get_post_meta($invoice_id, '_invoice_shoot_notes', true);
        $location = $shoot_location ?: 'Studio';
    } else {
        // Handle Booking Receipt (either passed directly as booking_id or mapped via invoice linked booking)
        if ($invoice_id && $is_booking_receipt) {
            $booking_id = intval(get_post_meta($invoice_id, '_invoice_booking_id', true));
        }
        
        if (empty($booking_id)) {
            wp_send_json_error(__('Booking not found or not linked.', 'studio-photography'));
        }
        
        $booking = get_post($booking_id);
        if (!$booking || $booking->post_type !== 'booking') {
            wp_send_json_error(__('Booking record missing.', 'studio-photography'));
        }

        $client_name = get_post_meta($booking_id, '_booking_client_name', true);
        $client_email = get_post_meta($booking_id, '_booking_client_email', true);
        $client_phone = get_post_meta($booking_id, '_booking_client_phone', true) ?: '—';
        $event_date = get_post_meta($booking_id, '_booking_event_date', true);
        $location = get_post_meta($booking_id, '_booking_location', true) ?: 'Studio';
        $amount_total = floatval(get_post_meta($booking_id, '_booking_amount_total', true));
        $amount_deposit = floatval(get_post_meta($booking_id, '_booking_amount_deposit', true));
        $paystack_ref = get_post_meta($booking_id, '_booking_paystack_reference', true);
        $status = get_post_meta($booking_id, '_booking_status', true) ?: 'pending';

        $session_id = get_post_meta($booking_id, '_booking_session_id', true);
        $package_id = get_post_meta($booking_id, '_booking_package_id', true);
        $session = get_post($session_id);
        $package = get_post($package_id);

        $invoice_num = 'INV-' . date('Y') . '-' . sprintf('%03d', $booking_id);
        $surcharge_amount = floatval(get_post_meta($booking_id, '_booking_delivery_surcharge', true));
        $base_price = $amount_total - $surcharge_amount;
    }

    $site_name = get_bloginfo('name');
    
    // Fetch all active packages for the current Session Type to display comparison list
    $all_session_packages = array();
    if ($session_id) {
        $packages_query = new WP_Query(array(
            'post_type' => 'package',
            'posts_per_page' => -1,
            'meta_key' => '_package_session_id',
            'meta_value' => $session_id,
            'orderby' => 'display_order ID',
            'order' => 'ASC'
        ));
        if ($packages_query->have_posts()) {
            while ($packages_query->have_posts()) {
                $packages_query->the_post();
                $p_id = get_the_ID();
                $all_session_packages[] = array(
                    'id' => $p_id,
                    'name' => get_the_title(),
                    'price' => floatval(get_post_meta($p_id, '_package_price', true)),
                    'duration' => get_post_meta($p_id, '_package_duration', true) ?: '1 hour',
                );
            }
            wp_reset_postdata();
        }
    }

    ob_start();
    ?>
    <div id="print-area-container" class="p-4 sm:p-8 bg-white border border-slate-200 rounded-2xl max-w-2xl mx-auto font-sans">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-slate-100 pb-5 mb-6">
            <div class="flex items-center gap-3">
                <div class="studio-receipt-logo">
                    <?php echo studio_photography_get_receipt_logo_html(isset($invoice_id) ? $invoice_id : 0); ?>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900"><?php echo esc_html($site_name); ?></h2>
                    <p class="text-xs text-slate-400"><?php _e('Professional Photography Studio', 'studio-photography'); ?></p>
                </div>
            </div>
            <div class="mt-4 sm:mt-0 text-left sm:text-right">
                <span class="text-xs font-bold uppercase tracking-wider <?php echo $status === 'completed' ? 'text-green-600 bg-green-50' : 'text-red-600 bg-red-50'; ?> px-2.5 py-1 rounded-full"><?php echo $status === 'completed' ? esc_html__('Payment Settled', 'studio-photography') : esc_html__('Unpaid / Pending', 'studio-photography'); ?></span>
                <p class="text-sm text-slate-900 font-bold mt-2.5"><?php _e('Receipt No:', 'studio-photography'); ?> <?php echo esc_html($invoice_num); ?></p>
                <p class="text-xs text-slate-400 mt-1"><?php _e('Date:', 'studio-photography'); ?> <?php echo date('F d, Y'); ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8 text-sm">
            <div>
                <h4 class="font-bold text-slate-400 uppercase text-xs mb-2"><?php _e('Billed To', 'studio-photography'); ?></h4>
                <p class="font-bold text-slate-800 text-base mb-1"><?php echo esc_html($client_name); ?></p>
                <?php if ($client_phone !== '—') : ?><p class="text-slate-500 mb-0.5"><?php echo esc_html($client_phone); ?></p><?php endif; ?>
                <p class="text-slate-500"><?php echo esc_html($client_email); ?></p>
            </div>
            <div class="sm:text-right">
                <h4 class="font-bold text-slate-400 uppercase text-xs mb-2"><?php _e('Issued By', 'studio-photography'); ?></h4>
                <p class="font-bold text-slate-800 text-base mb-1"><?php echo esc_html($site_name); ?></p>
                <p class="text-slate-500 mb-0.5"><?php echo esc_html(studio_photography_get_business_email()); ?></p>
                <p class="text-slate-500"><?php echo esc_html(studio_photography_get_business_location()); ?></p>
            </div>
        </div>

        <!-- Receipt Table -->
        <table class="w-full text-left text-sm mb-8">
            <thead>
                <tr class="border-b border-slate-100 text-slate-400 uppercase text-xs">
                    <th class="py-3 font-semibold"><?php _e('Photography Service & Package', 'studio-photography'); ?></th>
                    <th class="py-3 text-right font-semibold"><?php _e('Shoot Date', 'studio-photography'); ?></th>
                    <th class="py-3 text-right font-semibold"><?php _e('Cedi Total', 'studio-photography'); ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (!empty($invoice_items)) : ?>
                    <?php foreach ($invoice_items as $inv_item) : ?>
                        <tr class="text-slate-800">
                            <td class="py-4 font-bold">
                                <?php echo esc_html($inv_item['desc'] !== '' ? $inv_item['desc'] : __('Service Item', 'studio-photography')); ?>
                                <span class="block text-xs text-slate-400 font-normal mt-1">
                                    <?php echo intval($inv_item['qty']); ?> &times; GH₵ <?php echo number_format(floatval($inv_item['price']), 2); ?>
                                    <?php if (!empty($shoot_type)) : ?>&bull; <?php echo esc_html($shoot_type); ?><?php endif; ?>
                                </span>
                            </td>
                            <td class="py-4 text-right text-slate-600 font-medium"><?php echo !empty($event_date) ? esc_html(date('M d, Y', strtotime($event_date))) : esc_html__('Unset', 'studio-photography'); ?></td>
                            <td class="py-4 text-right font-bold text-slate-900">GH₵ <?php echo number_format($inv_item['qty'] * $inv_item['price'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                <tr class="text-slate-800">
                    <td class="py-4 font-bold">
                        <?php echo $session ? esc_html($session->post_title) : esc_html(!empty($shoot_type) ? $shoot_type : __('Custom Shoot', 'studio-photography')); ?>
                        <span class="block text-xs text-slate-400 font-normal mt-1">
                            <?php _e('Tier:', 'studio-photography'); ?> <?php echo $package ? esc_html($package->post_title) : esc_html(!empty($custom_package_name) ? $custom_package_name : __('Custom Pack', 'studio-photography')); ?> 
                            <?php if ($location !== 'Secure Online Delivery') : ?>• <?php echo esc_html($location); ?><?php endif; ?>
                        </span>
                    </td>
                    <td class="py-4 text-right text-slate-600 font-medium"><?php echo !empty($event_date) ? esc_html(date('M d, Y', strtotime($event_date))) : esc_html__('Unset', 'studio-photography'); ?></td>
                    <td class="py-4 text-right font-bold text-slate-900">GH₵ <?php echo number_format($base_price, 2); ?></td>
                </tr>
                <?php if ($surcharge_amount > 0) : ?>
                    <tr class="text-slate-800">
                        <td class="py-4 font-medium text-amber-600 flex items-center gap-1">
                            <span class="text-amber-500 font-bold">&#9733;</span>
                            <?php _e('Priority Delivery Surcharge (Rush)', 'studio-photography'); ?>
                        </td>
                        <td class="py-4 text-right text-slate-400 font-normal">—</td>
                        <td class="py-4 text-right font-bold text-amber-600">+GH₵ <?php echo number_format($surcharge_amount, 2); ?></td>
                    </tr>
                <?php endif; ?>
                <?php endif; /* end: no itemized services */ ?>
            </tbody>
        </table>

        <?php if (!empty($shoot_notes)) : ?>
            <div class="mb-6 bg-slate-50 border border-slate-100 rounded-xl p-4 text-sm text-slate-600 leading-relaxed">
                <strong class="block text-xs font-extrabold uppercase tracking-widest text-slate-400 mb-1.5"><?php _e('Shoot Notes', 'studio-photography'); ?></strong>
                <?php echo nl2br(esc_html($shoot_notes)); ?>
            </div>
        <?php endif; ?>

        <!-- Totals Breakdown -->
        <div class="border-t border-slate-100 pt-5 mb-8 flex justify-end">
            <div class="w-full sm:w-1/2 text-sm space-y-2.5">
                <div class="flex justify-between text-slate-500">
                    <span><?php _e('Subtotal Price:', 'studio-photography'); ?></span>
                    <span class="font-medium text-slate-900">GH₵ <?php echo number_format($amount_total, 2); ?></span>
                </div>
                
                <?php if ($amount_deposit > 0) : ?>
                    <div class="flex justify-between text-green-600 font-semibold bg-green-50/50 p-2.5 rounded-lg border border-green-100/50">
                        <span class="flex items-center gap-1">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                            <?php echo $status === 'completed' ? esc_html__('Total Settled:', 'studio-photography') : esc_html__('Deposit Paid via Paystack:', 'studio-photography'); ?>
                        </span>
                        <span>GH₵ <?php echo $status === 'completed' ? number_format($amount_total, 2) : number_format($amount_deposit, 2); ?></span>
                    </div>

                    <div class="flex justify-between text-slate-700 font-medium border-t border-slate-100 pt-2.5">
                        <span><?php _e('Outstanding Balance Due:', 'studio-photography'); ?></span>
                        <span class="font-bold text-slate-900 font-sans">
                            GH₵ <?php echo $status === 'completed' ? '0.00' : number_format($amount_total - $amount_deposit, 2); ?>
                        </span>
                    </div>
                <?php else : ?>
                    <div class="flex justify-between <?php echo $status === 'completed' ? 'text-green-600 bg-green-50/50 border-green-100/50' : 'text-red-600 bg-red-50/50 border-red-100/50'; ?> font-semibold p-2.5 rounded-lg border">
                        <span class="flex items-center gap-1">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <?php echo $status === 'completed' ? esc_html__('Paid / Completed:', 'studio-photography') : esc_html__('Outstanding Invoice Total:', 'studio-photography'); ?>
                        </span>
                        <span>GH₵ <?php echo number_format($amount_total, 2); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Package Comparison list -->
        <?php if ($is_booking_receipt && !empty($all_session_packages)) : ?>
            <div class="mt-8 pt-6 border-t border-slate-100 mb-6">
                <h4 class="text-xs font-extrabold uppercase tracking-widest text-slate-400 mb-4"><?php _e('Session Packages List', 'studio-photography'); ?></h4>
                <div class="space-y-2 text-xs">
                    <?php foreach ($all_session_packages as $p) : 
                        $is_selected = ($p['id'] === $package_id);
                        ?>
                        <div class="flex justify-between p-2.5 rounded-lg border <?php echo $is_selected ? 'border-slate-900 bg-slate-50 font-bold' : 'border-slate-100 bg-white'; ?>">
                            <span><?php echo esc_html($p['name']); ?> • <?php echo esc_html($p['duration']); ?> <?php echo $is_selected ? esc_html__('(Your Choice)', 'studio-photography') : ''; ?></span>
                            <span>GH₵ <?php echo number_format($p['price'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-4 text-xs text-slate-500 leading-relaxed mb-6">
            <span class="font-bold block text-slate-700 mb-1 flex items-center gap-1"><svg class="h-3.5 w-3.5 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg> <?php _e('Secure Transaction Log:', 'studio-photography'); ?></span>
            <?php if (!empty($paystack_ref)) : ?>
                <?php _e('Authenticated successfully by Paystack. Reference:', 'studio-photography'); ?> <strong><?php echo esc_html($paystack_ref); ?></strong>.
            <?php else : ?>
                <?php _e('Booking registered manually inside the Studio Dashboard by the administrator.', 'studio-photography'); ?>
            <?php endif; ?>
        </div>

        <p class="text-center text-slate-400 text-[10px] border-t pt-4 leading-relaxed mt-6">© <?php echo date('Y'); ?> <?php echo esc_html($site_name); ?>. <?php _e('All rights reserved.', 'studio-photography'); ?></p>
    </div>
    <?php
    $html_output = ob_get_clean();
    return $html_output;
}
add_action('wp_ajax_get_receipt_html', 'studio_photography_handle_get_receipt_html');


// ── 20. LOGO SLIDERS USE WORDPRESS'S NATIVE PREVIEW REFRESH ─────────────────

add_action('customize_preview_init', function() {
    // Logo sliders use WordPress's native 'refresh' transport — no custom live-preview JS needed.
});


// ═══ 21. WP-ADMIN REDESIGN — MATCH THE SITE DESIGN FOR AN EASY WORKFLOW ═══

// Custom "Studio Slate" admin color scheme (Users → Profile → Admin Color Scheme)
add_action('admin_init', function() {
    wp_admin_css_color(
        'studio-slate',
        __('Studio Slate (Site Design)', 'studio-photography'),
        false,
        array('#0f172a', '#1e293b', '#2563eb', '#f59e0b'),
        array('base' => '#f1f5f9', 'focus' => '#ffffff', 'current' => '#ffffff')
    );
});

// Site-styled wp-admin chrome (dark slate menu, rounded buttons & cards)
add_action('admin_enqueue_scripts', function() {
    ?>
    <style>
        body.wp-admin { -webkit-font-smoothing: antialiased; }
        /* Dark slate admin menu — like the site navbar */
        #adminmenuback, #adminmenuwrap, #adminmenu { background: #0f172a !important; }
        #adminmenu a { color: #cbd5e1 !important; }
        #adminmenu li.menu-top:hover, #adminmenu li.opensub > a.menu-top, #adminmenu li > a.menu-top:focus { background: #1e293b !important; color: #fff !important; }
        #adminmenu li.current a.menu-top, #adminmenu li.wp-has-current-submenu a.wp-has-current-submenu { background: #1e293b !important; color: #fff !important; box-shadow: inset 4px 0 0 #2563eb; }
        #adminmenu .wp-submenu { background: #0b1220 !important; }
        #adminmenu .wp-submenu a { color: #94a3b8 !important; }
        #adminmenu .wp-submenu a:hover, #adminmenu .wp-submenu li.current a { color: #fff !important; }
        #adminmenu .wp-menu-image:before { color: #94a3b8 !important; }
        #adminmenu li.current .wp-menu-image:before, #adminmenu li.wp-has-current-submenu .wp-menu-image:before { color: #60a5fa !important; }
        #collapse-button { color: #64748b !important; }
        /* Dark admin bar — like the site header */
        #wpadminbar { background: #0f172a !important; }
        #wpadminbar .ab-item, #wpadminbar a.ab-item { color: #cbd5e1 !important; }
        #wpadminbar .hover .ab-item, #wpadminbar .ab-item:hover { color: #fff !important; }
        /* Rounded primary buttons — like the site's pill buttons */
        .wp-core-ui .button-primary { background: #0f172a !important; border-color: #0f172a !important; border-radius: 999px !important; padding: 4px 18px !important; color: #fff !important; text-shadow: none !important; box-shadow: none !important; }
        .wp-core-ui .button-primary:hover { background: #1e293b !important; border-color: #1e293b !important; }
        .wp-core-ui .button, .wp-core-ui .button-secondary { border-radius: 10px !important; border-color: #cbd5e1 !important; color: #334155 !important; }
        .wp-core-ui .button:hover { border-color: #0f172a !important; color: #0f172a !important; }
        /* Card-style postboxes — like the site cards */
        .postbox { border: 1px solid #e2e8f0 !important; border-radius: 14px !important; box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05) !important; overflow: hidden; }
        .postbox .hndle, .postbox-header { border-bottom: 1px solid #f1f5f9 !important; }
        .postbox .hndle, .postbox-header h3 { font-weight: 700 !important; color: #0f172a !important; }
        /* Rounded inputs */
        .wp-admin input[type="text"], .wp-admin input[type="email"], .wp-admin input[type="url"], .wp-admin input[type="password"], .wp-admin input[type="search"], .wp-admin input[type="number"], .wp-admin input[type="date"], .wp-admin textarea, .wp-admin select { border-radius: 10px !important; border-color: #cbd5e1 !important; }
        .wp-admin input:focus, .wp-admin textarea:focus, .wp-admin select:focus { border-color: #0f172a !important; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important; outline: none !important; }
        /* Notices & headings */
        .notice { border-radius: 12px !important; }
        .wrap h1, .wrap h2 { letter-spacing: -0.02em !important; }
        /* Lists tables */
        .wp-list-table thead th, .wp-list-table tfoot th { color: #64748b !important; text-transform: uppercase; font-size: 11px; letter-spacing: 0.06em; }
        .wp-list-table th, .wp-list-table td { border-color: #f1f5f9 !important; }
        /* Blue links → site navy */
        .wp-admin a { color: #1d4ed8; }
        .wp-admin a:hover { color: #0f172a; }
        /* Submit box card */
        #poststuff #submitdiv .inside { background: #f8fafc; }
    </style>
    <?php
});

// Branded login page — slate gradient + your site logo + rounded card
add_action('login_enqueue_scripts', function() {
    $logo_id = get_theme_mod('custom_logo');
    $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'medium') : '';
    ?>
    <style>
        body.login { background: linear-gradient(135deg, #0f172a 0%, #1e293b 55%, #334155 100%) !important; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        body.login::before { content: ''; position: fixed; inset: 0; background: radial-gradient(ellipse at top right, rgba(37, 99, 235, 0.18), transparent 55%); pointer-events: none; }
        .login #login { position: relative; width: 400px; max-width: 92vw; padding: 0; }
        .login form { background: #fff !important; border: none !important; border-radius: 18px !important; box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4) !important; padding: 34px 30px !important; }
        .login h1 a {
            <?php if (!empty($logo_url)) : ?>
            background-image: url('<?php echo esc_url($logo_url); ?>') !important; background-size: contain !important; background-position: center !important; background-repeat: no-repeat !important; width: 240px !important; height: 84px !important;
            <?php else : ?>
            background-size: contain !important; width: 240px !important; height: 84px !important;
            <?php endif; ?>
            margin: 0 auto 26px !important;
        }
        .login label { color: #334155 !important; font-weight: 600 !important; }
        .login input[type="text"], .login input[type="password"] { border-radius: 12px !important; border-color: #cbd5e1 !important; padding: 12px 14px !important; }
        .login input:focus { border-color: #0f172a !important; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.14) !important; }
        .login .button-primary { background: #0f172a !important; border: none !important; border-radius: 999px !important; padding: 12px !important; font-weight: 700 !important; letter-spacing: 0.04em; box-shadow: 0 8px 20px rgba(15, 23, 42, 0.35) !important; }
        .login .button-primary:hover { background: #1e293b !important; }
        .login #nav, .login #backtoblog { text-align: center; }
        .login #nav a, .login #backtoblog a { color: #cbd5e1 !important; }
        .login #nav a:hover, .login #backtoblog a:hover { color: #fff !important; }
        .login .privacy-policy-page-link a { color: #94a3b8 !important; }
    </style>
    <?php
});

// Branded admin footer
add_filter('admin_footer_text', function() {
    echo '<span style="font-weight:600;color:#334155;">📸 Studio Photography Command Center</span> — manage your whole studio from here.';
});


// ═══ 22. 🔔 LIVE DOWNLOAD BELL IN WP-ADMIN (front-end dashboard retired) ═══

// Add the bell node to the admin bar (admins only)
add_action('admin_bar_menu', function($admin_bar) {
    if (!current_user_can('manage_options')) return;
    $admin_bar->add_node(array(
        'id'     => 'studio-dl-bell',
        'parent' => 'top-secondary',
        'title'  => '<span style="position:relative;display:inline-flex;align-items:center;"><span class="dashicons dashicons-bell" style="font-size:16px;width:16px;height:16px;margin-top:4px;"></span><span id="studio-dl-badge" style="display:none;position:absolute;top:-2px;right:-9px;background:#e11d48;color:#fff;font-size:9px;font-weight:700;border-radius:999px;min-width:14px;height:14px;line-height:14px;text-align:center;padding:0 3px;">0</span></span>',
        'href'   => '#',
        'meta'   => array('title' => __('Live Client Download Activity', 'studio-photography')),
    ));
}, 100);

// Bell engine: badge + flyout feed, polling the existing live-download endpoints
add_action('admin_footer', function() {
    if (!current_user_can('manage_options')) return;
    ?>
    <div id="studio-dl-panel" style="display:none;position:fixed;top:32px;right:6px;width:340px;max-width:92vw;background:#fff;border:1px solid #e2e8f0;border-radius:14px;box-shadow:0 20px 50px rgba(0,0,0,.28);z-index:99999;overflow:hidden;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
        <div style="padding:11px 14px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:#f8fafc;">
            <strong style="font-size:12px;color:#0f172a;">🔔 Live Download Activity</strong>
            <button type="button" id="studio-dl-markread" style="border:none;background:none;color:#2563eb;font-weight:700;font-size:11px;cursor:pointer;">Mark all read</button>
        </div>
        <div id="studio-dl-events" style="max-height:320px;overflow-y:auto;"></div>
    </div>
    <script>
    (function() {
        var ajaxUrl = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';
        var badge = document.getElementById('studio-dl-badge');
        var panel = document.getElementById('studio-dl-panel');
        var list = document.getElementById('studio-dl-events');

        function timeAgo(ts) {
            var s = Math.max(1, Math.floor(Date.now() / 1000 - ts));
            if (s < 60) return s + 's ago';
            if (s < 3600) return Math.floor(s / 60) + 'm ago';
            if (s < 86400) return Math.floor(s / 3600) + 'h ago';
            return Math.floor(s / 86400) + 'd ago';
        }

        function render(events, unread) {
            if (badge) {
                if (unread > 0) { badge.textContent = unread > 99 ? '99+' : unread; badge.style.display = 'inline-block'; }
                else { badge.style.display = 'none'; }
            }
            if (!list) return;
            if (!events.length) {
                list.innerHTML = '<div style="padding:22px;text-align:center;color:#94a3b8;font-size:12px;">No client downloads yet — you\'ll see them here in real time.</div>';
                return;
            }
            list.innerHTML = events.map(function(evt) {
                var label = evt.type === 'zip' ? 'Full ZIP Collection' : (evt.item || 'Photo');
                return '<div style="display:flex;gap:10px;padding:11px 14px;border-bottom:1px solid #f8fafc;' + (evt.seen ? 'opacity:.55;' : '') + '">' +
                    '<div style="width:30px;height:30px;border-radius:8px;flex-shrink:0;display:flex;align-items:center;justify-content:center;' + (evt.type === 'zip' ? 'background:#ecfdf5;color:#059669;' : 'background:#eff6ff;color:#2563eb;') + '">' +
                    '<span class="dashicons ' + (evt.type === 'zip' ? 'dashicons-media-archive' : 'dashicons-format-image') + '" style="font-size:15px;width:15px;height:15px;"></span></div>' +
                    '<div style="min-width:0;flex:1;"><div style="font-size:12.5px;font-weight:700;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + evt.client + '</div>' +
                    '<div style="font-size:11px;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + label + ' · ' + evt.gallery + '</div></div>' +
                    '<span style="font-size:10px;color:#94a3b8;white-space:nowrap;padding-top:2px;">' + timeAgo(evt.ts) + '</span></div>';
            }).join('');
        }

        function poll() {
            if (!badge) return;
            var body = new URLSearchParams({ action: 'studio_poll_download_feed' });
            fetch(ajaxUrl, { method: 'POST', body: body, credentials: 'same-origin' })
                .then(function(r) { return r.json(); })
                .then(function(data) { if (data && data.success) render(data.data.events, data.data.unread); })
                .catch(function() {});
        }

        document.addEventListener('DOMContentLoaded', function() {
            var node = document.getElementById('wp-admin-bar-studio-dl-bell');
            if (node) {
                node.addEventListener('click', function(e) {
                    e.preventDefault();
                    panel.style.display = (panel.style.display === 'none') ? 'block' : 'none';
                    if (panel.style.display === 'block') {
                        poll();
                        setTimeout(function() {
                            var body = new URLSearchParams({ action: 'studio_mark_downloads_seen' });
                            fetch(ajaxUrl, { method: 'POST', body: body, credentials: 'same-origin' }).then(poll).catch(function() {});
                        }, 2000);
                    }
                });
            }
            var markBtn = document.getElementById('studio-dl-markread');
            if (markBtn) {
                markBtn.addEventListener('click', function() {
                    var body = new URLSearchParams({ action: 'studio_mark_downloads_seen' });
                    fetch(ajaxUrl, { method: 'POST', body: body, credentials: 'same-origin' }).then(poll).catch(function() {});
                });
            }
            document.addEventListener('click', function(e) {
                if (panel.style.display === 'block' && !panel.contains(e.target) && !e.target.closest('#wp-admin-bar-studio-dl-bell')) {
                    panel.style.display = 'none';
                }
            });
            poll();
            setInterval(poll, 30000);
        });
    })();
    </script>
    <?php
});


// ═══ 23. 📄 PDF EXPORT — SINGLE RECEIPTS + BULK INVOICES/RECEIPTS (WP-ADMIN) ═══
// Uses the browser's own print engine (client-side "Save as PDF" dialog) so PDFs are
// pixel-perfect with zero external libraries — the same trick as the front-end print button.

// Render the invoice/receipt document HTML for PDF export (admin only)
function studio_photography_pdf_export_document() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Unauthorized.', 'studio-photography'));
    }

    $ids_param = isset($_GET['studio_pdf_invoice_ids']) ? sanitize_text_field($_GET['studio_pdf_invoice_ids']) : '';
    $invoice_ids = array_filter(array_map('intval', explode(',', $ids_param)));

    if (empty($invoice_ids)) {
        wp_die(__('No invoices selected for PDF export.', 'studio-photography'));
    }
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title><?php _e('Invoices & Receipts Export', 'studio-photography'); ?></title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
        window.addEventListener('load', function() {
            setTimeout(function() { window.print(); }, 900); // let Tailwind CDN paint first
        });
        </script>
    </head>
    <body class="bg-slate-100">
    <div class="mx-auto max-w-3xl p-4">
        <div class="text-center text-slate-400 text-xs mb-4 no-print" style="font-family: sans-serif;">
            🖨️ The print dialog is opening — choose <strong>"Save as PDF"</strong> as the destination to download your document(s).
        </div>
        <?php foreach ($invoice_ids as $idx => $invoice_id) :
            // Pure HTML straight from the receipt builder (no JSON layer)
            $html = studio_photography_build_receipt_html(0, intval($invoice_id), false);
            if (!empty($html)) :
                ?>
                <div class="bg-white rounded-xl shadow-sm mb-8 <?php echo ($idx > 0) ? 'print-page-break' : ''; ?>">
                    <?php echo $html; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
            .print-page-break { page-break-before: always; }
        }
    </style>
    </body>
    </html>
    <?php
    exit;
}
add_action('admin_init', function() {
    if (isset($_GET['studio_pdf_invoice_ids'])) {
        studio_photography_pdf_export_document();
    }
});


// ── PDF EXPORT UI (Invoices list) ──

// 1. "Download PDF" link on every invoice row
add_filter('post_row_actions', function($actions, $post) {
    if ($post->post_type === 'invoice' && current_user_can('manage_options')) {
        $actions['studio_pdf'] = '<a href="' . esc_url(admin_url('admin.php?studio_pdf_invoice_ids=' . $post->ID)) . '" target="_blank">📄 PDF</a>';
    }
    return $actions;
}, 10, 2);

// 2. Bulk action: "Download as PDF" for checked invoices
add_filter('bulk_actions-edit-invoice', function($actions) {
    $actions['studio_bulk_pdf'] = __('Download as PDF', 'studio-photography');
    return $actions;
});

add_filter('handle_bulk_actions-edit-invoice', function($redirect, $doaction, $object_ids) {
    if ($doaction !== 'studio_bulk_pdf' || empty($object_ids)) return $redirect;
    wp_safe_redirect(admin_url('admin.php?studio_pdf_invoice_ids=' . implode(',', array_map('intval', (array) $object_ids))));
    exit;
}, 10, 3);

// 3. Header button: export EVERY invoice in one PDF
add_action('admin_notices', function() {
    global $typenow;
    if ($typenow === 'invoice' && current_user_can('manage_options')) {
        $all = get_posts(array('post_type' => 'invoice', 'numberposts' => -1, 'post_status' => 'any', 'fields' => 'ids'));
        if (!empty($all)) {
            echo '<div class="notice notice-info inline" style="padding:10px 14px;display:flex;align-items:center;gap:10px;">'
                . '<span style="font-size:15px;">📄</span><strong>' . count($all) . ' invoices:</strong>'
                . '<a class="button button-primary" target="_blank" style="margin-left:4px;" href="' . esc_url(admin_url('admin.php?studio_pdf_invoice_ids=' . implode(',', $all))) . '">Download All as One PDF</a>'
                . '</div>';
        }
    }
});


// ═══ 24. WP-ADMIN COMPLETE REDESIGN — "STUDIO COMMAND CENTER" ═══

// 24A. Replace the default dashboard home with a site-styled command center
add_action('wp_dashboard_setup', function() {
    // Remove every default widget (Quick Draft, Events, Site Health, At a Glance, Activity)
    remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
    remove_meta_box('dashboard_primary', 'dashboard', 'side');
    remove_meta_box('dashboard_site_health', 'dashboard', 'normal');
    remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
    remove_meta_box('dashboard_activity', 'dashboard', 'normal');

    wp_add_dashboard_widget('studio_command_center', 'Studio Command Center', 'studio_photography_command_center_widget');
});

function studio_photography_command_center_widget() {
    // ── Live studio stats ──
    $paid_invoices = get_posts(array('post_type' => 'invoice', 'numberposts' => -1, 'meta_key' => '_invoice_status', 'meta_value' => 'paid', 'fields' => 'ids'));
    $revenue = 0.00;
    foreach ($paid_invoices as $pid_sum) $revenue += floatval(get_post_meta($pid_sum, '_invoice_total', true));

    $unpaid_invoices = get_posts(array('post_type' => 'invoice', 'numberposts' => -1, 'meta_key' => '_invoice_status', 'meta_value' => 'unpaid', 'fields' => 'ids'));
    $receivables = 0.00;
    foreach ($unpaid_invoices as $pid_un) $receivables += floatval(get_post_meta($pid_un, '_invoice_total', true));

    $bookings = get_posts(array('post_type' => 'booking', 'numberposts' => -1, 'fields' => 'ids'));
    $galleries = get_posts(array('post_type' => 'client_gallery', 'numberposts' => -1, 'fields' => 'ids'));

    $recent_bookings = get_posts(array('post_type' => 'booking', 'numberposts' => 4, 'orderby' => 'date', 'order' => 'DESC'));
    $recent_invoices = get_posts(array('post_type' => 'invoice', 'numberposts' => 4, 'orderby' => 'date', 'order' => 'DESC'));

    $pill = 'display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:999px;font-size:12px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;text-decoration:none;transition:all .2s;';
    ?>
    <style>
        #studio_command_center { border: none !important; box-shadow: none !important; background: transparent !important; }
        #studio_command_center .hndle, #studio_command_center .postbox-header { display: none !important; }
        #studio_command_center .inside { margin: 0 !important; padding: 0 !important; }
        #dashboard-widgets .postbox-container { width: 100% !important; } /* full-width command center */
        .studio-cc-quick:hover { background: #ffffff !important; color: #0f172a !important; }
        .studio-cc-act:hover { border-color: #0f172a !important; color: #0f172a !important; }
    </style>

    <!-- HERO -->
    <div style="background:linear-gradient(135deg,#0f172a 0%,#1e293b 60%,#334155 100%);border-radius:18px;padding:30px 28px;margin:8px 0 20px;color:#fff;position:relative;overflow:hidden;">
        <div style="position:absolute;inset:0;background:radial-gradient(ellipse at top right,rgba(37,99,235,.25),transparent 55%);pointer-events:none;"></div>
        <div style="position:relative;">
            <span style="font-size:11px;letter-spacing:.3em;text-transform:uppercase;color:#94a3b8;">Studio Command Center</span>
            <h1 style="margin:8px 0 6px;font-family:Georgia,'Times New Roman',serif;font-weight:300;font-size:30px;color:#f1f5f9;letter-spacing:-.01em;"><?php echo esc_html(get_bloginfo('name')); ?></h1>
            <p style="margin:0 0 18px;color:#94a3b8;font-size:13px;">Manage bookings, invoices, galleries & deliveries — everything in one place.</p>
            <div style="display:flex;flex-wrap:wrap;gap:10px;">
                <a class="studio-cc-quick" href="<?php echo esc_url(admin_url('post-new.php?post_type=booking')); ?>" style="<?php echo $pill; ?>background:#fff;color:#0f172a;">＋ Booking</a>
                <a class="studio-cc-quick" href="<?php echo esc_url(admin_url('post-new.php?post_type=invoice')); ?>" style="<?php echo $pill; ?>background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.35);">＋ Invoice</a>
                <a class="studio-cc-quick" href="<?php echo esc_url(admin_url('post-new.php?post_type=client_gallery')); ?>" style="<?php echo $pill; ?>background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.35);">＋ Client Gallery</a>
                <a class="studio-cc-quick" href="<?php echo esc_url(admin_url('post-new.php?post_type=session')); ?>" style="<?php echo $pill; ?>background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.35);">＋ Session</a>
                <a class="studio-cc-quick" href="<?php echo esc_url(studio_photography_get_portfolio_url()); ?>" target="_blank" style="<?php echo $pill; ?>background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.35);">↗ Portfolio</a>
                <a class="studio-cc-quick" href="<?php echo esc_url(studio_photography_get_booking_url()); ?>" target="_blank" style="<?php echo $pill; ?>background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.35);">↗ Booking Page</a>
            </div>
        </div>
    </div>

    <!-- STATS -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:14px;margin-bottom:20px;">
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:18px;">
            <span style="font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:#94a3b8;font-weight:700;">Total Revenue</span>
            <div style="font-size:24px;font-weight:800;color:#0f172a;margin-top:6px;">GH₵ <?php echo number_format($revenue, 2); ?></div>
            <div style="font-size:11px;color:#16a34a;font-weight:600;margin-top:2px;"><?php echo count($paid_invoices); ?> paid invoice(s)</div>
        </div>
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:18px;">
            <span style="font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:#94a3b8;font-weight:700;">Receivables</span>
            <div style="font-size:24px;font-weight:800;color:#e11d48;margin-top:6px;">GH₵ <?php echo number_format($receivables, 2); ?></div>
            <div style="font-size:11px;color:#94a3b8;margin-top:2px;"><?php echo count($unpaid_invoices); ?> unpaid invoice(s)</div>
        </div>
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:18px;">
            <span style="font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:#94a3b8;font-weight:700;">Bookings</span>
            <div style="font-size:24px;font-weight:800;color:#0f172a;margin-top:6px;"><?php echo count($bookings); ?></div>
            <div style="font-size:11px;color:#94a3b8;margin-top:2px;">all time</div>
        </div>
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:18px;">
            <span style="font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:#94a3b8;font-weight:700;">Client Galleries</span>
            <div style="font-size:24px;font-weight:800;color:#0f172a;margin-top:6px;"><?php echo count($galleries); ?></div>
            <div style="font-size:11px;color:#94a3b8;margin-top:2px;">delivered portals</div>
        </div>
    </div>

    <!-- RECENT ACTIVITY -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:14px;">
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:18px;">
            <strong style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#94a3b8;display:block;margin-bottom:10px;">Recent Bookings</strong>
            <?php foreach ($recent_bookings as $rb) : ?>
                <a href="<?php echo esc_url(admin_url('post.php?post=' . $rb->ID . '&action=edit')); ?>" style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f1f5f9;text-decoration:none;">
                    <span style="font-weight:700;color:#0f172a;font-size:13px;"><?php echo esc_html(get_post_meta($rb->ID, '_booking_client_name', true) ?: $rb->post_title); ?></span>
                    <span style="font-size:11px;color:#64748b;"><?php echo esc_html(get_post_meta($rb->ID, '_booking_event_date', true) ?: get_the_date('M d', $rb)); ?></span>
                </a>
            <?php endforeach; ?>
            <?php if (empty($recent_bookings)) : ?><span style="color:#94a3b8;font-size:12px;">No bookings yet.</span><?php endif; ?>
        </div>
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:18px;">
            <strong style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#94a3b8;display:block;margin-bottom:10px;">Recent Invoices</strong>
            <?php foreach ($recent_invoices as $ri) : $ri_status = get_post_meta($ri->ID, '_invoice_status', true); ?>
                <a href="<?php echo esc_url(admin_url('post.php?post=' . $ri->ID . '&action=edit')); ?>" style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f1f5f9;text-decoration:none;">
                    <span style="font-weight:700;color:#0f172a;font-size:13px;"><?php echo esc_html(get_post_meta($ri->ID, '_invoice_number', true) ?: $ri->post_title); ?></span>
                    <span style="font-size:11px;font-weight:700;color:<?php echo $ri_status === 'paid' ? '#16a34a' : '#e11d48'; ?>;">GH₵ <?php echo number_format(floatval(get_post_meta($ri->ID, '_invoice_total', true)), 2); ?></span>
                </a>
            <?php endforeach; ?>
            <?php if (empty($recent_invoices)) : ?><span style="color:#94a3b8;font-size:12px;">No invoices yet.</span><?php endif; ?>
        </div>
    </div>
    <?php
}

// 24B. Extra chrome: site canvas, serif headings, list polish
add_action('admin_enqueue_scripts', function() {
    ?>
    <style>
        /* Site-like canvas */
        body.wp-admin, #wpcontent { background: #f8fafc !important; }
        #wpbody-content { padding-bottom: 40px; }
        /* Serif display headings — like the site's font-serif heroes */
        .wrap h1, .wrap h2, .postbox .hndle, h1.wp-heading-inline {
            font-family: Georgia, 'Times New Roman', serif !important;
            font-weight: 400 !important;
            letter-spacing: -0.01em !important;
        }
        /* Uppercase micro-labels on list screens */
        .wp-list-table thead th, .wp-list-table tfoot th { font-size: 10.5px !important; }
        /* List rows: soft hover, light dividers */
        .wp-list-table tbody tr:hover { background: #f8fafc !important; }
        .wp-list-table th, .wp-list-table td { border-color: #f1f5f9 !important; }
        /* Subnav (All/Published/Trash) as soft pills */
        .subsubsub a { padding: 3px 10px; border-radius: 999px; }
        .subsubsub a.current { background: #0f172a; color: #fff !important; }
        /* Nav tabs → pill style */
        .nav-tab-wrapper, .nav-tab { border-radius: 999px 999px 0 0; }
        .nav-tab-active { background: #0f172a; color: #fff !important; }
        /* Page title action buttons */
        .page-title-action { border-radius: 999px !important; border: 1px solid #cbd5e1 !important; }
        .page-title-action:hover { background: #0f172a !important; color: #fff !important; border-color: #0f172a !important; }
        /* Hide the core welcome panel — replaced by the Command Center */
        .welcome-panel { display: none !important; }
    </style>
    <?php
});

// 24C. Focus the menu on the studio workflow (blog is gone) + quick site links
add_action('admin_menu', function() {
    remove_menu_page('edit.php');        // Posts (blog removed from the site)
    remove_menu_page('edit-comments.php'); // Comments

    // "Site Links" — jump straight to the live pages
    add_menu_page(__('Site Links', 'studio-photography'), __('↗ Site Links', 'studio-photography'), 'manage_options', 'studio-site-links', function() { wp_safe_redirect(home_url('/')); exit; }, 'dashicons-external', 3);
    add_submenu_page('studio-site-links', __('Homepage', 'studio-photography'), __('Homepage', 'studio-photography'), 'manage_options', 'studio-site-links', function() { wp_safe_redirect(home_url('/')); exit; });
    add_submenu_page('studio-site-links', __('Booking Page', 'studio-photography'), __('Booking Page', 'studio-photography'), 'manage_options', 'studio-site-booking', function() { wp_safe_redirect(studio_photography_get_booking_url()); exit; });
    add_submenu_page('studio-site-links', __('Portfolio', 'studio-photography'), __('Portfolio', 'studio-photography'), 'manage_options', 'studio-site-portfolio', function() { wp_safe_redirect(studio_photography_get_portfolio_url()); exit; });
});


// ═══ 25. 🧹 ZIP-CACHE HOUSEKEEPING — keep uploads/studio-zips small ═══
// Cached gallery archives older than 13 hours are expired junk; auto-delete them so
// they never bloat the uploads folder (and host backup tools like WPvivid don't stall on them).
function studio_photography_prune_zip_cache() {
    $dir = trailingslashit(wp_upload_dir()['basedir']) . 'studio-zips';
    if (!is_dir($dir)) return;
    foreach ((array) glob($dir . '/*.zip') as $f) {
        if (is_file($f) && (time() - (int) filemtime($f)) > 13 * HOUR_IN_SECONDS) {
            @unlink($f);
        }
    }
}
add_action('admin_init', 'studio_photography_prune_zip_cache');


// ═══ 26. ⚡ RESOURCE OPTIMIZATION — NEVER HIT HOST CPU LIMITS ═══

// 26A. GUEST PAGE-CACHE — anonymous visitors get served a cached copy of the heavy
// public pages (homepage, booking, portfolio) instead of rebuilding them per visit.
// Logged-in admins always see live pages. Any content save instantly rotates the cache.
function studio_photography_guest_page_cache_start() {
    if (is_admin() || is_user_logged_in()) return;
    if (defined('DOING_AJAX') || defined('DOING_CRON') || defined('REST_REQUEST')) return;
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] !== 'GET') return;
    if (!empty($_GET)) return; // query strings (e.g. ?clear_cache) always bypass

    $is_cacheable = is_front_page()
        || (function_exists('is_page_template') && (is_page_template('template-booking.php') || is_page_template('template-portfolio.php')));
    if (!$is_cacheable) return;

    $gen = (int) get_option('studio_pgcache_gen', 1);
    $key = 'studio_pgcache_' . $gen . '_' . md5(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/');

    $cached = get_transient($key);
    if (!empty($cached)) {
        echo $cached; // 🚀 full page served from cache — near-zero CPU
        exit;
    }

    ob_start(function($html) use ($key) {
        if (!empty($html) && strlen($html) > 2000) {
            set_transient($key, $html, 10 * MINUTE_IN_SECONDS);
        }
        return $html;
    });
}
add_action('template_redirect', 'studio_photography_guest_page_cache_start', 2);

// 26B. Rotate the guest cache whenever studio content changes (sessions, packages,
// portfolio, galleries, customizer saves) so edits appear instantly.
add_action('save_post', function($post_id) {
    $type = get_post_type($post_id);
    if (in_array($type, array('session', 'package', 'portfolio_gallery', 'client_gallery'), true)) {
        update_option('studio_pgcache_gen', (int) get_option('studio_pgcache_gen', 1) + 1, false);
    }
});
add_action('customize_save_after', function() {
    update_option('studio_pgcache_gen', (int) get_option('studio_pgcache_gen', 1) + 1, false);
});

// 26C. Slow the admin heartbeat (default 15s hammers admin-ajax; 60s is plenty)
add_filter('heartbeat_settings', function($settings) {
    $settings['interval'] = 60;
    return $settings;
});

// 26D. Front-end trims — drop emoji scripts & version generator from public pages
add_action('init', function() {
    if (is_admin()) return;
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'rsd_link');
});
