<?php

	class ModelTest extends PHPUnit_Framework_TestCase {


		public function instantiationProvider() {
			return [
				[ false, 2 ],
				[ false, '2' ],
				[ true, null ],
				[ true, [ 'foo' => 3 ] ],
				[ true, [ 'bar', 'car' ] ]
			];
		}

		/**
		 * @dataProvider instantiationProvider
		 */
		public function testInstantiation( $assertEquals, $initializationArgs ) {

			$result = false;

			try {

				new \browserfs\Model( $initializationArgs );

				$result = true;

			} catch ( \browserfs\Exception $e ) {

				$result = false;

			}

			$this->assertEquals( $assertEquals, $result );

		}

		public function testIfModelTriggers_BeforeSet_And_Set_Events() {

			$model = new \browserfs\Model( [ 'foo' => 2 ] );

			$beforeSet = false;
			$afterSet  = false;

			$oldVal = null;
			$newVal = null;
			$propertyName = null;

			$currentVal = null;
			$currentProperty = null;

			$model->on( 'before-set', function( \browserfs\Event $e ) use ( &$beforeSet, &$propertyName, &$newVal, &$oldVal ) {
				
				$beforeSet = true;

				$propertyName = $e->getArg(0);
				$newVal = $e->getArg(1);
				$oldVal = $e->getArg(2);

			} );

			$model->on( 'set', function( \browserfs\Event $e ) use ( &$afterSet, &$currentVal, &$currentProperty ) {
				$afterSet = true;
				$currentProperty = $e->getArg(0);
				$currentVal = $e->getArg(1);
			} );

			$model->set( 'foo', 'bar' );

			$this->assertEquals( true, $beforeSet );
			$this->assertEquals( true, $afterSet );
			$this->assertEquals( $propertyName, 'foo' );
			$this->assertEquals( $oldVal, 2 );
			$this->assertEquals( $newVal, 'bar' );
			$this->assertEquals( $currentVal, 'bar' );
			$this->assertEquals( $currentProperty, 'foo' );

		}

		public function testIfWeCanCancelAPropertySetter() {

			$model = new \browserfs\Model();

			$model->on( 'before-set', function( \browserfs\Event $e ) {
				$e->stopPropagation();
			} );

			$model->set( 'foo', 2 );

			$this->assertEquals( $model->get('foo'), null );

		}

	}