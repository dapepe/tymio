#!/bin/bash

set -o pipefail

for i in *.less ; do
	echo "Compiling \"$i\"..."
	( echo '@imgpath: "../img";' ; cat "$i" ) | plessc -r > "${i/.less/.css}"
done
