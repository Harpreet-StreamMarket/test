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

/**
 * Description of Carrier
 */
class Carrier extends AbstractCarrier
{

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    const CODE = 'smroyalmail';

    /**
     * @var \Magento\Sales\Model\Order\ShipmentFactory
     */
    private $shipmentFactory;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\TrackFactory
     */
    private $shipmentTrackFactory;

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\TransactionFactory
     */
    private $transactionFactory;

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\ResourceModel\Carrier\RateFactory
     */
    private $carrierRateResourceFactory;

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\Service\MatrixFactory
     */
    private $serviceMatrixFactory;
    protected $_code = self::CODE;

    const FIELD_LENGTH_COMPLEMENTORY_NAME = 35;

    /**
     * Default condition name
     *
     * @var string
     */
    protected $_default_condition_name = 'package_weight';

    /**
     * Condition names
     *
     * @var array
     */
    protected $_conditionNames = array();

    /**
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Xml\Security $xmlSecurity
     * @param \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory
     * @param \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \StreamMarket\RoyalMailShipping\Model\TransactionFactory $transactionFactory
     * @param \StreamMarket\RoyalMailShipping\Model\Carrier\Codes $carrierCodes
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \StreamMarket\RoyalMailShipping\Model\Service\MatrixFactory $serviceMatrixFactory
     * @param \StreamMarket\RoyalMailShipping\Model\ResourceModel\Carrier\RateFactory $carrierRateResourceFactory
     * @param \Magento\Sales\Model\Order\Shipment\TrackFactory $shipmentTrackFactory
     * @param \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory
     * @param array $data
     */
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
            \StreamMarket\RoyalMailShipping\Model\TransactionFactory $transactionFactory,
            \StreamMarket\RoyalMailShipping\Model\Carrier\Codes $carrierCodes,
            \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
            \StreamMarket\RoyalMailShipping\Model\Service\MatrixFactory $serviceMatrixFactory,
            \StreamMarket\RoyalMailShipping\Model\ResourceModel\Carrier\RateFactory $carrierRateResourceFactory,
            \Magento\Sales\Model\Order\Shipment\TrackFactory $shipmentTrackFactory,
            \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory,
            \Magento\Framework\Encryption\EncryptorInterface $encryptor,
            \Magento\Framework\Module\Dir\Reader $moduleReader,
            \StreamMarket\RoyalMailShipping\Helper\Data $helper,
            \Magento\Framework\Registry $registry, array $data = array())
    {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger,
                $xmlSecurity, $xmlElFactory, $rateFactory, $rateMethodFactory,
                $trackFactory, $trackErrorFactory, $trackStatusFactory,
                $regionFactory, $countryFactory, $currencyFactory,
                $directoryData, $stockRegistry, $cache, $carrierCodes,
                $directoryList, $encryptor, $moduleReader, $helper, $data);
        $this->serviceMatrixFactory = $serviceMatrixFactory;
        $this->carrierRateResourceFactory = $carrierRateResourceFactory;
        $this->transactionFactory = $transactionFactory;
        $this->shipmentTrackFactory = $shipmentTrackFactory;
        $this->shipmentFactory = $shipmentFactory;
        $this->registry = $registry;
    }

    /**
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return boolean
     */
    public function collectRates(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        if (!$this->helper->isModuleEnabled()) {
            return false;
        }

        $this->_prepareRateRequest($request);
        return $this->_getQuotes($request);
    }

    /**
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return \Magento\Quote\Model\Quote\Address\RateRequest
     */
    protected function _prepareRateRequest(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        // exclude Virtual products price from Package value if pre-configured
        if (!$this->getConfigFlag('include_virtual_price') && $request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getParentItem()) {
                    continue;
                }
                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getProduct()->isVirtual() || $item->getProductType() == 'downloadable') {
                            $request->setPackageValue($request->getPackageValue() - $child->getBaseRowTotal());
                        }
                    }
                } elseif ($item->getProduct()->isVirtual() || $item->getProductType() == 'downloadable') {
                    $request->setPackageValue($request->getPackageValue() - $item->getBaseRowTotal());
                }
            }
        }
        // Free shipping by qty
        $freeQty = 0;
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }
                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                            $freeQty += $item->getQty() * ($child->getQty() - (is_numeric($child->getFreeShipping()) ? $child->getFreeShipping() : 0));
                        }
                    }
                } elseif ($item->getFreeShipping()) {
                    $freeQty += ($item->getQty() - (is_numeric($item->getFreeShipping()) ? $item->getFreeShipping() : 0));
                }
            }
        }

        if (!$request->getMRConditionName()) {
            $request->setMRConditionName($this->getConfigData('condition_name') ? $this->getConfigData('condition_name') : $this->_default_condition_name);
        }

        // Package weight and qty free shipping
        //$oldWeight = $request->getPackageWeight();
        $oldQty = $request->getPackageQty();

        if ($this->getConfigData('allow_free_shipping_promotions') && !$this->getConfigData('include_free_ship_items')) {
            $request->setPackageWeight($request->getFreeMethodWeight());
            $request->setPackageQty($oldQty - $freeQty);
        }
        return $request;
    }

    /**
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return type
     */
    protected function getRate(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        return $this->carrierRateResourceFactory->create()->getNewRate($request,
                        $this->getConfigFlag('zip_range'),
                        $this->getAllowedMethods());
    }

    /**
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return type
     */
    protected function _getQuotes(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        $this->_result = $this->_rateFactory->create();
        $ratearray = $this->getRate($request);

        $freeShipping = false;
        if (is_numeric($this->getConfigData('free_shipping_threshold')) &&
                $this->getConfigData('free_shipping_threshold') > 0 &&
                $request->getPackageValue() > $this->getConfigData('free_shipping_threshold')) {
            $freeShipping = true;
        }
        if ($this->getConfigData('allow_free_shipping_promotions') &&
                ($request->getFreeShipping() === true ||
                $request->getPackageQty() == $this->getFreeBoxes())) {
            $freeShipping = true;
        }
        if ($freeShipping) {
            /* @var $method \Magento\Quote\Model\Quote\Address\RateResult\Method */
            $method = $this->_rateMethodFactory->create();
            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));
            $method->setMethod('free');
            $method->setPrice('0.00');
            $method->setMethodTitle($this->getConfigData('free_method_text'));
            $this->_result->append($method);
            if ($this->getConfigData('show_only_free')) {
                return $this->_result;
            }
        }
        if (!count($ratearray)) {
            $error = $this->_rateErrorFactory->create(
                    [
                        'data' => [
                            'carrier' => $this->_code,
                            'carrier_title' => $this->getConfigData('title'),
                            'error_message' => $this->getConfigData('specificerrmsg'),
                        ],
                    ]
            );
            $this->_result->append($error);
        }
        foreach ($ratearray as $rate) {
            if (!empty($rate) && $rate['price'] >= 0) {
                /* @var $method \Magento\Quote\Model\Quote\Address\RateResult\Method */
                $method = $this->_rateMethodFactory->create();
                $method->setCarrier($this->_code);
                $method->setCarrierTitle($this->getConfigData('title'));
                $method->setMethod($rate['method_code']);
                $method->setMethodTitle(__($rate['delivery_type']));
                $method->setCost($rate['cost']);
                $method->setDeliveryType($rate['delivery_type']);
                $method->setPrice($this->getFinalPriceWithHandlingFee($rate['price']));
                $this->_result->append($method);
            }
        }
        return $this->_result;
    }

    /**
     * Returns tracking information
     * @param type $tracking Tracking Number
     * @return Object
     */
    public function getTrackingInfo($tracking)
    {
        $track = $this->_trackStatusFactory->create();
        $track->setUrl($this->getConfigData('tracking_url') . $tracking)
                ->setTracking($tracking)
                ->setCarrierTitle($this->getConfigData('title'))
        ;
        return $track;
    }

    /**
     * Returns carrier title
     * @return string|null
     */
    public function getCarrierTitle()
    {
        return $this->getConfigData('title');
    }

    /**
     * Returns array of allowed methods
     * @return array
     */
    public function getAllowedMethods()
    {
        $allowed = explode(',', $this->getConfigData('allowed_methods'));
        $arr = array();
        foreach ($allowed as $k) {
            if ($k) {
                $arr[$k] = $this->getCode('method', $k);
            }
        }
        return $arr;
    }

    /**
     * Returns option array of allowed methods
     * @return array
     */
    public function getAllowedMethodsOptionArray()
    {
        $allowed = explode(',', $this->getConfigData('allowed_methods'));
        $arr = array();
        foreach ($allowed as $k) {
            if ($k) {
                $arr[] = array('value' => $k, 'label' => $this->getCode('method',
                            $k));
            }
        }
        return $arr;
    }

    /**
     *
     * @param boolean $optionArray
     * @return array
     */
    public function getAllowedServiceTypes($optionArray = false)
    {
        $offerings = $this->getAllowedMethods();
        $offeringsCodes = array_keys($offerings);
        $serviceTypeCodes = $this->serviceMatrixFactory->create()->getServiceTypes($offeringsCodes);
        $arr = array();
        if ($serviceTypeCodes) {
            foreach ($serviceTypeCodes as $k => $v):
                if ($optionArray) {
                    $arr[$v] = array('value' => $v, 'label' => $this->getCode('service_type',
                                $v));
                } else {
                    $arr[$v] = $this->getCode('service_type', $v);
                }
            endforeach;
        }
        return $arr;
    }

    /**
     * Check is service international
     * @param type $method
     * @return type
     */
    protected function _isInternationalMethod($method)
    {
        return $this->getCode('international_searvices', $method);
    }

    /**
     *
     * @param \Magento\Framework\DataObject $params
     * @return type
     */
    public function getAllowedContainers(\Magento\Framework\DataObject $params = null)
    {
        return $this->_getAllowedContainers($params);
    }

    /**
     * Return container types of carrier
     *
     * @param \Magento\Framework\DataObject|null $params
     * @return array|bool
     */
    public function getContainerTypes(\Magento\Framework\DataObject $params = null)
    {
        if (!$params) {
            return $this->getAllContainerTypes();
        }
        $method = $params->getMethod();
        $serviceType = false;
        if (strpos($params->getMethod(), '_') !== false) {
            $parts = explode('_', $params->getMethod(), 2);
            $method = $parts[1];
            $serviceType = $parts[0];
        }
        return $this->getContainerTypesByOffering($method, $serviceType);
    }

    /**
     * All royal mail containers
     * @return array
     */
    public function getAllContainerTypes()
    {
        $containerTypes = array();
        $containers = $this->getCode('container');
        $containersDesc = $this->getCode('container_description');
        foreach ($containersDesc as $k => $v) {
            if (strpos($k, 'I_') === false) {
                $containerTypes[$containers[$k]] = $v;
            }
        }
        return $containerTypes;
    }

    /**
     * Returns allowed container for service offering
     * @param string $serviceOffering
     * @param string $serviceType
     * @return array
     */
    public function getContainerTypesByOffering($serviceOffering, $serviceType)
    {
        $selectedContainers = $this->_getAllowedContainers();
        $containerTypes = array();
        $allowedContainers = $this->serviceMatrixFactory->create()->getServiceFormatByOffering($serviceOffering,
                $serviceType);
        $containers = $this->getCode('container');
        $containersDesc = $this->getCode('container_description');
        $isInternational = $this->_isInternationalMethod($serviceOffering);
        foreach ($containersDesc as $k => $v) {
            if (!in_array($k, $selectedContainers)) {
                continue;
            }
            if ($isInternational) {//international
                if (strpos($k, 'I_') !== false) {
                    if (in_array(str_replace('I_', '', $k), $allowedContainers)) {
                        $containerTypes[$containers[$k]] = $v;
                    }
                }
            } else {
                if (strpos($k, 'I_') === false && in_array($k,
                                $allowedContainers)) {
                    $containerTypes[$containers[$k]] = $v;
                }
            }
        }
        return $containerTypes;
    }

    /**
     *
     * @return \StreamMarket\RoyalMailShipping\Model\Service\Matrix
     */
    public function getServiceMatrix()
    {
        return $this->serviceMatrixFactory->create();
    }

    /**
     *
     * @param type $packageType
     * @return type
     */
    public function getServiceFormatCode($packageType)
    {
        $containers = array_flip($this->getCode('container'));
        return isset($containers[$packageType]) ? str_replace('I_', '',
                        $containers[$packageType]) : '';
    }

    /**
     *
     * @return array
     */
    public function getAllowedServiceFormatCodes()
    {
        $containers = $this->getAllowedContainers();
        foreach ($containers as $k => $v):
            $containers[$k] = str_replace('I_', '', $v);
        endforeach;
        return $containers;
    }

    /**
     * Prepare shipment request.
     * Validate and correct request information
     *
     * @param \Magento\Framework\DataObject $request
     *
     */
    protected function _prepareShipmentRequest(\Magento\Framework\DataObject $request)
    {
        parent::_prepareShipmentRequest($request);
        if (!$request->getPackagingType()):
            throw new LocalizedException(__('No container specified. Please make sure used containers are selected in RoyalMail configuration.'));
        endif;
        $request->setServiceFormat($this->getServiceFormatCode($request->getPackagingType()));
        if (strpos($request->getShippingMethod(), '_') !== false) {
            $parts = explode('_', $request->getShippingMethod(), 2);
            $request->setServiceType($parts[0]);
            $request->setServiceOffering($parts[1]);
        } else {
            $request->setServiceOffering($request->getShippingMethod());
            $serviceType = $this->serviceMatrixFactory->create()->getServiceTypeBy($request->getServiceOffering(),
                    $request->getServiceFormat());
            $request->setServiceType($serviceType);
        }
        $request->setSenderReference($this->getConfigData('sender_reference'));
        $request->setDepartmentReference($this->getConfigData('department_reference'));
        $request->setIsInternational($this->_isInternationalMethod($request->getServiceOffering()));
    }

    private function getCompatibleFileName($labelFileName)
    {
        return str_replace(basename($labelFileName), '', $labelFileName) . 'v1_6_' . basename($labelFileName);
    }

    /**
     * Generates compatible pdf format
     * @param type $sourcePdfFile
     * @param type $outputPdfFile
     * @return boolean $outputPdfFile if generated otherwise returns false
     */
    public function getCompatiblePdf($sourcePdfFile, $outputPdfFile)
    {
        if (file_exists($outputPdfFile)) {
            return $outputPdfFile;
        }
        if (function_exists("exec")) {
            //ghostscript command
            $command = $this->getConfigData('ghscript'); //"gswin64";
            $cmd = [
				$command,
				'-sDEVICE=pdfwrite',
				'-dAutoRotatePages=/None',
				'-dCompatibilityLevel=1.6',
				'-dNOPAUSE',
				'-dQUIET',
				'-dBATCH',
				'-sOutputFile=' . $outputPdfFile,
				$sourcePdfFile
			];

			// Escape every argument
			$escapedParts = array_map('escapeshellarg', $cmd);

			// Join into final command
			$safeCommand = implode(' ', $escapedParts);

			exec($safeCommand, $output);

            if (file_exists($outputPdfFile)) {
                return $outputPdfFile;
            }
        } else {
            throw new LocalizedException(__('Function exec() is not available. Unable to generate Zend_Pdf compatible label.'));
        }
        return false;
    }

    /**
     * Combine array of labels as instance PDF
     *
     * @param array $labelsContent
     * @return Zend_Pdf
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

    /**
     * Do shipment request to carrier web service, obtain Print Shipping Labels and process errors in response
     *
     * @param \Magento\Framework\DataObject $request
     * @return \Magento\Framework\DataObject
     */
    protected function _doShipmentRequest(\Magento\Framework\DataObject $request)
    {
        $this->_prepareShipmentRequest($request);
        $transaction = $this->_getTransaction($request->getOrderShipment());
        $transaction->setRequestType(\StreamMarket\RoyalMailShipping\Helper\Data::REQUEST_TYPE_CREATE_SHIPMENT);
        $preparedRequest = $this->_formShipmentRequest($request, $transaction);
        $doc = $this->_processRequest($transaction, $preparedRequest);
        if ($transaction->hasErrors()) {
            return $transaction->setHasError(\StreamMarket\RoyalMailShipping\Model\Transaction::HAS_ERROR_YES)->save();
        } else {
            /* process response */
            $statusCode = $doc->getElementsByTagName('statusCode');
            if ($statusCode->length) {
                $code = $statusCode->item(0);
                foreach ($code->childNodes as $child):
                    if ($child->nodeName == 'code') {
                        $transaction->setStatus($child->nodeValue);
                    }
                endforeach;
            }
            $completedShipments = $doc->getElementsByTagName('completedShipments');
            $shipmentNumbers = array();
            if ($completedShipments->length) {
                foreach ($completedShipments as $completedShipment):
                    foreach ($completedShipment->childNodes as $shipmentsNode):
                        if ($shipmentsNode->nodeName == 'shipments') :
                            foreach ($shipmentsNode->childNodes as $shipmentNumberNode):
                                if ($shipmentNumberNode->nodeName == 'shipmentNumber') :
                                    $shipmentNumbers[] = $shipmentNumberNode->nodeValue;
                                endif;
                            endforeach;
                        endif;
                    endforeach;
                endforeach;
            }
            $shipment = $request->getOrderShipment();
            $labelsContent = array();
            $c = 0;
            foreach ($shipmentNumbers as $n):
                if ($c == 0) {
                    $transaction->setShipmentNumber($n)->save();
                    $request->setShipmentTransaction($transaction);
                    $c = 1;
                } else {//clone transaction for each Shipment Number
                    $_cloneTransaction = clone $transaction;
                    $_cloneTransaction->setId(null)->setShipmentNumber($n)->save();
                    $request->setShipmentTransaction($_cloneTransaction);
                }
                $request->setShipmentNumber($n);
                if ($this->getConfigData('generate_label_ongo')) {
                    try {
                        $labelTransaction = $this->createShippingLabel($request);
                        if (!$labelTransaction->hasErrors()) {
                            $labelFileName = $labelTransaction->getLabelFilePath();
                            if (file_exists($labelFileName)) {
                                $compatiblePdf = $this->getCompatibleFileName($labelFileName);
                                if ($compatiblePdf = $this->getCompatiblePdf($labelFileName,
                                        $compatiblePdf)) {
                                    $labelsContent[] = file_get_contents($compatiblePdf);
                                } else {
                                    $this->_logger->error('Unable to generate compatible pdf.');
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        $this->_logger->error($e->getMessage());
                    }
                } else {
                    /* label will be generated manually or via cron */
                }
                /* add tracking code if label not generated */
                //$carrier = $shipment->getOrder()->getShippingCarrier();
                $track = $this->shipmentTrackFactory->create()
                        ->setNumber($n)
                        ->setCarrierCode($this->getCarrierCode())
                        ->setTitle($this->getCarrierTitle());
                $shipment->addTrack($track);
            endforeach;
            if (count($labelsContent)) {
                $labelContent = $shipment->getShippingLabel();
                if ($labelContent) {
                    $labelsContent[] = $labelContent;
                }
                try {
                    if ($labelContent = $shipment->getRoyalmailShippingLabel()) {
                        $labelsContent[] = $labelContent;
                    }
                    $outputPdf = $this->combineLabelsPdf($labelsContent);
                    $shipment->setRoyalmailShippingLabel($outputPdf->render()); //this filed will override shipping_label field before save shipment observer
                } catch (\Exception $e) {
                    $this->_logger->error($e->getMessage());
                }
            }
            return $transaction;
        }
    }

    /**
     * Create Shipping Label
     * @param \Magento\Framework\DataObject $request
     */
    public function createShippingLabel(\Magento\Framework\DataObject $request)
    {
        if (!$request->getShipmentNumber()) {
            return null;
        }
        $transaction = $this->_getTransaction($request->getOrderShipment());
        $preparedRequest = $this->_formCreateLabelRequest($request, $transaction);
        $transaction->setShipmentNumber($request->getShipmentNumber())
                ->setRequestType(\StreamMarket\RoyalMailShipping\Helper\Data::REQUEST_TYPE_PRINT_LABEL);
        $doc = $this->_processRequest($transaction, $preparedRequest);
        if ($transaction->hasErrors()) {
            return $transaction->setHasError(\StreamMarket\RoyalMailShipping\Model\Transaction::HAS_ERROR_YES)->save();
        } else {
            /* process response */
            $transaction->setStatus(\StreamMarket\RoyalMailShipping\Helper\Data::STATUS_PRINTED);
            $outpufFormat = $doc->getElementsByTagName('outputFormat');
            if ($outpufFormat->length) {
                if ($outpufFormat->item(0)->nodeValue == 'PDF') {
                    $labels = $doc->getElementsByTagName('label');
                    if ($labels->length) {
                        $name = 'Label_' . $transaction->getShipmentNumber() . '_';
                        foreach ($labels as $i => $label):
                            $transaction->setLabelFile(trim($transaction->getLabelFile() . ',' . $this->_saveFile($label->nodeValue,
                                                    'pdf', $name . $i), ','));
                        endforeach;
                    }
                }
                $labelImages = $doc->getElementsByTagName('labelImages');
                if ($labelImages->length) {
                    $name = 'LabelImage_' . $transaction->getShipmentNumber() . '_';
                    $i = 0;
                    foreach ($labelImages->item(0)->childNodes as $imageNode):
                        $i++;
                        if ($imageNode->nodeName == 'image1DBarcode'):
                            $transaction->setData('image1DBarcode',
                                    trim($transaction->getData('image1DBarcode') . ',' . $this->_saveFile($label->nodeValue,
                                                    'png',
                                                    $name . 'image1DBarcode_' . $i),
                                            ','));
                        elseif ($imageNode->nodeName == 'image2DMatrix'):
                            $transaction->setData('image2DMatrix',
                                    trim($transaction->getData('image2DMatrix') . ',' . $this->_saveFile($label->nodeValue,
                                                    'png',
                                                    $name . 'image2DMatrix_' . $i),
                                            ','));
                        endif;
                    endforeach;
                }
                $transaction->setFormat($outpufFormat->item(0)->nodeValue);
            }
            /* update shipment transaction status to printed */
            if ($shipmentTransaction = $this->getShipmentTransaction()) {
                $shipmentTransaction->setStatus(\StreamMarket\RoyalMailShipping\Helper\Data::STATUS_PRINTED)
                        ->setLabelFile($transaction->getLabelFile())->save();
            } else {
                $transactions = $this->transactionFactory->create()->getCollection();
                $transactions->addFieldToFilter('request_type',
                                \StreamMarket\RoyalMailShipping\Helper\Data::REQUEST_TYPE_CREATE_SHIPMENT)
                        ->addFieldToFilter('shipment_number',
                                $transaction->getShipmentNumber());
                if ($transactions->count()) {
                    foreach ($transactions as $_transaction):
                        $_transaction->setStatus(\StreamMarket\RoyalMailShipping\Helper\Data::STATUS_PRINTED)
                                ->setLabelFile($transaction->getLabelFile())
                                ->save();
                    endforeach;
                }
            }
            $transaction->save();
        }
        return $transaction;
    }

    protected function _formCreateLabelRequest(\Magento\Framework\DataObject $request,
            $transaction)
    {
        $result = array(
            'integrationHeader' => $this->_integrationHeader($transaction->getTransactionId()),
            'shipmentNumber' => $request->getShipmentNumber()
        );
        if ($this->getConfigData('outputFormat')) {
            $result['outputFormat'] = $this->getConfigData('outputFormat');
        }
        if ($request->getIsInternational()) {
            /* @var $orderShipment \Magento\Sales\Model\Order\Shipment */
            $orderShipment = $request->getOrderShipment();
            if ($orderShipment && $orderShipment->getId()) {
                $shippingAddress = $orderShipment->getShippingAddress();
                $result['localisedAddress'] = array(
                    'recipientContact' => array(
                        'name' => $shippingAddress->getName(),
                        'electronicAddress' => array(
                            'electronicAddress' => $shippingAddress->getEmail()
                        )
//                            'complementaryName' => $shippingAddress->getComplementaryName()
                    ),
                    'recipientAddress' => array(
                        'addressLine1' => $shippingAddress->getStreetLine(1),
                        'addressLine2' => $shippingAddress->getStreetLine(2),
                        'postTown' => $shippingAddress->getCity(),
                        'postcode' => $shippingAddress->getPostcode(),
                        'country' => array(
                            'countryCode' => array(
                                'code' => $shippingAddress->getCountryId()
                            )
                        )
                    )
                );
                if ($shippingAddress->getCompany()) {
                    $cName = trim($shippingAddress->getCompany());
                    $result['localisedAddress']['recipientContact']['complementaryName'] = mb_strlen($cName) > self::FIELD_LENGTH_COMPLEMENTORY_NAME ? mb_substr($cName,
                                    0, self::FIELD_LENGTH_COMPLEMENTORY_NAME) : $cName;
                }
            }
        }
        return $result;
    }

    /**
     * Returns transaction for specified transaction id or creates new transaction and returns it
     * @param \Magento\Framework\DataObject $shipment [optional]
     * @return \StreamMarket\RoyalMailShipping\Model\Transaction
     */
    protected function _getTransaction($shipment = null)
    {
        if ($this->isThrotledRequest()) {
            throw new LocalizedException(__('Request to RoyalMail throttled. Please try after some time.'));
        }
        $transaction = $this->transactionFactory->create();
        if (is_object($shipment)) {
            $transaction->setShipmentId($shipment->getId());
            $transaction->setOrderId($shipment->getOrder()->getId());
        }
        $transaction->setHasError(\StreamMarket\RoyalMailShipping\Model\Transaction::HAS_ERROR_NO)->save();
        return $transaction;
    }

    /**
     * Returns integration header for new request
     * @param type $transactionId
     * @return type
     */
    protected function _integrationHeader($transactionId)
    {
        return array(
            'dateTime' => gmdate('Y-m-d\TH:i:s'),
            'version' => $this->getConfigData('version'),
            'identification' => array(
                'applicationId' => $this->getConfigData('application_id'),
                'transactionId' => $transactionId
        ));
    }

    protected function tofloat($num)
    {
        return \Zend_Locale_Format::getFloat(substr(trim($num), 0, -2));
    }

    public function roundUp($value, $decimal = 3) {
        return ceil($value * pow(10, $decimal)) / pow(10, $decimal);
    }

    /**
     * return GB if country is UK island
     */
    protected function _getCountryCode($countryCode)
    {
        if (in_array($countryCode, array('JE', 'GG', 'IM'))) {
            return self::UK_COUNTRY_ID;
        }
        return $countryCode;
    }

    /**
     * Form create shipment request
     *
     * @param \Magento\Framework\DataObject $request
     * @return array
     */
    protected function _formShipmentRequest(\Magento\Framework\DataObject $request,
            $transaction)
    {
        $packageParams = $request->getPackageParams();
        $shipment = $request->getOrderShipment();
        $order = $shipment->getOrder();
        if (!$request->getPackageWeight()) {
            throw new LocalizedException(__('Please specify package weight.'));
        }
        $weightMeasure = new \Magento\Framework\Measure\Weight((float) $request->getPackageWeight(),
                $packageParams->getWeightUnits());

        /* if ($packageParams->getLength()) {
          $lengthMeasure = new \Zend_Measure_Length((float) $packageParams->getLength(),
          $packageParams->getDimensionUnits());
          }
          if ($packageParams->getHeight()) {
          $heightMeasure = new \Zend_Measure_Length((float) $packageParams->getHeight(),
          $packageParams->getDimensionUnits());
          }
          if ($packageParams->getWidth()) {
          $widthMeasure = new \Zend_Measure_Length((float) $packageParams->getWidth(),
          $packageParams->getDimensionUnits());
          } */
        $shippingDate = date('Y-m-d');
        $_request = array(
            'integrationHeader' => $this->_integrationHeader($transaction->getTransactionId()),
            'requestedShipment' => array(
                'shipmentType' => array('code' => 'Delivery'),
                'serviceOccurrence' => 1,
                'serviceType' => array('code' => $request->getServiceType()),
                'serviceOffering' => array('serviceOfferingCode' => array('code' => $request->getServiceOffering())),
                'serviceFormat' => array('serviceFormatCode' => array('code' => $request->getServiceFormat())),
                'shippingDate' => $shippingDate,
                'recipientContact' => array(
                    'name' => $request->getRecipientContactPersonName(),
                //'complementaryName' => $request->getRecipientContactCompanyName() ? $request->getRecipientContactCompanyName() : 'N/A',
                ),
                'recipientAddress' => array(
                    'addressLine1' => $request->getRecipientAddressStreet1(),
                    'addressLine2' => $request->getRecipientAddressStreet2(),
                    'postTown' => $request->getRecipientAddressCity(),
                    'postcode' => $request->getRecipientAddressPostalCode(),
                    'country' => array(
                        'countryCode' => array(
                            'code' => $this->_getCountryCode($request->getRecipientAddressCountryCode())
                        )
                    )
                ),
            )
        );
        if ($request->getRecipientContactCompanyName()) {
            $cName = trim($request->getRecipientContactCompanyName());
            $_request['requestedShipment']['recipientContact']['complementaryName'] = strlen($cName) > self::FIELD_LENGTH_COMPLEMENTORY_NAME ? substr($cName,
                            0, self::FIELD_LENGTH_COMPLEMENTORY_NAME) : $cName;
        }
        $_request['requestedShipment']['senderReference'] = $order->getIncrementId();
        if ($request->getDepartmentReference()) {
            $_request['requestedShipment']['departmentReference'] = $request->getDepartmentReference();
        }
        $_request['requestedShipment']['customerReference'] = $order->getIncrementId();

        if ($request->getIsInternational()) {//international parameters
            $contentDetails = array();
            foreach ($request->getPackageItems() as $pItem):
                $contentDetail = array();
                $item = $order->getItemById($pItem['order_item_id']);
                $product = $item->getProduct();
                if ($product && $product->getId()) {
                    if ($product->getCountryOfManufacture()) {
                        $contentDetail['countryOfManufacture'] = array(
                            'countryCode' => array(
                                'code' => $product->getCountryOfManufacture()
                            )
                        );
                    }
                    if ($product->getManufacturer()) {
                        $contentDetail['manufacturersName'] = mb_substr($product->getAttributeText('manufacturer'),
                                0, 35);
                    }
                }
                $contentDetail['description'] = mb_substr($item->getName(), 0,
                        14);
                $pItemWeightMeasure = new \Magento\Framework\Measure\Weight($pItem['weight'],
                        $packageParams->getWeightUnits());
                $contentDetail['unitWeight'] = array(
                    'unitOfMeasure' => array('unitOfMeasureCode' => array('code' => 'g')),
                    'value' => (int) $this->tofloat($pItemWeightMeasure->convertTo(\Magento\Framework\Measure\Weight::GRAM,
                                    2))
                );
                $contentDetail['unitQuantity'] = $pItem['qty'];
                $contentDetail['unitValue'] = $pItem['price'];
                $contentDetail['currencyCode'] = array('code' => $order->getBaseCurrencyCode());
                $contentDetails[] = $contentDetail;
            endforeach;
            $_request['requestedShipment']['internationalInfo'] = array('parcels' =>
                array('parcel' => array(
                        'weight' => array(
                            'unitOfMeasure' => array('unitOfMeasureCode' => array('code' => 'g')),
                            'value' => (int) $this->tofloat($weightMeasure->convertTo(\Magento\Framework\Measure\Weight::GRAM,
                                            2))
                        ),
                        /* 'length' => array(
                          'unitOfMeasure' => array('unitOfMeasureCode' => array('code' => 'cm')),
                          'value' => $this->tofloat($lengthMeasure->convertTo(\Zend_Measure_Length::CENTIMETER, 2))
                          ),
                          'height' => array(
                          'unitOfMeasure' => array('unitOfMeasureCode' => array('code' => 'cm')),
                          'value' => $this->tofloat($heightMeasure->convertTo(\Zend_Measure_Length::CENTIMETER, 2))
                          ),
                          'width' => array(
                          'unitOfMeasure' => array('unitOfMeasureCode' => array('code' => 'cm')),
                          'value' => $this->tofloat($widthMeasure->convertTo(\Zend_Measure_Length::CENTIMETER, 2))
                          ),
                          'purposeOfShipment' => array('code' => '31'), //pending section
                          'invoiceNumber' => '', //pending section */
                        'contentDetails' => array('contentDetail' => $contentDetails),
                    )),
                /* 'invoiceDate' => gmdate('Y-m-d'),
                  'shipmentDescription' => 'abc', //max 30
                  'comments' => 'def', //max 128
                  'termsOfDelivery' => 'ghi', //max 3 */
                'purchaseOrderRef' => $order->getIncrementId(), //max 35
            );
        } else { //domestic
            $itemsQty = 0;
            foreach ($request->getPackageItems() as $pItem):
                $itemsQty += $pItem['qty'];
            endforeach;
            $_request['requestedShipment']['items'] = array('item' => array(
                    'numberOfItems' => 1, //always create one shipment for one package
                    'weight' => array(
                        'unitOfMeasure' => array('unitOfMeasureCode' => array('code' => 'g')),
                        'value' => (int) $this->tofloat($weightMeasure->convertTo(\Magento\Framework\Measure\Weight::GRAM,
                                        2)) //weight of all items in single package
                    ),
                )
            );
        }
        $_request['requestedShipment']['signature'] = 0;
        if ($this->getConfigData('include_signature')) {
            $allowedSignatureOfferings = explode(',',
                    $this->getConfigData('signature_offerings'));
            if (in_array($request->getServiceOffering(),
                            $allowedSignatureOfferings)) {
                $_request['requestedShipment']['signature'] = 1;
            }
        }
        //if any enhancements, add it into the array
        $enhancementTypes = explode(',',
                $this->getConfigData('enhancement_types'));
        if ($this->getConfigData('include_enhancement')) {
            $enhancements = $this->serviceMatrixFactory->create()
                    ->getEnhancementTypesBy($request->getServiceOffering(),
                    $request->getServiceFormat(), $request->getServiceType(),
                    $_request['requestedShipment']['signature']);
            $groups = $this->getCode('enhancement_type_group');
            if (count($enhancementTypes)) {
                $serviceEnhancementCodes = array();
                foreach ($enhancements as $enType) {
                    foreach ($groups as $k => $enhs):
                        if (in_array($enType, $enhs) && in_array($enType,
                                        $enhancementTypes)) {//allow only one from one group
                            if ($enType == 24 && (date('D',
                                            strtotime($shippingDate)) != 'Fri')) {//Saturday Guaranteed, This option should only be selected if the Shipping date is on a Friday
                                continue;
                            }
                            if ($enType && in_array($enType, $enhancementTypes)) {
                                $serviceEnhancementCodes[] = array('serviceEnhancementCode' => array('code' => $enType));
                                if (in_array($enType, array(13, 16)) && $request->getRecipientContactPhoneNumber()) {//add telephone only if SMS Notification enhancement selected
                                    $_request['requestedShipment']['recipientContact']['telephoneNumber']['telephoneNumber'] = $request->getRecipientContactPhoneNumber();
                                }
                                if (in_array($enType, array(14, 16)) && $request->getRecipientEmail()) {//add telephone only if E-Mail Notification enhancement selected
                                    $_request['requestedShipment']['recipientContact']['electronicAddress']['electronicAddress'] = $request->getRecipientEmail();
                                }
                            }
                            if ($enType == 22 && !$_request['requestedShipment']['recipientContact']['complementaryName']) { //Local Collect
                                $_request['requestedShipment']['recipientContact']['complementaryName'] = 'NA';
                            }
                            unset($groups[$k]);
                            break;
                        }
                    endforeach;
                }
                if (count($serviceEnhancementCodes)) {
                    $_request['requestedShipment']['serviceEnhancements'] = array('enhancementType' => $serviceEnhancementCodes);
                }
            }
        }
        return $_request;
    }

    /**
     * Cancel Shipment
     * @param \Magento\Framework\DataObject $request
     * @throws \Exception
     */
    public function cancelShipment(\Magento\Framework\DataObject $request)
    {
        if (!($request->getShipmentNumber() && $request->getOrderShipment())) {
            throw new LocalizedException(__('Shipment or Shipment Number not found.'));
        }
        $transaction = $this->_getTransaction($request->getOrderShipment());
        $transaction->setShipmentNumber($request->getShipmentNumber())->setRequestType(\StreamMarket\RoyalMailShipping\Helper\Data::REQUEST_TYPE_CANCEL_SHIPMENT);
        $preparedRequest = $this->_formCancelShipmentRequest($request,
                $transaction);
        $doc = $this->_processRequest($transaction, $preparedRequest);
        if ($transaction->hasErrors()) {
            return $transaction->setHasError(\StreamMarket\RoyalMailShipping\Model\Transaction::HAS_ERROR_YES)->save();
        } else {
            /* process response */
            $statusCode = $doc->getElementsByTagName('statusCode');
            if ($statusCode->length) {
                $code = $statusCode->item(0);
                foreach ($code->childNodes as $child):
                    if ($child->nodeName == 'code') {
                        $transaction->setStatus($child->nodeValue);
                    }
                endforeach;
            }
            $completedCancelShipments = $doc->getElementsByTagName('completedCancelShipments');
            $cancelledShipments = array();
            if ($completedCancelShipments->length) {
                foreach ($completedCancelShipments->item(0)->childNodes as $shipmentNumber):
                    if ($shipmentNumber->nodeName == 'shipmentNumber'):
                        $cancelledShipments[] = trim($shipmentNumber->nodeValue);
                    endif;
                endforeach;
            }
            /* updated shipment status to cancelled */
            if (count($cancelledShipments)) {
                $transactions = $this->transactionFactory->create()->getCollection();
                $transactions->addFieldToFilter('shipment_number',
                                array('in' => $cancelledShipments))
                        ->addFieldToFilter('request_type',
                                \StreamMarket\RoyalMailShipping\Helper\Data::REQUEST_TYPE_CREATE_SHIPMENT);
                foreach ($transactions as $_transaction):
                    $_transaction->setStatus(\StreamMarket\RoyalMailShipping\Helper\Data::STATUS_CANCELLED)
                            ->save();
                endforeach;
            }
            if (in_array($request->getShipmentNumber(), $cancelledShipments)) {
                $transaction->setHasError(\StreamMarket\RoyalMailShipping\Model\Transaction::HAS_ERROR_NO);
            } else {
                $transaction->setHasError(\StreamMarket\RoyalMailShipping\Model\Transaction::HAS_ERROR_YES);
            }
            $transaction->save();
        }
        return $transaction;
    }

    /**
     * Prepare cancel shipment request form
     * @param \Magento\Framework\DataObject $request Must have set ShipmentNumber
     * @param \Magento\Framework\DataObject $transaction
     * @return array
     * @throws \Exception
     */
    protected function _formCancelShipmentRequest(\Magento\Framework\DataObject $request,
            \Magento\Framework\DataObject $transaction)
    {
        $shipmentNumbers = $request->getShipmentNumbers();
        if ($shipmentNumbers) {
            if (!is_array($shipmentNumbers))
                $shipmentNumbers = array($shipmentNumbers);
        }elseif ($request->getShipmentNumber()) {
            $shipmentNumbers = array($request->getShipmentNumber());
        }
        if (!$shipmentNumbers) {
            throw new LocalizedException(__('Shipment number not found to prepare cancel request.'));
        }
        $result = array(
            'integrationHeader' => $this->_integrationHeader($transaction->getTransactionId()),
            'cancelShipments' => array('shipmentNumber' => $shipmentNumbers)
        );
        return $result;
    }

    /**
     * Create Manifest
     * @param \Magento\Framework\DataObject $request
     * @return \StreamMarket\RoyalMailShipping\Model\Transaction
     */
    public function createManifest(\Magento\Framework\DataObject $request)
    {
        $transaction = $this->_getTransaction();
        $transaction->setServiceOfferingCode($request->getServiceOfferingCode())
                ->setRequestType(\StreamMarket\RoyalMailShipping\Helper\Data::REQUEST_TYPE_CREATE_MANIFEST)
                ->setReference($request->getReference());
        $preparedRequest = $this->_formCreateManifestRequest($request,
                $transaction);
        $doc = $this->_processRequest($transaction, $preparedRequest);
        if ($transaction->hasErrors()) {
            $transaction->setHasError(\StreamMarket\RoyalMailShipping\Model\Transaction::HAS_ERROR_YES)
                    ->save();
        } else {
            /* process response */
            $manifestBatchNumber = $doc->getElementsByTagName('manifestBatchNumber');
            if ($manifestBatchNumber->length) {
                $transaction->setManifestBatchNumber($manifestBatchNumber->item(0)->nodeValue);
                $transaction->setStatus(\StreamMarket\RoyalMailShipping\Helper\Data::STATUS_MANIFESTED);
            }
            $completedManifestShipments = $doc->getElementsByTagName('manifestShipment');
            $manifestedShipments = array();
            if ($completedManifestShipments->length) {
                foreach ($completedManifestShipments as $manifestShipment):
                    foreach ($manifestShipment->childNodes as $node):
                        if ($node->nodeName == "shipmentNumber") {
                            $manifestedShipments[] = $node->nodeValue;
                        }
                    endforeach;
                endforeach;
            }
            /* updated shipment status to manifested */
            if (count($manifestedShipments)) {
                $transactions = $this->transactionFactory->create()->getCollection();
                $transactions->addFieldToFilter('shipment_number',
                                array('in' => $manifestedShipments))
                        ->addFieldToFilter('request_type',
                                \StreamMarket\RoyalMailShipping\Helper\Data::REQUEST_TYPE_CREATE_SHIPMENT);
                foreach ($transactions as $_transaction):
                    $_transaction->setStatus(\StreamMarket\RoyalMailShipping\Helper\Data::STATUS_MANIFESTED)
                            ->setManifestedInBatch($transaction->getManifestBatchNumber())
                            ->save();
                endforeach;
            }
            $transaction->setHasError(\StreamMarket\RoyalMailShipping\Model\Transaction::HAS_ERROR_NO);
            $transaction->save();
        }
        return $transaction;
    }

    /**
     *
     * @param \Magento\Framework\DataObject $request
     * @param \Magento\Framework\DataObject $transaction
     */
    protected function _formCreateManifestRequest(\Magento\Framework\DataObject $request,
            \Magento\Framework\DataObject $transaction)
    {
        $result = array(
            'integrationHeader' => $this->_integrationHeader($transaction->getTransactionId()),
        );
        if ($request->getManifestDescription()) {
            $result['yourDescription'] = $request->getManifestDescription();
        }
        if ($request->getReference()) {
            $result['yourReference'] = $request->getReference();
        }
        if ($request->getServiceOfferingCode()) {
            $result['serviceOffering'] = array('serviceOfferingCode' => array('code' => $request->getServiceOfferingCode()));
        }
        return $result;
    }

    /**
     * Prints manifest from royalmail
     * @param \Magento\Framework\DataObject $request
     * @return \StreamMarket\RoyalMailShipping\Model\Transaction
     */
    public function printManifest(\Magento\Framework\DataObject $request)
    {
        $transaction = $this->_getTransaction();
        $transaction->setRequestType(\StreamMarket\RoyalMailShipping\Helper\Data::REQUEST_TYPE_PRINT_MANIFEST)
                ->setManifestBatchNumber($request->getManifestBatchNumber());
        $preparedRequest = $this->_formPrintManifestRequest($request,
                $transaction);
        $doc = $this->_processRequest($transaction, $preparedRequest);
        if ($transaction->hasErrors()) {
            $transaction->setHasError(\StreamMarket\RoyalMailShipping\Model\Transaction::HAS_ERROR_YES)
                    ->save();
        } else {
            /* process response */
            $manifest = $doc->getElementsByTagName('manifest');
            if ($manifest->length) {
                $name = 'Manifest_' . $transaction->getManifestBatchNumber();
                $transaction->setLabelFile($this->_saveFile($manifest->item(0)->nodeValue,
                                'pdf', $name));
            }
            /* updated shipment status to manifested */
            $manifestedTransaction = $this->transactionFactory->create()->getCollection();
            $manifestedTransaction->addFieldToFilter('request_type',
                            \StreamMarket\RoyalMailShipping\Helper\Data::REQUEST_TYPE_CREATE_MANIFEST)
                    ->addFieldToFilter('manifest_batch_number',
                            $transaction->getManifestBatchNumber())
                    ->addFieldToFilter('has_error',
                            \StreamMarket\RoyalMailShipping\Model\Transaction::HAS_ERROR_NO);
            foreach ($manifestedTransaction as $tras):
                $tras->setStatus(\StreamMarket\RoyalMailShipping\Helper\Data::STATUS_MANIFESTED_PRINTED)
                        ->setLabelFile($transaction->getLabelFile())
                        ->save();
            endforeach;
            $transaction->setHasError(\StreamMarket\RoyalMailShipping\Model\Transaction::HAS_ERROR_NO);
            $transaction->save();
        }
        return $transaction;
    }

    /**
     * Prepare print manifest request
     * @param \Magento\Framework\DataObject $request
     * @param \Magento\Framework\DataObject $transaction
     * @return array
     */
    protected function _formPrintManifestRequest(\Magento\Framework\DataObject $request,
            \Magento\Framework\DataObject $transaction)
    {
        $result = array(
            'integrationHeader' => $this->_integrationHeader($transaction->getTransactionId()),
        );
        $result['manifestBatchNumber'] = $request->getManifestBatchNumber();
        return $result;
    }

    public function printDocument(\Magento\Framework\DataObject $request)
    {
        /* pending shipment */
    }

    public function updateShipment(\Magento\Framework\DataObject $request)
    {
        /* pending shipment */
    }

    /**
     * Ship complete order with Royal Mail
     * @param \Magento\Sales\Model\Order $order
     * @param string $serviceOffering
     * @param string $containerType
     * @return \Magento\Sales\Model\Order\Shipment
     * @throws LocalizedException
     * @throws \Exception
     */
    public function shipOrderWith(\Magento\Sales\Model\Order $order,
            $serviceOffering, $containerType = null)
    {
        /* validate */
        if (!$order->getId()) {
            throw new LocalizedException(__("Invalid order."));
        }
//        if (!$order->canShip()) {
//            throw new LocalizedException(__('Can not do shipment for the order.'));
//        }
        /* @var $shipment \Magento\Sales\Model\Order\Shipment */
        $shipment = $this->registry->registry('current_shipment');
        $shipment->register();
        $request = new \Magento\Framework\DataObject();
        $request->setShippingMethod($serviceOffering);
        $request->setMethod($serviceOffering);
        $request->setOrderShipment($shipment);
        $containerTypes = $this->getContainerTypes($request);
        if (!count($containerTypes)) {
            throw new LocalizedException(__('Unable to find container type for service %1.',
                    $serviceOffering));
        } else {
            $containerTypes = array_keys($containerTypes);
        }
        if (!is_null($containerType)) {
            if (!in_array($containerType, $containerTypes)) {
                throw new LocalizedException(__('Container type is invalid for service %1.',
                        $serviceOffering));
            }
        } else {
            $containerType = $containerTypes[0]; //default container
        }
        /* prepare package params */
        $collection = $shipment->getAllItems();
        $package = array();
        $package[1] = array('params' => array('length' => '', 'height' => '', 'width' => ''));
        $weight = 0;
        foreach ($collection as $item):
            $_orderItem = $order->getItemById($item->getOrderItemId());
            if ($item->getIsVirtual() || ($_orderItem->isShipSeparately() && !($_orderItem->getParentItemId() || $_orderItem->getParentItem())) || (!$_orderItem->isShipSeparately() && ($_orderItem->getParentItemId() || $_orderItem->getParentItem()))):
                continue;
            endif;
            $package[1]['items'][$item->getId() ? $item->getId() : $item->getOrderItemId()] = array(
                'qty' => $item->getQty() * 1,
                'customs_value' => $item->getPrice(),
                'price' => $item->getPrice(),
                'name' => $item->getName(),
                'weight' => $this->roundUp($item->getWeight(), 3),
                'product_id' => $_orderItem->getProductId(),
                'order_item_id' => $item->getOrderItemId()
            );
            $weight += $this->roundUp($item->getWeight(), 3) * $item->getQty();
        endforeach;
        $package[1]['params']['weight'] = $weight;
        $package[1]['params']['weight_units'] = $this->getConfigData('unit_of_measure');
        $package[1]['params']['container'] = $containerType;
        $request->setPackageId(1);
        $request->setPackagingType($package[1]['params']['container']);
        $request->setPackageParams(new \Magento\Framework\DataObject($package[1]['params']));
        $request->setPackageItems($package[1]['items']);
        $shipment->setPackages($package);
        $address = $order->getShippingAddress();
        $this->setRecipientDetails($request, $address);
        $request->setBaseCurrencyCode($order->getBaseCurrencyCode());
        $request->setPackageWeight($weight);
        $request->setStoreId($order->getStoreId());
        $transaction = $this->_doShipmentRequest($request);
        if ($transaction->hasErrors()) {
            throw new \Exception($transaction->getMessage());
        }
        return $shipment;
    }

    /**
     * Set recipient details into request
     * @param \Magento\Framework\DataObject $request
     * @param \Magento\Sales\Model\Order\Address $address
     * @return void
     */
    protected function setRecipientDetails(\Magento\Framework\DataObject $request,
            \Magento\Sales\Model\Order\Address $address)
    {
        $request->setRecipientContactPersonName(trim($address->getFirstname() . ' ' . $address->getLastname()));
        $request->setRecipientContactPersonFirstName($address->getFirstname());
        $request->setRecipientContactPersonLastName($address->getLastname());
        $request->setRecipientContactCompanyName($address->getCompany());
        $request->setRecipientContactPhoneNumber($address->getTelephone());
        $request->setRecipientEmail($address->getEmail());
        $request->setRecipientAddressStreet(trim($address->getStreetLine(1) . ' ' . $address->getStreetLine(2)));
        $request->setRecipientAddressStreet1($address->getStreetLine(1));
        $request->setRecipientAddressStreet2($address->getStreetLine(2));
        $request->setRecipientAddressCity($address->getCity());
        $request->setRecipientAddressStateOrProvinceCode($address->getRegionCode() ?: $address->getRegion());
        $request->setRecipientAddressRegionCode($address->getRegionCode());
        $request->setRecipientAddressPostalCode($address->getPostcode());
        $request->setRecipientAddressCountryCode($address->getCountryId());
    }

    /**
     * Ship existing shipment with Royal Mail
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @param string $serviceOffering
     * @param string $containerType
     * @return \Magento\Sales\Model\Order\Shipment
     * @throws LocalizedException
     * @throws \Exception
     */
    public function shipShipmentWith(\Magento\Sales\Model\Order\Shipment $shipment,
            $serviceOffering, $containerType = null)
    {
        /* validate */
        if (!$shipment->getId()) {
            throw new LocalizedException(__("Invalid shipment."));
        }
        if ($shipment->getAllTracks()) {
            throw new LocalizedException(__("Shipment already has tracking.",
                    $shipment->getIncrementId()));
        }
        $request = new \Magento\Framework\DataObject();
        $request->setShippingMethod($serviceOffering);
        $request->setMethod($serviceOffering);
        $request->setOrderShipment($shipment);
        $containerTypes = $this->getContainerTypes($request);
        if (!count($containerTypes)) {
            throw new LocalizedException(__('Unable to find container type for service %1.',
                    $serviceOffering));
        } else {
            $containerTypes = array_keys($containerTypes);
        }
        if (!is_null($containerType)) {
            if (!in_array($containerType, $containerTypes)) {
                throw new LocalizedException(__('Container type is invalid for service %1.',
                        $serviceOffering));
            }
        } else {
            $containerType = $containerTypes[0]; //default container
        }
        /* prepare package params */
        $collection = $shipment->getAllItems();
        $package = array();
        $package[1] = array('params' => array('length' => '', 'height' => '', 'width' => ''));
        $weight = 0;
        $order = $shipment->getOrder();
        foreach ($collection as $item):
            $_orderItem = $order->getItemById($item->getOrderItemId());
            if ($item->getIsVirtual() || ($_orderItem->isShipSeparately() && !($_orderItem->getParentItemId() || $_orderItem->getParentItem())) || (!$_orderItem->isShipSeparately() && ($_orderItem->getParentItemId() || $_orderItem->getParentItem()))):
                continue;
            endif;
            $package[1]['items'][$item->getId() ? $item->getId() : $item->getOrderItemId()] = array(
                'qty' => $item->getQty() * 1,
                'customs_value' => $item->getPrice(),
                'price' => $item->getPrice(),
                'name' => $item->getName(),
                'weight' => $this->roundUp($item->getWeight(), 3),
                'product_id' => $_orderItem->getProductId(),
                'order_item_id' => $item->getOrderItemId()
            );
            $weight += $this->roundUp($item->getWeight(), 3) * $item->getQty();
        endforeach;
        $package[1]['params']['weight'] = $weight;
        $package[1]['params']['weight_units'] = $this->getConfigData('unit_of_measure');
        $package[1]['params']['container'] = $containerType;
        $request->setPackageId(1);
        $request->setPackagingType($package[1]['params']['container']);
        $request->setPackageWeight($package[1]['params']['weight']);
        $request->setPackageParams(new \Magento\Framework\DataObject($package[1]['params']));
        $request->setPackageItems($package[1]['items']);
        $shipment->setPackages($package);
        $shipmentStoreId = $order->getStoreId();
        $baseCurrencyCode = $order->getBaseCurrencyCode();
        $address = $order->getShippingAddress();
        $this->setRecipientDetails($request, $address);
        $request->setBaseCurrencyCode($baseCurrencyCode);
        $request->setStoreId($shipmentStoreId);
        $this->registry->register('current_shipment', $shipment);
        $transaction = $this->_doShipmentRequest($request);
        if ($transaction->hasErrors()) {
            throw new \Exception($transaction->getMessage());
        }
        return $shipment;
    }

}
