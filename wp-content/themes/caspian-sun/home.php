<?php
/**
 * Insights (blog) listing — the "Posts page".
 *
 * @package caspian-sun
 */

defined( 'ABSPATH' ) || exit;

get_header();

$page_id   = (int) get_option( 'page_for_posts' );
$poster    = csp_get( 'blog_hero_poster', $page_id );
$poster_sm = csp_get( 'blog_hero_poster_mobile', $page_id );
$video     = csp_get( 'blog_hero_video', $page_id );
$video_sm  = csp_get( 'blog_hero_video_mobile', $page_id );
$view_more = csp_opt( 'opt_view_more' );
$clients   = get_field( 'blog_trusted_clients', $page_id );
?>
<main id="main">

<section class="hero">
  <?php
	if ( $poster ) {
		$attrs = array( 'class' => 'hero-video hero-poster', 'alt' => '', 'loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'async' );
		if ( $poster_sm ) {
			$attrs['srcset'] = esc_url( wp_get_attachment_url( $poster_sm ) ) . ' 640w, ' . esc_url( wp_get_attachment_url( $poster ) ) . ' 1280w';
			$attrs['sizes']  = '100vw';
		}
		echo csp_img( $poster, $attrs ); // phpcs:ignore WordPress.Security.EscapeOutput
	}
	?>
  <?php if ( $video ) : ?>
  <video class="hero-video" muted loop playsinline preload="none" data-src="<?php echo csp_img_url( $video ); // phpcs:ignore WordPress.Security.EscapeOutput ?>" data-src-sm="<?php echo csp_img_url( $video_sm ? $video_sm : $video ); // phpcs:ignore WordPress.Security.EscapeOutput ?>" aria-hidden="true"></video>
  <?php endif; ?>
  <div class="hero-inner">
    <?php echo csp_get( 'blog_banner_heading', $page_id ) ? '<h1>' . csp_br( csp_get( 'blog_banner_heading', $page_id ) ) . '</h1>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
    <?php echo csp_p( csp_get( 'blog_banner_text', $page_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
  </div>
</section>

<?php csp_breadcrumb( array( array( $page_id ? get_the_title( $page_id ) : __( 'Insights', 'caspian-sun' ), '' ) ) ); ?>

<section class="blog-section">
  <?php echo csp_heading( csp_get( 'blog_grid_heading', $page_id ), 2, '', 'reveal' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
  <?php if ( have_posts() ) : ?>
  <div class="blog-grid">
    <?php
	while ( have_posts() ) :
		the_post();
		$title = get_the_title();
		?>
    <a href="<?php the_permalink(); ?>" class="blog-card reveal">
      <?php $img = csp_img( csp_get( 'article_card_image' ), array( 'alt' => $title ) ); ?>
      <?php echo $img ? '<div class="blog-card-img">' . $img . '</div>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <h3><?php echo esc_html( $title ); ?></h3>
      <?php echo csp_p( csp_article_summary( get_the_ID() ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php echo csp_view_more( '', $view_more, '', 'span', true ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    </a>
	<?php endwhile; ?>
  </div>
	<?php
	the_posts_pagination(
		array(
			'mid_size'  => 1,
			'prev_text' => '&larr;',
			'next_text' => '&rarr;',
		)
	);
	?>
  <?php endif; ?>
</section>

<?php if ( $clients || csp_get( 'blog_trusted_heading', $page_id ) ) : ?>
<section class="trusted">
  <div class="trusted-wrap">
    <div class="trusted-text reveal">
      <?php echo csp_heading( csp_get( 'blog_trusted_heading', $page_id ), 2, 'section-heading--on-dark' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php echo csp_p( csp_get( 'blog_trusted_text', $page_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    </div>
    <?php if ( $clients ) : ?>
    <div class="trusted-logos reveal">
      <?php foreach ( $clients as $c ) : ?>
        <?php if ( ! empty( $c['name'] ) ) : ?><div class="trusted-logo" data-client="<?php echo esc_attr( $c['name'] ); ?>"><span><?php echo esc_html( $c['name'] ); ?></span></div><?php endif; ?>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

</main>
<?php get_footer(); ?>
