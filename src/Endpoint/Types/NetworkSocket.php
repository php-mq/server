<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Endpoint\Types;

use hollodotme\PHPMQ\Endpoint\Interfaces\IdentifiesSocketAddress;

/**
 * Class NetworkSocket
 * @package hollodotme\PHPMQ\Endpoint\Types
 */
final class NetworkSocket implements IdentifiesSocketAddress
{
	/** @var string */
	private $ipAddress;

	/** @var int */
	private $port;

	public function __construct( string $ipAddress, int $port )
	{
		$this->ipAddress = $ipAddress;
		$this->port      = $port;
	}

	public function getAddress() : string
	{
		return $this->ipAddress;
	}

	public function getPort() : int
	{
		return $this->port;
	}
}
