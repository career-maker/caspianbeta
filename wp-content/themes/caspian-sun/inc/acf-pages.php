<?php
/**
 * ACF field groups — one per page, organised in tabs that follow the
 * order of sections on the page.
 *
 * @package caspian-sun
 */

defined( 'ABSPATH' ) || exit;

/** Banner fields shared by inner pages. */
function csp_banner_fields( $p ) {
	return array(
		csp_f_tab( 'Banner' ),
		csp_f_text( $p . '_banner_heading', 'Heading' ),
		csp_f_area( $p . '_banner_text', 'Intro text', 2 ),
		csp_f_img( $p . '_banner_image', 'Background image' ),
		csp_f_img( $p . '_banner_image_mobile', 'Background image — mobile (optional)', array( 'instructions' => 'Smaller crop used below 769px. Falls back to the main image.' ) ),
	);
}

/** Video hero fields shared by Home and Insights. */
function csp_video_hero_fields( $p ) {
	return array(
		csp_f_img( $p . '_hero_poster', 'Poster image', array( 'instructions' => 'Shown immediately while the video loads (1280×720).' ) ),
		csp_f_img( $p . '_hero_poster_mobile', 'Poster image — small (optional)', array( 'instructions' => 'Used on screens up to 640px wide.' ) ),
		csp_f_file( $p . '_hero_video', 'Background video (desktop)', array( 'mime_types' => 'mp4,webm' ) ),
		csp_f_file( $p . '_hero_video_mobile', 'Background video (mobile)', array( 'mime_types' => 'mp4,webm', 'instructions' => 'Smaller file for phones. Falls back to the desktop video.' ) ),
	);
}

add_action( 'acf/init', 'csp_register_page_groups' );
function csp_register_page_groups() {

	/* =================================================================== HOME */
	csp_group(
		'home',
		'Home Page',
		csp_loc_front(),
		array_merge(
			array( csp_f_tab( 'Hero' ) ),
			array(
				csp_f_area( 'home_hero_heading', 'Heading', 2, array( 'instructions' => 'Use a line break to split the heading over two lines.' ) ),
				csp_f_area( 'home_hero_text', 'Text', 4 ),
				csp_f_link( 'home_hero_button', 'Button' ),
			),
			csp_video_hero_fields( 'home' ),
			array(
				csp_f_tab( 'About' ),
				csp_f_text( 'home_about_eyebrow', 'Eyebrow' ),
				csp_f_area( 'home_about_heading', 'Heading', 2 ),
				csp_f_paras( 'home_about_paras' ),
				csp_f_link( 'home_about_button', 'Button' ),
				csp_f_img( 'home_about_image', 'Photo' ),
				csp_f_rep(
					'home_counters',
					'Counters',
					array(
						csp_f_img( 'icon', 'Icon' ),
						csp_f_num( 'number', 'Number', array( 'min' => 0 ) ),
						csp_f_text( 'suffix', 'Suffix', array( 'instructions' => 'e.g. +' ) ),
						csp_f_text( 'label', 'Label' ),
					),
					array( 'button_label' => 'Add counter', 'layout' => 'table' )
				),

				csp_f_tab( 'Categories' ),
				csp_f_text( 'home_cats_eyebrow', 'Eyebrow' ),
				csp_f_area( 'home_cats_heading', 'Heading', 2 ),
				csp_f_link( 'home_cats_button', 'Button' ),
				csp_f_img( 'home_cats_bg', 'Section background image', array( 'instructions' => 'Dark overlay is applied automatically.' ) ),
				csp_f_rep(
					'home_cats',
					'Category cards',
					array(
						csp_f_img( 'image', 'Image' ),
						csp_f_text( 'title', 'Title' ),
						csp_f_text( 'subtitle', 'Subtitle' ),
						csp_f_link( 'link', 'Link', array( 'instructions' => 'Only the URL is used (the whole card is clickable).' ) ),
					),
					array( 'button_label' => 'Add category card' )
				),

				csp_f_tab( 'Why Choose Us' ),
				csp_f_area( 'home_why_heading', 'Heading', 2 ),
				csp_f_area( 'home_why_text', 'Text', 3 ),
				csp_f_rep(
					'home_why_items',
					'Feature columns',
					array(
						csp_f_img( 'icon', 'Icon' ),
						csp_f_text( 'title', 'Title' ),
						csp_f_area( 'text', 'Text', 2 ),
					),
					array( 'button_label' => 'Add feature', 'layout' => 'table' )
				),
				csp_f_img( 'home_why_video_image', 'Video cover image' ),
				csp_f_file( 'home_why_video', 'Video file', array( 'mime_types' => 'mp4,webm', 'instructions' => 'Opens in a pop-up player when the play button is pressed. Clear it to hide the play button.' ) ),

				csp_f_tab( 'Clients' ),
				csp_f_area( 'home_clients_heading', 'Heading', 2 ),
				csp_f_area( 'home_clients_text', 'Text', 3 ),
				csp_f_link( 'home_clients_button', 'Button' ),
				csp_f_rep(
					'home_client_logos',
					'Client logos',
					array(
						csp_f_img( 'logo', 'Logo' ),
						csp_f_bool( 'invert', 'Logo is white on transparent', array( 'instructions' => 'Turn on to render the logo dark on the light card.' ) ),
					),
					array( 'button_label' => 'Add logo', 'layout' => 'table' )
				),

				csp_f_tab( 'Products' ),
				csp_f_text( 'home_products_eyebrow', 'Eyebrow' ),
				csp_f_area( 'home_products_heading', 'Heading', 2 ),
				csp_f_area( 'home_products_text', 'Text', 3 ),
				csp_f( 'relationship', 'home_products', 'Featured products', array(
					'post_type'     => array( 'product' ),
					'filters'       => array( 'search' ),
					'return_format' => 'id',
					'instructions'  => 'Pick and order the products shown on the home page. Card text and image come from each product.',
				) ),
				csp_f_link( 'home_products_button', 'Button' ),

				csp_f_tab( 'Testimonials' ),
				csp_f_img( 'home_testi_image', 'Large photo' ),
				csp_f_img( 'home_testi_avatars', 'Avatar cluster image' ),
				csp_f_text( 'home_testi_count', 'Counter value', array( 'instructions' => 'e.g. 1K +' ) ),
				csp_f_text( 'home_testi_count_label', 'Counter label' ),
				csp_f_text( 'home_testi_eyebrow', 'Eyebrow' ),
				csp_f_area( 'home_testi_heading', 'Heading', 2 ),
				csp_f_rep(
					'home_testimonials',
					'Testimonials',
					array(
						csp_f_select( 'type', 'Type', array( 'text' => 'Text quote', 'video' => 'Video' ), array( 'allow_null' => 0, 'default_value' => 'text' ) ),
						csp_f_area( 'quote', 'Quote (text type)', 4 ),
						csp_f_text( 'name', 'Name' ),
						csp_f_text( 'role', 'Role / company' ),
						csp_f_img( 'avatar', 'Avatar' ),
						csp_f_img( 'video_cover', 'Video cover image (video type)' ),
						csp_f_file( 'video', 'Video file (video type)', array( 'mime_types' => 'mp4,webm' ) ),
					),
					array( 'button_label' => 'Add testimonial' )
				),

				csp_f_tab( 'Articles' ),
				csp_f_text( 'home_articles_eyebrow', 'Eyebrow' ),
				csp_f_area( 'home_articles_heading', 'Heading', 2 ),
				csp_f_rep(
					'home_articles',
					'Articles — use 1, 3 or 5 (1st = large, 2nd–3rd = middle column, 4th–5th = right column)',
					array(
						csp_f( 'post_object', 'post', 'Article', array( 'post_type' => array( 'post' ), 'return_format' => 'id', 'ui' => 1 ) ),
						csp_f_img( 'image', 'Image for this slot' ),
						csp_f_bool( 'open', 'Show image by default (side columns)', array( 'instructions' => 'In the side columns one article shows its image; hovering the other swaps it.' ) ),
					),
					array( 'button_label' => 'Add article', 'max' => 5 )
				),

				csp_f_tab( 'Contact banner' ),
				csp_f_text( 'home_cta_eyebrow', 'Eyebrow' ),
				csp_f_area( 'home_cta_heading', 'Heading', 2 ),
				csp_f_area( 'home_cta_text', 'Text', 3 ),
				csp_f_link( 'home_cta_button', 'Button' ),
				csp_f_img( 'home_cta_image', 'Background image' ),
			)
		),
		0
	);

	/* ================================================================== ABOUT */
	csp_group(
		'about',
		'About Page',
		csp_loc_template( 'templates/about.php' ),
		array_merge(
			csp_banner_fields( 'about' ),
			array(
				csp_f_tab( 'Overview' ),
				csp_f_text( 'about_ov_eyebrow', 'Eyebrow' ),
				csp_f_text( 'about_ov_heading', 'Heading' ),
				csp_f_paras( 'about_ov_paras' ),
				csp_f_img( 'about_ov_image_back', 'Image — large (back)' ),
				csp_f_img( 'about_ov_image_front', 'Image — tall (front)' ),

				csp_f_tab( 'Services' ),
				csp_f_text( 'about_svc_eyebrow', 'Eyebrow' ),
				csp_f_text( 'about_svc_heading', 'Heading' ),
				csp_f_rep(
					'about_services',
					'Service cards',
					array(
						csp_f_select( 'icon', 'Icon', csp_icon_choices( array( 'truck', 'globe', 'box', 'search', 'link', 'check', 'clock', 'calendar', 'bolt' ) ), array( 'allow_null' => 0 ) ),
						csp_f_text( 'title', 'Title' ),
						csp_f_area( 'text', 'Text', 2 ),
					),
					array( 'button_label' => 'Add service' )
				),

				csp_f_tab( 'Sourcing expertise' ),
				csp_f_img( 'about_str_image', 'Image' ),
				csp_f_text( 'about_str_eyebrow', 'Eyebrow' ),
				csp_f_text( 'about_str_heading', 'Heading' ),
				csp_f_area( 'about_str_intro', 'Intro paragraph', 3 ),
				csp_f_text( 'about_str_lead', 'List lead-in' ),
				csp_f_rep( 'about_str_list', 'Checklist', array( csp_f_text( 'text', 'Item' ) ), array( 'layout' => 'table', 'button_label' => 'Add item', 'collapsed' => '' ) ),
				csp_f_area( 'about_str_closing', 'Closing paragraph', 3 ),

				csp_f_tab( 'International network' ),
				csp_f_text( 'about_net_eyebrow', 'Eyebrow' ),
				csp_f_text( 'about_net_heading', 'Heading' ),
				csp_f_area( 'about_net_intro', 'Intro', 3 ),
				csp_f_rep(
					'about_net_items',
					'Locations',
					array(
						csp_f_text( 'country', 'Country' ),
						csp_f_text( 'company', 'Company name' ),
						csp_f_area( 'address', 'Address', 2 ),
					),
					array( 'button_label' => 'Add location' )
				),

				csp_f_tab( 'Vision' ),
				csp_f_img( 'about_vis_image', 'Background image' ),
				csp_f_text( 'about_vis_eyebrow', 'Eyebrow' ),
				csp_f_text( 'about_vis_heading', 'Heading' ),
				csp_f_area( 'about_vis_text', 'Text', 4 ),

				csp_f_tab( 'Mission' ),
				csp_f_img( 'about_mis_image', 'Image' ),
				csp_f_text( 'about_mis_eyebrow', 'Eyebrow' ),
				csp_f_text( 'about_mis_heading', 'Heading' ),
				csp_f_paras( 'about_mis_paras' ),

				csp_f_tab( 'Strategic goal' ),
				csp_f_text( 'about_goal_eyebrow', 'Eyebrow' ),
				csp_f_text( 'about_goal_heading', 'Heading' ),
				csp_f_paras( 'about_goal_paras' ),

				csp_f_tab( 'How ordering works' ),
				csp_f_text( 'about_ord_eyebrow', 'Eyebrow' ),
				csp_f_text( 'about_ord_heading', 'Heading' ),
				csp_f_area( 'about_ord_intro', 'Intro', 5 ),
				csp_f_rep( 'about_ord_steps', 'Steps (numbered automatically)', array( csp_f_text( 'text', 'Step title' ) ), array( 'layout' => 'table', 'button_label' => 'Add step', 'collapsed' => '' ) ),
				csp_f_text( 'about_lt_heading', 'Lead-time heading' ),
				csp_f_rep(
					'about_lt_items',
					'Lead-time items',
					array(
						csp_f_select( 'icon', 'Icon', csp_icon_choices( array( 'calendar', 'bolt', 'clock', 'truck', 'check' ) ), array( 'allow_null' => 0 ) ),
						csp_f_text( 'label', 'Label' ),
						csp_f_area( 'text', 'Text', 2 ),
					),
					array( 'button_label' => 'Add item' )
				),

				csp_f_tab( 'Sourcing countries' ),
				csp_f_text( 'about_src_eyebrow', 'Eyebrow' ),
				csp_f_text( 'about_src_heading', 'Heading' ),
				csp_f_area( 'about_src_intro', 'Intro', 2 ),
				csp_f_rep(
					'about_src_countries',
					'Countries',
					array(
						csp_f_img( 'flag', 'Flag image' ),
						csp_f_text( 'name', 'Country name' ),
					),
					array( 'layout' => 'table', 'button_label' => 'Add country', 'collapsed' => '' )
				),
				csp_f_text( 'about_src_more', '"More regions" label', array( 'instructions' => 'Shown after the flags with a globe icon. Clear to hide.' ) ),
				csp_f_area( 'about_src_closing', 'Closing paragraph', 3 ),

				csp_f_tab( 'Industries' ),
				csp_f_text( 'about_ind_eyebrow', 'Eyebrow' ),
				csp_f_text( 'about_ind_heading', 'Heading' ),
				csp_f_area( 'about_ind_intro', 'Intro', 2 ),
				csp_f_rep( 'about_ind_list', 'Industry list', array( csp_f_text( 'text', 'Item' ) ), array( 'layout' => 'table', 'button_label' => 'Add industry', 'collapsed' => '' ) ),
				csp_f_area( 'about_ind_clients_text', 'Clients paragraph', 3 ),
				csp_f_rep( 'about_ind_chips', 'Client names', array( csp_f_text( 'text', 'Name' ) ), array( 'layout' => 'table', 'button_label' => 'Add client', 'collapsed' => '' ) ),

				csp_f_tab( 'Why choose us' ),
				csp_f_text( 'about_why_eyebrow', 'Eyebrow' ),
				csp_f_text( 'about_why_heading', 'Heading' ),
				csp_f_rep( 'about_why_list', 'Reasons', array( csp_f_text( 'text', 'Reason' ) ), array( 'layout' => 'table', 'button_label' => 'Add reason', 'collapsed' => '' ) ),
			)
		),
		0
	);

	/* ================================================================ PRODUCTS */
	csp_group(
		'products',
		'Products Page',
		csp_loc_template( 'templates/products.php' ),
		array_merge(
			csp_banner_fields( 'products' ),
			array(
				csp_f_tab( 'Search & filters' ),
				csp_f_text( 'products_search_heading', 'Heading above search' ),
				csp_f_text( 'products_search_placeholder', 'Search placeholder' ),
				csp_f_text( 'products_all_label', '"All" tab label' ),
				csp_f_text( 'products_other_tab', '"Other products" tab label', array( 'instructions' => 'Clear to hide the tab and the "Other Food Products" block.' ) ),
				csp_f_text( 'products_no_results', '"No results" message' ),
				csp_f_text( 'products_nav_label', 'Category filter accessible label' ),
				csp_f_tab( 'Other food products' ),
				csp_f_text( 'products_other_heading', 'Heading' ),
				csp_f_area( 'products_other_text', 'Text', 4 ),
				csp_f_link( 'products_other_button', 'Button' ),
			)
		),
		0
	);

	/* ================================================================= CONTACT */
	csp_group(
		'contact',
		'Contact Page',
		csp_loc_template( 'templates/contact.php' ),
		array_merge(
			csp_banner_fields( 'contact' ),
			array(
				csp_f_tab( 'Form' ),
				csp_f_text( 'contact_form_heading', 'Heading' ),
				csp_f_area( 'contact_form_text', 'Text', 2 ),
				csp_f_text( 'contact_label_name', 'Label — Name' ),
				csp_f_text( 'contact_label_phone', 'Label — Phone' ),
				csp_f_text( 'contact_label_email', 'Label — Email' ),
				csp_f_text( 'contact_product_placeholder', 'Product dropdown — first option', array( 'instructions' => 'Default: General enquiry (no specific product)' ) ),
				csp_f_text( 'contact_packing_placeholder', 'Packing dropdown — first option', array( 'instructions' => 'Default: Select packing' ) ),
				csp_f_text( 'contact_summary_heading', 'Error summary heading', array( 'instructions' => 'Shown above the form after a failed submit. Default: Please check these fields' ) ),
				csp_f_text( 'contact_label_subject', 'Label — Subject' ),
				csp_f_text( 'contact_label_product', 'Label — Product' ),
				csp_f_text( 'contact_label_packing', 'Label — Packing' ),
				csp_f_text( 'contact_label_message', 'Label — Message' ),
				csp_f_text( 'contact_placeholder_name', 'Placeholder — Name' ),
				csp_f_text( 'contact_placeholder_phone', 'Placeholder — Phone' ),
				csp_f_text( 'contact_placeholder_email', 'Placeholder — Email' ),
				csp_f_text( 'contact_placeholder_subject', 'Placeholder — Subject' ),
				csp_f_text( 'contact_placeholder_message', 'Placeholder — Message' ),
				csp_f_text( 'contact_submit', 'Submit button label' ),
				csp_f_text( 'contact_sending', '"Sending…" label' ),
				csp_f_text( 'contact_success', 'Success message' ),
				csp_f_text( 'contact_error', 'Generic error message' ),
				csp_f_tab( 'Contact info' ),
				csp_f_text( 'contact_info_heading', 'Heading' ),
				csp_f_text( 'contact_label_phones', 'Label — Phones' ),
				csp_f_text( 'contact_label_call', 'Label — Call' ),
				csp_f_text( 'contact_label_whatsapp', 'Label — WhatsApp' ),
				csp_f_text( 'contact_label_mail', 'Label — Email block' ),
				csp_f_text( 'contact_label_location', 'Label — Location' ),
				csp_f_text( 'contact_label_hours', 'Label — Open hours' ),
				csp_f_tab( 'Map' ),
				csp_f_text( 'contact_map_heading', 'Heading' ),
				csp_f_area( 'contact_map_text', 'Text', 2 ),
				csp_f_text( 'contact_map_name', 'Location card title' ),
				csp_f_tab( 'Bottom banner' ),
				csp_f_img( 'contact_cta_image', 'Photo' ),
				csp_f_img( 'contact_cta_watermark', 'Watermark graphic (optional)' ),
				csp_f_area( 'contact_cta_heading', 'Heading', 3 ),
				csp_f_area( 'contact_cta_text', 'Text', 2 ),
				csp_f_link( 'contact_cta_button', 'Button', array( 'instructions' => 'Use #contact-form to scroll to the form.' ) ),
			)
		),
		0
	);

	/* ============================================================ LEGAL PAGES */
	foreach ( array(
		'privacy' => array( 'Privacy Policy Page', 'templates/privacy.php' ),
		'terms'   => array( 'Terms of Use Page', 'templates/terms.php' ),
	) as $p => $def ) {
		csp_group(
			'legal_' . $p,
			$def[0],
			csp_loc_template( $def[1] ),
			array_merge(
				csp_banner_fields( $p ),
				array(
					csp_f_tab( 'Document' ),
					csp_f_text( $p . '_toc_heading', 'Contents box heading' ),
					csp_f_text( $p . '_updated', 'Last-updated line' ),
					csp_f_area( $p . '_lead', 'Lead paragraph', 3 ),
					csp_f_rep(
						$p . '_sections',
						'Sections (numbered and listed in the contents box automatically)',
						array(
							csp_f_text( 'title', 'Section title' ),
							csp_f_wysiwyg( 'content', 'Content' ),
							csp_f_bool( 'card', 'Show company contact card below this section', array( 'instructions' => 'Company, email, phone and address are taken from Theme Settings.' ) ),
						),
						array( 'button_label' => 'Add section' )
					),
					csp_f_tab( 'Contact card labels' ),
					csp_f_text( $p . '_card_company', 'Label — Company' ),
					csp_f_text( $p . '_card_email', 'Label — Email' ),
					csp_f_text( $p . '_card_phone', 'Label — Phone' ),
					csp_f_text( $p . '_card_address', 'Label — Address' ),
					csp_f_tab( 'Also read' ),
					csp_f_text( $p . '_also_label', 'Small label' ),
					csp_f_link( $p . '_also_link', 'Link', array( 'instructions' => 'The link text is the bold title of the box.' ) ),
					csp_f_text( $p . '_also_text', 'Description' ),
				)
			),
			0
		);
	}

	/* ================================================================== INSIGHTS */
	csp_group(
		'blog',
		'Insights (Blog) Page',
		csp_loc_posts_page(),
		array_merge(
			array( csp_f_tab( 'Banner' ), csp_f_text( 'blog_banner_heading', 'Heading' ), csp_f_area( 'blog_banner_text', 'Intro text', 2 ) ),
			csp_video_hero_fields( 'blog' ),
			array(
				csp_f_tab( 'Articles grid' ),
				csp_f_text( 'blog_grid_heading', 'Heading' ),
				csp_f_tab( 'Trusted by' ),
				csp_f_text( 'blog_trusted_heading', 'Heading' ),
				csp_f_area( 'blog_trusted_text', 'Text', 3 ),
				csp_f_rep( 'blog_trusted_clients', 'Client names', array( csp_f_text( 'name', 'Name' ) ), array( 'layout' => 'table', 'button_label' => 'Add client', 'collapsed' => '' ) ),
			)
		),
		0
	);
}
