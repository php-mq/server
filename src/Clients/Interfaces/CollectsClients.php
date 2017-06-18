<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Clients\Interfaces;

use PHPMQ\Server\Clients\MessageQueueClient;
use Psr\Log\LoggerAwareInterface;

/**
 * Interface CollectsClients
 * @package PHPMQ\Server\Clients\Interfaces
 */
interface CollectsClients extends LoggerAwareInterface
{
	public function add( MessageQueueClient $client ) : void;

	public function remove( MessageQueueClient $client ) : void;

	/**
	 * @return array|MessageQueueClient[]
	 */
	public function getActive() : array;

	public function dispatchMessages() : void;

	public function shutDown() : void;
}
