<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Configs;

use PHPMQ\Server\Loggers\Interfaces\ConfiguresLogFileLogger;

/**
 * Class LogFileLoggerConfig
 * @package PHPMQ\Server\Configs
 */
final class LogFileLoggerConfig implements ConfiguresLogFileLogger
{
	/** @var string */
	private $logFilePath;

	/** @var string */
	private $logLevel;

	public function __construct( string $logFilePath, string $logLevel )
	{
		$this->logFilePath = $logFilePath;
		$this->logLevel    = $logLevel;
	}

	public function getLogFilePath() : string
	{
		return $this->logFilePath;
	}

	public function getLogLevel() : string
	{
		return $this->logLevel;
	}
}
