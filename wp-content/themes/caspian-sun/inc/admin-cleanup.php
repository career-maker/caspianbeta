<?php
/**
 * WordPress admin tidy-up: only what is needed to manage this website.
 *
 * @package caspian-sun
 */

defined( 'ABSPATH' ) || exit;

// The theme / plugin file editors are a common attack path.
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}

// Field groups are defined in code (version controlled), so hide the ACF builder UI.
add_filter(
	'acf/settings/show_admin',
	function ( $show ) {
		return defined( 'CSP_SHOW_ACF_ADMIN' ) && CSP_SHOW_ACF_ADMIN ? $show : false;
	}
);
add_filter( 'acf/settings/show_updates', '__return_false' );

add_action(
	'admin_menu',
	function () {
		remove_menu_page( 'edit-comments.php' ); // Comments are not used.
		if ( ! current_user_can( 'manage_options' ) ) {
			remove_menu_page( 'tools.php' );
		}
		remove_submenu_page( 'themes.php', 'theme-editor.php' );
		// Posts are the "Insights" articles.
		global $menu, $submenu;
		foreach ( (array) $menu as $k => $m ) {
			if ( isset( $m[2] ) && 'edit.php' === $m[2] ) {
				$menu[ $k ][0] = __( 'Insights', 'caspian-sun' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
				$menu[ $k ][6] = 'dashicons-media-document'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
			}
		}
		if ( isset( $submenu['edit.php'] ) ) {
			foreach ( $submenu['edit.php'] as $k => $s ) {
				if ( 'edit-tags.php?taxonomy=category' === $s[2] || 'edit-tags.php?taxonomy=post_tag' === $s[2] ) {
					unset( $submenu['edit.php'][ $k ] ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
				}
			}
			if ( isset( $submenu['edit.php'][5] ) ) {
				$submenu['edit.php'][5][0] = __( 'All Articles', 'caspian-sun' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
			}
			if ( isset( $submenu['edit.php'][10] ) ) {
				$submenu['edit.php'][10][0] = __( 'Add Article', 'caspian-sun' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
			}
		}
	},
	999
);

// Articles do not use WordPress categories / tags.
add_action(
	'init',
	function () {
		unregister_taxonomy_for_object_type( 'category', 'post' );
		unregister_taxonomy_for_object_type( 'post_tag', 'post' );
	},
	20
);

add_filter(
	'post_type_labels_post',
	function ( $l ) {
		$l->name          = __( 'Insights', 'caspian-sun' );
		$l->singular_name = __( 'Article', 'caspian-sun' );
		$l->add_new_item  = __( 'Add New Article', 'caspian-sun' );
		$l->edit_item     = __( 'Edit Article', 'caspian-sun' );
		$l->all_items     = __( 'All Articles', 'caspian-sun' );
		$l->menu_name     = __( 'Insights', 'caspian-sun' );
		return $l;
	}
);

// Dashboard: drop the marketing widgets.
add_action(
	'wp_dashboard_setup',
	function () {
		foreach ( array( 'dashboard_primary', 'dashboard_secondary', 'dashboard_quick_press', 'dashboard_site_health', 'dashboard_right_now_comments', 'dashboard_activity', 'dashboard_incoming_links' ) as $w ) {
			remove_meta_box( $w, 'dashboard', 'normal' );
			remove_meta_box( $w, 'dashboard', 'side' );
		}
		remove_action( 'welcome_panel', 'wp_welcome_panel' );
	}
);

// Simple welcome panel pointing at the content areas.
add_action(
	'wp_dashboard_setup',
	function () {
		wp_add_dashboard_widget(
			'csp_help',
			__( 'Managing your website', 'caspian-sun' ),
			function () {
				echo '<p>' . esc_html__( 'Every text, image and link of the site is edited here:', 'caspian-sun' ) . '</p><ul style="list-style:disc;padding-left:1.2em">';
				echo '<li><a href="' . esc_url( admin_url( 'edit.php?post_type=page' ) ) . '"><strong>' . esc_html__( 'Pages', 'caspian-sun' ) . '</strong></a> — ' . esc_html__( 'Home, About, Products, Contact, Insights, legal pages.', 'caspian-sun' ) . '</li>';
				echo '<li><a href="' . esc_url( admin_url( 'edit.php?post_type=product' ) ) . '"><strong>' . esc_html__( 'Products', 'caspian-sun' ) . '</strong></a> — ' . esc_html__( 'catalogue items and categories.', 'caspian-sun' ) . '</li>';
				echo '<li><a href="' . esc_url( admin_url( 'edit.php' ) ) . '"><strong>' . esc_html__( 'Insights', 'caspian-sun' ) . '</strong></a> — ' . esc_html__( 'articles.', 'caspian-sun' ) . '</li>';
				echo '<li><a href="' . esc_url( admin_url( 'admin.php?page=csp-general' ) ) . '"><strong>' . esc_html__( 'Theme Settings', 'caspian-sun' ) . '</strong></a> — ' . esc_html__( 'logo, contact details, header, footer, SEO.', 'caspian-sun' ) . '</li>';
				echo '<li><a href="' . esc_url( admin_url( 'edit.php?post_type=csp_enquiry' ) ) . '"><strong>' . esc_html__( 'Enquiries', 'caspian-sun' ) . '</strong></a> — ' . esc_html__( 'messages sent through the contact form.', 'caspian-sun' ) . '</li>';
				echo '<li><a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '"><strong>' . esc_html__( 'Menus', 'caspian-sun' ) . '</strong></a> — ' . esc_html__( 'header and footer navigation.', 'caspian-sun' ) . '</li></ul>';
			}
		);
	}
);

// Keep the Pages list focused: hide the editor-only row actions that don't apply.
add_filter(
	'page_row_actions',
	function ( $actions, $post ) {
		unset( $actions['inline hide-if-no-js'] );
		return $actions;
	},
	10,
	2
);

// Remove the "Add New page" shortcut — the site's pages are fixed templates.
add_action(
	'admin_head',
	function () {
		$s = get_current_screen();
		if ( $s && 'edit-page' === $s->id ) {
			echo '<style>.page-title-action{display:none}</style>';
		}
	}
);

// Widgets / block-widget screens are not used.
add_action(
	'after_setup_theme',
	function () {
		remove_theme_support( 'widgets-block-editor' );
	}
);
add_action(
	'admin_menu',
	function () {
		remove_submenu_page( 'themes.php', 'widgets.php' );
	},
	999
);

// Hide the admin bar "New → Comment / Media" noise.
add_action(
	'admin_bar_menu',
	function ( $bar ) {
		$bar->remove_node( 'comments' );
		$bar->remove_node( 'wp-logo' );
		$bar->remove_node( 'new-media' );
	},
	999
);
