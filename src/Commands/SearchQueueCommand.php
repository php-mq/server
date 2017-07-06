<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Commands;

use PHPMQ\Server\Clients\Interfaces\TriggersExecution;
use PHPMQ\Server\Commands\Constants\Command;

/**
 * Class SearchQueueCommand
 * @package PHPMQ\Server\Commands
 */
final class SearchQueueCommand implements TriggersExecution
{
	/** @var string */
	private $searchTerm;

	public function __construct( string $searchTerm )
	{
		$this->searchTerm = $searchTerm;
	}

	public function getSearchTerm() : string
	{
		return $this->searchTerm;
	}

	public function getName() : string
	{
		return Command::SEARCH_QUEUE;
	}
}
