<?php
/**
 * Style Section Class.
 *
 * @package RT_TSS
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

if ( ! class_exists( 'TSSElementorStyleSections' ) ) :
	/**
	 * Style Section Class.
	 */
	class TSSElementorStyleSections {
		/**
		 * Accumulates tab fields.
		 *
		 * @access private
		 * @static
		 *
		 * @var array
		 */
		private $fields = [];

		/**
		 * Tab name.
		 *
		 * @access private
		 * @static
		 *
		 * @var array
		 */
		private $tab = \Elementor\Controls_Manager::TAB_STYLE;

		/**
		 * Colors Section
		 *
		 * @param array $condition Condition.
		 * @return array
		 */
		public function colors( $condition = null ) {
			$this->fields[] = [
				'mode'  => 'section_start',
				'id'    => 'tss_el_color_section',
				'label' => esc_html__( 'Colors', 'testimonial-slider-showcase' ),
				'tab'   => $this->tab,
			];

			$this->fields[] = [
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'id'              => 'tss_el_color_note',
				'raw'             => sprintf(
					'<h3 class="tss-elementor-group-heading">%s</h3>',
					esc_html__( 'Color Scheme', 'testimonial-slider-showcase' )
				),
				'content_classes' => 'elementor-panel-heading-title',
			];

			/**
			 * Accent colour for token-based layouts (layout 1): drives the card
			 * accent bar, quote mark, read-more link, author-name hover and the
			 * pagination hover / active states from a single value.
			 *
			 * The emitted declarations are kept byte-identical to the shortcode
			 * arm in TSSProHelper::layoutStyle() so the two cannot drift.
			 */
			$this->fields[] = [
				'type'        => \Elementor\Controls_Manager::COLOR,
				'id'          => 'tss_el_color_scheme_primary',
				'label'       => esc_html__( 'Primary Color', 'testimonial-slider-showcase' ),
				'description' => esc_html__(
					'Accent colour used across the layout. Individual controls below override it.',
					'testimonial-slider-showcase'
				),
				'selectors'   => [
					'{{WRAPPER}} .tss-wrapper' => '--tss-primary: {{VALUE}}; --tss-primary-hover: color-mix(in srgb, {{VALUE}} 80%, #000); --tss-primary-soft: color-mix(in srgb, {{VALUE}} 12%, #fff);',
				],
			];

			$this->fields[] = [
				'type'      => \Elementor\Controls_Manager::COLOR,
				'id'        => 'tss_el_primary_color',
				'label'     => esc_html__( 'Quote Icon Color', 'testimonial-slider-showcase' ),
				'selectors' => [
					'{{WRAPPER}} .tss-wrapper .tss-isotope1 .profile-img-wrapper:after, {{WRAPPER}} .tss-wrapper .tss-layout9 .profile-img-wrapper:after, {{WRAPPER}} .tss-wrapper .tss-isotope4 .profile-img-wrapper:after, {{WRAPPER}} .tss-wrapper .tss-carousel9 .profile-img-wrapper:after' => 'background: {{VALUE}}',
					'{{WRAPPER}} .tss-wrapper .item-content-wrapper:before, {{WRAPPER}} .tss-wrapper .item-content-wrapper:before, {{WRAPPER}} .tss-wrapper .single-item-wrapper:before' => 'color: {{VALUE}}',
					// Redesigned layouts render the quote as a real .tss-quote-mark
					// span instead of a :before glyph. .single-item-wrapper lifts this
					// to (0,4,0) so it beats every per-layout rule regardless of which
					// stylesheet Elementor prints first.
					'{{WRAPPER}} .tss-wrapper .single-item-wrapper .tss-quote-mark' => 'color: {{VALUE}}',
					// Carousel 11 / 12 wrap the copy in typographic quotes instead.
					// .item-content:before/:after only carry a glyph when there is no
					// <p>, so colouring them unconditionally is harmless.
					'{{WRAPPER}} .tss-wrapper .tss-carousel11 .item-content p:first-of-type:before, {{WRAPPER}} .tss-wrapper .tss-carousel11 .item-content p:last-of-type:after, {{WRAPPER}} .tss-wrapper .tss-carousel11 .item-content:before, {{WRAPPER}} .tss-wrapper .tss-carousel11 .item-content:after, {{WRAPPER}} .tss-wrapper .tss-carousel12 .item-content p:first-of-type:before, {{WRAPPER}} .tss-wrapper .tss-carousel12 .item-content p:last-of-type:after, {{WRAPPER}} .tss-wrapper .tss-carousel12 .item-content:before, {{WRAPPER}} .tss-wrapper .tss-carousel12 .item-content:after' => 'color: {{VALUE}}',
				],
				// These layouts draw no quote glyph at all, so the control would do nothing.
				'condition' => [
					'tss_el_layout_type!' => [
						'layout10',
						'layout_video',
						'layout_video2',
						'carousel10',
						'carousel_video',
						'carousel_video2',
						'isotope1',
						'isotope5',
						'isotope_video',
						'isotope_video2',
					],
				],
			];

			// Same selectors and em geometry as the shortcode's Quote Style arm in
			// TSSProHelper::layoutStyle(); see there for why the badges scale in em.
			$this->fields[] = [
				'type'      => \Elementor\Controls_Manager::COLOR,
				'id'        => 'tss_el_quote_bg_color',
				'label'     => esc_html__( 'Quote Badge Background', 'testimonial-slider-showcase' ),
				'selectors' => [
					'{{WRAPPER}} .tss-wrapper .tss-carousel3 .single-item-wrapper .tss-quote-mark, {{WRAPPER}} .tss-wrapper .tss-layout3 .single-item-wrapper .tss-quote-mark, {{WRAPPER}} .tss-wrapper .tss-isotope3 .single-item-wrapper .tss-quote-mark, {{WRAPPER}} .tss-wrapper .tss-layout9 .single-item-wrapper .profile-img-wrapper:after, {{WRAPPER}} .tss-wrapper .tss-isotope4 .single-item-wrapper .profile-img-wrapper:after, {{WRAPPER}} .tss-wrapper .tss-carousel9 .single-item-wrapper .profile-img-wrapper:after' => 'background: {{VALUE}}',
				],
				// Only these layouts draw the quote inside a badge.
				'condition' => [
					'tss_el_layout_type' => [ 'layout3', 'carousel3', 'isotope3', 'layout9', 'carousel9', 'isotope4' ],
				],
			];

			$this->fields[] = [
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'id'         => 'tss_el_quote_size',
				'label'      => esc_html__( 'Quote Icon Size', 'testimonial-slider-showcase' ),
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 10,
						'max' => 120,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .tss-wrapper .single-item-wrapper .tss-quote-mark' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .tss-wrapper .tss-carousel3 .single-item-wrapper .tss-quote-mark, {{WRAPPER}} .tss-wrapper .tss-layout3 .single-item-wrapper .tss-quote-mark, {{WRAPPER}} .tss-wrapper .tss-isotope3 .single-item-wrapper .tss-quote-mark' => 'width: 1.3077em; height: 1.3077em; padding-top: 0.4615em; top: -0.6538em;',
					'{{WRAPPER}} .tss-wrapper .tss-layout9 .single-item-wrapper .profile-img-wrapper:after, {{WRAPPER}} .tss-wrapper .tss-isotope4 .single-item-wrapper .profile-img-wrapper:after, {{WRAPPER}} .tss-wrapper .tss-carousel9 .single-item-wrapper .profile-img-wrapper:after' => 'font-size: {{SIZE}}{{UNIT}}; width: 2.3em; height: 2.3em; line-height: 2.3em;',
				],
				// Carousel 11 / 12 set their quotes in the copy, sized with the text.
				'condition'  => [
					'tss_el_layout_type!' => [
						'layout10',
						'layout_video',
						'layout_video2',
						'carousel10',
						'carousel11',
						'carousel12',
						'carousel_video',
						'carousel_video2',
						'isotope1',
						'isotope5',
						'isotope_video',
						'isotope_video2',
					],
				],
			];

			$this->fields[] = [
				'type'      => \Elementor\Controls_Manager::COLOR,
				'id'        => 'tss_el_author_name_color',
				'label'     => esc_html__( 'Author Name', 'testimonial-slider-showcase' ),
				'selectors' => [
					'{{WRAPPER}} .tss-wrapper .single-item-wrapper h3.author-name, {{WRAPPER}} .tss-wrapper .single-item-wrapper h3.author-name a' => 'color: {{VALUE}}',
				],
			];

			$this->fields[] = [
				'type'      => \Elementor\Controls_Manager::COLOR,
				'id'        => 'tss_el_author_bio_color',
				'label'     => esc_html__( 'Author Bio', 'testimonial-slider-showcase' ),
				'selectors' => [
					'{{WRAPPER}} .tss-wrapper .single-item-wrapper h4.author-bio' => 'color: {{VALUE}}',
				],
			];

			$this->fields[] = [
				'type'      => \Elementor\Controls_Manager::COLOR,
				'id'        => 'tss_el_rating_color',
				'label'     => esc_html__( 'Rating', 'testimonial-slider-showcase' ),
				'selectors' => [
					'{{WRAPPER}} .tss-wrapper .single-item-wrapper .rating-wrapper span.dashicons' => 'color: {{VALUE}}',
				],
			];

			$this->fields[] = [
				'type'      => \Elementor\Controls_Manager::COLOR,
				'id'        => 'tss_el_testimonial_text_color',
				'label'     => esc_html__( 'Testimonial Text', 'testimonial-slider-showcase' ),
				'selectors' => [
					'{{WRAPPER}} .tss-wrapper .single-item-wrapper .item-content' => 'color: {{VALUE}}',
				],
			];

			$this->fields = array_merge( apply_filters( 'rttss_elementor_style_color_scheme', $this->fields ) );

			$this->buttonColors( $condition );

			$this->fields = array_merge( apply_filters( 'rttss_elementor_before_colors_end', $this->fields ) );

			$this->fields[] = [
				'mode' => 'section_end',
			];

			return $this->fields;
		}

		/**
		 * Button Colors.
		 *
		 * @param array $condition Condition.
		 * @return array
		 */
		public function buttonColors( $condition = null ) {
			// Name the group after what it actually paints on this widget, so the
			// isotope filter controls are findable under a generic "Button" label.
			if ( 'with-pagination' === $condition ) {
				$heading = esc_html__( 'Pagination Button Colors', 'testimonial-slider-showcase' );
			} elseif ( 'with-slider-nav' === $condition ) {
				$heading = esc_html__( 'Arrow & Dot Colors', 'testimonial-slider-showcase' );
			} else {
				$heading = esc_html__( 'Filter & Pagination Button Colors', 'testimonial-slider-showcase' );
			}

			$this->fields[] = [
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'id'              => 'tss_el_button_note',
				'raw'             => sprintf(
					'<h3 class="tss-elementor-group-heading">%s</h3>',
					$heading
				),
				'separator'       => 'before',
				'content_classes' => 'elementor-panel-heading-title',
				'conditions'      => $this->buttonCondition( $condition ),
			];

			$this->fields[] = [
				'mode'       => 'tabs_start',
				'id'         => 'tss_el_button_colors',
				'conditions' => $this->buttonCondition( $condition ),
			];

			$this->fields[] = [
				'mode'       => 'tab_start',
				'id'         => 'tss_el_button_colors_normal',
				'label'      => esc_html__( 'Normal', 'testimonial-slider-showcase' ),
				'conditions' => $this->buttonCondition( $condition ),
			];

			$this->fields[] = [
				'type'       => \Elementor\Controls_Manager::COLOR,
				'id'         => 'tss_el_button_bg_color',
				'label'      => esc_html__( 'Background Color', 'testimonial-slider-showcase' ),
				'selectors'  => [
					'{{WRAPPER}} .tss-wrapper .tss-utility button, {{WRAPPER}} .tss-wrapper .tss-carousel-main .swiper-arrow, {{WRAPPER}} .tss-wrapper .tss-carousel-controls .swiper-arrow, {{WRAPPER}} .tss-wrapper .tss-carousel-main .swiper-pagination-bullet:not(.swiper-pagination-bullet-active), {{WRAPPER}} .tss-wrapper .tss-carousel-controls .swiper-pagination-bullet:not(.swiper-pagination-bullet-active), {{WRAPPER}} .tss-wrapper .tss-carousel .swiper-arrow, {{WRAPPER}} .tss-wrapper .tss-carousel .swiper-pagination-bullet:not(.swiper-pagination-bullet-active), {{WRAPPER}} .tss-wrapper .tss-utility .rt-button, {{WRAPPER}} .tss-wrapper .tss-pagination ul.pagination-list li a, {{WRAPPER}} .tss-wrapper .tss-isotope-button-wrapper .rt-iso-button:not(.selected)' => 'background-color: {{VALUE}}',
					// Read More follows the button colours; a fill makes it a button.
					'{{WRAPPER}} .tss-wrapper .single-item-wrapper .tss-readmore-toggle' => 'background-color: {{VALUE}}; padding: 6px 14px; border-radius: 4px; text-decoration: none;',
				],
				'conditions' => $this->buttonCondition( $condition ),

			];

			$this->fields[] = [
				'type'       => \Elementor\Controls_Manager::COLOR,
				'id'         => 'tss_el_button_text_color',
				'label'      => esc_html__( 'Text Color', 'testimonial-slider-showcase' ),
				'selectors'  => [
					'{{WRAPPER}} .tss-wrapper .tss-pagination ul.pagination-list li a, {{WRAPPER}} .tss-wrapper .tss-utility .rt-button, {{WRAPPER}} .tss-wrapper .tss-carousel-main .swiper-arrow > i, {{WRAPPER}} .tss-wrapper .tss-carousel-controls .swiper-arrow > i, {{WRAPPER}} .tss-wrapper .tss-carousel .swiper-arrow > i, {{WRAPPER}} .tss-wrapper .tss-utility .rt-button, {{WRAPPER}} .tss-wrapper .tss-isotope-button-wrapper .rt-iso-button:not(.selected), {{WRAPPER}} .tss-wrapper .single-item-wrapper .tss-readmore-toggle' => 'color: {{VALUE}}',
				],
				'conditions' => $this->buttonCondition( $condition ),
			];

			$this->fields[] = [
				'type'       => \Elementor\Group_Control_Border::get_type(),
				'id'         => 'tss_el_button_border',
				'mode'       => 'group',
				'label'      => esc_html__( 'Border', 'testimonial-slider-showcase' ),
				'selector'   => '{{WRAPPER}} .tss-wrapper .tss-pagination ul.pagination-list li.active span, {{WRAPPER}} .tss-wrapper .tss-pagination ul.pagination-list li a, {{WRAPPER}} .tss-wrapper .tss-carousel-main .swiper-arrow, {{WRAPPER}} .tss-wrapper .tss-carousel-controls .swiper-arrow, {{WRAPPER}} .tss-wrapper .tss-carousel .swiper-arrow, {{WRAPPER}} .tss-wrapper .tss-utility .rt-button, {{WRAPPER}} .tss-wrapper .tss-utility .rt-button, {{WRAPPER}} .tss-wrapper .tss-isotope-button-wrapper .rt-iso-button, {{WRAPPER}} .tss-wrapper .single-item-wrapper .tss-readmore-toggle',
				'conditions' => $this->buttonCondition( $condition ),
			];

			$this->fields[] = [
				'mode'       => 'tab_end',
				'conditions' => $this->buttonCondition( $condition ),
			];

			$this->fields[] = [
				'mode'       => 'tab_start',
				'id'         => 'tss_el_button_colors_hover',
				'label'      => esc_html__( 'Hover', 'testimonial-slider-showcase' ),
				'conditions' => $this->buttonCondition( $condition ),
			];

			$this->fields[] = [
				'type'       => \Elementor\Controls_Manager::COLOR,
				'id'         => 'tss_el_button_bg_color_hover',
				'label'      => esc_html__( 'Background Color', 'testimonial-slider-showcase' ),
				'selectors'  => [
					'{{WRAPPER}} .tss-wrapper .tss-carousel-main .swiper-arrow:hover, {{WRAPPER}} .tss-wrapper .tss-carousel-controls .swiper-arrow:hover, {{WRAPPER}} .tss-wrapper .tss-carousel-main .swiper-pagination-bullet:not(.swiper-pagination-bullet-active):hover, {{WRAPPER}} .tss-wrapper .tss-carousel-controls .swiper-pagination-bullet:not(.swiper-pagination-bullet-active):hover, {{WRAPPER}} .tss-wrapper .tss-carousel .swiper-arrow:hover, {{WRAPPER}} .tss-wrapper .tss-carousel .swiper-pagination-bullet:not(.swiper-pagination-bullet-active):hover, {{WRAPPER}} .tss-wrapper .tss-utility button:hover, {{WRAPPER}} .tss-wrapper .tss-utility .rt-button:hover, {{WRAPPER}} .tss-wrapper .tss-pagination ul.pagination-list li a:hover, {{WRAPPER}} .tss-wrapper .tss-isotope-button-wrapper .rt-iso-button:not(.selected):hover, {{WRAPPER}} .tss-wrapper .single-item-wrapper .tss-readmore-toggle:hover, {{WRAPPER}} .tss-wrapper .single-item-wrapper .tss-readmore-toggle:focus' => 'background-color: {{VALUE}}; text-decoration: none;',
				],
				'conditions' => $this->buttonCondition( $condition ),
			];

			$this->fields[] = [
				'type'       => \Elementor\Controls_Manager::COLOR,
				'id'         => 'tss_el_button_text_color_hover',
				'label'      => esc_html__( 'Text Color', 'testimonial-slider-showcase' ),
				'selectors'  => [
					'{{WRAPPER}} .tss-wrapper .tss-pagination ul.pagination-list li a:hover, {{WRAPPER}} .tss-wrapper .tss-isotope-button-wrapper .rt-iso-button:not(.selected):hover, {{WRAPPER}} .tss-wrapper .tss-utility .rt-button:hover, {{WRAPPER}} .tss-wrapper .tss-carousel-main .swiper-arrow:hover > i, {{WRAPPER}} .tss-wrapper .tss-carousel-controls .swiper-arrow:hover > i, {{WRAPPER}} .tss-wrapper .tss-carousel .swiper-arrow:hover > i, {{WRAPPER}} .tss-wrapper .single-item-wrapper .tss-readmore-toggle:hover, {{WRAPPER}} .tss-wrapper .single-item-wrapper .tss-readmore-toggle:focus' => 'color: {{VALUE}}',
				],
				'conditions' => $this->buttonCondition( $condition ),
			];

			$this->fields[] = [
				'type'       => \Elementor\Controls_Manager::COLOR,
				'id'         => 'tss_el_button_border_color_hover',
				'label'      => esc_html__( 'Border Color', 'testimonial-slider-showcase' ),
				'selectors'  => [
					'{{WRAPPER}} .tss-wrapper .tss-pagination ul.pagination-list li a:hover, {{WRAPPER}} .tss-wrapper .tss-carousel-main .swiper-arrow:hover, {{WRAPPER}} .tss-wrapper .tss-carousel-controls .swiper-arrow:hover, {{WRAPPER}} .tss-wrapper .tss-carousel .swiper-arrow:hover, {{WRAPPER}} .tss-wrapper .tss-utility .rt-button:hover, {{WRAPPER}} .tss-wrapper .tss-utility .rt-button:hover, {{WRAPPER}} .tss-wrapper .tss-isotope-button-wrapper .rt-iso-button:hover, {{WRAPPER}} .tss-wrapper .single-item-wrapper .tss-readmore-toggle:hover' => 'border-color: {{VALUE}}',
				],
				'conditions' => $this->buttonCondition( $condition ),
			];

			$this->fields[] = [
				'mode'       => 'tab_end',
				'conditions' => $this->buttonCondition( $condition ),
			];

			$this->fields[] = [
				'mode'       => 'tab_start',
				'id'         => 'tss_el_button_colors_active',
				'label'      => esc_html__( 'Active', 'testimonial-slider-showcase' ),
				'conditions' => $this->buttonCondition( $condition ),
			];

			$this->fields[] = [
				'type'       => \Elementor\Controls_Manager::COLOR,
				'id'         => 'tss_el_button_bg_color_active',
				'label'      => esc_html__( 'Background Color', 'testimonial-slider-showcase' ),
				'selectors'  => [
					'{{WRAPPER}} .tss-wrapper .tss-carousel-main .swiper-pagination-bullet-active, {{WRAPPER}} .tss-wrapper .tss-carousel-controls .swiper-pagination-bullet-active, {{WRAPPER}} .tss-wrapper .tss-carousel .swiper-pagination-bullet-active, {{WRAPPER}} .tss-wrapper .tss-pagination ul.pagination-list li.active span, {{WRAPPER}} .tss-wrapper .tss-isotope-button-wrapper .rt-iso-button.selected' => 'background-color: {{VALUE}}',
				],
				'conditions' => $this->buttonCondition( $condition ),
			];

			$this->fields[] = [
				'type'       => \Elementor\Controls_Manager::COLOR,
				'id'         => 'tss_el_button_text_color_active',
				'label'      => esc_html__( 'Text Color', 'testimonial-slider-showcase' ),
				'selectors'  => [
					'{{WRAPPER}} .tss-wrapper .slick-dots li.slick-active button, {{WRAPPER}} .tss-wrapper .tss-pagination ul.pagination-list li.active span, {{WRAPPER}} .tss-wrapper .tss-isotope-button-wrapper .rt-iso-button.selected' => 'color: {{VALUE}}',
				],
				'conditions' => $this->buttonCondition( $condition ),
			];

			$this->fields[] = [
				'type'       => \Elementor\Controls_Manager::COLOR,
				'id'         => 'tss_el_button_border_color_active',
				'label'      => esc_html__( 'Border Color', 'testimonial-slider-showcase' ),
				'selectors'  => [
					'{{WRAPPER}} .tss-wrapper .tss-carousel-main .swiper-pagination-bullet-active, {{WRAPPER}} .tss-wrapper .tss-carousel-controls .swiper-pagination-bullet-active, {{WRAPPER}} .tss-wrapper .tss-carousel .swiper-pagination-bullet-active, {{WRAPPER}} .tss-wrapper .tss-pagination ul.pagination-list li.active span, {{WRAPPER}} .tss-wrapper .tss-isotope-button-wrapper .rt-iso-button.selected' => 'border-color: {{VALUE}}',
				],
				'conditions' => $this->buttonCondition( $condition ),
			];

			$this->fields[] = [
				'mode'       => 'tab_end',
				'conditions' => $this->buttonCondition( $condition ),
			];

			$this->fields[] = [
				'mode'       => 'tabs_end',
				'conditions' => $this->buttonCondition( $condition ),
			];

			return $this->fields;
		}

		/**
		 * Typography Section
		 *
		 * @return array
		 */
		public function typography() {
			$this->fields[] = [
				'mode'  => 'section_start',
				'id'    => 'tss_el_typography_section',
				'label' => esc_html__( 'Typography', 'testimonial-slider-showcase' ),
				'tab'   => $this->tab,
			];

            $this->fields = array_merge( apply_filters( 'rttss_elementor_rating_average', $this->fields ) );

			$this->fields[] = [
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'id'              => 'tss_el_author_name_note',
				'raw'             => sprintf(
					'<h3 class="tss-elementor-group-heading">%s</h3>',
					esc_html__( 'Author Name', 'testimonial-slider-showcase' )
				),
				'content_classes' => 'elementor-panel-heading-title',
			];

			$this->fields[] = [
				'mode'     => 'group',
				'id'       => 'tss_el_author_name_typography',
				'type'     => \Elementor\Group_Control_Typography::get_type(),
				'selector' => '{{WRAPPER}} .tss-wrapper .single-item-wrapper h3.author-name, {{WRAPPER}} .tss-wrapper .single-item-wrapper h3.author-name a',
			];

			$this->fields[] = [
				'mode'                 => 'responsive',
				'id'                   => 'tss_el_author_name_alignment',
				'type'                 => \Elementor\Controls_Manager::CHOOSE,
				'label'                => esc_html__( 'Alignment', 'testimonial-slider-showcase' ),
				'options'              => [
					'left'   => [
						'title' => esc_html__( 'Left', 'testimonial-slider-showcase' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'testimonial-slider-showcase' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'  => [
						'title' => esc_html__( 'Right', 'testimonial-slider-showcase' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				// No default on purpose: every layout declares its own alignment in
				// wptestimonial.css (layout 1 is left aligned, the rest centered).
				// A hard default here is emitted as inline CSS and would win over
				// all of them.
				// Declarations from TSSProHelper::boxAlignCss(), shared with the
				// shortcode arm; it explains why text-align alone is not enough.
				'selectors_dictionary' => [
					'left'   => TSSPro()->boxAlignCss( 'left' ),
					'center' => TSSPro()->boxAlignCss( 'center' ),
					'right'  => TSSPro()->boxAlignCss( 'right' ),
				],
				'selectors'            => [
					'{{WRAPPER}} .tss-wrapper .single-item-wrapper h3.author-name' => '{{VALUE}}',
					'{{WRAPPER}} .tss-wrapper .single-item-wrapper h3.author-name a' => 'text-align: inherit;',
					TSSPro()->inlineNameSelector( '{{WRAPPER}} .tss-wrapper' ) => TSSPro()->inlineNameBlockCss(),
				],
				// Layout 9 is a photo card with the name, bio and stars centred in
				// an overlay; the design has no other placement for them.
				'condition'            => [
					'tss_el_layout_type!' => [ 'layout9', 'carousel9', 'isotope4' ],
				],
			];

			$this->fields[] = [
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'id'         => 'tss_el_author_name_margin',
				'mode'       => 'responsive',
				'label'      => esc_html__( 'Spacing', 'testimonial-slider-showcase' ),
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .tss-wrapper .single-item-wrapper h3.author-name, {{WRAPPER}} .tss-wrapper .single-item-wrapper h3.author-name a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			];

			$this->fields[] = [
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'id'              => 'tss_el_author_bio_note',
				'raw'             => sprintf(
					'<h3 class="tss-elementor-group-heading">%s</h3>',
					esc_html__( 'Author Bio', 'testimonial-slider-showcase' )
				),
				'separator'       => 'before',
				'content_classes' => 'elementor-panel-heading-title',
			];

			$this->fields[] = [
				'mode'     => 'group',
				'id'       => 'tss_el_author_bio_typography',
				'type'     => \Elementor\Group_Control_Typography::get_type(),
				'selector' => '{{WRAPPER}} .tss-wrapper .single-item-wrapper h4.author-bio',
			];

			$this->fields[] = [
				'mode'                 => 'responsive',
				'id'                   => 'tss_el_author_bio_alignment',
				'type'                 => \Elementor\Controls_Manager::CHOOSE,
				'label'                => esc_html__( 'Alignment', 'testimonial-slider-showcase' ),
				'options'              => [
					'left'   => [
						'title' => esc_html__( 'Left', 'testimonial-slider-showcase' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'testimonial-slider-showcase' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'  => [
						'title' => esc_html__( 'Right', 'testimonial-slider-showcase' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				// See tss_el_author_name_alignment — no default for the same reason.
				'selectors_dictionary' => [
					'left'   => TSSPro()->boxAlignCss( 'left' ),
					'center' => TSSPro()->boxAlignCss( 'center' ),
					'right'  => TSSPro()->boxAlignCss( 'right' ),
				],
				'selectors'            => [
					'{{WRAPPER}} .tss-wrapper .single-item-wrapper h4.author-bio' => '{{VALUE}}',
				],
				// Layout 9 is a photo card with the name, bio and stars centred in
				// an overlay; the design has no other placement for them.
				'condition'            => [
					'tss_el_layout_type!' => [ 'layout9', 'carousel9', 'isotope4' ],
				],
			];

			$this->fields[] = [
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'id'         => 'tss_el_author_bio_margin',
				'mode'       => 'responsive',
				'label'      => esc_html__( 'Spacing', 'testimonial-slider-showcase' ),
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .tss-wrapper .single-item-wrapper h4.author-bio' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			];

			$this->fields[] = [
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'id'              => 'tss_el_rating_note',
				'raw'             => sprintf(
					'<h3 class="tss-elementor-group-heading">%s</h3>',
					esc_html__( 'Rating', 'testimonial-slider-showcase' )
				),
				'separator'       => 'before',
				'content_classes' => 'elementor-panel-heading-title',
			];

			$this->fields[] = [
				'mode'     => 'group',
				'id'       => 'tss_el_rating_typography',
				'type'     => \Elementor\Group_Control_Typography::get_type(),
				'selector' => '{{WRAPPER}} .tss-wrapper .single-item-wrapper .rating-wrapper span.dashicons',
				// Stars are single icon-font glyphs, one per span: word spacing has no
				// spaces to act on and the font ships one weight, so only size applies.
				'exclude'  => [ 'font_family', 'font_weight', 'letter_spacing', 'word_spacing', 'text_transform', 'font_style', 'text_decoration', 'line_height' ], // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
			];

			$this->fields[] = [
				'mode'                 => 'responsive',
				'id'                   => 'tss_el_rating_alignment',
				'type'                 => \Elementor\Controls_Manager::CHOOSE,
				'label'                => esc_html__( 'Alignment', 'testimonial-slider-showcase' ),
				'options'              => [
					'left'   => [
						'title' => esc_html__( 'Left', 'testimonial-slider-showcase' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'testimonial-slider-showcase' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'  => [
						'title' => esc_html__( 'Right', 'testimonial-slider-showcase' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				// No default on purpose, as with the name alignment above: every layout
				// places its stars in wptestimonial.css, and a default here would be
				// emitted as inline CSS and move them on every existing widget.
				// The declarations come from TSSProHelper::ratingAlignCss(), shared
				// with the shortcode arm; it explains the video and layout 10 rules.
				'selectors_dictionary' => [
					'left'   => TSSPro()->ratingAlignCss( 'left' )['wrapper'],
					'center' => TSSPro()->ratingAlignCss( 'center' )['wrapper'],
					'right'  => TSSPro()->ratingAlignCss( 'right' )['wrapper'],
				],
				'selectors'            => [
					'{{WRAPPER}} .tss-wrapper .single-item-wrapper .rating-wrapper' => '{{VALUE}}',
					'{{WRAPPER}} .tss-wrapper .single-item-wrapper .tss-meta-text + .rating-wrapper' => TSSPro()->ratingAlignCss( '' )['video'],
					// Last, so it wins the specificity tie with the first selector.
					'{{WRAPPER}} .tss-wrapper .tss-layout10 .rating-wrapper, {{WRAPPER}} .tss-wrapper .tss-isotope5 .rating-wrapper, {{WRAPPER}} .tss-wrapper .tss-carousel10 .rating-wrapper' => TSSPro()->ratingAlignCss( '' )['layout10'],
				],
				// Layout 9 is a photo card with the name, bio and stars centred in
				// an overlay; the design has no other placement for them.
				'condition'            => [
					'tss_el_layout_type!' => [ 'layout9', 'carousel9', 'isotope4' ],
				],
			];

			$this->fields[] = [
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'id'         => 'tss_el_rating_spacing',
				'mode'       => 'responsive',
				'label'      => esc_html__( 'Icon Spacing', 'testimonial-slider-showcase' ),
				'size_units' => [ 'px', '%', 'em' ],
				'range'      => [
					'px' => [ 'max' => 100 ],
					'%'  => [ 'max' => 100 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .tss-wrapper .single-item-wrapper .rating-wrapper span.dashicons' => 'margin-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}  .tss-wrapper .single-item-wrapper .rating-wrapper span.dashicons' => 'margin-right: {{SIZE}}{{UNIT}};',
				],
			];

			$this->fields[] = [
				'type'               => \Elementor\Controls_Manager::DIMENSIONS,
				'id'                 => 'tss_el_rating_margin',
				'mode'               => 'responsive',
				'label'              => esc_html__( 'Spacing', 'testimonial-slider-showcase' ),
				'size_units'         => [ 'px', '%', 'em' ],
				'allowed_dimensions' => [ 'top', 'bottom' ],
				'selectors'          => [
					'{{WRAPPER}} .tss-wrapper .single-item-wrapper .rating-wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			];

			$this->fields[] = [
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'id'              => 'tss_el_testimonial_text_note',
				'raw'             => sprintf(
					'<h3 class="tss-elementor-group-heading">%s</h3>',
					esc_html__( 'Testimonial Text', 'testimonial-slider-showcase' )
				),
				'separator'       => 'before',
				'content_classes' => 'elementor-panel-heading-title',
			];

			$this->fields[] = [
				'mode'     => 'group',
				'id'       => 'tss_el_testimonial_text_typography',
				'type'     => \Elementor\Group_Control_Typography::get_type(),
				'selector' => '{{WRAPPER}} .tss-wrapper .single-item-wrapper .item-content',
			];

			$this->fields[] = [
				'mode'      => 'responsive',
				'id'        => 'tss_el_testimonial_text_alignment',
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'label'     => esc_html__( 'Alignment', 'testimonial-slider-showcase' ),
				'options'   => [
					'left'   => [
						'title' => esc_html__( 'Left', 'testimonial-slider-showcase' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'testimonial-slider-showcase' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'  => [
						'title' => esc_html__( 'Right', 'testimonial-slider-showcase' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				// No default on purpose. A hard 'left' here is emitted as inline CSS
				// that beats every stylesheet rule, so a layout whose own CSS centres
				// the quote (carousel 3) rendered centred on the shortcode arm and
				// left on the Elementor arm. Each layout now declares its own
				// alignment in wptestimonial.css and this control overrides it.
				'selectors' => [
					'{{WRAPPER}} .tss-wrapper .single-item-wrapper .item-content' => 'text-align: {{VALUE}}',
				],
			];

			$this->fields[] = [
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'id'         => 'tss_el_testimonial_text_margin',
				'mode'       => 'responsive',
				'label'      => esc_html__( 'Spacing', 'testimonial-slider-showcase' ),
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .tss-wrapper .single-item-wrapper .item-content' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			];

			$this->fields = array_merge( apply_filters( 'rttss_elementor_typography_end', $this->fields ) );

			$this->fields[] = [
				'mode' => 'section_end',
			];

			return $this->fields;
		}

		/**
		 * Image Section
		 *
		 * @return array
		 */
		public function image() {
			$this->fields[] = [
				'mode'  => 'section_start',
				'id'    => 'tss_el_image_styles_section',
				'label' => esc_html__( 'Image Style', 'testimonial-slider-showcase' ),
				'tab'   => $this->tab,
			];

			$this->fields[] = [
				'type'     => \Elementor\Group_Control_Border::get_type(),
				'id'       => 'tss_el_image_border',
				'mode'     => 'group',
				'label'    => esc_html__( 'Border', 'testimonial-slider-showcase' ),
				'selector' => '{{WRAPPER}} .tss-wrapper .tlp-openPopup:before, {{WRAPPER}} .tss-wrapper .rt-responsive-img',
			];

			$this->fields = array_merge( apply_filters( 'rttss_elementor_before_image_border', $this->fields ) );

			$this->fields[] = [
				'id'         => 'tss_el_grid_image_border-radius',
				'mode'       => 'responsive',
				'label'      => esc_html__( 'Border Radius', 'testimonial-slider-showcase' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				// No default on purpose. A hard 50% here is emitted as inline CSS at
				// (0,5,0) and beat every layout's own avatar shape, so the Elementor
				// arm rendered circles where the shortcode arm rendered squares, and
				// layouts with a deliberate radius (carousel 1 is a rounded square)
				// could not keep it. Each layout now declares its own shape in
				// wptestimonial.css, and "Image Type → Circle" remains the opt-in.
				'separator'  => 'before',
				'selectors'  => [
					'{{WRAPPER}} .tss-wrapper .tlp-openPopup:before, {{WRAPPER}} .tlp-video-image-2, {{WRAPPER}} .tss-wrapper .rt-responsive-img, {{WRAPPER}} .tss-wrapper .tss-layout9 .profile-img-wrapper:before, {{WRAPPER}} .tss-wrapper .tss-isotope4 .profile-img-wrapper:before, {{WRAPPER}} .tss-wrapper .tss-carousel9 .profile-img-wrapper:before, {{WRAPPER}} .tss-wrapper .tss-layout9 .profile-img-wrapper:after, {{WRAPPER}} .tss-wrapper .tss-isotope4 .profile-img-wrapper:after, {{WRAPPER}} .tss-wrapper .tss-carousel9 .profile-img-wrapper:after, {{WRAPPER}} .tss-wrapper .tss-carousel5 .profile-img-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			];

			$this->fields[] = [
				'mode' => 'section_end',
			];

			return $this->fields;
		}
		/**
		 * Grid Colors Section
		 *
		 * @return array
		 */
		public function gridColors() {
			$this->colors( 'with-pagination' );

			return $this->fields;
		}

		/**
		 * Common Colors Section.
		 *
		 * @return array
		 */
		public function commonColors() {
			$this->colors();

			return $this->fields;
		}

		/**
		 * Slider Colors Section.
		 *
		 * @return array
		 */
		public function sliderColors() {
			$this->colors( 'with-slider-nav' );

			return $this->fields;
		}

		/**
		 * Button Controls Condition.
		 *
		 * @param array $condition Condition.
		 * @return array
		 */
		private function buttonCondition( $condition = null ) {
			$conditions = [];

			if ( 'with-pagination' === $condition ) {
				$conditions['terms'][] = [
					'name'     => 'tss_el_pagination',
					'operator' => '==',
					'value'    => 'yes',
				];
			} elseif ( 'with-slider-nav' === $condition ) {
				// A slider's only buttons are its arrows and dots.
				$conditions['relation'] = 'or';
				$conditions['terms']    = [
					[
						'name'     => 'tss_el_slider_nav',
						'operator' => '==',
						'value'    => 'yes',
					],
					[
						'name'     => 'tss_el_slider_pagi',
						'operator' => '==',
						'value'    => 'yes',
					],
				];
			} else {
				$conditions['terms'] = [];
			}

			return $conditions;
		}
	}
endif;
