<?php
/**
 * Layout: Carousel 3.
 *
 * @package RT_TSS
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

$level = [];

if ( in_array( 'designation', $items, true ) && $designation ) {
	$level[] = "<span class='author-designation'>" . esc_html( $designation ) . '</span>';
}

if ( in_array( 'company', $items, true ) && $company ) {
	$level[] = "<span class='item-company'>" . esc_html( $company ) . '</span>';
}

if ( in_array( 'location', $items, true ) && $location ) {
	$level[] = "<span class='author-location'>" . esc_html( $location ) . '</span>';
}

$html  = null;
$html .= "<div class='{$grid} {$class}'>";
$html .= '<div class="single-item-wrapper">';
$html .= '<div class="tss-meta-info">';

if ( in_array( 'author_img', $items, true ) ) {
	$lazyLoader = $lazyLoad ? '<div class="swiper-lazy-preloader swiper-lazy-preloader-white"></div>' : '';
	$html      .= $link && function_exists( 'rttsp' ) ? "<div class='profile-img-wrapper'><a href='{$pLink}'>{$img}{$lazyLoader}</a></div>" : "<div class='profile-img-wrapper'>{$img}{$lazyLoader}</div>";
}

$html .= '</div>';

if ( in_array( 'rating', $items, true ) ) {
	$html .= '<div class="rating-wrapper">';

	for ( $i = 1; $i <= 5; $i++ ) {
		$starClass = 'filled';

		if ( $i > $rating ) {
			$starClass = 'empty';
		}

		$html .= "<span data-star='$i' class='star-$i dashicons dashicons-star-{$starClass}' aria-hidden='true'></span>";
	}

	$html .= '</div>';
}

if ( in_array( 'author', $items, true ) && $author ) {
	$html .= $link && function_exists( 'rttsp' ) ? "<h3 class='author-name'><a href='{$pLink}'>" . esc_html( $author ) . '</a></h3>' : "<h3 class='author-name'>" . esc_html( $author ) . '</h3>';
}

if ( ! empty( $level ) ) {
	$level     = array_filter( $level );
	$levelList = implode( ', ', $level );
	$html     .= '<h4 class="author-bio">' . $levelList . '</h4>';
}

if ( in_array( 'social_media', $items, true ) && ! empty( $social_media ) && function_exists( 'rttsp' ) ) {
	$html .= "<div class='author-social'>";
	$html .= TSSPro()->rtSocialLabel( isset( $social_label ) ? $social_label : null );

	foreach ( $social_media as $sid => $url ) {
		$html .= "<a href='{$url}' target='_blank'><span class='dashicons dashicons-{$sid}'></span></a>";
	}

	$html .= '</div>';
}

if ( in_array( 'social_share', $items, true ) && function_exists( 'rttsp' ) && isset( $iID, $scMeta, $shareItems ) ) {
	$html .= TSSPro()->rtShare( $iID, $scMeta, $shareItems );
}

$html .= '<div class="item-content-wrapper">';
if ( in_array( 'testimonial', $items, true ) && $testimonial ) {
	$html .= '<span class="tss-quote-mark" aria-hidden="true">&ldquo;</span>';
	$html .= "<div class='item-content'>{$testimonial}</div>";

	// Only when the shortcode's "Read More" field is checked. Revealed by JS
	// only when the text is actually clamped. The label reuses the existing
	// "Read more button text" option (tss_read_more_button_text /
	// tss_el_read_more_text) so a site that already set one is honoured. It arrives
	// pre-escaped from the render paths, so decode once and re-escape per context.
	// There is no matching "Read less" option, so that string stays translatable only.
	if ( in_array( 'read_more', $items, true ) ) {
		$tssMoreText = ! empty( $read_more ) ? wp_specialchars_decode( $read_more, ENT_QUOTES ) : __( 'Read more', 'testimonial-slider-showcase' );
		$tssLessText = __( 'Read less', 'testimonial-slider-showcase' );
		$html .= '<button class="tss-readmore-toggle" type="button" aria-expanded="false">';
		$html .= '<span class="tss-readmore-label" data-more="' . esc_attr( $tssMoreText ) . '" data-less="' . esc_attr( $tssLessText ) . '">' . esc_html( $tssMoreText ) . '</span>';
		// Chevron is drawn in CSS — printHtml() runs wp_kses_post(), which strips <svg>.
		$html .= '<span class="tss-readmore-icon" aria-hidden="true"></span>';
		$html .= '</button>';
	}
}

// Absorbs leftover height so the read-more sits directly under the quote.
$html .= '<div class="tss-card-spacer" aria-hidden="true"></div>';
$html .= '</div>';
$html .= '</div>';
$html .= '</div>';

TSSPro()->printHtml( $html );
