<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Protocol\Headers;

use PHPMQ\Server\Interfaces\RepresentsString;
use PHPMQ\Server\Traits\StringRepresenting;

/**
 * Class AbstractHeader
 * @package PHPMQ\Server\Protocol\Headers
 */
abstract class AbstractHeader implements RepresentsString
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
