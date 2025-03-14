=== Linear WordPress Integration ===
Contributors: jacobsmith
Tags: linear, project management, webhooks, integration
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Integrates Linear projects with WordPress by creating posts from webhook data.

== Description ==

Linear WordPress Integration creates WordPress posts automatically when new projects are created in Linear. It sets up a custom REST API endpoint to receive webhook data from Linear and creates new posts based on a configurable template.

= Features =

* Creates WordPress posts from Linear projects
* Adds project updates as comments on the corresponding posts
* Customizable post template with placeholders for Linear project data
* Secure webhook handling with signature validation
* Easy setup with minimal configuration

= Available Placeholders =

The following placeholders can be used in the post template:

* `{id}` - Linear project ID
* `{name}` - Project name
* `{description}` - Project description
* `{url}` - URL to the project in Linear
* `{created_at}` - Project creation date
* `{updated_at}` - Project last update date
* `{start_date}` - Project start date
* `{target_date}` - Project target completion date
* `{health}` - Project health status
* `{status_name}` - Project status
* `{lead_name}` - Project lead name
* `{lead_email}` - Project lead email
* `{initiative_linked}` - Linked initiative with URL
* `{initiative_name}` - Initiative name
* `{initiative_url}` - Initiative URL

== Installation ==

1. Upload the `linear-wp` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Linear WP to configure the plugin

= Webhook Setup =

1. In WordPress, go to Settings > Linear WP
2. Copy the Webhook URL shown on the settings page
3. In Linear, go to Settings > API > Webhooks
4. Create a new webhook with the URL from step 2
5. Copy the webhook signing secret from Linear and paste it into the plugin settings
6. Select which events to send to WordPress (recommended: Projects and Project Updates)
7. Save your settings in both Linear and WordPress

== Frequently Asked Questions ==

= How do I customize the post template? =

Go to Settings > Linear WP and edit the Post Template field. You can use any of the available placeholders listed above.

= Can I disable signature validation for testing? =

Yes, you can enable the "Bypass Signature Validation" option in the plugin settings. This is not recommended for production environments.

= How do I troubleshoot webhook issues? =

1. Check that the webhook URL in Linear matches the one shown in the plugin settings
2. Verify that the webhook secret in WordPress matches the one in Linear
3. Make sure the webhook is active in Linear
4. Check that you've selected the appropriate events (Projects and Project Updates)

== Screenshots ==

1. Plugin settings page
2. Example of a post created from a Linear project
3. Example of comments added from project updates

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release
