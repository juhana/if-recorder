import glob
import shutil

destination = '../inform7/Recording Parchment/'
files = glob.glob("../lib/*.js")

# create the manifest file
shutil.copy( '(manifest).txt.template', destination+'(manifest).txt' )
manifest = open( destination+'(manifest).txt', 'a' )

# copy library files over
for file in files:
    shutil.copy( file, destination )
    manifest.write( file.rsplit( '\\', 1 )[ 1 ] +"\n" )

manifest.close()