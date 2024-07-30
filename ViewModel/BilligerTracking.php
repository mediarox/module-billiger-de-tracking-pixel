<?php
/**
 * Copyright 2024 (c) mediarox UG (haftungsbeschraenkt) (http://www.mediarox.de)
 * See LICENSE for license details.
 */

namespace Mediarox\BilligerDeTrackingPixel\ViewModel;

use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Model\Order;

class BilligerTracking implements ArgumentInterface
{
    private const BILLIGER_TRACKING_URL_SUCCESS = 'https://cmodul.solutenetwork.com/conversion';
    private const BILLIGER_TRACKING_URL_LANDING = 'https://cmodul.solutenetwork.com/landing';
    private ?Order $order;

    public function __construct(
        private readonly Session $checkoutSession
    ) {
        $this->order = $this->checkoutSession->getLastRealOrder();
    }

    public function getOrderTotalValue(): float|null
    {
        return $this->order?->getSubtotal();
    }

    public function getOrderId(): string
    {
        return $this->order?->getIncrementId();
    }

    public function getConversionUrl(): string
    {
        return self::BILLIGER_TRACKING_URL_SUCCESS;
    }

    public function getLandingUrl(): string
    {
        return self::BILLIGER_TRACKING_URL_LANDING;
    }
}
