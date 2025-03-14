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
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"vivid-cyan-blue","textColor":"white","className":"is-style-fill"} -->
<div class="wp-block-button is-style-fill"><a class="wp-block-button__link has-white-color has-vivid-cyan-blue-background-color has-text-color has-background" href="{url}" target="_blank" rel="noreferrer noopener">View on Linear</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->';
return $template;
}

    /**
     * Get health status mapping
     *
     * @param string $health_key Health status key
     * @return array
     */
    public static function get_health_status_mapping($health_key = '') {
        $health_statuses = [
            'onTrack' => [
                'text' => 'On Track',
                'color' => '#34D399' // Green
            ],
            'offTrack' => [
                'text' => 'Off Track',
                'color' => '#F87171' // Red
            ],
            'atRisk' => [
                'text' => 'At Risk',
                'color' => '#FBBF24' // Yellow/Orange
            ]
        ];
        
        if (!empty($health_key) && isset($health_statuses[$health_key])) {
            return $health_statuses[$health_key];
        }
        
        // Default to "On Track" if not found
        return $health_statuses['onTrack'];
    }
    
    /**
     * Format health text with colored dot
     *
     * @param string $health Health status from Linear
     * @return string Formatted health text with colored dot
     */
    public static function format_health_text($health) {
        $health_mapping = self::get_health_status_mapping($health);
        $color = $health_mapping['color'];
        $text = $health_mapping['text'];
        
        // Create colored dot using inline CSS
        $dot = '<span style="display:inline-block; width:12px; height:12px; border-radius:50%; background-color:' . esc_attr($color) . '; margin-right:5px;"></span>';
        
        return $dot . $text;
    }
}
