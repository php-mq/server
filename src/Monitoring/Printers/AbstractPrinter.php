<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Monitoring\Printers;

use PHPMQ\Server\Interfaces\PreparesOutputForCli;
use PHPMQ\Server\Monitoring\Interfaces\CreatesMonitoringOutput;

/**
 * Class AbstractPrinter
 * @package PHPMQ\Server\Printers
 */
abstract class AbstractPrinter implements CreatesMonitoringOutput
{
	/** @var PreparesOutputForCli */
	private $cliWriter;

	public function __construct( PreparesOutputForCli $cliWriter )
	{
		$this->cliWriter = $cliWriter;
	}

	final protected function getCliWriter() : PreparesOutputForCli
	{
		return $this->cliWriter;
	}
}
