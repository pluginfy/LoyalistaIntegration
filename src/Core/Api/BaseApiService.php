<?php

namespace LoyalistaIntegration\Core\Api;

use LoyalistaIntegration\Helpers\ConfigHelper;
use Plenty\Log\Contracts\LoggerContract;

class BaseApiService
{

    const REQUEST_METHOD_GET = 'GET';
    const REQUEST_METHOD_PUT = 'PUT';
    const REQUEST_METHOD_POST = 'POST';

    /**
     * @var LoggerContract
     */
    protected $loggerContract;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;


    public function __construct(ConfigHelper $configHelper, LoggerContract $loggerContract)
    {
        $this->configHelper = $configHelper;
        $this->loggerContract = $loggerContract;
    }

    public function doCurl($requestUrl, $requestType, $httpHeader = array(), $postFields = '')
    {

        $vendorSecret = $this->configHelper->getVendorSecret();


        $headers = array(
            "Content-Type: application/json",
            "Accept: application/json",
            'Authorization: ' . 'Bearer ' .$vendorSecret,
        );

        if (!empty($httpHeader)) {
            $headers = array_merge($headers, $httpHeader );
        }

        try {
            $curl = curl_init($requestUrl);
            curl_setopt($curl, CURLOPT_URL, $requestUrl);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $requestType);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            if (!empty($postFields)) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postFields));
            }

            //for debug only!
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($curl);
            curl_close($curl);
            return json_decode($response, true);

        } catch (\Exception $exception) {
            //$this->getLogger(__FUNCTION__)->error(self::ERROR_CODE_EXCEPTION, $exception->getMessage());
            return $exception->getMessage();
        }

    }

}