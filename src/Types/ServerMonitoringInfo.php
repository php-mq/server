<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Types;

use PHPMQ\Server\Interfaces\CarriesInformation;
use PHPMQ\Server\Interfaces\IdentifiesMessage;
use PHPMQ\Server\Interfaces\IdentifiesQueue;

/**
 * Class ServerMonitoringInfo
 * @package PHPMQ\Server\Types
 */
final class ServerMonitoringInfo
{
	/** @var int */
	private $connectedClientsCount = 0;

	/** @var array */
	private $queueInfos = [];

	public function incrementConnectedClients() : void
	{
		$this->connectedClientsCount++;
	}

	public function decrementConnectedClients() : void
	{
		$this->connectedClientsCount--;
	}

	public function addMessage( IdentifiesQueue $queueName, CarriesInformation $message ) : void
	{
		$qn = $queueName->toString();

		if ( isset( $this->queueInfos[ $qn ] ) )
		{
			$this->queueInfos[ $qn ] = [];
		}

		$this->queueInfos[ $queueName->toString() ][ $message->getMessageId()->toString() ] = sprintf(
			'%s\t%s\t%d Bytes\t%s',
			$message->getMessageId()->toString(),
			'UNDISPATCHED',
			mb_strlen( $message->getContent() ),
			date( 'Y-m-d H:i:s', $message->createdAt() )
		);
	}

	public function removeMessage( IdentifiesQueue $queueName, IdentifiesMessage $messageId ) : void
	{
		if ( isset( $this->queueInfos[ $queueName->toString() ] ) )
		{
			unset( $this->queueInfos[ $queueName->toString() ][ $messageId->toString() ] );
		}
	}

	public function markMessageAsDispatched( IdentifiesQueue $queueName, CarriesInformation $message ) : void
	{
		$this->queueInfos[ $queueName->toString() ][ $message->getMessageId()->toString() ] = sprintf(
			'%s\t%s\t%d Bytes\t%s',
			$message->getMessageId()->toString(),
			'DISPATCHED',
			mb_strlen( $message->getContent() ),
			date( 'Y-m-d H:i:s', $message->createdAt() )
		);
	}

	public function markMessageAsUndispatched( IdentifiesQueue $queueName, CarriesInformation $message ) : void
	{
		$this->queueInfos[ $queueName->toString() ][ $message->getMessageId()->toString() ] = sprintf(
			'%s\t%s\t%d Bytes\t%s',
			$message->getMessageId()->toString(),
			'UNDISPATCHED',
			mb_strlen( $message->getContent() ),
			date( 'Y-m-d H:i:s', $message->createdAt() )
		);
	}

	public function getQueueInfos() : \Generator
	{
		foreach ( $this->queueInfos as $queueName => $messageInfos )
		{
			yield new QueueInfo( $queueName, $messageInfos );
		}
	}
}
