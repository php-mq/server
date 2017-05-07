<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Protocol;

use PHPMQ\Server\Interfaces\RepresentsString;
use PHPMQ\Server\Traits\StringRepresenting;

/**
 * Class AbstractPacket
 * @package PHPMQ\Server\Protocol
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
