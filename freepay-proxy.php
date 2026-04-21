<?php
/**
 * FreePay Brasil — Proxy Backend
 * Coloque este arquivo no seu servidor junto ao index.html
 * URL: https://seusite.com/freepay-proxy.php
 */

// ═══ SUAS CREDENCIAIS ════════════════════════════════════════════
define('FREEPAY_PUBLIC_KEY', 'freepay_live_4goIIsfQXV13wkEX95fIbpe9eIhETIfN');
define('FREEPAY_SECRET_KEY', 'sk_live_B6wTubWqdBiZdKo1vvZA2bT3UFeTrGcg');
define('FREEPAY_CREATE_URL', 'https://api.freepaybrasil.com/v1/payment-transaction/create');
define('FREEPAY_STATUS_URL', 'https://api.freepaybrasil.com/v1/payment-transaction');
// ═════════════════════════════════════════════════════════════════

// CORS — permite chamadas do seu próprio site
header('Access-Control-Allow-Origin: *'); // em produção, troque * pelo seu domínio
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Autenticação Basic: base64(PUBLIC_KEY:SECRET_KEY)
$auth = base64_encode(FREEPAY_PUBLIC_KEY . ':' . FREEPAY_SECRET_KEY);

$action = $_GET['action'] ?? '';

// ─── CRIAR TRANSAÇÃO (PIX ou Cartão) ────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
    $body = file_get_contents('php://input');

    $ch = curl_init(FREEPAY_CREATE_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Basic ' . $auth,
        ],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT        => 30,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        http_response_code(500);
        echo json_encode(['error' => 'cURL error: ' . $curlErr]);
        exit();
    }

    http_response_code($httpCode);
    echo $response;
    exit();
}

// ─── CONSULTAR STATUS ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'status') {
    $id = $_GET['id'] ?? '';
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'id obrigatório']);
        exit();
    }

    $ch = curl_init(FREEPAY_STATUS_URL . '/' . urlencode($id));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Basic ' . $auth,
        ],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT        => 15,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        http_response_code(500);
        echo json_encode(['error' => 'cURL error: ' . $curlErr]);
        exit();
    }

    http_response_code($httpCode);
    echo $response;
    exit();
}

http_response_code(404);
echo json_encode(['error' => 'Rota não encontrada. Use ?action=create ou ?action=status&id=XXX']);
