<?php
/**
 * Handles webhook requests from Linear
 *
 * @package Linear_WP
 */

namespace LinearWP;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use Exception;
use Parsedown;

// Prevent direct access
if (!defined('WPINC')) {
    die;
}

/**
 * Webhook handler class
 */
class Webhook_Handler {

    /**
     * The ID of this plugin.
     *
     * @var string
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @var string
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the webhook endpoint
     */
    public function register_webhook_endpoint() {
        register_rest_route('linear-wp/v1', '/webhook', array(
            'methods' => 'POST',
            'callback' => array($this, 'process_webhook'),
            'permission_callback' => array($this, 'validate_webhook_permission'),
        ));
    }

    /**
     * Validate webhook permission
     *
     * @return bool
     */
    public function validate_webhook_permission() {
        return true;
    }

    /**
     * Process webhook
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function process_webhook(WP_REST_Request $request) {
        try {
            // Get the request body
            $body = $request->get_body();
            
            // Get the webhook signature from the headers
            $signature = $request->get_header('linear_signature');
            
            // Validate the webhook signature
            try {
                // Validate webhook signature
                if (!$this->validate_webhook_signature($body, $signature)) {
                    return new WP_REST_Response(array(
                        'success' => false,
                        'message' => 'Invalid webhook signature'
                    ), 401);
                }
            } catch (Exception $e) {
                return new WP_REST_Response(array(
                    'success' => false,
                    'message' => 'Error validating webhook signature: ' . $e->getMessage()
                ), 500);
            }
            
            // Parse the JSON body
            $json_body = json_decode($body, true);
            
            // Validate the webhook data
            if (!$this->validate_webhook_data($json_body)) {
                return new WP_REST_Response(array(
                    'success' => false,
                    'message' => 'Invalid webhook data'
                ), 400);
            }
            
            if ($json_body['type'] === Config::WEBHOOK_TYPE_PROJECT && 
                $json_body['action'] === Config::WEBHOOK_ACTION_CREATE) {
                return $this->handle_project_creation($json_body['data']);
            }
            
            if ($json_body['type'] === Config::WEBHOOK_TYPE_PROJECT_UPDATE && 
                $json_body['action'] === Config::WEBHOOK_ACTION_CREATE) {
                return $this->handle_project_update($json_body['data']);
            }
            
            // If we get here, we don't know how to handle this webhook type/action
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Unsupported webhook type or action'
            ), 400);
            
        } catch (Exception $e) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Error processing webhook: ' . $e->getMessage()
            ), 500);
        }
    }
    
    /**
     * Validate webhook data
     *
     * @param array $data Webhook data
     * @return bool
     */
    private function validate_webhook_data($data) {
        // Check if required fields are present
        if (!isset($data['type']) || !isset($data['action'])) {
            return false;
        }
        
        // Check if data field is present for supported types
        if (($data['type'] === Config::WEBHOOK_TYPE_PROJECT || 
             $data['type'] === Config::WEBHOOK_TYPE_PROJECT_UPDATE) && 
            !isset($data['data'])) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate project data
     *
     * @param array $project_data Project data
     * @return bool
     */
    private function validate_project_data($project_data) {
        $required_fields = ['id', 'name'];
        
        foreach ($required_fields as $field) {
            if (!isset($project_data[$field]) || empty($project_data[$field])) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Validate project update data
     *
     * @param array $update_data Project update data
     * @return bool
     */
    private function validate_project_update_data($update_data) {
        // Project updates must have at least an ID and one changed field
        if (!isset($update_data['project']['id']) || empty($update_data['project']['id'])) {
            return false;
        }
        
        return true;
    }

    /**
     * Handle project creation
     *
     * @param array $project_data
     * @return WP_REST_Response
     */
    private function handle_project_creation($project_data) {
        // Validate project data
        if (!$this->validate_project_data($project_data)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Invalid project data'
            ), 400);
        }
        
        try {
            // Format the content
            $content = $this->format_project_content($project_data);
            
            // Create post
            $post_id = wp_insert_post(array(
                'post_title'   => sanitize_text_field($project_data['name']),
                'post_content' => $content,
                'post_status'  => 'publish',
                'post_type'    => 'post',
                'meta_input'   => array(
                    'linear_project_id' => sanitize_text_field($project_data['id']),
                    'linear_project_url' => esc_url_raw($project_data['url'])
                )
            ));
            
            if (is_wp_error($post_id)) {
                return new WP_REST_Response(array(
                    'success' => false,
                    'message' => 'Error creating post: ' . $post_id->get_error_message()
                ), 500);
            }
            
            return new WP_REST_Response(array(
                'success' => true,
                'message' => 'Post created successfully',
                'post_id' => $post_id
            ), 201);
            
        } catch (Exception $e) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Error creating post: ' . $e->getMessage()
            ), 500);
        }
    }

    /**
     * Handle project update
     *
     * @param array $project_data
     * @return WP_REST_Response
     */
    private function handle_project_update($project_data) {
        try {
            // Validate project update data
            if (!$this->validate_project_update_data($project_data)) {
                return new WP_REST_Response(array(
                    'success' => false,
                    'message' => 'Invalid project update data'
                ), 400);
            }
            
            // Find existing post by project ID
            $posts = get_posts(array(
                'post_type' => 'post',
                'meta_key' => 'linear_project_id',
                'meta_value' => sanitize_text_field($project_data['project']['id']),
                'posts_per_page' => 1
            ));
            
            if (empty($posts)) {
                // If no post exists, return an error
               return new WP_REST_Response(array(
                    'success' => false,
                    'message' => 'No post exists for this project'
                ), 400);
            }
            
            $post = $posts[0];
            
            // Format the update as a comment
            $comment = $this->format_update_as_comment($project_data);
            
            // Add the comment to the post
            $comment_id = wp_insert_comment(array(
                'comment_post_ID' => $post->ID,
                'comment_content' => $comment,
                'comment_type' => 'linear_update',
                'comment_meta' => array(
                    'linear_update_id' => sanitize_text_field($project_data['id']),
                ),
                'comment_approved' => 1
            ));
            
            if (is_wp_error($comment_id)) {
                return new WP_REST_Response(array(
                    'success' => false,
                    'message' => 'Error adding comment: ' . $comment_id->get_error_message()
                ), 500);
            }
            
            return new WP_REST_Response(array(
                'success' => true,
                'message' => 'Project update comment added',
                'comment_id' => $comment_id
            ), 201);
            
        } catch (Exception $e) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Error adding project update: ' . $e->getMessage()
            ), 500);
        }
    }
    
    /**
     * Format project content
     *
     * @param array $project_data
     * @return string
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
        $health = 'On Track';
        
        if (isset($project_data['health'])) {
            $health_text = $this->format_health_text($project_data['health']);
            $health = '<span style="display:inline-block; width:12px; height:12px; border-radius:50%; background-color:' . esc_attr(Config::get_health_status_mapping($project_data['health'])['color']) . '; margin-right:5px;"></span> ' . $health_text;
        }
        
        // Process dates
        $start_date = isset($project_data['startDate']) ? date('F j, Y', strtotime($project_data['startDate'])) : 'Not set';
        $target_date = isset($project_data['targetDate']) ? date('F j, Y', strtotime($project_data['targetDate'])) : 'Not set';
        
        // Replace placeholders in the template
        $replacements = array(
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
        );
        
        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Format health text
     *
     * @param string $health Health status from Linear
     * @return string Formatted health text with colored dot
     */
    private function format_health_text($health) {
        $health_mapping = Config::get_health_status_mapping($health);
        $color = $health_mapping['color'];
        $text = $health_mapping['text'];
        
        // Create colored dot using inline CSS
        $dot = '<span style="display:inline-block; width:12px; height:12px; border-radius:50%; background-color:' . esc_attr($color) . '; margin-right:5px;"></span>';
        
        return $dot . $text;
    }

    /**
     * Format update as comment
     *
     * @param array $update_data
     * @return string
     */
    private function format_update_as_comment($update_data) {
        $comment = '<h2>Project Update - ' . date('F j, Y') . '</h2>';
        
        // Get project data
        $project = isset($update_data['project']) ? $update_data['project'] : [];
        
        // Add health if present
        if (isset($update_data['health'])) {
            $health_text = $this->format_health_text($update_data['health']);
            $comment .= '<p>' . $health_text . '</p>';
        }
        
        // Add body/content of the update
        if (isset($update_data['body'])) {
            // Convert markdown to HTML using Parsedown
            $parsedown = new Parsedown();
            $body_html = $parsedown->text($update_data['body']);
            $comment .= '<div class="linear-update-body">' . $body_html . '</div>';
        }
        
        // Add URL to Linear if available
        if (isset($update_data['url'])) {
            $comment .= '<p><a href="' . esc_url($update_data['url']) . '" target="_blank">View in Linear</a></p>';
        }
        
        return $comment;
    }

    /**
     * Validate webhook signature
     *
     * @param string $payload
     * @param string $signature
     * @return bool
     */
    private function validate_webhook_signature($payload, $signature) {
        // Get the secret key directly from WordPress options
        $secret = get_option('linear_wp_webhook_secret', '');
        
        if (empty($secret) || empty($signature)) {
            return false;
        }
        
        // Check if signature is an array (some servers might pass headers as arrays)
        if (is_array($signature)) {
            $signature = reset($signature);
        }
        
        // Calculate the expected signature - Linear uses hex digest, not base64
        $expected_signature = hash_hmac('sha256', $payload, $secret);
        
        // Compare the signatures
        return hash_equals($expected_signature, $signature);
    }
}
