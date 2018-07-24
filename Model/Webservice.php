<?php

namespace Improntus\Ando\Model;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Directory\Model\Region;
use Improntus\Ando\Helper\Data as HelperAndo;

/**
 * Class Webservice
 *
 * @author Improntus <http://www.improntus.com>
 * @package Improntus\Ando\Model
 */
class Webservice
{
    /**
     * @var string
     */
    protected $_user;

    /**
     * @var string
     */
    protected $_pass;

    /**
     * @var string
     */
    protected $_apiUrl;

    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * @var HelperAndo
     */
    protected $_helper;

    /**
     * @var array
     */
    protected $_token;

    /**
     * Webservice constructor.
     * @param CheckoutSession $checkoutSession
     * @param Region $region
     * @param HelperAndo $helperAndo
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        Region $region,
        HelperAndo $helperAndo
    )
    {
        $this->_checkoutSession = $checkoutSession;
        $this->_region = $region;
        $this->_helper = $helperAndo;

        $this->_user = $helperAndo->getWebserviceUser();
        $this->_pass = $helperAndo->getWebservicePass();
        $this->_apiUrl = $helperAndo->getApiUrl();

        $this->_token = $this->getToken();
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        $curl = curl_init($this->_apiUrl.'login/');

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, "{\n\t\"email\": \"{$this->_user}\",\n\t\"password\": \"{$this->_pass}\"\n}");
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json"
        ));

        $response = curl_exec($curl);

        if(curl_error($curl))
        {
            $error = 'Se produjo un error al generar el token: '. curl_error($curl);
            \Improntus\Ando\Helper\Data::log($error ,'error_ando_'.date('m_Y').'.log');

            return null;
        }

        try{
            $token = \Zend_Json::decode($response);

            \Improntus\Ando\Helper\Data::log(print_r($response,true) ,'debug_ando.log');

            return $token;
        }
        catch (\Exception $e)
        {
            $error = 'Se produjo un error al generar el token: '. $e->getMessage();
            \Improntus\Ando\Helper\Data::log($error ,'error_ando_'.date('m_Y').'.log');

            return null;
        }
    }

    /**
     * @param $shippingParams
     * @return bool|mixed
     */
    public function getShippingQuote($shippingParams)
    {
        $curl = curl_init();

        if(!isset($this->_token['token']))
        {
            $error = 'Se produjo un error al solicitar cotización. No se generó un token de acceso';
            \Improntus\Ando\Helper\Data::log($error ,'error_ando_'.date('m_Y').'.log');

            return false;
        }

        curl_setopt_array($curl,
        [
            CURLOPT_URL => "{$this->_apiUrl}shipment/quote",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($shippingParams),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->_token['token']}",
                "Content-Type: application/json"
            ],
        ]);

        $response = curl_exec($curl);

        if(curl_error($curl))
        {
            $error = 'Se produjo un error al solicitar cotización: '. curl_error($curl);
            \Improntus\Ando\Helper\Data::log($error ,'error_ando_'.date('m_Y').'.log');

            return false;
        }

        try{
            $cotizacion = \Zend_Json::decode($response);

            if(isset($cotizacion['price'][0]['estimatedPrice']))
            {
                $this->_helper->setAndoQuoteId($cotizacion['quoteID']);

                return $cotizacion['price'][0]['estimatedPrice'];
            }
            if(isset($cotizacion['error']))
            {
                $error = 'Se produjo un error al solicitar cotización: '. $cotizacion['error_description'] . ' ShippingParams: ' .print_r($shippingParams,true);
                \Improntus\Ando\Helper\Data::log($error ,'error_ando_'.date('m_Y').'.log');

                return false;
            }

            if(isset($cotizacion['status']))
            {
                $errorDesc = \Improntus\Ando\Helper\ErrorCode::getError($cotizacion['status']);

                $error = 'Se produjo un error al solicitar cotización: '. $errorDesc . ' ShippingParams: ' .print_r($shippingParams,true);
                \Improntus\Ando\Helper\Data::log($error ,'error_ando_'.date('m_Y').'.log');

                return false;
            }
        }
        catch (\Exception $e)
        {
            $error = 'Se produjo un error al solicitar cotización: '. $e->getMessage() . ' Response: '. print_r($response,true);
            \Improntus\Ando\Helper\Data::log($error ,'error_ando_'.date('m_Y').'.log');

            return null;
        }
    }

    /**
     * @param $quoteId
     * @return bool|null
     */
    public function newShipment($quoteId)
    {
        $curl = curl_init();

        $postParams = [
            'quoteID' => $quoteId,
            'promocode' => '',
            'paymentMethod' => 'checking_account'
        ];

        curl_setopt_array($curl,
        [
            CURLOPT_URL => "{$this->_apiUrl}shipment/new",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($postParams),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->_token['token']}",
                "Content-Type: application/json"
            ],
        ]);

        $response = curl_exec($curl);

        if(curl_error($curl))
        {
            $error = 'Se produjo un error al generar el envio: '. curl_error($curl);
            \Improntus\Ando\Helper\Data::log($error ,'error_ando_'.date('m_Y').'.log');

            return false;
        }

        try{
            $shipment = \Zend_Json::decode($response);

            \Improntus\Ando\Helper\Data::log('Shipment debug: ' . print_r($response,true) ,'debug_ando.log');

            curl_close($curl);

            return isset($shipment['trackingID']) ? $shipment['trackingID'] : null;
        }
        catch (\Exception $e)
        {
            $error = 'Se produjo un error al generar el envio: '. $e->getMessage();
            \Improntus\Ando\Helper\Data::log($error ,'error_ando_'.date('m_Y').'.log');

            curl_close($curl);

            return false;
        }
    }

    /**
     * @param $shipmentId
     * @return bool|null
     */
    public function trackShipment($shipmentId)
    {
        $curl = curl_init();

        curl_setopt_array($curl,
        [
            CURLOPT_URL => "{$this->_apiUrl}shipment/track?trackingID=$shipmentId",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->_token['token']}",
                "Content-Type: application/json"
            ],
        ]);

       $response = curl_exec($curl);
        //{"status":{"statusID":8,"message":"RIDER NOT FOUND"}}
        
        if(curl_error($curl))
        {
            $error = 'Se produjo un error al consultar un envío: '. curl_error($curl);
            \Improntus\Ando\Helper\Data::log($error ,'error_ando_'.date('m_Y').'.log');

            return false;
        }

        try{
            $tracking = \Zend_Json::decode($response);

            curl_close($curl);

            return isset($tracking['status']['statusID']) ? \Improntus\Ando\Helper\ShipmentSatus::getShipmentMessage($tracking['status']['statusID']) : null;
        }
        catch (\Exception $e)
        {
            $error = 'Se produjo un error al generar el envio: '. $e->getMessage();
            \Improntus\Ando\Helper\Data::log($error ,'error_ando_'.date('m_Y').'.log');

            curl_close($curl);

            return false;
        }
    }
}