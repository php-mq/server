<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\EventHandlers\Interfaces;

use PHPMQ\Server\Clients\Interfaces\IdentifiesClient;
use PHPMQ\Server\Interfaces\IdentifiesMessage;
use PHPMQ\Server\Interfaces\IdentifiesQueue;
use PHPMQ\Server\Monitoring\Types\MonitoringRequest;
use PHPMQ\Server\Storage\Interfaces\ProvidesMessageData;
use PHPMQ\Server\Storage\Interfaces\StoresMessages;

/**
 * Interface CollectsServerMonitoringInfo
 * @package PHPMQ\Server\EventHandlers\Interfaces
 */
interface CollectsServerMonitoringInfo
{
	public function addMonitoringRequest( MonitoringRequest $monitoringRequest ) : void;

	public function removeMonitoringRequest( IdentifiesClient $clientId ) : void;

	public function addConnectedClient( IdentifiesClient $clientId ) : void;

	public function removeConnectedClient( IdentifiesClient $clientId ) : void;

	public function addMessage( IdentifiesQueue $queueName, ProvidesMessageData $message ) : void;

	public function removeMessage( IdentifiesQueue $queueName, IdentifiesMessage $messageId ) : void;

	public function markMessageAsDispatched( IdentifiesQueue $queueName, IdentifiesMessage $messageId ) : void;

	public function markMessageAsUndispatched( IdentifiesQueue $queueName, IdentifiesMessage $messageId ) : void;

	public function flushQueue( IdentifiesQueue $queueName ) : void;

	public function flushAllQueues() : void;

	public static function fromStorage( StoresMessages $storage );
}
