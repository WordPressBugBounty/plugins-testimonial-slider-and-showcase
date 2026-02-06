<?php
/**
 * Settings Page.
 *
 * @package RT_TSS
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}
?>

<div class="tss-wrap">
    <div class="tss-testimonial-wrapper">
        <?php TSSPro()->render_view( 'settings-header' ); ?>
        <div class="tss-setting-container">
            <form id="tss-settings">
                <div id="settings-tabs" class="rt-tabs rt-tab-container">
                    <div class="rt-settings-sidebar">
                        <ul class="tab-nav rt-tab-nav">
                            <?php
                            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                            $sTab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';
                            ?>
                            <li class="<?php echo ( 'support' !== $sTab ) ? 'active' : ''; ?>">
                                <a href="#general"><i class="dashicons dashicons-admin-settings"></i><?php esc_html_e( 'General', 'testimonial-slider-showcase' ); ?></a>
                            </li>
                            <?php do_action( 'rtts_setting_tabs_one' ); ?>
                            <?php do_action( 'rtts_setting_tabs_two' ); ?>

                        </ul>
                    </div>
                    <div class="rt-settings-content">
                        <div id="general" class="rt-tab-content" style="<?php echo ( 'support' !== $sTab ) ? 'display: block;' : ''; ?>">
                            <?php TSSPro()->printHtml( TSSPro()->rtFieldGenerator( TSSPro()->generalSettings() ), true ); ?>
                        </div>

                        <?php do_action( 'rtts_setting_tabs_content' ); ?>
                        <p class="submit">
                            <input type="submit" name="submit" id="tss-saveButton" class="rt-admin-btn button button-primary" value="<?php esc_attr_e( 'Save Changes', 'testimonial-slider-showcase' ); ?>">
                        </p>
                        <div class="rt-response"></div>
                    </div>

                    <?php
                    TSSPro()->rt_plugin_sc_pro_information();
                    ?>

                </div>
                <?php wp_nonce_field( TSSPro()->nonceText(), TSSPro()->nonceId() ); ?>
            </form>
        </div>
    </div>
</div>
