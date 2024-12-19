# Bento Statamic Addon
<img align="right" src="https://app.bentonow.com/brand/logoanim.gif">

> [!TIP]
> Need help? Join our [Discord](https://discord.gg/ssXXFRmt5F) or email jesse@bentonow.com for personalized support.

The Bento Statamic Addon makes it quick and easy to send emails and track events in your Statamic applications. We provide powerful and customizable APIs that can be used out-of-the-box to manage subscribers, track events, and send transactional emails through your Statamic Control Panel.

Get started with our [📚 integration guides](https://docs.bentonow.com), or [📘 browse the SDK reference](https://docs.bentonow.com/subscribers).

## Features

* **Statamic Control Panel Integration**: Configure Bento directly from your Statamic CP
* **Laravel Mail Integration**: Seamlessly integrate with Laravel's mail system to send transactional emails via Bento
* **Event Tracking**: Create and manage custom events directly from the CP
* **Form Integration**: Associate Bento events with Statamic forms for automated tracking
* **Subscriber Management**: Import and manage subscribers directly from your Statamic site
* **Author Management**: Easily configure email senders through the CP interface
* **Test Email Functionality**: Send test emails to verify your configuration
* **Frontend Tracking**: Optional automatic injection of Bento's tracking script
* **User Sync**: Automatic subscriber creation when new users register

## Requirements

- PHP 8.0+
- Statamic 5.0+
- Laravel 10.0+
- Bento API Keys

## Installation

1. Install the package via Composer:

```bash
composer require bentonow/bento-statamic-sdk
```

2. Publish the configuration file and assets:

```bash
php artisan vendor:publish --tag=bento-config
php artisan vendor:publish --tag=bento-statamic-assets

# (optional) if you wish to use a list of prebuilt bento events
php artisan vendor:publish --tag=bento-statamic-seeders
php artisan db:seed --class="Database\\Seeders\\Bento\\BentoFormEventsSeeder"


```

## Configuration

1. Access the Bento configuration page in your Statamic Control Panel under Tools > Bento

2. Enter your Bento credentials:
    - Site UUID
    - Publishable Key
    - Secret Key

   You can find these at [app.bentonow.com/account/teams](https://app.bentonow.com/account/teams)

3. Configure your email settings:
    - Select an author from your Bento account
    - Send a test email to verify your configuration

4. Alternatively, you can configure Bento through your `.env` file:

```dotenv
BENTO_ENABLED=true
BENTO_SITE_UUID="bento-site-uuid"
BENTO_PUBLISHABLE_KEY="bento-publishable-key"
BENTO_SECRET_KEY="bento-secret-key"
MAIL_MAILER="bento"
MAIL_FROM_ADDRESS="your-author@email.com"
```

## Advanced Features

### User Registration Tracking

Control automatic user registration tracking in Bento:
- Enable/disable automatic subscriber creation when new users register
- Automatically splits full names into first and last names
- Syncs email and basic user information
- Configure through Advanced Settings in the CP

### User Synchronization Tags

When automatic user synchronization is enabled, you can configure default tags that will be automatically applied to new users when they are synchronized with Bento:

- Choose from your existing Bento tags in the Advanced Settings
- Add multiple tags that will be applied to all new users
- Tags are stored consistently whether using database or file-based storage
- Easily manage tags through the Control Panel interface
- Remove tags with a single click
- Changes take effect immediately for new user registrations

#### Configuration Steps:

1. Navigate to Tools > Bento > Advanced Settings
2. Enable "Automatic User Sync"
3. Under the sync settings, use the tag selector to add default tags
4. Selected tags will automatically be applied to all new user registrations

#### Example Usage:

If you've selected tags like "new-user" and "needs-onboarding", when a new user registers:
- A subscriber is created in Bento
- The user's name is split into first and last name
- Email and basic user information is synced
- Both "new-user" and "needs-onboarding" tags are automatically applied

This feature allows you to automatically segment new users without any additional configuration.

### Frontend Tracking Script

Manage Bento's JavaScript tracking integration:
- Toggle automatic injection of Bento's tracking script
- Tracks visitor behavior and custom events
- Automatically adds tracking code to all frontend pages
- No manual code insertion required

### Custom Events Management

Create and manage custom events for Bento automation:
- Define custom event names through the CP interface
- Use events in Bento's advanced flows and email automations
- Track events across your Statamic site
- Manage events with an intuitive UI

### Form Event Integration

Connect Statamic forms with Bento events:
- Associate any Statamic form with a Bento event
- Automatically triggers Bento events on form submission
- Sends form data as event properties
- Perfect for triggering automated workflows
- Configure through the Form Events Manager in Advanced Settings

Example form event workflow:
1. Create a custom event in the CP (e.g., "Newsletter Signup")
2. Associate the event with a Statamic form
3. When the form is submitted:
    - Event is automatically triggered in Bento
    - Form data is sent as event properties
    - Triggers any associated Bento automations

## Automatic Features

### User Registration Tracking

The addon automatically tracks new user registrations in Bento:
- Creates a subscriber when a new user registers
- Splits full names into first and last names
- Syncs email and basic user information

### Email Integration

Once configured, the addon:
- Handles transactional emails through Bento
- Uses your configured Bento authors as email senders

## Things to Know

1. **Email Limitations**:
    - Bento does not support email attachments
    - No-reply sender addresses are not supported

2. **Rate Limits**:
    - The Bento Transactional Email API is designed for low-volume emails
    - Use for password resets, form notifications, etc.
    - Not suitable for newsletter or bulk email sending

3. **Author Configuration**:
    - You must use an authorized Bento author as your sender address
    - Authors can be configured through the CP interface

4. **Environment Handling**:
    - Configuration changes update your `.env` file
    - Cache is automatically cleared after configuration updates
    - Runtime configuration is updated immediately

## Contributing

We welcome contributions! Please follow these steps:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run the tests
5. Submit a pull request

For major changes, please open an issue first to discuss what you would like to change.

## License

The Bento Statamic Addon is open-sourced software licensed under the [MIT license](LICENSE.md).
