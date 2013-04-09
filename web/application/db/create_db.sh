rm db.sqlite 2> /dev/null
sqlite3 db.sqlite < db_schema.sql
sqlite3 db.sqlite < db_data.sql

