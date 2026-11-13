<?php
/*
 * Script Scheduler - JSON API for the dashboard page.
 *
 * Every request is a POST with one field "payload" = base64(JSON {action: ..., ...}).
 * (base64 keeps shell script text from tripping the NAS's web application firewall.)
 * The NAS front end (nasAdmin) only forwards requests of a logged-in dashboard user;
 * the X-Requested-With check below additionally blocks cross-site form posts.
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

define('APP_DIR', dirname(dirname(realpath(__FILE__))));
define('NAS_PROG', dirname(APP_DIR));
define('CONF_FILE', NAS_PROG . '/scriptscheduler.conf');
define('DEFAULT_DATA_DIR', NAS_PROG . '/scriptscheduler_data');
define('RUN_DIR', '/tmp/scriptscheduler');
define('NAME_RE', '/^[A-Za-z0-9_-][A-Za-z0-9._-]{0,99}$/');
define('MAX_SCRIPT_BYTES', 1048576);
define('LOG_TAIL_BYTES', 131072);

/* ---------- language ---------- */

// Language of the NAS dashboard (3 = German); the page sends its own choice with every request.
function nas_lang()
{
	return trim((string)@shell_exec('xmldbc -g /language 2>/dev/null')) === '3' ? 'de' : 'en';
}

$UI_LANG = nas_lang();

$MESSAGES = array(
	'badname' => array('de' => 'Ungültiger Skriptname. Erlaubt sind Buchstaben, Ziffern, Punkt, Unterstrich und Bindestrich.', 'en' => 'Invalid script name. Allowed are letters, digits, dot, underscore and hyphen.'),
	'changed' => array('de' => 'Der Zeitplan wurde inzwischen geändert (z. B. über die Freigabe). Die Ansicht wurde neu geladen – bitte noch einmal versuchen.', 'en' => 'The schedule was changed in the meantime (e.g. via the share). The view has been reloaded – please try again.'),
	'post' => array('de' => 'POST erwartet', 'en' => 'POST expected'),
	'denied' => array('de' => 'Anfrage abgelehnt', 'en' => 'Request rejected'),
	'badreq' => array('de' => 'Ungültige Anfrage', 'en' => 'Invalid request'),
	'noscript' => array('de' => 'Skript nicht gefunden', 'en' => 'Script not found'),
	'bigedit' => array('de' => 'Skript ist zu groß für den Editor (max. 1 MB).', 'en' => 'The script is too large for the editor (max. 1 MB).'),
	'big' => array('de' => 'Skript ist zu groß (max. 1 MB).', 'en' => 'The script is too large (max. 1 MB).'),
	'exists' => array('de' => 'Ein Skript mit diesem Namen gibt es bereits.', 'en' => 'A script with this name already exists.'),
	'runningdel' => array('de' => 'Das Skript läuft gerade. Bitte zuerst stoppen.', 'en' => 'The script is currently running. Please stop it first.'),
	'running' => array('de' => 'Das Skript läuft bereits.', 'en' => 'The script is already running.'),
	'choose' => array('de' => 'Bitte ein Skript auswählen.', 'en' => 'Please choose a script.'),
	'newline' => array('de' => 'Zeilenumbrüche sind nicht erlaubt.', 'en' => 'Line breaks are not allowed.'),
	'fields' => array('de' => 'Der Zeitplan braucht genau 5 Felder: Minute Stunde Tag Monat Wochentag.', 'en' => 'The schedule needs exactly 5 fields: minute hour day month weekday.'),
	'noentry' => array('de' => 'Eintrag nicht gefunden', 'en' => 'Entry not found'),
	'badpath' => array('de' => 'Ungültiger Pfad.', 'en' => 'Invalid path.'),
	'noparent' => array('de' => 'Der übergeordnete Ordner existiert nicht.', 'en' => 'The parent folder does not exist.'),
	'notshare' => array('de' => 'Der Ordner muss innerhalb einer Freigabe auf einem NAS-Laufwerk liegen (z. B. /shares/<Freigabe>/ScriptScheduler).', 'en' => 'The folder must be inside a share on a NAS drive (e.g. /shares/<share>/ScriptScheduler).'),
	'appdir' => array('de' => 'Der App-Ordner selbst kann nicht verwendet werden (er wird bei Updates ersetzt).', 'en' => 'The app folder itself cannot be used (it is replaced on updates).'),
	'mkdir' => array('de' => 'Ordner konnte nicht angelegt werden.', 'en' => 'The folder could not be created.'),
	'unknown' => array('de' => 'Unbekannte Aktion', 'en' => 'Unknown action'),
	'write' => array('de' => 'Datei konnte nicht geschrieben werden: ', 'en' => 'The file could not be written: '),
);

function T($key)
{
	global $MESSAGES, $UI_LANG;
	return $MESSAGES[$key][$UI_LANG];
}

function fail($msg, $code = 400)
{
	http_response_code($code);
	echo json_encode(array('ok' => false, 'error' => $msg));
	exit;
}

function done($data = array())
{
	echo json_encode(array('ok' => true) + $data, JSON_INVALID_UTF8_SUBSTITUTE);
	exit;
}

/* ---------- paths ---------- */

function data_dir()
{
	static $dir = null;
	if ($dir !== null) return $dir;
	$dir = DEFAULT_DATA_DIR;
	if (is_file(CONF_FILE)) {
		foreach (file(CONF_FILE, FILE_IGNORE_NEW_LINES) as $line) {
			if (strncmp($line, 'DATA_DIR=', 9) === 0 && trim(substr($line, 9)) !== '') {
				$dir = trim(substr($line, 9));
				break;
			}
		}
	}
	return $dir;
}

function scripts_dir() { return data_dir() . '/scripts'; }
function log_dir() { return data_dir() . '/logs'; }
function status_dir() { return data_dir() . '/logs/.status'; }
function schedule_file() { return data_dir() . '/schedule.conf'; }

function ensure_dirs()
{
	// world-writable like every other folder on a NAS share, so files can be edited via SMB
	foreach (array(data_dir(), scripts_dir(), log_dir(), status_dir()) as $d) {
		if (!is_dir($d)) {
			@mkdir($d, 0777, true);
			@chmod($d, 0777);
		}
	}
	if (!is_file(schedule_file())) {
		$lang = nas_lang();
		$sample = (string)@file_get_contents(APP_DIR . '/bin/schedule.sample.' . $lang);
		@file_put_contents(schedule_file(), str_replace('@EXAMPLE@', $lang === 'de' ? 'beispiel.sh' : 'example.sh', $sample));
		@chmod(schedule_file(), 0666);
	}
}

function script_name($p)
{
	$name = isset($p['name']) ? (string)$p['name'] : '';
	if (!preg_match(NAME_RE, $name)) {
		fail(T('badname'));
	}
	return $name;
}

function write_atomic($file, $content, $mode)
{
	$tmp = $file . '.tmp' . getmypid();
	if (file_put_contents($tmp, $content) === false) {
		fail(T('write') . $file, 500);
	}
	@chmod($tmp, $mode);
	if (!rename($tmp, $file)) {
		@unlink($tmp);
		fail(T('write') . $file, 500);
	}
}

/* ---------- schedule ---------- */

function schedule_lines()
{
	$text = is_file(schedule_file()) ? (string)file_get_contents(schedule_file()) : '';
	$text = str_replace("\r\n", "\n", $text);
	$lines = $text === '' ? array() : explode("\n", rtrim($text, "\n"));
	return array($lines, md5($text));
}

function check_schedule_file($file)
{
	global $UI_LANG;
	$errors = array();
	$out = array();
	exec('awk -f ' . escapeshellarg(APP_DIR . '/bin/cron.awk') . ' -v mode=check -v lang=' . $UI_LANG . ' ' . escapeshellarg($file) . ' 2>&1', $out);
	foreach ($out as $row) {
		$parts = explode("\t", $row, 2);
		if (count($parts) === 2 && ctype_digit($parts[0])) $errors[(int)$parts[0]] = $parts[1];
	}
	return $errors;
}

function schedule_entries()
{
	list($lines, $hash) = schedule_lines();
	$errors = is_file(schedule_file()) ? check_schedule_file(schedule_file()) : array();
	$entries = array();
	foreach ($lines as $i => $raw) {
		$line = $raw;
		$enabled = true;
		if (preg_match('/^#off[ \t]+(.*)$/', $line, $m)) {
			$enabled = false;
			$line = $m[1];
		} elseif (preg_match('/^\s*(#|$)/', $line)) {
			continue;
		}
		$f = preg_split('/[ \t]+/', trim($line));
		$boot = (strncmp($f[0], '@boot', 5) === 0);
		$n = $boot ? 1 : 5;
		$entry = array(
			'line' => $i + 1,
			'enabled' => $enabled,
			'boot' => $boot,
			'expr' => $boot ? $f[0] : implode(' ', array_slice($f, 0, 5)),
			'script' => isset($f[$n]) ? $f[$n] : '',
			'args' => implode(' ', array_slice($f, $n + 1)),
			'error' => isset($errors[$i + 1]) ? $errors[$i + 1] : null,
			'raw' => $raw,
		);
		// keep the argument text exactly as written (inner spacing preserved)
		if (preg_match('/^\s*(?:\S+\s+){' . ($n + 1) . '}(.*)$/', $line, $m)) $entry['args'] = rtrim($m[1]);
		$entries[] = $entry;
	}
	return array($entries, $hash);
}

function require_hash($p, $hash)
{
	if (!isset($p['hash']) || $p['hash'] !== $hash) {
		fail(T('changed'), 409);
	}
}

function save_schedule_lines($lines)
{
	write_atomic(schedule_file(), implode("\n", $lines) . "\n", 0666);
}

/* ---------- scripts & status ---------- */

function read_status($name)
{
	$st = array();
	$file = status_dir() . '/' . $name;
	if (is_file($file)) {
		foreach (file($file, FILE_IGNORE_NEW_LINES) as $line) {
			$kv = explode('=', $line, 2);
			if (count($kv) === 2) $st[$kv[0]] = $kv[1];
		}
	}
	$res = array('state' => 'never', 'start' => null, 'end' => null, 'exit' => null, 'trigger' => null);
	if (!$st) return $res;
	$res['start'] = isset($st['start']) ? (int)$st['start'] : null;
	$res['trigger'] = isset($st['trigger']) ? $st['trigger'] : null;
	if (isset($st['state']) && $st['state'] === 'running') {
		$w = isset($st['wrapper']) ? (int)$st['wrapper'] : 0;
		$cmd = $w > 0 ? @file_get_contents('/proc/' . $w . '/cmdline') : false;
		// the run.sh wrapper vanished without writing a result (reboot, kill -9)
		$res['state'] = ($cmd !== false && strpos($cmd, 'run.sh') !== false) ? 'running' : 'aborted';
	} else {
		$res['state'] = 'done';
		$res['end'] = isset($st['end']) ? (int)$st['end'] : null;
		$res['exit'] = isset($st['exit']) ? (int)$st['exit'] : null;
	}
	return $res;
}

function list_scripts()
{
	$out = array();
	$dir = scripts_dir();
	$names = is_dir($dir) ? scandir($dir) : array();
	foreach ($names as $name) {
		$path = $dir . '/' . $name;
		if (!is_file($path)) continue;
		$valid = (bool)preg_match(NAME_RE, $name);
		$log = log_dir() . '/' . $name . '.log';
		$out[] = array(
			'name' => $name,
			'valid' => $valid,
			'size' => filesize($path),
			'mtime' => filemtime($path),
			'status' => $valid ? read_status($name) : null,
			'log_size' => ($valid && is_file($log)) ? filesize($log) : 0,
		);
	}
	return $out;
}

function daemon_running()
{
	$pid = (int)@file_get_contents(RUN_DIR . '/schedulerd.pid');
	if ($pid <= 0) return false;
	$cmd = @file_get_contents('/proc/' . $pid . '/cmdline');
	return $cmd !== false && strpos($cmd, 'schedulerd') !== false;
}

function list_shares()
{
	$shares = array();
	foreach ((array)glob('/shares/*') as $link) {
		$real = realpath($link);
		$name = basename($link);
		if ($real === false || !is_dir($real) || $name === 'Volume_1') continue;
		if (!preg_match('#^/mnt/HD/HD_[a-z]\d/[^/]+$#', $real)) continue;
		$shares[] = array('name' => $name, 'path' => $real);
	}
	return $shares;
}

function background($cmd)
{
	exec('nohup ' . $cmd . ' </dev/null >/dev/null 2>&1 &');
}

/* ---------- request ---------- */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail(T('post'), 405);
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'], 'XMLHttpRequest') !== 0) {
	fail(T('denied'), 403);
}
$p = json_decode((string)base64_decode(isset($_POST['payload']) ? $_POST['payload'] : '', true), true);
if (!is_array($p) || !isset($p['action'])) fail(T('badreq'));
if (isset($p['lang']) && ($p['lang'] === 'de' || $p['lang'] === 'en')) $UI_LANG = $p['lang'];

ensure_dirs();

switch ($p['action']) {

case 'state':
	list($entries, $hash) = schedule_entries();
	$out = array();
	exec($UI_LANG === 'de' ? 'date "+%d.%m.%Y %H:%M"' : 'date "+%Y-%m-%d %H:%M"', $out);
	done(array(
		'entries' => $entries,
		'hash' => $hash,
		'scripts' => list_scripts(),
		'daemon' => daemon_running(),
		'data_dir' => data_dir(),
		'default_data_dir' => DEFAULT_DATA_DIR,
		'shares' => list_shares(),
		'server_time' => isset($out[0]) ? $out[0] : '',
		'now' => time(),
	));

case 'script_get':
	$name = script_name($p);
	$file = scripts_dir() . '/' . $name;
	if (!is_file($file)) fail(T('noscript'), 404);
	if (filesize($file) > MAX_SCRIPT_BYTES) fail(T('bigedit'));
	done(array('name' => $name, 'content' => str_replace("\r\n", "\n", (string)file_get_contents($file))));

case 'script_save':
	$name = script_name($p);
	$file = scripts_dir() . '/' . $name;
	$content = isset($p['content']) ? (string)$p['content'] : '';
	if (strlen($content) > MAX_SCRIPT_BYTES) fail(T('big'));
	if (!empty($p['create']) && file_exists($file)) fail(T('exists'));
	$content = str_replace("\r\n", "\n", $content);
	if ($content !== '' && substr($content, -1) !== "\n") $content .= "\n";
	write_atomic($file, $content, 0777);
	done();

case 'script_delete':
	$name = script_name($p);
	$st = read_status($name);
	if ($st['state'] === 'running') fail(T('runningdel'));
	@unlink(scripts_dir() . '/' . $name);
	@unlink(status_dir() . '/' . $name);
	@unlink(log_dir() . '/' . $name . '.log');
	done();

case 'run':
	$name = script_name($p);
	if (!is_file(scripts_dir() . '/' . $name)) fail(T('noscript'), 404);
	$st = read_status($name);
	if ($st['state'] === 'running') fail(T('running'));
	background('/bin/sh ' . escapeshellarg(APP_DIR . '/bin/run.sh') . ' ' . escapeshellarg($name) . ' manual');
	usleep(300000);
	done();

case 'stop':
	$name = script_name($p);
	background('/bin/sh ' . escapeshellarg(APP_DIR . '/bin/stop-job.sh') . ' ' . escapeshellarg($name));
	usleep(500000);
	done();

case 'log_get':
	$name = script_name($p);
	$file = log_dir() . '/' . $name . '.log';
	$text = '';
	$truncated = false;
	if (is_file($file)) {
		$size = filesize($file);
		$fh = fopen($file, 'rb');
		if ($fh) {
			if ($size > LOG_TAIL_BYTES) {
				fseek($fh, -LOG_TAIL_BYTES, SEEK_END);
				fgets($fh); // drop the partial first line
				$truncated = true;
			}
			$text = (string)stream_get_contents($fh);
			fclose($fh);
		}
	}
	done(array('name' => $name, 'text' => $text, 'truncated' => $truncated, 'status' => read_status($name)));

case 'log_clear':
	$name = script_name($p);
	$file = log_dir() . '/' . $name . '.log';
	if (is_file($file)) file_put_contents($file, '');
	done();

case 'sched_save':
	list($lines, $hash) = schedule_lines();
	require_hash($p, $hash);
	$script = isset($p['script']) ? (string)$p['script'] : '';
	if (!preg_match(NAME_RE, $script)) fail(T('choose'));
	$expr = trim(preg_replace('/\s+/', ' ', isset($p['expr']) ? (string)$p['expr'] : ''));
	$args = trim(isset($p['args']) ? (string)$p['args'] : '');
	if (preg_match('/[\r\n\0]/', $expr . $args)) fail(T('newline'));
	if (!preg_match('/^@boot(\+\d{1,4})?$/', $expr) && count(explode(' ', $expr)) !== 5) {
		fail(T('fields'));
	}
	$new = $expr . ' ' . $script . ($args !== '' ? ' ' . $args : '');
	$tmp = tempnam('/tmp', 'ssch');
	file_put_contents($tmp, $new . "\n");
	$errors = check_schedule_file($tmp);
	@unlink($tmp);
	if ($errors) fail(reset($errors));
	if (empty($p['enabled'])) $new = '#off ' . $new;
	if (isset($p['line']) && $p['line'] !== null) {
		$idx = (int)$p['line'] - 1;
		if (!isset($lines[$idx])) fail(T('noentry'), 404);
		$lines[$idx] = $new;
	} else {
		$lines[] = $new;
	}
	save_schedule_lines($lines);
	done();

case 'sched_toggle':
case 'sched_delete':
	list($lines, $hash) = schedule_lines();
	require_hash($p, $hash);
	$idx = (isset($p['line']) ? (int)$p['line'] : 0) - 1;
	if (!isset($lines[$idx]) || preg_match('/^\s*$/', $lines[$idx])) fail(T('noentry'), 404);
	$isOff = (bool)preg_match('/^#off[ \t]+/', $lines[$idx]);
	if (!$isOff && preg_match('/^\s*#/', $lines[$idx])) fail(T('noentry'), 404);
	if ($p['action'] === 'sched_delete') {
		array_splice($lines, $idx, 1);
	} elseif ($isOff) {
		$lines[$idx] = preg_replace('/^#off[ \t]+/', '', $lines[$idx]);
	} else {
		$lines[$idx] = '#off ' . ltrim($lines[$idx]);
	}
	save_schedule_lines($lines);
	done();

case 'datadir_set':
	$path = rtrim(trim(isset($p['path']) ? (string)$p['path'] : ''), '/');
	if ($path === '') $path = DEFAULT_DATA_DIR;
	if (preg_match('/[\r\n\0]/', $path) || $path[0] !== '/') fail(T('badpath'));
	$parent = realpath(dirname($path));
	$base = basename($path);
	if ($parent === false || !is_dir($parent)) fail(T('noparent'));
	if ($base === '.' || $base === '..') fail(T('badpath'));
	$target = $parent . '/' . $base;
	if (is_link($target)) $target = realpath($target);
	if ($target === false || !preg_match('#^/mnt/HD/HD_[a-z]\d/[^/]+(/[^/]+)+$#', $target)) {
		fail(T('notshare'));
	}
	if (strpos($target . '/', APP_DIR . '/') === 0) fail(T('appdir'));
	$old = data_dir();
	if ($target !== $old) {
		if (!is_dir($target) && !@mkdir($target, 0777, true)) fail(T('mkdir'), 500);
		@chmod($target, 0777);
		// take the current scripts and schedule along unless the target already has its own
		if (!is_file($target . '/schedule.conf')) {
			if (is_file($old . '/schedule.conf')) copy($old . '/schedule.conf', $target . '/schedule.conf');
			if (is_dir($old . '/scripts') && !is_dir($target . '/scripts')) {
				exec('cp -a ' . escapeshellarg($old . '/scripts') . ' ' . escapeshellarg($target . '/scripts'));
			}
			@chmod($target . '/schedule.conf', 0666);
		}
		write_atomic(CONF_FILE, 'DATA_DIR=' . $target . "\n", 0644);
	}
	done(array('data_dir' => $target, 'old_data_dir' => $old));

default:
	fail(T('unknown'));
}
