(function()
{

	//
	const DEFAULT_THROW = true;
	const DEFAULT_PARSE = false;
	const DEFAULT_RENDER = true;

	//
	css = { camel };

	//
	css.parse = (_string, _parse = DEFAULT_PARSE, _throw = DEFAULT_THROW) => {
		if(typeof _string !== 'string')
		{
			if(_throw)
			{
				throw new Error('Invalid _string argument');
			}
			
			return null;
		}

		return processString(_string, _parse);
	};

	//
	css.parse.url = (_string, _throw = DEFAULT_THROW) => {
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
		
		const object = parseFunctionalStyle(_string, false);

		if(! ('url' in object))
		{
			if(_throw)
			{
				throw new Error('No \'url()\' found in _string');
			}
			
			return null;
		}
		else if(typeof object.url === 'string')
		{
			return object.url;
		}
		else if(isArray(object.url, false)) for(var i = 0; i < object.url.length; ++i)
		{
			if(typeof object.url[i] === 'string')
			{
				return object.url[i];
			}
		}
		
		if(_throw)
		{
			throw new Error('No valid payload of the _string\'s \'url()\' found');
		}
		
		return null;
	};
	
	//	
	css.render = (_value, _throw = DEFAULT_THROW) => {
		if(typeof _value === 'string')
		{
			if(typeof (_value = processString(_value, false)) !== 'string')
			{
				return _value;
			}
			else if(_value.length === 0)
			{
				_value = '\'\'';
			}

			return _value;
		}
		
		if(typeof _value === 'string')
		{
			return _value;
		}
		else if(typeof _value === 'boolean')
		{
			return (_value ? 'auto' : 'none');
		}
		else if(typeof _value === 'undefined' || _value === null)
		{
			return '';
		}
		else if(isNumeric(_value))
		{
			return _value.toString();
		}
		else if(isArray(_value, true))
		{
			return renderArray(_value);
		}
		else if(isObject(_value, true))
		{
			return renderObject(_value);
		}
		else if(typeof _value.toString === 'function')
		{
			return _value.toString();
		}
		else if(_throw)
		{
			throw new Error('Invalid _value argument (unsupported type)');
		}
		
		return null;
	};
	
	//
	const processString = (_string, _parse = DEFAULT_PARSE) => {
		if(typeof _string !== 'string')
		{
			return _string;
		}
		else if((_string = _string.trim()).isEmpty)
		{
			return '';
		}
		else if(_string.length === 2 && _string[0] === _string[1])
		{
			const quote = String.quote;
			
			for(const q of quote)
			{
				if(_string[0] === q)
				{
					return '';
				}
			}
		}
		
		if(! _parse)
		{
			return _string;
		}
		
		const quotes = String.quote;
		const quoted = [];
		const result = [''];
		var quote = '';
		var open = false;

		for(var i = 0, j = 0, q = 0; i < _string.length; ++i)
		{
			if(_string[i] === '\\')
			{
				if(i < (_string.length - 1))
				{
					result[j] += _string[++i];
				}
				else
				{
					result[j] += '\\';
				}
			}
			else if(quote.length > 0)
			{
				if(_string[i] === quote)
				{
					quote = '';

					if(result[j].length > 0)
					{
						result[++j] = '';
					}
				}
				else
				{
					result[j] += _string[i];
				}
			}
			else if(open)
			{
				if(_string[i] === ')')
				{
					open = false;
				}
				else if(_string[i].isEmpty)
				{
					continue;
				}

				result[j] += _string[i];
			}
			else if(_string[i] === '(')
			{
				open = true;
				result[j] += '(';
			}
			else if(_string[i].isEmpty)
			{
				if(result[j].length > 0 || quoted.includes(j))
				{
					result[++j] = '';
				}
			}
			else
			{
				for(const q of quotes)
				{
					if(_string[i] === q)
					{
						quote = q;
						break;
					}
				}

				if(quote.length === 0)
				{
					result[j] += _string[i];
				}
				else
				{
					quoted[q++] = j;

					if(result[j].length > 0 && !quoted.includes(j))
					{
						result[++j] = '';
					}
				}
			}
		}

		for(var i = 0; i < result.length; ++i)
		{
			if(quoted.includes(i))
			{
				continue;
			}
			else if(typeof result[i] === 'string')
			{
				if(result[i].length === 0)
				{
					result.splice(i--, 1);
				}
				else if(result[i].includes('(') && result[i].includes(')'))
				{
					result[i] = parseFunctionalStyle(result[i], _parse);
				}
				else
				{
					result[i] = parseString(result[i], _parse);
				}
			}
			else for(var j = 0; j < result[i].length; ++j)
			{
				result[i][j] = parseString(result[i][j]);
			}
		}

		if(result.length === 0)
		{
			return '';
		}
		else if(result.length === 1)
		{
			return result[0];
		}
		
		return result;
	};

	const parseString = (_string, _parse = DEFAULT_PARSE) => {
		if(typeof _string !== 'string')
		{
			return _string;
		}
		else if((_string = _string.trim()).length === 0 || _string.isEmpty)
		{
			return (_parse ? null : '');
		}
		else if(_string.length === 2)
		{
			const quote = String.quote;
			
			for(const q of quote)
			{
				if(_string[0] === q && _string[1] === q)
				{
					return (_parse ? '' : _string);
				}
			}
		}
		else if(! _parse)
		{
			return _string;
		}

		const result = [''];
		const quoted = [];
		const quotes = String.quote;
		var quote = '';

		for(var i = 0, j = 0, q = 0; i < _string.length; ++i)
		{
			if(_string[i] === '\\')
			{
				if(i < (_string.length - 1))
				{
					result[j] += _string[++i];
				}
				else
				{
					result[j] += '\\';
				}
			}
			else if(quote.length > 0)
			{
				if(_string[i] === quote)
				{
					quote = '';

					if(result[j].length > 0)
					{
						result[++j] = '';
					}
				}
				else
				{
					result[j] += _string[i];
				}
			}
			else
			{
				for(const q of quotes)
				{
					if(_string[i] === q)
					{
						quote = q;
						break;
					}
				}

				if(quote.length === 0)
				{
					result[j] += _string[i];
				}
				else
				{
					quoted[q++] = j;

					if(result[j].length > 0 && !quoted.includes(j))
					{
						result[++j] = '';
					}

				}
			}
		}

		if(result[result.length - 1].length === 0 && !quoted.includes(result.length - 1))
		{
			result.pop();
		}

		for(var i = 0; i < result.length; ++i)
		{
			if(quoted.includes(i))
			{
				continue;
			}
			else if(result[i].length === 0)
			{
				result.splice(i--, 1);
			}
			else if(! isNaN(result[i]))
			{
				result[i] = Number(result[i]);
			}
			else switch(result[i].toLowerCase())
			{
				case 'auto':
					result[i] = true;
					break;
				case 'none':
					result[i] = false;
					break;
				default:
					result[i] = getValue(result[i], _parse, false, false);
					break;
			}
		}

		if(result.length === 1)
		{
			return result[0];
		}

		return result;
	};
	
	const parseFunctionalStyle = (_string, _parse = DEFAULT_PARSE) => {
		if(typeof _string !== 'string')
		{
			return _string;
		}
		else if((_string = _string.trim()).length === 0 || _string.isEmpty)
		{
			return '';
		}
		else if(! (_string.includes('(') && _string.includes(')')))
		{
			return parseString(_string, _parse);
		}

		const quotes = String.quote;
		var quoted = [];
		const object = Object.create(null);
		var quote = '';
		var open = false;
		var key = '';
		
		for(var i = 0, q = 0; i < _string.length; ++i)
		{
			if(_string[i] === '\\')
			{
				if(i < (_string.length - 1))
				{
					if(open)
					{
						result[key] += _string[++i];
					}
					else
					{
						key += _string[++i];
					}
				}
				else if(open)
				{
					object[key] += '\\';
				}
				else
				{
					key += '\\';
				}
			}
			else if(quote.length > 0)
			{
				if(_string[i] === quote)
				{
					quote = '';
				}
				else if(open)
				{
					object[key] += _string[i];
				}
				else
				{
					key += _string[i];
				}
			}
			else if(open)
			{
				if(_string[i] === ')')
				{
					open = false;
					key = '';
				}
				else if(_string[i].isEmpty)
				{
					if(object[key].length > 0 && !object[key][object[key].length - 1].isEmpty)
					{
						object[key] += ' ';
					}
				}
				else
				{
					object[key] += _string[i];
				}
			}
			else if(_string[i] === '(')
			{
				if(key.length > 0)
				{
					if(! (key in object))
					{
						object[key] = [];
					}
					
					open = true;
				}
				else
				{
					key += '(';
				}
			}
			else
			{
				for(const q of quotes)
				{
					if(_string[i] === q)
					{
						quote = q;
						break;
					}
				}

				if(quote.length === 0)
				{
					key += _string[i];
				}
				else
				{
					quoted[q++] = [ key, object[key].length - 1 ];
					var createNew = true;

					if(object[key].length > 0)
					{
						for(const q of quoted)
						{
							if(q[key].length > 0)
							{
								createNew = false;
								break;
							}
						}
					}

					if(createNew)
					{
						object[key].push('');
					}
				}
			}
		}

		const keys = Object.keys(object, false, false);

		if(keys.length === 0)
		{
			return parseString(_string, _parse);
		}

		const result = Object.create(null);
		const array = [];
		var item, quote = '';
		
		for(var i = 0, qarr = 0, q = 0; i < keys.length; ++i)
		{
			if((item = object[keys[i]].trim()).length === 0)
			{
				continue;
			}
			else
			{
				quoted = new Set();
				array.length = 1;
				array[0] = '';
			}
			
			for(var j = 0, k = 0; j < item.length; ++j)
			{
				if(quote.length > 0)
				{
					if(item[j] === quote)
					{
						array[++k] = '';
						quote = '';
					}
					else
					{
						array[k] += item[j];
					}
				}
				else if(item[j] === ',')
				{
					if(array[k].length > 0)
					{
						array[++k] = '';
					}
				}
				else if(item[j].isEmpty)
				{
					if(! array[k].isEmpty)
					{
						array[k] += ' ';
					}
				}
				else
				{
					for(const q of quotes)
					{
						if(item[j] === q)
						{
							quote = q;
							break;
						}
					}
					
					if(quote.length === 0)
					{
						array[k] += item[j];
					}
					else
					{
						quoted[q++] = k;

						if(array[k].length > 0 && !quoted.includes(k))
						{
							array[++k] = '';
						}
					}
				}
			}

			for(var k = 0; k < array.length; ++k)
			{
				if(quoted.has(k))
				{
					continue;
				}
				else
				{
					if((array[k] = parseString(array[k], _parse)) === null)
					{
						array.splice(k--, 1);
					}
				}
				/*else if((array[k] = parseString(array[k], _parse)).length === 0)
				{
					array.splice(k--, 1);
				}*/
			}
			
			if(array.length === 0)
			{
				result[keys[i]] = '';
			}
			else if(array.length === 1)
			{
				result[keys[i]] = array[0];
			}
			else
			{
				result[keys[i]] = [ ... array ];
			}
		}

		return result;
	};

	const renderFunctionalStyle = (_object) => {
		if(! isObject(_object, true))
		{
			return null;
		}
		
		const keys = Object.keys(_object, false, false);
		
		if(keys.length === 0)
		{
			return '';
		}
		
		var result = '';
		var item;

		for(var i = 0; i < keys.length; ++i)
		{
			result += keys[i] + '(';

			if((item = css.render(_object[keys[i]])).length === 0)
			{
				item = item.quote('\'');
			}

			result += item + ') ';
		}

		if(result.length > 0)
		{
			result = result.slice(0, -1);
		}
		
		return result;
	};
	
	const renderObject = renderFunctionalStyle;

	const renderArray = (_array) => {
		if(! isArray(_array, true))
		{
			return null;
		}
		else if(_array.length === 0)
		{
			return '';
		}
		
		var result = '';
		var item;
		
		for(var i = 0; i < _array.length; ++i)
		{
			if((item = css.render(_array[i])).length === 0)
			{
				item = item.quote('\'');
			}

			result += item + ' ';
		}
		
		if(result.length > 0)
		{
			result = result.slice(0, -1);
		}
		
		return result;
	};

	//
	
})();

