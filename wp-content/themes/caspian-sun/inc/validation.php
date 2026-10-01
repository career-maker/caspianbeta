<?php
/**
 * Strict server-side validation for the enquiry form (mirrored by assets/js/contact.js).
 * Every validator takes the RAW submitted string and returns array( cleaned value, error message ).
 *
 * @package caspian-sun
 */

defined( 'ABSPATH' ) || exit;

/** Trim every kind of whitespace (incl. NBSP, tabs, newlines) from both ends. */
function csp_v_trim( $s ) {
	return preg_replace( '/^[\s\x{00A0}\x{200B}]+|[\s\x{00A0}\x{200B}]+$/u', '', (string) $s );
}

/** Script / markup / SQL / template-injection patterns. */
function csp_v_injection( $s ) {
	return (bool) preg_match(
		'/<\s*\/?\s*[a-z!?]'
		. '|javascript\s*:|vbscript\s*:|data\s*:\s*text\/html'
		. '|\bon(?:error|load|click|mouse\w*|focus|blur|key\w*|change|submit|input|abort|toggle|animation\w*|pointer\w*)\s*='
		. '|\{\{|\}\}|\{%|%\}|\$\{'
		. '|\bunion\s+(?:all\s+)?select\b|\b(?:drop|truncate)\s+(?:table|database)\b|\bdelete\s+from\b|\binsert\s+into\b'
		. '|;\s*--|--\s*$|\/\*|\*\/'
		. '|[\'"]\s*or\s+[\'"]?\w+[\'"]?\s*=\s*[\'"]?\w+/i',
		$s
	);
}

function csp_v_alnum_count( $s ) {
	return preg_match_all( '/[\p{L}\p{N}]/u', $s );
}

function csp_v_name( $raw ) {
	$v = preg_replace( '/\s+/u', ' ', csp_v_trim( $raw ) );
	if ( '' === $v ) {
		return array( '', __( 'Please enter your name.', 'caspian-sun' ) );
	}
	if ( mb_strlen( $v ) < 2 ) {
		return array( $v, __( 'Name must be at least 2 characters.', 'caspian-sun' ) );
	}
	if ( mb_strlen( $v ) > 100 ) {
		return array( $v, __( 'Name is too long (100 characters maximum).', 'caspian-sun' ) );
	}
	if ( ! preg_match( '/^[\p{L}\p{M}][\p{L}\p{M} \'’.\-]*$/u', $v ) || preg_match_all( '/\p{L}/u', $v ) < 2 ) {
		return array( $v, __( 'Name can only contain letters, spaces, apostrophes, hyphens and full stops.', 'caspian-sun' ) );
	}
	return array( $v, '' );
}

function csp_v_phone( $raw ) {
	$v = preg_replace( '/\s+/u', ' ', csp_v_trim( $raw ) );
	if ( '' === $v ) {
		return array( '', __( 'Please enter your phone number.', 'caspian-sun' ) );
	}
	if ( ! preg_match( '/^\+?[0-9() .\-]+$/', $v ) ) {
		return array( $v, __( 'Phone number can only contain digits, spaces, ( ) - and a single leading +.', 'caspian-sun' ) );
	}
	$digits = preg_replace( '/\D/', '', $v );
	if ( strlen( $digits ) < 7 || strlen( $digits ) > 15 ) {
		return array( $v, __( 'Phone number must have between 7 and 15 digits.', 'caspian-sun' ) );
	}
	if ( 1 === count( array_unique( str_split( $digits ) ) ) ) {
		return array( $v, __( 'Please enter a valid phone number.', 'caspian-sun' ) );
	}
	return array( $v, '' );
}

function csp_v_email( $raw ) {
	$v = csp_v_trim( $raw );
	if ( '' === $v ) {
		return array( '', __( 'Please enter your email address.', 'caspian-sun' ) );
	}
	if ( strlen( $v ) > 254 ) {
		return array( $v, __( 'Email address is too long.', 'caspian-sun' ) );
	}
	$re = '/^[A-Za-z0-9](?:[A-Za-z0-9._%+\-]{0,62}[A-Za-z0-9])?@(?:[A-Za-z0-9](?:[A-Za-z0-9\-]{0,61}[A-Za-z0-9])?\.)+[A-Za-z]{2,24}$/';
	if ( ! preg_match( $re, $v ) || false !== strpos( $v, '..' ) || ! is_email( $v ) ) {
		return array( $v, __( 'Please enter a valid email address (e.g. name@example.com).', 'caspian-sun' ) );
	}
	return array( $v, '' );
}

/** Free text (message / subject). */
function csp_v_text( $raw, $label, $min, $max, $multiline ) {
	$v = csp_v_trim( str_replace( array( "\r\n", "\r" ), "\n", (string) $raw ) );
	if ( '' === $v ) {
		/* translators: %s: field label */
		return array( '', sprintf( __( 'Please enter your %s.', 'caspian-sun' ), $label ) );
	}
	if ( ! $multiline ) {
		$v = preg_replace( '/\s+/u', ' ', $v );
	}
	if ( preg_match( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $v ) ) {
		/* translators: %s: field label */
		return array( $v, sprintf( __( 'Your %s contains invalid characters.', 'caspian-sun' ), $label ) );
	}
	if ( mb_strlen( $v ) < $min ) {
		/* translators: 1: field label, 2: minimum length */
		return array( $v, sprintf( __( 'Your %1$s must be at least %2$d characters.', 'caspian-sun' ), $label, $min ) );
	}
	if ( mb_strlen( $v ) > $max ) {
		/* translators: 1: field label, 2: maximum length */
		return array( $v, sprintf( __( 'Your %1$s is too long (%2$d characters maximum).', 'caspian-sun' ), $label, $max ) );
	}
	if ( csp_v_injection( $v ) ) {
		/* translators: %s: field label */
		return array( $v, sprintf( __( 'Your %s contains code or characters that are not allowed.', 'caspian-sun' ), $label ) );
	}
	if ( csp_v_alnum_count( $v ) < 2 ) {
		/* translators: %s: field label */
		return array( $v, sprintf( __( 'Please enter a meaningful %s (letters or numbers, not only symbols).', 'caspian-sun' ), $label ) );
	}
	return array( $v, '' );
}

/** Published products with their packing options: array( title => array( size, ... ) ). */
function csp_enquiry_products() {
	$out = array();
	$ids = get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 300,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);
	foreach ( $ids as $id ) {
		$sizes = array();
		if ( function_exists( 'get_field' ) ) {
			foreach ( (array) get_field( 'packing', $id ) as $row ) {
				if ( ! empty( $row['size'] ) ) {
					$sizes[] = (string) $row['size'];
				}
			}
		}
		$out[ html_entity_decode( get_the_title( $id ), ENT_QUOTES, 'UTF-8' ) ] = $sizes;
	}
	return $out;
}
