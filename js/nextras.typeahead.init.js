/**
 * This file is part of the Nextras community extensions of Nette Framework
 *
 * @license    MIT
 * @link       https://github.com/nextras/forms
 * @author     Jan Skrasek
 */

jQuery(function($) {
	$('.typeahead').each(function() {
		$(this).typeahead({
			'remote': $(this).attr('data-typeahead-url') + '&q=%QUERY'
		});
	});
});
