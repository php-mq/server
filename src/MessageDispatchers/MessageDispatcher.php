<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\MessageDispatchers;

use hollodotme\PHPMQ\Endpoint\Interfaces\ConsumesMessages;
use hollodotme\PHPMQ\Endpoint\Interfaces\DispatchesMessages;
use hollodotme\PHPMQ\Protocol\Messages\MessageE2C;
use hollodotme\PHPMQ\Storage\Interfaces\StoresMessages;

/**
 * Class MessageDispatcher
 * @package hollodotme\PHPMQ\MessageDispatchers
 */
final class MessageDispatcher implements DispatchesMessages
{
	/** @var StoresMessages */
	private $storage;

	public function __construct( StoresMessages $storage )
	{
		$this->storage = $storage;
	}

	public function dispatchMessages( ConsumesMessages $client ) : void
	{
		if ( !$client->canConsumeMessages() )
		{
			return;
		}

		$queueName    = $client->getConsumptionQueueName();
		$messageCount = $client->getConsumptionMessageCount();
		$messages     = $this->storage->getUndispatched( $queueName, $messageCount );

		foreach ( $messages as $message )
		{
			$messageE2C = new MessageE2C( $message->getMessageId(), $queueName, $message->getContent() );

			$client->consumeMessage( $messageE2C );

			$this->storage->markAsDispached( $queueName, $message->getMessageId() );
		}
	}
}
