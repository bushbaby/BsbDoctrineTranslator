<?php

namespace BsbDoctrineTranslatorTest\Util;

use BsbDoctrineTranslator\Util\ConfigManipulate;
use BsbDoctrineTranslatorTest\Framework\TestCase;

class ConfigManipulateTest extends TestCase
{
    public function testOnMergeConfig()
    {
        $inputConfig    = array(
            'bsb_doctrine_translation_loader' => array(
                'entity_manager' => 'orm_default',
            ),
            'bsb_doctrine_translator' => array(
                'doctrine'       => array(
                    ConfigManipulate::EM_REPLACE_TOKEN => 'bar'
                ),
            ),
            'doctrine'                        => array()
        );
        $outputConfig   = array(
            'bsb_doctrine_translation_loader' => array(
                'entity_manager' => 'orm_default',
            ),
            'bsb_doctrine_translator' => array(
            ),
            'doctrine'                        => array(
                'orm_default' => 'bar',
            )
        );

        $event          = new \Zend\ModuleManager\ModuleEvent();
        $configListener = $this->getMock('Zend\ModuleManager\Listener\ConfigMergerInterface');

        $event->setConfigListener($configListener);

        $configListener->expects($this->once())->method('getMergedConfig')->with(false)->willReturn($inputConfig);
        $configListener->expects($this->once())->method('setMergedConfig')->with($outputConfig);

        ConfigManipulate::onMergeConfig($event);
    }
}
