# Automated deploy

Merging `develop` into `main` runs `.github/workflows/deploy.yml`:

1. GitHub builds the backend `vendor/` (Composer, PHP 8.3) and the website (`npm run build`), because the server cannot reach GitHub's download hosts, Packagist or npm reliably.
2. It uploads `backend-vendor.tgz` and `web-dist.tgz` to the directory **above** the checkout (`$DEPLOY_APP_DIR/..`).
3. Over SSH it resets the checkout to the merged commit and runs `deploy/deploy.sh`, which:
   - unpacks `vendor/`, puts the API in maintenance mode, runs `php artisan migrate --force`, rebuilds caches (`optimize:clear` + `optimize`) and brings it back up;
   - reloads PHP-FPM (new code, OPcache and DNS settings take effect);
   - swaps in the new website build in one move.

It only runs when `backend/`, `Nour Adhkar/`, `deploy/` or the workflow change, or manually from Actions → Deploy → Run workflow.

## One-time server setup

Run on the server as root (adjust the path to where the repo is cloned):

```bash
# 1. A deploy user that owns the checkout
adduser --disabled-password --gecos "" deploy
chown -R deploy:deploy /path/to/nour-adhkar
chown deploy:deploy /path/to            # CI uploads the build archives here
# Laravel must still be able to write storage/ and bootstrap/cache as the web server user:
chgrp -R www-data /path/to/nour-adhkar/backend/storage /path/to/nour-adhkar/backend/bootstrap/cache
chmod -R g+rwX /path/to/nour-adhkar/backend/storage /path/to/nour-adhkar/backend/bootstrap/cache

# 2. Let deploy reload PHP-FPM without a password (use your version, e.g. php8.3-fpm)
echo 'deploy ALL=(root) NOPASSWD: /usr/bin/systemctl reload php8.3-fpm' > /etc/sudoers.d/nour-deploy
chmod 440 /etc/sudoers.d/nour-deploy

# 3. SSH key for GitHub Actions (private key goes into the DEPLOY_SSH_KEY secret)
sudo -u deploy ssh-keygen -t ed25519 -N "" -f /home/deploy/.ssh/nour_ci
sudo -u deploy sh -c 'cat /home/deploy/.ssh/nour_ci.pub >> /home/deploy/.ssh/authorized_keys'
cat /home/deploy/.ssh/nour_ci            # copy into DEPLOY_SSH_KEY, then delete the file

# 4. git must be able to fetch without prompting (public repo over https is fine)
sudo -u deploy git -C /path/to/nour-adhkar fetch origin main
```

**Before the first deploy**, check `git status` in the server checkout. The deploy runs `git reset --hard`, which discards any uncommitted edits made directly on the server; commit or save them first. The backend `.env`, `storage/` and uploaded files are not tracked by git and are kept.

## GitHub settings (nour-adhkar repo → Settings → Secrets and variables → Actions)

| Name | Kind | Value |
|---|---|---|
| `DEPLOY_SSH_KEY` | secret | private key from step 3 |
| `DEPLOY_HOST` | secret | server IP or hostname (the real server, not the ArvanCloud CDN address) |
| `DEPLOY_USER` | secret | `deploy` |
| `DEPLOY_PORT` | secret | SSH port (optional, default 22) |
| `DEPLOY_KNOWN_HOSTS` | secret | output of `ssh-keyscan -p <port> <host>` run from your own machine |
| `DEPLOY_APP_DIR` | variable | absolute path of the checkout, e.g. `/var/www/nour-adhkar` |
| `DEPLOY_WEB_ROOT` | variable | directory the web server serves for adhkar.ir (optional; default `<checkout>/Nour Adhkar/dist`) |
