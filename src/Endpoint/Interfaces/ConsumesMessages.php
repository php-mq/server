<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint\Interfaces;

use PHPMQ\Server\Interfaces\IdentifiesMessage;
use PHPMQ\Server\Interfaces\IdentifiesQueue;
use PHPMQ\Server\Protocol\Messages\MessageE2C;

/**
 * Interface ConsumesMessages
 * @package PHPMQ\Server\Endpoint\Interfaces
 */
interface ConsumesMessages
{
	public function updateConsumptionInfo( IdentifiesQueue $queue, int $messageCount ) : void;

	public function canConsumeMessages() : bool;

	public function getConsumptionMessageCount() : int;

	public function getConsumptionQueueName() : IdentifiesQueue;

	public function consumeMessage( MessageE2C $message ) : void;

	public function acknowledgeMessage( IdentifiesQueue $queueName, IdentifiesMessage $messageId ) : void;
}
