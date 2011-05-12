/**
Parchment Transcript Recording Plugin
	* Version 1.0
	* URL: http://code.google.com/p/parchment-transcript/
	* Description: Parchment Transcript Recording Plugin sends transcripts of games being played on Parchment web interpreter to a remote server.
	* Author: Juhana Leinonen
	* Copyright: Copyright (c) 2011 Juhana Leinonen under MIT license.
**/

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
		story: '',

		// the player can opt out by having feedback=0 in the url
		optOut: ( typeof( getUrlVars()[ 'feedback' ] ) != 'undefined' && getUrlVars()[ 'feedback' ] != '1' ),
		
		// is the transcript saving server offline?
		serverOffline: false,
		
		send: function( window, styles, text ) {
			var self = this;
			
			if( !this.collectTranscripts() ) {
				return;
			}
			
			if( typeof( window ) != 'undefined' ) {
				self.window = window;
			}
			if( typeof( styles ) != 'undefined' ) {
				self.styles = styles;
			}
			if( typeof( text ) != 'undefined' ) {
				self.output = text;
			}
			
			var jsonData = $.toJSON( 
					{
					   'session': self.sessionId,
					   'log': {
					         'inputcount': self.inputcount,
					         'outputcount': self.outputcount,
					         'input': self.command.input,
					         'output': self.output,
					         'window': self.window,
					         'styles': self.styles,
					         'timestamp': new Date().getTime()
					      }
					}
			);
			
			// console.log( "JSON: "+jsonData );
			
			$.ajax( {
				type: 'POST',
				url: self.saveUrl,
				data: { data: jsonData }			
			} );

			// clearing the buffer for next turn
			self.output = '';
			self.outputcount++;
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
			var self = this;
			
			if( typeof( url ) == 'string' ) {
				self.saveUrl = url;
			}

			// If story name hasn't been given, set it to the file name.
			// This will not work with the archive search thing.
			if( self.story == '' ) {
				if( typeof( parchment.options.default_story ) != 'unknown' && parchment.options.default_story != '' ) {
					self.story = parchment.options.default_story;
					
					if( !parchment.options.lock_story && typeof( getUrlVars()[ 'story' ] ) != 'unknown' && getUrlVars()[ 'story' ] != '' ){
						self.story = getUrlVars()[ 'story' ];
					} 
				}
				else {
					if( !parchment.options.lock_story && typeof( getUrlVars()[ 'story' ] ) != 'unknown' ){
						self.story = getUrlVars()[ 'story' ];
					} 
				}
			}
			
			if( self.story == '' ) {
				self.story = '(unknown)';
			}
			
			if( !self.collectTranscripts() ) {
				return false;
			}
			
			var browserString = $.browser.name+' '+$.browser.version+' '+$.os.name; 

			// there doesn't seem to be an easy way to find out which engine
			// is or will be running at this point. 
			/* var engine = '';
			if( typeof( ENGINE_DESCRIPTION ) != 'undefined' ) {
				engine = ENGINE_DESCRIPTION;
			} */
			
			var initString = $.toJSON( {
					'session': self.sessionId,
					'start': {
						'story': self.story,
						'interpreter': 'Parchment',	// TODO: Does Parchment have version numbers? 
						'browser': browserString
					}
				}
			);
			
			$.ajax( {
				type: 'POST',
				url: self.saveUrl,
				data: { data: initString },
				// check what the server returns, flag it offline if not ok
				error: function() {
					self.serverOffline = true;
				},
				success: function( data ) {
					if( data.toLowerCase() != 'ok' ) {
						self.serverOffline = true;
					}
				}
			} );
				
			return true;
		},
		
		charName: function( keyCode ) {
			switch( keyCode ) {
				case 8:
					return '<backspace>';
				case 9:
					return '<tab>';
				case 13:
					return '<enter>';
				case 27:
					return '<esc>';
				case 32:
					return '<space>';
				case 37:
					return '<left>';
				case 38:
					return '<up>';
				case 39:
					return '<right>';
				case 40:
					return '<down>';
				case 46:
					return '<del>';
				default:
					return String.fromCharCode( keyCode );
			}
		},
		
		manualTranscriptMsg: "This story does not support saving transcripts manually."
};


$( document ).ready(function(){

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
			parchment.transcript.command.input = parchment.transcript.charName( command.input.keyCode );
			parchment.transcript.inputcount++;
		} 
	);

// Sending main game texts to the recorder.
// Status line is saved by the modified Console.renderHtml().
$( document ).bind(
		'TextOutput',
		function( data ) {
			if( data.output.window == 0 ) {
				parchment.transcript.send( data.output.window, data.output.styles, data.output.text );
			}
		}
);


/*
 * We need to modify Gnusto runner to get the final formatting of the status line.
 * Since Parchment loads some library files asynchronously we'll have to wait until
 * the runner.js file is loaded. When we reach the TextOutput function for the first
 * time we the file has been loaded and the first status line has not yet been printed.
 * 
 * As far as I can tell Console.renderHtml is called only when building the status line,
 * so we can safely (?) set the window to 1 (status line) when sending this text to
 * the transcript recorder. 
 * 
 * (There must be a better way to do this, but this works so it'll have to suffice.)
 */

var firstTextOutputHandler = function( data ) {
	Console.prototype.renderHtml = function() {
    var string = "";
    var currString = "";
    for (var y = 0; y < this.height; y++) {
      var currStyle = null;
      for (var x = 0; x < this.width; x++) {
        if (this._styles[y][x] !== currStyle) {
          if (currStyle !== null) {
  			parchment.transcript.send( 1, currStyle, currString.replace( /\&nbsp\;/gi, ' ') );
			currString = '';
            string += "</span>";
          }
          currStyle = this._styles[y][x];
          if (currStyle !== null) {
            string += '<span class="' + currStyle + '">';
          }
        }
        string += this._characters[y][x];
        currString += this._characters[y][x];
      }
      if (currStyle !== null) {
        string += "</span>";
		parchment.transcript.send( 1, currStyle, currString.replace( /\&nbsp\;/gi, ' ' )+"\n" );
		currString = '';
      }
      string += "<br/>";
    }
    return string;
  };
  
	// remove the handler, no need to run more than once
	$( document ).unbind( 'TextOutput', firstTextOutputHandler );
};

$( document ).bind( 'TextOutput', firstTextOutputHandler );

});



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
