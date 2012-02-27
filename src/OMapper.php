<?php
/*
 *      OMapper.php
 *      
 *      Copyright 2012 Dana Burkart <danaburkart@gmail.com>
 *      
 *      This program is free software; you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation; either version 2 of the License, or
 *      (at your option) any later version.
 *      
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *      
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, write to the Free Software
 *      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *      MA 02110-1301, USA.
 */
 
/**
 * The OMapper class is the main object mapping class. It takes an object
 * implementing the IDataStore interface which is used to manage the data being
 * mapped by OMapper.
 *
 * @author Dana Burkart
 */
 
define( 'DIR_DATASTORES', 'data_stores/' );
 
require_once 'IDataStore.php';
 
class OMapper {

	private $dataStore;
	private $objDir;

	public function __construct( $dataStore, $objDir ) {
		if ( is_object( $dataStore ) ) {
			$this->dataStore = $dataStore;
		} else {
			require_once DIR_DATASTORES . $dataStore . '.php';
			$this->dataStore = new $dataStore();
		}
		
		$this->objDir = $objDir;
	}
	
	/**
	 * If the object already exists in the data store, load it, otherwise create
	 * it.
	 */
	public function restore( &$obj ) {
		if ( !$this->peek( $obj ) ) {
			$obj = $this->create( $obj );
		} else {
			$obj = $this->load( $obj );
		}
		
		return $obj;
	}
	
	/**
	 * Creates a new object using the data store.
	 *
	 * @param obj the object to store
	 */
	public function create( &$obj ) {
		list( $name, $fields ) = $this->convert( $obj );
		$obj = $this->convert( $this->dataStore->create( $name, $fields ) );
		
		return $obj;
	}
	
	/**
	 * Saves an object to the data store.
	 *
	 * @param obj the object to save
	 */
	public function save( &$obj ) {
		list( $name, $fields ) = $this->convert( $obj, 'save' );
		$obj = $this->convert( $this->dataStore->save( $name, $fields ) );
		
		return $obj;
	}
	
	/**
	 * Loads an object from the data store. Modifies and returns the object.
	 *
	 * @param obj the object to load into
	 * @return the object passed in
	 */
	public function load( &$obj ) {
		$callhook = false;
		$reflect = new ReflectionClass( $obj );
		if ( $reflect->hasMethod( "__hook_load" ) ) $callhook = true;
	
		list( $name, $fields ) = $this->convert( $obj );
		
		$ld = $this->dataStore->load( $name, $fields );
		
		if ( $ld ) {
			if ( is_array( $ld[1][0] ) ) {
				$range = array();
				
				foreach ( $ld[1] as $o ) {
					$o = $this->convert( array( $ld[0], $o ), 'load' );
					if ( $callhook ) $o->__hook_load( $this );
					$range[] = $o;
				}
				
				$obj = $range;
				return $range;
			} else {
				$obj = $this->convert( $ld, 'load' );
				if ( $callhook ) $obj->__hook_load( $this );
				return $obj;
			}
		} else {
			return $ld;
		}
	}
	
	/**
	 * Delete an object from the data store. Sets the object to NULL.
	 *
	 * @param obj the object to delete
	 */
	public function delete( &$obj ) {
		list( $name, $fields ) = $this->convert( $obj );
		$obj = $this->convert( $this->dataStore->delete( $name, $fields ), 'delete' );
		
		return $obj;
	}
	
	/**
	 * Check whether or not an object exists in the data store.
	 *
	 * @param obj the object to check
	 * @return true if the object exists in the data store, false otherwise.
	 */
	public function peek( $obj ) {
		list( $name, $fields ) = $this->convert( $obj );
		
		if ( isset( $fields['id'] ) ) {
			return $this->dataStore->peek( $name, $fields['id'] );
		}
		
		return false;
	}

	//---- Private functions -------------------------------------------------//
	
	/**
	 * Converts between tuples and objects.
	 *
	 * @param a the tuple or object to convert
	 * @param recurse whether or not to recurse
	 * @return false if a is not a tuple or object, the converted entity 
	 *		otherwise
	 */
	private function convert( $a, $recurse=false ) {
		if ( is_array( $a ) ) {
			return $this->convertToObject( $a, $recurse );
		} else if ( is_object( $a ) ) {
			return $this->convertToArray( $a, $recurse );
		} else {
			return false;
		}
	}
	
	/**
	 * Convert a tuple to an object.
	 *
	 * @param arr the tuple to convert
	 * @param whether or not to recurse
	 */
	private function convertToObject( $arr, $recurse ) {
		list( $name, $fields ) = $arr;
		
		require_once $this->objDir . $name . '.php';
		
		$obj = new $name();
		
		foreach( $fields as $key => $val ) {
			if ( $key[0] == '_' ) {
				if ( $recurse ) {
					// The function name is stored in $recurse
					$func = $recurse;
					
					// Get rid of object notation (underscore)
					$subName = substr( $key, 1 );
					
					// Load up a new instance of the specified object
					require_once $this->objDir.$subName.'.php';
					$o = new $subName();
					
					// Set the id and recurse
					$o->id = $val;
					$o = $this->$func( $o );
					
					// Add the sub-object back in
					$obj->$key = $o;
				}
			} else {
				$obj->$key = $val;
			}
		}
		
		return $obj;
	}
	
	/**
	 * Convert an object to an tuple in the format expected by an IDataStore.
	 *
	 * @param obj the object to convert
	 * @param recurse whether or not to recurse
	 * @return a tuple containing the name of the structure and an array
	 */
	private function convertToArray( $obj, $recurse ) {
		$fields = array();
	
		$reflect = new ReflectionClass( $obj );
		$props = $reflect->getProperties( ReflectionProperty::IS_PUBLIC );
		
		foreach ( $props as $prop ) {
			if ( $prop->name[0] == '_' ) {
				if ( $recurse ) {
					$subObj = $prop->getValue($obj);
					$fields[ $prop->name ] = $subObj->id;
					
					// The name of the function to call is in $recurse
					$func = $recurse;
					
					// Get rid of the object notation (underscore)
					$name = substr( $prop->name, 1 );
					
					// Don't blow away the object if we're saving
					if ( $func == 'save' || $func == 'delete' ) {
						$this->$func( $prop->getValue( $obj ) );
					} 
					// Create a new object
					else if ( empty( $fields[ $prop->name ] ) && 
							( $func == 'create' || $func == 'restore' ) ) {
							
						// Find the object, and make a new instance
						require_once $this->objDir.$name.'.php';
						$o = new $name();
						
						// Set the id (should be empty)
						$o->id = $fields[ $prop->name ];
					
						// Recurse on the sub-object
						$o = $this->$func( $o );
						
						// Set the object id to the new id
						$fields[ $prop->name ] = $o->id;
					}
				}
			} else {
				$fields[ $prop->name ] = $prop->getValue($obj);
			}
		}
		
		return array( $reflect->getName(), $fields );
	}
}
