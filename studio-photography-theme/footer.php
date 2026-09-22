    <!-- Footer Section -->
    <?php
    // Fetch customizer options with fallbacks
    $footer_desc = get_theme_mod('studio_footer_brand_desc', "Capturing life's most precious moments with elegance, artistry, and attention to detail.");
    $footer_col1 = get_theme_mod('studio_footer_col1_title', 'Navigate');
    $footer_col2 = get_theme_mod('studio_footer_col2_title', 'Services');
    $footer_copyright = get_theme_mod('studio_footer_copyright', 'All rights reserved.');
    ?>
    <footer class="py-12 bg-white border-t border-gray-100">
        <div class="mx-auto max-w-7xl px-6">
            <div class="grid gap-10 md:grid-cols-3 text-left">
                <!-- Brand Column -->
                <div class="md:col-span-1">
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="mb-3 flex items-center gap-2.5">
                        <?php 
                        if (has_custom_logo()) {
                            the_custom_logo();
                        } else {
                            ?>
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50">
                                <i data-lucide="camera" class="h-4 w-4 text-blue-600"></i>
                            </div>
                            <span class="text-lg font-semibold"><?php bloginfo('name'); ?></span>
                            <?php
                        }
                        ?>
                    </a>
                    <p class="max-w-xs text-sm text-gray-500 leading-relaxed">
                        <?php echo esc_html($footer_desc); ?>
                    </p>
                </div>
                
                <!-- Navigate Column -->
                <div>
                    <h4 class="mb-4 text-xs font-semibold uppercase tracking-wider text-gray-900"><?php echo esc_html($footer_col1); ?></h4>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="<?php echo esc_url(home_url('/#about')); ?>" class="text-gray-500 hover:text-slate-900">About</a></li>
                        <li><a href="<?php echo esc_url(home_url('/#services')); ?>" class="text-gray-500 hover:text-slate-900">Services</a></li>
                        <li><a href="<?php echo esc_url(home_url('/#gallery')); ?>" class="text-gray-500 hover:text-slate-900">Gallery</a></li>
                        <?php 
                        $booking_url = studio_photography_get_booking_url();
                        ?>
                        <li><a href="<?php echo esc_url($booking_url); ?>" class="text-gray-500 hover:text-slate-900 font-medium text-blue-600 hover:text-blue-800">Book Now &rarr;</a></li>
                    </ul>
                </div>

                <!-- Services Column -->
                <div>
                    <h4 class="mb-4 text-xs font-semibold uppercase tracking-wider text-gray-900"><?php echo esc_html($footer_col2); ?></h4>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="<?php echo esc_url($booking_url); ?>" class="text-gray-500 hover:text-slate-900">Weddings</a></li>
                        <li><a href="<?php echo esc_url($booking_url); ?>" class="text-gray-500 hover:text-slate-900">Portraits</a></li>
                        <li><a href="<?php echo esc_url($booking_url); ?>" class="text-gray-500 hover:text-slate-900">Events</a></li>
                        <li><a href="<?php echo esc_url($booking_url); ?>" class="text-gray-500 hover:text-slate-900">Commercial</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="mt-10 border-t border-gray-100 pt-6 text-center text-sm text-gray-400">
                &copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. <?php echo esc_html($footer_copyright); ?>
            </div>
        </div>
    </footer>

    <?php wp_footer(); ?>

    <script>
        // Initialize lucide icons on load
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // Automatically repair local development links and media paths on static deployments (e.g. Vercel / Netlify)
        (function() {
            const isStaticDeploy = !window.location.hostname.includes('.local') && 
                                   !window.location.hostname.includes('localhost') && 
                                   !window.location.hostname.includes('127.0.0.1');
            
            if (isStaticDeploy) {
                // 1. Repair Navigation & Button Links
                document.querySelectorAll('a').forEach(function(link) {
                    const href = link.getAttribute('href');
                    if (href && (href.includes('.local') || href.includes('localhost') || href.includes('127.0.0.1'))) {
                        try {
                            const urlObj = new URL(href);
                            let pathname = urlObj.pathname;
                            
                            // Convert folders to direct index.html paths to prevent server 404s on direct static page loading!
                            if (pathname.endsWith('/') && pathname !== '/') {
                                pathname += 'index.html';
                            } else if (!pathname.endsWith('/') && pathname !== '/' && !pathname.includes('.')) {
                                pathname += '/index.html';
                            }
                            
                            link.setAttribute('href', pathname + urlObj.hash + urlObj.search);
                        } catch (e) {
                            const cleanHref = href.replace(/^https?:\/\/[^\/]+/, '');
                            link.setAttribute('href', cleanHref);
                        }
                    }
                });

                // 2. Repair Image & Graphics Media Links
                document.querySelectorAll('img').forEach(function(img) {
                    const src = img.getAttribute('src');
                    if (src && (src.includes('.local') || src.includes('localhost') || src.includes('127.0.0.1'))) {
                        const cleanSrc = src.replace(/^https?:\/\/[^\/]+/, '');
                        img.setAttribute('src', cleanSrc);
                    }
                });
            }
        })();
    </script>
</body>
</html>
