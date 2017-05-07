<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Storage;

use PHPMQ\Server\Interfaces\CarriesInformation;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\StorageMocking;
use PHPMQ\Server\Types\Message;
use PHPMQ\Server\Types\MessageId;
use PHPMQ\Server\Types\QueueName;
use PHPUnit\Framework\TestCase;

/**
 * Class MessageQueueSQLiteTest
 * @package PHPMQ\Server\Tests\Unit\Storage
 */
final class MessageQueueSQLiteTest extends TestCase
{
	use StorageMocking;

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
}
