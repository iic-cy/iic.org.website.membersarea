<?php
header('Content-Type: application/json');
include("servicesConfig.php");
error_log('cronDailyNewsNotification started');

function fetchJsonFromUrl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);

    $response = curl_exec($ch);
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('cURL request failed: ' . $error);
    }

    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($statusCode !== 200) {
        throw new Exception('HTTP request failed with status ' . $statusCode);
    }

    $decoded = json_decode($response, true);
    if (!is_array($decoded)) {
        throw new Exception('Invalid JSON response from ' . $url);
    }

    return $decoded;
}

function getAllTokens($mysqli) {
    $tokens = [];

    $sql = "SELECT expo_token FROM iic_push_tokens";
    $result = $mysqli->query($sql);
    if (!$result) {
        throw new Exception('Token query failed: ' . $mysqli->error);
    }

    while ($row = $result->fetch_assoc()) {
        if (!empty($row['expo_token'])) {
            $tokens[] = $row['expo_token'];
        }
    }

    return $tokens;
}

function splitIntoChunks($items, $size) {
    if (empty($items)) {
        return [];
    }
    return array_chunk($items, $size);
}

function normalizePostTitle($post) {
    $raw = $post['title']['rendered'] ?? '';
    $decoded = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return trim(strip_tags($decoded));
}

function getRecentPosts($posts, $thresholdUtc) {
    $recent = [];

    foreach ($posts as $post) {
        if (!isset($post['date_gmt'])) {
            continue;
        }

        try {
            $postDateUtc = new DateTime($post['date_gmt'], new DateTimeZone('UTC'));
        } catch (Exception $e) {
            continue;
        }

        if ($postDateUtc > $thresholdUtc) {
            $recent[] = [
                'id' => $post['id'] ?? null,
                'title' => normalizePostTitle($post),
                'date_gmt' => $post['date_gmt']
            ];
        }
    }

    return $recent;
}

function buildNotificationBody($titles) {
    $count = count($titles);
    if ($count === 0) {
        return 'New IIC news alerts are available.';
    }

    if ($count === 1) {
        return $titles[0];
    }

    $preview = array_slice($titles, 0, 3);
    $body = implode(' | ', $preview);

    if ($count > 3) {
        $body .= ' | +' . ($count - 3) . ' more';
    }

    return $body;
}

function sendExpoPushBatch($messages, $expoAccessToken = '') {
    $url = 'https://exp.host/--/api/v2/push/send';

    $headers = [
        'Accept: application/json',
        'Accept-Encoding: gzip, deflate',
        'Content-Type: application/json'
    ];

    if (!empty($expoAccessToken)) {
        $headers[] = 'Authorization: Bearer ' . $expoAccessToken;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messages));

    $response = curl_exec($ch);
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('Expo send failed: ' . $error);
    }

    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($statusCode < 200 || $statusCode >= 300) {
        throw new Exception('Expo send failed with HTTP status ' . $statusCode);
    }

    $decoded = json_decode($response, true);
    if (!is_array($decoded) || !isset($decoded['data']) || !is_array($decoded['data'])) {
        throw new Exception('Invalid Expo send response');
    }

    return $decoded['data'];
}

function fetchExpoReceipts($receiptIds, $expoAccessToken = '') {
    if (empty($receiptIds)) {
        return [];
    }

    $url = 'https://exp.host/--/api/v2/push/getReceipts';

    $headers = [
        'Accept: application/json',
        'Accept-Encoding: gzip, deflate',
        'Content-Type: application/json'
    ];

    if (!empty($expoAccessToken)) {
        $headers[] = 'Authorization: Bearer ' . $expoAccessToken;
    }

    $payload = ['ids' => array_values($receiptIds)];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

    $response = curl_exec($ch);
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('Expo receipts request failed: ' . $error);
    }

    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($statusCode < 200 || $statusCode >= 300) {
        throw new Exception('Expo receipts failed with HTTP status ' . $statusCode);
    }

    $decoded = json_decode($response, true);
    if (!is_array($decoded) || !isset($decoded['data']) || !is_array($decoded['data'])) {
        throw new Exception('Invalid Expo receipts response');
    }

    return $decoded['data'];
}

function deleteTokens($mysqli, $tokens) {
    if (empty($tokens)) {
        return 0;
    }

    $deleted = 0;
    $stmt = $mysqli->prepare("DELETE FROM iic_push_tokens WHERE expo_token = ?");
    if (!$stmt) {
        throw new Exception('Prepare delete failed: ' . $mysqli->error);
    }

    foreach (array_unique($tokens) as $token) {
        $stmt->bind_param("s", $token);
        if ($stmt->execute()) {
            $deleted += $stmt->affected_rows;
        }
    }

    $stmt->close();
    return $deleted;
}

function touchTokens($mysqli, $tokens) {
    if (empty($tokens)) {
        return 0;
    }

    $updated = 0;
    $stmt = $mysqli->prepare("UPDATE iic_push_tokens SET last_activity_at = NOW(), updated_at = NOW() WHERE expo_token = ?");
    if (!$stmt) {
        throw new Exception('Prepare update failed: ' . $mysqli->error);
    }

    foreach (array_unique($tokens) as $token) {
        $stmt->bind_param("s", $token);
        if ($stmt->execute()) {
            $updated += $stmt->affected_rows;
        }
    }

    $stmt->close();
    return $updated;
}

$result = [
    'result' => 0,
    'error' => '',
    'data' => [
        'articles_found' => 0,
        'tokens_found' => 0,
        'notifications_attempted' => 0,
        'tickets_received' => 0,
        'receipts_checked' => 0,
        'invalid_tokens_deleted' => 0,
        'tokens_touched' => 0,
        'run_time_utc' => gmdate('Y-m-d H:i:s')
    ]
];

try {
    $utcNow = new DateTime('now', new DateTimeZone('UTC'));
    $cyprusNow = (clone $utcNow)->setTimezone(new DateTimeZone('Europe/Nicosia'));
    $thresholdCyprus = (clone $cyprusNow)->modify('-24 hours');
    $thresholdUtc = (clone $thresholdCyprus)->setTimezone(new DateTimeZone('UTC'));

    $result['data']['window_start_utc'] = $thresholdUtc->format('Y-m-d H:i:s');
    $result['data']['window_end_utc'] = $utcNow->format('Y-m-d H:i:s');

    $posts = fetchJsonFromUrl('https://iic.org.cy/wp-json/wp/v2/posts?per_page=20');
    $recentPosts = getRecentPosts($posts, $thresholdUtc);
    $result['data']['articles_found'] = count($recentPosts);

    if (count($recentPosts) === 0) {
        $result['result'] = 1;
        $result['data']['message'] = 'No new posts in the last 24 hours.';
        echo json_encode($result);
        exit;
    }

    $tokens = getAllTokens($mysqli);
    $result['data']['tokens_found'] = count($tokens);

    if (count($tokens) === 0) {
        $result['result'] = 1;
        $result['data']['message'] = 'No registered Expo tokens.';
        echo json_encode($result);
        exit;
    }

    $titles = [];
    $postIds = [];
    foreach ($recentPosts as $post) {
        if (!empty($post['title'])) {
            $titles[] = $post['title'];
        }
        if (!empty($post['id'])) {
            $postIds[] = $post['id'];
        }
    }

    $notificationTitle = 'IIC News Alerts (' . count($recentPosts) . ')';
    $notificationBody = buildNotificationBody($titles);
    $badgeCount = count($recentPosts);

    $expoAccessToken = $SETTINGS['expo_access_token'] ?? '';

    $receiptIdToToken = [];
    $tokensToDelete = [];

    $tokenChunks = splitIntoChunks($tokens, 100);
    foreach ($tokenChunks as $tokenChunk) {
        $messages = [];

        foreach ($tokenChunk as $token) {
            $messages[] = [
                'to' => $token,
                'sound' => 'default',
                'title' => $notificationTitle,
                'body' => $notificationBody,
                'badge' => $badgeCount,
                'channelId' => 'default',
                'priority' => 'high',
                'data' => [
                    'type' => 'news_alerts',
                    'post_ids' => $postIds,
                    'count' => $badgeCount
                ]
            ];
        }

        $tickets = sendExpoPushBatch($messages, $expoAccessToken);
        $result['data']['notifications_attempted'] += count($messages);
        $result['data']['tickets_received'] += count($tickets);

        foreach ($tickets as $index => $ticket) {
            $token = $tokenChunk[$index] ?? null;
            if ($token === null) {
                continue;
            }

            $status = $ticket['status'] ?? '';
            if ($status === 'ok' && !empty($ticket['id'])) {
                $receiptIdToToken[$ticket['id']] = $token;
                continue;
            }

            if ($status === 'error') {
                $ticketError = $ticket['details']['error'] ?? '';
                if ($ticketError === 'DeviceNotRegistered') {
                    $tokensToDelete[] = $token;
                }
            }
        }
    }

    // Expo receipts can become available shortly after ticket creation.
    usleep(500000);

    $receiptIds = array_keys($receiptIdToToken);
    $receiptChunks = splitIntoChunks($receiptIds, 300);

    foreach ($receiptChunks as $receiptChunk) {
        $receipts = fetchExpoReceipts($receiptChunk, $expoAccessToken);
        $result['data']['receipts_checked'] += count($receipts);

        foreach ($receipts as $receiptId => $receipt) {
            $status = $receipt['status'] ?? '';
            if ($status !== 'error') {
                continue;
            }

            $receiptError = $receipt['details']['error'] ?? '';
            if ($receiptError === 'DeviceNotRegistered') {
                $token = $receiptIdToToken[$receiptId] ?? null;
                if (!empty($token)) {
                    $tokensToDelete[] = $token;
                }
            }
        }
    }

    $result['data']['invalid_tokens_deleted'] = deleteTokens($mysqli, $tokensToDelete);
    $result['data']['tokens_touched'] = touchTokens($mysqli, $tokens);

    $result['result'] = 1;
    $result['data']['message'] = 'Daily notifications completed.';

    error_log('cronDailyNewsNotification completed: ' . json_encode($result['data']));
    echo json_encode($result);
    echo "\n";
    
} catch (Exception $e) {
    http_response_code(500);
    $result['result'] = 0;
    $result['error'] = $e->getMessage();
    error_log('cronDailyNewsNotification failed: ' . $e->getMessage());
    echo json_encode($result);
}
?>
