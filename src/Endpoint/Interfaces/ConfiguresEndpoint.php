<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Endpoint\Interfaces;

/**
 * Interface ConfiguresEndpoint
 * @package hollodotme\PHPMQ\Endpoint\Interfaces
 */
interface ConfiguresEndpoint
{
	public function getSocketDomain() : int;

	public function getSocketType() : int;

	public function getSocketProtocol() : int;

	public function getBindToAddress() : IdentifiesSocketAddress;

	public function getListenBacklog() : int;
}
