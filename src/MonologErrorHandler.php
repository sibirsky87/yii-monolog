<?php

namespace YiiMonolog;

use CErrorHandler;
use Monolog\ErrorHandler;
use Monolog\Registry;

class MonologErrorHandler extends CErrorHandler
{
    public string $loggerName = 'main';
    protected ErrorHandler $errorHandler;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $logger = Registry::getInstance($this->loggerName);
        $this->errorHandler = new ErrorHandler($logger);

        $this->errorHandler->registerErrorHandler();
        $this->errorHandler->registerExceptionHandler();
    }

    /**
     * @inheritdoc
     */
    protected function handleException($e)
    {
        parent::handleException($e);
    }

    /**
     * @inheritDoc
     */
    protected function handleError($event)
    {
        $context = is_array($event->params) ? $event->params : [$event->params];
        $this->errorHandler->handleError($event->code, $event->message, $event->file, $event->line, $context);
        parent::handleError($event);
    }
}
