<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\StreamListeners;

use PHPMQ\Protocol\Constants\PacketLength;
use PHPMQ\Protocol\Interfaces\BuildsMessages;
use PHPMQ\Protocol\Interfaces\DefinesMessage;
use PHPMQ\Protocol\Interfaces\ProvidesMessageData;
use PHPMQ\Protocol\Messages\Acknowledgement;
use PHPMQ\Protocol\Messages\ConsumeRequest;
use PHPMQ\Protocol\Messages\MessageClientToServer;
use PHPMQ\Protocol\Types\MessageHeader;
use PHPMQ\Protocol\Types\MessageType;
use PHPMQ\Protocol\Types\PacketHeader;
use PHPMQ\Server\Clients\Exceptions\ClientDisconnectedException;
use PHPMQ\Server\Endpoint\Interfaces\ListensForStreamActivity;
use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
use PHPMQ\Server\Endpoint\Interfaces\TransfersData;
use PHPMQ\Server\Events\MessageQueue\ClientDisconnected;
use PHPMQ\Server\Events\MessageQueue\ClientSentAcknowledgement;
use PHPMQ\Server\Events\MessageQueue\ClientSentConsumeResquest;
use PHPMQ\Server\Events\MessageQueue\ClientSentMessage;
use PHPMQ\Server\Interfaces\CarriesEventData;
use PHPMQ\Server\Interfaces\PublishesEvents;
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

	public function __construct( PublishesEvents $evenBus, BuildsMessages $messageBuilder )
	{
		$this->eventBus       = $evenBus;
		$this->messageBuilder = $messageBuilder;
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

			$stream->shutDown();
			$stream->close();
			$loop->removeStream( $stream );
		}
	}

	/**
	 * @param TransfersData $stream
	 * @param TracksStreams $loop
	 *
	 * @throws \PHPMQ\Server\Clients\Exceptions\ClientDisconnectedException
	 * @throws \PHPMQ\Server\StreamListeners\Exceptions\InvalidMessageTypeReceivedException
	 * @throws \PHPMQ\Server\Streams\Exceptions\ReadTimedOutException
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
	 * @param DefinesMessage $messageHeader
	 * @param TransfersData  $stream
	 *
	 * @throws \PHPMQ\Server\Clients\Exceptions\ClientDisconnectedException
	 * @throws \PHPMQ\Server\Streams\Exceptions\ReadTimedOutException
	 * @return ProvidesMessageData
	 */
	private function readMessage( DefinesMessage $messageHeader, TransfersData $stream ) : ProvidesMessageData
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
	 * @param ProvidesMessageData $message
	 * @param TransfersData       $stream
	 * @param TracksStreams       $loop
	 *
	 * @throws \PHPMQ\Server\StreamListeners\Exceptions\InvalidMessageTypeReceivedException
	 * @return CarriesEventData
	 */
	private function createMessageEvent(
		ProvidesMessageData $message,
		TransfersData $stream,
		TracksStreams $loop
	) : CarriesEventData
	{
		$messageType = $message->getMessageType()->getType();

		switch ( $messageType )
		{
			case MessageType::MESSAGE_CLIENT_TO_SERVER:
				/** @var MessageClientToServer $message */
				return new ClientSentMessage( $message, $stream, $loop );

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
