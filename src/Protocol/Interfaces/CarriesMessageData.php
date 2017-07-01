<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Protocol\Interfaces;

use PHPMQ\Server\Interfaces\RepresentsString;

/**
 * Interface CarriesMessageData
 * @package PHPMQ\Server\Protocol\Interfaces
 */
interface CarriesMessageData extends RepresentsString
{
	public function getMessageType() : IdentifiesMessageType;
}
