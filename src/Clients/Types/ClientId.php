<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Clients\Types;

use hollodotme\PHPMQ\Clients\Interfaces\IdentifiesClient;
use hollodotme\PHPMQ\Traits\StringRepresenting;

/**
 * Class ClientId
 * @package hollodotme\PHPMQ\Clients\Types
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

	public static function generate() : IdentifiesClient
	{
		return new self( bin2hex( random_bytes( 16 ) ) );
	}

	public function toString() : string
	{
		return $this->clientId;
	}
}
