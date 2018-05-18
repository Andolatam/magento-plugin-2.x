<?php

namespace Improntus\Ando\Model;

use Improntus\Ando\Helper\Data as HelperAndo;
use Magento\Sales\Model\Convert\Order as ConvertOrder;

/**
 * Class Ando
 *
 * @author Improntus <http://www.improntus.com>
 * @package Improntus\Ando\Model
 */
class Ando
{
    protected $_helper;

    /**
     * @var \Magento\Shipping\Model\ShipmentNotifier
     */
    protected $_shipmentNotifier;

    /**
     * @var
     */
    protected $_webService;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var \Magento\Sales\Model\Order\ShipmentFactory
     */
    protected $_shipmentFactory;

    /**
     * Order converter.
     *
     * @var \Magento\Sales\Model\Convert\Order
     */
    protected $_converter;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\TrackFactory
     */
    protected $_trackFactory;

    /**
     * Ando constructor.
     * @param Webservice $webservice
     * @param ConvertOrder $convertOrder
     * @param \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory
     * @param HelperAndo $helperAndo
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Shipping\Model\ShipmentNotifier $shipmentNotifier
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Shipping\Model\CarrierFactory $carrierFactory
     * @param \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory
     */
    public function __construct(
        Webservice $webservice,
        ConvertOrder $convertOrder,
        \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory,
        HelperAndo $helperAndo,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Shipping\Model\ShipmentNotifier $shipmentNotifier,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Shipping\Model\CarrierFactory $carrierFactory,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory
    )
    {
        $this->_orderRepository = $orderRepository;
        $this->_shipmentFactory = $shipmentFactory;
        $this->_shipmentNotifier = $shipmentNotifier;
        $this->_carrierFactory = $carrierFactory;
        $this->_trackFactory = $trackFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_converter = $convertOrder;
        $this->_helper = $helperAndo;
        $this->_webService = $webservice;
    }

    /**
     * @param $orderId
     * @return bool|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function doShipment($orderId)
    {
        $order = $this->_orderRepository->get($orderId);

        if (!$order->canShip())
        {
            return false;
        }

        $shipment = $this->_shipmentFactory->create($order);

        try
        {
            $valorTotal = $pesoTotal = 0;
            $itemsArray = [];

            foreach ($order->getAllItems() AS $orderItem)
            {
                if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                    continue;
                }

                $qtyShipped = $orderItem->getQtyToShip();
                $shipmentItem = $this->_converter->itemToShipmentItem($orderItem)->setQty($qtyShipped);

                $valorTotal += $qtyShipped * $orderItem->getPrice();
                $pesoTotal  += $qtyShipped * $orderItem->getWeight();

                $itemsArray[$orderItem->getId()] =
                [
                    'qty'           => $qtyShipped,
                    'customs_value' => $orderItem->getPrice(),
                    'price'         => $orderItem->getPrice(),
                    'name'          => $orderItem->getName(),
                    'weight'        => $orderItem->getWeight(),
                    'product_id'    => $orderItem->getProductId(),
                    'order_item_id' => $orderItem->getId()
                ];

                $shipment->addItem($shipmentItem);
            }

            $shipment->setPackages(
            [
                1=> [
                    'items' => $itemsArray,
                    'params'=> [
                        'weight' => $pesoTotal,
                        'container'=> 1,
                        'customs_value'=> $valorTotal
                    ]
                ]
            ]);

            $shipment->register();
            $shipment->getOrder()->setIsInProcess(true);

            $shipmentId = $this->_webService->newShipment($order->getAndoQuoteId());

            if(!$shipmentId)
            {
                return false;
            }

            $trackingNumber = $shipmentId;

            $mensajeEstado = "La solicitud de retiro ando fue creada correctamente. Shipment ID: $shipmentId"; //Seguimiento envío <a href='{$trackingUrl}' target='_blank'>{$trackingNumber}</a>";
            $history = $order->addStatusHistoryComment($mensajeEstado);
            $history->setIsVisibleOnFront(true);
            $history->setIsCustomerNotified(true);
            $history->save();

            $carrier = $this->_carrierFactory->create($order->getShippingMethod(true)->getCarrierCode());
            $carrierCode = $carrier->getCarrierCode();
            $carrierTitle = $this->_scopeConfig->getValue(
                'carriers/' . $carrierCode . '/title',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $shipment->getStoreId()
            );

            $this->addTrackingNumbersToShipment($shipment, [$trackingNumber], $carrierCode, $carrierTitle);

            $shipment->save();
            $shipment->getOrder()->save();
            $this->_shipmentNotifier->notify($shipment);

            return $mensajeEstado;

        } catch (\Exception $e)
        {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage()));
        }
    }

    /**
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @param $trackingNumbers
     * @param $carrierCode
     * @param $carrierTitle
     * @return \Magento\Sales\Model\Order\Shipment
     */
    private function addTrackingNumbersToShipment(\Magento\Sales\Model\Order\Shipment $shipment,$trackingNumbers,$carrierCode,$carrierTitle)
    {
        foreach ($trackingNumbers as $number)
        {
            if (is_array($number))
            {
                $this->addTrackingNumbersToShipment($shipment, $number, $carrierCode, $carrierTitle);
            }
            else
            {
                $shipment->addTrack(
                    $this->_trackFactory->create()
                        ->setNumber($number)
                        ->setCarrierCode($carrierCode)
                        ->setTitle($carrierTitle)
                );
            }
        }

        return $shipment;
    }
}