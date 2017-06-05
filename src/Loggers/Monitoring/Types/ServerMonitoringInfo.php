<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Loggers\Monitoring\Types;

use PHPMQ\Server\Interfaces\CarriesInformation;
use PHPMQ\Server\Interfaces\IdentifiesMessage;
use PHPMQ\Server\Interfaces\IdentifiesQueue;
use PHPMQ\Server\Types\QueueInfo;

/**
 * Class ServerMonitoringInfo
 * @package PHPMQ\Server\Loggers\Monitoring\Types
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
		$this->connectedClientsCount = max( 0, $this->connectedClientsCount - 1 );
	}

	public function getConnectedClientsCount() : int
	{
		return $this->connectedClientsCount;
	}

	public function addMessage( IdentifiesQueue $queueName, CarriesInformation $message ) : void
	{
		$qn = $queueName->toString();

		if ( !isset( $this->queueInfos[ $qn ] ) )
		{
			$this->queueInfos[ $qn ] = [];
		}

		$this->queueInfos[ $qn ][ $message->getMessageId()->toString() ] = [
			'messageId'  => $message->getMessageId()->toString(),
			'dispatched' => false,
			'size'       => mb_strlen( $message->getContent() ),
			'createdAt'  => $message->createdAt(),
		];
	}

	public function removeMessage( IdentifiesQueue $queueName, IdentifiesMessage $messageId ) : void
	{
		$qn = $queueName->toString();

		if ( isset( $this->queueInfos[ $qn ] ) )
		{
			unset( $this->queueInfos[ $qn ][ $messageId->toString() ] );
		}

		if ( empty( $this->queueInfos[ $qn ] ) )
		{
			unset( $this->queueInfos[ $qn ] );
		}
	}

	public function markMessageAsDispatched( IdentifiesQueue $queueName, IdentifiesMessage $messageId ) : void
	{
		$this->queueInfos[ $queueName->toString() ][ $messageId->toString() ]['dispatched'] = true;
	}

	public function markMessageAsUndispatched( IdentifiesQueue $queueName, IdentifiesMessage $messageId ) : void
	{
		$this->queueInfos[ $queueName->toString() ][ $messageId->toString() ]['dispatched'] = false;
	}

	public function flushQueue( IdentifiesQueue $queueName ) : void
	{
		unset( $this->queueInfos[ $queueName->toString() ] );
	}

	public function flushAllQueues() : void
	{
		$this->queueInfos = [];
	}

	/**
	 * @return \Generator|QueueInfo[]
	 */
	public function getQueueInfos() : \Generator
	{
		foreach ( $this->queueInfos as $queueName => $messageInfos )
		{
			yield new QueueInfo( $queueName, $messageInfos );
		}
	}
}
