<?php
/**
 * Icon library. Icons are part of the approved design (inline SVG that
 * inherits colour from CSS). Editors choose an icon by name from a select
 * field, so no raw SVG is ever pasted into the admin.
 *
 * @package caspian-sun
 */

defined( 'ABSPATH' ) || exit;

/** Shared attribute set for the 24px stroke icons used on the About page. */
function csp_icon_stroke_attrs( $w = '1.6' ) {
	return 'viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="' . $w . '" stroke-linecap="round" stroke-linejoin="round"';
}

/** Raw icon definitions: name => [ label, svg-attributes, inner markup ]. */
function csp_icon_defs() {
	static $defs = null;
	if ( null !== $defs ) {
		return $defs;
	}
	$s = csp_icon_stroke_attrs();
	$phone_path = 'M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z';
	$defs = array(
		// Contact / social glyphs.
		'phone'        => array( 'Phone', 'viewBox="0 0 24 24"', '<path d="' . $phone_path . '" fill="none" stroke-width="1.5"/>' ),
		'phone-footer' => array( 'Phone (footer style)', 'viewBox="0 0 24 24"', '<path d="' . $phone_path . '" stroke-width="1.5"/>' ),
		'phone-line'   => array( 'Phone (outline)', csp_icon_stroke_attrs( '1.8' ), '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/>' ),
		'mail'         => array( 'Email', 'viewBox="0 0 24 24"', '<path d="M4 4h16v16H4z" stroke-width="1.5"/><path d="M4 6l8 6 8-6" stroke-width="1.5"/>' ),
		'pin'          => array( 'Map pin', 'viewBox="0 0 24 24"', '<path d="M12 21s7-6.5 7-11.5a7 7 0 10-14 0C5 14.5 12 21 12 21z" stroke-width="1.5"/><circle cx="12" cy="9.5" r="2.3" stroke-width="1.5"/>' ),
		'pin-large'    => array( 'Map pin (large)', 'viewBox="0 0 24 24" fill="none" stroke-width="1.6"', '<path d="M12 22s7-6.5 7-12a7 7 0 0 0-14 0c0 5.5 7 12 7 12z"/><circle cx="12" cy="10" r="2.5"/>' ),
		'whatsapp'     => array( 'WhatsApp', 'viewBox="0 0 24 24" fill="currentColor"', '<path d="M12 2a10 10 0 00-8.5 15.2L2 22l4.9-1.5A10 10 0 1012 2zm0 18.2c-1.6 0-3.1-.4-4.4-1.2l-.3-.2-3 .9.9-2.9-.2-.3A8.2 8.2 0 1112 20.2zm4.5-6.1c-.2-.1-1.4-.7-1.7-.8-.2-.1-.4-.1-.6.1-.2.2-.6.8-.8 1-.1.2-.3.2-.5.1-.2-.1-1-.4-2-1.2-.7-.6-1.2-1.4-1.4-1.6-.1-.2 0-.4.1-.5l.4-.5c.1-.1.2-.3.2-.4.1-.2 0-.3 0-.4-.1-.1-.6-1.4-.8-1.9-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.4.1-.6.3-.2.2-.8.8-.8 1.9s.8 2.2.9 2.4c.1.2 1.6 2.4 3.8 3.4.5.2.9.4 1.3.5.5.2 1 .1 1.4.1.4-.1 1.4-.6 1.6-1.1.2-.5.2-1 .1-1.1-.1-.1-.2-.2-.4-.3z"/>' ),
		'facebook'     => array( 'Facebook', 'viewBox="0 0 24 24" fill="currentColor"', '<path d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5.02 3.66 9.18 8.44 9.94v-7.03H7.9v-2.91h2.54V9.85c0-2.51 1.49-3.9 3.77-3.9 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56v1.89h2.78l-.45 2.91h-2.33V22c4.78-.76 8.44-4.92 8.44-9.94z"/>' ),
		'linkedin'     => array( 'LinkedIn', 'viewBox="0 0 24 24" fill="currentColor"', '<path d="M20.45 20.45h-3.56v-5.57c0-1.33-.02-3.03-1.85-3.03-1.86 0-2.14 1.45-2.14 2.94v5.66H9.34V9h3.42v1.56h.05c.48-.9 1.64-1.85 3.38-1.85 3.6 0 4.27 2.37 4.27 5.46zM5.34 7.43a2.07 2.07 0 110-4.13 2.07 2.07 0 010 4.13zM7.12 20.45H3.56V9h3.56z"/>' ),
		'instagram'    => array( 'Instagram', 'viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"', '<rect x="2.5" y="2.5" width="19" height="19" rx="5"/><circle cx="12" cy="12" r="4.3"/><circle cx="17.4" cy="6.6" r="1.1" fill="currentColor" stroke="none"/>' ),
		'x'            => array( 'X (Twitter)', 'viewBox="0 0 24 24" fill="currentColor"', '<path d="M18.24 2.5h3.3l-7.2 8.23 8.47 11.27h-6.63l-5.2-6.8-5.94 6.8H1.72l7.7-8.8L1.3 2.5h6.8l4.7 6.22zm-1.16 17.6h1.83L6.9 4.4H4.94z"/>' ),
		// Feature / service glyphs.
		'truck'        => array( 'Truck / delivery', $s, '<rect x="1" y="7" width="13" height="9" rx="1"/><path d="M14 10h4l3 3v3h-7z"/><circle cx="6" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/>' ),
		'globe'        => array( 'Globe', $s, '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 3.8 5.6 3.8 9S14.5 18.4 12 21c-2.5-2.6-3.8-5.6-3.8-9S9.5 5.6 12 3z"/>' ),
		'box'          => array( 'Box / product range', $s, '<path d="M3 8l9-5 9 5-9 5-9-5z"/><path d="M3 8v8l9 5 9-5V8M12 13v8"/>' ),
		'search'       => array( 'Search', $s, '<circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>' ),
		'link'         => array( 'Link / chain', $s, '<path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1"/><path d="M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/>' ),
		'check'        => array( 'Check mark', $s, '<path d="M5 12.5l4.5 4.5L19 7.5"/>' ),
		'clock'        => array( 'Clock', $s, '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.2 2"/>' ),
		'calendar'     => array( 'Calendar check', $s, '<rect x="3.5" y="5" width="17" height="15" rx="2"/><path d="M3.5 10h17M8 3v4M16 3v4"/><path d="M8.5 15l2.5 2.5L15.5 13"/>' ),
		'bolt'         => array( 'Lightning bolt', $s, '<path d="M13 2L4.5 13.5H11L10 22l8.5-11.5H12z"/>' ),
		// Product page assurances.
		'leaf'         => array( 'Leaf (responsibly sourced)', 'viewBox="0 0 24 24"', '<path d="M4 20C4 10 9 4 20 4c0 10-5 16-14 16"/><path d="M4 20c2.5-5.5 6.5-9 11-11"/>' ),
		'handling'     => array( 'Hand + package (careful handling)', 'viewBox="0 0 24 24"', '<path d="M8 3.5l4-1.8 4 1.8v4.2l-4 1.8-4-1.8z"/><path d="M8 3.5l4 1.8 4-1.8M12 5.3v4.2"/><path d="M2 15h4l4 1.5h4.5a1.5 1.5 0 0 1 0 3H10"/><path d="M14.5 17.5l5-2.6a1.6 1.6 0 0 1 1.8 2.6L15 21.3H8L6 20.3H2"/>' ),
		'global-std'   => array( 'Globe + check (global standards)', 'viewBox="0 0 24 24"', '<circle cx="11" cy="11" r="8"/><path d="M3 11h16M11 3c2.3 2.4 3.4 5 3.4 8s-1.1 5.6-3.4 8c-2.3-2.4-3.4-5-3.4-8s1.1-5.6 3.4-8z"/><circle cx="18" cy="18" r="4.3" fill="#fcfaf8"/><path d="M16 18l1.5 1.5 2.6-3"/>' ),
	);
	return $defs;
}

/** Select choices for ACF icon fields. */
function csp_icon_choices( $only = null ) {
	$out = array();
	foreach ( csp_icon_defs() as $k => $d ) {
		if ( null === $only || in_array( $k, $only, true ) ) {
			$out[ $k ] = $d[0];
		}
	}
	return $out;
}

/** Render an icon by name. Unknown / empty name renders nothing. */
function csp_icon( $name, $extra_attrs = '' ) {
	$defs = csp_icon_defs();
	if ( ! $name || ! isset( $defs[ $name ] ) ) {
		return '';
	}
	list( , $attrs, $inner ) = $defs[ $name ];
	return '<svg ' . $attrs . ( $extra_attrs ? ' ' . $extra_attrs : '' ) . ' aria-hidden="true" focusable="false">' . $inner . '</svg>';
}

/**
 * Country flags for the product "Origin" row (inline SVG, circular clip).
 * Matches the approved design; unknown countries simply show no flag.
 */
function csp_flag_svg( $country ) {
	static $flags = null;
	if ( null === $flags ) {
		$usa   = '<rect width="24" height="24" fill="#fff"/><g fill="#B22234"><rect y="0" width="24" height="3.4"/><rect y="6.9" width="24" height="3.4"/><rect y="13.7" width="24" height="3.4"/><rect y="20.6" width="24" height="3.4"/></g><rect width="12" height="13.7" fill="#3C3B6E"/>';
		$flags = array(
			'Russia'    => '<rect width="24" height="8" fill="#fff"/><rect y="8" width="24" height="8" fill="#0039A6"/><rect y="16" width="24" height="8" fill="#D52B1E"/>',
			'Norway'    => '<rect width="24" height="24" fill="#BA0C2F"/><rect x="6" width="5" height="24" fill="#fff"/><rect y="9.5" width="24" height="5" fill="#fff"/><rect x="7.3" width="2.4" height="24" fill="#00205B"/><rect y="10.8" width="24" height="2.4" fill="#00205B"/>',
			'Canada'    => '<rect width="24" height="24" fill="#fff"/><rect width="6" height="24" fill="#D80621"/><rect x="18" width="6" height="24" fill="#D80621"/><path d="M12 6l1.6 3.4 2.4-.8-.8 3.2 2 1-3.6 1.6.4 2.6H12h-2.0l.4-2.6L6.8 12.8l2-1-.8-3.2 2.4.8z" fill="#D80621"/>',
			'France'    => '<rect width="8" height="24" fill="#0055A4"/><rect x="8" width="8" height="24" fill="#fff"/><rect x="16" width="8" height="24" fill="#EF4135"/>',
			'Japan'     => '<rect width="24" height="24" fill="#fff"/><circle cx="12" cy="12" r="6" fill="#BC002D"/>',
			'Spain'     => '<rect width="24" height="24" fill="#C60B1E"/><rect y="6" width="24" height="12" fill="#FFC400"/>',
			'USA'       => $usa,
			'Alaska'    => $usa,
			'Australia' => '<rect width="24" height="24" fill="#00008B"/><rect width="12" height="12" fill="#00008B"/><path d="M0 0l12 12M12 0L0 12" stroke="#fff" stroke-width="2"/><path d="M6 0v12M0 6h12" stroke="#fff" stroke-width="3.4"/><path d="M6 0v12M0 6h12" stroke="#CC142B" stroke-width="1.8"/><circle cx="8" cy="19" r="1.6" fill="#fff"/><circle cx="18" cy="8" r="1" fill="#fff"/><circle cx="17" cy="19" r="1" fill="#fff"/><circle cx="21" cy="14" r="1" fill="#fff"/>',
			'Argentina' => '<rect width="24" height="24" fill="#74ACDF"/><rect y="8" width="24" height="8" fill="#fff"/><circle cx="12" cy="12" r="2" fill="#F6B40E"/>',
			'Hungary'   => '<rect width="24" height="8" fill="#CE2939"/><rect y="8" width="24" height="8" fill="#fff"/><rect y="16" width="24" height="8" fill="#477050"/>',
		);
	}
	$country = trim( (string) $country );
	if ( ! isset( $flags[ $country ] ) ) {
		return '';
	}
	return '<svg class="flag" viewBox="0 0 24 24" role="img" aria-label="' . esc_attr( $country . ' flag' ) . '"><g clip-path="url(#flagClip)">' . $flags[ $country ] . '</g></svg>';
}
