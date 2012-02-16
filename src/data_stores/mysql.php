<?php

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
		
		foreach ( $fields as $key => $value ) {
			if ( $key == 'id' ) continue;
			
			$keys 		.= "$key,";
			$values 	.= "$value,";
		}
		
		$keys[ strrpos( $keys, ',' )] = ')';
		$values[ strrpos( $values, ',' )] = ')';
	
		$query = "INSERT INTO $name $keys VALUES $values";
		$result = mysql_query( $query );
		
		return array( $name, $fields );
	}
	
	/**
	 * Save a record. An 'id' field should index the entry to be saved.
	 *
	 * @param name the name of the structure to save to
	 * @param fields an array containing new values for some (or all) fields
	 */
	public function save( $name, $fields ) {
		$columns = ' ';
		
		foreach ( $fields as $key => $value ) {
			if ( $key == 'id' ) continue;
			if ( empty( $value ) ) continue;
			
			$columns .= "$key='$value',";
		}
		
		$columns[ strlen( $columns ) - 1 ] = ' ';
		$columns = trim( $columns );
		
		if ( empty( $columns ) ) return array( $name, $fields );
		
		$query = "UPDATE $name SET $columns WHERE $name.id={$fields[id]} LIMIT 1";
		$result = mysql_query( $query );
		
		return array( $name, $fields );
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
		if ( !empty( $fields[ 'id' ] ) ) {
			$query = "SELECT * FROM $name WHERE $name.id={$fields[id]} LIMIT 1";
			
			$result = mysql_query( $query );
			$fields = mysql_fetch_assoc( $result );
			
			return array( $name, $fields );
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
	
	}
	
	/**
	 * Check whether the specified record exists in this data store.
	 *
	 * @param name the name of the storage structure
	 * @param id the id of the record in question
	 * @return true if the record exists, false otherwise
	 */
	public function peek( $name, $id ) {
		$query = "SELECT id FROM $name WHERE $name.id=$id";
		
		$result = mysql_query( $query );
		return mysql_count_rows( $result );
	}

}
