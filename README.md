# API Testlash Qo'llanmasi

Ushbu qo'llanma yaratilgan API'ni qanday sozlash va test qilishni tushuntiradi.

## 1. Sozlash

Ishni boshlashdan oldin loyihani sozlash kerak.

### 1.1. Ma'lumotlar bazasi jadvallarini yaratish (Migration)

Terminalda loyiha papkasida quyidagi buyruqni ishga tushiring. Bu `api_users` jadvalini yaratadi.

```bash
php artisan migrate
```

### 1.2. Test foydalanuvchisini yaratish (Seeding)

`api_users` jadvaliga test uchun foydalanuvchi qo'shish uchun quyidagi buyruqni ishga tushiring. Bu "bankcash" nomli foydalanuvchini `B@nkcash!1510++` paroli bilan yaratadi.

```bash
php artisan db:seed
```

## 2. API'ni Testlash

API'ni testlash uchun Postman, Insomnia yoki boshqa API klientidan foydalanishingiz mumkin.

### 2.1. Token Olish (Login)

Birinchi qadam — tizimga kirib, avtorizatsiya uchun Bearer token olish.

- **Method:** `POST`
- **URL:** `http://<sizning-loyihangiz-url>/api/login`
- **Headers:**
  - `Accept`: `application/json`
- **Body** (`raw`, `JSON` formatida):

```json
{
    "username": "bankcash",
    "password": "B@nkcash!1510++"
}
```

✅ **Muvaffaqiyatli javob:** Sizga `status: true` va `token` maydoniga ega JSON javob keladi. Shu tokenni keyingi qadam uchun nusxalab oling.

```json
{
    "status": true,
    "token": "1|aBcDeFgHiJkLmNoPqRsTuVwXyZ1234567890abcdef"
}
```

❌ **Muvaffaqiyatsiz javob:** Agar login yoki parol xato bo'lsa, quyidagi javobni olasiz:

```json
{
    "status": false,
    "message": "Invalid credentials"
}
```

### 2.2. Himoyalangan Ma'lumotni Olish

Endi, olgan tokeningizdan foydalanib, himoyalangan ma'lumotlarni so'rashingiz mumkin.

- **Method:** `POST`
- **URL:** `http://<sizning-loyihangiz-url>/api/get-data`
- **Headers:**
  - `Accept`: `application/json`
  - `Authorization`: `Bearer <SIZ_OLGAN_TOKENNI_SHU_YERGA_QO'YING>` (Masalan: `Bearer 1|aBcDeFg...`)
- **Body** (`raw`, `JSON` formatida):

```json
{
    "Branch ID": "00998",
    "Oper day": "2024-07-27"
}
```

✅ **Muvaffaqiyatli javob:**

```json
{
    "status": true,
    "message": "uspeshno"
}
```

❌ **Token xato bo'lsa:** Agar token noto'g'ri yoki kiritilmagan bo'lsa, `401 Unauthenticated` xatoligini olasiz.

Shu bilan testlash jarayoni yakunlanadi.