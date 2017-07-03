<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\StreamListeners;

use PHPMQ\Server\Clients\Exceptions\ClientDisconnectedException;
use PHPMQ\Server\Endpoint\Exceptions\InvalidMessageTypeReceivedException;
use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
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
use PHPMQ\Server\Timers\TimeoutTimer;

/**
 * Class MessageQueueClientListener
 * @package PHPMQ\Server\StreamListeners
 */
final class MessageQueueClientListener extends AbstractStreamListener
{
	private const CHUNK_SIZE        = 1024;

	private const READ_TIMEOUT_USEC = 500000;

	/** @var BuildsMessages */
	private $messageBuilder;

	/** @var PublishesEvents */
	private $eventBus;

	/** @var TimeoutTimer */
	private $timeoutTimer;

	public function __construct( PublishesEvents $evenBus )
	{
		$this->eventBus       = $evenBus;
		$this->messageBuilder = new MessageBuilder();
		$this->timeoutTimer   = new TimeoutTimer( self::READ_TIMEOUT_USEC );
	}

	/**
	 * @param resource      $stream
	 * @param TracksStreams $loop
	 *
	 * @throws \PHPMQ\Server\Endpoint\Exceptions\InvalidMessageTypeReceivedException
	 */
	protected function handleStreamActivity( $stream, TracksStreams $loop ) : void
	{
		try
		{
			$this->timeoutTimer->reset();
			$this->readMessages( $stream, $loop );
		}
		catch ( ClientDisconnectedException $e )
		{
			$this->eventBus->publishEvent( new ClientDisconnected( $stream ) );

			$loop->removeStream( $stream );
		}
	}

	/**
	 * @param resource      $stream
	 * @param TracksStreams $loop
	 *
	 * @throws \PHPMQ\Server\Clients\Exceptions\ClientDisconnectedException
	 * @throws \PHPMQ\Server\Endpoint\Exceptions\InvalidMessageTypeReceivedException
	 */
	private function readMessages( $stream, TracksStreams $loop ) : void
	{
		do
		{
			$headerBytes = fread( $stream, PacketLength::MESSAGE_HEADER );
			$this->guardReadBytes( $headerBytes );

			$this->timeoutTimer->start();

			$messageHeader = MessageHeader::fromString( $headerBytes );

			$message      = $this->readMessage( $messageHeader, $stream );
			$messageEvent = $this->createMessageEvent( $message, $stream, $loop );

			$this->eventBus->publishEvent( $messageEvent );

			$this->timeoutTimer->reset();

			$metaData = stream_get_meta_data( $stream );
		}
		while ( (int)$metaData['unread_bytes'] > 0 );
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
	 * @param resource      $stream
	 *
	 * @return CarriesMessageData
	 * @throws \PHPMQ\Server\Clients\Exceptions\ClientDisconnectedException
	 */
	private function readMessage( MessageHeader $messageHeader, $stream ) : CarriesMessageData
	{
		$packetCount = $messageHeader->getMessageType()->getPacketCount();

		$packets = [];

		for ( $i = 0; $i < $packetCount; $i++ )
		{
			$bytes = $this->read( $stream, PacketLength::PACKET_HEADER );

			$packetHeader = PacketHeader::fromString( $bytes );

			$bytes = $this->read( $stream, $packetHeader->getContentLength() );

			$packets[ $packetHeader->getPacketType() ] = $bytes;
		}

		return $this->messageBuilder->buildMessage( $messageHeader, $packets );
	}

	/**
	 * @param resource $stream
	 * @param int      $length
	 *
	 * @return string
	 * @throws \PHPMQ\Server\Clients\Exceptions\ClientDisconnectedException
	 */
	private function read( $stream, int $length ) : string
	{
		$buffer      = '';
		$bytesToRead = $length;

		while ( $bytesToRead > 0 )
		{
			$chunkSize = (int)min( $bytesToRead, self::CHUNK_SIZE );
			$bytes     = (string)fread( $stream, $chunkSize );

			$buffer      .= $bytes;
			$bytesToRead -= strlen( $bytes );

			if ( $this->timeoutTimer->timedOut() )
			{
				throw new ClientDisconnectedException( 'Read timed out.' );
			}
		}

		return $buffer;
	}

	/**
	 * @param CarriesMessageData $message
	 * @param resource           $stream
	 * @param TracksStreams      $loop
	 *
	 * @return CarriesEventData
	 * @throws \PHPMQ\Server\Endpoint\Exceptions\InvalidMessageTypeReceivedException
	 */
	private function createMessageEvent( CarriesMessageData $message, $stream, TracksStreams $loop ) : CarriesEventData
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
