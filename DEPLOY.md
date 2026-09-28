# Clusterfiy production runbook (3.2)

Single Ubuntu 24.04 server. No zero-downtime deploys; see "Downtime, honestly" below.

## 1. Provision the server

| Requirement | Command / notes |
|---|---|
| PHP 8.3+ | `sudo apt install php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml php8.3-bcmath php8.3-curl php8.3-zip php8.3-gd php8.3-sqlite3 unzip` then `php -v` must show 8.3+ |
| Composer 2 | `php -r "copy('https://getcomposer.org/installer','composer-setup.php');"` + install to `/usr/local/bin/composer` |
| Node 20 (build only) | `curl -fsSL https://deb.nodesource.com/setup_20.x \| sudo bash - && sudo apt install nodejs` |
| MySQL 8 | `sudo apt install mysql-server`, then `CREATE DATABASE clusterfiy; CREATE USER 'clusterfiy'@'localhost' IDENTIFIED BY '...'; GRANT ALL ON clusterfiy.* TO 'clusterfiy'@'localhost';` |
| Nginx | `sudo apt install nginx`, install `deploy/nginx-clusterfiy.conf`, `nginx -t && systemctl reload nginx` |
| HTTPS | `sudo apt install certbot python3-certbot-nginx && sudo certbot --nginx -d app.example.com` (auto-redirect + renewal) |
| Supervisor | `sudo apt install supervisor`, install `deploy/supervisor-clusterfiy-worker.conf`, `supervisorctl reread && supervisorctl update && supervisorctl status` |
| Scheduler | `(crontab -u www-data -l; cat deploy/scheduler.cron) \| crontab -u www-data -` (runs `schedule:run` every minute; jobs defined in `routes/console.php`) |
| Code + perms | `git clone ... /var/www/clusterfiy`, `chown -R www-data:www-data`, `chmod -R 775 storage bootstrap/cache` |

## 2. Configure

1. `cp deploy/.env.production.example .env`, fill secrets, `php artisan key:generate`.
2. First deploy: run `./deploy/deploy.sh` (does backup, migrate, caches, worker restart).

## 3. Mail (real transactional provider)

Production uses Postmark free tier (100 emails/month) with a log fallback:

- `MAIL_MAILER=failover` (primary `smtp` via `smtp.postmarkapp.com:587`, fallback `log`; see `config/mail.php`).
- Verify a sender signature/domain in Postmark, set `MAIL_FROM_ADDRESS`, `POSTMARK_API_KEY`.
- Test: `php artisan tinker --execute="Mail::raw('prod test', fn(\$m) => \$m->to('you@example.com')->subject('Clusterfiy prod'));"` then check Postmark activity.
- Log driver only: acceptable solely if the live demo does not need invites/password mail. Say so explicitly; otherwise it is a gap, not a feature.

## 4. Queue worker daemon

- Supervised via `deploy/supervisor-clusterfiy-worker.conf`: `queue:work database --sleep=3 --tries=3 --max-time=3600 --max-jobs=1000`, autostart/autorestart as `www-data`, logs to `storage/logs/worker.log`.
- Deploy restarts gracefully with `php artisan queue:restart` (finishes the current job, no lost work).
- Failed jobs land in `failed_jobs`; inspect with `php artisan queue:failed`, retry with `php artisan queue:retry all`.
- Check: `supervisorctl status clusterfiy-worker:*` must show RUNNING; send a test invite and confirm the mail job leaves the `jobs` table.

## 5. Scheduler cron

One cron line (see `deploy/scheduler.cron`) drives `routes/console.php`:

- `invitations:purge-expired` daily 03:00 (deletes unaccepted invites expired 30+ days; accepted rows kept).
- `billing:enforce-grace` daily 03:15 (downgrades past-due companies past `grace_until` to free; data kept).
- Check: `php artisan schedule:list` shows both; logs confirm nightly runs.

## 6. Deploy

```bash
cd /var/www/clusterfiy && ./deploy/deploy.sh
```

Script order: `down` → `composer install --no-dev` → `npm ci && npm run build` → key check → DB backup → `migrate --force` → `config:cache route:cache view:cache` → `queue:restart` → `up` → `/up` health check.

Rollback: `git checkout <prev-sha>`, restore `storage/backups/` dump if a migration changed schema, re-run script. Down-migrations are not relied on; the backup is the rollback.

## Downtime, honestly

This setup has no zero-downtime deploys. Each deploy takes the app down with `php artisan down` for the migrate + cache window, typically **15–60 seconds** (longer on first deploy when `npm ci` runs, but that happens before `down`... note: `npm ci` runs while live, so user-facing downtime is only migrate + cache + boot, usually **under 30 seconds**). Queue workers finish their current job and pick up new code on restart, so background mail is delayed, not lost. If even 30 seconds is unacceptable, the next step is a blue-green/symlink-release setup, which is deliberately out of scope here.
