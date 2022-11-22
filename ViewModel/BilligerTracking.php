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
use Magento\Framework\UrlInterface;
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
    private const BILLIGER_TRACKING_URL = 'https://billiger.de/sale';
    private const SHOP_ID_SYSTEM_CONFIG_PATH = 'mediarox_billiger_tracking/general/shop_id';
    private const METHOD_SYSTEM_CONFIG_PATH = 'mediarox_billiger_tracking/general/method';
    protected StoreManagerInterface $storeManager;
    private Session $checkoutSession;
    private Currency $currency;
    private ScopeConfigInterface $scopeConfig;
    private UrlInterface $url;
    private Order $order;

    /**
     * BilligerTracking constructor.
     *
     * @param  Session              $checkoutSession
     * @param  Currency             $currency
     * @param  ScopeConfigInterface $scopeConfig
     * @param  UrlInterface         $url
     */
    public function __construct(
        Session $checkoutSession,
        Currency $currency,
        ScopeConfigInterface $scopeConfig,
        UrlInterface $url,
        StoreManagerInterface $storeManager
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->currency = $currency;
        $this->scopeConfig = $scopeConfig;
        $this->url = $url;
        $this->storeManager = $storeManager;
    }

    /**
     * @return string
     */
    public function getBilligerTrackingUrl(): string
    {
        $this->order = $this->checkoutSession->getLastRealOrder();
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
        return $this->url->getDirectUrl(self::BILLIGER_TRACKING_URL, ['_query' => $data]);
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
            $data['val_' . $iterator] = $this->currency->formatPrecision(
                $orderItem->getPrice(),
                2,
                ['display' => Zend_Currency::NO_SYMBOL],
                false
            );
            $iterator++;
        }
        return $data;
    }

    /**
     * @return array
     */
    private function getOrderTotalValue(): array
    {
        return ['val' => $this->order->getBaseSubtotal()];
    }
}
