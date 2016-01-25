<?php

	namespace browserfs;

	class Event {

		protected $stopped = false;
		protected $name    = null;
		protected $args    = null;

		/**
		 * Constructor. Private, use ::create() method for creating a new Event
		 */
		protected function __construct( $eventName, $eventArgs ) {
			$this->name = $eventName;
			$this->args = $eventArgs;
		}

		/**
		 * stops the event propagation. no event listeners callbacks
		 * won't process the event from now on.
		 * @return void
		 * @throws \browserfs\Exception IF event has been allready stopped
		 */
		public function stopPropagation() {
			if ( !$this->stopped ) {
				$this->stopped = true;
			} else {
				throw new \browserfs\Exception('Event is allready stopped!');
			}
		}

		/**
		 * Returns whether the event is stopped or not
		 * @return boolean
		 */
		public function isPropagationStopped() {
			return $this->stopped;
		}

		/**
		 * Returns the arguments of the event, during it's creation
		 * @return string[]
		 */
		public function getArgs() {
			return $this->args;
		}

		/**
		 * Returns the argument number $index
		 * @return any;
		 */
		public function getArg( $index ) {
			return isset( $this->args[ $index ] )
				? $this->args[ $index ]
				: null;
		}

		/**
		 * Returns the number of arguments passed to the
		 * fire method of the event emitter that fired this
		 * event.
		 * @return int
		 */
		public function numArgs() {
			return count( $this->args );
		}

		/**
		 * Returns the name of the event
		 * @return string
		 */
		public function getName() {
			return $this->name;
		}

		/**
		 * Creates a new event. Static constructor.
		 * @param eventName - string - the name of the event
		 * @param eventArgs - any[] | null - event arguments.
		 * @throws \browserfs\Exception - if arguments are invalid
		 * @return \browserfs\Event
		 */
		public static function create( $eventName, $eventArgs = null ) {
			
			if ( !is_string( $eventName ) ) {
				throw new \browserfs\Exception('Invalid argument $eventName: string expected!');
			}
			
			if ( null !== $eventArgs && !is_array( $eventArgs ) ) {
				throw new \browserfs\Exception('Invalid argument $eventArgs: any[] | null expected');
			}

			return new static( $eventName, null === $eventArgs ? [] : array_values( $eventArgs ) );
		}

	}