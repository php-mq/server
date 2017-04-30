<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Clients\Interfaces;

use hollodotme\PHPMQ\Interfaces\RepresentsString;

/**
 * Interface IdentifiesClient
 * @package hollodotme\PHPMQ\Clients\Interfaces
 */
interface IdentifiesClient extends RepresentsString
{
	public static function generate() : IdentifiesClient;
}
