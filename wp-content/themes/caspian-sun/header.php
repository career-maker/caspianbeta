<?php
/**
 * Site header + mobile drawer.
 *
 * @package caspian-sun
 */

defined( 'ABSPATH' ) || exit;

$logo       = csp_opt( 'opt_logo' );
$cta        = csp_opt( 'opt_header_cta' );
$cta_l      = csp_link( $cta );
$show_phone = csp_opt( 'opt_header_phone_show' );
$phone      = csp_opt( 'opt_phone_display' );
$tel        = csp_opt( 'opt_phone_tel' );
$site       = get_bloginfo( 'name' );

/** Phone number block (span wrapper keeps the approved styling). */
$phone_html = '';
if ( $show_phone && $phone ) {
	$phone_html  = '<span class="phone-cta">' . csp_icon( 'phone' );
	$phone_html .= $tel ? '<a href="tel:' . esc_attr( preg_replace( '/[^\d+]/', '', $tel ) ) . '">' . esc_html( $phone ) . '</a>' : esc_html( $phone );
	$phone_html .= '</span>';
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'caspian-sun' ); ?></a>

<header>
  <div class="header-start">
  <?php if ( $cta_l && $cta_l['title'] ) : ?>
  <a class="cta-btn cta-left" <?php echo csp_link_attrs( $cta ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $cta_l['title'] ); ?></a>
  <?php endif; ?>
  <nav aria-label="<?php esc_attr_e( 'Primary', 'caspian-sun' ); ?>">
    <?php csp_flat_menu( 'header_left' ); ?>
  </nav>
  </div>
  <div class="logo"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( $site . ' — ' . __( 'Home', 'caspian-sun' ) ); ?>"><?php echo csp_img( $logo, array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></div>
  <div class="header-end">
  <nav class="nav-right" aria-label="<?php esc_attr_e( 'Secondary', 'caspian-sun' ); ?>">
    <?php csp_flat_menu( 'header_right' ); ?>
  </nav>
  <div class="header-right">
    <?php echo $phone_html; // phpcs:ignore WordPress.Security.EscapeOutput ?>
    <?php if ( $cta_l && $cta_l['title'] ) : ?>
    <a class="cta-btn cta-right" <?php echo csp_link_attrs( $cta ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $cta_l['title'] ); ?></a>
    <?php endif; ?>
    <button class="hamburger" id="hamburgerBtn" aria-label="<?php esc_attr_e( 'Open menu', 'caspian-sun' ); ?>" aria-expanded="false" aria-controls="mobileDrawer">
      <span></span><span></span><span></span>
    </button>
  </div>
  </div>
</header>

<div class="drawer-backdrop" id="drawerBackdrop"></div>
<aside class="mobile-drawer" id="mobileDrawer" aria-label="<?php esc_attr_e( 'Mobile navigation', 'caspian-sun' ); ?>">
  <div class="drawer-top">
    <a class="drawer-logo-link" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Home', 'caspian-sun' ); ?>"><?php echo csp_img( $logo, array( 'class' => 'drawer-logo', 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
    <button class="drawer-close" id="drawerClose" aria-label="<?php esc_attr_e( 'Close menu', 'caspian-sun' ); ?>">&times;</button>
  </div>
  <?php
	$dr_tel = preg_replace( '/[^\d+]/', '', (string) csp_opt( 'opt_phone_tel' ) );
	$dr_wa  = preg_replace( '/\D/', '', (string) csp_opt( 'opt_whatsapp_number' ) );
	if ( $dr_tel || $dr_wa ) :
		?>
  <div class="drawer-contact">
    <?php if ( $dr_tel ) : ?>
    <a class="dc-call" href="tel:<?php echo esc_attr( $dr_tel ); ?>"><?php echo csp_icon( 'phone-line' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( csp_get( 'contact_label_call', csp_page_by_template( 'templates/contact.php' ) ) ? csp_get( 'contact_label_call', csp_page_by_template( 'templates/contact.php' ) ) : __( 'Call', 'caspian-sun' ) ); ?></span></a>
    <?php endif; ?>
    <?php if ( $dr_wa ) : ?>
    <a class="dc-wa" href="<?php echo esc_url( 'https://wa.me/' . $dr_wa ); ?>" target="_blank" rel="noopener"><?php echo csp_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( csp_get( 'contact_label_whatsapp', csp_page_by_template( 'templates/contact.php' ) ) ? csp_get( 'contact_label_whatsapp', csp_page_by_template( 'templates/contact.php' ) ) : 'WhatsApp' ); ?></span></a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
  <nav class="drawer-nav" aria-label="<?php esc_attr_e( 'Mobile', 'caspian-sun' ); ?>">
    <?php csp_flat_menu( 'header_left' ); ?>
    <?php csp_flat_menu( 'header_right' ); ?>
  </nav>
  <div class="drawer-footer">
    <?php echo $phone_html; // phpcs:ignore WordPress.Security.EscapeOutput ?>
    <?php if ( $cta_l && $cta_l['title'] ) : ?>
    <a class="cta-btn-solid" <?php echo csp_link_attrs( $cta ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $cta_l['title'] ); ?></a>
    <?php endif; ?>
  </div>
</aside>
