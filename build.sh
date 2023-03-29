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

# Execute composer install --no-dev --no-scripts
NO_WP=true php composer.phar install --no-dev --no-scripts

# cp -r $CURPATH $OUTPUT
#
rsync -qavr --exclude="/vendor" --exclude='.git' $CURPATH/ $OUTPUT

# copy the whole folder vendor seems faster
cp -r $CURPATH/vendor $OUTPUT/

cd $OUTPUT

# create version file
touch VERSION
echo "$VERSION" > VERSION

# create default env file
cp .env.example .env

# clean up
rm -fr .git*
rm -fr build.sh
rm -fr php-cs-fixer
rm -fr composer.phar

# generate unique key
NO_WP=true php artisan cache:clear
NO_WP=true php artisan view:clear
NO_WP=true php artisan route:clear
NO_WP=true php artisan key:generate

cd ..
chmod 755 -R "$APPDIR"
chmod 775 -R "$APPDIR/storage"
chmod 775 -R "$APPDIR/bootstrap/cache"
zip -r "$APPZIP" "$APPDIR" > /dev/null
rm -fr "$APPDIR"

echo "File exported [$APPZIP]"
