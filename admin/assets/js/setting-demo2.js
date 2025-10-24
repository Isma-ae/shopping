"use strict";

// Setting Color

$(window).resize(function () {
	$(window).width();
});

getCheckmark();

$('.changeBodyBackgroundFullColor').on('click', function () {
	if ($(this).attr('data-color') == 'default') {
		$('body').removeAttr('data-background-full');
	} else {
		$('body').attr('data-background-full', $(this).attr('data-color'));
	}

	$(this).parent().find('.changeBodyBackgroundFullColor').removeClass("selected");
	$(this).addClass("selected");
	layoutsColors();
	getCheckmark();
});

$('.changeLogoHeaderColor').on('click', function () {
	if ($(this).attr('data-color') == 'default') {
		$('.logo-header').removeAttr('data-background-color');
	} else {
		$('.logo-header').attr('data-background-color', $(this).attr('data-color'));
	}

	$(this).parent().find('.changeLogoHeaderColor').removeClass("selected");
	$(this).addClass("selected");
	customCheckColor();
	layoutsColors();
	getCheckmark();
});

$('.changeTopBarColor').on('click', function () {
	if ($(this).attr('data-color') == 'default') {
		$('.main-header .navbar-header').removeAttr('data-background-color');
	} else {
		$('.main-header .navbar-header').attr('data-background-color', $(this).attr('data-color'));
	}

	$(this).parent().find('.changeTopBarColor').removeClass("selected");
	$(this).addClass("selected");
	layoutsColors();
	getCheckmark();
});

$('.changeSideBarColor').on('click', function () {
	if ($(this).attr('data-color') == 'default') {
		$('.sidebar').removeAttr('data-background-color');
	} else {
		$('.sidebar').attr('data-background-color', $(this).attr('data-color'));
	}

	$(this).parent().find('.changeSideBarColor').removeClass("selected");
	$(this).addClass("selected");
	layoutsColors();
	getCheckmark();
});

$('.changeBackgroundColor').on('click', function () {
	$('body').removeAttr('data-background-color');
	$('body').attr('data-background-color', $(this).attr('data-color'));
	$(this).parent().find('.changeBackgroundColor').removeClass("selected");
	$(this).addClass("selected");
	getCheckmark();
});

function customCheckColor() {
	var logoHeader = $('.logo-header').attr('data-background-color');
	if (logoHeader !== "white") {
		$('.logo-header .navbar-brand').attr('src', '../assets/img/kaiadmin/logo_light.svg');
	} else {
		$('.logo-header .navbar-brand').attr('src', '../assets/img/kaiadmin/logo_dark.svg');
	}
}


var toggle_customSidebar = false,
	custom_open = 0;

if (!toggle_customSidebar) {
	var toggle = $('.custom-template .custom-toggle');

	toggle.on('click', (function () {
		if (custom_open == 1) {
			$('.custom-template').removeClass('open');
			toggle.removeClass('toggled');
			custom_open = 0;
		} else {
			$('.custom-template').addClass('open');
			toggle.addClass('toggled');
			custom_open = 1;
		}
	}));
	toggle_customSidebar = true;
}

function getCheckmark() {
	var checkmark = `<i class="gg-check"></i>`;
	$('.btnSwitch').find('button').empty();
	$('.btnSwitch').find('button.selected').append(checkmark);
}

var imgf_change = function (ctrl, to, df) {
	if (df == null) df = "../images/2148875971.jpg";
	var input = $(ctrl)[0];
	if (input.files && input.files[0]) {
		var reader = new FileReader();
		reader.onload = function (e) {
			$(to).attr('src', e.target.result);
		};
		reader.readAsDataURL(input.files[0]);
		var name = input.files[0].name;
		var size = input.files[0].size;
		var type = input.files[0].type; // "image/jpeg" | image/png | image/gif | image/pjpeg
		var arr = name.split(".");
		var fType = (arr[arr.length - 1]).toLowerCase();;
		if (arr.length >= 2 && (fType == "jpg" || fType == "png" || fType == "gif" || fType == "JPEG" || fType == "JPG" || fType == "PNG" || fType == "GIF" || fType == "jpeg" || fType == "pjpeg")) {
			var reader = new FileReader();
			reader.onload = function (e) {
				$(to).attr('src', e.target.result);
			};
			reader.readAsDataURL(input.files[0]);
		} else {
			alert("รูปแบบไม่รองรับ รองรับเฉพาะ .jpg, .png, .gif");
			$(ctrl).val('');
			$(to).attr('src', df);
		}
	} else {
		alert("รูปแบบไม่รองรับ รองรับเฉพาะ .jpg, .png, .gif");
		$(ctrl).val('');
		$(to).attr('src', df);
	}
}

function errorImage(ctrl, df) {
	$(ctrl).attr("src", df);
}

var defaultCalendar = {
	sameDay: '[Today at] LT',
	nextDay: '[Tomorrow at] LT',
	nextWeek: 'dddd [at] LT',
	lastDay: '[Yesterday at] LT',
	lastWeek: '[Last] dddd [at] LT',
	sameElse: 'L'
};

function calendar(key, mom, now) {
	var output = this._calendar[key] || this._calendar['sameElse'];
	return isFunction(output) ? output.call(mom, now) : output;
}

var defaultLongDateFormat = {
	LTS: 'h:mm:ss A',
	LT: 'h:mm A',
	L: 'MM/DD/YYYY',
	LL: 'MMMM D, YYYY',
	LLL: 'MMMM D, YYYY h:mm A',
	LLLL: 'dddd, MMMM D, YYYY h:mm A'
};

function longDateFormat(key) {
	var format = this._longDateFormat[key],
		formatUpper = this._longDateFormat[key.toUpperCase()];

	if (format || !formatUpper) {
		return format;
	}

	this._longDateFormat[key] = formatUpper.replace(/MMMM|MM|DD|dddd/g, function (val) {
		return val.slice(1);
	});

	return this._longDateFormat[key];
}

var defaultInvalidDate = 'Invalid date';

function invalidDate() {
	return this._invalidDate;
}

var defaultOrdinal = '%d';
var defaultDayOfMonthOrdinalParse = /\d{1,2}/;

function ordinal(number) {
	return this._ordinal.replace('%d', number);
}

var defaultRelativeTime = {
	future: 'in %s',
	past: '%s ago',
	s: 'a few seconds',
	ss: '%d seconds',
	m: 'a minute',
	mm: '%d minutes',
	h: 'an hour',
	hh: '%d hours',
	d: 'a day',
	dd: '%d days',
	M: 'a month',
	MM: '%d months',
	y: 'a year',
	yy: '%d years'
};

function relativeTime(number, withoutSuffix, string, isFuture) {
	var output = this._relativeTime[string];
	return (isFunction(output)) ?
		output(number, withoutSuffix, string, isFuture) :
		output.replace(/%d/i, number);
}

function pastFuture(diff, output) {
	var format = this._relativeTime[diff > 0 ? 'future' : 'past'];
	return isFunction(format) ? format(output) : format.replace(/%s/i, output);
}