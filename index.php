<?php
// ======================
// CONFIG
// ======================
$token = "8235597889:AAHgRf4fyUW3oVI5ytlqFbO-eaANTESk_q4"; // توکن ربات از BotFather
$api   = "https://api.telegram.org/bot{$token}";
$logFile = __DIR__ . "/tg_log.txt";

$raw = file_get_contents("php://input");
file_put_contents($logFile, "-----\n" . date("Y-m-d H:i:s") . "\n" . $raw . "\n", FILE_APPEND);

$update = json_decode($raw, true);
if (!$update) exit;

$chat_id = $update["message"]["chat"]["id"] ?? null;
$text    = $update["message"]["text"] ?? null;

if (!$chat_id || $text === null) exit;

$text = trim($text);

$reply = null;
if ($text === "سلام") {
    $reply = "درود بر شما";
} else {
    // برای تست اینکه اصلا پیام می‌رسه:
    $reply = "پیامت رسید: " . $text;
}

$url = $api . "/sendMessage";
$postData = ["chat_id" => $chat_id, "text" => $reply];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
$res = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

file_put_contents($logFile, "SEND_RES:\n" . $res . "\nCURL_ERR:\n" . $err . "\n", FILE_APPEND);
