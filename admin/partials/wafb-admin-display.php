<?php
/**
 * Provide a admin area view for the plugin
 *
 * @package WhatsApp_Form_Builder
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
?>

<div class="wrap wafb-admin-wrapper">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <div class="wafb-container">
        <div class="wafb-sidebar">
            <h2><?php esc_html_e( 'Form Management', 'whatsapp-form-builder' ); ?></h2>
            
            <div class="wafb-form-controls">
                <input type="text" id="wafb-form-name" placeholder="<?php esc_attr_e( 'Form Name', 'whatsapp-form-builder' ); ?>" class="widefat" />
                <input type="hidden" id="wafb-form-id" value="0" />
                
                <button type="button" id="wafb-save-form" class="button button-primary">
                    <?php esc_html_e( 'Save Form', 'whatsapp-form-builder' ); ?>
                </button>
                <button type="button" id="wafb-new-form" class="button">
                    <?php esc_html_e( 'New Form', 'whatsapp-form-builder' ); ?>
                </button>
            </div>

            <div class="wafb-saved-forms">
                <h3><?php esc_html_e( 'Saved Forms', 'whatsapp-form-builder' ); ?></h3>
                <ul id="wafb-forms-list">
                    <?php
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'wafb_forms';
                    $forms      = $wpdb->get_results( "SELECT id, form_name FROM $table_name ORDER BY created_at DESC" );

                    if ( $forms ) {
                        foreach ( $forms as $form ) {
                            ?>
                            <li data-form-id="<?php echo esc_attr( $form->id ); ?>">
                                <span class="form-name"><?php echo esc_html( $form->form_name ); ?></span>
                                <div class="form-actions">
                                    <button type="button" class="button button-small wafb-load-form" data-form-id="<?php echo esc_attr( $form->id ); ?>">
                                        <?php esc_html_e( 'Load', 'whatsapp-form-builder' ); ?>
                                    </button>
                                    <button type="button" class="button button-small wafb-delete-form" data-form-id="<?php echo esc_attr( $form->id ); ?>">
                                        <?php esc_html_e( 'Delete', 'whatsapp-form-builder' ); ?>
                                    </button>
                                </div>
                            </li>
                            <?php
                        }
                    } else {
                        ?>
                        <li class="no-forms"><?php esc_html_e( 'No forms created yet.', 'whatsapp-form-builder' ); ?></li>
                        <?php
                    }
                    ?>
                </ul>
            </div>
        </div>

        <div class="wafb-main-content">
            <div class="wafb-builder-section">
                <h2><?php esc_html_e( 'Form Builder', 'whatsapp-form-builder' ); ?></h2>
                
                <div class="wafb-builder-wrapper">
                    <div class="wafb-fields-palette">
                        <h3><?php esc_html_e( 'Available Fields', 'whatsapp-form-builder' ); ?></h3>
                        <div class="wafb-field-list">
                            <div class="wafb-field-item" data-field-type="text">
                                <span class="dashicons dashicons-edit"></span>
                                <span class="field-label"><?php esc_html_e( 'Text Field', 'whatsapp-form-builder' ); ?></span>
                            </div>
                            <div class="wafb-field-item" data-field-type="email">
                                <span class="dashicons dashicons-email"></span>
                                <span class="field-label"><?php esc_html_e( 'Email Field', 'whatsapp-form-builder' ); ?></span>
                            </div>
                            <div class="wafb-field-item" data-field-type="phone">
                                <span class="dashicons dashicons-phone"></span>
                                <span class="field-label"><?php esc_html_e( 'Phone Field', 'whatsapp-form-builder' ); ?></span>
                            </div>
                            <div class="wafb-field-item" data-field-type="message">
                                <span class="dashicons dashicons-format-aside"></span>
                                <span class="field-label"><?php esc_html_e( 'Message Field', 'whatsapp-form-builder' ); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="wafb-drop-zone">
                        <div class="wafb-drop-placeholder">
                            <?php esc_html_e( 'Drag and drop fields here to build your form', 'whatsapp-form-builder' ); ?>
                        </div>
                        <div id="wafb-form-fields" class="wafb-form-fields"></div>
                    </div>
                </div>
            </div>

            <div class="wafb-preview-section">
                <h3><?php esc_html_e( 'Form Preview', 'whatsapp-form-builder' ); ?></h3>
                <div id="wafb-form-preview" class="wafb-form-preview">
                    <p class="preview-empty"><?php esc_html_e( 'Add fields to see preview', 'whatsapp-form-builder' ); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Field Settings Modal -->
<div id="wafb-field-modal" class="wafb-modal" style="display: none;">
    <div class="wafb-modal-content">
        <span class="wafb-modal-close">&times;</span>
        <h3><?php esc_html_e( 'Field Settings', 'whatsapp-form-builder' ); ?></h3>
        <div class="wafb-modal-body">
            <div class="form-field">
                <label for="wafb-field-label"><?php esc_html_e( 'Field Label:', 'whatsapp-form-builder' ); ?></label>
                <input type="text" id="wafb-field-label" class="widefat" />
            </div>
            <div class="form-field">
                <label for="wafb-field-placeholder"><?php esc_html_e( 'Placeholder:', 'whatsapp-form-builder' ); ?></label>
                <input type="text" id="wafb-field-placeholder" class="widefat" />
            </div>
            <div class="form-field">
                <label>
                    <input type="checkbox" id="wafb-field-required" />
                    <?php esc_html_e( 'Required Field', 'whatsapp-form-builder' ); ?>
                </label>
            </div>
        </div>
        <div class="wafb-modal-footer">
            <button type="button" class="button button-primary" id="wafb-save-field-settings">
                <?php esc_html_e( 'Save Settings', 'whatsapp-form-builder' ); ?>
            </button>
            <button type="button" class="button" id="wafb-cancel-field-settings">
                <?php esc_html_e( 'Cancel', 'whatsapp-form-builder' ); ?>
            </button>
        </div>
    </div>
</div>
