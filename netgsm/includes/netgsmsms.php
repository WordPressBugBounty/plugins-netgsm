<?php

class Netgsmsms
{
    private $usercode;
    private $password;
    private $title;
    private $language = '';
    private $startDate;
    private $appkey = "63610054a66a4e22adeeae5f375b015f";

    public function __construct($usercode, $password, $title = '', $TR_Char = '')
    {     //WP USE
        $this->usercode = trim($usercode);
        $this->password = $password;

        $this->title = $title;

        $this->language = '';
        if ($TR_Char == 1) {
            $this->language = "dil='TR'";
        }
    }

    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }
    public function getStartDate()
    {
        if ($this->startDate == '') {
            return '';
        }
        return date('dmYHi', strtotime($this->startDate));
    }

    public function sendSMS($phone, $mesaj, $filter = 0)
    {
        $filter = $this->iysDurumControl($filter);

        //WP USE
        $phones = array();
        $parts = explode(',', $phone);
        foreach ($parts as $value) {
            if ($value != '') {
                array_push($phones, $value);
            }
        }
        $number = "";
        foreach ($phones as $value) {
            $number .= '<no>' . $value . '</no>';
        }

        $PostAdress = 'https://api.netgsm.com.tr/sms/send/xml';
        $xml = array('body' => '<?xml version="1.0" encoding="UTF-8"?>
                            <mainbody>
                                <header>
                                    <company wp="1" ' . $this->language . '>NETGSM</company>
                                    <usercode>' . $this->usercode . '</usercode>
                                    <password>' . $this->password . '</password>
                                    <appkey>' . $this->appkey . '</appkey>
                                    <startdate>' . $this->getStartDate() . '</startdate>
                                    <stopdate></stopdate>
                                    <type>1:n</type>
                                    <msgheader>' . $this->title . '</msgheader>
                                    <filter>' . $filter . '</filter>
                                </header>
                                <body>
                                    <msg><![CDATA[' . $mesaj . ']]></msg>
                                    ' . $number . '
                                </body>
                            </mainbody>');
        $response = wp_remote_post($PostAdress, $xml);
        return $this->smsCevap($this->responseDecoder($response)['body']);
    }
    public function iysDurumControl($filter)
    {
        //giriş sayfasında gelen mesah turu icerik bilgisi netgsm_iys_control
        $netgsm_iys_control = sanitize_text_field(get_option("netgsm_iys_control"));
        //iys bolumunden gelen iys kontrolu
        $netgsm_brandcode_control = sanitize_text_field(get_option("netgsm_brandcode_control"));
        $netgsm_recipient_type = sanitize_text_field(get_option("netgsm_recipient_type"));
        $netgsm_brandcode_text = sanitize_text_field(get_option("netgsm_brandcode_text"));

        $starterFilter = $filter;



        if ($netgsm_brandcode_control && $netgsm_brandcode_text != "") {
            switch ($netgsm_recipient_type) {
                case 0:
                    $filter = 0;
                    break;
                case 1:
                    $filter = 11;
                    break;
                case 2:
                    $filter = 12;
                    break;
                case 3:
                    $filter = 13;
                    break;

                default:
                    $filter = 0;
                    break;
            }
        }

        if ($netgsm_iys_control) {
            switch ($netgsm_iys_control) {
                case 0:
                    $filter = 0;
                    break;
                case 1:
                    $filter = 11;
                    break;
                case 2:
                    $filter = 12;
                    break;
                case 3:
                    $filter = 0;
                    break;

                default:
                    $filter = 0;
                    break;
            }
        }

        if ($netgsm_brandcode_control && $starterFilter == 3) {

            $filter = 13;
        }
        if ($netgsm_brandcode_control && $netgsm_brandcode_text == '') {
            $filter = 0;
        }
        if (!$netgsm_iys_control && !$netgsm_brandcode_control) {
            $filter = 0;
        }
        return $filter;
    }

    /*
     * otp sms gönderimi
     */
    public function sendOTPSMS($phone, $message)
    {
        $request_url = 'https://api.netgsm.com.tr/sms/send/otp';
        $xml = array(
            'body' => '<?xml version="1.0"?>
                                <mainbody>
                                    <header>
                                        <usercode>' . $this->usercode . '</usercode>
                                        <password>' . $this->password . '</password>
                                        <appkey>' . $this->appkey . '</appkey>
                                        <msgheader>' . $this->title . '</msgheader>
                                    </header>
                                    <body>
                                        <msg><![CDATA[' . substr($message, 0, 150) . ']]></msg>
                                        <no>' . $phone . '</no>
                                    </body>
                                </mainbody>'
        );
        $response = wp_remote_post($request_url, $xml);
        return $this->xmlCevap($this->responseDecoder($response)['body']);
    }

    public function sendBulkSMS($data, $filter = 0)
    {
        $filter = $this->iysDurumControl($filter);
        //WP USE
        $PostAdress     = 'https://api.netgsm.com.tr/sms/send/xml';
        $xml            = array('body' => '<?xml version="1.0" encoding="UTF-8"?>
                                            <mainbody>
                                              <header>
                                                <company wp="1" ' . $this->language . '>Netgsm</company>
                                                    <usercode>' . $this->usercode . '</usercode>
                                                    <password>' . $this->password . '</password>
                                                    <appkey>' . $this->appkey . '</appkey>
                                                    <startdate></startdate>
                                                    <stopdate></stopdate>
                                                    <type>n:n</type>
                                                    <msgheader>' . $this->title . '</msgheader>
                                                    <filter>' . $filter . '</filter>
                                                    </header>
                                                <body>
                                                  ' . $data . '</body>
                                            </mainbody>');
        $response = wp_remote_post($PostAdress, $xml);

        return $this->smsCevap($this->responseDecoder($response)['body']);
    }

    public function xmlCevap($input)
    {
        $code = explode('<code>', $input);
        $code = explode('</code>', $code[1]);
        return $this->smsCevap($code[0]);
    }

    public function smsCevap($input)
    {        //WP USE
        $response = explode(' ', $input);
        if ($response[0] == '00' || $response[0] == '02') {
            $result = array('durum' => 1, 'kod' => $response[0], 'gorevid' => $response[1], 'mesaj' => 'Mesaj Gönderimi Başarılı.');
        } elseif ($response[0] == '20') {
            $result = array('durum' => 0, 'kod' => $response[0],  'mesaj' => 'Mesaj metninde ki problemden dolayı gönderilemediğini veya standart maksimum mesaj karakter sayısını geçtiğini ifade eder. ');
        } elseif ($response[0] == '30') {
            $result = array('durum' => 0, 'kod' => $response[0],  'mesaj' => 'Geçersiz kullanıcı adı , şifre veya kullanıcınızın API erişim izninin olmadığını gösterir.
Ayrıca eğer API erişiminizde IP sınırlaması yaptıysanız ve sınırladığınız ip dışında gönderim sağlıyorsanız 30 hata kodunu alırsınız. API erişim izninizi veya IP sınırlamanızı , web arayüzümüzden; sağ üst köşede bulunan ayarlar> API işlemleri menüsunden kontrol edebilirsiniz.');
        } elseif ($response[0] == '40') {
            $result = array('durum' => 0, 'kod' => $response[0],  'mesaj' => 'Mesaj başlığınızın (gönderici adınızın) sistemde tanımlı olmadığını ifade eder. Gönderici adlarınızı API ile sorgulayarak kontrol edebilirsiniz.');
        } elseif ($response[0] == '50') {
            $result = array('durum' => 0, 'kod' => $response[0],  'mesaj' => 'Gönderilen numarayı kontrol ediniz.');
        } elseif ($response[0] == '51') {
            $result = array('durum' => 0, 'kod' => $response[0],  'mesaj' => 'Tanımlı İYS marka bilgisi bulunamadı, kontrol ediniz.');
        } elseif ($response[0] == '60') {
            $result = array('durum' => 0, 'kod' => $response[0],  'mesaj' => 'Hesabınızda OTP SMS Paketi tanımlı değildir, kontrol ediniz.');
        } elseif ($response[0] == '70') {
            $result = array('durum' => 0, 'kod' => $response[0],  'mesaj' => 'Hatalı sorgulama. Gönderdiğiniz parametrelerden birisi hatalı veya zorunlu alanlardan birinin eksik olduğunu ifade eder.');
        } elseif ($response[0] == '80') {
            $result = array('durum' => 0, 'kod' => $response[0],  'mesaj' => 'Sorgulama sınır aşımı.(dakikada 100 adet gönderim yapılabilir.)');
        } else {
            $result = array('durum' => 0, 'kod' => $response[0],  'mesaj' => 'Bilinmeyen bir hatadan dolayı mesaj gönderilemedi.');
        }
        return $result;
    }

    public function getSmsBaslik()
    {     //WP USE
        $PostAdress = "https://api.netgsm.com.tr/sms/header/get/?usercode=$this->usercode&password=$this->password&appkey=" . $this->appkey;
        $result = wp_remote_get($PostAdress);
        return $this->getSmsBaslikSonuc($this->responseDecoder($result)['body']);
    }

    private function getSmsBaslikSonuc($response)
    {
        if ($response == 30) {
            return 'Geçersiz kullanıcı adı , şifre veya kullanıcınızın API erişim izninin olmadığını gösterir.
Ayrıca eğer API erişiminizde IP sınırlaması yaptıysanız ve sınırladığınız ip dışında gönderim sağlıyorsanız 30 hata kodunu alırsınız. API erişim izninizi veya IP sınırlamanızı , web arayüzümüzden; sağ üst köşede bulunan ayarlar> API işlemleri menüsunden kontrol edebilirsiniz.';
        } else {
            $result = explode('<br>', $response);
            return $result;
        }
    }

    private function getKrediSonuc($data)
    {
        $response = explode(' ', $data);
        $errorCode = $response[0];
        if ($errorCode == 30) {
            $result = '30Geçersiz kullanıcı adı , şifre veya kullanıcınızın API erişim izninin olmadığını gösterir.
Ayrıca eğer API erişiminizde IP sınırlaması yaptıysanız ve sınırladığınız ip dışında gönderim sağlıyorsanız 30 hata kodunu alırsınız. API erişim izninizi veya IP sınırlamanızı , web arayüzümüzden; sağ üst köşede bulunan ayarlar> API işlemleri menüsunden kontrol edebilirsiniz.';
        } elseif ($errorCode == 40) {
            $result = '40Arama kriterlerinize göre listelenecek kayıt olmadığını ifade eder.';
        } elseif ($errorCode == 70) {
            $result = '70Hatalı sorgulama. Gönderdiğiniz parametrelerden birisi hatalı veya zorunlu alanlardan birinin eksik olduğunu ifade eder.';
        } else {
            $result = '99' . $response[1];
        }
        return $result;
    }

    public function getMsgHeader($user, $pass)
    {
        $url = 'https://api.netgsm.com.tr/sms/rest/v2/msgheader';
        $auth = base64_encode($user . ':' . $pass);
        $response = wp_remote_get($url, array(
            'headers' => array(
                'Authorization' => 'Basic ' . $auth,
                'Accept'        => 'application/json', // gerekiyorsa
            ),
        ));
        if (is_wp_error($response)) {
            $result = array('giris' => CURLOPT_SSL_FALSESTART, 'durum' => 'error', 'mesaj' => $response->get_error_message());
            return $result;
        }
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if ($data['code'] == 00) {
            $result = array('giris' => true, 'durum' => 'success', 'mesaj' => 'Success Login');
        } elseif ($data['code'] == 30) {
            $result = array('giris' => false, 'durum' => 'error', 'mesaj' => 'Geçersiz kullanıcı adı , şifre veya kullanıcınızın API erişim izninin olmadığını gösterir.');
        } elseif ($data['code'] == 70) {
            $result = array('giris' => false, 'durum' => 'error', 'mesaj' => 'Kullanıcı adnız veya şifrenizi kontrol ediniz');
        } elseif ($data['code'] == 100 || $data['code'] == 101) {
            $result = array('giris' => false, 'durum' => 'error', 'mesaj' => 'Sistemsel hata tekrar deneyiniz');
        } else {
            $result = array('giris' => false, 'durum' => 'error', 'mesaj' => 'Bilgilerinizi kontro ediniz code:' . $data['code']);
        }
        return $result;
    }

    public function netgsm_GetKredi($user, $pass, $balanceOrBakiye)
    {

        //WP USE
        $url = 'https://api.netgsm.com.tr/balance';
        $payload = json_encode(array(
            'usercode' => $user,  // Kendi kullanıcı adınızı girin
            'password' => $pass,  // Kendi şifrenizi girin
            'stip' => $balanceOrBakiye
        ));
        $response = wp_remote_post($url, array(
            'method'    => 'POST',
            'body'      => $payload,
            'headers'   => array(
                'Content-Type' => 'application/json',
            ),
        ));
        if (is_wp_error($response)) {
            $result = array('giris' => 'error', 'durum' => 'error', 'mesaj' => $response->get_error_message());
            return $result;
        }
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);
        if (isset($data['balance'])) {
            $adet_sms = '';
            if ($balanceOrBakiye == 2) {
                $adet_sms = 'balance ' .  $data['balance'];;
            } else {
                $balanceList = $data['balance'];
                foreach ($balanceList as $key => $balance) {
                    $adet_sms .= $balance['amount'] . ' ' .  $balance['balance_name'] . " ";
                }
            }
            $result = array('giris' => 'success', 'durum' => true, 'tipmsj' => $adet_sms);
            return $result;
        }

        if ($data['code'] == 30) {
            $result = array('giris' => 'error', 'durum' => false, 'mesaj' => 'Geçersiz kullanıcı adı , şifre veya kullanıcınızın API erişim izninin olmadığını gösterir.');
            return $result;
        }
        if ($data['code'] == 60) {
            $result = array('giris' => 'error', 'durum' => false, 'mesaj' => 'Bakiye yetersiz.');
            return $result;
        }
        if ($data['code'] == 70) {
            $result = array('giris' => 'error', 'durum' => false, 'mesaj' => 'Kullanıcı adnız veya şifrenizi kontrol ediniz');
            return $result;
        }
        if (!isset($data['balance'])) {
            $result = array('giris' => 'error', 'durum' => false, 'mesaj' => 'Kullanıcı balance bilgileriniz kontrol ediniz');
            return $result;
        }
        $result = array('giris' => 'error', 'durum' => false, 'mesaj' => 'Kullanıcı adnız veya şifrenizi bilgilerini kontrol ediniz');
        return $result;
    }

    public function netgsm_GetPaket($user, $pass)
    {     //WP USE
        $response = wp_remote_get("https://api.netgsm.com.tr/get_kredi.asp?usercode=$user&password=$pass&tip=1&appkey=" . $this->appkey);
        $data = explode(' ', $this->responseDecoder($response)['body']);
        if ($data[0] == '30') {
            $result = array('giris' => 'error', 'durum' => false, 'mesaj' => 'Geçersiz kullanıcı adı , şifre veya kullanıcınızın API erişim izninin olmadığını gösterir.');
        } elseif ($data[0] == '40') {
            $result = array('giris' => 'success', 'durum' => true, 'mesaj' => 'Arama kriterlerinize göre listelenecek kayıt olmadığını ifade eder.');
        } else {
            if ($data[0] > 0) {
                $result = array('giris' => 'success', 'durum' => true, 'mesaj' => '', 'kredi' => $data[0], 'tipmsj' => 'Paket SMS Kalan ' . $data[0]); // sms
            } else {
                $result = array('giris' => 'success', 'durum' => true, 'mesaj' => ' Bakiye Satın Al', 'kredi' => $data[0], 'tipmsj' =>  'Paket SMS Kalan ' . $data[0]);
            }
        }
        return $result;
    }

    public function netgsm_GirisSorgula($user, $pass)    //WP USE
    {
        $message = "";
        if (empty($user) && !empty($pass))   $message = " Kullanıcı adı alanı boş.";
        if (empty($pass) && !empty($user))   $message = " Şifre alanı boş.";
        if (empty($user) && empty($pass))    $message = " Kullanıcı adı & şifre boş.";

        if ($message != "") {
            $result = array(
                'durum' => 'danger',
                'icon' => 'fa-exclamation-triangle',
                'mesaj' => $message,
                'btnkontrol' => 'disabled',
                'href' => ''
            );
            return json_encode($result);
        }

        $result = $this->getMsgHeader($user, $pass);

        if (!$result['giris']) {
            $response = array(
                'durum' => 'danger',
                'icon' => 'fa-exclamation-triangle',
                'mesaj' => $result['mesaj'],
                'btnkontrol' => 'disabled',
                'href' => ''
            );
            return json_encode($response);
        }
        $responsePaket = $this->netgsm_GetKredi($user, $pass, 1);
        $responseKredi = $this->netgsm_GetKredi($user, $pass, 2);
        if ($responseKredi['durum'] || $responsePaket['durum']) {
            $result = array(
                'durum' => 'success',
                'icon' => 'fa-check',
                'mesajKredi' => $responseKredi['tipmsj'],
                'mesajPaket' => $responsePaket['tipmsj'],
                'btnkontrol' => 'enabled',
                'href' => ''
            );
        } else {
            $result = array(
                'durum' => 'warning',
                'icon' => 'fa-shopping-cart',
                'mesajKredi' => ' Bakiye: ' . $responseKredi['mesaj'] . ' <i class=\'fa fa-external-link\'></i>',
                'mesajPaket' => ' Paket: ' . $responsePaket['mesaj'] . ' Paket satın al <i class=\'fa fa-external-link\'></i>',
                'btnkontrol' => 'enabled',
                'href' => 'https://portal.netgsm.com.tr/'
            );
        }
        return json_encode($result);

        if ($response['giris'] == 'success') {     //Giriş Başarılı
            if ($response['durum'] == 'success') {  //kredi varsa
                $result = array(
                    'durum' => 'success',
                    'icon' => 'fa-chech',
                    'mesaj' => $response['tipmsj'] . " : " . $response['kredi'],
                    'btnkontrol' => 'enabled',
                    'href' => ''
                );
            } else { //kredi yoksa
                $getpackage = $this->netgsm_GetPaket($user, $pass);
                if ($getpackage['giris'] == 'success') {    //giriş başarılı
                    if ($getpackage['durum'] == 'success') {  //kredi varsa
                        $result = array(
                            'durum' => 'success',
                            'icon' => 'fa-check',
                            'mesaj' => "<i class='fa fa-envelope-o'></i> " . ' SMS Bakiyeniz : ' . $getpackage['kredi'],
                            'btnkontrol' => 'enabled',
                            'href' => ''
                        );
                    } else { //kredi yoksa satın almaya yönlendir (Hem kredi hem paket yok.)
                        $result = array(
                            'durum' => 'warning',
                            'icon' => 'fa-shopping-cart',
                            'mesaj' => ' Krediniz:' . $response['kredi'] . '. Kredi satın al <i class=\'fa fa-external-link\'></i>',
                            'btnkontrol' => 'enabled',
                            'href' => 'https://portal.netgsm.com.tr/'
                        );
                    }
                }
            }
        } else { // giriş başarısız
            if (empty($user) && !empty($pass))
                $message = " Kullanıcı adı alanı boş.";
            elseif (empty($pass) && !empty($user))
                $message = " Şifre alanı boş.";
            elseif (empty($user) && empty($pass))
                $message = " Kullanıcı adı & şifre boş.";
            else {
                $message = " Kullanıcı adı veya şifreniz hatalı.";
            }
            $result = array(
                'durum' => 'danger',
                'icon' => 'fa-exclamation-triangle',
                'mesaj' => $message,
                'btnkontrol' => 'disabled',
                'href' => ''
            );
        }
        return json_encode($result);
    }

    private function inboxResponse($data)
    {
        $result = array();
        $result['status']  =  "400";
        $i = 1;
        if ($data == 30) {
            $result['message'] = " Geçersiz kullanıcı adı , şifre veya kullanıcınızın API erişim izninin olmadığını gösterir.
Ayrıca eğer API erişiminizde IP sınırlaması yaptıysanız ve sınırladığınız ip dışında gönderim sağlıyorsanız 30 hata kodunu alırsınız. API erişim izninizi veya IP sınırlamanızı , web arayüzümüzden; sağ üst köşede bulunan ayarlar> API işlemleri menüsunden kontrol edebilirsiniz.";
        } elseif ($data == 40) {
            $result['message'] = " Gösterilecek mesajınızın olmadığını ifade eder. Api ile mesajlarınızı eğer startdate ve stopdate parametlerini kullanmıyorsanız sadece bir kere listeyebilirsiniz. Listelenen mesajlar diğer sorgulamalarınızda gelmez.";
        } elseif ($data == 50) {
            $result['message'] = " Tarih formatı hatalıdır.";
        } elseif ($data == 51) {
            $result['message'] = " Tanımlı İYS marka kodu bilgisi bulunamadı.";
        } elseif ($data == 60) {
            $result['message'] = " Arama kiterlerindeki startdate ve stopdate zaman farkının 30 günden fazla olduğunu ifade eder.";
        } elseif ($data == 70) {
            $result['message'] = " Hatalı sorgulama. Gönderdiğiniz parametrelerden birisi hatalı veya zorunlu alanlardan birinin eksik olduğunu ifade eder.";
        } else {
            $parts = explode('<br>', $data);

            $result['status']  =  "200";

            foreach ($parts as $part) {
                $messages = explode(' | ', $part);
                $result[$i]['phone'] = $messages[0];
                $result[$i]['message'] = $messages[1];
                $result[$i]['time'] = $messages[2];
                $i++;
            }
        }

        return ($result);
    }

    public function inbox()
    {
        $xml = array('body' => '<?xml version="1.0"?>
                                    <mainbody>
                                        <header>
                                            <company>Netgsm</company>
                                            <usercode>' . $this->usercode . '</usercode>
                                            <password>' . $this->password . '</password>
                                            <appkey>' . $this->appkey . '</appkey>
                                            <startdate>' . date('dmYHi', time() - 60 * 60 * 24 * 30) . '</startdate>
                                            <stopdate>' . date('dmYHi', time() + 60 * 60 * 24) . '</stopdate>
                                            <type>0</type>
                                        </header>
                                    </mainbody>');
        $response = wp_remote_post('https://api.netgsm.com.tr/sms/receive/xml', $xml);
        return $this->inboxResponse($this->responseDecoder($response)['body']);
    }

    public function phoneToUser($phone, $users)
    {
        $result = array();
        foreach ($users as $user) {
            if ($user->billing_phone == $phone) {
                $result = $user;
                break;
            } else {
                $result = 0;
            }
        }
        return $result;
    }

    /*
     * Netsantral Reporları
     * Dakikada en fazla 2 kez sorgulanabilir
     */
    public function getVoipReport()
    {
        date_default_timezone_set('Europe/Istanbul');
        $startDate = date('dmYHi', time() - 60 * 60 * 24);
        $endDate = date('dmYHi', time());
        $PostAdress = 'https://api.netgsm.com.tr/netsantral/report/xml';
        $xml = array('body' => "<?xml version='1.0'?>
            <mainbody>
                <header>
                    <company>Netgsm</company>
                    <usercode>" . $this->usercode . "</usercode>
                    <password>" . $this->password . "</password>
                    <appkey>" . $this->appkey . "</appkey>
                    <startdate>" . $startDate . "</startdate>
                    <stopdate>" . $endDate . "</stopdate>
                    <version>3</version>
                </header>
            </mainbody>");
        $results = wp_remote_post($PostAdress, $xml);
        $results = $this->responseDecoder($results)['body'];

        if ($results != '' && in_array(trim($results), [100, 101])) {
            $response = ['error' => ['code' => trim($results), 'message' => 'Dakikada en fazla 2 kez sorgulanabilir.']];
            return json_encode($response, 256);
        }
        if ($results != '' && in_array(trim($results), [40])) {
            $response = ['error' => ['code' => trim($results), 'message' => 'Son 24 saat içinde listelenecek kayıt yok.']];
            return json_encode($response, 256);
        }
        if ($results != '' && in_array(trim($results), [30])) {
            $response = ['error' => ['code' => trim($results), 'message' => 'Geçersiz kullanıcı adı, şifre veya kullanıcınızın API erişim izniniz yok.']];
            return json_encode($response, 256);
        }

        if ($results != '' && in_array(trim($results), [50])) {
            $response = ['error' => ['code' => trim($results), 'message' => 'Tarih formatının hatalı.']];
            return json_encode($response, 256);
        }

        if ($results != '' && in_array(trim($results), [60])) {
            $response = ['error' => ['code' => trim($results), 'message' => 'Zaman farkının 7 günden fazla']];
            return json_encode($response, 256);
        }

        if ($results != '' && in_array(trim($results), [70])) {
            $response = ['error' => ['code' => trim($results), 'message' => 'Hatalı sorgulama.']];
            return json_encode($response, 256);
        }

        if ($results != '' && in_array(trim($results), [80])) {
            $response = ['error' => ['code' => trim($results), 'message' => 'Netsantral hizmeti kullanılmıyor.']];
            return json_encode($response, 256);
        }

        $results = explode('<br/>', $results);

        $response = [];
        foreach ($results as $result) {
            $result = trim($result);
            $cdr = explode('|', $result);
            $temp = [];
            if (isset($cdr[0]) && $cdr[0] != '') {
                $temp['call_id'] = $cdr[0];
                $temp['date'] = $cdr[1];
                $temp['dial_number'] = $cdr[2];
                $temp['caller_number'] = $cdr[3];
                $temp['time'] = $cdr[4];
                $temp['direction'] = $cdr[5];
                //$temp['recording'] = $cdr[6];
                array_push($response, $temp);
            }
        }
        $json['success'] = ['message' => 'Son 24 saatin kayıtları listeleniyor.'];
        $json['data'] = $response;

        return json_encode($json, 256);
    }

    /*
     * Santral Raporları (Sabit Telefon)
     * Dakikada en fazla 2 kez sorgulanabilir
     */
    public function getPhoneReport()
    {
        date_default_timezone_set('Europe/Istanbul');
        $startDate = date('dmYHi', time() - 60 * 60 * 24);
        $endDate = date('dmYHi', time());
        $PostAdress = 'https://api.netgsm.com.tr/voice/report/xml';
        $xml = array('body' => "<?xml version='1.0'?>
            <mainbody>
                <header>
                    <company>Netgsm</company>
                    <usercode>" . $this->usercode . "</usercode>
                    <password>" . $this->password . "</password>
                    <date></date>
                    <direction>4</direction>
                </header>
            </mainbody>");
        $results = wp_remote_post($PostAdress, $xml);
        $results = $this->responseDecoder($results)['body'];


        if ($results != '' && in_array(trim($results), [100, 101])) {
            $response = ['error' => ['code' => trim($results), 'message' => 'Dakikada en fazla 2 kez sorgulanabilir.']];
            return json_encode($response, 256);
        }
        if ($results != '' && in_array(trim($results), [40])) {
            $response = ['error' => ['code' => trim($results), 'message' => 'Son 24 saat içinde listelenecek kayıt yok.']];
            return json_encode($response, 256);
        }
        if ($results != '' && in_array(trim($results), [30])) {
            $response = ['error' => ['code' => trim($results), 'message' => 'Geçersiz kullanıcı adı, şifre veya kullanıcınızın API erişim izniniz yok.']];
            return json_encode($response, 256);
        }

        if ($results != '' && in_array(trim($results), [50])) {
            $response = ['error' => ['code' => trim($results), 'message' => 'Tarih formatının hatalı.']];
            return json_encode($response, 256);
        }

        if ($results != '' && in_array(trim($results), [70])) {
            $response = ['error' => ['code' => trim($results), 'message' => 'Hatalı sorgulama.']];
            return json_encode($response, 256);
        }


        $results = explode('<br/>', $results);

        $response = [];
        foreach ($results as $result) {
            $result = trim($result);
            $cdr = explode(' | ', $result);
            $temp = [];
            if (isset($cdr[0]) && $cdr[0] != '') {
                $temp['caller_number'] = $cdr[0];
                $temp['date'] = $cdr[1];
                $temp['time'] = $cdr[2];
                $temp['direction'] = $cdr[3];
                array_push($response, $temp);
            }
        }
        $json['success'] = ['message' => 'Bugünün kayıtları listeleniyor.'];
        $json['data'] = $response;

        return json_encode($json, 256);
    }


    public function getphoneClear($phone)
    {
        $unwanted = array(' ', '+', '(', ')', '.', '-', '&amp;', 'nbsp;');
        $replace    = array('', '', '', '', '', '', '', '');
        $result      = str_replace($unwanted, $replace, $phone);
        return $result;
    }

    public function timeconvert($time)
    {
        if (strpos($time, ':') !== false) {
            return $time;
        }
        if ($time < 60) {
            $second = ceil($time);
            if ($second < 10) {
                $second = '0' . $second;
            }
            return '00:00:' . $second;
        }
        $minute = floor($time / 60);
        $second = $time % 60;
        if ($minute < 60) {
            if ($minute < 10) {
                $minute = '0' . $minute;
            }
            if ($second < 10) {
                $second = '0' . $second;
            }
            return '00:' . $minute . ':' . $second;
        }
        $hour = floor($minute / 60);
        $minute = $minute % 60;
        if ($hour < 10) {
            $hour = '0' . $hour;
        }
        if ($minute < 10) {
            $minute = '0' . $minute;
        }
        if ($second < 10) {
            $second = '0' . $second;
        }
        return $hour . ':' . $minute . ':' . $second;
    }

    private function responseDecoder($response)
    {
        if (is_array($response) && !empty($response['body'])) {
            return $response;
        }
        $array['body'] = '';
        return $array;
    }


    public function iysadd($phone, $email, $date, $brandcode, $recipient_type, $type_array) //İYS'ye adres ekleme
    {
        if ($phone != '' || $email != '') {
            $jsonarray = array(
                "header" => array("username" => $this->usercode, "password" => $this->password, "brandCode" => $brandcode, "appkey" => $this->appkey),
                "body" => array(
                    "data" => []
                )
            );
            foreach ($type_array as $typearray) {
                if ($typearray == 'EPOSTA' && $email != '') {
                    $address = $email;
                    array_push($jsonarray['body']['data'], array(
                        "type" => $typearray,
                        "source" => "HS_WEB",
                        "recipient" => $address,
                        "status" => "ONAY",
                        "consentDate" => $date,
                        "recipientType" => $recipient_type
                    ));
                }
                if ($typearray != 'EPOSTA' && $phone != '') {
                    $address = $phone;
                    array_push($jsonarray['body']['data'], array(
                        "type" => $typearray,
                        "source" => "HS_WEB",
                        "recipient" => $address,
                        "status" => "ONAY",
                        "consentDate" => $date,
                        "recipientType" => $recipient_type
                    ));
                }
            }

            $url = "https://api.netgsm.com.tr/iys/add";
            $response = wp_remote_post($url, array(
                'headers'   => array('Content-Type' => 'application/json; charset=utf-8'),
                'body'      => json_encode($jsonarray),
                'method'    => 'POST'
            ));

            return $response;
        }
    }

    public function netasistan_yenitoken($appkey, $userkey)
    {
        $url = "http://www.netasistan.com/napi/v1/auth";
        $response = wp_remote_post($url, array(
            'headers'   => array(
                'Content-Type' => 'application/json; charset=utf-8',
                'app-key' => $appkey,
                'user-key' => $userkey
            ),
            'sslverify' => FALSE,
            'body' => '',
            'method'    => 'GET'
        ));

        return $response;
    }

    public function netasistan_ticket($ticket_name, $ticket_lastname, $ticket_number, $ticket_email, $ticket_header, $ticket_content, $ticket_etiketler, $token)
    {

        $jsonarray = array(
            'title' => $ticket_header,
            'content' => $ticket_content,
            'priority' => "1",
            'tags' => $ticket_etiketler,
            'phone' => $ticket_number,
            'email' => $ticket_email,
            'name' => $ticket_name,
            'surname' => $ticket_lastname
        );

        $url = "https://www.netasistan.com/napi/v1/ticket";
        $response = wp_remote_post($url, array(
            'headers'   => array(
                'Content-Type' => 'application/json; charset=utf-8',
                'username' => $this->usercode,
                'Authorization' => 'Bearer ' . $token,
            ),
            'sslverify' => FALSE,
            'body'      => json_encode($jsonarray),
            'method'    => 'POST'
        ));

        return $response;
    }
}
