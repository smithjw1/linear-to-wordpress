<?php
/**
 * Project update handler for Linear webhook data
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
     * @param array $project_data
     * @return WP_REST_Response
     */
    public function handle_project_update($project_data) {
        try {
            // Validate project update data
            if (!$this->validate_project_update_data($project_data)) {
                return new WP_REST_Response([
                    'success' => false,
                    'message' => 'Invalid project update data'
                ], 400);
            }
            
            // Find existing post by project ID
            $posts = get_posts([
                'post_type' => 'post',
                'meta_key' => 'linear_project_id',
                'meta_value' => sanitize_text_field($project_data['project']['id']),
                'posts_per_page' => 1
            ]);
            
            if (empty($posts)) {
                return new WP_REST_Response([
                    'success' => false,
                    'message' => 'No post found for this project'
                ], 404);
            }
            
            $post = $posts[0];
            
            // Format the update as a comment
            $comment_content = $this->format_update_as_comment($project_data);
            
            // Add comment to post
            $comment_id = wp_insert_comment([
                'comment_post_ID' => $post->ID,
                'comment_content' => $comment_content,
                'comment_type' => 'linear_update',
                'comment_approved' => 1,
                'comment_author' => 'Linear',
                'comment_meta' => [
                    'linear_update_id' => sanitize_text_field($project_data['id'])
                ]
            ]);
            
            if (is_wp_error($comment_id)) {
                return new WP_REST_Response([
                    'success' => false,
                    'message' => 'Error adding comment: ' . $comment_id->get_error_message()
                ], 500);
            }
            
            return new WP_REST_Response([
                'success' => true,
                'message' => 'Update added as comment',
                'comment_id' => $comment_id
            ], 201);
            
        } catch (Exception $e) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Error processing update: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format update as comment
     *
     * @param array $update_data
     * @return string
     */
    private function format_update_as_comment($update_data) {
        $comment = '<h2>Project Update - ' . date('F j, Y') . '</h2>';
        
        // Add body/content of the update if present
        if (isset($update_data['body'])) {
            $comment .= '<div class="linear-update-body">' . wp_kses_post($update_data['body']) . '</div>';
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
                $comment .= '<div class="linear-update-state">';
                $comment .= '<p>Status changed to: ' . esc_html($state_name) . '</p>';
                $comment .= '</div>';
            }
        }
        
        return $comment;
    }
}
