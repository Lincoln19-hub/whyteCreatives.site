<?php
/**
 * Studio Dashboard (front-end) — RETIRED.
 * The studio workflow now lives entirely inside WP-Admin (redesigned to match the site).
 * Anyone visiting /dashboard/ is taken straight there (admins) or back to the site.
 *
 * @package Studio_Photography
 */
if (current_user_can('manage_options')) {
    wp_safe_redirect(admin_url('admin.php'));
    exit;
}
wp_safe_redirect(home_url('/'));
exit;
