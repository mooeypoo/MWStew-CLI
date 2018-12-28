<?php

use MWStew\CLI\CreateExtensionCommand;
// use Symfony\Component\Console\Application
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use org\bovigo\vfs\vfsStream;

class CreateExtensionCommandTest extends KernelTestCase {
	protected $root;
	/**
     * set up test environmemt
     */
    public function setUp()
    {
        $this->root = vfsStream::setup('testDir');
    }

	public function testExecute()
	{
		$testName = 'testExtension';

		$command = new MWStew\CLI\CreateExtensionCommand();
		$commandTester = new CommandTester($command);
		$commandTester->execute(array(
			'name' => $testName,
			'--path' => vfsStream::url('testDir')
		));

		// the output of the command in the console
		$output = $commandTester->getDisplay();
		$this->assertContains(
			'Finished',
			$output,
			'Command finished successfully.'
		);

		// Test that there's a folder
		$this->assertTrue(
			$this->root->hasChild( $testName ),
			'Folder exists: ' . $testName
		);

		$this->assertTrue(
			$this->root->hasChild( $testName . '/extension.json' ),
			'File exists: ' . $testName . '/extension.json'
		);

	}
}
