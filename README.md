Here's a `README.md` file for your project:

```markdown
# Uptime Kuma to WordPress Webhook Handler

This project handles webhook POST requests from Uptime Kuma to a WordPress website. When an error is triggered in Uptime Kuma, a POST request with a JSON payload is sent to a PHP file on the WordPress server. This script processes the request, verifies a hashed token, clears caches, and logs the action.

## Table of Contents
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Security](#security)
- [Logging](#logging)
- [Support](#support)

## Features
- Receives webhook POST requests from Uptime Kuma.
- Verifies requests using a hashed token.
- Clears various WordPress caches (WP Rocket, Object Cache Pro).
- Logs actions and request details for debugging.

## Requirements
- WordPress installation.
- Uptime Kuma for monitoring.
- PHP 7.0 or higher.
- WP Rocket plugin (optional).
- Object Cache Pro plugin (optional).

## Installation
1. **Clone the repository:**
   ```bash
   git clone https://github.com/bostame/error_handler.git
   ```
2. **Move the `error_handler/500_handler.php` file to your WordPress installation:**
   Place it in the root directory or a suitable location on your server.
3. **Create a configuration file for the secret token:**
   ```php
   // secret.php
   <?php
   return [
       'secret_token' => hash('sha256', 'your-plain-secret-token')
   ];
   ?>
   ```

## Configuration
1. **Edit the webhook handler script:**
   Ensure the path to `wp-load.php` and `secret.php` is correct in `500_handler.php`.

   ```php
   require realpath('../wp-load.php');
   $config_path = realpath(__DIR__ . '/../token/secret.php');
   ```

2. **Configure Uptime Kuma:**
   - Set up a new monitor or alert with a webhook notification.
   - Ensure the token is hashed using SHA-256 before sending in the payload.
   
   Example JSON payload to send:
   ```json
   {
       "error": 500,
       "token": "hashed-token"
   }
   ```

## Usage
1. **Deploy the PHP script on your WordPress server.**
2. **Configure Uptime Kuma to send POST requests to this script:**
   - Set the URL to point to your PHP script, e.g., `https://yourwebsite.com/error_handler/500_handler.php`.

## Security
- **Token Verification:**
  The token in the request payload is hashed using SHA-256. The script verifies this hashed token against the hashed secret token stored in `secret.php`.
- **Security Headers:**
  The script includes security headers to prevent content type sniffing, clickjacking, and XSS attacks.

## Logging
- **Log File:**
  Actions and request details are logged in `uptime-kuma-log.txt` for debugging and auditing purposes.
- **Log Location:**
  The log file is stored in the same directory as the PHP script.

## License
This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

