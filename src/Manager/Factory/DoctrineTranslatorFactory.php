<?php

namespace BsbDoctrineTranslator\Factory;

use BsbDoctrineTranslator\Manager\DoctrineTranslator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DoctrineTranslatorFactory implements FactoryInterface
{
    /**
     * {@inheritdocs}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config         = $serviceLocator->get('config');
        $entity_manager = $config['bsb_doctrine_translation_loader']['entity_manager'];
        $entityManager  = $serviceLocator->get(sprintf('doctrine.entity_manager.%s', $entity_manager));

        $loader = $serviceLocator->get('MvcTranslator')->getPluginManager()->get('BsbDoctrineTranslationLoader');

        $config = isset($config['bsb_doctrine_translator']['manager']) ? $config['bsb_doctrine_translator']['manager'] : array();

        // copies text_domains from translator remote_translation
        if (isset($config['translator']['remote_translation']) && is_array($config['translator']['remote_translation'])) {
            foreach ($config['translator']['remote_translation'] as $remote_translation) {
                if ($remote_translation['type'] == 'BsbDoctrineTranslationLoader') {
                    $config['bsb_doctrine_translator']['manager']['text_domains'][] = $remote_translation['text_domain'];
                }
            }
        }

        return new DoctrineTranslator($loader, $entityManager, $config);
    }
}
