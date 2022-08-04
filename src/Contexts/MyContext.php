<?php
namespace LoyalistaIntegration\Contexts;
use Ceres\Contexts\GlobalContext;
use IO\Helper\ContextInterface;

class MyContext extends GlobalContext implements ContextInterface
{
    public $myVariable;

    public function init($params)
    {
        parent::init($params);



        $this->myVariable = "his is how you extend context classes.";
    }

    public function myText()
    {
        return $this->myVariable;

    }
}