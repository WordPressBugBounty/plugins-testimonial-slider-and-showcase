<?php
/**
 * Shortcode Class.
 *
 * @package RT_TSS
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

if ( ! class_exists( 'TSSProSC' ) ) :
	/**
	 * Shortcode Class.
	 */
	class TSSProSC {
		/**
		 * SC Scripts.
		 *
		 * @var array
		 */
		private $scA = [];

		/**
		 * Class construct
		 */
		public function __construct() {
			add_shortcode( 'rt-testimonial', [ $this, 'testimonial_shortcode' ] );
		}

		/**
		 * Register scripts
		 *
		 * @return void
		 */
		public function register_scripts() {
			$caro   = false;
			$iso    = false;
			$script = [];
			$style  = [];
			array_push( $script, 'jquery' );

			foreach ( $this->scA as $sc ) {
				if ( isset( $sc ) && is_array( $sc ) ) {
					if ( $sc['isCarousel'] ) {
						$caro = true;
					}
					if ( $sc['isIsotope'] ) {
						$iso = true;
					}
				}
			}

			if ( count( $this->scA ) ) {
				array_push( $script, 'tss-popup' );
				array_push( $script, 'tss-image-load' );

				if ( $caro ) {
					array_push( $style, 'swiper' );
					array_push( $script, 'swiper' );
				}

				if ( $iso ) {
					array_push( $script, 'tss-isotope' );
				}

				array_push( $style, 'tss-popup' );
				array_push( $style, 'tss-fontello' );
				array_push( $style, 'dashicons' );
				array_push( $script, 'tss' );

				wp_enqueue_style( $style );
				wp_enqueue_script( $script );

				$ajaxurl = '';

				if ( in_array( 'sitepress-multilingual-cms/sitepress.php', get_option( 'active_plugins' ) ) ) {
					$ajaxurl .= admin_url( 'admin-ajax.php?lang=' . ICL_LANGUAGE_CODE );
				} else {
					$ajaxurl .= admin_url( 'admin-ajax.php' );
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

		}

		/**
		 * Render
		 *
		 * @param array $atts Attributes.
		 * @return string
		 */
		public function testimonial_shortcode( $atts ) {
			$rand     = wp_rand();
			$layoutID = 'tss-container-' . $rand;
			$html     = null;
			$arg      = [];
			$atts     = shortcode_atts(
				[
					'id' => null,
				],
				$atts,
				'rt-testimonial'
			);
			$scID     = absint( $atts['id'] );

			if ( $scID && ! is_null( get_post( $scID ) ) ) {
				$scMeta        = get_post_meta( $scID );
				$buildMetas    = $this->metas( $scMeta, $scID );
				$arg['scMeta'] = $scMeta;
				$lazyLoadP     = false;

				if ( $buildMetas ) {
					extract( $buildMetas );
				}

				if ( ! in_array( $dCol, array_keys( TSSPro()->scColumns() ), true ) ) {
					$dCol = 3;
				}
				if ( ! in_array( $tCol, array_keys( TSSPro()->scColumns() ), true ) ) {
					$tCol = 2;
				}
				if ( ! in_array( $mCol, array_keys( TSSPro()->scColumns() ), true ) ) {
					$mCol = 1;
				}

				$dColItems  = $dCol;
				$tColItems  = $tCol;
				$mColItems  = $mCol;
				$isIsotope  = preg_match( '/isotope/', $layout );
				$isCarousel = preg_match( '/carousel/', $layout );
				$isVideo    = preg_match( '/video/', $layout );

				/* Argument create */
				$containerDataAttr  = false;
				$containerDataAttr .= " data-layout='{$layout}' data-desktop-col='{$dCol}'  data-tab-col='{$tCol}'  data-mobile-col='{$mCol}'";

				// Validation.
				$dCol = $dCol == 5 ? '24' : round( 12 / $dCol );
				$tCol = $tCol == 5 ? '24' : round( 12 / $tCol );
				$mCol = $mCol == 5 ? '24' : round( 12 / $mCol );

				if ( $isCarousel ) {
					$dCol = $tCol = $mCol = 12;
				}

				$arg['grid']      = "rt-col-md-{$dCol} rt-col-sm-{$tCol} rt-col-xs-{$mCol}";
				$arg['read_more'] = $readMore;
				$arg['class']     = $gridType . '-grid-item';
				$arg['class']    .= ' tss-grid-item';
				$preLoader        = null;

				if ( $isIsotope ) {
					$arg['class'] .= ' isotope-item';
					$preLoader     = 'tss-pre-loader';
				}

				if ( $isCarousel ) {
					$arg['class'] .= ' slide-item swiper-slide';
					$preLoader     = 'tss-pre-loader';
				}

				$masonryG = null;

				if ( $gridType == 'even' ) {
					$masonryG      = ' tss-even';
					$arg['class'] .= ' even-grid-item';
				} elseif ( 'masonry' === $gridType && ! $isIsotope && ! $isCarousel ) {
					$masonryG      = ' tss-masonry';
					$arg['class'] .= ' masonry-grid-item';
				}

				if ( 'no' === $margin ) {
					$arg['class'] .= ' no-margin';
				} else {
					$arg['class'] .= ' default-margin';
				}

				if ( 'circle' === $imageType ) {
					$arg['class'] .= ' tss-img-circle';
				} elseif ( 'square' === $imageType ) {
					$arg['class'] .= ' tss-img-square';
				} else {
					$arg['class'] .= ' tss-img-normal';
				}

				$arg['items']       = $itemFields;
				$arg['shareItems']  = [];
				$arg['anchorClass'] = null;
				$arg['link']        = $link ? true : false;

				// Start layout.
				$html .= TSSPro()->layoutStyle( $layoutID, get_post_meta( $scID ), $scID );
				$html .= "<div class='rt-container-fluid tss-wrapper ".esc_attr($parentClass)."' id='".esc_attr($layoutID)."' {$containerDataAttr}>";
				$html .= "<div data-title='" . esc_html__(
					'Loading ...',
					'testimonial-slider-showcase'
				) . "' class='rt-row tss-{$layout}{$masonryG} {$preLoader}'>";

				// WP_Query args.

				$query_args = new TSSQueryArgs();
				$tssArgs = $query_args->buildArgs( $buildMetas, $isCarousel );
				$tssQuery = new WP_Query( $tssArgs );
				if ( $tssQuery->have_posts() ) {
                    if ( $isIsotope ) {
						$terms = get_terms(
							[
								'taxonomy'   => TSSPro()->taxonomies['category'],
								'hide_empty' => false,
								'orderby'    => 'meta_value_num',
								'order'      => 'ASC',
								'meta_key'   => '_order', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
							]
						);

                        $html          .= '<div class="tss-iso-filter"><div id="iso-button-' . absint( $rand ) . '" class="tss-isotope-button-wrapper tooltip-active filter-button-group">';
						$htmlButton     = null;
						$fSelectTrigger = false;

                        if ( isset( $tssFilterType ) && 'rating' === $tssFilterType ) {
                            $args = [
                                'post_type'      => 'testimonial',
                                'posts_per_page' => -1,
                                'post_status'    => 'publish',
                            ];
                            $query = new WP_Query($args);
                            $totalReviews = 0;
                            $totalScore = 0;
                            if ($query->have_posts()) {
                                while ($query->have_posts()) {
                                    $query->the_post();
                                    $rating = get_post_meta(get_the_ID(), 'tss_rating', true);
                                    if (!empty($rating)) {
                                        $totalReviews++;
                                        $totalScore += floatval($rating);
                                    }
                                }
                                wp_reset_postdata();
                            }
                            $averageRating = $totalReviews > 0 ? round($totalScore / $totalReviews, 2) : 0;
                            if(  $hasAverage == 1 ){
                            ?>
                            <div class="tlp-average-rating">
                                <div class="tlp-rating-title"><?php echo esc_html( $averageRatingText ) ?></div>
                                <div class="tlp-client-rating">
                                    <?php
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= floor($averageRating)) {
                                            echo "<i class='dashicons dashicons-star-filled' aria-hidden='true'></i>";
                                        } elseif ($i <= $averageRating) {
                                            echo "<i class='dashicons custom-star-half' aria-hidden='true'></i>";
                                        } else {
                                            echo "<i class='dashicons dashicons-star-empty' aria-hidden='true'></i>";
                                        }
                                    }
                                    ?>
                                    <span><?php echo esc_html($averageRating); ?> <?php echo esc_html__( 'out of 5 stars', 'testimonial-slider-showcase' )?></span>
                                </div>
                                <div class="total-rating-text"><?php echo esc_html__('Based on', 'testimonial-slider-showcase')?> <?php echo esc_html($totalReviews); ?> <?php echo esc_html__('Reviews', 'testimonial-slider-showcase')?></div>
                            </div>
                            <?php } ?>

                            <?php $ratings = [5, 4, 3, 2, 1];
                            if (!empty( $ratings )) {
                                foreach ( $ratings as $rating ) {
                                    $htmlButton .= "<span class='rt-iso-button' data-filter-counter='" . esc_attr($rating) . "' data-filter='.rating_" . esc_attr($rating) . "'>";
                                    for ($i = 1; $i <= 5; $i++) {
                                        $starClass = ($i <= $rating) ? 'filled' : 'empty';
                                        $htmlButton .= "<i data-star='" . esc_attr($i) . "' class='star-$i dashicons dashicons-star-{$starClass}' aria-hidden='true'></i>";
                                    }
                                    $htmlButton .= "</span>";
                                }
                            }

                        } else{
                            // Real per-category totals for the whole wall, not just
                            // the page that is about to be printed — see
                            // TSSProHelper::isoFilterTotals().
                            $isoTotals = TSSPro()->isoFilterTotals( $tssArgs, ( empty( $postLimit ) || '-1' === $postLimit ) ? 0 : absint( $postLimit ) );

                            if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                                foreach ( $terms as $term ) {
                                    $fSelect = null;
                                    if ( $tItem == $term->term_id ) {
                                        $fSelect        = ' selected';
                                        $fSelectTrigger = true;
                                    }
                                    $fTotal      = TSSPro()->isoFilterTotalAttr( $isoTotals, $term->term_id );
                                    $htmlButton .= "<span class='rt-iso-button {$fSelect}' data-filter-counter=''{$fTotal} data-filter='.iso_{$term->term_id}' {$fSelect}>" . $term->name . '</span>';
                                }
                            }
                        }
						if ( empty( $isotopeShowAll ) ) {
							$fSelect = ( $fSelectTrigger ? null : ' selected' );
							$fTotal  = isset( $isoTotals ) ? TSSPro()->isoFilterTotalAttr( $isoTotals, 'all' ) : '';
							$html   .= "<span class='rt-iso-button{$fSelect}' data-filter-counter=''{$fTotal} data-filter='*'>" . esc_html__( 'Show all', 'testimonial-slider-showcase' ) . '</span>';
						}
						$html .= $htmlButton;
						$html .= '</div>';

						if ( ! empty( $isotopeSearchFilter ) ) {
							$html .= "<div class='iso-search'><input type='text' class='iso-search-input' placeholder='" . esc_html__( 'Search', 'testimonial-slider-showcase' ) . "' /></div>";
						}

						$html .= '</div>';

						$html .= '<div class="tss-isotope" id="tss-isotope-' . $rand . '">';
					} elseif ( $isCarousel ) {
						$autoPlay           = ( in_array( 'autoplay', $cOpt, true ) ? 'true' : 'false' );
						$autoPlayHoverPause = ( in_array( 'autoplayHoverPause', $cOpt, true ) ? 'true' : 'false' );
						$nav                = ( in_array( 'nav', $cOpt, true ) ? 'true' : 'false' );
						$dots               = ( in_array( 'dots', $cOpt, true ) ? 'true' : 'false' );
						$loop               = ( in_array( 'loop', $cOpt, true ) ? 'true' : 'false' );
						$lazyLoad           = ( in_array( 'lazy_load', $cOpt, true ) ? 'true' : 'false' );
						$lazyLoadP          = ( in_array( 'lazy_load', $cOpt, true ) ? true : false );
						$autoHeight         = ( in_array( 'auto_height', $cOpt, true ) ? 'true' : 'false' );
						$rtl                = ( in_array( 'rtl', $cOpt, true ) ? 'dir="rtl"' : '' );
						$carouselWrapper    = 'carousel11' === $layout || 'carousel12' === $layout ? 'tss-carousel-main' : 'tss-carousel';

						if ( 'carousel11' === $layout ) {
							$html .= $this->renderThumbSlider( $scID, $tssQuery, $scMeta, $arg );
						}

						$html .= '<div class="carousel-wrapper">';
						$html .= "<div {$rtl} class='{$carouselWrapper} swiper'
										data-loop='{$loop}'
										data-items-desktop='{$dColItems}'
										data-items-tab='{$tColItems}'
										data-items-mobile='{$mColItems}'
										data-autoplay='{$autoPlay}'
										data-autoplay-timeout='{$autoplayTimeout}'
										data-autoplay-hover-pause='{$autoPlayHoverPause}'
										data-dots='{$dots}'
										data-nav='{$nav}'
										data-lazy-load='{$lazyLoad}'
										data-auto-height='{$autoHeight}'
										data-smart-speed='{$smartSpeed}'
										>";

						$html .= '<div class="swiper-wrapper">';
					}

					while ( $tssQuery->have_posts() ) :
						$tssQuery->the_post();
						$iID                 = get_the_ID();
						$arg['iID']          = $iID;
						$arg['author']       = get_the_title();
						$arg['designation']  = get_post_meta( $iID, 'tss_designation', true );
						$arg['company']      = get_post_meta( $iID, 'tss_company', true );
						$arg['location']     = get_post_meta( $iID, 'tss_location', true );
						$arg['rating']       = get_post_meta( $iID, 'tss_rating', true );
						$arg['video']        = get_post_meta( $iID, 'tss_video', true );
						$arg['social_media'] = get_post_meta( $iID, 'tss_social_media', true );
						$arg['pLink']        = get_permalink();
						$aHtml               = null;

						if ( in_array( 'read_more', $arg['items'], true ) && function_exists( 'rttsp' ) ) {
							$aHtml = "<a class='rt-read-more' href='" . esc_url( $arg['pLink'] ) . "'>{$arg['read_more']}</a>";
						}

						if ( $testi_limit ) {
							$row_content = apply_filters( 'the_content', get_the_content() );
							$plain_text = wp_strip_all_tags( $row_content );
							$limited_text = wp_trim_words( $plain_text, $testi_limit, '...' );
							$arg['testimonial'] = '<p>' . $limited_text . '</p>' . $aHtml;

						} else {
							$arg['testimonial'] = apply_filters( 'the_content', get_the_content() );
						}

						$arg['video_url'] = get_post_meta( $iID, 'tss_video', true );

						if ( $isIsotope && taxonomy_exists( TSSPro()->taxonomies['category'] ) ) {
							// get_the_terms() reads the term cache WP_Query already primed for this
							// page of posts. wp_get_post_terms() goes to the database for every single
							// card, so an isotope wall cost one extra query per testimonial on top of
							// its own query - 12 of the 17 queries a 12-card wall used to run.
							$termAs = get_the_terms( $iID, TSSPro()->taxonomies['category'] );

							if ( is_wp_error( $termAs ) || ! $termAs ) {
								$termAs = [];
							}

							$isoFilter = null;

							if ( ! empty( $termAs ) ) {
								foreach ( $termAs as $term ) {
									$isoFilter .= ' ' . 'iso_' . $term->term_id;
									$isoFilter .= ' ' . $term->slug;
								}
							}

							$arg['isoFilter'] = $isoFilter;
						}

						if ( $lazyLoadP ) {
							$arg['lazyLoad'] = true;
							$arg['img']      = TSSPro()->getFeatureImage( $iID, $imgSize, $customImgSize, $defaultImgId, true );
						} else {
							$arg['lazyLoad'] = false;
							$arg['img']      = TSSPro()->getFeatureImage( $iID, $imgSize, $customImgSize, $defaultImgId );
						}

						// Render layout.
						$html .= TSSPro()->render( 'layouts/' . $layout, $arg );

					endwhile;


					if ( $isIsotope ) {
						$html .= '</div>'; // End isotope.
					} elseif ( $isCarousel ) {
						if ( 'grid' !== $scMeta['tss_layout'] ) {
							$html .= '</div>';

							$html .= 'true' === $nav ? '<div class="swiper-arrow swiper-button-next"><i class="rttss-right-open"></i></div><div class="swiper-arrow swiper-button-prev"><i class="rttss-left-open"></i></div>' : '';
							$html .= 'true' === $dots ? '<div class="swiper-pagination"></div>' : '';

							$html .= '</div>';

							$html .= '</div>'; // End Carousel item holder.

							if ( 'carousel12' === $layout ) {
								$html .= $this->renderThumbSlider( $scID, $tssQuery, $scMeta, $arg );
							}
						}
					}
				} else {
					$html .= '<p>' . esc_html__( 'No testimonial found', 'testimonial-slider-showcase' ) . '</p>';
				}

				if ( $isIsotope || $isCarousel ) {
					$html .= '<div class="rt-loading-overlay"></div><div class="rt-loading rt-ball-clip-rotate"><div></div></div>';
				}

				$html .= '</div>'; // End row.

				if ( $hasPagination && ! $isCarousel ) {
					$htmlUtility = null;
					$postPp      = $tssQuery->query_vars['posts_per_page'];
					$page        = $tssQuery->query_vars['paged'];
					$foundPosts  = $tssQuery->found_posts;
					$morePosts   = $foundPosts - ( $postPp * $page );
					$totalPage   = $tssQuery->max_num_pages;
					$foundPost   = $tssQuery->found_posts;

					// Both of these are optional settings, so a shortcode saved without
					// them has no such meta key at all. Reading them unguarded emitted
					// "Undefined array key tss_limit" plus "Trying to access array
					// offset on null" on every render of such a shortcode.
					if ( ! empty( $scMeta['tss_limit'][0] ) ) {
						$range     = ! empty( $scMeta['tss_posts_per_page'][0] ) ? $scMeta['tss_posts_per_page'][0] : 0;
						$foundPost = $tssQuery->found_posts;

						if ( $range && $foundPost > $range ) {
							// The limit is a cap, not a count: with fewer testimonials than
							// the limit, paging by the limit offered pages with nothing on them.
							$limit     = (int) $scMeta['tss_limit'][0];
							$foundPost = $limit > 0 ? min( $limit, (int) $tssQuery->found_posts ) : (int) $tssQuery->found_posts;
							$totalPage = ceil( $foundPost / $range );
						}
					}

					$foundPosts = $foundPost;

					if ( 'pagination' === $paginationType ) {
						$htmlUtility .= TSSPro()->pagination( $totalPage, $postPp );
					} elseif ( 'pagination_ajax' === $paginationType && ! $isIsotope ) {
						$htmlUtility .= TSSPro()->pagination(
							$totalPage,
							$postPp,
							true,
							$scID
						);
					} elseif ( 'load_more' === $paginationType ) {
						$morePosts      = $foundPosts - ( $postPp * $page );
						$noMorePostText = esc_html__( 'No More Post to load', 'testimonial-slider-showcase' );
						$loadingText    = esc_html__( 'Loading ...', 'testimonial-slider-showcase' );
						$htmlUtility   .= '<div class="tss-load-more">
												<span class="rt-button" data-sc-id="' . absint( $scID ) . '" data-total-pages="' . absint( $totalPage ) . '" data-posts-per-page="' . $postPp . '" data-found-posts="' . absint( $foundPosts ) . '" data-paged="1" data-no-more-post-text="' . esc_attr( $noMorePostText ) . '" data-loading-text="' . esc_attr( $loadingText ) . '">' . esc_html( $loadMore  ) . ' <span>(' . esc_html( $morePosts ) . ')</span></span>
											</div>';
					} elseif ( 'load_on_scroll' === $paginationType ) {
						// data-max-page was missing here while the Elementor twin has
						// always had it. scrollLoadMore() reads it to know when to stop,
						// so without it the comparison is against undefined and the
						// trigger keeps firing past the last page.
						$htmlUtility .= '<div class="tss-scroll-load-more" data-trigger="1" data-sc-id="' . absint( $scID ) . '" data-paged="2" data-max-page="' . absint( $totalPage ) . '"></div>';
					}

					// See TSSProHelper::isoPagerState(). The Load More button and the
					// scroll trigger already carry these numbers, so both are excluded
					// — exactly one element per wall owns the page number.
					if ( $isIsotope && ! in_array( $paginationType, [ 'load_more', 'load_on_scroll' ], true ) ) {
						$htmlUtility .= TSSPro()->isoPagerState(
							[
								'sc-id'          => $scID,
								'paged'          => $page,
								'total-pages'    => $totalPage,
								'posts-per-page' => $postPp,
								'found-posts'    => $foundPosts,
							]
						);
					}

					if ( $htmlUtility ) {
						$html .= "<div class='tss-utility'>" . $htmlUtility . '</div>';
					}
				}
				$html .= '</div>'; // tss-container.

				wp_reset_postdata();

				$scriptGenerator               = [];
				$scriptGenerator['layout']     = $layoutID;
				$scriptGenerator['rand']       = $rand;
				$scriptGenerator['scMeta']     = $scMeta;
				$scriptGenerator['isIsotope']  = ( $isIsotope || 'masonry' === $gridType ? true : false );
				$scriptGenerator['isCarousel'] = $isCarousel;
				$this->scA[]                   = $scriptGenerator;

				add_action( 'wp_footer', [ $this, 'register_scripts' ] );
			} else {
				$html .= '<p>' . esc_html__( 'No shortCode found', 'testimonial-slider-showcase' ) . '</p>';
			}

			return $html;
		}

		/**
		 * Meta values
		 *
		 * @param array $meta Meta values.
		 * @param int   $pID Post ID.
		 * @return array
		 */
		private function metas( array $meta, $pID = null ) {
			return [
				'layout'              => ! empty( $meta['tss_layout'][0] ) ? esc_attr( $meta['tss_layout'][0] ) : 'layout1',
				'dCol'                => isset( $meta['tss_desktop_column'][0] ) && $meta['tss_desktop_column'][0] != '' ? absint( $meta['tss_desktop_column'][0] ) : 3,
				'tCol'                => isset( $meta['tss_tab_column'][0] ) && $meta['tss_tab_column'][0] != '' ? absint( $meta['tss_tab_column'][0] ) : 2,
				'mCol'                => isset( $meta['tss_mobile_column'][0] ) && $meta['tss_mobile_column'][0] != '' ? absint( $meta['tss_mobile_column'][0] ) : 1,
				'customImgSize'       => get_post_meta( $pID, 'tss_custom_image_size', true ),
				'defaultImgId'        => ! empty( $meta['default_preview_image'][0] ) ? absint( $meta['default_preview_image'][0] ) : null,
				'imgSize'             => ! empty( $meta['tss_image_size'][0] ) ? sanitize_text_field( $meta['tss_image_size'][0] ) : 'medium',
				'testi_limit'         => ! empty( $meta['tss_testimonial_limit'][0] ) ? absint( $meta['tss_testimonial_limit'][0] ) : null,
				'gridType'            => ! empty( $meta['tss_grid_style'][0] ) ? esc_attr( $meta['tss_grid_style'][0] ) : 'even',
                'hasAverage'          => ! empty( $meta['tss_average_switch'][0] ) ? true : false,
				'readMore'            => ! empty( $meta['tss_read_more_button_text'][0] ) ? esc_html( $meta['tss_read_more_button_text'][0] ) : esc_html__( 'Read more', 'testimonial-slider-showcase' ),
				'averageRatingText'   => ! empty( $meta['tss_average_rating_text'][0] ) ? esc_html( $meta['tss_average_rating_text'][0] ) : esc_html__( 'Average Rating', 'testimonial-slider-showcase' ),
				'loadMore'            => ! empty( $meta['tss_load_more_button_text'][0] ) ? esc_html( $meta['tss_load_more_button_text'][0] ) : esc_html__( 'Load More', 'testimonial-slider-showcase' ),
				'margin'              => ! empty( $meta['tss_margin'][0] ) ? esc_attr( $meta['tss_margin'][0] ) : 'default',
				'imageType'           => ! empty( $meta['tss_image_type'][0] ) ? esc_attr( $meta['tss_image_type'][0] ) : 'normal',
				'itemFields'          => ! empty( $meta['tss_item_fields'] ) ? array_map( 'sanitize_text_field', $meta['tss_item_fields'] ) : [],
				'link'                => ! empty( $meta['tss_detail_page_link'][0] ) ? true : false,
				'parentClass'         => ! empty( $meta['tss_parent_class'][0] ) ? trim( $meta['tss_parent_class'][0] ) : null,
				'postIn'              => ! empty( $meta['tss_post__in'] ) ? $meta['tss_post__in'][0] : null,
				'postNotIn'           => ! empty( $meta['tss_post__not_in'] ) ? $meta['tss_post__not_in'][0] : null,
				'postLimit'           => ! empty( $meta['tss_limit'] ) ? $meta['tss_limit'][0] : null,
				'postOrderBy'         => ! empty( $meta['tss_order_by'] ) ? $meta['tss_order_by'][0] : null,
				'postOrder'           => ! empty( $meta['tss_order'] ) ? $meta['tss_order'][0] : null,
				'postPagination'      => ! empty( $meta['tss_pagination'] ) ? $meta['tss_pagination'][0] : null,
				'postsPerPage'        => ! empty( $meta['tss_posts_per_page'] ) ? $meta['tss_posts_per_page'][0] : null,
				'postCategories'      => ! empty( $meta['tss_categories'] ) ? $meta['tss_categories'] : [],
				'postTags'            => ! empty( $meta['tss_tags'] ) ? $meta['tss_tags'] : [],
				'taxonomyRelation'    => ! empty( $meta['tss_taxonomy_relation'] ) ? $meta['tss_taxonomy_relation'][0] : null,
				'hasPagination'       => ! empty( $meta['tss_pagination'][0] ) ? true : false,
				'paginationType'      => ! empty( $meta['tss_pagination_type'][0] ) ? esc_html( $meta['tss_pagination_type'][0] ) : 'pagination',
				'smartSpeed'          => ! empty( $meta['tss_carousel_speed'][0] ) ? absint( $meta['tss_carousel_speed'][0] ) : 250,
				'autoplayTimeout'     => ! empty( $meta['tss_carousel_autoplay_timeout'][0] ) ? absint( $meta['tss_carousel_autoplay_timeout'][0] ) : 5000,
				'cOpt'                => ! empty( $meta['tss_carousel_options'] ) ? array_filter( $meta['tss_carousel_options'] ) : [],
				'tItem'               => ! empty( $meta['tss_isotope_selected_filter'][0] ) ? absint( $meta['tss_isotope_selected_filter'][0] ) : null,
				'isotopeShowAll'      => ! empty( $meta['tss_isotope_filter_show_all'][0] ) ? true : false,
				'isotopeSearchFilter' => ! empty( $meta['tss_isotope_search_filtering'][0] ) ? true : false,
                'tssFilterType'       => ! empty( $meta['tss_isotope_filter_type'][0] ) ? esc_attr( $meta['tss_isotope_filter_type'][0] ) : 'cat',

            ];
		}

		/**
		 * Thumb slider
		 *
		 * @param int    $scID ID.
		 * @param object $query Query.
		 * @param array  $meta_value Meta values.
		 * @param array  $arg Args.
		 * @return string
		 */
		public function renderThumbSlider( $scID, $query, $meta_value, $arg ) {
			$html = '';
			$cOpt = ! empty( $meta_value['tss_carousel_options'] ) ? array_filter( $meta_value['tss_carousel_options'] ) : [];

			$customImgSize = get_post_meta( $scID, 'tss_custom_image_size', true );
			$defaultImgId  = ( ! empty( $meta_value['default_preview_image'][0] ) ? absint( $meta_value['default_preview_image'][0] ) : null );
			$imgSize       = ( ! empty( $meta_value['tss_image_size'][0] ) ? sanitize_text_field( $meta_value['tss_image_size'][0] ) : 'medium' );

			$rtl = ( in_array( 'rtl', $cOpt, true ) ? 'dir="rtl"' : '' );

			$html     .= "<div {$rtl} class='tss-carousel-thumb swiper'>";
				$html .= '<div class="swiper-wrapper">';

			while ( $query->have_posts() ) :
				$query->the_post();
				$iID          = get_the_ID();
				$arg['iID']   = $iID;
				$arg['pLink'] = get_permalink();
				$lazyLoadP    = in_array( 'lazy_load', $cOpt, true ) ? true : false;

				if ( $lazyLoadP ) {
					$arg['lazyLoad'] = true;
					$arg['img']      = TSSPro()->getFeatureImage( $iID, $imgSize, $customImgSize, $defaultImgId, true );
				} else {
					$arg['lazyLoad'] = false;
					$arg['img']      = TSSPro()->getFeatureImage( $iID, $imgSize, $customImgSize, $defaultImgId );
				}

				$html .= TSSPro()->render( 'layouts/carousel_thumb', $arg );

				endwhile;

				$html .= '</div>';
			$html     .= '</div>';

			return $html;
		}
	}
endif;
