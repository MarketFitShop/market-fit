<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger datos del formulario
    $nombre = htmlspecialchars($_POST['nombre'] ?? '');
    $apellido = htmlspecialchars($_POST['apellido'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $mensaje = htmlspecialchars($_POST['mensaje'] ?? '');

    // Verificar que todos los campos estén llenos
    if (empty($nombre) || empty($apellido) || empty($email) || empty($mensaje)) {
        echo "Por favor, completa todos los campos del formulario.";
        exit;
    }

    // Formato del mensaje para guardar en archivo
    $entrada = "Nombre: $nombre | Apellido: $apellido | Email: $email | Mensaje: $mensaje" . PHP_EOL;

    // Guardar en mensajes.txt
    file_put_contents("mensajes.txt", $entrada, FILE_APPEND | LOCK_EX);

    // Datos para enviar a Telegram
    $chat_id = "7748207562";  // Tu chat ID real
    $bot_token = "8167531945:AAFCT_d39aZ5INh183kCTZhxupOB7PqPVAo";  // Tu token real

    // Mensaje que se enviará por Telegram
    $text = "📩 <b>Nuevo mensaje de contacto</b>\n";
    $text .= "<b>Nombre:</b> $nombre\n";
    $text .= "<b>Apellido:</b> $apellido\n";
    $text .= "<b>Email:</b> $email\n";
    $text .= "<b>Mensaje:</b> $mensaje";

    // URL y parámetros para Telegram
    $url = "https://api.telegram.org/bot$bot_token/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML'
    ];

    // Enviar solicitud con cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Opcional: comentar estas dos líneas en producción con SSL válido
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    $response = curl_exec($ch);

    if ($response === false) {
        file_put_contents("telegram_error_log.txt", "cURL error: " . curl_error($ch) . PHP_EOL, FILE_APPEND);
    }

    curl_close($ch);

    $telegram_response = json_decode($response, true);

    if (!is_array($telegram_response) || empty($telegram_response["ok"])) {
        file_put_contents("telegram_error_log.txt", "Error en respuesta Telegram:\n" . $response . PHP_EOL, FILE_APPEND);
    }

    // Confirmación para el usuario
    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <title>Mensaje enviado</title>
        <link rel='stylesheet' href='style.css'>
    </head>
    <body>
        <div class='confirmacion' style='padding:40px; text-align:center; font-family:sans-serif;'>
            <h2>¡Gracias por contactarnos, $nombre!</h2>
            <p>Hemos recibido tu mensaje y te responderemos pronto.</p>
            <a href='index.html' style='display:inline-block; margin-top:20px; padding:10px 20px; background:#007bff; color:#fff; border-radius:5px; text-decoration:none;'>Volver al inicio</a>
        </div>
    </body>
    </html>";

} else {
    // Si se accede sin enviar formulario, redirigir
    header("Location: index.html");
    exit();
}
?>
