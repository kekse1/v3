(function()
{

	//
	const DEFAULT_THROW = true;
	const DEFAULT_REPEAT = null;
	const DEFAULT_CUT = false;
	const DEFAULT_PRINT_HTML = true;
	const DEFAULT_SPLIT_REST = false;
	const DEFAULT_COUNT_ONCE = false;
	const DEFAULT_INT = false;
	const DEFAULT_ENTITIES = 'json/entities.json';
	const DEFAULT_ENTITIES_URL = 'https://html.spec.whatwg.org/entities.json';
	const DEFAULT_HTML_ALL = true;
	const DEFAULT_HTML_HEX = true;

	//
	Object.defineProperty(String.prototype, 'print', { value: function(_object, ... _args)
	{
		var HTML = DEFAULT_PRINT_HTML;

		for(var i = 0; i < _args.length; ++i)
		{
			if(typeof _args[i] === 'boolean')
			{
				HTML = _args.splice(i--, 1)[0];
			}
		}

		if(this.length === 0)
		{
			return '';
		}
		else if(! isObject(_object))
		{
			_object = null;
		}

		const lookUp = (_key) => {
			if(_object !== null && (_key in _object))
			{
				if(isNumeric(_object[_key]))
				{
					if(HTML)
					{
						return _object[_key].toText();
					}

					return _object[_key].toLocaleString();
				}
				
				return _object[_key].toString();
			}

			return '%{' + _key + '}';
		};

		var result = '';
		var open = false;
		var data;

		for(var i = 0; i < this.length; ++i)
		{
			if(this[i] === '\\')
			{
				result += this[++i];
			}
			else if(this[i] === '%')
			{
				if(i < (this.length - 3) && this[i + 1] === '/')
				{
					data = '';

					for(var j = (i += 2); j < this.length; ++j, ++i)
					{
						if(this[j] === '\\' && j < (this.length - 1))
						{
							data += this[++j];
							++i;
						}
						else if(this[j] === '/')
						{
							break;
						}
						else
						{
							data += this[j];
						}
					}

					result += ' <!--[ ' + data + ' ]--> ';
				}
				else if(i < (this.length - 3) && this[i + 1] === '{')
				{
					data = '';

					for(var j = (i += 2); j < this.length; ++j, ++i)
					{
						if(this[j] === '\\' && j < (this.length - 1))
						{
							data += this[++j];
							++i;
						}
						else if(this[j] === '}')
						{
							break;
						}
						else
						{
							data += this[j];
						}
					}

					result += lookUp(data);
				}
				else
				{
					result += '%';
				}
			}
			else
			{
				result += this[i];
			}
		}

		return result;
	}});

	Object.defineProperty(String, 'print', { value: function(_string, _object)
	{
		if(typeof _string !== 'string')
		{
			throw new Error('Invalid _string argument');
		}
		else if(_string.length === 0)
		{
			return '';
		}

		return _string.print(_object);
	}});

	//
	var entities = null;
	
	const baseEntities = () => {
		entities = {
			'&amp;': {
				codepoints: [38],
				characters: '\u0026'
			},
			'&lt;': {
				codepoints: [60],
				characters: '\u003C'
			},
			'&gt;': {
				codepoints: [62],
				characters: '\u003E'
			},
			'&quot;': {
				codepoints: [34],
				characters: '\u0022'
			},
			'&apos;': {
				codepoints: [39],
				characters: '\u0027'
			}
		};

		console.error('No real String.entities loaded');
	};
	
	if(DEFAULT_ENTITIES)
	{
		require(DEFAULT_ENTITIES, (_e) => {
			if(isObject(_e.module))
			{
				entities = _e.module;
			}
			else if(DEFAULT_ENTITIES_URL)
			{
				require(DEFAULT_ENTITIES_URL, (_e) => {
					if(isObject(_e.module))
					{
						entities = _e.module;
						console.warn('String.entities catched by URL..');
					}
					else
					{
						baseEntities();
					}
				}, null, false, true);
			}
			else
			{
				baseEntities();
			}
		});
	}
	else if(DEFAULT_ENTITIES_URL)
	{
		require(DEFAULT_ENTITIES_URL, (_e) => {
			if(isObject(_e.module))
			{
				entities = _e.module;
				console.warn('String.entities catched by URL..');
			}
			else
			{
				baseEntities();
			}
		});
	}
	else
	{
		baseEntities();
	}

	//
	Object.defineProperty(String, 'entities', { get: function()
	{
		return { ... entities };
	}});
	
	const htmlByNumber = (_code) => {
		//
		const orig = _code;
		
		if(_code[0] === '#')
		{
			_code = _code.substr(1);
		}

		//
		var radix;
		
		if(_code[0].toLowerCase() === 'x')
		{
			radix = 16;
			_code = _code.substr(1);
		}
		else
		{
			radix = 10;
		}
		
		//
		var result;
		
		if(isNaN(result = parseInt(_code, radix)))
		{
			return null;
		}
		
		return String.fromCodePoint(result);
	};
	
	const htmlByName = (_code) => {
		const query = '&' + _code + ';';
		
		if(query in entities)
		{
			return entities[query].characters;
		}
		
		return null;
	};
	
	Object.defineProperty(String, 'html', { value: function(_code)
	{
		if(typeof _code !== 'string')
		{
			throw new Error('Invalid _code argument');
		}
		
		const orig = _code;
		
		if(_code[0] === '&')
		{
			_code = _code.substr(1);
		}
		
		if(_code[_code.length - 1] === ';')
		{
			_code = _code.slice(0, -1);
		}

		var result;
		
		if(_code.length === 0)
		{
			result = orig;
		}
		else if(_code[0] === '#')
		{
			if((result = htmlByNumber(_code)) === null)
			{
				result = orig;
			}
		}
		else if((result = htmlByName(_code)) === null)
		{
			result = orig;
		}
		
		return result;
	}});

	//
	Object.defineProperty(String.prototype, 'textLength', { get: function()
	{
		var result = 0;
		var open = '';
		var sub = 0;

		for(var i = 0; i < this.length; ++i)
		{
			if(this[i] === '\\')
			{
				if(i < (this.length - 1))
				{
					++i;
				}

				++result;
			}
			else if(open.length > 0)
			{
				++sub;

				if(this[i] === open)
				{
					open = '';
					sub = 0;
				}
			}
			else if(this[i] === '<')
			{
				open = '>';
				sub = 1;
			}
			else if(this[i] === '&')
			{
				open = ';';
				++result;
				sub = 0;
			}
			else
			{
				++result;
			}
		}

		if(open.length > 0)
		{
			result += sub;
		}

		return result;
	}});

	//
	// text w/o <> original &*;
	//
	Object.defineProperty(String.prototype, 'less', { get: function()
	{
		if(this.length === 0)
		{
			return '';
		}
		
		var result = '';
		var open = false;
		var sub;
		
		for(var i = 0; i < this.length; ++i)
		{
			if(this[i] === '\\')
			{
				if(i < (this.length - 1))
				{
					if(open)
					{
						sub += this[++i];
					}
					else
					{
						result += this[++i];
					}
				}
				else if(open)
				{
					sub += '\\';
				}
				else
				{
					result += '\\';
				}
			}
			else if(open)
			{
				sub += this[i];
				
				if(this[i] === '>')
				{
					open = false;
					sub = '';
				}
			}
			else if(this[i] === '<')
			{
				sub = '<';
				open = true;
			}
			else
			{
				result += this[i];
			}
		}
		
		if(open)
		{
			result += sub;
		}
		
		return result;
	}});
	
	//
	// text w/o <> replaced &*;
	//
	Object.defineProperty(String.prototype, 'text', { get: function()
	{
		if(this.length === 0)
		{
			return '';
		}
		
		var result = '';
		var open = '';
		var sub;
		
		for(var i = 0; i < this.length; ++i)
		{
			if(this[i] === '\\')
			{
				if(i < (this.length - 1))
				{
					if(open)
					{
						sub += this[++i];
					}
					else
					{
						result += this[++i];
					}
				}
				else if(open)
				{
					sub += '\\';
				}
				else
				{
					result += '\\';
				}
			}
			else if(open.length > 0)
			{
				sub += this[i];
				
				if(this[i] === open)
				{
					if(open === ';')
					{
						result += String.html(sub);
					}
					
					open = '';
					sub = '';
				}
			}
			else if(this[i] === '<')
			{
				open = '>';
				sub = '<';
			}
			else if(this[i] === '&')
			{
				open = ';';
				sub = '&';
			}
			else
			{
				result += this[i];
			}
		}
		
		if(open.length > 0)
		{
			result += sub;
		}
		
		return result;
	}});

	//
	// text w/ <> replaced &*;
	//
	Object.defineProperty(String.prototype, 'data', { get: function()
	{
		if(this.length === 0)
		{
			return '';
		}
		
		var result = '';
		var open = false;
		var sub;
		
		for(var i = 0; i < this.length; ++i)
		{
			if(this[i] === '\\')
			{
				if(i < (this.length - 1))
				{
					if(open)
					{
						sub += this[++i];
					}
					else
					{
						result += this[++i];
					}
				}
				else if(open)
				{
					sub += '\\';
				}
				else
				{
					result += '\\';
				}
			}
			else if(open)
			{
				sub += this[i];
				
				if(this[i] === ';')
				{
					result += String.html(sub);
					open = false;
					sub = '';
				}
			}
			else if(this[i] === '&')
			{
				sub = '&';
				open = true;
			}
			else
			{
				result += this[i];
			}
		}
		
		if(open)
		{
			result += sub;
		}
		
		return result;
	}});

	//
	Object.defineProperty(String.prototype, 'unicodeLength', { get: function()
	{
		return [ ... this.valueOf() ].length;
	}});

	//
	Object.defineProperty(String.prototype, 'replaces', { value: function(_from, _to, _repeat = DEFAULT_REPEAT)
	{
		//
		if(is(_from, 'RegExp'))
		{
			_repeat = 0;
		}
		else if(! isString(_from, 1))
		{
			throw new Error('Invalid _from argument');
		}

		if(typeof _to !== 'string')
		{
			throw new Error('Invalid _to argument');
		}
		
		if(! ((isInt(_repeat) && _repeat >= 0) || _repeat === null))
		{
			_repeat = DEFAULT_REPEAT;
		}

		if(_repeat === null && _to.includes(_from))
		{
			throw new Error('Infinite _repeat is not possible (_to includes _from)');
		}

		//
		var compare = this.valueOf();
		var result;

		do
		{
			result = compare.replaceAll(_from, _to);

			if(result === compare)
			{
				break;
			}
			else if(_repeat !== null && _repeat-- <= 0)
			{
				break;
			}
			else
			{
				compare = result;
			}
		}
		while(true);

		return result;
	}});

	Object.defineProperty(String.prototype, 'remove', { value: function(_needle, _repeat = DEFAULT_REPEAT, _throw = DEFAULT_THROW)
	{
		if(isString(_needle, true))
		{
			if(_needle.length === 0)
			{
				return this.valueOf();
			}

			_needle = [ _needle ];
		}
		else if(isArray(_needle, true))
		{
			for(var i = 0; i < _needle.length; ++i)
			{
				if(! isString(_needle[i], false))
				{
					if(_throw)
					{
						throw new Error('Invalid _needle[' + i + '] (no non-empty String)');
					}

					_needle.splice(i--, 1);
				}
				else if(_needle.lastIndexOf(_needle[i]) > i)
				{
					_needle.splice(i--, 1);
				}
			}

			if(_needle.length === 0)
			{
				return this.valueOf();
			}
		}
		else if(_throw)
		{
			throw new Error('Invalid _needle argument (neither String nor Array)');
		}
		else
		{
			return this.valueOf();
		}

		var compare = this.valueOf();
		var result;

		do
		{
			for(var i = 0; i < _needle.length; ++i)
			{
				result = compare.replaceAll(_needle[i], '');
			}

			if(result === compare)
			{
				break;
			}
			else
			{
				compare = result;
			}
		}
		while(true);

		return result;
	}});

	Object.defineProperty(String, 'repeat', { value: function(_count = 2, _string)
	{
		if(typeof _string !== 'string')
		{
			throw new Error('Invalid _string argument');
		}

		return _string.repeat(_count);
	}});

	Object.defineProperty(String.prototype, 'repeat', { value: function(_count = 2)
	{
		if(! (isInt(_count) && _count >= 0))
		{
			throw new Error('Invalid _count argument');
		}
		
		var result = '';

		for(var i = 0; i < _count; ++i)
		{
			result += this.valueOf();
		}

		return result;
	}});

	Object.defineProperty(String, 'fill', { value: function(_length, _pad)
	{
		return ('').fill(_length, _pad);
	}});

	Object.defineProperty(String.prototype, 'fill', { value: function(_length, _pad, _cut = DEFAULT_CUT)
	{
		if(! (isInt(_length) && _length >= 0))
		{
			throw new Error('Invalid _length argument');
		}
		else if(typeof _pad !== 'string' || _pad.length === 0)
		{
			throw new Error('Invalid _pad argument');
		}
		else if(typeof _cut !== 'boolean')
		{
			_cut = DEFAULT_CUT;
		}

		if(this.length > _length)
		{
			if(_cut)
			{
				return this.substr(0, _length);
			}

			return this.valueOf();
		}

		var result = this.valueOf();

		for(var i = 0, j = this.length; j < _length; ++i, ++j)
		{
			result += _pad[i % _pad.length];
		}

		return result;
	}});

	//
	Object.defineProperty(String.prototype, 'capitalize', { value: function(_sep)
	{
		if(typeof _sep !== 'string' || _sep.length === 0)
		{
			return this[0].toUpperCase() + this.substr(1);
		}
		
		const split = this.split(_sep);

		for(var i = 0; i < split.length; ++i)
		{
			split[i] = split[i][0].toUpperCase() + split[i].substr(1);
		}

		return split.join(_sep);
	}});
	
	//
	const _split = String.prototype.split;

	Object.defineProperty(String.prototype, 'split', { value: function(_sep, _count, _rest = DEFAULT_SPLIT_REST)
	{
		if(_count === 0)
		{
			return [];
		}
		else if(typeof _sep !== 'string')
		{
			if(isArray(_sep, 1)) for(var i = 0; i < _sep.length; ++i)
			{
				if(typeof _sep !== 'string')
				{
					_sep.splice(i--, 1);
				}
				else if(_sep.length === 0)
				{
					_sep.splice(i--, 1);
				}
			}
			else
			{
				throw new Error('Invalid _sep argument');
			}
			
			if(_sep.length === 0)
			{
				throw new Error('Invalid _sep argument');
			}
		}
		else if(_sep.length === 0)
		{
			return _split.call(this, '');
		}
		else
		{
			_sep = [ _sep ];
		}
		
		if(! isInt(_count))
		{
			_count = 0;
		}
		
		if(typeof _rest !== 'boolean')
		{
			_rest = DEFAULT_SPLIT_REST;
		}
		
		//
		_sep.uniq();
		_sep.lengthSort(false);
		
		//
		const result = [];
		var sub = '';
		var r;
		
		const routine = (_index) => {
			//
			for(var i = 0; i < _sep.length; ++i)
			{
				if(this.substr(_index, _sep[i].length) === _sep[i])
				{
					return _sep[i].length;
				}
			}
			
			//
			return 0;
		};
		
		if(_count >= 0) for(var i = 0; i < this.length; ++i)
		{
			if((r = routine(i)) === 0)
			{
				sub += this[i];
			}
			else
			{
				result.push(sub);
				sub = '';
				i += (r - 1);
				
				if(_count !== 0 && result.length >= _count)
				{
					if(_rest)
					{
						result[result.length - 1] += this.substr(i);
					}
					
					break;
				}
			}
		}
		else for(var i = this.length - 1; i >= 0; --i)
		{
			if((r = routine(i)) === 0)
			{
				sub = this[i] + sub;
			}
			else
			{
				result.unshift(sub);
				sub = '';
				i -= (r - 1);
				
				if(_count !== 0 && result.length >= -_count)
				{
					if(_rest)
					{
						result[0] = this.substring(0, i) + result[0];
					}
					
					break;
				}
			}
		}
		
		if(sub.length > 0)
		{
			result.push(sub);
		}
		
		return result;
	}});
	
	const _at = String.prototype.at;
	
	Object.defineProperty(String.prototype, 'at', { value: function(_index, _comparison, _case_sensitive = true)
	{
		if(arguments.length <= 1)
		{
			return _at.call(this, _index);
		}
		else if(typeof _case_sensitive !== 'boolean')
		{
			_case_sensitive = true;
		}
		
		if(typeof _comparison !== 'string' || _comparison.length === 0)
		{
			throw new Error('Invalid _comparison argument');
		}
		else if(! _case_sensitive)
		{
			_comparison = _comparison.toLowerCase();
		}
		
		var needle = this.substr(_index, _comparison.length);
		
		if(! _case_sensitive)
		{
			needle = needle.toLowerCase();
		}
		
		return (needle === _comparison);
	}});

	//
	const _indexOf = String.prototype.indexOf;
	const _lastIndexOf = String.prototype.lastIndexOf;

	Object.defineProperty(String.prototype, 'indexOf', { value: function(_needle, _position = 0, _case_sensitive = true)
	{
		if(typeof _case_sensitive !== 'boolean')
		{
			_case_sensitive = true;
		}

		if(typeof _position !== 'number')
		{
			_position = 0;
		}

		if(_case_sensitive)
		{
			return _indexOf.call(this.valueOf(), _needle, _position);
		}
		else
		{
			_needle = _needle.toLowerCase();
		}

		return _indexOf.call(this.toLowerCase(), _needle, _position);
	}});

	Object.defineProperty(String.prototype, 'lastIndexOf', { value: function(_needle, _position = +Infinity, _case_sensitive = true)
	{
		if(typeof _case_sensitive !== 'boolean')
		{
			_case_sensitive = true;
		}

		if(typeof _position !== 'number')
		{
			_position = +Infinity;
		}

		if(_case_sensitive)
		{
			return _lastIndexOf.call(this.valueOf(), _needle, _position);
		}
		else
		{
			_needle = _needle.toLowerCase();
		}

		return _lastIndexOf.call(this.toLowerCase(), _needle, _position);
	}});

	Object.defineProperty(String.prototype, 'indicesOf', { value: function(_needle, _case_sensitive = true)
	{
		if(typeof _needle !== 'string')
		{
			throw new Error('Invalid _needle argument');
		}
		else if(typeof _case_sensitive !== 'boolean')
		{
			_case_sensitive = true;
		}

		if(! _case_sensitive)
		{
			_needle = _needle.toLowerCase();
		}

		const result = [];
		const string = (_case_sensitive ? this.valueOf() : this.toLowerCase());
		var index = 0;
		var last = -1;

		do
		{
			if((last = _indexOf.call(string, _needle, last + 1)) > -1)
			{
				result[index++] = last;
			}
		}
		while(last > -1);

		return result;
	}});

	//
	Object.defineProperty(String.prototype, 'reverse', { value: function(... _args)
	{
		if(this.length === 0)
		{
			return '';
		}
		
		return this.split('').reverse(... _args).join('');
	}});

	//
	Object.defineProperty(String.prototype, 'isLowerCase', { get: function()
	{
		return (this.valueOf() === this.toLowerCase());
	}});

	Object.defineProperty(String.prototype, 'isUpperCase', { get: function()
	{
		return (this.valueOf() === this.toUpperCase());
	}});

	Object.defineProperty(String.prototype, 'oneCase', { get: function()
	{
		if(this.length === 0)
		{
			return null;
		}
		else if(this.toLowerCase() === this.valueOf() || this.toUpperCase() === this.valueOf())
		{
			return true;
		}

		return false;
	}});

	//
	isString = (_item, _min = 1) => {
		if(typeof _min === 'boolean')
		{
			_min = (_min ? 0 : 1);
		}
		else if(! (isInt(_min) && _min >= 0))
		{
			_min = 1;
		}

		if(typeof _item === 'string')
		{
			return (_item.length >= _min);
		}

		return false;
	};

	Object.defineProperty(String, 'isString', { value: isString.bind(String) });

	//
	Object.defineProperty(String, 'quote', { get: function()
	{
		return [ '`', '\'', '"' ];
	}});

	Object.defineProperty(String.prototype, 'quote', { value: function(_quote = true, _escape = true, _escape_limited = false)
	{
		if(_quote === false)
		{
			return this.valueOf();
		}

		const selectQuote = () => {
			const quote = String.quote;
			const orig = [ ... quote ];

			for(var i = 0; i < quote.length; ++i)
			{
				quote[i] = [ quote[i], this.indicesOf(quote[i]).length ];
			}

			quote.sort(1, true);
			const same = [ quote[0][0] ];

			for(var i = 1; i < quote.length; ++i)
			{
				if(quote[i][1] === quote[0][1])
				{
					same[i] = quote[i][0];
				}
				else
				{
					break;
				}
			}

			var res;

			for(var i = 0; i < orig.length; ++i)
			{
				if(same.indexOf(orig[i]) > -1)
				{
					res = orig[i];
					break;
				}
			}

			return res;
		};

		if(typeof _escape !== 'boolean')
		{
			_escape = true;
		}

		if(typeof _escape_limited !== 'boolean')
		{
			_escape_limited = true;
		}

		if(typeof _quote === 'string')
		{
			if(_quote.length === 0)
			{
				return this.valueOf();
			}
		}
		else
		{
			_quote = null;
		}

		if(_quote === null)
		{
			_quote = selectQuote();
		}

		//
		var result;

		if(_escape)
		{
			result = '';

			for(var i = 0; i < this.length; ++i)
			{
				if(_escape_limited && this.at(i, '\\'))
				{
					if(i < (this.length - 1) && this.at(i + 1, _quote))
					{
						result += '\\' + _quote;
						++i;
					}
					else
					{
						result += '\\';
					}
				}
				else if(this.at(i, _quote))
				{
					result += '\\' + this[i];
				}
				else
				{
					result += this[i];
				}
			}
		}
		else
		{
			result = this.valueOf();
		}

		return (_quote + result + _quote);
	}});

	//
	Object.defineProperty(String.prototype, 'startsWith', { value: function(... _args)
	{
		var CASE_SENSITIVE = true;

		for(var i = 0; i < _args.length; ++i)
		{
			if(typeof _args[i] === 'boolean')
			{
				CASE_SENSITIVE = _args.splice(i--, 1)[0];
			}
			else if(typeof _args[i] !== 'string')
			{
				throw new Error('Invalid ..._args[' + i + '] argument (not a non-empty String)');
			}
			else if(_args[i].length === 0)
			{
				return this.length;
			}
		}

		if(_args.length === 0)
		{
			return 0;
		}
		else
		{
			_args.uniq();
			_args.lengthSort(false);
		}

		var result = 0;
		var found;

		for(var i = 0; i < this.length; ++i)
		{
			found = -1;

			for(var j = 0; j < _args.length; ++j)
			{
				if(this.at(i, _args[j], CASE_SENSITIVE))
				{
					found = _args[j].length;
					break;
				}
			}

			if(found < 0)
			{
				break;
			}

			result += found;

			if(DEFAULT_COUNT_ONCE)
			{
				i += (found - 1);
			}
		}

		return result;
	}});

	Object.defineProperty(String.prototype, 'endsWith', { value: function(... _args)
	{
		var CASE_SENSITIVE = true;

		for(var i = 0; i < _args.length; ++i)
		{
			if(typeof _args[i] === 'boolean')
			{
				CASE_SENSITIVE = _args.splice(i--, 1)[0];
			}
			else if(typeof _args[i] !== 'string')
			{
				throw new Error('Invalid ..._args[' + i + '] argument (not a String)');
			}
			else if(_args[i].length === 0)
			{
				return this.length;
			}
		}

		if(_args.length === 0)
		{
			return 0;
		}
		else
		{
			_args.uniq();
			_args.lengthSort(false);
		}

		var result = 0;
		var found;

		for(var i = this.length - 1; i >= 0; --i)
		{
			found = -1;

			for(var j = 0; j < _args.length; ++j)
			{
				if(this.at(i - _args[j].length + 1, _args[j], CASE_SENSITIVE))
				{
					found = _args[j].length;
					break;
				}
			}

			if(found < 0)
			{
				break;
			}

			result += found;

			if(DEFAULT_COUNT_ONCE)
			{
				i -= (found - 1);
			}
		}

		return result;
	}});
	
	//
	Object.defineProperty(String, 'sameStart', { value: function(... _args)
	{
		if(_args.length === 0)
		{
			return '';
		}
		else for(var i = 0; i < _args.length; ++i)
		{
			if(typeof _args[i] !== 'string')
			{
				throw new Error('Invalid ..._args[' + i + '] argument (not a String)');
			}
		}
		
		_args.uniq();
		
		var result = 0;
		var same;
		
		for(var i = 0;; ++i)
		{
			same = true;
			
			if(i >= _args[0].length)
			{
				break;
			}
			else for(var j = 1; j < _args.length; ++j)
			{
				if(i >= _args[j].length)
				{
					break;
				}
				else if(_args[j][i] !== _args[0][i])
				{
					same = false;
					break;
				}
			}
			
			if(same)
			{
				++result;
			}
			else
			{
				break;
			}
		}
		
		return _args[0].substr(0, result);
	}});
	
	Object.defineProperty(String, 'sameEnd', { value: function(... _args)
	{
		if(_args.length === 0)
		{
			return '';
		}
		else for(var i = 0; i < _args.length; ++i)
		{
			if(typeof _args[i] === 'string')
			{
				if(_args[i].length > 0)
				{
					_args[i] = _args[i].reverse();
				}
			}
			else
			{
				throw new Error('Invalid ..._args[' + i + '] (not a String)');
			}
		}
		
		return String.sameStart(... _args).reverse();
	}});

	//
	Object.defineProperty(String.prototype, 'uniq', { value: function()
	{
		return String.uniq(this.valueOf());
	}});

	Object.defineProperty(String, 'uniq', { value: function(... _args)
	{
		if(_args.length === 0)
		{
			return null;
		}
		else for(var i = 0; i < _args.length; ++i)
		{
			if(typeof _args[i] !== 'string')
			{
				throw new Error('Invalid ..._args[' + i + ']');
			}
		}

		var result = '';

		for(var i = 0; i < _args.length; ++i)
		{
			for(var j = 0; j < _args[i].length; ++j)
			{
				if(! result.includes(_args[i][j]))
				{
					result += _args[i][j];
				}
			}
		}

		return result;
	}});

	//
	Object.defineProperty(String.prototype, 'unit', { value: function()
	{
		if(this.length === 0)
		{
			return null;
		}

		const result = [ '', '' ];
		var hadPoint = false;
		var hadValue = false;
		var charCode;

		var negative = false;
		var text = this.trim();

		for(var i = 0; i < text.length; ++i)
		{
			if(text[i] === '+')
			{
				continue;
			}
			else if(text[i] === '-')
			{
				negative = !negative;
			}
			else if(! text[i].isEmpty)
			{
				text = text.substr(i);
				break;
			}
		}

		for(var i = 0; i < text.length; ++i)
		{
			if(hadValue)
			{
				if(text[i] === 'n' && result[1].length === 0)
				{
					result[0] += 'n';
				}
				else if((charCode = text.charCodeAt(i)) >= 97 && charCode <= 122)
				{
					result[1] += text[i];
				}
				else if(charCode >= 65 && charCode <= 90)
				{
					result[1] += String.fromCharCode(charCode + 32);
				}
				else
				{
					break;
				}
			}
			else
			{
				if((charCode = text.charCodeAt(i)) >= 48 && charCode <= 57)
				{
					result[0] += text[i];
				}
				else if(text[i] === '.')
				{
					if(hadPoint)
					{
						break;
					}
					else
					{
						result[0] += '.';
						hadPoint = true;
					}
				}
				else if(! text[i].isEmpty)
				{
					hadValue = true;
					--i;
				}
			}
		}

		if(result[0].length === 0)
		{
			return text;
		}
		else if(result[0][result[0].length - 1] === 'n' && !isNaN(result[0].slice(0, -1)))
		{
			result[0] = BigInt.from(result[0].slice(0, -1));
		}
		else if(! isNaN(result[0]))
		{
			result[0] = Number(result[0]);
		}

		if(negative)
		{
			if(typeof result[0] === 'string')
			{
				result[0] = '-' + result[0];
			}
			else
			{
				result[0] = -result[0];
			}
		}

		return result;
	}});

	//
	Object.defineProperty(String.prototype, 'getIndex', { value: function(... _args)
	{
		if(_args.length === 0)
		{
			return this.length - 1;
		}

		const result = new Array(_args.length);

		for(var i = 0; i < _args.length; ++i)
		{
			if(isNumber(_args[i]))
			{
				result[i] = Math.getIndex(_args[i], this.length);
			}
			else
			{
				throw new Error('Invalid ..._args[' + i + '] (no Number)');
			}
		}

		if(result.length === 1)
		{
			return result[0];
		}

		return result;
	}});

	//
	Object.defineProperty(String.prototype, 'isEmpty', { get: function()
	{
		for(var i = 0; i < this.length; ++i)
		{
			if(this.charCodeAt(i) > 32)
			{
				return false;
			}
		}

		return true;
	}});

	Object.defineProperty(String.prototype, 'hasEmpty', { get: function()
	{
		if(this.length === 0)
		{
			return null;
		}
		else for(var i = 0; i < this.length; ++i)
		{
			if(this.charCodeAt(i) <= 32)
			{
				return true;
			}
		}

		return false;
	}});

	//
	const _trim = String.prototype.trim;

	Object.defineProperty(String.prototype, 'trim', { value: function(... _args)
	{
		var result = _trim.apply(this, _args);
		var remove = 0;

		for(var i = 0; i < result.length; ++i)
		{
			if(result.charCodeAt(i) <= 32)
			{
				++remove;
			}
			else
			{
				break;
			}
		}

		if(remove === result.length)
		{
			return '';
		}
		else if(remove > 0)
		{
			result = result.substr(remove);
			remove = 0;
		}

		for(var i = result.length - 1; i >= 0; --i)
		{
			if(result.charCodeAt(i) <= 32)
			{
				++remove;
			}
			else
			{
				break;
			}
		}

		if(remove === result.length)
		{
			return '';
		}
		else if(remove > 0)
		{
			result = result.slice(0, -remove);
		}

		return result;
	}});

	//

})();

