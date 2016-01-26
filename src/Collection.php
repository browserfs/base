<?php

	namespace browserfs;

	class Collection extends EventEmitter implements \Countable, \ArrayAccess, \Iterator {

		private $items    = [];
		private $len      = 0;
		private $position = 0;
		private $comparer = null;

		/**
		 * Constructor. Creates a new collection.
		 * @param items: any[] | null
		 */
		public function __construct( $items = null ) {
			
			if ( null !== $items ) {
				
				if ( is_array( $items ) ) {
				
					foreach ( $items as $item ) {
						
						$this->items[] = $this->decorate( $item );

						$this->len++;

					}
				
				} else {

					throw new \browserfs\Exception('Invalid argument. Expected array | null' );

				}
			}

		}

		/**
		 * Decorates an item before adding it to collection. Used for future
		 * collections implementations, when for example modifying an item a
		 * trigger of a specific event is made to the collection.
		 * @param $item: any
		 * @return <typeof>$item | $item;
		 */
		public function decorate( $item ) {
			return $item;
		}

		/**
		 * Decorates an item before removing it from collection. Used for future
		 * collections implementations, when for example modifying an item
		 * a trigger of a specific event is made to the collection.
		 * @param $item: any
		 * @return <typeof>$item | $item;
		 */
		public function undecorate( $item ) {
			return $item;
		}

		/**
		 * Adds an item to collection.
		 * @param $item: any
		 * @param $success: optional, is set to TRUE if the item has been added to the collection, or to FALSE otherwise.
		 * @return \browserfs\Collection (this) 
		 */
		public function add( $item, &$success = null ) {

			$result = $this->fire( 'before-add', $item );

			if ( !$result->isPropagationStopped() ) {

				$this->items[] = $this->decorate( $item );
				$this->len++;

				$this->fire( 'add', $item );

				$success = true;

			} else {

				$success = false;

			}

			return $this;

		}

		/**
		 * Removes the first item which is equal with the $item argument,
		 * and returns this collection.
		 * @param $item: any
		 * @return \browserfs\Collection ($this)
		 */
		public function remove( $item ) {

			for ( $i=0; $i<$this->len; $i++ ) {

				if ( $this->compare( $item, $this->items[$i] ) === 0 ) {
					
					$this->undecorate( $this->items[ $i ] );
				
					array_splice( $this->items, $i, 1 );
				
					$this->len--;

					break;
				}

			}

			return $this;

		}

		/**
		 * Compares two items from the collection. Should return
		 * 0 if items are equal, negative value if item1 < item2,
		 * and positive value if item2 > item1.
		 */
		public function compare( $item1, $item2 ) {

			return $this->comparer === null
				? self::defaultCompareWrapper( $item1, $item2 )
				: $this->comparer( $item1, $item2 );

		}

		/**
		 * Sets default comparer function
		 * @param $comparer: function( $item1, $item2 ): int
		 * @return $this
		 */
		public function setCompareFunction( $comparer ) {
			if ( is_callable( $comparer ) ) {
				$this->comparer = $comparer;
			} else {
				throw new \browserfs\Exception('Invalid argument! Expected callback( any, any ): int' );
			}
		}

		/**
		 * Returns TRUE if this collection contains an item, and FALSE otherwise
		 */
		public function contains( $item ) {
			
			for ( $i=0; $i < $this->len; $i++ ) {
			
				if ( $this->compare( $item, $this->items[$i] ) === 0 ) {
			
					return true;
			
				}
			
			}

			return false;
		}

		/**
		 * If this collection contains item $item, returns the index of the item
		 * in the collection, or -1 otherwise.
		 */
		public function indexOf( $item ) {

			for ( $i=0; $i < $this->len; $i++ ) {

				if ( $this->compare( $item, $this->items[$i] ) === 0 ) {
					return $i;
				}

			}

			return -1;

		}

		/**
		 * Executes callable callback with arguments ( $item, $index, $this ) on
		 * each item from the collection. If the callback returns FALSE, the traversing
		 * is stopped after that return statement.
		 */
		public function each( $callback ) {

			if ( is_callable( $callback ) ) {

				for ( $i=0; $i < $this->len; $i++ ) {

					if ( call_user_func( $callback, $this->items[$i], $i, $this ) === false ) {
						break;
					}

				}

			}

			return $this;

		}

		/**
		 * Returns another collection of the same type, with items which are passing a
		 * filter test.
		 * @param callback => function( $item, $index, $this ) => boolean
		 */
		public function filter( $callback ) {

			$result = [];

			if ( is_callable( $callback ) ) {

				for ( $i=0; $i<$this->len; $i++ ) {
					if ( call_user_func( $callback, $this->items[$i], $i, $this ) ) {
						$result[] = &$this->items[$i];
					}
				}

			}

			$myName = get_class();

			return new $myName( $result );

		}

		/**
		 * Returns another collection with this collection items, excluding
		 * the first $many items from this collection
		 * @param $many : int[ 0..length ] | null
		 * @return \browserfs\Collection
		 * @throws \browserfs\Exception on invalid argument(s)
		 */
		public function skip( $many = null ) {
			
			if ( null === $many || is_int( $many ) ) {

				if ( null != $many && $many > 0 ) {

					return new static( array_splice( $this->items, $many ) );
				
				} else
				if ( $many === null || $many === 0 ) {
				
					return $this;
				
				} else {
					throw new \browserfs\Exception('Invalid argument $many: Expected int[0..length] | null');
				}

			} else {

				throw new \browserfs\Exception('Invalid argument $many: Expected int[0..length] | null' );
			
			}
		}

		/**
		 * Returns another collection with this collection items, limited to
		 * at most $count items
		 * @param $count : int[-1..length] | null 
		 * @return \browserfs\Collection
		 * @throws \browserfs\Exception on invalid argument(s)
		 */
		public function limit( $count = null ) {
			
			if ( null === $count || is_int( $count ) ) {
			
				if ( null !== $count && $count >= 0 ) {
					
					return $count === $this->len
						? $this
						: new static( array_slice( $this->items, 0, $count ) );
				
				} else
				if ( $many === null || $many === -1 ) {
				
					return $this;
				
				} else {
				
					throw new \browserfs\Exception('Invalid argument $count: Expected int[-1..length] | null' );
				
				}
			} else {

				throw new \browserfs\Exception('Invalid argument $count: Expected int[-1..length] | null');
			
			}

		}

		/**
		 * Returns the item at position $index.
		 * @param $index: int[0..length-1]
		 * @return any
		 */
		public function at( $index ) {
			if ( is_int( $index ) ) {
				if ( $index >= 0 && $index < $this->len ) {
					return $this->items[ $index ];
				} else {
					throw new \browserfs\Exception('Index out of bounds!');
				}
			} else {
				throw new \browserfs\Exception('Invalid argument $index: Expected int');
			}
		}

		/**
		 * Returns a sorted collection based on a user-defined function
		 * @param $callback: function( $item1, $item2 ): int
		 * @return \browserfs\Collection
		 */
		public function sort( $callback = null , $ascending = true ) {

			if ( null !== $callback && !is_callable( $callback ) ) {
				throw new \browserfs\Exception('Invalid argument $callback: Expected callable( any, any ): int | null' );
			}

			$result = [];

			for ( $i=0; $i<$this->len; $i++ ) {
				$result[] = &$this->items[ $i ];
			}

			if ( is_callable( $callback ) ) {

				usort( $result, $callback );

			} else 
			{

				usort( $result, [ $this, 'compare' ] );

			}

			return new static( $ascending ? $result : array_reverse( $result ) );
		}

		/**
		 * Returns a collection with unique items.
		 * @param $callback: function( $item1, $item2 ): int | null
		 *      If the $callback parameter is null, this->compare will be
		 *      used.
		 * @return \browserfs\Collection
		 * @throws \browserfs\Exception on invalid argument
		 */
		public function unique( $callback = null ) {

			$result = [];

			if ( $callback === null ) {

				$n = 0;

				for ( $i=0; $i < $this->len; $i++ ) {

					$exists = false;

					for ( $j=0; $j < $n; $j++ ) {
						if ( $this->compare( $result[$j], $this->items[$i] ) === 0 ) {
							$exists = true;
							break;
						}
					}

					if ( !$exists ) {
						$result[] = $this->items[$i];
						$n++;
					}

				}

			} else
			if ( is_callable( $callback ) ) {

				$n = 0;

				for ( $i=0; $i<$this->len; $i++ ) {

					$exists = false;

					for ( $j = 0; $j < $n; $j++ ) {
						if ( call_user_func( $callback, $result[$j], $this->items[$i] ) === 0 ) {
							$exists = true;
							break;
						}
					}

					if ( !$exists ) {
						$result[] = $this->items[$i];
						$n++;
					}

				}

			} else {
				throw new \browserfs\Exception('Invalid argument: $callback. Expected: function( any, any ): int | null' );
			}

			return new self( $result );

		}

		/**
		 * Returns the length of the collection
		 *
		 * @implements \Countable::count()
		 * @return int
		 *
		 **/
		public function count() {

			return $this->len;

		}

		/**
		 * @implements \ArrayAccess::offsetExists
		 */
		public function offsetExists( $offset ) {
			return ( $offset >= 0 ) && ( $offset < $this->len );
		}

		/**
		 * @implements \ArrayAccess::offsetGet
		 */
		public function offsetGet( $offset ) {
			return @$this->items[ $offset ];
		}

		/**
		 * @implements \ArrayAccess::offsetSet
		 */
		public function offsetSet( $offset, $value ) {

			if ( $offset === $this->len || $offset === null || $offset === '' ) {
				
				$this->items[] = $this->decorate( $value );
				
				$this->len++;

			} else

			if ( $offset >=0 && $offset <= $this->len ) {
				
				$this->undecorate( $this->items[$offset] );
				
				$this->items[ $offset ] = $this->decorate( $value );
			
			} else {
				throw new \browserfs\Exception( 'Failed to set offset ' . json_encode( $offset ) . ': illegal offset!' );
			}
		}

		/**
		 * @implements \ArrayAccess::offsetUnset
		 */
		public function offsetUnset( $offset ) {
			if ( $offset >= 0 && $offset < $this->len ) {

				$this->undecorate( $this->items[ $offset ] );
				
				array_splice( $this->items, $offset, 1 );
				
				$this->len--;
			}
		}

		/**
		 * @implements \Iterator::rewind
		 */
		public function rewind() {

			$this->position = 0;

		}

		/**
		 * @implements \Iterator::current
		 */
		public function current() {

			return $this->position < $this->len
				? $this->items[ $this->position ]
				: null;

		}

		/**
		 * @implements \Iterator::key
		 */
		public function key() {

			return $this->position;

		}

		/**
		 * @implements \Iterator::next
		 */
		public function next() {

			$this->position++;

		}

		/**
		 * @implements \Iterator::valid
		 */
		public function valid() {
			return ( $this->position >= 0 ) && ( $this->position < $this->len );
		}

		/**
		 * A default compare function, used by this generic collection
		 */
		private static function defaultCompareWrapper( $a, $b ) {
			if ( $a === $b ) {
				return 0;
			} else {

				if ( is_string( $a ) ) {
					return strcmp( $a, $b . '' );
				} else
				if ( is_int( $a ) || is_float( $b ) ) {
					return (float)$a - $b;
				} else 
				return -1;

			}
		}

	}
