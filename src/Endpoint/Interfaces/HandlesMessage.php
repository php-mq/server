<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Endpoint\Interfaces;

use hollodotme\PHPMQ\Protocol\Interfaces\CarriesInformation;
use hollodotme\PHPMQ\Protocol\Interfaces\IdentifiesMessageType;
use Psr\Log\LoggerAwareInterface;

/**
 * Interface HandlesMessage
 * @package hollodotme\PHPMQ\Endpoint\Interfaces
 */
interface HandlesMessage extends LoggerAwareInterface
{
	public function acceptsMessageType( IdentifiesMessageType $messageType ) : bool;

	public function handle( CarriesInformation $message, ConsumesMessages $client ) : void;
}
