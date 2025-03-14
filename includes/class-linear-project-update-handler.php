<?php
/**
 * Project update handler for Linear
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
 * Project update handler class
 */
class Project_Update_Handler {

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
     * Validate project update data
     *
     * @param array $update_data Project update data
     * @return bool
     */
    public function validate_project_update_data($update_data) {
        // Project updates must have at least an ID and one changed field
        if (!isset($update_data['project']['id']) || empty($update_data['project']['id'])) {
            return false;
        }
        
        return true;
    }

    /**
     * Handle project update
     *
     * @param array $project_data Project update data from webhook
     * @return WP_REST_Response
     */
    public function handle_project_update($project_data) {
        try {
            // Validate project data
            if (!$this->validate_project_update_data($project_data)) {
                return new WP_REST_Response([
                    'success' => false,
                    'message' => 'Invalid project update data'
                ], 400);
            }
            
            // Find existing post by project ID
            $posts = get_posts([
                'post_type' => 'post',
                'post_status' => ['publish', 'draft'],
                'tax_query' => [
                    [
                        'taxonomy' => 'linear_project',
                        'field' => 'slug',
                        'terms' => sanitize_title($project_data['project']['id'])
                    ]
                ],
                'posts_per_page' => 1
            ]);
            
            if (empty($posts)) {
                return new WP_REST_Response([
                    'success' => false,
                    'message' => 'Project not found'
                ], 404);
            }
            
            $post = $posts[0];
            
            // Check if post is a draft and publish it if needed
            if ($post->post_status === 'draft') {
                wp_publish_post($post->ID);
            }
            
            // Format the update as a comment
            $comment_content = $this->format_update_as_comment($project_data);
            
            // Get the user who posted the update
            $comment_author = 'Linear';
            if (isset($project_data['user']) && isset($project_data['user']['name'])) {
                $comment_author = sanitize_text_field($project_data['user']['name']);
            } elseif (isset($project_data['updatedBy']) && isset($project_data['updatedBy']['name'])) {
                $comment_author = sanitize_text_field($project_data['updatedBy']['name']);
            }
            
            // Add comment to post
            $comment_id = wp_insert_comment([
                'comment_post_ID' => $post->ID,
                'comment_content' => $comment_content,
                'comment_type' => 'linear_update',
                'comment_author' => $comment_author,
                'comment_approved' => 1
            ]);
            
            if (!$comment_id) {
                throw new Exception('Failed to create comment');
            }
            
            return new WP_REST_Response([
                'success' => true,
                'message' => 'Project update added as comment',
                'comment_id' => $comment_id
            ], 201);
            
        } catch (Exception $e) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Error processing project update: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Format update as comment
     *
     * @param array $update_data Update data
     * @return string
     */
    private function format_update_as_comment($update_data) {
        $comment = '<h2>Project Update - ' . date_i18n(get_option('date_format')) . '</h2>';
        
        // Add body/content of the update if present
        if (isset($update_data['body'])) {
            $comment .= '<div class="update-body">' . wp_kses_post($update_data['body']) . '</div>';
        }
        
        // Add health status if present
        if (isset($update_data['health'])) {
            $new_health = $update_data['health'];
            $comment .= '<div class="linear-update-health">';
            $comment .= '<p>Health: ' . Config::format_health_text($new_health) . '</p>';
            $comment .= '</div>';
        }
        
        // Add state update if available
        if (isset($update_data['state'])) {
            $state = $update_data['state'];
            $state_name = isset($state['name']) ? sanitize_text_field($state['name']) : '';
            
            if (!empty($state_name)) {
                $comment .= '<div class="update-state">';
                $comment .= '<p>Status changed to: ' . esc_html($state_name) . '</p>';
                $comment .= '</div>';
            }
        }
        
        return $comment;
    }
}
