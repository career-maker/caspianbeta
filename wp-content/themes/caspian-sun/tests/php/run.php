<?php
/**
 * Unit tests (no WordPress needed): php -d extension=mbstring tests/php/run.php
 * Covers validation rules, mail/SMTP configuration, secret handling and the lower-case URL redirect helper.
 */
define( 'ABSPATH', __DIR__ );
define( 'CSP_URI', 'http://example.test/wp-content/themes/caspian-sun' );
define( 'PHP_URL_PATH_FALLBACK', 1 );

/* -------- minimal WordPress stubs -------- */
$GLOBALS['opts'] = array();
function __( $s ) { return $s; }
function get_option( $k, $d = false ) { return isset( $GLOBALS['opts'][ $k ] ) ? $GLOBALS['opts'][ $k ] : $d; }
function update_option( $k, $v ) { $GLOBALS['opts'][ $k ] = $v; return true; }
function add_action() {}
function add_filter() {}
function remove_action() {}
function register_setting() {}
function is_email( $e ) { return (bool) filter_var( $e, FILTER_VALIDATE_EMAIL ); }
function sanitize_email( $e ) { return preg_replace( '/[^a-z0-9+_.@-]/i', '', $e ); }
function sanitize_text_field( $s ) { return trim( strip_tags( (string) $s ) ); }
function sanitize_textarea_field( $s ) { return trim( strip_tags( (string) $s ) ); }
function wp_unslash( $v ) { return $v; }
function absint( $n ) { return abs( (int) $n ); }
function get_bloginfo() { return 'Caspian &amp; Sun'; }
function wp_specialchars_decode( $s ) { return html_entity_decode( $s ); }
function wp_parse_url( $u, $c = -1 ) { return parse_url( $u, $c ); }
function get_posts() { return array(); }
function csp_opt() { return ''; }

require dirname( __DIR__, 2 ) . '/inc/validation.php';
require dirname( __DIR__, 2 ) . '/inc/mail.php';
require dirname( __DIR__, 2 ) . '/inc/hardening.php';

/* -------- tiny test harness -------- */
$pass = 0; $fail = 0;
function t( $name, $cond, $info = '' ) {
	global $pass, $fail;
	if ( $cond ) { $pass++; } else { $fail++; echo "FAIL: $name $info\n"; }
}
function eq( $name, $got, $want ) { t( $name, $got === $want, 'got ' . var_export( $got, true ) . ' want ' . var_export( $want, true ) ); }

/* 1. Field validation matrix (shared with the JS suite) */
$cases = json_decode( file_get_contents( dirname( __DIR__ ) . '/validation-cases.json' ), true );
foreach ( $cases as list( $f, $v, $exp ) ) {
	switch ( $f ) {
		case 'name':    $r = csp_v_name( $v ); break;
		case 'phone':   $r = csp_v_phone( $v ); break;
		case 'email':   $r = csp_v_email( $v ); break;
		case 'message': $r = csp_v_text( $v, 'message', 2, 3000, true ); break;
		case 'subject': $r = csp_v_text( $v, 'subject', 2, 150, false ); break;
	}
	t( "matrix $f " . json_encode( mb_substr( $v, 0, 30 ), JSON_UNESCAPED_UNICODE ), ( '' === $r[1] ) === $exp, $r[1] );
}

/* 2. Normalisation */
eq( 'name collapses spaces', csp_v_name( '  John    Smith ' )[0], 'John Smith' );
eq( 'phone collapses spaces', csp_v_phone( ' 722   852 8095 ' )[0], '722 852 8095' );
eq( 'email trims', csp_v_email( ' a@b.co ' )[0], 'a@b.co' );
eq( 'subject newline becomes space', csp_v_text( "Hello\nWorld", 'subject', 2, 150, false )[0], 'Hello World' );
eq( 'message keeps newline', csp_v_text( "Hello\nWorld", 'message', 2, 3000, true )[0], "Hello\nWorld" );
eq( 'message CRLF normalised', csp_v_text( "A1\r\nB2", 'message', 2, 3000, true )[0], "A1\nB2" );

/* 3. Boundaries */
t( 'name 100 chars ok', '' === csp_v_name( str_repeat( 'a', 100 ) )[1] );
t( 'name 101 chars rejected', '' !== csp_v_name( str_repeat( 'a', 101 ) )[1] );
t( 'phone 7 digits ok', '' === csp_v_phone( '1234567' )[1] );
t( 'phone 6 digits rejected', '' !== csp_v_phone( '123456' )[1] );
t( 'phone 15 digits ok', '' === csp_v_phone( '123456789012345' )[1] );
t( 'phone 16 digits rejected', '' !== csp_v_phone( '1234567890123456' )[1] );
t( 'message 3000 ok', '' === csp_v_text( str_repeat( 'a', 3000 ), 'message', 2, 3000, true )[1] );
t( 'message 3001 rejected', '' !== csp_v_text( str_repeat( 'a', 3001 ), 'message', 2, 3000, true )[1] );
t( 'subject 150 ok', '' === csp_v_text( str_repeat( 'a', 150 ), 'subject', 2, 150, false )[1] );
t( 'subject 151 rejected', '' !== csp_v_text( str_repeat( 'a', 151 ), 'subject', 2, 150, false )[1] );
t( 'email local part 64 ok', '' === csp_v_email( str_repeat( 'a', 64 ) . '@example.com' )[1] );
t( 'email local part 65 rejected', '' !== csp_v_email( str_repeat( 'a', 65 ) . '@example.com' )[1] );
t( 'email consecutive dots rejected', '' !== csp_v_email( 'a..b@example.com' )[1] );
t( 'email no TLD rejected', '' !== csp_v_email( 'a@localhost' )[1] );
t( 'control char rejected', '' !== csp_v_text( "Hi\x00there", 'message', 2, 3000, true )[1] );
t( 'normal sentence with "select ... from" accepted', '' === csp_v_text( 'Please select the product from your list.', 'message', 2, 3000, true )[1] );
t( 'sentence with "on" word accepted', '' === csp_v_text( 'Delivery depends on the port and onions are fine.', 'message', 2, 3000, true )[1] );

/* 4. Injection patterns */
foreach ( array( '<svg onload=alert(1)>', 'javascript:alert(1)', '{{7*7}}', '${jndi:ldap://x}', '1 UNION SELECT password FROM users', "x' OR '1'='1", '<!-- hi -->', '<?php echo 1; ?>' ) as $bad ) {
	t( 'injection rejected: ' . $bad, csp_v_injection( $bad ) );
}

/* 5. SMTP configuration */
$GLOBALS['opts'] = array();
eq( 'no config -> SMTP off', csp_smtp_config(), array() );
$GLOBALS['opts']['csp_mail'] = array( 'gmail' => 'me@gmail.com', 'app_password' => 'abcd efgh ijkl mnop' );
$c = csp_smtp_config();
eq( 'gmail defaults host', $c['host'], 'smtp.gmail.com' );
eq( 'gmail defaults port', $c['port'], 587 );
eq( 'gmail defaults secure', $c['secure'], 'tls' );
eq( 'password spaces stripped', $c['pass'], 'abcdefghijklmnop' );
eq( 'username defaults to gmail', $c['user'], 'me@gmail.com' );
eq( 'from defaults to gmail', $c['from'], 'me@gmail.com' );
$GLOBALS['opts']['csp_mail'] = array( 'gmail' => 'me@gmail.com', 'app_password' => 'x', 'smtp_host' => 'mail.example.com', 'smtp_secure' => 'ssl', 'smtp_user' => 'u', 'smtp_from' => 'no-reply@example.com' );
$c = csp_smtp_config();
eq( 'custom host', $c['host'], 'mail.example.com' );
eq( 'ssl default port 465', $c['port'], 465 );
eq( 'custom user', $c['user'], 'u' );
eq( 'custom from', $c['from'], 'no-reply@example.com' );
$GLOBALS['opts']['csp_mail'] = array( 'gmail' => 'me@gmail.com', 'app_password' => '' );
eq( 'missing password -> SMTP off', csp_smtp_config(), array() );
$GLOBALS['opts']['csp_mail'] = array( 'gmail' => 'me@gmail.com', 'app_password' => 'x', 'smtp_secure' => 'bogus' );
eq( 'invalid encryption falls back to tls', csp_smtp_config()['secure'], 'tls' );

/* 6. Recipient + reCAPTCHA keys */
$GLOBALS['opts'] = array( 'admin_email' => 'admin@site.test', 'csp_mail' => array( 'gmail' => 'me@gmail.com' ) );
eq( 'recipient defaults to gmail', csp_enquiry_recipient(), 'me@gmail.com' );
$GLOBALS['opts']['csp_mail']['recipient'] = 'sales@site.test';
eq( 'explicit recipient wins', csp_enquiry_recipient(), 'sales@site.test' );
$GLOBALS['opts']['csp_mail'] = array();
eq( 'falls back to admin email', csp_enquiry_recipient(), 'admin@site.test' );
eq( 'reCAPTCHA off without keys', csp_recaptcha_keys(), array( '', '' ) );
$GLOBALS['opts']['csp_mail'] = array( 'recaptcha_site' => 'S' );
eq( 'reCAPTCHA needs both keys', csp_recaptcha_keys(), array( '', '' ) );
$GLOBALS['opts']['csp_mail'] = array( 'recaptcha_site' => 'S', 'recaptcha_secret' => 'K' );
eq( 'reCAPTCHA on with both keys', csp_recaptcha_keys(), array( 'S', 'K' ) );

/* 7. Settings sanitiser: secrets are kept, replaced or cleared correctly */
$GLOBALS['opts']['csp_mail'] = array( 'app_password' => 'oldpass', 'recaptcha_secret' => 'oldsecret' );
$o = csp_mail_sanitize( array( 'gmail' => 'a@b.co', 'app_password' => '', 'recaptcha_secret' => '' ) );
eq( 'blank password keeps saved', $o['app_password'], 'oldpass' );
eq( 'blank secret keeps saved', $o['recaptcha_secret'], 'oldsecret' );
$o = csp_mail_sanitize( array( 'app_password' => 'new pass here' ) );
eq( 'new password replaces and strips spaces', $o['app_password'], 'newpasshere' );
$o = csp_mail_sanitize( array( 'app_password' => '', 'clear_app_password' => '1' ) );
eq( 'remove box clears password', $o['app_password'], '' );
$o = csp_mail_sanitize( array( 'gmail' => 'not-an-email', 'recipient' => '<b>x</b>' ) );
eq( 'invalid gmail dropped', $o['gmail'], '' );
eq( 'invalid recipient dropped', $o['recipient'], '' );
$o = csp_mail_sanitize( array( 'recaptcha_threshold' => '5' ) );
eq( 'threshold clamped to 0.9', $o['recaptcha_threshold'], '0.9' );
$o = csp_mail_sanitize( array( 'recaptcha_threshold' => '0.01' ) );
eq( 'threshold clamped to 0.1', $o['recaptcha_threshold'], '0.1' );
$o = csp_mail_sanitize( array() );
eq( 'unchecked confirmation stored as 0', $o['autoreply'], '0' );
$o = csp_mail_sanitize( array( 'autoreply' => '1', 'smtp_host' => 'mail.exa mple.com<script>' ) );
eq( 'host stripped of illegal characters', $o['smtp_host'], 'mail.example.com' );
$o = csp_mail_sanitize( array( 'smtp_secure' => 'weird' ) );
eq( 'bad encryption normalised', $o['smtp_secure'], 'tls' );

/* 8. HTML mailer: escapes content, includes message, plain-text twin */
define( 'WPINC_TEST', 1 );
function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $s ) { return esc_html( $s ); }
function esc_url( $s ) { return htmlspecialchars( (string) $s ); }
function wp_date() { return '1 Jan 2026'; }
$html = csp_mail_html( array( 'title' => 'Hi <script>', 'intro' => 'a & b', 'rows' => array( 'Name' => '"Q" <i>x</i>', 'Empty' => '' ), 'message' => "line1\nline2", 'button' => array( 'Go', 'http://x/?a=1&b=2' ) ) );
t( 'mailer escapes title', false === strpos( $html, '<script>' ) );
t( 'mailer escapes row value', false !== strpos( $html, '&quot;Q&quot; &lt;i&gt;x&lt;/i&gt;' ) );
t( 'mailer skips empty rows', false === strpos( $html, '>Empty<' ) );
t( 'mailer keeps line breaks', false !== strpos( $html, 'line1<br' ) );
t( 'mailer has doctype + logo', 0 === strpos( ltrim( $html ), '<!doctype html>' ) && false !== strpos( $html, CSP_URI . '/assets/images/mail-logo.png' ) );
$txt = csp_mail_text( array( 'title' => 'T', 'intro' => 'I', 'rows' => array( 'Name' => 'N' ), 'message' => 'M', 'message_label' => 'Msg' ) );
t( 'plain-text twin has the fields', false !== strpos( $txt, 'Name: N' ) && false !== strpos( $txt, "Msg:\nM" ) );

/* 9. Lower-case URL redirect helper */
eq( 'lower-case path untouched', csp_url_case_target( '/about/', '' ), '' );
eq( 'upper-case path redirected', csp_url_case_target( '/About-Us/', '' ), '/about-us/' );
eq( 'query string preserved as-is', csp_url_case_target( '/About/?utm=ABC', '' ), '/about/?utm=ABC' );
eq( 'upper-case query alone does not redirect', csp_url_case_target( '/about/?utm=ABC', '' ), '' );
eq( 'sub-folder base kept', csp_url_case_target( '/caspwp/About/', '/caspwp' ), '/caspwp/about/' );
eq( 'sub-folder base case not touched', csp_url_case_target( '/caspwp/about/', '/caspwp' ), '' );
eq( 'wp-content skipped', csp_url_case_target( '/wp-content/uploads/Logo.PNG', '' ), '' );
eq( 'wp-admin skipped', csp_url_case_target( '/wp-admin/Index.php', '' ), '' );
eq( 'wp-login skipped', csp_url_case_target( '/wp-login.php', '' ), '' );
eq( 'percent escapes are not "upper case"', csp_url_case_target( '/caf%C3%A9/', '' ), '' );

echo "\n$pass passed, $fail failed\n";
exit( $fail ? 1 : 0 );
