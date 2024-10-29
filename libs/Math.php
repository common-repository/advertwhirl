<?php
/*
Copyright 2011  Mobile Sentience LLC  (email : oss@mobilesentience.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
                                                                 
*/

if(!function_exists(get_gcf)){

	/** Euclids Algorithm for calculating the greatest common factor of two or more numbers
	  * via http://www.calculatorsoup.com/calculators/math/gcf.php
	  * 
      * @values array of 2 or more numbers you wish to find the GCF of. 0 and negative numbers are invalid
	  *
	  * @return the greatest common factor or false  if there is an error
	  */
	function get_gcf($values){
		// count the number of values in the array
		$num_values = count($values);

		// $values must have at least 2 numbers
		if($num_values < 2)
			return false;

		// $values must all be positive numbers
		foreach($values as $value){
			if(!is_numeric($value) || $value <= 0)
				return false;
		}

		// get the first 2 values in the array        
		$x = current($values);
		$y = next($values);
   
		// set up a for-loop to check through all of the values in the array
		// the first pass will check 2 numbers then each additional pass will check 1
		// make ($num_values - 1) passes
		for ($i = 1; $i < $num_values; $i ++){
			// set up the larger and smaller of the values
			$a = max( $x, $y );
			$b = min( $x, $y );
			$c = 1;

			// find the GCF of $a and $b
			// it will be found when $c == 0
			do{
				$c = $a % $b;

				// capture last value of $b as the potential last GCF result
				$gcf = $b;

				// if $c did not = 0 we need to repeat with the values held in $b and $c
				// at this point $b is higher than $c so we set up for the next iteration
				// set $a to the higher number and $b to the lower number
				$a = $b;
				$b = $c;            
			} while ($c != 0);

			// if $c did == 0 then we have found the GCF of 2 numbers
			// now set up to find the GCF of the last GCF we found and the next value in the array()
			$x = $gcf;
			$y = next($values);

		}  // end for loop through array()
		return $gcf;
	}
}

?>
