Nextras Forms
=============


====================

**This repository is deprecated.<br>
Current releases work for Nette 2.2.<br>
For Nette 3.0+ see new repositories [nextras/form-components](https://github.com/nextras/form-components) and [nextras/forms-rendering](https://github.com/nextras/forms-rendering).**

====================


[![Build Status](https://travis-ci.org/nextras/forms.svg?branch=master)](https://travis-ci.org/nextras/forms)
[![Downloads this Month](https://img.shields.io/packagist/dm/nextras/forms.svg?style=flat)](https://packagist.org/packages/nextras/forms)
[![Stable version](http://img.shields.io/packagist/v/nextras/forms.svg?style=flat)](https://packagist.org/packages/nextras/forms)
[![Code coverage](https://img.shields.io/coveralls/nextras/forms.svg?style=flat)](https://coveralls.io/r/nextras/forms)

List of components:
- **DatePicker** - date picker, represented by DateTime object
- **DateTimePicker** - date & time picker, represented by DateTime object
- **Tyheahead** - the best autocomplete for your forms
- **BS3InputMacros** - input macros for Bootstrap 3 (adds some css classes)
- **Bs3FormRenderer** - renders forms quicky in Bootstrap 3 design
- **Bs4FormRenderer** - renders forms quicky in Bootstrap 4 design
- **ComponentControl** - form's control which contains Component interface and my have own template, receive signals & render snippets;

### Installation

The best way to install is using [Composer](http://getcomposer.org/):

```sh
$ composer require nextras/forms
```

For Date(Time)Picker we recommend use [DateTime Picker](http://www.malot.fr/bootstrap-datetimepicker/) for Bootstrap.
See JS init script.

### Documentation

Add in your `config.neon`:

```php
extensions:
    nextras.form: Nextras\Forms\Bridges\NetteDI\FormsExtension
```
