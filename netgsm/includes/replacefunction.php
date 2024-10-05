<?php

class ReplaceFunction
{
    public function __construct()
    {
    }

    public function netgsm_replace_newuser_to_text($data)
    {
        if(empty($data['first_name']))
            $data['first_name']='uyeadi';
        if(empty($data['last_name']))
            $data['last_name']='uyesoyadi';

        $istenmeyen = array('[uye_adi]', '[uye_soyadi]', '[kullanici_adi]', '[uye_telefonu]', '[uye_epostasi]');
        $degisen    = array($data['first_name'], $data['last_name'], $data['user_login'], $data['phone'], $data['user_email']);
        $result      = str_replace($istenmeyen, $degisen, $data['message']);
        return $result;
    }

    public function netgsm_replace_neworder_to_text($data)
    {
        $istenmeyen = array('[siparis_no]','[toplam_tutar]','[uye_adi]','[uye_soyadi]','[uye_telefonu]','[uye_epostasi]','[kullanici_adi]','[urun_bilgileri]','[urun_kdv]','[urun_adi]');
        $degisen    = array($data['order_id'],$data['total'], $data['first_name'], $data['last_name'], $data['phone'], $data['user_email'], $data['user_login'], $data['items'], $data['items_kdv'], $data['items_name']);
        $result      = str_replace($istenmeyen, $degisen, $data['message']);
        return $result;
    }

    public function netgsm_replace_twofactorauth_text($data)
    {
        $istenmeyen = array('[kod]','[telefon_no]','[ad]','[soyad]','[mail]', '[referans_no]');
        $degisen    = array($data['otpcode'],$data['phone'], $data['first_name'], $data['last_name'], $data['user_email'], $data['refno']);
        $result      = str_replace($istenmeyen, $degisen, $data['message']);
        return $result;
    }

    public function netgsm_replace_order_status_changes($data)
    {
        $istenmeyen = array('[siparis_no]', '[uye_adi]', '[uye_soyadi]', '[uye_telefonu]', '[uye_epostasi]','[kullanici_adi]', '[kargo_firmasi]', '[takip_kodu]');
        $degisen    = array($data['order_id'], $data['first_name'], $data['last_name'], $data['phone'], $data['user_email'], $data['user_login'], $data['trackingCompany'], $data['trackingCode']);
        $result      = str_replace($istenmeyen, $degisen, $data['message']);
        return $result;
    }

    public function netgsm_replace_add_note($data)
    {
        $istenmeyen = array('[siparis_no]', '[not]', '[uye_adi]', '[uye_soyadi]', '[uye_telefonu]', '[uye_epostasi]','[kullanici_adi]', '[siparis_toplamtutar]');
        $degisen    = array($data['order_id'], $data['note'], $data['first_name'], $data['last_name'], $data['phone'], $data['user_email'], $data['user_login'], $data['total']);
        $result      = str_replace($istenmeyen, $degisen, $data['message']);
        return $result;
    }

    public function netgsm_replace_shipping_company($data)
    {
        $istenmeyen = array('mng', 'surat', 'yk', 'aras');
        $degisen    = array('MNG Kargo','Sürat Kargo','Yurtiçi Kargo','Aras Kargo');
        $result      = str_replace($istenmeyen, $degisen, $data);
        return $result;
    }

    public function netgsm_replace_bulksms($data)
    {
        $istenmeyen = array('[uye_adi]', '[uye_soyadi]', '[uye_telefonu]', '[uye_epostasi]','[kullanici_adi]');
        $degisen    = array($data['first_name'], $data['last_name'], $data['phone'], $data['user_email'], $data['user_login']);
        $result      = str_replace($istenmeyen, $degisen, $data['message']);
        return $result;
    }

    public function netgsm_spaceTrim($data)
    {
        $istenmeyen = array(' ','(',')','-','*','_');
        $degisen    = array('','','','','','');
        $result      = str_replace($istenmeyen, $degisen, $data);
        return $result;
    }

    function netgsm_cf7_replace_all_var($postedData, $data)
    {
        $array_keys = array_keys($postedData);
        foreach ($array_keys as &$array_key) {
            $array_key = '['.$array_key.']';
        }
        foreach ($postedData as &$item) {
            if (is_array($item)){
                $item = $item[0];
            }
        }
        $array_values = array_values($postedData);
        $result      = str_replace($array_keys, $array_values, $data);
        return $result;
    }

    function netgsm_replace_order_meta_datas($order, $text, $key1='[meta:', $key2=']')
    {
        $meta_datas = [];
    
        foreach ($order->meta_data as $meta_datum) {
            if (is_array($meta_datum->value)) {
                // Eğer değer bir dizi ise ve içindeki bilgiler varsa
                if (isset($meta_datum->value[0]['tracking_provider'])) {
                    $meta_datas['tracking_provider'] = $meta_datum->value[0]['tracking_provider'];
                    $meta_datas['tracking_number'] = $meta_datum->value[0]['tracking_number'];
                }
            } elseif (is_object($meta_datum->value)) {
                // Eğer değer bir nesne ise
                $meta_datas[$meta_datum->key] = json_encode($meta_datum->value);
    
                // Nesne içinde 'tracking_provider' ve 'tracking_number' varsa
                if (isset($meta_datum->value->tracking_provider)) {
                    $meta_datas['tracking_provider'] = $meta_datum->value->tracking_provider;
                }
                if (isset($meta_datum->value->tracking_number)) {
                    $meta_datas['tracking_number'] = $meta_datum->value->tracking_number;
                }
            } else {
                // Diğer durumlarda
                $meta_datas[$meta_datum->key] = $meta_datum->value;
    
                // Eğer diğer durumda 'tracking_provider' ve 'tracking_number' varsa
                if (isset($meta_datum->value['tracking_provider'])) {
                    $meta_datas['tracking_provider'] = $meta_datum->value['tracking_provider'];
                }
                if (isset($meta_datum->value['tracking_number'])) {
                    $meta_datas['tracking_number'] = $meta_datum->value['tracking_number'];
                }
            }
        }
    
        $array_keys = array_keys($meta_datas);
        foreach ($array_keys as &$array_key) {
            $array_key = $key1 . $array_key . $key2;
        }
        $array_values = array_values($meta_datas);
        $message = str_replace($array_keys, $array_values, $text);
    
        return $message;
    }
    
    function netgsm_replace_order_meta_datas2($metadatas, $text, $key1='[meta:', $key2=']')
    {
        $meta_datas = [];
        foreach ($metadatas as $key=> $item) {
            $value = [];
            foreach ($item as $val) {
                array_push($value, $val);
            }
            $meta_datas[$key] = implode(',', $value);
        }
        $array_keys = array_keys($meta_datas);
        foreach ($array_keys as &$array_key) {
            $array_key = $key1.$array_key.$key2;
        }
        $array_values = array_values($meta_datas);
        $message      = str_replace($array_keys, $array_values, $text);

        return $message;
    }

    function netgsm_replace_order_add_datas($order, $text, $param, $key1='[data:', $key2=']')
    {
        $datas = [];
        foreach ($order->{$param} as $key => $meta_datum) {
            if (is_array($meta_datum)){
                foreach ($meta_datum as $k=>$item) {
                    if(!is_array($item) && !is_object($item)){
                        $datas[$key.'_'.$k] = $item;
                    }
                }
            } else {
                $datas[$key] = $meta_datum;
            }
        }
        $array_keys = array_keys($datas);
        foreach ($array_keys as &$array_key) {
            $array_key = $key1.$array_key.$key2;
        }
        $array_values = array_values($datas);
        $message      = str_replace($array_keys, $array_values, $text);
        return $message;
    }

    function netgsm_meta_data_replace($data, $text, $key1='[meta:', $key2=']')
    {
        $meta_datas = [];
        foreach ($data as $key => $meta_datum) {
            $meta_datas[$key] = $meta_datum;
        }

        $array_keys = array_keys($meta_datas);
        foreach ($array_keys as &$array_key) {
            $array_key = $key1.$array_key.$key2;
        }
        $array_values = array_values($meta_datas);
        $message      = str_replace($array_keys, $array_values, $text);

        return $message;
    }

    function netgsm_replace_array($old, $new, $data, $startChar='[', $endChar=']')
    {
        $old2 = []; $new2 = [];

        foreach($old as $key=>$value) {
            foreach($new as $key2=>$value2) {
                if($value == $startChar.$key2.$endChar){
                    array_push($old2, $value );
                    array_push($new2, $value2);
                }
            }
        }
        $result = str_replace($old2, $new2, $data);
        return $result;
    }



    function netgsm_replace_date($text){
        $date = date('d.m.Y');
        $text = str_replace('[tarih]',$date,$text);

        $needle = '[tarih';
        $lastPos = 0;
        $positions = [];
        while (($lastPos = strpos($text, $needle, $lastPos))!== false) {
            $positions[] = $lastPos;
            $lastPos = $lastPos + strlen($needle);
        }

        $change = [];
        $val = [];
        foreach ($positions as $start) {
            $end =  $start + 6;
            $a = substr($text, $end);
            $b = explode(']', $a);
            $b = $b[0];
            array_push($change, '[tarih'.$b.']');
            $date = date('d.m.Y', strtotime( $b.' day'));
            array_push($val, $date);
        }

        $text = str_replace($change, $val, $text);
        $text = $this->netgsm_replace_time($text);
        return $text;
    }

    function netgsm_replace_time($text){
        $date = date('H:i:s');
        $text = str_replace('[saat]',$date,$text);

        $needle = '[saat';
        $lastPos = 0;
        $positions = [];
        while (($lastPos = strpos($text, $needle, $lastPos))!== false) {
            $positions[] = $lastPos;
            $lastPos = $lastPos + strlen($needle);
        }

        $change = [];
        $val = [];
        foreach ($positions as $start) {
            $end =  $start + 5;
            $a = substr($text, $end);
            $b = explode(']', $a);
            $b = $b[0];
            array_push($change, '[saat'.$b.']');
            $date = date('H:i:s', strtotime( $b.' minutes'));
            array_push($val, $date);
        }

        $text = str_replace($change, $val, $text);
        return $text;
    }


}

?>