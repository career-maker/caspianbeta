<?php
/**
 * Lightweight SEO layer: titles, meta description, Open Graph / Twitter
 * cards and JSON-LD. Index/noindex follows WordPress' own
 * Settings → Reading → "Discourage search engines" switch, canonical URLs,
 * sitemap (/wp-sitemap.xml) and robots.txt come from WordPress core.
 *
 * @package caspian-sun
 */

defined( 'ABSPATH' ) || exit;

/** ID of the queried object that carries the SEO fields (0 on archives/404). */
function csp_seo_object_id() {
	if ( is_front_page() && ! is_home() ) {
		return (int) get_option( 'page_on_front' );
	}
	if ( is_home() ) {
		return (int) get_option( 'page_for_posts' );
	}
	return is_singular() ? get_queried_object_id() : 0;
}

function csp_site_name() {
	$n = csp_opt( 'seo_site_name' );
	return $n ? $n : get_bloginfo( 'name' );
}

add_filter(
	'pre_get_document_title',
	function ( $title ) {
		$id = csp_seo_object_id();
		if ( $id ) {
			$custom = csp_get( 'seo_title', $id );
			if ( $custom ) {
				return $custom;
			}
		}
		if ( is_front_page() ) {
			return csp_site_name();
		}
		if ( is_404() ) {
			return __( 'Page not found', 'caspian-sun' ) . ' | ' . csp_site_name();
		}
		if ( is_singular() || is_home() ) {
			$t = is_home() ? get_the_title( (int) get_option( 'page_for_posts' ) ) : single_post_title( '', false );
			return $t . ' | ' . csp_site_name();
		}
		return $title;
	}
);

function csp_seo_description() {
	$id = csp_seo_object_id();
	if ( $id ) {
		$d = csp_get( 'seo_description', $id );
		if ( $d ) {
			return $d;
		}
		if ( is_singular( 'post' ) && has_excerpt( $id ) ) {
			return wp_strip_all_tags( get_the_excerpt( $id ) );
		}
		if ( is_singular( 'product' ) ) {
			$d = csp_get( 'card_summary', $id );
			if ( $d ) {
				return wp_trim_words( $d, 32, '…' );
			}
		}
	}
	return csp_opt( 'seo_default_description' );
}

function csp_seo_image_id() {
	$id = csp_seo_object_id();
	if ( $id ) {
		$img = csp_get( 'seo_image', $id );
		if ( $img ) {
			return $img;
		}
		if ( is_singular( 'post' ) ) {
			$img = csp_get( 'article_image', $id );
		} elseif ( is_singular( 'product' ) ) {
			$g   = (array) get_field( 'gallery', $id );
			$img = $g ? reset( $g ) : csp_get( 'card_image', $id );
		}
		if ( $img ) {
			return $img;
		}
	}
	return csp_opt( 'seo_default_image' );
}

add_action(
	'wp_head',
	function () {
		$desc  = csp_seo_description();
		$title = wp_get_document_title();
		$url   = is_singular() ? get_permalink() : ( is_front_page() ? home_url( '/' ) : '' );
		$img   = csp_seo_image_id();
		$imgu  = $img ? wp_get_attachment_image_src( $img, 'large' ) : false;

		if ( $desc ) {
			echo '<meta name="description" content="' . esc_attr( wp_strip_all_tags( $desc ) ) . '">' . "\n";
		}
		echo '<meta property="og:locale" content="' . esc_attr( str_replace( '-', '_', get_bloginfo( 'language' ) ) ) . '">' . "\n";
		echo '<meta property="og:type" content="' . ( is_singular( 'post' ) ? 'article' : 'website' ) . '">' . "\n";
		echo '<meta property="og:site_name" content="' . esc_attr( csp_site_name() ) . '">' . "\n";
		echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
		if ( $desc ) {
			echo '<meta property="og:description" content="' . esc_attr( wp_strip_all_tags( $desc ) ) . '">' . "\n";
		}
		if ( $url ) {
			echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
		}
		if ( $imgu ) {
			echo '<meta property="og:image" content="' . esc_url( $imgu[0] ) . '">' . "\n";
			echo '<meta property="og:image:width" content="' . (int) $imgu[1] . '">' . "\n";
			echo '<meta property="og:image:height" content="' . (int) $imgu[2] . '">' . "\n";
		}
		echo '<meta name="twitter:card" content="' . ( $imgu ? 'summary_large_image' : 'summary' ) . '">' . "\n";
		echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
		if ( $desc ) {
			echo '<meta name="twitter:description" content="' . esc_attr( wp_strip_all_tags( $desc ) ) . '">' . "\n";
		}
		if ( is_home() && ! is_front_page() ) {
			echo '<link rel="canonical" href="' . esc_url( get_pagenum_link( max( 1, (int) get_query_var( 'paged' ) ) ) ) . '">' . "
";
		}
		if ( $imgu ) {
			echo '<meta name="twitter:image" content="' . esc_url( $imgu[0] ) . '">' . "\n";
		}

		$ld = csp_jsonld();
		if ( $ld ) {
			echo '<script type="application/ld+json">' . wp_json_encode( $ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
		}
	},
	5
);

/** Structured data graph for the current view. */
function csp_jsonld() {
	$logo_id = csp_opt( 'opt_logo' );
	$org     = array(
		'@type' => 'Organization',
		'@id'   => home_url( '/#organization' ),
		'name'  => csp_opt( 'seo_org_name' ) ? csp_opt( 'seo_org_name' ) : csp_site_name(),
		'url'   => home_url( '/' ),
	);
	if ( $logo_id ) {
		$org['logo'] = csp_img_url( $logo_id );
	}
	if ( csp_opt( 'seo_org_founded' ) ) {
		$org['foundingDate'] = csp_opt( 'seo_org_founded' );
	}
	if ( csp_opt( 'opt_email' ) ) {
		$org['email'] = csp_opt( 'opt_email' );
	}
	if ( csp_opt( 'opt_phone_tel' ) ) {
		$org['telephone'] = csp_opt( 'opt_phone_tel' );
	}
	if ( csp_opt( 'opt_address' ) ) {
		$org['address'] = trim( preg_replace( '/\s*[\r\n]+\s*/', ' ', csp_opt( 'opt_address' ) ) );
	}
	$same = array();
	foreach ( array_filter( (array) get_field( 'opt_social', 'option' ) ) as $s ) {
		if ( ! empty( $s['url'] ) && 'whatsapp' !== $s['network'] ) {
			$same[] = $s['url'];
		}
	}
	if ( $same ) {
		$org['sameAs'] = $same;
	}
	$graph = array();
	if ( is_front_page() ) {
		$graph[] = $org;
		$graph[] = array(
			'@type' => 'WebSite',
			'@id'   => home_url( '/#website' ),
			'url'   => home_url( '/' ),
			'name'  => csp_site_name(),
		);
	} elseif ( is_singular( 'product' ) ) {
		$g       = (array) get_field( 'gallery' );
		$graph[] = array_filter(
			array(
				'@type'       => 'Product',
				'name'        => get_the_title(),
				'description' => wp_strip_all_tags( csp_get( 'description' ) ),
				'image'       => $g ? csp_img_url( reset( $g ) ) : '',
				'brand'       => array( '@type' => 'Organization', 'name' => csp_site_name() ),
			)
		);
	} elseif ( is_singular( 'post' ) ) {
		$graph[] = array_filter(
			array(
				'@type'         => 'Article',
				'headline'      => get_the_title(),
				'datePublished' => get_the_date( 'c' ),
				'dateModified'  => get_the_modified_date( 'c' ),
				'author'        => array( '@type' => 'Organization', 'name' => csp_get( 'article_author' ) ? csp_get( 'article_author' ) : csp_site_name() ),
				'publisher'     => array( '@id' => home_url( '/#organization' ) ),
				'image'         => csp_img_url( csp_get( 'article_image' ) ),
				'mainEntityOfPage' => get_permalink(),
			)
		);
		$graph[] = $org;
	}
	return $graph ? array( '@context' => 'https://schema.org', '@graph' => $graph ) : array();
}

// Keep users and non-content types out of the XML sitemap.
add_filter(
	'wp_sitemaps_add_provider',
	function ( $provider, $name ) {
		return 'users' === $name ? false : $provider;
	},
	10,
	2
);
add_filter(
	'wp_sitemaps_taxonomies',
	function () {
		return array();
	}
);
