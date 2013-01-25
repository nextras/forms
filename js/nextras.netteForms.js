/**
 * This file is part of the Nextras community extensions of Nette Framework
 *
 * @license    MIT
 * @link       https://github.com/nextras
 * @author     Jan Skrasek
 */

Nette.getValuePrototype = Nette.getValue;
Nette.getValue = function(elem) {
	if (!elem || !elem.nodeName || !(elem.nodeName.toLowerCase() == 'input' && elem.name.match(/\[\]$/))) {
		return Nette.getValuePrototype(elem);
	} else {
		var value = [];
		for (var i = 0; i < elem.form.elements.length; i++) {
			var e = elem.form.elements[i];
			if (e.nodeName.toLowerCase() == 'input' && e.name == elem.name && e.checked) {
				value.push(e.value);
			}
		}

		return value.length == 0 ? null : value;
	}
}
