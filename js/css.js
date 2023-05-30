(function()
{

	//
	const DEFAULT_THROW = true;
	const DEFAULT_PARSE = true;

	/** Extended CSS value handling methods, etc.
	 * @module css
	 */

	//
	css = { camel };

	//
	Object.defineProperty(HTMLElement.prototype, 'parseStyle', { value: function(... _args)
	{
		var PARSE = DEFAULT_PARSE;

		for(var i = 0; i < _args.length; ++i)
		{
			if(isArray(_args[i], true))
			{
				PARSE = _args.splice(i--, 1)[0];
			}
			else if(typeof _args[i] === 'boolean')
			{
				PARSE = _args.splice(i--, 1)[0];
			}
			else if(isInt(_args[i]))
			{
				PARSE = _args.splice(i--, 1)[0];
			}
		}

		const result = this.getStyle(... _args);

		if(typeof result === 'undefined' || result === null)
		{
			return result;
		}
		else if(! isObject(result))
		{
			return css.parse(result, PARSE);
		}
		else for(const idx in result)
		{
			result[idx] = css.parse(result[idx], PARSE);
		}

		return result;
	}});

	Object.defineProperty(HTMLElement.prototype, 'renderStyle', { value: function(_key, _value, _options, _callback, _throw)
	{
throw new Error('TODO');//w/ animation etc. @ setStyle()
	}});

	Object.defineProperty(CSSStyleDeclaration.prototype, 'parse', { value: function(... _args)
	{
		return this.getPropertyValue(true, ... _args);
	}});

	Object.defineProperty(CSSStyleDeclaration.prototype, 'render', { value: function(_key, _value, _parse = true)
	{
		return this.style.setPropertyValue(_key, _value, _parse);
	}});
	
	/** Parses CSS values (if _parse !== false).
	 * @param {string|*} _string - the RAW CSS value. If not a String, either _throw or return.
	 * @param {boolean|array|integer|string} [DEFAULT_PARSE] _parse
	 * @param {boolean} [DEFAULT_THROW] _throw
	 * @returns {string|number|boolean|array|object|*}
	 */
	css.parse = (_string, _parse = DEFAULT_PARSE, _throw = DEFAULT_THROW) => {
		if(typeof _string !== 'string')
		{
			if(_throw)
			{
				throw new Error('Invalid _string argument');
			}
			
			return _string;
		}
		else if((_string = _string.trim()).length === 0)
		{
			return null;
		}
		else if(isInt(_parse) && _parse <= 0)
		{
			_parse = false;
		}

		if(!_parse)
		{
			return _string;
		}
		else if(_string.length === 2 && _string[0] === _string[1])
		{
			const quotes = String.quote;
			
			for(const q of quotes)
			{
				if(_string[0] === q)
				{
					return '';
				}
			}
		}

		const quotes = String.quote;
		const quoted = new Set();
		const result = [''];
		var quote = '';
		var open = 0;
		const func = new Set();
		
		for(var i = 0, j = 0; i < _string.length; ++i)
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
					result[++j] = '';
				}
				else
				{
					result[j] += _string[i];
				}
			}
			else if(open > 0)
			{
				result[j] += _string[i];
				
				if(_string[i] === ')')
				{
					if(--open <= 0)
					{
						open = 0;
						func.add(j);
						result[++j] = '';
					}
				}
				else if(_string[i] === '(')
				{
					++open;
				}
			}
			else if(_string[i] === '(')
			{
				open = 1;
				result[j] += '(';
			}
			else if(_string[i].isEmpty || _string[i] === ',')
			{
				if(result[j].length > 0)
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
					if(result[j].length > 0 || quoted.has(j))
					{
						result[++j] = '';
					}
					
					quoted.add(j);
				}
			}
		}

		for(var i = 0, j = 0; i < result.length; ++i, ++j)
		{
			if(quoted.has(j))
			{
				continue;
			}
			else if((result[i] = result[i].trim()).length === 0)
			{
				result.splice(i--, 1);
			}
			else if(func.has(j))
			{
				if(isInt(_parse) && _parse <= 1)
				{
					_parse = false;
				}

				result[i] = css.parse.functional(result[i], _parse, _throw);
			}
			else if(!_parse)
			{
				continue;
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

		if(result.length === 0)
		{
			return null;
		}
		else if(result.length === 1)
		{
			return result[0];
		}
		
		return result;
	};

	/** Parses CSS functional style values
	 * @param {string|*} _string - the whole value string, best w/ 'func()' key name, too.
	 * @param {boolean|array|integer|string} [DEFAULT_PARSE] _parse
	 * @param {boolean} [DEFAULT_THROW] _throw
	 * @returns {object|array|string|*}
	 */
	css.parse.functional = (_string, _parse = DEFAULT_PARSE, _throw = DEFAULT_THROW) => {
		//
		if(typeof _string !== 'string')
		{
			if(_throw)
			{
				throw new Error('Invalid _string argument');
			}
			
			return _string;
		}
		else if((_string = _string.trim()).length === 0)
		{
			return null;
		}
		else if(isInt(_parse) && _parse <= 0)
		{
			_parse = false;
		}

		//
		var array = [];
		const sub = Object.create(null);
		const opened = [];
		const quotes = String.quote;
		const quoted = new Map();
		var open = 0;
		var quote = '';
		var key = '';

		//
		for(var i = 0, j = 0, k = 0; i < _string.length; ++i)
		{
			if(_string[i] === '\\')
			{
				if(i < (_string.length - 1))
				{
					if(open)
					{
						array[j][k] += _string[++i];
					}
					else
					{
						key += _string[++i];
					}
				}
				else if(open)
				{
					array[j][j] += '\\';
				}
				else
				{
					key += '\\';
				}
			}
			else if(open)
			{
				if(quote.length > 0)
				{
					if(_string[i] === quote)
					{
						quote = '';
					}
					else
					{
						array[j][k] += _string[i];
					}
				}
				else if(_string[i] === '(')
				{
					++open;
					array[j][k] += '(';
				}
				else if(_string[i] === ')')
				{
					if(--open <= 0)
					{
						open = 0;
					}
					else
					{
						array[j][k] += ')';
					}
				}
				else if(open > 1)
				{
					array[j][k] += _string[i];
				}
				else if(_string[i] === ',')
				{
					array[++j] = [''];
					k = 0;
				}
				else if(_string[i].isEmpty)
				{
					if(array[j][k].length > 0 || (quoted.has(j) && quoted.get(j).has(k)))
					{
						array[j][++k] = '';
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
						array[j][k] += _string[i];
					}
					else
					{
						if(array[j][k].length > 0 || (quoted.has(j) && quoted.get(j).has(k)))
						{
							array[j][++k] = '';
						}

						if(! quoted.has(j))
						{
							quoted.set(j, new Set());
						}

						quoted.get(j).add(k);
					}
				}
			}
			else if(_string[i] === '(')
			{
				open = 1;
				array[j] = [''];
			}
			else if(!_string[i].isEmpty)
			{
				key += _string[i];
			}
		}

		//
		if(isInt(_parse))
		{
			if((--_parse) <= 0)
			{
				_parse = false;
			}
		}

		//
		var q;
		
		for(var i = 0, j = 0; i < array.length; ++i, ++j)
		{
			q = (quoted.has(j) ? quoted.get(j) : null);
			
			for(var k = 0, l = 0; k < array[i].length; ++k, ++l)
			{
				if(q === null || !q.has(l))
				{
					if(array[i][k].length === 0)
					{
						array[i].splice(k--, 1);
					}
					else if(_parse)
					{
						array[i][k] = css.parse(array[i][k], _parse, _throw);
					}
				}
			}
			
			if(array[i].length === 0 && q === null)
			{
				array.splice(i--, 1);
			}
			else if(array[i].length === 1)
			{
				array[i] = array[i][0];
			}
		}
		
		//
		if(array.length === 0)
		{
			array = true;
		}
		else if(array.length === 1)
		{
			array = array[0];
		}

		//
		var result;
		
		if(key.length > 0)
		{
			result = Object.create(null);
			result[key] = array;
		}
		else
		{
			result = array;
		}

		//
		return result;
	};
	
	/** Better extraction of 'url()' CSS values.
	 * @param {string|object} _string
	 * @param {boolean} [DEFAULT_THROW] _throw
	 * @returns {string|null}
	 */
	css.parse.url = (_string, _throw = DEFAULT_THROW) => {
		if(typeof _string !== 'string')
		{
			if(isObject(_string) && typeof _string.url === 'string')
			{
				return _string.url;
			}
			else if(_throw)
			{
				throw new Error('Invalid _string argument');
			}
			
			return null;
		}
		else if((_string = _string.trim()).length === 0)
		{
			return null;
		}
		
		const obj = css.parse.functional(_string, false, _throw);
		
		if(typeof obj.url === 'string')
		{
			return obj.url;
		}
		else if(isArray(obj.url, false))
		{
			return obj.url.join(' ');
		}
		
		return null;
	};
	
	/** Render items to CSS value strings
	 * @param {*} _item - Either supported types or any with .toString() implementation
	 * @param {boolean} [DEFAULT_THROW] _throw
	 * @returns {string}
	 */
	css.render = (_item, _throw = DEFAULT_THROW) => {
		var result;
		
		if(typeof _item === 'undefined' || _item === null)
		{
			result = '';
		}
		else if(typeof _item === 'string')
		{
			result = _item.quote('\'');
		}
		else if(typeof _item === 'boolean')
		{
			result = (_item ? 'auto' : 'none');
		}
		else if(isNumeric(_item, null))
		{
			result = _item.toString();
		}
		else if(isArray(_item, true))
		{
			result = fromArray(_item, _throw);
		}
		else if(isObject(_item, true))
		{
			result = fromObject(_item, _throw);
		}
		else if(Object.isExtensible(_item) && typeof _item.toString === 'function')
		{
			result = _item.toString();
		}
		else if(_throw)
		{
			throw new Error('Unsupported _item type (' + typeOf(_item) + ')');
		}
		else
		{
			return '';
		}
		
		return result;
	};

	/** Renders CSS functional style objects to their String form.
	 * @param {object} _object - Arrays won't get rendered here (yet)
	 * @param {boolean} [DEFAULT_THROW] _throw
	 * @returns {string}
	 */
	css.render.functional = (_object, _throw = DEFAULT_THROW) => {
		if(! isObject(_object, true, false))
		{
			if(typeof _object === 'string')
			{
				return _object;
			}
			else if(_throw)
			{
				throw new Error('Invalid _object argument');
			}
			
			return _object;
		}
		
		const keys = Object.keys(_object);
		
		if(keys.length === 0)
		{
			return '';
		}
		
		var result = '';
		
		for(var i = 0; i < keys.length; ++i)
		{
			result += keys[i] + '(';
			result += css.render(_object[keys[i]], _throw);
			result += ') ';
		}
		
		if(result.length > 0)
		{
			result = result.slice(0, -1);
		}
		
		return result;
	};

	//
	const fromArray = (_array, _throw = DEFAULT_THROW) => {
		if(! isArray(_array, true))
		{
			if(_throw)
			{
				throw new Error('Invalid _array argument');
			}
			
			return _array;
		}
		else if(_array.length === 0)
		{
			return '';
		}
		
		var result = '';
		
		for(var i = 0; i < _array.length; ++i)
		{
			result += css.render(_array[i], _throw) + ' ';
		}
		
		if(result.length > 0)
		{
			result = result.slice(0, -1);
		}
		
		return result;
	};
	
	const fromObject = (_object, _throw = DEFAULT_THROW) => {
		return css.render.functional(_object, _throw);
	};
	
	//
	
})();
