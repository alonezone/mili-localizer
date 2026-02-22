<?php
/*
Plugin Name: Mili Localizer
Description: Downloads external images in post content into the Media Library and replaces their URLs in content.
Version: 1.0.0
Author: Mili
License: GPL-2.0-or-later
Text Domain: mili-localizer
Plugin URI: https://github.com/alonezone/mili-localizer
Author URI: https://github.com/alonezone
*/

if (!defined('ABSPATH')) {
    exit;
}

define('MILI_LOCALIZER_VERSION', '1.0.0');
define('MILI_LOCALIZER_PLUGIN_FILE', __FILE__);
define('MILI_LOCALIZER_PLUGIN_DIR', plugin_dir_path(__FILE__));

require_once MILI_LOCALIZER_PLUGIN_DIR . 'includes/class-mili-localizer-options.php';
require_once MILI_LOCALIZER_PLUGIN_DIR . 'includes/class-mili-localizer-settings.php';
require_once MILI_LOCALIZER_PLUGIN_DIR . 'includes/class-mili-localizer-processor.php';
require_once MILI_LOCALIZER_PLUGIN_DIR . 'includes/class-mili-localizer-plugin.php';

new Mili_Localizer_Plugin();
