<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Clients;

use PHPMQ\Server\Clients\Interfaces\ProvidesConsumptionInfo;

/**
 * Class ConsumptionPool
 * @package PHPMQ\Server\Clients
 */
final class ConsumptionPool
{
	/** @var array|ProvidesConsumptionInfo[] */
	private $consumptionInfos = [];

	public function setConsumptionInfo( $stream, ProvidesConsumptionInfo $consumptionInfo ) : void
	{
		$streamId                            = (int)$stream;
		$this->consumptionInfos[ $streamId ] = $consumptionInfo;
	}

	public function getConsumptionInfo( $stream ) : ProvidesConsumptionInfo
	{
		$streamId = (int)$stream;

		return $this->consumptionInfos[ $streamId ] ?? new NullConsumptionInfo();
	}

	public function removeConsumptionInfo( $stream ) : void
	{
		$streamId = (int)$stream;
		unset( $this->consumptionInfos[ $streamId ] );
	}
}
