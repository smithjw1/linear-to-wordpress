# Linear WordPress Integration

A WordPress plugin that integrates Linear projects with WordPress by creating posts from webhook data.

## Description

Linear WordPress Integration creates WordPress posts automatically when new projects are created in Linear. It sets up a custom REST API endpoint to receive webhook data from Linear and creates new posts based on a configurable template.

## Features

- Creates WordPress posts from Linear projects
- Adds project updates as comments on the corresponding posts
- Customizable post template with placeholders for Linear project data
- Secure webhook handling with signature validation
- Easy setup with minimal configuration

## Installation

### Manual Installation

1. Upload the `linear-wp` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Linear WP to configure the plugin

### Using Composer

```bash
composer require jacobsmith/linear-wp
```

## Configuration

### Webhook Setup

1. In WordPress, go to Settings > Linear WP
2. Copy the Webhook URL shown on the settings page
3. In Linear, go to Settings > API > Webhooks
4. Create a new webhook with the URL from step 2
5. Copy the webhook signing secret from Linear and paste it into the plugin settings
6. Select which events to send to WordPress (recommended: Projects and Project Updates)
7. Save your settings in both Linear and WordPress

## Usage

Once configured, the plugin will automatically:

1. Create a new WordPress post when a new project is created in Linear
2. Add comments to the post when project updates are created in Linear

You can customize the post template in the plugin settings to control how Linear project data is displayed in WordPress.

## Available Placeholders

The following placeholders can be used in the post template:

- `{id}` - Linear project ID
- `{name}` - Project name
- `{description}` - Project description
- `{url}` - URL to the project in Linear
- `{created_at}` - Project creation date
- `{updated_at}` - Project last update date
- `{start_date}` - Project start date
- `{target_date}` - Project target completion date
- `{health}` - Project health status
- `{status_name}` - Project status
- `{lead_name}` - Project lead name
- `{lead_email}` - Project lead email
- `{initiative_linked}` - Linked initiative with URL
- `{initiative_name}` - Initiative name
- `{initiative_url}` - Initiative URL

## Update Comments

When a project update is created in Linear, the plugin will:

1. Find the corresponding WordPress post
2. Add a new comment with formatted update information
3. Include health status indicators (color-coded):
   - Off Track (Red)
   - At Risk (Yellow)
   - On Track (Green)

## Frequently Asked Questions

### How do I customize the post template?

Go to Settings > Linear WP and edit the Post Template field. You can use any of the available placeholders listed above.

### Can I disable signature validation for testing?

Yes, you can enable the "Bypass Signature Validation" option in the plugin settings. This is not recommended for production environments.

### How do I troubleshoot webhook issues?

1. Check that the webhook URL in Linear matches the one shown in the plugin settings
2. Verify that the webhook secret in WordPress matches the one in Linear
3. Make sure the webhook is active in Linear
4. Check that you've selected the appropriate events (Projects and Project Updates)

## Troubleshooting

### Posts are not being created

- Verify that your webhook is configured correctly in Linear
- Check that the webhook secret matches between Linear and WordPress
- Make sure you have selected the appropriate events in Linear
- Check your server's error logs for any PHP errors

### Webhook signature validation fails

- Make sure the webhook secret in WordPress exactly matches the one in Linear
- If testing locally, you may need to enable "Bypass Signature Validation"

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

### Development Setup

1. Clone the repository
2. Run `composer install` to install dependencies
3. Set up a local WordPress development environment

## License

This plugin is licensed under the GPL v2 or later.

## Changelog

### 1.0.0
- Initial release
- Support for creating posts from Linear projects
- Customizable post templates
- Project update comments
- Health status indicators
- Initiative linking
- Markdown support for project updates
