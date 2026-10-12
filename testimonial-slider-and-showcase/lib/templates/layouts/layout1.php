<?php
/**
 * Layout: Layout 1.
 *
 * Card layout: rating + quote mark on top, testimonial body in the middle,
 * author footer at the bottom. Shared by the shortcode, the Elementor grid
 * widget, the admin preview and both load-more AJAX handlers.
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

// The card. Keeps the .item-content-wrapper hook so the existing item
// background / padding style controls still target the card itself.
$html .= '<div class="item-content-wrapper">';

// Card head: rating on the left, decorative quote mark on the right.
$hasRating = in_array( 'rating', $items, true );
$html     .= '<div class="tss-card-head">';

if ( $hasRating ) {
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

// Belongs to the testimonial text, so it goes when "Show Testimonial" is off.
if ( in_array( 'testimonial', $items, true ) && $testimonial ) {
	$html .= '<span class="tss-quote-mark" aria-hidden="true">&rdquo;</span>';
}
$html .= '</div>';

// Testimonial body.
if ( in_array( 'testimonial', $items, true ) && $testimonial ) {
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

// Absorbs the leftover height so the author footer sits on the card floor.
// The testimonial itself must not flex-grow, or it would stretch past the
// six-line clamp and the clamp would never engage.
$html .= '<div class="tss-card-spacer" aria-hidden="true"></div>';

// Author footer.
$hasAuthorImg = in_array( 'author_img', $items, true );
$hasAuthor    = in_array( 'author', $items, true ) && $author;
$hasSocial    = in_array( 'social_media', $items, true ) && ! empty( $social_media ) && function_exists( 'rttsp' );
$hasShare     = in_array( 'social_share', $items, true ) && function_exists( 'rttsp' ) && isset( $iID, $scMeta, $shareItems );

if ( $hasAuthorImg || $hasAuthor || ! empty( $level ) || $hasSocial || $hasShare ) {
	$html .= '<div class="tss-meta-info">';

	if ( $hasAuthorImg ) {
		$html .= $link && function_exists( 'rttsp' ) ? "<div class='profile-img-wrapper'><a href='{$pLink}'>{$img}</a></div>" : "<div class='profile-img-wrapper'>{$img}</div>";
	}

	$html .= '<div class="tss-author-meta">';

	if ( $hasAuthor ) {
		$html .= $link && function_exists( 'rttsp' ) ? "<h3 class='author-name'><a href='{$pLink}'>" . esc_html( $author ) . '</a></h3>' : "<h3 class='author-name'>" . esc_html( $author ) . '</h3>';
	}

	if ( ! empty( $level ) ) {
		$level     = array_filter( $level );
		$levelList = implode( ', ', $level );
		$html     .= '<h4 class="author-bio">' . $levelList . '</h4>';
	}

	$html .= '</div>';

	if ( $hasSocial || $hasShare ) {
		$html .= '<div class="tss-meta-actions">';

		if ( $hasSocial ) {
			$html .= "<div class='author-social'>";
			// Labelled like rtShare()'s "Share on:", so the profile links can't be
			// mistaken for the share buttons when both rows are shown.
			$html .= TSSPro()->rtSocialLabel( isset( $social_label ) ? $social_label : null, true );

			foreach ( $social_media as $sid => $url ) {
				$html .= "<a href='{$url}' target='_blank'><span class='dashicons dashicons-{$sid}'></span></a>";
			}

			$html .= '</div>';
		}

		if ( $hasShare ) {
			$html .= TSSPro()->rtShare( $iID, $scMeta, $shareItems );
		}

		$html .= '</div>';
	}

	$html .= '</div>';
}

$html .= '</div>'; // item-content-wrapper.
$html .= '</div>'; // single-item-wrapper.
$html .= '</div>'; // grid column.

TSSPro()->printHtml( $html );
