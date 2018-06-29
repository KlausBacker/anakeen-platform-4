<?php

namespace Anakeen;

use Anakeen\Core\ContextManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;

class LogManager
{
    const channelName = "Anakeen Platform";
    const USER_FORMAT = "%channel%[%level_name%]{%user%}: %message% %context% %extra%";
    const ERROR = Logger::ERROR;
    const WARNING = Logger::WARNING;
    const INFO = Logger::INFO;
    const NOTICE = Logger::NOTICE;
    const DEBUG = Logger::DEBUG;
    /**
     * @var Logger
     */
    protected static $iLogger;
    protected static $iFormatter;

    public static function getLogger()
    {
        if (!self::$iLogger) {
            $sysHandler = new SyslogHandler(self::channelName, LOG_USER, self::getLogLevel());
            $sysHandler->setFormatter(self::getFormater());

            $phpErrorHandler = new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, Logger::ERROR);
            $phpErrorHandler->setFormatter(self::getFormater());
            self::$iLogger = new Logger(
                self::channelName,
                [$sysHandler, $phpErrorHandler],
                [
                    function ($record) {
                        if (ContextManager::getCurrentUser()) {
                            $record["user"] = ContextManager::getCurrentUser()->login;
                        }
                        return $record;
                    }
                ]
            );
        }
        return self::$iLogger;
    }

    public static function setFormater(FormatterInterface $formatter)
    {
        self::$iFormatter = $formatter;
        self::$iLogger = null;
    }

    protected static function getFormater()
    {
        if (self::$iFormatter === null) {
            self::$iFormatter = new LineFormatter(self::USER_FORMAT);
        }
        return self::$iFormatter;
    }

    /**
     * get loglevel conforming CORE_LOGLEVEL parameter
     * @return int
     */
    public static function getLogLevel()
    {
        static $pLogLevel = null;

        if ($pLogLevel === null) {
            $pLogLevel = ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "CORE_LOGLEVEL");
        }
        switch ($pLogLevel) {
            case "ERROR":
                return Logger::ERROR;
            case "WARNING":
                return Logger::WARNING;
            case "INFO":
                return Logger::INFO;
            case "NOTICE":
                return Logger::NOTICE;
            case "DEBUG":
                return Logger::DEBUG;
        }
        return Logger::INFO;
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public static function emergency($message, array $context = array())
    {
        self::getLogger()->emergency($message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public static function alert($message, array $context = array())
    {
        self::getLogger()->alert($message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public static function critical($message, array $context = array())
    {
        self::getLogger()->emergency($message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public static function error($message, array $context = array())
    {
        self::getLogger()->error($message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public static function warning($message, array $context = array())
    {
        self::getLogger()->warning($message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public static function notice($message, array $context = array())
    {
        self::getLogger()->notice($message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public static function info($message, array $context = array())
    {
        self::getLogger()->info($message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public static function debug($message, array $context = array())
    {
        self::getLogger()->debug($message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public static function log($level, $message, array $context = array())
    {
        self::getLogger()->log($level, $message, $context);
    }

    /**
     * Pushes a handler on to the stack.
     *
     * @param  HandlerInterface $handler
     * @return void
     */
    public static function pushHandler(HandlerInterface $handler)
    {
        self::getLogger()->pushHandler($handler);
    }

    /**
     * Set handlers, replacing all existing ones.
     *
     * @param  HandlerInterface[] $handlers
     * @return void
     */
    public static function setHandlers(array $handlers)
    {
        self::getLogger()->setHandlers($handlers);
    }
}
