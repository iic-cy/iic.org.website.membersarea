# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What This Is

PHP members portal for the Insurance Institute of Cyprus (IIC). No build system — procedural PHP files served directly by Apache. Live site: `https://iic.org.cy/members-area/`.

## Deployment

No build step. Deployments are direct file copies to the hosting server (cpanel.valicom.cloud). No CI/CD. Changes take effect immediately on upload.

## Architecture

Two parallel UIs: desktop (`index.php` → `learningStatement.php`) and mobile (`index-mobile.php` → `learningStatement-mobile.php`). Both share the same session-based auth and MySQL backend.

**Authentication flow:** Login form POSTs phone number + ID number → validates against `v_users_login` view → sets `$_SESSION['user_info']` → redirects to dashboard.

**Services layer** (`/services/`): JSON API endpoints used by the Expo mobile app. These use `mysqli` and prepared statements — the newer, safer pattern in this codebase.

**Legacy pages** use deprecated `mysql_*` functions via the polyfill `fix_mysql.inc.php`. This wraps them around `mysqli_*` calls. All legacy pages include this at the top.

**Push notification pipeline:**
1. Mobile app registers Expo token via `services/registerPushToken.php` → stored in `iic_push_tokens`
2. `services/cronDailyNewsNotification.php` runs as a cron job, fetches WordPress posts from `https://iic.org.cy/wp-json/wp/v2/posts`, and pushes via Expo API

**Database tables:** `v_users_login`, `student_online`, `student_login`, `learning_statement`, `iic_qrcode`, `iic_push_tokens`

## Key Constraints

**DB credentials are hardcoded** in `config.php` (legacy pages) and `services/servicesConfig.php`. Do not add new credential references — flag for environment variable migration if touching config.

**Two DB connection patterns exist:**
- Legacy: `mysql_*` via polyfill (desktop/mobile pages)
- Modern: `mysqli` with prepared statements (services layer)

When adding new database code, follow the services layer pattern (`mysqli` + prepared statements).

**Mixed Bootstrap versions:** Desktop pages use Bootstrap 4.3.1; mobile pages use Bootstrap 5.0.2. Keep them separate — do not cross-introduce classes.

**Greek strings:** UI labels and some code comments are in Greek. This is intentional.

## External Dependencies

- **WordPress REST API** (`iic.org.cy/wp-json/wp/v2/posts`) — news/alerts source
- **Expo Push API** — mobile push notifications
- **MySQL** — `iicorg_members` database on localhost
