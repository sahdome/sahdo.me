<?php
/**
 * @defgroup Benchmark Benchmark
 * @ingroup  Maintenance
 */

/**
 * Base code for benchmark scripts.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * https://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup Benchmark
 */

require_once __DIR__ . '/../Maintenance.php';

/**
 * Base class for benchmark scripts.
 *
 * @ingroup Benchmark
 */
abstract class Benchmarker extends Maintenance {
	private $results;

	public function __construct() {
		parent::__construct();
		$this->addOption( 'count', "How many times to run a benchmark", false, true );
	}

	public function bench( array $benchs ) {
		$bench_number = 0;
		$count = $this->getOption( 'count', 100 );

		foreach ( $benchs as $bench ) {
			// handle empty args
			if ( !array_key_exists( 'args', $bench ) ) {
				$bench['args'] = [];
			}

			$bench_number++;
			$start = microtime( true );
			for ( $i = 0; $i < $count; $i++ ) {
				call_user_func_array( $bench['function'], $bench['args'] );
			}
			$delta = microtime( true ) - $start;

			// function passed as a callback
			if ( is_array( $bench['function'] ) ) {
				$ret = get_class( $bench['function'][0] ) . '->' . $bench['function'][1];
				$bench['function'] = $ret;
			}

			$this->results[$bench_number] = [
				'function' => $bench['function'],
				'arguments' => $bench['args'],
				'count' => $count,
				'delta' => $delta,
				'average' => $delta / $count,
			];
		}
	}

	public function getFormattedResults() {
		$ret = sprintf( "Running PHP version %s (%s) on %s %s %s\n\n",
			phpversion(),
			php_uname( 'm' ),
			php_uname( 's' ),
			php_uname( 'r' ),
			php_uname( 'v' )
		);
		foreach ( $this->results as $res ) {
			// show function with args
			$ret .= sprintf( "%s times: function %s(%s) :\n",
				$res['count'],
				$res['function'],
				implode( ', ', $res['arguments'] )
			);
			$ret .= sprintf( "   %6.2fms (%6.2fms each)\n",
				$res['delta'] * 1000,
				$res['average'] * 1000
			);
		}

		return $ret;
	}
}
