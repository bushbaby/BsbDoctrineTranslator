<?php

namespace BsbDoctrineTranslator\Controller\Plugin;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

class TranslatorPluralFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getController()->getServiceLocator();

        return new TranslatePluralProxy($serviceLocator->get('MvcTranslator'));
    }
}
