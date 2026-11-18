<?php

declare(strict_types=1);

/**
 * GitHub push webhook → background deploy.sh (git pull).
 *
 * POST https://docs.stream-platform.cloud/deploy_webhook.php
 *
 * Setup: ../docs/auto-deploy.md or php bin/setup_deploy_webhook_secret.php
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function deploy_webhook_respond(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function deploy_webhook_secret(): string
{
    $fromEnv = trim((string)(getenv('DEPLOY_WEBHOOK_SECRET') ?: ''));
    if ($fromEnv !== '') {
        return $fromEnv;
    }

    $file = dirname(__DIR__) . '/data/deploy_webhook_secret';
    if (is_readable($file)) {
        return trim((string)file_get_contents($file));
    }

    return '';
}

function deploy_webhook_decode_payload(string $raw, string $contentType): ?array
{
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        return $decoded;
    }

    if (stripos($contentType, 'application/x-www-form-urlencoded') !== false) {
        $form = [];
        parse_str($raw, $form);
        if (!empty($form['payload']) && is_string($form['payload'])) {
            $fromForm = json_decode($form['payload'], true);
            if (is_array($fromForm)) {
                return $fromForm;
            }
        }
    }

    return null;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Hub-Signature-256, X-GitHub-Event');
    http_response_code(204);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    deploy_webhook_respond(405, ['ok' => false, 'error' => 'method_not_allowed']);
}

$secret = deploy_webhook_secret();
if ($secret === '') {
    deploy_webhook_respond(503, [
        'ok' => false,
        'error' => 'deploy_secret_not_set',
        'hint' => 'On the server: php bin/setup_deploy_webhook_secret.php then paste into GitHub → Webhooks (application/json).',
    ]);
}

$raw = file_get_contents('php://input');
if ($raw === false || $raw === '') {
    deploy_webhook_respond(400, ['ok' => false, 'error' => 'empty_body']);
}

$signatureHeader = (string)($_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '');
if (!preg_match('/^sha256=([a-f0-9]+)$/i', $signatureHeader, $matches)) {
    deploy_webhook_respond(401, [
        'ok' => false,
        'error' => 'missing_signature',
        'hint' => 'GitHub must send X-Hub-Signature-256. Set a Secret on the webhook.',
    ]);
}

$expected = hash_hmac('sha256', $raw, $secret);
if (!hash_equals(strtolower($expected), strtolower($matches[1]))) {
    deploy_webhook_respond(401, [
        'ok' => false,
        'error' => 'invalid_signature',
        'hint' => 'Server secret does not match GitHub webhook Secret. Content type must be application/json.',
        'content_type' => (string)($_SERVER['CONTENT_TYPE'] ?? ''),
        'secret_length' => strlen($secret),
    ]);
}

$event = strtolower(trim((string)($_SERVER['HTTP_X_GITHUB_EVENT'] ?? '')));
if ($event === 'ping') {
    deploy_webhook_respond(200, ['ok' => true, 'message' => 'pong']);
}

if ($event !== '' && $event !== 'push') {
    deploy_webhook_respond(200, ['ok' => true, 'ignored' => true, 'reason' => 'event_not_push', 'event' => $event]);
}

$contentType = (string)($_SERVER['CONTENT_TYPE'] ?? '');
$payload = deploy_webhook_decode_payload($raw, $contentType) ?? [];

$ref = (string)($payload['ref'] ?? '');
$allowedRefs = ['refs/heads/main', 'refs/heads/master'];
if ($ref !== '' && !in_array($ref, $allowedRefs, true)) {
    deploy_webhook_respond(200, [
        'ok' => true,
        'ignored' => true,
        'reason' => 'branch_not_deployed',
        'ref' => $ref,
    ]);
}

$projectRoot = dirname(__DIR__);
$deployScript = $projectRoot . '/deploy.sh';
if (!is_file($deployScript)) {
    deploy_webhook_respond(500, ['ok' => false, 'error' => 'deploy_sh_missing']);
}

$logDir = $projectRoot . '/data/deploy-logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0775, true);
}
$logFile = $logDir . '/deploy-' . gmdate('Ymd-His') . '.log';
$lockFile = $projectRoot . '/data/deploy.lock';

$cmd = sprintf(
    'nohup flock -n %s bash -lc %s >> %s 2>&1 & echo $!',
    escapeshellarg($lockFile),
    escapeshellarg('cd ' . escapeshellarg($projectRoot) . ' && git pull && bash ./deploy.sh --no-pull'),
    escapeshellarg($logFile)
);

$pid = trim((string)shell_exec($cmd));
if ($pid === '') {
    deploy_webhook_respond(200, [
        'ok' => true,
        'queued' => false,
        'message' => 'Deploy already running (lock busy)',
        'log' => basename($logFile),
    ]);
}

deploy_webhook_respond(202, [
    'ok' => true,
    'started' => true,
    'pid' => $pid,
    'ref' => $ref,
    'after' => (string)($payload['after'] ?? ''),
    'log' => basename($logFile),
]);
