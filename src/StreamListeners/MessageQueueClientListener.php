<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\StreamListeners;

use PHPMQ\Server\Clients\Exceptions\ClientDisconnectedException;
use PHPMQ\Server\Endpoint\Interfaces\ListensForStreamActivity;
use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
use PHPMQ\Server\Endpoint\Interfaces\TransfersData;
use PHPMQ\Server\Events\MessageQueue\ClientDisconnected;
use PHPMQ\Server\Events\MessageQueue\ClientSentAcknowledgement;
use PHPMQ\Server\Events\MessageQueue\ClientSentConsumeResquest;
use PHPMQ\Server\Events\MessageQueue\ClientSentMessageC2E;
use PHPMQ\Server\Interfaces\CarriesEventData;
use PHPMQ\Server\Interfaces\PublishesEvents;
use PHPMQ\Server\Protocol\Constants\PacketLength;
use PHPMQ\Server\Protocol\Headers\MessageHeader;
use PHPMQ\Server\Protocol\Headers\PacketHeader;
use PHPMQ\Server\Protocol\Interfaces\BuildsMessages;
use PHPMQ\Server\Protocol\Interfaces\CarriesMessageData;
use PHPMQ\Server\Protocol\Messages\Acknowledgement;
use PHPMQ\Server\Protocol\Messages\ConsumeRequest;
use PHPMQ\Server\Protocol\Messages\MessageBuilder;
use PHPMQ\Server\Protocol\Messages\MessageC2E;
use PHPMQ\Server\Protocol\Types\MessageType;
use PHPMQ\Server\StreamListeners\Exceptions\InvalidMessageTypeReceivedException;
use PHPMQ\Server\Streams\Constants\ChunkSize;
use PHPMQ\Server\Streams\Exceptions\ReadTimedOutException;
use Psr\Log\LoggerAwareTrait;

/**
 * Class MessageQueueClientListener
 * @package PHPMQ\Server\StreamListeners
 */
final class MessageQueueClientListener implements ListensForStreamActivity
{
	use LoggerAwareTrait;

	/** @var BuildsMessages */
	private $messageBuilder;

	/** @var PublishesEvents */
	private $eventBus;

	public function __construct( PublishesEvents $evenBus )
	{
		$this->eventBus       = $evenBus;
		$this->messageBuilder = new MessageBuilder();
	}

	/**
	 * @param TransfersData $stream
	 * @param TracksStreams $loop
	 *
	 * @throws \PHPMQ\Server\StreamListeners\Exceptions\InvalidMessageTypeReceivedException
	 */
	public function handleStreamActivity( TransfersData $stream, TracksStreams $loop ) : void
	{
		try
		{
			$this->readMessages( $stream, $loop );
		}
		catch ( ReadTimedOutException | ClientDisconnectedException $e )
		{
			$this->eventBus->publishEvent( new ClientDisconnected( $stream ) );

			$loop->removeStream( $stream );
		}
	}

	/**
	 * @param TransfersData $stream
	 * @param TracksStreams $loop
	 *
	 * @throws \PHPMQ\Server\Clients\Exceptions\ClientDisconnectedException
	 * @throws \PHPMQ\Server\StreamListeners\Exceptions\InvalidMessageTypeReceivedException
	 */
	private function readMessages( TransfersData $stream, TracksStreams $loop ) : void
	{
		do
		{
			$headerBytes = $stream->read( PacketLength::MESSAGE_HEADER );
			$this->guardReadBytes( $headerBytes );

			$messageHeader = MessageHeader::fromString( $headerBytes );

			$message      = $this->readMessage( $messageHeader, $stream );
			$messageEvent = $this->createMessageEvent( $message, $stream, $loop );

			$this->eventBus->publishEvent( $messageEvent );
		}
		while ( $stream->hasUnreadBytes() );
	}

	/**
	 * @param bool|null|int $bytes
	 *
	 * @throws \PHPMQ\Server\Clients\Exceptions\ClientDisconnectedException
	 */
	private function guardReadBytes( $bytes ) : void
	{
		if ( !$bytes )
		{
			throw new ClientDisconnectedException( 'Message queue client disconnected.' );
		}
	}

	/**
	 * @param MessageHeader $messageHeader
	 * @param TransfersData $stream
	 *
	 * @throws \PHPMQ\Server\Clients\Exceptions\ClientDisconnectedException
	 * @return CarriesMessageData
	 */
	private function readMessage( MessageHeader $messageHeader, TransfersData $stream ) : CarriesMessageData
	{
		$packetCount = $messageHeader->getMessageType()->getPacketCount();

		$packets = [];

		for ( $i = 0; $i < $packetCount; $i++ )
		{
			$bytes = $stream->readChunked( PacketLength::PACKET_HEADER, ChunkSize::READ );

			$packetHeader = PacketHeader::fromString( $bytes );

			$bytes = $stream->readChunked( $packetHeader->getContentLength(), ChunkSize::READ );

			$packets[ $packetHeader->getPacketType() ] = $bytes;
		}

		return $this->messageBuilder->buildMessage( $messageHeader, $packets );
	}

	/**
	 * @param CarriesMessageData $message
	 * @param TransfersData      $stream
	 * @param TracksStreams      $loop
	 *
	 * @throws \PHPMQ\Server\StreamListeners\Exceptions\InvalidMessageTypeReceivedException
	 * @return CarriesEventData
	 */
	private function createMessageEvent(
		CarriesMessageData $message,
		TransfersData $stream,
		TracksStreams $loop
	) : CarriesEventData
	{
		$messageType = $message->getMessageType()->getType();

		switch ( $messageType )
		{
			case MessageType::MESSAGE_C2E:
				/** @var MessageC2E $message */
				return new ClientSentMessageC2E( $message, $stream, $loop );

			case MessageType::CONSUME_REQUEST:
				/** @var ConsumeRequest $message */
				return new ClientSentConsumeResquest( $message, $stream, $loop );

			case MessageType::ACKNOWLEDGEMENT:
				/** @var Acknowledgement $message */
				return new ClientSentAcknowledgement( $message, $stream, $loop );

			default:
				throw new InvalidMessageTypeReceivedException( 'Unknown message type: ' . $messageType );
		}
	}
}
