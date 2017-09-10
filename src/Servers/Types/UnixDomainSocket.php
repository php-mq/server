<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Servers\Types;

use PHPMQ\Server\Servers\Interfaces\IdentifiesSocketAddress;

/**
 * Class UnixDomainAddress
 * @package PHPMQ\Server\Servers\Types
 */
final class UnixDomainSocket implements IdentifiesSocketAddress
{
	/** @var string */
	private $socketPath;

	/** @var array */
	private $contextOptions;

	public function __construct( string $socketPath, array $contextOptions = [] )
	{
		$this->socketPath     = $socketPath;
		$this->contextOptions = $contextOptions;
	}

	public function getSocketAddress() : string
	{
		return sprintf( 'unix://%s', $this->socketPath );
	}

	public function getContextOptions() : array
	{
		return $this->contextOptions;
	}
}
