<?php
require 'vendor/autoload.php'; // Carga Stripe SDK

\Stripe\Stripe::setApiKey('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');

header('Content-Type: application/json');

try {
    // Recoger datos del formulario
    $nombre = $_POST['nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $telefono = ($_POST['prefijo'] ?? '') . ($_POST['telefono'] ?? '');
    $calle = $_POST['calle'] ?? '';
    $apartamento = $_POST['apartamento'] ?? '';
    $provincia = $_POST['provincia'] ?? '';
    $municipio = $_POST['municipio'] ?? '';
    $codigo_postal = $_POST['codigo_postal'] ?? '';
    $email = $_POST['email'] ?? '';
    $notas = $_POST['notas'] ?? '';

    // Recibir carrito JSON desde el input oculto
    $productos_json = $_POST['productos_json'] ?? '[]';
    $productos = json_decode($productos_json, true);

    if (!$nombre || !$apellido || !$telefono || !$calle || !$provincia || !$municipio || !$codigo_postal || !$email) {
        throw new Exception('Faltan datos obligatorios.');
    }

    if (!$productos || !is_array($productos) || count($productos) === 0) {
        throw new Exception('El carrito está vacío.');
    }

    // Crear items para Stripe
    $line_items = [];
    foreach ($productos as $item) {
        if (!isset($item['nombre'], $item['precio'], $item['cantidad'])) {
            throw new Exception('Producto inválido en carrito.');
        }

        $line_items[] = [
            'price_data' => [
                'currency' => 'eur',
                'product_data' => [
                    'name' => $item['nombre'],
                ],
                'unit_amount' => intval(round($item['precio'] * 100)),
            ],
            'quantity' => intval($item['cantidad']),
        ];
    }

    // Crear sesión de pago Stripe
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => $line_items,
        'mode' => 'payment',
        'success_url' => 'https://tu_dominio.com/gracias.html?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'https://tu_dominio.com/cancelado.html',
        'customer_email' => $email,
        'metadata' => [
            'nombre' => $nombre,
            'apellido' => $apellido,
            'telefono' => $telefono,
            'direccion' => $calle . ' ' . $apartamento . ', ' . $municipio . ', ' . $provincia . ', CP ' . $codigo_postal,
            'notas' => $notas
        ],
    ]);

    // ============================
    // GUARDAR PEDIDO Y ENVIAR POR TELEGRAM
    // ============================

    $fecha_hora = date('Y-m-d H:i:s');
    $mensaje = "🛒 *Nuevo pedido recibido: [$fecha_hora]*\n\n";
    $mensaje .= "👤 Nombre: $nombre $apellido\n";
    $mensaje .= "📞 Teléfono: $telefono\n";
    $mensaje .= "📧 Email: $email\n";
    $mensaje .= "📝 Notas: $notas\n\n";
    $mensaje .= "📍 Dirección:\n";
    $mensaje .= "$calle, Apt: $apartamento\n";
    $mensaje .= "$municipio, $provincia - $codigo_postal\n\n";
    $mensaje .= "📦 Productos:\n";

    $total = 0;
    foreach ($productos as $item) {
        $subtotal = $item['precio'] * $item['cantidad'];
        $total += $subtotal;
        $mensaje .= "- {$item['nombre']} x {$item['cantidad']} = " . number_format($subtotal, 2) . "€\n";
    }

    $mensaje .= "\n💰 Total: " . number_format($total, 2) . "€\n";
    $mensaje .= "🔗 Enlace de pago: {$session->url}\n";
    $mensaje .= str_repeat("=", 50) . "\n\n";

    // Guardar en archivo
    file_put_contents("pedidos.txt", $mensaje, FILE_APPEND);

    // Enviar por Telegram
    $bot_token = "7521899276:AAFDpSU8HZcxfhumJr2vA9K0V2z_994A0Ao"; // <-- Reemplaza con tu token
    $chat_id = "7748207562"; // <-- Reemplaza con tu chat ID
    $url = "https://api.telegram.org/bot$bot_token/sendMessage";
    file_get_contents($url . "?" . http_build_query([
        'chat_id' => $chat_id,
        'text' => $mensaje,
        'parse_mode' => 'Markdown'
    ]));

    // Respuesta con URL de Stripe
    echo json_encode([
        'status' => 'ok',
        'url_pago_stripe' => $session->url
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
