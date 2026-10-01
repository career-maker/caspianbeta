<?php
/**
 * Single article (Insights detail).
 *
 * @package caspian-sun
 */

defined( 'ABSPATH' ) || exit;

get_header();

$page_id   = (int) get_option( 'page_for_posts' );
$view_more = csp_opt( 'opt_view_more' );

while ( have_posts() ) :
	the_post();
	$title  = get_the_title();
	$refs   = array_values( array_filter( (array) get_field( 'article_refs' ), function ( $r ) {
		return ! empty( $r['text'] ) || ! empty( $r['url'] );
	} ) );
	$author = csp_get( 'article_author' );
	$img    = csp_get( 'article_image' );
	$body   = csp_get( 'article_body' );
	$url    = get_permalink();

	$meta = array();
	if ( $author ) {
		$meta[] = esc_html( csp_opt( 'bd_by_label' ) . ' ' . $author );
	}
	$meta[] = esc_html( get_the_date( 'M j, Y' ) );
	if ( $refs && $refs[0]['url'] ) {
		$meta[] = '<span class="meta-ref">' . esc_html( csp_opt( 'bd_reference_label' ) ) . ' <a href="' . esc_url( $refs[0]['url'] ) . '" target="_blank" rel="noopener">' . esc_html( $refs[0]['text'] ? $refs[0]['text'] : $refs[0]['url'] ) . '</a></span>';
	}

	$share = array(
		'x'        => 'https://twitter.com/intent/tweet?url=' . rawurlencode( $url ) . '&text=' . rawurlencode( $title ),
		'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $url ),
		'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( $url ),
		'whatsapp' => 'https://wa.me/?text=' . rawurlencode( $title . ' ' . $url ),
	);
	// Instagram has no share URL: link to the brand profile configured in Theme Settings.
	foreach ( array_filter( (array) get_field( 'opt_social', 'option' ) ) as $s ) {
		if ( is_array( $s ) && isset( $s['network'] ) && 'instagram' === $s['network'] && ! empty( $s['url'] ) ) {
			$share = array_slice( $share, 0, 3, true ) + array( 'instagram' => $s['url'] ) + array_slice( $share, 3, null, true );
		}
	}
	$share_labels = csp_icon_choices();

	?>
<main id="main">
	<?php csp_banner( 'bd', false, 'option' ); ?>
	<?php
	csp_breadcrumb(
		array_filter(
			array(
				$page_id ? array( get_the_title( $page_id ), get_permalink( $page_id ) ) : array( __( 'Insights', 'caspian-sun' ), home_url( '/blog/' ) ),
				array( $title, '' ),
			)
		)
	);
	?>
<div class="article-wrap">
  <article>
    <div class="article-meta reveal"><?php echo implode( ' &nbsp;|&nbsp; ', $meta ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
    <h1 class="article-title reveal"><?php echo esc_html( $title ); ?></h1>
    <div class="share-row reveal">
      <?php echo csp_opt( 'bd_share_label' ) ? '<span>' . esc_html( csp_opt( 'bd_share_label' ) ) . '</span>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <div class="share-icons">
        <?php foreach ( $share as $net => $link ) : ?>
        <a href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( $share_labels[ $net ] ); ?>"><?php echo csp_icon( $net ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php echo csp_img( $img, array( 'class' => 'article-featured reveal', 'alt' => $title, 'loading' => 'eager', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    <?php if ( $body ) : ?><div class="article-body reveal"><?php echo wp_kses_post( $body ); ?></div><?php endif; ?>
    <?php if ( $refs ) : ?>
    <div class="article-refs reveal">
      <?php echo csp_opt( 'bd_refs_heading' ) ? '<h2>' . esc_html( csp_opt( 'bd_refs_heading' ) ) . '</h2>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <ol>
        <?php foreach ( $refs as $r ) : ?>
        <li><?php echo esc_html( $r['text'] ); ?><?php echo $r['url'] ? ( $r['text'] ? '. ' : '' ) . '<a href="' . esc_url( $r['url'] ) . '" target="_blank" rel="noopener">' . esc_html( $r['url'] ) . '</a>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
        <?php endforeach; ?>
      </ol>
    </div>
    <?php endif; ?>
  </article>

  <?php
	$related = new WP_Query(
		array(
			'post_type'           => 'post',
			'posts_per_page'      => 3,
			'post__not_in'        => array( get_the_ID() ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
	if ( $related->have_posts() ) :
		?>
  <aside class="sidebar">
    <?php echo csp_heading( csp_opt( 'bd_related_heading' ), 2, '', 'reveal' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    <div>
      <?php
		while ( $related->have_posts() ) :
			$related->the_post();
			$rt   = get_the_title();
			$rimg = csp_img( csp_get( 'article_card_image' ), array( 'alt' => $rt ) );
			?>
      <a href="<?php the_permalink(); ?>" class="related-card reveal in-view">
        <?php echo $rimg ? '<div class="related-card-img">' . $rimg . '</div>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <h3><?php echo esc_html( $rt ); ?></h3>
        <?php echo csp_p( csp_article_summary( get_the_ID() ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <?php echo csp_view_more( '', $view_more, '', 'span', true ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      </a>
		<?php endwhile; ?>
    </div>
  </aside>
		<?php
		wp_reset_postdata();
	endif;
	?>
</div>
</main>
<?php endwhile; ?>

<?php get_footer(); ?>
