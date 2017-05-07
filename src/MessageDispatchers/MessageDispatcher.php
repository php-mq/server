<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\MessageDispatchers;

use PHPMQ\Server\Clients\Client;
use PHPMQ\Server\Endpoint\Interfaces\ConsumesMessages;
use PHPMQ\Server\Endpoint\Interfaces\DispatchesMessages;
use PHPMQ\Server\Protocol\Messages\MessageE2C;
use PHPMQ\Server\Storage\Interfaces\StoresMessages;
use Psr\Log\LoggerAwareTrait;

/**
 * Class MessageDispatcher
 * @package PHPMQ\Server\MessageDispatchers
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
		$consumptionInfo = $client->getConsumptionInfo();

		if ( !$consumptionInfo->canConsume() )
		{
			return;
		}

		$queueName    = $consumptionInfo->getQueueName();
		$messageCount = $consumptionInfo->getMessageCount();
		$messages     = $this->storage->getUndispatched( $queueName, $messageCount );

		foreach ( $messages as $message )
		{
			$this->logger->debug(
				sprintf(
					'Dispatching messages %s to client %s.',
					$message->getMessageId(),
					$client->getClientId()
				)
			);

			$messageE2C = new MessageE2C( $message->getMessageId(), $queueName, $message->getContent() );

			$client->consumeMessage( $messageE2C );

			$this->logger->debug( '√ Message sent: ' . $message->getMessageId() );

			$this->storage->markAsDispached( $queueName, $message->getMessageId() );

			$this->logger->debug( '√ Message marked as dispatched: ' . $message->getMessageId() );
		}
	}
}
