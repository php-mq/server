<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Servers\Types;

use PHPMQ\Server\Servers\Constants\SocketType;
use PHPMQ\Server\Servers\Interfaces\IdentifiesSocketAddress;

/**
 * Class UnixDomainAddress
 * @package PHPMQ\Server\Servers\Types
 */
final class UnixDomainSocket implements IdentifiesSocketAddress
{
	/** @var string */
	private $socketPath;

	public function __construct( string $socketPath )
	{
		$this->socketPath = $socketPath;
	}

	public function getSocketAddress() : string
	{
		return sprintf( 'unix://%s', $this->socketPath );
	}

	public function getType() : int
	{
		return SocketType::UNIX;
	}
}
