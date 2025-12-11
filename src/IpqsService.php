<?php

namespace IpqsLaravel;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class IpqsService
{
    protected Client $client;
    protected string $apiKey;
    protected string $baseUrl = 'https://www.ipqualityscore.com/api/json/';

    public function __construct(Client $client, string $apiKey)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
    }

    /**
     * Make a request to the IPQS API
     * @param string $endpoint
     * @param array $params
     * @return array
     * @throws IpqsException
     */
    protected function request(string $endpoint, array $params = []): array
    {
        $params['key'] = $this->apiKey;
        $url = $this->baseUrl . $endpoint;

        try {
            if (empty($params)) {
                $response = $this->client->get($url, [
                    'headers' => [
                        'key' => $this->apiKey,
                    ]
                ]);
            } else {
                $response = $this->client->get($url, ['query' => $params]);
            }

            $data = json_decode($response->getBody()->getContents(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new IpqsException('Failed to parse JSON response from IPQS.');
            }

            if (!isset($data['success']) || !$data['success']) {
                $message = $data['message'] ?? 'Unknown IPQS API error.';
                throw new IpqsException($message);
            }

            return $data;

        } catch (Exception $e) {
            throw new IpqsException('HTTP request to IPQS failed: ' . $e->getMessage());
        } catch (GuzzleException $e) {
            throw new IpqsException('HTTP request to IPQS failed: ' . $e->getMessage());
        }
    }

    /**
     * Proxy & VPN Detection API
     * @see https://www.ipqualityscore.com/documentation/proxy-detection-api
     *
     * @param string $ip
     * @param ?string $userAgent
     * @param ?string $userLanguage
     * @param ?int $strictness
     * @return array
     * @throws IpqsException
     */
    public function checkIp(string $ip, ?string $userAgent = NULL, ?string $userLanguage = NULL, ?int $strictness = NULL): array
    {
        $params = [
            'user_agent' => $userAgent ?? '',
            'user_language' => $userLanguage ?? '',
            'strictness' => $strictness ?? 0,
            'allow_public_access_points' => 'true',
            'lighter_penalties' => 'true'
        ];
        return $this->request('ip/' . $this->apiKey . '/' . $ip, $params);
    }

    /**
     * Email Validation API
     * @see https://www.ipqualityscore.com/documentation/email-validation-api
     *
     * @param string $email
     * @param array $options [timeout, fast, etc.]
     * @return array
     * @throws IpqsException
     */
    public function verifyEmail(string $email, array $options = []): array
    {
        return $this->request('email/' . $this->apiKey . '/' . $email, $options);
    }

    /**
     * Phone Number Validation API
     * @see https://www.ipqualityscore.com/documentation/phone-validation-api
     *
     * @param string $phone
     * @param array $options [country_code, etc.]
     * @return array
     * @throws IpqsException
     */
    public function validatePhone(string $phone, array $options = []): array
    {
        return $this->request('phone/' . $this->apiKey . '/' . $phone, $options);
    }

    /**
     * Bulk Validation CSV API
     * @see https://www.ipqualityscore.com/documentation/bulk-validation-api
     *
     * @param string $type 'email', 'proxy', 'url', or 'phone'
     * @param array $list Array of items to validate (emails, IPs, or phones)
     * @return array
     * @throws IpqsException
     * @throws GuzzleException
     */
    public function bulkValidateCsv(string $type, array $list): array
    {
        $input = array_map(
            fn ($item) => [$item],
            array_filter($list, fn ($item) => !empty($item))
        );

        $body = [
            "type" => $type, // proxy | email | phone | url
            "file_name" => $type . ".csv",
            "key" => $this->apiKey,
            "input" => $input
        ];

        try {
            $response = $this->client->post($this->baseUrl . 'csv/upload', [
                'content-type' => 'application/json',
                'body' => json_encode($body),
            ]);
            $data = json_decode($response->getBody()->getContents(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new IpqsException('Failed to parse JSON response from IPQS.');
            }

            // Bulk API response structure is slightly different
            if (isset($data['errors']) && !empty($data['errors'])) {
                throw new IpqsException('IPQS Bulk API Error: ' . implode(', ', $data['errors']));
            }

            if (!isset($data['success']) || !$data['success']) {
                $message = $data['message'] ?? 'Unknown IPQS API error.';
                throw new IpqsException($message);
            }

            $statusUrl = $data['status_url'] ?? null;
            if (!$statusUrl) {
                throw new IpqsException('IPQS Bulk API did not return a status URL.');
            }

            $data = $this->checkStatus($statusUrl);

            $result = file_get_contents($data['downloads']['all']);
            $result = json_decode($result, true);

            return $result ?? [];

        } catch (Exception $e) {
            throw new IpqsException('HTTP request to IPQS failed: ' . $e->getMessage());
        }
    }

    /**
     * Check the status of a bulk validation job.
     * @param string $statusUrl
     * @return array
     * @throws IpqsException
     */
    protected function checkStatus(string $statusUrl): array
    {
        static $retries = 0;
        $maxRetries = 6;
        try {
            $data = $this->request($statusUrl);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new IpqsException('Failed to parse JSON response from IPQS.');
            }

            if($data['status'] !== 'FINISHED' && $retries < $maxRetries){
                $retries++;
                sleep(10);
                $data = $this->checkStatus($statusUrl);
            }
            return $data;
        } catch (Exception $e) {
            throw new IpqsException('HTTP request to IPQS failed: ' . $e->getMessage());
        }
    }

}
