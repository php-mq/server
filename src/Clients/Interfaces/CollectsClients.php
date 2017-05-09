<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Clients\Interfaces;

use PHPMQ\Server\Clients\Client;

/**
 * Interface CollectsClients
 * @package PHPMQ\Server\Clients\Interfaces
 */
interface CollectsClients
{
	public function add( Client $client ) : void;

	public function remove( Client $client ) : void;

	/**
	 * @return array|Client[]
	 */
	public function getActive() : array;

	public function dispatchMessages() : void;
}
