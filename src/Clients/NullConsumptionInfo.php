<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Clients;

use PHPMQ\Protocol\Interfaces\IdentifiesMessage;
use PHPMQ\Protocol\Interfaces\IdentifiesQueue;
use PHPMQ\Server\Clients\Interfaces\ProvidesConsumptionInfo;
use PHPMQ\Server\Exceptions\LogicException;
use PHPMQ\Server\Traits\StringRepresenting;

/**
 * Class NullConsumptionInfo
 * @package PHPMQ\Server\Clients
 */
final class NullConsumptionInfo implements ProvidesConsumptionInfo
{
	use StringRepresenting;

	public function canConsume() : bool
	{
		return false;
	}

	public function getMessageCount() : int
	{
		return 0;
	}

	public function getQueueName() : IdentifiesQueue
	{
		return new class implements IdentifiesQueue
		{
			use StringRepresenting;

			public function toString() : string
			{
				return '';
			}

			public function equals( IdentifiesQueue $other ) : bool
			{
				return false;
			}
		};
	}

	public function addMessageId( IdentifiesMessage $messageId ) : void
	{
		throw new LogicException( 'Cannot add message ID to ' . __CLASS__ );
	}

	public function removeMessageId( IdentifiesMessage $messageId ) : void
	{
		throw new LogicException( 'Cannot remove message ID from ' . __CLASS__ );
	}

	public function getMessageIds() : array
	{
		return [];
	}

	public function toString() : string
	{
		return __CLASS__;
	}
}
