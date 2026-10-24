<?php
/**
 * Helper Class.
 *
 * @package RT_TSS
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

if ( ! class_exists( 'TSSProHelper' ) ) :
	/**
	 * Helper Class.
	 */
	class TSSProHelper {
		public function verifyNonce() {
			$nonce     = ! empty( $_REQUEST[ $this->nonceId() ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $this->nonceId() ] ) ) : null;
			$nonceText = $this->nonceText();

			if ( ! wp_verify_nonce( $nonce, $nonceText ) ) {
				return false;
			}

			return true;
		}

		public  function getNonce(  ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return isset( $_REQUEST[  $this->nonceId() ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $this->nonceId() ] ) ) : null;

		}

		public function verifyRecaptcha() {
			$return           = false;
			$settings         = get_option( TSSPro()->options['settings'] );
			$recaptcha_secret = ( ! empty( $settings['tss_secret_key'] ) ? sanitize_text_field( $settings['tss_secret_key'] ) : null );

			if ( $recaptcha_secret ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$response = isset( $_REQUEST['g-recaptcha-response'] ) ? wp_remote_get( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $recaptcha_secret . '&response=' . sanitize_text_field( wp_unslash( $_REQUEST['g-recaptcha-response'] ) ) ) : null;
				$response = json_decode( $response['body'], true );

				if ( true == $response['success'] ) {
					$return = true;
				}
			}

			return $return;
		}

		public function nonceText() {
			return 'tss_nonce_text';
		}

		public function nonceId() {
			return 'tss_nonce';
		}

		public function tssTestimonialAllMetaFields() {
			return array_merge(
				TSSPro()->singleTestimonialFields()
			);
		}

		public function pfpScMetaFields() {
			return array_merge(
				TSSPro()->scLayoutMetaFields(),
				TSSPro()->scFilterMetaFields(),
				TSSPro()->scItemMetaFields(),
				TSSPro()->scStyleFields()
			);
		}

		public function rtFieldGenerator( $fields = [] ) {
			$html = null;

			if ( is_array( $fields ) && ! empty( $fields ) ) {
				$TSSProField = new TSSProField();

				foreach ( $fields as $fieldKey => $field ) {
					$html .= $TSSProField->Field( $fieldKey, $field );
				}
			}

			return $html;
		}

		public function tssAllSettingsFields() {
			return array_merge(
				TSSPro()->generalSettings(),
				TSSPro()->detailFieldControl(),
				TSSPro()->formFieldControl(),
				TSSPro()->recaptchaFields(),
				TSSPro()->othersSettings(),
				TSSPro()->rtLicenceField()
			);
		}

		/**
		 * Sanitize field value
		 *
		 * @param array $field
		 * @param null  $value
		 *
		 * @return array|null
		 * @internal param $value
		 */
		public function sanitize( $field = [], $value = null ) {
			$newValue = null;
			if ( is_array( $field ) ) {
				$type = ( ! empty( $field['type'] ) ? esc_attr( $field['type'] ) : 'text' );

				if ( empty( $field['multiple'] ) ) {
					if ( $type == 'text' || $type == 'number' || $type == 'select' || $type == 'checkbox' || $type == 'switch' || $type == 'radio' || $type == 'radio-image' ) {
						$newValue = sanitize_text_field( $value );
					} elseif ( $type == 'url' ) {
						$newValue = esc_url( $value );
					} elseif ( $type == 'rating' ) {
						$newValue = absint( $value );
						$newValue = ( $newValue > 5 ? 0 : $newValue );
					} elseif ( $type == 'video' ) {
						$newValue = esc_url( $value );
					} elseif ( $type == 'slug' ) {
						$newValue = sanitize_title_with_dashes( $value );
					} elseif ( $type == 'textarea' ) {
						$newValue = wp_kses_post( $value );
					} elseif ( $type == 'custom_css' ) {
						$newValue = esc_textarea( $value );
					} elseif ( $type == 'colorpicker' ) {
						$newValue = $this->sanitize_hex_color( $value );
					} elseif ( $type == 'image' ) {
						$newValue = absint( $value );
					} elseif ( $field['type'] == 'email' ) {
						$newValue = sanitize_email( $value );
					} elseif ( $type == 'image_size' ) {
						$newValue = [];
						if ($value){
							foreach ( $value as $k => $v ) {
								$newValue[ $k ] = esc_attr( $v );
							}
						}

					} elseif ( $type == 'style' || $type == 'multiple_options' ) {
						$newValue = [];

						if ($value){
							foreach ( $value as $k => $v ) {
								$nV = null;

								if ( $k == 'color' ) {
									$nV = $this->sanitize_hex_color( $v );
								} else {
									$nV = $this->sanitize( [ 'type' => 'text' ], $v );
								}

								if ( $nV ) {
									$newValue[ $k ] = $nV;
								}
							}
						}

						if ( empty( $newValue ) ) {
							$newValue = null;
						}
					} elseif ( $type == 'socialMedia' ) {
						if ( is_array( $value ) ) {
							foreach ( $value as $key => $val ) {
								$newValue[ $key ] = esc_url( $val );
							}
						}
					} else {
						$newValue = [];
					}
				} else {
					$newValue = [];

					if ( ! empty( $value ) ) {
						if ( is_array( $value ) ) {
							foreach ( $value as $key => $val ) {
								if ( $type == 'style' && $key == 0 ) {
									$newValue = $this->sanitize_hex_color( $val );
								} else {
									$newValue[] = sanitize_text_field( $val );
								}
							}
						} else {
							$newValue[] = sanitize_text_field( $value );
						}
					}
				}
			}

			return $newValue;
		}

		public function sanitize_hex_color( $color ) {
			if ( function_exists( 'sanitize_hex_color' ) ) {
				return sanitize_hex_color( $color );
			} else {
				if ( '' === $color ) {
					return '';
				}

				// 3 or 6 hex digits, or the empty string.
				if ( preg_match( '|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) ) {
					return $color;
				}
			}
		}

		public function getFeatureImage( $post_id = null, $fImgSize = 'medium', $customImgSize = [], $defaultImgId = null, $data_src = false ) {
			global $post;

			$img_class = 'rt-responsive-img';
			$imgSrc    = $image = null;
			$cSize     = false;
			$post_id   = ( $post_id ? absint( $post_id ) : $post->ID );
			$thumb_id  = get_post_thumbnail_id( $post_id );
			$alt       = esc_attr(trim( wp_strip_all_tags( get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) ) ));
			if ( empty( $alt ) ) {
				$alt = esc_attr( get_the_title( $post_id ) );
			}

			if ( $fImgSize == 'tss_custom' ) {
				$fImgSize = 'full';
				$cSize    = true;
			}

			if ( $thumb_id ) {
				$imageS = wp_get_attachment_image_src( $thumb_id, $fImgSize );
				$imgSrc = $imageS[0];

				$image = "<img alt='{$alt}' class='{$img_class}' src='{$imgSrc}' />"; // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage

				if ( $data_src ) {
					$image = "<img alt='{$alt}' class='{$img_class} swiper-lazy' data-src='{$imgSrc}' />"; // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
				}
			}
			if ( ! $imgSrc && $defaultImgId ) {
				$image  = wp_get_attachment_image_src( $defaultImgId, $fImgSize );
				$imgSrc = $image[0];
				$image  = "<img alt='{$alt}' class='{$img_class}' src='{$imgSrc}' />"; // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage

				if ( $data_src ) {
					$image = "<img alt='{$alt}' class='{$img_class} swiper-lazy' data-src='{$imgSrc}' />"; // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
				}
			}

			if ( $imgSrc && $cSize ) {
				$w = ( ! empty( $customImgSize['width'] ) ? absint( $customImgSize['width'] ) : null );
				$h = ( ! empty( $customImgSize['height'] ) ? absint( $customImgSize['height'] ) : null );
				$c = ( ! empty( $customImgSize['crop'] ) && 'soft' === $customImgSize['crop'] ? false : true );

				$actual_dimension = wp_get_attachment_metadata( $thumb_id, true );

				if ( empty( $actual_dimension ) && $defaultImgId ) {
					$actual_dimension = wp_get_attachment_metadata( $defaultImgId, true );
				}

				$actual_w = $actual_dimension['width'];
				$actual_h = $actual_dimension['height'];

				if ( $w && $h ) {
					if ( $w >= $actual_w || $h >= $actual_h ) {
						$w = 150;
						$h = 150;
					}

					$imgSrc = esc_url( TSSPro()->rtImageReSize( $imgSrc, $w, $h, $c ) );
					$image  = "<img alt='{$alt}' class='{$img_class}' src='{$imgSrc}' />"; // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage

					if ( $data_src ) {
						$image = "<img alt='{$alt}' class='{$img_class} swiper-lazy' data-src='{$imgSrc}' />"; // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
					}
				}
			}

			if ( ! $image ) {
				$imgSrc = esc_url( TSSPro()->placeholder_img_src() );
				$image  = "<img alt='{$alt}' class='{$img_class} rt-dummy-img' src='{$imgSrc}' />"; // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
			}

			return $image;
		}

		public function rtImageReSize( $url, $width = null, $height = null, $crop = null, $single = true, $upscale = false ) {
			$rtResize = new TSSProReSizer();

			return $rtResize->process( $url, $width, $height, $crop, $single, $upscale );
		}

		public function strip_tags_content( $text, $limit = 0, $link = '' ) {
			$text = preg_replace( '@<(\w+)\b.*?>.*?</\1>@si', '', $text );
			$text = esc_html( $text );

			if ( $limit > 0 && strlen( $text ) > $limit ) {
				$text = substr( $text, 0, $limit );
				$text = $text . ' ' . $link;
			}

			return $text;
		}

		/**
		 * id => name for one of this plugin's taxonomies, for a control's options.
		 *
		 * Shared by the three public list helpers below. Like the testimonial list,
		 * these only ever fill in a control's option list, so they are skipped
		 * where no panel is drawn and cached for the rest of the request — a page
		 * with two widgets asked for the same terms four times over.
		 *
		 * @param string $taxonomy Taxonomy key.
		 * @return array
		 */
		private function tssTermOptions( $taxonomy ) {
			if ( ! $this->tssNeedsControlOptions() ) {
				return [];
			}

			static $cache = [];

			if ( isset( $cache[ $taxonomy ] ) ) {
				return $cache[ $taxonomy ];
			}

			$terms    = [];
			$termList = get_terms(
				[
					'taxonomy'   => $taxonomy,
					'hide_empty' => false,
				]
			);

			if ( is_array( $termList ) && ! empty( $termList ) && empty( $termList['errors'] ) ) {
				foreach ( $termList as $term ) {
					$terms[ $term->term_id ] = $term->name;
				}
			}

			$cache[ $taxonomy ] = $terms;

			return $terms;
		}

		public function getAllTssCategoryList() {
			return $this->tssTermOptions( TSSPro()->taxonomies['category'] );
		}

		public function getAllTssCategoryListWithDefault() {
			$terms = $this->tssTermOptions( TSSPro()->taxonomies['category'] );

			// The "Show All" entry belongs to the option list, so it goes only where
			// the list itself does — an empty list means nobody is looking.
			if ( empty( $terms ) ) {
				return [];
			}

			return [ 'all' => esc_html__( 'Show All', 'testimonial-slider-showcase' ) ] + $terms;
		}

		public function getAllTssTagList() {
			return $this->tssTermOptions( TSSPro()->taxonomies['tag'] );
		}

		public function getAllTaxonomyObject() {
			$taxonomy_objects = get_object_taxonomies( TSSPro()->post_type, 'objects' );
			$taxonomy_list    = [];

			if ( ! empty( $taxonomy_objects ) ) {
				foreach ( $taxonomy_objects as $taxonomy ) {
					if ( ! in_array( $taxonomy->name, [ 'language', 'post_translations' ] ) ) {
						$taxonomy_list[] = $taxonomy;
					}
				}
			}

			return $taxonomy_list;
		}


		public function getPortfolioList() {
			$portfolios = [];
			$portQ      = new WP_Query(
				[
					'post_type'      => TSSPro()->post_type,
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'orderby'        => 'title',
					'order'          => 'ASC',
				]
			);

			if ( $portQ->have_posts() ) {
				while ( $portQ->have_posts() ) {
					$portQ->the_post();
					$portfolios[ get_the_ID() ] = get_the_title();
				}
			}

			wp_reset_postdata();

			return $portfolios;
		}

		public function getPortfolioListExceptId( $id = null ) {
			$id         = ( $id ? intval( $id ) : get_the_ID() );
			$portfolios = [];

			if ( $id ) {
				$portQ = get_posts(
					[
						'post_type'      => TSSPro()->post_type,
						'post_status'    => 'publish',
						'posts_per_page' => -1,
						'orderby'        => 'title',
						'post__not_in'   => [ $id ], // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
						'order'          => 'ASC',
					]
				);

				if ( ! empty( $portQ ) ) {
					foreach ( $portQ as $port ) {
						$portfolios[ $port->ID ] = $port->post_title;
					}
				}
			}

			return $portfolios;
		}

		public function TLPhex2rgba( $color, $opacity = false ) {
			$default = 'rgb(0,0,0)';

			if ( empty( $color ) ) {
				return $default;
			}

			if ( $color[0] == '#' ) {
				$color = substr( $color, 1 );
			}

			if ( strlen( $color ) == 6 ) {
				$hex = [ $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] ];
			} elseif ( strlen( $color ) == 3 ) {
				$hex = [ $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] ];
			} else {
				return $default;
			}

			$rgb = array_map( 'hexdec', $hex );

			if ( $opacity ) {
				if ( abs( $opacity ) > 1 ) {
					$opacity = 1.0;
				}

				$output = 'rgba(' . implode( ',', $rgb ) . ',' . $opacity . ')';
			} else {
				$output = 'rgb(' . implode( ',', $rgb ) . ')';
			}

			return $output;
		}

		public function tlp_custom_pagination( $numpages = '', $pagerange = '', $paged = '' ) {
			if ( empty( $pagerange ) ) {
				$pagerange = 2;
			}

			global $paged;

			if ( empty( $paged ) ) {
				$paged = 1;
			}

			if ( $numpages == '' ) {
				global $wp_query;
				$numpages = $wp_query->max_num_pages;

				if ( ! $numpages ) {
					$numpages = 1;
				}
			}

			/**
			 * We construct the pagination arguments to enter into our paginate_links
			 * function.
			 */
			$pagination_args = [
				'base'               => get_pagenum_link( 1 ) . '%_%',
				'format'             => 'page/%#%',
				'total'              => $numpages,
				'current'            => $paged,
				'show_all'           => false,
				'end_size'           => 1,
				'mid_size'           => $pagerange,
				'prev_next'          => true,
				'prev_text'          => esc_html__( '&laquo;', 'testimonial-slider-showcase' ),
				'next_text'          => esc_html__( '&raquo;', 'testimonial-slider-showcase' ),
				'type'               => 'list',
				'add_args'           => false,
				'add_fragment'       => '',
				'before_page_number' => '',
				'after_page_number'  => '',
			];

			$paginate_links = paginate_links( $pagination_args );
			$html           = null;

			if ( $paginate_links ) {
				$html .= "<nav class='tlp-pagination'>";
				$html .= $paginate_links;
				$html .= '</nav>';
			}

			return $html;
		}

		public function pagination( $pages = '', $range = 4, $ajax = false, $scID = '', $scMeta = null ) {
			$html      = null;
			$showitems = ( $range * 2 ) + 1;

			global $paged;

			if ( is_front_page() ) {
				$paged = ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1;
			} else {
				$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
			}

			if ( empty( $paged ) ) {
				$paged = 1;
			}

			if ( $pages == '' ) {
				global $wp_query;
				$pages = $wp_query->max_num_pages;

				if ( ! $pages ) {
					$pages = 1;
				}
			}

			$ajaxClass = null;
			$dataAttr  = null;

			if ( $ajax ) {
				$ajaxClass = ' tss-ajax';
				$dataAttr  = "data-sc-id='{$scID}' data-paged='1'";
			}

			if ( 'elementor' == $scID ) {
				$cImageSize         = ! empty( $scMeta['tss_el_image_custom_dimension'] ) ? $scMeta['tss_el_image_custom_dimension'] : [];
				$cImageSize['crop'] = ! empty( $scMeta['tss_el_image_crop'] ) ? $scMeta['tss_el_image_crop'] : '';

				$elData = [
					'tss_layout'                => $scMeta['tss_el_layout_type'],
					'tss_desktop_column'        => $scMeta['tss_el_cols'],
					'tss_tab_column'            => isset( $scMeta['tss_el_cols_tablet'] ) && $scMeta['tss_el_cols_tablet'] != '' ? absint( $scMeta['tss_el_cols_tablet'] ) : 2,
					'tss_mobile_column'         => isset( $scMeta['tss_el_cols_mobile'] ) && $scMeta['tss_el_cols_mobile'] != '' ? absint( $scMeta['tss_el_cols_mobile'] ) : 1,
					'tss_image_size'            => $scMeta['tss_el_image'],
					'custom_img_size'           => ! empty( $cImageSize ) ? $cImageSize : [],
					'default_image'             => ! empty( $scMeta['tss_el_image_default_preview']['id'] ) ? absint( $scMeta['tss_el_image_default_preview']['id'] ) : null,
					'tss_post__in'              => $scMeta['tss_el_include_posts'],
					'tss_post__not_in'          => $scMeta['tss_el_exclude_posts'],
					'tss_limit'                 => $scMeta['tss_el_posts_limit'],
					'tss_pagination'            => $scMeta['tss_el_pagination'],
					'tss_posts_per_page'        => $scMeta['tss_el_pagination_per_page'],
					'tss_categories'            => ! empty( $scMeta['tss_el_categories'] ) ? $scMeta['tss_el_categories'] : [],
					'tss_tags'                  => ! empty( $scMeta['tss_el_tags'] ) ? $scMeta['tss_el_tags'] : [],
					'tss_taxonomy_relation'     => $scMeta['tss_el_taxonomy_relation'],
					'tss_order_by'              => $scMeta['tss_el_posts_order_by'],
					'tss_order'                 => $scMeta['tss_el_posts_order'],
					'tss_testimonial_limit'     => $scMeta['tss_el_testimonial_text_limit'],
					'tss_grid_style'            => $scMeta['tss_el_grid_style'],
					'tss_read_more_button_text' => $scMeta['tss_el_read_more_text'],
					'tss_item_fields'           => $this->tssElContentVisibility( $scMeta ),
					'tss_share_fields'          => $scMeta['tss_el_social_share_items'],
					'tss_el_social_label'       => isset( $scMeta['tss_el_social_label'] ) ? $scMeta['tss_el_social_label'] : null,
					'tss_el_social_share_label' => isset( $scMeta['tss_el_social_share_label'] ) ? $scMeta['tss_el_social_share_label'] : null,
					'tss_detail_page_link'      => $scMeta['tss_el_detail_page_link'],
				];

				$dataAttr = 'data-paged="1" data-tss-elementor=\'' . wp_json_encode( $elData ) . '\'';
			}

			if ( 1 != $pages ) {
				$html .= '<div class="tss-pagination' . $ajaxClass . '" ' . $dataAttr . '>';
				$html .= '<ul class="pagination-list">';

				if ( $paged > 2 && $paged > $range + 1 && $showitems < $pages ) {
					$html .= "<li><a data-paged='1' href='" . get_pagenum_link( 1 ) . "' aria-label='First'>&laquo;</a></li>";
				}

				if ( $paged > 1 && $showitems < $pages ) {
					$p     = $paged - 1;
					$html .= "<li><a data-paged='{$p}' href='" . get_pagenum_link( $p ) . "' aria-label='Previous'>&lsaquo;</a></li>";
				}

				for ( $i = 1; $i <= $pages; $i++ ) {
					if ( 1 != $pages && ( ! ( $i >= $paged + $range + 1 || $i <= $paged - $range - 1 ) || $pages <= $showitems ) ) {
						$html .= ( $paged == $i ) ? '<li class="active"><span>' . $i . '</span>
						</li>' : "<li><a data-paged='{$i}' href='" . get_pagenum_link( $i ) . "'>" . $i . '</a></li>';
					}
				}

				if ( $paged < $pages && $showitems < $pages ) {
					$p     = $paged + 1;
					$html .= "<li><a data-paged='{$p}' href=\"" . get_pagenum_link( $paged + 1 ) . "\"  aria-label='Next'>&rsaquo;</a></li>";
				}

				if ( $paged < $pages - 1 && $paged + $range - 1 < $pages && $showitems < $pages ) {
					$html .= "<li><a data-paged='{$pages}' href='" . get_pagenum_link( $pages ) . "' aria-label='Last'>&raquo;</a></li>";
				}

				$html .= '</ul>';
				$html .= '</div>';
			}

			return $html;
		}

		public function rt_pagination( $pages = '', $range = 4 ) {
			$html      = null;
			$showitems = ( $range * 2 ) + 1;

			global $paged;

			if ( empty( $paged ) ) {
				$paged = 1;
			}

			if ( $pages == '' ) {
				global $wp_query;
				$pages = $wp_query->max_num_pages;

				if ( ! $pages ) {
					$pages = 1;
				}
			}

			if ( 1 != $pages ) {

				$html .= '<div class="rt-pagination">';
				$html .= '<ul class="pagination"><li class="disabled hidden-xs"><span><span aria-hidden="true">Page ' . $paged . ' of ' . $pages . '</span></span></li>';

				if ( $paged > 2 && $paged > $range + 1 && $showitems < $pages ) {
					$html .= "<li><a href='" . get_pagenum_link( 1 ) . "' aria-label='First'>&laquo;<span class='hidden-xs'> First</span></a></li>";
				}

				if ( $paged > 1 && $showitems < $pages ) {
					$html .= "<li><a href='" . get_pagenum_link( $paged - 1 ) . "' aria-label='Previous'>&lsaquo;<span class='hidden-xs'> Previous</span></a></li>";
				}

				for ( $i = 1; $i <= $pages; $i++ ) {
					if ( 1 != $pages && ( ! ( $i >= $paged + $range + 1 || $i <= $paged - $range - 1 ) || $pages <= $showitems ) ) {
						$html .= ( $paged == $i ) ? '<li class="active"><span>' . $i . '</span>
						</li>' : "<li><a href='" . get_pagenum_link( $i ) . "'>" . $i . '</a></li>';
					}
				}

				if ( $paged < $pages && $showitems < $pages ) {
					$html .= '<li><a href="' . get_pagenum_link( $paged + 1 ) . "\"  aria-label='Next'><span class='hidden-xs'>Next </span>&rsaquo;</a></li>";
				}

				if ( $paged < $pages - 1 && $paged + $range - 1 < $pages && $showitems < $pages ) {
					$html .= "<li><a href='" . get_pagenum_link( $pages ) . "' aria-label='Last'><span class='hidden-xs'>Last </span>&raquo;</a></li>";
				}

				$html .= '</ul>';
				$html .= '</div>';
			}

			return $html;

		}

		public function placeholder_img_src() {
			return TSSPro()->assetsUrl . 'images/placeholder.png';
		}

		public function get_image_sizes() {
			global $_wp_additional_image_sizes;

			$sizes = [];

			foreach ( get_intermediate_image_sizes() as $_size ) {
				if ( in_array( $_size, [ 'thumbnail', 'medium', 'large' ] ) ) {
					$sizes[ $_size ]['width']  = get_option( "{$_size}_size_w" );
					$sizes[ $_size ]['height'] = get_option( "{$_size}_size_h" );
					$sizes[ $_size ]['crop']   = (bool) get_option( "{$_size}_crop" );
				} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
					$sizes[ $_size ] = [
						'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
						'height' => $_wp_additional_image_sizes[ $_size ]['height'],
						'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
					];
				}
			}

			$imgSize = [];

			foreach ( $sizes as $key => $img ) {
				$imgSize[ $key ] = ucfirst( $key ) . " ({$img['width']}*{$img['height']})";
			}

			if ( function_exists( 'rttsp' ) ) {
				$imgSize['tss_custom'] = esc_html__( 'Custom image size', 'testimonial-slider-showcase' );
			}

			return $imgSize;
		}


		public function get_shortCode_list() {
			$list   = [];
			$scList = get_posts(
				[
					'post_type'      => TSSPro()->shortCodePT,
					'posts_per_page' => -1,
					'post_status'    => 'publish',
					'orderby'        => 'title',
					'order'          => 'ASC',
				]
			);

			if ( $scList && ! empty( $scList ) ) {
				foreach ( $scList as $sc ) {
					$list[ $sc->ID ] = $sc->post_title;
				}
			}

			return $list;
		}

		public function socialShare( $pLink ) {
			$html  = null;
			$html .= "<div class='single-portfolio-share'>
						<div class='fb-share'>
							<div class='fb-share-button' data-href='{$pLink}' data-layout='button_count'></div>
						</div>
						<div class='twitter-share'>
							<a href='{$pLink}' class='twitter-share-button'{count} data-url='https://about.twitter.com/resources/buttons#tweet'>Tweet</a>
						</div>
						<div class='googleplus-share'>
							<div class='g-plusone'></div>
						</div>
						<div class='linkedin-share'>
							<script type='IN/Share' data-counter='right'></script>
						</div>
						<div class='linkedin-share'>
							<a data-pin-do='buttonPin' data-pin-count='beside' href='https://www.pinterest.com/pin/create/button/?url=https%3A%2F%2Fwww.flickr.com%2Fphotos%2Fkentbrew%2F6851755809%2F&media=https%3A%2F%2Ffarm8.staticflickr.com%2F7027%2F6851755809_df5b2051c9_z.jpg&description=Next%20stop%3A%20Pinterest'><img src='//assets.pinterest.com/images/pidgets/pinit_fg_en_rect_gray_20.png' /></a>
						</div>
					</div>";
			$html .= '<div id="fb-root"></div>';

            wp_enqueue_script('facebook-sdk');
            wp_enqueue_script('twitter-widgets');
            wp_enqueue_script('google-plus');
            wp_enqueue_script('linkedin-sdk');
            wp_enqueue_script('pinterest-sdk');

			return $html;
		}

		public function doFlush() {
			if ( get_option( TSSPro()->options['flash'] ) ) {
				TSSPro()->flush_rewrite();
				update_option( TSSPro()->options['flash'], false );
			}
		}

		/**
		 * True per-category totals for an isotope wall.
		 *
		 * The filter buttons used to be counted client-side, from whatever
		 * happened to be in the DOM. With pagination on that is only the first
		 * page, so "Clients 2" meant "2 of the Clients loaded so far" while the
		 * Load More button beside it counted down the WHOLE query — two numbers
		 * that could never agree, and a pager that promised the same remainder no
		 * matter which tab was open.
		 *
		 * This runs the wall's own query once more, IDs only and without paging,
		 * and tallies the category terms across the result, so every button can
		 * carry the real number and the pager can count down the active one. Two
		 * extra queries per render, both served from the object cache on repeat.
		 *
		 * @param array $args  The wall's WP_Query args.
		 * @param int   $limit Wall size cap — the shortcode's own "limit" option.
		 *                     0 means uncapped.
		 * @return array Map of term_id => count, plus 'all' => wall size.
		 */
		/**
		 * Every post id the wall covers, in wall order, ignoring paging.
		 *
		 * This is the universe an isotope filter works over: the shortcode's own
		 * query capped by its "limit" option. Paging a single category has to stay
		 * inside it, or a wall limited to 12 would start serving the 13th and 14th
		 * post as soon as a category was opened.
		 *
		 * @param array $args  The wall's WP_Query args.
		 * @param int   $limit Wall size cap. 0 means uncapped.
		 * @return array
		 */
		public function isoWallIds( array $args, $limit = 0 ) {
			$args['posts_per_page']         = $limit > 0 ? $limit : -1;
			$args['fields']                 = 'ids';
			$args['no_found_rows']          = true;
			$args['update_post_meta_cache'] = false;
			$args['update_post_term_cache'] = false;
			$args['ignore_sticky_posts']    = true;

			unset( $args['paged'], $args['offset'] );

			$query = new WP_Query( $args );

			return $query->posts;
		}

		public function isoFilterTotals( array $args, $limit = 0 ) {
			$ids    = $this->isoWallIds( $args, $limit );
			$totals = [ 'all' => count( $ids ) ];

			if ( empty( $ids ) ) {
				return $totals;
			}

			// 'all_with_object_id' keeps one row per relationship. Plain 'ids'
			// de-duplicates across the whole set, which would report 1 per term.
			$rows = wp_get_object_terms(
				$ids,
				TSSPro()->taxonomies['category'],
				[ 'fields' => 'all_with_object_id' ]
			);

			if ( is_wp_error( $rows ) ) {
				return $totals;
			}

			foreach ( $rows as $row ) {
				$totals[ $row->term_id ] = isset( $totals[ $row->term_id ] ) ? $totals[ $row->term_id ] + 1 : 1;
			}

			return $totals;
		}

		/**
		 * Narrows a wall's args to one isotope category, page by page.
		 *
		 * A category tab used to be a browser-side filter over whatever had been
		 * paged in, which is why "5 per page" meant five of the WHOLE wall and a
		 * category could show two cards under a tab reading six. Pointing the query
		 * itself at the category makes "5 per page" mean five of that category, and
		 * lets found_posts / max_num_pages come back describing the filtered set so
		 * the pager counts down the right thing.
		 *
		 * Restricted to the wall's own universe (see isoWallIds) so the "limit"
		 * option still holds and the numbers match the tab counts, which are
		 * computed over that same universe.
		 *
		 * @param array $args  The wall's WP_Query args, already built.
		 * @param int   $term  Category term id.
		 * @param int   $limit Wall size cap. 0 means uncapped.
		 * @param int   $perPage Page size.
		 * @param int   $paged Page number, 1-based.
		 * @return array
		 */
		public function isoFilteredArgs( array $args, $term, $limit, $perPage, $paged ) {
			$ids = $this->isoWallIds( $args, $limit );

			// An empty post__in matches everything, so stand in an impossible id
			// rather than silently serving the whole wall.
			$args['post__in'] = ! empty( $ids ) ? $ids : [ 0 ];
			$args['orderby']  = 'post__in';

			$taxQ = isset( $args['tax_query'] ) ? $args['tax_query'] : [];

			$taxQ[] = [
				'taxonomy' => TSSPro()->taxonomies['category'],
				'field'    => 'term_id',
				'terms'    => [ absint( $term ) ],
			];

			if ( count( $taxQ ) > 1 ) {
				$taxQ['relation'] = 'AND';
			}

			$args['tax_query']      = $taxQ; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			$args['posts_per_page'] = absint( $perPage );
			$args['paged']          = max( 1, absint( $paged ) );

			unset( $args['offset'] );

			return $args;
		}

		/**
		 * Paging state for an isotope wall whose pager cannot carry it.
		 *
		 * The category filter runs in the browser, over whatever has been paged in,
		 * so opening a tab has to be able to fetch the rest of that category. The
		 * "Load More" button already carries the numbers needed to do that; with
		 * numbered pagination or load-on-scroll there is nowhere for the script to
		 * read them from, and the tab just sits there reading "Developers 6" over
		 * two cards with nothing to click.
		 *
		 * Deliberately NOT emitted for the Load More type: exactly one element must
		 * own the current page number, never two, or they drift apart and pages get
		 * fetched twice.
		 *
		 * @param array  $atts  data-* attributes, without the "data-" prefix.
		 * @param string $extra Raw extra attributes (the Elementor arm's payload).
		 * @return string
		 */
		/**
		 * Orderby with the post ID as a tie-break.
		 *
		 * Testimonials imported together share a date (and keep menu order 0),
		 * and MySQL is free to order ties differently in the page query and in
		 * each Load More / scroll query - so a later page could repeat an item
		 * and skip another. The ID makes the order total.
		 *
		 * @param string      $orderBy Orderby key.
		 * @param string|null $order   ASC or DESC.
		 * @return string|array
		 */
		public function tssOrderBy( $orderBy, $order = null ) {
			if ( ! in_array( $orderBy, [ 'menu_order', 'title', 'date' ], true ) ) {
				return $orderBy;
			}

			$order = 'ASC' === strtoupper( (string) $order ) ? 'ASC' : 'DESC';

			return [
				$orderBy => $order,
				'ID'     => $order,
			];
		}

		/**
		 * Post statuses a Load More / Load on Scroll request may return.
		 *
		 * Those requests go through admin-ajax.php, where is_admin() is true and
		 * WP_Query without a post_status also returns draft, pending and future
		 * posts - front-end submissions are saved as drafts awaiting approval.
		 * Matches what the page render shows: published, plus private for users
		 * who may read it.
		 *
		 * @return array
		 */
		public function tssLoadMoreStatus() {
			return current_user_can( 'read_private_posts' ) ? [ 'publish', 'private' ] : [ 'publish' ];
		}

		public function isoPagerState( array $atts, $extra = '' ) {
			$html = "<span class='tss-iso-pager-state'";

			foreach ( $atts as $key => $value ) {
				$html .= ' data-' . esc_attr( $key ) . "='" . esc_attr( $value ) . "'";
			}

			return $html . ' ' . $extra . '></span>';
		}

		/**
		 * The data-filter-total attribute for one isotope filter button.
		 *
		 * Emitted by all three renderers, so it lives here rather than being
		 * written out three times. Returns an empty string when there is no total
		 * to give — the rating filter has no category tally — and the script then
		 * falls back to counting the DOM, which is the old behaviour.
		 *
		 * @param array           $totals Output of isoFilterTotals().
		 * @param int|string|null $term   Term id, or 'all' for the Show all button.
		 * @return string
		 */
		public function isoFilterTotalAttr( $totals, $term ) {
			// No tally at all — the rating filter has none — so say nothing and let
			// the script fall back to counting the DOM.
			if ( ! is_array( $totals ) || ! isset( $totals['all'] ) ) {
				return '';
			}

			// A term with no members in this wall is absent from the tally, but the
			// answer for it is a definite zero, not "unknown". Saying so keeps the
			// server authoritative for every button: an "Include Testimonials"
			// filter can easily leave a category empty.
			$count = isset( $totals[ $term ] ) ? $totals[ $term ] : 0;

			return " data-filter-total='" . absint( $count ) . "'";
		}

		/**
		 * CSS for the Author Name / Author Bio Alignment options, shared by the
		 * shortcode (layoutStyle()) and the Elementor controls.
		 *
		 * text-align alone only moves text inside a box that is wider than it. In
		 * several layouts the name or bio box is exactly as wide as its text (a
		 * flex item in a centred column, or an inline-block name), so the auto
		 * margin moves the box itself. Only the side being aligned towards gets
		 * one, so each layout keeps its own spacing on the other side.
		 *
		 * @param string $align left, center or right; anything else (the
		 *                      shortcode's "justify") is text-align only.
		 * @return string
		 */
		public function boxAlignCss( $align ) {
			$map = [
				'left'   => 'text-align:left;margin-right:auto;',
				'center' => 'text-align:center;margin-left:auto;margin-right:auto;',
				'right'  => 'text-align:right;margin-left:auto;',
			];

			return isset( $map[ $align ] ) ? $map[ $align ] : 'text-align:' . $align . ';';
		}

		/**
		 * Selector for the layouts that set the author name to inline-block. That
		 * box is exactly as wide as the text, and an inline box ignores auto
		 * margins, so neither boxAlignCss() mechanism can move it.
		 * inlineNameBlockCss() turns it into a block of the same width.
		 *
		 * @param string $prefix Selector up to and including the .tss-wrapper part.
		 * @return string
		 */
		public function inlineNameSelector( $prefix ) {
			$sel = [];

			foreach ( [ 'layout5', 'carousel5', 'layout6', 'carousel6', 'layout7', 'carousel7', 'layout8', 'carousel8' ] as $layout ) {
				$sel[] = "{$prefix} .tss-{$layout} .single-item-wrapper h3.author-name";
			}

			return implode( ', ', $sel );
		}

		/**
		 * Declarations for inlineNameSelector().
		 *
		 * @return string
		 */
		public function inlineNameBlockCss() {
			return 'display:block;width:-webkit-fit-content;width:-moz-fit-content;width:fit-content;max-width:100%;';
		}

		/**
		 * CSS for the rating Alignment option, shared by the shortcode
		 * (layoutStyle()) and the Elementor control so the two arms stay in step.
		 *
		 * Most layouts size .rating-wrapper to the stars themselves (fit-content,
		 * inline-flex, or a flex item), so text-align alone has no room to act.
		 * Auto margins move the box inside its column, justify-content covers the
		 * flex wrappers, and text-align covers the full-width ones. Two families
		 * need more:
		 * - video: the stars sit at the end of the author row after a name block
		 *   that grows to fill it, so the wrapper takes the free space instead
		 *   and text-align places the stars inside it;
		 * - layout 10: the stars flow beside the floated photo, where a box pushed
		 *   right with an auto margin drops below it, so the wrapper goes full
		 *   width and text-align does the work.
		 *
		 * @param string $align left, center or right; anything else aligns left.
		 * @return array Declarations keyed wrapper / video / layout10.
		 */
		public function ratingAlignCss( $align ) {
			$map = [
				'left'   => 'text-align:left;justify-content:flex-start;margin-left:0;margin-right:auto;',
				'center' => 'text-align:center;justify-content:center;margin-left:auto;margin-right:auto;',
				'right'  => 'text-align:right;justify-content:flex-end;margin-left:auto;margin-right:0;',
			];

			return [
				'wrapper'  => isset( $map[ $align ] ) ? $map[ $align ] : $map['left'],
				'video'    => 'flex-grow:9999;width:auto;margin-left:0;margin-right:0;',
				'layout10' => 'width:auto;margin-left:0;margin-right:0;',
			];
		}


		public function layoutStyle( $layoutID, $meta, $scId = null ) {
			$css  = null;
			$css .= "<style type='text/css' media='all'>";

			// Layout 9 (and its carousel / isotope twins) centres the name, bio and
			// stars in an overlay on the photo; the Elementor arm hides those three
			// Alignment controls there, so the shortcode ignores them to match.
			$layout        = $scId ? ( isset( $meta['tss_layout'][0] ) ? $meta['tss_layout'][0] : '' ) : ( isset( $meta['tss_layout'] ) ? $meta['tss_layout'] : '' );
			$layoutAligned = ! in_array( $layout, [ 'layout9', 'carousel9', 'isotope4' ], true );

			// Variable
			if ( $scId ) {
				$colorSchema = ( ! empty( $meta['tss_color'][0] ) ? unserialize( $meta['tss_color'][0] ) : null );
				$button      = ( ! empty( $meta['tss_button_style'][0] ) ? unserialize( $meta['tss_button_style'][0] ) : null );
				$imgBorder   = ( ! empty( $meta['tss_image_border'][0] ) ? unserialize( $meta['tss_image_border'][0] ) : null );
				$tooltipBg   = ( ! empty( $meta['tss_iso_counter_tooltip_bg_color'][0] ) ? esc_attr( $meta['tss_iso_counter_tooltip_bg_color'][0] ) : null );
				$tooltipText = ( ! empty( $meta['tss_iso_counter_tooltip_text_color'][0] ) ? esc_attr( $meta['tss_iso_counter_tooltip_text_color'][0] ) : null );
				$gutter      = ! empty( $meta['tss_gutter'][0] ) ? absint( $meta['tss_gutter'][0] ) : null;
				$name        = ( ! empty( $meta['tss_author_name_style'][0] ) ? unserialize( $meta['tss_author_name_style'][0] ) : [] );
				$author_bio  = ( ! empty( $meta['tss_author_bio_style'][0] ) ? unserialize( $meta['tss_author_bio_style'][0] ) : [] );

				$rating      = ( ! empty( $meta['tss_rating_style'][0] ) ? unserialize( $meta['tss_rating_style'][0] ) : [] );
				$social      = ( ! empty( $meta['tss_social_style'][0] ) ? unserialize( $meta['tss_social_style'][0] ) : [] );
				$testimonial = ( ! empty( $meta['tss_testimonial_style'][0] ) ? unserialize( $meta['tss_testimonial_style'][0] ) : [] );
				$quote       = ( ! empty( $meta['tss_quote_style'][0] ) ? unserialize( $meta['tss_quote_style'][0] ) : [] );
				$overlay     = ( ! empty( $meta['tss_overlay_style'][0] ) ? unserialize( $meta['tss_overlay_style'][0] ) : null );
			} else {
				$colorSchema = ( ! empty( $meta['tss_color'] ) ? array_filter( $meta['tss_color'] ) : null );
				$button      = ( ! empty( $meta['tss_button_style'] ) ? array_filter( $meta['tss_button_style'] ) : null );
				$imgBorder   = ( ! empty( $meta['tss_image_border'] ) ? array_filter( $meta['tss_image_border'] ) : [] );
				$tooltipBg   = ( ! empty( $meta['tss_iso_counter_tooltip_bg_color'] ) ? esc_attr( $meta['tss_iso_counter_tooltip_bg_color'] ) : null );
				$tooltipText = ( ! empty( $meta['tss_iso_counter_tooltip_text_color'] ) ? esc_attr( $meta['tss_iso_counter_tooltip_text_color'] ) : null );
				$gutter      = ! empty( $meta['tss_gutter'] ) ? absint( $meta['tss_gutter'] ) : null;

				$name        = ( ! empty( $meta['tss_author_name_style'] ) ? array_map( 'sanitize_text_field', $meta['tss_author_name_style'] ) : [] );
				$author_bio  = ( ! empty( $meta['tss_author_bio_style'] ) ? array_map( 'sanitize_text_field', $meta['tss_author_bio_style'] ) : [] );
				$rating      = ( ! empty( $meta['tss_rating_style'] ) ? array_map( 'sanitize_text_field', $meta['tss_rating_style'] ) : [] );
				$social      = ( ! empty( $meta['tss_social_style'] ) ? array_map( 'sanitize_text_field', $meta['tss_social_style'] ) : [] );
				$testimonial = ( ! empty( $meta['tss_testimonial_style'] ) ? array_map( 'sanitize_text_field', $meta['tss_testimonial_style'] ) : [] );
				$quote       = ( ! empty( $meta['tss_quote_style'] ) ? array_map( 'sanitize_text_field', $meta['tss_quote_style'] ) : [] );
				$overlay     = ( ! empty( $meta['tss_overlay_style'] ) ? array_map( 'sanitize_text_field', $meta['tss_overlay_style'] ) : null );
			}

			if ( $colorSchema ) {
				if ( ! empty( $colorSchema['primary'] ) ) {
					$css .= "#{$layoutID}.tss-wrapper .tss-carousel1,
							#{$layoutID}.tss-wrapper .tss-isotope1 .profile-img-wrapper:after,
							#{$layoutID}.tss-wrapper .tss-layout9 .profile-img-wrapper:after,
							#{$layoutID}.tss-wrapper .tss-isotope4 .profile-img-wrapper:after,
							#{$layoutID}.tss-wrapper .tss-carousel9 .profile-img-wrapper:after {
								background: {$colorSchema['primary']} !important;
							}";

					// Layouts built on the design tokens (layout 1) take their whole
					// accent palette from these, so Primary drives the accent bar,
					// quote mark, read-more link, name hover and pagination at once.
					$primary = $colorSchema['primary'];
					$css    .= "#{$layoutID}.tss-wrapper {
								--tss-primary: {$primary};
								--tss-primary-hover: color-mix(in srgb, {$primary} 80%, #000);
								--tss-primary-soft: color-mix(in srgb, {$primary} 12%, #fff);
							}";
				}
				if ( ! empty( $colorSchema['container_bg'] ) ) {
					$css .= "#{$layoutID}.tss-wrapper {
							background: {$colorSchema['container_bg']};}";
				}
				if ( ! empty( $colorSchema['item_bg'] ) ) {
					// Layout 4 / isotope 2 used to paint .profile-img-wrapper too. Since
					// the redesign that is a small square box round a circular photo,
					// so it drew a square block behind the photo. The Elementor
					// "Content Background" control drops the same two selectors.
					$css .= "#{$layoutID}.tss-wrapper .item-content-wrapper,
							#{$layoutID}.tss-wrapper .tss-layout10 .item-content-wrapper .item-content,
							#{$layoutID}.tss-wrapper .tss-carousel10 .item-content-wrapper .item-content,
							#{$layoutID}.tss-wrapper .tss-isotope5 .item-content-wrapper .item-content,
							#{$layoutID}.tss-wrapper .tss-layout7 .single-item-wrapper,
							#{$layoutID}.tss-wrapper .tss-carousel7 .single-item-wrapper,
							#{$layoutID}.tss-wrapper .tss-carousel8 .single-item-wrapper,
							#{$layoutID}.tss-wrapper .tss-carousel4 .single-item-wrapper,
							#{$layoutID}.tss-wrapper .tss-carousel5 .single-item-wrapper,
							#{$layoutID}.tss-wrapper .tss-layout8 .single-item-wrapper{ background: {$colorSchema['item_bg']};}";
					$css .= "#{$layoutID}.tss-wrapper .tss-layout1 .item-content-wrapper:after,
							#{$layoutID}.tss-wrapper .tss-carousel1 .item-content-wrapper:after{
								border-right: 15px solid {$colorSchema['item_bg']};}";
					$css .= "#{$layoutID}.tss-wrapper .tss-carousel2 .item-content-wrapper:after,
							#{$layoutID}.tss-wrapper .tss-layout2 .item-content-wrapper:after{
							border-left: 15px solid {$colorSchema['item_bg']};}";
					// Layout 6 and its carousel twin draw the tail as a rotated square,
					// not a CSS-border triangle, so it takes the item background as a
					// background. Emitting border-top here instead would draw a 15px
					// stripe across the tail.
					$css .= "#{$layoutID}.tss-wrapper .tss-layout6 .item-content-wrapper:after,
							#{$layoutID}.tss-wrapper .tss-carousel6 .item-content-wrapper:after{
								background: {$colorSchema['item_bg']};}";
					// Layout 10's bubble tab is a rotated square, not a CSS-border
					// triangle, so it takes the item background as a background —
					// same treatment as layout 6's tail. Emitting border-color here
					// instead would leave the tab unpainted.
					$css .= "#{$layoutID}.tss-wrapper .tss-layout10 .item-content:after,
							#{$layoutID}.tss-wrapper .tss-isotope5 .item-content:after,
							#{$layoutID}.tss-wrapper .tss-carousel10 .item-content:after{ background: {$colorSchema['item_bg']};}";
					// Carousels 11 and 12 have no box of their own: paint the quote
					// alone, not .item-content-wrapper, whose reserved read-more row left
					// the box flush with the text on top and ~30px of empty row below.
					// The inset is a same-colour border, not padding: the quote is
					// line-clamped, and overflow clips at the padding box, so padding
					// would show the clamped 7th line. The min-height keeps the base
					// rule's six lines, plus the two borders, so slides stay one height;
					// align-content centres a shorter quote in it rather than leaving the
					// spare lines under the text (-webkit-box-pack alone does nothing in
					// Chrome once line-clamp is set).
					// Matches the Elementor arm in pro's ElementorFilters.
					$css .= "#{$layoutID}.tss-wrapper .tss-carousel11 .item-content-wrapper,
							#{$layoutID}.tss-wrapper .tss-carousel12 .item-content-wrapper{ background: transparent;}";
					$css .= "#{$layoutID}.tss-wrapper .tss-carousel11 .item-content-wrapper .item-content,
							#{$layoutID}.tss-wrapper .tss-carousel12 .item-content-wrapper .item-content{
								background: {$colorSchema['item_bg']};
								border: solid {$colorSchema['item_bg']};
								border-width: clamp(20px, 3vw, 32px) clamp(20px, 4vw, 40px);
								border-radius: var(--tss-radius, 16px);
								max-width: calc(62ch + 2 * clamp(20px, 4vw, 40px));
								min-height: calc(9em + 2 * clamp(20px, 3vw, 32px));
								-webkit-box-pack: center;
								align-content: center;}";

				}
			}
			$css .= "#{$layoutID}.tss-wrapper .tss-layout10 .item-content-wrapper,
					#{$layoutID}.tss-wrapper .tss-isotope5 .item-content-wrapper,
					#{$layoutID}.tss-wrapper .tss-carousel10 .item-content-wrapper{background: transparent;}";
			/* button */
			if ( $button ) {
				if ( ! empty( $button['bg'] ) ) {
					// The active dot and the selected filter are left to the Active
					// colour (or the layout default); carousel 12 moves its arrows and
					// dots into .tss-carousel-controls once swiper has initialised.
					$css .= "#{$layoutID}.tss-wrapper .tss-utility button,
						#{$layoutID}.tss-wrapper .tss-carousel-main .swiper-arrow,
						#{$layoutID}.tss-wrapper .tss-carousel-main .swiper-pagination-bullet:not(.swiper-pagination-bullet-active),
						#{$layoutID}.tss-wrapper .tss-carousel .swiper-arrow,
						#{$layoutID}.tss-wrapper .tss-carousel .swiper-pagination-bullet:not(.swiper-pagination-bullet-active),
						#{$layoutID}.tss-wrapper .tss-carousel-controls .swiper-arrow,
						#{$layoutID}.tss-wrapper .tss-carousel-controls .swiper-pagination-bullet:not(.swiper-pagination-bullet-active),
						#{$layoutID}.tss-wrapper .tss-utility .rt-button,
						#{$layoutID}.tss-wrapper .tss-pagination ul.pagination-list li a,
						#{$layoutID}.tss-wrapper .tss-isotope-button-wrapper .rt-iso-button:not(.selected){";
					$css .= "background-color: {$button['bg']};";
					$css .= ! empty( $button['text'] ) ? "color: {$button['text']};" : null;
					$css .= '}';
				}

				if ( ! empty( $button['hover_bg'] ) ) {
					$css .= "#{$layoutID}.tss-wrapper .tss-carousel-main .swiper-arrow:hover,
							#{$layoutID}.tss-wrapper .tss-carousel-main .swiper-pagination-bullet:not(.swiper-pagination-bullet-active):hover,
							#{$layoutID}.tss-wrapper .tss-carousel .swiper-arrow:hover,
							#{$layoutID}.tss-wrapper .tss-carousel .swiper-pagination-bullet:not(.swiper-pagination-bullet-active):hover,
							#{$layoutID}.tss-wrapper .tss-carousel-controls .swiper-arrow:hover,
							#{$layoutID}.tss-wrapper .tss-carousel-controls .swiper-pagination-bullet:not(.swiper-pagination-bullet-active):hover,
							#{$layoutID}.tss-wrapper .tss-utility button:hover,
							#{$layoutID}.tss-wrapper .tss-utility .rt-button:hover,
							#{$layoutID}.tss-wrapper .tss-pagination ul.pagination-list li a:hover,
							#{$layoutID}.tss-wrapper .tss-isotope-button-wrapper .rt-iso-button:not(.selected):hover{";
					$css .= "background-color: {$button['hover_bg']};";
					$css .= '}';
				}

				if ( ! empty( $button['active_bg'] ) ) {
					$css .= "#{$layoutID}.tss-wrapper .tss-carousel-main .swiper-pagination-bullet-active,
							#{$layoutID}.tss-wrapper .tss-carousel .swiper-pagination-bullet-active,
							#{$layoutID}.tss-wrapper .tss-carousel-controls .swiper-pagination-bullet-active,
							#{$layoutID}.tss-wrapper .tss-pagination ul.pagination-list li.active span,
							#{$layoutID}.tss-wrapper .tss-isotope-button-wrapper .rt-iso-button.selected{";
					$css .= "background-color: {$button['active_bg']};";
					$css .= '}';
				}
				if ( ! empty( $button['text'] ) ) {
					$css .= "#{$layoutID}.tss-wrapper .tss-pagination ul.pagination-list li a,
							#{$layoutID}.tss-wrapper .tss-utility .rt-button,
							#{$layoutID}.tss-wrapper .tss-carousel-main .swiper-arrow > i,
							#{$layoutID}.tss-wrapper .tss-carousel .swiper-arrow > i,
							#{$layoutID}.tss-wrapper .tss-carousel-controls .swiper-arrow > i{";
					$css .= "color: {$button['text']};";
					$css .= '}';
				}
				if ( ! empty( $button['hover_text'] ) ) {
					$css .= "#{$layoutID}.tss-wrapper .tss-pagination ul.pagination-list li a:hover,
								#{$layoutID}.tss-wrapper .tss-isotope-button-wrapper .rt-iso-button:not(.selected):hover,
								#{$layoutID}.tss-wrapper .tss-utility .rt-button:hover,
								#{$layoutID}.tss-wrapper .tss-carousel-main .swiper-arrow:hover > i,
								#{$layoutID}.tss-wrapper .tss-carousel .swiper-arrow:hover > i,
								#{$layoutID}.tss-wrapper .tss-carousel-controls .swiper-arrow:hover > i{";
					$css .= "color: {$button['hover_text']};";
					$css .= '}';
				}
				if ( ! empty( $button['border'] ) ) {
					$css .= "#{$layoutID}.tss-wrapper .tss-pagination ul.pagination-list li.active span,
								#{$layoutID}.tss-wrapper .tss-pagination ul.pagination-list li a,
								#{$layoutID}.tss-wrapper .tss-carousel-main .swiper-arrow,
								#{$layoutID}.tss-wrapper .tss-carousel .swiper-arrow,
								#{$layoutID}.tss-wrapper .tss-carousel-controls .swiper-arrow {";
					$css .= "border-color: {$button['border']};";
					$css .= '}';
				}

				// Read More (the clamp toggle and the text-limit link) has no settings of
				// its own, so it follows the button colours. Text colours apply as they
				// are. A background or a border turns it into a button (filled or
				// outlined); only then does the hover background apply, since a text
				// link with a fill but no padding would read as a highlight.
				$readMore      = "#{$layoutID}.tss-wrapper .single-item-wrapper .tss-readmore-toggle,
								#{$layoutID}.tss-wrapper .single-item-wrapper .rt-read-more";
				$readMoreHover = "#{$layoutID}.tss-wrapper .single-item-wrapper .tss-readmore-toggle:hover,
								#{$layoutID}.tss-wrapper .single-item-wrapper .tss-readmore-toggle:focus,
								#{$layoutID}.tss-wrapper .single-item-wrapper .rt-read-more:hover,
								#{$layoutID}.tss-wrapper .single-item-wrapper .rt-read-more:focus";

				if ( ! empty( $button['text'] ) ) {
					$css .= "{$readMore}{color: {$button['text']};}";
				}

				if ( ! empty( $button['hover_text'] ) ) {
					$css .= "{$readMoreHover}{color: {$button['hover_text']};}";
				}

				if ( ! empty( $button['bg'] ) || ! empty( $button['border'] ) ) {
					$css .= "{$readMore}{padding: 6px 14px; border-radius: 4px; text-decoration: none;";
					$css .= ! empty( $button['bg'] ) ? "background-color: {$button['bg']};" : null;
					$css .= ! empty( $button['border'] ) ? "border: 1px solid {$button['border']};" : null;
					$css .= '}';
					$css .= "{$readMoreHover}{text-decoration: none;";
					$css .= ! empty( $button['hover_bg'] ) ? "background-color: {$button['hover_bg']};" : null;
					$css .= '}';
				}
			}

			// Overlay. Only the layout 9 design has one: the ::before layer revealed
			// when a card is hovered or tapped (see .tss-is-revealed in the CSS).
			if ( $overlay ) {
				if ( ! empty( $overlay['color'] ) ) {
					$opacity = ! empty( $overlay['opacity'] ) ? esc_attr( $overlay['opacity'] ) / 100 : .9;
					$css    .= "#{$layoutID}.tss-wrapper .tss-layout9 .profile-img-wrapper:before,
						#{$layoutID}.tss-wrapper .tss-isotope4 .profile-img-wrapper:before,
						#{$layoutID}.tss-wrapper .tss-carousel9 .profile-img-wrapper:before{";
					$css    .= 'background: ' . $this->TLPhex2rgba( $overlay['color'], $opacity ) . ';';
					$css    .= '}';
				} elseif ( ! empty( $overlay['opacity'] ) ) {
					// No colour: fade the default Primary gradient instead. The layer's
					// own opacity runs the reveal (0 at rest), so only the revealed
					// states take the value, or the overlay would show without a hover.
					$opacity = min( 100, absint( $overlay['opacity'] ) ) / 100;
					$shown   = [];

					foreach ( [ 'layout9', 'isotope4', 'carousel9' ] as $overlayLayout ) {
						foreach ( [ ':hover', ':focus-within', '.tss-is-revealed' ] as $state ) {
							$shown[] = "#{$layoutID}.tss-wrapper .tss-{$overlayLayout} .single-item-wrapper{$state} .profile-img-wrapper:before";
						}
					}

					$css .= implode( ',', $shown ) . "{opacity: {$opacity};}";
				}
			}

			// * image border style
			if ( $imgBorder ) {
				if ( ! empty( $imgBorder['width'] ) ) {
					$style = ! empty( $imgBorder['style'] ) ? esc_attr( $imgBorder['style'] ) : 'solid';
					$css  .= "#$layoutID .profile-img-wrapper img {
							border-width: {$imgBorder['width']}px;
							border-style: {$style};
							}";
				}
				if ( ! empty( $imgBorder['color'] ) ) {
					$css .= "#$layoutID .profile-img-wrapper img { border-color: {$imgBorder['color']};}";
				}
			}

			// Isotope counter tooltip color
			if ( $tooltipBg || $tooltipText ) {
				$css .= "#tss-tooltip-{$layoutID}, #tss-tooltip-{$layoutID} .tss-tooltip-bottom:after {";
				$css .= ( $tooltipBg ? "background: {$tooltipBg}; " : null );
				$css .= ( $tooltipText ? "color: {$tooltipText};" : null );
				$css .= '}';
			}

			/* gutter */
			if ( $gutter ) {
				$css .= "#{$layoutID} [class*='rt-col-'] {";
				$css .= "padding-left : {$gutter}px;";
				$css .= "padding-right : {$gutter}px;";
				$css .= "margin-top : {$gutter}px;";
				$css .= "margin-bottom : {$gutter}px;";
				$css .= '}';
				$css .= "#{$layoutID} .rt-row{";
				$css .= "margin-left : -{$gutter}px;";
				$css .= "margin-right : -{$gutter}px;";
				$css .= '}';
				$css .= "#{$layoutID}.rt-container-fluid,#{$layoutID}.rt-container{";
				$css .= "padding-left : {$gutter}px;";
				$css .= "padding-right : {$gutter}px;";
				$css .= '}';
			}

			// Author name
			if ( ! empty( $name ) && is_array( $name ) ) {
				$css .= "#{$layoutID}.tss-wrapper .single-item-wrapper h3.author-name,
						#{$layoutID}.tss-wrapper .single-item-wrapper h3.author-name a{";
				$css .= ( ! empty( $name['color'] ) ? 'color:' . $name['color'] . ';' : null );
				$css .= ( ! empty( $name['weight'] ) ? 'font-weight:' . $name['weight'] . ';' : null );
				$css .= ( ! empty( $name['size'] ) ? 'font-size:' . $name['size'] . 'px;' : null );
				$css .= '}';

				if ( ! empty( $name['align'] ) && $layoutAligned ) {
					$css .= "#{$layoutID}.tss-wrapper .single-item-wrapper h3.author-name{" . $this->boxAlignCss( $name['align'] ) . '}';
					$css .= "#{$layoutID}.tss-wrapper .single-item-wrapper h3.author-name a{text-align:" . $name['align'] . ';}';
					$css .= $this->inlineNameSelector( "#{$layoutID}.tss-wrapper" ) . '{' . $this->inlineNameBlockCss() . '}';
				}
			}

			// Author bio
			if ( ! empty( $author_bio ) && is_array( $author_bio ) ) {
				$css .= "#{$layoutID}.tss-wrapper .single-item-wrapper h4.author-bio{";
				$css .= ( ! empty( $author_bio['color'] ) ? 'color:' . $author_bio['color'] . ';' : null );
				$css .= ( ! empty( $author_bio['align'] ) && $layoutAligned ? $this->boxAlignCss( $author_bio['align'] ) : null );
				$css .= ( ! empty( $author_bio['weight'] ) ? 'font-weight:' . $author_bio['weight'] . ';' : null );
				$css .= ( ! empty( $author_bio['size'] ) ? 'font-size:' . $author_bio['size'] . 'px;' : null );
				$css .= '}';
			}

			// Rating
			if ( ! empty( $rating ) && is_array( $rating ) ) {
				$css .= "#{$layoutID}.tss-wrapper .single-item-wrapper .rating-wrapper span.dashicons{";
				$css .= ( ! empty( $rating['color'] ) ? 'color:' . $rating['color'] . ';' : null );
				$css .= ( ! empty( $rating['weight'] ) ? 'font-weight:' . $rating['weight'] . ';' : null );
				$css .= ( ! empty( $rating['size'] ) ? 'font-size:' . $rating['size'] . 'px;' : null );
				$css .= '}';
				if ( ! empty( $rating['align'] ) && $layoutAligned ) {
					$ratingAlign = $this->ratingAlignCss( $rating['align'] );
					$css        .= "#{$layoutID}.tss-wrapper .single-item-wrapper .rating-wrapper{" . $ratingAlign['wrapper'] . '}';
					$css        .= "#{$layoutID}.tss-wrapper .single-item-wrapper .tss-meta-text + .rating-wrapper{" . $ratingAlign['video'] . '}';
					$css        .= "#{$layoutID}.tss-wrapper .tss-layout10 .rating-wrapper,#{$layoutID}.tss-wrapper .tss-isotope5 .rating-wrapper,#{$layoutID}.tss-wrapper .tss-carousel10 .rating-wrapper{" . $ratingAlign['layout10'] . '}';
				}
			}

			// Social media
			if ( ! empty( $social ) && is_array( $social ) ) {
				$css .= "#{$layoutID}.tss-wrapper .single-item-wrapper .author-social span.dashicons,
						#{$layoutID}.tss-wrapper .single-item-wrapper .author-social a:visited,
						#{$layoutID}.tss-wrapper .single-item-wrapper .author-social a{";
				$css .= ( ! empty( $social['color'] ) ? 'color:' . $social['color'] . ';' : null );
				$css .= ( ! empty( $social['weight'] ) ? 'font-weight:' . $social['weight'] . ';' : null );
				$css .= ( ! empty( $social['size'] ) ? 'font-size:' . $social['size'] . 'px;' : null );
				$css .= '}';
				// Same declarations as the Elementor tss_el_social_alignment control:
				// in most layouts the icon row is only as wide as its icons, so the
				// auto margins move the row and text-align moves icons inside it.
				$socialMargins = [
					'left'   => 'margin-left:0;margin-right:auto;',
					'center' => 'margin-left:auto;margin-right:auto;',
					'right'  => 'margin-left:auto;margin-right:0;',
				];
				$css .= "#{$layoutID}.tss-wrapper .single-item-wrapper .author-social {";
				$css .= ( ! empty( $social['align'] ) ? 'text-align:' . $social['align'] . ';' : null );
				$css .= ( ! empty( $social['align'] ) && isset( $socialMargins[ $social['align'] ] ) ? $socialMargins[ $social['align'] ] : null );
				$css .= '}';

				$css .= "#{$layoutID}.tss-wrapper .single-item-wrapper .author-social span.dashicons.dashicons-twitter:before {";
				$css .= ( ! empty( $social['color'] ) ? 'background:' . $social['color'] . ';' : null );
				$css .= '}';

				$css .= "#{$layoutID}.tss-wrapper .author-social .dashicons-skype:before,
						#{$layoutID}.tss-wrapper .author-social .dashicons-telegram:before {";
				$css .= ( ! empty( $social['color'] ) ? 'background-color:' . $social['color'] . ';' : null );
				$css .= '}';

				$css .= "#{$layoutID}.tss-wrapper .author-social .dashicons-skype:before,
						#{$layoutID}.tss-wrapper .author-social .dashicons-twitter:before,
						#{$layoutID}.tss-wrapper .author-social .dashicons-telegram:before {";
				$css .= ( ! empty( $social['size'] ) ? 'width:' . $social['size'] . 'px;' : null );
				$css .= ( ! empty( $social['size'] ) ? 'height:' . $social['size'] . 'px;' : null );
				$css .= '}';
			}


			// Testimonial
			if ( ! empty( $testimonial ) && is_array( $testimonial ) ) {
				$css .= "#{$layoutID}.tss-wrapper .single-item-wrapper .item-content{";
				$css .= ( ! empty( $testimonial['color'] ) ? 'color:' . $testimonial['color'] . ';' : null );
				$css .= ( ! empty( $testimonial['align'] ) ? 'text-align:' . $testimonial['align'] . ';' : null );
				$css .= ( ! empty( $testimonial['weight'] ) ? 'font-weight:' . $testimonial['weight'] . ';' : null );
				$css .= ( ! empty( $testimonial['size'] ) ? 'font-size:' . $testimonial['size'] . 'px;' : null );
				$css .= ( ! empty( $testimonial['style'] ) ? 'font-style:' . $testimonial['style'] . ';' : null );
				$css .= '}';
			}

			// Quote. Three shapes carry it: the .tss-quote-mark glyph (a round badge
			// on layout 3 / slider 3 / isotope 3), the layout 9 family's badge on the
			// photo (.profile-img-wrapper:after), and carousel 11 / 12's typographic
			// quotes around the copy. Badges are re-sized in em so they keep their
			// designed proportions (34px box around a 26px glyph, 46px around 20px).
			if ( ! empty( $quote ) && is_array( $quote ) ) {
				$qColor = ! empty( $quote['color'] ) ? sanitize_hex_color( $quote['color'] ) : '';
				$qBg    = ! empty( $quote['bg'] ) ? sanitize_hex_color( $quote['bg'] ) : '';
				$qSize  = ! empty( $quote['size'] ) ? absint( $quote['size'] ) : 0;
				$w      = "#{$layoutID}.tss-wrapper";
				$mark   = "{$w} .single-item-wrapper .tss-quote-mark";
				$badge  = "{$w} .tss-carousel3 .single-item-wrapper .tss-quote-mark, {$w} .tss-layout3 .single-item-wrapper .tss-quote-mark, {$w} .tss-isotope3 .single-item-wrapper .tss-quote-mark";
				$photo  = "{$w} .tss-layout9 .single-item-wrapper .profile-img-wrapper:after, {$w} .tss-isotope4 .single-item-wrapper .profile-img-wrapper:after, {$w} .tss-carousel9 .single-item-wrapper .profile-img-wrapper:after";
				$inline = "{$w} .tss-carousel11 .item-content p:first-of-type:before, {$w} .tss-carousel11 .item-content p:last-of-type:after, {$w} .tss-carousel11 .item-content:before, {$w} .tss-carousel11 .item-content:after, {$w} .tss-carousel12 .item-content p:first-of-type:before, {$w} .tss-carousel12 .item-content p:last-of-type:after, {$w} .tss-carousel12 .item-content:before, {$w} .tss-carousel12 .item-content:after";

				if ( $qColor ) {
					$css .= "{$mark}, {$photo}, {$inline}{color: {$qColor};}";
				}

				if ( $qBg ) {
					$css .= "{$badge}, {$photo}{background: {$qBg};}";
				}

				if ( $qSize ) {
					$css .= "{$mark}{font-size: {$qSize}px;}";
					$css .= "{$badge}{width: 1.3077em; height: 1.3077em; padding-top: 0.4615em; top: -0.6538em;}";
					$css .= "{$photo}{font-size: {$qSize}px; width: 2.3em; height: 2.3em; line-height: 2.3em;}";
				}
			}

			$css .= '</style>';

			return $css;
		}

		public function rtShare( $iID, $scMeta, $scEl = null ) {
			if ( ! $iID || ( is_array( $scMeta ) && empty( $scMeta ) ) ) {
				return;
			}

			if ( ! function_exists( 'rttsp' ) ) {
				return;
			}

			$ssList = [];

			if ( ! empty( $scEl ) ) {
				$ssList = array_map( 'sanitize_text_field', $scEl );
			} elseif ( ! empty( $scMeta['tss_el_social_share'] ) && ! empty( $scMeta['tss_el_social_share_items'] ) ) {
				$ssList = array_map( 'sanitize_text_field', $scMeta['tss_el_social_share_items'] );
			} else {
				$ssList = isset( $scMeta['social_share_items'] ) ? array_map( 'sanitize_text_field', $scMeta['social_share_items'] ) : [];
			}

			$permalink = get_the_permalink( $iID );
			$title     = get_the_title( $iID );
			$content   = get_post_field( 'post_content', $iID );
			$html      = null;

			if ( in_array( 'facebook', $ssList ) ) {
				$html .= "<a class='facebook' title='" . esc_html__(
					'Share on facebook',
					'testimonial-slider-showcase'
				) . "' target='_blank' href='https://www.facebook.com/sharer/sharer.php?u={$permalink}'><span class='dashicons dashicons-facebook-alt'></span></a>";
			}
			if ( in_array( 'twitter', $ssList ) ) {
				$html .= "<a class='twitter' title='" . esc_html__(
					'Share on twitter',
					'testimonial-slider-showcase'
				) . "' target='_blank' href='http://www.twitter.com/intent/tweet?url={$permalink}'><i class='dashicons dashicons-twitter'></i></a>";
			}
			if ( in_array( 'linkedin', $ssList ) ) {
				$html .= "<a class='linkedin' title='" . esc_html__(
					'Share on linkedin',
					'testimonial-slider-showcase'
				) . "' target='_blank' href='https://www.linkedin.com/shareArticle?mini=true&url={$permalink}'><i class='dashicons dashicons-linkedin'></i></a>";
			}
			if ( in_array( 'pinterest', $ssList ) ) {
				$html .= "<a class='pinterest' title='" . esc_html__(
					'Share on pinterest',
					'testimonial-slider-showcase'
				) . "' target='_blank' href='https://pinterest.com/pin/create/button/?url={$permalink}'><i class='dashicons dashicons-pinterest'></i></a>";
			}
			if ( in_array( 'email', $ssList ) ) {
				$html .= sprintf(
					'<a class="email" title="%s" href="mailto:?subject=%s&body=%s"><span class="dashicons dashicons-email"></span></a>',
					esc_html__( 'Share on Email', 'testimonial-slider-showcase' ),
					$title,
					$content
				);
			}

			if ( $html ) {
				// The Elementor "Social Share Label" control; the shortcode keeps the default.
				$shareLabel = is_array( $scMeta ) && isset( $scMeta['tss_el_social_share_label'] )
					? trim( sanitize_text_field( $scMeta['tss_el_social_share_label'] ) )
					: trim( __( 'Share on: ', 'testimonial-slider-showcase' ) );
				$shareLabel = '' !== $shareLabel ? "<span class='share-icons'>" . esc_html( $shareLabel ) . '</span> ' : '';
				$html       = "<div class='tss-social-share'>{$shareLabel}{$html}</div>";
			}

			return $html;
		}

		/**
		 * Label printed ahead of the social media icons.
		 *
		 * @param string|null $label   The Elementor "Social Media Label" value, or null
		 *                             when the render path sets none (shortcode).
		 * @param bool        $default Fall back to "Follow us:" when $label is null.
		 * @return string
		 */
		public function rtSocialLabel( $label = null, $default = false ) {
			if ( null === $label ) {
				if ( ! $default ) {
					return '';
				}

				$label = __( 'Follow us:', 'testimonial-slider-showcase' );
			}

			$label = trim( $label );

			return '' !== $label ? "<span class='tss-social-label'>" . esc_html( $label ) . '</span>' : '';
		}

		public function tssGridLayouts() {
			return apply_filters(
				'rttss_elementor_grid_layouts',
				[
					'layout1' => [
						'title' => esc_html__( 'Layout 1', 'testimonial-slider-showcase' ),
						'url'   => TSSPro()->assetsUrl . 'images/layouts/layout1.png',
					],
					'layout2' => [
						'title' => esc_html__( 'Layout 2', 'testimonial-slider-showcase' ),
						'url'   => TSSPro()->assetsUrl . 'images/layouts/layout2.png',
					],
				]
			);
		}

		public function tssSliderLayout() {
			return apply_filters(
				'rttss_elementor_slider_layouts',
				[
					'carousel1' => [
						'title' => esc_html__( 'Carousel 1', 'testimonial-slider-showcase' ),
						'url'   => TSSPro()->assetsUrl . 'images/layouts/carousel1.png',
					],
					'carousel3' => [
						'title' => esc_html__( 'Carousel 3', 'testimonial-slider-showcase' ),
						'url'   => TSSPro()->assetsUrl . 'images/layouts/carousel3.png',
					],
				]
			);
		}

		public function tssGetImageTypes() {
			return apply_filters(
				'rttss_image_types',
				[
					'normal' => esc_html__( 'Normal', 'testimonial-slider-showcase' ),
					'circle' => esc_html__( 'Circle', 'testimonial-slider-showcase' ),
					'square' => esc_html__( 'Square', 'testimonial-slider-showcase' ),
				]
			);
		}

		/**
		 * Whether a control's option list is going to be drawn.
		 *
		 * Elementor registers a widget's controls on the FRONT END as well as in
		 * the editor, so an option list built from a query runs on every page view,
		 * once per widget on the page. Nothing on the front end reads the list:
		 * Control_Select2 extends Base_Data_Control, whose get_value() returns the
		 * saved setting verbatim and never consults 'options'. So the list is only
		 * worth building where the panel is actually rendered.
		 *
		 * The editor page and the panel's AJAX both run under is_admin(); newer
		 * Elementor fetches some panel config over REST. The preview iframe is
		 * deliberately NOT included — it renders front-end markup, and the panel it
		 * belongs to lives in the outer admin frame.
		 *
		 * @return bool
		 */
		public function tssNeedsControlOptions() {
			if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
				return true;
			}

			if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->editor ) ) {
				return \Elementor\Plugin::$instance->editor->is_edit_mode();
			}

			return false;
		}

		public function tssAllTestimonialPosts() {
			// Every testimonial, full post rows, unbounded — this is the most
			// expensive option list the plugin builds, so it is skipped entirely
			// where it cannot be seen. See tssNeedsControlOptions().
			if ( ! $this->tssNeedsControlOptions() ) {
				return [];
			}

			// Two widgets on one page asked for this list twice; the answer cannot
			// change within a request.
			static $cache = null;

			if ( null !== $cache ) {
				return $cache;
			}

			$posts = [];
			$args  = [
				'post_type'      => [ TSSPro()->post_type ],
				'post_status'    => [ 'publish' ],
				'order'          => 'DESC',
				'orderby'        => 'date',
				'posts_per_page' => -1,
			];

			$loop = new WP_Query( $args );

			if ( $loop->have_posts() ) {
				while ( $loop->have_posts() ) {
					$loop->the_post();
					$posts[ get_the_ID() ] = get_the_title();
				}
			} else {
				$posts['no-posts'] = esc_html__( 'No testimonials found', 'testimonial-slider-showcase' );
			}

			wp_reset_postdata();

			$cache = $posts;

			return $cache;
		}

		public function tssPostsOrderBy() {
			$options = [
				'menu_order' => esc_html__( 'Menu Order', 'testimonial-slider-showcase' ),
				'title'      => esc_html__( 'Name', 'testimonial-slider-showcase' ),
				'ID'         => esc_html__( 'ID', 'testimonial-slider-showcase' ),
				'date'       => esc_html__( 'Date', 'testimonial-slider-showcase' ),
			];

			if ( function_exists( 'rttsp' ) ) {
				$options['rand'] = esc_html__( 'Random', 'testimonial-slider-showcase' );
			}

			return apply_filters(
				'rttss_post_orderby',
				$options
			);
		}

		public function tssPostsOrder() {
			return [
				'ASC'  => esc_html__( 'Ascending', 'testimonial-slider-showcase' ),
				'DESC' => esc_html__( 'Descending', 'testimonial-slider-showcase' ),
			];
		}

		public function tssSliderAutoplayDelay() {
			return [
				'8000' => esc_html__( '8 Seconds', 'testimonial-slider-showcase' ),
				'7000' => esc_html__( '7 Seconds', 'testimonial-slider-showcase' ),
				'6000' => esc_html__( '6 Seconds', 'testimonial-slider-showcase' ),
				'5000' => esc_html__( '5 Seconds', 'testimonial-slider-showcase' ),
				'4000' => esc_html__( '4 Seconds', 'testimonial-slider-showcase' ),
				'3000' => esc_html__( '3 Seconds', 'testimonial-slider-showcase' ),
				'2000' => esc_html__( '2 Seconds', 'testimonial-slider-showcase' ),
				'1000' => esc_html__( '1 Second', 'testimonial-slider-showcase' ),
			];
		}

		public function tssElContentVisibility( $settings ) {
			$visibility = [];

			if ( ! empty( $settings['tss_el_author'] ) ) {
				$visibility[] = 'author';
			}

			if ( ! empty( $settings['tss_el_author_img'] ) ) {
				$visibility[] = 'author_img';
			}

			if ( ! empty( $settings['tss_el_testimonial'] ) ) {
				$visibility[] = 'testimonial';
			}

			if ( ! empty( $settings['tss_el_designation'] ) ) {
				$visibility[] = 'designation';
			}

			if ( ! empty( $settings['tss_el_company'] ) ) {
				$visibility[] = 'company';
			}

			if ( ! empty( $settings['tss_el_location'] ) ) {
				$visibility[] = 'location';
			}

			if ( ! empty( $settings['tss_el_video'] ) ) {
				$visibility[] = 'video';
			}

			if ( ! empty( $settings['tss_el_rating'] ) ) {
				$visibility[] = 'rating';
			}

			if ( ! empty( $settings['tss_el_social_media'] ) ) {
				$visibility[] = 'social_media';
			}

			if ( ! empty( $settings['tss_el_social_share'] ) ) {
				$visibility[] = 'social_share';
			}

			if ( ! empty( $settings['tss_el_read_more'] ) ) {
				$visibility[] = 'read_more';
			}

			return array_map( 'sanitize_text_field', $visibility );
		}

		public function tssWordLimit( $limit, $link ) {
			$content = get_the_content();
			$limit++;

			$text = '';

			if ( mb_strlen( $content ) > $limit ) {
				$subex   = mb_substr( $content, 0, $limit );
				$exwords = explode( ' ', $subex );
				$excut   = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );

				if ( $excut < 0 ) {
					$text .= mb_substr( $subex, 0, $excut );
				} else {
					$text .= $subex;
				}

				$text .= '...';
			} else {
				$text .= $content;
			}

			$text = $text . ' ' . $link;

			return $text;
		}

		/**
		 * Sanitizes & Prints HTML.
		 *
		 * @param string $html HTML.
		 * @param bool   $allHtml All HTML.
		 * @return mixed
		 */
		public function printHtml( $html, $allHtml = false ) {
			if ( $allHtml ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo stripslashes_deep( $html );
			} else {
				echo wp_kses_post( stripslashes_deep( $html ) );
			}
		}
        public static function get_header($wp_version) {
            if ( version_compare( $wp_version, '5.9', '>=' ) && function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) { ?>
                <!doctype html>
            <html <?php language_attributes(); ?>>
                <head>
                    <meta charset="<?php bloginfo( 'charset' ); ?>">
                    <?php wp_head(); ?>
                </head>
            <body <?php body_class(); ?>>
                <?php wp_body_open(); ?>
                <div class="wp-site-blocks">
                <?php
                $theme      = wp_get_theme();
                $theme_slug = $theme->get( 'TextDomain' );
                echo stripslashes_deep(do_blocks( '<!-- wp:template-part {"slug":"header","theme":"' . esc_attr( $theme_slug ) . '","tagName":"header","className":"site-header tlp-portfolio-block-header"} /-->' )) ;
            } else {
                get_header();
            }
        }
        public static function get_footer($wp_version) {
            if ( version_compare( $wp_version, '5.9', '>=' ) && function_exists( 'wp_is_block_theme' ) && true === wp_is_block_theme() ) {
                $theme      = wp_get_theme();
                $theme_slug = $theme->get( 'TextDomain' );
                echo stripslashes_deep (do_blocks('<!-- wp:template-part {"slug":"footer","theme":"' . esc_attr( $theme_slug ) . '","tagName":"footer","className":"site-footer tlp-portfolio-block-footer"} /-->')) ;
                echo '</div>';
                wp_footer();
                echo '</body>';
                echo '</html>';
            } else {
                get_footer();
            }
        }

		/**
		 * Allowed HTML for wp_kses.
		 *
		 * @param string $level Tag level.
		 *
		 * @return mixed
		 */
		public function allowedHtml( $level = 'basic' ) {
			$allowed_html = [];

			switch ( $level ) {
				case 'basic':
					$allowed_html = [
						'b'      => [
							'class' => [],
							'id'    => [],
						],
						'i'      => [
							'class' => [],
							'id'    => [],
						],
						'u'      => [
							'class' => [],
							'id'    => [],
						],
						'br'     => [
							'class' => [],
							'id'    => [],
						],
						'em'     => [
							'class' => [],
							'id'    => [],
						],
						'span'   => [
							'class' => [],
							'id'    => [],
							'style' => [],
						],
						'strong' => [
							'class' => [],
							'id'    => [],
						],
						'hr'     => [
							'class' => [],
							'id'    => [],
						],
						'p'     => [
							'class' => [],
							'id'    => [],
						],
						'div'   => [
							'class' => [],
							'id'    => [],
						],
						'a'      => [
							'href'   => [],
							'title'  => [],
							'class'  => [],
							'id'     => [],
							'target' => [],
						],
					];
					break;

				case 'advanced':
					$allowed_html = [
						'b'      => [
							'class' => [],
							'id'    => [],
						],
						'i'      => [
							'class' => [],
							'id'    => [],
						],
						'u'      => [
							'class' => [],
							'id'    => [],
						],
						'br'     => [
							'class' => [],
							'id'    => [],
						],
						'em'     => [
							'class' => [],
							'id'    => [],
						],
						'span'   => [
							'class' => [],
							'id'    => [],
						],
						'strong' => [
							'class' => [],
							'id'    => [],
						],
						'hr'     => [
							'class' => [],
							'id'    => [],
						],
						'a'      => [
							'href'   => [],
							'title'  => [],
							'class'  => [],
							'id'     => [],
							'target' => [],
						],
						'input'  => [
							'type'  => [],
							'name'  => [],
							'class' => [],
							'value' => [],
						],
					];
					break;

				case 'image':
					$allowed_html = [
						'img' => [
							'src'      => [],
							'data-src' => [],
							'alt'      => [],
							'height'   => [],
							'width'    => [],
							'class'    => [],
							'id'       => [],
							'style'    => [],
							'srcset'   => [],
							'loading'  => [],
							'sizes'    => [],
						],
						'div' => [
							'class' => [],
						],
					];
					break;

				case 'anchor':
					$allowed_html = [
						'a' => [
							'href'  => [],
							'title' => [],
							'class' => [],
							'id'    => [],
							'style' => [],
						],
					];
					break;

				default:
					// code...
					break;
			}

			return $allowed_html;
		}

		/**
		 * Definition for wp_kses.
		 *
		 * @param string $string String to check.
		 * @param string $level Tag level.
		 *
		 * @return mixed
		 */
		public function htmlKses( $string, $level ) {
			if ( empty( $string ) ) {
				return;
			}

			return wp_kses( $string, self::allowedHtml( $level ) );
		}
	}
endif;
