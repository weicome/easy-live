#!/bin/bash

PWD=$(cd `dirname $0`/.. && pwd)

pushd $PWD
echo "Run UTest in $(pwd)"

IS_LINUX=yes
uname -s|grep Darwin >/dev/null && IS_DARWIN=yes && IS_LINUX=no
echo "IS_LINUX: $IS_LINUX, IS_DARWIN: $IS_DARWIN"

echo "Clean gcda files"
rm -f ./obj/*.gcda

echo "Build and run utest"
if [[ $IS_DARWIN == yes ]]; then
  make darwin-debug-gcov && ./obj/st_utest
else
  make linux-debug-gcov && ./obj/st_utest
fi
ret=$?; if [[ 0 -ne $ret ]]; then echo "Make ST utest fail, ret=$ret"; exit $ret; fi

echo "Generating coverage"
mkdir -p coverage &&
gcovr -r . -e LINUX -e DARWIN -e examples --html --html-details -o coverage/st.html &&
echo "Coverage report at coverage/st.html" &&
open coverage/st.html

popd
echo "UTest done, restore $(pwd)"

cat << END > /dev/stdout

  # CLI For DARWIN
  cd $PWD && rm -f ./obj/*.gcda &&
  make darwin-debug-gcov && ./obj/st_utest &&
  mkdir -p coverage && gcovr -r . -e LINUX -e DARWIN -e examples --html --html-details -o coverage/st.html &&
  open coverage/st.html

END