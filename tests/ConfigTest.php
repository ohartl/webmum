<?php

/**
 * @covers Config
 */
class ConfigTest extends PHPUnit_Framework_TestCase
{

	public function setUp()
	{
		Config::init(
			array(
				'test-value' => 123,
				'test' => array(
					'deep' => array(
						'deeper' => 'value',
					)
				)
			)
		);
	}


	public function testInit()
	{
		$this->assertEquals(Config::get('test-value'), 123);
		$this->assertEquals(Config::get('test.deep.deeper'), 'value');
	}


	public function testSet()
	{
		$this->assertEquals(Config::get('test-value'), 123);
		Config::set('test-value', false);
		$this->assertEquals(Config::get('test-value'), false);

		$this->assertEquals(Config::get('test.deep.deeper'), 'value');
		Config::set('test.deep.deeper', 'other');
		$this->assertEquals(Config::get('test.deep.deeper'), 'other');

		Config::set('test.new.deep.deeper', true);
		$this->assertEquals(Config::get('test.new.deep.deeper'), true);
	}


	public function testGet()
	{
		$this->assertTrue(is_array(Config::get(null)));

		$this->assertEquals(Config::get('test-value'), 123);

		$this->assertEquals(Config::get('test.deep.deeper'), 'value');

		$this->assertEquals(Config::get('test-default', 123456), 123456);
	}


	public function testHas()
	{
		$this->assertTrue(Config::has('test-value'));

		$this->assertTrue(Config::has('test.deep.deeper'));

		$this->assertFalse(Config::has('test-default'));

		Config::init(null);
		$this->assertFalse(Config::has(null));
	}
}