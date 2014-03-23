<?php


/**
 * class Cfe_Sms
 *
 * This is the entry point class to the SMS Api in the clib.
 *
 */

/* @todo move all the SMS lib into the Cfe folder once they respect the Zend convention */
include_once $_ENV['LEGACY_LIB_INCLUDE_PATH']. "/clib/sms/config.php";
include_once $_ENV['LEGACY_LIB_INCLUDE_PATH']. "/clib/sms/SMSserverConnexion.php";
include_once $_ENV['LEGACY_LIB_INCLUDE_PATH']. "/clib/sms/temporaryCode.class.php";
include_once $_ENV['LEGACY_LIB_INCLUDE_PATH']. "/clib/sms/productReference.class.php";
include_once $_ENV['LEGACY_LIB_INCLUDE_PATH']. "/clib/sms/smsWappushSender.class.php";
include_once $_ENV['LEGACY_LIB_INCLUDE_PATH']. "/clib/sms/smsMailpushSender.class.php";
include_once $_ENV['LEGACY_LIB_INCLUDE_PATH']. "/clib/sms/smsMtSender.class.php";
include_once $_ENV['LEGACY_LIB_INCLUDE_PATH']. "/clib/sms/mmsSender.class.php";
include_once $_ENV['LEGACY_LIB_INCLUDE_PATH']. "/clib/sms/burningCode.class.php";

/* @todo use a common library Cfe_Shorturl in /srv/www/lib/clean */
include_once $_ENV['LEGACY_LIB_INCLUDE_PATH']. "/clib/shorturl.php";

class Cfe_Sms {
    /**
     * get a command code (aka Burning code or Code brulable) for a given Id product.
     *
     * @deprecated use now getToken function - 24 oct. 08
     *
     * @param $productId [MANDATORY] int Catalogue Id Product encapsulated in the burning code.
     * @param $referer string Referer from where is used the burning code.
     * @param $canal int Way the burning code is used and most important is paid (SMS, WEB, etc...)
     * @param $fnHachage string function used to get the usefull referer information from the referer.
     * @param $source string source from where the burning code has been generated.
     *
     * @return int command code.
     */
    static public function getCommandCode($productId, $referer = null, $canal = null, $fnHachage = null, $source = null) {
        $tmpCodeObj = new clibTemporaryCode();
        return $tmpCodeObj->getCommandCode($productId, $referer, $canal, $source, $fnHachage);
    }

    /**
     * Confirm a command code to indicate the way of payment (only use after the getCommandCode)
     *
     * @deprecated use now confirmCommand function (after getToken)- 24 oct. 08
     * @param $commandId [MANDATORY] int Code burned code provided by the getCommandCode.
     * @param $price int Price of the transaction.
     * @param $canal string Canal of payment.
     *
     * @return bool true if the confirmation happens.
     */
    static public function confirmCommandCodePayment($commandId, $price = null, $canal = null) {
        $tmpCodeObj = new clibTemporaryCode();
        return $tmpCodeObj->confirmCommandCodePayment($commandId, $price, $canal);
    }
    /**
     * Get back the product Id provided to the command code (only use after the getCommandCode)
     *
     * @deprecated use now getData function (after getToken)- 24 oct. 08
     * @param $commandId [MANDATORY] int Code burned code provided by the getCommandCode.
     * @param $canal string Canal of payment.
     *
     * @return int product Id.
     */
    static public function getProductIdByCommandCode($commandId, $canal = null) {
        $tmpCodeObj = new clibTemporaryCode();
        return $tmpCodeObj->getProductId($commandId, $canal);
    }
    /**
     * Return the generated code list (only use after the getCommandCodes).
     *
     * @deprecated use now getTokenList function (after getToken)- 24 oct. 08
     * @param $dateSelection string Date the command Code has been generated.
     * @param $source string source of the command code.
     */
    static public function gettemporarycodelist($dateSelection = '', $source = null) {
        $sms = new clibTemporaryCode();
        try {
            return $sms->getCommandCodeList($dateSelection, $source);
        } catch (Exception $e) {
            return 'NOK';
        }
    }

    /**
     * Check if command Code is available (only use after the getCommandCode).
     *
     * @deprecated use now isTokenAvailable function (after getToken)- 24 oct. 08
     * @param $commandId [MANDATORY] int Code burned code provided by the getCommandCode.
     *
     * @return bool true if the command code is available.
     */
    static public function isCommandeCodeAvailable($commandId) {
        $tmpCodeObj = new clibTemporaryCode();
        return $tmpCodeObj->isCommandeCodeAvailable($commandId);
    }

    /**
     * Get the product associated to a SMS Code.
     *
     * @param $reference [MANDATORY] int SMS code.
     * @param $listId int restriction to a list Id.
     *
     * @return array product Ids associated to the SMS Code.
     */
    static public function searchProductsByReference($reference, $listId = false) {
        $tmpCodeObj = new clibproductReference();
        return $tmpCodeObj->searchProductsByReference($reference, $listId);
    }

    /**
     * Get SMS codes associated to a product Id.
     *
     * @param $productId [MANDATORY] int product Id.
     * @param $listId int restriction to a list Id.
     *
     * @return array SMS codes associated to the product Id.
     */
    static public function searchReferencesByProduct($productId, $listId = false) {
        $tmpCodeObj = new clibproductReference();
        return $tmpCodeObj->searchReferencesByProduct($productId, $listId);
    }

    /**
     * Send a wappush to an user msisdn.
     *
     * @param $msisdn [MANDATORY] string User MSISDN
     * @param $deliveryAdress [MANDATORY] string CMS page from where the content can be downloaded.
     * @param $partyId int Party Id used to download the product.
     * @param $title string Title of the wappush.
     * @param $delay int Delay of wappush sending in minutes.
     *
     * @return string OK if the sending is OK, NOK otherwise.
     */
    static public function sendWappush($msisdn, $deliveryAdress, $partyId = '', $title = '', $delay = 0) {

        $objShortUrlService = ShortURL :: create('default', $deliveryAdress, 7 * 24 * 3600, false, "", "", "", 1, 15);

        if (is_object($objShortUrlService)) {
            $deliveryAdress = $objShortUrlService->getShortUrl();
        }

        $sender = new smsWappushSender($msisdn, $deliveryAdress, $title, $delay);
        if ($partyId != '') {
            $sender->addPublisher($partyId);
        }
        try {
            return trim($sender->send());
        } catch (Exception $e) {
            return 'NOK';
        }
    }

    /**
     * Send a SMS to an user msisdn.
     *
     * @param $msisdn [MANDATORY] string User MSISDN
     * @param $mt [MANDATORY] string Text of the SMS.
     * @param $partyId int Party Id used to download the product.
     * @param $delay int Delay of wappush sending in minutes.
     * @param $soa int SOA Originated of the message.
     * @param $bulk bool indicate if MT is sent in bulk mode
     * @param $keywordId int Add keyword Id
     * @param $options array optionnal parameters
     *
     * @return string OK if the sending is OK, NOK otherwise.
     */
    static public function sendMT($msisdn, $mt, $partyId = '', $delay = '', $soa = '', $bulk = true, $options = null) {

        $sender = new smsMtSender($msisdn, $mt, $delay);
        if ($partyId != '') {
            $sender->addPublisher($partyId);
        }

        if ($soa != '') {
            $sender->addSoa($soa);
        }

        if ($bulk == false) {
            $sender->setDelayedMode();
        }

        if (is_array($options)) {
            /**
             *get KeywordId
             */
            if (isset ($options['keywordId'])) {
                $sender->addKeyworId($options['keywordId']);
            }
            if (isset ($options['debug'])) {
                if ($options['debug'] === true) {
                    $sender->debugModeOn();
                }
            }
        }

        try {
            return trim($sender->send());
        } catch (Exception $e) {
            return 'NOK';
        }

    }

    /**
     * Send a MMS to an user msisdn.
     *
     * @param $msisdn [MANDATORY] string User MSISDN
     * @param $urlToBrowse [MANDATORY] string Url to browse to have the MMS.
     * @param $partyId int Party Id used to download the product.
     * @param $delay int Delay of wappush sending in minutes.
     * @param $soa int SOA Originated of the message.
     * @param $title string Add a title to the MMS
     * @param $keywordId int Add a keyword Id to the MMS.
     *
     * @return string OK if the sending is OK, NOK otherwise.
     */
    static public function sendMMS($msisdn, $urlToBrowse, $partyId = '', $delay = '', $soa = '', $title = '', $keywordId = '') {

        $sender = new mmsSender($msisdn, $urlToBrowse, $delay, $title);
        if ($partyId != '') {
            $sender->addPublisher($partyId);
        }

        if ($soa != '') {
            $sender->addSoa($soa);
        }

        if ($keywordId != '') {
            $sender->addKeyworId($keywordId);
        }

        try {
            return trim($sender->send());
        } catch (Exception $e) {
            return 'NOK';
        }
    }

    /**
     * Send a mailpush to an user msisdn.
     *
     * @param $msisdn [MANDATORY] string User MSISDN
     * @param $deliveryAdress [MANDATORY] string CMS page from where the content can be downloaded.
     * @param $partyId int Party Id used to download the product.
     * @param $title string Title of the wappush.
     * @param $delay int Delay of mailpush sending in minutes.
     *
     * @return string OK if the sending is OK, NOK otherwise.
     */
    static public function sendMailPush($msisdn, $deliveryAdress, $partyId = '', $title = '', $delay = '') {
        $sender = new smsMailpushSender($msisdn, $deliveryAdress, $title);
        if ($partyId != '') {
            $sender->addPublisher($partyId);
        }
        try {
            return trim($sender->send());
        } catch (Exception $e) {
            return 'NOK';
        }
    }

    /**
     * Get a burning code token to encapsulated any type of data.
     *
     * @param $data [MANDATORY] mixed Any type of datas that can burned into the token.
     * @param $referer [MANDATORY] string referer from where the temporaray token is command.
     * @param $canal string payment canal
     * @param $source string source of generation
     * @param $duration int duration after wich the burning code is deleted.
     * @param $extras array extra informations set in the token.
     * @param $length int length of the burning code (5 by default)
     * @param $isNumeric bool if set to true the burning code is only numeric (false by default)
     *
     * @return string burning code token
     */
    static public function getToken($data, $referer = null, $canal = null, $source = null, $duration = null, $extras = null, $length = null, $isNumeric = null) {
        $tmpCodeObj = new clibBurningCode();
        return $tmpCodeObj->getToken($data, $referer, $canal, $source, $duration, $extras, $length, $isNumeric);
    }

    /**
     * Get the datas encapsulated in the burning code.
     *
     * @param $tmpCode [MANDATORY] string burning code generated by getToken.
     * @param $source [MANDATORY] string source of the generation.
     * @param $destroy bool flag to indicate if the burning code is destroyed once the getData is made.
     *
     * @return mixed datas set into the burning code.
     */
    static public function getData($tmpCode, $source = null, $destroy = null) {
        $tmpCodeObj = new clibBurningCode();
        return $tmpCodeObj->getData($tmpCode, $source, $destroy);
    }
    /**
     * Return the generated code list for a given date and source.
     *
     * @param $source string source of the codes.
     * @param $dateSelection string where the codes are generated.
     * @param $canal restriction string on the payment canal.
     *
     * @return array List of generated code list.
     */
    static public function getTokenList($source = null, $dateSelection = '', $canal = null) {
        $tmpCodeObj = new BurningCode($source);
        return $tmpCodeObj->getTokenList($source, $dateSelection);
    }

    /**
     * extract cooksess from a burning code
     *
     * @param $tmpCode [MANDATORY] string burning code generated by getToken.
     * @param $source string source of the codes.
     *
     * @return string Session cookie set in the burning code.
     */
    static public function getCookSess($tmpCode, $source = null) {
        $tmpCodeObj = new clibBurningCode();
        return $tmpCodeObj->getCookSess($tmpCode, $source);
    }

    /**
     * extract referere from a burning code
     *
     * @param $tmpCode [MANDATORY] string burning code generated by getToken.
     * @param $source string source of the codes.
     *
     * @return string referer set in the burning code.
     */
    static public function getReferer($tmpCode, $source = null) {
        $tmpCodeObj = new clibBurningCode();
        return $tmpCodeObj->getReferer($tmpCode, $source);
    }

    /**
     * Confirm a command code to indicate the way of payment (only use after the getCommandCode)
     *
     * @param $token [MANDATORY] string burning code generated by getToken.
     * @param $source [MANDATORY] string source of the codes.
     * @param $price int Price of the transaction.
     * @param $canal string Canal of payment.
     *
     * @return bool true if the confirmation happens.
     */
    static public function confirmCommand($token, $source, $price = null, $canal = null) {
        $tmpCodeObj = new clibBurningCode();
        return $tmpCodeObj->confirmCommand($token, $source, $price, $canal);
    }

    /**
     * Check if burning Code token is available.
     *
     * @param $token [MANDATORY] int command Code burned code provided by the getToken.
     * @param $source [MANDATORY] string source of the codes.
     *
     * @return bool true if the command code is available.
     */
    static function isTokenAvailable($token, $source) {
        $tmpCodeObj = new clibBurningCode();
        return $tmpCodeObj->isTokenAvailable($token, $source);
    }
}