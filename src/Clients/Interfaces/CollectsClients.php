<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Clients\Interfaces;

use PHPMQ\Server\Clients\Client;
use Psr\Log\LoggerAwareInterface;

/**
 * Interface CollectsClients
 * @package PHPMQ\Server\Clients\Interfaces
 */
interface CollectsClients extends LoggerAwareInterface
{
	public function add( Client $client ) : void;

	public function remove( Client $client ) : void;

	/**
	 * @return array|Client[]
	 */
	public function getActive() : array;

	public function dispatchMessages() : void;

	public function shutDown() : void;
}
