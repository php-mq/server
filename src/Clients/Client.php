<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Clients;

use hollodotme\PHPMQ\Clients\Interfaces\IdentifiesClient;
use hollodotme\PHPMQ\Endpoint\Interfaces\ConsumesMessages;
use hollodotme\PHPMQ\Exceptions\RuntimeException;
use hollodotme\PHPMQ\Interfaces\IdentifiesMessage;
use hollodotme\PHPMQ\Interfaces\IdentifiesQueue;
use hollodotme\PHPMQ\Protocol\Constants\PacketLength;
use hollodotme\PHPMQ\Protocol\Interfaces\BuildsMessages;
use hollodotme\PHPMQ\Protocol\Interfaces\CarriesInformation;
use hollodotme\PHPMQ\Protocol\MessageHeader;
use hollodotme\PHPMQ\Protocol\Messages\MessageE2C;
use hollodotme\PHPMQ\Protocol\PacketHeader;

/**
 * Class Client
 * @package hollodotme\PHPMQ\Clients
 */
final class Client implements ConsumesMessages
{
	/** @var IdentifiesClient */
	private $clientId;

	/** @var resource */
	private $socket;

	/** @var BuildsMessages */
	private $messageBuilder;

	/** @var bool */
	private $isDisconnected;

	/** @var array|IdentifiesMessage */
	private $consumedMessageIds;

	/** @var int */
	private $consumptionMessageCount;

	/** @var IdentifiesQueue */
	private $consumptionQueueName;

	public function __construct( IdentifiesClient $clientId, $socket, BuildsMessages $messageBuilder )
	{
		$this->clientId                = $clientId;
		$this->socket                  = $socket;
		$this->messageBuilder          = $messageBuilder;
		$this->isDisconnected          = false;
		$this->consumedMessageIds      = [];
		$this->consumptionMessageCount = 0;
	}

	public function getClientId() : IdentifiesClient
	{
		return $this->clientId;
	}

	public function collectSocket( array &$sockets ) : void
	{
		$sockets[ $this->clientId->toString() ] = $this->socket;
	}

	public function readMessage() : ?CarriesInformation
	{
		$buffer = '';
		$bytes  = socket_recv( $this->socket, $buffer, PacketLength::MESSAGE_HEADER, MSG_WAITALL );

		$this->guardReadBytes( $bytes );

		if ( $this->hasClientClosedConnection( $buffer ) )
		{
			return null;
		}

		$messageHeader = MessageHeader::fromString( $buffer );
		$packetCount   = $messageHeader->getMessageType()->getPacketCount();

		$packets = [];

		for ( $i = 0; $i < $packetCount; $i++ )
		{
			$buffer = '';
			$bytes  = socket_recv( $this->socket, $buffer, PacketLength::PACKET_HEADER, MSG_WAITALL );
			$this->guardReadBytes( $bytes );

			if ( $this->hasClientClosedConnection( $buffer ) )
			{
				return null;
			}

			$packetHeader = PacketHeader::fromString( $buffer );

			$buffer = '';
			$bytes  = socket_recv( $this->socket, $buffer, $packetHeader->getContentLength(), MSG_WAITALL );
			$this->guardReadBytes( $bytes );

			if ( $this->hasClientClosedConnection( $buffer ) )
			{
				return null;
			}

			$packets[ $packetHeader->getPacketType() ] = $buffer;
		}

		return $this->messageBuilder->buildMessage( $messageHeader, $packets );
	}

	private function guardReadBytes( $bytes ) : void
	{
		if ( false === $bytes )
		{
			throw new RuntimeException(
				'socket_recv() failed; reason: '
				. socket_strerror( socket_last_error( $this->socket ) )
			);
		}
	}

	private function hasClientClosedConnection( ?string $buffer ) : bool
	{
		if ( null === $buffer )
		{
			$this->isDisconnected = true;

			return true;
		}

		return false;
	}

	public function isDisconnected() : bool
	{
		return $this->isDisconnected;
	}

	public function updateConsumptionInfo( IdentifiesQueue $queueName, int $messageCount ) : void
	{
		$this->consumptionQueueName    = $queueName;
		$this->consumptionMessageCount = $messageCount;

		if ( !isset( $this->consumedMessageIds[ $queueName->toString() ] ) )
		{
			$this->consumedMessageIds[ $queueName->toString() ] = [];
		}
	}

	public function canConsumeMessages() : bool
	{
		return ($this->getConsumptionMessageCount() > 0);
	}

	public function getConsumptionMessageCount() : int
	{
		$queueName = '';
		if ( null !== $this->consumptionQueueName )
		{
			$queueName = $this->consumptionQueueName->toString();
		}

		return ($this->consumptionMessageCount - count( $this->consumedMessageIds[ $queueName ] ?? [] ));
	}

	public function getConsumptionQueueName() : IdentifiesQueue
	{
		return $this->consumptionQueueName;
	}

	public function consumeMessage( MessageE2C $message ) : void
	{
		$bytes = socket_write( $this->socket, $message->toString() );

		if ( false === $bytes )
		{
			throw new RuntimeException( 'Could not write message to client socket.' );
		}

		$this->consumedMessageIds[ $message->getQueueName()->toString() ][] = $message->getMessageId();
	}

	public function acknowledgeMessage( IdentifiesQueue $queueName, IdentifiesMessage $messageId ) : void
	{
		$key         = $queueName->toString();
		$consumedIds = $this->consumedMessageIds[ $key ] ?? [];

		$this->consumedMessageIds[ $key ] = array_diff( $consumedIds, [ $messageId ] );
	}
}
