<?php

namespace BsbDoctrineTranslator\Factory;

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
        $config = $serviceLocator->get('config');

        $paths = $config['bsbdoctrinetranslator']['manager']['source_paths'] ?: array();
        $extensions = $config['bsbdoctrinetranslator']['manager']['source_file_extensions'] ?: array();
        $config = array(
            'paths' => $paths,
            'extensions' => $extensions,
        );

        $service = new SourceScanner($config);

        return $service;
    }
}