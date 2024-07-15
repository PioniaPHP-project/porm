# Porm - Pionia orm

However much you can use this independently, it is designed to be used
with [Pionia Framework](https://pionia.netlify.app)

Configuration

In your `settings.ini` add the following:-

```ini 
[db]
;... reset of the conf
```

For SQLite3

```ini 
[db]
database = youdb.sqlite3
type = sqlite3
```

For MySQL/POSTGRES

```ini 
[db]
;//pgsql
type = mysql
host = localhost
database = your_db
username = your_username
password = your_password
```


