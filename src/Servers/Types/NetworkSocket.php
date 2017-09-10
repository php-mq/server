<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Servers\Types;

use PHPMQ\Server\Servers\Constants\SocketType;
use PHPMQ\Server\Servers\Interfaces\IdentifiesSocketAddress;

/**
 * Class NetworkSocket
 * @package PHPMQ\Server\Servers\Types
 */
final class NetworkSocket implements IdentifiesSocketAddress
{
	/** @var string */
	private $host;

	/** @var int */
	private $port;

	public function __construct( string $host, int $port )
	{
		$this->host = $host;
		$this->port = $port;
	}

	public function getSocketAddress() : string
	{
		return sprintf( 'tcp://%s:%s', $this->host, $this->port );
	}

	public function getType() : int
	{
		return SocketType::TCP;
	}
}
