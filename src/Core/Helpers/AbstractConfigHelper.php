<?php

namespace  LoyalistaIntegration\Core\Helpers;

/**
 * Config Abstract Helper Class
 */
abstract class AbstractConfigHelper
{
    const PLUGIN_NAME = 'LoyalistaIntegration';

    /**
     * @return mixed
     */
    abstract function getVendorID();

    /**
     * @return mixed
     */
    abstract function getVendorHash();

    /**
     * @return mixed
     */
    abstract  function getVendorSecret();



}