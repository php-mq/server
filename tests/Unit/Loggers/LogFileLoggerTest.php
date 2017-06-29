<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Tests\Unit\Loggers;

use PHPMQ\Server\Loggers\Constants\LogLevel;
use PHPMQ\Server\Loggers\Interfaces\ConfiguresLogFileLogger;
use PHPMQ\Server\Loggers\LogFileLogger;
use PHPUnit\Framework\TestCase;

final class LogFileLoggerTest extends TestCase
{
	/** @var ConfiguresLogFileLogger */
	private $config;

	protected function setUp() : void
	{
		$this->config = $this->getConfig( LogLevel::LOG_LEVEL_ERROR );
		@unlink( $this->config->getLogFilePath() );
	}

	public function testCanLogLiteralMessage() : void
	{
		$logger = new LogFileLogger( $this->config );

		$expectedLogRegExp = "#^\[alert\] | .+ | Unit-Test\n$#";

		$logger->alert( 'Unit-Test' );

		$content = file_get_contents( $this->config->getLogFilePath() );

		$this->assertRegExp( $expectedLogRegExp, $content );
	}

	private function getConfig( string $logLevel ) : ConfiguresLogFileLogger
	{
		return new class($logLevel) implements ConfiguresLogFileLogger
		{
			/** @var string */
			private $logLevel;

			public function __construct( string $logLevel )
			{
				$this->logLevel = $logLevel;
			}

			public function getLogFilePath() : string
			{
				return dirname( __DIR__, 3 ) . '/build/logs/phpmq.log';
			}

			public function getLogLevel() : string
			{
				return $this->logLevel;
			}
		};
	}

	public function testCanLogMessageWithContext() : void
	{
		$logger = new LogFileLogger( $this->config );

		$expectedLogRegExp = "#^\[error\] | .+ | Unit-Test | Context\: [case => context]\n$#";

		$logger->error( 'Unit-Test {case}', [ 'case' => 'context' ] );

		$content = file_get_contents( $this->config->getLogFilePath() );

		$this->assertRegExp( $expectedLogRegExp, $content );
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
		$logger = new LogFileLogger( $config );
		@unlink( $config->getLogFilePath() );

		$logger->critical( '.' );
		$logger->emergency( '.' );
		$logger->error( '.' );
		$logger->alert( '.' );
		$logger->warning( '.' );
		$logger->notice( '.' );
		$logger->info( '.' );
		$logger->debug( '.' );

		$content = file_get_contents( $config->getLogFilePath() );

		$this->assertRegExp( $expectedRegExp, $content );
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
