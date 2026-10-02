<?php
/**
 * Template Name: Contact
 *
 * @package caspian-sun
 */

defined( 'ABSPATH' ) || exit;

get_header();

$phone    = csp_opt( 'opt_phone_display' );
$tel      = preg_replace( '/[^\d+]/', '', (string) csp_opt( 'opt_phone_tel' ) );
$wa_disp  = csp_opt( 'opt_whatsapp_display' );
$wa_num   = preg_replace( '/\D/', '', (string) csp_opt( 'opt_whatsapp_number' ) );
$email    = csp_opt( 'opt_email' );
$address  = csp_opt( 'opt_address' );
$hours    = csp_opt( 'opt_hours' );

// Product enquiry context from the product page ("Contact Us" button).
// phpcs:disable WordPress.Security.NonceVerification
$q_product = isset( $_GET['enquiry_product'] ) ? html_entity_decode( sanitize_text_field( wp_unslash( $_GET['enquiry_product'] ) ), ENT_QUOTES, 'UTF-8' ) : '';
$q_size    = isset( $_GET['enquiry_size'] ) ? sanitize_text_field( wp_unslash( $_GET['enquiry_size'] ) ) : '';
$sent      = isset( $_GET['csp_sent'] ) ? sanitize_key( wp_unslash( $_GET['csp_sent'] ) ) : '';
// phpcs:enable

$token   = csp_form_token();
$catalog = csp_enquiry_products();

// Preselect the product / packing sent by the product page (case-insensitive match against the catalogue).
$sel_product = '';
foreach ( array_keys( $catalog ) as $title ) {
	if ( '' !== $q_product && 0 === strcasecmp( $title, $q_product ) ) {
		$sel_product = $title;
		break;
	}
}
$sel_size = '';
if ( $sel_product ) {
	foreach ( $catalog[ $sel_product ] as $sz ) {
		if ( '' !== $q_size && 0 === strcasecmp( $sz, $q_size ) ) {
			$sel_size = $sz;
			break;
		}
	}
}
$product_opts = array( '' => csp_get( 'contact_product_placeholder' ) ? csp_get( 'contact_product_placeholder' ) : __( 'General enquiry (no specific product)', 'caspian-sun' ) );
foreach ( array_keys( $catalog ) as $title ) {
	$product_opts[ $title ] = $title;
}
$packing_ph = csp_get( 'contact_packing_placeholder' ) ? csp_get( 'contact_packing_placeholder' ) : __( 'Select packing', 'caspian-sun' );
$size_opts  = array( '' => $packing_ph );
if ( $sel_product ) {
	foreach ( $catalog[ $sel_product ] as $sz ) {
		$size_opts[ $sz ] = $sz;
	}
}

/** Labelled input / textarea. Validation rules live in inc/validation.php (server) and assets/js/contact.js (live). */
function csp_field_row( $id, $name, $type, $label, $placeholder, $required, $extra = '' ) {
	if ( '' === (string) $label ) {
		return;
	}
	$req = $required ? ' <span class="req" aria-hidden="true">*</span>' : '';
	echo '<div class="form-group"><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . $req . '</label>'; // phpcs:ignore WordPress.Security.EscapeOutput
	if ( 'textarea' === $type ) {
		echo '<textarea id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '"' . ( $placeholder ? ' placeholder="' . esc_attr( $placeholder ) . '"' : '' ) . ( $required ? ' aria-required="true"' : '' ) . ' rows="5" aria-describedby="' . esc_attr( $id ) . '-err"></textarea>';
	} else {
		echo '<input id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" type="' . esc_attr( $type ) . '"' . ( $placeholder ? ' placeholder="' . esc_attr( $placeholder ) . '"' : '' ) . ( $required ? ' aria-required="true"' : '' ) . ' ' . $extra . ' aria-describedby="' . esc_attr( $id ) . '-err">'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '<span class="form-error" id="' . esc_attr( $id ) . '-err" role="alert"></span></div>';
}

/** Labelled <select>. $options: array( value => label ). */
function csp_select_row( $id, $name, $label, $options, $selected, $disabled = false ) {
	echo '<div class="form-group"><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';
	echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '"' . ( $disabled ? ' disabled' : '' ) . ' aria-describedby="' . esc_attr( $id ) . '-err">';
	foreach ( $options as $val => $text ) {
		echo '<option value="' . esc_attr( $val ) . '"' . selected( (string) $selected, (string) $val, false ) . '>' . esc_html( $text ) . '</option>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '</select><span class="form-error" id="' . esc_attr( $id ) . '-err" role="alert"></span></div>';
}
?>
<main id="main">

  <?php csp_banner( 'contact' ); ?>
  <?php csp_breadcrumb( array( array( get_the_title(), '' ) ) ); ?>

  <section class="contact-section">
    <div class="contact-grid">
      <div class="form-section" id="contact-form">
        <?php echo csp_heading( csp_get( 'contact_form_heading' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <?php echo csp_p( csp_get( 'contact_form_text' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <?php if ( 'ok' === $sent ) : ?>
        <div class="form-status is-success" role="status"><?php echo esc_html( csp_get( 'contact_success' ) ); ?></div>
        <?php elseif ( 'err' === $sent ) : ?>
        <div class="form-status is-error" role="alert"><?php echo esc_html( csp_get( 'contact_error' ) ); ?></div>
        <?php endif; ?>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="cspContactForm" novalidate data-products="<?php echo esc_attr( wp_json_encode( $catalog ) ); ?>" data-packing-label="<?php echo esc_attr( $packing_ph ); ?>" data-sending="<?php echo esc_attr( csp_get( 'contact_sending' ) ); ?>" data-success="<?php echo esc_attr( csp_get( 'contact_success' ) ); ?>" data-error="<?php echo esc_attr( csp_get( 'contact_error' ) ); ?>">
          <input type="hidden" name="action" value="csp_contact">
          <?php wp_nonce_field( 'csp_contact', 'csp_nonce' ); ?>
          <input type="hidden" name="csp_ts" value="<?php echo esc_attr( $token ); ?>">
          <div class="form-summary" id="cf-summary" role="alert" tabindex="-1" hidden><strong><?php echo esc_html( csp_get( 'contact_summary_heading' ) ? csp_get( 'contact_summary_heading' ) : __( 'Please check these fields', 'caspian-sun' ) ); ?></strong><ul></ul></div>
          <div class="csp-hp" aria-hidden="true"><label>Leave this field empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
          <div class="form-row">
            <?php csp_field_row( 'cf-name', 'name', 'text', ( csp_get( 'contact_label_name' ) ? csp_get( 'contact_label_name' ) : __( 'Name', 'caspian-sun' ) ), csp_get( 'contact_placeholder_name' ), true, 'autocomplete="name"' ); ?>
            <?php csp_field_row( 'cf-phone', 'phone', 'tel', ( csp_get( 'contact_label_phone' ) ? csp_get( 'contact_label_phone' ) : __( 'Phone', 'caspian-sun' ) ), csp_get( 'contact_placeholder_phone' ), true, 'autocomplete="tel" inputmode="tel"' ); ?>
          </div>
          <?php csp_field_row( 'cf-email', 'email', 'email', ( csp_get( 'contact_label_email' ) ? csp_get( 'contact_label_email' ) : __( 'Email', 'caspian-sun' ) ), csp_get( 'contact_placeholder_email' ), true, 'autocomplete="email"' ); ?>
          <div class="form-row">
            <?php csp_select_row( 'cf-product', 'product', csp_get( 'contact_label_product' ) ? csp_get( 'contact_label_product' ) : __( 'Product', 'caspian-sun' ), $product_opts, $sel_product ); ?>
            <?php csp_select_row( 'cf-size', 'size', csp_get( 'contact_label_packing' ) ? csp_get( 'contact_label_packing' ) : __( 'Packing', 'caspian-sun' ), $size_opts, $sel_size, ! $sel_product || count( $size_opts ) < 2 ); ?>
          </div>
          <?php csp_field_row( 'cf-subject', 'subject', 'text', csp_get( 'contact_label_subject' ) ? csp_get( 'contact_label_subject' ) : __( 'Subject', 'caspian-sun' ), csp_get( 'contact_placeholder_subject' ) ? csp_get( 'contact_placeholder_subject' ) : __( 'How can we help?', 'caspian-sun' ), true ); ?>
          <?php csp_field_row( 'cf-message', 'message', 'textarea', ( csp_get( 'contact_label_message' ) ? csp_get( 'contact_label_message' ) : __( 'Message', 'caspian-sun' ) ), csp_get( 'contact_placeholder_message' ), true ); ?>
          <?php if ( csp_get( 'contact_submit' ) ) : ?>
          <div class="btn-shop"><button type="submit" class="form-submit"><?php echo esc_html( csp_get( 'contact_submit' ) ); ?></button></div>
          <?php endif; ?>
          <div class="form-status" role="status" aria-live="polite"></div>
        </form>
      </div>

      <div class="info-box">
        <?php echo csp_heading( csp_get( 'contact_info_heading' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <?php if ( $phone || $wa_disp ) : ?>
        <div class="info-item">
          <?php echo csp_get( 'contact_label_phones' ) ? '<div class="info-label">' . esc_html( csp_get( 'contact_label_phones' ) ) . '</div>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
          <ul class="contact-lines">
            <?php if ( $phone ) : ?>
            <li><span class="cl-icon" aria-hidden="true"><?php echo csp_icon( 'phone-line' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><span class="cl-text"><?php echo csp_get( 'contact_label_call' ) ? '<span class="cl-label">' . esc_html( csp_get( 'contact_label_call' ) ) . '</span>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo $tel ? '<a href="tel:' . esc_attr( $tel ) . '">' . esc_html( $phone ) . '</a>' : esc_html( $phone ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span></li>
            <?php endif; ?>
            <?php if ( $wa_disp ) : ?>
            <li><span class="cl-icon cl-wa" aria-hidden="true"><?php echo csp_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><span class="cl-text"><?php echo csp_get( 'contact_label_whatsapp' ) ? '<span class="cl-label">' . esc_html( csp_get( 'contact_label_whatsapp' ) ) . '</span>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo $wa_num ? '<a href="' . esc_url( 'https://wa.me/' . $wa_num ) . '" target="_blank" rel="noopener" aria-label="' . esc_attr( sprintf( /* translators: %s: label */ __( 'Chat with us on %s', 'caspian-sun' ), 'WhatsApp' ) ) . '">' . esc_html( $wa_disp ) . '</a>' : esc_html( $wa_disp ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span></li>
            <?php endif; ?>
          </ul>
        </div>
        <?php endif; ?>
        <?php if ( $email ) : ?>
        <div class="info-item">
          <?php echo csp_get( 'contact_label_mail' ) ? '<div class="info-label">' . esc_html( csp_get( 'contact_label_mail' ) ) . '</div>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
          <div class="info-value"><a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"><?php echo esc_html( antispambot( $email ) ); ?></a></div>
        </div>
        <?php endif; ?>
        <?php if ( $address ) : ?>
        <div class="info-item">
          <?php echo csp_get( 'contact_label_location' ) ? '<div class="info-label">' . esc_html( csp_get( 'contact_label_location' ) ) . '</div>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
          <div class="info-value"><?php echo csp_br( $address ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
        </div>
        <?php endif; ?>
        <?php if ( $hours ) : ?>
        <div class="info-item">
          <?php echo csp_get( 'contact_label_hours' ) ? '<div class="info-label">' . esc_html( csp_get( 'contact_label_hours' ) ) . '</div>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
          <div class="info-value"><?php echo csp_br( $hours ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <?php if ( csp_get( 'contact_map_heading' ) || $address ) : ?>
  <section class="map-section">
    <?php echo csp_heading( csp_get( 'contact_map_heading' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    <?php echo csp_p( csp_get( 'contact_map_text' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    <div class="map-container">
      <div class="map-placeholder">
        <div class="map-marker">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="#D7BB51" aria-hidden="true"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 0 1 0-5 2.5 2.5 0 0 1 0 5z"/></svg>
        </div>
      </div>
      <?php if ( csp_get( 'contact_map_name' ) || $address ) : ?>
      <div class="location-card">
        <?php echo csp_get( 'contact_map_name' ) ? '<div class="location-name">' . esc_html( csp_get( 'contact_map_name' ) ) . '</div>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <?php echo $address ? '<div class="location-details">' . csp_br( $address ) . '</div>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
      </div>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php
	$cta_img = csp_get( 'contact_cta_image' );
	if ( $cta_img || csp_get( 'contact_cta_heading' ) ) :
		?>
  <section class="cta-section">
    <div class="cta-grid">
      <?php if ( $cta_img ) : ?><div class="cta-image"><?php echo csp_img( $cta_img, array( 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div><?php endif; ?>
      <div class="cta-content">
        <?php echo csp_img( csp_get( 'contact_cta_watermark' ), array( 'class' => 'cta-watermark', 'alt' => '', 'aria-hidden' => 'true', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <?php echo csp_heading( csp_get( 'contact_cta_heading' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <?php echo csp_p( csp_get( 'contact_cta_text' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <?php echo csp_btn( csp_get( 'contact_cta_button' ), 'btn-shop', '', 'cta-button-alt' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

</main>
<?php get_footer(); ?>
