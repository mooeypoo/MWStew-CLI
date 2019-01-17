<?php

namespace MWStew\CLI;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Question\ChoiceQuestion;

class ListHooksCommand extends MWStewBaseCommand {
	protected $hooksHelper = null;
	protected $hookLookup = null;
	protected $hooksByPage = [];

	public function __construct( string $name = null ) {
		parent::__construct( $name );
		$this->hookLookup = new Helpers\HookLookup( 90 );
		$this->hooksByPage = [];
	}

	protected function configure() {
		$this
			->setName( 'list-hooks' )
			->setHelp( 'Shows  a list of available and recognized hooks and provides information about them' )
			->setDescription( 'Show the available recognized hooks that can be added to an extension or skin bundle.' )
			->addOption(
				'search',
				's',
				InputOption::VALUE_REQUIRED,
				'Show hooks with that contain the given string.',
				''
			)
			->addOption(
				'prefix',
				'p',
				InputOption::VALUE_REQUIRED,
				'Show hooks with the given prefix.',
				''
			)
			->addOption(
				'highlight',
				'hi',
				InputOption::VALUE_OPTIONAL,
				'Highlight the piece of the hook name matching the search or prefix given',
				false
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		parent::execute( $input, $output );

		$questionHelper = $this->getHelper('question');

		$headerSection = $output->section();
		$headerSection->writeln( $this->getOutputHeader() );

		$searchPrefix = $input->getOption( 'prefix' );
		$searchString = $input->getOption( 'search' );
		$highlight = $input->getOption( 'highlight' ) !== false;
		if ( $searchPrefix ) {
			$this->hooksByPage = $this->hookLookup->getHookPagesFromPrefix( $searchPrefix, $highlight );
		} else if ( $searchString ) {
			$this->hooksByPage = $this->hookLookup->getHookPagesFromSearch( $searchString, $highlight );
		} else {
			$this->hooksByPage = $this->hookLookup->getHookPages();
		}


		if ( count( $this->hooksByPage ) === 0 ) {
			$output->writeln( 'No hooks were found.' );
			return 0;
		}
		$section = $output->section();

		$maxPages = count( $this->hooksByPage );
		$requestedPage = 0;
		$continue = true;
		while ( $continue ) {
			$options = [];
			if ( $requestedPage < 0 || $requestedPage > $maxPages - 1  ) {
				// This is sanity check; if this is displayed, there's a serious issue
				$section->writeln( 'ERROR: Requesting invalid page: ' . $requestedPage );
				break;
			}

			// Output the table
			// $section->write(sprintf("\033\143"));
			$section->writeln( $this->getOutputHeader() );
			$this->showHookPage( $section, $requestedPage );

			// Deal with the navigation options
			if ( $requestedPage > 0 ) {
				$options[] = 'Previous page';
			}

			if ( $requestedPage < ( $maxPages - 1 ) ) {
				$options[] = 'Next page';
			}
			$options[] = 'Quit';

			$question = new ChoiceQuestion(
				'Action:',
				$options,
				array_search( 'Quit', $options )
			);
			$response = $questionHelper->ask( $input, $output, $question );

			if ( $response === 'Next page' ) {
				$requestedPage++;
			} else if ( $response === 'Previous page' ) {
				$requestedPage--;
			} else {
				$continue = false;
				break;
			}
		}
		return 0;
	}

	protected function showHookPage( $output, $page = 0 ) {
		$table = new Table( $output );
		$cols = [];
		$rows = [];

		$hookNames = $this->hooksByPage[ $page ];

		// Split to columns
		$index = 0;
		$offset = 0;
		$maxRows = 30;
		while ( $offset < count( $hookNames ) ) {
			$cols[ $index ] = array_slice(
				$hookNames,
				$offset,
				$maxRows
			);

			$index++;
			$offset = $offset + $maxRows;
		}

		// Build rows
		for ( $i = 0; $i < $maxRows; $i++ ) {
			$rows[ $i ] = [];
			foreach ( $cols as $colID => $colContents ) {
				if ( !isset( $colContents[ $i ] ) ) {
					break;
				}
				$rows[ $i ][] = $colContents[ $i ];
			}
		}
		$table
			->setRows( $rows )
			->setFooterTitle( 'Page ' . ( $page + 1 ) . '/' . count( $this->hooksByPage ) )
			->render();
	}
}
