<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Storage;

use PHPMQ\Server\Interfaces\CarriesInformation;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\StorageMockingRedis;
use PHPMQ\Server\Types\Message;
use PHPMQ\Server\Types\MessageId;
use PHPMQ\Server\Types\MessageQueueStatus;
use PHPMQ\Server\Types\QueueName;
use PHPUnit\Framework\TestCase;

/**
 * Class MessageQueueSQLiteTest
 * @package PHPMQ\MessageQueueServer\Tests\Unit\Storage
 */
final class MessageQueueRedisTest extends TestCase
{
	use StorageMockingRedis;

	public function setUp() : void
	{
		$this->setUpStorage();
	}

	public function tearDown() : void
	{
		$this->tearDownStorage();
	}

	public function testCanEnqueueMessages() : void
	{
		$queueName = new QueueName( 'TestQueue' );

		$this->messageQueue->enqueue( $queueName, $this->getMessage( 'unit-test' ) );

		$status = $this->messageQueue->getQueueStatus( $queueName );

		$this->assertEquals( $queueName, $status->getQueueName() );
		$this->assertSame( 1, $status->getCountTotal() );
		$this->assertSame( 1, $status->getCountUndispatched() );
		$this->assertSame( 0, $status->getCountDispatched() );

		$this->messageQueue->enqueue( $queueName, $this->getMessage( 'test-unit' ) );

		$status = $this->messageQueue->getQueueStatus( $queueName );

		$this->assertEquals( $queueName, $status->getQueueName() );
		$this->assertSame( 2, $status->getCountTotal() );
		$this->assertSame( 2, $status->getCountUndispatched() );
		$this->assertSame( 0, $status->getCountDispatched() );
	}

	private function getMessage( string $content ) : CarriesInformation
	{
		return new Message( MessageId::generate(), $content );
	}

	public function testCanMarkMessagesAsDispatched() : void
	{
		$queueName = new QueueName( 'TestQueue' );
		$message   = $this->getMessage( 'unit-test' );

		$this->messageQueue->enqueue( $queueName, $message );

		$status = $this->messageQueue->getQueueStatus( $queueName );

		$this->assertEquals( $queueName, $status->getQueueName() );
		$this->assertSame( 1, $status->getCountTotal() );
		$this->assertSame( 1, $status->getCountUndispatched() );
		$this->assertSame( 0, $status->getCountDispatched() );

		$this->messageQueue->markAsDispached( $queueName, $message->getMessageId() );

		$status = $this->messageQueue->getQueueStatus( $queueName );

		$this->assertEquals( $queueName, $status->getQueueName() );
		$this->assertSame( 1, $status->getCountTotal() );
		$this->assertSame( 0, $status->getCountUndispatched() );
		$this->assertSame( 1, $status->getCountDispatched() );
	}

	public function testCanMarkMessagesAsUndispatched() : void
	{
		$queueName = new QueueName( 'TestQueue' );
		$message   = $this->getMessage( 'unit-test' );

		$this->messageQueue->enqueue( $queueName, $message );

		$this->messageQueue->markAsDispached( $queueName, $message->getMessageId() );

		$status = $this->messageQueue->getQueueStatus( $queueName );

		$this->assertEquals( $queueName, $status->getQueueName() );
		$this->assertSame( 1, $status->getCountTotal() );
		$this->assertSame( 0, $status->getCountUndispatched() );
		$this->assertSame( 1, $status->getCountDispatched() );

		$this->messageQueue->markAsUndispatched( $queueName, $message->getMessageId() );

		$status = $this->messageQueue->getQueueStatus( $queueName );

		$this->assertEquals( $queueName, $status->getQueueName() );
		$this->assertSame( 1, $status->getCountTotal() );
		$this->assertSame( 1, $status->getCountUndispatched() );
		$this->assertSame( 0, $status->getCountDispatched() );
	}

	public function testCanDequeueMessages() : void
	{
		$queueName = new QueueName( 'TestQueue' );
		$message1  = $this->getMessage( 'unit-test' );
		$message2  = $this->getMessage( 'test-unit' );

		$this->messageQueue->enqueue( $queueName, $message1 );
		$this->messageQueue->enqueue( $queueName, $message2 );

		$status = $this->messageQueue->getQueueStatus( $queueName );

		$this->assertEquals( $queueName, $status->getQueueName() );
		$this->assertSame( 2, $status->getCountTotal() );
		$this->assertSame( 2, $status->getCountUndispatched() );
		$this->assertSame( 0, $status->getCountDispatched() );

		$this->messageQueue->dequeue( $queueName, $message1->getMessageId() );
		$this->messageQueue->dequeue( $queueName, $message2->getMessageId() );

		$status = $this->messageQueue->getQueueStatus( $queueName );

		$this->assertEquals( $queueName, $status->getQueueName() );
		$this->assertSame( 0, $status->getCountTotal() );
		$this->assertSame( 0, $status->getCountUndispatched() );
		$this->assertSame( 0, $status->getCountDispatched() );
	}

	public function testCanGetUndispatchedMessages() : void
	{
		$queueName = new QueueName( 'TestQueue' );
		$message1  = $this->getMessage( 'unit-test' );
		$message2  = $this->getMessage( 'test-unit' );
		$message3  = $this->getMessage( 'last' );

		$this->messageQueue->enqueue( $queueName, $message1 );
		$this->messageQueue->enqueue( $queueName, $message2 );
		$this->messageQueue->enqueue( $queueName, $message3 );

		$expectedMessages = [
			$message1,
			$message2,
			$message3,
		];

		$this->assertEquals(
			$message1,
			$this->messageQueue->getUndispatched( $queueName )->current()
		);

		$this->assertEquals(
			$expectedMessages,
			iterator_to_array( $this->messageQueue->getUndispatched( $queueName, 3 ) )
		);
	}

	public function testCanFlushAQueue() : void
	{
		$queueName = new QueueName( 'TestQueue' );
		$message1  = $this->getMessage( 'unit-test' );
		$message2  = $this->getMessage( 'test-unit' );
		$message3  = $this->getMessage( 'last' );

		$this->messageQueue->enqueue( $queueName, $message1 );
		$this->messageQueue->enqueue( $queueName, $message2 );
		$this->messageQueue->enqueue( $queueName, $message3 );

		$status = $this->messageQueue->getQueueStatus( $queueName );

		$this->assertSame( 3, $status->getCountTotal() );
		$this->assertSame( 3, $status->getCountUndispatched() );
		$this->assertSame( 0, $status->getCountDispatched() );

		$this->messageQueue->flushQueue( $queueName );

		$status = $this->messageQueue->getQueueStatus( $queueName );

		$this->assertSame( 0, $status->getCountTotal() );
		$this->assertSame( 0, $status->getCountUndispatched() );
		$this->assertSame( 0, $status->getCountDispatched() );
	}

	public function testCanFlushAllQueues() : void
	{
		$queueName1 = new QueueName( 'TestQueue1' );
		$queueName2 = new QueueName( 'TestQueue2' );
		$message1   = $this->getMessage( 'unit-test' );
		$message2   = $this->getMessage( 'test-unit' );
		$message3   = $this->getMessage( 'last' );

		$expectedQueueStatus = [
			new MessageQueueStatus(
				[
					'queueName'         => 'TestQueue1',
					'countTotal'        => 3,
					'countUndispatched' => 3,
					'countDispatched'   => 0,
				]
			),
			new MessageQueueStatus(
				[
					'queueName'         => 'TestQueue2',
					'countTotal'        => 3,
					'countUndispatched' => 3,
					'countDispatched'   => 0,
				]
			),
		];

		$this->messageQueue->enqueue( $queueName1, $message1 );
		$this->messageQueue->enqueue( $queueName1, $message2 );
		$this->messageQueue->enqueue( $queueName1, $message3 );

		$this->messageQueue->enqueue( $queueName2, $message1 );
		$this->messageQueue->enqueue( $queueName2, $message2 );
		$this->messageQueue->enqueue( $queueName2, $message3 );

		$status = $this->messageQueue->getAllQueueStatus();

		foreach ( $status as $queueStatus )
		{
			$this->assertTrue( in_array( $queueStatus, $expectedQueueStatus, false ) );
		}

//		$this->assertEquals( $expectedQueueStatus, iterator_to_array( $status ) );

		$this->messageQueue->flushAllQueues();

		$status = $this->messageQueue->getAllQueueStatus();

		$this->assertEquals( [], iterator_to_array( $status ) );
	}
}
