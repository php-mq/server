<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint\Interfaces;

use PHPMQ\Server\Protocol\Interfaces\CarriesInformation;
use PHPMQ\Server\Protocol\Interfaces\IdentifiesMessageType;
use Psr\Log\LoggerAwareInterface;

/**
 * Interface HandlesMessage
 * @package PHPMQ\Server\Endpoint\Interfaces
 */
interface HandlesMessage extends LoggerAwareInterface
{
	public function acceptsMessageType( IdentifiesMessageType $messageType ) : bool;

	public function handle( CarriesInformation $message, ConsumesMessages $client ) : void;
}
