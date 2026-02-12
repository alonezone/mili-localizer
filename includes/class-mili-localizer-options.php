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
            $part = trim(strtolower($part));
            $part = preg_replace('#^https?://#', '', $part);
            $part = trim($part, " \t\n\r\0\x0B/");
            if ($part !== '') {
                $domains[] = $part;
            }
        }

        $output['excluded_domains'] = implode("\n", array_values(array_unique($domains)));

        $quality = isset($input['compression_quality']) ? (int) $input['compression_quality'] : (int) $defaults['compression_quality'];
        $output['compression_quality'] = max(10, min(100, $quality));

        $output['run_on_publish'] = !empty($input['run_on_publish']) ? 1 : 0;
        $output['run_on_update'] = !empty($input['run_on_update']) ? 1 : 0;
        $output['set_first_as_featured'] = !empty($input['set_first_as_featured']) ? 1 : 0;

        return $output;
    }

    public static function excluded_domains() {
        $options = self::get();
        $parts = preg_split('/[\r\n,]+/', (string) $options['excluded_domains']);
        $domains = array();

        foreach ($parts as $part) {
            $part = trim(strtolower($part));
            $part = preg_replace('#^https?://#', '', $part);
            $part = trim($part, " \t\n\r\0\x0B/");
            if ($part !== '') {
                $domains[] = $part;
            }
        }

        return array_values(array_unique($domains));
    }
}
