<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Validators;

use PHPMQ\Server\Validators\Interfaces\ValidatesEnvironment;

/**
 * Class ArgumentValidator
 * @package PHPMQ\Server\Validators
 */
final class ArgumentValidator implements ValidatesEnvironment
{
	/** @var array */
	private $arguments;

	/** @var bool */
	private $passed = false;

	/** @var array */
	private $messages = [];

	public function __construct( array $arguments )
	{
		$this->arguments = $arguments;
	}

	public function failed() : bool
	{
		$this->validate();

		return !$this->passed;
	}

	private function validate() : void
	{
		$this->messages = [];

		if ( isset( $this->arguments[1] ) && !file_exists( $this->arguments[1] ) )
		{
			$this->passed     = false;
			$this->messages[] = sprintf(
				'<bg:red>ERROR:<:bg> Could not read configuration file: "%s". File not found.',
				$this->arguments[1]
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
