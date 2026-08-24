<?php
	if (!defined('ABSPATH')) {
		exit; // Exit if accessed directly
	}
	if (!class_exists('ABP_Hooks')) {
		class ABP_Hooks {
			public function __construct() {

			}

		}
		new ABP_Hooks();
	}