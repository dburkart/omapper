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
			$this->create( $obj );
		} else {
			$this->load( $obj );
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
		$this->dataStore->create( $name, $fields );
		
		return $obj;
	}
	
	/**
	 * Saves an object to the data store.
	 *
	 * @param obj the object to save
	 */
	public function save( $obj ) {
		list( $name, $fields ) = $this->convert( $obj );
		$this->dataStore->save( $name, $fields );
	}
	
	/**
	 * Loads an object from the data store. Modifies and returns the object.
	 *
	 * @param obj the object to load into
	 * @return the object passed in
	 */
	public function load( &$obj ) {
		list( $name, $fields ) = $this->convert( $obj );
		$obj = $this->convert( $this->dataStore->load( $name, $fields ) );
		
		return $obj;
	}
	
	/**
	 * Delete an object from the data store. Sets the object to NULL.
	 *
	 * @param obj the object to delete
	 */
	public function delete( &$obj ) {
		list( $name, $fields ) = $this->convert( $obj );
		$this->dataStore->delete( $name, $fields );
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
	 * Convert an object to an tuple in the format expected by an IDataStore.
	 *
	 * @param obj the object to convert
	 * @return a tuple containing the name of the structure and an array
	 */
	private function convert( $a ) {
		if ( is_array( $a ) ) {
			return $this->convertToObject( $a );
		} else if ( is_object( $a ) ) {
			return $this->convertToArray( $a );
		} else {
			return false;
		}
	}
	
	private function convertToObject( $arr ) {
		list( $name, $fields ) = $arr;
		
		require_once $this->objDir . $name . '.php';
		
		$obj = new $name();
		
		foreach( $fields as $key => $val ) {
			$obj->$key = $val;
		}
		
		return $obj;
	}
	
	private function convertToArray( $obj ) {
		$fields = array();
	
		$reflect = new ReflectionClass( $obj );
		$props = $reflect->getProperties( ReflectionProperty::IS_PUBLIC );
		
		foreach ( $props as $prop ) {
			$fields[ $prop->name ] = $prop->getValue($obj);
		}
		
		return array( $reflect->getName(), $fields );
	}
}
