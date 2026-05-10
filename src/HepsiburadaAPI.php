<?php
require_once __DIR__ . '/Request.php';

class HepsiburadaAPI {
    private $request;
    private $currentUserToken;

    public function __construct() {
        $this->request = new Request();
    }

    public function selecteduser($token) {
        $jsonFilePath = __DIR__ . "/../MyUsers/{$token}.json";
        
        if (!file_exists($jsonFilePath)) {
            die("Hata: {$token} isimli kullanıcı JSON dosyası bulunamadı!\n");
        }

        $userData = json_decode(file_get_contents($jsonFilePath), true);
        $this->currentUserToken = $token;

        $this->request = new Request($userData);
        
        return $this;
    }

    public function search($keyword, $page = 1) {
        $url = "https://scorpion.hepsiburada.com/api/v1/listing/search/" . urlencode($keyword) . "/products?page={$page}";
        return $this->request->get($url);
    }

    public function addToCart($productData) {
        $url = "https://mobileapi.hepsiburada.com/api/AddCartItems";
        
        $payload = [
            "product" => $productData,
            "relatedProducts" => [],
            "origin" => "SfProductDetail"
        ];

        return $this->request->post($url, $payload);
    }

    public function countChart(){
        $url = "https://mobileapi.hepsiburada.com/api/CartItemCount";
        return $this->request->get($url);
    }

    public function login($username, $password){
        $url = "https://mobileapi.hepsiburada.com/api/v2/auth/login/mail";
        $payload = [
            "identifier" => "$username",
            "password" => "$password"
        ];
        return $this->request->post($url, $payload);
    }

    public function emailotp($otpref, $code){
            $url = "https://mobileapi.hepsiburada.com/api/v1/auth/mfa/mail/otp-validation";
            $payload = [
                "otpReference" => $otpref,
                "code" => $code
            ];

            $jsonString = $this->request->post($url, $payload);

            $response = json_decode($jsonString, true);

            if ($response['code'] == 200 && is_array($response['data']) && isset($response['data']['result']['token'])) {
                
                $newToken = $response['data']['result']['token'];
                $userInfo = $response['data']['result']['userInfo'];

                $firstName = strtolower(trim($userInfo['fn'] ?? 'user'));
                $lastName = strtolower(trim($userInfo['ln'] ?? $userInfo['uid']));
                
                $search = ['ç','ğ','ı','i','ö','ş','ü',' '];
                $replace = ['c','g','i','i','o','s','u','_'];
                $fileName = str_replace($search, $replace, $firstName . "_" . $lastName);

                $userData = $this->request->getUserData();

                $userData['x_jwt'] = $newToken;
                $userData['x_authorization'] = $newToken;

                $jsonFilePath = __DIR__ . "/../MyUsers/{$fileName}.json";
                file_put_contents($jsonFilePath, json_encode($userData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }

            return $jsonString;
        }

    public function AllBasket(){
        $url = "https://checkout.hepsiburada.com/mobile/api/v1/basket/all";
        return $this->request->get($url);
    }

    public function lookSellerProfile($keyword){
        $url = "https://scorpion.hepsiburada.com/api/v1/listing/merchant/merchantsComponent";
        $payload = [
            "deeplink" => "hbapp://merchant?urlKeyword\u003d$keyword"
        ];
        // keyword ==> magza-url-adi  |||=> {  "deeplink": "hbapp://merchant?urlKeyword\u003dyatas"  }
        return $this->request->post($url, $payload);
    }

    public function followSellerProfile($sellerId){
        $url = "https://scorpion.hepsiburada.com/api/v1/listing/merchant/follow";
        $payload = [
            "ownerIdentifier" => $sellerId,
            "ownerType" => "merchant"
        ];
        return $this->request->post($url, $payload);
    }

    public function MyAdresses(){
        //Gelen response json değil html olabilir bu yüzden yazılımlarınızda hata oluşabilir!
        $url = "https://hesabim.hepsiburada.com/adreslerim?forMobileApp=true";
        return $this->request->get($url);
    }

    public function logout($anonymousToken){
        $url = "https://mobileapi.hepsiburada.com/api/v1/auth/logout";
       $payload = [
            "result" => [
                "anonymousToken" => $anonymousToken
            ],
            "messages" => [
                [
                    "type" => "Success",
                    "title" => "",
                    "message" => "",
                    "duration" => 0
                ]
            ]
        ];
        return $this->request->post($url, $payload);
    }

    public function loginviagsm($gsm){
        $url = "https://mobileapi.hepsiburada.com/api/v1/auth/login/gsm";
        $payload = [
            "identifier" => "+90$gsm"
        ];
        return $this->request->post($url, $payload);
    }

    public function gsmloginotp(){
        $url = "https://mobileapi.hepsiburada.com/api/v1/auth/login/gsm/otp-validation";
        $payload = [
            "otpReference" => "$otpref",
            "code" => "$code"
        ];

            $jsonString = $this->request->post($url, $payload);

            $response = json_decode($jsonString, true);

            if ($response['code'] == 200 && is_array($response['data']) && isset($response['data']['result']['token'])) {
                
                $newToken = $response['data']['result']['token'];
                $userInfo = $response['data']['result']['userInfo'];

                $firstName = strtolower(trim($userInfo['fn'] ?? 'user'));
                $lastName = strtolower(trim($userInfo['ln'] ?? $userInfo['uid']));
                
                $search = ['ç','ğ','ı','i','ö','ş','ü',' '];
                $replace = ['c','g','i','i','o','s','u','_'];
                $fileName = str_replace($search, $replace, $firstName . "_" . $lastName);

                $userData = $this->request->getUserData();

                $userData['x_jwt'] = $newToken;
                $userData['x_authorization'] = $newToken;

                $jsonFilePath = __DIR__ . "/../MyUsers/{$fileName}.json";
                file_put_contents($jsonFilePath, json_encode($userData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }

            return $jsonString;
    }



}
