<?php
// ======================
// CONFIG
// ======================
$token = "8235597889:AAHgRf4fyUW3oVI5ytlqFbO-eaANTESk_q4"; // توکن ربات از BotFather
$api   = "https://api.telegram.org/bot{$token}";
$update = json_decode(file_get_contents("php://input"), true);

// فقط پیام‌های کانال
if (!isset($update["channel_post"])) {
    exit;
}

$post = $update["channel_post"];
$text = $post["text"] ?? "";

// فقط از کانال مبدا
if (($post["chat"]["username"] ?? "") !== "TSdayan") {
    exit;
}

// الگوی مورد نظر
$pattern = "/^(.*?)\n\n🔴فروش\s([\d,]+)\n\n@TSdayan$/u";

if (preg_match($pattern, $text, $matches)) {

    $title = trim($matches[1]);      // آبشده
    $price = trim($matches[2]);      // 80,405

    // ساخت متن جدید
    $newText = $title . "\n\n🔴فروش " . $price . "\n\n@aeinweb";

    // ارسال به کانال مقصد
    file_get_contents($api . "/sendMessage?" . http_build_query([
        "chat_id" => "@aeinweb",
        "text"    => $newText
    ]));
}

