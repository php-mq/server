<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint\Interfaces;

/**
 * Interface IdentifiesSocketAddress
 * @package PHPMQ\Server\Endpoint\Interfaces
 */
interface IdentifiesSocketAddress
{
	public function getSocketAddress() : string;
}
