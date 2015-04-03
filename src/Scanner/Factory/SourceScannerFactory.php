<?php

namespace BsbDoctrineTranslator\Scanner\Factory;

use BsbDoctrineTranslator\Scanner\SourceScanner;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SourceScannerFactory implements FactoryInterface
{
    /**
     * {@inheritdocs}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config     = $serviceLocator->get('config');
        $paths      = $config['bsb_doctrine_translator']['manager']['source_paths'] ?: array();
        $extensions = $config['bsb_doctrine_translator']['manager']['source_file_extensions'] ?: array();
        $config     = array(
            'paths'      => $paths,
            'extensions' => $extensions,
        );

        return new SourceScanner($config);
    }
}
