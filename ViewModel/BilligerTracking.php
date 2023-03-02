<?php

/**
 * @package   Mediarox_BilligerDeTrackingPixel
 * @copyright Copyright 2020 (c) mediarox UG (haftungsbeschraenkt) (http://www.mediarox.de)
 * @author    Marcus Bernt <mbernt@mediarox.de>
 */

namespace Mediarox\BilligerDeTrackingPixel\ViewModel;

use Magento\Checkout\Model\Session;
use Magento\Directory\Model\Currency;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Url\QueryParamsResolverInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mediarox\BilligerDeTrackingPixel\Model\Source\Config\MethodOptions;
use Zend_Currency;

/**
 * Class BilligerTracking
 */
class BilligerTracking implements ArgumentInterface
{
    private const BILLIGER_TRACKING_URL = 'https://billiger.de/sale?';
    private const SHOP_ID_SYSTEM_CONFIG_PATH = 'mediarox_billiger_tracking/general/shop_id';
    private const METHOD_SYSTEM_CONFIG_PATH = 'mediarox_billiger_tracking/general/method';
    protected StoreManagerInterface $storeManager;
    protected QueryParamsResolverInterface $paramsResolver;
    private Session $checkoutSession;
    private PriceCurrencyInterface $currency;
    private ScopeConfigInterface $scopeConfig;
    private Order $order;

    public function __construct(
        Session $checkoutSession,
        PriceCurrencyInterface $currency,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        QueryParamsResolverInterface $paramsResolver
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->currency = $currency;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->paramsResolver = $paramsResolver;
    }

    /**
     * @return string
     */
    public function getBilligerTrackingUrl(): string
    {
        $this->order = $this->checkoutSession->getLastRealOrder();
        $trackingUrl = '';
        if ($this->order->getId()) {
            $method = $this->scopeConfig->getValue(
                self::METHOD_SYSTEM_CONFIG_PATH,
                ScopeInterface::SCOPE_STORE,
                $this->storeManager->getStore()
            );
            $data = [
                'shop_id' => $this->scopeConfig->getValue(
                    self::SHOP_ID_SYSTEM_CONFIG_PATH,
                    ScopeInterface::SCOPE_STORE,
                    $this->storeManager->getStore()
                ),
                'oid'     => $this->order->getIncrementId(),
            ];

            switch ($method) {
                case MethodOptions::METHOD_INCLUDE_ORDER_ITEMS:
                    $data = array_merge($data, $this->getOrderItems());
                    break;
                case MethodOptions::METHOD_EXCLUDE_ORDER_ITEMS:
                    $data = array_merge($data, $this->getOrderTotalValue());
            }
            $query = str_replace('%2C', ',', http_build_query($data, '', '&'));
            $trackingUrl = self::BILLIGER_TRACKING_URL . $query;
        }
        return $trackingUrl;
    }

    /**
     * @return array
     */
    private function getOrderItems(): array
    {
        $orderItems = $this->order->getAllItems();
        $data = [];
        $iterator = 1;
        foreach ($orderItems as $orderItem) {
            $data['aid_' . $iterator] = $orderItem->getSku();
            $data['name_' . $iterator] = $orderItem->getName();
            $data['cnt_' . $iterator] = (int)$orderItem->getQtyOrdered();
            $data['val_' . $iterator] = $orderItem->getPriceInclTax();
            $iterator++;
        }
        return $data;
    }

    /**
     * @return array
     */
    private function getOrderTotalValue(): array
    {
        return ['val' => $this->order->getGrandTotal()];
    }
}
