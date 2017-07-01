<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Clients;

use PHPMQ\Server\Clients\Exceptions\ClientDisconnectedException;
use PHPMQ\Server\Clients\Exceptions\ClientHasPendingMessagesException;
use PHPMQ\Server\Clients\Interfaces\IdentifiesClient;
use PHPMQ\Server\Clients\Interfaces\ProvidesConsumptionInfo;
use PHPMQ\Server\Protocol\Constants\PacketLength;
use PHPMQ\Server\Protocol\Headers\MessageHeader;
use PHPMQ\Server\Protocol\Headers\PacketHeader;
use PHPMQ\Server\Protocol\Interfaces\CarriesMessageData;
use PHPMQ\Server\Protocol\Messages\MessageBuilder;
use PHPMQ\Server\Protocol\Messages\MessageE2C;

/**
 * Class MessageQueueClient
 * @package PHPMQ\Server\Clients
 */
final class MessageQueueClient extends AbstractClient
{
	/** @var ProvidesConsumptionInfo */
	private $consumptionInfo;

	public function __construct( IdentifiesClient $clientId, $socket )
	{
		parent::__construct( $clientId, $socket );
		$this->consumptionInfo = new NullConsumptionInfo();
	}

	/**
	 * @param MessageBuilder $messageBuilder
	 *
	 * @throws \PHPMQ\Server\Clients\Exceptions\ClientDisconnectedException
	 * @return CarriesMessageData
	 */
	public function readMessage( MessageBuilder $messageBuilder ) : CarriesMessageData
	{
		$bytes = $this->read( PacketLength::MESSAGE_HEADER );
		$this->guardReadBytes( $bytes );

		$messageHeader = MessageHeader::fromString( $bytes );
		$packetCount   = $messageHeader->getMessageType()->getPacketCount();

		$packets = [];

		for ( $i = 0; $i < $packetCount; $i++ )
		{
			$bytes = $this->read( PacketLength::PACKET_HEADER );
			$this->guardReadBytes( $bytes );

			$packetHeader = PacketHeader::fromString( $bytes );

			$bytes = $this->read( $packetHeader->getContentLength() );
			$this->guardReadBytes( $bytes );

			$packets[ $packetHeader->getPacketType() ] = $bytes;
		}

		return $messageBuilder->buildMessage( $messageHeader, $packets );
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
				sprintf( 'MessageQueueClient has disconnected. [MessageQueueClient ID: %s]', $this->getClientId() )
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
	 * @throws \PHPMQ\Server\Clients\Exceptions\ClientDisconnectedException
	 */
	public function consumeMessage( MessageE2C $message ) : void
	{
		$bytes = $this->write( $message->toString() );

		if ( 0 === $bytes )
		{
			throw new ClientDisconnectedException(
				sprintf( 'MessageQueueClient has disconnected. [MessageQueueClient ID: %s]', $this->getClientId() )
			);
		}

		$this->consumptionInfo->addMessageId( $message->getMessageId() );
	}
}
