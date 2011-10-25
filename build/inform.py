import os
import glob
import shutil

destination = os.path.abspath( '../inform7/Recording Parchment' )
files = glob.glob( os.path.abspath( "../lib/*.js" ) )

# create the manifest file
shutil.copy( '(manifest).txt.template', destination+'/(manifest).txt' )
manifest = open( destination+'/(manifest).txt', 'a' )

# copy library files over
for source in files:
    shutil.copy( source, destination )
    manifest.write( os.path.basename( source ) +"\n" )

manifest.close()
