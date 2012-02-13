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
 
require_once 'IDataStore.php';
 
class OMapper {

	private $dataStore;

	public function __construct( IDataStore $dataStore ) {
		$this->dataStore = $dataStore;
	}
	
	/**
	 * Creates a new object using the data store.
	 *
	 * @param obj the object to store
	 */
	public function create( object $obj ) {
	
	}
	
	/**
	 * Saves an object to the data store.
	 *
	 * @param obj the object to save
	 */
	public function save( object $obj ) {
	
	}
	
	/**
	 * Loads an object from the data store. Modifies and returns the object.
	 *
	 * @param obj the object to load into
	 * @return the object passed in
	 */
	public function load( object &$obj ) {
	
	}
	
	/**
	 * Delete an object from the data store. Sets the object to NULL.
	 *
	 * @param obj the object delete
	 */
	public function delete( object &$obj ) {
	
	}

}
