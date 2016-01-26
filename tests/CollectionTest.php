<?php

	class CollectionTest extends PHPUnit_Framework_TestCase {

		protected $collection = null;

		protected function setUp() {

			$this->collection = new \browserfs\Collection( [ 1, 2, 3, 4, 4, 3, 2, 1 ] );

		}

		public function testCollectionLength() {

			$this->assertEquals( 8, count( $this->collection ) );

		}

		public function testForeach() {

			$visited = 0;

			foreach ( $this->collection as $item ) {
				$visited++;
			}

			$this->assertEquals( $visited, count( $this->collection ) );

		}

		public function testAddAnItemToCollection() {

			$beforeAdd = count( $this->collection );

			$this->collection[] = 2;

			$this->assertEquals( $beforeAdd + 1, count( $this->collection ) );

		}

		public function testRemoveAnItemFromCollection() {

			$beforeAdd = count( $this->collection );

			unset( $this->collection[0] );

			$this->assertEquals( $beforeAdd - 1, count( $this->collection ) );

		}

		public function testTraverseCollection() {

			$visited = 0;
			$len = count( $this->collection );

			$this->collection->each( function( $item, $itemIndex ) use ( &$visited ) {
				$visited++;
			});

			$this->assertEquals( $visited, $len );

		}

		public function testTraverseCollectionAndBreak() {

			$visited = 0;

			$this->collection->each( function( $item, $itemIndex ) use ( &$visited ) {
				
				$visited++;
				
				if ( $itemIndex == 2 ) {
					return FALSE; // BREAK
				}
			});

			$this->assertEquals( $visited, 3 );

		}

		public function testFilterCollection() {

			$subCollection = $this->collection->filter( function( $item ) {
				return $item === 4;
			} );

			$newLength = count( $subCollection );

			$this->assertEquals( true, $newLength );

		}

		public function testIndexOfMethod() {

			$this->assertEquals( 1, $this->collection->indexOf( 2 ) );

		}

	}