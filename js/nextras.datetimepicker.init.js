/**
 * This file is part of the Nextras community extensions of Nette Framework
 *
 * @license    MIT
 * @link       https://github.com/nextras/forms
 * @author     Jan Skrasek
 */

jQuery(function($) {
	$('input.date, input.datetime-local').each(function(i, el) {
		el = $(el);
		var isDate = el.is('.date') || el.is('[type="date"]');
		el.first().type = 'text';
		el.datetimepicker({
			startDate: el.attr('min'),
			endDate: el.attr('max'),
			weekStart: 1,
			minView: isDate ? 'month' : 'hour',
			format: isDate ? 'd. m. yyyy' : 'd. m. yyyy - hh:ii', // for seconds support use 'd. m. yyyy - hh:ii:ss'
			autoclose: true
		});
		el.attr('value') && el.datetimepicker('setValue');
	});
});
