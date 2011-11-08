import os
import glob
import shutil
from subprocess import call

# minify the library
call([ 
      "/usr/bin/java", 
      "-jar", "../../closure/closure-compiler/compiler.jar", 
      "--js", "../src/if-recorder.js", 
      "--compilation_level=SIMPLE_OPTIMIZATIONS", 
      "--js_output_file", "../lib/if-recorder.min.js"
      ])

# create the Inform template
releasedir = os.path.abspath( './release' );
destination = os.path.abspath( '../inform7/Recording Parchment' )

# location of Parchment main files
files = ( glob.glob( os.path.abspath( "../../parchment/lib/*.min.js" ) ) )

# css files
files.extend( glob.glob( os.path.abspath( "../../parchment/src/quixe/media/*.css" ) ) )
files.append( os.path.abspath( "../../parchment/parchment.css" ) )

# add recorder files
files.extend( glob.glob( os.path.abspath( "../lib/*.min.js" ) ) )

# create the manifest file
shutil.copy( '(manifest).txt.template', destination+'/(manifest).txt' )
manifest = open( destination+'/(manifest).txt', 'a' )

# copy library files over
for source in files:
    shutil.copy( source, destination )
    manifest.write( os.path.basename( source ) +"\n" )

# create zip files
os.chdir( destination )
os.chdir( '../' )

call([
      "zip",
      "-rq",
      releasedir+"/inform7-template.zip",
      "Recording Parchment",
      # exclude OS X's system files
      "-x", '*.DS_Store*'
    ])


# create tools zip
os.chdir( '../' )

call([
      "zip",
      "-rq",
      releasedir+"/if-recorder-tools.zip",
      "tools",
      # exclude OS X's system files
      "-x", '*.DS_Store*'
    ])

# create main zip
call([
      "zip",
      "-rq",
      releasedir+"/if-recorder-client.zip",
      "lib",
      "index.html",
      "parchment.transcript.settings.js",
      "LICENSE",
      "-x", '*.DS_Store*'
    ])

manifest.close()
