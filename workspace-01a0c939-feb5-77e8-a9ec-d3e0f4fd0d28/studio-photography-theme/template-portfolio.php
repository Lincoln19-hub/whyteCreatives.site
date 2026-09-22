<?php
/**
 * Template Name: Portfolio Gallery (Google Drive)
 * Public portfolio page — sample work collections pulled live from Google Drive,
 * filterable by category (Weddings, Events, Portraits...).
 *
 * @package Studio_Photography
 */

get_header();

$is_admin_preview = current_user_can('manage_options');

// Fetch all published portfolio galleries
$portfolio_query = new WP_Query(array(
    'post_type' => 'portfolio_gallery',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC',
));

// Build the card data (cover image, photo count, category)
$cards = array();
$filter_terms = array();
if ($portfolio_query->have_posts()) {
    while ($portfolio_query->have_posts()) {
        $portfolio_query->the_post();
        $pid = get_the_ID();

        $terms = wp_get_post_terms($pid, 'portfolio_category');
        $cat_name = (!is_wp_error($terms) && !empty($terms)) ? $terms[0]->name : '';
        $cat_slug = (!is_wp_error($terms) && !empty($terms)) ? $terms[0]->slug : '';

        // Photos pulled live from the linked Google Drive folder (admins bypass the cache)
        $photos = studio_photography_get_portfolio_photos($pid, $is_admin_preview || isset($_GET['clear_cache']));

        $cover = studio_photography_media_url(get_post_meta($pid, '_portfolio_cover_url', true), 800);
        if (empty($cover)) $cover = get_the_post_thumbnail_url($pid, 'large');
        if (empty($cover) && !empty($photos)) $cover = studio_photography_sized_image_url(!empty($photos[0]['thumbnail']) ? $photos[0]['thumbnail'] : $photos[0]['url'], 800);
        if (empty($cover)) $cover = 'https://images.unsplash.com/photo-1492691527719-9d1e07e534b4?auto=format&fit=crop&q=80&w=1000';

        $cards[] = array(
            'title' => get_the_title(),
            'url' => get_permalink($pid),
            'category' => $cat_name,
            'slug' => $cat_slug,
            'cover' => $cover,
            'count' => count($photos),
        );

        if ($cat_slug !== '' && !isset($filter_terms[$cat_slug])) $filter_terms[$cat_slug] = $cat_name;
    }
    wp_reset_postdata();
}
?>

<div class="pt-28 pb-20 bg-slate-50 min-h-screen">
    <div class="mx-auto max-w-7xl px-6">

        <!-- Heading -->
        <div class="text-center max-w-2xl mx-auto mb-10">
            <span class="text-xs uppercase tracking-[0.3em] font-light text-slate-400">Our Work</span>
            <h1 class="mt-3 text-4xl font-bold text-slate-900 sm:text-5xl font-serif"><?php the_title(); ?></h1>
            <p class="mt-4 text-slate-500 text-sm leading-relaxed">Browse our latest shoots by category — tap any collection to view the full gallery of sample work.</p>
        </div>

        <?php if (!empty($cards)) : ?>

        <!-- Category filter pills -->
        <div id="portfolio-filters" class="flex flex-wrap justify-center gap-2 mb-10">
            <button type="button" data-filter="all" class="filter-pill h-10 px-5 rounded-full text-xs uppercase tracking-wider font-bold border transition-all bg-slate-900 text-white border-slate-900 shadow-md">All Work</button>
            <?php foreach ($filter_terms as $slug => $name) : ?>
                <button type="button" data-filter="<?php echo esc_attr($slug); ?>" class="filter-pill h-10 px-5 rounded-full text-xs uppercase tracking-wider font-bold border transition-all bg-white text-slate-600 border-slate-200 hover:border-slate-900"><?php echo esc_html($name); ?></button>
            <?php endforeach; ?>
        </div>

        <!-- Collections grid -->
        <div id="portfolio-grid" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($cards as $card) : ?>
                <a href="<?php echo esc_url($card['url']); ?>" data-category="<?php echo esc_attr($card['slug']); ?>" class="portfolio-card group block">
                    <div class="relative overflow-hidden rounded-2xl aspect-[4/3] bg-slate-100 shadow-xs transition-all hover:shadow-xl">
                        <img src="<?php echo esc_url($card['cover']); ?>" alt="<?php echo esc_attr($card['title']); ?>" loading="lazy" decoding="async" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent opacity-80 transition-opacity group-hover:opacity-95"></div>

                        <div class="absolute top-4 left-4">
                            <?php if (!empty($card['category'])) : ?>
                                <span class="text-[10px] font-bold uppercase tracking-widest text-white bg-white/20 backdrop-blur px-3 py-1 rounded-full border border-white/30"><?php echo esc_html($card['category']); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="absolute bottom-0 left-0 right-0 p-5">
                            <h3 class="text-white font-bold text-lg leading-tight font-serif"><?php echo esc_html($card['title']); ?></h3>
                            <p class="text-white/70 text-xs mt-1 font-light">
                                <?php if ($card['count'] > 0) : ?>
                                    <?php echo intval($card['count']); ?> photos
                                <?php else : ?>
                                    View collection
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <?php else : ?>

        <!-- Empty state -->
        <div class="max-w-lg mx-auto text-center bg-white border border-dashed border-slate-200 rounded-2xl p-12">
            <i data-lucide="images" class="mx-auto h-12 w-12 text-slate-300 mb-4"></i>
            <h3 class="text-lg font-bold text-slate-700">No portfolio collections yet</h3>
            <p class="text-sm text-slate-400 mt-2 leading-relaxed">Showcase your best work! Add collections from the WordPress dashboard:</p>
            <div class="mt-5 text-xs text-slate-500 bg-slate-50 p-4 rounded-xl text-left leading-relaxed">
                <strong class="block text-slate-700 mb-1">💡 Quick Setup:</strong>
                1. Dashboard → <strong>Portfolio Galleries</strong> → Add Portfolio Gallery<br>
                2. Give it a title (e.g. "Ama & Kojo — Wedding")<br>
                3. Paste a <strong>public Google Drive folder link</strong> with the photos<br>
                4. Pick a <strong>category</strong> (Weddings, Events, Portraits...) & Publish
            </div>
        </div>

        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var pills = document.querySelectorAll('#portfolio-filters .filter-pill');
        var cards = document.querySelectorAll('#portfolio-grid .portfolio-card');
        if (!pills.length || !cards.length) return;

        var activeCls = 'filter-pill h-10 px-5 rounded-full text-xs uppercase tracking-wider font-bold border transition-all bg-slate-900 text-white border-slate-900 shadow-md';
        var idleCls  = 'filter-pill h-10 px-5 rounded-full text-xs uppercase tracking-wider font-bold border transition-all bg-white text-slate-600 border-slate-200 hover:border-slate-900';

        pills.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var f = this.getAttribute('data-filter');
                pills.forEach(function(b) { b.className = (b === btn) ? activeCls : idleCls; });
                cards.forEach(function(card) {
                    var show = (f === 'all') || (card.getAttribute('data-category') === f);
                    card.style.display = show ? '' : 'none';
                });
            });
        });
    });
</script>

<?php get_footer(); ?>
