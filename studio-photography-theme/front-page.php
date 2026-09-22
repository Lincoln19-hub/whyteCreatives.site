<?php
get_header();

// Paths to default placeholder images inside theme assets (high-resolution premium photography CDNs)
$default_hero_img = 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=1200';
$default_about_img = 'https://images.unsplash.com/photo-1554080353-a576cf803bda?auto=format&fit=crop&q=80&w=1000';

// Use bulletproof booking page URL finder
$booking_url = studio_photography_get_booking_url();

// ── 1. FETCH HERO CUSTOMIZER SETTINGS ──
$hero_pre = get_theme_mod('studio_hero_pre', 'Capturing Moments, Creating Memories');
$hero_title = get_theme_mod('studio_hero_title', 'Timeless Photography for Your Most Important Days');
$hero_desc = get_theme_mod('studio_hero_desc', "We specialize in capturing the essence of life's most precious moments with elegance, artistry, and attention to detail. From intimate portraits to grand celebrations, every frame tells your story.");
$hero_btn1_text = get_theme_mod('studio_hero_btn1_text', 'Book a Session');
$hero_btn2_text = get_theme_mod('studio_hero_btn2_text', 'View My Work');
$hero_btn2_link = get_theme_mod('studio_hero_btn2_link', '#gallery');
$custom_hero_img = get_theme_mod('studio_hero_image');
$hero_img = !empty($custom_hero_img) ? $custom_hero_img : $default_hero_img;

// ── 2. FETCH ABOUT CUSTOMIZER SETTINGS ──
$about_title = get_theme_mod('studio_about_title', 'Where Art Meets Authenticity');
$about_desc_raw = get_theme_mod('studio_about_description', "With over 15 years of experience in professional photography, we've dedicated ourselves to capturing the authentic moments that matter most. Our approach combines technical expertise with artistic direction to create images that transcend time.\n\nEvery project is treated as a unique collaboration. We listen to your vision, understand your story, and deliver photographs that exceed expectations.");
$custom_about_img = get_theme_mod('studio_about_image');
$about_img = !empty($custom_about_img) ? $custom_about_img : $default_about_img;
$about_points_raw = get_theme_mod('studio_about_points', "Award-winning photography team\nState-of-the-art equipment & editing\nFast turnaround with online gallery\nPersonalized consultation for every client");
$about_points = array_filter(array_map('trim', explode("\n", $about_points_raw)));

// ── 3. FETCH CTA CUSTOMIZER SETTINGS ──
$cta_pre = get_theme_mod('studio_cta_pre', 'Ready to Capture Your Story?');
$cta_title = get_theme_mod('studio_cta_title', "Let's Create Something Beautiful");
$cta_desc = get_theme_mod('studio_cta_desc', 'Book a consultation with our team today and let us bring your vision to life.');
$cta_btn1_text = get_theme_mod('studio_cta_btn1_text', 'Book a Session');
$cta_btn2_text = get_theme_mod('studio_cta_btn2_text', 'View Portfolio');
$cta_btn2_link = get_theme_mod('studio_cta_btn2_link', '#gallery');


// Get active sessions query for Services & Gallery (bulletproof sorting)
$sessions_query = new WP_Query(array(
    'post_type' => 'session',
    'posts_per_page' => 6,
    'orderby' => 'ID',
    'order' => 'ASC',
    'meta_query' => array(
        array(
            'key' => '_session_active',
            'value' => 'yes',
            'compare' => '='
        )
    )
));
?>

<div class="min-h-screen">
    <!-- Hero Section -->
    <section class="relative overflow-hidden pt-28 pb-16 md:pt-36 md:pb-24 bg-white">
        <div class="mx-auto max-w-7xl px-6">
            <div class="grid items-center gap-12 md:grid-cols-[1fr_0.9fr] md:gap-16">
                <div>
                    <?php if (!empty($hero_pre)) : ?>
                        <div class="mb-4 inline-flex items-center gap-2 text-sm font-medium text-blue-600">
                            <i data-lucide="sparkles" class="h-4 w-4"></i>
                            <?php echo esc_html($hero_pre); ?>
                        </div>
                    <?php endif; ?>
                    
                    <h1 class="text-5xl font-bold leading-[1.1] tracking-tight text-gray-900 md:text-6xl lg:text-[4.5rem]">
                        <?php echo wp_kses_post(nl2br($hero_title)); ?>
                    </h1>
                    
                    <p class="mt-5 max-w-lg text-lg leading-relaxed text-gray-500">
                        <?php echo esc_html($hero_desc); ?>
                    </p>
                    
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="<?php echo esc_url($booking_url); ?>" class="btn btn-primary btn-lg"><?php echo esc_html($hero_btn1_text); ?></a>
                        <a href="<?php echo esc_url($hero_btn2_link); ?>" class="btn btn-outline btn-lg"><?php echo esc_html($hero_btn2_text); ?></a>
                    </div>
                </div>
                <div class="relative">
                    <div class="overflow-hidden rounded-2xl shadow-2xl">
                        <img
                            src="<?php echo esc_url($hero_img); ?>"
                            alt="Professional portrait photography"
                            class="aspect-[3/4] w-full object-cover"
                        />
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-24 bg-white border-t border-gray-100">
        <div class="mx-auto max-w-7xl px-6">
            <div class="mx-auto mb-14 max-w-xl text-center">
                <div class="mb-3 inline-flex items-center gap-2 text-sm font-medium text-blue-600">
                    <i data-lucide="calendar" class="h-4 w-4"></i>
                    Our Services
                </div>
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 md:text-4xl">
                    Tailored Photography Experiences
                </h2>
            </div>
            
            <div class="grid gap-6 md:grid-cols-3">
                <?php
                if ($sessions_query->have_posts()) :
                    while ($sessions_query->have_posts()) : $sessions_query->the_post();
                        $category = get_post_meta(get_the_ID(), '_session_category', true);
                        $icon = 'camera';
                        if ($category == 'Wedding') $icon = 'heart';
                        elseif ($category == 'Corporate' || $category == 'Commercial') $icon = 'briefcase';
                        elseif ($category == 'Graduation') $icon = 'graduation-cap';
                        ?>
                        <div class="group rounded-xl border border-gray-100 bg-white p-8 transition-all hover:border-gray-200 hover:shadow-lg">
                            <div class="mb-5 flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                                <i data-lucide="<?php echo esc_attr($icon); ?>" class="h-5 w-5"></i>
                            </div>
                            <h3 class="mb-2 text-lg font-semibold text-gray-900"><?php the_title(); ?></h3>
                            <p class="mb-4 text-sm leading-relaxed text-gray-500"><?php echo esc_html(wp_trim_words(get_the_content(), 15)); ?></p>
                            <a href="<?php echo esc_url($booking_url); ?>" class="text-sm font-medium text-blue-600 hover:text-blue-800 flex items-center gap-1">
                                Book Now <span class="transition-transform group-hover:translate-x-1">&rarr;</span>
                            </a>
                        </div>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                else :
                    // Default fallback services if database isn't seeded yet
                    $default_services = array(
                        array('title' => 'Weddings', 'desc' => 'Celebrate your love story with timeless wedding photography that captures every precious moment.', 'icon' => 'heart'),
                        array('title' => 'Portraits', 'desc' => 'Professional portraits that showcase your personality and elegance with artistic direction.', 'icon' => 'camera'),
                        array('title' => 'Events', 'desc' => 'Corporate events, celebrations, and special occasions documented with style and professionalism.', 'icon' => 'calendar')
                    );
                    foreach ($default_services as $service) :
                        ?>
                        <div class="group rounded-xl border border-gray-100 bg-white p-8 transition-all hover:border-gray-200 hover:shadow-lg">
                            <div class="mb-5 flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                                <i data-lucide="<?php echo esc_attr($service['icon']); ?>" class="h-5 w-5"></i>
                            </div>
                            <h3 class="mb-2 text-lg font-semibold text-gray-900"><?php echo esc_html($service['title']); ?></h3>
                            <p class="mb-4 text-sm leading-relaxed text-gray-500"><?php echo esc_html($service['desc']); ?></p>
                            <a href="<?php echo esc_url($booking_url); ?>" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                                Book Now &rarr;
                            </a>
                        </div>
                        <?php
                    endforeach;
                endif;
                ?>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="border-y border-gray-100 bg-gray-50 py-24">
        <div class="mx-auto grid max-w-7xl items-center gap-12 px-6 md:grid-cols-2 md:gap-20">
            <div class="overflow-hidden rounded-2xl shadow-xl">
                <img
                    src="<?php echo esc_url($about_img); ?>"
                    alt="Photography in action"
                    class="aspect-[4/5] w-full object-cover"
                />
            </div>
            <div>
                <div class="mb-3 inline-flex items-center gap-2 text-sm font-medium text-blue-600">
                    <i data-lucide="clock" class="h-4 w-4"></i>
                    About the Studio
                </div>
                <h2 class="mb-5 text-3xl font-bold tracking-tight text-gray-900 md:text-4xl">
                    <?php echo esc_html($about_title); ?>
                </h2>
                <div class="mb-8 text-base leading-relaxed text-gray-500 space-y-4">
                    <?php echo wp_kses_post(nl2br($about_desc_raw)); ?>
                </div>
                
                <?php if (!empty($about_points)) : ?>
                    <ul class="mb-8 space-y-3">
                        <?php foreach ($about_points as $point) : ?>
                            <li class="flex items-start gap-2.5 text-sm text-gray-600">
                                <span class="mt-0.5 flex h-4 w-4 flex-shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                                    <svg class="h-2.5 w-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                <?php echo esc_html($point); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <a href="<?php echo esc_url($booking_url); ?>" class="btn btn-primary">Start Your Project</a>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section id="gallery" class="py-24 bg-white">
        <div class="mx-auto max-w-7xl px-6">
            <div class="mx-auto mb-10 max-w-xl text-center">
                <div class="mb-3 inline-flex items-center gap-2 text-sm font-medium text-blue-600">
                    <i data-lucide="camera" class="h-4 w-4"></i>
                    Our Gallery
                </div>
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 md:text-4xl">Explore Our Latest Work</h2>
            </div>
            
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <?php
                // Get session posts to populate gallery with their images, or use default images (high-resolution premium photography CDNs)
                $gallery_items = array(
                    array('label' => 'Outdoor Portrait', 'pos' => 'center', 'image' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=1000', 'images' => array('https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=1000')),
                    array('label' => 'Studio Portrait', 'pos' => 'top', 'image' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&q=80&w=1000', 'images' => array('https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&q=80&w=1000')),
                    array('label' => 'Wedding Celebration', 'pos' => 'bottom', 'image' => 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&q=80&w=1000', 'images' => array('https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&q=80&w=1000')),
                    array('label' => 'Timeless Session', 'pos' => 'center', 'image' => 'https://images.unsplash.com/photo-1509631179647-0177331693ae?auto=format&fit=crop&q=80&w=1000', 'images' => array('https://images.unsplash.com/photo-1509631179647-0177331693ae?auto=format&fit=crop&q=80&w=1000')),
                );

                $gallery_query = new WP_Query(array(
                    'post_type' => 'session',
                    'posts_per_page' => 4,
                    'orderby' => 'ID',
                    'order' => 'ASC',
                    'meta_query' => array(
                        array(
                            'key' => '_session_active',
                            'value' => 'yes',
                            'compare' => '='
                        )
                    )
                ));

                $index = 0;
                if ($gallery_query->have_posts()) {
                    $gallery_items = array(); // reset default placeholders to use active sessions
                    while ($gallery_query->have_posts()) {
                        $gallery_query->the_post();
                        $session_id = get_the_ID();
                        
                        // 1. Fetch images from our custom visual Gallery metabox first!
                        $gallery_ids_raw = get_post_meta($session_id, '_session_gallery_ids', true);
                        $gallery_ids = !empty($gallery_ids_raw) ? array_filter(array_map('intval', explode(',', $gallery_ids_raw))) : array();
                        
                        $images_list = array();
                        if (!empty($gallery_ids)) {
                            foreach ($gallery_ids as $img_id) {
                                $img_url = wp_get_attachment_image_url($img_id, 'large');
                                if ($img_url) {
                                    $images_list[] = $img_url;
                                }
                                if (count($images_list) >= 10) break;
                            }
                        }
                        
                        // 2. Fall back to all attached media if the custom uploader is empty!
                        if (empty($images_list)) {
                            $attached_media = get_attached_media('image', $session_id);
                            if (!empty($attached_media)) {
                                foreach ($attached_media as $media_id => $media_post) {
                                    $images_list[] = wp_get_attachment_image_url($media_id, 'large');
                                    if (count($images_list) >= 10) break;
                                }
                            }
                        }
                        
                        $featured_img = get_the_post_thumbnail_url($session_id, 'large');
                        if (empty($featured_img)) {
                            // Pasted cover URL — Google Drive share links are converted automatically!
                            $featured_img = studio_photography_media_url(get_post_meta($session_id, '_session_image', true), 1000);
                        }
                        
                        // Ensure featured image is the cover first!
                        if (!empty($featured_img) && !in_array($featured_img, $images_list)) {
                            array_unshift($images_list, $featured_img);
                        }

                        // 📂 Extra slideshow images (Session Options → "Google Drive Folder / Image URLs")
                        // Paste a whole Drive FOLDER link → every image inside is pulled automatically!
                        $extra_urls_raw = get_post_meta($session_id, '_session_image_urls', true);
                        if (!empty($extra_urls_raw)) {
                            $extra_list = array();
                            $bypass_cache = current_user_can('manage_options') || isset($_GET['clear_cache']);
                            foreach (array_filter(array_map('trim', explode("\n", str_replace("\r", "", $extra_urls_raw)))) as $extra_line) {
                                $is_folder = (strpos($extra_line, 'drive.google.com') !== false && strpos($extra_line, '/folders/') !== false);
                                if ($is_folder) {
                                    // Whole-folder expansion (admins bypass the cache for instant refresh)
                                    if ($bypass_cache && preg_match('/\/folders\/([a-zA-Z0-9_-]+)/', $extra_line, $fpm)) {
                                        delete_transient('studio_folder_imgs_' . $fpm[1]);
                                    }
                                    foreach (studio_photography_get_drive_folder_images($extra_line) as $ff) {
                                        $u = studio_photography_sized_image_url($ff['url'], 1000);
                                        if (!empty($u) && !in_array($u, $images_list) && !in_array($u, $extra_list)) {
                                            $extra_list[] = $u;
                                        }
                                    }
                                } else {
                                    $extra_converted = studio_photography_media_url($extra_line, 1000);
                                    if (!empty($extra_converted) && !in_array($extra_converted, $images_list) && !in_array($extra_converted, $extra_list)) {
                                        $extra_list[] = $extra_converted;
                                    }
                                }
                            }
                            // Slideshow order: cover first, then folder/URL images, then uploaded gallery images
                            $images_list = array_merge(array_slice($images_list, 0, 1), $extra_list, array_slice($images_list, 1));
                        }
                        if (count($images_list) > 10) {
                            $images_list = array_slice($images_list, 0, 10);
                        }
                        
                        if (empty($images_list) && !empty($featured_img)) {
                            $images_list[] = $featured_img;
                        }
                        
                        if (empty($images_list)) {
                            $default_photos = array(
                                'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=1000',
                                'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&q=80&w=1000',
                                'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&q=80&w=1000',
                                'https://images.unsplash.com/photo-1509631179647-0177331693ae?auto=format&fit=crop&q=80&w=1000'
                            );
                            $images_list[] = $default_photos[$index % 4];
                        }
                        
                        $gallery_items[] = array(
                            'label' => get_the_title(),
                            'image' => $images_list[0],
                            'images' => $images_list,
                            'pos' => studio_photography_get_session_focal($session_id)
                        );
                        $index++;
                        if ($index >= 4) break;
                    }
                    wp_reset_postdata();
                }

                foreach ($gallery_items as $idx => $item) :
                    $json_images = !empty($item['images']) ? $item['images'] : array($item['image']);
                    ?>
                    <div 
                        class="group relative aspect-[3/4] overflow-hidden rounded-xl bg-gray-100 shadow-sm cursor-pointer homepage-gallery-card"
                        data-index="<?php echo $idx; ?>"
                        data-label="<?php echo esc_attr($item['label']); ?>"
                        data-images='<?php echo json_encode($json_images); ?>'
                    >
                        <img
                            src="<?php echo esc_url($item['image']); ?>"
                            alt="<?php echo esc_attr($item['label']); ?>"
                            class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                            style="object-position: <?php echo esc_attr($item['pos']); ?>;"
                        />
                        <div class="absolute inset-0 flex items-end bg-gradient-to-t from-black/60 via-transparent opacity-0 transition-opacity group-hover:opacity-100">
                            <div class="p-5 flex flex-col">
                                <span class="text-sm font-medium text-white"><?php echo esc_html($item['label']); ?></span>
                                <span class="text-xs text-white/70 font-light mt-1 flex items-center gap-1">
                                    <i data-lucide="images" class="h-3 w-3 inline text-white/80"></i>
                                    <?php echo count($json_images); ?> Photo<?php echo count($json_images) !== 1 ? 's' : ''; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php
                endforeach;
                ?>
            </div>
        </div>
        
        <!-- Immersive Homepage Lightbox Slider -->
        <div id="homepage-lightbox" class="fixed inset-0 z-50 bg-black flex flex-col justify-between p-4 opacity-0 pointer-events-none transition-opacity duration-300 select-none">
            <!-- Header -->
            <div class="flex items-center justify-between text-white p-2 sm:px-6">
                <span id="hp-lightbox-counter" class="text-sm font-light tracking-widest">1 of 12</span>
                <button id="hp-lightbox-close" class="hover:text-red-400 transition-colors" title="Close Slideshow">
                    <i data-lucide="x" class="h-6 w-6"></i>
                </button>
            </div>
            
            <!-- Center Area -->
            <div class="flex-1 flex items-center justify-center relative px-2 sm:px-12">
                <button id="hp-lightbox-prev" class="absolute left-2 sm:left-6 text-white/50 hover:text-white bg-white/5 hover:bg-white/15 h-12 w-12 rounded-full flex items-center justify-center transition-all" title="Previous Image">
                    <i data-lucide="chevron-left" class="h-6 w-6"></i>
                </button>
                
                <img id="hp-lightbox-img" src="" alt="Gallery Preview" class="max-h-[82vh] max-w-full object-contain rounded-sm shadow-2xl transition-all duration-300">
                
                <button id="hp-lightbox-next" class="absolute right-2 sm:right-6 text-white/50 hover:text-white bg-white/5 hover:bg-white/15 h-12 w-12 rounded-full flex items-center justify-center transition-all" title="Next Image">
                    <i data-lucide="chevron-right" class="h-6 w-6"></i>
                </button>
            </div>
            
            <!-- Footer -->
            <div id="hp-lightbox-title" class="text-center text-white/70 text-sm font-light py-4 tracking-wide truncate max-w-xl mx-auto">
                Session Name
            </div>
        </div>

        <!-- Homepage Lightbox JS Controller -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const hpCards = document.querySelectorAll('.homepage-gallery-card');
                if (hpCards.length === 0) return;

                const hpLightbox = document.getElementById('homepage-lightbox');
                const hpImg = document.getElementById('hp-lightbox-img');
                const hpCounter = document.getElementById('hp-lightbox-counter');
                const hpTitle = document.getElementById('hp-lightbox-title');
                const hpClose = document.getElementById('hp-lightbox-close');
                const hpPrev = document.getElementById('hp-lightbox-prev');
                const hpNext = document.getElementById('hp-lightbox-next');

                let activeImages = [];
                let activeTitle = "";
                let activeIndex = 0;

                function openHpLightbox(card) {
                    try {
                        activeImages = JSON.parse(card.getAttribute('data-images'));
                        activeTitle = card.getAttribute('data-label');
                        activeIndex = 0;

                        if (!activeImages || activeImages.length === 0) return;

                        updateHpLightboxContent();
                        hpLightbox.classList.remove('pointer-events-none', 'opacity-0');
                        hpLightbox.classList.add('opacity-100');
                        document.body.style.overflow = 'hidden'; // Lock background scrolling
                    } catch(e) {
                        console.error("Failed to load session gallery images:", e);
                    }
                }

                function closeHpLightbox() {
                    hpLightbox.classList.add('pointer-events-none', 'opacity-0');
                    hpLightbox.classList.remove('opacity-100');
                    document.body.style.overflow = ''; // Unlock background scrolling
                }

                function updateHpLightboxContent() {
                    hpImg.classList.add('scale-95', 'opacity-50');
                    
                    setTimeout(() => {
                        hpImg.src = activeImages[activeIndex];
                        hpImg.alt = `${activeTitle} - Photo ${activeIndex + 1}`;
                        hpCounter.textContent = `${activeIndex + 1} of ${activeImages.length}`;
                        hpTitle.textContent = `${activeTitle} • Photo ${activeIndex + 1}`;
                        hpImg.classList.remove('scale-95', 'opacity-50');
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }, 100);
                }

                function nextHpImage() {
                    if (activeImages.length <= 1) return;
                    activeIndex = (activeIndex + 1) % activeImages.length;
                    updateHpLightboxContent();
                }

                function prevHpImage() {
                    if (activeImages.length <= 1) return;
                    activeIndex = (activeIndex - 1 + activeImages.length) % activeImages.length;
                    updateHpLightboxContent();
                }

                // Bind cards to open lightbox
                hpCards.forEach(card => {
                    card.addEventListener('click', () => openHpLightbox(card));
                });

                // Lightbox Controls
                hpClose.addEventListener('click', closeHpLightbox);
                hpNext.addEventListener('click', nextHpImage);
                hpPrev.addEventListener('click', prevHpImage);

                // Key Bindings
                document.addEventListener('keydown', function(e) {
                    if (hpLightbox.classList.contains('opacity-100')) {
                        if (e.key === 'ArrowRight') nextHpImage();
                        else if (e.key === 'ArrowLeft') prevHpImage();
                        else if (e.key === 'Escape') closeHpLightbox();
                    }
                });

                // Swipe Gestures for Mobile
                let hpStartX = 0;
                hpLightbox.addEventListener('touchstart', e => { hpStartX = e.touches[0].clientX; }, {passive: true});
                hpLightbox.addEventListener('touchend', e => {
                    let diffX = e.changedTouches[0].clientX - hpStartX;
                    if (Math.abs(diffX) > 60) {
                        if (diffX > 0) prevHpImage(); // Swipe Right
                        else nextHpImage(); // Swipe Left
                    }
                }, {passive: true});
            });
        </script>
    </section>


    <!-- CTA Section -->
    <section class="border-y border-gray-100 bg-gray-50 py-24">
        <div class="mx-auto max-w-2xl px-6 text-center">
            <?php if (!empty($cta_pre)) : ?>
                <div class="mb-3 inline-flex items-center gap-2 text-sm font-medium text-blue-600">
                    <i data-lucide="sparkles" class="h-4 w-4"></i>
                    <?php echo esc_html($cta_pre); ?>
                </div>
            <?php endif; ?>
            
            <h2 class="mb-4 text-3xl font-bold tracking-tight text-gray-900 md:text-4xl">
                <?php echo esc_html($cta_title); ?>
            </h2>
            
            <p class="mb-8 text-lg text-gray-500">
                <?php echo esc_html($cta_desc); ?>
            </p>
            
            <div class="flex justify-center gap-3">
                <a href="<?php echo esc_url($booking_url); ?>" class="btn btn-primary btn-lg"><?php echo esc_html($cta_btn1_text); ?></a>
                <a href="<?php echo esc_url($cta_btn2_link); ?>" class="btn btn-outline btn-lg"><?php echo esc_html($cta_btn2_text); ?></a>
            </div>
        </div>
    </section>
</div>

<?php
get_footer();
