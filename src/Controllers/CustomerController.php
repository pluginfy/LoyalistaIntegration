<?php
namespace LoyalistaIntegration\Controllers;

use LoyalistaIntegration\Helpers\LoyalistaHelper;
use Plenty\Plugin\Controller;
use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Frontend\Services\AccountService;

use LoyalistaIntegration\Services\API\LoyalistaApiService;
use LoyalistaIntegration\Helpers\ConfigHelper;

use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Templates\Twig;

/**
 * Customer Controller Class
 */
class CustomerController extends Controller
{
    /**
     * @return false|string|void
     */
    public function unRegisterCustomer()
    {
        $account_service = pluginApp(AccountService::class);
        $plenty_customer_id  = $account_service->getAccountContactId();

        $contactRepo = pluginApp(ContactRepositoryContract::class);
        $contact = $contactRepo->findContactById($plenty_customer_id);

        if ($contact->id > 0){

            $configHelper = pluginApp(configHelper::class);
            $shopReference = $configHelper->getShopID();

            // data to send api
            $customer = array(
                'shop_reference' => $shopReference,
                'reference_id'=> $contact->id,
            );

            $api = pluginApp(LoyalistaApiService::class);
            $response = $api->unRegisterCustomer($customer);

            if ($response['success'] === true){
                $return = ['status' => 'OK'];
                return json_encode($return);
            }else{
                $return = ['status' => 'Error', 'message' => 'Failed' , 'response' => $response ];
                return json_encode($return);
            }
        }
    }

    /**
     * @return false|string|void
     */
    public function registerCustomer()
    {
        $account_service = pluginApp(AccountService::class);
        $plenty_customer_id  = $account_service->getAccountContactId();

        $contactRepo = pluginApp(ContactRepositoryContract::class);
        $contact = $contactRepo->findContactById($plenty_customer_id);

        if ($contact->id > 0){

            $configHelper = pluginApp(configHelper::class);
            $shopReference = $configHelper->getShopID();

            $customer = array(
                'shop_reference' => $shopReference,
                'reference_id'=> $contact->id,
                'name' => $contact->fullName,
                'email' => $contact->email
            );

            $api = pluginApp(LoyalistaApiService::class);
            $response = $api->registerCustomer($customer);

            if(!$response['success']) {
                if (is_array($response['message'])) {
                    $msgBag = '';
                    foreach ($response['message'] as $error) {
                        $msgBag .= "<span>{$error[0]}</span>";
                    }
                    $response['message'] = $msgBag;
                }
            }

            return json_encode($response);
        }
    }

    /**
     * @param Request $request
     * @return false|string|void
     */
    public function mergeCustomer(Request $request)
    {
        $post_data = $request->all();

        $account_service = pluginApp(AccountService::class);
        $plenty_customer_id  = $account_service->getAccountContactId();

        $contactRepo = pluginApp(ContactRepositoryContract::class);
        $contact = $contactRepo->findContactById($plenty_customer_id);

        if ($contact->id > 0){

            $configHelper = pluginApp(configHelper::class);
            $shopReference = $configHelper->getShopID();

            $customer = array(
                'shop_reference' => $shopReference,
                'reference_id'=> $contact->id,
                'customer_email_address' => $post_data['customer_email_address'],
            );

            $api = pluginApp(LoyalistaApiService::class);

            $response = $api->mergeCustomr($customer);

            if ($response['success'] === true){
                $return = ['status' => 'OK'];
                return json_encode($return);
            }else{
                $return = ['status' => 'ERROR', 'message' => $response['message']];
                return json_encode($return);
            }
        }
    }

    /**
     * @param Request $request
     * @param Twig $twig
     * @return mixed
     */
    public function loayslistaAccountPage(Request $request, Twig $twig)
    {
        $helper = pluginApp(LoyalistaHelper::class);
        $data = $helper->hydrate_my_account_data();

        return $twig->render('LoyalistaIntegration::content.LoyalistaAccountPage', $data);
    }
}