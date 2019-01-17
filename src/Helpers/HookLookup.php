<?php

namespace MWStew\CLI\Helpers;

class HookLookup {
	protected $hooksHelper = null;
	protected $pages = [];

	public function __construct( $hooksPerPage = 50 ) {
		$this->hooksHelper = new \MWStew\Builder\Hooks();
		$this->hooksPerPage = $hooksPerPage;
		$this->hookNames = $this->hooksHelper->getHookNames();
		asort( $this->hookNames );
	}

	/**
	 * Collect the hook names and divide them into pages
	 * for display
	 *
	 * @return Array An array organized by page number
	 *  and an array of hook names
	 */
	protected function buildHookPages( $hookNames ) {
		$pageLength = $this->hooksPerPage;
		$totalNumOfHooks = count( $hookNames );
		// $numOfPages = (int)( ceil( $totalNumOfHooks / $pageLength ) );

		$pages = [];
		$index = 0;
		$offset = 0;
		while ( $offset < $totalNumOfHooks ) {
			$pages[ $index ] = array_slice(
				$hookNames,
				$offset,
				$pageLength
			);

			$index++;
			$offset = $offset + $pageLength;

			if ( $index === 500 ) {
				// Sanity check infinite loop break
				return;
			}
		}

		return $pages;
	}

	public function getHookPages() {
		return $this->buildHookPages( $this->hookNames );
	}

	public function getFilteredResults( $string = '', $prefix = true, $highlight = true ) {
		// Search for the hooks
		$string = strtolower( preg_replace('/\s+/', '_', $string ) );
		$relevantHooks = array_filter( $this->hookNames, function ( $hookName ) use ( $string, $prefix ) {
			if ( $prefix ) {
				return strpos( strtolower( $hookName ), $string ) === 0;
			} else {
				return strpos( strtolower( $hookName ), $string ) !== false;
			}
		} );

		if ( $highlight ) {
			$result = [];
			foreach ( $relevantHooks as $rhook ) {
				// $result[] = preg_replace( [ '/'. $string . '/i' ], [ '<hi>' . $string . '</>' ], $rhook );
				$result[] = preg_replace( '/('. $string . ')/i', '<hi>$1</>', $rhook );
			}
			$relevantHooks = $result;
		}

		// Build pages
		return $this->buildHookPages( $relevantHooks );
	}

	public function getHookPagesFromPrefix( $prefix = '', $highlightSearch = true ) {
		return $this->getFilteredResults( $prefix, true, $highlightSearch );
	}

	public function getHookPagesFromSearch( $search = '', $highlightSearch = true ) {
		return $this->getFilteredResults( $search, false, $highlightSearch );
	}
}
