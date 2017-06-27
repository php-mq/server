<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Monitoring;

use PHPMQ\Server\Clients\Interfaces\IdentifiesClient;
use PHPMQ\Server\EventHandlers\Interfaces\CollectsServerMonitoringInfo;
use PHPMQ\Server\Interfaces\IdentifiesMessage;
use PHPMQ\Server\Interfaces\IdentifiesQueue;
use PHPMQ\Server\Monitoring\Interfaces\ProvidesServerMonitoringInfo;
use PHPMQ\Server\Monitoring\Types\MonitoringRequest;
use PHPMQ\Server\Monitoring\Types\QueueInfo;
use PHPMQ\Server\Storage\Interfaces\ProvidesMessageData;
use PHPMQ\Server\Storage\Interfaces\StoresMessages;

/**
 * Class ServerMonitoringInfo
 * @package PHPMQ\Server\Monitoring
 */
final class ServerMonitoringInfo implements ProvidesServerMonitoringInfo, CollectsServerMonitoringInfo
{
	/** @var array|MonitoringRequest[] */
	private $monitoringRequests = [];

	/** @var array */
	private $connectedClients = [];

	/** @var array */
	private $queueInfos = [];

	public function addMonitoringRequest( MonitoringRequest $monitoringRequest ) : void
	{
		$clientId = $monitoringRequest->getMaintenanceClient()->getClientId();

		$this->monitoringRequests[ $clientId->toString() ] = $monitoringRequest;
	}

	public function removeMonitoringRequest( IdentifiesClient $clientId ) : void
	{
		unset( $this->monitoringRequests[ $clientId->toString() ] );
	}

	public function hasMonitoringRequests() : bool
	{
		return (count( $this->monitoringRequests ) > 0);
	}

	public function getMonitoringRequests() : array
	{
		return $this->monitoringRequests;
	}

	public function addConnectedClient( IdentifiesClient $clientId ) : void
	{
		$this->connectedClients[ $clientId->toString() ] = true;
	}

	public function removeConnectedClient( IdentifiesClient $clientId ) : void
	{
		unset( $this->connectedClients[ $clientId->toString() ] );
	}

	public function getConnectedClientsCount() : int
	{
		return count( $this->connectedClients );
	}

	public function getQueueCount() : int
	{
		return count( $this->queueInfos );
	}

	public function getMaxQueueSize() : int
	{
		$maxSize = 0;
		foreach ( $this->queueInfos as $messageInfos )
		{
			$maxSize = max( $maxSize, count( $messageInfos ) );
		}

		return $maxSize;
	}

	public function addMessage( IdentifiesQueue $queueName, ProvidesMessageData $message ) : void
	{
		$qn = $queueName->toString();

		if ( !isset( $this->queueInfos[ $qn ] ) )
		{
			$this->queueInfos[ $qn ] = [];
		}

		$this->queueInfos[ $qn ][ $message->getMessageId()->toString() ] = [
			'messageId'  => $message->getMessageId()->toString(),
			'dispatched' => false,
			'size'       => strlen( $message->getContent() ),
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
	 * @return iterable|QueueInfo[]
	 */
	public function getQueueInfos() : iterable
	{
		foreach ( $this->queueInfos as $queueName => $messageInfos )
		{
			yield new QueueInfo( $queueName, $messageInfos );
		}
	}

	public function getQueueInfo( IdentifiesQueue $queueName ) : QueueInfo
	{
		$qn = $queueName->toString();

		return new QueueInfo( $qn, $this->queueInfos[ $qn ] ?? [] );
	}

	public static function fromStorage( StoresMessages $storage ) : self
	{
		$instance = new self();
		$storage->resetAllDispatched();

		foreach ( $storage->getAllUndispatchedGroupedByQueueName() as $queueName => $messages )
		{
			/** @var \Generator $messages */
			foreach ( $messages as $message )
			{
				$instance->addMessage( $queueName, $message );
			}
		}

		return $instance;
	}
}
