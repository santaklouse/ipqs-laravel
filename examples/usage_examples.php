<?php

require_once __DIR__ . '/../vendor/autoload.php';

// --- Настройка для использования вне Laravel ---
// Если вы используете это в Laravel, этот блок не нужен.
// Laravel автоматически зарегистрирует сервис.
use IpqsLaravel\IpqsService;
use IpqsLaravel\IpqsException;
use GuzzleHttp\Client;

// Вставьте ваш API ключ
$apiKey = 'YOUR_IPQS_API_KEY';

// Создаем экземпляр сервиса вручную
$ipqsService = new IpqsService(new Client(), $apiKey);

echo "--- IPQS Service Usage Examples ---\n\n";

// --- 1. Proxy & VPN Detection ---
echo "1. Checking IP address (8.8.8.8)...\n";
try {
    $ipResult = $ipqsService->checkIp('8.8.8.8', [
        'strictness' => 1,
        'user_agent' => 'Mozilla/5.0...',
        'user_language' => 'en-US',
    ]);
    echo "Success! Proxy Score: " . $ipResult['proxy_score'] . "\n";
    echo "Is VPN: " . ($ipResult['vpn'] ? 'Yes' : 'No') . "\n";
    echo "Full Response:\n";
    print_r($ipResult);
} catch (IpqsException $e) {
    echo "Error checking IP: " . $e->getMessage() . "\n";
}
echo str_repeat("-", 40) . "\n\n";


// --- 2. Email Verification ---
echo "2. Verifying email (test@ipqualityscore.com)...\n";
try {
    $emailResult = $ipqsService->verifyEmail('test@ipqualityscore.com', [
        'timeout' => 5,
    ]);
    echo "Success! Valid: " . ($emailResult['valid'] ? 'Yes' : 'No') . "\n";
    echo "Deliverable: " . ($emailResult['deliverable'] ? 'Yes' : 'No') . "\n";
    echo "Full Response:\n";
    print_r($emailResult);
} catch (IpqsException $e) {
    echo "Error verifying email: " . $e->getMessage() . "\n";
}
echo str_repeat("-", 40) . "\n\n";


// --- 3. Phone Number Validation ---
echo "3. Validating phone number (+15551234567)...\n";
try {
    $phoneResult = $ipqsService->validatePhone('+15551234567', [
        'country_code' => 'US',
    ]);
    echo "Success! Valid: " . ($phoneResult['valid'] ? 'Yes' : 'No') . "\n";
    echo "Mobile: " . ($phoneResult['mobile'] ? 'Yes' : 'No') . "\n";
    echo "Full Response:\n";
    print_r($phoneResult);
} catch (IpqsException $e) {
    echo "Error validating phone: " . $e->getMessage() . "\n";
}
echo str_repeat("-", 40) . "\n\n";


// --- 4. Bulk Validation CSV ---
echo "4. Bulk validating a CSV string...\n";
$csvContent = "email\n";
$csvContent .= "test@ipqualityscore.com\n";
$csvContent .= "invalid-email-address\n";

try {
    // Для bulk-запроса можно передать строку
    $bulkResult = $ipqsService->bulkValidateCsv($type, $list);
    echo "Success! Bulk job started.\n";
    echo "Full Response:\n";
    print_r($bulkResult);
} catch (IpqsException $e) {
    echo "Error with bulk validation: " . $e->getMessage() . "\n";
}
echo str_repeat("-", 40) . "\n";
