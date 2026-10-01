<?php
/**
 * Theme Settings (ACF options pages and their field groups).
 *
 * @package caspian-sun
 */

defined( 'ABSPATH' ) || exit;

add_action( 'acf/init', 'csp_register_options_pages' );
function csp_register_options_pages() {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}
	acf_add_options_page(
		array(
			'page_title'  => __( 'Theme Settings', 'caspian-sun' ),
			'menu_title'  => __( 'Theme Settings', 'caspian-sun' ),
			'menu_slug'   => 'csp-settings',
			'capability'  => 'manage_options',
			'position'    => 59,
			'icon_url'    => 'dashicons-admin-generic',
			'redirect'    => true,
			'autoload'    => true,
		)
	);
	$subs = array(
		'csp-general'  => __( 'Branding & Contact', 'caspian-sun' ),
		'csp-header'   => __( 'Header & Footer', 'caspian-sun' ),
		'csp-product'  => __( 'Product Page', 'caspian-sun' ),
		'csp-article'  => __( 'Article Page', 'caspian-sun' ),
		'csp-404'      => __( '404 Page', 'caspian-sun' ),
		'csp-seo'      => __( 'SEO Defaults', 'caspian-sun' ),
	);
	foreach ( $subs as $slug => $title ) {
		acf_add_options_sub_page(
			array(
				'page_title'  => $title,
				'menu_title'  => $title,
				'parent_slug' => 'csp-settings',
				'menu_slug'   => $slug,
				'capability'  => 'manage_options',
				'autoload'    => true,
			)
		);
	}
}

add_action( 'acf/init', 'csp_register_options_groups' );
function csp_register_options_groups() {
	$networks = array(
		'facebook'  => 'Facebook',
		'linkedin'  => 'LinkedIn',
		'instagram' => 'Instagram',
		'x'         => 'X (Twitter)',
		'whatsapp'  => 'WhatsApp',
	);

	/* ---- Branding & Contact ------------------------------------------------ */
	csp_group(
		'opt_general',
		'Branding & Contact',
		csp_loc_options( 'csp-general' ),
		array(
			csp_f_tab( 'Branding' ),
			csp_f_img( 'opt_logo', 'Logo', array( 'instructions' => 'Used in the header, mobile menu, footer and page preloader (square, 360×360 recommended).' ) ),
			csp_f_img( 'opt_favicon', 'Favicon', array( 'instructions' => 'Browser tab icon (PNG, square).' ) ),
			csp_f_tab( 'Contact details' ),
			csp_f_text( 'opt_phone_display', 'Phone — display text', array( 'instructions' => 'Shown in header, footer and contact page. e.g. +971 58 561 6040' ) ),
			csp_f_text( 'opt_phone_tel', 'Phone — dial number', array( 'instructions' => 'Digits with country code, used for the tel: link. e.g. +971585616040' ) ),
			csp_f_text( 'opt_whatsapp_display', 'WhatsApp — display text' ),
			csp_f_text( 'opt_whatsapp_number', 'WhatsApp — number for wa.me link', array( 'instructions' => 'Digits only, with country code and no "+". e.g. 971585616040' ) ),
			csp_f( 'email', 'opt_email', 'Email address' ),
			csp_f_area( 'opt_address', 'Address', 2, array( 'instructions' => 'One line per row. Footer shows it on a single line; contact page keeps the line breaks.' ) ),
			csp_f_area( 'opt_hours', 'Opening hours', 2, array( 'instructions' => 'One line per row.' ) ),
			csp_f_tab( 'Social networks' ),
			csp_f_rep(
				'opt_social',
				'Social links',
				array(
					csp_f_select( 'network', 'Network', $networks, array( 'allow_null' => 0, 'default_value' => 'facebook' ) ),
					csp_f_url( 'url', 'Profile URL' ),
				),
				array( 'layout' => 'table', 'button_label' => 'Add social link', 'collapsed' => '' )
			),
			csp_f_tab( 'Common labels' ),
			csp_f_text( 'opt_breadcrumb_home', 'Breadcrumb — "Home" label' ),
			csp_f_text( 'opt_view_more', '"View More" link label' ),
		)
	);

	/* ---- Header & Footer --------------------------------------------------- */
	csp_group(
		'opt_header',
		'Header & Footer',
		csp_loc_options( 'csp-header' ),
		array(
			csp_f_tab( 'Header' ),
			csp_f_link( 'opt_header_cta', 'Header button ("Get In Touch")', array( 'instructions' => 'Shown in the header and the mobile menu. Clear the link to hide the button. Navigation links are managed under Appearance → Menus.' ) ),
			csp_f_bool( 'opt_header_phone_show', 'Show phone number in header and mobile menu', array( 'default_value' => 1 ) ),
			csp_f_tab( 'Footer — headings & text' ),
			csp_f_text( 'opt_footer_social_heading', 'Social column heading' ),
			csp_f_text( 'opt_footer_about_heading', 'About column heading' ),
			csp_f_area( 'opt_footer_about_text', 'About text', 5 ),
			csp_f_text( 'opt_footer_company_heading', 'Company column heading', array( 'instructions' => 'Links come from the "Footer — Company column" menu.' ) ),
			csp_f_text( 'opt_footer_products_heading', 'Products column heading', array( 'instructions' => 'Links come from the "Footer — Products column" menu.' ) ),
			csp_f_text( 'opt_footer_contact_heading', 'Contact column heading', array( 'instructions' => 'Phone, email and address come from Branding & Contact.' ) ),
			csp_f_tab( 'Footer — bottom bar' ),
			csp_f_text( 'opt_copyright', 'Copyright line' ),
			csp_f_rep(
				'opt_legal_links',
				'Legal links',
				array( csp_f_link( 'link', 'Link' ) ),
				array( 'layout' => 'table', 'button_label' => 'Add legal link', 'collapsed' => '' )
			),
			csp_f_text( 'opt_credit_text', 'Designer credit text' ),
			csp_f_link( 'opt_credit_link', 'Designer credit link', array( 'instructions' => 'Link target only; the visible label is the logo below.' ) ),
			csp_f_img( 'opt_credit_logo', 'Designer credit logo' ),
		),
		0
	);

	/* ---- Product page settings -------------------------------------------- */
	csp_group(
		'opt_product',
		'Product Detail Page',
		csp_loc_options( 'csp-product' ),
		array(
			csp_f_tab( 'Banner' ),
			csp_f_text( 'pd_banner_heading', 'Banner heading' ),
			csp_f_area( 'pd_banner_text', 'Banner text', 2 ),
			csp_f_img( 'pd_banner_image', 'Banner image' ),
			csp_f_img( 'pd_banner_image_mobile', 'Banner image — mobile (optional)' ),
			csp_f_tab( 'Product panel' ),
			csp_f_text( 'pd_badge', 'Image badge text' ),
			csp_f_text( 'pd_origin_label', '"Origin" label' ),
			csp_f_text( 'pd_packing_label', '"Packing" label' ),
			csp_f_text( 'pd_packing_hint', 'Packing hint (nothing selected)' ),
			csp_f_text( 'pd_packing_selected', 'Packing hint (option selected)', array( 'instructions' => 'The chosen option is appended after this text.' ) ),
			csp_f_text( 'pd_enquire_label', 'Enquiry button label' ),
			csp_f_link( 'pd_back_link', 'Secondary button ("Back to Shop")' ),
			csp_f_rep(
				'pd_assurances',
				'Assurance badges',
				array(
					csp_f_select( 'icon', 'Icon', csp_icon_choices( array( 'leaf', 'handling', 'global-std', 'check', 'globe', 'truck', 'box' ) ), array( 'allow_null' => 0 ) ),
					csp_f_area( 'text', 'Text', 2, array( 'instructions' => 'Use a line break for a two-line label.' ) ),
				),
				array( 'layout' => 'table', 'button_label' => 'Add badge', 'collapsed' => '' )
			),
			csp_f_tab( 'Related products' ),
			csp_f_text( 'pd_related_heading', 'Related products heading' ),
			csp_f_link( 'pd_related_link', '"View all" link' ),
		),
		0
	);

	/* ---- Article page settings -------------------------------------------- */
	csp_group(
		'opt_article',
		'Article Detail Page',
		csp_loc_options( 'csp-article' ),
		array(
			csp_f_tab( 'Banner' ),
			csp_f_text( 'bd_banner_heading', 'Banner heading' ),
			csp_f_area( 'bd_banner_text', 'Banner text', 2 ),
			csp_f_img( 'bd_banner_image', 'Banner image' ),
			csp_f_img( 'bd_banner_image_mobile', 'Banner image — mobile (optional)' ),
			csp_f_tab( 'Labels' ),
			csp_f_text( 'bd_by_label', '"By" label' ),
			csp_f_text( 'bd_reference_label', '"Reference:" label' ),
			csp_f_text( 'bd_share_label', '"Share with:" label' ),
			csp_f_text( 'bd_refs_heading', 'References heading' ),
			csp_f_text( 'bd_related_heading', 'Sidebar heading (related articles)' ),
		),
		0
	);

	/* ---- 404 -------------------------------------------------------------- */
	csp_group(
		'opt_404',
		'404 Page',
		csp_loc_options( 'csp-404' ),
		array(
			csp_f_text( 'e404_banner_heading', 'Banner heading' ),
			csp_f_area( 'e404_banner_text', 'Banner text', 2 ),
			csp_f_img( 'e404_banner_image', 'Banner image' ),
			csp_f_img( 'e404_banner_image_mobile', 'Banner image — mobile (optional)' ),
			csp_f_text( 'e404_heading', 'Message heading' ),
			csp_f_area( 'e404_text', 'Message text', 3 ),
			csp_f_link( 'e404_button', 'Button' ),
		),
		0
	);

	/* ---- SEO defaults ----------------------------------------------------- */
	csp_group(
		'opt_seo',
		'SEO Defaults',
		csp_loc_options( 'csp-seo' ),
		array(
			csp_f_text( 'seo_site_name', 'Site name', array( 'instructions' => 'Appended to page titles ("Page | Site name") and used in Open Graph / structured data.' ) ),
			csp_f_area( 'seo_default_description', 'Default meta description', 3, array( 'instructions' => 'Used when a page has no description of its own.' ) ),
			csp_f_img( 'seo_default_image', 'Default social-sharing image', array( 'instructions' => 'Open Graph / Twitter image used when a page has none (1200×630 recommended).' ) ),
			csp_f_text( 'seo_org_name', 'Organisation legal name (structured data)' ),
			csp_f_text( 'seo_org_founded', 'Year founded (structured data)' ),
		),
		0
	);

	/* ---- Per-page SEO (pages, posts, products) ---------------------------- */
	foreach ( defined( 'WPSEO_VERSION' ) ? array() : array( 'page', 'post', 'product' ) as $pt ) { // Yoast replaces these boxes.
		$seo_fields = array(
			csp_f_text( 'seo_title', 'SEO title', array( 'instructions' => 'Leave empty to use "Page title | Site name".', 'maxlength' => 70 ) ),
			csp_f_area( 'seo_description', 'Meta description', 3, array( 'maxlength' => 200, 'instructions' => 'Leave empty to use the default (or the excerpt for articles/products).' ) ),
			csp_f_img( 'seo_image', 'Social-sharing image', array( 'instructions' => 'Leave empty to use the page image or the site default.' ) ),
		);
		foreach ( $seo_fields as $i => $f ) {
			$seo_fields[ $i ]['key'] = csp_seo_key( $f['name'], $pt ); // Field keys must be unique per group.
		}
		csp_group( 'seo_' . $pt, 'SEO', csp_loc_type( $pt ), $seo_fields, 90, array( 'position' => 'normal', 'hide_on_screen' => array() ) );
	}
}
