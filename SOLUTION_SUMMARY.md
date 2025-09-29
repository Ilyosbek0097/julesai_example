# Muammoning Yechimi: Muddati O'tgan va Noto'g'ri Tokenlarni Ajratish

Ushbu hujjat "Token muddati o'tgan" va "Token noto'g'ri" holatlari uchun qanday qilib alohida xabarlar chiqarishga erishganimizni tushuntiradi.

## Muammo

Dastlab, API so'rovida token muddati o'tgan bo'lsa ham, token noto'g'ri kiritilgan bo'lsa ham, Laravel bir xil "Unauthenticated" xabarini qaytarayotgan edi. Bu holat API'dan foydalanuvchi uchun xatolikni aniqlashni qiyinlashtiradi.

**Maqsad:** Foydalanuvchiga har bir holat uchun aniq va tushunarli xabar qaytarish:
1.  Tokenning **muddati o'tgan** bo'lsa: "Token muddati o'tgan."
2.  Token **noto'g'ri** yoki **mavjud bo'lmasa**: "Token noto'g'ri yoki mavjud emas."

## Yechimning Arxitekturasi

Bu muammoni hal qilish uchun biz **ikki bosqichli tekshiruv** tizimini qo'lladik. Har bir bosqich o'zining aniq vazifasini bajaradi.

### 1-Bosqich: Maxsus Middleware (`CheckTokenExpiry.php`)

Biz `app/Http/Middleware/CheckTokenExpiry.php` nomli yangi "oraliq dastur" (middleware) yaratdik.

*   **Vazifasi:** Faqat bitta narsani tekshiradi — so'rovdagi Bearer tokenining muddati tugaganmi yoki yo'qmi.
*   **Ishlash tartibi:** Bu middleware `auth:sanctum`'dan **oldin** ishga tushadi.
    1.  Agar so'rovda token bo'lsa, uni ma'lumotlar bazasidan topadi (`personal_access_tokens` jadvalidan).
    2.  Agar token topilsa va uning `expires_at` maydoni hozirgi vaqtdan o'tgan bo'lsa, darhol **"Token muddati o'tgan."** degan JSON javobini qaytaradi va so'rovni shu yerda to'xtatadi.
    3.  Agar tokenning muddati o'tmagan bo'lsa yoki token umuman mavjud bo'lmasa, u so'rovni keyingi bosqichga o'tkazib yuboradi.

### 2-Bosqich: Laravelning Xatoliklarni Boshqarish Tizimi (`bootstrap/app.php`)

Birinchi bosqichdan muvaffaqiyatli o'tgan so'rov endi Laravelning standart `auth:sanctum` tekshiruviga keladi.

*   **Vazifasi:** Tokenning haqiqiyligini tekshirish.
*   **Ishlash tartibi:**
    1.  `auth:sanctum` tokenning imzosi to'g'riligini va ma'lumotlar bazasida mavjudligini tekshiradi.
    2.  Agar token **noto'g'ri** (imzosi xato) yoki **mavjud bo'lmasa**, `auth:sanctum` `AuthenticationException` xatoligini yuzaga keltiradi.
    3.  Biz `bootstrap/app.php` fayliga qo'shgan kod aynan shu `AuthenticationException`'ni ushlab oladi va **"Token noto'g'ri yoki mavjud emas."** degan maxsus JSON javobini qaytaradi.

### Konfiguratsiya (`bootstrap/app.php` va `routes/api.php`)

Ushbu ikki tizim birgalikda ishlashi uchun biz quyidagi sozlamalarni qildik:

1.  **Alias (qisqa nom):** `bootstrap/app.php` faylida `CheckTokenExpiry` middleware'imizga `'check_token_expiry'` degan qisqa nom berdik.
2.  **To'g'ri tartib:** `routes/api.php` faylida himoyalangan marshrutlar uchun middleware'larni **to'g'ri tartibda** yozdik: `['check_token_expiry', 'auth:sanctum']`. Bu "avval muddatini tekshir, keyin haqiqiyligini tekshir" degan mantiqni ta'minlaydi.

## Natija

Ushbu yondashuv tufayli endi tizim har bir xatolik holatini aniq ajrata oladi va foydalanuvchiga tushunarli javob qaytaradi, bu esa API'ning ishonchliligi va qulayligini oshiradi.