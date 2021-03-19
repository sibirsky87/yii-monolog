<?php

namespace YiiMonolog;

use CApplicationComponent;
use CException;
use Closure;
use Exception;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Monolog\Registry;
use RuntimeException;
use Yii;

class MonologComponent extends CApplicationComponent
{
    public string $name = 'application';
    public string $loggerName = 'main';
    public array $handlers = [];
    public array $processors = [];

    /**
     * @inheritdoc
     * @throws RuntimeException|CException
     */
    public function init()
    {
        $logger = new Logger($this->name);

        foreach ($this->handlers as $handler) {
            $logger->pushHandler($this->createHandler($handler));
        }

        foreach ($this->processors as $processor) {
            $logger->pushProcessor($this->createProcessor($processor));
        }

        Registry::addLogger($logger, $this->loggerName);

        parent::init();
    }

    /**
     * @param  string|array  $config
     *
     * @return HandlerInterface
     * @throws RuntimeException|CException
     */
    protected function createHandler($config): HandlerInterface
    {
        if ($config instanceof Closure) {
            return $config();
        }

        if (isset($config['formatter'])) {
            $formatterConfig = $config['formatter'];
            unset($config['formatter']);
        }

        /** @var HandlerInterface $instance */
        if (is_array($config)) {
            $instance = call_user_func_array(['Yii', 'createComponent'], $config);
        } else {
            $instance = Yii::createComponent($config);
        }

        if (isset($formatterConfig)) {
            $formatter = $this->createFormatter($formatterConfig);
            $instance->setFormatter($formatter);
        }

        return $instance;
    }

    /**
     * @param  string|array  $config
     *
     * @return FormatterInterface
     * @throws CException
     */
    protected function createFormatter($config): FormatterInterface
    {
        if (is_array($config)) {
            $instance = call_user_func_array(['Yii', 'createComponent'], $config);
        } else {
            $instance = Yii::createComponent($config);
        }

        return $instance;
    }

    /**
     * @param  array|string  $config
     *
     * @return Closure
     * @throws RuntimeException
     */
    protected function createProcessor($config): callable
    {
        try {
            if (is_array($config)) {
                $instance = call_user_func_array(['Yii', 'createComponent'], $config);
            } else {
                $instance = Yii::createComponent($config);
            }
            if (is_callable($instance)) {
                return $instance;
            }
        } catch (Exception $exception) {
        }

        throw new RuntimeException(
            'Unknown processor type, must be a Closure or a valid config for an invokable component'
        );
    }
}
