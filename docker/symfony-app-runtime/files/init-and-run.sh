#!/usr/bin/env bash

CUSTOM_INIT_SCRIPT=./init

# Exit on error
set -e

echo "----------------------------------"
echo "- Initializing Symfony app..."
echo "----------------------------------"
echo "> Installing assets"
bin/console assets:install --symlink
echo "> Clearing cache and warming it up"
bin/console cache:clear --quiet --no-interaction --no-ansi

if [ -f "${CUSTOM_INIT_SCRIPT}" ]; then
  if [ ! -x "${CUSTOM_INIT_SCRIPT}" ]; then
    chmod +x ${CUSTOM_INIT_SCRIPT}
  fi

  echo "> Running custom init script"
  ${CUSTOM_INIT_SCRIPT}
  # The init script should be run once, so it's safer to delete it
  rm ${CUSTOM_INIT_SCRIPT}
fi

echo "> Refreshing file permissions"
chown -R www-data:www-data /var/www/html/var

echo "----------------------------------"
echo "- Running Apache..."
echo "----------------------------------"
exec apache2-foreground
