<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Protocol\Interfaces;

/**
 * Interface IdentifiesMessageType
 * @package hollodotme\PHPMQ\Protocol\Interfaces
 */
interface IdentifiesMessageType
{
	public function getType() : int;

	public function getPacketCount() : int;
}
