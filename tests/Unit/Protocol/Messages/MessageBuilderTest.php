<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Protocol\Messages;

use PHPMQ\Server\Protocol\Constants\PacketType;
use PHPMQ\Server\Protocol\Constants\ProtocolVersion;
use PHPMQ\Server\Protocol\Headers\MessageHeader;
use PHPMQ\Server\Protocol\Interfaces\CarriesMessageData;
use PHPMQ\Server\Protocol\Messages\Acknowledgement;
use PHPMQ\Server\Protocol\Messages\ConsumeRequest;
use PHPMQ\Server\Protocol\Messages\MessageBuilder;
use PHPMQ\Server\Protocol\Messages\MessageC2E;
use PHPMQ\Server\Protocol\Messages\MessageE2C;
use PHPMQ\Server\Protocol\Types\MessageType;
use PHPMQ\Server\Types\MessageId;
use PHPUnit\Framework\TestCase;

/**
 * Class MessageBuilderTest
 * @package PHPMQ\MessageQueueServer\Tests\Unit\Protocol\Messages
 */
final class MessageBuilderTest extends TestCase
{
	public function testCanBuildMessageC2E() : void
	{
		$builder = new MessageBuilder();

		$messageHeader = new MessageHeader(
			ProtocolVersion::VERSION_1,
			new MessageType( MessageType::MESSAGE_C2E )
		);

		$packets = [
			PacketType::QUEUE_NAME      => 'Test-Queue',
			PacketType::MESSAGE_CONTENT => 'Unit-Test',
		];

		/** @var MessageC2E $message */
		$message = $builder->buildMessage( $messageHeader, $packets );

		$this->assertInstanceOf( CarriesMessageData::class, $message );
		$this->assertInstanceOf( MessageC2E::class, $message );

		$this->assertSame( 'Test-Queue', $message->getQueueName()->toString() );
		$this->assertSame( 'Unit-Test', $message->getContent() );
		$this->assertSame( MessageType::MESSAGE_C2E, $message->getMessageType()->getType() );
	}

	public function testCanBuildConsumeRequest() : void
	{
		$builder = new MessageBuilder();

		$messageHeader = new MessageHeader(
			ProtocolVersion::VERSION_1,
			new MessageType( MessageType::CONSUME_REQUEST )
		);

		$packets = [
			PacketType::QUEUE_NAME            => 'Test-Queue',
			PacketType::MESSAGE_CONSUME_COUNT => 5,
		];

		/** @var ConsumeRequest $message */
		$message = $builder->buildMessage( $messageHeader, $packets );

		$this->assertInstanceOf( CarriesMessageData::class, $message );
		$this->assertInstanceOf( ConsumeRequest::class, $message );

		$this->assertSame( 'Test-Queue', $message->getQueueName()->toString() );
		$this->assertSame( 5, $message->getMessageCount() );
		$this->assertSame( MessageType::CONSUME_REQUEST, $message->getMessageType()->getType() );
	}

	public function testCanBuildMessageE2C() : void
	{
		$builder = new MessageBuilder();

		$messageHeader = new MessageHeader(
			ProtocolVersion::VERSION_1,
			new MessageType( MessageType::MESSAGE_E2C )
		);

		$messageId = MessageId::generate();

		$packets = [
			PacketType::QUEUE_NAME      => 'Test-Queue',
			PacketType::MESSAGE_CONTENT => 'Unit-Test',
			PacketType::MESSAGE_ID      => $messageId->toString(),
		];

		/** @var MessageE2C $message */
		$message = $builder->buildMessage( $messageHeader, $packets );

		$this->assertInstanceOf( CarriesMessageData::class, $message );
		$this->assertInstanceOf( MessageE2C::class, $message );

		$this->assertSame( 'Test-Queue', $message->getQueueName()->toString() );
		$this->assertSame( 'Unit-Test', $message->getContent() );
		$this->assertSame( $messageId->toString(), $message->getMessageId()->toString() );
		$this->assertSame( MessageType::MESSAGE_E2C, $message->getMessageType()->getType() );
	}

	public function testCanBuildAcknowledgement() : void
	{
		$builder = new MessageBuilder();

		$messageHeader = new MessageHeader(
			ProtocolVersion::VERSION_1,
			new MessageType( MessageType::ACKNOWLEDGEMENT )
		);

		$messageId = MessageId::generate();

		$packets = [
			PacketType::QUEUE_NAME => 'Test-Queue',
			PacketType::MESSAGE_ID => $messageId->toString(),
		];

		/** @var MessageE2C $message */
		$message = $builder->buildMessage( $messageHeader, $packets );

		$this->assertInstanceOf( CarriesMessageData::class, $message );
		$this->assertInstanceOf( Acknowledgement::class, $message );

		$this->assertSame( 'Test-Queue', $message->getQueueName()->toString() );
		$this->assertSame( $messageId->toString(), $message->getMessageId()->toString() );
		$this->assertSame( MessageType::ACKNOWLEDGEMENT, $message->getMessageType()->getType() );
	}

	/**
	 * @expectedException \PHPMQ\Server\Protocol\Exceptions\MessageTypeNotImplementedException
	 */
	public function testUnknowMessageTypeThrowsException() : void
	{
		$builder = new MessageBuilder();

		$messageHeader = new MessageHeader(
			ProtocolVersion::VERSION_1,
			new MessageType( 123 )
		);

		$builder->buildMessage( $messageHeader, [] );
	}

	/**
	 * @expectedException \PHPMQ\Server\Exceptions\LogicException
	 */
	public function testWrongPacketCountThrowsException() : void
	{
		$builder = new MessageBuilder();

		$messageHeader = new MessageHeader(
			ProtocolVersion::VERSION_1,
			new MessageType( MessageType::MESSAGE_C2E )
		);

		$builder->buildMessage( $messageHeader, [] );
	}
}
