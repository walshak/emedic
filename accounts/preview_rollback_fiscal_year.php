<?php
session_start();
require_once('../Connections/Conn.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'Method not allowed']));
}

$fiscal_year_id = filter_input(INPUT_POST, 'fiscal_year_id', FILTER_VALIDATE_INT);

if (!$fiscal_year_id) {
    die(json_encode(['success' => false, 'message' => 'Invalid fiscal year ID']));
}

try {
    // 1. Verify the year is closed and get next year ID
    $stmt = $db->prepare("
        SELECT fy1.closed, fy2.id as next_year_id, fy2.closed as next_year_closed
        FROM chart_fiscal_year fy1
        LEFT JOIN chart_fiscal_year fy2 ON fy2.begin > fy1.end
        WHERE fy1.id = ?
        ORDER BY fy2.begin ASC
        LIMIT 1
    ");
    $stmt->execute(array($fiscal_year_id));
    $yearInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$yearInfo || !$yearInfo['closed']) {
        die(json_encode(['success' => false, 'message' => 'Fiscal year is not closed.']));
    }

    $next_year_id = $yearInfo['next_year_id'];
    $next_year_closed = $yearInfo['next_year_closed'];
    $can_rollback = true;
    $blocking_reasons = [];

    if (!$next_year_id) {
        $can_rollback = false;
        $blocking_reasons[] = "Cannot find the next fiscal year that was created during closing.";
    }

    if ($next_year_closed) {
        $can_rollback = false;
        $blocking_reasons[] = "The next fiscal year is already closed. You must roll that one back first.";
    }

    $transCount = 0;
    if ($next_year_id) {
        // Check for manual transactions in the new year
        $stmt = $db->prepare("
            SELECT COUNT(*) as count
            FROM chart_ledger
            WHERE fiscal_year = ?
            AND app_no != 'YEARSTART'
        ");
        $stmt->execute(array($next_year_id));
        $transCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        if ($transCount > 0) {
            $can_rollback = false;
            $blocking_reasons[] = "The next fiscal year already has $transCount manual transaction(s). You cannot rollback a year if the subsequent year has active data.";
        }
    }

    // Count closing entries to be removed
    $stmt = $db->prepare("
        SELECT COUNT(*) as count, SUM(cr_amt) as total_cr, SUM(dr_amt) as total_dr
        FROM chart_ledger
        WHERE fiscal_year = ? AND app_no = 'YEAREND'
    ");
    $stmt->execute(array($fiscal_year_id));
    $closing = $stmt->fetch(PDO::FETCH_ASSOC);
    $closing_entries_count = $closing['count'];

    // Count opening entries to be removed
    $opening_entries_count = 0;
    if ($next_year_id) {
        $stmt = $db->prepare("
            SELECT COUNT(*) as count
            FROM chart_ledger
            WHERE fiscal_year = ? AND app_no = 'YEARSTART'
        ");
        $stmt->execute(array($next_year_id));
        $opening = $stmt->fetch(PDO::FETCH_ASSOC);
        $opening_entries_count = $opening['count'];
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'can_rollback' => $can_rollback,
            'blocking_reasons' => $blocking_reasons,
            'closing_entries_to_delete' => $closing_entries_count,
            'opening_entries_to_delete' => $opening_entries_count,
            'next_year_to_delete' => $next_year_id ? true : false,
            'manual_transactions_blocking' => $transCount
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
