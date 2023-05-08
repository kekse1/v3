(function()
{

	//
	intl = (_lang = navigator.language, _type, _options) => {
		if(! isObject(_options))
		{
			_options = {};
		}
		
		if(! isString(_type))
		{
			throw new Error('Invalid _type argument');
		}
		else
		{
			_type = _type.toLowerCase();
		}

		var type = null;
		const t = intl.type;

		for(const tt of t)
		{
			if(tt.toLowerCase() === _type)
			{
				type = tt;
				break;
			}
		}

		if(type === null)
		{
			throw new Error('Invalid _type argument');
		}
		else if(isArray(_lang, false))
		{
			for(var i = 0; i < _lang.length; ++i)
			{
				if(isString(_lang[i], false))
				{
					_lang = _lang[i];
					break;
				}
			}
		}
		
		if(! isString(_lang, false))
		{
			_lang = navigator.language;
		}

		return new Intl[type](_lang, _options);
	};

	//
	intl.DateTimeFormat = (_value, _lang, _options) => {
		return intl(_lang, 'DateTimeFormat', _options).format(_value);
	};

	intl.NumberFormat = (_value, _lang, _options) => {
		return intl(_lang, 'NumberFormat', _options).format(_value);
	};

	intl.Collator = (_value, _lang, _options) => {
		return intl(_lang, 'Collator', _options).format(_value);
	};

	intl.ListFormat = (_value, _lang, _options) => {
		return intl(_lang, 'ListFormat', _options).format(_value);
	};

	intl.PluralRules = (_value, _lang, _options) => {
		return intl(_lang, 'PluralRules', _options).format(_value);
	};

	intl.RelativeTimeFormat = (_value, _lang, _options) => {
		if(! isString(_options, false))
		{
			throw new Error('Invalid _options argument');
		}
		else
		{
			_options = _options.toLowerCase();
		}

		return intl(_lang, 'RelativeTimeFormat').format(_value, _options);
	};

	//
	Object.defineProperty(intl, 'type', { get: function()
	{
		return [
			'Collator',
			'DateTimeFormat',
			'ListFormat',
			'NumberFormat',
			'PluralRules',
			'RelativeTimeFormat'
		];
	}});

	//
	intl.Currency = intl.currency = (_value, _currency, _lang) => {
		if(! isNumber(_value))
		{
			throw new Error('Invalid _value argument');
		}
		else if(! isString(_currency, false))
		{
			throw new Error('Invalid _currency argument');
		}
		else if(! isString(_lang, false))
		{
			_lang = navigator.language;
		}

		return new Intl.NumberFormat(_lang, { style: 'currency', currency: _currency }).format(_value);
	};

	//

})();

