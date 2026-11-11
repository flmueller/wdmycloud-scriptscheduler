# Script Scheduler for WD My Cloud (OS 5)

An app for the WD My Cloud (single bay, Gen2, firmware 5.x) that runs your own
shell scripts on a schedule. Manage everything from the dashboard (Apps →
Script Scheduler → Configure) or by editing plain files.

- Schedule entries: every N minutes, hourly, daily, on weekdays, monthly, at NAS
  start-up (`@boot`) or any cron expression; entries can be disabled.
- Scripts: create and edit in the dashboard, run now, stop, view the log and exit code.
- German and English. The app follows the NAS language setting: German if the
  dashboard is set to German, English otherwise.

Developed and tested on a WD My Cloud Gen2 with firmware 5.33.102. Other OS 5
models are untested.

## Install

Download the `.bin` from the [releases](https://github.com/flmueller/wdmycloud-scriptscheduler/releases)
and install it in the dashboard: Apps → "Install an app manually". Updates are
installed the same way; scripts, schedule and logs are kept.

## Build

    ./build.sh            # writes ../packages/scriptscheduler_<version>_WDMyCloud.bin
    ./build.sh <model>    # other models: use the name from /etc/model on the NAS

`build.sh` needs `mksapkg-OS5` from [WDCommunity/wdpksrc](https://github.com/WDCommunity/wdpksrc)
(default location `../wdpksrc/mksapkg-OS5`, or set `MKSAPKG=/path/to/mksapkg-OS5`).

## Files on the NAS

Data folder (change it in the app under "Storage location", default
`/mnt/HD/HD_a2/Nas_Prog/scriptscheduler_data`):

    schedule.conf      the schedule (cron syntax, see the comment in the file)
    scripts/           your scripts
    logs/<script>.log  output and exit code of every run (trimmed above 1 MB)

Schedule lines: `minute hour day month weekday script [arguments]` or
`@boot script [arguments]`. A leading `#off ` disables an entry. File changes
take effect from the next minute.

## Behaviour

- Scripts run as root with `scripts/` as the working directory.
- A script never runs twice at once: if the previous run is still active, the
  new one is skipped (noted in the log).
- The interpreter is taken from the first line (`#!/bin/bash` …), otherwise
  `/bin/sh`. Windows line endings (CRLF) are removed when a script is run.
- Scheduling is done by a small daemon of its own (`bin/schedulerd.sh`),
  independent of the firmware's crontab, which is regenerated on every boot.
- Disabling the app in the dashboard stops all scheduled runs.
- Uninstalling keeps the data folder and `Nas_Prog/scriptscheduler.conf`.

## Security

Anyone who can write to the data folder can run commands as root. If the folder
is inside a share, that share must be writable by trusted users only (not
"Public"). The web interface is only reachable for a logged-in dashboard user.

## License

MIT, see [LICENSE](LICENSE).

---

## Deutsch

App für das WD My Cloud (1-Bay, Gen2, Firmware 5.x), die eigene Shell-Skripte
nach Zeitplan ausführt. Bedienung über das Dashboard (Apps → Script Scheduler →
Konfigurieren) oder direkt über Dateien.

Sprache: Deutsch und Englisch. Die App folgt der Spracheinstellung des NAS
(Dashboard → Einstellungen → Sprache): bei Deutsch ist alles deutsch, bei jeder
anderen Sprache englisch – Oberfläche, Fehlermeldungen und Protokollzeilen.

### Bauen und installieren

    ./build.sh                 # erzeugt ../packages/scriptscheduler_<Version>_WDMyCloud.bin

`build.sh` braucht `mksapkg-OS5` aus [WDCommunity/wdpksrc](https://github.com/WDCommunity/wdpksrc)
(Standard: `../wdpksrc/mksapkg-OS5`, sonst `MKSAPKG=/pfad ./build.sh`). Für andere Modelle den Modellnamen aus `/etc/model`
des NAS übergeben: `./build.sh <Modell>`.

Installation: Dashboard → Apps → „App manuell installieren“ und die `.bin` wählen.
Für ein Update zuerst in `apkg.rc` die Version erhöhen, neu bauen und die neue `.bin`
genauso installieren; Skripte, Zeitplan und Protokolle bleiben erhalten.

### Dateien auf dem NAS

Datenordner (in der App unter „Speicherort“ änderbar, Standard
`/mnt/HD/HD_a2/Nas_Prog/scriptscheduler_data`):

    schedule.conf      Zeitplan (Cron-Syntax, siehe Kommentar in der Datei)
    scripts/           die Skripte
    logs/<Skript>.log  Ausgabe und Exit-Code jedes Laufs (wird ab 1 MB gekürzt)

Zeitplan-Zeilen: `Minute Stunde Tag Monat Wochentag Skript [Argumente]` oder
`@boot Skript [Argumente]` (einmal nach dem Hochfahren). `#off ` am Zeilenanfang
deaktiviert einen Eintrag. Änderungen an den Dateien wirken ab der nächsten Minute.

### Verhalten

- Skripte laufen als root, mit dem Ordner `scripts/` als Arbeitsverzeichnis.
- Ein Skript läuft nie doppelt: Ist der vorherige Lauf noch aktiv, wird der neue
  übersprungen (steht im Protokoll).
- Der Interpreter kommt aus der ersten Zeile (`#!/bin/bash` …), sonst `/bin/sh`.
  Windows-Zeilenenden (CRLF) werden beim Ausführen automatisch entfernt.
- Der Zeitplan läuft über einen eigenen kleinen Dienst (`bin/schedulerd.sh`),
  unabhängig vom crontab der Firmware, der bei jedem Neustart neu erzeugt wird.
- Wird die App im Dashboard deaktiviert, starten keine Skripte mehr.
- Beim Deinstallieren bleiben der Datenordner und
  `/mnt/HD/HD_a2/Nas_Prog/scriptscheduler.conf` bewusst erhalten.

### Sicherheit

Wer im Datenordner schreiben darf, kann Befehle als root ausführen. Liegt der
Ordner in einer Freigabe, darf diese nur für vertrauenswürdige Benutzer
schreibbar sein (nicht „Public“).
