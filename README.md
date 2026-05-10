# 🚀 Hepsiburada PHP API SDK (Unofficial)

Bu kütüphane, Hepsiburada'nın Mobil API'leri ile (Scorpion & MobileAPI) doğrudan haberleşmek için yazılmış, esnek bir PHP kütüphanesidir. 

Hepsiburada sunucularından gelen veriyi hiçbir şekilde bozmadan, saf JSON formatında dışarıya proxy (ayna) yapar. Kendi backend sisteminizi, fiyat takip botunuzu veya otomatik sipariş otomasyonunuzu kurmak için idealdir.

## 🌟 Özellikler

* ⚡ Saf JSON Çıktısı: Araya PHP dizileri (array) girmez. Hepsiburada'nın orijinal JSON'unu direkt Frontend'inize veya uygulamanıza paslar.
* 👥 Çoklu Hesap Desteği (Multi-Session): MyUsers/ klasörü altındaki JSON dosyaları ile aynı anda yüzlerce farklı hesabı yönetebilirsiniz.
* 🔐 Tam Otomatik Login & MFA: E-posta veya Cep Telefonu ile giriş ve OTP (Tek Kullanımlık Şifre) doğrulamasını destekler. Başarılı girişte cihaz yetkilerini otomatik günceller ve kaydeder.
* 🛒 Gelişmiş Sepet Yönetimi: Sepete ürün ekleme işlemlerini orijinal Hepsiburada imza (Signature) yapısıyla çözer.

---

## 📂 Klasör Yapısı

Projeyi kurduğunuzda dizin yapınızın şu şekilde olması tavsiye edilir:

HepsiburadaAPI/

│
├── MyUsers/                     # Kullanıcı oturumlarının otomatik kaydedildiği klasör
│   └── default.json             # Sistemin çalışması için gereken temel cihaz bilgileri
│
├── src/
│   ├── Request.php              # cURL işlemlerini ve Headerları yöneten ana motor
│   └── HepsiburadaAPI.php       # Metodların (Search, Cart, Login) bulunduğu vitrin
│
└── index.php                    # API'yi çalıştıracağınız veya test edeceğiniz dosya

*Not: MyUsers/default.json dosyasını sizin için doldurdum fakat işe yaramazsa burpsuite gibi uygulamalar ile kendi cihazınıza özel device idleri koymanız gerekebilir.*

---

## 🛠️ Mevcut Fonksiyonlar ve Kullanımları

Kütüphaneyi çağırmak ve işlemlere başlamak çok basittir. Aksi belirtilmedikçe tüm API fonksiyonları Saf JSON String döndürür.

### 1. Kütüphaneyi Başlatma ve Kullanıcı Seçimi
```php
require_once __DIR__ . '/src/HepsiburadaAPI.php';

// Çıktıyı JSON olarak ayarlayalım
header('Content-Type: application/json; charset=utf-8');

$hepsiburada = new HepsiburadaAPI();

// İşlem yapılacak kullanıcıyı seçin (örn: default.json veya acun_ilicali.json)
$hepsiburada->selecteduser("default"); 
```

### 2. Ürün Arama (Search API)
```php
// Belirli bir kelime ile ürün araması yapar
// $keyword: Aranacak kelime (örn: "telefon")
// $page: Sayfa numarası (opsiyonel)
$aramaSonucu = $hepsiburada->search("telefon");
echo $aramaSonucu; 
```

### 3. Oturum Açma (Login) Fonksiyonları
Bu kütüphane hem E-posta hem de GSM ile girişi destekler. Başarılı OTP doğrulamasından sonra `MyUsers` klasörüne kullanıcının adıyla yeni bir yetki dosyası otomatik açılır.

**A. E-posta ile Giriş**
```php
// 1. Adım: E-posta giriş isteği gönder
$login = $hepsiburada->login("kullanici@mail.com", "Sifre123!");
$loginData = json_decode($login, true);
$otpRef = $loginData['result']['otpReference']; // Gelen referans kodunu al

// 2. Adım: Maile gelen kodu doğrula
$otpSonuc = $hepsiburada->emailotp($otpRef, "123456");
echo $otpSonuc; 
```

**B. Telefon Numarası (GSM) ile Giriş**
```php
// 1. Adım: Telefon numarasına giriş isteği gönder
$gsmLogin = $hepsiburada->loginviagsm("53X1234567");
$gsmData = json_decode($gsmLogin, true);
$otpRef = $gsmData['result']['otpReference'];

// 2. Adım: Telefona gelen SMS kodunu doğrula
$gsmOtpSonuc = $hepsiburada->gsmlogibotp($otpRef, "123456");
echo $gsmOtpSonuc;
```

### 4. Sepet İşlemleri (Cart Management)
```php
// Yetkili kullanıcıyı seç
$hepsiburada->selecteduser("ornek_kullanici");

// A. Sepetteki Toplam Ürün Sayısını Getirir
echo $hepsiburada->countChart();

// B. Sepetteki Tüm Ürünlerin Detayını Getirir (Checkout API)
echo $hepsiburada->AllBasket();

// C. Sepete Ürün Ekleme
$urunDetayi = [
    "name" => "REDMI 15C 256 GB Siyah",
    "sku" => "HBCV00009R5JVA",
    "listingId" => "e3a84d72-1061-4fed-91aa-b96489981d43",
    "quantity" => 1,
    "price" => [
        "currency" => "TL",
        "discountedPrice" => 8899.0
    ]
];
echo $hepsiburada->addToCart($urunDetayi);
```

### 5. Mağaza / Satıcı İşlemleri
```php
// A. Satıcı Profilini Görüntüleme
// $keyword: Mağazanın URL slug'ı (örn: yatas, teknosa)
echo $hepsiburada->lookSellerProfile("yatas");

// B. Satıcıyı Takip Etme
// $sellerId: Mağazanın benzersiz kimliği (Owner Identifier)
echo $hepsiburada->followSellerProfile("123456789");
```

### 6. Kullanıcı Bilgileri
```php
// Kullanıcının kayıtlı adreslerini getirir. 
// DİKKAT: Bu endpoint bazen JSON yerine HTML sayfa dönebilir.
echo $hepsiburada->MyAdresses();
```

### 7. Çıkış Yapma (Logout)
Hesaptan çıkış yapar ve `MyUsers` klasöründeki yetkileri ezip cihazı "Misafir" (Anonymous) statüsüne geçirir.
```php
// Hedef kullanıcının şu anki misafir token'ı gereklidir
$anonToken = "eyJhbGciOiJIUzI1NiIs..."; 
echo $hepsiburada->logout($anonToken);
```

---

## ⚠️ Önemli Uyarılar & Güvenlik

1. Akamai Koruması: Çok sık istek atmanız durumunda Hepsiburada'nın arkasındaki Akamai güvenlik duvarı sizi bloklayıp HTML veya Captcha döndürebilir. İşlemler arasına bekleme (sleep/usleep) koymanız tavsiye edilir.
2. Sertifika & Paket Yakalama: Kendi tokenlarınızı (JWT, X-Authorization) çıkarmak için HTTP Toolkit veya Charles Proxy kullanacaksanız, cihazınızın Root'lu veya Sistem Sertifikası enjekte edilebilir bir Android Emülatör olması şarttır. Modern Android sürümleri kullanıcı sertifikalarını reddeder.
3. Sorumluluk Reddi: Bu kütüphane eğitim ve araştırma amaçlı geliştirilmiştir. Hepsiburada'nın resmi API'si değildir. Ticari kullanımda veya kurallara aykırı bot işlemlerinde yaşanabilecek engelleme ve yasal sorunlardan geliştirici sorumlu tutulamaz.

---
Versiyon: 1.0.0
