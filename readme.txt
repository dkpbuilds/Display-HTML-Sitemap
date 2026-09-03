=== Display HTML Sitemap ===
Contributors: dkpbuilds
Tags: post type, custom post type, settings, sitemap, html sitemap
Donate link: https://www.paypal.me/obstinatedipak
Requires at least: 5.0
Requires PHP: 7.4
Tested up to: 7.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Display HTML Sitemap creates a beautiful, configurable HTML sitemap for your website. It supports Pages, Posts, and all Custom Post Types. You can reorder, rename, enable/disable post types, and exclude specific posts.

== Description ==
Display HTML Sitemap generates a clean hierarchical HTML sitemap. It supports all public post types and includes a settings page for full control.

= Available Shortcodes =

* `[display-html-sitemap]` — Basic usage (uses saved settings)
* `[display-html-sitemap orderby="menu_order" order="ASC" posts_per_page="-1" exclude=""]` — With parameters

**Shortcode Parameters** (all optional):

* `orderby` — `menu_order`, `title`, `date`, `modified`, `rand` (default: `menu_order`)
* `order` — `ASC` or `DESC` (default: `ASC`)
* `posts_per_page` / `number` — Number of items to show (`-1` = all, default: `-1`)
* `exclude` — Comma separated post IDs to hide (overrides global exclude setting)
* `hierarchical` — `1` (default) for nested tree, `0` for flat list

== Installation ==
1. Download the plugin and unzip it.
2. Upload the folder display-html-sitemap/ to your /wp-content/plugins/ folder.
3. Activate the plugin from your WordPress admin panel.
4. Installation finished.

== Frequently Asked Questions ==
= Does this plugin has a settings page? =
Yes, it does. You can choose post types, custom post types also rename them to be displayed on sitemap

= Does this plugin work with multi site? =
Yes, this plugin is compatible with multi site

= Does this plugin have any dependency? =
No, just with a installation of WordPress it works.

= Does this plugin supports custom post types ? =
Yes, it will list all custom post types for you on settings page. You can choose whether to keep all or not

== Screenshots ==
1. Settings page of HTML Sitemap
2. Shortcode to be used in Sitemap Page
3. Sitemap page displaying posts

== Upgrade Notice ==
= 1.2.1 =
* Fixed missing child posts when using hierarchical=0 flat list mode
* Fixed fatal error on admin settings page
* Fixed CSS layout: column header alignment and slug wrapping
* Fixed backward compatibility for get_option() returning false

== Changelog ==
= 1.2.1 =
* Fixed missing child posts when using hierarchical=0 flat list mode
* Fixed fatal error on admin settings page
* Fixed CSS layout: column header alignment and slug wrapping
* Fixed backward compatibility for get_option() returning false
= 1.2.0 =
* Added shortcode parameters (orderby, order, posts_per_page/number, exclude, hierarchical)
* Added global post exclude setting
* Renamed plugin to Display HTML Sitemap
* Requires PHP 7.4
* Tested up to WordPress 6.7
= 1.0.5 =
* UI Updates
= 1.0.4 =
* Fixed: Multiple blank UL tags removed
* Fixed: Admin screen broken layout
* Minor Improvements
= 1.0.3 =
* Fixed: Conflict when number of Custom Post Types exceeds 5
* Fixed: Wrong Post Type name on Frontend
* Minor Improvements
= 1.0.2 =
* Fixed: Longer Post Type Name Breaking Design
= 1.0.1 =
* Minor Improvements
= 1.0.0 =
* Intial realease
