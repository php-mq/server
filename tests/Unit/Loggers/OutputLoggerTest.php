<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Tests\Unit\Loggers;

use PHPMQ\Server\Loggers\OutputLogger;
use PHPUnit\Framework\TestCase;

final class OutputLoggerTest extends TestCase
{
	public function testCanLogLiteralMessage() : void
	{
		$logger = new OutputLogger();

		$this->expectOutputRegex( "#^\[debug\] | .+ | Unit-Test\n$#" );

		$logger->debug( 'Unit-Test' );
	}

	public function testCanLogMessageWithContext() : void
	{
		$logger = new OutputLogger();

		$this->expectOutputRegex( "#^\[error\] | .+ | Unit-Test | Context\: [case => context]\n$#" );

		$logger->error( 'Unit-Test {case}', [ 'case' => 'context' ] );
	}
}
