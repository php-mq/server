<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Servers\Interfaces;

/**
 * Interface IdentifiesSocketAddress
 * @package PHPMQ\Server\Servers\Interfaces
 */
interface IdentifiesSocketAddress
{
	public function getSocketAddress() : string;
}
