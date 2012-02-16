<?php
/*
 *      mysql.php
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

class mysql implements IDataStore {

	public function __construct() {
		$user = "test";
		$pw = "test";
		$host = "localhost";
		
		$thedb="testdb";

		$db = mysql_connect($host,$user,$pw);
		mysql_select_db($thedb,$db);
	}

	/**
	 * Create a new record. An 'id' field is ignored, as that should be managed
	 * by the data store.
	 *
	 * @param name the name of the storage structure
	 * @param fields an array containing any fields being initialized
	 */
	public function create( $name, $fields ) {
		$keys = '(';
		$values = '(';
		$origName = $name;
		$name = strtolower( $name );
		
		foreach ( $fields as $key => $value ) {
			if ( $key == 'id' ) continue;
			
			$keys 		.= "$key,";
			$values 	.= "'$value',";
		}
		
		$keys[ strrpos( $keys, ',' )] = ')';
		$values[ strrpos( $values, ',' )] = ')';
	
		$query = mysql_real_escape_string("INSERT INTO $name $keys VALUES $values");
		$result = mysql_query( $query );
		$fields[ 'id' ] = mysql_insert_id( $result );
		
		return array( $origName, $fields );
	}
	
	/**
	 * Save a record. An 'id' field should index the entry to be saved.
	 *
	 * @param name the name of the structure to save to
	 * @param fields an array containing new values for some (or all) fields
	 */
	public function save( $name, $fields ) {
		$columns = ' ';
		$origName = $name;
		$name = strtolower( $name );
		
		foreach ( $fields as $key => $value ) {
			if ( $key == 'id' ) continue;
			if ( empty( $value ) ) continue;
			
			$columns .= "$key='$value',";
		}
		
		$columns[ strlen( $columns ) - 1 ] = ' ';
		$columns = trim( $columns );
		
		if ( empty( $columns ) ) return array( $name, $fields );
		
		$query = mysql_real_escape_string("UPDATE $name SET $columns WHERE $name.id={$fields[id]} LIMIT 1");
		$result = mysql_query( $query );
		
		return array( $origName, $fields );
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
		$origName = $name;
		$name = strtolower( $name );
	
		if ( !empty( $fields[ 'id' ] ) ) {
			$query = mysql_real_escape_string("SELECT * FROM $name WHERE $name.id={$fields[id]} LIMIT 1");
			
			$result = mysql_query( $query );
			if ( mysql_numrows( $result ) <= 0 ) return false;
			
			$fields = mysql_fetch_assoc( $result );
			
			return array( $origName, $fields );
		} else {
			$sel = '';
			
			foreach ( $fields as $key => $value ) {
				if ( empty( $value ) ) continue;
				
				$sel .= " $name.$key='$value' AND";
			}
			
			$sel = substr( $sel, 0, -4 );
			
			$query = mysql_real_escape_string("SELECT * FROM $name WHERE$sel");
			$result = mysql_query( $query );
			
			if ( mysql_numrows( $result ) <= 0 ) return false;
			
			$fields = mysql_fetch_assoc( $result );
			
			return array( $origName, $fields );
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
		$origName = $name;
		$name = strtolower( $name );
	
	}
	
	/**
	 * Check whether the specified record exists in this data store.
	 *
	 * @param name the name of the storage structure
	 * @param id the id of the record in question
	 * @return true if the record exists, false otherwise
	 */
	public function peek( $name, $id ) {
		$name = strtolower( $name );
		
		$query = mysql_real_escape_string("SELECT id FROM $name WHERE $name.id=$id");
		
		$result = mysql_query( $query );
		return mysql_count_rows( $result );
	}

}
