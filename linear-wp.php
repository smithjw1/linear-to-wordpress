<?php
/**
 * Plugin Name: Linear WordPress Integration
 * Plugin URI: https://wordpress.org/plugins/linear-wp/
 * Description: Integrates Linear projects with WordPress by creating posts from webhook data
 * Version: 1.0.0
 * Author: Jacob Smith
 * Author URI: https://automattic.com
 * Text Domain: linear-wp
 * Domain Path: /languages
 * License: GPL v2 or later
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Plugin version
define('LINEAR_WP_VERSION', '1.0.0');

// Plugin path
define('LINEAR_WP_PATH', plugin_dir_path(__FILE__));

// Plugin URL
define('LINEAR_WP_URL', plugin_dir_url(__FILE__));

// Load Composer dependencies if available
if (file_exists(LINEAR_WP_PATH . 'vendor/autoload.php')) {
    require_once LINEAR_WP_PATH . 'vendor/autoload.php';
}

// Include the main plugin class files
require_once LINEAR_WP_PATH . 'includes/class-linear-config.php';
require_once LINEAR_WP_PATH . 'includes/class-linear-loader.php';
require_once LINEAR_WP_PATH . 'includes/class-linear-admin.php';
require_once LINEAR_WP_PATH . 'includes/class-linear-webhook-handler.php';
require_once LINEAR_WP_PATH . 'includes/class-linear.php';

/**
 * Begin execution of the plugin
 *
 * @since 1.0.0
 */
function run_linear_wp() {
    // Initialize error handling
    set_error_handler('linear_wp_error_handler');
    
    try {
        $plugin = new LinearWP\Linear();
        $plugin->run();
    } catch (Exception $e) {
        // Log the exception
        error_log('Linear WordPress Plugin Error: ' . $e->getMessage());
        
        // Show admin notice if we're in the admin area
        if (is_admin()) {
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error"><p>';
                echo 'Linear WordPress Plugin Error: ' . esc_html($e->getMessage());
                echo '</p></div>';
            });
        }
    }
}

/**
 * Custom error handler for the plugin
 *
 * @param int $errno Error number
 * @param string $errstr Error message
 * @param string $errfile File where the error occurred
 * @param int $errline Line where the error occurred
 * @return bool
 */
function linear_wp_error_handler($errno, $errstr, $errfile, $errline) {
    // Only handle errors from this plugin
    if (strpos($errfile, 'linear-wp') === false) {
        return false;
    }
    
    $error_message = "Linear WordPress Error: [$errno] $errstr - $errfile:$errline";
    error_log($error_message);
    
    // Don't execute PHP's internal error handler
    return true;
}

// Start the plugin
run_linear_wp();
