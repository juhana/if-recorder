from subprocess import call

call([ 
      "/usr/bin/java", 
      "-jar", "../../closure/closure-compiler/compiler.jar", 
      "--js", "../src/if-recorder.js", 
      "--compilation_level=SIMPLE_OPTIMIZATIONS", 
      "--js_output_file", "../lib/if-recorder.min.js"
])