<?php
// Test conexión Telegram
$ch = curl_init("https://api.telegram.org");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
if ($res === false) {
    echo "Telegram ERROR: " . curl_error($ch) . "\n";
} else {
    echo "Telegram OK\n";
}
curl_close($ch);

// Test conexión Stripe
$ch = curl_init("https://api.stripe.com");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
if ($res === false) {
    echo "Stripe ERROR: " . curl_error($ch) . "\n";
} else {
    echo "Stripe OK\n";
}
curl_close($ch);
?>
