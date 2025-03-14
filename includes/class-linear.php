<?php
/**
 * The main plugin class
 * 
 * @package Linear_WP
 */

namespace LinearWP;

// Prevent direct access
if (!defined('WPINC')) {
    die;
}

/**
 * The core plugin class.
 * 
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 */
class Linear {

    /**
     * The unique identifier of this plugin.
     *
     * @var string
     */
    protected $plugin_name = 'linear-wp';

    /**
     * The current version of the plugin.
     *
     * @var string
     */
    protected $version = '1.0.0';

    /**
     * Initialize the plugin and set its properties.
     */
    public function __construct() {
        $this->version = LINEAR_WP_VERSION;
        $this->register_hooks();
    }

    /**
     * Register all hooks for the plugin
     */
    private function register_hooks() {
        // Admin hooks
        $plugin_admin = new Admin();
        add_action('admin_menu', [$plugin_admin, 'add_settings_page']);
        add_action('admin_init', [$plugin_admin, 'register_settings']);
        
        // Register taxonomies
        $taxonomy_handler = new Taxonomy($this->get_plugin_name(), $this->get_version());
        add_action('init', [$taxonomy_handler, 'register_taxonomies']);
        
        // Webhook hooks
        $webhook_handler = new Webhook_Handler($this->get_plugin_name(), $this->get_version());
        add_action('rest_api_init', [$webhook_handler, 'register_webhook_endpoint']);
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @return string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @return string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}
