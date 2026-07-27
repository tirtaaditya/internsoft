#!/usr/bin/env bash
# Internsoft domain monitor runner (Linux cron)
# Contoh cron tiap 1 menit:
# * * * * * /path/to/internsoft/scripts/monitor-run.sh >> /path/to/internsoft/writable/logs/monitor-cron.log 2>&1

cd "$(dirname "$0")/.." || exit 1
php spark monitor:run
