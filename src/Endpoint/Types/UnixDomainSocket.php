<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Endpoint\Types;

use hollodotme\PHPMQ\Endpoint\Interfaces\IdentifiesSocketAddress;

/**
 * Class UnixDomainAddress
 * @package hollodotme\PHPMQ\Endpoint\Types
 */
final class UnixDomainSocket implements IdentifiesSocketAddress
{
	/** @var string */
	private $socketPath;

	public function __construct( string $socketPath )
	{
		$this->socketPath = $socketPath;
	}

	public function getAddress() : string
	{
		return $this->socketPath;
	}

	public function getPort() : int
	{
		return 0;
	}
}
