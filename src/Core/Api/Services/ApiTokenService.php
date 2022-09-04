<?php
namespace LoyalistaIntegration\Core\Api\Services;

use LoyalistaIntegration\Core\Api\BaseApiService;
use LoyalistaIntegration\Helpers\ConfigHelper;
use Plenty\Log\Contracts\LoggerContract;


class ApiTokenService extends BaseApiService
{
    /**
     * Request methods.
     */
    const REQUEST_METHOD_GET = 'GET';
    const REQUEST_METHOD_PUT = 'PUT';
    const REQUEST_METHOD_POST = 'POST';


    /**
     * Error code types.
     */
    const ERROR_CODE_EXCEPTION = 'exception';
    const ERROR_CODE_INVALID = 'Invalid Credentials';
    const ERROR_CODE_PD_RESPONSE = 'PD-Response';
    const ERROR_CODE_PLENTY_NOT_MATCHED = 'Plenty ID not matched';
    const ERROR_CODE_PLUGIN_DISABLED = 'Plugin is not activated';
    const ERROR_CODE_ORDER_DATA = 'OrderData';
    const ERROR_CODE_POST_FIELDS = 'PostFields';

    public function __construct(ConfigHelper $configHelper, LoggerContract $loggerContract)
    {
        parent::__construct($configHelper, $loggerContract);
    }

    public function verify($vendor_id = '' , $access_token='')
    {

        $httpHeader = [
            //'Authorization: ' . 'Bearer ' .$token,
            'Accept: application/json',
            'Loyalista-Integration-Agent : ' . 'PlentyMarket -v 0.0.0.1'  // Get from plugin config
        ];

        $url = 'http://loyalista.de/v1/validate_user_token';
        $postFields = array('vendor_id'=>'2' ,
                            'access_token' => '2|4ZiUunRpYSL6efouPIh9qOXbhKJnwpqjgGm3ftcG');

        // Get from setting
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
           // curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');


            curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);

            if (!empty($postFields)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            }

            $response = curl_exec($ch);
            curl_close($ch);
            return $response;
        } catch (\Exception $exception) {

            // Todo get logger before returning

            return  'oppps ' ;//$exception->getMessage();
        }

    }

    public function add_text2()
    {
        return 'Iam from token';

    }
}