/**
 * Settings for Parchment transcript recording plugin.
 */


/**
 * Location of the story file (http://example.com/story.z8) 
 */
parchment.options.default_story = "";


/**
 * URL of the server-side script that receives the transcript.
 * 
 * If this option is left blank, transcript recording will NOT begin.
 */
parchment.transcript.saveUrl = "tools/server/save-sql.php";


/**
 * Should the player be locked to this story (true or false)? 
 * If not locked, the player can play any game with this
 * installation by giving the story file in the URL.
 */
parchment.options.lock_story = true;


/**
 * Story name used as an identifier. If left empty, the story file URL is used.  
 */
parchment.transcript.story = "";


/**
 * Start the transcript recording.
 */	
$(document).ready(function(){ parchment.transcript.initialize(); });