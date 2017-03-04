<?php

class N11
{

    private $appKey = '';
    private $appSecret = '';

    protected $urlList = [
        'category' => 'https://api.n11.com/ws/CategoryService.wsdl',
        'city' => 'https://api.n11.com/ws/CityService.wsdl',
        'product' => 'https://api.n11.com/ws/ProductService.wsdl',
        'shipment' => 'https://api.n11.com/ws/ShipmentCompanyService.wsdl',
    ];


    /**
     * N11 constructor.
     * @param string $appKey
     * @param string $appSecret
     */
    public function __construct($appKey = null, $appSecret = null)
    {
        $this->appKey = $appKey;
        $this->appSecret = $appSecret;
    }


    private function authXMLcreator()
    {
        return '<auth>
                  <appKey>' . $this->appKey . '</appKey>
            <appSecret>' . $this->appSecret . '</appSecret>
         </auth>';
    }


    function curlPoster($url, $xml_post_string)
    {
        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "Content-length: " . strlen($xml_post_string),
        );


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);
        return $response;

    }

    public function getTopLevelCategories()
    {

        $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sch="http://www.n11.com/ws/schemas">
   <soapenv:Header/>
   <soapenv:Body>
      <sch:GetTopLevelCategoriesRequest>
      ' . $this->authXMLcreator() . '
               </sch:GetTopLevelCategoriesRequest>
   </soapenv:Body>
</soapenv:Envelope>';   // data from the form, e.g. some ID number

        $response = $this->curlPoster($this->urlList['category'], $xml_post_string);

        return $response;
    }

    /*
     * kategori özellikleri
     * */
    public function getCategoryAttributes($categoryId)
    {
        $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sch="http://www.n11.com/ws/schemas">
   <soapenv:Header/>
   <soapenv:Body>
      <sch:GetCategoryAttributesRequest>
         <categoryId>' . $categoryId . '</categoryId>
        ' . $this->authXMLcreator() . '
      </sch:GetCategoryAttributesRequest>
   </soapenv:Body>
</soapenv:Envelope>';
        return $this->curlPoster($this->urlList['category'], $xml_post_string);
    }

    /*
     * kategori üst kategorileri
     * */
    public function getParentCategory($categoryId)
    {
        $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sch="http://www.n11.com/ws/schemas">
   <soapenv:Header/>
   <soapenv:Body>
      <sch:GetParentCategoryRequest>
         <categoryId>' . $categoryId . '</categoryId>
          ' . $this->authXMLcreator() . '
      </sch:GetParentCategoryRequest>
   </soapenv:Body>
</soapenv:Envelope>';

        return $this->curlPoster($this->urlList['category'], $xml_post_string);

    }

    /*
     * kategori alt kategorileri
     * */
    public function getSubCategory($categoryId)
    {
        $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sch="http://www.n11.com/ws/schemas">
<soapenv:Header/>
   <soapenv:Body>
      <sch:GetSubCategoriesRequest>
         <categoryId>' . $categoryId . '</categoryId>
         ' . $this->authXMLcreator() . '
      </sch:GetSubCategoriesRequest>
</soapenv:Body>
</soapenv:Envelope>';
        return $this->curlPoster($this->urlList['category'], $xml_post_string);
    }


    public function getCities()
    {
        $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sch="http://www.n11.com/ws/schemas">
   <soapenv:Header/>
   <soapenv:Body>
      <sch:GetCitiesRequest/>
   </soapenv:Body>
</soapenv:Envelope>';
        return $this->curlPoster($this->urlList['city'], $xml_post_string);
    }

    /*
     * şehir detay
     * */
    public function getCityDetail($cityCode)
    {
        $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sch="http://www.n11.com/ws/schemas">
   <soapenv:Header/>
   <soapenv:Body>
      <sch:GetCityRequest>
         <cityCode>' . $cityCode . '</cityCode>
      </sch:GetCityRequest>
   </soapenv:Body>
</soapenv:Envelope>';
        return $this->curlPoster($this->urlList['city'], $xml_post_string);
    }

    /*
     * Şehir ilçeleri
     * */
    public function getCityDistricts($cityCode)
    {
        $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sch="http://www.n11.com/ws/schemas">
   <soapenv:Header/>
   <soapenv:Body>
      <sch:GetDistrictRequest>
         <cityCode>' . $cityCode . '</cityCode>
      </sch:GetDistrictRequest>
   </soapenv:Body>
</soapenv:Envelope>';
        return $this->curlPoster($this->urlList['city'], $xml_post_string);
    }


    /*
     * -----------------PRODUCTS-------------------
     * */
    public function getProducts($pagination = 1, $pageSize = 20)
    {
        /*
 *  Mağazaya ait tüm ürünleri listeler (Get Product List)
 * currentPage= bulunduğu sayfanın pagination değeri
 * pageSize = her sayfada gösterilecek maksimum ürün adeti
 * */
        $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sch="http://www.n11.com/ws/schemas">
   <soapenv:Header/>
   <soapenv:Body>
      <sch:GetProductListRequest>
       ' . $this->authXMLcreator() . '   
         <pagingData>
            <currentPage>' . $pagination . '</currentPage>  
            <pageSize>' . $pageSize . '</pageSize>
         </pagingData>
      </sch:GetProductListRequest>
   </soapenv:Body>
</soapenv:Envelope>';

        return $this->curlPoster($this->urlList['product'], $xml_post_string);


    }


    public function getProductById($productId)
    {
        $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sch="http://www.n11.com/ws/schemas">
   <soapenv:Header/>
   <soapenv:Body>
      <sch:GetProductByProductIdRequest>
         ' . $this->authXMLcreator() . '
         <productId>' . $productId . '</productId>
      </sch:GetProductByProductIdRequest>
   </soapenv:Body>
</soapenv:Envelope>';
        return $this->curlPoster($this->urlList['product'], $xml_post_string);

    }


    public function getProductByCode()
    {
        $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sch="http://www.n11.com/ws/schemas">
   <soapenv:Header/>
   <soapenv:Body>
      <sch:GetProductBySellerCodeRequest>
       ' . $this->authXMLcreator() . '
         <sellerCode>IlrCIzhPnm</sellerCode>
      </sch:GetProductBySellerCodeRequest>
   </soapenv:Body>
</soapenv:Envelope>';
        return $this->curlPoster($this->urlList['product'], $xml_post_string);
    }


    public function productSave($productXML)
    {

        /*  $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sch="http://www.n11.com/ws/schemas">
     <soapenv:Header/>
     <soapenv:Body>
        <sch:SaveProductRequest>
         '.$this->authXMLcreator().'

           <product>
              <productSellerCode>az32897591</productSellerCode>
                  <title>Deneme urunudur satin almayiniz.</title>
              <subtitle>Api test urunudur.</subtitle>
              <description>Deneme urunumuz</description>
              <attributes>

                 <attribute>
                    <name>Marka</name>
                    <value>HP</value>
                 </attribute>
                  <attribute>
                    <name>Ekran Boyutu</name>
                    <value>15.6\'\' İnç</value>
                 </attribute>
                    <attribute>
                    <name>Harddisk Kapasitesi</name>
                    <value>1 Tb</value>
                 </attribute>

                 <attribute>
                    <name>İşletim Sistemi</name>
                    <value>Free Dos</value>
                 </attribute>
                 <attribute>
                    <name>Notebook İşlemci Tipi</name>
                    <value>Intel Core İ5</value>
                 </attribute>

              </attributes>
              <category>
                 <id>1000271</id>
              </category>
              <price>0.99</price>
              <currencyType>TL</currencyType>
              <images>
                 <!--1 or more repetitions:-->
                 <image>
                    <url>http://www.magazapark.com/images/urunler/619_hp-n9t15ea-1458_2.jpg</url>
                    <order>1</order>
                 </image>
              </images>
              <saleStartDate></saleStartDate>
              <saleEndDate></saleEndDate>
              <productionDate></productionDate>
              <expirationDate></expirationDate>
              <productCondition>1</productCondition>
              <preparingDay>3</preparingDay>
              <discount>10</discount>
              <shipmentTemplate>Elektronik</shipmentTemplate>
              <stockItems>
                 <!--1 or more repetitions:-->
                 <stockItem>
                    <quantity>3</quantity>
                    <sellerStockCode>453222</sellerStockCode>
                    <attributes>
                       <!--1 or more repetitions:-->
                       <attribute>
                       <name>Marka</name>
                          <value>HP</value>
                       </attribute>
                    </attributes>
                    <optionPrice>0.99</optionPrice>
                 </stockItem>
              </stockItems>

           </product>
        </sch:SaveProductRequest>
     </soapenv:Body>
  </soapenv:Envelope>';
        */
        $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sch="http://www.n11.com/ws/schemas">
   <soapenv:Header/>
   <soapenv:Body>
      <sch:SaveProductRequest>
       ' . $this->authXMLcreator() . '  
         <product>
            <productSellerCode>1629</productSellerCode>
                <title>Deneme urunudur satin almayiniz.</title>
            <subtitle>Api test urunudur.</subtitle>
            <description>Deneme urunumuz</description>
            <attributes>         
           
               <attribute>
                  <name>Marka</name>
                  <value>Msi</value>
               </attribute>
                <attribute>
                  <name>Ekran Boyutu</name>
                  <value>18.4\'\' İnç</value>
               </attribute>
                  <attribute>
                  <name>Harddisk Kapasitesi</name>
                  <value>1 Tb</value>
               </attribute>    
                         
               <attribute>
                  <name>İşletim Sistemi</name>
                  <value>Windows 8</value>
               </attribute>      
               <attribute>
                  <name>Notebook İşlemci Tipi</name>
                  <value>Intel Core İ7</value>
               </attribute>
               
            </attributes>            
            <category>
               <id>1000271</id>
            </category>
            <price>4322.32</price>
            <currencyType>TL</currencyType>
            <images>
               <!--1 or more repetitions:-->
               <image>
                  <url>http://www.magazapark.com/images/urunler/619_msi-gt80-2-1629_2.jpg</url>
                  <order>1</order>
               </image>
                  <image>
                  <url>http://www.magazapark.com/images/urunler/619_msi-gt80-2-1629_2.jpg</url>
                  <order>2</order>
               </image>
                  <image>
                  <url>http://www.magazapark.com/images/urunler/619_msi-gt80-2-1629_3.jpg</url>
                  <order>3</order>
               </image>
            </images>
            <saleStartDate></saleStartDate>
            <saleEndDate></saleEndDate>
            <productionDate></productionDate>
            <expirationDate></expirationDate>
            <productCondition>1</productCondition>
            <preparingDay>3</preparingDay>
            <discount></discount>
            <shipmentTemplate>Elektronik</shipmentTemplate>
            <stockItems>
               <!--1 or more repetitions:-->
               <stockItem>
                  <quantity>12</quantity>
                  <sellerStockCode>1629</sellerStockCode>
                  <attributes>
                     <!--1 or more repetitions:-->
                     <attribute>
                     <name>Marka</name>
                        <value>Msi</value>
                     </attribute>
                  </attributes>
                  <optionPrice>4322.32</optionPrice>
               </stockItem>              
            </stockItems>          
                
         </product>
      </sch:SaveProductRequest>
   </soapenv:Body>
</soapenv:Envelope>';


        $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sch="http://www.n11.com/ws/schemas">
   <soapenv:Header/>
   <soapenv:Body>
      <sch:SaveProductRequest>
       ' . $this->authXMLcreator() . $productXML . '  
        
      </sch:SaveProductRequest>
   </soapenv:Body>
</soapenv:Envelope>';
        return $this->curlPoster($this->urlList['product'], $xml_post_string);


        return $this->curlPoster($this->urlList['product'], $xml_post_string);

    }

    /*
     * n11 id sine göre ürünü siler
     * */
    public function productDeleteByN11Id($productId)
    {
        $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sch="http://www.n11.com/ws/schemas">
   <soapenv:Header/>
   <soapenv:Body>
      <sch:DeleteProductByIdRequest>
       ' . $this->authXMLcreator() . '
         <productId>' . $productId . '</productId>
      </sch:DeleteProductByIdRequest>
   </soapenv:Body>
</soapenv:Envelope>';
        return $this->curlPoster($this->urlList['product'], $xml_post_string);
    }


    /*
 * mağaza  ürün koduna göre ürün siler
 * */
    public function productDeleteBySellerCode($productCode)
    {
        $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sch="http://www.n11.com/ws/schemas">
   <soapenv:Header/>
   <soapenv:Body>
      <sch:DeleteProductBySellerCodeRequest>
       ' . $this->authXMLcreator() . '
         <productSellerCode>' . $productCode . '</productSellerCode>
      </sch:DeleteProductBySellerCodeRequest>
   </soapenv:Body>
</soapenv:Envelope>';
        return $this->curlPoster($this->urlList['product'], $xml_post_string);
    }


    /*
* Bir ürünün N11 ürün ID sini kullanarak indirim bilgilerinin güncellenmesi için kullanılır.
* */

    public function UpdateDiscountValueByN11ProductId($productId, $type, $value, $startDate, $endDate)
    {
        $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sch="http://www.n11.com/ws/schemas">
   <soapenv:Header/>
   <soapenv:Body>
      <sch:UpdateDiscountValueByProductIdRequest>
       ' . $this->authXMLcreator() . '
         <productId>' . $productId . '</productId>
          <productDiscount>
            <discountType>2</discountType>
            <discountValue>15</discountValue>
            <discountStartDate>?</discountStartDate>
            <discountEndDate>?</discountEndDate>
         </productDiscount>
      </sch:UpdateDiscountValueByProductIdRequest>
   </soapenv:Body>
</soapenv:Envelope>';
        return $this->curlPoster($this->urlList['product'], $xml_post_string);
    }


    /*
     * Bir ürünün mağaza ürün kodunu kullanarak indirim bilgilerinin güncellenmesi için kullanılır.
     */
    public function UpdateDiscountValueBySellerCode($sellerCode, $type, $value, $startDate, $endDate)
    {
        $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sch="http://www.n11.com/ws/schemas">
   <soapenv:Header/>
   <soapenv:Body>
      <sch:UpdateDiscountValueBySellerCodeRequest>
       ' . $this->authXMLcreator() . '
         <productSellerCode>' . $sellerCode . '</productSellerCode>
          <productDiscount>
            <discountType>2</discountType>
            <discountValue>15</discountValue>
            <discountStartDate>?</discountStartDate>
            <discountEndDate>?</discountEndDate>
         </productDiscount>
      </sch:UpdateDiscountValueBySellerCodeRequest>
   </soapenv:Body>
</soapenv:Envelope>';
        return $this->curlPoster($this->urlList['product'], $xml_post_string);
    }

    /*
     * ir ürünün N11 ürün ID si kullanılarak ürünün sadece baz fiyat bilgilerini,
     *  ürün stok birimi fiyat bilgilerini veya her ikisinin güncellenmesi için kullanılır.
     * */
    public function UpdateProductPriceById($productId, $price, $stockCode, $priceStock)
    {
        $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sch="http://www.n11.com/ws/schemas">
   <soapenv:Header/>
   <soapenv:Body>
      <sch:UpdateProductPriceByIdRequest>
       ' . $this->authXMLcreator() . '
         <productId>' . $productId . '</productId>
         <price>' . $price . '</price>
         <currencyType>1</currencyType>
         <stockItems>
            <!--1 or more repetitions:-->
            <stockItem>
               <sellerStockCode>' . $stockCode . '</sellerStockCode>
               <optionPrice>' . $priceStock . '</optionPrice>
            </stockItem>
         </stockItems>
      </sch:UpdateProductPriceByIdRequest>
   </soapenv:Body>
</soapenv:Envelope>';
        return $this->curlPoster($this->urlList['product'], $xml_post_string);

    }

    /*
     * Bir ürünün mağaza ürün kodu kullanarak fiyat bilgilerinin güncellenmesi için kullanılır.

     * */

    public function UpdateProductPriceBySellerCode($productCode, $price, $stockCode, $priceStock)
    {
        $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sch="http://www.n11.com/ws/schemas">
   <soapenv:Header/>
   <soapenv:Body>
      <sch:UpdateProductPriceBySellerCodeRequest>
       ' . $this->authXMLcreator() . '
         <productSellerCode>' . $productCode . '</productSellerCode>
         <price>' . $price . '</price>
         <currencyType>1</currencyType>
         <stockItems>
            <!--1 or more repetitions:-->
            <stockItem>
               <sellerStockCode>' . $stockCode . '</sellerStockCode>
               <optionPrice>' . $priceStock . '</optionPrice>
            </stockItem>
         </stockItems>
      </sch:UpdateProductPriceBySellerCodeRequest>
   </soapenv:Body>
</soapenv:Envelope>';
        return $this->curlPoster($this->urlList['product'], $xml_post_string);

    }

//TODO PARAMETRELERİ EKLE
    /*
     *Müşterileriniz tarafından mağazanıza sorulan soruları listeler
     * */
    public function GetProductQuestionList($page=0,$size=100,$productId=null)
    {
        $xml_post_string='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sch="http://www.n11.com/ws/schemas">
   <soapenv:Header/>
   <soapenv:Body>
      <sch:GetProductQuestionListRequest>
         '.$this->authXMLcreator().'
         <productQuestionSearch>
            <productId>'.$productId.'</productId>
            <buyerEmail></buyerEmail>
            <subject></subject>
            <status></status>
            <questionDate></questionDate>
         </productQuestionSearch>
         <pagingData>
            <currentPage>'.$page.'</currentPage>
            <pageSize>'.$size.'</pageSize>
         </pagingData>
      </sch:GetProductQuestionListRequest>
   </soapenv:Body>
</soapenv:Envelope>';
    }


    /*
     * ----------- SHIPMENT----------------------
     * */
    public function GetShipmentTemplateList()
    {
        $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sch="http://www.n11.com/ws/schemas">
   <soapenv:Header/>
   <soapenv:Body>
      <sch:GetShipmentTemplateListRequest>
         ' . $this->authXMLcreator() . '
      </sch:GetShipmentTemplateListRequest>
   </soapenv:Body>
</soapenv:Envelope>';
        return $this->curlPoster($this->urlList['product'], $xml_post_string);

    }


    public function xmlParser($data)
    {
        // SimpleXML seems to have problems with the colon ":" in the <xxx:yyy> response tags, so take them out
        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $data);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $responseArray = json_decode($json, true);
        return $responseArray;
    }


}




//1000210:Bilgisayar Kategorisi
//1000271:Bilgisayar->Dizüstü bilgisayar Kategorisi

//$n11 = new N11('APP KEY', 'APP SECRET');

//var_dump($n11->getTopLevelCategories());
//var_dump($n11->getCategoryAttributes(1000271));
//var_dump($n11->getParentCategory(1000271));
//var_dump($n11->getSubCategory(1000271));
//var_dump($n11->getCities());
//var_dump($n11->getCityDetail(34));
//var_dump($n11->getCityDistricts(34));
//var_dump($n11->getProductById(34));
//var_dump($n11->getProductByCode(34));
//var_dump($n11->getProducts());
//var_dump($n11->productSave());


