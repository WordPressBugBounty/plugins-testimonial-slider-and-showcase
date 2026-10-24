(function ($) {
	'use strict';
	$('*')
		.on('touchstart', function () {
			$(this).trigger('hover');
		})
		.on('touchend', function () {
			$(this).trigger('hover');
		});

	$(function () {
		preFunction();
		$(document)
			.on('mouseover', '.tss-isotope-button-wrapper.tooltip-active .rt-iso-button', function () {
				var self = $(this),
					count = self.attr('data-filter-counter'),
					id = self.parents('.tss-wrapper').attr('id');
				$tooltip =
					'<div class="tss-tooltip" id="tss-tooltip-' +
					id +
					'">' +
					'<div class="tss-tooltip-content">' +
					count +
					'</div>' +
					'<div class="tss-tooltip-bottom"></div>' +
					'</div>';
				$('body').append($tooltip);
				var $tooltip = $('body > .tss-tooltip');
				var tHeight = $tooltip.outerHeight();
				var tBottomHeight = $tooltip.find('.tss-tooltip-bottom').outerHeight();
				var tWidth = $tooltip.outerWidth();
				var tHolderWidth = self.outerWidth();
				var top = self.offset().top - (tHeight + tBottomHeight);
				var left = self.offset().left;
				$tooltip
					.css({
						top: top + 'px',
						left: left + 'px',
						opacity: 1
					})
					.show();
				if (tWidth <= tHolderWidth) {
					var itemLeft = (tHolderWidth - tWidth) / 2;
					left = left + itemLeft;
					$tooltip.css('left', left + 'px');
				} else {
					var itemLeft = (tWidth - tHolderWidth) / 2;
					left = left - itemLeft;
					if (left < 0) {
						left = 0;
					}
					$tooltip.css('left', left + 'px');
				}
			})
			.on('mouseout', '.tss-isotope-button-wrapper.tooltip-active .rt-iso-button', function () {
				$('body > .tss-tooltip').fadeOut(300, function () { $(this).remove(); });
			});
	});

	$(window).on('load resize', function () {
		preFunction();
	});

	function preFunction() {
		overlayIconResize();
		clampReadMore();
		tssMakeRevealFocusable();
		tssCacheLoadMoreLabel();
		tssFilterGlider();
	}

	/**
	 * Remembers each Load More button's own wording while it is still pristine.
	 *
	 * The remaining-count is re-rendered into the button as the visitor pages and
	 * filters, and the button is emptied to "Loading ..." during each request, so
	 * by the time a label is needed the original wording may already be gone. The
	 * text is a translated, user-settable string — it cannot be reconstructed.
	 */
	function tssCacheLoadMoreLabel() {
		$('.tss-load-more .rt-button').each(function () {
			var button = $(this),
				cached = button.attr('data-base-text');

			if (typeof cached !== 'undefined' && cached !== false && cached !== '') {
				return;
			}

			var base = button.clone().children().remove().end().text().trim();

			if (base) {
				button.attr('data-base-text', base);
			}
		});
	}

	/**
	 * The isotope filter row is a segmented control: one pill slides to whichever
	 * button is selected. The pill has to be a separate element to animate between
	 * buttons, and it is built here rather than printed with the filter row
	 * because that row is emitted by three renderers (TSSProSC,
	 * TSSElementorRender, TSSPreview) and none of them are layout-aware.
	 *
	 * It began as isotope 1's and now runs on every isotope wall, matching the
	 * shared filter-bar CSS. The selector is deliberately layout-agnostic: a wall
	 * with no pill CSS would still get a glider element, but it stays invisible
	 * until .tss-glider-ready is added and the stylesheet is what gives it a face.
	 *
	 * Called from preFunction(), so it re-measures on load, on resize and after
	 * every AJAX pagination / load-more response, and again from
	 * isoFilterCounter() once the count chips have changed the button widths.
	 *
	 * @param {boolean} refresh Re-read the pill colour instead of reusing the
	 *                          remembered one. The Elementor editor passes it
	 *                          when a style control changes the button colours.
	 */
	function tssFilterGlider(refresh) {
		$('.tss-isotope-button-wrapper').each(function () {
			var track = this,
				$track = $(this),
				$buttons = $track.find('.rt-iso-button'),
				active = $track.find('.rt-iso-button.selected').get(0) || $buttons.get(0),
				glider = $track.children('.tss-glider').get(0);

			if (!active) {
				return;
			}

			if (!glider) {
				glider = document.createElement('span');
				glider.className = 'tss-glider';
				glider.setAttribute('aria-hidden', 'true');
				track.insertBefore(glider, track.firstChild);
			}

			// A refresh forgets the remembered colour and freezes the button
			// fades, so the read below gets the settled colour rather than a
			// frame of the transition (the reason the colour is remembered).
			if (refresh) {
				$track.removeData('tssGliderFill');
				$buttons.css('transition', 'none');
			}

			// Hand every button its own fill back before measuring, so whichever
			// one is selected now reports the colour it would paint.
			$buttons.each(function () {
				this.style.backgroundColor = '';
			});

			var t = track.getBoundingClientRect(),
				a = active.getBoundingClientRect();

			// Nothing laid out yet — measuring now would park the pill at zero
			// width. Window load, the font swap or a resize comes back for it, and
			// the CSS fallback is what shows until then.
			if (!a.width) {
				if (refresh) {
					$buttons.css('transition', '');
				}
				return;
			}

			// The pill takes the colour the selected button resolved to, so the
			// Button "Background" / "Active Background" style controls still drive
			// it: those are emitted at ID specificity and no rule in
			// wptestimonial.css could outrank them.
			//
			// Read once, on the first pass, and remember it. The buttons carry a
			// background transition, so a read taken during a filter click samples
			// a colour part-way through the fade from the idle colour to the
			// active one — the pill came out green instead of purple that way.
			// Nothing changes these colours after load, so one read is enough.
			if (!$track.data('tssGliderFill')) {
				var fill = window.getComputedStyle(active).backgroundColor;

				if (fill && fill !== 'transparent' && fill.replace(/\s/g, '') !== 'rgba(0,0,0,0)') {
					$track.data('tssGliderFill', fill);
					glider.style.background = fill;
				}
			}

			// The selected button's own fill is then cleared inline — the one
			// thing that does outrank those ID-specificity controls — so the pill
			// is what the visitor actually sees moving.
			active.style.backgroundColor = 'transparent';

			if (refresh) {
				// Flush the transparent fill while the fades are still off, or it
				// would animate out once they are restored.
				void active.offsetWidth;
				$buttons.css('transition', '');
			}

			// Measured off the rects rather than offsetLeft/offsetTop so the pill
			// still lands correctly when the buttons wrap onto a second row, and
			// under RTL. clientLeft/clientTop are the track's border, which the
			// absolutely positioned pill is already offset past.
			glider.style.width = a.width + 'px';
			glider.style.height = a.height + 'px';
			glider.style.transform =
				'translate(' +
				(a.left - t.left - track.clientLeft) +
				'px,' +
				(a.top - t.top - track.clientTop) +
				'px)';

			$track.addClass('tss-glider-ready');
		});
	}

	// The filter handlers that move .selected are bound to the button wrapper, so
	// they have already run by the time this one is reached on document.
	$(document).on('click', '.tss-isotope-button-wrapper .rt-iso-button', function () {
		tssFilterGlider();
	});

	// A web font swapping in after load changes every button's width.
	if (document.fonts && document.fonts.ready && typeof document.fonts.ready.then === 'function') {
		document.fonts.ready.then(tssFilterGlider);
	}

	/**
	 * Reveal cards are focusable so keyboard users get the same overlay that
	 * :hover gives a mouse. The attribute cannot come from the layout template:
	 * that markup is printed through TSSProHelper::printHtml(), and its
	 * wp_kses_post() strips tabindex off a div. Re-applied by preFunction() after
	 * every AJAX pagination / load-more response, which replaces the cards.
	 */
	function tssMakeRevealFocusable() {
		$('.tss-reveal-item').not('[tabindex]').attr('tabindex', '0');
	}

	/**
	 * Card layouts clamp the testimonial to a fixed number of lines and reveal a
	 * "Read more" toggle only when the text is genuinely cut off. Driven off the
	 * presence of .tss-readmore-toggle so it covers any layout that opts in, not
	 * a hard-coded layout list. Runs on load, on resize and after every AJAX
	 * pagination / load-more response via preFunction().
	 */
	function clampReadMore() {
		$('.tss-readmore-toggle').closest('.single-item-wrapper').each(function () {
			var card = $(this),
				toggle = card.find('.tss-readmore-toggle').first(),
				// Most layouts clamp .item-content itself. Layout 10 makes that
				// element the speech bubble and clamps an inner .tss-quote instead, so
				// that the toggle can sit inside the bubble without being clamped away
				// with the text — measure whichever element is the clamped one.
				content = card.find('.item-content .tss-quote').first();

			if (!content.length) {
				content = card.find('.item-content').first();
			}

			if (!content.length || !toggle.length) {
				return;
			}

			// The pro read-more link already trims the text server side and lives
			// inside the clamped box, so clamping there would hide it.
			if (content.find('.rt-read-more').length) {
				card.addClass('tss-no-clamp');
				toggle.removeClass('tss-is-visible');
				return;
			}

			// Leave a card the visitor has opened alone.
			if (card.hasClass('tss-expanded')) {
				return;
			}

			var el = content.get(0);

			if (el.scrollHeight > el.clientHeight + 2) {
				toggle.addClass('tss-is-visible');
			} else {
				toggle.removeClass('tss-is-visible');
			}
		});
	}

	$(document).on('click', '.tss-readmore-toggle', function (e) {
		e.preventDefault();
		var toggle = $(this),
			card = toggle.closest('.single-item-wrapper'),
			label = toggle.find('.tss-readmore-label'),
			open = card.toggleClass('tss-expanded').hasClass('tss-expanded');

		toggle.attr('aria-expanded', open ? 'true' : 'false');

		if (label.length) {
			label.text(open ? label.data('less') : label.data('more'));
		}

		// An isotope / masonry wall positions every card absolutely, so a card
		// that just grew or shrank keeps its old slot and overlaps the ones
		// below it until something else triggers a relayout. Nothing in the
		// toggle tells the grid its content changed, so tell it here. Layouts
		// on a plain grid have no such ancestor and fall through untouched.
		var wall = card.closest('.tss-isotope, .rt-row.tss-masonry');

		if (wall.length && wall.data('isotope')) {
			wall.isotope('layout');
		}
	});

	/**
	 * Reveal-on-hover cards (the layout 9 / isotope 4 / carousel 9 medallion) opt
	 * in with .tss-reveal-item. A coarse pointer has no hover, so the overlay —
	 * and the social and share links inside it — would be unreachable there. On
	 * those devices the first tap reveals the card instead of following whatever
	 * it landed on, and a second tap behaves normally; a tap outside closes it.
	 * Devices that do hover keep the pure-CSS path and never enter this handler.
	 *
	 * Delegated from document so it survives the AJAX pagination / load-more
	 * re-render, which replaces the cards wholesale.
	 */
	function tssHasHover() {
		return !window.matchMedia || !window.matchMedia('(hover: none)').matches;
	}

	$(document).on('click', '.tss-reveal-item', function (e) {
		if (tssHasHover() || $(this).hasClass('tss-is-revealed')) {
			return;
		}

		e.preventDefault();
		$('.tss-reveal-item.tss-is-revealed').removeClass('tss-is-revealed');
		$(this).addClass('tss-is-revealed');
	});

	$(document).on('click', function (e) {
		if (tssHasHover() || $(e.target).closest('.tss-reveal-item').length) {
			return;
		}

		$('.tss-reveal-item.tss-is-revealed').removeClass('tss-is-revealed');
	});

	RTInitCaroIso();

	$(window).on('et_fb_ajax_render', function(){
		rtInitCarousel();
	});

	function RTInitCaroIso() {
		var elementInstances = [];
		var elementThumbInstances = [];

		$('.tss-wrapper').each(function (index) {
			var container = $(this);
			var str = $(this).attr('data-layout');
			if (str) {
				var qsRegex,
					buttonFilter,
					Iso = container.find('.tss-isotope'),
					caro = container.find('.tss-carousel'),
					caroThumb = container.find('.tss-carousel-main'),
					html_loading =
						'<div class="rt-loading-overlay"></div><div class="rt-loading rt-ball-clip-rotate"><div></div></div>',
					preLoader = container.find('.tss-pre-loader');
				if (preLoader.find('.rt-loading-overlay').length == 0) {
					preLoader.append(html_loading);
				}

				if (caroThumb.length) {
					var carouselOptions = RTCarouselOptions(caroThumb);

					// Carousels 11 and 12 are focus carousels: the strip shows the active face plus
					// one neighbour either side, so its slides-per-view is pinned at 3 rather
					// than following the column setting, which belongs to the MAIN slider.
					// Swiper sizes a thumb slide as strip-width divided by slides-per-view, so
					// a two-column shortcode was spreading the three faces hundreds of pixels
					// apart; pinned at 3 the gap is constant at every breakpoint and every
					// column setting.
					var tssThumbPerView = ( 'carousel11' === str || 'carousel12' === str ) ? 3 : null;

					carouselOptions.responsive = {
						0: {
							slidesPerView: tssThumbPerView || (carouselOptions.mItems ? carouselOptions.mItems : 3)
						},
						575: {
							slidesPerView: tssThumbPerView || (carouselOptions.tItems ? carouselOptions.tItems : 3)
						},
						991: {
							slidesPerView: tssThumbPerView || (carouselOptions.items ? carouselOptions.items : 5)
						}
					};

					RTElementThumbCarousel(container, carouselOptions, elementThumbInstances, index);

					// Carousel 12 shows its controls BELOW the face strip. The arrows and the
					// pagination are printed inside the main swiper container, which the render
					// paths emit before the strip, so their position cannot be fixed in CSS -
					// they are nested one level deeper than the strip they need to follow.
					// Moving them AFTER swiper has initialised is safe: swiper stores element
					// references and binds its click handlers to the nodes themselves, and both
					// survive a reparent. Doing it before init would instead break
					// uniqueNavElements, which narrows a shared selector by searching inside the
					// swiper element.
					if ( 'carousel12' === str ) {
						var tssThumbEl = container.find('.tss-carousel-thumb').last();
						var tssCtrls = container.find('.tss-carousel-controls');
						
						if ( ! tssCtrls.length ) {
							tssCtrls = $('<div class="tss-carousel-controls"></div>').insertAfter( tssThumbEl );
						}
						
						container.find('.tss-carousel-main').children('.swiper-arrow, .swiper-pagination').appendTo( tssCtrls );
					}

					caroThumb.parents('.rt-row').removeClass('tss-pre-loader');
					caroThumb.parents('.rt-row').find('.rt-loading-overlay, .rt-loading').fadeOut(100);

					// Updating the sliders
					setTimeout(function () {
						elementThumbInstances.forEach(function (sliders) {
							sliders.forEach(function (slider) {
								slider.update();
							});
						});
					}, 50);
				}

				if (caro.length) {
					var carouselEl = {
						container: container.find('.tss-carousel'),
						navPrev: '.swiper-button-prev',
						navNext: '.swiper-button-next',
						dotsEl: '.swiper-pagination'
					};

					var carouselOptions = RTCarouselOptions(caro);

					carouselOptions.responsive = {
						0: {
							slidesPerView: carouselOptions.mItems ? carouselOptions.mItems : 1,
							slidesPerGroup: carouselOptions.mItems ? carouselOptions.mItems : 1
						},
						767: {
							slidesPerView: carouselOptions.tItems ? carouselOptions.tItems : 2,
							slidesPerGroup: carouselOptions.tItems ? carouselOptions.tItems : 2
						},
						991: {
							slidesPerView: carouselOptions.items ? carouselOptions.items : 3,
							slidesPerGroup: carouselOptions.items ? carouselOptions.items : 3
						}
					};

					RTElementCarousel(carouselEl, carouselOptions, elementInstances, index);

					// Updating the sliders
					setTimeout(function () {
						elementInstances.forEach(function (slider) {
							slider.update();
						});
					}, 50);

					// Updating the sliders in tab
					$('body').on('shown.bs.tab', 'a[data-bs-toggle="tab"], a[data-bs-toggle="pill"]', function (e) {
						elementInstances.forEach(function (slider) {
							slider.update();
						});
					});

					$('.vc_tta-tabs-list li').on('click', function () {
						setTimeout(function () {
							elementInstances.forEach(function (slider) {
								slider.update();
							});
						}, 100);
					});

					caro.parents('.rt-row').removeClass('tss-pre-loader');
					caro.parents('.rt-row').find('.rt-loading-overlay, .rt-loading').fadeOut(100);
				} else if (Iso.length) {
					var IsoButton = container.find('.tss-isotope-button-wrapper');
					if (!buttonFilter) {
						buttonFilter = IsoButton.find('.rt-iso-button.selected').data('filter');
					}
					var isotope = Iso.imagesLoaded(function () {
						Iso.parents('.rt-row').removeClass('tss-pre-loader');
						Iso.parents('.rt-row').find('.rt-loading-overlay, .rt-loading').remove();
						preFunction();
						isotope.isotope({
							itemSelector: '.isotope-item',
							masonry: {
								columnWidth: '.isotope-item'
							},
							filter: function () {
								var $this = $(this);
								var searchResult = qsRegex ? $this.text().match(qsRegex) : true;
								var buttonResult = buttonFilter ? $this.is(buttonFilter) : true;
								return searchResult && buttonResult;
							}
						});
						isoFilterCounter(container, isotope);
						tssScrollFill(container, isotope, 'isotope', IsoButton);
					});
					// use value of search field to filter
					var $quicksearch = container.find('.iso-search-input').keyup(
						debounce(function () {
							qsRegex = new RegExp($quicksearch.val(), 'gi');
							isotope.isotope();
						})
					);

					IsoButton.on('click', '.rt-iso-button', function (e) {
						e.preventDefault();
						buttonFilter = $(this).attr('data-filter');
						isotope.isotope();
						$(this).parent().find('.selected').removeClass('selected');
						$(this).addClass('selected');
					});

					if (container.find('.tss-utility .tss-load-more').length) {
						container.find('.tss-load-more').on('click', '.rt-button', function (e) {
							e.preventDefault(e);
							loadMoreButton($(this), isotope, container, 'isotope', IsoButton);
						});
					}

					if (container.find('.tss-utility .tss-scroll-load-more').length) {
						$(window).on('scroll', function () {
							var $this = container.find('.tss-utility .tss-scroll-load-more');
							scrollLoadMore($this, isotope, container, 'isotope', IsoButton);
						});
					}
				} else if (container.find('.rt-row.tss-masonry').length) {
					var masonryTarget = container.find('.rt-row.tss-masonry');
					preFunction();
					var isotopeM = masonryTarget.imagesLoaded(function () {
						isotopeM.isotope({
							itemSelector: '.masonry-grid-item',
							masonry: {
								columnWidth: '.masonry-grid-item'
							}
						});
						tssScrollFill(container, isotopeM, 'mLayout');
					});
					if (container.find('.tss-utility .tss-load-more').length) {
						container.find('.tss-load-more').on('click', '.rt-button', function (e) {
							e.preventDefault(e);
							loadMoreButton($(this), isotopeM, container, 'mLayout');
						});
					}
					if (container.find('.tss-utility .tss-scroll-load-more').length) {
						$(window).on('scroll', function () {
							var $this = container.find('.tss-utility .tss-scroll-load-more');
							if ($this.attr('data-trigger') > 0) {
								scrollLoadMore($this, isotopeM, container, 'mLayout');
							}
						});
					}

					if (container.find('.tss-utility .tss-pagination.tss-ajax').length) {
						ajaxPagination(container, isotopeM);
					}
				} else {
					if (container.find('.tss-utility  .tss-load-more').length) {
						container.find('.tss-load-more').on('click', '.rt-button', function (e) {
							e.preventDefault(e);
							loadMoreButton($(this), isotopeM, container, 'eLayout');
						});
					}
					if (container.find('.tss-utility .tss-scroll-load-more').length) {
						$(window).on('scroll', function () {
							var $this = container.find('.tss-utility .tss-scroll-load-more');
							if ($this.attr('data-trigger') > 0) {
								scrollLoadMore($this, isotopeM, container, 'eLayout');
							}
						});
						tssScrollFill(container, isotopeM, 'eLayout');
					}
					if (container.find('.tss-utility .tss-pagination.tss-ajax').length) {
						ajaxPagination(container);
					}
				}
			}
		});
	}

	function RTCarouselOptions(el) {
		return {
			items: parseInt(el.data('items-desktop')),
			tItems: parseInt(el.data('items-tab')),
			mItems: parseInt(el.data('items-mobile')),
			loop: el.data('loop'),
			nav: el.data('nav'),
			dots: el.data('dots'),
			autoplay: el.data('autoplay'),
			autoplayDelay: parseInt(el.data('autoplay-timeout'), 10),
			autoPlayHoverPause: el.data('autoplay-hover-pause'),
			autoHeight: el.data('auto-height'),
			lazyLoad: el.data('lazy-load'),
			rtl: el.data('rtl'),
			smartSpeed: parseInt(el.data('smart-speed'))
		};
	}

	function RTElementCarousel(elements, carouselOptions, instance, index) {
		var slider = elements.container.addClass('instance-' + index);
		elements.container.parent().find(elements.navPrev).addClass('prev-' + index);
		elements.container.parent().find(elements.navNext).addClass('next-' + index);

		var options = {
			slidesPerView: carouselOptions.items ? carouselOptions.items : 2,
			slidesPerGroup: carouselOptions.items ? carouselOptions.items : 2,
			spaceBetween: 0,
			speed: carouselOptions.smartSpeed ? carouselOptions.smartSpeed : 1000,
			loop: carouselOptions.loop ? carouselOptions.loop : false,
			autoHeight: carouselOptions.autoHeight ? carouselOptions.autoHeight : false,

			// Responsive breakpoints
			breakpoints: carouselOptions.responsive
		};

		if (carouselOptions.autoplay) {
			options.autoplay = {
				delay: carouselOptions.autoplayDelay,
				pauseOnMouseEnter: carouselOptions.autoPlayHoverPause,
				disableOnInteraction: false
			};
		}

		if (carouselOptions.lazyLoad) {
			options.preloadImages = false;
			options.lazy = true;
		}

		if (carouselOptions.nav) {
			options.navigation = {
				nextEl: elements.navNext,
				prevEl: elements.navPrev
			};
		}

		if (carouselOptions.dots) {
			options.pagination = {
				el: elements.dotsEl,
				type: 'bullets',
				clickable: true
			};
		}
		instance[index] = new Swiper(slider[0], options);
	}

	function RTElementThumbCarousel(container, options, instance, index) {
		// Params
		var mainSlider = container.find('.tss-carousel-main').addClass('instance-' + index),
			navSlider = container.find('.tss-carousel-thumb').addClass('instance-' + index),
			navPrev = '.swiper-button-prev',
			navNext = '.swiper-button-next',
			dotsEl = '.swiper-pagination';

		container.find(navPrev).addClass('prev-' + index);
		container.find(navNext).addClass('next-' + index);

		// Main Slider
		var mainSliderOptions = {
			loop: true,
			speed: options.smartSpeed ? options.smartSpeed : 1000,
			loopedSlides: 5,
			autoHeight: options.autoHeight ? options.autoHeight : false
		};

		if (options.nav) {
			mainSliderOptions.navigation = {
				nextEl: navNext,
				prevEl: navPrev
			};
		}

		if (options.dots) {
			mainSliderOptions.pagination = {
				el: dotsEl,
				type: 'bullets',
				clickable: true
			};
		}
		var main = new Swiper(mainSlider[0], mainSliderOptions);

		// Navigation Slider
		var navSliderOptions = {
			loop: true,
			speed: options.smartSpeed ? options.smartSpeed : 1000,
			observer: true,
			observerParents: true,
			slidesPerView: options.items ? options.items : 5,
			centeredSlides: true,
			spaceBetween: 0,
			touchRatio: 0.2,
			slideToClickedSlide: true,
			loopedSlides: 5,
			watchSlidesProgress: true,
			breakpoints: options.responsive
		};

		if (options.autoplay) {
			navSliderOptions.autoplay = {
				delay: options.autoplayDelay,
				pauseOnMouseEnter: options.autoPlayHoverPause,
				disableOnInteraction: false
			};
		}

		if (options.lazyLoad) {
			navSliderOptions.preloadImages = false;
			navSliderOptions.lazy = true;
		}

		var thumb = new Swiper(navSlider[0], navSliderOptions);

		// Syncing the sliders
		main.controller.control = thumb;
		thumb.controller.control = main;

		instance[index] = [main, thumb];
	}

	/**
	 * The open isotope category filter, or null when there is nothing useful to
	 * say about one: no filter bar, "Show all" is the open tab, or the renderer
	 * sent no data-filter-total (the rating filter has no category tally).
	 */
	function tssActiveFilter(container) {
		var active = container.find('.tss-isotope-button-wrapper .rt-iso-button.selected').first();

		if (!active.length) {
			return null;
		}

		var filter = active.attr('data-filter'),
			total = parseInt(active.attr('data-filter-total'), 10);

		if (!filter || isNaN(total)) {
			return null;
		}

		// "Show all" filters on '*'; the cards themselves are .isotope-item, which
		// is isotope's own itemSelector here.
		var selector = filter === '*' ? '.isotope-item' : filter;

		return {
			selector: selector,
			// "Show all" is not a category — it must never be auto-filled, or
			// opening it would defeat pagination and pull the whole wall.
			isAll: filter === '*',
			total: total,
			loaded: container.find('.tss-isotope').find(selector).length
		};
	}

	/**
	 * Puts the right remaining-count on the Load More button.
	 *
	 * With a category filter open that is the remainder for THAT category, which
	 * is the only number that means anything beside a filtered wall. The button
	 * used to show the whole-query remainder, so opening "Clients" with 2 cards
	 * showing still offered "Load More (9)" — and it read the same on every tab.
	 */
	function tssRelabelLoadMore(container) {
		var button = container.find('.tss-load-more .rt-button').first();

		if (!button.length) {
			return;
		}

		var base = button.attr('data-base-text');

		if (typeof base === 'undefined' || base === false || base === '') {
			base = button.clone().children().remove().end().text().trim();

			// Never cache an empty label. beforeSend swaps the button for its
			// "Loading ..." span, so a capture taken mid-request would lose the
			// wording and the button would come back reading just "(8)".
			// tssCacheLoadMoreLabel() normally gets there first, at page load.
			if (base) {
				button.attr('data-base-text', base);
			}
		}

		if (!base) {
			return;
		}

		// found-posts and total-pages describe whichever set is on show — the whole
		// wall, or one category once tssApplyServerFilter() has re-queried — so the
		// plain remainder is already the right number for the open tab.
		var remaining =
			parseInt(button.attr('data-found-posts'), 10) -
			parseInt(button.attr('data-posts-per-page'), 10) * parseInt(button.attr('data-paged'), 10);

		if (remaining > 0) {
			button.html(base + ' <span>(' + remaining + ')</span>');
		} else {
			button.html(button.data('no-more-post-text'));
		}
	}

	/**
	 * Brings in the rest of a category the moment its tab is opened.
	 *
	 * The filter runs in the browser over whatever has been paged in, so opening
	 * "Developers" could show 2 cards under a tab reading 6, with a pager offering
	 * 4 more — every number right on its own and nothing agreeing with anything.
	 * Clicking a tab is an explicit request for that category, so fetch what it is
	 * missing and let the tab, the wall and the pager tell the same story.
	 *
	 * Only ever on a click, only for real categories, and bounded by the page
	 * count: "Show all" is excluded so ordinary paging is untouched.
	 */
	/**
	 * Whichever element owns this wall's current page number.
	 *
	 * "Load More" keeps it on its button, load-on-scroll on its trigger div, and
	 * the pager types that have no client-side element at all get a
	 * .tss-iso-pager-state span from the renderer. Exactly one of them exists per
	 * wall, so there is never a second copy to drift out of step.
	 */
	function tssPagerState(container) {
		var el = container.find('.tss-load-more .rt-button').first();

		if (!el.length) {
			el = container.find('.tss-scroll-load-more').first();
		}

		if (!el.length) {
			el = container.find('.tss-iso-pager-state').first();
		}

		return el.length ? el : null;
	}

	// The two pager elements count differently: .tss-scroll-load-more stores the
	// NEXT page to fetch and calls its ceiling data-max-page, while the Load More
	// button and the state span store the CURRENT page against data-total-pages.
	// Everything below works in "next page" / "last page" terms.
	function tssPagerNext(state) {
		var paged = parseInt(state.attr('data-paged'), 10);

		return state.is('.tss-scroll-load-more') ? paged : paged + 1;
	}

	function tssPagerLast(state) {
		return parseInt(
			state.is('.tss-scroll-load-more') ? state.attr('data-max-page') : state.attr('data-total-pages'),
			10
		);
	}

	function tssPagerAdvance(state, fetched) {
		state.attr('data-paged', state.is('.tss-scroll-load-more') ? fetched + 1 : fetched);
	}

	function tssPagerSetLast(state, pages) {
		state.attr(state.is('.tss-scroll-load-more') ? 'data-max-page' : 'data-total-pages', pages);
	}

	// The category the pager is currently querying, '0' for none.
	function tssPagerTerm(state) {
		var term = state.attr('data-tss-term');

		return term && term !== '0' ? term : 0;
	}

	/**
	 * Re-queries the wall for one category and re-points the pager at it.
	 *
	 * A category tab is a query, not a mask over what happens to be loaded. That
	 * is what makes "5 per page" mean five of THAT category rather than five of
	 * the whole wall, and it is the only way the pager can count down the set the
	 * visitor is actually looking at. So the term goes to the server, the wall is
	 * replaced with its first page, and found / pages come back describing the
	 * filtered set.
	 *
	 * Returns false when it does not apply, and the caller falls back to filtering
	 * in the browser: a wall with no pager already holds everything, and numbered
	 * pagination is server-rendered links that know nothing about the open tab —
	 * that mode uses tssFillPages() instead.
	 */
	function tssApplyServerFilter(container, term) {
		var state = tssPagerState(container);

		if (!state || state.is('.tss-iso-pager-state')) {
			return false;
		}

		if (state.data('tss-requesting')) {
			return true;
		}

		var isotope = container.find('.tss-isotope'),
			elData = state.data('tss-elementor'),
			data = 'scID=' + state.attr('data-sc-id') + '&paged=1&tssTerm=' + term;

		if (typeof elData !== 'undefined') {
			data = 'scID=elementor&elData=' + JSON.stringify(elData) + '&paged=1&tssTerm=' + term;
		}

		data = data + '&action=tssLoadMore&' + tss.nonceId + '=' + tss.nonce;

		state.data('tss-requesting', true);

		$.ajax({
			type: 'post',
			url: tss.ajaxurl,
			data: data,
			success: function (response) {
				state.removeData('tss-requesting');

				if (response.error) {
					return;
				}

				// The whole wall is swapped for the first page of the set now on
				// show, so isotope is told to re-collect its items rather than being
				// handed an append.
				isotope.html(response.data).isotope('reloadItems').isotope('updateSortData');

				state.attr('data-tss-term', term);
				state.attr('data-found-posts', response.found);
				tssPagerSetLast(state, response.pages);
				tssPagerAdvance(state, 1);

				// loadMoreButton() parks pointer-events on the button when it runs
				// out of pages. A different tab has its own page count, so give it
				// back.
				state.css('pointer-events', '');

				isotope.imagesLoaded(function () {
					preFunction();
					isotope.isotope();
					isoFilterCounter(container, isotope);
				});
			},
			error: function () {
				state.removeData('tss-requesting');
			}
		});

		return true;
	}

	/**
	 * The numbered-pagination fallback.
	 *
	 * Numbered pagination is server-rendered links carrying no notion of the open
	 * tab, so a category cannot be paged the way tssApplyServerFilter() pages one
	 * for the browser-driven pagers. The next best thing is to bring the whole
	 * category into the wall, so the tab at least shows everything it counts
	 * rather than the slice that happened to be rendered.
	 */
	function tssFillActiveFilter(container) {
		var state = tssPagerState(container),
			active = tssActiveFilter(container);

		if (!state || !state.is('.tss-iso-pager-state')) {
			return;
		}

		if (!active || active.isAll || active.loaded >= active.total) {
			return;
		}

		if (state.data('tss-filling')) {
			return;
		}

		if (!(tssPagerNext(state) <= tssPagerLast(state))) {
			return;
		}

		state.data('tss-filling', true);
		state.data('tss-fill-depth', 0);
		tssFillPages(container, state);
	}

	/**
	 * Pulls in pages until the open category is complete.
	 *
	 * Used where there is no Load More button to drive: numbered pagination and
	 * load-on-scroll. Without it a category tab on a paginated wall shows only
	 * the slice that happened to be rendered — "Developers 6" over two cards, or
	 * an empty wall under a tab reading 3 — and nothing the visitor can click
	 * fixes it.
	 *
	 * Fetches FORWARD from the rendered page, so on page 1 (where visitors land)
	 * the category ends up complete and in order. Deeper into a numbered pager it
	 * can only reach the pages after the current one — the earlier ones were
	 * never in this wall to begin with, which is a limitation of filtering in the
	 * browser over a server-paged wall, not something this can repair.
	 */
	function tssFillPages(container, state) {
		var isotope = container.find('.tss-isotope'),
			isoButton = container.find('.tss-isotope-button-wrapper'),
			elData = state.data('tss-elementor'),
			scID = state.attr('data-sc-id'),
			depth = parseInt(state.data('tss-fill-depth'), 10) || 0,
			paged = tssPagerNext(state);

		var done = function () {
			state.removeData('tss-filling');
		};

		if (!(paged <= tssPagerLast(state)) || depth >= 25) {
			done();
			return;
		}

		var data = 'scID=' + scID + '&paged=' + paged;

		if (typeof elData !== 'undefined') {
			data = 'scID=elementor&elData=' + JSON.stringify(elData) + '&paged=' + paged;
		}

		data = data + '&action=tssLoadMore&' + tss.nonceId + '=' + tss.nonce;

		$.ajax({
			type: 'post',
			url: tss.ajaxurl,
			data: data,
			success: function (response) {
				if (response.error) {
					done();
					return;
				}

				tssPagerAdvance(state, paged);
				state.data('tss-fill-depth', depth + 1);
				renderIsotope(container, isotope, response.data, isoButton);

				var active = tssActiveFilter(container);

				if (active && !active.isAll && active.loaded < active.total) {
					tssFillPages(container, state);
				} else {
					done();
				}
			},
			error: function () {
				done();
			}
		});
	}

	// Opening a tab re-queries the wall for that category where the pager is one
	// the browser drives. Where it is not, the tab still changes what "load more"
	// means, so relabel it and pull in whatever the category is missing.
	$(document).on('click', '.tss-isotope-button-wrapper .rt-iso-button', function () {
		var container = $(this).closest('.tss-wrapper'),
			filter = $(this).attr('data-filter'),
			term = 0;

		if (filter && filter.indexOf('.iso_') === 0) {
			term = parseInt(filter.slice(5), 10) || 0;
		}

		// Rating-mode buttons filter on .rating_N — there is no category to query
		// by, so they stay a browser-side filter.
		if ((filter === '*' || term) && tssApplyServerFilter(container, term)) {
			return;
		}

		tssRelabelLoadMore(container);
		tssFillActiveFilter(container);
	});

	function isoFilterCounter(container, isotope) {
		container.find('.tss-isotope-button-wrapper .rt-iso-button').each(function () {
			var self = $(this),
				filter = self.data('filter'),
				total = self.attr('data-filter-total');

			// The renderers send the real total for the whole wall. Counting the
			// DOM only ever described the page that happened to be loaded, so with
			// pagination on "Clients 2" meant "2 of them so far" and grew as the
			// visitor paged. Fall back to the DOM where there is no total — the
			// rating filter has no category tally — which is the old behaviour.
			if (typeof total !== 'undefined' && total !== false && total !== '') {
				self.attr('data-filter-counter', total);
			} else if (filter != '*') {
				self.attr('data-filter-counter', isotope.find(filter).length);
			} else {
				// "Show all" counts the cards the wall holds. This used to be the
				// SUM of the category counts, which double-counted anything filed
				// under two categories and missed anything filed under none — a wall
				// of 12 reported 15. '.isotope-item' is isotope's own itemSelector,
				// so it is exactly the set the '*' filter matches.
				self.attr('data-filter-counter', isotope.find('.isotope-item').length);
			}
		});

		tssRelabelLoadMore(container);

		// Isotope 1 renders these counts as a chip inside the button, so filling
		// them in changes every button's width — the sliding pill has to be
		// re-measured or it keeps the width it had before the counts arrived.
		tssFilterGlider();
	}

	function loadMoreButton($this, $isotope, container, layout, IsoButton) {
		// The label and its remaining-count are owned by tssRelabelLoadMore().
		var loadingText = $this.data('loading-text'),
			scID = $this.attr('data-sc-id'),
			elData = $this.data('tss-elementor'),
			paged = parseInt($this.attr('data-paged'), 10) + 1,
			totalPages = parseInt($this.attr('data-total-pages'), 10),
			foundPosts = parseInt($this.attr('data-found-posts'), 10),
			postsPerPage = parseInt($this.attr('data-posts-per-page'), 10),
			data = 'scID=' + scID + '&paged=' + paged,
			// The open category filter as it stands BEFORE this page lands, so the
			// response can be checked for whether it actually contributed anything
			// to the filtered view.
			filterBefore = tssActiveFilter(container),
			data;

		data = 'scID=' + scID + '&paged=' + paged;

		if (typeof elData !== 'undefined') {
			data = 'scID=elementor&elData=' + JSON.stringify(elData) + '&paged=' + paged;
		}

		data = data + '&action=tssLoadMore&' + tss.nonceId + '=' + tss.nonce;

		// Keep paging inside the open category — see tssApplyServerFilter().
		if (tssPagerTerm($this)) {
			data = data + '&tssTerm=' + tssPagerTerm($this);
		}
		if (container.data('archive')) {
			data = data + '&archive=' + container.data('archive');
			if (container.data('archive-value')) {
				data = data + '&archive-value=' + container.data('archive-value');
			}
		}

		$.ajax({
			type: 'post',
			url: tss.ajaxurl,
			data: data,
			beforeSend: function () {
				$this.html('<span class="more-loading">' + loadingText + '</span>');
			},
			success: function (data) {
				if (!data.error) {
					$this.attr('data-paged', paged);
					if (layout == 'isotope') {
						renderIsotope(container, $isotope, data.data, IsoButton);
					} else if (layout == 'mLayout') {
						$isotope
							.append(data.data)
							.isotope('appended', data.data)
							.isotope('updateSortData')
							.isotope('reloadItems');
						$isotope.imagesLoaded(function () {
							$isotope.isotope();
						});
					} else {
						container.children('.rt-row').append(data.data);
						container.children('.rt-row').imagesLoaded(function () {
							preFunction();
						});
					}

					// Labelled from what is actually left now the page has landed.
					// renderIsotope() has already done this via isoFilterCounter()
					// on the isotope path; the other two layouts have not.
					if (layout != 'isotope') {
						tssRelabelLoadMore(container);
					}

					if (totalPages <= paged) {
						$this.css("pointer-events", "none");
						$this.unbind("click");
					}

					// The filter is applied in the browser but the pages come from the
					// server, so a category's cards are scattered across pages nobody
					// has loaded. Finish the category rather than stopping on a page
					// boundary: otherwise a tab reads "Developers 6" over 2 cards and
					// the pager offers 4 more, three numbers describing three
					// different things. Bounded — data-paged advances every pass, and
					// "Show all" is excluded so plain paging is untouched.
					$this.removeData('tss-filling');
				} else {
					$this.text(data.msg);
					$this.attr('disabled', 'disabled');
					$this.parent().hide();
				}
			},
			error: function (jqXHR, textStatus, errorThrown) {
				$this.removeData('tss-filling');
				container.find('.more-loading').remove();
				alert(textStatus + ' (' + errorThrown + ')');
			}
		});
		return false;
	}

	function renderIsotope(container, $isotope, data, IsoButton) {
		var qsRegexG, buttonFilter;
		if (!buttonFilter) {
			buttonFilter = IsoButton.find('.rt-iso-button.selected').data('filter');
		}

		$isotope.append(data).isotope('appended', data).isotope('reloadItems').isotope('updateSortData');
		$isotope.imagesLoaded(function () {
			preFunction();
			$isotope.isotope();
		});

		$(IsoButton).on('click', '.rt-iso-button', function (e) {
			e.preventDefault();
			buttonFilter = $(this).attr('data-filter');
			$isotope.isotope();
			$(this).parent().find('.selected').removeClass('selected');
			$(this).addClass('selected');
		});
		var $quicksearch = container.find('.iso-search-input').keyup(
			debounce(function () {
				qsRegexG = new RegExp($quicksearch.val(), 'gi');
				$isotope.isotope();
			})
		);
		isoFilterCounter(container, $isotope);
	}

	// The scroll trigger was only checked on a scroll event, so when the first
	// page fit the screen there was nothing to scroll and Load on Scroll never
	// loaded anything. Check once the wall is laid out; scrollLoadMore() checks
	// again after each batch, until the trigger leaves the screen or the last
	// page is in.
	function tssScrollFill(container, $isotope, layout, IsoButton) {
		var $this = container.find('.tss-utility .tss-scroll-load-more');

		if ($this.length) {
			scrollLoadMore($this, $isotope, container, layout, IsoButton);
		}
	}

	function scrollLoadMore($this, $isotope, container, layout, IsoButton) {
		var viewportHeight = $(window).height();
		var scrollTop = $(window).scrollTop();
		var targetHeight = $this.offset().top + $this.outerHeight() - 50;
		var targetScroll = scrollTop + viewportHeight;

		if (targetScroll >= targetHeight) {
			var trigger = $this.attr('data-trigger');
			if (trigger == 1) {
				// $this.data('trigger', false);
				$this.attr('data-trigger', 0);
				var data,
					scID = $this.attr('data-sc-id'),
					elData = $this.data('tss-elementor'),
					maxpage = parseInt($this.attr('data-max-page'), 10),
					paged = parseInt($this.attr('data-paged'), 10);

				data = 'scID=' + scID + '&paged=' + paged;

				if (typeof elData !== 'undefined') {
					data = 'scID=elementor&elData=' + JSON.stringify(elData) + '&paged=' + paged;
				}

				// data = 'scID=' + scID + '&paged=' + paged;
				data = data + '&action=tssLoadMore&' + tss.nonceId + '=' + tss.nonce;

				// Keep paging inside the open category — see tssApplyServerFilter().
				if (tssPagerTerm($this)) {
					data = data + '&tssTerm=' + tssPagerTerm($this);
				}

				if (container.data('archive')) {
					data = data + '&archive=' + container.data('archive');
					if (container.data('archive-value')) {
						data = data + '&archive-value=' + container.data('archive-value');
					}
				}
				$.ajax({
					type: 'post',
					url: tss.ajaxurl,
					data: data,
					beforeSend: function () {
						$this.html('<span class="more-loading">Loading ...</span>');
					},
					success: function (data) {
						if (!data.error) {
							$this.attr('data-paged', paged + 1);
							if (layout == 'isotope') {
								renderIsotope(container, $isotope, data.data, IsoButton);
							} else if (layout == 'mLayout') {
								$isotope
									.append(data.data)
									.isotope('appended', data.data)
									.isotope('updateSortData')
									.isotope('reloadItems');
								$isotope.imagesLoaded(function () {
									$isotope.isotope();
								});
							} else {
								container.children('.rt-row').append(data.data);
								container.children('.rt-row').imagesLoaded(function () {
									preFunction();
								});
							}
							$this.html('');
							$this.attr('data-trigger', 1);

							// Numbers, not strings: as text "10" > "9" is false, so one more
							// empty request went out after the last page.
							if (paged + 1 > maxpage) {
								$this.attr('data-trigger', 0);
							}

							// Still short of the screen? Fetch the next page too - see tssScrollFill().
							setTimeout(function () {
								scrollLoadMore($this, $isotope, container, layout, IsoButton);
							}, 400);
						} else {
							$this.html('');
							$this.attr('data-trigger', 0);
						}
					},
					error: function (jqXHR, textStatus, errorThrown) {
						container.find('.more-loading').remove();
						alert(textStatus + ' (' + errorThrown + ')');
					}
				});
			} // if trigger == 1
		}
	}

	function ajaxPagination(container, isotopeM) {
		container.find('.tss-pagination.tss-ajax ul li').on('click', 'a', function (e) {
			e.preventDefault();
			var data,
				$this = $(this),
				target = $this.parents('li'),
				parent = target.parents('.tss-pagination.tss-ajax'),
				activeLi = parent.find('li.active'),
				activeNumber = parseInt(activeLi.text(), 10),
				replaced = "<a data-paged='" + activeNumber + "' href='#'>" + activeNumber + '</a>',
				scID = parent.data('sc-id'),
				elData = parent.data('tss-elementor'),
				paged = $this.data('paged');
			activeLi.html(replaced);
			parent.find('li').removeClass('active');
			target.addClass('active');
			target.html('<span>' + paged + '</span>');

			data = 'scID=' + scID + '&paged=' + paged;

			if (typeof elData !== 'undefined') {
				data = 'scID=elementor&elData=' + JSON.stringify(elData) + '&paged=' + paged;
			}

			data = data + '&action=tssLoadMore&' + tss.nonceId + '=' + tss.nonce;

			if (container.data('archive')) {
				data = data + '&archive=' + container.data('archive');
				if (container.data('archive-value')) {
					data = data + '&archive-value=' + container.data('archive-value');
				}
			}
			$.ajax({
				type: 'post',
				url: tss.ajaxurl,
				data: data,
				beforeSend: function () {
					parent.append(
						'<div class="tss-loading-holder"><span class="more-loading">Loading ...</span></div>'
					);
				},
				success: function (data) {

					if (!data.error) {
						if (typeof isotopeM === 'undefined') {
							container.children('.rt-row').animate({
								opacity: 0
							});
							setTimeout(function () {
								container.children('.rt-row').html(data.data);
								container.children('.rt-row').imagesLoaded(function () {
									preFunction();
									container.children('.rt-row').animate(
										{
											opacity: 1
										},
										1000
									);
								});
							}, 500);
						} else {
							container.children('.rt-row').find('.masonry-grid-item').remove();
							isotopeM
								.append(data.data)
								.isotope('appended', data.data)
								.isotope('updateSortData')
								.isotope('reloadItems');
							isotopeM.imagesLoaded(function () {
								preFunction();
								isotopeM.isotope();
							});
						}

						$('.tss-wrapper').animate({ scrollTop: 0 }, 700);
						$('html, body').animate({
							scrollTop: ($('.tss-wrapper').offset().top - 100)
						}, 700);
					} else {
						alert(data.msg);
					}
					container.find('.tss-loading-holder').remove();
				},
				error: function (jqXHR, textStatus, errorThrown) {
					container.find('.tss-loading-holder').remove();
					alert(textStatus + ' (' + errorThrown + ')');
				}
			});
		});
	}

	// debounce so filtering doesn't happen every millisecond
	function debounce(fn, threshold) {
		var timeout;
		return function debounced() {
			if (timeout) {
				clearTimeout(timeout);
			}

			function delayed() {
				fn();
				timeout = null;
			}

			setTimeout(delayed, threshold || 100);
		};
	}

	function overlayIconResize() {
		$('.tlp-item').each(function () {
			var holder_height = $(this).height();
			var a_height = $(this).find('.tlp-overlay .link-icon').height();
			var h = (holder_height - a_height) / 2;
			$(this).find('.link-icon').css('margin-top', h + 'px');
		});
	}

	function RTPromoContent() {
		parent.document.addEventListener("mousedown", function (e) {
			var widgets = parent.document.querySelectorAll(".elementor-element--promotion");

			if (widgets.length > 0) {
				for (var i = 0; i < widgets.length; i++) {
					if (widgets[i].contains(e.target)) {
						var dialog = parent.document.querySelector("#elementor-element--promotion__dialog");
						var icon = widgets[i].querySelector(".icon > i");

						if (icon.classList.toString().indexOf("tss-promotional-element") >= 0) {
							dialog.querySelector(".dialog-buttons-action").style.visibility = "hidden";
							dialog.querySelector(".dialog-buttons-action").style.width = 0;
							dialog.querySelector(".dialog-buttons-action").style.height = 0;
							dialog.querySelector(".dialog-buttons-action").style.opacity = 0;
							dialog.querySelector(".dialog-buttons-action").style.padding = 0;

							if (dialog.querySelector(".rt-dialog-buttons-action") === null) {
								var button = document.createElement("a");
								var buttonText = document.createTextNode("Get Pro");

								button.setAttribute("href", "https://www.radiustheme.com/downloads/wp-testimonial-slider-showcase-pro-wordpress/");
								button.setAttribute("target", "_blank");
								button.classList.add(
									"dialog-button",
									"dialog-action",
									"dialog-buttons-action",
									"elementor-button",
									"elementor-button-success",
									"rt-dialog-buttons-action"
								);
								button.appendChild(buttonText);

								dialog.querySelector(".dialog-buttons-action").insertAdjacentHTML("afterend", button.outerHTML);
							} else {
								dialog.querySelector(".rt-dialog-buttons-action").style.display = "";
							}
						} else {
							dialog.querySelector(".dialog-buttons-action").style.display = "";
							dialog.querySelector(".dialog-buttons-action").style.visibility = "";
							dialog.querySelector(".dialog-buttons-action").style.width = "";
							dialog.querySelector(".dialog-buttons-action").style.height = "";
							dialog.querySelector(".dialog-buttons-action").style.opacity = "";
							dialog.querySelector(".dialog-buttons-action").style.padding = "";

							if (dialog.querySelector(".rt-dialog-buttons-action") !== null) {
								dialog.querySelector(".rt-dialog-buttons-action").style.display = "none";
							}
						}

						break;
					}
				}
			}
		});
	}

	function RTInitIsotope() {
		var container = jQuery('.tss-wrapper');

		if (container.find('.tss-isotope, .tss-isotope2').length) {
			container.find('.tss-isotope, .tss-isotope2').isotope();
		}

		if (container.find('.rt-row.tss-masonry').length) {
			var masonryTarget = container.find('.rt-row.tss-masonry');
			masonryTarget.isotope({
				itemSelector: '.isotope-item',
				masonry: { columnWidth: '.isotope-item' }
			});
		}
	}

	/**
	 * Re-creates a freshly rendered widget's isotope / masonry items in this window.
	 *
	 * The Elementor editor builds a dropped or re-rendered widget's HTML in the
	 * editor window and then moves it into this preview frame, so those nodes keep
	 * the editor window's HTMLElement. Isotope skips anything that is not an
	 * instance of its own window's HTMLElement, found no items and laid out
	 * nothing until a page reload served the markup natively. Re-parsing the
	 * wall's contents here gives Isotope nodes it accepts. Walls that are already
	 * native — including ones Isotope has laid out — are left untouched.
	 *
	 * @param {jQuery} $scope The widget element that just rendered.
	 */
	function tssNativeLayoutItems($scope) {
		if (!$scope || !$scope.length) {
			return;
		}

		$scope.find('.tss-isotope, .rt-row.tss-masonry').each(function () {
			var first = this.firstElementChild;

			if (first && !(first instanceof window.HTMLElement)) {
				this.innerHTML = this.innerHTML;
			}
		});
	}

	// Elementor Frontend Load
	$(window).on('elementor/frontend/init', function () {
		if (elementorFrontend.isEditMode()) {
			elementorFrontend.hooks.addAction('frontend/element_ready/widget', function ($scope) {
				tssNativeLayoutItems($scope);
				RTInitCaroIso();
				preFunction();
			});

			// Style controls restyle the preview by rewriting the widget's
			// <style id="elementor-style-{id}"> without re-rendering it, so the
			// filter pill would keep the colour it read on first render.
			if (window.MutationObserver) {
				var tssStyleTimer;

				new MutationObserver(function (records) {
					var touched = records.some(function (record) {
						var node = 3 === record.target.nodeType ? record.target.parentNode : record.target;

						if (node && node.id && 0 === node.id.indexOf('elementor-style-')) {
							return true;
						}

						return Array.prototype.some.call(record.addedNodes, function (added) {
							return added.id && 0 === added.id.indexOf('elementor-style-');
						});
					});

					if (touched) {
						clearTimeout(tssStyleTimer);
						tssStyleTimer = setTimeout(function () {
							tssFilterGlider(true);
						}, 60);
					}
				}).observe(document.head, { childList: true, characterData: true, subtree: true });
			}

			// Promo Content.
			RTPromoContent();
		}
	});

	// Open popup
	// $('.tlp-openPopup').on('click', function () {
	// 	const popupId = $(this).data('popup-id');
	// 	$('#' + popupId).fadeIn();
	// });
	//
	// // Close popup
	// $(document).on('click', '.close-popup', function () {
	// 	$(this).closest('.tlp-popup').fadeOut();
	// });
	//
	// // Close popup when clicking outside content
	// $(window).on('click', function (event) {
	// 	if ($(event.target).hasClass('tlp-popup')) {
	// 		$(event.target).fadeOut();
	// 	}
	// });

	/**
	 * Video-popup layouts (layout_video2 and its carousel / isotope siblings).
	 * Bound by delegation from document rather than to the elements themselves:
	 * binding directly only reaches the cards present at load, so a poster
	 * rendered by AJAX pagination or load-more had no handler and clicking it
	 * navigated away to the raw video URL instead of opening the popup.
	 */
	$(document).magnificPopup({
		delegate: '.tlp-openPopup',
		type: 'iframe'
	});


		// $('.tlp-openPopup').magnificPopup({
		// 	type: 'iframe',
		// 	mainClass: 'mfp-fade',
		// 	removalDelay: 160,
		// 	preloader: false,
		// 	fixedContentPos: false
		// });




})(jQuery);
