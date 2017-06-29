<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Validators;

use PHPMQ\Server\Validators\Interfaces\ValidatesEnvironment;

/**
 * Class PHPVersionValidator
 * @package PHPMQ\Server\Validators
 */
final class PHPVersionValidator implements ValidatesEnvironment
{
	/** @var string */
	private $minPhpVersion;

	/** @var string */
	private $phpVersion;

	/** @var string */
	private $phpBinary;

	/** @var string */
	private $packageVersion;

	/** @var array */
	private $messages;

	/** @var bool */
	private $passed = false;

	public function __construct( string $minPhpVersion, string $phpVersion, string $phpBinary, string $packageVersion )
	{
		$this->minPhpVersion  = $minPhpVersion;
		$this->phpVersion     = $phpVersion;
		$this->phpBinary      = $phpBinary;
		$this->packageVersion = $packageVersion;
	}

	public function failed() : bool
	{
		$this->validate();

		return !$this->passed;
	}

	private function validate() : void
	{
		$this->messages = [];

		if ( version_compare( $this->minPhpVersion, $this->phpVersion, '>' ) )
		{
			$this->passed     = false;
			$this->messages[] = sprintf(
				'PHPMQ %s by Holger Woltersdorf and contributors.' . PHP_EOL . PHP_EOL .
				'This version of PHPMQ is supported on PHP >= 7.1.0' . PHP_EOL .
				'You are using PHP %s (%s).' . PHP_EOL,
				$this->packageVersion,
				$this->phpVersion,
				$this->phpBinary
			);

			return;
		}

		$this->passed = true;
	}

	public function getMessages() : array
	{
		return $this->messages;
	}
}
