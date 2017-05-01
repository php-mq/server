<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Endpoint\Interfaces;

use hollodotme\PHPMQ\Interfaces\CarriesInformation;

/**
 * Interface HandlesMessage
 * @package hollodotme\PHPMQ\Endpoint\Interfaces
 */
interface HandlesMessage
{
	public function handle( CarriesInformation $carriesInformation ) : void;
}
