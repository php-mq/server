<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint\Interfaces;

use PHPMQ\Server\Clients\MessageQueueClient;
use PHPMQ\Server\Protocol\Interfaces\CarriesInformation;

/**
 * Class MessageQueueMessageHandler
 * @package PHPMQ\Server\Endpoint
 */
interface HandlesMessages
{
	public function handle( CarriesInformation $message, MessageQueueClient $client ) : void;
}
