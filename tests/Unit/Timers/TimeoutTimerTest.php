<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Timers;

use PHPMQ\Server\Timers\TimeoutTimer;
use PHPUnit\Framework\TestCase;

/**
 * Class TimeoutTimerTest
 * @package PHPMQ\Server\Tests\Unit\Timers
 */
final class TimeoutTimerTest extends TestCase
{
	public function testIsNotTimedOutIfNotStarted() : void
	{
		$timer = new TimeoutTimer( 500000 );
		$this->assertFalse( $timer->timedOut() );
	}

	public function testCanCheckIfTimedOut() : void
	{
		$timer = new TimeoutTimer( 500000 );
		$timer->start();

		$this->assertFalse( $timer->timedOut() );

		usleep( 600000 );

		$this->assertTrue( $timer->timedOut() );
	}

	public function testCanResetTimer() : void
	{
		$timer = new TimeoutTimer( 500000 );
		$timer->start();

		$this->assertFalse( $timer->timedOut() );

		usleep( 600000 );

		$this->assertTrue( $timer->timedOut() );

		$timer->reset();

		$this->assertFalse( $timer->timedOut() );
	}

	public function testCanRestartTimer() : void
	{
		$timer = new TimeoutTimer( 500000 );
		$timer->start();

		$this->assertFalse( $timer->timedOut() );

		usleep( 600000 );

		$this->assertTrue( $timer->timedOut() );

		$timer->restart();

		$this->assertFalse( $timer->timedOut() );

		usleep( 600000 );

		$this->assertTrue( $timer->timedOut() );
	}
}
