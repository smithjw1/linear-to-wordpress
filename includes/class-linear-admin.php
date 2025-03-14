<?php
/**
 * Admin functionality
 *
 * @package Linear_WP
 */

namespace LinearWP;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Admin class
 */
class Admin {
    /**
     * Plugin name
     *
     * @var string
     */
    private $plugin_name;

    /**
     * Plugin version
     *
     * @var string
     */
    private $version;

    /**
     * Constructor
     */
    public function __construct() {
        $this->plugin_name = 'linear-wp';
        
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
        
        // Add menu page
        add_action('admin_menu', array($this, 'add_settings_page'));
        
        // Register and enqueue admin assets
        add_action('admin_enqueue_scripts', array($this, 'register_admin_assets'));
    }

    /**
     * Add settings page
     */
    public function add_settings_page() {
        add_options_page(
            'Linear WordPress Integration',
            'Linear Integration',
            'manage_options',
            'linear-wp-settings',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Register all settings
     */
    public function register_settings() {
        register_setting(
            'linear_wp_settings',
            'linear_wp_webhook_secret',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );

        register_setting(
            'linear_wp_settings',
            'linear_wp_post_template',
            array(
                'sanitize_callback' => 'wp_kses_post',
                'default' => Config::get_default_post_template()
            )
        );
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        // Get current options
        $webhook_secret = get_option('linear_wp_webhook_secret', '');
        $post_template = get_option('linear_wp_post_template', Config::get_default_post_template());
        
        // Include the template
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/partials/linear-admin-display.php';
    }

    /**
     * Register and enqueue admin assets (scripts and styles)
     */
    public function register_admin_assets($hook) {
        // Only load on our settings page
        if ($hook != 'settings_page_linear-wp-settings') {
            return;
        }
        
        // Register and enqueue the admin CSS
        wp_register_style(
            'linear-wp-admin-css',
            plugin_dir_url(dirname(__FILE__)) . 'admin/css/linear-wp-admin.css',
            array(),
            LINEAR_WP_VERSION
        );
        wp_enqueue_style('linear-wp-admin-css');
        
        // Register and enqueue the admin JavaScript
        wp_register_script(
            'linear-wp-admin-js',
            plugin_dir_url(dirname(__FILE__)) . 'admin/js/linear-wp-admin.js',
            array('jquery'),
            LINEAR_WP_VERSION,
            true
        );
        
        // Localize the script with the default template
        wp_localize_script(
            'linear-wp-admin-js',
            'linearWpAdmin',
            array(
                'defaultTemplate' => Config::get_default_post_template()
            )
        );
        
        wp_enqueue_script('linear-wp-admin-js');
    }
}
