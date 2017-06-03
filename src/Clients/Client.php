<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Clients;

use PHPMQ\Server\Clients\Exceptions\ClientDisconnectedException;
use PHPMQ\Server\Clients\Exceptions\ClientHasPendingMessagesException;
use PHPMQ\Server\Clients\Exceptions\ReadFailedException;
use PHPMQ\Server\Clients\Exceptions\WriteFailedException;
use PHPMQ\Server\Clients\Interfaces\IdentifiesClient;
use PHPMQ\Server\Clients\Interfaces\ProvidesConsumptionInfo;
use PHPMQ\Server\Endpoint\Constants\SocketShutdownMode;
use PHPMQ\Server\Endpoint\Interfaces\ConsumesMessages;
use PHPMQ\Server\Protocol\Constants\PacketLength;
use PHPMQ\Server\Protocol\Headers\MessageHeader;
use PHPMQ\Server\Protocol\Headers\PacketHeader;
use PHPMQ\Server\Protocol\Interfaces\BuildsMessages;
use PHPMQ\Server\Protocol\Interfaces\CarriesInformation;
use PHPMQ\Server\Protocol\Messages\MessageE2C;

/**
 * Class Client
 * @package PHPMQ\Server\Clients
 */
final class Client implements ConsumesMessages
{
	/** @var IdentifiesClient */
	private $clientId;

	/** @var resource */
	private $socket;

	/** @var BuildsMessages */
	private $messageBuilder;

	/** @var ProvidesConsumptionInfo */
	private $consumptionInfo;

	public function __construct( IdentifiesClient $clientId, $socket, BuildsMessages $messageBuilder )
	{
		$this->clientId        = $clientId;
		$this->socket          = $socket;
		$this->messageBuilder  = $messageBuilder;
		$this->consumptionInfo = new NullConsumptionInfo();
	}

	public function getClientId() : IdentifiesClient
	{
		return $this->clientId;
	}

	public function collectSocket( array &$sockets ) : void
	{
		$sockets[ $this->clientId->toString() ] = $this->socket;
	}

	/**
	 * @throws \PHPMQ\Server\Clients\Exceptions\ClientDisconnectedException
	 * @throws \PHPMQ\Server\Clients\Exceptions\ReadFailedException
	 * @return CarriesInformation
	 */
	public function readMessage() : CarriesInformation
	{
		$buffer = '';
		$bytes  = @socket_recv( $this->socket, $buffer, PacketLength::MESSAGE_HEADER, MSG_WAITALL );

		$this->guardReadBytes( $bytes );
		$this->guardClientIsConnected( $buffer );

		$messageHeader = MessageHeader::fromString( $buffer );
		$packetCount   = $messageHeader->getMessageType()->getPacketCount();

		$packets = [];

		for ( $i = 0; $i < $packetCount; $i++ )
		{
			$buffer = '';
			$bytes  = @socket_recv( $this->socket, $buffer, PacketLength::PACKET_HEADER, MSG_WAITALL );
			$this->guardReadBytes( $bytes );
			$this->guardClientIsConnected( $buffer );

			$packetHeader = PacketHeader::fromString( $buffer );

			$buffer = '';
			$bytes  = @socket_recv( $this->socket, $buffer, $packetHeader->getContentLength(), MSG_WAITALL );
			$this->guardReadBytes( $bytes );
			$this->guardClientIsConnected( $buffer );

			$packets[ $packetHeader->getPacketType() ] = $buffer;
		}

		return $this->messageBuilder->buildMessage( $messageHeader, $packets );
	}

	/**
	 * @param bool|null|int $bytes
	 *
	 * @throws \PHPMQ\Server\Clients\Exceptions\ReadFailedException
	 */
	private function guardReadBytes( $bytes ) : void
	{
		if ( false === $bytes )
		{
			throw new ReadFailedException(
				'socket_recv() failed; reason: '
				. socket_strerror( socket_last_error( $this->socket ) )
			);
		}
	}

	/**
	 * @param null|string $buffer
	 *
	 * @throws \PHPMQ\Server\Clients\Exceptions\ClientDisconnectedException
	 */
	private function guardClientIsConnected( ?string $buffer ) : void
	{
		if ( null === $buffer )
		{
			throw new ClientDisconnectedException(
				sprintf( 'Client has disconnected. [Client ID: %s]', $this->clientId )
			);
		}
	}

	public function updateConsumptionInfo( ProvidesConsumptionInfo $consumptionInfo ) : void
	{
		if ( count( $this->consumptionInfo->getMessageIds() ) > 0 )
		{
			throw new ClientHasPendingMessagesException( 'Cannot update consumption info.' );
		}

		$this->consumptionInfo = $consumptionInfo;
	}

	public function getConsumptionInfo() : ProvidesConsumptionInfo
	{
		return $this->consumptionInfo;
	}

	/**
	 * @param MessageE2C $message
	 *
	 * @throws \PHPMQ\Server\Clients\Exceptions\WriteFailedException
	 */
	public function consumeMessage( MessageE2C $message ) : void
	{
		$bytes = @socket_write( $this->socket, $message->toString() );

		if ( false === $bytes )
		{
			throw new WriteFailedException( 'Could not write message to client socket.' );
		}

		$this->consumptionInfo->addMessageId( $message->getMessageId() );
	}

	public function shutDown() : void
	{
		socket_shutdown( $this->socket, SocketShutdownMode::READING_WRITING );
	}
}
