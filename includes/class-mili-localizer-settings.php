<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Mili_Localizer_Settings {
    public const SETTINGS_SLUG = 'miel-settings';

    public function hooks() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function add_settings_page() {
        add_options_page(
            'Mili Localizer',
            'Mili Localizer',
            'manage_options',
            self::SETTINGS_SLUG,
            array($this, 'render_settings_page')
        );
    }

    public function register_settings() {
        register_setting(
            'miel_settings_group',
            Mili_Localizer_Options::OPTION_KEY,
            array(
                'sanitize_callback' => array('Mili_Localizer_Options', 'sanitize'),
            )
        );

        add_settings_section('miel_main', 'General Settings', '__return_false', self::SETTINGS_SLUG);

        add_settings_field('excluded_domains', 'Excluded Domains', array($this, 'render_excluded_domains_field'), self::SETTINGS_SLUG, 'miel_main');
        add_settings_field('compression_quality', 'Compression Quality (JPEG/WebP)', array($this, 'render_quality_field'), self::SETTINGS_SLUG, 'miel_main');
        add_settings_field('run_on_publish', 'Run On Publish', array($this, 'render_run_on_publish_field'), self::SETTINGS_SLUG, 'miel_main');
        add_settings_field('run_on_update', 'Run On Update', array($this, 'render_run_on_update_field'), self::SETTINGS_SLUG, 'miel_main');
        add_settings_field('set_first_as_featured', 'Set First Imported Image As Featured Image', array($this, 'render_set_featured_field'), self::SETTINGS_SLUG, 'miel_main');
        add_settings_field('filename_mode', 'Image File Naming', array($this, 'render_filename_mode_field'), self::SETTINGS_SLUG, 'miel_main');
        add_settings_field('random_filename_pattern', 'Random Filename Pattern', array($this, 'render_filename_pattern_field'), self::SETTINGS_SLUG, 'miel_main');
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1>Mili Localizer</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('miel_settings_group');
                do_settings_sections(self::SETTINGS_SLUG);
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function render_excluded_domains_field() {
        $options = Mili_Localizer_Options::get();
        ?>
        <textarea name="<?php echo esc_attr(Mili_Localizer_Options::OPTION_KEY); ?>[excluded_domains]" rows="6" class="large-text code"><?php echo esc_textarea($options['excluded_domains']); ?></textarea>
        <p class="description">One domain per line or comma-separated. Example: cdn.example.com</p>
        <?php
    }

    public function render_quality_field() {
        $options = Mili_Localizer_Options::get();
        ?>
        <input type="number" min="10" max="100" step="1" name="<?php echo esc_attr(Mili_Localizer_Options::OPTION_KEY); ?>[compression_quality]" value="<?php echo esc_attr((string) $options['compression_quality']); ?>" />
        <p class="description">Used for JPEG and WebP recompression after download.</p>
        <?php
    }

    public function render_run_on_publish_field() {
        $options = Mili_Localizer_Options::get();
        ?>
        <label>
            <input type="checkbox" name="<?php echo esc_attr(Mili_Localizer_Options::OPTION_KEY); ?>[run_on_publish]" value="1" <?php checked(1, (int) $options['run_on_publish']); ?> />
            Process posts when they are published.
        </label>
        <?php
    }

    public function render_run_on_update_field() {
        $options = Mili_Localizer_Options::get();
        ?>
        <label>
            <input type="checkbox" name="<?php echo esc_attr(Mili_Localizer_Options::OPTION_KEY); ?>[run_on_update]" value="1" <?php checked(1, (int) $options['run_on_update']); ?> />
            Process posts when already-published posts are updated.
        </label>
        <?php
    }

    public function render_set_featured_field() {
        $options = Mili_Localizer_Options::get();
        ?>
        <label>
            <input type="checkbox" name="<?php echo esc_attr(Mili_Localizer_Options::OPTION_KEY); ?>[set_first_as_featured]" value="1" <?php checked(1, (int) $options['set_first_as_featured']); ?> />
            If no featured image exists, use the first imported image as featured image.
        </label>
        <?php
    }

    public function render_filename_mode_field() {
        $options = Mili_Localizer_Options::get();
        ?>
        <select name="<?php echo esc_attr(Mili_Localizer_Options::OPTION_KEY); ?>[filename_mode]">
            <option value="preserve" <?php selected('preserve', $options['filename_mode']); ?>>Preserve original filename</option>
            <option value="random" <?php selected('random', $options['filename_mode']); ?>>Use random pattern</option>
        </select>
        <p class="description">Choose whether imported images keep original names or use your custom random pattern.</p>
        <?php
    }

    public function render_filename_pattern_field() {
        $options = Mili_Localizer_Options::get();
        ?>
        <input type="text" class="regular-text code" name="<?php echo esc_attr(Mili_Localizer_Options::OPTION_KEY); ?>[random_filename_pattern]" value="<?php echo esc_attr($options['random_filename_pattern']); ?>" />
        <p class="description">Used only in random mode. Tokens: {post_id}, {date}, {time}, {datetime}, {rand4}, {rand8}, {uniqid}</p>
        <?php
    }
}
