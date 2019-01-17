<?php

namespace MWStew\CLI;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class MWStewBaseCommand extends Command {
	protected $styles = [];

	public function __construct( string $name = null ) {
		parent::__construct( $name );

		$this->styles = [
			'mw' => new OutputFormatterStyle( 'yellow', 'black', [ 'bold' ] ),
			'code' => new OutputFormatterStyle( 'green', 'black' ),
			'stop' => new OutputFormatterStyle( 'red', 'black', [ 'bold' ] ),
			'error' => new OutputFormatterStyle( 'red' ),
			'working' => new OutputFormatterStyle( 'green', 'default', [ 'bold' ] ),
			'finished' => new OutputFormatterStyle( 'green', 'black', [ 'bold' ] ),
			'hi' => new OutputFormatterStyle( 'black', 'green', [ 'bold' ] ),
		];
	}
	protected function execute(InputInterface $input, OutputInterface $output) {
		// Register all styles
		foreach ( $this->styles as $styleName => $styleObject ) {
			$output->getFormatter()->setStyle( $styleName, $styleObject );
		}
	}

	protected function getOutputHeader() {
		return array_merge(
			[ '' ],
			$this->getMediaWikiAscii( 'mw' ),
			$this->getMWStewAscii(),
			[ '<mw>          =*=*= MediaWiki extension maker =*=*=                   </>' ],
			[ '' ]
		);
	}

	protected function outError( $str = '' ) {
		return [
			'<stop>ERROR.</> <error>' . $str . '</>',
			''
		];
	}

	protected function getMediaWikiAscii( $style = null ) {
		$ascii = [
			'          __  __          _ _    __          ___ _    _           ',
			'         |  \/  |        | (_)   \ \        / (_) |  (_)          ',
			'         | \  / | ___  __| |_  __ \ \  /\  / / _| | ___           ',
			'         | |\/| |/ _ \/ _` | |/ _` \ \/  \/ / | | |/ / |          ',
			'         | |  | |  __/ (_| | | (_| |\  /\  /  | |   <| |          ',
			'         |_|  |_|\___|\__,_|_|\__,_| \/  \/   |_|_|\_\_|          ',
		];

		return $this->addStyleToArray( $ascii, $style );
	}

	protected function getMWStewAscii( $style = null ) {
		$ascii = [
			'     ███╗   ███╗██╗    ██╗███████╗████████╗███████╗██╗    ██╗   ',
			'     ████╗ ████║██║    ██║██╔════╝╚══██╔══╝██╔════╝██║    ██║   ',
			'     ██╔████╔██║██║ █╗ ██║███████╗   ██║   █████╗  ██║ █╗ ██║   ',
			'     ██║╚██╔╝██║██║███╗██║╚════██║   ██║   ██╔══╝  ██║███╗██║   ',
			'     ██║ ╚═╝ ██║╚███╔███╔╝███████║   ██║   ███████╗╚███╔███╔╝   ',
			'     ╚═╝     ╚═╝ ╚══╝╚══╝ ╚══════╝   ╚═╝   ╚══════╝ ╚══╝╚══╝    ',
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
