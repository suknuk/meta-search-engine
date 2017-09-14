#!/bin/sh

set -e

search_engines="/var/www/html/search-engines.txt"

echo -n > "$search_engines"

while [ -n "$1" ]; do
  echo "$1" >> "$search_engines"
  echo "Adding search engine \"$1\"."
  shift
done

echo "Starting apache in foreground."
docker-php-entrypoint apache2-foreground &
apache_pid="$!"

kill_apache() {
  kill "$apache_pid"
}
#trap 'kill_apache' INT
wait "$apache_pid"
