<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Endpoint\Interfaces;

use hollodotme\PHPMQ\Interfaces\IdentifiesMessage;
use hollodotme\PHPMQ\Interfaces\IdentifiesQueue;
use hollodotme\PHPMQ\Protocol\Messages\MessageE2C;

/**
 * Interface ConsumesMessages
 * @package hollodotme\PHPMQ\Endpoint\Interfaces
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
