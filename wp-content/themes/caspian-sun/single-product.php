<?php
/**
 * Single product.
 *
 * @package caspian-sun
 */

defined( 'ABSPATH' ) || exit;

get_header();

$products_page = csp_page_by_template( 'templates/products.php' );
$contact_page  = csp_page_by_template( 'templates/contact.php' );
$view_more     = csp_opt( 'opt_view_more' );

while ( have_posts() ) :
	the_post();
	$pid     = get_the_ID();
	$name    = get_the_title();
	$cats    = get_the_terms( $pid, 'product_category' );
	$cat     = $cats && ! is_wp_error( $cats ) ? $cats[0]->name : '';
	$gallery = array_values( array_filter( (array) get_field( 'gallery' ) ) );
	$desc    = csp_get( 'description' );
	$origin  = array_values( array_filter( (array) get_field( 'origin' ), function ( $o ) {
		return ! empty( $o['country'] );
	} ) );
	$packing = array_values( array_filter( (array) get_field( 'packing' ), function ( $o ) {
		return ! empty( $o['size'] );
	} ) );
	$enq_base = $contact_page ? get_permalink( $contact_page ) : home_url( '/contact/#contact-form' ); // Falls back when no page uses the Contact template.
	$enq_url  = add_query_arg( 'enquiry_product', rawurlencode( $name ), $enq_base );
	?>
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
  <defs><clipPath id="flagClip"><circle cx="12" cy="12" r="12"/></clipPath></defs>
</svg>

<?php csp_banner( 'pd', false, 'option' ); ?>

	<?php
	csp_breadcrumb(
		array_filter(
			array(
				$products_page ? array( get_the_title( $products_page ), get_permalink( $products_page ) ) : null,
				array( $name, '' ),
			)
		)
	);
	?>

<main class="detail" id="main">

  <div class="gallery reveal">
    <div class="gallery-main">
      <?php echo csp_opt( 'pd_badge' ) ? '<span class="badge">' . esc_html( csp_opt( 'pd_badge' ) ) . '</span>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php echo $gallery ? csp_img( $gallery[0], array( 'id' => 'mainImg', 'alt' => $name, 'loading' => 'eager', 'fetchpriority' => 'high' ) ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
    </div>
    <?php if ( $gallery ) : ?>
    <div class="thumbs" id="thumbs" role="tablist" aria-label="<?php esc_attr_e( 'Product photos', 'caspian-sun' ); ?>">
      <?php foreach ( $gallery as $i => $gid ) : ?>
      <button class="thumb<?php echo 0 === $i ? ' active' : ''; ?>" type="button" role="tab" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>" data-src="<?php echo csp_img_url( $gid ); // phpcs:ignore WordPress.Security.EscapeOutput ?>" data-srcset="<?php echo esc_attr( (string) wp_get_attachment_image_srcset( $gid, 'full' ) ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: photo number */ __( 'Show photo %d', 'caspian-sun' ), $i + 1 ) ); ?>"><?php echo csp_img( $gid, array( 'alt' => $name . ' ' . ( $i + 1 ), 'decoding' => 'async' ), 'medium' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <div class="info">
    <?php echo $cat ? '<span class="tag reveal">' . esc_html( $cat ) . '</span>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
    <h1 class="reveal"><?php echo esc_html( $name ); ?></h1>
    <div class="rule reveal"></div>
    <?php echo $desc ? '<p class="desc reveal">' . csp_br( $desc ) . '</p>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>

    <?php if ( $origin ) : ?>
    <div class="specs reveal">
      <div class="spec-row">
        <?php echo csp_opt( 'pd_origin_label' ) ? '<div class="spec-label">' . esc_html( csp_opt( 'pd_origin_label' ) ) . '</div>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <div class="spec-value">
          <?php foreach ( $origin as $o ) : ?>
          <span class="origin-item"><?php echo csp_flag_svg( $o['country'], ! empty( $o['flag'] ) ? (int) $o['flag'] : 0 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $o['country'] ); ?></span>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <?php if ( $packing ) : ?>
    <div class="sizes-block reveal">
      <div class="sizes-head">
        <?php echo csp_opt( 'pd_packing_label' ) ? '<div class="spec-label">' . esc_html( csp_opt( 'pd_packing_label' ) ) . '</div>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <div class="sizes-hint" id="sizesHint" data-idle="<?php echo esc_attr( csp_opt( 'pd_packing_hint' ) ); ?>" data-selected="<?php echo esc_attr( csp_opt( 'pd_packing_selected' ) ); ?>"><?php echo esc_html( csp_opt( 'pd_packing_hint' ) ); ?></div>
      </div>
      <div class="sizes" id="sizes" role="radiogroup" aria-label="<?php echo esc_attr( csp_opt( 'pd_packing_label' ) ); ?>">
        <?php foreach ( $packing as $p ) : ?>
        <button class="size" type="button" role="radio" aria-checked="false" data-size="<?php echo esc_attr( $p['size'] ); ?>"><span><?php echo esc_html( $p['size'] ); ?></span></button>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php $back = csp_opt( 'pd_back_link' ); ?>
    <?php if ( ( $enq_url && csp_opt( 'pd_enquire_label' ) ) || csp_link( $back ) ) : ?>
    <div class="cta-row reveal">
      <?php if ( $enq_url && csp_opt( 'pd_enquire_label' ) ) : ?>
      <div class="btn-shop"><a id="enquireBtn" href="<?php echo esc_url( $enq_url ); ?>"><?php echo esc_html( csp_opt( 'pd_enquire_label' ) ); ?> <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a></div>
      <?php endif; ?>
      <?php
		$bl = csp_link( $back );
		if ( $bl && $bl['title'] ) {
			echo '<a class="btn-outline" ' . csp_link_attrs( $back ) . '>' . esc_html( $bl['title'] ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		?>
    </div>
    <?php endif; ?>

    <?php $assure = get_field( 'pd_assurances', 'option' ); ?>
    <?php if ( $assure ) : ?>
    <div class="assure reveal">
      <?php foreach ( $assure as $a ) : ?>
      <div class="assure-item"><?php echo csp_icon( $a['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo csp_br( $a['text'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

</main>

	<?php
	/* Related: same category first, topped up from the rest of the catalogue. */
	$ids = array();
	if ( $cats && ! is_wp_error( $cats ) ) {
		$ids = get_posts(
			array(
				'post_type'      => 'product',
				'posts_per_page' => 4,
				'post__not_in'   => array( $pid ),
				'fields'         => 'ids',
				'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
				'tax_query'      => array( array( 'taxonomy' => 'product_category', 'field' => 'term_id', 'terms' => $cats[0]->term_id ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
			)
		);
	}
	if ( count( $ids ) < 4 ) {
		$more = get_posts(
			array(
				'post_type'      => 'product',
				'posts_per_page' => 4 - count( $ids ),
				'post__not_in'   => array_merge( array( $pid ), $ids ),
				'fields'         => 'ids',
				'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
			)
		);
		$ids  = array_merge( $ids, $more );
	}
	$rl = csp_link( csp_opt( 'pd_related_link' ) );
	if ( $ids ) :
		?>
<section class="related">
  <div class="related-head reveal">
    <?php echo csp_opt( 'pd_related_heading' ) ? '<h2 class="related-title">' . esc_html( csp_opt( 'pd_related_heading' ) ) . '</h2>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
    <?php echo ( $rl && $rl['title'] ) ? '<a class="view-all" ' . csp_link_attrs( csp_opt( 'pd_related_link' ) ) . '>' . esc_html( $rl['title'] ) . ' <span class="dash"></span></a>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
  </div>
  <div class="related-grid" id="related">
		<?php foreach ( $ids as $rid ) : ?>
    <a href="<?php echo esc_url( get_permalink( $rid ) ); ?>" class="rcard reveal in-view"><div class="rimg"><?php echo csp_img( csp_get( 'card_image', $rid ), array( 'alt' => get_the_title( $rid ), 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div><div class="rbody"><h3><?php echo esc_html( get_the_title( $rid ) ); ?></h3><?php echo $view_more ? '<span class="rmore">' . esc_html( $view_more ) . ' <span class="dash"></span></span>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?></div></a>
		<?php endforeach; ?>
  </div>
</section>
	<?php endif; ?>
<?php endwhile; ?>

<?php get_footer(); ?>
