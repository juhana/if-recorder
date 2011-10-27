/**
 * Parchment-specific settings for the IF Recorder plugin.
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
ifRecorder.saveUrl = "tools/server/save-sql.php";


/**
 * Should the player be locked to this story (true or false)? 
 * If not locked, the player can play any game with this
 * installation by giving the story file in the URL.
 */
parchment.options.lock_story = true;


/**
 * Story name used as an identifier. If left empty, the story file URL is used.  
 */
ifRecorder.story.name = "";


/**
 * The version number of the current story (optional).
 */
ifRecorder.story.version = "";


/**
 * If you want to add some aditional information to the saved data,
 * add it here.
 */
ifRecorder.info = "";


/**
 * Start the transcript recording.
 * (do not edit unless you know what you're doing)
 */	
$(function(){ ifRecorder.initialize(); });
