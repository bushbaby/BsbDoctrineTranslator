<?php

namespace BsbDoctrineTranslator\Factory;

use BsbDoctrineTranslator\Manager\DoctrineTranslatorService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ManagerFactory implements FactoryInterface
{
    /**
     * {@inheritdocs}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config     = $serviceLocator->get('config');
        $em         = $serviceLocator->get('Doctrine\ORM\EntityManager');
        $loader     = $serviceLocator->get('MvcTranslator')->getPluginManager()->get('bsbdoctrinetranslator');

        $config     = isset($config['bsbdoctrinetranslator']['manager']) ? $config['bsbdoctrinetranslator']['manager'] : array();

        $service = new DoctrineTranslatorService($loader, $em, $config);

        return $service;
    }
}