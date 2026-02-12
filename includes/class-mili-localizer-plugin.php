<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Mili_Localizer_Plugin {
    private $settings;
    private $processor;

    public function __construct() {
        $this->settings = new Mili_Localizer_Settings();
        $this->processor = new Mili_Localizer_Processor();

        $this->settings->hooks();
        $this->processor->hooks();
    }
}
