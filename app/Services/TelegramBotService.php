<?php

namespace App\Services;

use Telegram\Bot\Api;
use Telegram\Bot\Keyboard\Keyboard;
use App\Models\User;
use App\Models\Shop;
use App\Models\Product;
use App\Models\ProductComment;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class TelegramBotService
{
    protected $telegram;

    public function __construct()
    {
        $this->telegram = new Api(config('telegram.bots.alqorshop.token'));
    }

    public function handleUpdate($update)
    {
        try {
            if (isset($update['message'])) {
                $this->handleMessage($update['message']);
            } elseif (isset($update['callback_query'])) {
                $this->handleCallbackQuery($update['callback_query']);
            }
        } catch (\Exception $e) {
            Log::error('Bot Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    protected function handleMessage($message)
    {
        $chatId = $message['chat']['id'];
        $telegramId = $message['from']['id'];
        $text = $message['text'] ?? '';

        $user = User::where('telegram_id', $telegramId)->first();

        // Photo handling
        if (isset($message['photo']) && $user && $user->state === 'adding_product_photo') {
            $this->handleProductPhoto($message);
            return;
        }

        // State-based message handling
        if ($user && $user->state) {
            $this->handleStateMessage($message, $user);
            return;
        }

        // Commands
        switch ($text) {
            case '/start':
                $this->handleStart($message);
                break;

            case '🏪 Do\'konlar':
            case '/shops':
                $this->showShops($chatId);
                break;

            case '🛒 Savat':
            case '/cart':
                $this->showCart($chatId, $telegramId);
                break;

            case '📦 Buyurtmalarim':
                $this->showMyOrders($chatId, $telegramId);
                break;

            case '/sotuvchi':
            case '💼 Sotuvchi paneli':
                $this->showSellerMenu($chatId, $telegramId);
                break;
        }
    }

        protected function handleCallbackQuery($callbackQuery)
{
    $chatId = $callbackQuery['message']['chat']['id'];
    $messageId = $callbackQuery['message']['message_id'];
    $telegramId = $callbackQuery['from']['id'];
    $data = $callbackQuery['data'];

    Log::info("Callback: $data from user: $telegramId");

    $this->telegram->answerCallbackQuery([
        'callback_query_id' => $callbackQuery['id']
    ]);

    // Role selection
    if ($data === 'role_buyer') {
        $this->registerUser($telegramId, $callbackQuery['from'], 'buyer');
        $this->editMessage($chatId, $messageId,
            "✅ <b>Siz xaridor sifatida ro'yxatdan o'tdingiz!</b>\n\n" .
            "Endi do'konlardan mahsulot xarid qilishingiz mumkin."
        );
        sleep(1);
        $this->showMainMenu($chatId);
    }
    elseif ($data === 'role_seller') {
        $this->registerUser($telegramId, $callbackQuery['from'], 'seller');
        $this->editMessage($chatId, $messageId,
            "✅ <b>Siz sotuvchi sifatida ro'yxatdan o'tdingiz!</b>\n\n" .
            "Endi do'kon yaratishingiz kerak."
        );
        sleep(1);
        $this->requestShopName($chatId, $telegramId);
    }
    // Shop with pagination
    elseif (str_starts_with($data, 'shop_')) {
        $parts = explode('_', $data);
        $shopId = $parts[1];
        $page = isset($parts[2]) ? (int)$parts[2] : 1;
        $this->showShopProductsPaginated($chatId, $messageId, $shopId, $page);
    }
    // Comments with pagination (BU PRODUCT_DAN OLDIN BO'LISHI KERAK!)
    elseif (str_starts_with($data, 'comments_')) {
        $parts = explode('_', $data);
        $productId = $parts[1];
        $page = isset($parts[2]) ? (int)$parts[2] : 1;
        $this->showAllComments($chatId, $messageId, $productId, $page);
    }
    // Add comment (BU HAM PRODUCT_DAN OLDIN!)
    elseif (str_starts_with($data, 'comment_')) {
        $productId = str_replace('comment_', '', $data);
        $this->startAddComment($chatId, $telegramId, $productId);
    }
    // Product detail (ENG OXIRDA!)
    elseif (str_starts_with($data, 'product_')) {
        $productId = str_replace('product_', '', $data);
        $this->showProductWithComments($chatId, $messageId, $productId);
    }
    // Cart
    elseif (str_starts_with($data, 'addcart_')) {
        $productId = str_replace('addcart_', '', $data);
        $this->addToCart($chatId, $telegramId, $productId);
    }
    elseif (str_starts_with($data, 'cart_plus_')) {
        $productId = str_replace('cart_plus_', '', $data);
        $this->updateCartQuantity($chatId, $messageId, $telegramId, $productId, 1);
    }
    elseif (str_starts_with($data, 'cart_minus_')) {
        $productId = str_replace('cart_minus_', '', $data);
        $this->updateCartQuantity($chatId, $messageId, $telegramId, $productId, -1);
    }
    elseif (str_starts_with($data, 'cart_remove_')) {
        $productId = str_replace('cart_remove_', '', $data);
        $this->removeFromCart($chatId, $messageId, $telegramId, $productId);
    }
    elseif ($data === 'checkout') {
        $this->startCheckout($chatId, $telegramId);
    }
    elseif ($data === 'clear_cart') {
        $this->clearCart($chatId, $telegramId);
    }
    // Seller
    elseif ($data === 'seller_menu') {
        $this->showSellerMenu($chatId, $telegramId);
    }
    elseif ($data === 'add_product') {
        $this->startAddProduct($chatId, $telegramId);
    }
    elseif ($data === 'my_products') {
        $this->showMyProducts($chatId, $telegramId);
    }
    elseif ($data === 'seller_orders') {
        $this->showSellerOrders($chatId, $telegramId);
    }
    elseif ($data === 'stats') {
        $this->showStatistics($chatId, $telegramId);
    }
    // Product management
    elseif (str_starts_with($data, 'edit_product_')) {
        $productId = str_replace('edit_product_', '', $data);
        $this->editProduct($chatId, $messageId, $productId);
    }
    elseif (str_starts_with($data, 'delete_product_')) {
        $productId = str_replace('delete_product_', '', $data);
        $this->deleteProduct($chatId, $messageId, $telegramId, $productId);
    }
    elseif (str_starts_with($data, 'toggle_product_')) {
        $productId = str_replace('toggle_product_', '', $data);
        $this->toggleProductAvailability($chatId, $messageId, $productId);
    }
    // Orders
    elseif (str_starts_with($data, 'order_confirm_')) {
        $orderId = str_replace('order_confirm_', '', $data);
        $this->confirmOrder($chatId, $messageId, $orderId);
    }
    elseif (str_starts_with($data, 'order_cancel_')) {
        $orderId = str_replace('order_cancel_', '', $data);
        $this->cancelOrder($chatId, $messageId, $orderId);
    }
    elseif (str_starts_with($data, 'reply_')) {
        $commentId = str_replace('reply_', '', $data);
        $this->startReplyToComment($chatId, $telegramId, $commentId);
    }
    elseif ($data === 'back_to_shops') {
        $this->showShops($chatId);
        try {
            $this->telegram->deleteMessage([
                'chat_id' => $chatId,
                'message_id' => $messageId
            ]);
        } catch (\Exception $e) {
            // Ignore
        }
    }
    elseif ($data === 'noop') {
        // Do nothing - just for display
    }
    elseif ($data === 'become_seller_yes') {
        $user = User::where('telegram_id', $telegramId)->first();
        if ($user) {
            $user->update(['role' => 'seller']);
            $this->editMessage($chatId, $messageId, "✅ Siz sotuvchi sifatida ro'yxatdan o'tdingiz!");
            sleep(1);
            $this->requestShopName($chatId, $telegramId);
        }
    }
    elseif ($data === 'become_seller_no') {
        $this->editMessage($chatId, $messageId, "Asosiy menyuga xush kelibsiz!");
        sleep(1);
        $this->showMainMenu($chatId);
    }
}
    protected function startReplyToComment($chatId, $telegramId, $commentId)
{
    $user = User::where('telegram_id', $telegramId)->first();
    $comment = ProductComment::find($commentId);

    if (!$comment) {
        $this->sendMessage($chatId, "❌ Izoh topilmadi");
        return;
    }

    $product = $comment->product;
    $originalUsername = $this->formatUsername($comment);

    $tempData = [
        'product_id' => $product->id,
        'parent_comment_id' => $commentId
    ];

    $user->update([
        'state' => 'replying_to_comment',
        'temp_data' => $tempData
    ]);

    $this->sendMessage($chatId,
        "↩️ <b>Javob yozish</b>\n\n" .
        "📦 Mahsulot: {$product->name}\n" .
        "💬 Javob: {$originalUsername}\n" .
        "📝 {$comment->comment}\n\n" .
        "Iltimos, javobingizni yozing:"
    );
}
    // ==================== USER REGISTRATION ====================

    protected function handleStart($message)
    {
        $chatId = $message['chat']['id'];
        $telegramId = $message['from']['id'];
        $user = User::where('telegram_id', $telegramId)->first();

        if (!$user) {
            $keyboard = Keyboard::make()
                ->inline()
                ->row([
                    Keyboard::inlineButton([
                        'text' => '🛍️ Xaridor',
                        'callback_data' => 'role_buyer'
                    ]),
                    Keyboard::inlineButton([
                        'text' => '🏪 Sotuvchi',
                        'callback_data' => 'role_seller'
                    ]),
                ]);

            $this->sendMessage($chatId,
                "🌙 <b>Alqor Shop ga xush kelibsiz!</b>\n\n" .
                "📱 Qishloq mahsulotlarini sotish va sotib olish uchun eng qulay platforma.\n\n" .
                "Siz kim sifatida foydalanmoqchisiz?",
                $keyboard
            );
        } else {
            $this->showMainMenu($chatId);
        }
    }

    protected function registerUser($telegramId, $from, $role)
    {
        User::updateOrCreate(
            ['telegram_id' => $telegramId],
            [
                'username' => $from['username'] ?? null,
                'full_name' => $from['first_name'] . ' ' . ($from['last_name'] ?? ''),
                'role' => $role,
                'state' => null,
                'temp_data' => null,
            ]
        );

        Log::info("User registered: $telegramId as $role");
    }

    // ==================== MAIN MENU ====================

    protected function showMainMenu($chatId)
    {
        $keyboard = [
            'keyboard' => [
                [
                    ['text' => '🏪 Do\'konlar'],
                    ['text' => '🛒 Savat'],
                ],
                [
                    ['text' => '📦 Buyurtmalarim'],
                    ['text' => '💼 Sotuvchi paneli'],
                ]
            ],
            'resize_keyboard' => true,
        ];

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "🏠 <b>Asosiy menyu</b>\n\nQuyidagi bo'limlardan birini tanlang:",
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode($keyboard)
        ]);
    }

    // ==================== SHOPS ====================

    protected function showShops($chatId)
    {
        $shops = Shop::where('is_active', true)->get();

        if ($shops->isEmpty()) {
            $this->sendMessage($chatId, "😔 Hozircha faol do'konlar yo'q");
            return;
        }

        $keyboard = Keyboard::make()->inline();

        foreach ($shops as $shop) {
            $productCount = $shop->products()->where('is_available', true)->count();
            $keyboard->row([
                Keyboard::inlineButton([
                    'text' => "🏪 {$shop->name} ({$productCount} ta mahsulot)",
                    'callback_data' => 'shop_' . $shop->id . '_1'
                ])
            ]);
        }

        $this->sendMessage($chatId,
            "🏪 <b>Do'konlar ro'yxati</b>\n\n" .
            "Qaysi do'kondan xarid qilmoqchisiz?",
            $keyboard
        );
    }

    protected function showShopProductsPaginated($chatId, $messageId, $shopId, $page = 1)
    {
        $shop = Shop::find($shopId);

        if (!$shop) {
            $this->editMessage($chatId, $messageId, "❌ Do'kon topilmadi");
            return;
        }

        $perPage = 20;
        $products = $shop->products()
            ->where('is_available', true)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        if ($products->isEmpty()) {
            $this->editMessage($chatId, $messageId,
                "😔 <b>{$shop->name}</b>\n\nHozircha mahsulotlar yo'q"
            );
            return;
        }

        // Delete old message
        try {
            $this->telegram->deleteMessage([
                'chat_id' => $chatId,
                'message_id' => $messageId
            ]);
        } catch (\Exception $e) {
            Log::warning('Could not delete message');
        }

        // Header
        $headerText = "🏪 <b>{$shop->name}</b>\n\n";
        if ($shop->description) {
            $headerText .= "{$shop->description}\n\n";
        }
        $headerText .= "📦 Sahifa {$page} / " . $products->lastPage() . "\n";
        $headerText .= "Jami: {$products->total()} ta mahsulot\n\n";

        $this->sendMessage($chatId, $headerText);

        // Send products
        foreach ($products as $product) {
            $this->sendProductCard($chatId, $product, $shopId, $page);
            usleep(200000); // 0.2 second delay
        }

        // Pagination buttons
        $keyboard = Keyboard::make()->inline();
        $row = [];

        if ($products->currentPage() > 1) {
            $row[] = Keyboard::inlineButton([
                'text' => '◀️ Oldingi',
                'callback_data' => 'shop_' . $shopId . '_' . ($page - 1)
            ]);
        }

        $row[] = Keyboard::inlineButton([
            'text' => "📄 {$page}/{$products->lastPage()}",
            'callback_data' => 'noop'
        ]);

        if ($products->hasMorePages()) {
            $row[] = Keyboard::inlineButton([
                'text' => 'Keyingi ▶️',
                'callback_data' => 'shop_' . $shopId . '_' . ($page + 1)
            ]);
        }

        if (!empty($row)) {
            $keyboard->row($row);
        }

        $keyboard->row([
            Keyboard::inlineButton([
                'text' => '◀️ Do\'konlar',
                'callback_data' => 'back_to_shops'
            ])
        ]);

        $this->sendMessage($chatId,
            "━━━━━━━━━━━━━━━\nSahifa {$page} / {$products->lastPage()}",
            $keyboard
        );
    }

    protected function sendProductCard($chatId, $product, $shopId, $page)
    {
        $keyboard = Keyboard::make()
            ->inline()
            ->row([
                Keyboard::inlineButton([
                    'text' => '👁 Batafsil',
                    'callback_data' => 'product_' . $product->id
                ]),
                Keyboard::inlineButton([
                    'text' => '🛒 Savat',
                    'callback_data' => 'addcart_' . $product->id
                ])
            ]);

        $commentsCount = $product->comments()->count();
        if ($commentsCount > 0) {
            $keyboard->row([
                Keyboard::inlineButton([
                    'text' => "💬 {$commentsCount} ta izoh",
                    'callback_data' => 'comments_' . $product->id
                ])
            ]);
        }

        $text = "📦 <b>{$product->name}</b>\n\n";
        if ($product->description) {
            $text .= mb_substr($product->description, 0, 100);
            if (mb_strlen($product->description) > 100) {
                $text .= "...";
            }
            $text .= "\n\n";
        }
        $text .= "💰 Narxi: <b>{$product->formatted_price}</b>";

        if ($commentsCount > 0) {
            $text .= "\n💬 Izohlar: {$commentsCount} ta";
        }

        try {
            $this->telegram->sendPhoto([
                'chat_id' => $chatId,
                'photo' => $product->image_url,
                'caption' => $text,
                'parse_mode' => 'HTML',
                'reply_markup' => $keyboard
            ]);
        } catch (\Exception $e) {
            Log::error('Product card error: ' . $e->getMessage());
            $this->sendMessage($chatId, $text, $keyboard);
        }
    }

    // ==================== COMMENTS ====================

    protected function showProductWithComments($chatId, $messageId, $productId)
    {
        $product = Product::with(['comments' => function($q) {
            $q->latest()->take(3);
        }])->find($productId);

        if (!$product) {
            $this->editMessage($chatId, $messageId, "❌ Mahsulot topilmadi");
            return;
        }

        $keyboard = Keyboard::make()
            ->inline()
            ->row([
                Keyboard::inlineButton([
                    'text' => '🛒 Savatga qo\'shish',
                    'callback_data' => 'addcart_' . $product->id
                ])
            ]);

        $commentsCount = $product->comments()->count();
        if ($commentsCount > 0) {
            $keyboard->row([
                Keyboard::inlineButton([
                    'text' => "💬 Barcha izohlar ({$commentsCount})",
                    'callback_data' => 'comments_' . $product->id
                ])
            ]);
        }

        $keyboard->row([
            Keyboard::inlineButton([
                'text' => '✍️ Izoh qoldirish',
                'callback_data' => 'comment_' . $product->id
            ])
        ]);

        $keyboard->row([
            Keyboard::inlineButton([
                'text' => '◀️ Orqaga',
                'callback_data' => 'shop_' . $product->shop_id . '_1'
            ])
        ]);

        $text = "📦 <b>{$product->name}</b>\n\n";

        if ($product->description) {
            $text .= "{$product->description}\n\n";
        }

        $text .= "💰 Narxi: <b>{$product->formatted_price}</b>\n";
        $text .= "🏪 Do'kon: {$product->shop->name}\n\n";

        if ($product->comments->count() > 0) {
            $text .= "💬 <b>So'nggi izohlar:</b>\n\n";

            foreach ($product->comments as $comment) {
                $text .= "👤 {$comment->user_name}\n";
                $text .= "   {$comment->comment}\n";
                $text .= "   <i>" . $comment->created_at->diffForHumans() . "</i>\n\n";
            }

            if ($commentsCount > 3) {
                $text .= "... va yana " . ($commentsCount - 3) . " ta izoh";
            }
        }

        try {
            $this->telegram->sendPhoto([
                'chat_id' => $chatId,
                'photo' => $product->image_url,
                'caption' => $text,
                'parse_mode' => 'HTML',
                'reply_markup' => $keyboard
            ]);

            $this->telegram->deleteMessage([
                'chat_id' => $chatId,
                'message_id' => $messageId
            ]);
        } catch (\Exception $e) {
            $this->editMessage($chatId, $messageId, $text, $keyboard);
        }
    }
    protected function showAllComments($chatId, $messageId, $productId, $page = 1)
{
    $product = Product::find($productId);

    if (!$product) {
        try {
            $this->telegram->deleteMessage([
                'chat_id' => $chatId,
                'message_id' => $messageId
            ]);
        } catch (\Exception $e) {
            // Ignore
        }
        $this->sendMessage($chatId, "❌ Mahsulot topilmadi");
        return;
    }

    $perPage = 10;

    // Faqat top-level comments (reply bo'lmaganlar)
    $comments = $product->comments()
        ->topLevel()
        ->with('replies', 'user')
        ->latest()
        ->paginate($perPage, ['*'], 'page', $page);

    try {
        $this->telegram->deleteMessage([
            'chat_id' => $chatId,
            'message_id' => $messageId
        ]);
    } catch (\Exception $e) {
        Log::warning('Could not delete message');
    }

    if ($comments->isEmpty()) {
        $keyboard = Keyboard::make()
            ->inline()
            ->row([
                Keyboard::inlineButton([
                    'text' => '✍️ Birinchi bo\'lib izoh qoldiring',
                    'callback_data' => 'comment_' . $product->id
                ])
            ])
            ->row([
                Keyboard::inlineButton([
                    'text' => '◀️ Orqaga',
                    'callback_data' => 'product_' . $product->id
                ])
            ]);

        $this->sendMessage($chatId,
            "💬 <b>Izohlar</b>\n\n" .
            "Hali izohlar yo'q. Birinchi bo'lib izoh qoldiring!",
            $keyboard
        );
        return;
    }

    $totalComments = $product->comments()->count();
    $text = "💬 <b>Barcha izohlar ({$totalComments} ta)</b>\n\n";
    $text .= "📦 {$product->name}\n";
    $text .= "📄 Sahifa {$page} / {$comments->lastPage()}\n\n";

    $startIndex = ($page - 1) * $perPage;

    foreach ($comments as $index => $comment) {
        $commentNumber = $startIndex + $index + 1;

        // Username with link
        $username = $this->formatUsername($comment);

        $text .= "{$commentNumber}. {$username}\n";

        // Comment text
        $commentText = $comment->comment;
        if (mb_strlen($commentText) > 200) {
            $commentText = mb_substr($commentText, 0, 200) . "...";
        }
        $text .= "   {$commentText}\n";
        $text .= "   <i>" . $comment->created_at->format('d.m.Y H:i') . "</i>\n";

        // Replies
        $repliesCount = $comment->replies()->count();
        if ($repliesCount > 0) {
            $text .= "   💬 {$repliesCount} ta javob\n";

            // Show first 2 replies
            $replies = $comment->replies()->latest()->take(2)->get();
            foreach ($replies as $reply) {
                $replyUsername = $this->formatUsername($reply);
                $text .= "     ↳ {$replyUsername}: {$reply->comment}\n";
            }

            if ($repliesCount > 2) {
                $text .= "     <i>... va yana " . ($repliesCount - 2) . " ta javob</i>\n";
            }
        }

        $text .= "\n";
    }

    // Buttons
    $keyboard = Keyboard::make()->inline();

    // Comment buttons (for reply)
    $buttonRow = [];
    foreach ($comments as $index => $comment) {
        $commentNumber = $startIndex + $index + 1;
        $buttonRow[] = Keyboard::inlineButton([
            'text' => "↩️ {$commentNumber}",
            'callback_data' => 'reply_' . $comment->id
        ]);

        // 5 ta tugmadan keyin yangi qator
        if (count($buttonRow) == 5) {
            $keyboard->row($buttonRow);
            $buttonRow = [];
        }
    }

    if (!empty($buttonRow)) {
        $keyboard->row($buttonRow);
    }

    // Pagination
    $row = [];
    if ($comments->currentPage() > 1) {
        $row[] = Keyboard::inlineButton([
            'text' => '◀️ Oldingi',
            'callback_data' => 'comments_' . $productId . '_' . ($page - 1)
        ]);
    }

    if ($comments->total() > $perPage) {
        $row[] = Keyboard::inlineButton([
            'text' => "📄 {$page}/{$comments->lastPage()}",
            'callback_data' => 'noop'
        ]);
    }

    if ($comments->hasMorePages()) {
        $row[] = Keyboard::inlineButton([
            'text' => 'Keyingi ▶️',
            'callback_data' => 'comments_' . $productId . '_' . ($page + 1)
        ]);
    }

    if (!empty($row)) {
        $keyboard->row($row);
    }

    // Main buttons
    $keyboard->row([
        Keyboard::inlineButton([
            'text' => '✍️ Yangi izoh',
            'callback_data' => 'comment_' . $product->id
        ])
    ]);

    $keyboard->row([
        Keyboard::inlineButton([
            'text' => '◀️ Orqaga',
            'callback_data' => 'product_' . $product->id
        ])
    ]);

    $this->sendMessage($chatId, $text, $keyboard);
}

// Helper: Format username with link
protected function formatUsername($comment)
{
    $user = $comment->user;

    if ($user && $user->username) {
        // Telegram username link
        return "<a href='tg://user?id={$comment->user_telegram_id}'>@{$user->username}</a>";
    } else {
        // Just name (clickable link by telegram_id)
        return "<a href='tg://user?id={$comment->user_telegram_id}'>{$comment->user_name}</a>";
    }
}


    protected function startAddComment($chatId, $telegramId, $productId)
    {
        $user = User::where('telegram_id', $telegramId)->first();
        $product = Product::find($productId);

        if (!$product) {
            $this->sendMessage($chatId, "❌ Mahsulot topilmadi");
            return;
        }

        $tempData = ['product_id' => $productId];

        $user->update([
            'state' => 'adding_comment',
            'temp_data' => $tempData
        ]);

        $this->sendMessage($chatId,
            "✍️ <b>Izoh qoldirish</b>\n\n" .
            "📦 Mahsulot: {$product->name}\n\n" .
            "Iltimos, izohingizni yozing:"
        );
    }



    // ==================== CART ====================

    protected function addToCart($chatId, $telegramId, $productId)
    {
        $product = Product::find($productId);

        if (!$product || !$product->is_available) {
            $this->sendMessage($chatId, "❌ Mahsulot mavjud emas");
            return;
        }

        $cart = Cart::where('telegram_id', $telegramId)
            ->where('product_id', $productId)
            ->first();

        if ($cart) {
            $cart->increment('quantity');
            $this->sendMessage($chatId, "✅ Mahsulot miqdori oshirildi!");
        } else {
            Cart::create([
                'telegram_id' => $telegramId,
                'shop_id' => $product->shop_id,
                'product_id' => $productId,
                'quantity' => 1
            ]);
            $this->sendMessage($chatId, "✅ Mahsulot savatga qo'shildi!");
        }
    }

    protected function showCart($chatId, $telegramId)
    {
        $cartItems = Cart::where('telegram_id', $telegramId)
            ->with('product')
            ->get();

        if ($cartItems->isEmpty()) {
            $this->sendMessage($chatId, "🛒 Savatingiz bo'sh");
            return;
        }

        $text = "🛒 <b>Savatingiz:</b>\n\n";
        $total = 0;
        $keyboard = Keyboard::make()->inline();

        foreach ($cartItems as $item) {
            $subtotal = $item->product->price * $item->quantity;
            $total += $subtotal;

            $text .= "• {$item->product->name}\n";
            $text .= "  {$item->quantity} x " . number_format($item->product->price, 0, '.', ' ') . " = ";
            $text .= number_format($subtotal, 0, '.', ' ') . " so'm\n\n";

            $keyboard->row([
                Keyboard::inlineButton([
                    'text' => '➖',
                    'callback_data' => 'cart_minus_' . $item->product_id
                ]),
                Keyboard::inlineButton([
                    'text' => " ({$item->quantity})".$item->product->name,
                    'callback_data' => 'noop'
                ]),
                Keyboard::inlineButton([
                    'text' => '➕',
                    'callback_data' => 'cart_plus_' . $item->product_id
                ]),
                Keyboard::inlineButton([
                    'text' => '🗑',
                    'callback_data' => 'cart_remove_' . $item->product_id
                ]),
            ]);
        }

        $text .= "━━━━━━━━━━━━━━━\n";
        $text .= "💰 <b>Jami: " . number_format($total, 0, '.', ' ') . " so'm</b>";

        $keyboard->row([
            Keyboard::inlineButton([
                'text' => '✅ Buyurtma berish',
                'callback_data' => 'checkout'
            ])
        ]);
        $keyboard->row([
            Keyboard::inlineButton([
                'text' => '🗑 Savatni tozalash',
                'callback_data' => 'clear_cart'
            ])
        ]);

        $this->sendMessage($chatId, $text, $keyboard);
    }

    protected function updateCartQuantity($chatId, $messageId, $telegramId, $productId, $change)
    {
        $cart = Cart::where('telegram_id', $telegramId)
            ->where('product_id', $productId)
            ->first();

        if (!$cart) {
            return;
        }

        if ($change > 0) {
            $cart->increment('quantity', $change);
        } else {
            if ($cart->quantity + $change <= 0) {
                $cart->delete();
            } else {
                $cart->decrement('quantity', abs($change));
            }
        }

        $this->showCart($chatId, $telegramId);

        try {
            $this->telegram->deleteMessage([
                'chat_id' => $chatId,
                'message_id' => $messageId
            ]);
        } catch (\Exception $e) {
            // Ignore
        }
    }

    protected function removeFromCart($chatId, $messageId, $telegramId, $productId)
    {
        Cart::where('telegram_id', $telegramId)
            ->where('product_id', $productId)
            ->delete();

        $this->showCart($chatId, $telegramId);

        try {
            $this->telegram->deleteMessage([
                'chat_id' => $chatId,
                'message_id' => $messageId
            ]);
        } catch (\Exception $e) {
            // Ignore
        }
    }

    protected function clearCart($chatId, $telegramId)
    {
        Cart::where('telegram_id', $telegramId)->delete();
        $this->sendMessage($chatId, "✅ Savat tozalandi");
    }

    // ==================== CHECKOUT ====================

    protected function startCheckout($chatId, $telegramId)
    {
        $user = User::where('telegram_id', $telegramId)->first();

        $cartItems = Cart::where('telegram_id', $telegramId)->count();

        if ($cartItems == 0) {
            $this->sendMessage($chatId, "❌ Savatingiz bo'sh");
            return;
        }

        $user->update(['state' => 'checkout_name']);

        $this->sendMessage($chatId,
            "📝 <b>Buyurtma berish</b>\n\n" .
            "Iltimos, ismingizni kiriting:"
        );
    }

    // ==================== SELLER ====================

    protected function requestShopName($chatId, $telegramId)
    {
        $user = User::where('telegram_id', $telegramId)->first();
        $user->update(['state' => 'waiting_shop_name']);

        $this->sendMessage($chatId,
            "🏪 <b>Do'kon yaratish</b>\n\n" .
            "Do'koningiz nomini kiriting:\n\n" .
            "<i>Masalan: Fermer Mahsulotlari, Organik Do'kon</i>"
        );
    }
    protected function saveReply($chatId, $user, $text)
{
    if (mb_strlen($text) < 2) {
        $this->sendMessage($chatId, "❌ Javob juda qisqa. Qaytadan yozing:");
        return;
    }

    if (mb_strlen($text) > 500) {
        $this->sendMessage($chatId, "❌ Javob juda uzun (maksimal 500 ta belgi). Qisqartiring:");
        return;
    }

    $tempData = $user->temp_data;
    $productId = $tempData['product_id'];
    $parentCommentId = $tempData['parent_comment_id'];

    $product = Product::find($productId);
    $parentComment = ProductComment::find($parentCommentId);

    if (!$product || !$parentComment) {
        $this->sendMessage($chatId, "❌ Xatolik yuz berdi");
        $user->update(['state' => null, 'temp_data' => null]);
        return;
    }

    // Save reply
    $reply = ProductComment::create([
        'product_id' => $productId,
        'parent_id' => $parentCommentId,
        'user_telegram_id' => $user->telegram_id,
        'user_name' => $user->full_name,
        'comment' => $text
    ]);

    $user->update([
        'state' => null,
        'temp_data' => null
    ]);

    $this->sendMessage($chatId,
        "✅ <b>Javob qo'shildi!</b>\n\n" .
        "Rahmat!"
    );

    // Notify original commenter
    if ($parentComment->user_telegram_id != $user->telegram_id) {
        $replyUsername = $this->formatUsername($reply);

        $notifText = "💬 <b>Sizning izohingizga javob keldi!</b>\n\n";
        $notifText .= "📦 Mahsulot: {$product->name}\n";
        $notifText .= "↩️ {$replyUsername} javob berdi:\n";
        $notifText .= "{$text}";

        $keyboard = Keyboard::make()
            ->inline()
            ->row([
                Keyboard::inlineButton([
                    'text' => '👁 Ko\'rish',
                    'callback_data' => 'comments_' . $product->id . '_1'
                ])
            ]);

        try {
            $this->sendMessage($parentComment->user_telegram_id, $notifText, $keyboard);
        } catch (\Exception $e) {
            Log::error('Failed to notify original commenter: ' . $e->getMessage());
        }
    }

    // Notify seller
    $shop = $product->shop;
    $seller = $shop->user;

    if ($seller->telegram_id != $user->telegram_id) {
        $sellerText = "💬 <b>Yangi javob!</b>\n\n";
        $sellerText .= "📦 Mahsulot: {$product->name}\n";
        $sellerText .= "👤 {$user->full_name} javob berdi:\n";
        $sellerText .= "{$text}";

        $keyboard = Keyboard::make()
            ->inline()
            ->row([
                Keyboard::inlineButton([
                    'text' => '👁 Ko\'rish',
                    'callback_data' => 'comments_' . $product->id . '_1'
                ])
            ]);

        try {
            $this->sendMessage($seller->telegram_id, $sellerText, $keyboard);
        } catch (\Exception $e) {
            Log::error('Failed to notify seller: ' . $e->getMessage());
        }
    }
}
    protected function saveComment($chatId, $user, $text)
{
    if (mb_strlen($text) < 2) {
        $this->sendMessage($chatId, "❌ Izoh juda qisqa. Qaytadan yozing:");
        return;
    }

    if (mb_strlen($text) > 500) {
        $this->sendMessage($chatId, "❌ Izoh juda uzun (maksimal 500 ta belgi). Qisqartiring:");
        return;
    }

    $tempData = $user->temp_data;
    $productId = $tempData['product_id'];

    $product = Product::find($productId);

    if (!$product) {
        $this->sendMessage($chatId, "❌ Mahsulot topilmadi");
        $user->update(['state' => null, 'temp_data' => null]);
        return;
    }

    ProductComment::create([
        'product_id' => $productId,
        'parent_id' => null, // ← Top-level comment
        'user_telegram_id' => $user->telegram_id,
        'user_name' => $user->full_name,
        'comment' => $text
    ]);

    $user->update([
        'state' => null,
        'temp_data' => null
    ]);

    $this->sendMessage($chatId,
        "✅ <b>Izoh qo'shildi!</b>\n\n" .
        "Rahmat fikr-mulohazangiz uchun!"
    );

    // Notify seller
    $shop = $product->shop;
    $seller = $shop->user;

    if ($seller->telegram_id != $user->telegram_id) {
        $sellerText = "💬 <b>Yangi izoh!</b>\n\n";
        $sellerText .= "📦 Mahsulot: {$product->name}\n";
        $sellerText .= "👤 Foydalanuvchi: {$user->full_name}\n\n";
        $sellerText .= "💭 Izoh:\n{$text}";

        $keyboard = Keyboard::make()
            ->inline()
            ->row([
                Keyboard::inlineButton([
                    'text' => '👁 Ko\'rish',
                    'callback_data' => 'comments_' . $product->id . '_1'
                ])
            ]);

        try {
            $this->sendMessage($seller->telegram_id, $sellerText, $keyboard);
        } catch (\Exception $e) {
            Log::error('Failed to notify seller: ' . $e->getMessage());
        }
    }
}
    protected function handleStateMessage($message, $user)
    {
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';

        switch ($user->state) {
            case 'waiting_shop_name':
                $this->saveShopName($chatId, $user, $text);
                break;

            case 'waiting_shop_description':
                $this->saveShopDescription($chatId, $user, $text);
                break;

            case 'adding_product_name':
                $this->saveProductName($chatId, $user, $text);
                break;
            case 'adding_comment':
                $this->saveComment($chatId, $user, $text);
                break;

            case 'replying_to_comment':
                $this->saveReply($chatId, $user, $text);
                break;

            case 'adding_product_description':
                $this->saveProductDescription($chatId, $user, $text);
                break;

            case 'adding_product_price':
                $this->saveProductPrice($chatId, $user, $text);
                break;

            case 'adding_product_cost':
                $this->saveProductCost($chatId, $user, $text);
                break;

            case 'checkout_name':
                $this->saveCheckoutName($chatId, $user, $text);
                break;

            case 'checkout_phone':
                $this->saveCheckoutPhone($chatId, $user, $text);
                break;

            case 'checkout_address':
                $this->saveCheckoutAddress($chatId, $user, $text);
                break;

            case 'adding_comment':
                $this->saveComment($chatId, $user, $text);
                break;
        }
    }

    protected function saveShopName($chatId, $user, $name)
    {
        if (mb_strlen($name) < 3) {
            $this->sendMessage($chatId, "❌ Do'kon nomi juda qisqa. Qaytadan kiriting:");
            return;
        }

        $tempData = ['shop_name' => $name];
        $user->update([
            'state' => 'waiting_shop_description',
            'temp_data' => $tempData
        ]);

        $this->sendMessage($chatId,
            "✅ Do'kon nomi: <b>$name</b>\n\n" .
            "Endi do'koningiz haqida qisqacha ma'lumot kiriting:\n\n" .
            "Yoki /skip yuboring"
        );
    }

    protected function saveShopDescription($chatId, $user, $description)
    {
        $tempData = $user->temp_data;

        if ($description === '/skip') {
            $description = null;
        }

        $shop = Shop::create([
            'user_id' => $user->id,
            'name' => $tempData['shop_name'],
            'description' => $description,
            'is_active' => true
        ]);

        $user->update([
            'state' => null,
            'temp_data' => null
        ]);

        $this->sendMessage($chatId,
            "🎉 <b>Tabriklaymiz!</b>\n\n" .
            "Do'koningiz yaratildi: <b>{$shop->name}</b>\n\n" .
            "Endi mahsulot qo'shing."
        );

        sleep(1);
        $this->showSellerMenu($chatId, $user->telegram_id);
    }

    protected function saveCheckoutName($chatId, $user, $name)
    {
        if (mb_strlen($name) < 2) {
            $this->sendMessage($chatId, "❌ Ism juda qisqa. Qaytadan kiriting:");
            return;
        }

        $tempData = $user->temp_data ?? [];
        $tempData['buyer_name'] = $name;

        $user->update([
            'state' => 'checkout_phone',
            'temp_data' => $tempData
        ]);

        $this->sendMessage($chatId,
            "✅ Ism: <b>$name</b>\n\n" .
            "Telefon raqamingizni kiriting:\n" .
            "<i>Masalan: +998901234567</i>"
        );
    }

    protected function saveCheckoutPhone($chatId, $user, $phone)
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        if (mb_strlen($phone) < 9) {
            $this->sendMessage($chatId, "❌ Telefon noto'g'ri. Qaytadan:");
            return;
        }

        $tempData = $user->temp_data;
        $tempData['buyer_phone'] = $phone;

        $user->update([
            'state' => 'checkout_address',
            'temp_data' => $tempData
        ]);

        $this->sendMessage($chatId,
            "✅ Telefon: <b>$phone</b>\n\n" .
            "Yetkazib berish manzilini kiriting:"
        );
    }

    protected function saveCheckoutAddress($chatId, $user, $address)
    {
        if (mb_strlen($address) < 10) {
            $this->sendMessage($chatId, "❌ Manzil juda qisqa. Qaytadan:");
            return;
        }

        $tempData = $user->temp_data;
        $cartItems = Cart::where('telegram_id', $user->telegram_id)->with('product')->get();

        if ($cartItems->isEmpty()) {
            $this->sendMessage($chatId, "❌ Savatingiz bo'sh");
            $user->update(['state' => null, 'temp_data' => null]);
            return;
        }

        $shopId = $cartItems->first()->shop_id;
        $items = [];
        $total = 0;

        foreach ($cartItems as $item) {
            $items[] = [
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'price' => $item->product->price,
                'cost_price' => $item->product->cost_price,
                'quantity' => $item->quantity,
            ];
            $total += $item->product->price * $item->quantity;
        }

        $order = Order::create([
            'shop_id' => $shopId,
            'buyer_telegram_id' => $user->telegram_id,
            'buyer_name' => $tempData['buyer_name'],
            'buyer_phone' => $tempData['buyer_phone'],
            'buyer_address' => $address,
            'items' => $items,
            'total_amount' => $total,
            'status' => 'pending'
        ]);

        Cart::where('telegram_id', $user->telegram_id)->delete();
        $user->update(['state' => null, 'temp_data' => null]);

        $this->sendMessage($chatId,
            "✅ <b>Buyurtma qabul qilindi!</b>\n\n" .
            "📦 Buyurtma #{$order->id}\n" .
            "💰 Jami: {$order->formatted_total}\n\n" .
            "Tez orada sotuvchi bog'lanadi."
        );

        // Notify seller
        $shop = Shop::find($shopId);
        $seller = $shop->user;

        $sellerText = "🔔 <b>Yangi buyurtma!</b>\n\n";
        $sellerText .= "📦 Buyurtma #{$order->id}\n\n";
        $sellerText .= "👤 Xaridor: {$order->buyer_name}\n";
        $sellerText .= "📞 Telefon: {$order->buyer_phone}\n";
        $sellerText .= "📍 Manzil: {$order->buyer_address}\n\n";
        $sellerText .= "🛒 Mahsulotlar:\n";

        foreach ($order->items as $item) {
            $sellerText .= "• {$item['name']} x{$item['quantity']} = " .
                number_format($item['price'] * $item['quantity'], 0, '.', ' ') . " so'm\n";
        }

        $sellerText .= "\n💰 Jami: {$order->formatted_total}";

        $keyboard = Keyboard::make()
            ->inline()
            ->row([
                Keyboard::inlineButton([
                    'text' => '✅ Tasdiqlash',
                    'callback_data' => 'order_confirm_' . $order->id
                ]),
                Keyboard::inlineButton([
                    'text' => '❌ Bekor qilish',
                    'callback_data' => 'order_cancel_' . $order->id
                ]),
            ]);

        try {
            $this->sendMessage($seller->telegram_id, $sellerText, $keyboard);
        } catch (\Exception $e) {
            Log::error('Failed to notify seller: ' . $e->getMessage());
        }
    }

    protected function showSellerMenu($chatId, $telegramId)
    {
        $user = User::where('telegram_id', $telegramId)->first();

        if (!$user) {
            $this->handleStart(['chat' => ['id' => $chatId], 'from' => ['id' => $telegramId]]);
            return;
        }

        if ($user->role === 'seller') {
            $shop = $user->shop;

            if (!$shop) {
                $this->requestShopName($chatId, $telegramId);
                return;
            }

            $keyboard = Keyboard::make()
                ->inline()
                ->row([
                    Keyboard::inlineButton(['text' => '➕ Mahsulot qo\'shish', 'callback_data' => 'add_product']),
                ])
                ->row([
                    Keyboard::inlineButton(['text' => '📦 Mahsulotlarim', 'callback_data' => 'my_products']),
                ])
                ->row([
                    Keyboard::inlineButton(['text' => '🛒 Buyurtmalar', 'callback_data' => 'seller_orders']),
                ])
                ->row([
                    Keyboard::inlineButton(['text' => '📊 Statistika', 'callback_data' => 'stats']),
                ]);

            $productCount = $shop->products()->count();
            $pendingOrders = $shop->orders()->where('status', 'pending')->count();

            $this->sendMessage($chatId,
                "🏪 <b>Sotuvchi paneli</b>\n\n" .
                "Do'kon: <b>{$shop->name}</b>\n" .
                "Status: " . ($shop->is_active ? '✅ Faol' : '⏸ Faol emas') . "\n\n" .
                "📦 Mahsulotlar: {$productCount} ta\n" .
                "🛒 Yangi buyurtmalar: {$pendingOrders} ta\n\n" .
                "Amallardan birini tanlang:",
                $keyboard
            );
        } else {
            $keyboard = Keyboard::make()
                ->inline()
                ->row([
                    Keyboard::inlineButton(['text' => '✅ Ha, sotuvchi bo\'lish', 'callback_data' => 'become_seller_yes']),
                ])
                ->row([
                    Keyboard::inlineButton(['text' => '❌ Yo\'q', 'callback_data' => 'become_seller_no']),
                ]);

            $this->sendMessage($chatId,
                "💼 <b>Sotuvchi bo'lish</b>\n\n" .
                "Siz hozir xaridor sifatida ro'yxatdan o'tgansiz.\n\n" .
                "Sotuvchi bo'lib, o'z do'koningizni ochmoqchimisiz?",
                $keyboard
            );
        }
    }

    protected function startAddProduct($chatId, $telegramId)
    {
        $user = User::where('telegram_id', $telegramId)->first();

        if (!$user->shop) {
            $this->sendMessage($chatId, "❌ Avval do'kon yarating");
            return;
        }

        $user->update(['state' => 'adding_product_photo']);

        $this->sendMessage($chatId,
            "📸 <b>Yangi mahsulot</b>\n\n" .
            "Mahsulot rasmini yuboring:"
        );
    }

    protected function handleProductPhoto($message)
    {
        $chatId = $message['chat']['id'];
        $telegramId = $message['from']['id'];
        $user = User::where('telegram_id', $telegramId)->first();

        $photos = $message['photo'];
        $photo = end($photos);
        $fileId = $photo['file_id'];

        $tempData = ['image_file_id' => $fileId];

        $user->update([
            'state' => 'adding_product_name',
            'temp_data' => $tempData
        ]);

        $this->sendMessage($chatId,
            "✅ Rasm qabul qilindi!\n\n" .
            "Mahsulot nomini kiriting:"
        );
    }

    protected function saveProductName($chatId, $user, $name)
    {
        if (mb_strlen($name) < 2) {
            $this->sendMessage($chatId, "❌ Nom juda qisqa. Qaytadan:");
            return;
        }

        $tempData = $user->temp_data;
        $tempData['name'] = $name;

        $user->update([
            'state' => 'adding_product_description',
            'temp_data' => $tempData
        ]);

        $this->sendMessage($chatId,
            "✅ Nom: <b>$name</b>\n\n" .
            "Tavsif kiriting yoki /skip:"
        );
    }

    protected function saveProductDescription($chatId, $user, $description)
    {
        $tempData = $user->temp_data;

        if ($description === '/skip') {
            $description = null;
        }

        $tempData['description'] = $description;

        $user->update([
            'state' => 'adding_product_price',
            'temp_data' => $tempData
        ]);

        $this->sendMessage($chatId,
            "Narxini kiriting (so'mda):\n" .
            "<i>Masalan: 50000</i>"
        );
    }

    protected function saveProductPrice($chatId, $user, $price)
    {
        $price = preg_replace('/[^0-9]/', '', $price);

        if (empty($price) || $price < 100) {
            $this->sendMessage($chatId, "❌ Narx noto'g'ri. Qaytadan:");
            return;
        }

        $tempData = $user->temp_data;
        $tempData['price'] = $price;

        $user->update([
            'state' => 'adding_product_cost',
            'temp_data' => $tempData
        ]);

        $this->sendMessage($chatId,
            "✅ Narx: " . number_format($price, 0, '.', ' ') . " so'm\n\n" .
            "Tannarx kiriting yoki /skip:"
        );
    }

    protected function saveProductCost($chatId, $user, $cost)
    {
        $tempData = $user->temp_data;

        if ($cost === '/skip') {
            $cost = 0;
        } else {
            $cost = preg_replace('/[^0-9]/', '', $cost);
        }

        $product = Product::create([
            'shop_id' => $user->shop->id,
            'name' => $tempData['name'],
            'description' => $tempData['description'],
            'price' => $tempData['price'],
            'cost_price' => $cost,
            'image_url' => $tempData['image_file_id'],
            'is_available' => true
        ]);

        $user->update([
            'state' => null,
            'temp_data' => null
        ]);

        try {
            $this->telegram->sendPhoto([
                'chat_id' => $chatId,
                'photo' => $product->image_url,
                'caption' => "✅ <b>Mahsulot qo'shildi!</b>\n\n" .
                    "📦 {$product->name}\n" .
                    "💰 Narx: {$product->formatted_price}\n" .
                    ($cost > 0 ? "💚 Foyda: " . number_format($product->profit, 0, '.', ' ') . " so'm" : ""),
                'parse_mode' => 'HTML'
            ]);
        } catch (\Exception $e) {
            $this->sendMessage($chatId, "✅ Mahsulot qo'shildi!");
        }

        sleep(1);
        $this->showSellerMenu($chatId, $user->telegram_id);
    }

    protected function showMyProducts($chatId, $telegramId)
    {
        $user = User::where('telegram_id', $telegramId)->first();
        $products = $user->shop->products()->get();

        if ($products->isEmpty()) {
            $keyboard = Keyboard::make()
                ->inline()
                ->row([
                    Keyboard::inlineButton([
                        'text' => '➕ Mahsulot qo\'shish',
                        'callback_data' => 'add_product'
                    ])
                ]);

            $this->sendMessage($chatId, "📦 Sizda mahsulotlar yo'q", $keyboard);
            return;
        }

        $text = "📦 <b>Mahsulotlarim</b>\n\n";
        $keyboard = Keyboard::make()->inline();

        foreach ($products as $product) {
            $status = $product->is_available ? '✅' : '⏸';
            $text .= "{$status} {$product->name} - {$product->formatted_price}\n";

            $keyboard->row([
                Keyboard::inlineButton([
                    'text' => $product->name,
                    'callback_data' => 'edit_product_' . $product->id
                ])
            ]);
        }

        $keyboard->row([
            Keyboard::inlineButton([
                'text' => '➕ Yangi mahsulot',
                'callback_data' => 'add_product'
            ])
        ]);
        $keyboard->row([
            Keyboard::inlineButton([
                'text' => '◀️ Orqaga',
                'callback_data' => 'seller_menu'
            ])
        ]);

        $this->sendMessage($chatId, $text, $keyboard);
    }

    protected function editProduct($chatId, $messageId, $productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            $this->editMessage($chatId, $messageId, "❌ Mahsulot topilmadi");
            return;
        }

        $keyboard = Keyboard::make()
            ->inline()
            ->row([
                Keyboard::inlineButton([
                    'text' => $product->is_available ? '⏸ To\'xtatish' : '✅ Faollashtirish',
                    'callback_data' => 'toggle_product_' . $product->id
                ])
            ])
            ->row([
                Keyboard::inlineButton([
                    'text' => '🗑 O\'chirish',
                    'callback_data' => 'delete_product_' . $product->id
                ])
            ])
            ->row([
                Keyboard::inlineButton([
                    'text' => '◀️ Orqaga',
                    'callback_data' => 'my_products'
                ])
            ]);

        $text = "📦 <b>{$product->name}</b>\n\n";
        if ($product->description) {
            $text .= "{$product->description}\n\n";
        }
        $text .= "💰 Narx: {$product->formatted_price}\n";
        $text .= "💵 Tannarx: " . number_format($product->cost_price, 0, '.', ' ') . " so'm\n";
        $text .= "💚 Foyda: " . number_format($product->profit, 0, '.', ' ') . " so'm\n";
        $text .= "Status: " . ($product->is_available ? '✅ Faol' : '⏸ To\'xtatilgan');

        $this->editMessage($chatId, $messageId, $text, $keyboard);
    }

    protected function toggleProductAvailability($chatId, $messageId, $productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return;
        }

        $product->update(['is_available' => !$product->is_available]);

        $this->editProduct($chatId, $messageId, $productId);
    }

    protected function deleteProduct($chatId, $messageId, $telegramId, $productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return;
        }

        $product->delete();

        $this->editMessage($chatId, $messageId, "✅ Mahsulot o'chirildi");

        sleep(1);
        $this->showMyProducts($chatId, $telegramId);
    }

    protected function showMyOrders($chatId, $telegramId)
    {
        $orders = Order::where('buyer_telegram_id', $telegramId)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        if ($orders->isEmpty()) {
            $this->sendMessage($chatId, "📦 Sizda buyurtmalar yo'q");
            return;
        }

        $text = "📦 <b>Buyurtmalarim:</b>\n\n";

        foreach ($orders as $order) {
            $text .= "#{$order->id} - {$order->status_text}\n";
            $text .= "💰 {$order->formatted_total}\n";
            $text .= "📅 " . $order->created_at->format('d.m.Y H:i') . "\n\n";
        }

        $this->sendMessage($chatId, $text);
    }

    protected function showSellerOrders($chatId, $telegramId)
    {
        $user = User::where('telegram_id', $telegramId)->first();

        $orders = Order::where('shop_id', $user->shop->id)
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        if ($orders->isEmpty()) {
            $this->sendMessage($chatId, "🛒 Buyurtmalar yo'q");
            return;
        }

        $text = "🛒 <b>Buyurtmalar:</b>\n\n";
        $keyboard = Keyboard::make()->inline();

        $pending = $orders->where('status', 'pending');

        if ($pending->count() > 0) {
            $text .= "⏳ <b>Yangi ({$pending->count()} ta):</b>\n";
            foreach ($pending as $order) {
                $text .= "#{$order->id} - {$order->buyer_name} - {$order->formatted_total}\n";
            }
        }

        $keyboard->row([
            Keyboard::inlineButton([
                'text' => '◀️ Orqaga',
                'callback_data' => 'seller_menu'
            ])
        ]);

        $this->sendMessage($chatId, $text, $keyboard);
    }

    protected function confirmOrder($chatId, $messageId, $orderId)
    {
        $order = Order::find($orderId);

        if (!$order) {
            return;
        }

        $order->confirm();

        $this->editMessage($chatId, $messageId,
            "✅ Buyurtma tasdiqlandi!\n\n" .
            "Xaridor bilan bog'laning:\n" .
            "📞 {$order->buyer_phone}"
        );

        try {
            $this->sendMessage($order->buyer_telegram_id,
                "✅ <b>Buyurtmangiz tasdiqlandi!</b>\n\n" .
                "📦 Buyurtma #{$order->id}\n" .
                "Sotuvchi siz bilan bog'lanadi."
            );
        } catch (\Exception $e) {
            Log::error('Failed to notify buyer: ' . $e->getMessage());
        }
    }

    protected function cancelOrder($chatId, $messageId, $orderId)
    {
        $order = Order::find($orderId);

        if (!$order) {
            return;
        }

        $order->cancel();

        $this->editMessage($chatId, $messageId, "❌ Buyurtma bekor qilindi");

        try {
            $this->sendMessage($order->buyer_telegram_id,
                "❌ <b>Buyurtma bekor qilindi</b>\n\n" .
                "📦 Buyurtma #{$order->id}"
            );
        } catch (\Exception $e) {
            Log::error('Failed to notify buyer: ' . $e->getMessage());
        }
    }

    protected function showStatistics($chatId, $telegramId)
    {
        $user = User::where('telegram_id', $telegramId)->first();
        $shop = $user->shop;

        $totalOrders = $shop->orders()->count();
        $pendingOrders = $shop->orders()->where('status', 'pending')->count();
        $confirmedOrders = $shop->orders()->where('status', 'confirmed')->count();

        $totalSales = $shop->orders()
            ->whereIn('status', ['confirmed', 'delivered'])
            ->sum('total_amount');

        $totalProfit = 0;
        $confirmedOrdersList = $shop->orders()
            ->whereIn('status', ['confirmed', 'delivered'])
            ->get();

        foreach ($confirmedOrdersList as $order) {
            $totalProfit += $order->total_profit;
        }

        $productCount = $shop->products()->count();
        $activeProducts = $shop->products()->where('is_available', true)->count();

        $text = "📊 <b>Statistika</b>\n\n";
        $text .= "🏪 Do'kon: <b>{$shop->name}</b>\n\n";
        $text .= "📦 Mahsulotlar: {$productCount} ta\n";
        $text .= "✅ Faol: {$activeProducts} ta\n\n";
        $text .= "🛒 Jami buyurtmalar: {$totalOrders} ta\n";
        $text .= "⏳ Yangi: {$pendingOrders} ta\n";
        $text .= "✅ Tasdiqlangan: {$confirmedOrders} ta\n\n";
        $text .= "💰 Jami sotuvlar: " . number_format($totalSales, 0, '.', ' ') . " so'm\n";
        $text .= "💚 Jami foyda: " . number_format($totalProfit, 0, '.', ' ') . " so'm";

        $keyboard = Keyboard::make()
            ->inline()
            ->row([
                Keyboard::inlineButton([
                    'text' => '◀️ Orqaga',
                    'callback_data' => 'seller_menu'
                ])
            ]);

        $this->sendMessage($chatId, $text, $keyboard);
    }

    // ==================== HELPERS ====================

    protected function sendMessage($chatId, $text, $keyboard = null)
    {
        $params = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];

        if ($keyboard) {
            if (is_array($keyboard)) {
                $params['reply_markup'] = json_encode($keyboard);
            } else {
                $params['reply_markup'] = $keyboard;
            }
        }

        return $this->telegram->sendMessage($params);
    }

    protected function editMessage($chatId, $messageId, $text, $keyboard = null)
    {
        $params = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];

        if ($keyboard) {
            $params['reply_markup'] = $keyboard;
        }

        try {
            return $this->telegram->editMessageText($params);
        } catch (\Exception $e) {
            Log::warning('Could not edit message: ' . $e->getMessage());
        }
    }
}