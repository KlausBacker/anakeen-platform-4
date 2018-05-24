<?php

namespace Anakeen;

use Anakeen\Core\Internal\ApplicationParameterManager;
use Anakeen\Core\Internal\LogLineFormatter;
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

    public static function getLogger()
    {
        if (!self::$iLogger) {
            self::$iLogger = new Logger(self::channelName);

            $sysHandler = new SyslogHandler(self::channelName, LOG_USER, self::getLogLevel());
            $sysHandler->setFormatter(new LogLineFormatter(self::USER_FORMAT));

            $phpErrorHandler= new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, Logger::ERROR);
            $phpErrorHandler->setFormatter(new LogLineFormatter(self::USER_FORMAT));
            self::$iLogger = new Logger(self::channelName, [$sysHandler, $phpErrorHandler]);
        }
        return self::$iLogger;
    }

    /**
     * get loglevel conforming CORE_LOGLEVEL parameter
     * @return int
     */
    public static function getLogLevel()
    {
        static $pLogLevel = null;

        if ($pLogLevel === null) {
            $pLogLevel = ApplicationParameterManager::getScopedParameterValue("CORE_LOGLEVEL");
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
