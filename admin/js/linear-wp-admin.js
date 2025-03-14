/**
 * Linear WordPress Integration Admin JS
 */
document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    // Handle reset template button
    const resetButton = document.getElementById('reset-template-button');
    if (resetButton) {
        resetButton.addEventListener('click', function() {
            // Get the default template from the localized variable
            if (typeof linearWpAdmin !== 'undefined' && linearWpAdmin.defaultTemplate) {
                // Set the template textarea value
                document.getElementById('linear_wp_post_template').value = linearWpAdmin.defaultTemplate;
                console.log('Template reset successful');
            } else {
                console.error('Default template not available');
            }
        });
    }
});
