<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\DisconnectHandlers;

use PHPMQ\Server\Clients\Client;
use PHPMQ\Server\Clients\Interfaces\HandlesClientDisconnect;
use PHPMQ\Server\Storage\Interfaces\StoresMessages;
use Psr\Log\LoggerAwareTrait;

/**
 * Class ClientDisconnectHandler
 * @package PHPMQ\Server\DisconnectHandlers
 */
final class ClientDisconnectHandler implements HandlesClientDisconnect
{
	use LoggerAwareTrait;

	/** @var StoresMessages */
	private $storage;

	public function __construct( StoresMessages $storage )
	{
		$this->storage = $storage;
	}

	public function handleDisconnect( Client $client ) : void
	{
		$consumptionInfo = $client->getConsumptionInfo();
		$queueName       = $consumptionInfo->getQueueName();
		$messageIds      = $consumptionInfo->getMessageIds();

		foreach ( $messageIds as $messageId )
		{
			$this->storage->markAsUndispatched( $queueName, $messageId );

			$consumptionInfo->removeMessageId( $messageId );
		}
	}
}
