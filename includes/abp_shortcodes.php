<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    if (!class_exists('ABP_Shortcodes')) {
        class ABP_Shortcodes {
            public function __construct() {

            }

        }
        new ABP_Shortcodes();
    }