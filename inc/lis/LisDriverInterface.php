<?php
/**
 * Interface LisDriverInterface
 * Abstract contract for Laboratory Information System (LIS) drivers.
 * Allows swapping LIS providers (e.g. ClinOS, HL7, custom APIs) without modifying EMR business logic.
 */
interface LisDriverInterface {
    /**
     * Get test catalog from LIS API.
     * @return array Array of canonical tests
     */
    public function getTestCatalog(): array;

    /**
     * Send order to LIS API.
     * @param array $orderData
     * @return array Response array containing order_id, order_uid, label_url, specimens, etc.
     */
    public function createOrder(array $orderData): array;

    /**
     * Check status of a single order by external_order_id.
     * @param string $externalOrderId
     * @return array Order status payload
     */
    public function getOrder(string $externalOrderId): array;

    /**
     * Pull validated results using cursor-based after_id polling.
     * @param int $afterId
     * @param int $limit
     * @return array Array containing items, count, after_id, next_after_id
     */
    public function pullResults(int $afterId, int $limit = 100): array;

    /**
     * Fetch label HTML content securely from LIS server using auth keys.
     * @param string $labelUrl
     * @return string Raw HTML/Content of tube label
     */
    public function getLabelContent(string $labelUrl): string;
}
