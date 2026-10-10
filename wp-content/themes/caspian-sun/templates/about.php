<?php
/**
 * Template Name: About
 *
 * @package caspian-sun
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main">

<?php csp_banner( 'about' ); ?>
<?php csp_breadcrumb( array( array( get_the_title(), '' ) ) ); ?>

<?php
/* Overview */
$ov_back  = csp_get( 'about_ov_image_back' );
$ov_front = csp_get( 'about_ov_image_front' );
?>
<section>
  <div class="wrap">
    <div class="who-grid">
      <div class="who-text">
        <?php echo csp_eyebrow( csp_get( 'about_ov_eyebrow' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <?php echo csp_heading( csp_get( 'about_ov_heading' ), 2, 'section-heading--upper' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <div class="who-paras"><?php echo csp_bold_terms( csp_paras( get_field( 'about_ov_paras' ) ), array( 'CASPIAN & SUN FOOD TRADING', 'Türkiye', 'United Arab Emirates', '1981' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
      </div>
      <?php if ( $ov_back || $ov_front ) : ?>
      <div class="who-images">
        <?php echo csp_img( $ov_back, array( 'class' => 'who-img-back', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <?php echo csp_img( $ov_front, array( 'class' => 'who-img-front', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<script>
(function(){
  var t=document.querySelector('.who-text'),p=t&&t.querySelector('.who-paras');
  if(!p)return;
  var more=<?php echo wp_json_encode( __( 'Read more', 'caspian-sun' ) ); ?>,less=<?php echo wp_json_encode( __( 'Read less', 'caspian-sun' ) ); ?>,b;
  function check(){
    if(t.classList.contains('is-open'))return;
    var over=p.scrollHeight>p.clientHeight+2;
    t.classList.toggle('is-clipped',over);
    if(over&&!b){b=document.createElement('button');b.type='button';b.className='who-more';b.textContent=more;b.setAttribute('aria-expanded','false');
      b.onclick=function(){var o=t.classList.toggle('is-open');b.textContent=o?less:more;b.setAttribute('aria-expanded',o);if(!o)check();};
      t.appendChild(b);}
    if(b)b.hidden=!over;
  }
  check();addEventListener('resize',check);addEventListener('load',check);
})();
</script>

<?php
/* Services */
$services = get_field( 'about_services' );
if ( $services || csp_get( 'about_svc_heading' ) ) :
	?>
<section class="alt-bg">
  <div class="wrap">
    <div class="sec-head">
      <?php echo csp_eyebrow( csp_get( 'about_svc_eyebrow' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php echo csp_heading( csp_get( 'about_svc_heading' ), 2, 'section-heading--upper' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    </div>
    <?php if ( $services ) : ?>
    <div class="svc-grid">
      <?php foreach ( $services as $s ) : ?>
      <div class="svc-card reveal"><span class="svc-fill" aria-hidden="true"></span><div class="svc-icon"><?php echo csp_icon( $s['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div><?php echo $s['title'] ? '<h3>' . esc_html( $s['title'] ) . '</h3>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo csp_p( $s['text'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php
/* Sourcing expertise */
$str_list = get_field( 'about_str_list' );
$str_img  = csp_get( 'about_str_image' );
?>
<section class="why-section">
  <div class="why-grid">
    <?php if ( $str_img ) : ?><div class="why-img-wrap"><?php echo csp_img( $str_img, array( 'class' => 'why-img', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div><?php endif; ?>
    <div class="why-content">
      <?php echo csp_eyebrow( csp_get( 'about_str_eyebrow' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php echo csp_heading( csp_get( 'about_str_heading' ), 2, 'section-heading--upper' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php echo csp_p( csp_get( 'about_str_intro' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php echo csp_p( csp_get( 'about_str_lead' ), 'list-lead' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php if ( $str_list ) : ?>
      <ul class="check-list">
        <?php foreach ( $str_list as $li ) : ?>
          <?php if ( ! empty( $li['text'] ) ) : ?><li><?php echo csp_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $li['text'] ); ?></li><?php endif; ?>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
      <?php echo csp_p( csp_get( 'about_str_closing' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    </div>
  </div>
</section>

<?php
/* International network */
$net = get_field( 'about_net_items' );
if ( $net || csp_get( 'about_net_heading' ) ) :
	?>
<section class="cream">
  <div class="wrap">
    <div class="process-head">
      <?php echo csp_eyebrow( csp_get( 'about_net_eyebrow' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php echo csp_heading( csp_get( 'about_net_heading' ), 2, 'section-heading--upper' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php echo csp_p( csp_get( 'about_net_intro' ), 'process-intro' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    </div>
    <?php if ( $net ) : ?>
    <div class="process-grid">
      <?php foreach ( $net as $n ) : ?>
      <div class="process-item">
        <div class="process-circle"><?php echo csp_icon( 'pin-large' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
        <div><?php echo $n['country'] ? '<h3>' . esc_html( $n['country'] ) . '</h3>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?><p><?php echo $n['company'] ? '<strong>' . esc_html( $n['company'] ) . '</strong>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo ( $n['company'] && $n['address'] ) ? '<br>' : ''; ?><?php echo csp_br( $n['address'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php
/* Vision */
$vis_img = csp_get( 'about_vis_image' );
if ( csp_get( 'about_vis_heading' ) || csp_get( 'about_vis_text' ) ) :
	?>
<section class="best-section">
  <?php echo csp_img( $vis_img, array( 'class' => 'best-bg', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
  <div class="best-card">
    <?php echo csp_eyebrow( csp_get( 'about_vis_eyebrow' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    <?php echo csp_heading( csp_get( 'about_vis_heading' ), 2, 'section-heading--upper' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    <?php echo csp_p( csp_get( 'about_vis_text' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
  </div>
</section>
<?php endif; ?>

<?php
/* Mission */
$mis_img = csp_get( 'about_mis_image' );
?>
<section>
  <div class="wrap">
    <div class="sustain-grid">
      <?php echo csp_img( $mis_img, array( 'class' => 'sustain-img', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <div class="sustain-content">
        <?php echo csp_eyebrow( csp_get( 'about_mis_eyebrow' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <?php echo csp_heading( csp_get( 'about_mis_heading' ), 2, 'section-heading--upper' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <?php echo csp_paras( get_field( 'about_mis_paras' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      </div>
    </div>
  </div>
</section>

<?php if ( csp_get( 'about_goal_heading' ) || get_field( 'about_goal_paras' ) ) : ?>
<section>
  <div class="wrap">
    <div class="goal-text goal-solo">
        <?php echo csp_eyebrow( csp_get( 'about_goal_eyebrow' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <?php echo csp_heading( csp_get( 'about_goal_heading' ), 2, 'section-heading--upper' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <?php echo csp_paras( get_field( 'about_goal_paras' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php
/* How ordering works */
$steps = get_field( 'about_ord_steps' );
$lt    = get_field( 'about_lt_items' );
if ( $steps || csp_get( 'about_ord_heading' ) ) :
	?>
<section class="alt-bg">
  <div class="wrap">
    <div class="sec-head">
      <?php echo csp_eyebrow( csp_get( 'about_ord_eyebrow' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php echo csp_heading( csp_get( 'about_ord_heading' ), 2, 'section-heading--upper' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php echo csp_p( csp_get( 'about_ord_intro' ), 'sec-intro' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    </div>
    <?php if ( $steps ) : ?>
    <div class="steps">
      <?php
		$default_icons = array( 'mail', 'box', 'search', 'bolt', 'check', 'handling', 'global-std', 'truck', 'clock' );
		$i             = 0;
		foreach ( $steps as $st ) {
			if ( empty( $st['text'] ) ) {
				continue;
			}
			++$i;
			$icon = ! empty( $st['icon'] ) ? $st['icon'] : ( isset( $default_icons[ $i - 1 ] ) ? $default_icons[ $i - 1 ] : 'check' );
			echo '<div class="step reveal"><span class="step-icon" aria-hidden="true">' . csp_icon( $icon ) . '</span><div class="step-num" aria-hidden="true">' . esc_html( sprintf( '%02d', $i ) ) . '</div><h3><span class="sr-only">' . esc_html( sprintf( /* translators: %d: step number */ __( 'Step %d:', 'caspian-sun' ), $i ) ) . ' </span>' . esc_html( $st['text'] ) . '</h3></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		?>
    </div>
    <?php endif; ?>
    <?php if ( $lt || csp_get( 'about_lt_heading' ) ) : ?>
    <div class="lead-time reveal">
      <span class="lt-wm" aria-hidden="true"></span>
      <div class="lt-head"><span class="lt-icon"><?php echo csp_icon( 'clock' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><?php echo csp_get( 'about_lt_heading' ) ? '<h3>' . esc_html( csp_get( 'about_lt_heading' ) ) . '</h3>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
      <?php if ( $lt ) : ?>
      <div class="lt-grid">
        <?php foreach ( $lt as $item ) : ?>
        <div class="lt-item"><span class="lt-ico"><?php echo csp_icon( $item['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><div><?php echo $item['label'] ? '<span class="lt-label">' . esc_html( $item['label'] ) . '</span>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo csp_p( $item['text'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div></div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php
/* Sourcing countries */
$countries = get_field( 'about_src_countries' );
$more      = csp_get( 'about_src_more' );
if ( $countries || csp_get( 'about_src_heading' ) ) :
	?>
<section class="cream">
  <div class="wrap">
    <div class="goal-countries">
        <?php echo csp_eyebrow( csp_get( 'about_src_eyebrow' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <?php echo csp_heading( csp_get( 'about_src_heading' ), 2, 'section-heading--upper' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <?php echo csp_p( csp_get( 'about_src_intro' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <?php if ( $countries || $more ) : ?>
        <ul class="flag-grid">
          <?php foreach ( (array) $countries as $c ) : ?>
          <li class="flag-card"><?php echo $c['flag'] ? '<span class="flag-img">' . csp_img( $c['flag'], array( 'alt' => '', 'width' => 72, 'height' => 48, 'decoding' => 'async' ) ) . '</span>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo $c['name'] ? '<span class="flag-name">' . esc_html( $c['name'] ) . '</span>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
          <?php endforeach; ?>
          <?php if ( $more ) : ?>
          <li class="flag-card flag-more"><span class="flag-img globe"><?php echo csp_icon( 'globe', 'stroke-width="1.5"' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><span class="flag-name"><?php echo esc_html( $more ); ?></span></li>
          <?php endif; ?>
        </ul>
        <?php endif; ?>
        <?php echo csp_p( csp_get( 'about_src_closing' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php
/* Industries */
$ind   = get_field( 'about_ind_list' );
$chips = get_field( 'about_ind_chips' );
if ( $ind || $chips || csp_get( 'about_ind_heading' ) ) :
	?>
<section>
  <div class="wrap">
    <div class="ind-grid">
      <div class="ind-text reveal">
        <?php echo csp_eyebrow( csp_get( 'about_ind_eyebrow' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <?php echo csp_heading( csp_get( 'about_ind_heading' ), 2, 'section-heading--upper' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <?php echo csp_p( csp_get( 'about_ind_intro' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <?php if ( $ind ) : ?><ul class="ind-list"><?php foreach ( $ind as $li ) { echo ! empty( $li['text'] ) ? '<li>' . esc_html( $li['text'] ) . '</li>' : ''; } // phpcs:ignore WordPress.Security.EscapeOutput ?></ul><?php endif; ?>
      </div>
      <?php if ( $chips || csp_get( 'about_ind_clients_text' ) ) : ?>
      <div class="ind-clients reveal">
        <?php echo csp_p( csp_get( 'about_ind_clients_text' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <?php if ( $chips ) : ?><ul class="client-chips" aria-label="<?php esc_attr_e( 'Customers', 'caspian-sun' ); ?>"><?php foreach ( $chips as $c ) { echo ! empty( $c['text'] ) ? '<li>' . esc_html( $c['text'] ) . '</li>' : ''; } // phpcs:ignore WordPress.Security.EscapeOutput ?></ul><?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php
/* Why choose us */
$why = get_field( 'about_why_list' );
if ( $why || csp_get( 'about_why_heading' ) ) :
	?>
<section class="alt-bg">
  <div class="wrap">
    <div class="sec-head">
      <?php echo csp_eyebrow( csp_get( 'about_why_eyebrow' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php echo csp_heading( csp_get( 'about_why_heading' ), 2, 'section-heading--upper' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    </div>
    <?php if ( $why ) : ?>
    <ul class="why-list">
      <?php foreach ( $why as $li ) : ?>
        <?php if ( ! empty( $li['text'] ) ) : ?><li class="reveal"><?php echo csp_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $li['text'] ); ?></span></li><?php endif; ?>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

</main>
<?php get_footer(); ?>
