/**
 * This file is part of the Nextras community extensions of Nette Framework
 *
 * @license    MIT
 * @link       https://github.com/nextras/forms
 * @author     Jan Skrasek
 */

$.fn.datetimepicker.dates['cs'] = {
        days: ["Neděle", "Pondělí", "Úterý", "Středa", "Čtvrtek", "Pátek", "Sobota", "Neděle"],
        daysShort: ["Ned", "Pon", "Úte", "Stř", "Čtv", "Pát", "Sob", "Ned"],
        daysMin: ["Ne", "Po", "Út", "St", "Čt", "Pá", "So", "Ne"],
        months: ["Leden", "Únor", "Březen", "Duben", "Květen", "Červen", "Červenec", "Srpen", "Září", "Říjen", "Listopad", "Prosinec"],
        monthsShort: ["Led", "Úno", "Bře", "Dub", "Kvě", "Čvn", "Čvc", "Srp", "Zář", "Říj", "Lis", "Pro"],
        today: "Dnes"
    };

jQuery(function($) {
	$('input[type="date"]:not(.date)').addClass('date');
	$('input[type="datetime-local"]:not(.datetime-local)').addClass('datetime-local');

	$('input.date, input.datetime-local').each(function(i, el) {
		el = $(el);
		var isDate = el.is('.date') || el.is('[type="date"]');

		var value = el.val();
		el.get(0).type = 'text';
		el.val(value); // MS Edge workaround

		el.datetimepicker({
			startDate: el.attr('min'),
			endDate: el.attr('max'),
			language: el.data('language'),
			weekStart: 1,
			minView: isDate ? 'month' : 'hour',
			format: isDate ? 'd. m. yyyy' : 'd. m. yyyy - hh:ii', // for seconds support use 'd. m. yyyy - hh:ii:ss'
			autoclose: true
		});
		el.attr('value') && el.datetimepicker('setValue');
	});
});
