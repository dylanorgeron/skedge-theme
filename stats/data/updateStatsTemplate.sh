#!/bin/bash

scriptdir=$(dirname "$0") && cd "${scriptdir}" || exit 1

curl -s -L -c libsyn-stats-cookie --data-urlencode  "email=" --data-urlencode "password=" https://login.libsyn.com > /dev/null

curl -s -L -b ./libsyn-stats-cookie https://four.libsyn.com/stats/ajax-export/show_id/56517/type/downloads/target/show/id/56517/ -o total.csv

curl -s -L -b ./libsyn-stats-cookie https://four.libsyn.com/stats/ajax-export/show_id/56517/type/three-month/target/show/id/56517/ -o episode.csv

curl -s -L -b ./libsyn-stats-cookie https://four.libsyn.com/stats/ajax-export/show_id/56517/type/monthly-totals/target/show/id/56517/constraint/ -o monthly.csv

curl -s -L -b ./libsyn-stats-cookie https://four.libsyn.com/stats/ajax-export/show_id/56517/type/weekly-totals/target/show/id/56517/constraint/ -o weekly.csv

curl -s -L -b ./libsyn-stats-cookie https://four.libsyn.com/stats/ajax-export/show_id/56517/type/countries/target/show/id/56517/ -o countries.csv

curl -s -L -b ./libsyn-stats-cookie https://four.libsyn.com/stats/ajax-export/show_id/56517/type/regions/target/show/id/56517/constraint/ -o regions.csv

curl -s -L -b ./libsyn-stats-cookie https://four.libsyn.com/stats/ajax-export/show_id/56517/type/user-agents/target/show/id/56517/ -o technology.csv

touch countries.csv
touch episode.csv
touch monthly.csv
touch regions.csv
touch technology.csv
touch total.csv
touch weekly.csv
