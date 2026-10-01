<?php
/**
 * Small builder layer on top of ACF Pro's local (PHP) field registration.
 *
 * Field keys are derived from field names so they are stable across
 * environments and the seeder / templates can refer to fields by name.
 * Field names are globally unique (page prefix_…), so one meta pool per
 * object never collides.
 *
 * @package caspian-sun
 */

defined( 'ABSPATH' ) || exit;

/** Admin notice when ACF Pro is missing. */
add_action(
	'admin_notices',
	function () {
		if ( ! class_exists( 'ACF' ) || ! function_exists( 'acf_add_options_page' ) ) {
			echo '<div class="notice notice-error"><p><strong>Caspian &amp; Sun theme:</strong> ACF Pro is required. Activate the "Advanced Custom Fields PRO" plugin.</p></div>';
		}
	}
);

/* ------------------------------------------------------------ Field makers */

function csp_fk( $name ) {
	return 'field_csp_' . $name;
}

/** Key of an SEO field for a given post type (the same names exist on page/post/product). */
function csp_seo_key( $name, $post_type ) {
	return 'field_csp_' . $name . '_' . $post_type;
}

function csp_f( $type, $name, $label, $args = array() ) {
	return array_merge(
		array(
			'key'   => csp_fk( $name ),
			'name'  => $name,
			'label' => $label,
			'type'  => $type,
		),
		$args
	);
}

function csp_f_text( $name, $label, $args = array() ) {
	return csp_f( 'text', $name, $label, $args );
}

/** Multi-line plain text (line breaks are rendered as <br>). */
function csp_f_area( $name, $label, $rows = 3, $args = array() ) {
	return csp_f( 'textarea', $name, $label, array_merge( array( 'rows' => $rows, 'new_lines' => '' ), $args ) );
}

function csp_f_img( $name, $label, $args = array() ) {
	return csp_f(
		'image',
		$name,
		$label,
		array_merge(
			array(
				'return_format' => 'id',
				'preview_size'  => 'medium',
				'library'       => 'all',
			),
			$args
		)
	);
}

function csp_f_link( $name, $label, $args = array() ) {
	return csp_f( 'link', $name, $label, array_merge( array( 'return_format' => 'array' ), $args ) );
}

function csp_f_url( $name, $label, $args = array() ) {
	return csp_f( 'url', $name, $label, $args );
}

function csp_f_bool( $name, $label, $args = array() ) {
	return csp_f( 'true_false', $name, $label, array_merge( array( 'ui' => 1, 'default_value' => 0 ), $args ) );
}

function csp_f_num( $name, $label, $args = array() ) {
	return csp_f( 'number', $name, $label, $args );
}

function csp_f_select( $name, $label, $choices, $args = array() ) {
	return csp_f( 'select', $name, $label, array_merge( array( 'choices' => $choices, 'allow_null' => 1, 'return_format' => 'value' ), $args ) );
}

function csp_f_file( $name, $label, $args = array() ) {
	return csp_f( 'file', $name, $label, array_merge( array( 'return_format' => 'id', 'library' => 'all' ), $args ) );
}

function csp_f_wysiwyg( $name, $label, $args = array() ) {
	return csp_f(
		'wysiwyg',
		$name,
		$label,
		array_merge(
			array(
				'tabs'         => 'visual',
				'toolbar'      => 'basic',
				'media_upload' => 0,
			),
			$args
		)
	);
}

function csp_f_tab( $label ) {
	return array(
		'key'       => '',
		'label'     => $label,
		'name'      => '',
		'type'      => 'tab',
		'placement' => 'top',
	);
}

/** Repeater; sub-field keys are namespaced by the parent so they stay unique. */
function csp_f_rep( $name, $label, $subs, $args = array() ) {
	foreach ( $subs as $i => $s ) {
		$subs[ $i ]['key'] = csp_fk( $name . '_' . $s['name'] );
	}
	return csp_f(
		'repeater',
		$name,
		$label,
		array_merge(
			array(
				'layout'       => 'block',
				'button_label' => __( 'Add item', 'caspian-sun' ),
				'sub_fields'   => $subs,
				'collapsed'    => $subs ? $subs[0]['key'] : '',
			),
			$args
		)
	);
}

/** Heading + paragraphs sub-pattern used by many sections. */
function csp_f_paras( $name, $label = 'Paragraphs' ) {
	return csp_f_rep( $name, $label, array( csp_f_area( 'text', 'Paragraph', 4 ) ), array( 'button_label' => __( 'Add paragraph', 'caspian-sun' ), 'layout' => 'block' ) );
}

/* ---------------------------------------------------------- Location rules */

function csp_loc_template( $tpl ) {
	return array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => $tpl ) ) );
}
function csp_loc_front() {
	return array( array( array( 'param' => 'page_type', 'operator' => '==', 'value' => 'front_page' ) ) );
}
function csp_loc_posts_page() {
	return array( array( array( 'param' => 'page_type', 'operator' => '==', 'value' => 'posts_page' ) ) );
}
function csp_loc_type( $type ) {
	return array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => $type ) ) );
}
function csp_loc_options( $slug ) {
	return array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => $slug ) ) );
}
function csp_loc_taxonomy( $tax ) {
	return array( array( array( 'param' => 'taxonomy', 'operator' => '==', 'value' => $tax ) ) );
}

/** Register a field group (call inside acf/init). Tab keys are generated from the group key. */
function csp_group( $key, $title, $location, $fields, $order = 0, $extra = array() ) {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	foreach ( $fields as $i => $f ) {
		if ( 'tab' === $f['type'] ) {
			$fields[ $i ]['key'] = 'field_csp_tab_' . substr( md5( $key . $f['label'] ), 0, 12 );
		}
	}
	acf_add_local_field_group(
		array_merge(
			array(
				'key'                   => 'group_csp_' . $key,
				'title'                 => $title,
				'fields'                => $fields,
				'location'              => $location,
				'menu_order'            => $order,
				'position'              => 'acf_after_title',
				'style'                 => 'default',
				'label_placement'       => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen'        => array( 'the_content', 'excerpt', 'discussion', 'comments', 'author', 'format', 'featured_image', 'send-trackbacks' ),
				'active'                => true,
			),
			$extra
		)
	);
}
