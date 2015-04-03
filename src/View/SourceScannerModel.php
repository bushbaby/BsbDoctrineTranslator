<?php
/**
 * Created by JetBrains PhpStorm.
 * User: bas
 * Date: 25-06-13
 * Time: 14:02
 * To change this template use File | Settings | File Templates.
 */

namespace BsbDoctrineTranslator\View;

use BsbDoctrineTranslator\Model\Message;
use Console_Table;
use Zend\View\Model\ConsoleModel;

/**
 * Class SourceScannerModel
 *
 * @todo    replace pear table with http://symfony.com/doc/current/components/console/helpers/table.html
 * @package BsbDoctrineTranslator\View
 */
class SourceScannerModel extends ConsoleModel
{
    /**
     * Constructor
     *
     * @param  null|array|Traversable $variables
     * @param  array|Traversable      $options
     */
    public function __construct($variables = null, $options = null)
    {
        parent::__construct($variables, $options);
    }

    /**
     * Get result text.
     *
     * @return mixed
     */
    public function getResult()
    {

        $result = $this->getVariable(self::RESULT);

        if (!$result) {
            $this->render();
        }

        return $this->getVariable(self::RESULT);
    }

    protected function render()
    {
        $tbl     = new Console_Table(CONSOLE_TABLE_ALIGN_LEFT, CONSOLE_TABLE_BORDER_ASCII, 1, 'utf-8');
        $headers = ['', 'Kind', 'Key', 'Language', 'Domain'];
        if ($this->getVariable('show-translation')) {
            $headers[] = 'Translation';
        }
        if ($this->getVariable('verbose')) {
            $headers[] = 'Origin';
        }
        $tbl->setHeaders($headers);
        $tbl->setAlign(0, CONSOLE_TABLE_ALIGN_RIGHT);

        // array
        $messages = $this->getVariable('messages');

        usort($messages, function (Message $a, Message $b) {

            return strcmp($a->getTextDomain(), $b->getTextDomain());
        });

        $props = ['Key', 'TextDomain'];

        usort($messages, function ($a, $b) use ($props) {
            for ($i = 1; $i < count($props); $i++) {
                $getMethodPrev = 'get' . $props[$i - 1];
                $getMethod     = 'get' . $props[$i];
                if ($a->$getMethodPrev() == $b->$getMethodPrev()) {
                    return strcmp((string) $a->$getMethod(), (string) $b->$getMethod());
                }
            }
            $getMethod = 'get' . $props[0];

            return strcmp($a->$getMethod(), $b->$getMethod());
        });

        /** @var $occurance Message */
        foreach ($messages as $key => $occurance) {
            $row = [
                $key + 1,
                $occurance->getKind() == Message::KIND_SINGULAR ? 'S' : 'P',
                $occurance->getKey() === false ? ($this->getVariable('verbose')) ? '- ' . $occurance->getKeyInfo() : '-' : wordwrap($occurance->getKey(), 60, "\n"),
                $occurance->getLocale() === false ? ($this->getVariable('verbose')) ? '- ' . $occurance->getLocaleInfo() : '-' : $occurance->getLocale(),
                $occurance->getTextDomain() === false ? ($this->getVariable('verbose')) ? '- ' . $occurance->getTextDomainInfo() : '-' : $occurance->getTextDomain()
            ];

            if ($this->getVariable('show-translation')) {
                $row[] = $occurance->getTranslation();
            }

            if ($this->getVariable('verbose')) {
                $row[] = (is_array($occurance->getOrigin())) ? implode("\n", $occurance->getOrigin()) : $occurance->getOrigin();
            }

            $tbl->addRow($row);
        }

        $this->setVariable(self::RESULT, $tbl->getTable());
    }
}
