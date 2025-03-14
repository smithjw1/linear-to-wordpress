<?php
/**
 * Webhook handler for Linear
 *
 * @package Linear_WP
 */

namespace LinearWP;

use WP_REST_Request;
use WP_REST_Response;
use Exception;

// Prevent direct access
if (!defined('WPINC')) {
    die;
}

/**
 * Webhook handler class
 */
class Webhook_Handler {

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
     * Project handler instance
     *
     * @var Project_Handler
     */
    private $project_handler;

    /**
     * Project update handler instance
     *
     * @var Project_Update_Handler
     */
    private $project_update_handler;

    /**
     * Initialize the class
     *
     * @param string $plugin_name The plugin name
     * @param string $version The plugin version
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->project_handler = new Project_Handler($plugin_name, $version);
        $this->project_update_handler = new Project_Update_Handler($plugin_name, $version);
    }

    /**
     * Register webhook endpoint
     */
    public function register_webhook_endpoint() {
        register_rest_route('linear-wp/v1', '/webhook', [
            'methods' => 'POST',
            'callback' => [$this, 'process_webhook'],
            'permission_callback' => [$this, 'validate_webhook_request'],
        ]);
    }

    /**
     * Validate webhook request
     *
     * @param WP_REST_Request $request
     * @return bool|WP_REST_Response
     */
    public function validate_webhook_request(WP_REST_Request $request) {
        try {
            // Get request data
            $data = $request->get_json_params();
            
            // Validate webhook signature
            $signature = $request->get_header('linear_signature');
            if (!$this->validate_webhook_signature($signature, $request->get_body())) {
                return new WP_REST_Response([
                    'success' => false,
                    'message' => 'Invalid webhook signature'
                ], 401);
            }
            
            // Validate webhook data
            if (!$this->validate_webhook_data($data)) {
                return new WP_REST_Response([
                    'success' => false,
                    'message' => 'Invalid webhook data'
                ], 400);
            }
            
            return true;
        } catch (Exception $e) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Error validating webhook: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process webhook
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function process_webhook(WP_REST_Request $request) {
        try {
            // Get request data
            $data = $request->get_json_params();
            
            // Process based on webhook type and action
            if ($data['type'] === Config::WEBHOOK_TYPE_PROJECT && 
                $data['action'] === Config::WEBHOOK_ACTION_CREATE) {
                return $this->project_handler->handle_project_creation($data['data']);
            } elseif ($data['type'] === Config::WEBHOOK_TYPE_PROJECT_UPDATE && 
                      $data['action'] === Config::WEBHOOK_ACTION_CREATE) {
                return $this->project_update_handler->handle_project_update($data['data']);
            }
            
            // Unsupported webhook type or action
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Unsupported webhook type or action'
            ], 400);
            
        } catch (Exception $e) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Error processing webhook: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Validate webhook signature
     *
     * @param string $signature Signature from request header
     * @param string $payload Request body
     * @return bool
     */
    private function validate_webhook_signature($signature, $payload) {
        // Get webhook secret from settings
        $webhook_secret = get_option('linear_wp_webhook_secret', '');
        
        // If no secret is set, skip signature validation
        if (empty($webhook_secret)) {
            return true;
        }
        
        // If no signature provided, validation fails
        if (empty($signature)) {
            return false;
        }
        
        // Calculate expected signature
        $expected_signature = hash_hmac('sha256', $payload, $webhook_secret);
        
        // Compare signatures
        return hash_equals($expected_signature, $signature);
    }
    
    /**
     * Validate webhook data
     *
     * @param array $data Webhook data
     * @return bool
     */
    private function validate_webhook_data($data) {
        // Check required fields
        if (!isset($data['type']) || !isset($data['action'])) {
            return false;
        }
        
        // Check if webhook type is supported
        if ($data['type'] !== Config::WEBHOOK_TYPE_PROJECT && 
            $data['type'] !== Config::WEBHOOK_TYPE_PROJECT_UPDATE) {
            return false;
        }
        
        // Check if webhook action is supported
        if ($data['action'] !== Config::WEBHOOK_ACTION_CREATE && 
            $data['action'] !== Config::WEBHOOK_ACTION_UPDATE) {
            return false;
        }
        
        return true;
    }
}
