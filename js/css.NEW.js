(function()
{

	//
	const DEFAULT_THROW = true;
	const DEFAULT_PARSE = false;

	//
	css = { camel };

	//
	css.url = (_string, _throw = DEFAULT_THROW) => {
		if(! isString(_string, false))
		{
			if(_throw)
			{
				throw new Error('Invalid _string argument');
			}

			return null;//_string;
		}
		else if(! _string.toLowerCase().includes('url'))
		{
			if(_throw)
			{
				throw new Error('Contains no url()');
			}

			return null;//_string;
		}
		else
		{
			_string = _string.trim();
		}

		const quote = String.quote;
		var result = '';
		var open = false;
		var c;

		for(var i = 0; i < _string.length; ++i)
		{
			if(_string[i] === '\\')
			{
				if(open)
				{
					if(i < (_string.length - 1))
					{
						result += _string[++i];
					}
					else
					{
						result += '\\';
					}
				}
				else
				{
					++i;
				}
			}
			else if(open)
			{
				if(_string[i] === ')')
				{
					open = false;
					break;
				}
				else
				{
					c = false;

					for(const q of quote)
					{
						if(_string[i] === q)
						{
							c = true;
							break;
						}
					}

					if(c)
					{
						continue;
					}
				}

				result += _string[i];
			}
			else if(_string.at(i, 'url(', false))
			{
				open = true;
				i += 3;
			}
		}

		if(open && _throw)
		{
			throw new Error('Invalid CSS (not closed)');
		}

		return result;
	};

	//
	css.parse = (_string, _parse = DEFAULT_PARSE) => {
		if(typeof _string !== 'string')
		{
			return _string;
		}
		else if((_string = _string.trim()).length === 0 || _string.isEmpty)
		{
			return '';
		}
		else if(_string.length === 2)
		{
			const quote = String.quote;

			for(const q of quote)
			{
				if(_string[0] === q && _string[1] === q)
				{
					return '';
				}
			}
		}
throw new Error('TODO');//also str..unit(.., ['deg']), etc.. soon!
	};

	css.render = (_value) => {
		if(typeof _value === 'string')
		{
			if((_value = _value.trim()).hasEmpty)
			{
				_value = _value.quote('\'');
			}

			return _value;
		}

		var result;

		if(typeof _value === 'undefined')
		{
			result = '';
		}
		else if(_value === null)
		{
			result = 'auto';
		}
		else if(typeof _value === 'boolean')
		{
			result = (_value ? 'auto' : 'none');
		}

throw new Error('TODO');
	};
	
})();

