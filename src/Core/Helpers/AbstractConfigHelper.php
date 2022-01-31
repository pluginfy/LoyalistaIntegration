<?php

namespace  LoyalistaIntegration\Core\Helpers;

abstract class AbstractConfigHelper
{
    const PLUGIN_NAME = 'LoyalistaIntegration';

    abstract function getVendorID();

    abstract function getVendorHash();

    abstract  function getVendorSecret();



}