<?php

namespace MWStew\CLI;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class CreateExtensionCommand extends Command {
	private $styles = [];

	public function __construct( string $name = null ) {
		parent::__construct( $name );

		$this->styles = [
			'mw' => new OutputFormatterStyle( 'yellow', 'black', [ 'bold' ] ),
			'code' => new OutputFormatterStyle( 'green', 'black' ),
			'stop' => new OutputFormatterStyle( 'red', 'black', [ 'bold' ] ),
			'error' => new OutputFormatterStyle( 'red' ),
			'working' => new OutputFormatterStyle( 'green', 'default', [ 'bold' ] ),
		];
	}
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
				'The path for the new files.',
				getcwd() . '/extensions'
			)
			->addOption(
				'author',
				'a',
				InputOption::VALUE_REQUIRED,
				'Extension author.',
				''
			)
			->addOption(
				'title',
				't',
				InputOption::VALUE_REQUIRED,
				'Extension display title.',
				''
			)
			->addOption(
				'description',
				'desc',
				InputOption::VALUE_REQUIRED,
				'A short description for the extension',
				''
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		// Register all styles
		foreach ( $this->styles as $styleName => $styleObject ) {
			$output->getFormatter()->setStyle( $styleName, $styleObject );
		}

		$output->writeln( '' );
		$output->writeln( $this->getMediaWikiAscii( 'mw' ) );
		$output->writeln( $this->getMWStewAscii() );
		$output->writeln( [
			'<mw>          =*=*= MediaWiki extension maker =*=*=           </>',
			''
		] );

		$name = $input->getArgument( 'name' );
		$data = [
			'name' => $name,
			'author' => $input->getOption( 'author' ),
			'title' => $input->getOption( 'title' ),
			'description' => $input->getOption( 'description' ),
		];

		try {
			$generator = new \MWStew\Builder\Generator( $data );
		} catch ( \Exception $e ) {
			$output->writeln( $this->outError( 'There were errors while trying to create the extension files:' ) );
			$errors = json_decode( $e->getMessage() );
			foreach ( $errors as $eField => $eErrors ) {
				$output->writeln( '* ' . $eField . ': ' . $eErrors[0] );
			}
			return 1;
		}
		$path = $input->getOption( 'path' );
		$extPath = $path . '/' . $name;

		$output->writeln( '<working>Starting...</>' );
		$output->writeln( 'Building extension in "<code>' . $extPath . '</>"' );

		if ( file_exists( $extPath ) ) {
			// Path doesn't exist
			$output->writeln( $this->outError( 'The folder already exists: ' . $extPath ) );
			return 1;
		}

		// Create the new folder
		if ( !mkdir( $extPath, 0777, true ) ) {
			$output->writeln( $this->outError( 'Failed to create the requested folder: ' . $extPath ) );
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
			$output->writeln( '<error>Failed to create these files:</>' );
			foreach ( $failed as $fail ) {
				$output->writeln( ' * ' . $fail );
			}
		}
		$output->writeln( '<info>Finished. Files available at ' . $extPath .'</info>' );

		$output->writeln( '' );
		return 0;
	}

	protected function outError( $str = '' ) {
		return [
			'<stop>Stopping.</> <error>' . $str . '</>',
			''
		];
	}

	protected function getMediaWikiAscii( $style = null ) {
		$ascii = [
			'       __  __          _ _    __          ___ _    _      ',
			'      |  \/  |        | (_)   \ \        / (_) |  (_)     ',
			'      | \  / | ___  __| |_  __ \ \  /\  / / _| | ___      ',
			'      | |\/| |/ _ \/ _` | |/ _` \ \/  \/ / | | |/ / |     ',
			'      | |  | |  __/ (_| | | (_| |\  /\  /  | |   <| |     ',
			'      |_|  |_|\___|\__,_|_|\__,_| \/  \/   |_|_|\_\_|     ',
		];

		return $this->addStyleToArray( $ascii, $style );
	}

	protected function getMWStewAscii( $style = null ) {
		$ascii = [
			' ███╗   ███╗██╗    ██╗███████╗████████╗███████╗██╗    ██╗',
			' ████╗ ████║██║    ██║██╔════╝╚══██╔══╝██╔════╝██║    ██║',
			' ██╔████╔██║██║ █╗ ██║███████╗   ██║   █████╗  ██║ █╗ ██║',
			' ██║╚██╔╝██║██║███╗██║╚════██║   ██║   ██╔══╝  ██║███╗██║',
			' ██║ ╚═╝ ██║╚███╔███╔╝███████║   ██║   ███████╗╚███╔███╔╝',
			' ╚═╝     ╚═╝ ╚══╝╚══╝ ╚══════╝   ╚═╝   ╚══════╝ ╚══╝╚══╝ ',
		];
		return $this->addStyleToArray( $ascii, $style );
	}

	protected function addStyleToArray( $ascii = [], $style = null ) {
		if ( $style ) {
			$new = [];
			foreach ( $ascii as $a ) {
				$new[] = '<' . $style . '>' . $a . '</>';
			}
			return $new;
		}
		return $ascii;
	}
}
