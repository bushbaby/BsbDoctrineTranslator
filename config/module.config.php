<?php

return array(
    'bsb_doctrine_translator' => array(
        'manager'  => array(
            'source_paths'           => array(
            ),
            'source_file_extensions' => array(
            ),
        ),
        /**
         * The $configarray('doctrine') configuration will be manipulated with these 'template' options
         * and will be removed from $configarray('bsb_doctrine_translator')
         */
        'doctrine' => array(
            'configuration' => array(
                \BsbDoctrineTranslator\Util\ConfigManipulate::EM_REPLACE_TOKEN => array(
                    'entity_namespaces' => array(
                        'BsbDoctrineTranslationLoader' => 'BsbDoctrineTranslator\Entity',
                    ),
                ),
            ),
            'driver'        => array(
                \BsbDoctrineTranslator\Util\ConfigManipulate::EM_REPLACE_TOKEN => array(
                    'drivers' => array(
                        'BsbDoctrineTranslator\Entity'        => 'bsbdoctrinetranslator_entity',
                        'BsbDoctrineTranslationLoader\Entity' => 'bsbdoctrinetranslationloader_entity',
                    )
                )
            ),
        ),
    ),
    'doctrine'                => array(
        'driver' => array(
            'bsbdoctrinetranslator_entity' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\XmlDriver',
                'paths' => __DIR__ . '/xml/bsbdoctrinetranslator',
            ),
        ),
    ),
    'controllers'             => array(
        'invokables' => array(
            'BsbDoctrineTranslator\Controller\CompareController'
            => 'BsbDoctrineTranslator\Controller\CompareController',
            'BsbDoctrineTranslator\Controller\ScannerController'
            => 'BsbDoctrineTranslator\Controller\ScannerController',
        ),
    ),

    'service_manager' =>            array(
        'factories' => array(
            'BsbDoctrineTranslator\Scanner' => 'BsbDoctrineTranslator\Scanner\Factory\SourceScannerFactory',
            'BsbDoctrineTranslator\Manager' => 'BsbDoctrineTranslator\Manager\Factory\DoctrineTranslatorFactory',
        ),
    ),

    /**
     *  list
     *  - keys of kind
     *  - new of kind (not in db)
     *  - obsolete of kind (not in source)
     *  - obsolete of kind (not in source)
     *
     *  - kind both|singular|plural
     *
     *  - for locale array(any or specific]
     *  - for domain array(any or specific minus ignored ones]
     *
     *  - include translation
     *  - include locale
     *  - include origin
     *
     * bsb-doctrine-translator list (all|new|obsolete):type (both|singular|plural):type array(<locale>] array(<domain>] array(--verbose|-v]'
     */

    'console'                 => array(
        'router' => array(
            'routes' => array(
                'bsb-doctrine-translator'         => array(
                    'type'    => 'Simple',
                    'options' => array(
                        'route'    => 'bsb-doctrine-translator compare source-to-database [--locale=] [--domain=] [--kind=] [--verbose|-v]',
                        'defaults' => array(
                            'controller' => 'BsbDoctrineTranslator\Controller\CompareController',
                            'action'     => 'compare-source-to-database'
                        ),
                    ),
                ),
                'bsb-doctrine-translator-import'  => array(
                    'type'    => 'Simple',
                    'options' => array(
                        'route'    => 'bsb-doctrine-translator import <file>',
                        'defaults' => array(
                            'controller' => 'BsbDoctrineTranslator\Controller\CompareController',
                            'action'     => 'import'
                        ),
                    ),
                ),
                'bsb-doctrine-translator-export'  => array(
                    'type'    => 'Simple',
                    'options' => array(
                        'route'    => 'bsb-doctrine-translator export <file>',
                        'defaults' => array(
                            'controller' => 'BsbDoctrineTranslator\Controller\CompareController',
                            'action'     => 'export'
                        ),
                    ),
                ),
                'bsb-doctrine-translator-scanner' => array(
                    'type'    => 'Simple',
                    'options' => array(
                        'route'    => 'bsb-doctrine-translator scan [--path=] [--extension=] [--locale=] [--domain=] [--kind=] [--verbose|-v]',
                        'defaults' => array(
                            'controller' => 'BsbDoctrineTranslator\Controller\ScannerController',
                            'action'     => 'scan'
                        ),
                    ),
                ),
            ),
        ),
    ),
);
