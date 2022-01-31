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


}
