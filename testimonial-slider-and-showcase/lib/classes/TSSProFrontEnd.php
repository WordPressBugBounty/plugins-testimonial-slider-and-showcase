<?php
/**
 * Frontend Class.
 *
 * @package RT_TSS
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

if ( ! class_exists( 'TSSProFrontEnd' ) ) :
	/**
	 * Frontend Class.
	 */
	class TSSProFrontEnd {
		/**
		 * Class constructor
		 */
		public function __construct() {
			add_action( 'wp_enqueue_scripts', [ $this, 'tss_front_end' ] );
		}

		/**
		 * Enqueue
		 *
		 * @return void
		 */
		public function tss_front_end() {
			wp_enqueue_style( 'tss' );

			$settings = get_option( TSSPro()->options['settings'] );

			if ( ! empty( $settings['custom_css'] ) ) {
				wp_add_inline_style( 'tss', esc_html( $settings['custom_css'] ) );
			}
            wp_register_script('facebook-sdk', 'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.5', [ 'jquery' ], '1.0.0', true);
            // Twitter Widgets
            wp_register_script('twitter-widgets', 'https://platform.twitter.com/widgets.js', [ 'jquery' ], '1.0.0', true);
            // Google Plus
            wp_register_script('google-plus', 'https://apis.google.com/js/platform.js', [ 'jquery' ], '1.0.0', true);
            // LinkedIn
            wp_register_script('linkedin-sdk', 'https://platform.linkedin.com/in.js', [ 'jquery' ], '1.0.0', true);
            // Add inline data for the script (e.g., lang: en_US)
            // Pinterest
            wp_register_script('pinterest-sdk', 'https://assets.pinterest.com/js/pinit.js', [ 'jquery' ], '1.0.0', true);
		}

	}
endif;
