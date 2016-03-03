<?php

/**
 * @covers Message
 */
class MessageTest extends PHPUnit_Framework_TestCase
{

	public function testSingleton()
	{
		$this->assertInstanceOf('Message', Message::getInstance());
	}


	public function testAdd()
	{
		Message::getInstance()->add(Message::TYPE_SUCCESS, 'lorem');

		$out = Message::getInstance()->render();

		$this->assertContains(Message::TYPE_SUCCESS, $out);
		$this->assertContains('lorem', $out);
	}


	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testAddRestrictTypes()
	{
		Message::getInstance()->add('wrong-type', 'lorem');
	}


	public function testAddShortcuts()
	{
		Message::getInstance()->fail('lorem');
		$this->assertContains(Message::TYPE_FAIL, Message::getInstance()->render());

		Message::getInstance()->error('lorem');
		$this->assertContains(Message::TYPE_ERROR, Message::getInstance()->render());

		Message::getInstance()->warning('lorem');
		$this->assertContains(Message::TYPE_WARNING, Message::getInstance()->render());

		Message::getInstance()->success('lorem');
		$this->assertContains(Message::TYPE_SUCCESS, Message::getInstance()->render());
	}

}