<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Monitoring\Formatters;

/**
 * Class ByteFormatter
 * @package PHPMQ\Server\Monitoring\Formatters
 */
final class ByteFormatter
{
	public function format( int $bytes, int $precision = 2 ): string
	{
		return $this->getHumanReadableFormat( $bytes, $precision );
	}

	private function getHumanReadableFormat( int $bytes, int $precision ): string
	{
		$units       = [ 'B', 'KB', 'MB', 'GB', 'TB' ];
		$actualBytes = max( $bytes, 0 );
		$pow         = floor( ($actualBytes ? log( $actualBytes ) : 0) / log( 1024 ) );
		$pow         = min( $pow, count( $units ) - 1 );
		$actualBytes /= (1 << (10 * $pow));
		$actualBytes = round( $actualBytes, $precision );

		return number_format( $actualBytes, $precision, ',', '.' ) . ' ' . $units[ $pow ];
	}
}
