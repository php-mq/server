<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Protocol\Interfaces;

use hollodotme\PHPMQ\Protocol\MessageHeader;

/**
 * Interface BuildsMessages
 * @package hollodotme\PHPMQ\Endpoint\Interfaces
 */
interface BuildsMessages
{
	public function buildMessage( MessageHeader $messageHeader, array $packets ) : CarriesInformation;
}
