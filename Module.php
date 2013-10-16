<?php

namespace BsbDoctrineTranslator;


use Zend\Console\Adapter\AdapterInterface;
use Zend\ServiceManager\Config;
use Zend\Mvc\MvcEvent;
use Zend\I18n\Translator\Translator;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\AggregateResolver;

class Module
{

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'BsbDoctrineTranslator\Scanner' => 'BsbDoctrineTranslator\Factory\SourceScannerFactory',
                'BsbDoctrineTranslator\Manager' => 'BsbDoctrineTranslator\Factory\ManagerFactory',
            ),
        );
    }

    public function getModuleDependencies() {
        return array('BsbDoctrineTranslationLoader');
    }
    /**
     * {@inheritDoc}
     */
    public function getConsoleBanner(AdapterInterface $console)
    {
        return 'BsbDoctrineTranslator : alpha something';
    }

    /**
     * {@inheritDoc}
     */
    public function getConsoleUsage(AdapterInterface $console)
    {
        return array(
            'bsb-doctrine-translator compare source-to-database [--locale=] [--domain=] [--kind=] [--verbose|-v]' => 'Compares message found in source to those stored in the database',
            'bsb-doctrine-translator import <file>' => 'Import excel file to database',
            'bsb-doctrine-translator export <file>' => 'Export excel file from database',
            'bsb-doctrine-translator scan [--path=] [--extension=] [--locale=] [--domain=] [--kind=] [--verbose|-v]' => 'Scan source for invokations of translate and translatePlural',

            array(
                '<domain>', 'TextDomain to process',
                '<locale>', 'Locale to process'),
        );
    }
}
