<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Clients;

use PHPMQ\Protocol\Interfaces\IdentifiesMessage;
use PHPMQ\Protocol\Interfaces\IdentifiesQueue;
use PHPMQ\Server\Clients\Interfaces\ProvidesConsumptionInfo;
use PHPMQ\Server\Traits\StringRepresenting;

/**
 * Class ConsumptionInfo
 * @package PHPMQ\Server\Clients
 */
final class ConsumptionInfo implements ProvidesConsumptionInfo
{
	use StringRepresenting;

	/** @var IdentifiesQueue */
	private $queueName;

	/** @var int */
	private $messageCount;

	/** @var array|IdentifiesMessage[] */
	private $messageIds;

	public function __construct( IdentifiesQueue $queueName, int $messageCount )
	{
		$this->queueName    = $queueName;
		$this->messageCount = $messageCount;
		$this->messageIds   = [];
	}

	public function getQueueName() : IdentifiesQueue
	{
		return $this->queueName;
	}

	public function getMessageCount() : int
	{
		return $this->messageCount - count( $this->messageIds );
	}

	public function addMessageId( IdentifiesMessage $messageId ) : void
	{
		$this->messageIds[ $messageId->toString() ] = $messageId;
	}

	public function removeMessageId( IdentifiesMessage $messageId ) : void
	{
		unset( $this->messageIds[ $messageId->toString() ] );
	}

	/**
	 * @return array|IdentifiesMessage[]
	 */
	public function getMessageIds() : array
	{
		return array_values( $this->messageIds );
	}

	public function canConsume() : bool
	{
		return ($this->messageCount > count( $this->messageIds ));
	}

	public function toString() : string
	{
		return sprintf(
			'Queue name: "%s", Message count: %d, Currently consumed: %d',
			$this->queueName->toString(),
			$this->messageCount,
			count( $this->messageIds )
		);
	}
}
