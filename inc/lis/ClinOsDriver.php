<?php
require_once __DIR__ . '/LisDriverInterface.php';

/**
 * Class ClinOsDriver
 * Implementation of LIS Driver for ClinOS / ZMKC LIVS API contract.
 */
class ClinOsDriver implements LisDriverInterface {
    private $baseUrl;
    private $emrKey;
    private $catalogueKey;

    public function __construct(string $baseUrl, ?string $emrKey, ?string $catalogueKey) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->emrKey = $emrKey;
        $this->catalogueKey = $catalogueKey;
    }

    /**
     * Helper to send HTTP requests using cURL
     */
    private function request(string $endpoint, string $method = 'GET', ?array $data = null, array $extraHeaders = []): array {
        $url = (strpos($endpoint, 'http://') === 0 || strpos($endpoint, 'https://') === 0) 
            ? $endpoint 
            : $this->baseUrl . '/' . ltrim($endpoint, '/');

        $ch = curl_init($url);

        $headers = [
            'Accept: application/json'
        ];

        if ($data !== null) {
            $headers[] = 'Content-Type: application/json';
            $jsonPayload = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
        }

        $headers = array_merge($headers, $extraHeaders);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $responseBody = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new Exception("ClinOS cURL Error: " . $curlError);
        }

        $decoded = json_decode($responseBody, true);
        
        return [
            'http_code' => $httpCode,
            'body' => $decoded !== null ? $decoded : $responseBody,
            'raw' => $responseBody
        ];
    }

    /**
     * Get test catalog from ClinOS
     */
    public function getTestCatalog(): array {
        if (empty($this->catalogueKey)) {
            throw new Exception("ClinOS Catalogue Key (X-ClinOS-Key) is missing.");
        }

        $headers = ['X-ClinOS-Key: ' . $this->catalogueKey];
        $res = $this->request('/api/v1/lab/test-catalog', 'GET', null, $headers);

        if ($res['http_code'] >= 200 && $res['http_code'] < 300) {
            return is_array($res['body']) ? $res['body'] : [];
        }

        throw new Exception("Failed to fetch ClinOS catalog (HTTP {$res['http_code']}): " . (is_string($res['body']) ? $res['body'] : json_encode($res['body'])));
    }

    /**
     * Send order to ClinOS
     */
    public function createOrder(array $orderData): array {
        if (empty($this->emrKey)) {
            throw new Exception("ClinOS EMR Integration Key (X-ClinOS-EMR-Key) is missing.");
        }

        $headers = ['X-ClinOS-EMR-Key: ' . $this->emrKey];
        $res = $this->request('/api/v1/emr/orders', 'POST', $orderData, $headers);

        if ($res['http_code'] == 201 || ($res['http_code'] >= 200 && $res['http_code'] < 300)) {
            return is_array($res['body']) ? $res['body'] : ['raw' => $res['body']];
        }

        if ($res['http_code'] == 409 && is_array($res['body'])) {
            $body = $res['body'];
            $detailData = $body['detail']['detail'] ?? ($body['detail'] ?? $body);
            if (is_array($detailData) && !empty($detailData['clinos_order_id'])) {
                $orderUid = $detailData['clinos_order_uid'] ?? null;
                $labelUrl = $orderUid ? "/api/v1/lab/orders/{$orderUid}/label" : null;
                return [
                    'order_id' => $detailData['clinos_order_id'],
                    'order_uid' => $orderUid,
                    'label_url' => $labelUrl,
                    'already_existed' => true,
                    'raw' => $body
                ];
            }
        }

        $errorMsg = is_array($res['body']) && isset($res['body']['message']) 
            ? $res['body']['message'] 
            : (is_string($res['body']) ? $res['body'] : json_encode($res['body']));

        throw new Exception("ClinOS Create Order Failed (HTTP {$res['http_code']}): {$errorMsg}");
    }

    /**
     * Get order details by external order ID
     */
    public function getOrder(string $externalOrderId): array {
        if (empty($this->emrKey)) {
            throw new Exception("ClinOS EMR Key missing.");
        }

        $headers = ['X-ClinOS-EMR-Key: ' . $this->emrKey];
        $res = $this->request('/api/v1/emr/orders/' . urlencode($externalOrderId), 'GET', null, $headers);

        if ($res['http_code'] >= 200 && $res['http_code'] < 300) {
            return is_array($res['body']) ? $res['body'] : [];
        }

        throw new Exception("ClinOS Get Order Failed (HTTP {$res['http_code']})");
    }

    /**
     * Pull results from ClinOS cursor
     */
    public function pullResults(int $afterId, int $limit = 100): array {
        if (empty($this->emrKey)) {
            throw new Exception("ClinOS EMR Key missing.");
        }

        $headers = ['X-ClinOS-EMR-Key: ' . $this->emrKey];
        $endpoint = "/api/v1/emr/results?after_id={$afterId}&limit={$limit}";
        $res = $this->request($endpoint, 'GET', null, $headers);

        if ($res['http_code'] >= 200 && $res['http_code'] < 300) {
            return is_array($res['body']) ? $res['body'] : [];
        }

        throw new Exception("ClinOS Pull Results Failed (HTTP {$res['http_code']})");
    }

    /**
     * Fetch label HTML content securely using X-ClinOS-Key
     */
    public function getLabelContent(string $labelUrl): string {
        if (empty($this->catalogueKey)) {
            throw new Exception("ClinOS Catalogue Key missing.");
        }

        $headers = ['X-ClinOS-Key: ' . $this->catalogueKey];
        $res = $this->request($labelUrl, 'GET', null, $headers);

        if ($res['http_code'] >= 200 && $res['http_code'] < 300) {
            return is_string($res['body']) ? $res['body'] : json_encode($res['body']);
        }

        throw new Exception("Failed to download tube label (HTTP {$res['http_code']})");
    }
}
