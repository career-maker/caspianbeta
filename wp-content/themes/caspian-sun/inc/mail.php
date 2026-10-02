<?php
/**
 * Mail & reCAPTCHA settings, SMTP transport and HTML e-mail templates.
 *
 * All values live in the "csp_mail" option and are read at send / render time,
 * so changing the Gmail address, app password, SMTP details or reCAPTCHA keys
 * takes effect on the very next request. Secrets never live in the theme files.
 *
 * Admin screen: Theme Settings → Mail & reCAPTCHA.
 *
 * @package caspian-sun
 */

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------ Settings */

/** One saved setting (optionally with a default). */
function csp_mail_opt( $key, $default = '' ) {
	$all = get_option( 'csp_mail', array() );
	if ( ! is_array( $all ) || ! isset( $all[ $key ] ) || '' === $all[ $key ] ) {
		return $default;
	}
	return $all[ $key ];
}

/** Effective SMTP configuration, or an empty array when SMTP is not configured. */
function csp_smtp_config() {
	$gmail = trim( (string) csp_mail_opt( 'gmail' ) );
	$user  = trim( (string) csp_mail_opt( 'smtp_user', $gmail ) );
	$pass  = preg_replace( '/\s+/', '', (string) csp_mail_opt( 'app_password' ) );
	$host  = trim( (string) csp_mail_opt( 'smtp_host', 'smtp.gmail.com' ) );
	if ( '' === $user || '' === $pass || '' === $host ) {
		return array();
	}
	$secure = (string) csp_mail_opt( 'smtp_secure', 'tls' );
	$secure = in_array( $secure, array( 'tls', 'ssl', 'none' ), true ) ? $secure : 'tls';
	$port   = (int) csp_mail_opt( 'smtp_port', 'ssl' === $secure ? 465 : 587 );
	$from   = trim( (string) csp_mail_opt( 'smtp_from', $gmail ? $gmail : $user ) );
	return array(
		'host'   => $host,
		'port'   => $port > 0 ? $port : 587,
		'secure' => $secure,
		'user'   => $user,
		'pass'   => $pass,
		'from'   => is_email( $from ) ? $from : ( is_email( $user ) ? $user : '' ),
		'name'   => trim( (string) csp_mail_opt( 'from_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) ) ),
	);
}

/** Where enquiries are delivered. */
function csp_enquiry_recipient() {
	foreach ( array( csp_mail_opt( 'recipient' ), csp_mail_opt( 'gmail' ), get_option( 'admin_email' ) ) as $e ) {
		if ( is_email( $e ) ) {
			return $e;
		}
	}
	return '';
}

/** reCAPTCHA v3 keys: array( site, secret ) — both empty when disabled. */
function csp_recaptcha_keys() {
	$site   = trim( (string) csp_mail_opt( 'recaptcha_site' ) );
	$secret = trim( (string) csp_mail_opt( 'recaptcha_secret' ) );
	return ( $site && $secret ) ? array( $site, $secret ) : array( '', '' );
}

/* --------------------------------------------------------------- SMTP transport */

add_action(
	'phpmailer_init',
	function ( $mailer ) {
		$c = csp_smtp_config();
		if ( ! $c ) {
			return; // Fall back to PHP mail().
		}
		$mailer->isSMTP();
		$mailer->Host        = $c['host']; // phpcs:ignore WordPress.NamingConventions
		$mailer->Port        = $c['port']; // phpcs:ignore WordPress.NamingConventions
		$mailer->SMTPAuth    = true; // phpcs:ignore WordPress.NamingConventions
		$mailer->Username    = $c['user']; // phpcs:ignore WordPress.NamingConventions
		$mailer->Password    = $c['pass']; // phpcs:ignore WordPress.NamingConventions
		$mailer->SMTPSecure  = 'none' === $c['secure'] ? '' : $c['secure']; // phpcs:ignore WordPress.NamingConventions
		$mailer->SMTPAutoTLS = 'none' !== $c['secure']; // phpcs:ignore WordPress.NamingConventions
		$mailer->Timeout     = 15; // phpcs:ignore WordPress.NamingConventions
		$mailer->CharSet     = 'UTF-8'; // phpcs:ignore WordPress.NamingConventions
		if ( $c['from'] ) {
			$mailer->Sender = $c['from']; // phpcs:ignore WordPress.NamingConventions
		}
		$alt = csp_mail_alt( null );
		if ( $alt ) {
			$mailer->AltBody = $alt; // phpcs:ignore WordPress.NamingConventions
		}
	}
);
// The plain-text alternative is also attached when sending through PHP mail().
add_action(
	'phpmailer_init',
	function ( $mailer ) {
		$alt = csp_mail_alt( null );
		if ( $alt && empty( $mailer->AltBody ) ) { // phpcs:ignore WordPress.NamingConventions
			$mailer->AltBody = $alt; // phpcs:ignore WordPress.NamingConventions
		}
	},
	20
);

/** Tiny holder for the plain-text alternative of the mail being sent. */
function csp_mail_alt( $set = false ) {
	static $alt = '';
	if ( false !== $set ) {
		$alt = (string) $set;
	}
	return $alt;
}

add_filter(
	'wp_mail_from',
	function ( $from ) {
		$c = csp_smtp_config();
		return ( $c && $c['from'] ) ? $c['from'] : $from; // Gmail only accepts its own address.
	}
);
add_filter(
	'wp_mail_from_name',
	function ( $name ) {
		$c = csp_smtp_config();
		return ( $c && $c['name'] ) ? $c['name'] : $name;
	}
);

// Remember the outcome of every send (last 20) so the settings screen can show what happened.
function csp_mail_log_add( $ok, $to, $subject, $msg = '' ) {
	$log = get_option( 'csp_mail_log', array() );
	$log = is_array( $log ) ? $log : array();
	array_unshift(
		$log,
		array(
			'ok'   => (bool) $ok,
			'time' => time(),
			'to'   => is_array( $to ) ? implode( ', ', $to ) : (string) $to,
			'subj' => (string) $subject,
			'msg'  => (string) $msg,
		)
	);
	update_option( 'csp_mail_log', array_slice( $log, 0, 20 ), false );
	update_option( 'csp_mail_status', array( 'ok' => (bool) $ok, 'time' => time(), 'msg' => (string) $msg ), false );
}
add_action(
	'wp_mail_failed',
	function ( $err ) {
		$d = is_wp_error( $err ) ? $err->get_error_data() : array();
		csp_mail_log_add( false, isset( $d['to'] ) ? $d['to'] : '', isset( $d['subject'] ) ? $d['subject'] : '', is_wp_error( $err ) ? $err->get_error_message() : 'Unknown error' );
	}
);
add_action(
	'wp_mail_succeeded',
	function ( $d ) {
		csp_mail_log_add( true, isset( $d['to'] ) ? $d['to'] : '', isset( $d['subject'] ) ? $d['subject'] : '' );
	}
);

/** wp_mail() wrapper: HTML body + plain-text alternative. */
function csp_send_html( $to, $subject, $html, $text, $headers = array() ) {
	$headers[] = 'Content-Type: text/html; charset=UTF-8';
	csp_mail_alt( $text );
	$ok = wp_mail( $to, $subject, $html, $headers );
	csp_mail_alt( '' );
	return $ok;
}

/* ------------------------------------------------------------- HTML mail design */

/** URL of the website logo for e-mails (PNG/JPG copy is generated once from a WebP/SVG logo). */
function csp_mail_logo_url() {
	$fallback = CSP_URI . '/assets/images/mail-logo.png';
	$id       = (int) csp_opt( 'opt_logo' );
	if ( ! $id ) {
		return $fallback;
	}
	$mime = get_post_mime_type( $id );
	$url  = wp_get_attachment_image_url( $id, 'medium' );
	if ( ! $url ) {
		$url = wp_get_attachment_url( $id );
	}
	if ( in_array( $mime, array( 'image/png', 'image/jpeg', 'image/gif' ), true ) && $url ) {
		return $url;
	}
	$cache = get_option( 'csp_mail_logo', array() );
	if ( is_array( $cache ) && isset( $cache['id'], $cache['url'] ) && (int) $cache['id'] === $id ) {
		return $cache['url'];
	}
	$file = get_attached_file( $id );
	$up   = wp_upload_dir();
	if ( $file && is_readable( $file ) && empty( $up['error'] ) ) {
		$editor = wp_get_image_editor( $file );
		if ( ! is_wp_error( $editor ) ) {
			$editor->resize( 240, 240, false );
			$name = 'csp-mail-logo-' . $id . '.png';
			$res  = $editor->save( trailingslashit( $up['path'] ) . $name, 'image/png' );
			if ( ! is_wp_error( $res ) ) {
				$new = trailingslashit( $up['url'] ) . $name;
				update_option( 'csp_mail_logo', array( 'id' => $id, 'url' => $new ), false );
				return $new;
			}
		}
	}
	return $fallback;
}

/**
 * Branded, table-based, inline-styled e-mail (renders in Gmail, Outlook, Apple Mail, mobile).
 *
 * @param array $a preheader, eyebrow, title, intro (plain text), rows (label => value|array(text,href)),
 *                 message_label, message, button (array(label,url)), note.
 */
function csp_mail_html( $a ) {
	$teal   = '#1E4951';
	$gold   = '#D7BB51';
	$gold_d = '#AA8D3F';
	$ink    = '#171813';
	$muted  = '#6b6b64';
	$cream  = '#F7F3EC';
	$serif  = "'Playfair Display',Georgia,'Times New Roman',serif";
	$sans   = "'Manrope',-apple-system,'Segoe UI',Helvetica,Arial,sans-serif";

	$site  = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$logo  = csp_mail_logo_url();
	$phone = csp_opt( 'opt_phone_display' );
	$tel   = csp_opt( 'opt_phone_tel' );
	$email = csp_opt( 'opt_email' );
	$addr  = trim( preg_replace( '/\s*[\r\n]+\s*/', ', ', (string) csp_opt( 'opt_address' ) ) );

	$rows = '';
	if ( ! empty( $a['rows'] ) ) {
		foreach ( $a['rows'] as $label => $val ) {
			$text = is_array( $val ) ? $val[0] : $val;
			if ( '' === (string) $text ) {
				continue;
			}
			$cell = is_array( $val ) && ! empty( $val[1] )
				? '<a href="' . esc_url( $val[1] ) . '" style="color:' . $teal . ';text-decoration:none;font-weight:600;">' . esc_html( $text ) . '</a>'
				: esc_html( $text );
			$rows .= '<tr><td style="padding:11px 0;border-bottom:1px solid #ece6d8;font:600 12px/1.4 ' . $sans . ';letter-spacing:.08em;text-transform:uppercase;color:' . $gold_d . ';width:34%;vertical-align:top;">' . esc_html( $label ) . '</td>'
				. '<td style="padding:11px 0;border-bottom:1px solid #ece6d8;font:400 15px/1.5 ' . $sans . ';color:' . $ink . ';vertical-align:top;">' . $cell . '</td></tr>';
		}
	}

	ob_start();
	?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="color-scheme" content="light only">
<title><?php echo esc_html( isset( $a['title'] ) ? $a['title'] : $site ); ?></title>
</head>
<body style="margin:0;padding:0;background:<?php echo $cream; ?>;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;"><?php echo esc_html( isset( $a['preheader'] ) ? $a['preheader'] : '' ); ?></div>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:<?php echo $cream; ?>;">
<tr><td align="center" style="padding:28px 12px;">
  <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:600px;background:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 6px 28px rgba(30,73,81,.12);">
    <tr><td align="center" style="background:<?php echo $teal; ?>;padding:30px 24px 22px;">
      <img src="<?php echo esc_url( $logo ); ?>" width="92" height="92" alt="<?php echo esc_attr( $site ); ?>" style="display:block;border-radius:50%;border:3px solid <?php echo $gold; ?>;background:#fff;">
      <div style="font:600 11px/1 <?php echo $sans; ?>;letter-spacing:.28em;text-transform:uppercase;color:<?php echo $gold; ?>;margin-top:16px;"><?php echo esc_html( $site ); ?></div>
    </td></tr>
    <tr><td style="height:4px;background:<?php echo $gold; ?>;line-height:4px;font-size:0;">&nbsp;</td></tr>
    <tr><td style="padding:38px 40px 8px;">
      <?php if ( ! empty( $a['eyebrow'] ) ) : ?>
      <div style="font:700 11px/1 <?php echo $sans; ?>;letter-spacing:.2em;text-transform:uppercase;color:<?php echo $gold_d; ?>;margin-bottom:12px;"><?php echo esc_html( $a['eyebrow'] ); ?></div>
      <?php endif; ?>
      <h1 style="margin:0 0 14px;font:500 28px/1.25 <?php echo $serif; ?>;color:<?php echo $teal; ?>;"><?php echo esc_html( isset( $a['title'] ) ? $a['title'] : '' ); ?></h1>
      <?php if ( ! empty( $a['intro'] ) ) : ?>
      <p style="margin:0 0 6px;font:400 15px/1.7 <?php echo $sans; ?>;color:<?php echo $ink; ?>;"><?php echo nl2br( esc_html( $a['intro'] ) ); ?></p>
      <?php endif; ?>
    </td></tr>
    <?php if ( $rows ) : ?>
    <tr><td style="padding:10px 40px 0;">
      <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-top:1px solid #ece6d8;"><?php echo $rows; // phpcs:ignore WordPress.Security.EscapeOutput ?></table>
    </td></tr>
    <?php endif; ?>
    <?php if ( ! empty( $a['message'] ) ) : ?>
    <tr><td style="padding:22px 40px 0;">
      <?php if ( ! empty( $a['message_label'] ) ) : ?>
      <div style="font:700 11px/1 <?php echo $sans; ?>;letter-spacing:.2em;text-transform:uppercase;color:<?php echo $gold_d; ?>;margin-bottom:10px;"><?php echo esc_html( $a['message_label'] ); ?></div>
      <?php endif; ?>
      <div style="background:<?php echo $cream; ?>;border-left:4px solid <?php echo $gold; ?>;border-radius:6px;padding:16px 18px;font:400 15px/1.7 <?php echo $sans; ?>;color:<?php echo $ink; ?>;"><?php echo nl2br( esc_html( $a['message'] ) ); ?></div>
    </td></tr>
    <?php endif; ?>
    <?php if ( ! empty( $a['button'] ) ) : ?>
    <tr><td align="left" style="padding:28px 40px 0;">
      <a href="<?php echo esc_url( $a['button'][1] ); ?>" style="display:inline-block;background:<?php echo $teal; ?>;color:#ffffff;text-decoration:none;font:700 14px/1 <?php echo $sans; ?>;letter-spacing:.04em;padding:15px 30px;border-radius:999px;border-bottom:3px solid <?php echo $gold; ?>;"><?php echo esc_html( $a['button'][0] ); ?></a>
    </td></tr>
    <?php endif; ?>
    <?php if ( ! empty( $a['note'] ) ) : ?>
    <tr><td style="padding:22px 40px 0;font:400 13px/1.6 <?php echo $sans; ?>;color:<?php echo $muted; ?>;"><?php echo nl2br( esc_html( $a['note'] ) ); ?></td></tr>
    <?php endif; ?>
    <tr><td style="padding:34px 40px 0;"><div style="height:1px;background:#ece6d8;line-height:1px;font-size:0;">&nbsp;</div></td></tr>
    <tr><td align="center" style="padding:22px 40px 34px;font:400 13px/1.8 <?php echo $sans; ?>;color:<?php echo $muted; ?>;">
      <strong style="color:<?php echo $teal; ?>;font-family:<?php echo $serif; ?>;font-size:15px;"><?php echo esc_html( $site ); ?></strong><br>
      <?php if ( $addr ) : ?><?php echo esc_html( $addr ); ?><br><?php endif; ?>
      <?php if ( $phone ) : ?><a href="tel:<?php echo esc_attr( $tel ? $tel : $phone ); ?>" style="color:<?php echo $muted; ?>;text-decoration:none;"><?php echo esc_html( $phone ); ?></a><?php endif; ?>
      <?php if ( $phone && $email ) : ?> &nbsp;&bull;&nbsp; <?php endif; ?>
      <?php if ( $email ) : ?><a href="mailto:<?php echo esc_attr( $email ); ?>" style="color:<?php echo $muted; ?>;text-decoration:none;"><?php echo esc_html( $email ); ?></a><?php endif; ?>
    </td></tr>
  </table>
  <div style="font:400 11px/1.6 <?php echo $sans; ?>;color:#9a9a92;padding:16px 10px;max-width:520px;"><?php echo esc_html( isset( $a['footer_note'] ) ? $a['footer_note'] : '' ); ?></div>
</td></tr>
</table>
</body>
</html>
	<?php
	return (string) ob_get_clean();
}

/** Plain-text twin of csp_mail_html(). */
function csp_mail_text( $a ) {
	$out   = array();
	$out[] = isset( $a['title'] ) ? $a['title'] : '';
	$out[] = str_repeat( '-', 40 );
	if ( ! empty( $a['intro'] ) ) {
		$out[] = $a['intro'];
		$out[] = '';
	}
	foreach ( (array) ( isset( $a['rows'] ) ? $a['rows'] : array() ) as $label => $val ) {
		$text = is_array( $val ) ? $val[0] : $val;
		if ( '' !== (string) $text ) {
			$out[] = $label . ': ' . $text;
		}
	}
	if ( ! empty( $a['message'] ) ) {
		$out[] = '';
		$out[] = ( ! empty( $a['message_label'] ) ? $a['message_label'] . ":\n" : '' ) . $a['message'];
	}
	if ( ! empty( $a['note'] ) ) {
		$out[] = '';
		$out[] = $a['note'];
	}
	$out[] = '';
	$out[] = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	return implode( "\n", $out );
}

/** Mail to the site owner for a new enquiry. */
function csp_mail_notify( $d ) {
	$site = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$a    = array(
		'preheader'     => sprintf( 'New enquiry from %s', $d['name'] ),
		'eyebrow'       => $d['product'] ? 'Product enquiry' : 'New enquiry',
		'title'         => $d['product'] ? sprintf( 'Enquiry about %s', $d['product'] ) : 'You have a new enquiry',
		'intro'         => sprintf( '%s sent a message through the website contact form.', $d['name'] ),
		'rows'          => array(
			'Name'    => $d['name'],
			'Email'   => array( $d['email'], 'mailto:' . $d['email'] ),
			'Phone'   => $d['phone'] ? array( $d['phone'], 'tel:' . preg_replace( '/[^0-9+]/', '', $d['phone'] ) ) : '',
			'Subject' => $d['subject'],
			'Product' => $d['product'],
			'Packing' => $d['size'],
			'Received' => wp_date( 'j M Y, H:i' ),
		),
		'message_label' => 'Message',
		'message'       => $d['message'],
		'button'        => array( 'Reply to ' . $d['name'], 'mailto:' . $d['email'] . '?subject=' . rawurlencode( 'Re: your enquiry to ' . $site ) ),
		'footer_note'   => 'Sent automatically from the contact form at ' . home_url( '/' ) . '. Replying to this email answers the visitor directly.',
	);
	$subject = '[' . $site . '] ' . $d['subject'] . ( $d['product'] ? ' — ' . $d['product'] : '' );
	return csp_send_html(
		csp_enquiry_recipient(),
		csp_oneline( $subject ),
		csp_mail_html( $a ),
		csp_mail_text( $a ),
		array( 'Reply-To: ' . $d['name'] . ' <' . $d['email'] . '>' )
	);
}

/** Confirmation mail to the visitor. */
function csp_mail_confirm( $d ) {
	$site    = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$subject = csp_oneline( csp_mail_opt( 'autoreply_subject', 'Thank you for contacting ' . $site ) );
	$intro   = (string) csp_mail_opt( 'autoreply_body', "Thank you for your enquiry. We have received your message and a member of our team will get back to you shortly." );
	$a       = array(
		'preheader'     => 'We have received your enquiry.',
		'eyebrow'       => 'Enquiry received',
		'title'         => 'Thank you, ' . $d['name'],
		'intro'         => $intro,
		'rows'          => array(
			'Subject' => $d['subject'],
			'Product' => $d['product'],
			'Packing' => $d['size'],
			'Email'   => $d['email'],
			'Phone'   => $d['phone'],
		),
		'message_label' => 'Your message',
		'message'       => $d['message'],
		'button'        => array( 'Visit our website', home_url( '/' ) ),
		'note'          => 'You can simply reply to this email if you would like to add anything.',
		'footer_note'   => 'You are receiving this email because an enquiry was submitted with this address on ' . home_url( '/' ) . '.',
	);
	$headers = array();
	$to_us   = csp_enquiry_recipient();
	if ( $to_us ) {
		$headers[] = 'Reply-To: ' . $to_us;
	}
	return csp_send_html( $d['email'], $subject, csp_mail_html( $a ), csp_mail_text( $a ), $headers );
}

/* ------------------------------------------------------------------ Admin page */

add_action(
	'admin_menu',
	function () {
		global $menu;
		$cb     = 'csp_mail_page';
		$parent = '';
		foreach ( (array) $menu as $m ) { // ACF's "Theme Settings" top-level item (its slug is the first sub page when it redirects).
			if ( isset( $m[2] ) && in_array( $m[2], array( 'csp-settings', 'csp-general' ), true ) ) {
				$parent = $m[2];
				break;
			}
		}
		if ( $parent ) {
			add_submenu_page( $parent, 'Mail & reCAPTCHA', 'Mail & reCAPTCHA', 'manage_options', 'csp-mail', $cb );
		} else {
			add_options_page( 'Mail & reCAPTCHA', 'Mail & reCAPTCHA', 'manage_options', 'csp-mail', $cb );
		}
	},
	100
);

add_action(
	'admin_init',
	function () {
		register_setting(
			'csp_mail_group',
			'csp_mail',
			array(
				'type'              => 'array',
				'sanitize_callback' => 'csp_mail_sanitize',
				'autoload'          => false,
			)
		);
	}
);

function csp_mail_sanitize( $in ) {
	$old = get_option( 'csp_mail', array() );
	$old = is_array( $old ) ? $old : array();
	$in  = is_array( $in ) ? wp_unslash( $in ) : array();
	$out = array();

	$email_labels = array( 'gmail' => 'Gmail address', 'recipient' => 'Receive enquiries at', 'smtp_from' => 'From address' );
	foreach ( array( 'gmail', 'recipient', 'smtp_from' ) as $k ) {
		$raw       = isset( $in[ $k ] ) ? trim( (string) $in[ $k ] ) : '';
		$v         = sanitize_email( $raw );
		$out[ $k ] = is_email( $v ) ? $v : '';
		if ( '' !== $raw && '' === $out[ $k ] && function_exists( 'add_settings_error' ) ) {
			add_settings_error( 'csp_mail', 'csp_bad_' . $k, sprintf( '"%1$s" is not a valid email address, so "%2$s" was not saved.', esc_html( $raw ), $email_labels[ $k ] ), 'error' );
		}
	}
	foreach ( array( 'from_name', 'smtp_host', 'smtp_user', 'autoreply_subject', 'recaptcha_site' ) as $k ) {
		$out[ $k ] = isset( $in[ $k ] ) ? trim( sanitize_text_field( $in[ $k ] ) ) : '';
	}
	$out['smtp_host']   = preg_replace( '/[^A-Za-z0-9.\-]/', '', $out['smtp_host'] );
	$out['autoreply_body'] = isset( $in['autoreply_body'] ) ? trim( sanitize_textarea_field( $in['autoreply_body'] ) ) : '';
	$out['smtp_port']   = ! empty( $in['smtp_port'] ) ? (string) absint( $in['smtp_port'] ) : '';
	$out['smtp_secure'] = ( isset( $in['smtp_secure'] ) && in_array( $in['smtp_secure'], array( 'tls', 'ssl', 'none' ), true ) ) ? $in['smtp_secure'] : 'tls';
	$out['autoreply']   = ! empty( $in['autoreply'] ) ? '1' : '0';
	$thr                = isset( $in['recaptcha_threshold'] ) ? (float) $in['recaptcha_threshold'] : 0.5;
	$out['recaptcha_threshold'] = (string) min( 0.9, max( 0.1, $thr ? $thr : 0.5 ) );

	// Secrets: blank keeps the saved value; the "remove" box clears it.
	foreach ( array( 'app_password', 'recaptcha_secret' ) as $k ) {
		$new = isset( $in[ $k ] ) ? trim( $in[ $k ] ) : '';
		if ( 'app_password' === $k ) {
			$new = preg_replace( '/\s+/', '', $new );
		}
		if ( ! empty( $in[ 'clear_' . $k ] ) ) {
			$out[ $k ] = '';
		} elseif ( '' !== $new ) {
			$out[ $k ] = sanitize_text_field( $new );
		} else {
			$out[ $k ] = isset( $old[ $k ] ) ? $old[ $k ] : '';
		}
	}
	return $out;
}

add_action(
	'admin_post_csp_mail_test',
	function () {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'csp_mail_test' ) ) {
			wp_die( 'Not allowed.' );
		}
		$to = csp_enquiry_recipient();
		$a  = array(
			'preheader'     => 'Test email from your website.',
			'eyebrow'       => 'Test email',
			'title'         => 'Your mail settings work',
			'intro'         => 'This message was sent from Theme Settings → Mail & reCAPTCHA. Enquiry notifications and visitor confirmations will look like this.',
			'rows'          => array(
				'Sent to'   => $to,
				'Via'       => csp_smtp_config() ? csp_smtp_config()['host'] . ':' . csp_smtp_config()['port'] : 'PHP mail() (SMTP not configured)',
				'Sent at'   => wp_date( 'j M Y, H:i' ),
			),
			'message_label' => 'Sample enquiry',
			'message'       => 'Hello, I would like a price list for frozen king crab legs. Thank you.',
			'button'        => array( 'Open the website', home_url( '/' ) ),
		);
		update_option( 'csp_mail_status', array( 'ok' => null, 'time' => time(), 'msg' => '' ), false );
		$ok = $to ? csp_send_html( $to, '[' . get_bloginfo( 'name' ) . '] Mail test', csp_mail_html( $a ), csp_mail_text( $a ) ) : false;
		wp_safe_redirect( add_query_arg( array( 'page' => 'csp-mail', 'csp_test' => $ok ? 'ok' : 'fail' ), admin_url( 'admin.php' ) ) );
		exit;
	}
);

function csp_mail_field( $name, $label, $type = 'text', $help = '', $extra = '' ) {
	$v = csp_mail_opt( $name );
	echo '<tr><th scope="row"><label for="csp_' . esc_attr( $name ) . '">' . esc_html( $label ) . '</label></th><td>';
	printf(
		'<input type="%1$s" id="csp_%2$s" name="csp_mail[%2$s]" value="%3$s" class="regular-text" autocomplete="off" %4$s>',
		esc_attr( $type ),
		esc_attr( $name ),
		esc_attr( $v ),
		$extra // phpcs:ignore WordPress.Security.EscapeOutput
	);
	if ( $help ) {
		echo '<p class="description">' . wp_kses_post( $help ) . '</p>';
	}
	echo '</td></tr>';
}

function csp_mail_secret_field( $name, $label, $help ) {
	$has = '' !== (string) csp_mail_opt( $name );
	echo '<tr><th scope="row"><label for="csp_' . esc_attr( $name ) . '">' . esc_html( $label ) . '</label></th><td>';
	printf(
		'<input type="password" id="csp_%1$s" name="csp_mail[%1$s]" value="" class="regular-text" autocomplete="new-password" placeholder="%2$s">',
		esc_attr( $name ),
		esc_attr( $has ? '•••••••••••• saved — type to replace' : 'Not set' )
	);
	if ( $has ) {
		printf( ' <label style="margin-left:8px"><input type="checkbox" name="csp_mail[clear_%s]" value="1"> remove saved value</label>', esc_attr( $name ) );
	}
	echo '<p class="description">' . wp_kses_post( $help ) . '</p></td></tr>';
}

function csp_mail_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$status = get_option( 'csp_mail_status', array() );
	$cfg    = csp_smtp_config();
	list( $rs, $rk ) = csp_recaptcha_keys();
	$secure = csp_mail_opt( 'smtp_secure', 'tls' );
	?>
	<style>
	.csp-card{background:#fff;border:1px solid #dcdcde;border-top:3px solid #D7BB51;border-radius:4px;padding:6px 22px 14px;margin:18px 0;max-width:900px}
	.csp-card h2{margin:14px 0 0;color:#1E4951}
	.csp-pill{display:inline-block;padding:2px 10px;border-radius:99px;font-size:12px;font-weight:600}
	.csp-on{background:#e3f4e8;color:#14692e}.csp-off{background:#f0f0f1;color:#50575e}.csp-bad{background:#fbe3e3;color:#a1171d}
	</style>
	<div class="wrap">
		<h1>Mail &amp; reCAPTCHA</h1>
		<p>Changes apply immediately — nothing is cached. Secrets are stored in the database only, never in theme files.</p>
		<?php settings_errors(); ?>
		<?php if ( isset( $_GET['csp_test'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
			<?php if ( 'ok' === $_GET['csp_test'] ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
				<div class="notice notice-success"><p>Test email sent to <strong><?php echo esc_html( csp_enquiry_recipient() ); ?></strong>. Check the inbox (and spam).</p></div>
			<?php else : ?>
				<div class="notice notice-error"><p><strong>Test email failed.</strong> <?php echo esc_html( ! empty( $status['msg'] ) ? $status['msg'] : 'No recipient set or the server refused the message.' ); ?></p></div>
			<?php endif; ?>
		<?php endif; ?>

		<p>
			SMTP: <?php echo $cfg ? '<span class="csp-pill csp-on">Active — ' . esc_html( $cfg['host'] . ':' . $cfg['port'] ) . '</span>' : '<span class="csp-pill csp-off">Not configured — using PHP mail()</span>'; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			&nbsp; reCAPTCHA: <?php echo $rk ? '<span class="csp-pill csp-on">Active</span>' : '<span class="csp-pill csp-off">Off (honeypot + time-trap still protect the form)</span>'; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( ! empty( $status['time'] ) && isset( $status['ok'] ) && null !== $status['ok'] ) : ?>
				&nbsp; Last send: <?php echo $status['ok'] ? '<span class="csp-pill csp-on">OK</span>' : '<span class="csp-pill csp-bad">Failed</span>'; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span class="description"><?php echo esc_html( human_time_diff( $status['time'] ) . ' ago' . ( ! $status['ok'] && $status['msg'] ? ' — ' . $status['msg'] : '' ) ); ?></span>
			<?php endif; ?>
		</p>

		<form method="post" action="options.php">
			<?php settings_fields( 'csp_mail_group' ); ?>

			<div class="csp-card"><h2>Enquiry mailbox (Gmail)</h2>
			<table class="form-table" role="presentation">
				<?php
				csp_mail_field( 'gmail', 'Gmail address', 'email', 'The account that <strong>sends</strong> every email and, by default, <strong>receives</strong> enquiries.' );
				csp_mail_secret_field( 'app_password', 'Gmail app password', '16-character Google <em>App password</em> (Google Account → Security → 2-Step Verification → App passwords). Spaces are ignored. Not your normal Gmail password.' );
				csp_mail_field( 'recipient', 'Receive enquiries at', 'email', 'Optional. Leave empty to deliver enquiries to the Gmail address above.' );
				csp_mail_field( 'from_name', 'Sender name', 'text', 'Shown as the sender of confirmation emails. Default: site title.' );
				?>
			</table></div>

			<div class="csp-card"><h2>Confirmation email to the visitor</h2>
			<table class="form-table" role="presentation">
				<tr><th scope="row">Send confirmation</th><td><label><input type="checkbox" name="csp_mail[autoreply]" value="1" <?php checked( '0' !== (string) csp_mail_opt( 'autoreply', '1' ) ); ?>> Email the visitor a branded confirmation after they submit an enquiry</label></td></tr>
				<?php csp_mail_field( 'autoreply_subject', 'Subject', 'text', '', 'placeholder="Thank you for contacting us"' ); ?>
				<tr><th scope="row"><label for="csp_autoreply_body">Message</label></th><td>
					<textarea id="csp_autoreply_body" name="csp_mail[autoreply_body]" rows="4" class="large-text"><?php echo esc_textarea( csp_mail_opt( 'autoreply_body', "Thank you for your enquiry. We have received your message and a member of our team will get back to you shortly." ) ); ?></textarea>
					<p class="description">Intro paragraph of the email. The visitor's own message and product are added automatically.</p></td></tr>
			</table></div>

			<div class="csp-card"><h2>Google reCAPTCHA v3</h2>
			<table class="form-table" role="presentation">
				<?php
				csp_mail_field( 'recaptcha_site', 'Site key', 'text', 'From <a href="https://www.google.com/recaptcha/admin" target="_blank" rel="noopener">google.com/recaptcha/admin</a> — choose <em>reCAPTCHA v3</em> and add this site\'s domain.' );
				csp_mail_secret_field( 'recaptcha_secret', 'Secret key', 'Both keys are required. When saved, the contact form is protected immediately; clear them to switch reCAPTCHA off.' );
				csp_mail_field( 'recaptcha_threshold', 'Minimum score', 'number', '0.1 – 0.9 (default 0.5). Submissions scoring lower are rejected.', 'step="0.1" min="0.1" max="0.9" placeholder="0.5" style="width:90px"' );
				?>
			</table></div>

			<div class="csp-card"><h2>Advanced SMTP <span class="description" style="font-weight:400">— optional</span></h2>
			<p class="description">Leave blank to use Gmail (smtp.gmail.com, port 587, TLS, login = Gmail address + app password). Fill in to use any other mail provider; the password above is then used as the SMTP password.</p>
			<table class="form-table" role="presentation">
				<?php
				csp_mail_field( 'smtp_host', 'SMTP host', 'text', '', 'placeholder="smtp.gmail.com"' );
				csp_mail_field( 'smtp_port', 'Port', 'number', '587 for TLS, 465 for SSL, 25 for none.', 'placeholder="587" style="width:90px"' );
				?>
				<tr><th scope="row"><label for="csp_smtp_secure">Encryption</label></th><td>
					<select id="csp_smtp_secure" name="csp_mail[smtp_secure]">
						<option value="tls" <?php selected( $secure, 'tls' ); ?>>TLS (STARTTLS)</option>
						<option value="ssl" <?php selected( $secure, 'ssl' ); ?>>SSL</option>
						<option value="none" <?php selected( $secure, 'none' ); ?>>None</option>
					</select></td></tr>
				<?php
				csp_mail_field( 'smtp_user', 'SMTP username', 'text', 'Default: the Gmail address.' );
				csp_mail_field( 'smtp_from', 'From address', 'email', 'Default: the Gmail address. Gmail only sends as its own address.' );
				?>
			</table></div>

			<?php submit_button( 'Save settings' ); ?>
		</form>

		<?php $log = get_option( 'csp_mail_log', array() ); ?>
		<div class="csp-card"><h2>Recent emails</h2>
			<?php if ( empty( $log ) ) : ?>
				<p class="description">Nothing sent yet. Every enquiry notification and visitor confirmation is listed here with its result.</p>
			<?php else : ?>
			<table class="widefat striped"><thead><tr><th>When</th><th>To</th><th>Subject</th><th>Result</th></tr></thead><tbody>
				<?php foreach ( (array) $log as $row ) : ?>
				<tr>
					<td><?php echo esc_html( human_time_diff( $row['time'] ) . ' ago' ); ?></td>
					<td><?php echo esc_html( $row['to'] ); ?></td>
					<td><?php echo esc_html( $row['subj'] ); ?></td>
					<td><?php echo $row['ok'] ? '<span class="csp-pill csp-on">Sent</span>' : '<span class="csp-pill csp-bad">Failed</span> ' . esc_html( $row['msg'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody></table>
			<?php endif; ?>
		</div>

		<div class="csp-card"><h2>Send a test email</h2>
			<p>Sends a sample of the branded enquiry email to <strong><?php echo esc_html( csp_enquiry_recipient() ? csp_enquiry_recipient() : '— set an address first —' ); ?></strong> using the <em>saved</em> settings.</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="csp_mail_test">
				<?php wp_nonce_field( 'csp_mail_test' ); ?>
				<?php submit_button( 'Send test email', 'secondary', 'submit', false ); ?>
			</form>
		</div>
	</div>
	<?php
}
