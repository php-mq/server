<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Events\Maintenance;

use PHPMQ\Server\Commands\HelpCommand;
use PHPMQ\Server\Interfaces\CarriesEventData;
use PHPMQ\Stream\Interfaces\TransfersData;

/**
 * Class ClientRequestedHelp
 * @package PHPMQ\Server\Events\Maintenance
 */
final class ClientRequestedHelp implements CarriesEventData
{
	/** @var TransfersData */
	private $stream;

	/** @var HelpCommand */
	private $helpCommand;

	public function __construct( TransfersData $stream, HelpCommand $helpCommand )
	{
		$this->stream      = $stream;
		$this->helpCommand = $helpCommand;
	}

	public function getStream() : TransfersData
	{
		return $this->stream;
	}

	public function getHelpCommand() : HelpCommand
	{
		return $this->helpCommand;
	}
}
