<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Loggers;

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
}
