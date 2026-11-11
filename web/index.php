<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Cache-Control" content="no-cache">
<title>Script Scheduler</title>
</head>
<body>
<style>
#ssch { font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.45; color: #1f2933; background: #fff; padding: 18px 22px 28px; text-align: left; box-sizing: border-box; min-height: 100%; }
#ssch *, #ssch *:before, #ssch *:after { box-sizing: border-box; }
#ssch h2 { font-size: 22px; font-weight: normal; margin: 0; color: #1f2933; }
#ssch h3 { font-size: 16px; font-weight: bold; margin: 0; color: #1f2933; }
#ssch .ssch-head { display: flex; align-items: center; flex-wrap: wrap; gap: 8px 14px; margin-bottom: 6px; }
#ssch .ssch-sub { color: #5b6770; font-size: 13px; }
#ssch .ssch-pill { display: inline-block; padding: 2px 10px; border-radius: 10px; font-size: 12px; font-weight: bold; white-space: nowrap; }
#ssch .ssch-ok { background: #dff3e4; color: #17633a; }
#ssch .ssch-bad { background: #fde3e1; color: #a12622; }
#ssch .ssch-run { background: #dcebfb; color: #134f8f; }
#ssch .ssch-idle { background: #e9edf0; color: #47525b; }
#ssch .ssch-section { margin-top: 24px; }
#ssch .ssch-bar { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 8px; }
#ssch .ssch-tablewrap { overflow-x: auto; border: 1px solid #d5dbe0; border-radius: 4px; }
#ssch table { width: 100%; border-collapse: collapse; background: #fff; }
#ssch th { text-align: left; font-size: 12px; font-weight: bold; color: #5b6770; background: #f3f5f7; padding: 7px 10px; border-bottom: 1px solid #d5dbe0; white-space: nowrap; }
#ssch td { padding: 8px 10px; border-bottom: 1px solid #e6eaed; vertical-align: middle; color: #1f2933; }
#ssch tr:last-child td { border-bottom: 0; }
#ssch td.ssch-actions { white-space: nowrap; text-align: right; }
#ssch td.ssch-empty { color: #5b6770; text-align: center; padding: 18px 10px; }
#ssch code, #ssch .ssch-mono { font-family: Consolas, Menlo, "DejaVu Sans Mono", monospace; font-size: 13px; }
#ssch code { background: #f0f2f4; padding: 1px 5px; border-radius: 3px; color: #1f2933; }
#ssch .ssch-off td { color: #8a949c; }
#ssch .ssch-err { color: #a12622; font-size: 12px; margin-top: 2px; }
#ssch .ssch-small { font-size: 12px; color: #5b6770; }
#ssch button { font: inherit; font-size: 13px; padding: 5px 12px; margin: 0 0 0 4px; border: 1px solid #b6bfc7; border-radius: 4px; background: #fff; color: #1f2933; cursor: pointer; height: auto; width: auto; min-width: 0; line-height: 1.3; }
#ssch button:hover { background: #eef1f4; }
#ssch button:disabled { opacity: .5; cursor: default; }
#ssch button.ssch-primary { background: #0067c5; border-color: #0067c5; color: #fff; }
#ssch button.ssch-primary:hover { background: #00559f; }
#ssch button.ssch-danger { color: #a12622; }
#ssch input[type=text], #ssch input[type=number], #ssch input[type=time], #ssch select, #ssch textarea { font: inherit; font-size: 14px; padding: 5px 8px; border: 1px solid #b6bfc7; border-radius: 4px; background: #fff; color: #1f2933; height: auto; margin: 0; max-width: 100%; }
#ssch input[type=number] { width: 80px; }
#ssch textarea { width: 100%; resize: vertical; white-space: pre; overflow: auto; tab-size: 4; }
#ssch label { color: #1f2933; font-size: 14px; }
#ssch .ssch-msg { margin: 12px 0 0; padding: 9px 12px; border-radius: 4px; background: #fde3e1; color: #7d1d1a; }
#ssch .ssch-msg.ssch-info { background: #dff3e4; color: #17633a; }
#ssch .ssch-overlay { position: fixed; left: 0; top: 0; right: 0; bottom: 0; background: rgba(15, 23, 30, .55); z-index: 99990; display: flex; align-items: center; justify-content: center; padding: 16px; }
#ssch .ssch-dialog { background: #fff; border-radius: 6px; width: 760px; max-width: 100%; max-height: 100%; display: flex; flex-direction: column; box-shadow: 0 8px 30px rgba(0, 0, 0, .35); }
#ssch .ssch-dialog.ssch-narrow { width: 580px; }
#ssch .ssch-dhead { padding: 14px 18px; border-bottom: 1px solid #e6eaed; display: flex; align-items: center; justify-content: space-between; gap: 10px; }
#ssch .ssch-dbody { padding: 16px 18px; overflow: auto; }
#ssch .ssch-dfoot { padding: 12px 18px; border-top: 1px solid #e6eaed; display: flex; justify-content: flex-end; align-items: center; gap: 4px; }
#ssch .ssch-row { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
#ssch .ssch-row > label:first-child { width: 110px; flex: none; color: #47525b; }
#ssch .ssch-days label { margin-right: 8px; white-space: nowrap; }
#ssch pre.ssch-log { margin: 0; padding: 10px 12px; background: #16202a; color: #dde4ea; border-radius: 4px; min-height: 240px; max-height: 58vh; overflow: auto; white-space: pre-wrap; word-break: break-word; font-family: Consolas, Menlo, "DejaVu Sans Mono", monospace; font-size: 12.5px; line-height: 1.4; }
#ssch .ssch-hidden { display: none !important; }
#ssch .ssch-store { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; }
</style>

<div id="ssch">
	<div class="ssch-head">
		<h2>Script Scheduler</h2>
		<span id="ssch-daemon" class="ssch-pill ssch-idle">…</span>
		<span id="ssch-time" class="ssch-sub"></span>
	</div>
	<div class="ssch-sub" data-th="sub"></div>
	<div id="ssch-msg" class="ssch-msg ssch-hidden"></div>

	<div class="ssch-section">
		<div class="ssch-bar">
			<h3 data-t="schedule">Schedule</h3>
			<button type="button" class="ssch-primary" id="ssch-add-entry" data-t="addEntry">Add entry</button>
		</div>
		<div class="ssch-tablewrap"><table>
			<thead><tr><th data-t="thActive">Active</th><th data-t="thWhen">When</th><th data-t="thScript">Script</th><th data-t="thArgs">Arguments</th><th></th></tr></thead>
			<tbody id="ssch-entries"></tbody>
		</table></div>
	</div>

	<div class="ssch-section">
		<div class="ssch-bar">
			<h3 data-t="scripts">Scripts</h3>
			<button type="button" class="ssch-primary" id="ssch-add-script" data-t="newScript">New script</button>
		</div>
		<div class="ssch-tablewrap"><table>
			<thead><tr><th>Name</th><th data-t="thLastRun">Last run</th><th data-t="thResult">Result</th><th></th></tr></thead>
			<tbody id="ssch-scripts"></tbody>
		</table></div>
	</div>

	<div class="ssch-section">
		<div class="ssch-bar"><h3 data-t="storage">Storage location</h3></div>
		<div class="ssch-small" style="margin-bottom:8px" data-th="storageInfo"></div>
		<div class="ssch-store">
			<select id="ssch-share"></select>
			<button type="button" id="ssch-set-datadir" data-t="changeStorage">Change storage location</button>
		</div>
		<div class="ssch-small" style="margin-top:8px" data-t="storageWarn"></div>
	</div>

	<!-- schedule entry dialog -->
	<div class="ssch-overlay ssch-hidden" id="ssch-dlg-entry">
		<div class="ssch-dialog ssch-narrow">
			<div class="ssch-dhead"><h3 id="ssch-entry-title"></h3></div>
			<div class="ssch-dbody">
				<div class="ssch-row"><label for="ssch-e-script" data-t="thScript">Script</label><select id="ssch-e-script"></select></div>
				<div class="ssch-row"><label for="ssch-e-type" data-t="lRun">Run</label>
					<select id="ssch-e-type">
						<option value="minutes" data-t="tMinutes">every … minutes</option>
						<option value="hourly" data-t="tHourly">hourly</option>
						<option value="daily" data-t="tDaily">daily</option>
						<option value="weekly" data-t="tWeekly">on weekdays</option>
						<option value="monthly" data-t="tMonthly">monthly</option>
						<option value="boot" data-t="tBoot">when the NAS starts</option>
						<option value="cron" data-t="tCron">Expert (cron expression)</option>
					</select>
				</div>
				<div class="ssch-row" data-for="minutes"><label for="ssch-e-every" data-t="lInterval">Interval</label><input type="number" id="ssch-e-every" min="1" max="59" value="15"> <span data-t="minutes">minutes</span></div>
				<div class="ssch-row" data-for="hourly"><label for="ssch-e-minute" data-t="lAtMinute">At minute</label><input type="number" id="ssch-e-minute" min="0" max="59" value="0"></div>
				<div class="ssch-row ssch-days" data-for="weekly"><label data-t="lWeekdays">Weekdays</label>
					<span id="ssch-e-days"></span>
				</div>
				<div class="ssch-row" data-for="monthly"><label for="ssch-e-dom" data-t="lDom">Day of month</label><input type="number" id="ssch-e-dom" min="1" max="31" value="1"></div>
				<div class="ssch-row" data-for="daily weekly monthly"><label for="ssch-e-time" data-t="lTime">Time</label><input type="time" id="ssch-e-time" value="03:00"></div>
				<div class="ssch-row" data-for="cron"><label for="ssch-e-cron" data-t="lCron">Cron expression</label><input type="text" id="ssch-e-cron" class="ssch-mono" style="width:220px" placeholder="*/10 8-18 * * 1-5">
					<div class="ssch-small" style="width:100%;padding-left:118px" data-t="cronHint">minute hour day month weekday</div></div>
				<div class="ssch-row"><label for="ssch-e-args" data-t="thArgs">Arguments</label><input type="text" id="ssch-e-args" style="flex:1;min-width:160px" placeholder="optional" data-tp="optional"></div>
				<div class="ssch-row"><label></label><label><input type="checkbox" id="ssch-e-enabled" checked> <span data-t="active">Entry is active</span></label></div>
				<div id="ssch-entry-err" class="ssch-err"></div>
			</div>
			<div class="ssch-dfoot">
				<button type="button" data-close data-t="cancel">Cancel</button>
				<button type="button" class="ssch-primary" id="ssch-entry-save" data-t="save">Save</button>
			</div>
		</div>
	</div>

	<!-- script editor dialog -->
	<div class="ssch-overlay ssch-hidden" id="ssch-dlg-script">
		<div class="ssch-dialog">
			<div class="ssch-dhead"><h3 id="ssch-script-title"></h3></div>
			<div class="ssch-dbody">
				<div class="ssch-row" id="ssch-s-namerow"><label for="ssch-s-name" data-t="lFilename">File name</label><input type="text" id="ssch-s-name" class="ssch-mono" style="width:260px" placeholder="my-script.sh" data-tp="phName" maxlength="100"></div>
				<textarea id="ssch-s-content" class="ssch-mono" rows="20" spellcheck="false" wrap="off"></textarea>
				<div id="ssch-script-err" class="ssch-err"></div>
			</div>
			<div class="ssch-dfoot">
				<button type="button" data-close data-t="cancel">Cancel</button>
				<button type="button" class="ssch-primary" id="ssch-script-save" data-t="save">Save</button>
			</div>
		</div>
	</div>

	<!-- log dialog -->
	<div class="ssch-overlay ssch-hidden" id="ssch-dlg-log">
		<div class="ssch-dialog">
			<div class="ssch-dhead"><h3 id="ssch-log-title"></h3><span id="ssch-log-state" class="ssch-pill ssch-idle"></span></div>
			<div class="ssch-dbody">
				<div id="ssch-log-note" class="ssch-small ssch-hidden" style="margin-bottom:6px" data-t="logTail">Only the end of the log is shown.</div>
				<pre class="ssch-log" id="ssch-log-text"></pre>
			</div>
			<div class="ssch-dfoot">
				<button type="button" class="ssch-danger" id="ssch-log-clear" style="margin-right:auto;margin-left:0" data-t="clearLog">Clear log</button>
				<button type="button" data-close data-t="close">Close</button>
			</div>
		</div>
	</div>
</div>

<script>
(function () {
	var API = '/apps/scriptscheduler/api.php';
	var root = document.getElementById('ssch');
	if (!root) return;
	var $ = function (id) { return document.getElementById(id); };
	var state = null;
	var editLine = null;      // schedule line being edited (null = new)
	var editScript = null;    // script being edited (null = new)
	var logName = null;
	// Follow the dashboard language (3 = German); outside the dashboard use the browser language.
	var lang = (typeof MULTI_LANGUAGE !== 'undefined')
		? (String(MULTI_LANGUAGE) === '3' ? 'de' : 'en')
		: (/^de/i.test(navigator.language || '') ? 'de' : 'en');
	var I18N = {
		schedule: ["Zeitplan", "Schedule"],
		addEntry: ["Eintrag hinzufügen", "Add entry"],
		thActive: ["Aktiv", "Active"],
		thWhen: ["Wann", "When"],
		thScript: ["Skript", "Script"],
		thArgs: ["Argumente", "Arguments"],
		scripts: ["Skripte", "Scripts"],
		newScript: ["Neues Skript", "New script"],
		thLastRun: ["Letzter Lauf", "Last run"],
		thResult: ["Ergebnis", "Result"],
		storage: ["Speicherort", "Storage location"],
		changeStorage: ["Speicherort ändern", "Change storage location"],
		lRun: ["Ausführen", "Run"],
		tMinutes: ["alle … Minuten", "every … minutes"],
		tHourly: ["stündlich", "hourly"],
		tDaily: ["täglich", "daily"],
		tWeekly: ["an Wochentagen", "on weekdays"],
		tMonthly: ["monatlich", "monthly"],
		tBoot: ["beim Start des NAS", "when the NAS starts"],
		tCron: ["Experte (Cron-Ausdruck)", "Expert (cron expression)"],
		lInterval: ["Abstand", "Interval"],
		minutes: ["Minuten", "minutes"],
		lAtMinute: ["Zur Minute", "At minute"],
		lWeekdays: ["Wochentage", "Weekdays"],
		lDom: ["Tag im Monat", "Day of month"],
		lTime: ["Uhrzeit", "Time"],
		lCron: ["Cron-Ausdruck", "Cron expression"],
		cronHint: ["Minute Stunde Tag Monat Wochentag", "minute hour day month weekday"],
		save: ["Speichern", "Save"],
		lFilename: ["Dateiname", "File name"],
		logTail: ["Es wird nur das Ende des Protokolls angezeigt.", "Only the end of the log is shown."],
		clearLog: ["Protokoll leeren", "Clear log"],
		close: ["Schließen", "Close"],
		cancel: ["Abbrechen", "Cancel"],
		active: ["Eintrag ist aktiv", "Entry is active"],
		optional: ["optional", "optional"],
		phName: ["mein-skript.sh", "my-script.sh"],
		sub: ["Eigene Shell-Skripte nach Zeitplan ausführen. Skripte laufen als <code>root</code>.", "Run your own shell scripts on a schedule. Scripts run as <code>root</code>."],
		storageInfo: ["Skripte (<code>scripts/</code>), Zeitplan (<code>schedule.conf</code>) und Protokolle (<code>logs/</code>) liegen in <code id=\"ssch-datadir\"></code>. Liegt der Ordner in einer Freigabe, lassen sich die Dateien auch direkt über das Netzlaufwerk bearbeiten; Änderungen werden innerhalb einer Minute übernommen.", "Scripts (<code>scripts/</code>), the schedule (<code>schedule.conf</code>) and logs (<code>logs/</code>) are stored in <code id=\"ssch-datadir\"></code>. If the folder is inside a share, the files can also be edited directly over the network; changes are picked up within a minute."],
		storageWarn: ["Achtung: Wer in diesem Ordner schreiben darf, kann Befehle als root auf dem NAS ausführen. Nur eine Freigabe wählen, auf die ausschließlich vertrauenswürdige Benutzer Schreibzugriff haben (nicht „Public“).", "Warning: anyone who can write to this folder can run commands as root on the NAS. Only choose a share that exclusively trusted users can write to (not “Public”)."],
		days: ["So Mo Di Mi Do Fr Sa", "Sun Mon Tue Wed Thu Fri Sat"],
		newScriptComment: ["Was das Skript ausgibt, landet im Protokoll.", "Everything the script prints ends up in the log."],
		eSession: ["Nicht angemeldet oder Sitzung abgelaufen. Bitte neu am Dashboard anmelden.", "Not logged in or session expired. Please log in to the dashboard again."],
		eHttp: ["Das NAS hat nicht wie erwartet geantwortet (HTTP {0}).", "The NAS did not respond as expected (HTTP {0})."],
		dBoot: ["Beim Start des NAS", "When the NAS starts"],
		dMinutes: ["Alle {0} Minuten", "Every {0} minutes"],
		dHourly: ["Stündlich zur Minute {0}", "Hourly at minute {0}"],
		dDaily: ["Täglich um {0} Uhr", "Daily at {0}"],
		dWeekly: ["{0} um {1} Uhr", "{0} at {1}"],
		dMonthly: ["Monatlich am {0}. um {1} Uhr", "Monthly on day {0} at {1}"],
		noEntries: ["Noch keine Einträge. Mit „Eintrag hinzufügen“ ein Skript einplanen.", "No entries yet. Use “Add entry” to schedule a script."],
		badEntry: ["Fehlerhafter Eintrag: {0}", "Invalid entry: {0}"],
		scriptMissing: ["Skript nicht gefunden", "Script not found"],
		disable: ["Deaktivieren", "Disable"],
		cDelEntry: ["Diesen Zeitplan-Eintrag löschen?", "Delete this schedule entry?"],
		sNever: ["noch nie gelaufen", "never run"],
		sRunningFor: ["läuft seit {0}", "running for {0}"],
		sAborted: ["abgebrochen", "aborted"],
		sOk: ["erfolgreich", "successful"],
		sFail: ["Fehler (Exit-Code {0})", "Failed (exit code {0})"],
		noScripts: ["Noch keine Skripte. Mit „Neues Skript“ eines anlegen oder Dateien in den Ordner scripts/ kopieren.", "No scripts yet. Use “New script” to create one or copy files into the scripts/ folder."],
		badFile: ["Dateiname nicht verwendbar – erlaubt sind nur Buchstaben, Ziffern, Punkt, Unterstrich und Bindestrich (keine Leerzeichen/Umlaute).", "File name cannot be used – only letters, digits, dot, underscore and hyphen are allowed (no spaces or special characters)."],
		trig_schedule: ["Zeitplan", "schedule"],
		duration: [", Dauer {0}", ", took {0}"],
		stop: ["Stoppen", "Stop"],
		cStop: ["Laufendes Skript „{0}“ abbrechen?", "Stop the running script “{0}”?"],
		runNow: ["Jetzt starten", "Run now"],
		log: ["Protokoll", "Log"],
		cDelScript: ["Skript „{0}“ und sein Protokoll endgültig löschen?", "Permanently delete the script “{0}” and its log?"],
		cDelUsed: ["Achtung: Es wird noch in {0} Zeitplan-Eintrag/Einträgen verwendet.", "Warning: it is still used by {0} schedule entry/entries."],
		svcOn: ["Zeitplan-Dienst läuft", "Scheduler service running"],
		svcHint: ["Die App ist deaktiviert oder der Dienst wurde beendet. App unter „Apps“ aus- und wieder einschalten.", "The app is disabled or the service was stopped. Switch the app off and on again under “Apps”."],
		nasTime: ["NAS-Zeit: {0}", "NAS time: {0}"],
		stInternal: ["Intern (nicht über das Netzwerk erreichbar)", "Internal (not reachable over the network)"],
		stShare: ["Freigabe „{0}“ → Ordner ScriptScheduler", "Share “{0}” → folder ScriptScheduler"],
		editEntry: ["Zeitplan-Eintrag bearbeiten", "Edit schedule entry"],
		needScript: ["Bitte zuerst ein Skript anlegen.", "Please create a script first."],
		eNumber: ["{0}: bitte eine Zahl von {1} bis {2} eingeben.", "{0}: please enter a number from {1} to {2}."],
		eCron: ["Der Cron-Ausdruck braucht genau 5 Felder: Minute Stunde Tag Monat Wochentag.", "The cron expression needs exactly 5 fields: minute hour day month weekday."],
		minute: ["Minute", "Minute"],
		eTime: ["Bitte eine Uhrzeit im Format HH:MM angeben.", "Please enter a time in the format HH:MM."],
		eDays: ["Bitte mindestens einen Wochentag wählen.", "Please choose at least one weekday."],
		editScript: ["Skript bearbeiten: {0}", "Edit script: {0}"],
		eFilename: ["Dateiname: erlaubt sind Buchstaben, Ziffern, Punkt, Unterstrich und Bindestrich (z. B. backup.sh).", "File name: allowed are letters, digits, dot, underscore and hyphen (e.g. backup.sh)."],
		logEmpty: ["(Das Protokoll ist leer.)", "(The log is empty.)"],
		sRunning: ["läuft", "running"],
		logTitle: ["Protokoll: {0}", "Log: {0}"],
		cClearLog: ["Protokoll von „{0}“ leeren?", "Clear the log of “{0}”?"],
		stSame: ["Dieser Speicherort ist bereits eingestellt.", "This storage location is already in use."],
		cStorage: ["Speicherort ändern auf\n{0}?\n\nVorhandene Skripte und der Zeitplan werden dorthin kopiert, falls dort noch keine liegen. Der bisherige Ordner bleibt unverändert bestehen.", "Change the storage location to\n{0}?\n\nExisting scripts and the schedule are copied there unless that folder already has its own. The previous folder is left unchanged."],
		stChanged: ["Speicherort geändert: {0}", "Storage location changed: {0}"],
		dMinute: ["Jede Minute", "Every minute"],
		enable: ["Aktivieren", "Enable"],
		trig_manual: ["manuell", "manual"],
		trig_boot: ["Systemstart", "boot"],
		svcOff: ["Zeitplan-Dienst gestoppt", "Scheduler service stopped"],
		newEntry: ["Neuer Zeitplan-Eintrag", "New schedule entry"],
		edit: ["Bearbeiten", "Edit"],
		del: ["Löschen", "Delete"]
	};
	function t(key) {
		var str = I18N[key] ? I18N[key][lang === 'de' ? 0 : 1] : key;
		for (var i = 1; i < arguments.length; i++) str = str.split('{' + (i - 1) + '}').join(arguments[i]);
		return str;
	}
	var DAYS = t('days').split(' ');
	var NEW_SCRIPT = '#!/bin/sh\n# ' + t('newScriptComment') + '\n\necho "Start: $(date)"\n';

	// The dashboard may load this page more than once; stop timers of an older copy.
	if (window.__sschTimer) clearInterval(window.__sschTimer);

	function el(tag, attrs, children) {
		var e = document.createElement(tag);
		for (var k in (attrs || {})) {
			if (k === 'text') e.textContent = attrs[k];
			else if (k === 'on') { for (var ev in attrs.on) e.addEventListener(ev, attrs.on[ev]); }
			else if (attrs[k] !== null && attrs[k] !== false) e.setAttribute(k, attrs[k] === true ? '' : attrs[k]);
		}
		(children || []).forEach(function (c) { if (c) e.appendChild(typeof c === 'string' ? document.createTextNode(c) : c); });
		return e;
	}

	function b64(str) {
		return btoa(unescape(encodeURIComponent(str)));
	}

	function api(payload, cb, errTarget) {
		payload.lang = lang;
		var xhr = new XMLHttpRequest();
		xhr.open('POST', API, true);
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
		xhr.onreadystatechange = function () {
			if (xhr.readyState !== 4) return;
			var res = null;
			try { res = JSON.parse(xhr.responseText); } catch (e) {}
			if (res && res.ok) { cb(res); return; }
			var msg = (res && res.error) ? res.error
				: (xhr.status === 401 || xhr.status === 403) ? t('eSession')
				: t('eHttp', xhr.status);
			if (payload.silent) return;
			if (errTarget) errTarget.textContent = msg; else showMsg(msg);
			if (xhr.status === 409) refresh();
		};
		xhr.send('payload=' + encodeURIComponent(b64(JSON.stringify(payload))));
	}

	function showMsg(text, info) {
		var m = $('ssch-msg');
		m.textContent = text;
		m.className = 'ssch-msg' + (info ? ' ssch-info' : '');
		clearTimeout(showMsg.t);
		showMsg.t = setTimeout(function () { m.className = 'ssch-msg ssch-hidden'; }, info ? 5000 : 12000);
	}

	function pad(n) { return (n < 10 ? '0' : '') + n; }

	function fmtTime(ts) {
		if (!ts) return '–';
		var d = new Date(ts * 1000);
		var hm = pad(d.getHours()) + ':' + pad(d.getMinutes());
		if (lang !== 'de') return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' ' + hm;
		return pad(d.getDate()) + '.' + pad(d.getMonth() + 1) + '.' + d.getFullYear() + ' ' + hm;
	}

	function fmtDuration(s) {
		if (s < 60) return s + ' s';
		if (s < 3600) return Math.floor(s / 60) + ' min ' + (s % 60) + ' s';
		return Math.floor(s / 3600) + ' h ' + Math.floor((s % 3600) / 60) + ' min';
	}

	// "30 2 * * *" -> {type: 'daily', ...}; anything unusual stays a raw cron expression
	function parseExpr(expr) {
		if (expr === '@boot') return { type: 'boot' };
		var f = expr.split(' ');
		if (f.length !== 5) return { type: 'cron' };
		var num = /^\d+$/, m;
		if (f[1] === '*' && f[2] === '*' && f[3] === '*' && f[4] === '*') {
			if (f[0] === '*') return { type: 'minutes', every: 1 };
			if ((m = /^\*\/(\d+)$/.exec(f[0])) && +m[1] >= 1 && +m[1] <= 59) return { type: 'minutes', every: +m[1] };
			if (num.test(f[0])) return { type: 'hourly', minute: +f[0] };
		}
		if (num.test(f[0]) && num.test(f[1]) && f[3] === '*') {
			var time = pad(+f[1]) + ':' + pad(+f[0]);
			if (f[2] === '*' && f[4] === '*') return { type: 'daily', time: time };
			if (f[2] === '*' && /^[0-7](,[0-7])*$/.test(f[4])) {
				return { type: 'weekly', time: time, days: f[4].split(',').map(function (d) { return +d % 7; }) };
			}
			if (num.test(f[2]) && f[4] === '*') return { type: 'monthly', time: time, dom: +f[2] };
		}
		return { type: 'cron' };
	}

	function describe(expr) {
		var p = parseExpr(expr);
		switch (p.type) {
			case 'boot': return t('dBoot');
			case 'minutes': return p.every === 1 ? t('dMinute') : t('dMinutes', p.every);
			case 'hourly': return t('dHourly', pad(p.minute));
			case 'daily': return t('dDaily', p.time);
			case 'weekly':
				var order = [1, 2, 3, 4, 5, 6, 0];
				return t('dWeekly', order.filter(function (d) { return p.days.indexOf(d) >= 0; }).map(function (d) { return DAYS[d]; }).join(', '), p.time);
			case 'monthly': return t('dMonthly', p.dom, p.time);
		}
		return null;
	}

	function scriptExists(name) {
		return state.scripts.some(function (s) { return s.name === name; });
	}

	/* ---------- rendering ---------- */

	function renderEntries() {
		var tb = $('ssch-entries');
		tb.textContent = '';
		if (!state.entries.length) {
			tb.appendChild(el('tr', {}, [el('td', { colspan: 5, 'class': 'ssch-empty', text: t('noEntries') })]));
			return;
		}
		state.entries.forEach(function (en) {
			var desc = en.error ? null : describe(en.expr);
			var when = el('td', {}, [desc ? el('div', { text: desc }) : null, el('div', { 'class': desc ? 'ssch-small ssch-mono' : 'ssch-mono', text: en.expr })]);
			var scriptCell = el('td', {}, [el('span', { 'class': 'ssch-mono', text: en.script })]);
			if (en.error) scriptCell.appendChild(el('div', { 'class': 'ssch-err', text: t('badEntry', en.error) }));
			else if (!scriptExists(en.script)) scriptCell.appendChild(el('div', { 'class': 'ssch-err', text: t('scriptMissing') }));
			var toggle = el('input', { type: 'checkbox', checked: en.enabled, title: en.enabled ? t('disable') : t('enable'), on: { change: function () {
				api({ action: 'sched_toggle', line: en.line, hash: state.hash }, refresh);
			} } });
			tb.appendChild(el('tr', { 'class': en.enabled ? '' : 'ssch-off' }, [
				el('td', {}, [toggle]),
				when,
				scriptCell,
				el('td', { 'class': 'ssch-mono', text: en.args }),
				el('td', { 'class': 'ssch-actions' }, [
					el('button', { type: 'button', text: t('edit'), on: { click: function () { openEntry(en); } } }),
					el('button', { type: 'button', 'class': 'ssch-danger', text: t('del'), on: { click: function () {
						if (confirm(t('cDelEntry') + '\n\n' + en.expr + '  ' + en.script)) api({ action: 'sched_delete', line: en.line, hash: state.hash }, refresh);
					} } })
				])
			]));
		});
	}

	function statusPill(st) {
		if (!st || st.state === 'never') return el('span', { 'class': 'ssch-pill ssch-idle', text: t('sNever') });
		if (st.state === 'running') return el('span', { 'class': 'ssch-pill ssch-run', text: t('sRunningFor', fmtDuration(Math.max(0, state.now - st.start))) });
		if (st.state === 'aborted') return el('span', { 'class': 'ssch-pill ssch-bad', text: t('sAborted') });
		if (st.exit === 0) return el('span', { 'class': 'ssch-pill ssch-ok', text: t('sOk') });
		return el('span', { 'class': 'ssch-pill ssch-bad', text: t('sFail', st.exit) });
	}

	function renderScripts() {
		var tb = $('ssch-scripts');
		tb.textContent = '';
		if (!state.scripts.length) {
			tb.appendChild(el('tr', {}, [el('td', { colspan: 4, 'class': 'ssch-empty', text: t('noScripts') })]));
			return;
		}
		state.scripts.forEach(function (s) {
			if (!s.valid) {
				tb.appendChild(el('tr', { 'class': 'ssch-off' }, [
					el('td', { 'class': 'ssch-mono', text: s.name }),
					el('td', { colspan: 3 }, [el('div', { 'class': 'ssch-err', text: t('badFile') })])
				]));
				return;
			}
			var st = s.status, running = st.state === 'running';
			var last = '–';
			if (st.start) {
				last = fmtTime(st.start) + (st.trigger ? ' (' + (I18N['trig_' + st.trigger] ? t('trig_' + st.trigger) : st.trigger) + ')' : '');
				if (st.end && st.state === 'done') last += t('duration', fmtDuration(st.end - st.start));
			}
			var runBtn = running
				? el('button', { type: 'button', 'class': 'ssch-danger', text: t('stop'), on: { click: function () {
					if (confirm(t('cStop', s.name))) api({ action: 'stop', name: s.name }, refresh);
				} } })
				: el('button', { type: 'button', text: t('runNow'), on: { click: function () {
					api({ action: 'run', name: s.name }, function () { refresh(); openLog(s.name); });
				} } });
			tb.appendChild(el('tr', {}, [
				el('td', { 'class': 'ssch-mono', text: s.name }),
				el('td', { text: last }),
				el('td', {}, [statusPill(st)]),
				el('td', { 'class': 'ssch-actions' }, [
					runBtn,
					el('button', { type: 'button', text: t('log'), on: { click: function () { openLog(s.name); } } }),
					el('button', { type: 'button', text: t('edit'), on: { click: function () { openScript(s.name); } } }),
					el('button', { type: 'button', 'class': 'ssch-danger', text: t('del'), disabled: running, on: { click: function () {
						var used = state.entries.filter(function (e) { return e.script === s.name; }).length;
						var q = t('cDelScript', s.name);
						if (used) q += '\n\n' + t('cDelUsed', used);
						if (confirm(q)) api({ action: 'script_delete', name: s.name }, refresh);
					} } })
				])
			]));
		});
	}

	function renderMeta() {
		var d = $('ssch-daemon');
		d.textContent = state.daemon ? t('svcOn') : t('svcOff');
		d.className = 'ssch-pill ' + (state.daemon ? 'ssch-ok' : 'ssch-bad');
		d.title = state.daemon ? '' : t('svcHint');
		$('ssch-time').textContent = t('nasTime', state.server_time);
		$('ssch-datadir').textContent = state.data_dir;

		var sel = $('ssch-share');
		if (document.activeElement !== sel) {
			sel.textContent = '';
			var opts = [{ label: t('stInternal'), path: state.default_data_dir }];
			state.shares.forEach(function (sh) { opts.push({ label: t('stShare', sh.name), path: sh.path + '/ScriptScheduler' }); });
			if (!opts.some(function (o) { return o.path === state.data_dir; })) opts.push({ label: state.data_dir, path: state.data_dir });
			opts.forEach(function (o) { sel.appendChild(el('option', { value: o.path, text: o.label, selected: o.path === state.data_dir })); });
		}
	}

	function refresh() {
		api({ action: 'state', silent: !!state }, function (res) {
			state = res;
			renderMeta();
			renderEntries();
			renderScripts();
		});
	}

	/* ---------- dialogs ---------- */

	function show(id) { $(id).classList.remove('ssch-hidden'); }
	function hide(id) { $(id).classList.add('ssch-hidden'); }

	function updateEntryFields() {
		var type = $('ssch-e-type').value;
		Array.prototype.forEach.call($('ssch-dlg-entry').querySelectorAll('[data-for]'), function (row) {
			row.classList.toggle('ssch-hidden', row.getAttribute('data-for').split(' ').indexOf(type) < 0);
		});
	}

	function openEntry(en) {
		if (!state) return;
		editLine = en ? en.line : null;
		$('ssch-entry-title').textContent = en ? t('editEntry') : t('newEntry');
		$('ssch-entry-err').textContent = '';
		var sel = $('ssch-e-script');
		sel.textContent = '';
		var names = state.scripts.filter(function (s) { return s.valid; }).map(function (s) { return s.name; });
		if (en && en.script && names.indexOf(en.script) < 0) names.push(en.script);
		if (!names.length) { showMsg(t('needScript')); return; }
		names.forEach(function (n) { sel.appendChild(el('option', { value: n, text: n })); });

		var p = en ? parseExpr(en.expr) : { type: 'daily', time: '03:00' };
		if (en) sel.value = en.script;
		$('ssch-e-type').value = p.type;
		$('ssch-e-every').value = p.every || 15;
		$('ssch-e-minute').value = p.minute || 0;
		$('ssch-e-time').value = p.time || '03:00';
		$('ssch-e-dom').value = p.dom || 1;
		$('ssch-e-cron').value = (en && !en.boot) ? en.expr : '';
		Array.prototype.forEach.call($('ssch-e-days').querySelectorAll('input'), function (cb) {
			cb.checked = p.days ? p.days.indexOf(+cb.value) >= 0 : (+cb.value >= 1 && +cb.value <= 5);
		});
		$('ssch-e-args').value = en ? en.args : '';
		$('ssch-e-enabled').checked = en ? en.enabled : true;
		updateEntryFields();
		show('ssch-dlg-entry');
	}

	function intIn(id, lo, hi, label) {
		var v = $(id).value.trim();
		if (!/^\d+$/.test(v) || +v < lo || +v > hi) throw new Error(t('eNumber', label, lo, hi));
		return +v;
	}

	function buildExpr() {
		var type = $('ssch-e-type').value;
		if (type === 'boot') return '@boot';
		if (type === 'cron') {
			var c = $('ssch-e-cron').value.trim().replace(/\s+/g, ' ');
			if (c.split(' ').length !== 5) throw new Error(t('eCron'));
			return c;
		}
		if (type === 'minutes') {
			var n = intIn('ssch-e-every', 1, 59, t('lInterval'));
			return (n === 1 ? '*' : '*/' + n) + ' * * * *';
		}
		if (type === 'hourly') return intIn('ssch-e-minute', 0, 59, t('minute')) + ' * * * *';
		var t = /^(\d{1,2}):(\d{2})/.exec($('ssch-e-time').value);
		if (!t || +t[1] > 23 || +t[2] > 59) throw new Error(t('eTime'));
		var hm = (+t[2]) + ' ' + (+t[1]);
		if (type === 'daily') return hm + ' * * *';
		if (type === 'monthly') return hm + ' ' + intIn('ssch-e-dom', 1, 31, t('lDom')) + ' * *';
		var days = Array.prototype.filter.call($('ssch-e-days').querySelectorAll('input'), function (cb) { return cb.checked; }).map(function (cb) { return +cb.value; });
		if (!days.length) throw new Error(t('eDays'));
		days.sort();
		return hm + ' * * ' + days.join(',');
	}

	function saveEntry() {
		var err = $('ssch-entry-err'), expr;
		err.textContent = '';
		try { expr = buildExpr(); } catch (e) { err.textContent = e.message; return; }
		api({
			action: 'sched_save', hash: state.hash, line: editLine, expr: expr,
			script: $('ssch-e-script').value, args: $('ssch-e-args').value, enabled: $('ssch-e-enabled').checked
		}, function () { hide('ssch-dlg-entry'); refresh(); }, err);
	}

	function openScript(name) {
		editScript = name || null;
		$('ssch-script-err').textContent = '';
		$('ssch-script-title').textContent = name ? t('editScript', name) : t('newScript');
		$('ssch-s-namerow').classList.toggle('ssch-hidden', !!name);
		$('ssch-s-name').value = '';
		if (!name) {
			$('ssch-s-content').value = NEW_SCRIPT;
			show('ssch-dlg-script');
			$('ssch-s-name').focus();
			return;
		}
		api({ action: 'script_get', name: name }, function (res) {
			$('ssch-s-content').value = res.content;
			show('ssch-dlg-script');
		});
	}

	function saveScript() {
		var err = $('ssch-script-err');
		err.textContent = '';
		var name = editScript || $('ssch-s-name').value.trim();
		if (!/^[A-Za-z0-9_-][A-Za-z0-9._-]{0,99}$/.test(name)) {
			err.textContent = t('eFilename');
			return;
		}
		api({ action: 'script_save', name: name, content: $('ssch-s-content').value, create: !editScript }, function () {
			hide('ssch-dlg-script');
			refresh();
		}, err);
	}

	function loadLog(scroll) {
		if (!logName) return;
		var name = logName;
		api({ action: 'log_get', name: name, silent: true }, function (res) {
			if (logName !== name) return;
			var pre = $('ssch-log-text');
			var atBottom = pre.scrollHeight - pre.scrollTop - pre.clientHeight < 30;
			var text = res.text || t('logEmpty');
			if (pre.textContent !== text) pre.textContent = text;
			if (scroll || atBottom) pre.scrollTop = pre.scrollHeight;
			$('ssch-log-note').classList.toggle('ssch-hidden', !res.truncated);
			var st = res.status, pill = $('ssch-log-state');
			pill.textContent = st.state === 'running' ? t('sRunning') : st.state === 'never' ? '' : st.state === 'aborted' ? t('sAborted') : st.exit === 0 ? t('sOk') : t('sFail', st.exit);
			pill.className = 'ssch-pill ' + (st.state === 'running' ? 'ssch-run' : st.state === 'never' ? 'ssch-hidden' : (st.state === 'done' && st.exit === 0) ? 'ssch-ok' : 'ssch-bad');
		});
	}

	function openLog(name) {
		logName = name;
		$('ssch-log-title').textContent = t('logTitle', name);
		$('ssch-log-text').textContent = '';
		$('ssch-log-state').className = 'ssch-pill ssch-hidden';
		show('ssch-dlg-log');
		loadLog(true);
	}

	function closeDialogs() {
		['ssch-dlg-entry', 'ssch-dlg-script', 'ssch-dlg-log'].forEach(hide);
		logName = null;
	}

	/* ---------- wiring ---------- */

	// static texts; the data-th entries contain markup and come only from the dictionary above
	Array.prototype.forEach.call(root.querySelectorAll('[data-t]'), function (e) { e.textContent = t(e.getAttribute('data-t')); });
	Array.prototype.forEach.call(root.querySelectorAll('[data-th]'), function (e) { e.innerHTML = t(e.getAttribute('data-th')); });
	Array.prototype.forEach.call(root.querySelectorAll('[data-tp]'), function (e) { e.placeholder = t(e.getAttribute('data-tp')); });

	[1, 2, 3, 4, 5, 6, 0].forEach(function (d) {
		$('ssch-e-days').appendChild(el('label', {}, [el('input', { type: 'checkbox', value: d }), ' ' + DAYS[d]]));
	});
	$('ssch-e-type').addEventListener('change', updateEntryFields);
	$('ssch-add-entry').addEventListener('click', function () { openEntry(null); });
	$('ssch-entry-save').addEventListener('click', saveEntry);
	$('ssch-add-script').addEventListener('click', function () { openScript(null); });
	$('ssch-script-save').addEventListener('click', saveScript);
	$('ssch-log-clear').addEventListener('click', function () {
		if (logName && confirm(t('cClearLog', logName))) api({ action: 'log_clear', name: logName }, function () { loadLog(true); refresh(); });
	});
	Array.prototype.forEach.call(root.querySelectorAll('[data-close]'), function (b) { b.addEventListener('click', closeDialogs); });
	$('ssch-dlg-log').addEventListener('mousedown', function (e) { if (e.target === this) closeDialogs(); });
	// Tab inserts a tab character in the script editor
	$('ssch-s-content').addEventListener('keydown', function (e) {
		if (e.key !== 'Tab' || e.shiftKey || e.ctrlKey || e.altKey) return;
		e.preventDefault();
		var s = this.selectionStart;
		this.value = this.value.substring(0, s) + '\t' + this.value.substring(this.selectionEnd);
		this.selectionStart = this.selectionEnd = s + 1;
	});
	$('ssch-set-datadir').addEventListener('click', function () {
		var path = $('ssch-share').value;
		if (!state || path === state.data_dir) { showMsg(t('stSame'), true); return; }
		if (!confirm(t('cStorage', path))) return;
		api({ action: 'datadir_set', path: path }, function (res) {
			showMsg(t('stChanged', res.data_dir), true);
			$('ssch-share').blur();
			refresh();
		});
	});

	var tick = 0;
	window.__sschTimer = setInterval(function () {
		if (!document.getElementById('ssch') || document.getElementById('ssch') !== root) { clearInterval(window.__sschTimer); return; }
		if (document.hidden) return;
		tick++;
		if (logName) loadLog(false);
		var editing = !$('ssch-dlg-entry').classList.contains('ssch-hidden') || !$('ssch-dlg-script').classList.contains('ssch-hidden');
		if (tick % 3 === 0 && !editing) refresh();
	}, 2000);

	refresh();
})();
</script>
</body>
</html>
