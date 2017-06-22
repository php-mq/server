<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Clients\Types;

use PHPMQ\Server\Clients\Interfaces\IdentifiesClient;
use PHPMQ\Server\Traits\StringRepresenting;

/**
 * Class ClientId
 * @package PHPMQ\Server\Clients\Types
 */
final class ClientId implements IdentifiesClient
{
	use StringRepresenting;

	/** @var string */
	private $clientId;

	public function __construct( string $clientId )
	{
		$this->clientId = $clientId;
	}

	public function toString() : string
	{
		return $this->clientId;
	}
}
