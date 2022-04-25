<?php

namespace YiiMonolog;

use Monolog\Registry;
use Monolog\ErrorHandler;
use Monolog\Utils;
use Psr\Log\LogLevel;

class MonologErrorHandler extends \CErrorHandler
{
    /** @var string */
    public $loggerName = 'main';
    /** @var Monolog\ErrorHandler */
    protected $errorHandler;

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
        $logger = Registry::getInstance($this->loggerName);
        $logger->log(
            LogLevel::ERROR,
            sprintf('Uncaught Exception %s: "%s" at %s line %s', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine()),
            ['exception' => $e]
        );
        parent::handleException($e);
    }

    /**
     * @inheritDoc
     */
    protected function handleError($event)
    {
        $this->errorHandler->handleError($event->code, $event->message, $event->file, $event->line, $event->params);
        parent::handleError($event);
    }
}
