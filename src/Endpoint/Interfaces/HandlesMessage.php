<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Endpoint\Interfaces;

use hollodotme\PHPMQ\Protocol\Interfaces\CarriesInformation;
use hollodotme\PHPMQ\Protocol\Interfaces\IdentifiesMessageType;

/**
 * Interface HandlesMessage
 * @package hollodotme\PHPMQ\Endpoint\Interfaces
 */
interface HandlesMessage
{
	public function acceptsMessageType( IdentifiesMessageType $messageType ) : bool;

	public function handle( CarriesInformation $message ) : void;
}
