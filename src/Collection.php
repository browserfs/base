<?php

	namespace browserfs;

	class Collection extends EventEmitter implements \Countable, \ArrayAccess, \Iterator {

		protected $items = [];
		protected $len   = 0;

		protected $position = 0;

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
		 * trigger to the collection is made
		 */
		public function decorate( $item ) {

			return $item;

		}

		/**
		 * Decorates an item before removing it from collection. Used for future
		 * collections implementations, when for example modifying an item
		 * a trigger to the collection should be made.
		 */
		public function undecorate( $item ) {
			return $item;
		}

		/**
		 * Adds an item to collection.
		 */
		public function add( $item ) {

			$result = $this->fire( 'before-add', $item );

			if ( !$result->isPropagationStopped() ) {

				$this->items[] = $this->decorate( $item );
				$this->len++;

				$this->fire( 'add', $item );

			}

		}

		/**
		 * Removes the first item which is equal with the $item argument,
		 * and returns this collection.
		 */
		public function remove( $item ) {

			for ( $i=0; $i<$this->len; $i++ ) {
				if ( $this->compare( $item, $this->items[$i] ) ) {
					
					$this->undecorate( $this->items[ $i ] );
				
					array_splice( $this->items, $i, 1 );
				
					$this->len--;

					break;
				}
			}

			return $this;

		}

		public function compare( $item1, $item2 ) {

			return $item1 === $item2
				? 0
				: -1;

		}

		public function contains( $item ) {
			
			for ( $i=0; $i < $this->len; $i++ ) {
			
				if ( $this->compare( $item, $this->items[$i] ) === 0 ) {
			
					return true;
			
				}
			
			}

			return false;
		}

		public function indexOf( $item ) {

			for ( $i=0; $i < $this->len; $i++ ) {

				if ( $this->compare( $item, $this->items[$i] ) === 0 ) {
					return $i;
				}

			}

			return -1;

		}

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
		 * \Countable::count()
		 **/
		public function count() {

			return $this->len;

		}

		/**
		 * \ArrayAccess::offsetExists
		 */
		public function offsetExists( $offset ) {
			return ( $offset >= 0 ) && ( $offset < $this->len );
		}

		/**
		 * \ArrayAccess::offsetGet
		 */
		public function offsetGet( $offset ) {
			return @$this->items[ $offset ];
		}

		/**
		 * \ArrayAccess::offsetSet
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
		 * \ArrayAccess::offsetUnset
		 */
		public function offsetUnset( $offset ) {
			if ( $offset >= 0 && $offset < $this->len ) {

				$this->undecorate( $this->items[ $offset ] );
				
				array_splice( $this->items, $offset, 1 );
				
				$this->len--;
			}
		}

		/**
		 * \Iterator::rewind
		 */
		public function rewind() {

			$this->position = 0;

		}

		/**
		 * \Iterator::current
		 */
		public function current() {

			return $this->position < $this->len
				? $this->items[ $this->position ]
				: null;

		}

		/**
		 * \Iterator::key
		 */
		public function key() {

			return $this->position;

		}

		/**
		 * \Iterator::next
		 */
		public function next() {

			$this->position++;

		}

		/**
		 * \Iterator::valid
		 */
		public function valid() {
			return ( $this->position >= 0 ) && ( $this->position < $this->len );
		}

	}
