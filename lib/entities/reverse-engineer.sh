#!/bin/bash

MY_DIR="$(dirname "$0")"

cd "${MY_DIR}" || exit 1

"${MY_DIR}/../propel/generator/bin/propel-gen" . reverse

sed -i -f patch.sed schema.xml

"${MY_DIR}/../propel/generator/bin/propel-gen"
