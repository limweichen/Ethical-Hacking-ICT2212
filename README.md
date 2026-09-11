# ICT2212_STUDENT17

Basic PHP + MySQL login app.
Built as a Docker image on a laptop, pushed to Docker Hub, and run with Docker on EC2.

## Folders

```
web/                The website (built into the image)
db/schema.sql       Database tables (applied automatically on container start)
db/migrate.php      Script that applies schema.sql
docker/web/         Dockerfile + entrypoint for the web image
secrets/            Local settings file (app.env is never committed)
compose.yml         LOCAL dev stack (laptop)
release.sh          LAPTOP: build a numbered version and push to Docker Hub
deploy/             Copy this folder to EC2
  compose.yml       PRODUCTION stack (EC2)
  setup-docker.sh   EC2, once: install Docker, stop old LAMP services
  update.sh         EC2: back up DB, deploy a version
  rollback.sh       EC2: list versions / go back to an older one
  restore-db.sh     EC2: restore the database from a backup
```

## Workflow

```
laptop: edit code -> docker compose up (test at localhost:8080)
laptop: git commit, then ./release.sh v2     (build + push version v2)
EC2:    sudo bash ~/deploy/update.sh v2    (backup DB, run v2)
```

## Local development

1. `cp secrets/app.env.example secrets/app.env` (once)
2. `docker compose up --build -d`
3. Open http://localhost:8080

## First-time EC2 setup

1. Put your Docker Hub username in `release.sh` and `deploy/compose.yml`.
2. `scp -i key.pem -r deploy ubuntu@EC2-IP:~`
3. On EC2: `sudo bash ~/deploy/setup-docker.sh`
4. On EC2: `sudo docker login`
5. On laptop: `docker login` then `./release.sh v1`
6. On EC2: `sudo bash ~/deploy/update.sh v1`
7. Open http://EC2-IP

## Versions and rollback

Every `./release.sh vN` pushes a new version that is never overwritten
(and tags the git commit with the same name).

On EC2:

```bash
sudo bash ~/deploy/rollback.sh        # show what's running, history, available versions
sudo bash ~/deploy/rollback.sh v2     # go back to v2
```

Rollback changes the code only. `update.sh` backs up the database before every
deploy (last 10 kept in `~/deploy/backups/`). To restore data:

```bash
sudo bash ~/deploy/restore-db.sh                               # list backups
sudo bash ~/deploy/restore-db.sh backups/20260910-170000-before-v3.sql
```
