<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Loggers\Monitoring\Formatters;

/**
 * Class ByteFormatter
 * @package PHPMQ\Server\Loggers\Monitoring\Formatters
 */
final class ByteFormatter
{
	/** @var int */
	private $bytes;

	public function __construct( int $byte )
	{
		$this->bytes = $byte;
	}

	public function format( int $precision = 2 ) : string
	{
		return $this->getHumanReadableFormat( $precision );
	}

	private function getHumanReadableFormat( int $precision ) : string
	{
		$units = [ 'B ', 'KB', 'MB', 'GB', 'TB' ];
		$bytes = max( $this->bytes, 0 );
		$pow   = floor( ($bytes ? log( $bytes ) : 0) / log( 1024 ) );
		$pow   = min( $pow, count( $units ) - 1 );
		$bytes /= (1 << (10 * $pow));
		$bytes = round( $bytes, $precision );

		return number_format( $bytes, $precision, ',', '.' ) . ' ' . $units[ $pow ];
	}
}
