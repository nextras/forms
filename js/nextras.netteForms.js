Nette.getListValue = function(form, elemName) {
	var value = [];
	for (var i = 0; i < form.elements.length; i++) {
		var elem = form.elements[i];
		if (elem.nodeName.toLowerCase() == 'input' && elem.name == elemName && elem.checked) {
			value.push(elem.value);
		}
	}

	return value;
}

Nette.validators.listFilled = function(elem, arg, val) {
	var value = Nette.getListValue(elem.form, elem.name);
	return value.length > 0;
};
