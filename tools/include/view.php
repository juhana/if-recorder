<?php
function display_transcript( $db, $session, $options ) {
	$prevInputCount = 0;
	$prevOutputCount = 0;
	
	$gameText = '';
	$statusLineText = '';
	
	$html = ( $options[ 'output' ] == 'html' );
	
	$query = $db->prepare( 'SELECT * FROM transcripts WHERE session = ? ORDER BY outputcount ASC' );
	$query->execute( array( $session ) ) or database_error( $query->errorInfo() );
	
	$rows = $query->fetchAll();
	
	$transcript = '';
	
	foreach( $rows as $snippet ) {
		// The output count should be continuous. If we've skipped a count, inform the user.
		if( $prevOutputCount < $snippet[ 'outputcount' ] - 1 && $options[ 'warnings' ] ) {
			if( $html ) {
				$transcript .= '<div class="error">WARNING: Possible gap in the transcript</div>';
			}
			else {
				$transcript .= '*** WARNING: Possible gap in the transcript ***';
			}
		}
		
		$prevOutputCount = $snippet[ 'outputcount' ];
	
		// When input count increments, we've started a new turn.
		if( $prevInputCount != $snippet[ 'inputcount' ] ) {
			if( !empty( $statusLineText ) && $options[ 'statusline' ] == 'inline' ) {
				if( $html ) {
					$transcript .= '<div class="statusline">';	// the extra class will instruct the browser to use fixed-width font
				}
				$transcript .= $statusLineText;
				
				if( $html ) {
					$transcript .= '</div>';
				}
			}
			$transcript .= $gameText; 
			$gameText = '';
			$statusLineText = '';
			if( $html ) {
				$transcript .= '<span class="command" id="command-'.$snippet[ 'inputcount' ].'">'.htmlentities( $snippet[ 'input' ] ).'</span><br />';
			}
			else {
				$transcript .= $snippet[ 'input' ]."\n";
			}
			$prevInputCount = $snippet[ 'inputcount' ];
		}
	
		if( $snippet[ 'window' ] == 0 ) {
			if( $html ) {
				$gameText .= '<span class="'.$snippet[ 'styles' ].'">';
				$gameText .= nl2br( str_replace( '  ', '&nbsp; ', str_replace( '  ', '&nbsp; ', htmlentities( $snippet[ 'output' ] ) ) ) );
				$gameText .= '</span>';
			}
			else {
				$gameText .= $snippet[ 'output' ];
			}
		}
		
		if( $snippet[ 'window' ] == 1 ) {
			if( $html ) {
				$statusLineText .= '<span class="'.$snippet[ 'styles' ].'">';
				$statusLineText .= nl2br( str_replace( ' ', '&nbsp;', htmlentities( $snippet[ 'output' ] ) ) );
				$statusLineText .= '</span>';
			}
			else {
				$statusLineText .= $snippet[ 'output' ];
			}
		}
	}
	
	if( !empty( $statusLineText ) && $options[ 'statusline' ] == 'inline' ) {
		if( $html ) {
			$transcript .= '<div class="statusline">';
		}
		$transcript .= $statusLineText;
		if( $html ) {
			$transcript .= '</div>';
		}
	}
	
	$transcript .= $gameText;
	 
	return stripslashes( $transcript );
}