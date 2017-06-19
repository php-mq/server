<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Servers;

use PHPMQ\Server\Servers\Interfaces\ProvidesClientInfo;

/**
 * Class ClientInfo
 * @package PHPMQ\Server\Servers
 */
final class ClientInfo implements ProvidesClientInfo
{
	/** @var string */
	private $name;

	/** @var resource */
	private $socket;

	public function __construct( string $name, $socket )
	{
		$this->name   = $name;
		$this->socket = $socket;
	}

	public function getName() : string
	{
		return $this->name;
	}

	public function getSocket()
	{
		return $this->socket;
	}
}
