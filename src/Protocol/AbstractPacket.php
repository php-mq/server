<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Protocol;

use hollodotme\PHPMQ\Interfaces\RepresentsString;
use hollodotme\PHPMQ\Traits\StringRepresenting;

/**
 * Class AbstractPacket
 * @package hollodotme\PHPMQ\Protocol
 */
abstract class AbstractPacket implements RepresentsString
{
	use StringRepresenting;

	/** @var string */
	private $identifier;

	public function __construct( string $identifier )
	{
		$this->identifier = $identifier;
	}

	final protected function getIdentifier() : string
	{
		return $this->identifier;
	}
}
