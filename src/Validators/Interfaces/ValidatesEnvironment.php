<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Validators\Interfaces;

/**
 * Interface ValidatesEnvironment
 * @package PHPMQ\Server\Validators\Interfaces
 */
interface ValidatesEnvironment
{
	public function failed() : bool;

	public function getMessages() : array;
}
