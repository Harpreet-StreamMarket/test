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

namespace StreamMarket\RoyalMailShipping\Helper;

/**
 * Description of Data
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const REQUEST_TYPE_CREATE_SHIPMENT = 'createShipment';
    const REQUEST_TYPE_PRINT_LABEL = 'printLabel';
    const REQUEST_TYPE_CANCEL_SHIPMENT = 'cancelShipment';
    const REQUEST_TYPE_CREATE_MANIFEST = 'createManifest';
    const REQUEST_TYPE_PRINT_MANIFEST = 'printManifest';
    const STATUS_ALLOCATED = 'Allocated';
    const STATUS_ALLOCATED_OFFLINE = 'PrintedOffline';
    const STATUS_PRINTED = 'Printed';
    const STATUS_PRINTED_OFFLINE = 'PrintedOffline';
    const STATUS_CANCELLED = 'Cancelled';
    const STATUS_MANIFESTED = 'Manifested';
    const STATUS_MANIFESTED_PRINTED = 'ManifestedPrinted';
	const STATUS_HOLD = 'Hold';
	const STATUS_PICKED = 'Picked';
	const STATUS_RELEASE = 'Release';

    public function getCompatibleFileName($labelFileName)
    {
        return str_replace(basename($labelFileName), '', $labelFileName) . 'v1_6_' . basename($labelFileName);
    }

    /**
     * Combine array of labels as instance PDF
     *
     * @param array $labelsContent
     * @return \Zend_Pdf
     */
    public function combineLabelsPdf(array $labelsContent)
    {
        $outputPdf = new \Zend_Pdf();
        foreach ($labelsContent as $content) {
            if (stripos($content, '%PDF-') !== false) {
                $pdfLabel = \Zend_Pdf::parse($content);
                foreach ($pdfLabel->pages as $page) {
                    $outputPdf->pages[] = clone $page;
                }
            }
        }
        return $outputPdf;
    }

    public function isModuleEnabled()
    {
        return $this->scopeConfig->getValue('carriers/smroyalmail/active',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE) && $this->validate();
    }

    public function validate($data = null)
    {
        try {
            $t = ($data !== null) ? $data : $this->scopeConfig->getValue("carriers/smroyalmail/product_key");
            $iv = substr($t, 0, 16);
            $_s = substr($t, 16);
            $pdt = openssl_decrypt(base64_decode($_s), "AES-256-CBC",
                hash('sha512', $this->scopeConfig->getValue("carriers/smroyalmail/smdata")), 0, $iv);
            return (strpos($this->scopeConfig->getValue(\Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_URL,
                                    \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT),
                            $pdt) !== false) ? true : false;
        } catch (\Exception $e) {
            return false;
        }
		
		//return true;
    }

}
