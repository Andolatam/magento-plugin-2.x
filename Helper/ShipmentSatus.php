<?php

namespace Improntus\Ando\Helper;

/**
 * Class ShipmentSatus
 *
 * @author Improntus <http://www.improntus.com>
 * @package Improntus\Ando\Helper
 */
class ShipmentSatus
{
    /**
     * @var array
     */
    public static $shipmentMessage = [
        '1' => 'Cancelled.',
        '2' => 'Rejected.',
        '3' => 'On Its way.',
        '4' => 'Waiting for Payment.',
        '5' => 'Payment Successful',
        '6' => 'Payment Rejected',
        '7' => 'Waiting for Rider',
        '8' => 'Rider not found',
        '9' => 'Traveling to Start Address',
        '10' =>'The rider is at the Start Address',
        '11' =>'Traveling to End Address',
        '12' => 'The rider is at the End Address',
        '13' => 'Package Delivered succesfully to User',
        '14' => 'Package Delivered to Rider',
        '15' => 'Delivered Succesfully',
        '16' => 'Paid',
        '17' => 'Cancelled',
        '18' =>	'Voided',
        '19' =>	'Closed',
        '20' =>	'Shipment Cancelled by the Rider',
        '21' =>	'Shipment Cancelled by the Receiver',
        '22' => 'Shipment Cancelled by the Sender',
        '23' => 'Waiting for Confirmation',
        '24' => 'Waiting for a Second Confirmation',
        '25' => 'Started'
    ];

    /**
     * @param $code
     * @return mixed|null
     */
    public static function getShipmentMessage($code)
    {
        return isset(self::$shipmentMessage[$code]) ? __(self::$shipmentMessage[$code]) : null;
    }
}
