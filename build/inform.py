import os
import glob
import shutil

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

manifest.close()
