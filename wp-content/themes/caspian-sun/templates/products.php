<?php
/**
 * Template Name: Products
 *
 * @package caspian-sun
 */

defined( 'ABSPATH' ) || exit;

get_header();

$terms = get_terms(
	array(
		'taxonomy'   => 'product_category',
		'hide_empty' => true,
		'meta_key'   => 'category_order', // phpcs:ignore WordPress.DB.SlowDBQuery
		'orderby'    => 'meta_value_num',
		'order'      => 'ASC',
	)
);
if ( is_wp_error( $terms ) ) {
	$terms = array();
}

$view_more = csp_opt( 'opt_view_more' );
$other_tab = csp_get( 'products_other_tab' );
$other_btn = csp_get( 'products_other_button' );
$show_other = $other_tab && ( csp_get( 'products_other_heading' ) || csp_get( 'products_other_text' ) );
$arrow_svg = '<svg viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true"><path d="M2 12h14"/><circle cx="18" cy="12" r="2"/></svg>';
?>
<main id="main">

<?php csp_banner( 'products' ); ?>
<?php csp_breadcrumb( array( array( get_the_title(), '' ) ) ); ?>

<div class="shop-wrap">
  <div class="search-row reveal">
    <?php echo csp_heading( csp_get( 'products_search_heading' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    <div class="search-controls">
      <div class="search-box">
        <svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
        <input type="search" placeholder="<?php echo esc_attr( csp_get( 'products_search_placeholder' ) ); ?>" aria-label="<?php echo esc_attr( csp_get( 'products_search_placeholder' ) ? csp_get( 'products_search_placeholder' ) : __( 'Search products', 'caspian-sun' ) ); ?>">
      </div>
    </div>
  </div>

  <div class="cat-tabs reveal" role="group" aria-label="<?php echo esc_attr( csp_get( 'products_nav_label' ) ); ?>">
    <?php if ( csp_get( 'products_all_label' ) ) : ?><button type="button" class="cat-tab active" data-filter="all" aria-pressed="true"><?php echo esc_html( csp_get( 'products_all_label' ) ); ?></button><?php endif; ?>
    <?php foreach ( $terms as $t ) : ?>
    <button type="button" class="cat-tab" data-filter="<?php echo esc_attr( $t->slug ); ?>" aria-pressed="false"><?php echo esc_html( $t->name ); ?></button>
    <?php endforeach; ?>
    <?php if ( $show_other ) : ?><button type="button" class="cat-tab" data-filter="other" aria-pressed="false"><?php echo esc_html( $other_tab ); ?></button><?php endif; ?>
  </div>

  <div class="shop-grid">
    <?php
	foreach ( $terms as $t ) :
		$q = new WP_Query(
			array(
				'post_type'      => 'product',
				'posts_per_page' => -1,
				'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
				'no_found_rows'  => true,
				'tax_query'      => array( array( 'taxonomy' => 'product_category', 'field' => 'term_id', 'terms' => $t->term_id ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
			)
		);
		if ( ! $q->have_posts() ) {
			continue;
		}
		?>
    <section class="cat-block" id="<?php echo esc_attr( $t->slug ); ?>" data-cat="<?php echo esc_attr( $t->name ); ?>">
      <h2 class="section-heading cat-heading"><?php echo esc_html( $t->name ); ?></h2>
      <div class="product-grid">
      <?php
		while ( $q->have_posts() ) :
			$q->the_post();
			$name = get_the_title();
			$url  = get_permalink();
			$sum  = csp_get( 'card_summary' );
			$link = $view_more ? '<a href="' . esc_url( $url ) . '" class="view-link" aria-label="' . esc_attr( $view_more . ': ' . $name ) . '">' . esc_html( $view_more ) . ' ' . $arrow_svg . '</a>' : '';
			?>
      <div class="product-card reveal" data-name="<?php echo esc_attr( mb_strtolower( $name ) ); ?>">
        <div class="product-icon">
          <?php echo csp_img( csp_get( 'card_image' ), array( 'alt' => $name, 'sizes' => '(max-width: 600px) 100vw, 340px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
          <div class="product-overlay">
            <h3><?php echo esc_html( $name ); ?></h3>
            <?php echo csp_p( $sum ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
            <?php echo $link; // phpcs:ignore WordPress.Security.EscapeOutput ?>
          </div>
        </div>
        <div class="product-info">
          <div class="product-name"><?php echo esc_html( $name ); ?></div>
          <?php echo $link; // phpcs:ignore WordPress.Security.EscapeOutput ?>
        </div>
      </div>
		<?php endwhile; ?>
      </div>
    </section>
		<?php
		wp_reset_postdata();
	endforeach;
	?>

    <?php if ( $show_other ) : ?>
    <section class="cat-block other-block" id="other" data-cat="Other">
      <?php echo csp_heading( csp_get( 'products_other_heading' ), 2, '', 'cat-heading' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php echo csp_p( csp_get( 'products_other_text' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php echo csp_btn( $other_btn ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    </section>
    <?php endif; ?>
    <p class="no-results"><?php echo esc_html( csp_get( 'products_no_results' ) ); ?></p>
  </div>
</div>

</main>
<?php get_footer(); ?>
