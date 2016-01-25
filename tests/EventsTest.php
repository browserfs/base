<?php

	class EventsTest extends PHPUnit_Framework_TestCase {

		public function testThisDotOn() {
			$emitter = new \browserfs\EventEmitter();

			$tick = 0;

			$emitter->on( 'tick', function() use ( &$tick ) {
				$tick++;
			} );

			$emitter->fire( 'tick' );
			$emitter->fire( 'tick' );

			$this->assertEquals( true, $tick === 2 );

		}

		public function testThisDotOnce() {

			$emitter = new \browserfs\EventEmitter();

			$tick = 0;

			$emitter->once( 'tick', function() use ( &$tick ) {
				$tick++;
			} );

			$emitter->fire( 'tick' );
			$emitter->fire( 'tick' );

			$this->assertEquals( true, $tick === 1 );

		}

		public function testThisDotOff() {

			$emitter = new \browserfs\EventEmitter();

			$tick = 0;

			$emitter->on( 'tick', $cb = function() use ( &$tick ) {
				$tick++;
			} );

			$emitter->fire( 'tick' );

			$emitter->off( 'tick', $cb );

			$emitter->fire( 'tick' );

			$this->assertEquals( true, $tick === 1 );

		}

		public function testThisDotOffInsideCallback() {

			$emitter = new \browserfs\EventEmitter();

			$tick = 0;

			$emitter->on( 'tick', $cb = function() use ( &$tick, $emitter ) {
				$tick++;
				$emitter->off( 'tick' );
			} );

			$emitter->on( 'tick', $cb = function() use ( &$tick ) {
				$tick++;
			} );


			$emitter->fire( 'tick' );

			$this->assertEquals( true, $tick === 2 );

			$emitter->fire( 'tick' );

			$this->assertEquals( true, $tick === 2 );

		}

		public function testThisDotstopPropagation() {

			$emitter = new \browserfs\EventEmitter();

			$tick = 0;

			$emitter->on( 'tick', function( \browserfs\Event $e ) {
				$e->stopPropagation();
			});

			// this should not run
			$emitter->on( 'tick', function() use ( &$tick ) {
				$tick++;
			});

			$emitter->fire( 'tick' );

			$this->assertEquals( true, $tick === 0 );

		}

		public function testEventArguments() {

			$emitter = new \browserfs\EventEmitter();

			$tick = 0;

			$emitter->on( 'tick', function( \browserfs\Event $e ) use ( &$tick ) {

				$args = $e->getArgs();

				$tick = $args[0] + $args[1];

			});

			$emitter->fire( 'tick', 1, 2 );

			$this->assertEquals( true, $tick === 3 );

		}


	}