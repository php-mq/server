<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Clients\Interfaces;

use PHPMQ\Protocol\Interfaces\IdentifiesMessage;
use PHPMQ\Protocol\Interfaces\IdentifiesQueue;
use PHPMQ\Server\Interfaces\RepresentsString;

/**
 * Interface ProvidesConsumptionInfo
 * @package PHPMQ\Server\Clients\Interfaces
 */
interface ProvidesConsumptionInfo extends RepresentsString
{
	public function canConsume() : bool;

	public function getMessageCount() : int;

	public function getQueueName() : IdentifiesQueue;

	public function addMessageId( IdentifiesMessage $messageId ) : void;

	public function removeMessageId( IdentifiesMessage $messageId ) : void;

	/**
	 * @return array|IdentifiesMessage[]
	 */
	public function getMessageIds() : array;
}
