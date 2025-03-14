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
    <h1 class="wp-heading-inline">Linear WordPress Integration</h1>
    
    <?php settings_errors('linear_wp_messages'); ?>
    
    <div class="card">
        <h2><?php esc_html_e('Webhook Configuration', 'linear-wp'); ?></h2>
        
        <div class="linear-wp-webhook-url" style="margin-bottom: 20px;">
            <label for="linear-wp-webhook-url" style="display: block; margin-bottom: 5px; font-weight: bold;">
                <?php esc_html_e('Webhook URL', 'linear-wp'); ?>
            </label>
            <div class="components-flex" style="align-items: center;">
                <input 
                    type="text" 
                    id="linear-wp-webhook-url" 
                    class="components-text-control__input" 
                    style="width: 100%; max-width: 500px; padding: 6px 8px; border-radius: 4px; border: 1px solid #757575;"
                    value="<?php echo esc_url(rest_url('linear-wp/v1/webhook')); ?>" 
                    readonly 
                />
                <button type="button" class="button" onclick="navigator.clipboard.writeText('<?php echo esc_url(rest_url('linear-wp/v1/webhook')); ?>');">
                    <?php esc_html_e('Copy to Clipboard', 'linear-wp'); ?>
                </button>
            </div>
            <p class="description"><?php esc_html_e('Use this URL in Linear webhook settings.', 'linear-wp'); ?></p>
        </div>
    </div>
    
    <div class="card" style="margin-top: 20px;">
        <h2><?php esc_html_e('Settings', 'linear-wp'); ?></h2>
        
        <form method="post" action="options.php">
            <?php settings_fields('linear_wp_settings'); ?>
            
            <div class="components-panel" style="margin-bottom: 20px;">
                <div class="components-panel__body is-opened">
                    <label for="linear_wp_webhook_secret" style="display: block; margin-bottom: 5px; font-weight: bold;">
                        <?php esc_html_e('Webhook Secret', 'linear-wp'); ?>
                    </label>
                    <input 
                        type="password" 
                        name="linear_wp_webhook_secret" 
                        id="linear_wp_webhook_secret" 
                        class="components-text-control__input" 
                        style="width: 100%; max-width: 500px; padding: 6px 8px; border-radius: 4px; border: 1px solid #757575;"
                        value="<?php echo esc_attr($webhook_secret); ?>" 
                    />
                    <p class="description"><?php esc_html_e('Enter the webhook secret from Linear. This is used to verify webhook requests.', 'linear-wp'); ?></p>
                </div>
            </div>
            
            <div class="components-panel" style="margin-bottom: 20px;">
                <div class="components-panel__body is-opened">
                    <label for="linear_wp_post_template" style="display: block; margin-bottom: 5px; font-weight: bold;">
                        <?php esc_html_e('Post Template', 'linear-wp'); ?>
                    </label>
                    
                    <div class="linear-wp-placeholders" style="margin-bottom: 15px;">
                        <strong><?php esc_html_e('Available placeholders:', 'linear-wp'); ?></strong>
                        <div class="placeholder-columns" style="display: flex; flex-wrap: wrap; margin-top: 10px;">
                            <div class="placeholder-column" style="flex: 1; min-width: 150px; margin-right: 20px; margin-bottom: 10px;">
                                <div class="components-notice" style="background-color: #f0f0f0; padding: 10px; border-radius: 4px;">
                                    <ul style="margin: 0; padding-left: 20px; word-wrap: break-word; overflow-wrap: break-word;">
                                        <li style="margin-bottom: 5px;">{id}</li>
                                        <li style="margin-bottom: 5px;">{name}</li>
                                        <li style="margin-bottom: 5px;">{description}</li>
                                        <li style="margin-bottom: 5px;">{url}</li>
                                        <li style="margin-bottom: 5px;">{created_at}</li>
                                        <li style="margin-bottom: 5px;">{updated_at}</li>
                                        <li style="margin-bottom: 5px;">{start_date}</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="placeholder-column" style="flex: 1; min-width: 150px; margin-bottom: 10px;">
                                <div class="components-notice" style="background-color: #f0f0f0; padding: 10px; border-radius: 4px;">
                                    <ul style="margin: 0; padding-left: 20px; word-wrap: break-word; overflow-wrap: break-word;">
                                        <li style="margin-bottom: 5px;">{target_date}</li>
                                        <li style="margin-bottom: 5px;">{health}</li>
                                        <li style="margin-bottom: 5px;">{status_name}</li>
                                        <li style="margin-bottom: 5px;">{lead_name}</li>
                                        <li style="margin-bottom: 5px;">{lead_email}</li>
                                        <li style="margin-bottom: 5px;">{initiative_linked}</li>
                                        <li style="margin-bottom: 5px;">{initiative_name}</li>
                                        <li style="margin-bottom: 5px;">{initiative_url}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <textarea 
                        name="linear_wp_post_template" 
                        id="linear_wp_post_template" 
                        class="components-textarea-control__input" 
                        style="width: 100%; min-height: 200px; padding: 8px; border-radius: 4px; border: 1px solid #757575; font-family: monospace;"
                        rows="10"
                    ><?php echo esc_textarea($post_template); ?></textarea>
                    
                    <div class="template-reset-container" style="margin-top: 10px; margin-bottom: 15px;">
                        <button type="button" id="reset-template-button" class="button button-secondary">
                            <?php esc_html_e('Reset to Default Template', 'linear-wp'); ?>
                        </button>
                    </div>
                    
                    <p class="description"><?php esc_html_e('Template for posts created from Linear projects.', 'linear-wp'); ?></p>
                </div>
            </div>
            
            <?php submit_button(esc_html__('Save Settings', 'linear-wp')); ?>
        </form>
    </div>
</div>
