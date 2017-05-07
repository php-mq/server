<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Clients\Interfaces;

use PHPMQ\Server\Clients\Client;
use Psr\Log\LoggerAwareInterface;

/**
 * Interface HandlesClientDisconnection
 * @package PHPMQ\Server\Clients\Interfaces
 */
interface HandlesClientDisconnect extends LoggerAwareInterface
{
	public function handleDisconnect( Client $client ) : void;
}
