<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Endpoint\Interfaces;

use hollodotme\PHPMQ\Interfaces\IdentifiesMessage;
use hollodotme\PHPMQ\Protocol\Messages\MessageE2C;

/**
 * Interface ConsumesMessages
 * @package hollodotme\PHPMQ\Endpoint\Interfaces
 */
interface ConsumesMessages
{
	public function updateConsumptionCount( int $messageCount ) : void;

	public function canConsumeMessages() : bool;

	public function getConsumableMessageCount() : int;

	public function consumeMessage( MessageE2C $message ) : void;

	public function acknowledgeMessage( IdentifiesMessage $messageId ) : void;
}
