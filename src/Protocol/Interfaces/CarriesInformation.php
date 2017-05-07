<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Protocol\Interfaces;

use PHPMQ\Server\Interfaces\RepresentsString;

/**
 * Interface CarriesInformation
 * @package PHPMQ\Server\Protocol\Interfaces
 */
interface CarriesInformation extends RepresentsString
{
	public function getMessageType() : IdentifiesMessageType;
}
