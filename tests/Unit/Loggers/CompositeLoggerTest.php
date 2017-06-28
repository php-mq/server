<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Tests\Unit\Loggers;

use PHPMQ\Server\Loggers\CompositeLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;

final class CompositeLoggerTest extends TestCase
{
	public function testCanAddLoggers() : void
	{
		$logger        = new CompositeLogger();
		$loggerOutput1 = new class extends AbstractLogger
		{
			public function log( $level, $message, array $context = [] )
			{
				echo sprintf( "Logger1 - [%s]: %s\n", $level, sprintf( $message, ...$context ) );
			}
		};

		$loggerOutput2 = new class extends AbstractLogger
		{
			public function log( $level, $message, array $context = [] )
			{
				echo sprintf( "Logger2 - [%s]: %s\n", $level, sprintf( $message, ...$context ) );
			}
		};

		$logger->addLoggers( $loggerOutput1, $loggerOutput2 );

		$this->expectOutputString(
			"Logger1 - [debug]: This is a Unit-Test\n"
			. "Logger2 - [debug]: This is a Unit-Test\n"
		);

		$logger->debug( 'This is a %s', [ 'Unit-Test' ] );
	}
}
