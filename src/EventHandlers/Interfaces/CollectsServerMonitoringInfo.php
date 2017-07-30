<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\EventHandlers\Interfaces;

use PHPMQ\Protocol\Interfaces\IdentifiesMessage;
use PHPMQ\Protocol\Interfaces\IdentifiesQueue;
use PHPMQ\Server\Storage\Interfaces\ProvidesMessageData;
use PHPMQ\Server\Storage\Interfaces\StoresMessages;
use PHPMQ\Stream\Interfaces\IdentifiesStream;

/**
 * Interface CollectsServerMonitoringInfo
 * @package PHPMQ\Server\EventHandlers\Interfaces
 */
interface CollectsServerMonitoringInfo
{
	public function addConnectedClient( IdentifiesStream $streamId ) : void;

	public function removeConnectedClient( IdentifiesStream $streamId ) : void;

	public function addMessage( IdentifiesQueue $queueName, ProvidesMessageData $message ) : void;

	public function removeMessage( IdentifiesQueue $queueName, IdentifiesMessage $messageId ) : void;

	public function markMessageAsDispatched( IdentifiesQueue $queueName, IdentifiesMessage $messageId ) : void;

	public function markMessageAsUndispatched( IdentifiesQueue $queueName, IdentifiesMessage $messageId ) : void;

	public function flushQueue( IdentifiesQueue $queueName ) : void;

	public function flushAllQueues() : void;

	public static function fromStorage( StoresMessages $storage );
}
