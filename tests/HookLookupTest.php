<?php

use MWStew\CLI\Helpers\HookLookup;
use PHPUnit\Framework\TestCase;

class HookLookupTest extends TestCase {
	public function testHookPages() {
		$hookLookup = new HookLookup();
		$hooksBuilder = new \MWStew\Builder\Hooks();

		$pages = $hookLookup->getHookPages();
		$expectedNumberOfHooks = count( $hooksBuilder->getHookNames() );

		// Count the actual number of hooks inside the pages
		// and make sure we have the same number that we started
		// with
		$count = 0;
		for ( $i = 0; $i < count( $pages ); $i++ ) {
			$count += count( $pages[ $i ] );
		}

		$this->assertEquals(
			$count,
			$expectedNumberOfHooks,
			'Page division includes all hooks'
		);

		$hookLookup = new HookLookup( 42 );
		$this->assertEquals(
			(int) ceil( $expectedNumberOfHooks / 42 ),
			count( $hookLookup->getHookPages() ),
			'Page division respects given number of pages'
		);
	}
}
