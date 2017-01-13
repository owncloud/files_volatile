Volatile Files
==============

This app adds a new Folder in every users home that is intended to contain volatile files.

Volatile Folder
---------------

The default folder name is 'Volatile Files', but it can be changed with eg:
```
  occ config:app:set files_volatile folder-name --value "Temporary (Files older than 30 days will be deleted)"
```

The folder Itself cannot be deleted or renamed, but files within can.

Volatile Storage
----------------

The files themselves are stored in the users home dir in an app specific folder, eg: `/admin/files_volatile`

*Note: even when an objectstore is configured as primary storage*

Expire Command
--------------

To expire files add a nightly cron job that executes
```
  occ files:volatile:expire
```

You can change the default number of 30 days by adding `--days 60`

You can test what would happen by adding `--dry-run`

Todo
----

- [ ] add disk usage report without volatile files
- [ ] store volatile files on objectstore
