=== Mili Localizer ===
Contributors: militools
Tags: images, media, import, external images, featured image
Requires at least: 4.7
Tested up to: 6.9
Requires PHP: 7.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatically imports external images from post content into the WordPress Media Library and rewrites image URLs.

== Description ==

Mili Localizer scans post content on publish and/or update. If it finds external image URLs, it downloads those images, uploads them to the Media Library, and replaces the original image URLs in the post content.

Key capabilities:

* Import external `<img>` images.
* Rewrite content image URLs to local uploads.
* Preserve `alt` attributes in content.
* Exclude specific domains from import.
* Configure JPEG/WebP compression quality.
* Optionally set first imported image as featured image if none exists.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to `Settings -> Mili Localizer`.
4. Configure options and save.

== Frequently Asked Questions ==

= Does it process images already hosted on my site? =

No. It skips images that are already on your own domain.

= Does it keep the image alt text? =

Yes. The plugin only replaces image URLs and keeps existing `alt` attributes untouched.

= Can I skip some external domains? =

Yes. Add domains in the Excluded Domains setting.

== Changelog ==

= 1.0.0 =
* Initial release.
