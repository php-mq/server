<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Storage;

use PHPMQ\Server\Storage\Interfaces\ProvidesMessageData;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\StorageMockingRedis;
use PHPMQ\Server\Types\Message;
use PHPMQ\Server\Types\MessageId;
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
		$message   = $this->getMessage( 'unit-test' );
		$this->messageQueue->enqueue( $queueName, $message );

		$messages = iterator_to_array( $this->messageQueue->getUndispatched( $queueName ) );

		$this->assertCount( 1, $messages );
		$this->assertEquals( $message, $messages[0] );
	}

	private function getMessage( string $content ) : ProvidesMessageData
	{
		return new Message( MessageId::generate(), $content );
	}

	public function testCanMarkMessagesAsDispatched() : void
	{
		$queueName = new QueueName( 'TestQueue' );
		$message   = $this->getMessage( 'unit-test' );

		$this->messageQueue->enqueue( $queueName, $message );

		$this->messageQueue->markAsDispached( $queueName, $message->getMessageId() );

		$messages = iterator_to_array( $this->messageQueue->getUndispatched( $queueName ) );

		$this->assertCount( 0, $messages );
	}

	public function testCanMarkMessagesAsUndispatched() : void
	{
		$queueName = new QueueName( 'TestQueue' );
		$message   = $this->getMessage( 'unit-test' );

		$this->messageQueue->enqueue( $queueName, $message );

		$this->messageQueue->markAsDispached( $queueName, $message->getMessageId() );

		$this->messageQueue->markAsUndispatched( $queueName, $message->getMessageId() );

		$messages = iterator_to_array( $this->messageQueue->getUndispatched( $queueName ) );

		$this->assertCount( 1, $messages );
		$this->assertEquals( $message, $messages[0] );
	}

	public function testCanDequeueMessages() : void
	{
		$queueName = new QueueName( 'TestQueue' );
		$message1  = $this->getMessage( 'unit-test' );
		$message2  = $this->getMessage( 'test-unit' );

		$this->messageQueue->enqueue( $queueName, $message1 );
		$this->messageQueue->enqueue( $queueName, $message2 );

		$messages = iterator_to_array( $this->messageQueue->getUndispatched( $queueName, 2 ) );

		$this->assertCount( 2, $messages );

		$this->messageQueue->dequeue( $queueName, $message1->getMessageId() );

		$messages = iterator_to_array( $this->messageQueue->getUndispatched( $queueName ) );

		$this->assertCount( 1, $messages );
		$this->assertEquals( $message2, $messages[0] );

		$this->messageQueue->dequeue( $queueName, $message2->getMessageId() );

		$messages = iterator_to_array( $this->messageQueue->getUndispatched( $queueName ) );
		$this->assertCount( 0, $messages );
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

		$messages = iterator_to_array( $this->messageQueue->getUndispatched( $queueName, 3 ) );

		$this->assertCount( 3, $messages );

		$this->messageQueue->flushQueue( $queueName );

		$messages = iterator_to_array( $this->messageQueue->getUndispatched( $queueName, 3 ) );

		$this->assertCount( 0, $messages );
	}

	public function testCanFlushAllQueues() : void
	{
		$queueName1 = new QueueName( 'TestQueue1' );
		$queueName2 = new QueueName( 'TestQueue2' );
		$message1   = $this->getMessage( 'unit-test' );
		$message2   = $this->getMessage( 'test-unit' );
		$message3   = $this->getMessage( 'last' );

		$this->messageQueue->enqueue( $queueName1, $message1 );
		$this->messageQueue->enqueue( $queueName1, $message2 );
		$this->messageQueue->enqueue( $queueName1, $message3 );

		$this->messageQueue->enqueue( $queueName2, $message1 );
		$this->messageQueue->enqueue( $queueName2, $message2 );
		$this->messageQueue->enqueue( $queueName2, $message3 );

		$messages1 = iterator_to_array( $this->messageQueue->getUndispatched( $queueName1, 3 ) );
		$messages2 = iterator_to_array( $this->messageQueue->getUndispatched( $queueName2, 3 ) );

		$this->assertCount( 3, $messages1 );
		$this->assertCount( 3, $messages2 );

		$this->messageQueue->flushAllQueues();

		$messages1 = iterator_to_array( $this->messageQueue->getUndispatched( $queueName1, 3 ) );
		$messages2 = iterator_to_array( $this->messageQueue->getUndispatched( $queueName2, 3 ) );

		$this->assertCount( 0, $messages1 );
		$this->assertCount( 0, $messages2 );
	}

	public function testCanResetAllDispatched() : void
	{
		$queueName1 = new QueueName( 'TestQueue1' );
		$queueName2 = new QueueName( 'TestQueue2' );
		$message1   = $this->getMessage( 'unit-test' );
		$message2   = $this->getMessage( 'test-unit' );
		$message3   = $this->getMessage( 'last' );

		$this->messageQueue->enqueue( $queueName1, $message1 );
		$this->messageQueue->enqueue( $queueName1, $message2 );
		$this->messageQueue->enqueue( $queueName1, $message3 );

		$this->messageQueue->enqueue( $queueName2, $message1 );
		$this->messageQueue->enqueue( $queueName2, $message2 );
		$this->messageQueue->enqueue( $queueName2, $message3 );

		$this->messageQueue->markAsDispached( $queueName1, $message1->getMessageId() );
		$this->messageQueue->markAsDispached( $queueName1, $message2->getMessageId() );
		$this->messageQueue->markAsDispached( $queueName1, $message3->getMessageId() );

		$this->messageQueue->markAsDispached( $queueName2, $message1->getMessageId() );
		$this->messageQueue->markAsDispached( $queueName2, $message2->getMessageId() );
		$this->messageQueue->markAsDispached( $queueName2, $message3->getMessageId() );

		$messages1 = iterator_to_array( $this->messageQueue->getUndispatched( $queueName1, 3 ) );
		$messages2 = iterator_to_array( $this->messageQueue->getUndispatched( $queueName2, 3 ) );

		$this->assertCount( 0, $messages1 );
		$this->assertCount( 0, $messages2 );

		$this->messageQueue->resetAllDispatched();

		$messages1 = iterator_to_array( $this->messageQueue->getUndispatched( $queueName1, 3 ) );
		$messages2 = iterator_to_array( $this->messageQueue->getUndispatched( $queueName2, 3 ) );

		$this->assertCount( 3, $messages1 );
		$this->assertCount( 3, $messages2 );
	}
}
