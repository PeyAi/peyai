<?php
// ======================
// CONFIG
// ======================
$token = "8235597889:AAHgRf4fyUW3oVI5ytlqFwwbO-eaANTESk_q4"; // توکن ربات از BotFather
$api   = "https://api.telegram.org/bot{$token}";
$SOURCE_USERNAME = "TSdayan";     // بدون @
$TARGET_CHANNEL  = "@paroo_podcast";    // با @
$TARGET_TAG      = "@paroo_podcast";    // متنی که پایین پیام میاد

$LOG_FILE = __DIR__ . "/tg_channel_log.txt";

// ======================
// READ UPDATE + LOG
// ======================
$raw = file_get_contents("php://input");
file_put_contents($LOG_FILE, "-----\n" . date("Y-m-d H:i:s") . "\n" . $raw . "\n", FILE_APPEND);

$update = json_decode($raw, true);
if (!$update) exit;

// هم پست جدید، هم ادیت پست کانال (اگر خواستی می‌تونی فقط channel_post بذاری)
$post = $update["channel_post"] ?? $update["edited_channel_post"] ?? null;
if (!$post) exit;

// ======================
// VALIDATE SOURCE CHANNEL
// ======================
$chatUsername = $post["chat"]["username"] ?? "";
if (strtolower($chatUsername) !== strtolower($SOURCE_USERNAME)) {
    // از کانال دیگه‌ای آمده
    exit;
}

// ======================
// GET TEXT (text OR caption)
// ======================
$text = $post["text"] ?? $post["caption"] ?? "";
$text = trim($text);
if ($text === "") exit;

// نرمال‌سازی خط جدیدها
$text = str_replace(["\r\n", "\r"], "\n", $text);

// ======================
// FILTER BY FORMAT
// قالب مورد انتظار (منعطف):
// line1: هر متن (مثل آبشده)
// سپس یک یا چند خط خالی
// سپس 🔴فروش <عدد با , یا بدون>
// سپس یک یا چند خط خالی
// سپس @TSdayan (یا با فاصله)
// ======================
$pattern = '/^(?<title>.+?)\n+\s*🔴\s*فروش\s*(?<price>[\d,]+)\s*\n+\s*@TSdayan\s*$/u';

if (!preg_match($pattern, $text, $m)) {
    // فرمت مدنظر نبود
    exit;
}

$title = trim($m["title"]);
$price = trim($m["price"]);

// ساخت متن خروجی با تغییر آیدی پایین
$newText = $title . "\n\n🔴فروش " . $price . "\n\n" . $TARGET_TAG;

// ======================
// SEND TO TARGET CHANNEL
// ======================
$payload = [
    "chat_id" => $TARGET_CHANNEL,
    "text"    => $newText,
];

$ch = curl_init($API . "/sendMessage");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
$res = curl_exec($ch);
$err = curl_error($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// لاگ نتیجه ارسال
file_put_contents($LOG_FILE, "SEND_HTTP: {$http}\nSEND_ERR: {$err}\nSEND_RES: {$res}\n", FILE_APPEND);
