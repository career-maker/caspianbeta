<?php
/**
 * Fallback template (search results, archives). The site's real content is
 * rendered by front-page.php, home.php, single*.php and templates/*.php.
 *
 * @package caspian-sun
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main">
  <section class="notfound">
    <div class="notfound-inner">
      <h1 class="section-heading"><?php echo esc_html( is_search() ? __( 'Search results', 'caspian-sun' ) : get_the_archive_title() ); ?></h1>
      <?php if ( have_posts() ) : ?>
        <?php
		while ( have_posts() ) :
			the_post();
			?>
        <p><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></p>
		<?php endwhile; ?>
      <?php else : ?>
        <p><?php esc_html_e( 'Nothing found.', 'caspian-sun' ); ?></p>
      <?php endif; ?>
      <?php echo csp_btn( array( 'url' => '/', 'title' => __( 'Back to home', 'caspian-sun' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    </div>
  </section>
</main>
<?php get_footer(); ?>
