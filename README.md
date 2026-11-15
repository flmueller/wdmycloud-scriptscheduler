# Script Scheduler for WD My Cloud (OS 5)

An app for the WD My Cloud (single bay, Gen2, firmware 5.x) that runs your own
shell scripts on a schedule. Manage everything from the dashboard (Apps →
Script Scheduler → Configure) or by editing plain files.

- Schedule entries: every N minutes, hourly, daily, on weekdays, monthly, at NAS
  start-up (`@boot`, optionally delayed: `@boot+5`) or any cron expression; entries can be disabled.
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

Data folder `/mnt/HD/HD_a2/Nas_Prog/scriptscheduler_data` (owned by root, not
part of any share; edit the files in the app or via SSH):

    schedule.conf      the schedule (cron syntax, see the comment in the file)
    scripts/           your scripts
    logs/<script>.log  output and exit code of every run (trimmed above 1 MB)

Schedule lines: `minute hour day month weekday script [arguments]` or
`@boot script [arguments]` (once after boot; `@boot+N` waits N minutes after
boot, N up to 1440). A leading `#off ` disables an entry. File changes
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
- Uninstalling keeps the data folder.

## Security

Whoever can change a script or the schedule is root on the NAS. Therefore the
data folder, the scripts and the schedule must belong to root and must not be
writable by anyone else; the app creates them that way (folders 700, files
600/700) and starts nothing while this is not the case. The web page then shows
a warning with a button that hands all files back to root. The web interface is
only reachable for a logged-in dashboard user.

Up to version 1.2.0 the data folder could be placed in a share and was
world-writable. Installing 1.3.0 moves scripts and schedule back to
`Nas_Prog/scriptscheduler_data` (the folder in the share is left in place) and
restricts the permissions.

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

Datenordner `/mnt/HD/HD_a2/Nas_Prog/scriptscheduler_data` (gehört root, liegt in
keiner Freigabe; Dateien in der App oder per SSH bearbeiten):

    schedule.conf      Zeitplan (Cron-Syntax, siehe Kommentar in der Datei)
    scripts/           die Skripte
    logs/<Skript>.log  Ausgabe und Exit-Code jedes Laufs (wird ab 1 MB gekürzt)

Zeitplan-Zeilen: `Minute Stunde Tag Monat Wochentag Skript [Argumente]` oder
`@boot Skript [Argumente]` (einmal nach dem Hochfahren; `@boot+N` wartet damit
N Minuten, höchstens 1440). `#off ` am Zeilenanfang
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
- Beim Deinstallieren bleibt der Datenordner bewusst erhalten.

### Sicherheit

Wer ein Skript oder den Zeitplan ändern kann, ist root auf dem NAS. Datenordner,
Skripte und Zeitplan müssen deshalb root gehören und dürfen für niemanden sonst
schreibbar sein. Die App legt sie so an (Ordner 700, Dateien 600/700) und startet
nichts, solange das nicht erfüllt ist. Die Web-Oberfläche zeigt dann eine Warnung
mit einem Knopf, der alle Dateien wieder root übergibt. Die Web-Oberfläche ist nur
für am Dashboard angemeldete Benutzer erreichbar.

Bis Version 1.2.0 konnte der Datenordner in einer Freigabe liegen und war für alle
schreibbar. Die Installation von 1.3.0 holt Skripte und Zeitplan zurück nach
`Nas_Prog/scriptscheduler_data` (der Ordner in der Freigabe bleibt liegen) und
schränkt die Rechte ein.
