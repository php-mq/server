<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Loggers;

use PHPMQ\Server\Constants\AnsiColors;
use PHPMQ\Server\Loggers\Constants\LogLevel;
use PHPMQ\Server\Loggers\Interfaces\ConfiguresLogFileLogger;

/**
 * Class LogFileLogger
 * @package PHPMQ\Server\Loggers
 */
final class LogFileLogger extends AbstractLogger
{
	/** @var ConfiguresLogFileLogger */
	private $config;

	public function __construct( ConfiguresLogFileLogger $config )
	{
		$this->config = $config;
	}

	public function log( $level, $message, array $context = [] ) : void
	{
		if ( !in_array( $level, LogLevel::LOG_LEVEL_ASSOC[ $this->config->getLogLevel() ], true ) )
		{
			return;
		}

		$logMessage = $this->getLogMessage( $level, $message, $context );
		$logMessage = str_replace( array_keys( AnsiColors::COLORS ), '', $logMessage );

		error_log( $logMessage . "\n", 3, $this->config->getLogFilePath() );
	}
}
