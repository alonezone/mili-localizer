<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Mili_Localizer_Processor {
    private static $is_internal_update = false;

    public function hooks() {
        add_action('transition_post_status', array($this, 'on_publish_transition'), 10, 3);
        add_action('post_updated', array($this, 'on_post_updated'), 10, 3);
    }

    public function on_publish_transition($new_status, $old_status, $post) {
        if ($new_status !== 'publish' || $old_status === 'publish') {
            return;
        }

        $options = Mili_Localizer_Options::get();
        if (empty($options['run_on_publish'])) {
            return;
        }

        $this->process_post_if_allowed($post);
    }

    public function on_post_updated($post_id, $post_after, $post_before) {
        if ($post_after->post_status !== 'publish' || $post_before->post_status !== 'publish') {
            return;
        }

        $options = Mili_Localizer_Options::get();
        if (empty($options['run_on_update'])) {
            return;
        }

        $this->process_post_if_allowed($post_after);
    }

    private function process_post_if_allowed($post) {
        if (self::$is_internal_update) {
            return;
        }

        if (!$post instanceof WP_Post) {
            return;
        }

        if (wp_is_post_revision($post->ID) || wp_is_post_autosave($post->ID)) {
            return;
        }

        if (!post_type_supports($post->post_type, 'editor')) {
            return;
        }

        $this->process_post((int) $post->ID);
    }

    private function process_post($post_id) {
        $post = get_post($post_id);
        if (!$post || trim((string) $post->post_content) === '') {
            return;
        }

        if (!preg_match_all('/<img[^>]*>/i', $post->post_content, $img_matches)) {
            return;
        }

        $options = Mili_Localizer_Options::get();
        $excluded_domains = Mili_Localizer_Options::excluded_domains();
        $quality = (int) $options['compression_quality'];

        $content = $post->post_content;
        $source_map = array();
        $first_attachment_id = 0;

        foreach ($img_matches[0] as $img_tag) {
            if (!preg_match('/\ssrc=(["\'])(.*?)\1/i', $img_tag, $src_match)) {
                continue;
            }

            $src_raw = $src_match[2];
            $src = html_entity_decode(trim($src_raw), ENT_QUOTES);
            $alt_text = $this->extract_image_alt($img_tag);

            if ($src === '' || !$this->is_external_url($src, $excluded_domains)) {
                continue;
            }

            if (!isset($source_map[$src])) {
                $source_map[$src] = $this->import_external_image($src, $post_id, $quality, $alt_text);
            }

            $import_data = $source_map[$src];
            if (empty($import_data['attachment_id']) || empty($import_data['url'])) {
                continue;
            }

            $new_url = $import_data['url'];
            $attachment_id = (int) $import_data['attachment_id'];
            if ($alt_text !== '') {
                $this->maybe_set_attachment_alt($attachment_id, $alt_text);
            }

            if ($first_attachment_id === 0) {
                $first_attachment_id = $attachment_id;
            }

            $updated_tag = str_replace($src_raw, $new_url, $img_tag);
            $content = str_replace($img_tag, $updated_tag, $content);
        }

        if ($content !== $post->post_content) {
            self::$is_internal_update = true;
            wp_update_post(
                array(
                    'ID' => $post_id,
                    'post_content' => $content,
                )
            );
            self::$is_internal_update = false;
        }

        if (!empty($options['set_first_as_featured']) && $first_attachment_id > 0 && !has_post_thumbnail($post_id)) {
            set_post_thumbnail($post_id, $first_attachment_id);
        }
    }

    private function import_external_image($url, $post_id, $quality, $alt_text = '') {
        if (!function_exists('download_url')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        if (!function_exists('media_handle_sideload')) {
            require_once ABSPATH . 'wp-admin/includes/media.php';
        }
        if (!function_exists('wp_generate_attachment_metadata')) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        $tmp_file = download_url($url, 30);
        if (is_wp_error($tmp_file)) {
            return array();
        }

        $this->compress_image($tmp_file, $quality);

        $path = wp_parse_url($url, PHP_URL_PATH);
        $filename = $path ? wp_basename($path) : '';

        if ($filename === '') {
            $filename = 'external-image-' . wp_generate_password(8, false) . '.jpg';
        }

        $file = array(
            'name' => sanitize_file_name($filename),
            'tmp_name' => $tmp_file,
        );

        $attachment_id = media_handle_sideload($file, $post_id);
        if (is_wp_error($attachment_id)) {
            @unlink($tmp_file);
            return array();
        }

        $attachment_url = wp_get_attachment_url($attachment_id);
        if (!$attachment_url) {
            return array();
        }

        if ($alt_text !== '') {
            $this->maybe_set_attachment_alt((int) $attachment_id, $alt_text);
        }

        return array(
            'attachment_id' => (int) $attachment_id,
            'url' => $attachment_url,
        );
    }

    private function compress_image($file_path, $quality) {
        $mime = wp_get_image_mime($file_path);
        if (!in_array($mime, array('image/jpeg', 'image/webp'), true)) {
            return;
        }

        $editor = wp_get_image_editor($file_path);
        if (is_wp_error($editor)) {
            return;
        }

        if (method_exists($editor, 'set_quality')) {
            $editor->set_quality($quality);
        }

        $editor->save($file_path);
    }

    private function is_external_url($url, $excluded_domains) {
        $parts = wp_parse_url($url);
        if (empty($parts['host'])) {
            return false;
        }

        $host = strtolower($parts['host']);

        if ($this->is_local_host($host)) {
            return false;
        }

        foreach ($excluded_domains as $domain) {
            if ($host === $domain || str_ends_with($host, '.' . $domain)) {
                return false;
            }
        }

        return true;
    }

    private function is_local_host($host) {
        $site_host = wp_parse_url(home_url(), PHP_URL_HOST);
        if (!$site_host) {
            return false;
        }

        $host = strtolower($host);
        $site_host = strtolower($site_host);

        if ($host === $site_host) {
            return true;
        }

        $host_nw = preg_replace('/^www\./', '', $host);
        $site_nw = preg_replace('/^www\./', '', $site_host);

        if ($host_nw === $site_nw) {
            return true;
        }

        if (str_ends_with($host, '.' . $site_nw) || str_ends_with($site_nw, '.' . $host)) {
            return true;
        }

        return false;
    }

    private function extract_image_alt($img_tag) {
        if (!preg_match('/\salt=(["\'])(.*?)\1/i', $img_tag, $alt_match)) {
            return '';
        }

        $alt = html_entity_decode(trim($alt_match[2]), ENT_QUOTES);
        return sanitize_text_field($alt);
    }

    private function maybe_set_attachment_alt($attachment_id, $alt_text) {
        if ($attachment_id <= 0 || $alt_text === '') {
            return;
        }

        $existing_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
        if ($existing_alt === '') {
            update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt_text);
        }
    }
}
