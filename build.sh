#!/bin/bash

# Step 1: make sure you are at the correct tag/version
# Step 2: make sure you run php "composer.phar install" and "composer.phar dump-autoload" without error
# Step 2: configure the patch.rb file


if [ -z "$1" ]
then
  echo "Error: please specify the output path" && exit 1
fi

if [ ! -d "$1" ]
then
  echo "Error: directory does not exist" && exit 1
fi

APPNAME="chatgpt"
VERSION=$(git tag --points-at HEAD)
# VERSION=${VERSION:-"-latest"}

if [ -z "$VERSION" ]
then
  echo "Error: no version (tag) found" && exit 1
fi

APPDIR="$APPNAME"
APPZIP="$APPNAME-$VERSION.zip"
OUTPUT="$1/$APPDIR"
CURDIR=$(pwd)

if [ -d "$OUTPUT" ]; then
  echo "Error: director [$OUTPUT] already exists" && exit 1
fi

CURPATH=$(pwd)


# cp -r $CURPATH $OUTPUT
#
rsync -qavr --exclude="/vendor" --exclude='.git' $CURPATH/ $OUTPUT

cd $OUTPUT

# clean up
rm -fr .git*
rm -fr build.sh
rm -fr php-cs-fixer

cd ..
chmod 755 -R "$APPDIR"
zip -r "$APPZIP" "$APPDIR" > /dev/null
rm -fr "$APPDIR"

echo "File exported [$APPZIP]"
