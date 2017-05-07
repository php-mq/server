<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\MessageHandlers;

use PHPMQ\Server\Clients\Client;
use PHPMQ\Server\Clients\ConsumptionInfo;
use PHPMQ\Server\Endpoint\Interfaces\ConsumesMessages;
use PHPMQ\Server\Endpoint\Interfaces\HandlesMessage;
use PHPMQ\Server\Protocol\Interfaces\CarriesInformation;
use PHPMQ\Server\Protocol\Interfaces\IdentifiesMessageType;
use PHPMQ\Server\Protocol\Messages\ConsumeRequest;
use PHPMQ\Server\Protocol\Types\MessageType;
use PHPMQ\Server\Storage\Interfaces\StoresMessages;
use Psr\Log\LoggerAwareTrait;

/**
 * Class ConsumeRequestHandler
 * @package PHPMQ\Server\MessageHandlers
 */
final class ConsumeRequestHandler implements HandlesMessage
{
	use LoggerAwareTrait;

	/** @var StoresMessages */
	private $storage;

	public function __construct( StoresMessages $storage )
	{
		$this->storage = $storage;
	}

	public function acceptsMessageType( IdentifiesMessageType $messageType ) : bool
	{
		return ($messageType->getType() === MessageType::CONSUME_REQUEST);
	}

	/**
	 * @param CarriesInformation|ConsumeRequest $message
	 * @param ConsumesMessages|Client           $client
	 */
	public function handle( CarriesInformation $message, ConsumesMessages $client ) : void
	{
		$this->logger->debug( '' );
		$this->logger->debug(
			sprintf(
				'Received %s for queue "%s" with content:',
				get_class( $message ),
				$message->getQueueName()->toString()
			)
		);

		$this->logger->debug( $message->toString() );

		$this->cleanUpClientConsumption( $client );

		$consumptionInfo = new ConsumptionInfo( $message->getQueueName(), $message->getMessageCount() );
		$client->updateConsumptionInfo( $consumptionInfo );

		$this->logger->debug(
			sprintf(
				'√ Updated consumption info of client: %s to %s',
				$client->getClientId()->toString(),
				$client->getConsumptionInfo()->toString()
			)
		);

		$this->logger->debug( '' );
	}

	private function cleanUpClientConsumption( Client $client ) : void
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
