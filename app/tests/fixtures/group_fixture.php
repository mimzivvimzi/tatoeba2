<?php
/* Group Fixture generated on: 2014-04-15 22:03:33 : 1397592213 */
class GroupFixture extends CakeTestFixture {
	var $name = 'Group';

	var $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 100, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
	);

	var $records = array(
		array(
			'id' => '1',
			'name' => 'admin',
			'created' => '2009-01-11 21:23:50',
			'modified' => '2009-01-11 21:23:50'
		),
		array(
			'id' => '2',
			'name' => 'moderator',
			'created' => '2009-01-11 21:24:03',
			'modified' => '2009-01-11 21:24:03'
		),
		array(
			'id' => '3',
			'name' => 'trusted_user',
			'created' => '2009-01-11 21:24:13',
			'modified' => '2009-01-11 21:24:13'
		),
		array(
			'id' => '4',
			'name' => 'user',
			'created' => '2009-01-11 21:24:22',
			'modified' => '2009-01-11 21:24:22'
		),
		array(
			'id' => '5',
			'name' => 'inactive',
			'created' => '2009-01-11 21:24:29',
			'modified' => '2009-01-11 21:24:29'
		),
		array(
			'id' => '6',
			'name' => 'spammer',
			'created' => '2009-01-31 02:43:41',
			'modified' => '2009-01-31 02:43:41'
		),
	);
}
