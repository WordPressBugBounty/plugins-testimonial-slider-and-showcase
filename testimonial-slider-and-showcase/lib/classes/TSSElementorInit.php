<?php
/**
 * Elementor Init class.
 *
 * @package RT_TSS
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

if ( ! class_exists( 'TSSElementorInit' ) ) :
	/**
	 * Elementor Init class.
	 */
	class TSSElementorInit {
		/**
		 * Elementor Widgets
		 *
		 * @var [type]
		 */
		public $elementorWidgets;

		/**
		 * Class Constructor.
		 */
		public function __construct() {
			// Wait for `init`, as Team does: this class is built while the plugin
			// file loads, and on multisite a network-activated TSS loads before a
			// site-activated Elementor, so checking for Elementor here dropped
			// every widget. By `init` every plugin has loaded.
			add_action( 'init', [ $this, 'initElementor' ], 0 );
		}

		/**
		 * Hooks into Elementor, if it is active.
		 *
		 * @return void
		 */
		public function initElementor() {
			if ( ! did_action( 'elementor/loaded' ) ) {
				return;
			}

			add_action( 'elementor/widgets/register', [ $this, 'registerWidgets' ] );
			add_action( 'elementor/controls/register', [ $this, 'registerControls' ] );
			add_action( 'elementor/elements/categories_registered', [ $this, 'addCategory' ] );
			add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'editorStyles' ] );
			add_filter( 'elementor/editor/localize_settings', [ $this, 'promotePremiumWidgets' ] );
			add_action( 'wp_enqueue_scripts', [ $this, 'previewScriptData' ], 100 );
		}

		/**
		 * Prints the `tss` AJAX settings in the editor preview.
		 *
		 * The widgets localize `tss` while they render, but a widget dropped into
		 * the editor renders over AJAX after the preview has loaded, so on a page
		 * with no TSS widget yet `tss` was never printed and every AJAX action of
		 * the dropped widget (pagination, Load More, filter refill) threw
		 * "tss is not defined" until the page was saved and reloaded.
		 *
		 * @return void
		 */
		public function previewScriptData() {
			if ( ! did_action( 'elementor/loaded' ) || ! \Elementor\Plugin::$instance->preview->is_preview_mode() || ! wp_script_is( 'tss', 'registered' ) ) {
				return;
			}

			if ( in_array( 'sitepress-multilingual-cms/sitepress.php', get_option( 'active_plugins' ) ) ) {
				$ajaxurl = admin_url( 'admin-ajax.php?lang=' . ICL_LANGUAGE_CODE );
			} else {
				$ajaxurl = admin_url( 'admin-ajax.php' );
			}

			wp_localize_script(
				'tss',
				'tss',
				[
					'ajaxurl' => esc_url( $ajaxurl ),
					'nonce'   => esc_attr( wp_create_nonce( TSSPro()->nonceText() ) ),
					'nonceId' => esc_attr( TSSPro()->nonceId() ),
				]
			);
		}

		/**
		 * Register widgets
		 *
		 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
		 * @return void
		 */
		public function registerWidgets( $widgets_manager ) {
			$this->includes();

			TSSElementorWidgets::register( $widgets_manager );
		}

		/**
		 * Register controls
		 *
		 * @param object $controls_manager Controls Manager.
		 * @return void
		 */
		public function registerControls( $controls_manager ) {
			$this->includes();

			TSSElementorControls::register( $controls_manager );
		}

		/**
		 * Add Category
		 *
		 * @param object $elements_manager Elements manager.
		 * @return void
		 */
		public function addCategory( $elements_manager ) {
			$categories['tss-elementor-widgets'] = [
				'title' => esc_html__( 'Testimonial Slider and Showcase', 'testimonial-slider-showcase' ),
				'icon'  => 'fa fa-plug',
			];

			$el_categories = $elements_manager->get_categories();
			$categories    = array_merge(
				array_slice( $el_categories, 0, 1 ),
				$categories,
				array_slice( $el_categories, 1 )
			);

			$set_categories = function( $categories ) {
				$this->categories = $categories;
			};

			$set_categories->call( $elements_manager, $categories );
		}

		/**
		 * Editor styles.
		 *
		 * @return void
		 */
		public function editorStyles() {
			$img = TSSPro()->assetsUrl . '/images/element-icon.svg';
			$css = '
				.elementor-element .icon .tss-element {
					width: 50px;
					height: 50px;
				}

				.elementor-element .icon .tss-element::before {
					content: url( ' . $img . ');
					content: "";
					background-image: url(' . $img . ');
					width: 50px;
					height: 50px;
					position: absolute;
					background-repeat: no-repeat;
					background-position: center;
					background-size: 100%;
					margin-left: -25px;
					top: 20px;
				}

				.elementor-element .tss-element::after {
					background: #93003c;
					margin: 3px;
					content: "RT";
					font-family: Roboto,Arial,Helvetica,Verdana,sans-serif;
					font-size: 9px;
					position: absolute;
					top: 5px;
					left: 5px;
					padding: 4px 5px 2px;
					color: #fff;
					font-weight: bold;
				}

				.elementor-control[class*="elementor-control-tss_el"] .elementor-control-title {
					font-size: 13px;
					font-weight: 500;
				}

				.elementor-control[class*="elementor-control-tss_el"] .elementor-control-field-description {
					font-size: 13px;
				}

				.elementor-control .tss-elementor-group-heading {
					font-weight: bold;
					border-left: 4px solid #2271b1;
					padding: 10px;
					background: #f1f1f1;
					color: #495157;
				}
			';

			wp_add_inline_style( 'elementor-editor', $css );
		}

		/**
		 * Includes path.
		 *
		 * @return void
		 */
		private function includes() {
			TSSPro()->loadClass( TSSPro()->incPath . '/elementor/' );
		}

		/**
		 * Promotion
		 *
		 * @param array $config Config.
		 * @return array
		 */
		public function promotePremiumWidgets( $config ) {

			if ( function_exists( 'rttsp' ) ) {
				return $config;
			}

			if ( ! isset( $config['promotionWidgets'] ) || ! is_array( $config['promotionWidgets'] ) ) {
				$config['promotionWidgets'] = [];
			}

			$pro_widgets = [
				[
					'name'        => 'rt-testimonial-isotope',
					'title'       => esc_html__( 'Isotope Layout', 'testimonial-slider-showcase' ),
					'description' => esc_html__( 'Isotope Layout', 'testimonial-slider-showcase' ),
					'icon'        => 'eicon-testimonial-carousel tss-element tss-promotional-element',
					'categories'  => '[ "tss-elementor-widgets" ]',
				],
			];

			$config['promotionWidgets'] = array_merge( $config['promotionWidgets'], $pro_widgets );

			return $config;
		}
	}

endif;
