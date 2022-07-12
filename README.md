Telegram Bot
============
Telegram bot yaratish uchun qulaylashtirilgan kengaytma

O'rnatish
------------
Kengaytmani [composer](http://getcomposer.org/download/) orqali o'rnatishingiz mumkin.

```
composer require uzdevid/yii2-telegrambot "dev-main"
```

Foydalanish
------------

Konfiguratsiya parametrlarini kiritish.
--------------------------------------

```php
$config = [
    'token' => '<token>',
    'parse_mode' => 'markdown',
    'webhook_url' => '<webhook url>'
];
```

Agar habarlarni faqat bitta chat-ga yuborqmoqchi bo'lsangiz ushbu massivga **chat_id** ni ham qo'shishingiz mumkin.

Bot class-ini yaratish.
-----------------------

```php
$bot = new TelegramBot($config);
```

Chat-ga oddiy habar yuborish.
-----------------------------

```php
$bot->send(['chat_id' => 1234567, 'text' => 'Hello world']);
```

Agar **chat_id** konfiguratsiyada ko'rsatilgan bo'lsa va yuborish uchun faqat matn berilsa quyidagi usuldan
foydalanishingiz mumkin

```php
$bot->send(['Hello world']);
```

Habarni o'zgartirish
--------------------

```php
$bot->edit(['message_id' => 1020, 'text' => "Yangi matn"]);
```

Tugmalar yaratish
-----------------

Tugmalar yaratish

```php
$bot->createKeyboard([
    [['text' => "Button 1"]],
    [['text' => "Button 2"]]
]);
$bot->send(['chat_id' => 1234567, 'text' => 'Mesage with url buttons']);
```

Habar ostida (inline) tugmalar yaratish

```php
$bot->createInlineKeyboard([
    [['text' => "Button 1", 'url' => "https://google.com"]],
    [['text' => "Button 2", 'url' => "https://devid.uz"]]
]);
$bot->send(['chat_id' => 1234567, 'text' => 'Mesage with url buttons']);
```

Callback tugmalar yaratish

```php
$bot->createInlineKeyboard([
    [['text' => "Callback Button 1", 'callback_data' => json_encode(['command' => '/btn-1'])]]
]);
$bot->send(['chat_id' => 1234567, 'text' => 'Mesage with callback']);
```

callback_data paramtriga **command**-dan tashqari qo'shimcha paramtrlar qo'shishingiz mumkin. Misol uchun: id

```php
$bot->createInlineKeyboard([
    [[
        'text' => "Callback Button 1",
        'callback_data' => json_encode(['command' => '/btn-1', 'id' => 100])
    ]]
]);
$bot->send(['chat_id' => 1234567, 'text' => 'Mesage with callback button and id']);
```

Webhook o'rnatish va o'chirish
------------------------------
Webhook o'rnatishda va o'chirishda konfiguratsiyada webhook_url ko'rsatilgan bo'lishi shart. Qolgan holatlarda shart
emas.

Webhook o'rnatish

```php
$bot->setWebHook();
```

Webhook o'chirish

```php
$bot->deleteWebHook();
```

Bot foydalanuvchisidan kelgan so'rovlarni qabul qilish, va javob qaytarish.
---------------------------------------------------------------------------

```php
$bot->onMessage('/start', function ($bot) {
    $bot->send("Chat ID: {$bot->chat_id}");
});
```

Callback tugmasi orqali kelgan so'rovni qayta ishlash.

```php
$bot->onCallBack('/btn-1', function ($bot, $callback_data){
    $bot->send("Siz /btn-1 tugmasini bosdingiz. Tugma id-si: " . $callback_data['id']);
});
```

Ko'rib turganingizdek bu ikkala metod ichida **send** orqali habar yuboryatganingizda **chat_id** ko'rsatishingiz shart
emas. Qaysi foydalanuvchidan so'rov kelsa, javob ham huddi shu foydalanuvchiga qaytadi.