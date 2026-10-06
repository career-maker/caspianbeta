<?php
/**
 * Template helpers. Every helper returns an empty string for empty input so
 * templates can simply skip markup when a field has been cleared.
 *
 * @package caspian-sun
 */

defined( 'ABSPATH' ) || exit;

/** Trimmed value of an ACF field (empty string when unset). */
function csp_get( $name, $post_id = null ) {
	if ( ! function_exists( 'get_field' ) ) {
		return '';
	}
	$v = get_field( $name, $post_id );
	if ( is_string( $v ) ) {
		return trim( $v );
	}
	return $v ? $v : '';
}

/** Article summary: the editable "Article summary" field, falling back to the WordPress excerpt (existing content). */
function csp_article_summary( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$s       = function_exists( 'get_field' ) ? get_field( 'article_summary', $post_id ) : '';
	return is_string( $s ) && '' !== trim( $s ) ? trim( $s ) : get_the_excerpt( $post_id );
}

/** Option-page field. */
function csp_opt( $name ) {
	return csp_get( $name, 'option' );
}

/**
 * Escape text, keep <b>/<strong>/<em>/<i>/<br> typed in the dashboard, convert new lines to <br>,
 * and turn [gold]words[/gold] (or [gold]words[gold]) into a golden highlight.
 */
function csp_br( $text ) {
	$allowed = array(
		'b'      => array(),
		'strong' => array(),
		'em'     => array(),
		'i'      => array(),
		'br'     => array(),
	);
	// Editors often type <br>bold text</br>; a <br> that is closed by </br> means bold.
	$text = preg_replace( '#<br\s*/?>(.*?)</br\s*>#is', '<strong>$1</strong>', (string) $text );
	$out  = nl2br( wp_kses( $text, $allowed ), false );
	return preg_replace( '#\[gold\](.*?)\[/?gold\]#is', '<span class="gold-text">$1</span>', $out );
}

/** Make a stored URL safe and absolute (supports "/path/", "#hash", tel:, mailto:). */
function csp_url( $url ) {
	$url = trim( (string) $url );
	if ( '' === $url ) {
		return '';
	}
	if ( '/' === $url[0] && ( ! isset( $url[1] ) || '/' !== $url[1] ) ) {
		$url = home_url( $url );
	}
	return esc_url( $url );
}

/** Normalise an ACF Link field into [ url, title, target ] (empty array when no URL). */
function csp_link( $link ) {
	if ( ! is_array( $link ) || empty( $link['url'] ) ) {
		return array();
	}
	return array(
		'url'    => csp_url( $link['url'] ),
		'title'  => isset( $link['title'] ) ? trim( $link['title'] ) : '',
		'target' => ! empty( $link['target'] ) ? $link['target'] : '',
	);
}

/** Attribute string for a link array (href + target/rel). Empty when no URL. */
function csp_link_attrs( $link, $extra = '' ) {
	$l = csp_link( $link );
	if ( ! $l ) {
		return '';
	}
	$a = 'href="' . $l['url'] . '"';
	if ( '_blank' === $l['target'] ) {
		$a .= ' target="_blank" rel="noopener"';
	}
	return $a . ( $extra ? ' ' . $extra : '' );
}

/**
 * Button in the theme's .btn-shop style. Renders nothing unless both a label
 * and a URL exist, so cleared buttons never point anywhere unexpected.
 */
function csp_btn( $link, $wrap_class = 'btn-shop', $aria = '', $a_class = '' ) {
	$l = csp_link( $link );
	if ( ! $l || '' === $l['title'] ) {
		return '';
	}
	$extra = ( $aria ? 'aria-label="' . esc_attr( $aria ) . '" ' : '' ) . ( $a_class ? 'class="' . esc_attr( $a_class ) . '"' : '' );
	return '<div class="' . esc_attr( $wrap_class ) . '"><a ' . csp_link_attrs( $link, $extra ) . '>' . esc_html( $l['title'] ) . '</a></div>';
}

/** <img> for an attachment ID (empty string if missing). SVGs are emitted without srcset. */
function csp_img( $id, $attrs = array(), $size = 'full' ) {
	$id = is_array( $id ) ? ( isset( $id['ID'] ) ? (int) $id['ID'] : 0 ) : (int) $id;
	if ( ! $id || ! get_post( $id ) ) {
		return '';
	}
	if ( 'image/svg+xml' === get_post_mime_type( $id ) ) {
		$url = wp_get_attachment_url( $id );
		if ( ! $url ) {
			return '';
		}
		$attrs += array(
			'alt'      => get_post_meta( $id, '_wp_attachment_image_alt', true ),
			'loading'  => 'lazy',
			'decoding' => 'async',
		);
		if ( empty( $attrs['width'] ) && empty( $attrs['height'] ) ) {
			$dims = csp_svg_dimensions( $id );
			if ( $dims ) {
				$attrs['width']  = $dims[0];
				$attrs['height'] = $dims[1];
			}
		}
		$html = '<img src="' . esc_url( $url ) . '"';
		foreach ( $attrs as $k => $v ) {
			$html .= ' ' . esc_attr( $k ) . '="' . esc_attr( $v ) . '"';
		}
		return $html . '>';
	}
	if ( ! wp_attachment_is_image( $id ) ) {
		return '';
	}
	return wp_get_attachment_image( $id, $size, false, $attrs );
}

/** Intrinsic size of an SVG attachment (from its width/height or viewBox), cached; array( w, h ) or empty. */
function csp_svg_dimensions( $id ) {
	$cached = get_post_meta( $id, '_csp_svg_dims', true );
	if ( is_array( $cached ) && 2 === count( $cached ) ) {
		return $cached;
	}
	$file = get_attached_file( $id );
	if ( ! $file || ! is_readable( $file ) ) {
		return array();
	}
	$head = (string) file_get_contents( $file, false, null, 0, 2048 ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	$w    = $h = 0;
	if ( preg_match( '/<svg[^>]*\swidth="([\d.]+)(?:px)?"/i', $head, $mw ) && preg_match( '/<svg[^>]*\sheight="([\d.]+)(?:px)?"/i', $head, $mh ) ) {
		$w = (float) $mw[1];
		$h = (float) $mh[1];
	} elseif ( preg_match( '/viewBox="[\d.\-]+[ ,]+[\d.\-]+[ ,]+([\d.]+)[ ,]+([\d.]+)"/i', $head, $mv ) ) {
		$w = (float) $mv[1];
		$h = (float) $mv[2];
	}
	if ( $w > 0 && $h > 0 ) {
		$dims = array( (int) round( $w ), (int) round( $h ) );
		update_post_meta( $id, '_csp_svg_dims', $dims );
		return $dims;
	}
	return array();
}

/** True when a (PNG/WebP) logo is a solid emblem — an opaque centre that would turn into a plain white disc if rendered as a white silhouette. Cached. */
function csp_logo_is_solid( $id ) {
	$id     = is_array( $id ) ? ( isset( $id['ID'] ) ? (int) $id['ID'] : 0 ) : (int) $id;
	$cached = $id ? get_post_meta( $id, '_csp_logo_solid', true ) : '';
	if ( '' !== $cached ) {
		return '1' === $cached;
	}
	$solid = false;
	$file  = $id ? get_attached_file( $id ) : '';
	if ( $file && is_readable( $file ) && function_exists( 'imagecreatefromstring' ) && in_array( get_post_mime_type( $id ), array( 'image/png', 'image/webp' ), true ) ) {
		$im = @imagecreatefromstring( (string) file_get_contents( $file ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions, WordPress.PHP.NoSilencedErrors
		if ( $im ) {
			$w = imagesx( $im );
			$h = imagesy( $im );
			$opaque = 0;
			$total  = 0;
			for ( $gx = 0; $gx < 12; $gx++ ) {
				for ( $gy = 0; $gy < 12; $gy++ ) {
					$px = (int) ( $w * ( 0.3 + 0.4 * $gx / 11 ) );
					$py = (int) ( $h * ( 0.3 + 0.4 * $gy / 11 ) );
					$a  = ( imagecolorat( $im, min( $px, $w - 1 ), min( $py, $h - 1 ) ) >> 24 ) & 127; // 0 = opaque, 127 = transparent.
					++$total;
					if ( $a <= 10 ) {
						++$opaque;
					}
				}
			}
			$solid = $total && ( $opaque / $total ) >= 0.9;
			imagedestroy( $im );
		}
	}
	if ( $id ) {
		update_post_meta( $id, '_csp_logo_solid', $solid ? '1' : '0' );
	}
	return $solid;
}

/** Attachment URL or empty string. */
function csp_img_url( $id, $size = 'full' ) {
	$id = is_array( $id ) ? ( isset( $id['ID'] ) ? (int) $id['ID'] : 0 ) : (int) $id;
	if ( ! $id ) {
		return '';
	}
	$url = 'full' === $size ? wp_get_attachment_url( $id ) : wp_get_attachment_image_url( $id, $size );
	return $url ? esc_url( $url ) : '';
}

/** CSS declaration setting a custom property to an image URL ('' when no image). */
function csp_bg_var( $var, $id ) {
	$url = csp_img_url( $id );
	return $url ? '--' . $var . ":url('" . $url . "')" : '';
}

/** Build a style attribute from non-empty declarations. */
function csp_style_attr( $decls ) {
	$decls = array_filter( (array) $decls );
	return $decls ? ' style="' . esc_attr( implode( ';', $decls ) ) . '"' : '';
}

/** Section heading: <hX class="section-heading …">text</hX>. Empty text renders nothing. */
function csp_heading( $text, $level = 2, $mods = '', $extra_class = '' ) {
	$text = trim( (string) $text );
	if ( '' === $text ) {
		return '';
	}
	$class = trim( 'section-heading ' . $mods . ' ' . $extra_class );
	return sprintf( '<h%1$d class="%2$s">%3$s</h%1$d>', (int) $level, esc_attr( $class ), csp_br( $text ) );
}

/** Kept for older templates: csp_br() now handles [gold] itself. */
function csp_gold( $text ) {
	return csp_br( $text );
}

/** Eyebrow line. */
function csp_eyebrow( $text, $class = '' ) {
	$text = trim( (string) $text );
	return '' === $text ? '' : '<div class="eyebrow' . ( $class ? ' ' . esc_attr( $class ) : '' ) . '">' . esc_html( $text ) . '</div>';
}

/** Paragraph. */
function csp_p( $text, $class = '' ) {
	$text = trim( (string) $text );
	return '' === $text ? '' : '<p' . ( $class ? ' class="' . esc_attr( $class ) . '"' : '' ) . '>' . csp_br( $text ) . '</p>';
}

/** "View More" link (or a plain span when no URL is given). Empty label renders nothing. */
function csp_view_more( $url, $label, $aria = '', $tag = 'a', $arrow = false ) {
	if ( '' === (string) $label ) {
		return '';
	}
	$circle = $arrow ? '<span class="vm-circle"><svg viewBox="0 0 24 24" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>' : '<span class="vm-circle"></span>';
	$inner  = esc_html( $label ) . ' <span class="vm-dash"></span>' . $circle;
	if ( 'span' === $tag || ! $url ) {
		return '<span class="view-more">' . $inner . '</span>';
	}
	return '<a class="view-more" href="' . esc_url( $url ) . '"' . ( $aria ? ' aria-label="' . esc_attr( $aria ) . '"' : '' ) . '>' . $inner . '</a>';
}

/** Paragraphs from a repeater with a `text` sub field. */
function csp_paras( $rows, $key = 'text' ) {
	$out = '';
	foreach ( (array) $rows as $r ) {
		$out .= csp_p( isset( $r[ $key ] ) ? $r[ $key ] : '' );
	}
	return $out;
}

/**
 * Inner-page banner. Background image is supplied through CSS custom
 * properties (--hero-bg / --hero-bg-sm) consumed by the page stylesheet.
 */
function csp_banner( $prefix, $h1 = true, $post_id = null, $img_override = 0 ) {
	$title = csp_get( $prefix . '_banner_heading', $post_id );
	$text  = csp_get( $prefix . '_banner_text', $post_id );
	$img   = $img_override ? $img_override : csp_get( $prefix . '_banner_image', $post_id );
	$imgm  = csp_get( $prefix . '_banner_image_mobile', $post_id );
	$style = csp_style_attr( array( csp_bg_var( 'hero-bg', $img ), csp_bg_var( 'hero-bg-sm', ( $imgm && ! $img_override ) ? $imgm : $img ) ) );
	echo '<section class="hero"' . $style . '><div class="hero-inner">'; // phpcs:ignore WordPress.Security.EscapeOutput
	if ( $title ) {
		echo $h1 ? '<h1>' . csp_br( $title ) . '</h1>' : '<div class="hero-title">' . csp_br( $title ) . '</div>'; // phpcs:ignore
	}
	echo csp_p( $text ); // phpcs:ignore
	echo '</div></section>';
}

/** Breadcrumb bar. $trail = array( array( label, url ), … ); the last item is the current page. */
function csp_breadcrumb( $trail ) {
	$home = csp_opt( 'opt_breadcrumb_home' );
	echo '<div class="crumbs" role="navigation" aria-label="' . esc_attr__( 'Breadcrumb', 'caspian-sun' ) . '">';
	if ( '' !== $home ) {
		echo '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html( $home ) . '</a><span class="sep"></span>';
	}
	$n = count( $trail );
	foreach ( $trail as $i => $t ) {
		if ( $i === $n - 1 || empty( $t[1] ) ) {
			echo '<span class="current">' . esc_html( $t[0] ) . '</span>';
		} else {
			echo '<a href="' . esc_url( $t[1] ) . '">' . esc_html( $t[0] ) . '</a><span class="sep"></span>';
		}
	}
	echo '</div>';
}

/** Published page ID assigned to a page template (cached). */
function csp_page_by_template( $template ) {
	static $cache = array();
	if ( ! isset( $cache[ $template ] ) ) {
		$p = get_posts(
			array(
				'post_type'   => 'page',
				'meta_key'    => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'  => $template, // phpcs:ignore WordPress.DB.SlowDBQuery
				'numberposts' => 1,
				'post_status' => 'publish',
				'fields'      => 'ids',
			)
		);
		$cache[ $template ] = $p ? (int) $p[0] : 0;
	}
	return $cache[ $template ];
}
