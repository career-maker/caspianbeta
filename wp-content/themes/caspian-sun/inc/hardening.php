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

/**
 * Lower-case redirect target for a request URI, or '' when none is needed.
 *
 * @param string $uri  Request URI (path + optional query).
 * @param string $base Path of the home URL without trailing slash ('' at the domain root, '/caspwp' in a sub-folder).
 */
function csp_url_case_target( $uri, $base ) {
	$path = (string) wp_parse_url( $uri, PHP_URL_PATH );
	$rest = ( '' !== $base && 0 === strpos( $path, $base ) ) ? substr( $path, strlen( $base ) ) : $path;
	$check = preg_replace( '/%[0-9a-fA-F]{2}/', '', $rest ); // Percent-escapes (%E0…) are case-insensitive hex, not content.
	if ( $check === strtolower( $check ) || preg_match( '#^/(wp-admin|wp-content|wp-includes|wp-json)(/|$)|^/wp-login\.php#i', $rest ) ) {
		return '';
	}
	$query = (string) wp_parse_url( $uri, PHP_URL_QUERY );
	return $base . strtolower( $rest ) . ( $query ? '?' . $query : '' );
}

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
		$base   = rtrim( (string) wp_parse_url( home_url(), PHP_URL_PATH ), '/' );
		$target = csp_url_case_target( wp_unslash( $_SERVER['REQUEST_URI'] ), $base ); // phpcs:ignore WordPress.Security
		if ( '' !== $target ) {
			wp_safe_redirect( untrailingslashit( home_url() ) . substr( $target, strlen( $base ) ), 301 );
			exit;
		}
	},
	0
);

/* ---- robots.txt also when WordPress lives in a sub-folder (core only registers the rule at the domain root) ---- */
add_action(
	'init',
	function () {
		if ( is_admin() || empty( $_SERVER['REQUEST_URI'] ) ) {
			return;
		}
		$path = (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ); // phpcs:ignore WordPress.Security
		$base = rtrim( (string) wp_parse_url( home_url(), PHP_URL_PATH ), '/' );
		if ( '' === $base || $path !== $base . '/robots.txt' ) {
			return; // At the domain root WordPress already serves it.
		}
		status_header( 200 );
		header( 'Content-Type: text/plain; charset=utf-8' );
		do_action( 'do_robotstxt' );
		$public = get_option( 'blog_public' );
		echo apply_filters( 'robots_txt', "User-agent: *
Disallow: /wp-admin/
", $public ); // phpcs:ignore WordPress.Security.EscapeOutput
		exit;
	},
	1
);

/* ---- robots.txt (virtual — do not add a physical file, or the sitemap line is lost) ---- */
add_filter(
	'robots_txt',
	function ( $output, $public ) {
		if ( ! $public ) {
			return "User-agent: *
Disallow: /
"; // Pre-launch: block all crawling.
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

/* ---- Pre-launch: Settings → Reading → "Discourage search engines" (blog_public = 0) blocks indexing everywhere ---- */
add_action(
	'send_headers',
	function () {
		if ( '0' === (string) get_option( 'blog_public' ) ) {
			header( 'X-Robots-Tag: noindex, nofollow, noarchive, nosnippet', true );
		}
	}
);
// No sitemap while the site is not public.
add_filter(
	'wpseo_enable_xml_sitemap',
	function ( $on ) {
		return '0' === (string) get_option( 'blog_public' ) ? false : $on;
	}
);
add_filter(
	'wp_sitemaps_enabled',
	function ( $on ) {
		return '0' === (string) get_option( 'blog_public' ) ? false : $on;
	}
);

/* ---- Compress the HTML itself when the web server does not (static files are handled by .htaccess) ---- */
add_action(
	'after_setup_theme',
	function () {
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI ) || headers_sent() ) {
			return;
		}
		$accept = isset( $_SERVER['HTTP_ACCEPT_ENCODING'] ) ? (string) $_SERVER['HTTP_ACCEPT_ENCODING'] : '';
		if ( false === stripos( $accept, 'gzip' ) || ! extension_loaded( 'zlib' ) || ini_get( 'zlib.output_compression' ) || ob_get_level() > 1 ) {
			return;
		}
		foreach ( headers_list() as $h ) {
			if ( 0 === stripos( $h, 'Content-Encoding:' ) ) {
				return; // Something else already compresses.
			}
		}
		@ini_set( 'zlib.output_compression', 'On' ); // phpcs:ignore WordPress.PHP.IniSet, WordPress.PHP.NoSilencedErrors
		@ini_set( 'zlib.output_compression_level', '6' ); // phpcs:ignore WordPress.PHP.IniSet, WordPress.PHP.NoSilencedErrors
	},
	0
);

/* ---- HTML pages must always be re-validated: a phone that kept an old copy would keep pointing at old CSS/JS file versions ---- */
add_action(
	'send_headers',
	function () {
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || is_feed() ) {
			return;
		}
		header( 'Cache-Control: no-cache, must-revalidate, max-age=0', true );
	},
	99
);

/* ---- Support aid: open any page with ?csp_debug=1 to show what the visitor's device really renders (no effect otherwise) ---- */
add_action(
	'wp_footer',
	function () {
		if ( ! isset( $_GET['csp_debug'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}
		?>
<div id="csp-debug" style="position:fixed;left:0;right:0;bottom:0;z-index:2147483647;background:#000;color:#0f0;font:12px/1.35 monospace;padding:8px;max-height:60vh;overflow:auto;white-space:pre-wrap;word-break:break-all"></div>
<script>
(function(){
  function g(sel){return document.querySelector(sel);}
  function run(){
    var h=g('.hero'),p=g('.about-text p'),L=[].slice.call(document.querySelectorAll('link[rel=stylesheet]')).map(function(l){return l.href.split('/').pop();});
    var cs=p?getComputedStyle(p):null, hs=h?h.getBoundingClientRect():null;
    var out=[
      'innerWidth x innerHeight: '+innerWidth+' x '+innerHeight,
      'screen: '+screen.width+' x '+screen.height+'  DPR: '+devicePixelRatio,
      'visualViewport: '+(window.visualViewport?Math.round(visualViewport.width)+' x '+Math.round(visualViewport.height)+' scale '+visualViewport.scale:'n/a'),
      'matches (max-width:767px): '+matchMedia('(max-width:767px)').matches,
      'matches (max-width:768px): '+matchMedia('(max-width:768px)').matches,
      'supports 100dvh: '+(window.CSS&&CSS.supports&&CSS.supports('height','100dvh')),
      'hero height: '+(hs?Math.round(hs.height):'no .hero')+'   computed: '+(h?getComputedStyle(h).height:''),
      'about p text-align: '+(cs?cs.textAlign+'  hyphens: '+cs.hyphens:'(no .about-text p on this page)'),
      'stylesheets: '+L.join(', '),
      'html lang: '+document.documentElement.lang,
      'UA: '+navigator.userAgent
    ];
    g('#csp-debug').textContent=out.join('\n');
  }
  window.addEventListener('load',function(){setTimeout(run,800);});
  window.addEventListener('resize',run);
})();
</script>
		<?php
	},
	99
);
