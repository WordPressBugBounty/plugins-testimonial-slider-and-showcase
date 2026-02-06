<div class="tlp-promo-container">
    <div class="tlp-promo-inner">
        <div class="promo-image">
            <img src="<?php echo esc_url( TSSPro()->assetsUrl . 'images/testimonial-banner.png' ); ?>" alt="Testimonial Slider Plugin">
        </div>
        <div class="promo-features">
            <h2 class="promo-title">
                Testimonial Showcase & Slider Plugin for WordPress
            </h2>
            <ul>
                <li>30 Amazing Layouts with Grid, Slider, Isotope & Video.</li>
                <li>Front End Submission</li>
                <li>Layout Preview in Shortcode Settings.</li>
                <li>Taxonomy Ordering</li>
                <li>Filter by Star Rating</li>
                <li>And Many More...</li>
            </ul>
            <?php
            $current = time();
            if(mktime( 0, 0, 0, 11, 15, 2025 ) <= $current && $current <= mktime( 0, 0, 0, 1, 5, 2026 )) {
                ?>
                <div class="offer">
                    <a href="<?php echo esc_url( TSSPro()->pro_version_link() ); ?>" target="_blank">
                        <img style="width:100%" src="<?php echo esc_url( TSSPro()->assetsUrl . 'images/offerx.png'); ?>" alt="Testimonial Slider Plugin">
                    </a>
                </div>
            <?php } ?>
            <a class="rt-admin-btn" href="<?php echo esc_url( TSSPro()->pro_version_link() ); ?>" target="_blank">
                Get The Deal!
            </a>
        </div>
    </div>
</div>