<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Protocol\Interfaces;

use hollodotme\PHPMQ\Interfaces\RepresentsString;

/**
 * Interface CarriesInformation
 * @package hollodotme\PHPMQ\Protocol\Interfaces
 */
interface CarriesInformation extends RepresentsString
{
	public function getMessageType() : IdentifiesMessageType;
}
