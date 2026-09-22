<?php
/*
Template Name: Booking Page Template
*/
get_header();

// Fetch sessions & packages
$sessions_data = array();
$sessions_query = new WP_Query(array(
    'post_type' => 'session',
    'posts_per_page' => -1,
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

if ($sessions_query->have_posts()) {
    while ($sessions_query->have_posts()) {
        $sessions_query->the_post();
        $session_id = get_the_ID();
        
        $session_image = get_the_post_thumbnail_url($session_id, 'medium');
        if (empty($session_image)) {
            $session_image = studio_photography_media_url(get_post_meta($session_id, '_session_image', true), 600);
        }
        
        // Fetch first taxonomy category term dynamically
        $terms = wp_get_post_terms($session_id, 'session_category');
        $category_name = (!is_wp_error($terms) && !empty($terms)) ? $terms[0]->name : '';
        
        // Query packages for this session (bulletproof sorting)
        $packages_data = array();
        $packages_query = new WP_Query(array(
            'post_type' => 'package',
            'posts_per_page' => -1,
            'orderby' => 'ID',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key' => '_package_session_id',
                    'value' => $session_id,
                    'compare' => '='
                ),
                array(
                    'key' => '_package_active',
                    'value' => 'yes',
                    'compare' => '='
                )
            )
        ));
        
        if ($packages_query->have_posts()) {
            while ($packages_query->have_posts()) {
                $packages_query->the_post();
                $pkg_id = get_the_ID();
                
                $features_raw = get_post_meta($pkg_id, '_package_features', true);
                $features = array();
                if (!empty($features_raw)) {
                    if (is_array($features_raw)) {
                        $features = $features_raw;
                    } else {
                        $features = array_map('trim', explode("\n", str_replace("\r", "", $features_raw)));
                    }
                }
                
                $packages_data[] = array(
                    'id' => $pkg_id,
                    'name' => get_the_title(),
                    'description' => get_the_excerpt(),
                    'price' => floatval(get_post_meta($pkg_id, '_package_price', true)),
                    'duration' => get_post_meta($pkg_id, '_package_duration', true) ?: '1 hour',
                    'max_people' => intval(get_post_meta($pkg_id, '_package_max_people', true)) ?: 1,
                    'edited_photos' => intval(get_post_meta($pkg_id, '_package_edited_photos', true)) ?: 10,
                    'outfit_changes' => intval(get_post_meta($pkg_id, '_package_outfit_changes', true)) ?: 1,
                    'locations' => intval(get_post_meta($pkg_id, '_package_locations', true)) ?: 1,
                    'delivery_time' => get_post_meta($pkg_id, '_package_delivery_time', true) ?: '3 Days',
                    'deposit_percentage' => intval(get_post_meta($pkg_id, '_package_deposit_percentage', true)) ?: 50,
                    'reschedule_allowed' => get_post_meta($pkg_id, '_package_reschedule_allowed', true) === 'yes',
                    'reschedule_hours' => intval(get_post_meta($pkg_id, '_package_reschedule_hours', true)) ?: 48,
                    'features' => $features,
                );
            }
            wp_reset_postdata();
        }
        
        $sessions_data[] = array(
            'id' => $session_id,
            'name' => get_the_title(),
            'description' => get_the_excerpt() ?: get_the_content(),
            'category' => $category_name,
            'image' => $session_image ?: (get_template_directory_uri() . '/assets/images/hero-portrait.webp'),
            'focal' => get_post_meta($session_id, '_session_focal', true),
            'packages' => $packages_data
        );
    }
    wp_reset_postdata();
}

// ── FETCH BOOKING CUSTOMIZER SETTINGS (OR FALLBACK TO DEFAULTS) ──
$bk_title = get_theme_mod('studio_booking_title', 'Book Your Session');
$bk_subtitle = get_theme_mod('studio_booking_subtitle', 'Choose your photography session type and select a package');
$bk_step1 = get_theme_mod('studio_booking_step1', 'Select Session Type');
$bk_step2 = get_theme_mod('studio_booking_step2', 'Select Package');
$bk_step3 = get_theme_mod('studio_booking_step3', 'Your Booking Details');
$bk_sidebar_header = get_theme_mod('studio_booking_sidebar_header', 'Booking Summary');
$bk_sidebar_empty = get_theme_mod('studio_booking_sidebar_empty', 'Select a session and package to see your booking summary');
$bk_success_title = get_theme_mod('studio_booking_success_title', 'Booking Confirmed!');
$bk_success_msg = get_theme_mod('studio_booking_success_msg', 'Thank you! Your photography booking has been registered successfully. A team member will reach out to you shortly to finalize.');
?>

<!-- Load Paystack Inline JavaScript Library -->
<script src="https://js.paystack.co/v1/inline.js"></script>

<div class="min-h-screen bg-slate-50 pt-24 pb-12">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
        <!-- Title -->
        <div class="mb-8 text-center">
            <h1 id="booking-page-title" data-default-title="<?php echo esc_attr($bk_title); ?>" class="text-3xl font-bold text-slate-900 sm:text-4xl"><?php echo esc_html($bk_title); ?></h1>
            <p class="mt-2 text-slate-500"><?php echo esc_html($bk_subtitle); ?></p>
        </div>

        <div class="grid gap-8 lg:grid-cols-3">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Step 1: Select Session -->
                <section>
                    <h2 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-900">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white">1</span>
                        <?php echo esc_html($bk_step1); ?>
                    </h2>

                    <div id="sessions-grid" class="grid gap-4 sm:grid-cols-2">
                        <!-- Rendered by JS -->
                    </div>
                </section>

                <!-- Step 2: Select Package -->
                <section id="packages-section" class="hidden">
                    <h2 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-900">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white">2</span>
                        <?php echo esc_html($bk_step2); ?>
                    </h2>

                    <div id="packages-grid" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                        <!-- Rendered by JS -->
                    </div>
                </section>
                
                <!-- Step 3: Enter Customer Details -->
                <section id="details-section" class="hidden">
                    <h2 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-900">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white">3</span>
                        <?php echo esc_html($bk_step3); ?>
                    </h2>

                    <div class="card p-6">
                        <form id="booking-form" class="space-y-4">
                            <input type="hidden" id="form-session-id" name="session_id">
                            <input type="hidden" id="form-package-id" name="package_id">
                            
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="label" for="client_name">Full Name *</label>
                                    <input type="text" id="client_name" name="client_name" required placeholder="e.g. Sarah Mensah" class="input">
                                </div>
                                <div>
                                    <label class="label" for="client_email">Email Address *</label>
                                    <input type="email" id="client_email" name="client_email" required placeholder="e.g. sarah@email.com" class="input">
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="label" for="client_phone">Phone Number *</label>
                                    <input type="tel" id="client_phone" name="client_phone" required placeholder="e.g. +233 24 123 4567" class="input">
                                </div>
                                <div>
                                    <label class="label" for="event_date">Event Date *</label>
                                    <input type="date" id="event_date" name="event_date" required class="input">
                                </div>
                            </div>

                            <div>
                                <label class="label" for="event_location">Shoot Location *</label>
                                <input type="text" id="event_location" name="event_location" required placeholder="e.g. East Legon, Accra / Studio" class="input">
                            </div>

                            <div class="mt-4">
                                <label class="label" for="photo_delivery_date">Desired Photo Delivery Date *</label>
                                <input type="date" id="photo_delivery_date" name="photo_delivery_date" required class="input" style="border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px; width: 100%;">
                                <p class="description" style="margin-top: 5px; font-size: 11px; color: #64748b;">Choose when you want your final edited photos delivered. Surcharges apply automatically for priority turnarounds: within 3 days is +35%, within 24 hours is +40%.</p>
                            </div>
                        </form>
                    </div>
                </section>
            </div>

            <!-- Booking Summary Sidebar -->
            <div class="lg:col-span-1">
                <div class="sticky top-28 rounded-2xl bg-white p-6 shadow-sm border border-slate-100">
                    <h3 class="text-lg font-semibold text-slate-900"><?php echo esc_html($bk_sidebar_header); ?></h3>

                    <div id="summary-empty" class="mt-4 rounded-xl border-2 border-dashed border-slate-200 p-6 text-center">
                        <i data-lucide="camera" class="mx-auto mb-2 h-8 w-8 text-slate-300"></i>
                        <p class="text-sm text-slate-400">
                            <?php echo esc_html($bk_sidebar_empty); ?>
                        </p>
                    </div>

                    <div id="summary-content" class="mt-4 space-y-4 hidden">
                        <!-- Session -->
                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Session</p>
                            <p id="summary-session-name" class="mt-1 font-medium text-slate-900">—</p>
                        </div>

                        <!-- Package -->
                        <div id="summary-package-block" class="rounded-xl bg-slate-50 p-4 hidden">
                            <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Package</p>
                            <p id="summary-package-name" class="mt-1 font-medium text-slate-900">—</p>
                            <p id="summary-package-meta" class="mt-0.5 text-xs text-slate-500">—</p>
                        </div>

                        <!-- Price breakdown -->
                        <div id="summary-pricing-block" class="space-y-2 border-t pt-4 hidden">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Package Base Price</span>
                                <span id="summary-total-price" class="font-medium text-slate-900">GH₵ 0.00</span>
                            </div>
                            <!-- Dynamic Priority Surcharge row -->
                            <div id="summary-surcharge-row" class="flex justify-between text-sm hidden">
                                <span class="text-slate-500">Timeline Surcharge</span>
                                <span id="summary-surcharge-price" class="font-bold text-amber-600">+GH₵ 0.00</span>
                            </div>
                            <!-- Dynamic Updated Grand Total row -->
                            <div id="summary-grand-total-row" class="flex justify-between text-sm border-t border-slate-100 pt-2 hidden">
                                <span class="text-slate-800 font-bold">Updated Grand Total</span>
                                <span id="summary-grand-total-price" class="font-extrabold text-slate-900">GH₵ 0.00</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span id="summary-deposit-label" class="text-slate-500">Deposit (50%)</span>
                                <span id="summary-deposit-price" class="font-semibold text-green-600">GH₵ 0.00</span>
                            </div>
                            <div class="flex justify-between border-t pt-2 text-sm">
                                <span class="text-slate-500">Remaining Balance</span>
                                <span id="summary-balance-price" class="font-medium text-slate-900">GH₵ 0.00</span>
                            </div>
                        </div>

                        <!-- Delivery info -->
                        <div id="summary-delivery-badge" class="rounded-xl bg-blue-50 p-3 text-xs text-blue-700 hidden">
                            📷 Delivery in <span id="summary-delivery-days">3 Days</span>
                        </div>

                        <div id="summary-reschedule-badge" class="rounded-xl bg-green-50 p-3 text-xs text-green-700 hidden">
                            ✓ Free rescheduling (with <span id="summary-reschedule-hours">48</span>h notice)
                        </div>

                        <!-- Book Button -->
                        <button
                            id="book-submit-btn"
                            disabled
                            type="button"
                            class="btn btn-primary w-full py-3"
                        >
                            Select a Package to Book
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="success-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex min-h-screen items-center justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black/50 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
        
        <!-- Content -->
        <div class="relative inline-block transform overflow-hidden rounded-2xl bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
            <div class="bg-white px-6 pt-5 pb-6 sm:p-8 sm:pb-6">
                <div class="text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-green-100 text-green-600">
                        <i data-lucide="check-circle" class="h-8 w-8"></i>
                    </div>
                    <h3 class="mt-4 text-2xl font-bold tracking-tight text-slate-900"><?php echo esc_html($bk_success_title); ?></h3>
                    <p class="mt-2 text-sm text-slate-500">
                        <?php echo esc_html($bk_success_msg); ?>
                    </p>
                    
                    <div class="mt-6 rounded-xl border border-slate-100 bg-slate-50/50 p-4 text-left text-sm space-y-2">
                        <div class="flex justify-between"><span class="text-slate-500">Session:</span><span id="receipt-session" class="font-medium text-slate-900">—</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Package:</span><span id="receipt-package" class="font-medium text-slate-900">—</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Date:</span><span id="receipt-date" class="font-medium text-slate-900">—</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Required Deposit:</span><span id="receipt-deposit" class="font-bold text-green-600">—</span></div>
                    </div>
                </div>
            </div>
            <div class="bg-slate-50 px-6 py-4 flex flex-wrap justify-center gap-3">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-outline px-6">Return Home</a>
                <a id="success-receipt-btn" href="#" target="_blank" class="btn btn-primary px-6 flex items-center gap-1.5"><i data-lucide="printer" class="h-4 w-4"></i> View Receipt</a>
            </div>
        </div>
    </div>
</div>

<!-- ==================================================================== -->
<!-- 100% BULLETPROOF INLINED FRONTEND ENGINE (BYPASSES ALL SERVER CACHES!) -->
<!-- ==================================================================== -->
<script>
    // Global data definitions
    window.studioSessionsData = <?php echo json_encode($sessions_data); ?>;
    window.wpAjaxUrl = "<?php echo esc_url(admin_url('admin-ajax.php')); ?>"; // absolute path resolves subdirectories!
    window.paystackPublicKey = "<?php echo esc_js(get_option('studio_paystack_public_key')); ?>";
    window.paystackEnabled = "<?php echo esc_js(get_option('studio_paystack_enabled')); ?>";
    window.whatsappPhone = "<?php echo esc_js(get_theme_mod('studio_whatsapp_phone', '233241234567')); ?>";

    document.addEventListener('DOMContentLoaded', function () {
        const sessionsGrid = document.getElementById('sessions-grid');
        if (!sessionsGrid || !window.studioSessionsData) return;

        function getSessionDisplayName(session) {
            if (!session) return '';
            let displayTitle = session.name || '';
            if ((displayTitle.toLowerCase().trim() === 'book a session' || displayTitle.toLowerCase().trim() === 'book now') && session.category) {
                return session.category;
            }
            return displayTitle;
        }

        const data = window.studioSessionsData;
        let selectedSession = null;
        let selectedPackage = null;

        // DOM Cache
        const packagesSection = document.getElementById('packages-section');
        const packagesGrid = document.getElementById('packages-grid');
        const detailsSection = document.getElementById('details-section');
        const bookingForm = document.getElementById('booking-form');
        
        const summaryEmpty = document.getElementById('summary-empty');
        const summaryContent = document.getElementById('summary-content');
        const summarySessionName = document.getElementById('summary-session-name');
        const summaryPackageBlock = document.getElementById('summary-package-block');
        const summaryPackageName = document.getElementById('summary-package-name');
        const summaryPackageMeta = document.getElementById('summary-package-meta');
        const summaryPricingBlock = document.getElementById('summary-pricing-block');
        const summaryTotalPrice = document.getElementById('summary-total-price');
        const summaryDepositLabel = document.getElementById('summary-deposit-label');
        const summaryDepositPrice = document.getElementById('summary-deposit-price');
        const summaryBalancePrice = document.getElementById('summary-balance-price');
        const summaryDeliveryBadge = document.getElementById('summary-delivery-badge');
        const summaryDeliveryDays = document.getElementById('summary-delivery-days');
        const summaryRescheduleBadge = document.getElementById('summary-reschedule-badge');
        const summaryRescheduleHours = document.getElementById('summary-reschedule-hours');
        const bookSubmitBtn = document.getElementById('book-submit-btn');

        const successModal = document.getElementById('success-modal');
        const receiptSession = document.getElementById('receipt-session');
        const receiptPackage = document.getElementById('receipt-package');
        const receiptDate = document.getElementById('receipt-date');
        const receiptDeposit = document.getElementById('receipt-deposit');

        // Render Sessions
        function renderSessions() {
            sessionsGrid.innerHTML = '';
            if (data.length === 0) {
                sessionsGrid.innerHTML = `
                    <div class="col-span-2 rounded-2xl border-2 border-dashed border-slate-200 p-12 text-center bg-white">
                        <i data-lucide="camera" class="mx-auto mb-3 h-10 w-10 text-slate-300"></i>
                        <p class="text-slate-500 font-medium">No sessions available at the moment</p>
                    </div>
                `;
                if (typeof lucide !== 'undefined') lucide.createIcons();
                return;
            }

            data.forEach(session => {
                const isSelected = selectedSession && selectedSession.id === session.id;
                const borderClass = isSelected ? 'border-slate-900 shadow-lg' : 'border-slate-200 hover:border-slate-400 hover:shadow-md';
                
                let displayTitle = session.name || '';
                let displaySubtitle = session.category || '';
                
                // If the session post was named "Book a Session" or "Book Now" by mistake, automatically heal it by using the category name as the main title!
                if ((displayTitle.toLowerCase().trim() === 'book a session' || displayTitle.toLowerCase().trim() === 'book now') && session.category) {
                    displayTitle = session.category;
                    displaySubtitle = '';
                }

                const btn = document.createElement('button');
                btn.className = `group relative overflow-hidden rounded-2xl border-2 p-0 text-left bg-white transition-all ${borderClass}`;
                btn.type = 'button';
                btn.innerHTML = `
                    <div class="h-32 w-full overflow-hidden bg-slate-100">
                        <img src="${session.image}" alt="${displayTitle}" ${session.focal ? `style="object-position:${session.focal};"` : ''} class="h-full w-full object-cover transition-transform group-hover:scale-105" />
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-slate-900">${displayTitle}</h3>
                        ${displaySubtitle ? `<p class="mt-0.5 text-xs text-blue-600 font-medium">${displaySubtitle}</p>` : ''}
                        ${session.description ? `<p class="mt-1 text-sm text-slate-500 line-clamp-2">${session.description}</p>` : ''}
                        <p class="mt-2 text-xs text-slate-400">${session.packages.length} package${session.packages.length !== 1 ? 's' : ''} available</p>
                    </div>
                    ${isSelected ? `<div class="absolute right-3 top-3 rounded-full bg-slate-900 p-1"><i data-lucide="check-circle" class="h-4 w-4 text-white"></i></div>` : ''}
                `;

                btn.addEventListener('click', () => selectSession(session));
                sessionsGrid.appendChild(btn);
            });

            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        function selectSession(session) {
            selectedSession = session;
            selectedPackage = null;
            document.getElementById('form-session-id').value = session.id;
            document.getElementById('form-package-id').value = '';

            const pageTitleEl = document.getElementById('booking-page-title');
            if (pageTitleEl) {
                pageTitleEl.textContent = session ? session.name : pageTitleEl.getAttribute('data-default-title');
            }

            renderSessions();
            renderPackages();
            updateSummary();

            packagesSection.classList.remove('hidden');
            detailsSection.classList.add('hidden');
            packagesSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // Render Packages
        function renderPackages() {
            packagesGrid.innerHTML = '';
            if (!selectedSession) return;

            const packages = selectedSession.packages;
            if (packages.length === 0) {
                packagesGrid.innerHTML = `
                    <div class="col-span-2 rounded-2xl border-2 border-dashed border-slate-200 p-12 text-center bg-white">
                        <p class="text-slate-500 font-medium">No packages available for this session</p>
                    </div>
                `;
                return;
            }

            packages.forEach(pkg => {
                const isSelected = selectedPackage && selectedPackage.id === pkg.id;
                const borderClass = isSelected ? 'border-slate-900 bg-slate-50 shadow-lg' : 'border-slate-200 hover:border-slate-400 hover:shadow-md';
                const depositAmount = (pkg.price * (pkg.deposit_percentage / 100)).toFixed(2);

                const btn = document.createElement('button');
                btn.className = `rounded-2xl border-2 p-6 text-left bg-white transition-all ${borderClass}`;
                btn.type = 'button';
                btn.innerHTML = `
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">${pkg.name}</h3>
                            <p class="mt-1 text-2xl font-bold text-slate-900">GH₵ ${pkg.price.toFixed(2)}</p>
                        </div>
                        ${isSelected ? '<i data-lucide="check-circle" class="h-6 w-6 text-slate-900"></i>' : ''}
                    </div>
                    ${pkg.features.length > 0 ? `
                    <div class="mt-4 space-y-1.5 border-t border-slate-100 pt-4">
                        ${pkg.features.map(f => `<div class="flex items-center gap-2 text-sm text-slate-600"><i data-lucide="check-circle" class="h-3.5 w-3.5 shrink-0 text-green-500"></i><span>${f}</span></div>`).join('')}
                    </div>` : ''}
                    <div class="mt-4 rounded-lg bg-slate-100 px-3 py-2 text-xs text-slate-500">Deposit: GH₵ ${depositAmount} (${pkg.deposit_percentage}%)</div>
                `;

                btn.addEventListener('click', () => selectPackage(pkg));
                packagesGrid.appendChild(btn);
            });

            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        function selectPackage(pkg) {
            selectedPackage = pkg;
            document.getElementById('form-package-id').value = pkg.id;
            renderPackages();
            updateSummary();
            detailsSection.classList.remove('hidden');
            detailsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // Sidebar Calculator with Surcharge bounds!
        function updateSummary() {
            if (!selectedSession) {
                summaryEmpty.classList.remove('hidden');
                summaryContent.classList.add('hidden');
                bookSubmitBtn.disabled = true;
                return;
            }

            summaryEmpty.classList.add('hidden');
            summaryContent.classList.remove('hidden');
            summarySessionName.textContent = getSessionDisplayName(selectedSession);

            if (selectedPackage) {
                summaryPackageBlock.classList.remove('hidden');
                summaryPackageName.textContent = selectedPackage.name;
                summaryPackageMeta.textContent = "Photography Session Selection";

                // Calculate automatic surcharge based on the chosen dates difference!
                const eventDateVal = document.getElementById('event_date').value;
                const deliveryDateVal = document.getElementById('photo_delivery_date').value;
                let surchargePct = 0;
                let diffDays = 0;

                if (eventDateVal && deliveryDateVal) {
                    const date1 = new Date(eventDateVal.replace(/-/g, '/'));
                    const date2 = new Date(deliveryDateVal.replace(/-/g, '/'));
                    
                    date1.setHours(0,0,0,0);
                    date2.setHours(0,0,0,0);
                    
                    const diffTime = date2 - date1;
                    diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                    if (diffDays < 0) {
                        alert('Desired Photo Delivery Date cannot be before the Shoot Date!');
                        document.getElementById('photo_delivery_date').value = '';
                        diffDays = 0;
                    } else if (diffDays >= 0 && diffDays <= 2) {
                        surchargePct = 40; // Next-Day Rush (+40%)
                    } else if (diffDays <= 5) {
                        surchargePct = 35; // Express (+35%)
                    }
                }

                // Math breakdown
                const basePrice = parseFloat(selectedPackage.price);
                const surchargeGHS = basePrice * (surchargePct / 100);
                const price = basePrice + surchargeGHS;
                
                const depositPct = parseInt(selectedPackage.deposit_percentage);
                const deposit = (price * (depositPct / 100));
                const balance = price - deposit;

                summaryTotalPrice.textContent = `GH₵ ${basePrice.toFixed(2)}`;
                
                const surchargeRow = document.getElementById('summary-surcharge-row');
                const surchargePriceSpan = document.getElementById('summary-surcharge-price');
                const grandTotalRow = document.getElementById('summary-grand-total-row');
                const grandTotalPriceSpan = document.getElementById('summary-grand-total-price');

                if (surchargeRow && surchargePriceSpan) {
                    if (surchargePct > 0) {
                        surchargePriceSpan.textContent = `+GH₵ ${surchargeGHS.toFixed(2)} (${surchargePct}% - ${diffDays} Day${diffDays !== 1 ? 's' : ''} Rush)`;
                        surchargeRow.classList.remove('hidden');
                        
                        if (grandTotalRow && grandTotalPriceSpan) {
                            grandTotalPriceSpan.textContent = `GH₵ ${price.toFixed(2)}`;
                            grandTotalRow.classList.remove('hidden');
                        }
                    } else {
                        surchargeRow.classList.add('hidden');
                        if (grandTotalRow) grandTotalRow.classList.add('hidden');
                    }
                }

                summaryDepositLabel.textContent = `Deposit (${depositPct}%)`;
                summaryDepositPrice.textContent = `GH₵ ${deposit.toFixed(2)}`;
                summaryBalancePrice.textContent = `GH₵ ${balance.toFixed(2)}`;
                summaryPricingBlock.classList.remove('hidden');

                summaryDeliveryDays.textContent = selectedPackage.delivery_time;
                summaryDeliveryBadge.classList.remove('hidden');

                if (selectedPackage.reschedule_allowed) {
                    summaryRescheduleHours.textContent = selectedPackage.reschedule_hours;
                    summaryRescheduleBadge.classList.remove('hidden');
                } else {
                    summaryRescheduleBadge.classList.add('hidden');
                }

                bookSubmitBtn.textContent = `Book Now — GH₵ ${deposit.toFixed(2)} Deposit`;
                bookSubmitBtn.disabled = false;
            } else {
                summaryPackageBlock.classList.add('hidden');
                summaryPricingBlock.classList.add('hidden');
                summaryDeliveryBadge.classList.add('hidden');
                summaryRescheduleBadge.classList.add('hidden');
                bookSubmitBtn.textContent = 'Select a Package to Book';
                bookSubmitBtn.disabled = true;
            }
        }

        // AJAX Submission
        function submitBookingAJAX(paystackReference = '') {
            const formData = new FormData(bookingForm);
            formData.append('action', 'submit_studio_booking');
            if (paystackReference) formData.append('paystack_reference', paystackReference);

            fetch(window.wpAjaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    receiptSession.textContent = getSessionDisplayName(selectedSession);
                    receiptPackage.textContent = selectedPackage.name;
                    receiptDate.textContent = document.getElementById('event_date').value;
                    
                    const depositPct = selectedPackage.deposit_percentage;
                    const basePrice = parseFloat(selectedPackage.price);
                    
                    // calculate total with surcharge
                    const eventDateVal = document.getElementById('event_date').value;
                    const deliveryDateVal = document.getElementById('photo_delivery_date').value;
                    let surchargePct = 0;
                    if (eventDateVal && deliveryDateVal) {
                        const date1 = new Date(eventDateVal.replace(/-/g, '/'));
                        const date2 = new Date(deliveryDateVal.replace(/-/g, '/'));
                        date1.setHours(0,0,0,0);
                        date2.setHours(0,0,0,0);
                        const diffDays = Math.ceil((date2 - date1) / (1000 * 60 * 60 * 24));
                        if (diffDays >= 0 && diffDays <= 2) surchargePct = 40;
                        else if (diffDays <= 5) surchargePct = 35;
                    }
                    const totalVal = basePrice + (basePrice * (surchargePct / 100));
                    const depositVal = (totalVal * (depositPct / 100)).toFixed(2);
                    
                    if (paystackReference) {
                        receiptDeposit.innerHTML = `GH₵ ${depositVal} (${depositPct}%) <br><span class="text-xs text-green-600 font-bold bg-green-50 px-2 py-0.5 rounded-full inline-block mt-1">Paid via Paystack (${paystackReference})</span>`;
                    } else {
                        receiptDeposit.textContent = `GH₵ ${depositVal} (${depositPct}%) [Manual Settlement]`;
                    }

                    // Dynamically point the receipt print button to our new dedicated booking receipt!
                    const receiptBtn = document.getElementById('success-receipt-btn');
                    let receiptUrl = '';
                    if (receiptBtn && data.data && data.data.booking_id) {
                        receiptUrl = window.wpAjaxUrl.replace('wp-admin/admin-ajax.php', '') + '?studio_booking_receipt=1&booking_id=' + data.data.booking_id;
                        receiptBtn.href = receiptUrl;
                    }

                    successModal.classList.remove('hidden');
                    bookingForm.reset();
                    
                    const pageTitleEl = document.getElementById('booking-page-title');
                    if (pageTitleEl) {
                        pageTitleEl.textContent = pageTitleEl.getAttribute('data-default-title');
                    }

                    selectedSession = null;
                    selectedPackage = null;
                    renderSessions();
                    updateSummary();
                    packagesSection.classList.add('hidden');
                    detailsSection.classList.add('hidden');
                } else {
                    alert(data.data || 'Failed to submit booking.');
                    resetBookBtnState();
                }
            })
            .catch(() => { alert('An error occurred during submission.'); resetBookBtnState(); });
        }

        let originalBtnText = '';
        function resetBookBtnState() {
            bookSubmitBtn.disabled = false;
            bookSubmitBtn.textContent = originalBtnText || 'Book Now';
        }

        bookSubmitBtn.addEventListener('click', function(e) {
            e.preventDefault();

            const clientName = document.getElementById('client_name').value.trim();
            const clientEmail = document.getElementById('client_email').value.trim();
            const clientPhone = document.getElementById('client_phone').value.trim();
            const eventDate = document.getElementById('event_date').value.trim();
            const eventLocation = document.getElementById('event_location').value.trim();
            const deliveryDate = document.getElementById('photo_delivery_date').value.trim();

            if (!clientName || !clientEmail || !clientPhone || !eventDate || !eventLocation || !deliveryDate) {
                alert('Please fill out all required fields marked with an asterisk (*).');
                return;
            }

            originalBtnText = bookSubmitBtn.textContent;
            bookSubmitBtn.disabled = true;
            bookSubmitBtn.innerHTML = `<i data-lucide="loader" class="h-4 w-4 animate-spin inline mr-2"></i> Initializing Booking...`;
            if (typeof lucide !== 'undefined') lucide.createIcons();

            // Calculate deposit with surcharge
            const basePrice = parseFloat(selectedPackage.price);
            const date1 = new Date(eventDate.replace(/-/g, '/'));
            const date2 = new Date(deliveryDate.replace(/-/g, '/'));
            date1.setHours(0,0,0,0);
            date2.setHours(0,0,0,0);
            const diffDays = Math.ceil((date2 - date1) / (1000 * 60 * 60 * 24));
            
            let surchargePct = 0;
            if (diffDays >= 0 && diffDays <= 2) surchargePct = 40;
            else if (diffDays <= 5) surchargePct = 35;
            
            const totalPrice = basePrice + (basePrice * (surchargePct / 100));
            const depositAmount = totalPrice * (parseInt(selectedPackage.deposit_percentage) / 100);

            if (window.paystackEnabled === 'yes' && window.paystackPublicKey && typeof PaystackPop !== 'undefined') {
                bookSubmitBtn.innerHTML = `<i data-lucide="credit-card" class="h-4 w-4 animate-spin inline mr-2"></i> Opening Paystack Gateway...`;
                if (typeof lucide !== 'undefined') lucide.createIcons();

                // Generate dynamic Paystack reference prefix based on the session type name on the booking page
                let refPrefix = 'STUDIO';
                if (selectedSession && selectedSession.name) {
                    refPrefix = selectedSession.name
                        .toUpperCase()
                        .replace(/[^A-Z0-9\s-]/g, '') // Keep alphanumeric, spaces and dashes
                        .trim()
                        .replace(/[\s-]+/g, '-');    // Replace spaces/multiple dashes with a single dash
                }

                let paystackPopup = PaystackPop.setup({
                    key: window.paystackPublicKey,
                    email: clientEmail,
                    amount: Math.round(depositAmount * 100),
                    currency: 'GHS',
                    ref: refPrefix + '-' + Date.now(),
                    metadata: {
                        custom_fields: [
                            { display_name: "Customer Name", variable_name: "customer_name", value: clientName },
                            { display_name: "Shoot Date", variable_name: "shoot_date", value: eventDate },
                            { display_name: "Package", variable_name: "package_name", value: selectedPackage.name }
                        ]
                    },
                    callback: function(response) {
                        submitBookingAJAX(response.reference);
                    },
                    onClose: function() {
                        alert('Checkout deposit settlement cancelled.');
                        resetBookBtnState();
                    }
                });

                // Open the Paystack inline checkout popup
                paystackPopup.open();
            } else {
                // Paystack not configured — submit the booking directly without online payment
                submitBookingAJAX('');
            }
        });

        // Event listeners for dates to calculate surcharge dynamically in real-time!
        const deliveryDateInput = document.getElementById('photo_delivery_date');
        const eventDateInput = document.getElementById('event_date');
        if (deliveryDateInput) deliveryDateInput.addEventListener('change', updateSummary);
        if (eventDateInput) eventDateInput.addEventListener('change', updateSummary);

        renderSessions();
    });
</script>

<?php
get_footer();
?>
