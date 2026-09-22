<?php
/**
 * Single Template for Portfolio Gallery CPT — public sample collection
 * pulled live from a public Google Drive folder. Includes a cinematic hero,
 * masonry grid and an immersive lightbox viewer.
 *
 * @package Studio_Photography
 */
get_header();

$is_admin_preview = current_user_can('manage_options');

while (have_posts()) : the_post();
    $pid = get_the_ID();

    $photos = studio_photography_get_portfolio_photos($pid, $is_admin_preview || isset($_GET['clear_cache']));
    $terms = wp_get_post_terms($pid, 'portfolio_category');
    $category_name = (!is_wp_error($terms) && !empty($terms)) ? $terms[0]->name : '';
    $portfolio_home = studio_photography_get_portfolio_url();

    // Cover: manual cover URL → featured image → first Drive photo → fallback
    $cover = studio_photography_media_url(get_post_meta($pid, '_portfolio_cover_url', true), 1600);
    if (empty($cover)) $cover = get_the_post_thumbnail_url($pid, 'full');
    if (empty($cover) && !empty($photos)) $cover = studio_photography_sized_image_url(!empty($photos[0]['thumbnail']) ? $photos[0]['thumbnail'] : $photos[0]['url'], 1200);
    if (empty($cover)) $cover = 'https://images.unsplash.com/photo-1492691527719-9d1e07e534b4?auto=format&fit=crop&q=80&w=2000';

    // Lightbox data (view-only: portfolio samples are not downloadable)
    $lightbox_data = array();
    foreach ($photos as $p) {
        $lightbox_data[] = array('url' => isset($p['url']) ? $p['url'] : $p['thumbnail'], 'title' => $p['title']);
    }
    ?>

    <!-- 1. Cinematic Hero -->
    <section class="relative h-[75vh] w-full overflow-hidden flex items-end justify-center bg-black">
        <img src="<?php echo esc_url($cover); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" class="absolute inset-0 h-full w-full object-cover opacity-85 select-none" fetchpriority="high">
        <div class="absolute inset-0 bg-gradient-to-b from-black/30 via-black/10 to-black/80"></div>

        <div class="relative z-10 text-center text-white pb-16 px-6 max-w-4xl">
            <?php if (!empty($category_name)) : ?>
                <span class="text-[11px] font-bold uppercase tracking-[0.3em] text-white/80 bg-white/10 backdrop-blur px-4 py-1.5 rounded-full border border-white/20"><?php echo esc_html($category_name); ?></span>
            <?php else : ?>
                <span class="text-xs uppercase tracking-[0.3em] font-light text-slate-200">Portfolio Collection</span>
            <?php endif; ?>
            <h1 class="mt-4 text-4xl sm:text-6xl font-light tracking-tight font-serif text-slate-100"><?php the_title(); ?></h1>
            <div class="mt-4 flex items-center justify-center gap-2 text-sm text-slate-300 font-light">
                <?php if (!empty($lightbox_data)) : ?>
                    <span><?php echo count($lightbox_data); ?> Photos</span>
                    <span>•</span>
                <?php endif; ?>
                <span>Sample Work</span>
            </div>

            <a href="#portfolio-collection" class="mt-8 inline-flex h-11 px-6 items-center justify-center rounded-full border border-white/40 bg-white/10 hover:bg-white hover:text-black transition-all gap-2 text-xs uppercase tracking-widest backdrop-blur-xs font-semibold">
                View Collection
                <i data-lucide="chevron-down" class="h-4 w-4 animate-bounce"></i>
            </a>
        </div>
    </section>

    <!-- 2. Back to portfolio + masonry grid -->
    <section id="portfolio-collection" class="py-10 bg-white min-h-[50vh]">
        <div class="mx-auto max-w-7xl px-4 md:px-6">
            <a href="<?php echo esc_url($portfolio_home); ?>" class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-slate-500 hover:text-slate-900 transition-colors mb-8">
                <i data-lucide="arrow-left" class="h-4 w-4"></i> All Collections
            </a>

            <?php if (!empty($photos)) : ?>
                <div class="columns-2 md:columns-3 lg:columns-4 gap-2 md:gap-3">
                    <?php foreach ($photos as $index => $item) : ?>
                        <div class="break-inside-avoid mb-2 md:mb-3 relative overflow-hidden rounded-lg bg-slate-50 shadow-xs transition-all hover:shadow-lg group">
                            <img
                                src="<?php echo esc_url(!empty($item['thumbnail']) ? $item['thumbnail'] : $item['url']); ?>"
                                alt="<?php echo esc_attr($item['title']); ?>"
                                loading="lazy" decoding="async"
                                class="w-full h-auto object-cover cursor-pointer select-none pf-trigger-lightbox transition-transform duration-500 group-hover:scale-105"
                                data-index="<?php echo $index; ?>"
                            >
                            <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-4 pointer-events-none">
                                <span class="text-white text-xs font-light tracking-wide truncate"><?php echo esc_html(wp_trim_words($item['title'], 5)); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="max-w-md mx-auto p-12 text-center bg-slate-50 border border-dashed border-slate-200 rounded-2xl my-8">
                    <i data-lucide="image" class="mx-auto h-12 w-12 text-slate-300 mb-3"></i>
                    <h3 class="text-lg font-bold text-slate-700">No Photos Found</h3>
                    <p class="text-sm text-slate-400 mt-1 leading-relaxed">
                        This collection has no photos yet. <?php if ($is_admin_preview) : ?>Check that the Google Drive folder link is public and contains images — then reload with ?clear_cache=1.<?php else : ?>Please check back soon!<?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- 3. Immersive Lightbox -->
    <div id="pf-lightbox" class="fixed inset-0 z-50 bg-black flex flex-col justify-between p-4 opacity-0 pointer-events-none transition-opacity duration-300 select-none">
        <div class="flex items-center justify-between text-white p-2 sm:px-6">
            <span id="pf-counter" class="text-sm font-light tracking-widest">1 of 1</span>
            <button id="pf-close" class="hover:text-red-400 transition-colors" title="Close">
                <i data-lucide="x" class="h-6 w-6"></i>
            </button>
        </div>

        <div class="flex-1 flex items-center justify-center relative px-2 sm:px-12">
            <button id="pf-prev" class="absolute left-2 sm:left-6 text-white/50 hover:text-white bg-white/5 hover:bg-white/15 h-12 w-12 rounded-full flex items-center justify-center transition-all" title="Previous">
                <i data-lucide="chevron-left" class="h-6 w-6"></i>
            </button>

            <img id="pf-img" src="" alt="Portfolio Preview" class="max-h-[80vh] max-w-full object-contain rounded-sm shadow-2xl transition-all duration-300">

            <button id="pf-next" class="absolute right-2 sm:right-6 text-white/50 hover:text-white bg-white/5 hover:bg-white/15 h-12 w-12 rounded-full flex items-center justify-center transition-all" title="Next">
                <i data-lucide="chevron-right" class="h-6 w-6"></i>
            </button>
        </div>

        <div id="pf-title" class="text-center text-white/70 text-sm font-light py-4 tracking-wide truncate max-w-xl mx-auto">—</div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var data = <?php echo json_encode($lightbox_data); ?>;
            if (!data.length) return;

            var lb = document.getElementById('pf-lightbox');
            var img = document.getElementById('pf-img');
            var counter = document.getElementById('pf-counter');
            var title = document.getElementById('pf-title');
            var current = 0;

            function render() {
                var item = data[current];
                img.src = item.url;
                img.alt = item.title;
                counter.textContent = (current + 1) + ' of ' + data.length;
                title.textContent = item.title;
            }
            function openLb(i) {
                current = parseInt(i, 10) || 0;
                render();
                lb.classList.remove('pointer-events-none', 'opacity-0');
                lb.classList.add('opacity-100');
                document.body.style.overflow = 'hidden';
            }
            function closeLb() {
                lb.classList.add('pointer-events-none', 'opacity-0');
                lb.classList.remove('opacity-100');
                document.body.style.overflow = '';
            }
            function nav(d) {
                current = (current + d + data.length) % data.length;
                render();
            }

            document.querySelectorAll('.pf-trigger-lightbox').forEach(function(el) {
                el.addEventListener('click', function() { openLb(this.getAttribute('data-index')); });
            });
            document.getElementById('pf-close').addEventListener('click', closeLb);
            document.getElementById('pf-prev').addEventListener('click', function() { nav(-1); });
            document.getElementById('pf-next').addEventListener('click', function() { nav(1); });
            lb.addEventListener('click', function(e) { if (e.target === lb) closeLb(); });
            document.addEventListener('keydown', function(e) {
                if (lb.classList.contains('pointer-events-none')) return;
                if (e.key === 'Escape') closeLb();
                if (e.key === 'ArrowLeft') nav(-1);
                if (e.key === 'ArrowRight') nav(1);
            });

            // 📱 Swipe navigation (phones & tablets)
            var tsX = 0, tsY = 0;
            lb.addEventListener('touchstart', function(e) {
                tsX = e.changedTouches[0].clientX;
                tsY = e.changedTouches[0].clientY;
            }, { passive: true });
            lb.addEventListener('touchend', function(e) {
                var dx = e.changedTouches[0].clientX - tsX;
                var dy = e.changedTouches[0].clientY - tsY;
                if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy)) {
                    nav(dx < 0 ? 1 : -1); // swipe left → next, swipe right → previous
                }
            }, { passive: true });
        });
    </script>

<?php endwhile; ?>

<?php get_footer(); ?>
