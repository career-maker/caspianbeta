<?php
/**
 * Contact / product-enquiry form handling.
 *
 * Protection: nonce, honeypot, time-trap token, per-IP rate limit, optional
 * reCAPTCHA v3, server-side validation, header-injection-safe wp_mail().
 * Each valid submission is stored as a private "Enquiry" and e-mailed.
 *
 * @package caspian-sun
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_post_nopriv_csp_contact', 'csp_handle_contact' );
add_action( 'admin_post_csp_contact', 'csp_handle_contact' );
add_action( 'wp_ajax_nopriv_csp_contact', 'csp_handle_contact' );
add_action( 'wp_ajax_csp_contact', 'csp_handle_contact' );

/** Signed timestamp token — proves the form was rendered at least N seconds before submit. */
function csp_form_token() {
	$ts = time();
	return $ts . '.' . hash_hmac( 'sha256', (string) $ts, wp_salt( 'nonce' ) );
}

/** Returns 'ok', 'fast' (submitted in < 3 s: bot) or 'invalid' (missing / forged / older than a day). */
function csp_check_token( $token ) {
	$parts = explode( '.', (string) $token );
	if ( 2 !== count( $parts ) || ! ctype_digit( $parts[0] ) ) {
		return 'invalid';
	}
	if ( ! hash_equals( hash_hmac( 'sha256', $parts[0], wp_salt( 'nonce' ) ), $parts[1] ) ) {
		return 'invalid';
	}
	$age = time() - (int) $parts[0];
	if ( $age > DAY_IN_SECONDS ) {
		return 'invalid';
	}
	return $age < 3 ? 'fast' : 'ok';
}

/** Strip CR/LF so values can never inject mail headers. */
function csp_oneline( $s ) {
	return trim( preg_replace( '/[\r\n\t]+/', ' ', (string) $s ) );
}

function csp_handle_contact() {
	$is_ajax = wp_doing_ajax();
	$fail    = function ( $errors, $code = 422 ) use ( $is_ajax ) {
		if ( $is_ajax ) {
			wp_send_json_error( array( 'errors' => $errors, 'message' => isset( $errors['_form'] ) ? $errors['_form'] : '' ), $code );
		}
		$back = wp_get_referer() ? wp_get_referer() : home_url( '/' );
		wp_safe_redirect( add_query_arg( 'csp_sent', 'err', remove_query_arg( 'csp_sent', $back ) ) . '#contact-form' );
		exit;
	};

	// phpcs:disable WordPress.Security.NonceVerification -- verified below.
	if ( ! isset( $_POST['csp_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['csp_nonce'] ) ), 'csp_contact' ) ) {
		$fail( array( '_form' => csp_get( 'contact_error', csp_page_by_template( 'templates/contact.php' ) ) ), 403 );
	}

	// Honeypot: bots fill the hidden field. Pretend success.
	if ( ! empty( $_POST['website'] ) ) {
		csp_form_done( $is_ajax );
	}
	$tok = csp_check_token( isset( $_POST['csp_ts'] ) ? wp_unslash( $_POST['csp_ts'] ) : '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	if ( 'fast' === $tok ) {
		csp_form_done( $is_ajax );
	}
	if ( 'invalid' === $tok ) {
		$fail( array( '_form' => __( 'This form has expired. Please reload the page and try again.', 'caspian-sun' ) ), 400 );
	}

	$name    = isset( $_POST['name'] ) ? csp_oneline( sanitize_text_field( wp_unslash( $_POST['name'] ) ) ) : '';
	$phone   = isset( $_POST['phone'] ) ? csp_oneline( sanitize_text_field( wp_unslash( $_POST['phone'] ) ) ) : '';
	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
	$product = isset( $_POST['product'] ) ? csp_oneline( sanitize_text_field( wp_unslash( $_POST['product'] ) ) ) : '';
	$size    = isset( $_POST['size'] ) ? csp_oneline( sanitize_text_field( wp_unslash( $_POST['size'] ) ) ) : '';
	// phpcs:enable

	$page = csp_page_by_template( 'templates/contact.php' );
	$e    = array();
	if ( mb_strlen( $name ) < 2 ) {
		$e['name'] = __( 'Please enter your name (at least 2 characters).', 'caspian-sun' );
	} elseif ( mb_strlen( $name ) > 100 ) {
		$e['name'] = __( 'Name is too long.', 'caspian-sun' );
	}
	if ( '' !== $phone && ( ! preg_match( '/^[0-9+\-\s().]{6,25}$/', $phone ) || strlen( preg_replace( '/\D/', '', $phone ) ) < 6 ) ) {
		$e['phone'] = __( 'Please enter a valid phone number.', 'caspian-sun' );
	}
	if ( ! is_email( $email ) ) {
		$e['email'] = __( 'Please enter a valid email address.', 'caspian-sun' );
	}
	if ( mb_strlen( $message ) < 10 ) {
		$e['message'] = __( 'Please write a message (at least 10 characters).', 'caspian-sun' );
	} elseif ( mb_strlen( $message ) > 3000 ) {
		$e['message'] = __( 'Message is too long (3000 characters maximum).', 'caspian-sun' );
	}
	if ( $e ) {
		$fail( $e );
	}

	// Rate limit: 5 submissions per IP per 10 minutes.
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key = 'csp_rl_' . md5( $ip );
	$cnt = (int) get_transient( $key );
	if ( $cnt >= 5 ) {
		$fail( array( '_form' => __( 'Too many submissions. Please try again in a few minutes.', 'caspian-sun' ) ), 429 );
	}
	set_transient( $key, $cnt + 1, 10 * MINUTE_IN_SECONDS );

	// Optional reCAPTCHA v3 (keys from Theme Settings → Mail & reCAPTCHA, read on every request).
	list( $rc_site, $secret ) = csp_recaptcha_keys();
	if ( $secret && $rc_site ) {
		$token = isset( $_POST['g-recaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$res   = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array( 'timeout' => 8, 'body' => array( 'secret' => $secret, 'response' => $token, 'remoteip' => $ip ) ) );
		$body  = is_wp_error( $res ) ? array() : json_decode( wp_remote_retrieve_body( $res ), true );
		$min   = (float) csp_mail_opt( 'recaptcha_threshold', 0.5 );
		if ( empty( $body['success'] ) || ( isset( $body['score'] ) && $body['score'] < $min ) || ( isset( $body['action'] ) && 'contact' !== $body['action'] ) ) {
			if ( ! empty( $body['error-codes'] ) ) {
				error_log( 'Caspian theme: reCAPTCHA rejected: ' . implode( ',', (array) $body['error-codes'] ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
			}
			$fail( array( '_form' => csp_get( 'contact_error', $page ) ), 400 );
		}
	}

	// Store the enquiry.
	$title = $product ? sprintf( '%s — %s', $name, $product ) : $name;
	$id    = wp_insert_post(
		array(
			'post_type'   => 'csp_enquiry',
			'post_status' => 'private',
			'post_title'  => wp_strip_all_tags( $title ),
		)
	);
	if ( $id && ! is_wp_error( $id ) ) {
		foreach ( compact( 'name', 'phone', 'email', 'message', 'product', 'size', 'ip' ) as $k => $v ) {
			update_post_meta( $id, '_csp_' . $k, $v );
		}
	}

	// Branded e-mails: notification to the owner, confirmation to the visitor.
	$data = compact( 'name', 'phone', 'email', 'message', 'product', 'size' );
	$sent = csp_mail_notify( $data );
	if ( '0' !== (string) csp_mail_opt( 'autoreply', '1' ) ) {
		csp_mail_confirm( $data );
	}

	// The enquiry is saved even if mail transport is unavailable; log the failure.
	if ( ! $sent ) {
		error_log( 'Caspian theme: wp_mail() failed for enquiry #' . (int) $id ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
	}
	csp_form_done( $is_ajax );
}

function csp_form_done( $is_ajax ) {
	$page = csp_page_by_template( 'templates/contact.php' );
	if ( $is_ajax ) {
		wp_send_json_success( array( 'message' => csp_get( 'contact_success', $page ) ) );
	}
	$back = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	wp_safe_redirect( add_query_arg( 'csp_sent', 'ok', remove_query_arg( 'csp_sent', $back ) ) . '#contact-form' );
	exit;
}
