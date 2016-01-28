@echo off

call ..\propel\generator\bin\propel-gen.bat . reverse

sed -i -f patch.sed schema.xml

call ..\propel\generator\bin\propel-gen.bat

pause