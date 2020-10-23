<?php 
set_time_limit(0);
 class Ups {
    public $url = "http://ws.ups.com.tr/wsCreateShipment/wsCreateShipment.asmx";
    public $customerNumber = "89E***";
    public $username = "Yv6M****************";
    public $password = "jStZ****************";
    public $SessionID = "";
    public $ShipperName = "XXXX Company";
    public $ShipperAddress = "Yenimahalle/ Ankara";
    public $ShipperCityCode = 6;//city code
    public $ShipperAreaCode = 1528; //area code

    
    public function getir( $xml  ){
      $ch = curl_init();
      curl_setopt( $ch, CURLOPT_URL, $this->url );
      curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
      curl_setopt( $ch, CURLOPT_POST, 1 );
      curl_setopt( $ch, CURLOPT_POSTFIELDS, $xml );
      curl_setopt( $ch, CURLOPT_HTTPHEADER, 
          [
              "Content-Type: text/xml; charset=UTF-8"
          ]
      );
      $output = curl_exec( $ch );
      return $output;    
    }

    public function xml_filtrele( $xml_veri, $filtre ){
      preg_match_all('@'.$filtre.'@si', $xml_veri, $yeni);
      if ( $yeni[0]  && $yeni[1][0] ) {
        return $yeni[1][0];
      }
    }

    public function oturum_olustur( ){
        $xml = '<?xml version="1.0" encoding="utf-8"?>'
        .'<soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">'
        .'<soap12:Body>'
            .'<Login_Type1 xmlns="http://ws.ups.com.tr/wsCreateShipment">'
                .'<CustomerNumber>'.$this->customerNumber.'</CustomerNumber>'
                .'<UserName>'.$this->username.'</UserName>'
                .'<Password>'.$this->password.'</Password>'
            .'</Login_Type1>'
        .'</soap12:Body>'
        .'</soap12:Envelope>';

        $xml_veri = $this->getir( $xml );
        $this->SessionID = $this->xml_filtrele( $xml_veri , '<SessionID>(.*?)</SessionID>'  );
        return $this->SessionID;

    }
    public function gonderi_olustur( $musteri ){
      $this->oturum_olustur();
      $xml = '<?xml version="1.0" encoding="utf-8"?>
      <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
        <soap:Body>
          <CreateShipment_Type2 xmlns="http://ws.ups.com.tr/wsCreateShipment">
            <SessionID>'.$this->SessionID.'</SessionID>
            <ShipmentInfo>
              <ShipperAccountNumber>'.$this->customerNumber.'</ShipperAccountNumber>
              <ShipperName>'.$this->ShipperName.'</ShipperName>
              <ShipperContactName></ShipperContactName>
              <ShipperAddress>'.$this->ShipperAddress.'</ShipperAddress>
              <ShipperCityCode>'.$this->ShipperCityCode.'</ShipperCityCode>
              <ShipperAreaCode>'.$this->ShipperAreaCode.'</ShipperAreaCode>
              <ShipperPostalCode></ShipperPostalCode>
              <ShipperPhoneNumber></ShipperPhoneNumber>
              <ShipperPhoneExtension></ShipperPhoneExtension>
              <ShipperMobilePhoneNumber></ShipperMobilePhoneNumber>
              <ShipperEMail></ShipperEMail>
              <ShipperExpenseCode></ShipperExpenseCode>
              <ConsigneeAccountNumber></ConsigneeAccountNumber>
              <ConsigneeName>'.$musteri["ad_soyad"].'</ConsigneeName>
              <ConsigneeContactName></ConsigneeContactName>
              <ConsigneeAddress>'.$musteri["adres"].'</ConsigneeAddress>
              <ConsigneeCityCode>'.$musteri["sehir_kodu"].'</ConsigneeCityCode>
              <ConsigneeAreaCode>'.$musteri["bolge_kodu"].'</ConsigneeAreaCode>
              <ConsigneePostalCode></ConsigneePostalCode>
              <ConsigneePhoneNumber></ConsigneePhoneNumber>
              <ConsigneePhoneExtension></ConsigneePhoneExtension>
              <ConsigneeMobilePhoneNumber></ConsigneeMobilePhoneNumber>
              <ConsigneeEMail></ConsigneeEMail>
              <ConsigneeExpenseCode></ConsigneeExpenseCode>
              <ServiceLevel>'.$musteri["hizmet_turu"].'</ServiceLevel>
              <PaymentType>'.$musteri["odeme_tipi"].'</PaymentType>
              <PackageType>'.$musteri["paket_turu"].'</PackageType>
              <NumberOfPackages>'.$musteri["paket_adedi"].'</NumberOfPackages>
              <CustomerReferance></CustomerReferance>
              <CustomerInvoiceNumber></CustomerInvoiceNumber>
              <DescriptionOfGoods></DescriptionOfGoods>
              <DeliveryNotificationEmail></DeliveryNotificationEmail>
              <IdControlFlag>0</IdControlFlag>
              <PhonePrealertFlag>0</PhonePrealertFlag>
              <SmsToShipper>0</SmsToShipper>
              <SmsToConsignee>0</SmsToConsignee>
              <InsuranceValue>0</InsuranceValue>
              <InsuranceValueCurrency></InsuranceValueCurrency>
              <ValueOfGoods>'.$musteri["odeme_tutari"].'</ValueOfGoods>
              <ValueOfGoodsCurrency>'.$musteri["odeme_para_birimi"].'</ValueOfGoodsCurrency>
              <ValueOfGoodsPaymentType>'.$musteri["mal_odeme_tipi"].'</ValueOfGoodsPaymentType>
              <ThirdPartyAccountNumber></ThirdPartyAccountNumber>
              <ThirdPartyExpenseCode></ThirdPartyExpenseCode>
            </ShipmentInfo>
            <ReturnLabelLink>true</ReturnLabelLink>
            <ReturnLabelImage>true</ReturnLabelImage>
          </CreateShipment_Type2>
        </soap:Body>
      </soap:Envelope>';
      
      $gonderi_cevabi = $this->getir( $xml );
      echo $gonderi_cevabi;
      $gonderi_donen["ShipmentNo"] = $this->xml_filtrele( $gonderi_cevabi  , '<ShipmentNo>(.*?)</ShipmentNo>'  );
      $gonderi_donen["LinkForLabelPrinting"] = $this->xml_filtrele( $gonderi_cevabi  , '<LinkForLabelPrinting>(.*?)</LinkForLabelPrinting>'  );
      $gonderi_donen["BarkodArrayPng"] = $this->xml_filtrele( $gonderi_cevabi  , '<BarkodArrayPng><string>(.*?)</string></BarkodArrayPng>'  );
      return $gonderi_donen;
    }
 }





/* GÖNDERİ OLUŞTUR - BAŞLANGIÇ */


$ups = new Ups;

$musteri = array(
  "ad_soyad"=>"Mehmet TOKMAK",
  "adres"=>"Ergazi Mahallesi XXX Bulvarı No:1",
  "sehir_kodu"=>6,    //city code
  "bolge_kodu"=>1528, //area code
  "hizmet_turu"=>3,   // 1) Express 09:00   |   3) STANDART           |   4) Express 10:30   |   5) Express 12:00    |     6) Express Saver
  "odeme_tipi"=>2,    // 1) Alıcı Ödemeli   |   2) Gönderen Ödemeli   |   4) Üçüncü Şahıs Ödemeli
  "paket_turu"=>"K",  // D) Mektup          |   K) Paket
  "paket_adedi"=>1,
  "odeme_tutari"=>88,             //TAHSIL EDILEN MAL BEDELI, INT OLARAK
  "odeme_para_birimi"=>"TL",      //PARA BIRIMI (TL, EUR, USD OLABILIR) 
  "mal_odeme_tipi"=>1             //ODEME SEKLI (1= NAKIT, 2= CEK, 3= KREDI KARTI TEK CEKIM)
);

$gonderi_cevabi = $ups->gonderi_olustur( $musteri );

/* GÖNDERİ OLUŞTUR - BİTİŞ */


echo "<pre>";
print_r( $gonderi_cevabi );
echo "</pre>";

echo '<img width="380px" height="540px" src="data:image/jpeg;base64, '.$gonderi_cevabi["BarkodArrayPng"].'">';


echo '<br><a href="'.$gonderi_cevabi["LinkForLabelPrinting"].'">Yazdır</a>';





?>
