<?php

return array(
    'bsb_doctrine_translator' => array(
        'manager' => array(
            'text_domains' => array(
            ),
            'source_paths' => array(
            ),
            'source_file_extensions' => array(
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'BsbDoctrineTranslator\Controller\CompareController' => 'BsbDoctrineTranslator\Controller\CompareController',
            'BsbDoctrineTranslator\Controller\ScannerController' => 'BsbDoctrineTranslator\Controller\ScannerController',
        ),
    ),
    'doctrine' => array(
        'driver' => array(
            'bsbdoctrinetranslator_annotation_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src')
            ),
            'orm_default' => array(
                'drivers' => array(
                    'BsbDoctrineTranslator\Entity' => 'bsbdoctrinetranslator_annotation_driver'
                )
            )
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
     *  - for locale [any or specific]
     *  - for domain [any or specific minus ignored ones]
     *
     *  - include translation
     *  - include locale
     *  - include origin
     *
     * bsb-doctrine-translator list (all|new|obsolete):type (both|singular|plural):type [<locale>] [<domain>] [--verbose|-v]'
     */

    'console'   => array(
        'router' => array(
            'routes' => array(
                'bsb-doctrine-translator' => array(
                    'type'    => 'Simple',
                    'options' => array(
                        'route'    => 'bsb-doctrine-translator compare source-to-database [--locale=] [--domain=] [--kind=] [--verbose|-v]',
                        'defaults' => array(
                            'controller' => 'BsbDoctrineTranslator\Controller\CompareController',
                            'action'     => 'compare-source-to-database'
                        ),
                    ),
                ),
                'bsb-doctrine-translator-import' => array(
                    'type'    => 'Simple',
                    'options' => array(
                        'route'    => 'bsb-doctrine-translator import <file>',
                        'defaults' => array(
                            'controller' => 'BsbDoctrineTranslator\Controller\CompareController',
                            'action'     => 'import'
                        ),
                    ),
                ),
                'bsb-doctrine-translator-export' => array(
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
