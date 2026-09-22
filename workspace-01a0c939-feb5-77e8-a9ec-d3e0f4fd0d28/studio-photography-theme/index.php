<?php
/**
 * Blog removed — every blog/archive view redirects gracefully to the homepage.
 *
 * @package Studio_Photography
 */
wp_safe_redirect(home_url('/'), 301);
exit;
