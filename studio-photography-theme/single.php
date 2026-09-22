<?php
/**
 * Blog removed — old post links redirect gracefully to the homepage.
 * (Client Galleries and Portfolio Galleries keep their own dedicated templates.)
 *
 * @package Studio_Photography
 */
wp_safe_redirect(home_url('/'), 301);
exit;
