<?php

/**
 * Licensed under the MIT license.
 *
 * For the full copyright and license information, please view the LICENSE file.
 *
 * @author Rémi Lanvin <remi@cloudconnected.fr>
 * @link https://github.com/rlanvin/php-rrule
 */

namespace TeamBooking\RRule;

/**
 * Common interface for RRule and RSet objects
 */
interface RRuleInterface extends \Iterator, \ArrayAccess, \Countable
{
	/**
	 * Return all the occurrences in an array of \DateTime.
	 *
	 * @return array An array of \DateTime objects
	 */
	public function getOccurrences();

	/**
	 * Return all the ocurrences after a date, before a date, or between two dates.
	 *
	 * @param mixed $begin Can be null to return all occurrences before $end
	 * @param mixed $end Can be null to return all occurrences after $begin
	 * @return array An array of \DateTime objects
	 */
	public function getOccurrencesBetween($begin, $end);

	/**
	 * Return true if $date is an occurrence.
	 *
	 * @param mixed $date
	 * @return bool
	 */
	public function occursAt($date);

	/**
	 * Return true if the rrule has an end condition, false otherwise
	 *
	 * @return bool
	 */
	public function isFinite();

	/**
	 * Return true if the rrule has no end condition (infite)
	 *
	 * @return bool
	 */
	public function isInfinite();
}