<?php
/**
 * Content seeder.
 *
 * Imports every text, image, video, menu and setting of the approved HTML
 * website into WordPress (Media Library + ACF fields) so the first
 * installation renders identically to the HTML design.
 *
 * Source data:  seed/data/seed.json   (generated from the HTML files)
 *               seed/media/…          (image / video files)
 *
 * Run:  wp csp seed [--force]     — or simply activate the theme.
 * The seeder is idempotent: re-running updates the same pages / posts /
 * products and re-uses already imported media. It never runs on its own
 * after the first successful import unless --force is given, so editor
 * changes are never overwritten.
 *
 * @package caspian-sun
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_switch_theme',
	function () {
		if ( ! get_option( 'csp_seeded' ) && function_exists( 'update_field' ) ) {
			csp_seed_run();
		}
	}
);

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command(
		'csp seed',
		function ( $args, $assoc ) {
			$force = ! empty( $assoc['force'] );
			if ( get_option( 'csp_seeded' ) && ! $force ) {
				WP_CLI::warning( 'Already seeded. Use --force to re-run (this overwrites field values with the original HTML content).' );
				return;
			}
			csp_seed_run( true );
			WP_CLI::success( 'Content imported.' );
		}
	);
}

/** Import (or reuse) a media file from seed/media and return its attachment ID. */
function csp_seed_media( $rel, $alt = '' ) {
	static $cache = array();
	if ( isset( $cache[ $rel ] ) ) {
		return $cache[ $rel ];
	}
	$existing = get_posts(
		array(
			'post_type'   => 'attachment',
			'post_status' => 'any',
			'meta_key'    => '_csp_seed_src', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'  => $rel, // phpcs:ignore WordPress.DB.SlowDBQuery
			'numberposts' => 1,
			'fields'      => 'ids',
		)
	);
	if ( $existing ) {
		return $cache[ $rel ] = (int) $existing[0];
	}
	$src = CSP_DIR . '/seed/media/' . $rel;
	if ( ! file_exists( $src ) ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$name = preg_replace( '#^(images/)#', '', $rel );
	$name = sanitize_file_name( str_replace( '/', '-', $name ) );
	$up   = wp_upload_dir();
	$file = wp_unique_filename( $up['path'], $name );
	$dest = trailingslashit( $up['path'] ) . $file;
	if ( ! copy( $src, $dest ) ) {
		return 0;
	}
	$type = wp_check_filetype( $file, array_merge( get_allowed_mime_types(), array( 'svg' => 'image/svg+xml' ) ) );
	$id   = wp_insert_attachment(
		array(
			'post_mime_type' => $type['type'] ? $type['type'] : 'application/octet-stream',
			'post_title'     => ucwords( str_replace( array( '-', '_' ), ' ', pathinfo( $file, PATHINFO_FILENAME ) ) ),
			'post_status'    => 'inherit',
		),
		$dest
	);
	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}
	if ( 'image/svg+xml' !== $type['type'] ) {
		$meta = wp_generate_attachment_metadata( $id, $dest );
		wp_update_attachment_metadata( $id, $meta );
	}
	if ( $alt ) {
		update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	}
	update_post_meta( $id, '_csp_seed_src', $rel );
	return $cache[ $rel ] = (int) $id;
}

/** Recursively swap @img:/@product:/@post: markers for real IDs. */
function csp_seed_resolve( $v, $alts ) {
	if ( is_array( $v ) ) {
		foreach ( $v as $k => $x ) {
			$v[ $k ] = csp_seed_resolve( $x, $alts );
		}
		return $v;
	}
	if ( ! is_string( $v ) ) {
		return $v;
	}
	if ( 0 === strpos( $v, '@img:' ) ) {
		$rel = substr( $v, 5 );
		return csp_seed_media( $rel, isset( $alts[ $rel ] ) ? $alts[ $rel ] : '' );
	}
	if ( 0 === strpos( $v, '@product:' ) ) {
		$p = get_page_by_path( substr( $v, 9 ), OBJECT, 'product' );
		return $p ? $p->ID : 0;
	}
	if ( 0 === strpos( $v, '@post:' ) ) {
		$p = get_page_by_path( substr( $v, 6 ), OBJECT, 'post' );
		return $p ? $p->ID : 0;
	}
	return $v;
}

/** Save an array of name => value ACF fields on an object. */
function csp_seed_fields( $fields, $post_id, $alts, $seo_type = '' ) {
	foreach ( $fields as $name => $value ) {
		$key = 0 === strpos( $name, 'seo_' ) ? csp_seo_key( $name, $seo_type ) : csp_fk( $name );
		update_field( $key, csp_seed_resolve( $value, $alts ), $post_id );
	}
}

function csp_seed_run( $verbose = false ) {
	if ( ! function_exists( 'update_field' ) ) {
		return;
	}
	@set_time_limit( 0 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	$log = function ( $m ) use ( $verbose ) {
		if ( $verbose && defined( 'WP_CLI' ) ) {
			WP_CLI::log( $m );
		}
	};
	$data = json_decode( file_get_contents( CSP_DIR . '/seed/data/seed.json' ), true );
	$alts = $data['media_alts'];

	/* ---- site settings & default content clean-up --------------------- */
	update_option( 'blogname', 'Caspian & Sun Food Trading LLC' );
	update_option( 'blogdescription', 'Premium Seafood, Meat & Poultry' );
	update_option( 'timezone_string', 'Asia/Dubai' );
	update_option( 'posts_per_page', 12 );
	update_option( 'default_comment_status', 'closed' );
	update_option( 'default_ping_status', 'closed' );
	update_option( 'blog_public', 0 ); // Staging: discourage indexing. Switch on at launch (Settings → Reading).
	update_option( 'permalink_structure', '/blog/%postname%/' );
	update_option( 'show_on_front', 'page' );

	foreach ( array( 'hello-world' => 'post', 'sample-page' => 'page', 'privacy-policy' => 'page' ) as $slug => $type ) {
		$old = get_page_by_path( $slug, OBJECT, $type );
		if ( $old && ! get_post_meta( $old->ID, '_csp_seed', true ) && ( 'privacy-policy' !== $slug || 'draft' === $old->post_status ) ) {
			wp_delete_post( $old->ID, true );
		}
	}

	/* ---- categories ---------------------------------------------------- */
	foreach ( $data['categories'] as $i => $c ) {
		$t = term_exists( $c['slug'], 'product_category' );
		if ( ! $t ) {
			$t = wp_insert_term( $c['name'], 'product_category', array( 'slug' => $c['slug'] ) );
		}
		if ( ! is_wp_error( $t ) ) {
			update_field( csp_fk( 'category_order' ), ( $i + 1 ) * 10, 'product_category_' . ( is_array( $t ) ? $t['term_id'] : $t ) );
		}
	}

	/* ---- products ------------------------------------------------------ */
	foreach ( $data['products'] as $p ) {
		$existing = get_page_by_path( $p['slug'], OBJECT, 'product' );
		$id       = $existing ? $existing->ID : wp_insert_post(
			array(
				'post_type'   => 'product',
				'post_status' => 'publish',
				'post_title'  => $p['title'],
				'post_name'   => $p['slug'],
				'menu_order'  => $p['menu_order'],
			)
		);
		wp_set_object_terms( $id, sanitize_title( $p['category'] ), 'product_category' );
		csp_seed_fields( $p['fields'], $id, $alts, 'product' );
		$log( 'product: ' . $p['slug'] );
	}

	/* ---- posts (Insights) --------------------------------------------- */
	foreach ( $data['posts'] as $p ) {
		$existing = get_page_by_path( $p['slug'], OBJECT, 'post' );
		$args     = array(
			'post_type'    => 'post',
			'post_status'  => 'publish',
			'post_title'   => $p['title'],
			'post_name'    => $p['slug'],
			'post_excerpt' => $p['excerpt'],
			'post_date'    => $p['date'],
			'post_date_gmt' => get_gmt_from_date( $p['date'] ),
		);
		$id = $existing ? $existing->ID : wp_insert_post( $args );
		csp_seed_fields( $p['fields'], $id, $alts, 'post' );
		$log( 'post: ' . $p['slug'] );
	}

	/* ---- pages --------------------------------------------------------- */
	$slugs = array( 'home' => 'home', 'about' => 'about', 'products' => 'products', 'contact' => 'contact', 'privacy' => 'privacy-policy', 'terms' => 'terms', 'blog' => 'blog' );
	$ids   = array();
	foreach ( $data['pages'] as $key => $pg ) {
		$slug     = $slugs[ $key ];
		$existing = get_page_by_path( $slug );
		$id       = $existing ? $existing->ID : wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => $pg['title'],
				'post_name'   => $slug,
			)
		);
		update_post_meta( $id, '_wp_page_template', $pg['template'] ? $pg['template'] : 'default' );
		update_post_meta( $id, '_csp_seed', 1 );
		$ids[ $key ] = $id;
	}
	update_option( 'page_on_front', $ids['home'] );
	update_option( 'page_for_posts', $ids['blog'] );
	update_option( 'wp_page_for_privacy_policy', $ids['privacy'] );
	foreach ( $data['pages'] as $key => $pg ) {
		csp_seed_fields( $pg['fields'], $ids[ $key ], $alts, 'page' );
		$log( 'page: ' . $key );
	}

	/* ---- theme settings ------------------------------------------------ */
	foreach ( $data['options'] as $name => $value ) {
		update_field( csp_fk( $name ), csp_seed_resolve( $value, $alts ), 'option' );
	}
	$log( 'options saved' );

	/* ---- menus --------------------------------------------------------- */
	$page_by_path = array(
		'/'                 => $ids['home'],
		'/products/'        => $ids['products'],
		'/about/'           => $ids['about'],
		'/blog/'            => $ids['blog'],
		'/contact/'         => $ids['contact'],
		'/privacy-policy/'  => $ids['privacy'],
		'/terms/'           => $ids['terms'],
	);
	$titles    = array(
		'header_left'     => 'Header — left',
		'header_right'    => 'Header — right',
		'footer_company'  => 'Footer — Company',
		'footer_products' => 'Footer — Products',
	);
	$locations = array();
	foreach ( $data['menus'] as $loc => $items ) {
		$menu = wp_get_nav_menu_object( $titles[ $loc ] );
		$mid  = $menu ? $menu->term_id : wp_create_nav_menu( $titles[ $loc ] );
		if ( $menu ) {
			foreach ( (array) wp_get_nav_menu_items( $mid ) as $old ) {
				wp_delete_post( $old->ID, true );
			}
		}
		foreach ( $items as $pos => $it ) {
			$item = array(
				'menu-item-title'  => $it['title'],
				'menu-item-status' => 'publish',
				'menu-item-position' => $pos + 1,
			);
			if ( isset( $page_by_path[ $it['url'] ] ) ) {
				$item += array( 'menu-item-type' => 'post_type', 'menu-item-object' => 'page', 'menu-item-object-id' => $page_by_path[ $it['url'] ] );
			} else {
				$item += array( 'menu-item-type' => 'custom', 'menu-item-url' => home_url( $it['url'] ) );
			}
			wp_update_nav_menu_item( $mid, 0, $item );
		}
		$locations[ $loc ] = $mid;
	}
	set_theme_mod( 'nav_menu_locations', $locations );

	update_option( 'csp_needs_flush', 1 ); // Flushed on the next request, once all post types are registered.
	update_option( 'csp_seeded', CSP_VERSION );
	$log( 'done' );
}
