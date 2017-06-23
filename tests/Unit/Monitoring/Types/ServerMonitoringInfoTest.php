<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Monitoring\Types;

use PHPMQ\Server\Interfaces\CarriesInformation;
use PHPMQ\Server\Interfaces\IdentifiesMessage;
use PHPMQ\Server\Interfaces\IdentifiesQueue;
use PHPMQ\Server\Monitoring\Types\ServerMonitoringInfo;
use PHPMQ\Server\Types\QueueInfo;
use PHPUnit\Framework\TestCase;

/**
 * Class ServerMonitoringInfoTest
 * @package PHPMQ\MessageQueueServer\Tests\Unit\Monitoring\Types
 */
final class ServerMonitoringInfoTest extends TestCase
{
	public function testCanIncrementConnectedClients(): void
	{
		$info = new ServerMonitoringInfo();

		$this->assertSame( 0, $info->getConnectedClientsCount() );

		$info->incrementConnectedClients();

		$this->assertSame( 1, $info->getConnectedClientsCount() );

		$info->incrementConnectedClients();

		$this->assertSame( 2, $info->getConnectedClientsCount() );
	}

	public function testCanDecrementConnectedClients(): void
	{
		$info = new ServerMonitoringInfo();

		$this->assertSame( 0, $info->getConnectedClientsCount() );

		$info->decrementConnectedClients();

		$this->assertSame( 0, $info->getConnectedClientsCount() );

		$info->incrementConnectedClients();
		$info->incrementConnectedClients();

		$this->assertSame( 2, $info->getConnectedClientsCount() );

		$info->decrementConnectedClients();

		$this->assertSame( 1, $info->getConnectedClientsCount() );

		$info->decrementConnectedClients();

		$this->assertSame( 0, $info->getConnectedClientsCount() );
	}

	public function testCanAddMessage(): void
	{
		$info = new ServerMonitoringInfo();

		/** @var IdentifiesQueue $queueName */
		$queueName = $this->getQueueNameMock( 'Test-Queue' );
		/** @var CarriesInformation $message */
		$message = $this->getMessageMock( 'Unit-Test-ID', 'Unit-Test', 1234567890 );

		$info->addMessage( $queueName, $message );

		$expectedQueueInfos = [
			new QueueInfo(
				'Test-Queue',
				[
					'Unit-Test-ID' => [
						'messageId'  => 'Unit-Test-ID',
						'dispatched' => false,
						'size'       => 9,
						'createdAt'  => 1234567890,
					],
				]
			),
		];

		$queueInfos = iterator_to_array( $info->getQueueInfos() );

		$this->assertEquals( $expectedQueueInfos, $queueInfos );
	}

	private function getQueueNameMock( string $name ): \PHPUnit_Framework_MockObject_MockObject
	{
		$queueName = $this->getMockBuilder( IdentifiesQueue::class )
		                  ->setMethods( [ 'toString' ] )
		                  ->getMockForAbstractClass();
		$queueName->expects( $this->any() )
		          ->method( 'toString' )
		          ->willReturn( $name );

		return $queueName;
	}

	private function getMessageMock(
		string $msgId,
		string $content,
		int $createdAt
	): \PHPUnit_Framework_MockObject_MockObject
	{
		$messageId = $this->getMessageIdMock( $msgId );

		$message = $this->getMockBuilder( CarriesInformation::class )
		                ->setMethods( [ 'getMessageId', 'getContent', 'createdAt' ] )
		                ->getMockForAbstractClass();
		$message->expects( $this->any() )
		        ->method( 'getMessageId' )
		        ->willReturn( $messageId );
		$message->expects( $this->any() )
		        ->method( 'getContent' )
		        ->willReturn( $content );
		$message->expects( $this->any() )
		        ->method( 'createdAt' )
		        ->willReturn( $createdAt );

		return $message;
	}

	private function getMessageIdMock( string $msgId ): \PHPUnit_Framework_MockObject_MockObject
	{
		$messageId = $this->getMockBuilder( IdentifiesMessage::class )
		                  ->setMethods( [ 'toString' ] )
		                  ->getMockForAbstractClass();
		$messageId->expects( $this->any() )
		          ->method( 'toString' )
		          ->willReturn( $msgId );

		return $messageId;
	}

	public function testCanRemoveMessage(): void
	{
		$info = new ServerMonitoringInfo();

		/** @var IdentifiesQueue $queueName */
		$queueName = $this->getQueueNameMock( 'Test-Queue' );
		/** @var IdentifiesMessage $messageId */
		$messageId = $this->getMessageIdMock( 'Unit-Test-ID' );
		/** @var CarriesInformation $message */
		$message = $this->getMessageMock( 'Unit-Test-ID', 'Unit-Test', 1234567890 );

		$info->addMessage( $queueName, $message );

		$queueInfos = iterator_to_array( $info->getQueueInfos() );

		$this->assertCount( 1, $queueInfos );

		$info->removeMessage( $queueName, $messageId );

		$queueInfos = iterator_to_array( $info->getQueueInfos() );

		$this->assertCount( 0, $queueInfos );
	}

	public function testCanMarkMessageAsDispatched(): void
	{
		$info = new ServerMonitoringInfo();

		/** @var IdentifiesQueue $queueName */
		$queueName = $this->getQueueNameMock( 'Test-Queue' );
		/** @var IdentifiesMessage $messageId */
		$messageId = $this->getMessageIdMock( 'Unit-Test-ID' );
		/** @var CarriesInformation $message */
		$message = $this->getMessageMock( 'Unit-Test-ID', 'Unit-Test', 1234567890 );

		$info->addMessage( $queueName, $message );

		$info->markMessageAsDispatched( $queueName, $messageId );

		$expectedQueueInfos = [
			new QueueInfo(
				'Test-Queue',
				[
					'Unit-Test-ID' => [
						'messageId'  => 'Unit-Test-ID',
						'dispatched' => true,
						'size'       => 9,
						'createdAt'  => 1234567890,
					],
				]
			),
		];

		$queueInfos = iterator_to_array( $info->getQueueInfos() );

		$this->assertEquals( $expectedQueueInfos, $queueInfos );
	}

	public function testCanMarkMessageAsUndispatched(): void
	{
		$info = new ServerMonitoringInfo();

		/** @var IdentifiesQueue $queueName */
		$queueName = $this->getQueueNameMock( 'Test-Queue' );
		/** @var IdentifiesMessage $messageId */
		$messageId = $this->getMessageIdMock( 'Unit-Test-ID' );
		/** @var CarriesInformation $message */
		$message = $this->getMessageMock( 'Unit-Test-ID', 'Unit-Test', 1234567890 );

		$info->addMessage( $queueName, $message );

		$info->markMessageAsDispatched( $queueName, $messageId );

		$expectedQueueInfos = [
			new QueueInfo(
				'Test-Queue',
				[
					'Unit-Test-ID' => [
						'messageId'  => 'Unit-Test-ID',
						'dispatched' => true,
						'size'       => 9,
						'createdAt'  => 1234567890,
					],
				]
			),
		];

		$queueInfos = iterator_to_array( $info->getQueueInfos() );

		$this->assertEquals( $expectedQueueInfos, $queueInfos );

		$info->markMessageAsUndispatched( $queueName, $messageId );

		$expectedQueueInfos = [
			new QueueInfo(
				'Test-Queue',
				[
					'Unit-Test-ID' => [
						'messageId'  => 'Unit-Test-ID',
						'dispatched' => false,
						'size'       => 9,
						'createdAt'  => 1234567890,
					],
				]
			),
		];

		$queueInfos = iterator_to_array( $info->getQueueInfos() );

		$this->assertEquals( $expectedQueueInfos, $queueInfos );
	}

	public function testCanFlushQueues(): void
	{
		$info = new ServerMonitoringInfo();

		/** @var IdentifiesQueue $queueName1 */
		$queueName1 = $this->getQueueNameMock( 'Test-Queue-1' );
		/** @var IdentifiesQueue $queueName2 */
		$queueName2 = $this->getQueueNameMock( 'Test-Queue-2' );
		/** @var CarriesInformation $message */
		$message = $this->getMessageMock( 'Unit-Test-ID', 'Unit-Test', 1234567890 );

		$info->addMessage( $queueName1, $message );
		$info->addMessage( $queueName2, $message );

		$expectedQueueInfos = [
			new QueueInfo(
				'Test-Queue-1',
				[
					'Unit-Test-ID' => [
						'messageId'  => 'Unit-Test-ID',
						'dispatched' => false,
						'size'       => 9,
						'createdAt'  => 1234567890,
					],
				]
			),
			new QueueInfo(
				'Test-Queue-2',
				[
					'Unit-Test-ID' => [
						'messageId'  => 'Unit-Test-ID',
						'dispatched' => false,
						'size'       => 9,
						'createdAt'  => 1234567890,
					],
				]
			),
		];

		$queueInfos = iterator_to_array( $info->getQueueInfos() );

		$this->assertEquals( $expectedQueueInfos, $queueInfos );

		$info->flushQueue( $queueName1 );

		$queueInfos = iterator_to_array( $info->getQueueInfos() );

		$this->assertEquals( array_slice( $expectedQueueInfos, -1 ), $queueInfos );

		$info->flushAllQueues();

		$queueInfos = iterator_to_array( $info->getQueueInfos() );

		$this->assertCount( 0, $queueInfos );
	}
}
