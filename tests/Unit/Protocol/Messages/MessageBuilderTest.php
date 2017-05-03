<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Tests\Unit\Protocol\Messages;

use hollodotme\PHPMQ\Protocol\Constants\PacketType;
use hollodotme\PHPMQ\Protocol\Constants\ProtocolVersion;
use hollodotme\PHPMQ\Protocol\Interfaces\CarriesInformation;
use hollodotme\PHPMQ\Protocol\MessageHeader;
use hollodotme\PHPMQ\Protocol\Messages\Acknowledgement;
use hollodotme\PHPMQ\Protocol\Messages\ConsumeRequest;
use hollodotme\PHPMQ\Protocol\Messages\MessageBuilder;
use hollodotme\PHPMQ\Protocol\Messages\MessageC2E;
use hollodotme\PHPMQ\Protocol\Messages\MessageE2C;
use hollodotme\PHPMQ\Protocol\Types\MessageType;
use hollodotme\PHPMQ\Types\MessageId;
use PHPUnit\Framework\TestCase;

/**
 * Class MessageBuilderTest
 * @package hollodotme\PHPMQ\Tests\Unit\Protocol\Messages
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

		$this->assertInstanceOf( CarriesInformation::class, $message );
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

		$this->assertInstanceOf( CarriesInformation::class, $message );
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

		$this->assertInstanceOf( CarriesInformation::class, $message );
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

		$this->assertInstanceOf( CarriesInformation::class, $message );
		$this->assertInstanceOf( Acknowledgement::class, $message );

		$this->assertSame( 'Test-Queue', $message->getQueueName()->toString() );
		$this->assertSame( $messageId->toString(), $message->getMessageId()->toString() );
		$this->assertSame( MessageType::ACKNOWLEDGEMENT, $message->getMessageType()->getType() );
	}

	/**
	 * @expectedException \hollodotme\phpmq\Exceptions\RuntimeException
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
	 * @expectedException \hollodotme\PHPMQ\Exceptions\LogicException
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
