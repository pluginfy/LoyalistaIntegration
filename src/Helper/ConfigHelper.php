<?php

namespace LoyalistaIntegration\Helper;

use Plenty\Plugin\ConfigRepository;

/**
 * Class ConfigHelper.
 */
class ConfigHelper
{
    /**
     * Product identifiers.
     */
    const PRODUCT_IDENTIFIER_ID = 'id"';
    const PRODUCT_IDENTIFIER_NUMBER = 'number';
    const PRODUCT_IDENTIFIER_VARIATION = 'variation';

    /**
     * Configuration enable/disable values.
     */
    const CONFIG_ENABLE_TRUE = 'true';
    const CONFIG_ENABLE_FALSE = 'false';
    const VALUE_1 = '1';
    const VALUE_0 = '0';
    const VALUE_YES = 1;
    const VALUE_NO = 0;

    /**
     * @var ConfigRepository
     */
    private $config;

    /**
     * ConfigHelper constructor.
     *
     * @param ConfigRepository $config
     */
    public function __construct(ConfigRepository $config)
    {
        $this->config = $config;
    }

    /**
     * Gets enabled from plugin configurations.
     *
     * @return bool
     */
    public function getEnabled()
    {
        return $this->config->get('LoyalistaIntegration.is_active');
    }


    /**
     * Gets Shop Id from plugin configurations.
     *
     * @return string|string[]|null
     */
    public function getShopId()
    {
        $shopId = $this->config->get('LoyalistaIntegration.shop_id');

        return preg_replace('/\s+/', '', $shopId);
    }


    /**
     * Gets Shop Secret from plugin configurations.
     *
     * @return string|string[]|null
     */
    public function getShopSecret()
    {
        $secret = $this->config->get('LoyalistaIntegration.shop_secret');

        return preg_replace('/\s+/', '', $secret);
    }

    /**
     * Gets Order Statuses array from plugin configurations.
     *
     * @return array
     */
    public function getOrderStatus()
    {
        $status = $this->config->get('LoyalistaIntegration.order_status');
        $statusArray = explode(',', $status);

        return $statusArray;
    }


    /**
     * Gets Show Widget from plugin configurations.
     *
     * @return bool
     */
    public function getShowWidgets()
    {
        return $this->config->get('LoyalistaIntegration.show_widgets');
    }

}
