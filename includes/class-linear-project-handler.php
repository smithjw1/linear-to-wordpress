<?php
/**
 * Project handler for Linear webhook data
 *
 * @package Linear_WP
 */

namespace LinearWP;

use WP_REST_Response;
use Exception;

// Prevent direct access
if (!defined('WPINC')) {
    die;
}

/**
 * Project handler class
 */
class Project_Handler {

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
     * Taxonomy handler instance
     *
     * @var Taxonomy
     */
    private $taxonomy;

    /**
     * Initialize the class
     *
     * @param string $plugin_name The plugin name
     * @param string $version The plugin version
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->taxonomy = new Taxonomy($plugin_name, $version);
    }

    /**
     * Validate project data
     *
     * @param array $project_data Project data
     * @return bool
     */
    public function validate_project_data($project_data) {
        // Required fields for a valid project
        $required_fields = ['id', 'name', 'url'];
        
        foreach ($required_fields as $field) {
            if (!isset($project_data[$field]) || empty($project_data[$field])) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Handle project creation
     *
     * @param array $project_data Project data from webhook
     * @return WP_REST_Response
     */
    public function handle_project_creation($project_data) {
        // Validate project data
        if (!$this->validate_project_data($project_data)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Invalid project data'
            ], 400);
        }
        
        try {
            // Check if project already exists
            $posts = get_posts([
                'post_type' => 'post',
                'post_status' => ['publish', 'draft'],
                'tax_query' => [
                    [
                        'taxonomy' => 'linear_project',
                        'field' => 'slug',
                        'terms' => sanitize_title($project_data['id'])
                    ]
                ],
                'posts_per_page' => 1
            ]);

            if (!empty($posts)) {
                return new WP_REST_Response([
                    'success' => false,
                    'message' => 'Project already exists'
                ], 400);
            }

            // Format project content
            $content = $this->format_project_content($project_data);

            // Create post
            $post_id = wp_insert_post([
                'post_title'   => sanitize_text_field($project_data['name']),
                'post_content' => $content,
                'post_status'  => 'draft',
                'post_type'    => 'post',
            ]);

            if (is_wp_error($post_id)) {
                throw new Exception('Failed to create post: ' . $post_id->get_error_message());
            }

            // Add project taxonomy term
            $term_id = $this->taxonomy->get_or_create_project_term(
                $project_data['id'],
                $project_data['name'],
                $project_data['url']
            );

            if (is_wp_error($term_id)) {
                throw new Exception('Failed to create project term: ' . $term_id->get_error_message());
            }

            // Assign term to post
            wp_set_post_terms($post_id, [$term_id], 'linear_project');

            return new WP_REST_Response([
                'success' => true,
                'message' => 'Project created successfully',
                'post_id' => $post_id
            ], 201);

        } catch (Exception $e) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Error creating project: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format project content
     *
     * @param array $project_data Project data
     * @return string Formatted content
     */
    private function format_project_content($project_data) {
        // Get the template directly from WordPress options
        $template = get_option('linear_wp_post_template', Config::get_default_post_template());
        
        // Process initiative data
        $initiative_linked = 'None';
        $initiative_name = '';
        $initiative_url = '';
        
        if (isset($project_data['initiative']) && is_array($project_data['initiative'])) {
            $initiative = $project_data['initiative'];
            $initiative_name = isset($initiative['name']) ? sanitize_text_field($initiative['name']) : '';
            $initiative_url = isset($initiative['url']) ? esc_url_raw($initiative['url']) : '';
            
            if (!empty($initiative_name) && !empty($initiative_url)) {
                $initiative_linked = '<a href="' . esc_url($initiative_url) . '" target="_blank">' . esc_html($initiative_name) . '</a>';
            } elseif (!empty($initiative_name)) {
                $initiative_linked = esc_html($initiative_name);
            }
        }
        
        // Process lead data
        $lead_name = 'Unassigned';
        $lead_email = '';
        
        if (isset($project_data['lead']) && is_array($project_data['lead'])) {
            $lead = $project_data['lead'];
            $lead_name = isset($lead['name']) ? sanitize_text_field($lead['name']) : 'Unassigned';
            $lead_email = isset($lead['email']) ? sanitize_email($lead['email']) : '';
        }
        
        // Process status data
        $status_name = 'Not Started';
        
        if (isset($project_data['state']) && is_array($project_data['state'])) {
            $state = $project_data['state'];
            $status_name = isset($state['name']) ? sanitize_text_field($state['name']) : 'Not Started';
        }
        
        // Process health data
        $health = Config::format_health_text('onTrack');
        
        if (isset($project_data['health'])) {
            $health = Config::format_health_text($project_data['health']);
        }
        
        // Process dates
        $start_date = isset($project_data['startDate']) ? date('F j, Y', strtotime($project_data['startDate'])) : 'Not set';
        $target_date = isset($project_data['targetDate']) ? date('F j, Y', strtotime($project_data['targetDate'])) : 'Not set';
        
        // Replace placeholders in the template
        $replacements = [
            '{id}' => isset($project_data['id']) ? esc_html($project_data['id']) : '',
            '{name}' => isset($project_data['name']) ? esc_html($project_data['name']) : '',
            '{description}' => isset($project_data['description']) ? wp_kses_post($project_data['description']) : '',
            '{url}' => isset($project_data['url']) ? esc_url($project_data['url']) : '',
            '{created_at}' => isset($project_data['createdAt']) ? date('F j, Y', strtotime($project_data['createdAt'])) : '',
            '{updated_at}' => isset($project_data['updatedAt']) ? date('F j, Y', strtotime($project_data['updatedAt'])) : '',
            '{start_date}' => $start_date,
            '{target_date}' => $target_date,
            '{health}' => $health,
            '{status_name}' => $status_name,
            '{lead_name}' => $lead_name,
            '{lead_email}' => $lead_email,
            '{initiative_linked}' => $initiative_linked,
            '{initiative_name}' => $initiative_name,
            '{initiative_url}' => $initiative_url,
        ];
        
        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}
