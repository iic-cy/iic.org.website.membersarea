<?php
header('Content-Type: application/json');

include("../servicesConfig.php");

$data = [
    'result' => 0,
    'error'  => '',
    'data'   => []
];

// Secret must be set via Apache SetEnv (or server env) as VERIFY_TEST_KEY.
// If the env var is absent or empty the endpoint always returns 403.
$expectedKey  = getenv('VERIFY_TEST_KEY');
$providedKey  = $_SERVER['HTTP_X_TEST_KEY'] ?? '';

if ($expectedKey === false || $expectedKey === '' || $providedKey !== $expectedKey) {
    http_response_code(403);
    $data['error'] = 'Forbidden';
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    $data['error'] = 'Method not allowed';
    echo json_encode($data);
    exit;
}

$expoToken = trim($_GET['expo_token'] ?? '');

if ($expoToken === '') {
    http_response_code(400);
    $data['error'] = 'Missing expo_token parameter';
    echo json_encode($data);
    exit;
}

try {
    $stmt = $mysqli->prepare(
        'SELECT expo_token, platform FROM iic_push_tokens WHERE expo_token = ? LIMIT 1'
    );
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $mysqli->error);
    }

    $stmt->bind_param('s', $expoToken);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $data['result'] = 1;
    $data['data']   = [
        'exists'      => $row !== null,
        'expo_token'  => $row['expo_token'] ?? null,
        'platform'    => $row['platform']    ?? null,
    ];
} catch (Exception $e) {
    error_log('verifyPushToken error: ' . $e->getMessage());
    http_response_code(500);
    $data['error'] = 'Database request failed';
}

echo json_encode($data);
?>
