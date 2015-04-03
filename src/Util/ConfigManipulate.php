<?php

namespace BsbDoctrineTranslator\Util;

use BsbDoctrineTranslationLoader\Util\ConfigManipulate as TranslationLoaderConfigManipulate;
use Zend\ModuleManager\ModuleEvent;
use Zend\Stdlib\ArrayUtils;

class ConfigManipulate extends TranslationLoaderConfigManipulate
{
    /**
     * Handler listens
     *
     * @param ModuleEvent $e
     */
    public static function onMergeConfig(ModuleEvent $e)
    {
        $configListener = $e->getConfigListener();
        $config         = $configListener->getMergedConfig(false);

        $entityManager = $config['bsb_doctrine_translation_loader']['entity_manager'];
        $doctrineConf  = $config['bsb_doctrine_translator']['doctrine'];

        // manipulate configuration
        $doctrineConf  = self::replaceKey($doctrineConf, self::EM_REPLACE_TOKEN, $entityManager);
        unset($config['bsb_doctrine_translator']['doctrine']);
        $config['doctrine'] = ArrayUtils::merge($config['doctrine'], $doctrineConf);

        unset($config['doctrine']['driver'][$entityManager]['drivers']['BsbDoctrineTranslationLoader\Entity']);
        unset($config['doctrine']['driver']['bsbdoctrinetranslationloader_entity']);

        $configListener->setMergedConfig($config);
    }
}
