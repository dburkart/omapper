<?php
/*
 *      session.php
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

require_once 'IDataStore.php';

class Session implements IDataStore {
	public function __construct() {
		// Start the session
		session_start();
	}

	/**
	 * Create a new record. An 'id' field is ignored, as that should be managed
	 * by the data store.
	 *
	 * @param name the name of the storage structure
	 * @param fields an array containing any fields being initialized
	 */
	public function create( $name, $fields ) {
		// If the id isn't set, find the next available id.
		if ( !isset( $fields['id'] ) ) {
			$i = 1;
			
			while ( isset( $_SESSION[$name.':'.$i] ) ) {
				$i++;
			}
			
			$fields['id'] = $i;
		}
		
		if ( $this->peek( $name, $fields['id'] ) ) {
			return false;
		}
		
		$_SESSION[ $name . ':' . $fields['id'] ] = $fields;
		
		return array( $name, $_SESSION[ $name . ':' . $fields['id'] ] );
	}
	
	/**
	 * Save a record. An 'id' field should index the entry to save to.
	 *
	 * @param name the name of the structure to save to
	 * @param fields an array containing new values for some (or all) fields
	 */
	public function save( $name, $fields ) {
		if ( !isset( $fields['id'] ) || 
			 !isset( $_SESSION[ $name . ':' . $fields['id'] ] ) ) {
			return false;
		}
		
		$_SESSION[ $name . ':' . $fields['id'] ] = $fields;
	}
	
	/**
	 * Load a record. An 'id' field should index the entry to be loaded. Altern-
	 * itively, if no 'id' field is supplied, an array of records will be loaded
	 * that matches any filled in fields.
	 *
	 * @param name the name of the structure to load from
	 * @param fields an array containing fields to be loaded
	 */
	public function load( $name, $fields ) {
		if ( isset( $fields['id'] ) ) {
			return array( $name, $_SESSION[ $name . ':' . $fields['id'] ] );
		} else {
			
		}
	}
	
	/**
	 * Delete record(s). It is expected that all entries with matching fields
	 * will be deleted.
	 *
	 * @param name the name of the storage structure
	 * @param fields the fields to match
	 */
	public function delete( $name, $fields ) {
		unset( $_SESSION[ $name . ':' . $fields['id'] ] );
	}
	
	/**
	 * Check whether the specified record exists in this data store.
	 *
	 * @param name the name of the storage structure
	 * @param id the id of the record in question
	 * @return true if the record exists, false otherwise
	 */
	public function peek( $name, $id ) {
		return isset( $_SESSION[ $name . ':' . $id ] );
	}
}
