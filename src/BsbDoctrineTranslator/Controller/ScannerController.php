<?php

namespace BsbDoctrineTranslator\Controller;

use BsbDoctrineTranslator\Model\Filter;
use BsbDoctrineTranslator\Model\FilterConstant;
use BsbDoctrineTranslator\Model\Message;
use BsbDoctrineTranslator\Scanner\SourceScanner;
use BsbDoctrineTranslator\View\SourceScannerModel;
use Console_Table;
use Zend\Console\Console;
use Zend\Console\Prompt\Confirm;
use Zend\Console\Request as ConsoleRequest;
use Zend\Http\Request as HttpRequest;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ConsoleModel;

class ScannerController extends AbstractActionController
{
    public function scanAction()
    {

        /** @var $request Request */
        $request = $this->getRequest();

        $path       = $request->getParam('path', null);
        $extension  = $request->getParam('extension', null);

        $kind       = 0;
        $kind       += $request->getParam('kind') == 'singular' ? Message::KIND_SINGULAR : 0;
        $kind       += $request->getParam('kind') == 'plural' ? Message::KIND_PLURAL : 0;

        if ($kind == 0) {
            $kind = Message::KIND_SINGULAR + Message::KIND_PLURAL;
        }

        $locale          = $request->getParam('locale', FilterConstant::NONE);
        $domain          = $request->getParam('domain', FilterConstant::NONE);
        $isVerbose       = $request->getParam('verbose', false) || $request->getParam('v', false);

        /** @var SourceScanner $functionScanner */
        $functionScanner = $this->getServiceLocator()->get('BsbDoctrineTranslator\Scanner');

        if ($path) {
            $functionScanner->setPaths(explode(',', $path));
        }

        if ($extension) {
            $functionScanner->setExtensions(explode(',', $extension));
        }

        // Scans the source for occurances of singular and/or plurals definitions
        $messages = $functionScanner->scan($kind, $locale, $domain);
        $messages = $functionScanner->compact($messages, FilterConstant::VARIABLE_ANY);

        if ($request instanceof HttpRequest) {
            $model = new ViewModel(array('verbose' => $isVerbose, 'error'=>$functionScanner->getErrors(), 'messages'=>$messages));
        } elseif ($request instanceof ConsoleRequest) {
            $model = new SourceScannerModel(array('verbose' => $isVerbose, 'error'=>$functionScanner->getErrors(), 'messages'=>$messages));
        } else {
            throw new RuntimeException('Cannot handle request of type ' . get_class($request));
        }

        return $model;
    }
}