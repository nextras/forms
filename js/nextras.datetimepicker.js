/**
 * This file is part of the Nextras community extensions of Nette Framework
 *
 * Copyright (c) 2012 Jan Skrasek (http://jan.skrasek.com)
 *
 * @license    MIT
 * @link       https://github.com/nextras
 */

$.datetimeLocalToDate = function (str) {
	var regexp = /(\d\d\d\d)(-)?(\d\d)(-)?(\d\d)(T)?(\d\d)(:)?(\d\d)(:)?(\d\d)(\.\d+)?/;
	d = str.match(new RegExp(regexp));
	if (!d)
		return null;

	date = new Date(parseInt(d[1], 10), parseInt(d[3], 10) - 1, parseInt(d[5], 10), parseInt(d[7], 10), parseInt(d[9], 10), parseInt(d[11], 10));
	return date;
}

$.dateTimePicker = function() {
	var el = $(this);
	var value = el.val();
	var date = value ? $.datetimeLocalToDate(value) : null;
	var dateFormat = 'd. m. yy'; // standard is $.datepicker.W3C;

	var minDate = el.attr("min") || null;
	if (minDate) minDate = $.datetimeLocalToDate(minDate);
	var maxDate = el.attr("max") || null;
	if (maxDate) maxDate = $.datetimeLocalToDate(maxDate);

	el.get(0).type = "text"; // changing via jQuery is prohibited, because of IE

	if (date) {
		var time = '';
		time += (date.getHours() < 10 ? '0' : '') + date.getHours();
		time += ':' + (date.getMinutes() < 10 ? '0' : '') + date.getMinutes();
		el.val($.datepicker.formatDate(dateFormat, date) + ' ' + time);
	}

	el.datetimepicker({
		dateFormat: dateFormat,
		timeFormat: 'hh:mm',
		minDate: minDate,
		maxDate: maxDate,
	});
};
