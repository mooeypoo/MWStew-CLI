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
			)
			->addOption(
				'url',
				'url',
				InputOption::VALUE_REQUIRED,
				'URL for the extension documentation',
				''
			)
			->addOption(
				'license',
				'l',
				InputOption::VALUE_REQUIRED,
				'License for the extension. Available options: MIT, Apache-2.0, GPL-2.0+'
			)
			->addOption(
				'js',
				'js',
				InputOption::VALUE_OPTIONAL,
				'Add files for a JavaScript development environment',
				false
			)
			->addOption(
				'php',
				'php',
				InputOption::VALUE_OPTIONAL,
				'Add files for a PHP development environment',
				false
			)
			->addOption(
				'specialname',
				'sn',
				InputOption::VALUE_REQUIRED,
				'Name for a special page to be included in the extension. Must be a valid MediaWiki title',
				''
			)
			->addOption(
				'specialtitle',
				'st',
				InputOption::VALUE_REQUIRED,
				'A readable title for the special page. Requires the --specialname option to be given as well.',
				''
			)
			->addOption(
				'specialintro',
				'si',
				InputOption::VALUE_REQUIRED,
				'An introduction text that will appear at the top of the special page. Requires the --specialname option to be given as well.',
				''
			);
	}

	protected function getOutputHeader() {
		return array_merge(
			[ '' ],
			$this->getMediaWikiAscii( 'mw' ),
			$this->getMWStewAscii(),
			[ '<mw>          =*=*= MediaWiki extension maker =*=*=           </>' ],
			[ '' ]
		);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		// Register all styles
		foreach ( $this->styles as $styleName => $styleObject ) {
			$output->getFormatter()->setStyle( $styleName, $styleObject );
		}

		$output->writeln( $this->getOutputHeader() );

		$name = $input->getArgument( 'name' );
		$data = [
			'name' => $name,
			'author' => $input->getOption( 'author' ),
			'title' => $input->getOption( 'title' ),
			'description' => $input->getOption( 'description' ),
			'url' => $input->getOption( 'url' ),
			'specialpage_name' => $input->getOption( 'specialname' ),
			'specialpage_title' => $input->getOption( 'specialtitle' ),
			'specialpage_intro' => $input->getOption( 'specialintro' ),
		];

		if ( $input->getOption( 'js' ) !== false ) {
			$data['dev_js'] = true;
		}
		if ( $input->getOption( 'php' ) !== false ) {
			$data['dev_php'] = true;
		}

		// Validate license
		$license = $input->getOption( 'license' );
		$validLicenses = [ 'MIT', 'Apache-2.0', 'GPL-2.0+' ];
		if ( $license ) {
			if ( in_array( $license, $validLicenses ) ) {
				$data['license'] = $license;
			} else {
				$output->writeln( $this->outError(
					'Chosen license "' . $license . '" is invalid.' .
					' Please choose a valid license: ' . join( ', ', $validLicenses )
				) );
				return 1;
			}
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

		// Build extension files
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
