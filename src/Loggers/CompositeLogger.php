<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Loggers;

use PHPMQ\Server\Configs\ConfigBuilder;
use PHPMQ\Server\Constants\LoggerType;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

/**
 * Class CompositeLogger
 * @package PHPMQ\Server\Loggers
 */
final class CompositeLogger extends AbstractLogger
{
	/** @var array|LoggerInterface[] */
	private $loggers = [];

	public function addLoggers( LoggerInterface ...$loggers ) : void
	{
		foreach ( $loggers as $logger )
		{
			$this->loggers[] = $logger;
		}
	}

	public function log( $level, $message, array $context = [] ) : void
	{
		foreach ( $this->loggers as $logger )
		{
			$logger->log( $level, $message, $context );
		}
	}

	public static function fromConfigBuilder( ConfigBuilder $configBuilder ) : self
	{
		$logger = new self();
		if ( in_array( LoggerType::LOG_FILE, $configBuilder->getActiveLoggers(), true ) )
		{
			$logger->addLoggers( new LogFileLogger( $configBuilder->getLogFileLoggerConfig() ) );
		}

		if ( in_array( LoggerType::OUTPUT, $configBuilder->getActiveLoggers(), true ) )
		{
			$logger->addLoggers( new OutputLogger( $configBuilder->getOutputLoggerConfig() ) );
		}

		return $logger;
	}
}
