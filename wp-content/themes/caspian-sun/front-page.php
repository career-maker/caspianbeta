<?php
/**
 * Home page.
 *
 * @package caspian-sun
 */

defined( 'ABSPATH' ) || exit;

get_header();

$view_more = csp_opt( 'opt_view_more' );
?>
<main id="main">

<?php
/* ------------------------------------------------------------- 1. HERO */
$poster    = csp_get( 'home_hero_poster' );
$poster_sm = csp_get( 'home_hero_poster_mobile' );
$video     = csp_get( 'home_hero_video' );
$video_sm  = csp_get( 'home_hero_video_mobile' );
$h_btn     = csp_get( 'home_hero_button' );
?>
<section class="hero" id="top">
  <?php if ( $poster ) : ?>
  <?php
	$poster_attrs = array( 'class' => 'hero-video hero-poster', 'alt' => '', 'loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'async' );
	if ( $poster_sm ) {
		$poster_attrs['srcset'] = esc_url( wp_get_attachment_url( $poster_sm ) ) . ' 640w, ' . esc_url( wp_get_attachment_url( $poster ) ) . ' 1280w';
		$poster_attrs['sizes']  = '100vw';
	}
	echo csp_img( $poster, $poster_attrs ); // phpcs:ignore WordPress.Security.EscapeOutput
	?>
  <?php endif; ?>
  <?php if ( $video ) : ?>
  <video class="hero-video" muted loop playsinline preload="none" data-src="<?php echo csp_img_url( $video ); // phpcs:ignore WordPress.Security.EscapeOutput ?>" data-src-sm="<?php echo csp_img_url( $video_sm ? $video_sm : $video ); // phpcs:ignore WordPress.Security.EscapeOutput ?>" aria-hidden="true"></video>
  <?php endif; ?>
  <div class="hero-inner">
    <?php if ( csp_get( 'home_hero_heading' ) ) : ?><h1><?php echo csp_br( csp_get( 'home_hero_heading' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h1><?php endif; ?>
    <?php echo csp_p( csp_get( 'home_hero_text' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    <?php echo csp_btn( $h_btn, 'btn-shop', csp_link( $h_btn ) ? csp_link( $h_btn )['title'] : '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
  </div>
</section>

<?php
/* ------------------------------------------------------------ 2. ABOUT */
$counters = get_field( 'home_counters' );
$about_img = csp_get( 'home_about_image' );
?>
<section class="sec about">
  <div class="wrap about-grid">
    <div class="about-text reveal">
      <?php echo csp_eyebrow( csp_get( 'home_about_eyebrow' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php echo csp_heading( csp_get( 'home_about_heading' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php echo csp_paras( get_field( 'home_about_paras' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php echo csp_btn( csp_get( 'home_about_button' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    </div>
    <?php if ( $about_img ) : ?><div class="about-photo reveal"><?php echo csp_img( $about_img, array( 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div><?php endif; ?>
    <?php if ( $counters ) : ?>
    <div class="counters reveal">
      <?php foreach ( $counters as $c ) : ?>
      <div class="counter"><?php echo csp_img( $c['icon'], array( 'alt' => '', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?><div><strong data-count="<?php echo esc_attr( (int) $c['number'] ); ?>" data-suffix="<?php echo esc_attr( $c['suffix'] ); ?>"><?php echo esc_html( number_format_i18n( (int) $c['number'] ) . $c['suffix'] ); ?></strong><span><?php echo esc_html( $c['label'] ); ?></span></div></div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php
/* -------------------------------------------------------- 3. CATEGORIES */
$cats = get_field( 'home_cats' );
if ( $cats || csp_get( 'home_cats_heading' ) ) :
	?>
<section class="sec cats"<?php echo csp_style_attr( array( csp_bg_var( 'cats-bg', csp_get( 'home_cats_bg' ) ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
  <div class="wrap">
    <div class="cats-head reveal">
      <div>
        <?php echo csp_eyebrow( csp_get( 'home_cats_eyebrow' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <?php echo csp_heading( csp_get( 'home_cats_heading' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      </div>
      <?php echo csp_btn( csp_get( 'home_cats_button' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    </div>
    <?php if ( $cats ) : ?>
    <div class="cat-grid">
      <?php foreach ( $cats as $c ) : ?>
      <a class="cat-card reveal"<?php echo ( csp_link( $c['link'] ) ? ' href="' . csp_link( $c['link'] )['url'] . '"' : '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo csp_img( $c['image'], array( 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?><div class="cat-label"><?php echo $c['title'] ? '<b>' . esc_html( $c['title'] ) . '</b>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo $c['subtitle'] ? '<span>' . esc_html( $c['subtitle'] ) . '</span>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?></div></a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php
/* ------------------------------------------------------------- 4. WHY */
$why       = get_field( 'home_why_items' );
$why_video = csp_get( 'home_why_video' );
$why_img   = csp_get( 'home_why_video_image' );
?>
<section class="sec why" style="padding-bottom:clamp(3rem,6vw,6.25rem)">
  <?php if ( csp_get( 'home_why_heading' ) || csp_get( 'home_why_text' ) ) : ?>
  <div class="wrap why-head reveal">
    <?php echo csp_heading( csp_get( 'home_why_heading' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    <?php echo csp_p( csp_get( 'home_why_text' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
  </div>
  <?php endif; ?>
  <?php if ( $why ) : ?>
  <div class="why-strip reveal">
    <div class="why-grid">
      <?php foreach ( $why as $w ) : ?>
      <div class="why-item"><?php echo csp_img( $w['icon'], array( 'alt' => '', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo $w['title'] ? '<h3>' . esc_html( $w['title'] ) . '</h3>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo csp_p( $w['text'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
  <?php if ( $why_img ) : ?>
  <div class="wrap">
    <div class="why-video reveal"<?php echo $why_video ? ' data-video="' . csp_img_url( $why_video ) . '"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
      <?php echo csp_img( $why_img, array( 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php if ( $why_video ) : ?><button class="play-btn" type="button" aria-label="<?php esc_attr_e( 'Play video', 'caspian-sun' ); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3.5v17l14-8.5z"/></svg></button><?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
</section>

<?php
/* --------------------------------------------------------- 5. CLIENTS */
$logos = get_field( 'home_client_logos' );
if ( $logos || csp_get( 'home_clients_heading' ) ) :
	?>
<section class="sec clients">
  <div class="wrap clients-grid">
    <div class="reveal">
      <?php echo csp_heading( csp_get( 'home_clients_heading' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php echo csp_p( csp_get( 'home_clients_text' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php echo csp_btn( csp_get( 'home_clients_button' ), 'btn-shop light' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    </div>
    <?php if ( $logos ) : ?>
    <div class="logos reveal">
      <?php
		$n = 0;
		foreach ( $logos as $l ) {
			$img = csp_img( $l['logo'], array( 'decoding' => 'async' ) );
			if ( ! $img ) {
				continue;
			}
			++$n;
			echo '<div class="logo-cell' . ( ! empty( $l['invert'] ) ? ' logo-cell--light' : '' ) . '">' . $img . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		// Empty filler cell(s) complete the 5-column grid.
		for ( $i = 0; $n && $i < ( 5 - $n % 5 ) % 5; $i++ ) {
			echo '<div class="logo-cell" aria-hidden="true"></div>';
		}
		?>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php
/* ------------------------------------------------------- 6. PRODUCTS */
$featured = get_field( 'home_products' );
if ( $featured || csp_get( 'home_products_heading' ) ) :
	?>
<section class="sec">
  <div class="wrap">
    <div class="prod-head reveal">
      <?php echo csp_eyebrow( csp_get( 'home_products_eyebrow' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php echo csp_heading( csp_get( 'home_products_heading' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php echo csp_p( csp_get( 'home_products_text' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    </div>
    <?php if ( $featured ) : ?>
    <div class="prod-grid">
      <?php
		foreach ( $featured as $pid ) :
			$name = get_the_title( $pid );
			$url  = get_permalink( $pid );
			$sum  = csp_get( 'card_summary', $pid );
			$vm   = csp_view_more( $url, $view_more, $view_more . ': ' . $name );
			?>
      <div class="pcard reveal"><div class="pimg"><?php echo csp_img( csp_get( 'card_image', $pid ), array( 'alt' => $name, 'sizes' => '(max-width: 600px) 100vw, 340px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?><div class="poverlay"><h3><?php echo esc_html( $name ); ?></h3><?php echo csp_p( $sum ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo $vm; // phpcs:ignore WordPress.Security.EscapeOutput ?></div></div><div class="pinfo"><b><?php echo esc_html( $name ); ?></b><?php echo $vm; // phpcs:ignore WordPress.Security.EscapeOutput ?></div></div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php $pb = csp_btn( csp_get( 'home_products_button' ) ); ?>
    <?php if ( $pb ) : ?><div class="prod-cta reveal"><?php echo $pb; // phpcs:ignore WordPress.Security.EscapeOutput ?></div><?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php
/* -------------------------------------------------- 7. TESTIMONIALS */
$testis   = get_field( 'home_testimonials' );
$t_image  = csp_get( 'home_testi_image' );
$q_icon   = '<svg class="q" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 21h7l3-8V3H3v10h5zM14 21h7l3-8V3H14v10h5z" transform="scale(.96)"/></svg>';
if ( $testis || $t_image || csp_get( 'home_testi_heading' ) ) :
	?>
<section class="sec testi">
  <div class="wrap testi-grid">
    <?php if ( $t_image ) : ?>
    <div class="testi-photo reveal">
      <?php echo csp_img( $t_image, array( 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php if ( csp_get( 'home_testi_count' ) || csp_get( 'home_testi_avatars' ) ) : ?>
      <div class="served"><?php echo csp_img( csp_get( 'home_testi_avatars' ), array( 'alt' => '', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?><div><?php echo csp_get( 'home_testi_count' ) ? '<strong>' . esc_html( csp_get( 'home_testi_count' ) ) . '</strong>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo csp_get( 'home_testi_count_label' ) ? '<span>' . esc_html( csp_get( 'home_testi_count_label' ) ) . '</span>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?></div></div>
      <?php endif; ?>
    </div>
    <?php endif; ?>
    <div class="testi-body">
      <?php echo csp_eyebrow( csp_get( 'home_testi_eyebrow' ), 'reveal' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php echo csp_heading( csp_get( 'home_testi_heading' ), 2, '', 'reveal' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php if ( $testis ) : ?>
      <div class="slider reveal">
        <div class="track" id="track">
          <?php
			foreach ( $testis as $t ) :
				$is_video = 'video' === $t['type'] && $t['video'];
				$person   = '<div class="tperson">' . csp_img( $t['avatar'], array( 'alt' => $t['name'], 'decoding' => 'async' ) ) . '<div>' . ( $t['name'] ? '<b>' . esc_html( $t['name'] ) . '</b>' : '' ) . ( $t['role'] ? '<span>' . esc_html( $t['role'] ) . '</span>' : '' ) . '</div></div>';
				if ( $is_video ) :
					?>
          <article class="tcard video" data-video="<?php echo csp_img_url( $t['video'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>"<?php echo csp_style_attr( array( csp_bg_var( 'testi-video-bg', $t['video_cover'] ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
            <?php echo $t['name'] ? '<h3 class="sr-only">' . esc_html( sprintf( /* translators: %s: person */ __( 'Testimonial from %s', 'caspian-sun' ), $t['name'] ) ) . '</h3>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
            <button class="play-btn" type="button" aria-label="<?php esc_attr_e( 'Play testimonial video', 'caspian-sun' ); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3.5v17l14-8.5z"/></svg></button>
            <?php echo $person; // phpcs:ignore WordPress.Security.EscapeOutput ?>
          </article>
				<?php else : ?>
          <article class="tcard">
            <?php echo $t['name'] ? '<h3 class="sr-only">' . esc_html( sprintf( __( 'Testimonial from %s', 'caspian-sun' ), $t['name'] ) ) . '</h3>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
            <?php echo $q_icon; // phpcs:ignore WordPress.Security.EscapeOutput ?>
            <?php echo csp_p( $t['quote'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
            <?php echo $person; // phpcs:ignore WordPress.Security.EscapeOutput ?>
          </article>
				<?php endif; ?>
          <?php endforeach; ?>
        </div>
        <div class="slider-ui">
          <div class="dots" id="dots"><?php foreach ( $testis as $i => $t ) { echo '<i' . ( 0 === $i ? ' class="on"' : '' ) . '></i>'; } // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
          <div class="arrows">
            <button type="button" id="prev" aria-label="<?php esc_attr_e( 'Previous', 'caspian-sun' ); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5M11 6l-6 6 6 6"/></svg></button>
            <button type="button" id="next" aria-label="<?php esc_attr_e( 'Next', 'caspian-sun' ); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></button>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php
/* ----------------------------------------------------- 8. ARTICLES */
$arts = array_values( array_filter( (array) get_field( 'home_articles' ), function ( $a ) {
	return ! empty( $a['post'] ) && 'publish' === get_post_status( $a['post'] );
} ) );

if ( 4 === count( $arts ) ) {
	$arts = array_slice( $arts, 0, 3 ); // The grid layout is designed for 1, 2, 3 or 5 articles.
}

/** Render one article card. $mode: 'big' | 'side'. */
function csp_home_article( $a, $mode, $view_more ) {
	$pid   = (int) $a['post'];
	$title = get_the_title( $pid );
	$img   = csp_img( $a['image'] ? $a['image'] : csp_get( 'article_card_image', $pid ), array( 'alt' => $title, 'decoding' => 'async' ) );
	$sum   = csp_article_summary( $pid );
	$cls   = 'art ' . ( 'big' === $mode ? 'big' : '' ) . ' reveal' . ( 'side' === $mode && ! empty( $a['open'] ) ? ' show-img' : '' );
	echo '<a class="' . esc_attr( trim( $cls ) ) . '" href="' . esc_url( get_permalink( $pid ) ) . '">'; // phpcs:ignore WordPress.Security.EscapeOutput
	if ( 'big' === $mode ) {
		echo $img ? '<div class="aimg">' . $img . '</div>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput
	} else {
		echo $img ? '<div class="awrap"><div class="aimg"><div class="abox">' . $img . '</div></div></div>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '<h3>' . esc_html( $title ) . '</h3>' . csp_p( $sum ) . csp_view_more( '', $view_more, '', 'span' ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput
}

if ( $arts || csp_get( 'home_articles_heading' ) ) :
	?>
<section class="sec">
  <div class="wrap">
    <div class="art-head reveal">
      <?php echo csp_eyebrow( csp_get( 'home_articles_eyebrow' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php echo csp_heading( csp_get( 'home_articles_heading' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    </div>
    <?php if ( $arts ) : ?>
    <div class="art-grid">
      <?php csp_home_article( $arts[0], 'big', $view_more ); ?>
      <?php if ( count( $arts ) > 1 ) : ?>
      <div class="acol"><?php foreach ( array_slice( $arts, 1, 2 ) as $a ) { csp_home_article( $a, 'side', $view_more ); } ?></div>
      <?php endif; ?>
      <?php if ( count( $arts ) > 3 ) : ?>
      <div class="acol"><?php foreach ( array_slice( $arts, 3, 2 ) as $a ) { csp_home_article( $a, 'side', $view_more ); } ?></div>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php
/* ------------------------------------------------------------ 9. CTA */
if ( csp_get( 'home_cta_heading' ) || csp_get( 'home_cta_text' ) ) :
	?>
<section class="cta"<?php echo csp_style_attr( array( csp_bg_var( 'cta-bg', csp_get( 'home_cta_image' ) ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
  <div class="cta-bg" aria-hidden="true"></div>
  <div class="wrap reveal">
    <?php echo csp_eyebrow( csp_get( 'home_cta_eyebrow' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    <?php echo csp_heading( csp_get( 'home_cta_heading' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    <?php echo csp_p( csp_get( 'home_cta_text' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    <?php echo csp_btn( csp_get( 'home_cta_button' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
  </div>
</section>
<?php endif; ?>

</main>

<div class="lightbox" id="lightbox" aria-hidden="true">
  <button class="lb-close" id="lbClose" aria-label="<?php esc_attr_e( 'Close video', 'caspian-sun' ); ?>">&times;</button>
  <video id="lbVideo" controls playsinline></video>
</div>

<?php get_footer(); ?>
