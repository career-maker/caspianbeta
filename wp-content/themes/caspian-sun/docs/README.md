# Caspian & Sun Food Trading — WordPress theme (`caspian-sun`)

1:1 conversion of the approved HTML site into a custom, ACF Pro–driven theme.
Pixel comparison against the HTML (Chromium, desktop 1440 / tablet 768 / mobile 390): **0.00 – 0.05 % differing pixels** on every page; Firefox and WebKit spot-checked.

## 1. Install & activate

1. WordPress 6.4+, PHP 8.0+. (Local site: `D:\wp sites\caspian`, http://caspian.local)
2. Install **Advanced Custom Fields PRO** (from `D:\abhiram\advanced-custom-fields-pro-main.zip`, v6.8.9) → Plugins → Activate. *ACF Free is not supported.*
3. Copy the `caspian-sun` folder to `wp-content/themes/` and activate it (Appearance → Themes).
4. Activation automatically imports **all HTML content, images, videos, menus and settings** (`inc/seed.php`, data in `seed/`). Re-run manually any time with
   `wp csp seed --force` (⚠ overwrites field values with the original HTML content).
5. Settings → Reading: untick **"Discourage search engines"** when the site goes live (it is ticked on staging, mirroring the original `noindex`).
6. Mail: add SMTP constants to `wp-config.php` (never in the theme) — see `inc/forms.php` (`CSP_SMTP_HOST/PORT/USER/PASS/SECURE/FROM`). Local uses Mailpit.
7. Optional: Theme Settings → Branding & Contact → enter Google reCAPTCHA v3 keys.

## 2. Managing content (non-developer guide)

| Where | What |
|---|---|
| **Pages → Home / About / Products / Contact / Insights / Privacy / Terms** | One field group per page, organised in tabs that follow the page top-to-bottom. No editor box – only structured fields. |
| **Products** | Add/edit products: title, category, card summary, description, card image, photo gallery, origin countries, packing options. Order with *Order* (Page attributes). |
| **Products → Categories** | Seafood / Poultry / Meat; "Display order" controls tabs and sections. |
| **Insights** | Articles: title, excerpt (card text), featured + listing image, author, body, references. Publish date is the article date. |
| **Theme Settings** | Branding & Contact (logo, favicon, phone, WhatsApp, email, address, hours, social, form delivery) · Header & Footer · Product Page · Article Page · 404 Page · SEO Defaults. |
| **Appearance → Menus** | Header left / right, footer Company, footer Products. |
| **Enquiries** | Every contact-form submission is stored here and e-mailed. |
| **SEO box** (pages, articles, products) | Title, description, share image; empty = sensible defaults. |

* Replace / remove an image: use the image field's pencil / ✕. Clearing a field hides that element (no broken images, no leftover defaults).
* Repeaters (cards, counters, logos, testimonials, steps…) can be added, reordered (drag) and deleted; empty repeaters hide their section.
* Buttons are ACF *Link* fields (label + URL + new-tab). No label or no URL → button not rendered.

## 3. Architecture

```
caspian-sun/
  functions.php  header.php  footer.php
  front-page.php home.php single.php single-product.php 404.php page.php index.php
  templates/     about · products · contact · privacy · terms
  template-parts/legal.php
  inc/           helpers · icons · setup · post-types · acf-lib · acf-options · acf-pages · acf-content · forms · seo · admin-cleanup · seed
  assets/        css (components + one stylesheet per page type) · js · fonts · images
  seed/          data/seed.json (generated from the HTML) · media/ (imported to the Media Library)
  docs/          README.md · FIELD-MAPPING.md · QA-CHECKLIST.md
```

* AGENTS.md rules honoured: every section title is `.section-heading` (+ modifiers); no tag-based heading CSS (the one `h1` selector in the products CSS was converted to `.search-row .section-heading`); no `min-height` added to content sections.
* Per-page CSS is the original page CSS, extracted unchanged except that background-image URLs became CSS custom properties (`--hero-bg`, `--cats-bg`, `--cta-bg`, `--testi-video-bg`) so those images are editable.
* Field groups are registered in code (version-controlled). The ACF admin builder is hidden; define `CSP_SHOW_ACF_ADMIN` to show it.

## 4. Remaining hardcoded content (by design)

| Item | Why |
|---|---|
| Icon artwork (check, truck, globe, social…) in `inc/icons.php` | Design assets; editors pick icons by name from a select list. |
| Country flag SVGs on the product *Origin* row (`csp_flag_svg`) | Same as HTML (inline SVG by country name); unknown countries show no flag. |
| Accessibility strings (aria-labels: "Open menu", "Close video", "Skip to content"…) | UI chrome, translatable with `__()`. |
| Contact-page map | The HTML has a styled placeholder (no live map/iframe) – kept as is. |
| Client-side validation messages in `assets/js/contact.js` and server messages in `inc/forms.php` | English only, matched by server-side checks. |
| Product-page "Contact Us" target | Always the Contact template page (URL is dynamic: product + packing). |

## 5. Deliberate differences from the HTML (bug fixes / required by WordPress)

* Header "Get In Touch" buttons were non-functional `<button>`s → now links (default → Contact page; editable).
* Contact CTA pointed to `#contact-form`, which did not exist → anchor added to the form.
* Home product cards had every "View more" aria-label reading "King Crab Leg" → correct per product.
* Blog share icons were `#` placeholders → real share URLs (X, Facebook, WhatsApp) + Instagram profile.
* Products page had two `<h1>` → second is now an `h2` (same look); article page `h1` is the article title (banner is a styled `div`).
* Contact form had no backend → full handler (nonce, honeypot, time trap, rate-limit, optional reCAPTCHA, stored + e-mailed).
* Product enquiry parameters renamed `enquiry_product` / `enquiry_size` (`?product=` collides with the product post-type query var).
* Terms page link `privacy-policy.html` → `/privacy-policy/`.
* New (not in HTML): 404 page, Enquiries, SEO meta/Open Graph/JSON-LD, sitemap (`/wp-sitemap.xml`), "No results" etc. editable labels.

## 6. Features not implemented / limitations

* Real-time map embed (not in HTML).
* Multilingual support (strings are translation-ready, no translation plugin added).
* Server-level protections (directory listing, `.git`/backup file blocking, HSTS, gzip/cache headers) belong in the web-server config (Local uses nginx; the repo `.htaccess` is Apache-only) – see Security in QA-CHECKLIST.
* Hero video autoplay is intentionally deferred until first interaction (as in the HTML).
