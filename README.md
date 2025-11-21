Moodle Time Machine (local_timemachine)
======================================

Admin-only local plugin (Moodle 4.5+) that automatically backs up all courses in a selected category when content changes are detected. It stores backups under moodledata/MoodleTimeMachine and can also upload each archive to an FTP server.
The number of backup versions kept is configurable by the administrator (default: 7).

Key features
- Admin-only access and configuration
- Category selection to include courses
- Change-detection to avoid unnecessary backups
- Retention of last 7 versions per course
- Admin interface with search, download and deletion
- Optional FTP upload (passive mode supported)
- Fixed storage folder: moodledata/MoodleTimeMachine (auto-created)

Scheduled Task
- Runs daily at 02:00 by default (Site administration -> Server -> Scheduled tasks -> Moodle Time Machine)

Settings
- Site administration -> Plugins -> Local plugins -> Moodle Time Machine (storage folder is not configurable)

Security and robustness
- Storage folder is auto-created under moodledata with sanity checks to prevent traversal
- Download and deletion actions enforce sesskey, capability checks and storage-bound paths
- Backups are streamed to disk to avoid loading large archives into memory
- Retention and cleanup use guarded deletions to avoid touching files outside the plugin storage
- FTP upload is optional, validated for readable files and avoids logging credentials

Maintainer
- zMoodle (https://app.zmoodle.com)
