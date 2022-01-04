<?php

namespace LoyalistaIntegration\Helper;

use Plenty\Modules\Helper\Contracts\UrlBuilderRepositoryContract;
use Plenty\Modules\Item\Variation\Contracts\VariationRepositoryContract;
use Plenty\Modules\System\Contracts\WebstoreRepositoryContract;
use Plenty\Modules\Item\ItemImage\Contracts\ItemImageRepositoryContract;
use Plenty\Modules\Order\Shipping\Countries\Contracts\CountryRepositoryContract;

/**
 * Class EkomiHelper.
 */
class LoyalistaHelper
{
    /**
     * Plugin name in PD.
     */
    const PLUGIN_NAME = 'plentymarkets';
    /**
     * @var ConfigRepository
     */
    private $configHelper;
    /**
     * @var WebstoreRepositoryContract
     */
    private $webStoreRepository;
    /**
     * @var ItemImageRepositoryContract
     */
    private $imagesRepository;
    /**
     * @var CountryRepositoryContract
     */
    private $countryRepository;

    /**
     * @var VariationRepositoryContract
     */
    private $itemVariationRepository;

    /**
     * @var UrlBuilderRepositoryContract
     */
    private $urlBuilderRepositoryContract;

    /**
     * Initializes object variables.
     *
     * @param WebstoreRepositoryContract   $webStoreRepository
     * @param ConfigHelper                 $configHelper
     * @param ItemImageRepositoryContract  $imagesRepository
     * @param CountryRepositoryContract    $countryRepository
     * @param VariationRepositoryContract  $itemVariationRepository
     * @param UrlBuilderRepositoryContract $urlBuilderRepositoryContract
     */
    public function __construct(
        WebstoreRepositoryContract $webStoreRepository,
        ConfigHelper $configHelper,
        ItemImageRepositoryContract $imagesRepository,
        CountryRepositoryContract $countryRepository,
        VariationRepositoryContract $itemVariationRepository,
        UrlBuilderRepositoryContract $urlBuilderRepositoryContract
    ) {
        $this->configHelper = $configHelper;
        $this->webStoreRepository = $webStoreRepository;
        $this->imagesRepository = $imagesRepository;
        $this->countryRepository = $countryRepository;
        $this->itemVariationRepository = $itemVariationRepository;
        $this->urlBuilderRepositoryContract = $urlBuilderRepositoryContract;
    }

    /**
     * Gets the order data and prepare post variables.
     *
     * @param array $order order object as array
     *
     * @return array the comma separated parameters
     */
    public function preparePostVars($order)
    {
        $plentyId = $order['plentyId'];
        $fields = array(
            'shop_id' => $this->configHelper->getShopId(),
            'interface_password' => $this->configHelper->getShopSecret(),
        );
        $order['senderName'] = $this->getWebStoreName($plentyId);
        $order['senderEmail'] = '';
        $totalAddress = count($order['addresses']);
        for ($index = 0; $index < $totalAddress; $index++) {
            $countryInfo = $this->countryRepository->getCountryById($order['addresses'][$index]['countryId']);
            $order['addresses'][$index]['countryName'] = $countryInfo->name;
            $order['addresses'][$index]['isoCode2'] = $countryInfo->isoCode2;
            $order['addresses'][$index]['isoCode3'] = $countryInfo->isoCode3;
        }

        $order['orderItems'] = $this->getProductsData($order['orderItems'], $plentyId);
        $fields['order_data'] = $order;

        return $fields;
    }

    /**
     * Gets web store.
     *
     * @param int $plentyId
     *
     * @return string
     */
    protected function getWebStoreName($plentyId)
    {
        $temp1 = $this->webStoreRepository->findByPlentyId($plentyId)->toArray();
        if (isset($temp1['name'])) {
            return $temp1['name'];
        }

        return '';
    }

    /**
     * Gets the products data.
     *
     * @param array $orderItems
     * @param int   $plentyId
     *
     * @return array
     */
    protected function getProductsData($orderItems, $plentyId)
    {
        $products = array();
        $totalItems = count($orderItems);
        for ($index = 0; $index < $totalItems; $index++) {
            $item = $orderItems[$index];
            if (isset($item['itemVariationId']) && $item['itemVariationId'] > ConfigHelper::VALUE_NO) {
                $itemVariation = $this->itemVariationRepository->findById($item['itemVariationId']);
                if ($itemVariation) {
                    $itemId = $itemVariation->itemId;
                    $item['itemId'] = $itemId;
                    $item['itemVariationNumber'] = $itemVariation->number;
                    $item['image_url'] = utf8_decode($this->getItemImageUrl($itemId, $item['itemVariationId']));
                    $item['canonical_url'] = utf8_decode($this->getItemUrl($plentyId, $itemId));
                    $products[] = $item;
                }
            }
        }

        return $products;
    }

    /**
     * Gets Item image url.
     *
     * @param int $plentyId
     * @param int $itemId
     *
     * @return array
     */
    public function getItemUrl($plentyId, $itemId) {
        $itemUrl = $this->urlBuilderRepositoryContract->getItemUrl($itemId,$plentyId);
        if(empty($itemUrl)){
            $itemUrl = $this->getStoreDomain($plentyId);
            $itemUrl = $itemUrl . '/a-' . $itemId;
        }

        return $itemUrl;
    }

    /**
     * Gets item image url.
     *
     * @param int    $itemId
     * @param string $variationId
     *
     * @return string
     */
    public function getItemImageUrl($itemId, $variationId) {
        $variationImage = $this->imagesRepository->findByVariationId($variationId);
        $itemImage = $this->imagesRepository->findByItemId($itemId);
        if (isset($variationImage[0])) {
            return $variationImage[0]['url'];
        } elseif (isset($itemImage[0])) {
            return $itemImage[0]['url'];
        }
        return '';
    }

    /**
     * Gets Store domain Url.
     *
     * @param int $plentyId
     *
     * @return string
     */
    protected function getStoreDomain($plentyId)
    {
        $temp1 = $this->webStoreRepository->findByPlentyId($plentyId)->toArray();
        if (isset($temp1['configuration']['domain'])) {
            return $temp1['configuration']['domain'];
        }

        return '';
    }

    /**
     * Prepares filter to be applied in fetching orders.
     *
     * @param int $turnaroundTime
     *
     * @return array
     */
    public function prepareFilter($turnaroundTime)
    {
        $updatedAtFrom = date('Y-m-d\TH:i:s+00:00', strtotime("-{$turnaroundTime} day"));
        $updatedAtTo = date('Y-m-d\TH:i:s+00:00');

        return ['updatedAtFrom' => $updatedAtFrom, 'updatedAtTo' => $updatedAtTo];
    }
}
