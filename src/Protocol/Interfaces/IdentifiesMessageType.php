<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Protocol\Interfaces;

/**
 * Interface IdentifiesMessageType
 * @package PHPMQ\Server\Protocol\Interfaces
 */
interface IdentifiesMessageType
{
	public function getType() : int;

	public function getPacketCount() : int;
}
