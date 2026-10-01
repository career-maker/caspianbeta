# ACF field mapping

Generated from the registered field groups. *Where used* lists the template that reads the field.

## Branding & Contact

| Field name | Label | Type | Where used |
|---|---|---|---|
| **— Branding —** | | | |
| `opt_logo` | Logo | image | footer.php, header.php, seo.php, setup.php |
| `opt_favicon` | Favicon | image | setup.php |
| **— Contact details —** | | | |
| `opt_phone_display` | Phone — display text | text | footer.php, header.php, legal.php, contact.php |
| `opt_phone_tel` | Phone — dial number | text | footer.php, header.php, seo.php, legal.php, contact.php |
| `opt_whatsapp_display` | WhatsApp — display text | text | contact.php |
| `opt_whatsapp_number` | WhatsApp — number for wa.me link | text | contact.php |
| `opt_email` | Email address | email | footer.php, seo.php, legal.php, contact.php |
| `opt_address` | Address | textarea | footer.php, seo.php, legal.php, contact.php |
| `opt_hours` | Opening hours | textarea | contact.php |
| **— Social networks —** | | | |
| `opt_social` | Social links | repeater | footer.php, seo.php, single.php |
| **— Common labels —** | | | |
| `opt_breadcrumb_home` | Breadcrumb — "Home" label | text | helpers.php |
| `opt_view_more` | "View More" link label | text | front-page.php, home.php, single-product.php, single.php, products.php |
| **— Contact form delivery —** | | | |
| `opt_form_recipient` | Send enquiries to | email | forms.php |
| `opt_form_autoreply` | Send confirmation email to the visitor | true_false | forms.php |
| `opt_form_autoreply_subject` | Confirmation email — subject | text | forms.php |
| `opt_form_autoreply_body` | Confirmation email — message | textarea | forms.php |
| `opt_recaptcha_site` | Google reCAPTCHA v3 — site key | text | forms.php, setup.php |
| `opt_recaptcha_secret` | Google reCAPTCHA v3 — secret key | text | forms.php, setup.php |

## Header & Footer

| Field name | Label | Type | Where used |
|---|---|---|---|
| **— Header —** | | | |
| `opt_header_cta` | Header button ("Get In Touch") | link | header.php |
| `opt_header_phone_show` | Show phone number in header and mobile menu | true_false | header.php |
| **— Footer — headings & text —** | | | |
| `opt_footer_social_heading` | Social column heading | text | footer.php |
| `opt_footer_about_heading` | About column heading | text | footer.php |
| `opt_footer_about_text` | About text | textarea | footer.php |
| `opt_footer_company_heading` | Company column heading | text | footer.php |
| `opt_footer_products_heading` | Products column heading | text | footer.php |
| `opt_footer_contact_heading` | Contact column heading | text | footer.php |
| **— Footer — bottom bar —** | | | |
| `opt_copyright` | Copyright line | text | footer.php |
| `opt_legal_links` | Legal links | repeater | footer.php |
| `opt_credit_text` | Designer credit text | text | footer.php |
| `opt_credit_link` | Designer credit link | link | footer.php |
| `opt_credit_logo` | Designer credit logo | image | footer.php |

## Product Detail Page

| Field name | Label | Type | Where used |
|---|---|---|---|
| **— Banner —** | | | |
| `pd_banner_heading` | Banner heading | text | inc/helpers.php (csp_banner) |
| `pd_banner_text` | Banner text | textarea | inc/helpers.php (csp_banner) |
| `pd_banner_image` | Banner image | image | inc/helpers.php (csp_banner) |
| `pd_banner_image_mobile` | Banner image — mobile (optional) | image | inc/helpers.php (csp_banner) |
| **— Product panel —** | | | |
| `pd_badge` | Image badge text | text | single-product.php |
| `pd_origin_label` | "Origin" label | text | single-product.php |
| `pd_packing_label` | "Packing" label | text | single-product.php |
| `pd_packing_hint` | Packing hint (nothing selected) | text | single-product.php |
| `pd_packing_selected` | Packing hint (option selected) | text | single-product.php |
| `pd_enquire_label` | Enquiry button label | text | single-product.php |
| `pd_back_link` | Secondary button ("Back to Shop") | link | single-product.php |
| `pd_assurances` | Assurance badges | repeater | single-product.php |
| **— Related products —** | | | |
| `pd_related_heading` | Related products heading | text | single-product.php |
| `pd_related_link` | "View all" link | link | single-product.php |

## Article Detail Page

| Field name | Label | Type | Where used |
|---|---|---|---|
| **— Banner —** | | | |
| `bd_banner_heading` | Banner heading | text | inc/helpers.php (csp_banner) |
| `bd_banner_text` | Banner text | textarea | inc/helpers.php (csp_banner) |
| `bd_banner_image` | Banner image | image | inc/helpers.php (csp_banner) |
| `bd_banner_image_mobile` | Banner image — mobile (optional) | image | inc/helpers.php (csp_banner) |
| **— Labels —** | | | |
| `bd_by_label` | "By" label | text | single.php |
| `bd_reference_label` | "Reference:" label | text | single.php |
| `bd_share_label` | "Share with:" label | text | single.php |
| `bd_refs_heading` | References heading | text | single.php |
| `bd_related_heading` | Sidebar heading (related articles) | text | single.php |

## 404 Page

| Field name | Label | Type | Where used |
|---|---|---|---|
| `e404_banner_heading` | Banner heading | text | inc/helpers.php (csp_banner) |
| `e404_banner_text` | Banner text | textarea | inc/helpers.php (csp_banner) |
| `e404_banner_image` | Banner image | image | inc/helpers.php (csp_banner) |
| `e404_banner_image_mobile` | Banner image — mobile (optional) | image | inc/helpers.php (csp_banner) |
| `e404_heading` | Message heading | text | 404.php |
| `e404_text` | Message text | textarea | 404.php |
| `e404_button` | Button | link | 404.php |

## SEO Defaults

| Field name | Label | Type | Where used |
|---|---|---|---|
| `seo_site_name` | Site name | text | seo.php |
| `seo_default_description` | Default meta description | textarea | seo.php |
| `seo_default_image` | Default social-sharing image | image | seo.php |
| `seo_org_name` | Organisation legal name (structured data) | text | seo.php, legal.php |
| `seo_org_founded` | Year founded (structured data) | text | seo.php |

## SEO

| Field name | Label | Type | Where used |
|---|---|---|---|
| `seo_title` | SEO title | text | seo.php |
| `seo_description` | Meta description | textarea | seo.php |
| `seo_image` | Social-sharing image | image | seo.php |

## SEO

| Field name | Label | Type | Where used |
|---|---|---|---|
| `seo_title` | SEO title | text | seo.php |
| `seo_description` | Meta description | textarea | seo.php |
| `seo_image` | Social-sharing image | image | seo.php |

## SEO

| Field name | Label | Type | Where used |
|---|---|---|---|
| `seo_title` | SEO title | text | seo.php |
| `seo_description` | Meta description | textarea | seo.php |
| `seo_image` | Social-sharing image | image | seo.php |

## Home Page

| Field name | Label | Type | Where used |
|---|---|---|---|
| **— Hero —** | | | |
| `home_hero_heading` | Heading | textarea | front-page.php |
| `home_hero_text` | Text | textarea | front-page.php |
| `home_hero_button` | Button | link | front-page.php |
| `home_hero_poster` | Poster image | image | front-page.php, setup.php |
| `home_hero_poster_mobile` | Poster image — small (optional) | image | front-page.php |
| `home_hero_video` | Background video (desktop) | file | front-page.php |
| `home_hero_video_mobile` | Background video (mobile) | file | front-page.php |
| **— About —** | | | |
| `home_about_eyebrow` | Eyebrow | text | front-page.php |
| `home_about_heading` | Heading | textarea | front-page.php |
| `home_about_paras` | Paragraphs | repeater | front-page.php |
| `home_about_button` | Button | link | front-page.php |
| `home_about_image` | Photo | image | front-page.php |
| `home_counters` | Counters | repeater | front-page.php |
| **— Categories —** | | | |
| `home_cats_eyebrow` | Eyebrow | text | front-page.php |
| `home_cats_heading` | Heading | textarea | front-page.php |
| `home_cats_button` | Button | link | front-page.php |
| `home_cats_bg` | Section background image | image | front-page.php |
| `home_cats` | Category cards | repeater | front-page.php |
| **— Why Choose Us —** | | | |
| `home_why_heading` | Heading | textarea | front-page.php |
| `home_why_text` | Text | textarea | front-page.php |
| `home_why_items` | Feature columns | repeater | front-page.php |
| `home_why_video_image` | Video cover image | image | front-page.php |
| `home_why_video` | Video file | file | front-page.php |
| **— Clients —** | | | |
| `home_clients_heading` | Heading | textarea | front-page.php |
| `home_clients_text` | Text | textarea | front-page.php |
| `home_clients_button` | Button | link | front-page.php |
| `home_client_logos` | Client logos | repeater | front-page.php |
| **— Products —** | | | |
| `home_products_eyebrow` | Eyebrow | text | front-page.php |
| `home_products_heading` | Heading | textarea | front-page.php |
| `home_products_text` | Text | textarea | front-page.php |
| `home_products` | Featured products | relationship | front-page.php |
| `home_products_button` | Button | link | front-page.php |
| **— Testimonials —** | | | |
| `home_testi_image` | Large photo | image | front-page.php |
| `home_testi_avatars` | Avatar cluster image | image | front-page.php |
| `home_testi_count` | Counter value | text | front-page.php |
| `home_testi_count_label` | Counter label | text | front-page.php |
| `home_testi_eyebrow` | Eyebrow | text | front-page.php |
| `home_testi_heading` | Heading | textarea | front-page.php |
| `home_testimonials` | Testimonials | repeater | front-page.php |
| **— Articles —** | | | |
| `home_articles_eyebrow` | Eyebrow | text | front-page.php |
| `home_articles_heading` | Heading | textarea | front-page.php |
| `home_articles` | Articles (1st = large, 2nd–3rd = middle column, 4th–5th = right column) | repeater | front-page.php |
| **— Contact banner —** | | | |
| `home_cta_eyebrow` | Eyebrow | text | front-page.php |
| `home_cta_heading` | Heading | textarea | front-page.php |
| `home_cta_text` | Text | textarea | front-page.php |
| `home_cta_button` | Button | link | front-page.php |
| `home_cta_image` | Background image | image | front-page.php |

## About Page

| Field name | Label | Type | Where used |
|---|---|---|---|
| **— Banner —** | | | |
| `about_banner_heading` | Heading | text | inc/helpers.php (csp_banner) |
| `about_banner_text` | Intro text | textarea | inc/helpers.php (csp_banner) |
| `about_banner_image` | Background image | image | inc/helpers.php (csp_banner) |
| `about_banner_image_mobile` | Background image — mobile (optional) | image | inc/helpers.php (csp_banner) |
| **— Overview —** | | | |
| `about_ov_eyebrow` | Eyebrow | text | about.php |
| `about_ov_heading` | Heading | text | about.php |
| `about_ov_paras` | Paragraphs | repeater | about.php |
| `about_ov_image_back` | Image — large (back) | image | about.php |
| `about_ov_image_front` | Image — tall (front) | image | about.php |
| **— Services —** | | | |
| `about_svc_eyebrow` | Eyebrow | text | about.php |
| `about_svc_heading` | Heading | text | about.php |
| `about_services` | Service cards | repeater | about.php |
| **— Sourcing expertise —** | | | |
| `about_str_image` | Image | image | about.php |
| `about_str_eyebrow` | Eyebrow | text | about.php |
| `about_str_heading` | Heading | text | about.php |
| `about_str_intro` | Intro paragraph | textarea | about.php |
| `about_str_lead` | List lead-in | text | about.php |
| `about_str_list` | Checklist | repeater | about.php |
| `about_str_closing` | Closing paragraph | textarea | about.php |
| **— International network —** | | | |
| `about_net_eyebrow` | Eyebrow | text | about.php |
| `about_net_heading` | Heading | text | about.php |
| `about_net_intro` | Intro | textarea | about.php |
| `about_net_items` | Locations | repeater | about.php |
| **— Vision —** | | | |
| `about_vis_image` | Background image | image | about.php |
| `about_vis_eyebrow` | Eyebrow | text | about.php |
| `about_vis_heading` | Heading | text | about.php |
| `about_vis_text` | Text | textarea | about.php |
| **— Mission —** | | | |
| `about_mis_image` | Image | image | about.php |
| `about_mis_eyebrow` | Eyebrow | text | about.php |
| `about_mis_heading` | Heading | text | about.php |
| `about_mis_paras` | Paragraphs | repeater | about.php |
| **— Strategic goal —** | | | |
| `about_goal_eyebrow` | Eyebrow | text | about.php |
| `about_goal_heading` | Heading | text | about.php |
| `about_goal_paras` | Paragraphs | repeater | about.php |
| **— How ordering works —** | | | |
| `about_ord_eyebrow` | Eyebrow | text | about.php |
| `about_ord_heading` | Heading | text | about.php |
| `about_ord_intro` | Intro | textarea | about.php |
| `about_ord_steps` | Steps (numbered automatically) | repeater | about.php |
| `about_lt_heading` | Lead-time heading | text | about.php |
| `about_lt_items` | Lead-time items | repeater | about.php |
| **— Sourcing countries —** | | | |
| `about_src_eyebrow` | Eyebrow | text | about.php |
| `about_src_heading` | Heading | text | about.php |
| `about_src_intro` | Intro | textarea | about.php |
| `about_src_countries` | Countries | repeater | about.php |
| `about_src_more` | "More regions" label | text | about.php |
| `about_src_closing` | Closing paragraph | textarea | about.php |
| **— Industries —** | | | |
| `about_ind_eyebrow` | Eyebrow | text | about.php |
| `about_ind_heading` | Heading | text | about.php |
| `about_ind_intro` | Intro | textarea | about.php |
| `about_ind_list` | Industry list | repeater | about.php |
| `about_ind_clients_text` | Clients paragraph | textarea | about.php |
| `about_ind_chips` | Client names | repeater | about.php |
| **— Why choose us —** | | | |
| `about_why_eyebrow` | Eyebrow | text | about.php |
| `about_why_heading` | Heading | text | about.php |
| `about_why_list` | Reasons | repeater | about.php |

## Products Page

| Field name | Label | Type | Where used |
|---|---|---|---|
| **— Banner —** | | | |
| `products_banner_heading` | Heading | text | inc/helpers.php (csp_banner) |
| `products_banner_text` | Intro text | textarea | inc/helpers.php (csp_banner) |
| `products_banner_image` | Background image | image | inc/helpers.php (csp_banner) |
| `products_banner_image_mobile` | Background image — mobile (optional) | image | inc/helpers.php (csp_banner) |
| **— Search & filters —** | | | |
| `products_search_heading` | Heading above search | text | products.php |
| `products_search_placeholder` | Search placeholder | text | products.php |
| `products_all_label` | "All" tab label | text | products.php |
| `products_other_tab` | "Other products" tab label | text | products.php |
| `products_no_results` | "No results" message | text | products.php |
| `products_nav_label` | Category filter accessible label | text | products.php |
| **— Other food products —** | | | |
| `products_other_heading` | Heading | text | products.php |
| `products_other_text` | Text | textarea | products.php |
| `products_other_button` | Button | link | products.php |

## Contact Page

| Field name | Label | Type | Where used |
|---|---|---|---|
| **— Banner —** | | | |
| `contact_banner_heading` | Heading | text | inc/helpers.php (csp_banner) |
| `contact_banner_text` | Intro text | textarea | inc/helpers.php (csp_banner) |
| `contact_banner_image` | Background image | image | inc/helpers.php (csp_banner) |
| `contact_banner_image_mobile` | Background image — mobile (optional) | image | inc/helpers.php (csp_banner) |
| **— Form —** | | | |
| `contact_form_heading` | Heading | text | contact.php |
| `contact_form_text` | Text | textarea | contact.php |
| `contact_label_name` | Label — Name | text | contact.php |
| `contact_label_phone` | Label — Phone | text | contact.php |
| `contact_label_email` | Label — Email | text | contact.php |
| `contact_label_message` | Label — Message | text | contact.php |
| `contact_placeholder_name` | Placeholder — Name | text | contact.php |
| `contact_placeholder_phone` | Placeholder — Phone | text | contact.php |
| `contact_placeholder_email` | Placeholder — Email | text | contact.php |
| `contact_placeholder_message` | Placeholder — Message | text | contact.php |
| `contact_submit` | Submit button label | text | contact.php |
| `contact_sending` | "Sending…" label | text | contact.php |
| `contact_success` | Success message | text | forms.php, contact.php |
| `contact_error` | Generic error message | text | forms.php, contact.php |
| **— Contact info —** | | | |
| `contact_info_heading` | Heading | text | contact.php |
| `contact_label_phones` | Label — Phones | text | contact.php |
| `contact_label_call` | Label — Call | text | contact.php |
| `contact_label_whatsapp` | Label — WhatsApp | text | contact.php |
| `contact_label_mail` | Label — Email block | text | contact.php |
| `contact_label_location` | Label — Location | text | contact.php |
| `contact_label_hours` | Label — Open hours | text | contact.php |
| **— Map —** | | | |
| `contact_map_heading` | Heading | text | contact.php |
| `contact_map_text` | Text | textarea | contact.php |
| `contact_map_name` | Location card title | text | contact.php |
| **— Bottom banner —** | | | |
| `contact_cta_image` | Photo | image | contact.php |
| `contact_cta_watermark` | Watermark graphic (optional) | image | contact.php |
| `contact_cta_heading` | Heading | textarea | contact.php |
| `contact_cta_text` | Text | textarea | contact.php |
| `contact_cta_button` | Button | link | contact.php |

## Privacy Policy Page

| Field name | Label | Type | Where used |
|---|---|---|---|
| **— Banner —** | | | |
| `privacy_banner_heading` | Heading | text | inc/helpers.php (csp_banner) |
| `privacy_banner_text` | Intro text | textarea | inc/helpers.php (csp_banner) |
| `privacy_banner_image` | Background image | image | inc/helpers.php (csp_banner) |
| `privacy_banner_image_mobile` | Background image — mobile (optional) | image | inc/helpers.php (csp_banner) |
| **— Document —** | | | |
| `privacy_toc_heading` | Contents box heading | text | legal.php |
| `privacy_updated` | Last-updated line | text | legal.php |
| `privacy_lead` | Lead paragraph | textarea | legal.php |
| `privacy_sections` | Sections (numbered and listed in the contents box automatically) | repeater | legal.php |
| **— Contact card labels —** | | | |
| `privacy_card_company` | Label — Company | text | legal.php |
| `privacy_card_email` | Label — Email | text | legal.php |
| `privacy_card_phone` | Label — Phone | text | legal.php |
| `privacy_card_address` | Label — Address | text | legal.php |
| **— Also read —** | | | |
| `privacy_also_label` | Small label | text | legal.php |
| `privacy_also_link` | Link | link | legal.php |
| `privacy_also_text` | Description | text | legal.php |

## Terms of Use Page

| Field name | Label | Type | Where used |
|---|---|---|---|
| **— Banner —** | | | |
| `terms_banner_heading` | Heading | text | inc/helpers.php (csp_banner) |
| `terms_banner_text` | Intro text | textarea | inc/helpers.php (csp_banner) |
| `terms_banner_image` | Background image | image | inc/helpers.php (csp_banner) |
| `terms_banner_image_mobile` | Background image — mobile (optional) | image | inc/helpers.php (csp_banner) |
| **— Document —** | | | |
| `terms_toc_heading` | Contents box heading | text | legal.php |
| `terms_updated` | Last-updated line | text | legal.php |
| `terms_lead` | Lead paragraph | textarea | legal.php |
| `terms_sections` | Sections (numbered and listed in the contents box automatically) | repeater | legal.php |
| **— Contact card labels —** | | | |
| `terms_card_company` | Label — Company | text | legal.php |
| `terms_card_email` | Label — Email | text | legal.php |
| `terms_card_phone` | Label — Phone | text | legal.php |
| `terms_card_address` | Label — Address | text | legal.php |
| **— Also read —** | | | |
| `terms_also_label` | Small label | text | legal.php |
| `terms_also_link` | Link | link | legal.php |
| `terms_also_text` | Description | text | legal.php |

## Insights (Blog) Page

| Field name | Label | Type | Where used |
|---|---|---|---|
| **— Banner —** | | | |
| `blog_banner_heading` | Heading | text | home.php |
| `blog_banner_text` | Intro text | textarea | home.php |
| `blog_hero_poster` | Poster image | image | home.php |
| `blog_hero_poster_mobile` | Poster image — small (optional) | image | home.php |
| `blog_hero_video` | Background video (desktop) | file | home.php |
| `blog_hero_video_mobile` | Background video (mobile) | file | home.php |
| **— Articles grid —** | | | |
| `blog_grid_heading` | Heading | text | home.php |
| **— Trusted by —** | | | |
| `blog_trusted_heading` | Heading | text | home.php |
| `blog_trusted_text` | Text | textarea | home.php |
| `blog_trusted_clients` | Client names | repeater | home.php |

## Product Details

| Field name | Label | Type | Where used |
|---|---|---|---|
| `card_summary` | Card summary | textarea | front-page.php, seo.php, products.php |
| `description` | Full description | textarea | seo.php, single-product.php |
| `card_image` | Card image | image | front-page.php, home.php, seo.php, single-product.php, single.php, products.php |
| `gallery` | Product page photos | gallery | seo.php, setup.php, single-product.php |
| `origin` | Origin (countries) | repeater | setup.php, single-product.php |
| `packing` | Packing options | repeater | single-product.php |

## Article Details

| Field name | Label | Type | Where used |
|---|---|---|---|
| `article_image` | Featured image | image | seo.php, single.php |
| `article_card_image` | Listing image | image | front-page.php, home.php, single.php |
| `article_author` | Author name | text | seo.php, single.php |
| `article_body` | Article body | wysiwyg | single.php |
| `article_refs` | References | repeater | single.php |

## Category display

| Field name | Label | Type | Where used |
|---|---|---|---|
| `category_order` | Display order | number | products.php |
