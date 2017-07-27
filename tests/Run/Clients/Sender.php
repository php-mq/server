<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Run\Clients;

use PHPMQ\Server\Endpoint\Interfaces\TransfersData;
use PHPMQ\Server\Exceptions\RuntimeException;
use PHPMQ\Server\Interfaces\IdentifiesMessage;
use PHPMQ\Server\Protocol\Constants\PacketLength;
use PHPMQ\Server\Protocol\Headers\MessageHeader;
use PHPMQ\Server\Protocol\Headers\PacketHeader;
use PHPMQ\Server\Protocol\Interfaces\BuildsMessages;
use PHPMQ\Server\Protocol\Interfaces\CarriesMessageData;
use PHPMQ\Server\Protocol\Messages\MessageReceipt;
use PHPMQ\Server\Streams\Constants\ChunkSize;

/**
 * Class Sender
 * @package PHPMQ\Server\Tests\Run\Clients
 */
final class Sender
{
	private const LOOP_WAIT_USEC  = 200000;

	private const RECEIPT_TIMEOUT = 500000;

	/** @var TransfersData */
	private $stream;

	/** @var BuildsMessages */
	private $messageBuilder;

	public function __construct( TransfersData $stream, BuildsMessages $messageBuilder )
	{
		$this->stream         = $stream;
		$this->messageBuilder = $messageBuilder;
	}

	public function writeMessage( CarriesMessageData $message ) : IdentifiesMessage
	{
		$this->stream->writeChunked( $message->toString(), ChunkSize::WRITE );
		$timeout = microtime( true ) + self::RECEIPT_TIMEOUT;

		while ( true )
		{
			if ( $timeout < microtime( true ) )
			{
				break;
			}

			$reads  = [];
			$writes = $excepts = null;
			$this->stream->collectRawStream( $reads );

			usleep( self::LOOP_WAIT_USEC );

			if ( !@stream_select( $reads, $writes, $excepts, 0, self::LOOP_WAIT_USEC ) )
			{
				continue;
			}

			/** @var MessageReceipt $receipt */
			$receipt = $this->readMessageReceipt();

			return $receipt->getMessageId();
		}

		throw new RuntimeException( 'Reading message receipt timed out.' );
	}

	private function readMessageReceipt() : CarriesMessageData
	{
		$bytes         = $this->stream->read( PacketLength::MESSAGE_HEADER );
		$messageHeader = MessageHeader::fromString( $bytes );
		$packets       = [];

		for ( $i = 0; $i < $messageHeader->getMessageType()->getPacketCount(); $i++ )
		{
			$bytes = $this->stream->readChunked( PacketLength::PACKET_HEADER, ChunkSize::READ );

			$packetHeader = PacketHeader::fromString( $bytes );

			$bytes = $this->stream->readChunked( $packetHeader->getContentLength(), ChunkSize::READ );

			$packets[ $packetHeader->getPacketType() ] = $bytes;
		}

		return $this->messageBuilder->buildMessage( $messageHeader, $packets );
	}

	public function disconnect() : void
	{
		$this->stream->shutDown();
		$this->stream->close();
	}
}
