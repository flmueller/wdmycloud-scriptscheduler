# Parser for schedule.conf.
#
#   awk -f cron.awk -v mode=match -v m=5 -v h=14 -v dom=11 -v mon=11 -v dow=3 schedule.conf
#       prints "script<TAB>args" for every enabled entry that is due at that time
#   awk -f cron.awk -v mode=boot schedule.conf
#       prints "script<TAB>args" for every enabled @boot entry
#   awk -f cron.awk -v mode=check [-v lang=de] schedule.conf
#       prints "line<TAB>message" for every invalid entry (enabled or "#off")

BEGIN {
	DOW = "sunmontuewedthufrisat"
	MON = "janfebmaraprmayjunjulaugsepoctnovdec"
	m += 0; h += 0; dom += 0; mon += 0; dow += 0
	de = (lang == "de")
	L_MIN = de ? "Minute" : "Minute"; L_HOUR = de ? "Stunde" : "Hour"; L_DOM = de ? "Tag" : "Day"
	L_MON = de ? "Monat" : "Month"; L_DOW = de ? "Wochentag" : "Weekday"
	T_STEP = de ? "ungültige Schrittweite" : "invalid step"
	T_RANGE = de ? "ungültiger Bereich" : "invalid range"
	T_VALUE = de ? "ungültiger Wert" : "invalid value"
	T_ALLOWED = de ? "erlaubt" : "allowed"
	T_EMPTY = de ? "leer" : "empty"
	T_EXPECT = de ? "Erwartet: Minute Stunde Tag Monat Wochentag Skript [Argumente]" : "Expected: minute hour day month weekday script [arguments]"
	T_NOSCRIPT = de ? "Skriptname fehlt" : "Script name missing"
	T_BADSCRIPT = de ? "Ungültiger Skriptname" : "Invalid script name"
	T_CHARS = de ? "erlaubt: Buchstaben, Ziffern, . _ -" : "allowed: letters, digits, . _ -"
}

function num(s, names, lo, hi,    n, p) {
	s = tolower(s)
	if (s ~ /^[0-9]+$/) {
		n = s + 0
	} else if (names != "" && length(s) == 3 && (p = index(names, s)) > 0 && (p - 1) % 3 == 0) {
		n = (p - 1) / 3 + (names == MON ? 1 : 0)
	} else {
		return -1
	}
	return (n < lo || n > hi) ? -1 : n
}

# 1 if value v is selected by field spec f. Sets err on a syntax error.
function field(f, v, lo, hi, names, label,    parts, n, i, item, step, sl, d, a, b, ok) {
	ok = 0
	n = split(f, parts, ",")
	for (i = 1; i <= n; i++) {
		item = parts[i]
		step = 1
		sl = index(item, "/")
		if (sl) {
			step = substr(item, sl + 1)
			item = substr(item, 1, sl - 1)
			if (step !~ /^[0-9]+$/ || step + 0 < 1) { err = label ": " T_STEP " \"" parts[i] "\""; return 0 }
			step += 0
		}
		if (item == "*") {
			a = lo; b = hi
		} else if ((d = index(item, "-")) > 1) {
			a = num(substr(item, 1, d - 1), names, lo, hi)
			b = num(substr(item, d + 1), names, lo, hi)
			if (a < 0 || b < 0 || a > b) { err = label ": " T_RANGE " \"" parts[i] "\" (" T_ALLOWED " " lo "-" hi ")"; return 0 }
		} else {
			a = num(item, names, lo, hi)
			if (a < 0) { err = label ": " T_VALUE " \"" parts[i] "\" (" T_ALLOWED " " lo "-" hi ")"; return 0 }
			b = sl ? hi : a
		}
		if (v >= a && v <= b && (v - a) % step == 0) ok = 1
	}
	if (n < 1) err = label ": " T_EMPTY
	return ok
}

function valid_script(s) {
	return s ~ /^[A-Za-z0-9_-][A-Za-z0-9._-]*$/
}

{
	sub(/\r$/, "")
	enabled = 1
	if ($0 ~ /^#off[ \t]/) { enabled = 0; sub(/^#off[ \t]+/, "") }
	else if ($0 ~ /^[ \t]*#/) next
	if (NF == 0) next

	err = ""
	if ($1 == "@boot") {
		nsched = 1
	} else {
		nsched = 5
		if (NF < 6) {
			err = T_EXPECT
		} else {
			okm = field($1, m, 0, 59, "", L_MIN)
			if (err == "") okh = field($2, h, 0, 23, "", L_HOUR)
			if (err == "") okdom = field($3, dom, 1, 31, "", L_DOM)
			if (err == "") okmon = field($4, mon, 1, 12, MON, L_MON)
			if (err == "") {
				okdow = field($5, dow, 0, 7, DOW, L_DOW)
				if (err == "" && !okdow && dow == 0) okdow = field($5, 7, 0, 7, DOW, L_DOW)
			}
		}
	}
	script = $(nsched + 1)
	if (err == "" && script == "") err = T_NOSCRIPT
	if (err == "" && !valid_script(script)) err = T_BADSCRIPT " \"" script "\" (" T_CHARS ")"

	if (mode == "check") {
		if (err != "") print NR "\t" err
		next
	}
	if (err != "" || !enabled) next

	args = $0
	for (i = 1; i <= nsched + 1; i++) sub(/^[ \t]*[^ \t]+/, "", args)
	sub(/^[ \t]+/, "", args)

	if (mode == "boot") {
		if (nsched == 1) print script "\t" args
	} else if (mode == "match" && nsched == 5) {
		# like cron: if both day-of-month and weekday are restricted, either may match
		if ($3 !~ /^\*/ && $5 !~ /^\*/) day = (okdom || okdow)
		else day = (okdom && okdow)
		if (okm && okh && okmon && day) print script "\t" args
	}
}
