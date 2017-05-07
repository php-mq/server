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
	public function getSocketDomain() : int;

	public function getSocketType() : int;

	public function getSocketProtocol() : int;

	public function getBindToAddress() : IdentifiesSocketAddress;

	public function getListenBacklog() : int;
}
