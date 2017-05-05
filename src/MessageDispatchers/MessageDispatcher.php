<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\MessageDispatchers;

use hollodotme\PHPMQ\Clients\Client;
use hollodotme\PHPMQ\Endpoint\Interfaces\ConsumesMessages;
use hollodotme\PHPMQ\Endpoint\Interfaces\DispatchesMessages;
use hollodotme\PHPMQ\Protocol\Messages\MessageE2C;
use hollodotme\PHPMQ\Storage\Interfaces\StoresMessages;
use Psr\Log\LoggerAwareTrait;

/**
 * Class MessageDispatcher
 * @package hollodotme\PHPMQ\MessageDispatchers
 */
final class MessageDispatcher implements DispatchesMessages
{
	use LoggerAwareTrait;

	/** @var StoresMessages */
	private $storage;

	public function __construct( StoresMessages $storage )
	{
		$this->storage = $storage;
	}

	/**
	 * @param ConsumesMessages|Client $client
	 */
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
			$this->logger->debug( '' );
			$this->logger->debug(
				sprintf(
					"Dispatching messages %s to client %s.",
					$message->getMessageId(),
					$client->getClientId()
				)
			);

			$messageE2C = new MessageE2C( $message->getMessageId(), $queueName, $message->getContent() );

			$client->consumeMessage( $messageE2C );

			$this->logger->debug( '√ Message sent.' );

			$this->storage->markAsDispached( $queueName, $message->getMessageId() );

			$this->logger->debug( '√ Message marked as dispatched.' );
			$this->logger->debug( '' );
		}
	}
}
