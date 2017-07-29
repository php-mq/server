<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Storage;

use PHPMQ\Server\Interfaces\IdentifiesQueue;
use PHPMQ\Server\Storage\Interfaces\ProvidesMessageData;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\QueueIdentifierMocking;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\StorageMockingSQLite;
use PHPMQ\Server\Types\Message;
use PHPMQ\Server\Types\MessageId;
use PHPUnit\Framework\TestCase;

/**
 * Class SQLiteStorageTest
 * @package PHPMQ\Server\Tests\Unit\Storage
 */
final class SQLiteStorageTest extends TestCase
{
	use StorageMockingSQLite;
	use QueueIdentifierMocking;

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
		$queueName = $this->getQueueName( 'TestQueue' );
		$message   = $this->getMessage( 'unit-test' );
		$this->storage->enqueue( $queueName, $message );

		$messages = iterator_to_array( $this->storage->getUndispatched( $queueName ) );

		$this->assertCount( 1, $messages );
		$this->assertEquals( $message, $messages[0] );
	}

	private function getMessage( string $content ) : ProvidesMessageData
	{
		return new Message( MessageId::generate(), $content );
	}

	public function testCanMarkMessagesAsDispatched() : void
	{
		$queueName = $this->getQueueName( 'TestQueue' );
		$message   = $this->getMessage( 'unit-test' );

		$this->storage->enqueue( $queueName, $message );

		$this->storage->markAsDispached( $queueName, $message->getMessageId() );

		$messages = iterator_to_array( $this->storage->getUndispatched( $queueName ) );

		$this->assertCount( 0, $messages );
	}

	public function testCanMarkMessagesAsUndispatched() : void
	{
		$queueName = $this->getQueueName( 'TestQueue' );
		$message   = $this->getMessage( 'unit-test' );

		$this->storage->enqueue( $queueName, $message );

		$this->storage->markAsDispached( $queueName, $message->getMessageId() );

		$this->storage->markAsUndispatched( $queueName, $message->getMessageId() );

		$messages = iterator_to_array( $this->storage->getUndispatched( $queueName ) );

		$this->assertCount( 1, $messages );
		$this->assertEquals( $message, $messages[0] );
	}

	public function testCanDequeueMessages() : void
	{
		$queueName = $this->getQueueName( 'TestQueue' );
		$message1  = $this->getMessage( 'unit-test' );
		$message2  = $this->getMessage( 'test-unit' );

		$this->storage->enqueue( $queueName, $message1 );
		$this->storage->enqueue( $queueName, $message2 );

		$messages = iterator_to_array( $this->storage->getUndispatched( $queueName, 2 ) );

		$this->assertCount( 2, $messages );

		$this->storage->dequeue( $queueName, $message1->getMessageId() );

		$messages = iterator_to_array( $this->storage->getUndispatched( $queueName ) );

		$this->assertCount( 1, $messages );
		$this->assertEquals( $message2, $messages[0] );

		$this->storage->dequeue( $queueName, $message2->getMessageId() );

		$messages = iterator_to_array( $this->storage->getUndispatched( $queueName ) );
		$this->assertCount( 0, $messages );
	}

	public function testCanGetUndispatchedMessages() : void
	{
		$queueName = $this->getQueueName( 'TestQueue' );
		$message1  = $this->getMessage( 'unit-test' );
		$message2  = $this->getMessage( 'test-unit' );
		$message3  = $this->getMessage( 'last' );

		$this->storage->enqueue( $queueName, $message1 );
		$this->storage->enqueue( $queueName, $message2 );
		$this->storage->enqueue( $queueName, $message3 );

		$expectedMessages = [
			$message1,
			$message2,
			$message3,
		];

		$this->assertEquals(
			$message1,
			$this->storage->getUndispatched( $queueName )->current()
		);

		$this->assertEquals(
			$expectedMessages,
			iterator_to_array( $this->storage->getUndispatched( $queueName, 3 ) )
		);
	}

	public function testCanFlushAQueue() : void
	{
		$queueName = $this->getQueueName( 'TestQueue' );
		$message1  = $this->getMessage( 'unit-test' );
		$message2  = $this->getMessage( 'test-unit' );
		$message3  = $this->getMessage( 'last' );

		$this->storage->enqueue( $queueName, $message1 );
		$this->storage->enqueue( $queueName, $message2 );
		$this->storage->enqueue( $queueName, $message3 );

		$messages = iterator_to_array( $this->storage->getUndispatched( $queueName, 3 ) );

		$this->assertCount( 3, $messages );

		$this->storage->flushQueue( $queueName );

		$messages = iterator_to_array( $this->storage->getUndispatched( $queueName, 3 ) );

		$this->assertCount( 0, $messages );
	}

	public function testCanFlushAllQueues() : void
	{
		$queueName1 = $this->getQueueName( 'TestQueue1' );
		$queueName2 = $this->getQueueName( 'TestQueue2' );
		$message1   = $this->getMessage( 'unit-test' );
		$message2   = $this->getMessage( 'test-unit' );
		$message3   = $this->getMessage( 'last' );

		$this->storage->enqueue( $queueName1, $message1 );
		$this->storage->enqueue( $queueName1, $message2 );
		$this->storage->enqueue( $queueName1, $message3 );

		$this->storage->enqueue( $queueName2, $message1 );
		$this->storage->enqueue( $queueName2, $message2 );
		$this->storage->enqueue( $queueName2, $message3 );

		$messages1 = iterator_to_array( $this->storage->getUndispatched( $queueName1, 3 ) );
		$messages2 = iterator_to_array( $this->storage->getUndispatched( $queueName2, 3 ) );

		$this->assertCount( 3, $messages1 );
		$this->assertCount( 3, $messages2 );

		$this->storage->flushAllQueues();

		$messages1 = iterator_to_array( $this->storage->getUndispatched( $queueName1, 3 ) );
		$messages2 = iterator_to_array( $this->storage->getUndispatched( $queueName2, 3 ) );

		$this->assertCount( 0, $messages1 );
		$this->assertCount( 0, $messages2 );
	}

	public function testCanResetAllDispatched() : void
	{
		$queueName1 = $this->getQueueName( 'TestQueue1' );
		$queueName2 = $this->getQueueName( 'TestQueue2' );
		$message1   = $this->getMessage( 'unit-test' );
		$message2   = $this->getMessage( 'test-unit' );
		$message3   = $this->getMessage( 'last' );

		$this->storage->enqueue( $queueName1, $message1 );
		$this->storage->enqueue( $queueName1, $message2 );
		$this->storage->enqueue( $queueName1, $message3 );

		$this->storage->enqueue( $queueName2, $message1 );
		$this->storage->enqueue( $queueName2, $message2 );
		$this->storage->enqueue( $queueName2, $message3 );

		$this->storage->markAsDispached( $queueName1, $message1->getMessageId() );
		$this->storage->markAsDispached( $queueName1, $message2->getMessageId() );
		$this->storage->markAsDispached( $queueName1, $message3->getMessageId() );

		$this->storage->markAsDispached( $queueName2, $message1->getMessageId() );
		$this->storage->markAsDispached( $queueName2, $message2->getMessageId() );
		$this->storage->markAsDispached( $queueName2, $message3->getMessageId() );

		$messages1 = iterator_to_array( $this->storage->getUndispatched( $queueName1, 3 ) );
		$messages2 = iterator_to_array( $this->storage->getUndispatched( $queueName2, 3 ) );

		$this->assertCount( 0, $messages1 );
		$this->assertCount( 0, $messages2 );

		$this->storage->resetAllDispatched();

		$messages1 = iterator_to_array( $this->storage->getUndispatched( $queueName1, 3 ) );
		$messages2 = iterator_to_array( $this->storage->getUndispatched( $queueName2, 3 ) );

		$this->assertCount( 3, $messages1 );
		$this->assertCount( 3, $messages2 );
	}

	public function testCanGetAllUndispatchedGroupedByQueueName() : void
	{
		$queueName1 = $this->getQueueName( 'TestQueue1' );
		$queueName2 = $this->getQueueName( 'TestQueue2' );
		$message1   = $this->getMessage( 'unit-test' );
		$message2   = $this->getMessage( 'test-unit' );
		$message3   = $this->getMessage( 'last' );

		$this->storage->enqueue( $queueName1, $message1 );
		$this->storage->enqueue( $queueName1, $message2 );
		$this->storage->enqueue( $queueName1, $message3 );

		$this->storage->enqueue( $queueName2, $message1 );
		$this->storage->enqueue( $queueName2, $message2 );
		$this->storage->enqueue( $queueName2, $message3 );

		$expectedArray = [
			'TestQueue1' => [
				$message1,
				$message2,
				$message3,
			],
			'TestQueue2' => [
				$message1,
				$message2,
				$message3,
			],
		];

		$actualArray = [];

		foreach ( $this->storage->getAllUndispatchedGroupedByQueueName() as $queueName => $messages )
		{
			$this->assertInstanceOf( IdentifiesQueue::class, $queueName );
			$actualArray[ $queueName->toString() ] = iterator_to_array( $messages );
		}

		$this->assertEquals( $expectedArray, $actualArray );
	}
}
