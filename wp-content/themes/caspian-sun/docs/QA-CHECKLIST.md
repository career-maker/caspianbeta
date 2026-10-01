# QA checklist — 2026-10-01 (local: http://caspian.local)

Method: automated Playwright (Chromium, Firefox, WebKit) + WP-CLI + admin UI walkthrough. Scripts: pixel diff vs original HTML, functional suite (29 checks), empty-state sweep, DOM validity sweep.

## Branding
| Check | Result |
|---|---|
| Favicon output from Theme Settings | PASS |
| Logo (header / drawer / footer / preloader) | PASS |
| Browser title per page (from original `<title>`) | PASS |
| Fonts (self-hosted woff2, `font-display:swap`) | PASS |

## Navigation
| Check | Result |
|---|---|
| Header + drawer (5 links) + footer menus from WP menus | PASS |
| Active-item highlight (incl. Insights on article, Products on product) | PASS |
| Breadcrumbs, anchors (`/products/#seafood`) | PASS |
| Crawl of internal links on 9 pages (30 URLs) – no 404 | PASS (1 broken legacy link fixed) |
| Carousel arrows / dots / drag / lightbox | PASS |
| Mobile drawer open/close, footer accordion | PASS |

## CMS / ACF
| Check | Result |
|---|---|
| All content prefilled (92 media, 7 pages, 14 products, 7 articles, 4 menus, options) | PASS |
| Every field referenced by a template (automated; see FIELD-MAPPING.md) | PASS |
| Edit via admin UI → frontend updates (hero heading) | PASS |
| Clear field via UI → element disappears (no default restored) | PASS |
| Remove image via UI → no broken `<img>` | PASS |
| **Empty-state sweep**: every field on every page/product/article/option cleared, 10 URLs fetched | PASS – HTTP 200, 0 empty `src`, 0 empty headings/hrefs, 0 PHP warnings |
| Repeaters add / delete / reorder / empty | PASS (ACF native; empty hides section) |
| No Classic/Gutenberg editor on pages, products, articles | PASS |
| Backend cleanup (comments, tags/categories, widgets, dashboard, "Add page") | PASS |

## Forms (contact)
| Check | Result |
|---|---|
| Empty / spaces-only / invalid e-mail / invalid phone / short message → inline errors | PASS |
| Valid submission incl. special chars & `<script>` text → success, form reset | PASS |
| Admin notification e-mail (Mailpit via SMTP constants) | PASS |
| Enquiry stored in Enquiries (escaped on display) | PASS |
| Missing nonce → 403; honeypot; time-trap; rate-limit (5 / 10 min / IP) | PASS (nonce tested; others by code review) |
| reCAPTCHA v3 | N/A (optional, off until keys entered; untested) |
| Visitor confirmation e-mail | N/A (option provided, off by default) |
| No-JS fallback (admin-post redirect) | PASS by code review, not browser-tested |

## Responsive
| Check | Result |
|---|---|
| Pixel parity 1440 / 768 / 390 (9 pages) | PASS (≤ 0.3 % diff; header on Contact ≈ 0.3 %: sub-pixel text) |
| No horizontal scroll | PASS |
| Firefox & WebKit (home, contact, product @390) | PASS |
| Real iOS Safari / Android Chrome devices | N/A (not available; WebKit/Chromium emulation only) |
| Light/dark theme | N/A (design has none) |

## SEO
| Check | Result |
|---|---|
| Unique title + meta description per page, single H1 | PASS |
| Canonical (incl. Insights listing), Open Graph, Twitter, JSON-LD | PASS |
| `/wp-sitemap.xml` (users/taxonomies removed), virtual robots.txt | PASS |
| Staging `noindex` follows *Discourage search engines* | PASS – **switch off at launch** |
| Image alt text from Media Library | PASS (0 images without alt attribute) |

## Quality
| Check | Result |
|---|---|
| Duplicate IDs / unlabeled inputs / broken images (10 URLs) | PASS (0) |
| PHP errors (debug log, all pages, admin screens) | PASS (1 warning found & fixed) |
| JS console (all pages) | PASS (only the intentional 404 request) |
| W3C validator / CSS validator | N/A (not run; original CSS reused unchanged) |
| Lighthouse / Core Web Vitals | N/A (not run; assets, lazy-loading, poster preload, deferred JS preserved from HTML) |
| Accessibility: skip link, focus rings, labels, `aria-*`, reduced-motion (from HTML) | PASS (manual review; no axe run) |

## Security
| Check | Result |
|---|---|
| Output escaped, input sanitised, nonce, capability (`manage_options` for settings) | PASS (code review) |
| No secrets in theme; SMTP via `wp-config.php` | PASS |
| XML-RPC off, user enumeration blocked (REST + `?author=`), file editor off, security headers | PASS |
| SVG upload admin-only with script/handler scan | PASS |
| HTTPS / SSL, directory listing, `.git`/backup blocking, brute-force protection, WP/plugin updates | N/A – server / hosting level (set up on production) |

## 404
PASS — editable banner/message/button, correct status, responsive (shares legal-page CSS), button goes home.

## Remaining defects / notes
1. Header on Contact page differs from the HTML by ~0.3 % pixels (anti-aliasing of header text) – cosmetic.
2. reCAPTCHA, visitor auto-reply and no-JS submit path are implemented but not exercised.
3. Production items to do at launch: SSL, un-tick *Discourage search engines*, SMTP credentials, server rules, real sitemap submission.
