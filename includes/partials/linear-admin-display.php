<?php
/**
 * Admin settings page display with modern WordPress components
 *
 * @package Linear_WP
 */

// Prevent direct access
if (!defined('WPINC')) {
    die;
}

// Ensure WordPress styles are loaded
wp_enqueue_style('wp-components');
wp_enqueue_script('wp-element');
wp_enqueue_script('wp-components');
?>

<div class="wrap linear-wp-admin">
    <h1 class="wp-heading-inline"><?php esc_html_e('Linear WordPress Integration', 'linear-wp'); ?></h1>
    
    <?php settings_errors('linear_wp_messages'); ?>
    
    <div class="components-card components-panel components-panel__body is-opened">
        <div class="components-card__header">
            <h2 class="components-card__title"><?php esc_html_e('Webhook URL', 'linear-wp'); ?></h2>
        </div>
        <div class="components-card__body">
            <div class="linear-wp-webhook-url">
                <div class="components-base-control">
                    <div class="components-base-control__field">
                        <input 
                            type="text" 
                            id="linear-wp-webhook-url" 
                            class="components-text-control__input"
                            value="<?php echo esc_url(rest_url('linear-wp/v1/webhook')); ?>" 
                            readonly 
                        />
                    </div>
                    <button type="button" class="components-button is-secondary" onclick="navigator.clipboard.writeText('<?php echo esc_url(rest_url('linear-wp/v1/webhook')); ?>');">
                        <?php esc_html_e('Copy to Clipboard', 'linear-wp'); ?>
                    </button>
                    <p class="components-base-control__help"><?php esc_html_e('Use this URL in Linear webhook settings.', 'linear-wp'); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="components-card mt-5">
        <div class="components-card__header">
            <h2 class="components-card__title"><?php esc_html_e('Settings', 'linear-wp'); ?></h2>
        </div>
        <div class="components-card__body">
            <form method="post" action="options.php">
                <?php settings_fields('linear_wp_settings'); ?>
                
                <div class="components-panel mb-5">
                    <div class="components-panel__body is-opened">
                        <div class="components-base-control">
                            <label for="linear_wp_webhook_secret" class="components-base-control__label">
                                <?php esc_html_e('Webhook Secret', 'linear-wp'); ?>
                            </label>
                            <div class="components-base-control__field">
                                <input 
                                    type="password" 
                                    name="linear_wp_webhook_secret" 
                                    id="linear_wp_webhook_secret" 
                                    class="components-text-control__input" 
                                    value="<?php echo esc_attr($webhook_secret); ?>" 
                                />
                            </div>
                            <p class="components-base-control__help"><?php esc_html_e('Enter the webhook secret from Linear. This is used to verify webhook requests.', 'linear-wp'); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="components-panel mb-5">
                    <div class="components-panel__body is-opened">
                        <div class="components-base-control">
                            <label for="linear_wp_post_template" class="components-base-control__label">
                                <?php esc_html_e('Post Template', 'linear-wp'); ?>
                            </label>
                            
                            <div class="linear-wp-placeholders mb-3">
                                <strong><?php esc_html_e('Available placeholders:', 'linear-wp'); ?></strong>
                                <div class="components-grid components-panel-body">
                                    <div class="components-grid-item" style="grid-column: 1;">
                                        <div class="components-notice is-info">
                                            <div class="components-notice__content">
                                                <ul>
                                                    <li>{id}</li>
                                                    <li>{name}</li>
                                                    <li>{description}</li>
                                                    <li>{url}</li>
                                                    <li>{created_at}</li>
                                                    <li>{updated_at}</li>
                                                    <li>{start_date}</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="components-grid-item" style="grid-column: 2;">
                                        <div class="components-notice is-info">
                                            <div class="components-notice__content">
                                                <ul>
                                                    <li>{target_date}</li>
                                                    <li>{health}</li>
                                                    <li>{status_name}</li>
                                                    <li>{lead_name}</li>
                                                    <li>{lead_email}</li>
                                                    <li>{initiative_linked}</li>
                                                    <li>{initiative_name}</li>
                                                    <li>{initiative_url}</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="components-base-control__field">
                                <textarea 
                                    name="linear_wp_post_template" 
                                    id="linear_wp_post_template" 
                                    class="components-textarea-control__input"
                                    rows="10"
                                ><?php echo esc_textarea($post_template); ?></textarea>
                            </div>
                            
                            <div class="template-reset-container components-base-control__field">
                                <button type="button" id="reset-template-button" class="components-button is-secondary">
                                    <?php esc_html_e('Reset to Default Template', 'linear-wp'); ?>
                                </button>
                            </div>
                            
                            <p class="components-base-control__help"><?php esc_html_e('Template for posts created from Linear projects.', 'linear-wp'); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="components-base-control__field">
                    <?php submit_button(esc_html__('Save Settings', 'linear-wp'), 'primary', 'submit', false); ?>
                </div>
            </form>
        </div>
    </div>
</div>
