<?php
/**
 * Linear Taxonomy Handler
 *
 * @package Linear_WP
 */

namespace LinearWP;

// Prevent direct access
if (!defined('WPINC')) {
    die;
}

/**
 * Linear Taxonomy Handler class
 */
class Taxonomy {

    /**
     * The plugin name
     *
     * @var string
     */
    private $plugin_name;

    /**
     * The plugin version
     *
     * @var string
     */
    private $version;

    /**
     * Initialize the class
     *
     * @param string $plugin_name The plugin name
     * @param string $version The plugin version
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the Linear Project taxonomy
     */
    public function register_taxonomies() {
        $labels = array(
            'name'                       => _x('Linear Projects', 'Taxonomy General Name', 'linear-wp'),
            'singular_name'              => _x('Linear Project', 'Taxonomy Singular Name', 'linear-wp'),
            'menu_name'                  => __('Linear Projects', 'linear-wp'),
            'all_items'                  => __('All Linear Projects', 'linear-wp'),
            'parent_item'                => __('Parent Linear Project', 'linear-wp'),
            'parent_item_colon'          => __('Parent Linear Project:', 'linear-wp'),
            'new_item_name'              => __('New Linear Project Name', 'linear-wp'),
            'add_new_item'               => __('Add New Linear Project', 'linear-wp'),
            'edit_item'                  => __('Edit Linear Project', 'linear-wp'),
            'update_item'                => __('Update Linear Project', 'linear-wp'),
            'view_item'                  => __('View Linear Project', 'linear-wp'),
            'separate_items_with_commas' => __('Separate linear projects with commas', 'linear-wp'),
            'add_or_remove_items'        => __('Add or remove linear projects', 'linear-wp'),
            'choose_from_most_used'      => __('Choose from the most used', 'linear-wp'),
            'popular_items'              => __('Popular Linear Projects', 'linear-wp'),
            'search_items'               => __('Search Linear Projects', 'linear-wp'),
            'not_found'                  => __('Not Found', 'linear-wp'),
            'no_terms'                   => __('No linear projects', 'linear-wp'),
            'items_list'                 => __('Linear Projects list', 'linear-wp'),
            'items_list_navigation'      => __('Linear Projects list navigation', 'linear-wp'),
        );
        
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => false,
            'public'                     => false,
            'show_ui'                    => false,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => false,
            'show_tagcloud'              => false,
            'show_in_rest'               => true,
            'meta_box_cb'                => false, // Hide the meta box on post edit screen
            'capabilities' => [
                'manage_terms' => 'manage_options',
                'edit_terms'   => 'manage_options',
                'delete_terms' => 'manage_options',
                'assign_terms' => 'manage_options',
            ],
        );
        
        register_taxonomy('linear_project', array('post'), $args);
    }

    /**
     * Get or create a Linear Project term
     *
     * @param string $project_id The Linear project ID
     * @param string $project_name The Linear project name
     * @param string $project_url The Linear project URL
     * @return int|WP_Error Term ID on success, WP_Error on failure
     */
    public function get_or_create_project_term($project_id, $project_name, $project_url) {
        // Check if term already exists
        $term = get_term_by('slug', sanitize_title($project_id), 'linear_project');
        
        if ($term) {
            return $term->term_id;
        }
        
        // Create new term
        $result = wp_insert_term(
            sanitize_text_field($project_name),
            'linear_project',
            array(
                'slug' => sanitize_title($project_id),
                'description' => esc_url_raw($project_url),
            )
        );
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        return $result['term_id'];
    }

    /**
     * Get project URL from term
     *
     * @param int $term_id The term ID
     * @return string The project URL
     */
    public function get_project_url_from_term($term_id) {
        $term = get_term($term_id, 'linear_project');
        
        if (is_wp_error($term) || !$term) {
            return '';
        }
        
        return $term->description;
    }

    /**
     * Get project ID from term
     *
     * @param int $term_id The term ID
     * @return string The project ID
     */
    public function get_project_id_from_term($term_id) {
        $term = get_term($term_id, 'linear_project');
        
        if (is_wp_error($term) || !$term) {
            return '';
        }
        
        return $term->slug;
    }

    /**
     * Get project term for a post
     *
     * @param int $post_id The post ID
     * @return WP_Term|false The project term or false if not found
     */
    public function get_project_term_for_post($post_id) {
        $terms = wp_get_post_terms($post_id, 'linear_project');
        
        if (is_wp_error($terms) || empty($terms)) {
            return false;
        }
        
        return $terms[0];
    }
}
