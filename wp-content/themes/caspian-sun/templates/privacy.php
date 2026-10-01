<?php
/**
 * Template Name: Privacy Policy
 *
 * @package caspian-sun
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'template-parts/legal', null, array( 'prefix' => 'privacy' ) );
get_footer();
