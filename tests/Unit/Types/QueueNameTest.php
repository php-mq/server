<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Tests\Unit\Types;

use PHPMQ\Server\Types\QueueName;
use PHPUnit\Framework\TestCase;

final class QueueNameTest extends TestCase
{
	public function testCanCheckIfQueueNamesAreEqual(): void
	{
		$queue1 = new QueueName( 'Unit-Test-Queue' );
		$queue2 = new QueueName( 'Unit-Test-Queue' );
		$queue3 = new QueueName( 'Example-Queue' );

		$this->assertTrue( $queue1->equals( $queue2 ) );
		$this->assertTrue( $queue2->equals( $queue1 ) );
		$this->assertFalse( $queue1->equals( $queue3 ) );
		$this->assertFalse( $queue2->equals( $queue3 ) );
		$this->assertFalse( $queue3->equals( $queue1 ) );
		$this->assertFalse( $queue3->equals( $queue2 ) );
	}

	public function testCanGetValueAsString(): void
	{
		$queue = new QueueName( 'Unit-Test-Queue' );

		$this->assertSame( 'Unit-Test-Queue', (string)$queue );
		$this->assertSame( 'Unit-Test-Queue', $queue->toString() );
	}
}
