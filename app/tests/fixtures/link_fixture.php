<?php
/* SentencesTranslation Fixture generated on: 2014-04-15 01:02:28 : 1397516548 */
class LinkFixture extends CakeTestFixture {
	var $name = 'Link';
	var $table = 'sentences_translations';

	var $fields = array(
		'sentence_id' => array('type' => 'integer', 'null' => false, 'default' => NULL),
		'translation_id' => array('type' => 'integer', 'null' => false, 'default' => NULL),
		'sentence_lang' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 4, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'translation_lang' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 4, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'distance' => array('type' => 'integer', 'null' => false, 'default' => '1', 'length' => 2),
		'indexes' => array('sentence_id' => array('column' => array('sentence_id', 'translation_id'), 'unique' => 1), 'translation_id' => array('column' => 'translation_id', 'unique' => 0), 'sentence_lang' => array('column' => 'sentence_lang', 'unique' => 0), 'translation_lang' => array('column' => 'translation_lang', 'unique' => 0), 'sentence_id_2' => array('column' => 'sentence_id', 'unique' => 0)),
	);

	var $records = array(
		array(
			'sentence_id' => '1',
			'translation_id' => '2',
			'sentence_lang' => NULL,
			'translation_lang' => NULL,
			'distance' => '1'
		),
		array(
			'sentence_id' => '2',
			'translation_id' => '1',
			'sentence_lang' => NULL,
			'translation_lang' => NULL,
			'distance' => '1'
		),
		array(
			'sentence_id' => '1',
			'translation_id' => '3',
			'sentence_lang' => NULL,
			'translation_lang' => NULL,
			'distance' => '1'
		),
		array(
			'sentence_id' => '3',
			'translation_id' => '1',
			'sentence_lang' => NULL,
			'translation_lang' => NULL,
			'distance' => '1'
		),
		array(
			'sentence_id' => '1',
			'translation_id' => '4',
			'sentence_lang' => NULL,
			'translation_lang' => NULL,
			'distance' => '1'
		),
		array(
			'sentence_id' => '4',
			'translation_id' => '1',
			'sentence_lang' => NULL,
			'translation_lang' => NULL,
			'distance' => '1'
		),
		array(
			'sentence_id' => '2',
			'translation_id' => '4',
			'sentence_lang' => NULL,
			'translation_lang' => NULL,
			'distance' => '1'
		),
		array(
			'sentence_id' => '4',
			'translation_id' => '2',
			'sentence_lang' => NULL,
			'translation_lang' => NULL,
			'distance' => '1'
		),
		array(
			'sentence_id' => '2',
			'translation_id' => '5',
			'sentence_lang' => NULL,
			'translation_lang' => NULL,
			'distance' => '1'
		),
		array(
			'sentence_id' => '5',
			'translation_id' => '2',
			'sentence_lang' => NULL,
			'translation_lang' => NULL,
			'distance' => '1'
		),
		array(
			'sentence_id' => '4',
			'translation_id' => '6',
			'sentence_lang' => NULL,
			'translation_lang' => NULL,
			'distance' => '1'
		),
		array(
			'sentence_id' => '6',
			'translation_id' => '4',
			'sentence_lang' => NULL,
			'translation_lang' => NULL,
			'distance' => '1'
		),
	);
}
