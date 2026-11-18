<?php

declare(strict_types=1);

/**
 *   php bin/setup_deploy_webhook_secret.php
 *   php bin/setup_deploy_webhook_secret.php --secret='PASTE_GITHUB_SECRET'
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(1);
}

$projectRoot = dirname(__DIR__);
$dataDir = $projectRoot . '/data';
$secretFile = $dataDir . '/deploy_webhook_secret';

if (!is_dir($dataDir) && !mkdir($dataDir, 0775, true) && !is_dir($dataDir)) {
    fwrite(STDERR, "Cannot create data dir\n");
    exit(1);
}

$provided = null;
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--secret=')) {
        $provided = substr($arg, strlen('--secret='));
    }
}

$secret = $provided !== null && $provided !== ''
    ? trim($provided)
    : bin2hex(random_bytes(32));

if ($secret === '') {
    fwrite(STDERR, "Secret is empty\n");
    exit(1);
}

if (file_put_contents($secretFile, $secret) === false) {
    fwrite(STDERR, "Failed writing {$secretFile}\n");
    exit(1);
}

chmod($secretFile, 0600);
$wwwUser = getenv('DEPLOY_WWW_USER') ?: 'www-data';
if (function_exists('posix_getpwnam') && posix_getpwnam($wwwUser)) {
    @chown($secretFile, $wwwUser);
}

echo "Wrote: {$secretFile}\n";
echo "Length: " . strlen($secret) . "\n\n";
echo "1) GitHub → Settings → Webhooks → Add/Edit:\n";
echo "   Payload URL: https://docs.stream-platform.cloud/deploy_webhook.php\n";
echo "   Content type: application/json\n";
echo "   Secret: paste the line below\n";
echo "   Events: Just the push event\n";
echo "2) Save, then Redeliver / push to main.\n";
echo "3) Logs: data/deploy-logs/\n\n";
echo $secret . "\n";
