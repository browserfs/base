<?php
	
	namespace browserfs\Model;

	interface Mapper {

		/**
		 * Saves a model to it's storage
		 * @param $model \browserfs\Model object
		 * @throws \browserfs\Exception
		 * @return void
		 */
		public function save( \browserfs\Model $model );

		/**
		 * Fetch a list of models from their storage, and returns them
		 * @param  @cryteria: a hash containing the cryteria that's to be searched
		 * @param  @skip: skip [x] objects from the result set. Use null to disable
		 * @param  @limit: limit [x] objects from the result set. Use null to disable
		 * @return \browserfs\Collection
		 */
		public function fetch( array $cryteria, int $skip = null, int $limit = null );

		/**
		 * Fetch a single model from it's storage, based by it's id, and returns it
		 * @param $objectId - the id of the object
		 * @return \browserfs\model | null
		 */
		public function fetchById( $objectId );

		/**
		 * Removes a model from it's storage.
		 */
		public function remove( \browserfs\Model $model );

	}