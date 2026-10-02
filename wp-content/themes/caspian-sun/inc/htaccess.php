<?php
/**
 * Writes the hardening and performance rules into the site's own .htaccess (Apache / LiteSpeed).
 * The file is not kept in git because each install has a different RewriteBase (e.g. /caspwp/ in a sub-folder).
 * Runs once per rules version; bump CSP_HTACCESS_VERSION after editing the rules below.
 *
 * @package caspian-sun
 */

defined( 'ABSPATH' ) || exit;

define( 'CSP_HTACCESS_VERSION', '3' );

function csp_htaccess_blocks() {
	$deny = array(
		'  <IfModule mod_authz_core.c>',
		'    Require all denied',
		'  </IfModule>',
		'  <IfModule !mod_authz_core.c>',
		'    Order allow,deny',
		'    Deny from all',
		'  </IfModule>',
	);
	$hardening = array_merge(
		array(
			'Options -Indexes',
			'<FilesMatch "^(readme\.html|license\.txt|wp-config-sample\.php|xmlrpc\.php|local-xdebuginfo\.php|debug\.log)$">',
		),
		$deny,
		array( '</FilesMatch>' )
	);
	$performance = array(
		'<IfModule mod_deflate.c>',
		'  AddOutputFilterByType DEFLATE text/html text/plain text/css text/xml application/xml application/xhtml+xml application/javascript text/javascript application/json application/ld+json image/svg+xml application/rss+xml font/ttf font/otf',
		'</IfModule>',
		'<IfModule mod_brotli.c>',
		'  AddOutputFilterByType BROTLI_COMPRESS text/html text/plain text/css text/xml application/xml application/javascript text/javascript application/json application/ld+json image/svg+xml',
		'</IfModule>',
		'<IfModule mod_expires.c>',
		'  ExpiresActive On',
		'  ExpiresByType image/webp "access plus 1 year"',
		'  ExpiresByType image/png "access plus 1 year"',
		'  ExpiresByType image/jpeg "access plus 1 year"',
		'  ExpiresByType image/gif "access plus 1 year"',
		'  ExpiresByType image/svg+xml "access plus 1 year"',
		'  ExpiresByType image/x-icon "access plus 1 year"',
		'  ExpiresByType video/mp4 "access plus 1 year"',
		'  ExpiresByType font/woff2 "access plus 1 year"',
		'  ExpiresByType font/woff "access plus 1 year"',
		'  ExpiresByType application/font-woff2 "access plus 1 year"',
		'  ExpiresByType text/css "access plus 1 year"',
		'  ExpiresByType application/javascript "access plus 1 year"',
		'  ExpiresByType text/javascript "access plus 1 year"',
		'</IfModule>',
		'<IfModule mod_headers.c>',
		'  <FilesMatch "\.(webp|png|jpe?g|gif|svg|ico|mp4|woff2?|css|js)$">',
		'    Header set Cache-Control "public, max-age=31536000, immutable"',
		'  </FilesMatch>',
		'</IfModule>',
	);
	return array(
		'Caspian hardening'   => $hardening,
		'Caspian performance' => $performance,
	);
}

add_action(
	'init',
	function () {
		if ( get_option( 'csp_htaccess_v' ) === CSP_HTACCESS_VERSION || wp_doing_ajax() ) {
			return;
		}
		if ( false === stripos( (string) ( isset( $_SERVER['SERVER_SOFTWARE'] ) ? $_SERVER['SERVER_SOFTWARE'] : 'apache' ), 'nginx' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/misc.php';
			$file = get_home_path() . '.htaccess';
			if ( file_exists( $file ) && is_writable( $file ) ) {
				foreach ( csp_htaccess_blocks() as $marker => $lines ) {
					insert_with_markers( $file, $marker, $lines );
				}
			}
		}
		update_option( 'csp_htaccess_v', CSP_HTACCESS_VERSION, true );
	},
	99
);
