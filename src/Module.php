<?php

namespace BsbDoctrineTranslator;

use Zend\Console\Adapter\AdapterInterface;
use Zend\ModuleManager\ModuleEvent;
use Zend\ModuleManager\ModuleManager;
use Zend\Stdlib\ArrayUtils;

class Module
{
    public function init(ModuleManager $moduleManager)
    {
        $moduleManager
            ->getEventManager()
            ->attach(
                ModuleEvent::EVENT_MERGE_CONFIG,
                array('BsbDoctrineTranslator\Util\ConfigManipulate', 'onMergeConfig')
            );
    }

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getModuleDependencies()
    {
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
            'bsb-doctrine-translator compare source-to-database [--locale=] [--domain=] [--kind=] [--verbose|-v]'    => 'Compares message found in source to those stored in the database',
            'bsb-doctrine-translator import <file>'                                                                  => 'Import excel file to database',
            'bsb-doctrine-translator export <file>'                                                                  => 'Export excel file from database',
            'bsb-doctrine-translator scan [--path=] [--extension=] [--locale=] [--domain=] [--kind=] [--verbose|-v]' => 'Scan source for invokations of translate and translatePlural',
            array(
                '<domain>',
                'TextDomain to process',
                '<locale>',
                'Locale to process'
            ),
        );
    }
}
