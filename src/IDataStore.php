<?php
/*
 *      IDataStore.php
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
 * The IDataStore interface specifies an interface between OMapper and some
 * specific data storage structure.
 *
 * @author Dana Burkart
 */

interface IDataStore {

	/**
	 * Create a new record. An 'id' field is ignored, as that should be managed
	 * by the data store.
	 *
	 * @param name the name of the storage structure
	 * @param fields an array containing any fields being initialized
	 */
	public function create( string $name, array $fields );
	
	/**
	 * Update a record. An 'id' field should index the entry to be deleted.
	 *
	 * @param name the name of the structure to update
	 * @param fields containing new values
	 */
	public function update( string $name, array $fields );
	
	/**
	 * Delete record(s). It is expected that all entries with matching fields
	 * will be deleted.
	 *
	 * @param name the name of the storage structure
	 * @param fields the fields to match
	 */
	public function delete( string $name, array $fields );

}