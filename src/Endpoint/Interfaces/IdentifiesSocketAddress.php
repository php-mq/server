<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Endpoint\Interfaces;

/**
 * Interface IdentifiesSocketAddress
 * @package hollodotme\PHPMQ\Endpoint\Interfaces
 */
interface IdentifiesSocketAddress
{
	public function getAddress() : string;

	public function getPort() : int;
}
