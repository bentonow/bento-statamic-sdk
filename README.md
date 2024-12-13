# Bento Statamic Addon
<img align="right" src="https://app.bentonow.com/brand/logoanim.gif">

> [!TIP]
> Need help? Join our [Discord](https://discord.gg/ssXXFRmt5F) or email jesse@bentonow.com for personalized support.

The Bento Statamic Addon makes it quick and easy to send emails and track events in your Statamic applications. We provide powerful and customizable APIs that can be used out-of-the-box to manage subscribers, track events, and send transactional emails through your Statamic Control Panel.

Get started with our [📚 integration guides](https://docs.bentonow.com), or [📘 browse the SDK reference](https://docs.bentonow.com/subscribers).

## Features

* **Statamic Control Panel Integration**: Configure Bento directly from your Statamic CP
* **Laravel Mail Integration**: Seamlessly integrate with Laravel's mail system to send transactional emails via Bento
* **Event Tracking**: Automatically track user registrations and custom events
* **Subscriber Management**: Import and manage subscribers directly from your Statamic site
* **Author Management**: Easily configure email senders through the CP interface
* **Test Email Functionality**: Send test emails to verify your configuration

## Requirements

- PHP 8.0+
- Statamic 5.0+
- Laravel 10.0+
- Bento API Keys

## Installation

1. Install the package via Composer:

```bash
composer require bento/bento-statamic-sdk
```

2. Publish the configuration file and assets:

```bash
php artisan vendor:publish --tag=bento-config
php artisan vendor:publish --tag=bento-statamic-assets
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
