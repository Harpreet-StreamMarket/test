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

namespace StreamMarket\RoyalMailShipping\Block\Adminhtml\Rate\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class GenericButton
 */
class GenericButton
{

    /**
     * @var \StreamMarket\RoyalMailShipping\Api\RateRepositoryInterface
     */
    private $rateRepository;

    /**
     * @var Context
     */
    protected $context;

    /**
     *
     * @param Context $context
     * @param \StreamMarket\RoyalMailShipping\Api\RateRepositoryInterface $rateRepository
     */
    public function __construct(
    Context $context,
            \StreamMarket\RoyalMailShipping\Api\RateRepositoryInterface $rateRepository
    )
    {
        $this->context = $context;
        $this->rateRepository = $rateRepository;
    }

    /**
     * Return Rate ID
     *
     * @return int|null
     */
    public function getRateId()
    {
        try {
            return $this->rateRepository->getById($this->context->getRequest()->getParam('rate_id'))->getId();
        } catch (NoSuchEntityException $e) {

        }
        return null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }

}
