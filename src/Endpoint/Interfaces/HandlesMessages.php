<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint\Interfaces;

use PHPMQ\Server\Clients\Client;
use PHPMQ\Server\Protocol\Interfaces\CarriesInformation;

/**
 * Class MessageHandler
 * @package PHPMQ\Server\Endpoint
 */
interface HandlesMessages
{
	public function handle( CarriesInformation $message, Client $client ) : void;
}
