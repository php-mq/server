<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint\Types;

use PHPMQ\Server\Endpoint\Interfaces\IdentifiesSocketAddress;

/**
 * Class UnixDomainAddress
 * @package PHPMQ\Server\Endpoint\Types
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
