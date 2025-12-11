# IPQS Laravel Wrapper

A simple and modern wrapper for the [IPQualityScore (IPQS)](https://www.ipqualityscore.com/) API, tailored for Laravel applications.

# Installation

Require the package using Composer:

```bash
composer require santaklouse/ipqs-laravel
```

# Laravel Setup

## 1. Publish the configuration file

```bash
php artisan vendor:publish --provider="IpqsLaravel\\IpqsServiceProvider" --tag="ipqs-config"
```

This will create a `config/ipqs.php` file in your project.

## 2. Add your API key

Add your IPQS API key to your `.env` file:

```
IPQS_API_KEY=your_api_key_here
```

You can get an API key from your [IPQS Dashboard](https://www.ipqualityscore.com/user/dashboard).

# Usage

## Dependency Injection (Recommended)

You can type-hint the `IpqsService` in your controllers or other classes resolved by Laravel's service container.

```php
use IpqsLaravel\\IpqsService;
use IpqsLaravel\\IpqsException;

class MyController extends Controller
{
    protected IpqsService $ipqs;

    public function __construct(IpqsService $ipqs)
    {
        $this->ipqs = $ipqs;
    }

    public function checkUserIp(Request $request)
    {
        try {
            $ip = $request->ip();
            $result = $this->ipqs->checkIp($ip);

            if ($result['proxy'] || $result['vpn']) {
                // Block or flag the user
                return response()->json(['message' => 'Proxy/VPN detected.'], 403);
            }

            return response()->json(['message' => 'IP is clean.']);

        } catch (IpqsException $e) {
            // Log the error or handle it
            report($e);
            return response()->json(['message' => 'Could not verify IP.'], 500);
        }
    }
}
```

## Facade

You can also use the provided `Ipqs` facade for quick access.

```php
use IpqsLaravel\\Facades\\Ipqs;
use IpqsLaravel\\IpqsException;

// ...

try {
    $emailResult = Ipqs::verifyEmail('user@example.com');
    if (!$emailResult['valid']) {
        // Handle invalid email
    }
} catch (IpqsException $e) {
    report($e);
}
```

## Available Methods

### `checkIp(string $ip, array $options = [])`
Detects proxies, VPNs, and tor connections.
*See [IPQS Proxy Detection Docs](https://www.ipqualityscore.com/documentation/proxy-detection-api)*

### `verifyEmail(string $email, array $options = [])`
Validates an email address for deliverability and fraud.
*See [IPQS Email Validation Docs](https://www.ipqualityscore.com/documentation/email-validation-api)*

### `validatePhone(string $phone, array $options = [])`
Validates a phone number to check if it's valid, mobile, and a VOIP.
*See [IPQS Phone Validation Docs](https://www.ipqualityscore.com/documentation/phone-validation-api)*

### `bulkValidateCsv($fileContent)`
Submits a CSV file for bulk validation of emails, IPs, or phones.
*See [IPQS Bulk Validation Docs](https://www.ipqualityscore.com/documentation/bulk-validation-api)*

# Configuration

The `config/ipqs.php` file allows you to set default options for each API type, which will be merged with any options you pass at runtime.
