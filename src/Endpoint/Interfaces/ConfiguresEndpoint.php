<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint\Interfaces;

/**
 * Interface ConfiguresEndpoint
 * @package PHPMQ\Server\Endpoint\Interfaces
 */
interface ConfiguresEndpoint
{
	public function getSocketAddress(): string;
}
