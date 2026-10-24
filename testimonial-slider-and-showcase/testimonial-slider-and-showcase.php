<?php
/**
 * Plugin Name: Testimonial Slider and Showcase 
 * Plugin URI: https://radiustheme.com
 * Description: Best Testimonial Slider and Showcase plugin for WordPress website. Testimonials Slider plugin is most customizable and developer friendly testimonial plugin to manage your customers testimonial.
 * Author: RadiusTheme
 * Version: 3.0.2
 * Author URI: https://radiustheme.com
 * Tested up to: 7.1
 * Requires PHP: 7.0
 * License: GPLv3
 * Tag: testimonials slider, testimonial, testimonials,testimonial slide, testimonial showcase, responsive testimonial, testimonial plugin
 * Text Domain: testimonial-slider-showcase
 * Domain Path: /languages
 *
 * @package RT_TSS
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

define( 'TSS_VERSION', '3.0.2' );
define( 'TSS_AUTHOR', 'RadiusTheme' );
define( 'EDD_TSS_STORE_URL', 'https://www.radiustheme.com' );
define( 'EDD_TSS_ITEM_ID', 11023 );
define( 'TSS_ITEM_NAME', 'Testimonial Slider And Showcase' );
define( 'TSS_PLUGIN_PATH', dirname( __FILE__ ) );
define( 'TSS_PLUGIN_ACTIVE_FILE_NAME', __FILE__ );
define( 'TSS_PLUGIN_URL', plugins_url( '', __FILE__ ) );
define( 'TSS_LANGUAGE_PATH', dirname( plugin_basename( __FILE__ ) ) . '/languages' );

/**
 * Oldest Pro release this free version works with.
 *
 * Free and Pro ship in lock-step. Raise this ONLY on a release that actually
 * breaks Pro, and raise RTTSP_MIN_FREE_VERSION in Pro to match.
 */
define( 'TSS_MIN_PRO_VERSION', '3.0.0' );

/**
 * Is the active Pro new enough to boot against this release?
 *
 * Pro's entry file loads before this one — WordPress sorts `active_plugins`,
 * and `<dir>-pro/` sorts before `<dir>/` — so RTTSP_VERSION is already defined
 * whenever Pro is active. Undefined means Pro is not active: nothing to block.
 *
 * @return bool
 */
function tss_is_pro_compatible() {
	return ! defined( 'RTTSP_VERSION' )
		|| version_compare( RTTSP_VERSION, TSS_MIN_PRO_VERSION, '>=' );
}

/**
 * Warn that the active Pro is too old for this release of the free plugin.
 */
add_action(
	'admin_notices',
	function () {
		if ( tss_is_pro_compatible() || ! current_user_can( 'update_plugins' ) ) {
			return;
		}

		printf(
			'<div class="notice notice-error"><p><strong>%1$s</strong> %2$s <a href="%3$s">%4$s</a></p></div>',
			esc_html__( 'Testimonial Slider and Showcase Pro update required.', 'testimonial-slider-showcase' ),
			sprintf(
				/* translators: 1: free version, 2: required Pro version, 3: installed Pro version. */
				esc_html__( 'Testimonial Slider and Showcase %1$s requires Pro %2$s or later. You are running Pro %3$s, so all Pro features are turned off.', 'testimonial-slider-showcase' ),
				esc_html( TSS_VERSION ),
				esc_html( TSS_MIN_PRO_VERSION ),
				esc_html( RTTSP_VERSION )
			),
			esc_url( self_admin_url( 'plugins.php' ) ),
			esc_html__( 'Go to Plugins', 'testimonial-slider-showcase' )
		);
	}
);

require 'lib/init.php';

