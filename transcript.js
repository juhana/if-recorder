/**
Parchment Transcript Recording Plugin
	* Version 0.1
	* URL: http://code.google.com/p/parchment-transcript/
	* Description: Parchment Transcript Recording Plugin sends transcripts of games being played on Parchment web interpreter to a remote server.
	* Author: Juhana Leinonen
	* Copyright: Copyright (c) 2011 Juhana Leinonen under MIT license.
**/

$( document ).ready(function(){
	
parchment.transcript = {
		sessionId: (new Date().getTime())+""+( Math.ceil( Math.random() * 1000 ) ),
		command: { input: '', timestamp: 0 },
		output: '',
		statusline: '',
		window: 0,	// which window the transcript has been saved to

		/* Turn count is the game's internal turn counter
		 * input count is the number of actual input given by the player
		 * output count is the number of packets sent to the server
		 */
		turncount: 0,	// turncount is currently unused since we don't have means to access game's turn count. 
		inputcount: 0,
		outputcount: 1,
		styles: '',
		saveUrl: '',
		storyUrl: getUrlVars()[ 'story' ],	// TODO: handle games not in the url

		// the player can opt out by having feedback=0 in the url
		optOut: ( typeof( getUrlVars()[ 'feedback' ] ) != 'undefined' && getUrlVars()[ 'feedback' ] != '1' ),
		
		// is the transcript saving server offline?
		serverOffline: false,
		
		send: function() {
			if( !this.collectTranscripts() ) {
				return;
			}
			
			var jsonData = $.toJSON( 
					{
					   'session': this.sessionId,
					   'log': {
					         'inputcount': this.inputcount,
					         'outputcount': this.outputcount,
					         'input': this.command.input,
					         'output': this.output,
					         'window': this.window,
					         'styles': this.styles
					      }
					}
			);
			
			// console.log( "JSON: "+jsonData );
			
			$.ajax( {
				type: 'POST',
				url: this.saveUrl,
				data: { data: jsonData }			
			} );

			// clearing the buffer for next turn
			this.output = '';
			this.outputcount++;
		},
		
		/*
		 * Check whether we should collect transcript information. 
		 * Transcripts are collected if:
		 *  - we know the url where to send the transcripts
		 *  - the player hasn't opted out with feedback=0 option
		 */
		collectTranscripts: function() {
			if( this.saveUrl == '' || this.optOut || this.serverOffline ) {
				return false;
			}
			return true;
		},
		
		initialize: function( url ) {
			if( typeof( url ) == 'string' ) {
				this.saveUrl = url;
			}
			
			if( !this.collectTranscripts() ) {
				return false;
			}
			
			var browserString = $.browser.name+' '+$.browser.version+' '+$.os.name; 

			var engine = 'Quixe';
			if( parchment.lib.Story.zcode ) {
				engine = 'Gnusto';
			}
			
			var initString = $.toJSON( {
					'session': this.sessionId,
					'start': {
						'story': this.storyUrl,
						'interpreter': 'Parchment / '+engine,	// TODO: Does Parchment have version numbers? 
						'browser': browserString
					}
				}
			);
			
			$.ajax( {
				type: 'POST',
				url: this.saveUrl,
				data: { data: initString },
				// check what the server returns, flag it offline if not ok
				error: function() {
					this.serverOffline = true;
				},
				success: function( data ) {
					this.serverOffline = ( data.toLowerCase() != 'ok' );
				}
			} );
			
			return true;
		}
};

/* save commands when Parchment calls the hooks in [z]ui.js */
$( document ).bind( 
		'LineInput', 
		function( command ) {
//			console.log( 'cmd: '+command.toSource() );
			parchment.transcript.command = command;
			parchment.transcript.inputcount++;
		} 
	);

$( document ).bind( 
		'CharInput', 
		function( command ) { 
//			console.log( 'charcmd: '+command.toSource() );
			parchment.transcript.command = command;
			parchment.transcript.command.input = String.fromCharCode( command.input.keyCode ); // TODO: Handle non-alphabet input like arrow keys
			parchment.transcript.inputcount++;
		} 
	);

$( document ).bind(
		'TextOutput',
		function( data ) {
			parchment.transcript.output += data.output.text;
			parchment.transcript.window = data.output.window;
			parchment.transcript.styles = data.output.styles;
			parchment.transcript.send();
		}
);


/* source: http://jquery-howto.blogspot.com/2009/09/get-url-parameters-values-with-jquery.html */
function getUrlVars()
{
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++)
    {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}

});