<?php

/**
 * RoyalMailShipping by StreamMarket
 *
 * @category    StreamMarket
 * @package StreamMarket_RoyalMailShipping
 * @author  Product Development Team <support@StreamMarket.co.uk>
 * @license http://extensions.StreamMarket.co.uk/license
 *
 */

namespace StreamMarket\RoyalMailShipping\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Description of AbstractCarrier
 */
abstract class AbstractCarrier extends AbstractCarrierOnline implements \Magento\Shipping\Model\Carrier\CarrierInterface
{

    /**
     * @var \StreamMarket\RoyalMailShipping\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    private $encryptor;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    private $moduleReader;

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\Carrier\Codes
     */
    private $carrierCodes;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    private $cache;

    const UK_COUNTRY_ID = 'GB';
    const THROTTLING_CACHE_KEY = 'royalmail_throttled';
    const THROTTLING_ERROR_CODE = 'E0010';
    const LABEL_DIR_NAME = 'sm_royalmail';
    const WSDL_FILE = 'ShippingAPI_V2_0_9.wsdl';

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
            \Psr\Log\LoggerInterface $logger,
            \Magento\Framework\Xml\Security $xmlSecurity,
            \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
            \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
            \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
            \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
            \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
            \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
            \Magento\Directory\Model\RegionFactory $regionFactory,
            \Magento\Directory\Model\CountryFactory $countryFactory,
            \Magento\Directory\Model\CurrencyFactory $currencyFactory,
            \Magento\Directory\Helper\Data $directoryData,
            \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
            \Magento\Framework\App\CacheInterface $cache,
            \StreamMarket\RoyalMailShipping\Model\Carrier\Codes $carrierCodes,
            DirectoryList $directoryList,
            \Magento\Framework\Encryption\EncryptorInterface $encryptor,
            \Magento\Framework\Module\Dir\Reader $moduleReader,
            \StreamMarket\RoyalMailShipping\Helper\Data $helper,
            array $data = array())
    {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger,
                $xmlSecurity, $xmlElFactory, $rateFactory, $rateMethodFactory,
                $trackFactory, $trackErrorFactory, $trackStatusFactory,
                $regionFactory, $countryFactory, $currencyFactory,
                $directoryData, $stockRegistry, $data);
        $this->cache = $cache;
        $this->directoryList = $directoryList;
        $this->carrierCodes = $carrierCodes;
        $this->moduleReader = $moduleReader;
        $this->encryptor = $encryptor;
        $this->helper = $helper;
    }

    /**
     * Determine whether zip-code is required
     *
     * @param string|null $countryId
     * @return bool
     */
    public function isZipCodeRequired($countryId = null)
    {
        return true;
    }

    /**
     * Check if carrier has shipping label option available
     *
     * @return boolean
     */
    public function isShippingLabelsAvailable()
    {
        return true;
    }

    /**
     * @param type $tracking
     * @return \Magento\Shipping\Model\Tracking\Result
     */
    public function getTrackingInfo($tracking)
    {
        $result = $this->getTracking($tracking);
        if ($result instanceof \Magento\Shipping\Model\Tracking\Result) {
            if ($trackings = $result->getAllTrackings()) {
                return $trackings[0];
            }
        } elseif (is_string($result) && !empty($result)) {
            return $result;
        }
        return parent::getTrackingInfo($tracking);
    }

    /**
     * Check is post code valid
     *
     * @return boolean
     */
    public function isValidPostcode($postCode = null, $countryId = null)
    {
        if ($countryId == self::UK_COUNTRY_ID && $postCode != null) {
            return $this->validateUKPostcode($postCode);
        }
        return true;
    }

    /**
     * Function to see if a string is a UK postcode or not. The postcode is also
     * formatted so it contains no strings. Full or partial postcodes can be used.
     *
     * @param string $toCheck
     * @return boolean
     */
    protected function validateUKPostcode($toCheck)
    {
        // Permitted letters depend upon their position in the postcode.
        $alpha1 = "[abcdefghijklmnoprstuwyz]";                          // Character 1
        $alpha2 = "[abcdefghklmnopqrstuvwxy]";                          // Character 2
        $alpha3 = "[abcdefghjkstuw]";                                   // Character 3
        $alpha4 = "[abehmnprvwxy]";                                     // Character 4
        $alpha5 = "[abdefghjlnpqrstuwxyz]";                             // Character 5
        // Expression for postcodes: AN NAA, ANN NAA, AAN NAA, and AANN NAA with a space
        // Or AN, ANN, AAN, AANN with no whitespace
        $pcexp[0] = '^(' . $alpha1 . '{1}' . $alpha2 . '{0,1}[0-9]{1,2})([[:space:]]{0,})([0-9]{1}' . $alpha5 . '{2})?$';

        // Expression for postcodes: ANA NAA
        // Or ANA with no whitespace
        $pcexp[1] = '^(' . $alpha1 . '{1}[0-9]{1}' . $alpha3 . '{1})([[:space:]]{0,})([0-9]{1}' . $alpha5 . '{2})?$';

        // Expression for postcodes: AANA NAA
        // Or AANA With no whitespace
        $pcexp[2] = '^(' . $alpha1 . '{1}' . $alpha2 . '[0-9]{1}' . $alpha4 . ')([[:space:]]{0,})([0-9]{1}' . $alpha5 . '{2})?$';

        // Exception for the special postcode GIR 0AA
        // Or just GIR
        $pcexp[3] = '^(gir)([[:space:]]{0,})?(0aa)?$';

        // Standard BFPO numbers
        $pcexp[4] = '^(bfpo)([[:space:]]{0,})([0-9]{1,4})$';

        // c/o BFPO numbers
        $pcexp[5] = '^(bfpo)([[:space:]]{0,})(c\/o([[:space:]]{0,})[0-9]{1,3})$';

        // Overseas Territories
        $pcexp[6] = '^([a-z]{4})([[:space:]]{0,})(1zz)$';

        // Anquilla
        $pcexp[7] = '^(ai\-2640)$';

        // Load up the string to check, converting into lowercase
        $postcode = strtolower($toCheck);

        // Assume we are not going to find a valid postcode
        $valid = false;

        // Check the string against the six types of postcodes
        foreach ($pcexp as $regexp) {
            if (preg_match('/' . $regexp . '/i', $postcode, $matches)) {

                // Load new postcode back into the form element
                $postcode = strtoupper($matches[1]);
                if (isset($matches[3])) {
                    $postcode .= ' ' . strtoupper($matches[3]);
                }

                // Take account of the special BFPO c/o format
                $postcode = preg_replace('/C\/O/', 'c/o ', $postcode);

                // Remember that we have found that the code is valid and break from loop
                $valid = true;
                break;
            }
        }
        // Return with the reformatted valid postcode in uppercase if the postcode was
        // valid
        if ($valid) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Processing additional validation to check if carrier applicable.
     *
     * @param \Magento\Framework\DataObject $request
     * @return $this|bool|\Magento\Framework\DataObject
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function proccessAdditionalValidation(\Magento\Framework\DataObject $request)
    {
        //Skip by item validation if there is no items in request
        if (!count($this->getAllItems($request))) {
            return $this;
        }

        $maxAllowedWeight = (double) $this->getConfigData('max_package_weight');
        $errorMsg = '';
        $configErrorMsg = $this->getConfigData('specificerrmsg');
        $defaultErrorMsg = __('The shipping module is not available.');
        $showMethod = $this->getConfigData('showmethod');

        /** @var $item \Magento\Quote\Model\Quote\Item */
        foreach ($this->getAllItems($request) as $item) {
            $product = $item->getProduct();
            if ($product && $product->getId()) {
                $weight = $product->getWeight();
                $stockItemData = $this->stockRegistry->getStockItem(
                        $product->getId(), $item->getStore()->getWebsiteId()
                );
                $doValidation = true;

                if ($stockItemData->getIsQtyDecimal() && $stockItemData->getIsDecimalDivided()) {
                    if ($stockItemData->getEnableQtyIncrements() && $stockItemData->getQtyIncrements()
                    ) {
                        $weight = $weight * $stockItemData->getQtyIncrements();
                    } else {
                        $doValidation = false;
                    }
                } elseif ($stockItemData->getIsQtyDecimal() && !$stockItemData->getIsDecimalDivided()) {
                    $weight = $weight * $item->getQty();
                }

                if ($doValidation && $weight > $maxAllowedWeight) {
                    $errorMsg = $configErrorMsg ? $configErrorMsg : $defaultErrorMsg;
                    break;
                }
            }
        }

        if (!$errorMsg && !$request->getDestPostcode() && $this->isZipCodeRequired($request->getDestCountryId())) {
            $errorMsg = __('This shipping method is not available. Please specify the zip code.');
        }
        if (!$errorMsg && !$this->isValidPostcode($request->getDestPostcode(),
                        $request->getDestCountryId())) {
            $errorMsg = __('This shipping method is not available, please specify valid ZIP-code.');
        }
        if ($errorMsg && $showMethod) {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($errorMsg);

            return $error;
        } elseif ($errorMsg) {
            return false;
        }

        return $this;
    }

    /**
     * Returns royalmail SOAP API gateway url
     * @return string
     */
    public function getGatewayUrl()
    {
        $url = $this->getConfigData('gateway_url');
        if (!$url) {
            throw new LocalizedException(__('Please specify gateway URL.'));
        }
        return $url;
    }

    /**
     * Shipping API WSDL URL
     * @return string
     */
    protected function _getShippingAPIWSDL()
    {
        $etcDir = $this->moduleReader->getModuleDir(
                \Magento\Framework\Module\Dir::MODULE_ETC_DIR,
                'StreamMarket_RoyalMailShipping'
        );
        return $etcDir . DIRECTORY_SEPARATOR . 'soap' . DIRECTORY_SEPARATOR . self::WSDL_FILE;
    }

    /**
     * Royal Mail SoapClient Object
     * @return SoapClient
     */
    protected function _getClient()
    {
        if (!$this->getConfigData('active')) {
            throw new LocalizedException(__('RoyalMail shipping is inactive.'));
        }
        $wsdlURL = $this->_getShippingAPIWSDL();
        $apiUsername = $this->getConfigData('api_user');
        $clientID = $this->getConfigData('client_id');
        $clientSecret = $this->encryptor->decrypt($this->getConfigData('client_secret'));

        $apiPassword = $this->encryptor->decrypt($this->getConfigData('api_password'));
        if (!$apiUsername || !$clientID || !$clientSecret || !$apiPassword) {
            throw new LocalizedException(__('Please specify RoyalMail Shipping API access credentials and try again.'));
        }
        $created = gmdate('Y-m-d\TH:i:s\Z');
        $nonce = mt_rand();
        $nonce_date_pwd = $nonce . $created . base64_encode(sha1($apiPassword,
                                TRUE));
        $passwordDigest = base64_encode(sha1($nonce_date_pwd, TRUE));
        $ENCODEDNONCE = base64_encode($nonce);
        //ini_set("soap.wsdl_cache_enabled", 1);
        ini_set('default_socket_timeout', 180);
        $soapclient_options = array();
        $soapclient_options['uri'] = 'http://schemas.xmlsoap.org/soap/envelope/';
        $soapclient_options['cache_wsdl'] = WSDL_CACHE_MEMORY;
        $soapclient_options['style'] = SOAP_RPC;
        $soapclient_options['use'] = SOAP_ENCODED;
        $soapclient_options['trace'] = true;
        $soapclient_options['soap_version'] = SOAP_1_1;
        $soapclient_options['encoding'] = 'UTF-8';
        $soapclient_options['connection_timeout'] = 180;
        $soapclient_options['ssl_method'] = 'SOAP_SSL_METHOD_SSLv3';
        $soapclient_options['stream_context'] = stream_context_create(
                [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ],
                    'http' =>
                    [
                        'header' => implode("\r\n",
                                [
                            'Accept: application/soap+xml',
                            'X-IBM-Client-Id: ' . $clientID,
                            'X-IBM-Client-Secret: ' . $clientSecret,
                                ]
                        ),
                    ],
                ]
        );
        $soapclient_options['location'] = $this->getConfigData('gateway_url');
        /**
         *  launch soap client
         */
        $soapClent = new \SoapClient($wsdlURL, $soapclient_options);
        $soapClent->__setLocation($soapclient_options['location']);
        /**
         *  headers needed for royal mail
         */
        $HeaderObjectXML = '<wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
               <wsse:UsernameToken wsu:Id="UsernameToken-000">
                  <wsse:Username>' . $apiUsername . '</wsse:Username>
                  <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordDigest">' . $passwordDigest . '</wsse:Password>
                  <wsse:Nonce EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">' . $ENCODEDNONCE . '</wsse:Nonce>
                  <wsu:Created>' . $created . '</wsu:Created>
               </wsse:UsernameToken>
           </wsse:Security>';
        /**
         *  push the header into soap
         */
        $HeaderObject = new \SoapVar($HeaderObjectXML, XSD_ANYXML);
        /**
         *  push soap header
         */
        $soapHeader = new \SoapHeader('http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd',
                'Security', $HeaderObject);
        $soapClent->__setSoapHeaders($soapHeader);
        return $soapClent;
    }

    /**
     * Returns wait time for throttling
     * @return int
     */
    protected function _getThrottlingWaitTime()
    {
        return $this->getConfigData('throttling_wait_time') > 0 ? $this->getConfigData('throttling_wait_time') : 60;
    }

    /**
     * Set throttling wait flat to cache
     */
    protected function setThrotledRequest()
    {
        $this->cache->save('1', self::THROTTLING_CACHE_KEY, [],
                $this->_getThrottlingWaitTime());
    }

    /**
     * Is request throttled
     * @return boolean
     */
    protected function isThrotledRequest()
    {
        if (false !== $this->cache->load(self::THROTTLING_CACHE_KEY)) {
            return true;
        }
        return false;
    }

    /**
     * Process request transaction and handle errors
     * @param \Magento\Framework\DataObject $transaction
     * @param array $preparedRequest
     * @return \DOMDocument
     */
    protected function _processRequest(\Magento\Framework\DataObject $transaction,
            $preparedRequest)
    {
        if (!$this->helper->isModuleEnabled()) {
            throw new LocalizedException(__('Royal Mail shipping module is not active.'));
        }
        if ($this->isThrotledRequest()) {
            try {
                /**
                 *  Delete empty transaction
                 */
                if ($transaction->getId()) {
                    $transaction->delete();
                }
            } catch (\Exception $e) {
                $this->_logger->error($e->getMessage());
            }
            throw new LocalizedException(__('Request to RoyalMail throttled. Please try after some time.'));
        }
        $soapClient = $this->_getClient();
        $messageNonXml = null;
        try {
            $soapClient->__soapCall($transaction->getRequestType(),
                    array($preparedRequest),
                    array('soapaction' => $this->getGatewayUrl()));
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
            $messageNonXml = $e->getMessage();
        }
        $requestXml = $soapClient->__getLastRequest();
        $responseXml = $soapClient->__getLastResponse();
        $this->_debug('REQUEST "' . $transaction->getRequestType() . '"');
        $this->_debug($requestXml);
        $this->_debug('RESPONSE "' . $transaction->getRequestType() . '"');
        $this->_debug($responseXml);
        if (!$responseXml) {//if response xml is empty
            if ($messageNonXml) {
                $transaction->setHasError(true)
                        ->setMessage($messageNonXml)
                        ->save();
//                throw new LocalizedException($messageNonXml);
            } else {
                $transaction->setHasError(true)
                        ->setMessage(__('Unable to fetch API response.'))
                        ->save();
                throw new LocalizedException(__('Unable to fetch API response. Please try again.'));
            }
        }
        $doc = new \DOMDocument();
        try {
            $doc->loadXML($responseXml);
        } catch (\Exception $e) {
            $transaction->setHasError(true)->setMessage($e->getMessage())->save();
            if ($messageNonXml) {
//                throw new LocalizedException($messageNonXml);
            } else {
                throw new LocalizedException($e->getMessage());
            }
        }
        $faults = $doc->getElementsByTagName('Fault');
        if ($faults->length) {
            $exceptionCodes = $doc->getElementsByTagName('exceptionCode');
            if ($exceptionCodes->length) {
                $exceptionTexts = $doc->getElementsByTagName('exceptionText');
                $faultMessage = '[EXCEPTION CODE: ' . (($faultCode = $exceptionCodes->item(0)) ? $faultCode->nodeValue : '') . '] ';
                if ($m = $this->getCode('errors', $faultCode->nodeValue)) {
                    $faultMessage .= $m;
                } else {
                    $faultMessage .= (($faultString = $exceptionTexts->item(0)) ? $faultString->nodeValue : '');
                }
            } else {
                $faultMessage = '[FAULT CODE: ' . (($faultCode = $doc->getElementsByTagName('faultcode')->item(0)) ? $faultCode->nodeValue : '') . '] ' . (($faultString = $doc->getElementsByTagName('faultstring')->item(0)) ? $faultString->nodeValue : '');
            }
            if ($faultCode && $faultCode->nodeValue == self::THROTTLING_ERROR_CODE) {
                $this->setThrotledRequest();
            }
            $transaction->setMessage($transaction->getMessage() ? $transaction->getMessage() . '<br/>' . $faultMessage : $faultMessage);
            $transaction->setErrors($transaction->getMessage());
        }
        $integrationFooter = $doc->getElementsByTagName('integrationFooter');
        if ($integrationFooter->length):
            foreach ($integrationFooter->item(0)->childNodes as $node):
                if ($node->nodeName == 'errors'):
                    foreach ($node->childNodes as $errorNode):
                        foreach ($errorNode->childNodes as $child):
                            if ($child->nodeName == 'errorDescription') {
                                $transaction->setMessage($transaction->getMessage() ? $transaction->getMessage() . '<br/>' . $child->nodeValue : $child->nodeValue);
                                $transaction->setErrors($transaction->getMessage());
                            }
                        endforeach;
                    endforeach;
                endif;
            endforeach;
        endif;
        return $doc;
    }

    /**
     * Saves content to file
     * @param string $data Base64 encoded file content
     * @param string $format Content file format
     * @param string $name Name of file
     * @return string New unique filename
     */
    protected function _saveFile($data, $format, $name)
    {
        $mediaPath = $this->directoryList->getUrlPath(DirectoryList::MEDIA);
        $dir = $mediaPath . DIRECTORY_SEPARATOR . self::LABEL_DIR_NAME . DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('Y-m-d') . DIRECTORY_SEPARATOR;
        if (!file_exists($dir)) {
            @mkdir($dir, 0755, true);
        }
        $fileName = $dir . $name . '_' . date('H-i-s');
        $format = strtolower($format);
        switch ($format):
            case 'png':
                $fileName .= '.png';
                file_put_contents($fileName, base64_decode($data));
                break;
            case 'ds':
                $this->_logger->debug('Data stream not supported yet by Royal Mail Shipping module.');
                /* not implemented */
                break;
            case 'pdf'://PDF
            default ://DEFAULT
                $fileName .= '.pdf';
                file_put_contents($fileName, base64_decode($data));
        endswitch;
        return str_replace($mediaPath, '', $fileName);
    }

    protected function _getAllowedContainers(\Magento\Framework\DataObject $params = null)
    {
        return explode(',', $this->getConfigData('containers'));
    }

    /**
     *
     * @staticvar array $codes
     * @param type $type
     * @param type $code
     * @return boolean|array
     */
    public function getCode($type, $code = '')
    {
        return $this->carrierCodes->getCode($type, $code);
    }

}
