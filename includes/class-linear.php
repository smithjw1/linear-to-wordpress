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
     * The loader that's responsible for maintaining and registering all hooks.
     *
     * @var Loader
     */
    protected $loader;

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
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_webhook_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies() {
        $this->loader = new Loader();
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     */
    private function define_admin_hooks() {
        $plugin_admin = new Admin();

        $this->loader->add_action('admin_menu', $plugin_admin, 'add_settings_page');
        $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
    }

    /**
     * Register all of the hooks related to the webhook functionality
     * of the plugin.
     */
    private function define_webhook_hooks() {
        $webhook_handler = new Webhook_Handler($this->get_plugin_name(), $this->get_version());
        
        $this->loader->add_action('rest_api_init', $webhook_handler, 'register_webhook_endpoint');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     */
    public function run() {
        $this->loader->run();
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
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @return Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
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
