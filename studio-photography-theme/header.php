<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php wp_title('|', true, 'right'); ?><?php bloginfo('name'); ?></title>
    
    <!-- Tailwind Play CDN & configuration -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#0f172a',
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Dynamic Site Logo Positioning & Sizing CSS -->
    <?php
    $logo_margin = get_theme_mod('studio_logo_margin_left', 0);
    $logo_height = get_theme_mod('studio_logo_height', 40);
    ?>
    <style id="studio-logo-margin-css">
        .custom-logo-link {
            display: inline-flex !important;
            align-items: center !important;
            margin-left: <?php echo intval($logo_margin); ?>px !important;
        }
    </style>
    <style id="studio-logo-size-css">
        .custom-logo {
            height: <?php echo intval($logo_height); ?>px !important;
            width: auto !important;
            max-width: 100% !important;
            object-fit: contain !important;
        }
        @media (max-width: 480px) {
            .custom-logo {
                max-width: 52vw !important;
                max-height: 44px !important;
                height: auto !important;
                width: auto !important;
            }
        }
    </style>

    <!-- ⚡ Speed: pre-warm connections to the CDNs that serve this page's media -->
    <link rel="preconnect" href="https://lh3.googleusercontent.com" crossorigin>
    <link rel="dns-prefetch" href="https://lh3.googleusercontent.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdn.tailwindcss.com">
    <link rel="dns-prefetch" href="https://unpkg.com">
    <link rel="dns-prefetch" href="https://js.paystack.co">

    <!-- 📱 REFINED MOBILE TYPOGRAPHY v2 (phones only — rescales the WHOLE type ladder) -->
    <style>
    @media (max-width: 640px) {
        /* 🔍 NATIVE "75% ZOOM" LOOK — the whole theme scales from the root font-size,
           so 75% reproduces the zoomed-out look proportionally (text, spacing, buttons) */
        html { font-size: 75%; -webkit-text-size-adjust: 100%; }
        body {
            font-size: 0.9rem;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
        }
        /* Headings: fluid and tighter */
        h1 { font-size: clamp(1.55rem, 6vw, 1.9rem) !important; line-height: 1.15 !important; letter-spacing: -0.02em !important; }
        h2 { font-size: clamp(1.25rem, 5.2vw, 1.55rem) !important; line-height: 1.22 !important; letter-spacing: -0.015em !important; }
        h3 { font-size: clamp(1rem, 4vw, 1.15rem) !important; line-height: 1.32 !important; }
        h4 { font-size: 0.95rem !important; }

        /* ── The real fix: rescale Tailwind's whole utility ladder on phones ──
           (most text on the site is sized with these classes, not heading tags) */
        .text-7xl, .text-6xl, .text-5xl { font-size: clamp(1.7rem, 6.5vw, 2.05rem) !important; line-height: 1.12 !important; }
        .text-4xl { font-size: clamp(1.5rem, 6vw, 1.8rem) !important; line-height: 1.18 !important; }
        .text-3xl { font-size: clamp(1.3rem, 5.4vw, 1.55rem) !important; line-height: 1.22 !important; }
        .text-2xl { font-size: 1.15rem !important; line-height: 1.3 !important; }
        .text-xl  { font-size: 1.02rem !important; line-height: 1.38 !important; }
        .text-lg  { font-size: 0.95rem !important; line-height: 1.45 !important; }
        .text-base { font-size: 0.9rem !important; }
        .text-sm  { font-size: 0.86rem !important; }
        .text-xs  { font-size: 0.8rem !important; }

        .font-serif { letter-spacing: -0.01em; }
        p { line-height: 1.62; }
        .tracking-\[0\.3em\] { letter-spacing: 0.2em; }
    }
    @media (max-width: 380px) {
        body { font-size: 0.85rem; }
        .text-2xl { font-size: 1.08rem !important; }
        .text-xl { font-size: 0.98rem !important; }
        .text-lg { font-size: 0.92rem !important; }
    }
    /* 📱→📲 SMOOTH TRANSITION for small tablets (641–768px) — bridges the compact
       phone ladder and full desktop sizes so scaling never "jumps" at a breakpoint */
    @media (min-width: 641px) and (max-width: 768px) {
        html { font-size: 87.5%; } /* bridges the mobile 75% and desktop 100% smoothly */
        h1 { font-size: clamp(1.8rem, 4vw, 2.2rem) !important; }
        h2 { font-size: clamp(1.45rem, 3.2vw, 1.75rem) !important; }
        h3 { font-size: 1.18rem !important; }
        .text-7xl, .text-6xl, .text-5xl { font-size: clamp(2rem, 4.2vw, 2.6rem) !important; line-height: 1.12 !important; }
        .text-4xl { font-size: clamp(1.7rem, 3.5vw, 2.1rem) !important; }
        .text-3xl { font-size: clamp(1.45rem, 3.1vw, 1.8rem) !important; }
        .text-2xl { font-size: 1.28rem !important; }
        .text-xl { font-size: 1.1rem !important; }
        .text-lg { font-size: 1rem !important; }
    }
    </style>

    <?php wp_head(); ?>
</head>
<body <?php body_class('min-h-screen bg-white'); ?>>

    <?php 
    // Use the bulletproof helper URL finder
    $booking_url = studio_photography_get_booking_url();
    ?>

    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 border-b border-gray-100 bg-white/80 backdrop-blur-md">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
            <?php
            if (has_custom_logo()) {
                // Output the custom logo DIRECTLY — it brings its own <a class="custom-logo-link">.
                // (Never wrap it in another link: nested <a> tags are invalid HTML and browsers
                // break them apart, shoving the logo out of its navbar slot!)
                the_custom_logo();
            } else {
                ?>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="flex items-center gap-2.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-slate-900">
                        <i data-lucide="camera" class="h-4 w-4 text-blue-600"></i>
                    </div>
                    <span class="text-lg font-semibold"><?php bloginfo('name'); ?></span>
                </a>
                <?php
            }
            ?>
            
            <div class="hidden items-center gap-8 md:flex">
                <a href="<?php echo esc_url(home_url('/#about')); ?>" class="text-sm text-gray-600 hover:text-gray-900">About</a>
                <a href="<?php echo esc_url(home_url('/#services')); ?>" class="text-sm text-gray-600 hover:text-gray-900">Services</a>
                <a href="<?php echo esc_url(studio_photography_get_portfolio_url()); ?>" class="text-sm text-gray-600 hover:text-gray-900">Gallery</a>
            </div>

            <div class="flex items-center gap-3">
                <?php if (current_user_can('manage_options')) : ?>
                    <a href="<?php echo esc_url(admin_url()); ?>" class="hidden text-sm text-gray-600 hover:text-gray-900 md:inline-flex">Admin</a>
                <?php endif; ?>
                <a href="<?php echo esc_url($booking_url); ?>" class="btn btn-primary">Book a Session</a>

                <!-- 📱 MOBILE HAMBURGER MENU BUTTON (phones only) -->
                <button type="button" id="studio-mobile-menu-btn" aria-label="Open menu" aria-expanded="false" class="md:hidden h-10 w-10 rounded-full border border-gray-200 bg-white/70 flex items-center justify-center text-gray-700 hover:border-gray-900 transition-all">
                    <i data-lucide="menu" class="h-5 w-5 studio-ic-open"></i>
                    <i data-lucide="x" class="h-5 w-5 studio-ic-close hidden"></i>
                </button>
            </div>
        </div>

        <!-- 📱 MOBILE NAVIGATION PANEL (slide-down, phones only) -->
        <div id="studio-mobile-menu" class="hidden md:hidden border-t border-gray-100 bg-white/95 backdrop-blur-md shadow-lg">
            <div class="mx-auto max-w-7xl px-6 py-4 flex flex-col gap-1">
                <a href="<?php echo esc_url(home_url('/#about')); ?>" class="py-3 px-4 rounded-xl text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-slate-50 transition-colors">About</a>
                <a href="<?php echo esc_url(home_url('/#services')); ?>" class="py-3 px-4 rounded-xl text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-slate-50 transition-colors">Services</a>
                <a href="<?php echo esc_url(studio_photography_get_portfolio_url()); ?>" class="py-3 px-4 rounded-xl text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-slate-50 transition-colors">Gallery</a>
                <?php if (current_user_can('manage_options')) : ?>
                    <a href="<?php echo esc_url(admin_url()); ?>" class="py-3 px-4 rounded-xl text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-slate-50 transition-colors">WP-Admin</a>
                <?php endif; ?>
                <a href="<?php echo esc_url($booking_url); ?>" class="btn btn-primary mt-2 text-center">Book a Session</a>
            </div>
        </div>
        <script>
            (function() {
                var btn = document.getElementById('studio-mobile-menu-btn');
                var menu = document.getElementById('studio-mobile-menu');
                if (!btn || !menu) return;
                btn.addEventListener('click', function() {
                    var opening = menu.classList.contains('hidden');
                    menu.classList.toggle('hidden');
                    btn.setAttribute('aria-expanded', opening ? 'true' : 'false');
                    btn.setAttribute('aria-label', opening ? 'Close menu' : 'Open menu');
                    var icOpen = btn.querySelector('.studio-ic-open');
                    var icClose = btn.querySelector('.studio-ic-close');
                    if (icOpen) icOpen.classList.toggle('hidden', opening);
                    if (icClose) icClose.classList.toggle('hidden', !opening);
                });
                // Close the panel whenever a link inside it is tapped
                menu.querySelectorAll('a').forEach(function(a) {
                    a.addEventListener('click', function() { menu.classList.add('hidden'); });
                });
            })();
        </script>
    </nav>
