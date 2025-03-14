<?php
/**
 * Admin settings page template
 *
 * @package Linear_P2
 */

// Prevent direct access
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="linear-p2-admin-wrapper">
        <div class="linear-p2-admin-main">
            <form method="post" action="options.php">
                <?php settings_fields('linear_p2_settings'); ?>
                <?php do_settings_sections('linear_p2_settings'); ?>
                
                <div class="linear-p2-settings-card">
                    <h2><?php esc_html_e('Webhook Settings', 'linear-p2'); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e('Webhook Secret', 'linear-p2'); ?></th>
                            <td>
                                <input type="password" name="linear_p2_webhook_secret" value="<?php echo esc_attr($webhook_secret); ?>" class="regular-text" />
                                <p class="description"><?php esc_html_e('Enter your Linear webhook secret for signature validation.', 'linear-p2'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Bypass Signature Validation', 'linear-p2'); ?></th>
                            <td>
                                <input type="checkbox" name="linear_p2_bypass_validation" value="1" <?php checked(true, $bypass_validation); ?> />
                                <span class="description"><?php esc_html_e('Enable this for testing only. Never use in production.', 'linear-p2'); ?></span>
                            </td>
                        </tr>
                    </table>
                    <p><?php esc_html_e('Configure your webhook in Linear with the following URL:', 'linear-p2'); ?></p>
                    <code class="linear-p2-webhook-url"><?php echo esc_url($webhook_url); ?></code>
                    <p><?php esc_html_e('Make sure to select the "Projects" resource and both the "Create" and "Update" actions.', 'linear-p2'); ?></p>
                </div>
                
                <div class="linear-p2-settings-card">
                    <h2><?php esc_html_e('Post Template', 'linear-p2'); ?></h2>
                    <p><?php esc_html_e('Define how project data should be formatted when creating posts. Use the following placeholders:', 'linear-p2'); ?></p>
                    
                    <div class="linear-p2-placeholders-container" style="display: flex; flex-wrap: wrap;">
                        <div class="linear-p2-placeholders-column" style="flex: 1; min-width: 250px;">
                            <ul class="linear-p2-placeholders">
                                <li><code>{id}</code> - <?php esc_html_e('Project ID', 'linear-p2'); ?></li>
                                <li><code>{name}</code> - <?php esc_html_e('Project name', 'linear-p2'); ?></li>
                                <li><code>{description}</code> - <?php esc_html_e('Project description', 'linear-p2'); ?></li>
                                <li><code>{url}</code> - <?php esc_html_e('Project URL in Linear', 'linear-p2'); ?></li>
                                <li><code>{created_at}</code> - <?php esc_html_e('Project creation date', 'linear-p2'); ?></li>
                                <li><code>{updated_at}</code> - <?php esc_html_e('Project last update date', 'linear-p2'); ?></li>
                                <li><code>{start_date}</code> - <?php esc_html_e('Project start date', 'linear-p2'); ?></li>
                                <li><code>{target_date}</code> - <?php esc_html_e('Project target date', 'linear-p2'); ?></li>
                            </ul>
                        </div>
                        <div class="linear-p2-placeholders-column" style="flex: 1; min-width: 250px;">
                            <ul class="linear-p2-placeholders">
                                <li><code>{health}</code> - <?php esc_html_e('Project health status', 'linear-p2'); ?></li>
                                <li><code>{status_name}</code> - <?php esc_html_e('Project status name', 'linear-p2'); ?></li>
                                <li><code>{lead_name}</code> - <?php esc_html_e('Project lead name', 'linear-p2'); ?></li>
                                <li><code>{lead_email}</code> - <?php esc_html_e('Project lead email', 'linear-p2'); ?></li>
                                <li><code>{initiative_linked}</code> - <?php esc_html_e('Initiative name with link to Linear URL', 'linear-p2'); ?></li>
                                <li><code>{initiative_name}</code> - <?php esc_html_e('Initiative name (without link)', 'linear-p2'); ?></li>
                                <li><code>{initiative_url}</code> - <?php esc_html_e('Initiative URL in Linear', 'linear-p2'); ?></li>
                            </ul>
                        </div>
                    </div>
                    
                    <textarea name="linear_p2_post_template" rows="10" class="large-text code"><?php echo esc_textarea($post_template); ?></textarea>
                    <p>
                        <a href="<?php echo esc_url(add_query_arg('linear_p2_reset_template', 'true')); ?>" class="button button-secondary">
                            <?php esc_html_e('Reset to Default Template', 'linear-p2'); ?>
                        </a>
                        <span class="description"><?php esc_html_e('This will replace your current template with the default one.', 'linear-p2'); ?></span>
                    </p>
                </div>
                
                <?php submit_button(); ?>
            </form>
        </div>
    </div>
</div>
