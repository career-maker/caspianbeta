<?php
/**
 * Caspian & Sun Food Trading — theme bootstrap.
 *
 * @package caspian-sun
 */

defined( 'ABSPATH' ) || exit;

define( 'CSP_VERSION', '1.0.0' );
define( 'CSP_DIR', get_template_directory() );
define( 'CSP_URI', get_template_directory_uri() );

require_once CSP_DIR . '/inc/helpers.php';
require_once CSP_DIR . '/inc/icons.php';
require_once CSP_DIR . '/inc/setup.php';
require_once CSP_DIR . '/inc/post-types.php';
require_once CSP_DIR . '/inc/acf-lib.php';
require_once CSP_DIR . '/inc/acf-options.php';
require_once CSP_DIR . '/inc/acf-pages.php';
require_once CSP_DIR . '/inc/acf-content.php';
require_once CSP_DIR . '/inc/validation.php';
require_once CSP_DIR . '/inc/mail.php';
require_once CSP_DIR . '/inc/forms.php';
require_once CSP_DIR . '/inc/seo.php';
require_once CSP_DIR . '/inc/hardening.php';
require_once CSP_DIR . '/inc/htaccess.php';
require_once CSP_DIR . '/inc/admin-cleanup.php';
require_once CSP_DIR . '/inc/seed.php';
