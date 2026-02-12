# Mili Localizer

Automatically imports external images found in post content into your WordPress Media Library and rewrites image URLs in the post content.

## Features

- Imports external `<img>` sources on publish and/or update.
- Replaces external image URLs in post content with local Media Library URLs.
- Keeps existing `alt` attributes untouched in the content.
- Allows excluding specific domains from import.
- Lets you configure JPEG/WebP compression quality.
- Optionally sets the first imported image as featured image when no featured image exists.

## Installation

1. Copy this folder into `wp-content/plugins/`.
2. Go to **Plugins** in WordPress admin.
3. Activate **Mili Localizer**.

## Settings

Path: `Settings -> Mili Localizer`

- **Excluded Domains**: One domain per line (or comma-separated). Images from these domains are skipped.
- **Compression Quality**: Number from 10 to 100 for JPEG/WebP optimization.
- **Run On Publish**: Enable processing when a post is published.
- **Run On Update**: Enable processing when a published post is updated.
- **Set First Imported Image As Featured Image**: If enabled and no featured image is set, the first imported image becomes featured.

## Notes

- Only external image URLs are processed.
- Images already on your own site host are ignored.
- This plugin targets post types that support the editor.

## Changelog

### 1.0.0
- Initial release.
