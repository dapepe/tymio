#!/bin/bash

set -o pipefail

for i in *.less ; do
	echo "Compiling \"$i\"..."
	( echo '@imgpath: "../img";' ; cat "$i" ) | /z/htdocs/gx/lib/lessphp/plesswin -r > "${i/.less/.css}"
done
