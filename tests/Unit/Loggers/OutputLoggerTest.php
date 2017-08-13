<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Tests\Unit\Loggers;

use PHPMQ\Server\Loggers\Constants\LogLevel;
use PHPMQ\Server\Loggers\Interfaces\ConfiguresOutputLogger;
use PHPMQ\Server\Loggers\OutputLogger;
use PHPUnit\Framework\TestCase;

final class OutputLoggerTest extends TestCase
{
	public function testCanLogLiteralMessage() : void
	{
		$logger = new OutputLogger( $this->getConfig( LogLevel::LOG_LEVEL_DEBUG ) );

		$this->expectOutputRegex( "#^\[debug\] | .+ | Unit-Test\n$#" );

		$logger->debug( 'Unit-Test' );
	}

	private function getConfig( string $logLevel ) : ConfiguresOutputLogger
	{
		return new class($logLevel) implements ConfiguresOutputLogger
		{
			/** @var string */
			private $logLevel;

			public function __construct( string $logLevel )
			{
				$this->logLevel = $logLevel;
			}

			public function getLogLevel() : string
			{
				return $this->logLevel;
			}
		};
	}

	public function testCanLogMessageWithContext() : void
	{
		$logger = new OutputLogger( $this->getConfig( LogLevel::LOG_LEVEL_ERROR ) );

		$this->expectOutputRegex( "#^\[error\] | .+ | Unit-Test | Context\: [case => context]\n$#" );

		$logger->error( 'Unit-Test {case}', [ 'case' => 'context' ] );
	}

	/**
	 * @param string $logLevel
	 * @param string $expectedRegExp
	 *
	 * @dataProvider logLevelProvider
	 */
	public function testCanOmitEntriesByLogLevel( string $logLevel, string $expectedRegExp ) : void
	{
		$config = $this->getConfig( $logLevel );
		$logger = new OutputLogger( $config );

		$logger->critical( 'critical' );
		$logger->emergency( 'emergency' );
		$logger->error( 'error' );
		$logger->alert( 'alert' );
		$logger->warning( 'warning' );
		$logger->notice( 'notice' );
		$logger->info( 'info' );
		$logger->debug( 'debug' );

		$this->expectOutputRegex( $expectedRegExp );
	}

	public function logLevelProvider() : array
	{
		return [
			[
				'logLevel'       => LogLevel::LOG_LEVEL_ERROR,
				'expectedRegExp' => "#^\[critical\] | .+ | \.\n"
				                    . "\[emergency\] | .+ | \.\n"
				                    . "\[error\] | .+ | \.\n"
				                    . "\[alert\] | .+ | \.\n$#",
			],
			[
				'logLevel'       => LogLevel::LOG_LEVEL_INFO,
				'expectedRegExp' => "#^\[critical\] | .+ | \.\n"
				                    . "\[emergency\] | .+ | \.\n"
				                    . "\[error\] | .+ | \.\n"
				                    . "\[alert\] | .+ | \.\n"
				                    . "\[warning\] | .+ | \.\n"
				                    . "\[notice\] | .+ | \.\n"
				                    . "\[info\] | .+ | \.\n$#",
			],
			[
				'logLevel'       => LogLevel::LOG_LEVEL_DEBUG,
				'expectedRegExp' => "#^\[critical\] | .+ | \.\n"
				                    . "\[emergency\] | .+ | \.\n"
				                    . "\[error\] | .+ | \.\n"
				                    . "\[alert\] | .+ | \.\n"
				                    . "\[warning\] | .+ | \.\n"
				                    . "\[notice\] | .+ | \.\n"
				                    . "\[info\] | .+ | \.\n"
				                    . "\[debug\] | .+ | \.\n$#",
			],
		];
	}
}
