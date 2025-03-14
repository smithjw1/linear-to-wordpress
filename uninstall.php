<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Linear_WP
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete all plugin options
$options = array(
    'linear_wp_webhook_secret',
    'linear_wp_post_template',
    'linear_wp_webhook_log'
);

// Handle multisite installations
if (is_multisite()) {
    global $wpdb;
    
    // Get all blog IDs
    $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
    
    // Loop through each blog
    foreach ($blog_ids as $blog_id) {
        switch_to_blog($blog_id);
        
        // Delete options for this blog
        foreach ($options as $option) {
            delete_option($option);
        }
        
        // Delete transients
        delete_transient('linear_wp_webhook_cache');
        
        restore_current_blog();
    }
} else {
    // Single site installation
    foreach ($options as $option) {
        delete_option($option);
    }
    
    // Delete transients
    delete_transient('linear_wp_webhook_cache');
}
