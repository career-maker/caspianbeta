<?php
/**
 * Theme setup: supports, menus, assets, head output, hardening.
 *
 * @package caspian-sun
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', 'csp_setup' );
function csp_setup() {
	load_theme_textdomain( 'caspian-sun', CSP_DIR . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'post-thumbnails' );
	remove_theme_support( 'core-block-patterns' );

	register_nav_menus(
		array(
			'header_left'    => __( 'Header — left links', 'caspian-sun' ),
			'header_right'   => __( 'Header — right links', 'caspian-sun' ),
			'footer_company' => __( 'Footer — Company column', 'caspian-sun' ),
			'footer_products' => __( 'Footer — Products column', 'caspian-sun' ),
		)
	);
}

/* ------------------------------------------------------------------ Menus */

/** Walker that prints bare <a> tags (the approved header markup has no <li>). */
class CSP_Flat_Walker extends Walker_Nav_Menu {
	public function start_lvl( &$output, $depth = 0, $args = null ) {} // phpcs:ignore
	public function end_lvl( &$output, $depth = 0, $args = null ) {} // phpcs:ignore
	public function end_el( &$output, $item, $depth = 0, $args = null ) {} // phpcs:ignore

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) { // phpcs:ignore
		$active = array_intersect( (array) $item->classes, array( 'current-menu-item', 'current-menu-ancestor', 'current-menu-parent', 'current_page_parent' ) );
		$attrs  = ' href="' . esc_url( $item->url ) . '"';
		if ( $active ) {
			$attrs .= ' class="active" aria-current="page"';
		}
		if ( '_blank' === $item->target ) {
			$attrs .= ' target="_blank" rel="noopener"';
		}
		$output .= '<a' . $attrs . '>' . esc_html( $item->title ) . '</a>';
	}
}

/** Print a flat anchor list for a menu location. */
function csp_flat_menu( $location ) {
	if ( ! has_nav_menu( $location ) ) {
		return;
	}
	wp_nav_menu(
		array(
			'theme_location' => $location,
			'container'      => false,
			'items_wrap'     => '%3$s',
			'walker'         => new CSP_Flat_Walker(),
			'depth'          => 1,
			'fallback_cb'    => false,
		)
	);
}

/** Print a plain <ul><li><a> list for footer columns. */
function csp_list_menu( $location ) {
	if ( ! has_nav_menu( $location ) ) {
		return;
	}
	wp_nav_menu(
		array(
			'theme_location' => $location,
			'container'      => false,
			'menu_class'     => '',
			'items_wrap'     => '<ul style="list-style:none;">%3$s</ul>',
			'depth'          => 1,
			'fallback_cb'    => false,
		)
	);
}

// Footer list items carry no WordPress classes (keeps markup identical to the design).
add_filter(
	'nav_menu_css_class',
	function ( $classes, $item, $args ) {
		if ( isset( $args->theme_location ) && 0 === strpos( (string) $args->theme_location, 'footer_' ) ) {
			return array();
		}
		// Highlight the Products menu item while viewing a single product.
		if ( is_singular( 'product' ) && (int) $item->object_id === csp_page_by_template( 'templates/products.php' ) ) {
			$classes[] = 'current-menu-parent';
		}
		return $classes;
	},
	10,
	3
);
add_filter( 'nav_menu_item_id', '__return_empty_string' );

/* ----------------------------------------------------------------- Assets */

/** Which page stylesheet / script belongs to the current request. */
function csp_page_assets() {
	if ( is_front_page() ) {
		return array( 'home', 'home' );
	}
	if ( is_singular( 'product' ) ) {
		return array( 'product-detail', 'product-detail' );
	}
	if ( is_singular( 'post' ) ) {
		return array( 'blog-detail', '' );
	}
	if ( is_home() ) {
		return array( 'blog', '' );
	}
	if ( is_page_template( 'templates/about.php' ) ) {
		return array( 'about', '' );
	}
	if ( is_page_template( 'templates/products.php' ) ) {
		return array( 'products', 'products' );
	}
	if ( is_page_template( 'templates/contact.php' ) ) {
		return array( 'contact', 'contact' );
	}
	if ( is_404() ) {
		return array( '404', '' );
	}
	return array( 'legal', 'legal' );
}

/** Cache-busting version: the file's modification time, so every deploy reaches visitors at once. */
function csp_asset_ver( $rel ) {
	$f = CSP_DIR . '/assets/' . $rel;
	return is_readable( $f ) ? CSP_VERSION . '.' . filemtime( $f ) : CSP_VERSION;
}

add_action( 'wp_enqueue_scripts', 'csp_enqueue' );
function csp_enqueue() {
	$uri = CSP_URI . '/assets/';
	// preloader.css is tiny and needed for first paint: printed inline in <head> (see csp_inline_preloader_css), not a separate request.
	wp_enqueue_style( 'csp-components', $uri . 'css/components.css', array(), csp_asset_ver( 'css/components.css' ) );

	list( $css, $js ) = csp_page_assets();
	wp_enqueue_style( 'csp-page', $uri . 'css/page-' . $css . '.css', array( 'csp-components' ), csp_asset_ver( 'css/page-' . $css . '.css' ) );

	wp_enqueue_script( 'csp-components', $uri . 'js/components.js', array(), csp_asset_ver( 'js/components.js' ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
	if ( $js ) {
		wp_enqueue_script( 'csp-page', $uri . 'js/' . $js . '.js', array( 'csp-components' ), csp_asset_ver( 'js/' . $js . '.js' ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
	}
	if ( 'contact' === $js ) {
		wp_localize_script(
			'csp-page',
			'CSP_FORM',
			array(
				'ajax'  => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'csp_contact' ),
				'recaptcha' => csp_recaptcha_keys()[0],
				'packingLabel' => csp_get( 'contact_packing_placeholder' ) ? csp_get( 'contact_packing_placeholder' ) : '',
			)
		);
	}
}

/** Inline the small preloader stylesheet. */
add_action(
	'wp_head',
	function () {
		$f = CSP_DIR . '/assets/css/preloader.css';
		if ( is_readable( $f ) ) {
			$css = (string) file_get_contents( $f ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			$css = preg_replace( '#/\*.*?\*/#s', '', $css );
			$css = trim( preg_replace( '/\s+/', ' ', $css ) );
			echo '<style id="csp-preloader-css">' . str_replace( '</', '<\/', $css ) . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
		}
	},
	1
);

/** Preload the two above-the-fold fonts so they download in parallel with the CSS instead of after it. */
add_action(
	'wp_head',
	function () {
		foreach ( array( 'Manrope.woff2', 'PlayfairDisplay.woff2' ) as $font ) {
			if ( is_readable( CSP_DIR . '/assets/fonts/' . $font ) ) {
				echo '<link rel="preload" href="' . esc_url( CSP_URI . '/assets/fonts/' . $font ) . '" as="font" type="font/woff2" crossorigin>' . "
"; // phpcs:ignore WordPress.Security.EscapeOutput
			}
		}
	},
	1
);

// Strip WordPress front-end extras the design does not use.
add_action(
	'wp_enqueue_scripts',
	function () {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'classic-theme-styles' );
	},
	100
);
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'rest_output_link_wp_head' );
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
remove_action( 'wp_head', 'wp_resource_hints', 2 );
add_filter( 'the_generator', '__return_empty_string' );

/* -------------------------------------------------------------- Head extras */

// Skip the preloader animation on repeat visits (tiny inline script — must run before first paint).
add_action(
	'wp_head',
	function () {
		echo '<script>document.documentElement.classList.add("js");try{if(sessionStorage.getItem("plSeen"))document.documentElement.classList.add("pl-skip")}catch(e){}</script>' . "\n";
	},
	1
);

// Favicon: see inc/hardening.php (get_site_icon_url filter -> Theme Settings image or theme fallback).

// Preload the hero image for LCP.
add_action(
	'wp_head',
	function () {
		if ( is_front_page() ) {
			$id = csp_get( 'home_hero_poster' );
			if ( $id ) {
				$src    = wp_get_attachment_image_url( $id, 'full' );
				$srcset = wp_get_attachment_image_srcset( $id, 'full' );
				if ( $src ) {
					echo '<link rel="preload" as="image" href="' . esc_url( $src ) . '"' . ( $srcset ? ' imagesrcset="' . esc_attr( $srcset ) . '" imagesizes="100vw"' : '' ) . ' fetchpriority="high">' . "\n";
				}
			}
			return;
		}
		$map = array(
			'templates/about.php'    => 'about',
			'templates/products.php' => 'products',
			'templates/contact.php'  => 'contact',
			'templates/privacy.php'  => 'privacy',
			'templates/terms.php'    => 'terms',
		);
		foreach ( $map as $tpl => $prefix ) {
			if ( is_page_template( $tpl ) ) {
				$d = csp_get( $prefix . '_banner_image' );
				$m = csp_get( $prefix . '_banner_image_mobile' );
				if ( $d ) {
					echo '<link rel="preload" as="image" href="' . csp_img_url( $d ) . '" fetchpriority="high"' . ( $m ? ' media="(min-width:769px)"' : '' ) . '>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
				}
				if ( $m ) {
					echo '<link rel="preload" as="image" href="' . csp_img_url( $m ) . '" fetchpriority="high" media="(max-width:768px)">' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
				}
			}
		}
	},
	3
);

/** Preloader markup right after <body>. */
add_action(
	'wp_body_open',
	function () {
		$logo = csp_opt( 'opt_logo' );
		echo '<div id="preloader" aria-hidden="true"><div class="pl-disc"></div><div class="pl-logo"><span class="pl-ring"></span>';
		echo csp_img( $logo, array( 'alt' => '', 'loading' => 'eager', 'width' => 360, 'height' => 360, 'sizes' => '(max-width: 768px) 120px, 168px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
		echo '</div></div>' . "\n";
	}
);

/* ---------------------------------------------------------------- Security */

add_filter( 'xmlrpc_enabled', '__return_false' );

add_action(
	'send_headers',
	function () {
		if ( is_admin() ) {
			return;
		}
		header( 'X-Content-Type-Options: nosniff' );
		header( 'X-Frame-Options: SAMEORIGIN' );
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
		header( 'Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=()' );
		if ( is_ssl() ) {
			header( 'Strict-Transport-Security: max-age=31536000' );
		}
	}
);

// Hide user enumeration through the REST API and ?author= for visitors.
add_filter(
	'rest_endpoints',
	function ( $endpoints ) {
		if ( ! is_user_logged_in() ) {
			unset( $endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
		}
		return $endpoints;
	}
);
add_action(
	'template_redirect',
	function () {
		if ( is_author() ) {
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	}
);

/** Allow SVG uploads for administrators only (flags, partner marks, designer credit). */
add_filter(
	'upload_mimes',
	function ( $m ) {
		if ( current_user_can( 'manage_options' ) ) {
			$m['svg'] = 'image/svg+xml';
		}
		return $m;
	}
);
add_filter(
	'wp_check_filetype_and_ext',
	function ( $data, $file, $filename, $mimes ) {
		if ( 'svg' === strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) ) && current_user_can( 'manage_options' ) ) {
			$data = array( 'ext' => 'svg', 'type' => 'image/svg+xml', 'proper_filename' => $filename );
		}
		return $data;
	},
	10,
	4
);
add_filter(
	'wp_handle_upload_prefilter',
	function ( $file ) {
		if ( isset( $file['name'] ) && 'svg' === strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) ) ) {
			$body = file_get_contents( $file['tmp_name'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			if ( preg_match( '/<script|\son[a-z]+\s*=|javascript:|<foreignObject/i', (string) $body ) ) {
				$file['error'] = __( 'This SVG contains disallowed active content.', 'caspian-sun' );
			}
		}
		return $file;
	}
);

/** Comments are not used by this site. */
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );
add_filter( 'comments_array', '__return_empty_array', 10 );

/** Flush rewrite rules once after the seeder (post types / permalink structure changed). */
add_action(
	'init',
	function () {
		if ( get_option( 'csp_needs_flush' ) ) {
			flush_rewrite_rules( false );
			delete_option( 'csp_needs_flush' );
		}
	},
	99
);
