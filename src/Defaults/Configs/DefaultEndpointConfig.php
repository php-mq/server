<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Defaults\Configs;

use hollodotme\PHPMQ\Endpoint\Constants\SocketDomain;
use hollodotme\PHPMQ\Endpoint\Constants\SocketType;
use hollodotme\PHPMQ\Endpoint\Interfaces\ConfiguresEndpoint;
use hollodotme\PHPMQ\Endpoint\Interfaces\IdentifiesSocketAddress;
use hollodotme\PHPMQ\Endpoint\Types\UnixDomainSocket;

/**
 * Class DefaultEndpointConfig
 * @package hollodotme\PHPMQ\Defaults\Configs
 */
final class DefaultEndpointConfig implements ConfiguresEndpoint
{
	public function getSocketDomain() : int
	{
		return SocketDomain::UNIX;
	}

	public function getSocketType() : int
	{
		return SocketType::STREAM;
	}

	public function getSocketProtocol() : int
	{
		return 0;
	}

	public function getBindToAddress() : IdentifiesSocketAddress
	{
		return new UnixDomainSocket( '/tmp/phpmq.sock' );
	}

	public function getListenBacklog() : int
	{
		return SOMAXCONN;
	}
}
