<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Mili_Localizer_Options {
    public const OPTION_KEY = 'miel_options';

    public static function defaults() {
        return array(
            'excluded_domains' => '',
            'compression_quality' => 82,
            'run_on_publish' => 1,
            'run_on_update' => 1,
            'set_first_as_featured' => 0,
            'filename_mode' => 'preserve',
            'random_filename_pattern' => 'mili-{post_id}-{date}-{rand8}',
        );
    }

    public static function get() {
        return wp_parse_args(get_option(self::OPTION_KEY, array()), self::defaults());
    }

    public static function sanitize($input) {
        $defaults = self::defaults();
        $output = $defaults;

        $raw_domains = isset($input['excluded_domains']) ? wp_unslash($input['excluded_domains']) : '';
        $parts = preg_split('/[\r\n,]+/', (string) $raw_domains);
        $domains = array();

        foreach ($parts as $part) {
            $domain = self::sanitize_domain($part);
            if ($domain !== '') {
                $domains[] = $domain;
            }
        }

        $output['excluded_domains'] = implode("\n", array_values(array_unique($domains)));

        $quality = isset($input['compression_quality']) ? (int) $input['compression_quality'] : (int) $defaults['compression_quality'];
        $output['compression_quality'] = max(10, min(100, $quality));

        $output['run_on_publish'] = !empty($input['run_on_publish']) ? 1 : 0;
        $output['run_on_update'] = !empty($input['run_on_update']) ? 1 : 0;
        $output['set_first_as_featured'] = !empty($input['set_first_as_featured']) ? 1 : 0;

        $mode = isset($input['filename_mode']) ? sanitize_key($input['filename_mode']) : $defaults['filename_mode'];
        $output['filename_mode'] = in_array($mode, array('preserve', 'random'), true) ? $mode : $defaults['filename_mode'];

        $pattern = isset($input['random_filename_pattern']) ? wp_unslash($input['random_filename_pattern']) : $defaults['random_filename_pattern'];
        $output['random_filename_pattern'] = self::sanitize_filename_pattern($pattern);

        return $output;
    }

    public static function excluded_domains() {
        $options = self::get();
        $parts = preg_split('/[\r\n,]+/', (string) $options['excluded_domains']);
        $domains = array();

        foreach ($parts as $part) {
            $domain = self::sanitize_domain($part);
            if ($domain !== '') {
                $domains[] = $domain;
            }
        }

        return array_values(array_unique($domains));
    }

    private static function sanitize_domain($value) {
        $value = sanitize_text_field((string) $value);
        $value = strtolower(trim($value));
        $value = preg_replace('#^https?://#', '', $value);
        $value = trim($value, " \t\n\r\0\x0B/");

        // Keep only valid domain characters after generic text sanitization.
        $value = preg_replace('/[^a-z0-9\.\-]/', '', $value);

        return $value;
    }

    private static function sanitize_filename_pattern($value) {
        $value = sanitize_text_field((string) $value);
        $value = preg_replace('/[^a-zA-Z0-9\-\_\{\}\s]/', '', $value);
        $value = trim($value);

        if ($value === '') {
            return self::defaults()['random_filename_pattern'];
        }

        return $value;
    }
}
