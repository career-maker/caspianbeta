<?php
/**
 * Generic page fallback (pages without a dedicated template).
 *
 * @package caspian-sun
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main">
  <section class="notfound">
    <div class="notfound-inner">
      <h1 class="section-heading"><?php the_title(); ?></h1>
      <?php
		while ( have_posts() ) :
			the_post();
			the_content();
		endwhile;
		?>
    </div>
  </section>
</main>
<?php get_footer(); ?>
