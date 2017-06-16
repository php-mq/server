<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Clients;

use PHPMQ\Server\Clients\Exceptions\ClientDisconnectedException;
use PHPMQ\Server\Clients\Exceptions\ClientHasPendingMessagesException;
use PHPMQ\Server\Clients\Exceptions\WriteFailedException;
use PHPMQ\Server\Clients\Interfaces\IdentifiesClient;
use PHPMQ\Server\Clients\Interfaces\ProvidesConsumptionInfo;
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
	 * @return null|CarriesInformation
	 */
	public function readMessage() : ?CarriesInformation
	{
		$bytes = fread( $this->socket, PacketLength::MESSAGE_HEADER );
		$this->guardReadBytes( $bytes );

		$messageHeader = MessageHeader::fromString( $bytes );
		$packetCount   = $messageHeader->getMessageType()->getPacketCount();

		$packets = [];

		for ( $i = 0; $i < $packetCount; $i++ )
		{
			$bytes = fread( $this->socket, PacketLength::PACKET_HEADER );
			$this->guardReadBytes( $bytes );

			$packetHeader = PacketHeader::fromString( $bytes );

			$bytes = fread( $this->socket, $packetHeader->getContentLength() );
			$this->guardReadBytes( $bytes );

			$packets[ $packetHeader->getPacketType() ] = $bytes;
		}

		return $this->messageBuilder->buildMessage( $messageHeader, $packets );
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
			throw new ClientDisconnectedException(
				sprintf( 'Client has disconnected. [Client ID: %s]', $this->clientId )
			);
		}
	}

	public function hasUnreadData() : bool
	{
		$metaData = stream_get_meta_data( $this->socket );

		return (true !== $metaData['eof'] || $metaData['unread_bytes'] > 0);
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
		$bytes = fwrite( $this->socket, $message->toString() );

		if ( false === $bytes )
		{
			throw new WriteFailedException( 'Could not write message to client socket.' );
		}

		$this->consumptionInfo->addMessageId( $message->getMessageId() );
	}

	public function shutDown() : void
	{
		stream_socket_shutdown( $this->socket, STREAM_SHUT_RDWR );
	}
}
