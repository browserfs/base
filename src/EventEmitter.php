<?php

	namespace browserfs;

	class EventEmitter {
		
		private $events = [];
		private $fireId = 0;

		public final function on( $eventName, $eventCallback ) {
			
			if ( !is_string( $eventName ) ) {
			
				throw new \browserfs\Exception('Invalid argument $eventName: string expected');
			
			} else {
			
				if ( !strlen( $eventName ) ) {
			
					throw new \browserfs\Exception('Invalid argument $eventName: expected non-empty string');
			
				} else {
			
					if ( !is_callable( $eventCallback ) ) {
			
						throw new \browserfs\Exception('Invalid argument $eventCallback: callable expected' );
			
					} else {

						$this->events[ $eventName ] = isset( $this->events[ $eventName ] ) 
							? $this->events[ $eventName ] 
							: [];

						$this->events[ $eventName ][] = [
							'once' => false,
							'callback' => $eventCallback,
							'fireId' => 0
						];

					}
				}
			}

		}

		public final function once( $eventName, $eventCallback ) {

			if ( !is_string( $eventName ) ) {
			
				throw new \browserfs\Exception('Invalid argument $eventName: string expected');
			
			} else {
			
				if ( !strlen( $eventName ) ) {
			
					throw new \browserfs\Exception('Invalid argument $eventName: expected non-empty string');
			
				} else {
			
					if ( !is_callable( $eventCallback ) ) {
			
						throw new \browserfs\Exception('Invalid argument $eventCallback: callable expected' );
			
					} else {

						$this->events[ $eventName ] = isset( $this->events[ $eventName ] ) ? $this->events[ $eventName ] : [];

						$this->events[ $eventName ][] = [
							'once' => true,
							'callback' => $eventCallback,
							'fireId' => 0
						];
					}
				}
			}
		}

		private function callbackEquals( $callback1, $callback2 ) {

			if ( is_array( $callback1 ) && is_array( $callback2 ) ) {
				
				if ( count( $callback1 ) == count( $callback2 ) ) {
				
					for ( $i=0, $len = count( $callback1 ); $i<$len; $i++ ) {
						if ( $callback1[$i] != $callback2[$i] ) {
							return false;
						}
					}

					return true;

				} else {
					
					return false;
				
				}

			} else {

				return $callback1 == $callback2;

			}

		}

		public final function off( $eventName, $eventCallback = null ) {

			if ( is_string( $eventName ) ) {

				if ( strlen( $eventName ) > 0 ) {

					if ( isset( $this->events[ $eventName ] ) ) {

						if ( $eventCallback === null ) {

							unset( $this->events[ $eventName ] );
						
						} else {

							for ( $i = count( $this->events[ $eventName ] ) - 1; $i>=0; $i-- ) {

								if ( $this->callbackEquals( $eventCallback, $this->events[ $eventName ][ $i ]['callback'] ) ) {

									// remove event
									array_splice( $this->events[ $eventName ], $i, 1 );

								}

							}

						}

					}

				} else {

					throw new \browserfs\Exception('Invalid argument $eventName: non-empty string expected!');

				}

			} else {

				throw new \browserfs\Exception('Invalid argument $eventName: string expected!' );

			}

		}

		public final function fire( $eventName /* ... $eventArgs: any[] */ ) {

			if ( !is_string( $eventName ) ) {
				throw new \browserfs\Exception('Invalid argument $eventName: string expected' );
			} else
			if ( !strlen( $eventName ) ) {
				throw new \browserfs\Exception('Invalid argument $eventName: non-empty string expected');
			}

			if ( !isset( $this->events[ $eventName ] ) ) {
				return;
			}

			$eventArgs = array_slice( func_get_args(), 1 );

			$event = Event::create( $eventName, $eventArgs );

			try {

				$this->fireId++;

				// run the loop on a copy, in order to allow the $this->off method
				// to work properly
				$copy = [];

				foreach ( $this->events[ $eventName ] as &$subscriber ) {
					$copy[] = &$subscriber;
				}

				foreach ( $copy as &$subscriber ) {

					$subscriber['fireId'] = $this->fireId;

					call_user_func( $subscriber['callback'], $event );

					if ( $event->isPropagationStopped() ) {
						break;
					}
				}

				if ( isset( $this->events[ $eventName ] ) ) {

					// remove the fired "once" listeners
					for ( $i = count( $this->events[ $eventName ] ) - 1; $i >= 0; $i-- ) {

						if ( $this->events[ $eventName ][$i]['fireId'] === $this->fireId ) {
						
							if ( $this->events[ $eventName ][$i]['once'] === true ) {
								// remove event
								array_splice( $this->events[$eventName], $i, 1 );
							}
						
						}
					
					}

				}

			} catch ( \Exception $e ) {
				
				throw $e;
			}

		}
	}