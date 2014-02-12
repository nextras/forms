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
			remote: {
				url: $(this).attr('data-typeahead-url'),
				wildcard: '__QUERY_PLACEHOLDER__'
			}
		});
	});
});
