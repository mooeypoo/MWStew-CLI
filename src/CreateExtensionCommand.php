<?php

namespace MWStew\CLI;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CreateExtensionCommand extends Command {
	protected function configure() {
		$this
			->setName( 'create-extension' )
			->setDescription( 'Create the basic files needed to develop a new MediaWiki extension' )
			->addArgument(
				'name',
				InputArgument::REQUIRED,
				'Name of the extension. Alphabet or numbers only, no spaces.'
			)
			->addOption(
				'path',
				'p',
				InputOption::VALUE_REQUIRED,
				'The path for the new files. (Default: ./extensions and if not exist, current folder)',
				getcwd() . '/extensions'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$name = $input->getArgument( 'name' );

		try {
			$generator = new \MWStew\Builder\Generator( [ 'name' => $name ] );
		} catch ( \Exception $e ) {
			$output->writeln( 'There were errors while trying to create the extension files:' );
			$errors = json_decode( $e->getMessage() );

			foreach ( $errors as $eField => $eErrors ) {
				$output->writeln( '* ' . $eField . ': ' . $eErrors[0] );
			}
			return 1;
		}
		$output->writeln( 'Building extension...' );

		$path = $input->getOption( 'path' );

		$extPath = $path . '/' . $name;
		if ( file_exists( $extPath ) ) {
			// Path doesn't exist
			$output->writeln( 'A folder already exists: ' . $extPath );
			return 1;
		}

		// Create the new folder
		if ( !mkdir( $extPath, 0777, true ) ) {
			$output->writeln( 'Failed to create the path: ' .$extPath );
			return 1;
		}

		$output->writeln( 'Outputting to path: ' . $extPath );

		$files = $generator->getFiles();

		$failed = [];
		$succeeded = [];
		$foldersCreated = [];
		foreach ( $files as $fName => $fContent ) {
			// Deeal with nested folders
			if ( strpos( $fName, '/' ) !== false ) {
				// There are nested folders. Create them first
				$structure = explode( '/', $fName );
				array_pop( $structure ); // Remove the last piece (that's the file name)
				$folderToCreate = $extPath . '/' . implode( '/', $structure );
				if ( !in_array( $folderToCreate, $foldersCreated ) ) {
					// Create the deep folder if it's not created yet
					if ( !mkdir( $folderToCreate, 0777, true ) ) {
						$failed[] = $folderToCreate;
					} else {
						$succeeded[] = $folderToCreate;
					}
					// Add it to the created folders array
					$foldersCreated[] = $folderToCreate;
				}
			}

			// Create the files
			if ( file_put_contents( $extPath . '/' . $fName, $fContent ) ) {
				$succeeded[] = $extPath . '/' . $fName;
			} else {
				$failed[] = $extPath . '/' . $fName;
			}
		}

		if ( count( $succeeded ) ) {
			$output->writeln( 'Created files:' );
			foreach ( $succeeded as $success ) {
				$output->writeln( ' * ' . $success );
			}
		}
		if ( count( $failed ) ) {
			$output->writeln( 'Failed to create these files:' );
			foreach ( $failed as $fail ) {
				$output->writeln( ' * ' . $fail );
			}
		}
		$output->writeln( 'Finished. Files available at ' . $extPath );

		return 0;
	}

}
