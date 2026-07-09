<?php
// ============================================================
// Zentra – Google Service Account JWT Authentication
// No Composer required — uses openssl_sign() for RS256
// ============================================================

/**
 * Get a valid Google OAuth2 access token for the given scope.
 * Caches tokens in the google_tokens DB table until they expire.
 */
function google_get_access_token(string $scope): string {
    require_once __DIR__ . '/db.php';
    $db = db();

    // Check cache
    $stmt = $db->prepare('SELECT access_token, expires_at FROM google_tokens WHERE scope = ?');
    $stmt->execute([$scope]);
    $cached = $stmt->fetch();

    if ($cached && strtotime($cached['expires_at']) > time() + 60) {
        return $cached['access_token'];
    }

    // Read service account key
    $keyFile = GOOGLE_SERVICE_ACCOUNT_JSON;
    if (!file_exists($keyFile)) {
        throw new RuntimeException('Google service account key file not found');
    }

    $sa = json_decode(file_get_contents($keyFile), true);
    if (!$sa || empty($sa['private_key']) || empty($sa['client_email']) || empty($sa['token_uri'])) {
        throw new RuntimeException('Invalid service account key file');
    }

    // Build JWT
    $now = time();
    $header = base64url_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
    $claims = base64url_encode(json_encode([
        'iss'   => $sa['client_email'],
        'scope' => $scope,
        'aud'   => $sa['token_uri'],
        'iat'   => $now,
        'exp'   => $now + 3600,
    ]));

    $input = $header . '.' . $claims;
    $privateKey = openssl_pkey_get_private($sa['private_key']);
    if (!$privateKey) {
        throw new RuntimeException('Failed to parse private key from service account');
    }

    openssl_sign($input, $signature, $privateKey, 'SHA256');
    $jwt = $input . '.' . base64url_encode($signature);

    // Exchange JWT for access token
    $ch = curl_init($sa['token_uri']);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
    ]);
    $resp = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        error_log('Google token exchange failed: HTTP ' . $httpCode . ' | ' . substr($resp, 0, 500));
        throw new RuntimeException('Google OAuth token exchange failed (HTTP ' . $httpCode . ')');
    }

    $data = json_decode($resp, true);
    if (empty($data['access_token'])) {
        throw new RuntimeException('No access_token in Google OAuth response');
    }

    $token     = $data['access_token'];
    $expiresIn = (int)($data['expires_in'] ?? 3600);
    $expiresAt = date('Y-m-d H:i:s', $now + $expiresIn);

    // Cache token (upsert)
    $db->prepare(
        'INSERT INTO google_tokens (scope, access_token, expires_at)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE access_token = VALUES(access_token), expires_at = VALUES(expires_at)'
    )->execute([$scope, $token, $expiresAt]);

    return $token;
}

/**
 * Make an authenticated POST request to a Google API endpoint.
 */
function google_api_post(string $url, array $body, string $scope): array {
    $token = google_get_access_token($scope);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($body),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
    ]);
    $resp     = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 400) {
        error_log("Google API POST $url failed: HTTP $httpCode | " . substr($resp, 0, 500));
        return ['error' => true, 'http_code' => $httpCode, 'body' => $resp];
    }
    return json_decode($resp, true) ?: [];
}

/**
 * Base64 URL-safe encoding (no padding).
 */
function base64url_encode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
