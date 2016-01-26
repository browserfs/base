<?php

	namespace browserfs;

	class Collection extends EventEmitter implements \Countable {

		protected $items = [];
		protected $len   = 0;

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
			
			for ( $i=0, $len = count( $this->items ); $i < $len; $i++ ) {
			
				if ( $this->compare( $item, $this->items[$i] ) === 0 ) {
			
					return true;
			
				}
			
			}

			return false;
		}

		public function indexOf( $item ) {

			for ( $i=0, $len = count( $this->items ); $i < $len; $i++ ) {

				if ( $this->compare( $item, $this->items[$i] ) === 0 ) {
					return $i;
				}

			}

			return -1;

		}

		public function count() {

			return $this->len;

		}

	}
