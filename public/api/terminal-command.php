<?php
declare(strict_types=1);

require dirname(__DIR__) . '/includes/autoload.php';

use LastJob\Rules;
use LastJob\Terminal\TerminalCommandRouter;
use LastJob\Terminal\TerminalState;

header('Content-Type: application/json; charset=utf-8');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

try {
    $raw = file_get_contents('php://input');
    $payload = json_decode((string) $raw, true);
    if (!is_array($payload)) {
        $payload = $_POST;
    }

    $command = (string) ($payload['command'] ?? '');
    $state = TerminalState::fromSession(is_array($_SESSION['terminal_state'] ?? null) ? $_SESSION['terminal_state'] : null);
    $router = new TerminalCommandRouter(new Rules());
    $response = $router->handle($state, $command);
    $_SESSION['terminal_state'] = $state->toArray();

    echo json_encode($response, JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'error' => $e->getMessage(),
        'lines' => ['terminal fault: ' . $e->getMessage()],
    ], JSON_UNESCAPED_SLASHES);
}
