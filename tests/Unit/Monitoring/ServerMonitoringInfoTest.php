<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Monitoring;

use PHPMQ\Server\Monitoring\ServerMonitoringInfo;
use PHPMQ\Server\Monitoring\Types\QueueInfo;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\MessageIdentifierMocking;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\QueueIdentifierMocking;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\StorageMockingSQLite;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\StreamIdentifierMocking;
use PHPMQ\Server\Types\Message;
use PHPUnit\Framework\TestCase;

/**
 * Class ServerMonitoringInfoTest
 * @package PHPMQ\Server\Tests\Unit\Monitoring
 */
final class ServerMonitoringInfoTest extends TestCase
{
	use StorageMockingSQLite;
	use QueueIdentifierMocking;
	use StreamIdentifierMocking;
	use MessageIdentifierMocking;

	public function testCanGetStartTime() : void
	{
		$info              = new ServerMonitoringInfo();
		$expectedStartTime = time();

		$this->assertSame( $expectedStartTime, $info->getStartTime() );
	}

	public function testCanGetCountOfConnectedClients() : void
	{
		$info = new ServerMonitoringInfo();
		$info->addConnectedClient( $this->getStreamId( 'Unit-Test-1' ) );
		$info->addConnectedClient( $this->getStreamId( 'Unit-Test-2' ) );
		$info->addConnectedClient( $this->getStreamId( 'Unit-Test-2' ) );
		$info->addConnectedClient( $this->getStreamId( 'Unit-Test-3' ) );

		$this->assertSame( 3, $info->getConnectedClientsCount() );

		$info->removeConnectedClient( $this->getStreamId( 'Unit-Test-2' ) );

		$this->assertSame( 2, $info->getConnectedClientsCount() );
	}

	public function testCanGetQueueCount() : void
	{
		$info = new ServerMonitoringInfo();

		$this->assertSame( 0, $info->getQueueCount() );

		$info->addMessage(
			$this->getQueueName( 'Test-Queue' ),
			new Message( $this->getMessageId( 'Test-ID' ), 'Unit-Test' )
		);

		$this->assertSame( 1, $info->getQueueCount() );

		$info->addMessage(
			$this->getQueueName( 'Example-Queue' ),
			new Message( $this->getMessageId( 'Test-ID' ), 'Unit-Test' )
		);

		$this->assertSame( 2, $info->getQueueCount() );
	}

	public function testCanGetQueueInfos() : void
	{
		$info         = new ServerMonitoringInfo();
		$testQueue    = $this->getQueueName( 'Test-Queue' );
		$exampleQueue = $this->getQueueName( 'Example-Queue' );
		$messageId    = $this->getMessageId( 'Test-ID' );

		$testMessage    = new Message( $messageId, 'Unit-Test' );
		$exampleMessage = new Message( $messageId, 'Unit-Example' );

		$info->addMessage( $testQueue, $testMessage );
		$info->addMessage( $exampleQueue, $exampleMessage );

		$info->markMessageAsDispatched( $exampleQueue, $messageId );

		$expectedQueueInfos = [
			new QueueInfo(
				$testQueue->toString(),
				[
					$messageId->toString() => [
						'messageId'  => $messageId->toString(),
						'dispatched' => false,
						'size'       => strlen( 'Unit-Test' ),
						'createdAt'  => $testMessage->createdAt(),
					],
				]
			),
			new QueueInfo(
				$exampleQueue->toString(),
				[
					$messageId->toString() => [
						'messageId'  => $messageId->toString(),
						'dispatched' => true,
						'size'       => strlen( 'Unit-Example' ),
						'createdAt'  => $exampleMessage->createdAt(),
					],
				]
			),
		];

		$this->assertEquals( $expectedQueueInfos, iterator_to_array( $info->getQueueInfos() ) );
		$this->assertEquals( $expectedQueueInfos[0], $info->getQueueInfo( $testQueue ) );
		$this->assertEquals( $expectedQueueInfos[1], $info->getQueueInfo( $exampleQueue ) );
	}

	public function testCanGetMaxQueueSize() : void
	{
		$info = new ServerMonitoringInfo();

		$this->assertSame( 0, $info->getMaxQueueSize() );

		$testQueue    = $this->getQueueName( 'Test-Queue' );
		$exampleQueue = $this->getQueueName( 'Example-Queue' );
		$messageId1   = $this->getMessageId( 'Test-ID-1' );
		$messageId2   = $this->getMessageId( 'Test-ID-2' );

		$testMessage     = new Message( $messageId1, 'Unit-Test' );
		$exampleMessage1 = new Message( $messageId1, 'Unit-Example-1' );
		$exampleMessage2 = new Message( $messageId2, 'Unit-Example-2' );

		$info->addMessage( $testQueue, $testMessage );
		$info->addMessage( $exampleQueue, $exampleMessage1 );

		$this->assertSame( 1, $info->getMaxQueueSize() );

		$info->addMessage( $exampleQueue, $exampleMessage2 );

		$this->assertSame( 2, $info->getMaxQueueSize() );
	}

	public function testCanFlushAQueue() : void
	{
		$info = new ServerMonitoringInfo();

		$testQueue    = $this->getQueueName( 'Test-Queue' );
		$exampleQueue = $this->getQueueName( 'Example-Queue' );
		$messageId1   = $this->getMessageId( 'Test-ID-1' );

		$testMessage    = new Message( $messageId1, 'Unit-Test' );
		$exampleMessage = new Message( $messageId1, 'Unit-Example-1' );

		$info->addMessage( $testQueue, $testMessage );
		$info->addMessage( $exampleQueue, $exampleMessage );

		$this->assertSame( 2, $info->getQueueCount() );

		$info->flushQueue( $exampleQueue );

		$this->assertSame( 1, $info->getQueueCount() );

		$info->flushQueue( $testQueue );

		$this->assertSame( 0, $info->getQueueCount() );
	}

	public function testCanFLushAllQueues() : void
	{
		$info = new ServerMonitoringInfo();

		$testQueue    = $this->getQueueName( 'Test-Queue' );
		$exampleQueue = $this->getQueueName( 'Example-Queue' );
		$messageId1   = $this->getMessageId( 'Test-ID-1' );

		$testMessage    = new Message( $messageId1, 'Unit-Test' );
		$exampleMessage = new Message( $messageId1, 'Unit-Example-1' );

		$info->addMessage( $testQueue, $testMessage );
		$info->addMessage( $exampleQueue, $exampleMessage );

		$this->assertSame( 2, $info->getQueueCount() );

		$info->flushAllQueues();

		$this->assertSame( 0, $info->getQueueCount() );
	}

	public function testCanRemoveMessage() : void
	{
		$info = new ServerMonitoringInfo();

		$testQueue    = $this->getQueueName( 'Test-Queue' );
		$exampleQueue = $this->getQueueName( 'Example-Queue' );
		$messageId1   = $this->getMessageId( 'Test-ID-1' );
		$messageId2   = $this->getMessageId( 'Test-ID-2' );

		$testMessage     = new Message( $messageId1, 'Unit-Test' );
		$exampleMessage1 = new Message( $messageId1, 'Unit-Example-1' );
		$exampleMessage2 = new Message( $messageId2, 'Unit-Example-2' );

		$info->addMessage( $testQueue, $testMessage );
		$info->addMessage( $exampleQueue, $exampleMessage1 );
		$info->addMessage( $exampleQueue, $exampleMessage2 );

		$this->assertSame( 2, $info->getQueueCount() );

		$info->removeMessage( $exampleQueue, $exampleMessage1->getMessageId() );

		$this->assertSame( 2, $info->getQueueCount() );

		$info->removeMessage( $exampleQueue, $exampleMessage2->getMessageId() );

		$this->assertSame( 1, $info->getQueueCount() );
	}

	public function testCanMarkMessageAsUndispatched() : void
	{
		$info      = new ServerMonitoringInfo();
		$testQueue = $this->getQueueName( 'Test-Queue' );
		$messageId = $this->getMessageId( 'Test-ID' );

		$testMessage = new Message( $messageId, 'Unit-Test' );

		$info->addMessage( $testQueue, $testMessage );

		$info->markMessageAsDispatched( $testQueue, $messageId );

		$expectedQueueInfo = new QueueInfo(
			$testQueue->toString(),
			[
				$messageId->toString() => [
					'messageId'  => $messageId->toString(),
					'dispatched' => true,
					'size'       => strlen( 'Unit-Test' ),
					'createdAt'  => $testMessage->createdAt(),
				],
			]
		);

		$this->assertEquals( $expectedQueueInfo, $info->getQueueInfo( $testQueue ) );

		$info->markMessageAsUndispatched( $testQueue, $messageId );

		$expectedQueueInfo = new QueueInfo(
			$testQueue->toString(),
			[
				$messageId->toString() => [
					'messageId'  => $messageId->toString(),
					'dispatched' => false,
					'size'       => strlen( 'Unit-Test' ),
					'createdAt'  => $testMessage->createdAt(),
				],
			]
		);

		$this->assertEquals( $expectedQueueInfo, $info->getQueueInfo( $testQueue ) );
	}

	public function testCanConstructFromStorage() : void
	{
		$this->setUpStorage();

		$testQueue    = $this->getQueueName( 'Test-Queue' );
		$exampleQueue = $this->getQueueName( 'Example-Queue' );
		$messageId1   = $this->getMessageId( 'Test-ID-1' );
		$messageId2   = $this->getMessageId( 'Test-ID-2' );

		$testMessage     = new Message( $messageId1, 'Unit-Test' );
		$exampleMessage1 = new Message( $messageId1, 'Unit-Example-1' );
		$exampleMessage2 = new Message( $messageId2, 'Unit-Example-2' );

		$info = ServerMonitoringInfo::fromStorage( $this->messageQueue );
		$this->assertSame( 0, $info->getQueueCount() );
		$this->assertSame( 0, $info->getMaxQueueSize() );
		$this->assertSame( 0, $info->getConnectedClientsCount() );

		$this->messageQueue->enqueue( $testQueue, $testMessage );
		$this->messageQueue->enqueue( $exampleQueue, $exampleMessage1 );
		$this->messageQueue->enqueue( $exampleQueue, $exampleMessage2 );

		$info = ServerMonitoringInfo::fromStorage( $this->messageQueue );
		$this->assertSame( 2, $info->getQueueCount() );
		$this->assertSame( 2, $info->getMaxQueueSize() );
		$this->assertSame( 0, $info->getConnectedClientsCount() );

		$this->tearDownStorage();
	}
}
