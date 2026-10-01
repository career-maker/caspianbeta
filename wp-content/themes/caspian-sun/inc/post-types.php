<?php
/**
 * Custom post types & taxonomies.
 *
 * - product            : catalogue items (title + ACF fields)
 * - product_category   : Seafood / Poultry / Meat
 * - csp_enquiry        : contact-form submissions (admin only)
 *
 * Pages, posts and products are edited exclusively through ACF field groups,
 * so the block editor and the classic editor box are switched off for them.
 *
 * @package caspian-sun
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'csp_register_types' );
function csp_register_types() {
	register_post_type(
		'product',
		array(
			'labels'              => array(
				'name'               => __( 'Products', 'caspian-sun' ),
				'singular_name'      => __( 'Product', 'caspian-sun' ),
				'add_new_item'       => __( 'Add New Product', 'caspian-sun' ),
				'edit_item'          => __( 'Edit Product', 'caspian-sun' ),
				'all_items'          => __( 'All Products', 'caspian-sun' ),
				'menu_name'          => __( 'Products', 'caspian-sun' ),
				'search_items'       => __( 'Search Products', 'caspian-sun' ),
				'not_found'          => __( 'No products found.', 'caspian-sun' ),
			),
			'public'              => true,
			'has_archive'         => false, // The "Products" page (template) is the listing.
			'rewrite'             => array( 'slug' => 'product', 'with_front' => false ),
			'menu_icon'           => 'dashicons-cart',
			'menu_position'       => 21,
			'supports'            => array( 'title', 'page-attributes' ),
			'show_in_rest'        => false,
			'exclude_from_search' => false,
		)
	);

	register_taxonomy(
		'product_category',
		'product',
		array(
			'labels'            => array(
				'name'          => __( 'Product Categories', 'caspian-sun' ),
				'singular_name' => __( 'Product Category', 'caspian-sun' ),
				'menu_name'     => __( 'Categories', 'caspian-sun' ),
			),
			'hierarchical'      => true,
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => false,
			'rewrite'           => false,
		)
	);

	register_post_type(
		'csp_enquiry',
		array(
			'labels'              => array(
				'name'          => __( 'Enquiries', 'caspian-sun' ),
				'singular_name' => __( 'Enquiry', 'caspian-sun' ),
				'menu_name'     => __( 'Enquiries', 'caspian-sun' ),
				'all_items'     => __( 'All Enquiries', 'caspian-sun' ),
				'edit_item'     => __( 'View Enquiry', 'caspian-sun' ),
				'not_found'     => __( 'No enquiries yet.', 'caspian-sun' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'menu_icon'           => 'dashicons-email-alt',
			'menu_position'       => 22,
			'supports'            => array( 'title' ),
			'capability_type'     => 'post',
			'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'        => true,
			'exclude_from_search' => true,
		)
	);

	// Pages, posts and products have no body editor: all content is ACF-managed.
	remove_post_type_support( 'page', 'editor' );
	remove_post_type_support( 'post', 'editor' );
	remove_post_type_support( 'page', 'thumbnail' );
	remove_post_type_support( 'post', 'thumbnail' );
	remove_post_type_support( 'post', 'comments' );
	remove_post_type_support( 'post', 'trackbacks' );
	remove_post_type_support( 'page', 'comments' );
}

add_filter(
	'use_block_editor_for_post_type',
	function ( $use, $type ) {
		return in_array( $type, array( 'page', 'post', 'product', 'csp_enquiry' ), true ) ? false : $use;
	},
	10,
	2
);

/** Product order inside each category follows the "Order" attribute, then title. */
add_action(
	'pre_get_posts',
	function ( $q ) {
		if ( ! is_admin() && $q->is_main_query() && $q->is_post_type_archive( 'product' ) ) {
			$q->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
		}
	}
);

/* ------------------------------------------------- Enquiry admin (read-only) */

add_action(
	'add_meta_boxes',
	function () {
		add_meta_box(
			'csp_enquiry_details',
			__( 'Enquiry details', 'caspian-sun' ),
			function ( $post ) {
				$rows = array(
					'name'    => __( 'Name', 'caspian-sun' ),
					'phone'   => __( 'Phone', 'caspian-sun' ),
					'email'   => __( 'Email', 'caspian-sun' ),
					'subject' => __( 'Subject', 'caspian-sun' ),
					'product' => __( 'Product', 'caspian-sun' ),
					'size'    => __( 'Packing', 'caspian-sun' ),
					'message' => __( 'Message', 'caspian-sun' ),
					'ip'      => __( 'IP address', 'caspian-sun' ),
				);
				echo '<table class="form-table"><tbody>';
				foreach ( $rows as $k => $label ) {
					$v = get_post_meta( $post->ID, '_csp_' . $k, true );
					if ( '' === $v ) {
						continue;
					}
					echo '<tr><th>' . esc_html( $label ) . '</th><td>' . nl2br( esc_html( $v ) ) . '</td></tr>';
				}
				echo '</tbody></table>';
			},
			'csp_enquiry',
			'normal',
			'high'
		);
	}
);

add_filter(
	'manage_csp_enquiry_posts_columns',
	function () {
		return array(
			'cb'    => '<input type="checkbox" />',
			'title' => __( 'Enquiry', 'caspian-sun' ),
			'email' => __( 'Email', 'caspian-sun' ),
			'prod'  => __( 'Product', 'caspian-sun' ),
			'date'  => __( 'Date', 'caspian-sun' ),
		);
	}
);
add_action(
	'manage_csp_enquiry_posts_custom_column',
	function ( $col, $id ) {
		if ( 'email' === $col ) {
			echo esc_html( get_post_meta( $id, '_csp_email', true ) );
		} elseif ( 'prod' === $col ) {
			echo esc_html( get_post_meta( $id, '_csp_product', true ) );
		}
	},
	10,
	2
);
