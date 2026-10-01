<?php
/**
 * Legal document layout (Privacy Policy / Terms of Use).
 *
 * @package caspian-sun
 * @var string $args['prefix'] ACF field prefix.
 */

defined( 'ABSPATH' ) || exit;

$p        = $args['prefix'];
$sections = array_values( array_filter( (array) get_field( $p . '_sections' ), function ( $s ) {
	return ! empty( $s['title'] ) || ! empty( $s['content'] );
} ) );
$also     = csp_get( $p . '_also_link' );
$also_l   = csp_link( $also );
$phone    = csp_opt( 'opt_phone_display' );
$tel      = preg_replace( '/[^\d+]/', '', (string) csp_opt( 'opt_phone_tel' ) );
$email    = csp_opt( 'opt_email' );
$address  = trim( preg_replace( '/\s*[\r\n]+\s*/', ' ', (string) csp_opt( 'opt_address' ) ) );
$company  = csp_opt( 'opt_company_name' ) ? csp_opt( 'opt_company_name' ) : csp_opt( 'seo_org_name' );
?>
<main id="main">

  <?php csp_banner( $p ); ?>
  <?php csp_breadcrumb( array( array( get_the_title(), '' ) ) ); ?>

  <div class="legal">
    <?php if ( $sections ) : ?>
    <aside class="toc reveal" aria-label="<?php echo esc_attr( csp_get( $p . '_toc_heading' ) ? csp_get( $p . '_toc_heading' ) : __( 'On this page', 'caspian-sun' ) ); ?>">
      <?php echo csp_get( $p . '_toc_heading' ) ? '<h2>' . esc_html( csp_get( $p . '_toc_heading' ) ) . '</h2>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <ol>
        <?php foreach ( $sections as $i => $s ) : ?>
          <?php if ( ! empty( $s['title'] ) ) : ?><li><a href="#s<?php echo (int) ( $i + 1 ); ?>"><?php echo esc_html( $s['title'] ); ?></a></li><?php endif; ?>
        <?php endforeach; ?>
      </ol>
    </aside>
    <?php endif; ?>

    <div>
      <article class="doc reveal">
        <?php echo csp_get( $p . '_updated' ) ? '<div class="updated">' . esc_html( csp_get( $p . '_updated' ) ) . '</div>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <?php echo csp_p( csp_get( $p . '_lead' ), 'lead' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>

        <?php foreach ( $sections as $i => $s ) : ?>
        <section id="s<?php echo (int) ( $i + 1 ); ?>">
          <?php if ( ! empty( $s['title'] ) ) : ?><h2><span class="n"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span><?php echo esc_html( $s['title'] ); ?></h2><?php endif; ?>
          <?php echo wp_kses_post( $s['content'] ); ?>
          <?php if ( ! empty( $s['card'] ) ) : ?>
          <div class="contact-card">
            <?php echo ( $company ) ? '<div><span>' . esc_html( csp_get( $p . '_card_company' ) ) . '</span>' . esc_html( $company ) . '</div>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
            <?php echo ( $email ) ? '<div><span>' . esc_html( csp_get( $p . '_card_email' ) ) . '</span><a href="mailto:' . esc_attr( antispambot( $email ) ) . '">' . esc_html( antispambot( $email ) ) . '</a></div>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
            <?php echo ( $phone ) ? '<div><span>' . esc_html( csp_get( $p . '_card_phone' ) ) . '</span>' . ( $tel ? '<a href="tel:' . esc_attr( $tel ) . '">' . esc_html( $phone ) . '</a>' : esc_html( $phone ) ) . '</div>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
            <?php echo ( $address ) ? '<div><span>' . esc_html( csp_get( $p . '_card_address' ) ) . '</span>' . esc_html( $address ) . '</div>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
          </div>
          <?php endif; ?>
        </section>
        <?php endforeach; ?>
      </article>

      <?php if ( $also_l && $also_l['title'] ) : ?>
      <div class="also reveal">
        <a <?php echo csp_link_attrs( $also ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
          <div><?php echo csp_get( $p . '_also_label' ) ? '<small>' . esc_html( csp_get( $p . '_also_label' ) ) . '</small>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?><b><?php echo esc_html( $also_l['title'] ); ?></b><?php echo csp_get( $p . '_also_text' ) ? '<span class="d">' . esc_html( csp_get( $p . '_also_text' ) ) . '</span>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
      </div>
      <?php endif; ?>
    </div>
  </div>

</main>
