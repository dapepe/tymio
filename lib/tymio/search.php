<?php

class Search {

	static public function getSearchTerms($search) {
		if ( !preg_match_all('`(?:"(?:\\\\"|[^"])*")|[^ ]+`', $search, $matches) )
			return array();

		$result = array();
		foreach ($matches[0] as $match) {
			$result[] = (
				strpos($match, '"') === 0
				? substr($match, 1, strlen($match) - 2)
				: $match
			);
		}

		return $result;
	}

	/**
	 * Builds a {@link Criterion} object for the specified search term.
	 * Requires that {@link $searchColumns} contains at least one element.
	 *
	 * @param Criteria $c
	 * @param string $term
	 * @param array $searchColumns
	 * @return Criterion
	 * @see addSearchCriteria()
	 */
	static private function getSearchTermCriterion(Criteria $c, $term, array $searchColumns) {
		$searchCriterion = $c->getNewCriterion(array_shift($searchColumns), '%'.$term.'%', Criteria::LIKE)->setIgnoreCase(true);
		foreach ($searchColumns as $columnName)
			$searchCriterion->addOr($c->getNewCriterion($columnName, '%'.$term.'%', Criteria::LIKE)->setIgnoreCase(true));

		return $searchCriterion;
	}

	/**
	 * Builds a {@link Criteria} object.
	 *
	 * @param Criteria $c The {@link Criteria} object to use.
	 * @param string $search A string to search for. Ignored if NULL.
	 * @param array $searchColumns An array containing the search columns as
	 *     defined by Propel column name class constants, e.g.
	 *     {@link UserPeer::ID}.
	 * @return Criteria
	 */
	static public function addSearchCriteria(Criteria $c, $search, array $searchColumns) {
		// Determine full-text search columns
		if ( ((string)$search !== '') and (count($searchColumns) > 0) ) {
			$searchTerms = self::getSearchTerms($search);
			if ( count($searchTerms) === 0 )
				return $c;

			$searchCriterion = self::getSearchTermCriterion($c, array_shift($searchTerms), $searchColumns);
			foreach ($searchTerms as $term)
				$searchCriterion->addAnd(self::getSearchTermCriterion($c, $term, $searchColumns));

			$c->add($searchCriterion);
		}

		return $c;
	}

}

?>