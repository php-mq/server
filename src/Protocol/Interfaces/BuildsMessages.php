<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Protocol\Interfaces;

use PHPMQ\Server\Protocol\MessageHeader;

/**
 * Interface BuildsMessages
 * @package PHPMQ\Server\Endpoint\Interfaces
 */
interface BuildsMessages
{
	public function buildMessage( MessageHeader $messageHeader, array $packets ) : CarriesInformation;
}
