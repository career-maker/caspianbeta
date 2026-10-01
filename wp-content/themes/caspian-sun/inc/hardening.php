<?php
/**
 * Launch checklist: closed REST API, lower-case URLs, robots.txt, favicon fallback.
 * (Directory listing, readme.html etc. are handled in the root / uploads .htaccess files.)
 *
 * @package caspian-sun
 */

defined( 'ABSPATH' ) || exit;

/* ---- REST API closed to the public (admin screens still work when logged in) ---- */
add_filter(
	'rest_authentication_errors',
	function ( $result ) {
		if ( true === $result || is_wp_error( $result ) ) {
			return $result;
		}
		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'rest_forbidden', __( 'The REST API is not available to visitors.', 'caspian-sun' ), array( 'status' => 401 ) );
		}
		return $result;
	}
);
remove_action( 'template_redirect', 'rest_output_link_header', 11 );

/* ---- Case-sensitive URLs: /About-Us/ → 301 → /about-us/ ---- */
add_action(
	'init',
	function () {
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI ) || empty( $_SERVER['REQUEST_URI'] ) ) {
			return;
		}
		$method = isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : 'GET';
		if ( ! in_array( $method, array( 'GET', 'HEAD' ), true ) ) {
			return;
		}
		$uri  = wp_unslash( $_SERVER['REQUEST_URI'] ); // phpcs:ignore WordPress.Security
		$path = (string) wp_parse_url( $uri, PHP_URL_PATH );
		$base = rtrim( (string) wp_parse_url( home_url(), PHP_URL_PATH ), '/' );
		$rest = ( '' !== $base && 0 === strpos( $path, $base ) ) ? substr( $path, strlen( $base ) ) : $path;
		if ( $rest === strtolower( $rest ) || preg_match( '#^/(wp-admin|wp-content|wp-includes|wp-json)(/|$)|^/wp-login\.php#i', $rest ) ) {
			return;
		}
		$query = (string) wp_parse_url( $uri, PHP_URL_QUERY );
		wp_safe_redirect( home_url( strtolower( $rest ) ) . ( $query ? '?' . $query : '' ), 301 );
		exit;
	},
	0
);

/* ---- robots.txt (virtual — do not add a physical file, or the sitemap line is lost) ---- */
add_filter(
	'robots_txt',
	function ( $output, $public ) {
		if ( ! $public ) {
			return $output; // "Discourage search engines" is on: keep WordPress' Disallow: /
		}
		$lines = array(
			'User-agent: *',
			'Disallow: /wp-admin/',
			'Allow: /wp-admin/admin-ajax.php',
			'Disallow: /wp-json/',
			'Disallow: /?s=',
			'Disallow: /search/',
			'Disallow: /*?*utm_',
			'',
		);
		$map = defined( 'WPSEO_VERSION' ) ? home_url( '/sitemap_index.xml' ) : home_url( '/wp-sitemap.xml' );
		return implode( "\n", $lines ) . "\nSitemap: " . $map . "\n";
	},
	100000,
	2
);

/* ---- Favicon: Theme Settings image → theme fallback; also answers /favicon.ico ---- */
add_filter(
	'get_site_icon_url',
	function ( $url ) {
		if ( $url ) {
			return $url;
		}
		$id = csp_opt( 'opt_favicon' );
		if ( $id ) {
			$u = wp_get_attachment_url( $id );
			if ( $u ) {
				return $u;
			}
		}
		return CSP_URI . '/assets/images/favicon.png';
	}
);
add_action(
	'do_faviconico',
	function () {
		$file = CSP_DIR . '/assets/images/favicon.ico';
		if ( is_readable( $file ) ) {
			header( 'Content-Type: image/x-icon' );
			header( 'Cache-Control: public, max-age=604800' );
			readfile( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			exit;
		}
	},
	1
);
