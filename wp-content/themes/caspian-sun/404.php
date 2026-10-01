<?php
/**
 * 404 page.
 *
 * @package caspian-sun
 */

defined( 'ABSPATH' ) || exit;

get_header();
$btn = csp_opt( 'e404_button' );
?>
<main id="main">
  <?php csp_banner( 'e404', true, 'option' ); ?>
  <section class="notfound">
    <div class="notfound-inner">
      <div class="notfound-code" aria-hidden="true">404</div>
      <?php echo csp_heading( csp_opt( 'e404_heading' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php echo csp_p( csp_opt( 'e404_text' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php echo csp_btn( $btn ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    </div>
  </section>
</main>
<?php get_footer(); ?>
