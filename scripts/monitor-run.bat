@echo off
REM Internsoft domain monitor runner (Windows Task Scheduler)
REM Jadwalkan tiap 1 menit.

cd /d "%~dp0\.."
php spark monitor:run
