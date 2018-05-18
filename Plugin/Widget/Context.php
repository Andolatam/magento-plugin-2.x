<?php
namespace Improntus\Ando\Plugin\Widget;

use Magento\Backend\Block\Widget\Context AS Subject;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\Url;
use Magento\Framework\UrlInterface;

/**
 * Class Context
 *
 * @author Improntus <http://www.improntus.com>
 * @package Improntus\Ando\Plugin\Widget
 */
class Context
{
    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var UrlInterface
     */
    protected $_backendUrl;

    /**
     * Context constructor.
     * @param StoreManagerInterface $storeManagerInterface
     * @param Order $order
     * @param Url $frontendUrl
     * @param UrlInterface $urlInterface
     */
    public function __construct(
        StoreManagerInterface $storeManagerInterface,
        Order $order,
        Url $frontendUrl,
        UrlInterface $urlInterface
    )
    {
        $this->_storeManagerInterface  = $storeManagerInterface;
        $this->_order                  = $order;
        $this->_frontendUrl            = $frontendUrl;
        $this->_backendUrl             = $urlInterface;
    }

    /**
     * @param Subject $subject
     * @param $buttonList
     * @return mixed
     */
    public function afterGetButtonList(
        Subject $subject,
        $buttonList
    )
    {
        $orderId    = $subject->getRequest()->getParam('order_id');
        $order      = $this->_order->load($orderId);

        $baseUrl = $this->_backendUrl->getUrl('ando/retiro/solicitar',['order_id' =>$orderId,'rk'=>uniqid()]);

        if($subject->getRequest()->getFullActionName() == 'sales_order_view' && !$order->hasShipments() &&
            ($order->getShippingMethod() == 'andomoto_andomoto' || $order->getShippingMethod() == 'andobicicleta_andobicicleta'))
        {
            $buttonList->add(
                'solicitar_ando',
                [
                    'label'     => __('Solicitar retiro ANDO'),
                    'onclick' => "confirmSetLocation('¿Esta seguro que desea solicitar el retiro de sus productos?', '{$baseUrl}')",
                    'class'     => 'primary'
                ]
            );
        }

        return $buttonList;
    }
}