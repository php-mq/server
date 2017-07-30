<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Client\Tests\Unit\Builders;

use PHPMQ\Protocol\Constants\PacketType;
use PHPMQ\Protocol\Constants\ProtocolVersion;
use PHPMQ\Protocol\Interfaces\DefinesMessage;
use PHPMQ\Protocol\Messages\Acknowledgement;
use PHPMQ\Protocol\Messages\ConsumeRequest;
use PHPMQ\Protocol\Messages\MessageClientToServer;
use PHPMQ\Protocol\Types\MessageType;
use PHPMQ\Server\Builders\MessageBuilder;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\MessageIdentifierMocking;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\QueueIdentifierMocking;
use PHPUnit\Framework\TestCase;

/**
 * Class MessageBuilderTest
 * @package PHPMQ\Client\Tests\Unit\Builders
 */
final class MessageBuilderTest extends TestCase
{
	use QueueIdentifierMocking;
	use MessageIdentifierMocking;

	public function testCanBuildMessageClientToServer() : void
	{
		$builder       = new MessageBuilder();
		$queueName     = $this->getQueueName( 'Unit-Test-Queue' );
		$messageType   = new MessageType( MessageType::MESSAGE_CLIENT_TO_SERVER );
		$messageHeader = $this->getMockBuilder( DefinesMessage::class )->getMockForAbstractClass();
		$messageHeader->expects( $this->any() )->method( 'getMessageType' )->willReturn( $messageType );
		$messageHeader->expects( $this->any() )->method( 'getProtocolVersion' )->willReturn(
			ProtocolVersion::VERSION_1
		);

		$packets = [
			PacketType::QUEUE_NAME      => $queueName->toString(),
			PacketType::MESSAGE_CONTENT => 'Unit-Test',
		];

		/** @var DefinesMessage $messageHeader */
		/** @var MessageClientToServer $message */
		$message = $builder->buildMessage( $messageHeader, $packets );

		$this->assertInstanceOf( MessageClientToServer::class, $message );
		$this->assertEquals( $messageType, $message->getMessageType() );
		$this->assertTrue( $queueName->equals( $message->getQueueName() ) );
		$this->assertSame( 'Unit-Test', $message->getContent() );
	}

	public function testCanBuildConsumeRequest() : void
	{
		$builder       = new MessageBuilder();
		$queueName     = $this->getQueueName( 'Unit-Test-Queue' );
		$messageType   = new MessageType( MessageType::CONSUME_REQUEST );
		$messageHeader = $this->getMockBuilder( DefinesMessage::class )->getMockForAbstractClass();
		$messageHeader->expects( $this->any() )->method( 'getMessageType' )->willReturn( $messageType );
		$messageHeader->expects( $this->any() )->method( 'getProtocolVersion' )->willReturn(
			ProtocolVersion::VERSION_1
		);

		$packets = [
			PacketType::QUEUE_NAME            => $queueName->toString(),
			PacketType::MESSAGE_CONSUME_COUNT => '5',
		];

		/** @var DefinesMessage $messageHeader */
		/** @var ConsumeRequest $message */
		$message = $builder->buildMessage( $messageHeader, $packets );

		$this->assertInstanceOf( ConsumeRequest::class, $message );
		$this->assertEquals( $messageType, $message->getMessageType() );
		$this->assertTrue( $queueName->equals( $message->getQueueName() ) );
		$this->assertSame( 5, $message->getMessageCount() );
	}

	public function testCanBuildAcknowledgement() : void
	{
		$builder       = new MessageBuilder();
		$queueName     = $this->getQueueName( 'Unit-Test-Queue' );
		$messageId     = $this->getMessageId( 'Unit-Test-ID' );
		$messageType   = new MessageType( MessageType::ACKNOWLEDGEMENT );
		$messageHeader = $this->getMockBuilder( DefinesMessage::class )->getMockForAbstractClass();
		$messageHeader->expects( $this->any() )->method( 'getMessageType' )->willReturn( $messageType );
		$messageHeader->expects( $this->any() )->method( 'getProtocolVersion' )->willReturn(
			ProtocolVersion::VERSION_1
		);

		$packets = [
			PacketType::QUEUE_NAME => $queueName->toString(),
			PacketType::MESSAGE_ID => $messageId->toString(),
		];

		/** @var DefinesMessage $messageHeader */
		/** @var Acknowledgement $message */
		$message = $builder->buildMessage( $messageHeader, $packets );

		$this->assertInstanceOf( Acknowledgement::class, $message );
		$this->assertEquals( $messageType, $message->getMessageType() );
		$this->assertTrue( $queueName->equals( $message->getQueueName() ) );
		$this->assertTrue( $messageId->equals( $message->getMessageId() ) );
	}

	/**
	 * @expectedException \PHPMQ\Server\Builders\Exceptions\MessageTypeNotImplementedException
	 */
	public function testNotImplementedMessageTypeThrowsException() : void
	{
		$builder       = new MessageBuilder();
		$messageType   = new MessageType( 99999 );
		$messageHeader = $this->getMockBuilder( DefinesMessage::class )->getMockForAbstractClass();
		$messageHeader->expects( $this->any() )->method( 'getMessageType' )->willReturn( $messageType );
		$messageHeader->expects( $this->any() )->method( 'getProtocolVersion' )->willReturn(
			ProtocolVersion::VERSION_1
		);

		/** @var DefinesMessage $messageHeader */
		$builder->buildMessage( $messageHeader, [] );
	}

	/**
	 * @expectedException \PHPMQ\Server\Builders\Exceptions\PacketCountMismatchException
	 */
	public function testPacketCountMismatchThrowsException() : void
	{
		$builder       = new MessageBuilder();
		$messageType   = new MessageType( MessageType::MESSAGE_CLIENT_TO_SERVER );
		$messageHeader = $this->getMockBuilder( DefinesMessage::class )->getMockForAbstractClass();
		$messageHeader->expects( $this->any() )->method( 'getMessageType' )->willReturn( $messageType );
		$messageHeader->expects( $this->any() )->method( 'getProtocolVersion' )->willReturn(
			ProtocolVersion::VERSION_1
		);

		/** @var DefinesMessage $messageHeader */
		$builder->buildMessage( $messageHeader, [] );
	}
}
