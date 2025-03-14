/**
 * Linear WordPress Integration Admin JS
 */
(function($) {
    'use strict';

    // Document ready
    $(function() {
        // Handle reset template button
        $('#reset-template-button').on('click', function() {
            // Get the default template from the localized variable
            if (typeof linearWpAdmin !== 'undefined' && linearWpAdmin.defaultTemplate) {
                // Set the template textarea value
                $('#linear_wp_post_template').val(linearWpAdmin.defaultTemplate);
                console.log('Template reset successful');
            } else {
                console.error('Default template not available');
            }
        });
    });

})(jQuery);
