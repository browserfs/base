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
						$this->items[] = $item;
						$this->len++;
					}
				
				} else {

					throw new \browserfs\Exception('Invalid argument. Expected array | null' );

				}
			}
		}

		public function add( $item ) {

			$result = $this->fire( 'before-add', $item );

			if ( !$result->isPropagationStopped() ) {

				$this->items[] = $item;
				$this->len++;

				$this->fire( 'add', $item );

			}

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

		public function count() {

			return $this->len;

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

		public function offsetExists( $offset ) {
			return ( $offset >= 0 ) && ( $offset < $this->len );
		}

		public function offsetGet( $offset ) {
			return @$this->items[ $offset ];
		}

		public function offsetSet( $offset, $value ) {

			if ( $offset === $this->len || $offset === null || $offset === '' ) {
				$this->items[] = $value;
				$this->len++;
			} else

			if ( $offset >=0 && $offset <= $this->len ) {
				$this->items[ $offset ] = $value;
			} else {
				throw new \browserfs\Exception( 'Failed to set offset ' . json_encode( $offset ) . ': illegal offset!' );
			}
		}

		public function offsetUnset( $offset ) {
			if ( $offset >= 0 && $offset < $this->len ) {
				array_splice( $this->items, $offset, 1 );
				$this->len--;
			}
		}

		public function rewind() {

			$this->position = 0;

		}

		public function current() {

			return $this->position < $this->len
				? $this->items[ $this->position ]
				: null;

		}

		public function key() {

			return $this->position;

		}

		public function next() {

			$this->position++;

		}

		public function valid() {

			return ( $this->position >= 0 ) && ( $this->position < $this->len );

		}

	}
