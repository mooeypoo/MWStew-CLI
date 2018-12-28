<?php

use MWStew\CLI\CreateExtensionCommand;
// use Symfony\Component\Console\Application
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CreateExtensionCommandTest extends KernelTestCase {
	public function testExecute()
	{
		// $kernel = self::bootKernel();
		// $application = new Application($kernel);

		$testPath = './tests/temp';
		$testName = 'testExtension';

		// $command = $application->find('create-extension');
		$command = new MWStew\CLI\CreateExtensionCommand();
		$commandTester = new CommandTester($command);
		$commandTester->execute(array(
			// 'command'  => $command->getName(),

			// pass arguments to the helper
			'name' => $testName,
			'--path' => $testPath
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
			file_exists( $testPath . '/' . $testName ),
			'Folder exists: ' . $testPath . '/' . $testName
		);

		$this->assertTrue(
			file_exists( $testPath . '/' . $testName . '/extension.json' ),
			'File exists: ' . $testPath . '/' . $testName . '/extension.json'
		);

		// Remove that folder
		$this->delTree( $testPath . '/' . $testName );
	}

	public static function delTree($dir) {
		$files = array_diff(scandir($dir), array('.','..'));
		foreach ($files as $file) {
			if ( is_dir( "$dir/$file" ) ) {
				self::delTree("$dir/$file");
			} else {
				unlink("$dir/$file");
			}
		}
		return rmdir($dir);
	}
}
