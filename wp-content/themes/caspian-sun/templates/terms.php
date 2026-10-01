<?php
/**
 * Template Name: Terms of Use
 *
 * @package caspian-sun
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'template-parts/legal', null, array( 'prefix' => 'terms' ) );
get_footer();
