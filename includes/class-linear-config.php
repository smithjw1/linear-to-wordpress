<?php
/**
 * Configuration constants and settings for the plugin
 *
 * @package Linear_WP
 */

namespace LinearWP;

// Prevent direct access
if (!defined('WPINC')) {
    die;
}

/**
 * Plugin configuration class
 */
class Config {

    /**
     * Webhook types
     */
    const WEBHOOK_TYPE_PROJECT = 'Project';
    const WEBHOOK_TYPE_PROJECT_UPDATE = 'ProjectUpdate';

    /**
     * Webhook actions
     */
    const WEBHOOK_ACTION_CREATE = 'create';
    const WEBHOOK_ACTION_UPDATE = 'update';

    /**
     * Get plugin option with default fallback
     *
     * @param string $option_name Option name
     * @param mixed $default Default value
     * @return mixed
     */
    public static function get_option($option_name, $default = '') {
        return get_option($option_name, $default);
    }

    /**
     * Get default post template
     *
     * @return string
     */
    public static function get_default_post_template() {
        $template = '<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"width":"33%"} -->
<div class="wp-block-column" style="flex-basis:33%"><!-- wp:paragraph -->
<p>{status_name}</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"33.33%"} -->
<div class="wp-block-column" style="flex-basis:33.33%"><!-- wp:paragraph -->
<p>{health}</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"33%"} -->
<div class="wp-block-column" style="flex-basis:33%"><!-- wp:paragraph -->
<p>{initiative_linked}</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"width":"50%"} -->
<div class="wp-block-column" style="flex-basis:50%"><!-- wp:paragraph -->
<p>{lead_name}</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"50%"} -->
<div class="wp-block-column" style="flex-basis:50%"><!-- wp:paragraph -->
<p>{start_date} - {target_date}</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:paragraph -->
<p>{description}</p>
<!-- /wp:paragraph -->';

        return $template;
    }

    /**
     * Get health status mapping
     *
     * @param string $health_key Health key from Linear
     * @return array
     */
    public static function get_health_status_mapping($health_key = '') {
        $health_statuses = array(
            'onTrack' => array(
                'text' => 'On Track',
                'color' => '#2DA446'
            ),
            'atRisk' => array(
                'text' => 'At Risk',
                'color' => '#F2C94C'
            ),
            'offTrack' => array(
                'text' => 'Off Track',
                'color' => '#EB5757'
            ),
        );

        if (isset($health_statuses[$health_key])) {
            return $health_statuses[$health_key];
        }

        // Default to on track if not found
        return $health_statuses['onTrack'];
    }
}
