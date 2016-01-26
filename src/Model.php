<?php

	namespace browserfs;

	/**
	 * The \browserfs\Model class represents a entity which can have properties,
	 * and has methods used to obtain and set it's properties.
	 */
	class Model extends EventEmitter {

		protected $properties = [];

		/**
		 * Constructor. Creates a new Model, with it's properties
		 * loaded from @modelData argument ( optional ).
		 *
		 * @param modelData: [ key: <string | int > ] => any = null
		 * @throws \browserfs\Exception on invalid initialization modelData value
		 */
		public function __construct( $modelData = null ) {
			
			if ( $modelData !== null ) {

				if ( is_array( $modelData ) ) {

					foreach ( $modelData as $key => $value ) {

						$this->properties[ $key ] = $value;

					}

				} else {

					throw new \browserfs\Exception( 'Invalid argument $modelData: Expected hash | null');

				}

			}
		}

		/**
		 * Returns the value of the property whose name is $propertyName
		 * @param propertyName: string | int
		 * @throws \browserfs\Exception if the property name is invalid
		 */
		public function get( $propertyName ) {
			
			if ( self::isProperty( $propertyName ) ) {
				
				return array_key_exists( $propertyName, $this->properties )
					? $this->properties[ $propertyName ]
					: null;

			} else {
			
				throw new \browserfs\Exception('Invalid argument: $propertyName: value cannot be a key!' );
			
			}
		}

		/**
		 * Sets the value of the property whose name is $propertyName,
		 * to the @propertyValue.
		 *
		 * @param $propertyName: string | int
		 * @param $propertyValue: any
		 * @return boolean - true if value was set, false otherwise
		 * @fires  before-set( propertyName, newValue, oldValue )
		 * @fires  set ( propertyName, currentValue )
		 * @throws \browserfs\Exception if the property name is invalid
		 */
		public function set( $propertyName, $propertyValue ) {
			
			if ( self::isProperty( $propertyName ) ) {

				$event = $this->fire( 'before-set', $propertyName, $propertyValue, $this->get( $propertyName ) );
				
				if ( !$event->isPropagationStopped() ) {
					
					$this->properties[ $propertyName ] = $propertyValue;

					$this->fire( 'set', $propertyName, $propertyValue );

					return true;

				} else {

					return false;

				}

			} else {

				throw new \browserfs\Error('Invalid argument $propertyName: value cannot be a key!' );

			}

		}

		/**
		 * Determines if this model has a property called $propertyName.
		 * @param propertyName - string | int
		 * @return boolean
		 */
		public function hasProperty( $propertyName ) {
			
			if ( self::isProperty( $propertyName ) ) {
			
				return array_key_exists( $propertyName, $this->properties );
			
			} else {
			
				return false;
			
			}
		}

		/**
		 * Returns true if a value can be a key of a model
		 * @return boolean
		 */
		protected static function isProperty( $keyName ) {
			return ( is_int( $keyName ) && $keyName >= 0 ) || ( is_string( $keyName ) && $keyName != '' );
		}

	}