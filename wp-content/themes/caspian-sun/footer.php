<?php
/**
 * Site footer.
 *
 * @package caspian-sun
 */

defined( 'ABSPATH' ) || exit;

$logo    = csp_opt( 'opt_logo' );
$socials = get_field( 'opt_social', 'option' );
$about   = csp_opt( 'opt_footer_about_text' );
$phone   = csp_opt( 'opt_phone_display' );
$tel     = csp_opt( 'opt_phone_tel' );
$email   = csp_opt( 'opt_email' );
$address = trim( preg_replace( '/\s*[\r\n]+\s*/', ' ', (string) csp_opt( 'opt_address' ) ) );
$legal   = get_field( 'opt_legal_links', 'option' );
$credit  = csp_opt( 'opt_credit_logo' );

/** Accordion column heading (design: h3 with a toggle icon on mobile). */
function csp_footer_h3( $text ) {
	if ( '' === trim( (string) $text ) ) {
		return;
	}
	echo '<h3><span>' . esc_html( $text ) . '</span><span class="footer-toggle-icon"></span></h3>';
}
?>
<footer>
  <div class="footer-grid">
    <div class="footer-section">
      <a class="footer-logo-link" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Home', 'caspian-sun' ); ?>"><?php echo csp_img( $logo, array( 'class' => 'footer-logo-badge' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
      <?php if ( $socials ) : ?>
      <?php if ( csp_opt( 'opt_footer_social_heading' ) ) : ?><h3><?php echo esc_html( csp_opt( 'opt_footer_social_heading' ) ); ?></h3><?php endif; ?>
      <div class="social-links">
        <?php
        foreach ( $socials as $s ) :
			$url  = ! empty( $s['url'] ) ? esc_url( $s['url'] ) : '';
			$icon = csp_icon( $s['network'] );
			if ( ! $url || ! $icon ) {
				continue;
			}
			$labels = csp_icon_choices();
			?>
        <a href="<?php echo $url; // phpcs:ignore WordPress.Security.EscapeOutput ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( isset( $labels[ $s['network'] ] ) ? $labels[ $s['network'] ] : $s['network'] ); ?>"><?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <?php if ( $about || csp_opt( 'opt_footer_about_heading' ) ) : ?>
    <div class="footer-section footer-accordion">
      <?php csp_footer_h3( csp_opt( 'opt_footer_about_heading' ) ); ?>
      <div class="footer-content">
        <?php echo csp_p( $about ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if ( has_nav_menu( 'footer_company' ) ) : ?>
    <div class="footer-section footer-accordion">
      <?php csp_footer_h3( csp_opt( 'opt_footer_company_heading' ) ); ?>
      <div class="footer-content"><?php csp_list_menu( 'footer_company' ); ?></div>
    </div>
    <?php endif; ?>

    <?php if ( has_nav_menu( 'footer_products' ) ) : ?>
    <div class="footer-section footer-accordion">
      <?php csp_footer_h3( csp_opt( 'opt_footer_products_heading' ) ); ?>
      <div class="footer-content"><?php csp_list_menu( 'footer_products' ); ?></div>
    </div>
    <?php endif; ?>

    <?php if ( $phone || $email || $address ) : ?>
    <div class="footer-section footer-accordion">
      <?php csp_footer_h3( csp_opt( 'opt_footer_contact_heading' ) ); ?>
      <div class="footer-content">
        <?php if ( $phone ) : ?>
        <div class="footer-contact-item"><?php echo csp_icon( 'phone-footer' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo $tel ? '<a href="tel:' . esc_attr( preg_replace( '/[^\d+]/', '', $tel ) ) . '">' . esc_html( $phone ) . '</a>' : '<span>' . esc_html( $phone ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
        <?php endif; ?>
        <?php if ( $email ) : ?>
        <div class="footer-contact-item"><?php echo csp_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"><?php echo esc_html( antispambot( $email ) ); ?></a></div>
        <?php endif; ?>
        <?php if ( $address ) : ?>
        <div class="footer-contact-item"><?php echo csp_icon( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $address ); ?></span></div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <div class="footer-bottom">
    <?php if ( csp_opt( 'opt_copyright' ) ) : ?><p><?php echo esc_html( csp_opt( 'opt_copyright' ) ); ?></p><?php endif; ?>
    <?php
    $links = array();
    foreach ( (array) $legal as $row ) {
		$l = csp_link( isset( $row['link'] ) ? $row['link'] : null );
		if ( $l && $l['title'] ) {
			$links[] = '<a ' . csp_link_attrs( $row['link'] ) . '>' . esc_html( $l['title'] ) . '</a>';
		}
	}
    if ( $links ) {
		echo '<p>' . implode( ' | ', $links ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
    ?>
    <?php
    $credit_link = csp_opt( 'opt_credit_link' );
    if ( csp_opt( 'opt_credit_text' ) || $credit ) :
		?>
    <p><?php echo esc_html( csp_opt( 'opt_credit_text' ) ); ?>
      <?php if ( $credit && csp_link( $credit_link ) ) : ?>
      <a <?php echo csp_link_attrs( $credit_link, 'style="display:inline-flex;align-items:center;vertical-align:middle;margin-left:4px;"' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo csp_img( $credit, array( 'style' => 'height:14px;width:auto;display:block;' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
      <?php elseif ( $credit ) : ?>
        <?php echo csp_img( $credit, array( 'style' => 'height:14px;width:auto;display:inline-block;vertical-align:middle;margin-left:4px;' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php endif; ?>
    </p>
    <?php endif; ?>
  </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
