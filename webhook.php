<?php
/**
 * GitHub Webhook – משיכה אוטומטית (git pull) בעת push לריפו
 *
 * הגדרה ב-GitHub: Settings → Webhooks → Add webhook
 * Payload URL: https://your-domain.com/webhook.php
 * Content type: application/json
 * Secret: בחר סיסמה והזן אותה ב-WEBHOOK_SECRET למטה
 */

// ============ הגדרות ============
$WEBHOOK_SECRET = getenv('GITHUB_WEBHOOK_SECRET') ?: '12345';
$REPO_PATH      = __DIR__;  // תיקיית הפרויקט (או נתיב מלא, למשל /var/www/doc.trustgrade.cloud)
$BRANCH         = 'main';   // סניף למשיכה (main או master)
$LOG_FILE       = __DIR__ . '/webhook.log';

// ============ פונקציות ============
function logMessage($msg) {
    global $LOG_FILE;
    $line = date('Y-m-d H:i:s') . ' ' . $msg . "\n";
    @file_put_contents($LOG_FILE, $line, FILE_APPEND | LOCK_EX);
}

function respond($code, $body) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($body);
    exit;
}

// רק POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['ok' => false, 'error' => 'Method not allowed']);
}

// קבלת גוף הבקשה
$rawInput = file_get_contents('php://input');
if ($rawInput === false || $rawInput === '') {
    respond(400, ['ok' => false, 'error' => 'Empty body']);
}

// אימות חתימה (אם הוגדר Secret ב-GitHub)
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
if ($WEBHOOK_SECRET !== '' && $WEBHOOK_SECRET !== '12345') {
    $expected = 'sha256=' . hash_hmac('sha256', $rawInput, $WEBHOOK_SECRET);
    if (!hash_equals($expected, $signature)) {
        logMessage('Webhook rejected: invalid signature');
        respond(403, ['ok' => false, 'error' => 'Invalid signature']);
    }
}

$payload = json_decode($rawInput, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    respond(400, ['ok' => false, 'error' => 'Invalid JSON']);
}

// רק אירועי push
$event = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? '';
if ($event !== 'push') {
    respond(200, ['ok' => true, 'message' => 'Ignored event: ' . $event]);
}

$ref = $payload['ref'] ?? '';
// ref בצורה refs/heads/main
if ($ref !== 'refs/heads/' . $BRANCH) {
    logMessage("Ignored ref: $ref (expected refs/heads/$BRANCH)");
    respond(200, ['ok' => true, 'message' => "Ignored branch: $ref"]);
}

// ביצוע git pull
$repoPath = realpath($REPO_PATH);
if (!$repoPath || !is_dir($repoPath . '/.git')) {
    logMessage("Invalid repo path or not a git repo: $REPO_PATH");
    respond(500, ['ok' => false, 'error' => 'Invalid repository path']);
}

$cmd = sprintf('cd %s && git pull origin %s 2>&1', escapeshellarg($repoPath), escapeshellarg($BRANCH));
exec($cmd, $output, $returnCode);

$outputStr = implode("\n", $output);
logMessage("git pull exit code: $returnCode\n$outputStr");

if ($returnCode !== 0) {
    respond(500, [
        'ok'   => false,
        'error' => 'git pull failed',
        'code'  => $returnCode,
        'output' => $outputStr,
    ]);
}

respond(200, [
    'ok'     => true,
    'message' => 'Pull completed',
    'output'  => $outputStr,
]);
