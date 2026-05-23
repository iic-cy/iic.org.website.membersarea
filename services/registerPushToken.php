<?php
header('Content-Type: application/json');

include("servicesConfig.php");

$data = [
    'result' => 0,
    'error' => '',
    'data' => []
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $data['error'] = 'Method not allowed';
    echo json_encode($data);
    exit;
}

$expoToken = trim($_POST['expo_token'] ?? '');
$platform = trim($_POST['platform'] ?? '');

$validToken = preg_match('/^(ExponentPushToken|ExpoPushToken)\[[^\]]+\]$/', $expoToken) === 1;
$validPlatform = in_array($platform, ['ios', 'android'], true);

if (!$validToken || !$validPlatform) {
    http_response_code(400);
    $data['error'] = 'Invalid parameters';
    echo json_encode($data);
    exit;
}

$stmt = null;

try {
    $sql = "INSERT INTO iic_push_tokens (expo_token, platform, last_activity_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE platform = VALUES(platform), updated_at = NOW(), last_activity_at = NOW()";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $mysqli->error);
    }

    $stmt->bind_param("ss", $expoToken, $platform);

    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }

    $data['result'] = 1;
    $data['data'] = [
        'expo_token' => $expoToken,
        'platform' => $platform
    ];
} catch (Exception $e) {
    error_log('registerPushToken error: ' . $e->getMessage());
    http_response_code(500);
    $data['error'] = 'Database request failed';
} finally {
    if ($stmt !== null) {
        $stmt->close();
    }
}

echo json_encode($data);
?>