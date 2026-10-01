<?php
/**
 * ACF field groups for repeatable content: products, articles, product categories.
 *
 * @package caspian-sun
 */

defined( 'ABSPATH' ) || exit;

add_action( 'acf/init', 'csp_register_content_groups' );
function csp_register_content_groups() {

	/* ---------------------------------------------------------------- Product */
	csp_group(
		'product',
		'Product Details',
		csp_loc_type( 'product' ),
		array(
			csp_f_area( 'card_summary', 'Card summary', 4, array( 'instructions' => 'Short text shown in the hover panel on product cards (home and Products page).' ) ),
			csp_f_area( 'description', 'Full description', 8, array( 'instructions' => 'Shown on the product page.' ) ),
			csp_f_img( 'card_image', 'Card image', array( 'instructions' => 'Image used on product cards and in related-product lists.' ) ),
			csp_f( 'gallery', 'gallery', 'Product page photos', array( 'return_format' => 'id', 'preview_size' => 'medium', 'library' => 'all', 'insert' => 'append', 'instructions' => 'First photo is the main image; extra photos appear as thumbnails.' ) ),
			csp_f_rep( 'origin', 'Origin (countries)', array( csp_f_text( 'country', 'Country', array( 'instructions' => 'A flag is shown automatically for known countries; for any other country upload one below.' ) ), csp_f_img( 'flag', 'Flag image (optional)', array( 'instructions' => 'Overrides the built-in flag. Square image recommended.' ) ) ), array( 'layout' => 'table', 'button_label' => 'Add country', 'collapsed' => '' ) ),
			csp_f_rep( 'packing', 'Packing options', array( csp_f_text( 'size', 'Packing option' ) ), array( 'layout' => 'table', 'button_label' => 'Add packing option', 'collapsed' => '' ) ),
		),
		0
	);

	/* --------------------------------------------------------------- Article */
	csp_group(
		'article',
		'Article Details',
		csp_loc_type( 'post' ),
		array(
			csp_f_img( 'article_image', 'Featured image', array( 'instructions' => 'Large image at the top of the article.' ) ),
			csp_f_img( 'article_card_image', 'Listing image', array( 'instructions' => 'Image used on the Insights page and in related-article lists.' ) ),
			csp_f_text( 'article_author', 'Author name' ),
			csp_f_area( 'article_summary', 'Article summary', 3, array( 'instructions' => 'Short text shown on article cards (home page, Insights page, related articles) and under the title. Leave empty to use the WordPress excerpt.' ) ),
			csp_f_wysiwyg( 'article_body', 'Article body', array( 'toolbar' => 'full', 'tabs' => 'all', 'media_upload' => 1 ) ),
			csp_f_rep(
				'article_refs',
				'References',
				array(
					csp_f_text( 'text', 'Reference title' ),
					csp_f_url( 'url', 'URL' ),
				),
				array( 'layout' => 'table', 'button_label' => 'Add reference', 'collapsed' => '' )
			),
		),
		0,
		array( 'hide_on_screen' => array( 'the_content', 'discussion', 'comments', 'author', 'format', 'featured_image', 'send-trackbacks' ) )
	);
	// The WordPress excerpt (post summary) is used as the article card text.

	/* ------------------------------------------------------ Product category */
	csp_group(
		'product_category',
		'Category display',
		csp_loc_taxonomy( 'product_category' ),
		array(
			csp_f_num( 'category_order', 'Display order', array( 'default_value' => 10, 'instructions' => 'Lower numbers appear first on the Products page.' ) ),
		),
		0,
		array( 'position' => 'normal', 'hide_on_screen' => array() )
	);
}
