<?php
/**
 * Elementor Slider Widget Class.
 *
 * @package RT_TSS
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Elementor Slider Widget Class.
 */
class TSSElementorSliderWidget extends \Elementor\Widget_Base {
	/**
	 * Controls
	 *
	 * @var array
	 */
	private $rtControls = [];

	/**
	 * Widget name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'rt-testimonial-slider';
	}

	/**
	 * Widget title
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Testimonial Slider Layout', 'testimonial-slider-showcase' );
	}

	/**
	 * Widget icon
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-testimonial-carousel tss-element';
	}

	/**
	 * Widget scripts
	 *
	 * @return array
	 */
	public function get_script_depends() {

		return [ 'tss-image-load', 'tss-popup', 'swiper', 'tss-isotope', 'tss' ];

	}

	/**
	 * Widget styles
	 *
	 * @return array
	 */
	public function get_style_depends() {
		return [ 'swiper', 'tss-popup', 'tss-fontello', 'dashicons', 'tss' ];
	}

	/**
	 * Widget category
	 *
	 * @return array
	 */
	public function get_categories() {
		return [ 'tss-elementor-widgets' ];
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->controlTabs();

		if ( empty( $this->rtControls ) ) {
			return;
		}

		TSSPro()->tssAddElementorControls( $this->rtControls, $this );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	private function controlTabs() {
		$tabs = [
			TSSElementorSliderContent::class,
			TSSElementorSliderStyle::class,
		];

		$this->rtControls = TSSPro()->tssInitTabs( $tabs, $this->rtControls );
	}

	/**
	 * Render.
	 *
	 * @return void
	 */
	protected function render() {
		$data = $this->get_settings_for_display();
		$html = new TSSElementorRender();

		TSSPro()->printHtml( $html->testimonialRender( $data ) );
	}
}
