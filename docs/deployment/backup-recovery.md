# Backup and recovery

Published source and content are version-controlled, but production data is not. The VPS topology uses two stable named volumes: `portfolio_app-database` for SQLite and `portfolio_app-storage` for sessions, cache, logs, and private uploads. A release must never replace or copy these volumes.

Before each migration, the SSH adapter uses SQLite `VACUUM INTO` through the application container and writes `/var/www/html/storage/backups/database-<commit-sha>.sqlite`. This is a deployment safety copy, not a backup schedule. Configure encrypted, off-host backups of the database and storage volumes with retention, access control, recovery-point and recovery-time objectives. Test restore integrity before enabling production.

For recovery, stop the application, restore a verified backup into the persistent volumes, run the appropriate migrations or forward fix, deploy the compatible immutable release, and verify the public smoke routes. Record the incident, backup identifier, release SHA, operator, and verification results. Never restore a database backup over a live writable application.
