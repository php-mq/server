<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Validators;

use PHPMQ\Server\Validators\Interfaces\ValidatesEnvironment;

/**
 * Class CompositeValidator
 * @package PHPMQ\Server\Validators
 */
final class CompositeValidator implements ValidatesEnvironment
{
	/** @var array|ValidatesEnvironment[] */
	private $validators = [];

	/** @var array */
	private $messages = [];

	public function addValidators( ValidatesEnvironment ...$validators ) : void
	{
		foreach ( $validators as $validator )
		{
			$this->validators[] = $validator;
		}
	}

	public function failed() : bool
	{
		foreach ( $this->validators as $validator )
		{
			if ( $validator->failed() )
			{
				$this->messages = $validator->getMessages();

				return true;
			}
		}

		return false;
	}

	public function getMessages() : array
	{
		return $this->messages;
	}
}
