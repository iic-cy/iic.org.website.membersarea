<?php
header('Content-Type: application/json');

include("servicesConfig.php");

$result = [
    'result' => 0,
    'error' => '',
    'data' => [
        'stale_tokens_deleted' => 0,
        'threshold_days' => 60,
        'run_time_utc' => gmdate('Y-m-d H:i:s')
    ]
];

try {
    $sixtyDaysAgo = gmdate('Y-m-d H:i:s', strtotime('-60 days'));
    $result['data']['threshold_date_utc'] = $sixtyDaysAgo;

    $stmt = $mysqli->prepare(
        "DELETE FROM iic_push_tokens WHERE last_activity_at < ?"
    );

    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $mysqli->error);
    }

    $stmt->bind_param("s", $sixtyDaysAgo);

    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }

    $deleted = $stmt->affected_rows;
    $stmt->close();

    $result['result'] = 1;
    $result['data']['stale_tokens_deleted'] = $deleted;
    $result['data']['message'] = $deleted > 0
        ? "Deleted {$deleted} stale token(s) inactive for 60+ days."
        : 'No stale tokens found.';

    error_log('cronCleanupStaleTokens completed: ' . json_encode($result['data']));
    echo json_encode($result);

} catch (Exception $e) {
    http_response_code(500);
    $result['result'] = 0;
    $result['error'] = $e->getMessage();
    error_log('cronCleanupStaleTokens failed: ' . $e->getMessage());
    echo json_encode($result);
}
?>
