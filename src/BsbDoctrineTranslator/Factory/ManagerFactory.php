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
        $loader     = $serviceLocator->get('MvcTranslator')->getPluginManager()->get('BsbDoctrineTranslationLoader');

        $config     = isset($config['bsb_doctrine_translator']['manager']) ? $config['bsb_doctrine_translator']['manager'] : array();

        // copies text_domains from translator remote_translation
        if (isset($config['translator']['remote_translation']) && is_array($config['translator']['remote_translation'])) {
            foreach($config['translator']['remote_translation'] as $remote_translation) {
                if ($remote_translation['type'] == 'BsbDoctrineTranslationLoader') {
                    $config['bsb_doctrine_translator']['manager']['text_domains'][] = $remote_translation['text_domain'];
                }
            }
        }

        $service = new DoctrineTranslatorService($loader, $em, $config);

        return $service;
    }
}