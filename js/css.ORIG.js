(function()
{

	//
	const DEFAULT_THROW = true;
	const DEFAULT_PARSE = true;

	//
	//TODO/convert between object and string..
	//
	css = (_object_string, _throw = DEFAULT_THROW) => {
throw new Error('TODO');
	};

	//
	css.camel = camel;
	css.matrix = css.matrix3d = CSSMatrix;

	//
	css.toString = (_value, _throw = DEFAULT_THROW) => {
		if(typeof _value === 'string')
		{
			return trim(_value);
		}
		else if(typeof _value === 'boolean')
		{
			_value = (_value ? 'auto' : 'none');
		}
		else if(isNumeric(_value))
		{
			_value = _value.toString();
		}
		else if(typeof _value === 'undefined')
		{
			_value = 'undefined';
		}
		else if(_value === null)
		{
			_value = 'null';
		}
		else if(isArray(_value, true))
		{
			_value = css.fromArray(_value, _throw);
		}
		else if(isObject(_value, true))
		{
			_value = css.fromObject(_value, _throw);
		}
		else if(_throw)
		{
			throw new Error('Unsupported type \'' + typeOf(_value) + '\'');
		}
		else
		{
			return '';//null//undefined/..;
		}

		return _value;
	};

	css.fromArray = (_array, _throw = DEFAULT_THROW) => {
		if(! isArray(_array, true))
		{
			if(_throw)
			{
				throw new Error('Is not an Array');
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
			if(typeof _array[i] !== 'string')
			{
				_array[i] = css.toString(_array[i], _throw);
			}

			if(_array[i].includes(' ') || _array[i].includes('\t'))
			{
				result += _array[i].quote('\'');
			}
			else
			{
				result += _array[i];
			}

			result += ' ';
		}
		
		if(result.length > 0)
		{
			result = result.slice(0, -1);
		}

		return result;
	};
	
	css.fromObject = (_value, _throw = DEFAULT_THROW) => {
		if(! isObject(_value, true))
		{
			if(_throw)
			{
				throw new Error('Is not an Object');
			}
			
			return _value;
		}

		const keys = Object.keys(_value, false, false);
		
		if(keys.length === 0)
		{
			if(isArray(_value, true))
			{
				return css.fromArray(_value, _throw);
			}
			
			return '';
		}
		
		var result = '';
		
		for(var i = 0; i < keys.length; ++i)
		{
throw new Error('TODO');
		}
		
		return result;
	};

	css.fromString = (_value, _throw = DEFAULT_THROW) => {
		if(typeof _value !== 'string')
		{
			if(_throw)
			{
				throw new Error('Is not a String');
			}
			
			return _value;
		}
		else if((_value = trim(_value)).length === 0)
		{
			return '';
		}
		else if(! isNaN(_value))
		{
			_value = Number(_value);
		}
		else if(_value[_value.length - 1] === 'n' && !isNaN(_value.slice(0, -1)))
		{
			_value = BigInt.from(_value.slice(0, -1));
		}
		else switch(_value.toLowerCase())
		{
			case 'none':
			case 'false':
			case 'no':
				_value = false;
				break;
			case 'auto':
			case 'true':
			case 'yes':
				_value = true;
				break;
			case 'null':
				_value = null;
				break;
			case 'undefined':
				_value = undefined;
				break;
		}

		return _value;
	};

	const processString = (_string, _parse = DEFAULT_PARSE, _throw = DEFAULT_THROW) => {
		if(typeof _string !== 'string')
		{
			if(_throw)
			{
				throw new Error('Invalid _string argument');
			}
			
			return _string;
		}
		else if((_string = trim(_string)).length === 0)
		{
			return (_parse ? 0 : '');
		}
		else if(_parse === null)
		{
			return _string;
		}
		else if(typeof _parse !== 'boolean' && !isString(_parse, false) && !isArray(_parse, false))
		{
			_parse = DEFAULT_PARSE;
		}

		const result = [''];
		var open = '';

		for(var i = 0, j = 0; i < _string.length; ++i)
		{
			if(open.length > 0)
			{
				if(_string[i] === open)
				{
					open = '';
					result[++j] = '';
				}
				else
				{
					result[j] += _string[i];
				}
			}
			else if(_string[i] === '\'' || _string[i] === '"' || _string[i] === '`')
			{
				open = _string[i];
				result[++j] = '';
			}
			else if(_string[i] === '(')
			{
				open = ')';
				result[++j] = [];
			}
			else if(_string[i] === ' ')
			{
				result[++j] = '';
			}
			else if(_string[i] === '\\')
			{
				if(i < (_string.length - 1))
				{
					result[j] += _string[++i];
				}
				else
				{
					result[j] += _string[i];
				}
			}
			else
			{
				result[j] += _string[i];
			}
		}
		
		for(var i = 0; i < result.length; ++i)
		{
			if(result[i].length === 0)
			{
				result.splice(i--, 1);
			}
		}

		if(isString(_parse, false) || isArray(_parse, false) || _parse === true)
		{
			for(var i = 0; i < result.length; ++i)
			{
				result[i] = getValue(result[i], _parse);
			}
		}

		if(!!_parse) for(var i = 0; i < result.length; ++i)
		{
			if(typeof result[i] !== 'string')
			{
				continue;
			}
			else if(typeof (result[i] = css.fromString(result[i])) === 'string' && result[i].length === 0 && _parse === true)
			{
				result[i] = 0;
			}
		}

		if(result.length === 0)
		{
			return (_parse === true ? 0 : '');
		}
		else if(result.length === 1)
		{
			return result[0];
		}
		else if(_parse === null)
		{
			return css.fromArray(result);
		}

		return result;
	};

	//
	const trim = (_string) => {
		if(typeof _string !== 'string')
		{
			return _string;
		}
		else if(_string.length === 0)
		{
			return '';
		}
		
		var result = _string;
		var compare;
		
		do
		{
			compare = result.trim().replaces('\t', ' ', 0).replaces('  ', ' ', null).replaces(';;', ';', null).remove('\r').remove('\n').trim();
			
			while(compare[compare.length - 1] === ';')
			{
				compare = compare.slice(0, -1);
			}
			
			if(compare === result)
			{
				break;
			}
			else
			{
				result = compare;
			}
		}
		while(result !== compare);

		return result;
	};

	//
	css.getUrl = (_string, _throw = DEFAULT_THROW) => {
		return 'favicon.png';
throw new Error('TODO');
	};
	
	//
	css.split = (_string, _throw = DEFAULT_THROW) => {
		const blocks = css.split.blocks(_string, _throw);

		if(blocks === null)
		{
			return null;
		}
		else if(typeof blocks === 'string')
		{
			return css.split.line(blocks, _throw);
		}

		const result = Object.create(null);
		var split, keys;
		var block;
		var k, v;

		for(const idx in blocks)
		{
			keys = [];
			split = idx.split(',');
			
			for(var i = 0, j = 0; i < split.length; ++i)
			{
				if(split[i].length === 0)
				{
					continue;
				}
				else
				{
					keys[j++] = split[i];
				}
			}
			
			if(keys.length === 0)
			{
				if(_throw)
				{
					throw new Error('No CSS keys defined');
				}

				return null;
			}
			else if((block = css.split.block(blocks[idx], _throw)) === null)
			{
				return null;
			}
			else if(isArray(block, true))
			{
				for(var i = 0; i < keys.length; ++i)
				{
					result[keys[i]] = block;
				}
			}
			else
			{
				throw new Error('DEBUG/TODO');
			}
		}

		return result;
	};
	
	css.split.blocks = (_string, _throw = DEFAULT_THROW) => {
		if(typeof _string !== 'string')
		{
			if(_throw)
			{
				throw new Error('Not a String');
			}

			return _string;
		}
		else if((_string = trim(_string)).length === 0)
		{
			return Object.create(null);
		}
		
		var result = Object.create(null)
		var open = false;
		var sub = '';
		
		for(var i = 0, j = 0; i < _string.length; ++i)
		{
			if(open)
			{
				if(_string[i] === '}')
				{
					open = false;
					
					if(typeof result === 'string')
					{
						result = result.trim();
						break;
					}
					else
					{
						result[sub] = result[sub].trim();
						sub = '';
					}
				}
				else if(typeof result === 'string')
				{
					result += _string[i];
				}
				else if(sub.length > 0)
				{
					result[sub] += _string[i];
				}
				else if(_throw)
				{
					throw new Error('Unexpected');
				}
				else
				{
					return null;
				}
			}
			else if(_string[i] === '{')
			{
				if((sub = sub.trim()).length === 0)
				{
					result = '';
				}
				else
				{
					result[sub] = '';
				}
				
				open = true;
			}
			else if(_string[i] !== '\t' && _string[i] !== ' ')
			{
				sub += _string[i];
			}
		}
		
		if(sub.length > 0)
		{
			return trim(sub);
		}

		return result;
	};
	
	css.split.block = (_string, _throw = DEFAULT_THROW) => {
		if(typeof _string !== 'string')
		{
			if(_throw)
			{
				throw new Error('Not a String');
			}
			
			return _string;
		}
		else if((_string = trim(_string)).length === 0)
		{
			return [];
		}

		const split = _string.split(';');
		const result = [];
		var key, value;
		
		for(var i = 0, j = 0; i < split.length; ++i)
		{
			if(split[i].length === 0)
			{
				continue;
			}
			else if((split[i] = split[i].split(':', 2, true)).length === 0)
			{
				continue;
			}
			else if(split[i].length === 1)
			{
				if(_throw)
				{
					throw new Error('Invalid CSS _string');
				}

				return null;
			}
			else
			{
				key = trim(split[i][0]);
				value = trim(split[i][1]);
			}

			result[j++] = [ key, value ];
			result[key] = value;
		}

		return result;
	};
	
	css.split.line = (_string, _throw = DEFAULT_THROW) => {
		if(isArray(_string, true))
		{
			for(var i = 0; i < _string.length; ++i)
			{
				if(typeof _string[i] === 'string')
				{
					_string[i] = trim(_string[i]);
				}
			}
			
			return _string;
		}
		else if(typeof _string !== 'string')
		{
			if(_throw)
			{
				throw new Error('Not a String');
			}
			
			return _string;
		}
		else if((_string = trim(_string)).length === 0)
		{
			return '';
		}
		else if((_string = _string.split(':', 2, true)).length === 0)
		{
			return [''];
		}
		else if(_string.length === 1)
		{
			return trim(_string[0]);
		}
		else for(var i = 0; i < 2; ++i)
		{
			_string[i] = trim(_string[i]);
		}

		return [ _string[0], _string[1] ];
	};

	//
	const render = (_item, _throw = DEFAULT_THROW) => {
throw new Error('TODO');
	};
	
	//render.block = (_..
	//render.blocks = (_..
	//render.line = (_..

	//
	css.parse = (_string, _parse = DEFAULT_PARSE, _throw = DEFAULT_THROW) => {
		if(typeof _string !== 'string')
		{
			if(_throw)
			{
				throw new Error('Not a String');
			}
			
			return _string;
		}
		else if((_string = trim(_string)).length === 0)
		{
			return [];
		}
		else if((_string = css.split(_string, _throw)) === null)
		{
			return null;
		}
		else if(typeof _string === 'string')
		{
			if(_parse)
			{
				return css.fromString(_string, _throw);
			}
			
			return _string;
		}
		else if(isArray(_string, true))
		{
			if(_string.length === 0)
			{
				return css.parse.value('', _parse, _throw);
			}
			else if(_string.length === 1)
			{
				return css.parse.value(_string[0], _parse, _throw);
			}
			else if(_string.length === 2)
			{
				_string[1] = css.parse.value(_string[1], _parse, _throw);
			}
			else for(var i = 0; i < _string.length; ++i)
			{
				_string[i] = css.parse.value(_string[i], _parse, _throw);
			}
			
			return _string;
		}
		else if(isObject(_string, true))
		{
alert('starting parse():\n\n\n'+JSON.stringify(_string));//{"pi":[["pi1","3.14"]]}//
			return result;
		}
		else if(_throw)
		{
	throw new Error('DEBUG/TODO');
		}
		else
		{
	throw new Error('DEBUG/TODO');
			return null;
		}
		
		return _string;
	};
	
	css.render = (_value, _throw = DEFAULT_THROW) => {
		if(typeof _value === 'string')
		{
			return _value;
		}
		else if(! isArray(_value, true))
		{
			return css.render.value(_value, _throw);
		}
		else if(_value.length === 0)
		{
			return '';
		}
		
		var result = '';
		
		for(var i = 0; i < _value.length; ++i)
		{
			if(_value.length === 0 || typeof _value[0] !== 'string')
			{
				if(_throw)
				{
					throw new Error('Invalid _value[' + i + ']');
				}
				
				return null;
			}
			else
			{
				result += _value[i][0] + ': ' + css.render.value(_value[i].slice(1), _throw) + '\n';
			}
		}
		
		return result.slice(0, -1);
	};
	
	//
	css.parse.value = (_string, _parse = DEFAULT_PARSE, _throw = DEFAULT_THROW) => {
		if(typeof _string !== 'string')
		{
			if(isArray(_string, true))
			{
				if(_string.length === 2)
				{
					_string[1] = processString(_string[1], _parse, _throw);
				}
				
				return _string;
			}
			else if(_throw)
			{
				throw new Error('Your _string argument is invalid');
			}

			return null;
		}
		else if((_string = trim(_string)).length === 0)
		{
			return (_parse ? 0 : '');
		}

		return processString(_string, _parse, _throw);
	};

	css.parse.block = (_string, _throw = DEFAULT_THROW) => {
		//
	};
	
	css.parse.value.functional = (_string, _throw = DEFAULT_THROW) => {
throw new Error('TODO');
	};

	css.render.value = (_value, _throw = DEFAULT_THROW) => {
throw new Error('TODO');
	};

	css.render.value.block = (_object, _throw = DEFAULT_THROW) => {
throw new Error('TODO?');
	};
	
	css.render.value.functional = (_object, _throw = DEFAULT_THROW) => {
throw new Error('TODO');
	};

	//
	css.parseFunctionalStyle = (_string, _throw = DEFAULT_THROW) => {
		if(! isString(_string, true))
		{
			throw new Error('Invalid _string argument');
		}
		else if((_string = _string.trim().replaces('  ', ' ', null)).length === 0 || _string === 'none')
		{
			return [];
		}
		
		//
		const result = [];
		var open = false;
		var params = '';
		var name = '';
		var s;
		var j = 0;
		
		for(var i = 0; i < _string.length; ++i)
		{
			if(_string[i] === '(')
			{
				open = true;
			}
			else if(_string[i] === ')')
			{
				open = false;
				
				if(name.length > 0)
				{
					if(params.length > 0)
					{
						s = params.split(',');
						params = '';
					}
					else
					{
						s = [];
					}
					
					result[name] = s;
					result[j++] = [ name, ... s ];
					
					name = '';
				}
				else
				{
					throw new Error('Invalid function style string');
				}
			}
			else if(open)
			{
				if(_string[i] === ' ' || _string[i] === '\t')
				{
					continue;
				}
				else
				{
					params += _string[i];
				}
			}
			else if(_string[i] !== ' ' && _string[i] !== '\t')
			{
				name += _string[i];
			}
			else if(name.length > 0)
			{
				if(params.length > 0)
				{
					s = params.split(',');
					params = '';
				}
				else
				{
					s = [];
				}
					
				result[name] = s;
				result[j++] = [ name, ... s ];
				
				name = '';
			}
		}

		if(name.length > 0)
		{
			if(params.length === 0)
			{
				s = [];
			}
			else
			{
				s = params.split(',');
			}
			
			result[name] = s;
			result.push([ name, ... s ]);
		}

		if(('none' in result) && j > 1)
		{
			for(var i = 0; i < result.length; ++i)
			{
				if(result[i][0] === 'none')
				{
					result.splice(i, 1);
					break;
				}
			}
			
			delete result.none;
		}
		
		return result;
	};
	
	css.renderFunctionalStyle = (_object, _throw = DEFAULT_THROW) => {
		if(typeof _object === 'string')
		{
			if(_object.length === 0)
			{
				return 'none';
			}

			return _object;
		}
		else if(! isObject(_object))
		{
			throw new Error('Invalid _object argument (not an Object)');
		}
		
		//
		var result = '';
		
		//
		const fromArray = () => {
			//
			if(_object.length === 0)
			{
				return 'none';
			}
			else if(_object.length === 1)
			{
				if(_object[0][0] === 'none')
				{
					return 'none';
				}
			}
			else for(var i = 0; i < _object.length; ++i)
			{
				if(_object[i][0] === 'none')
				{
					_object.splice(i, 1);
				}
			}
			
			//
			for(var i = 0; i < _style_object.length; ++i)
			{
				result += _object[i].shift() + '(' + _object[i].join(',') + ') ';
			}
			
			return result.slice(0, -1);
		};
		
		const fromObject = () => {
			//
			const keys = Object.keys(_object);
			
			if(keys.length === 0)
			{
				return 'none';
			}
			else if(keys.length === 1)
			{
				if(keys[0] === 'none')
				{
					return 'none';
				}
			}
			else
			{
				if(keys.includes('none'))
				{
					keys.remove('none');
					delete keys.none;
					
					if(isArray(_object)) for(var i = 0; i < _object.length; ++i)
					{
						if(_object[i][0] === 'none')
						{
							_object.splice(i, 1);
							break;
						}
					}
				}
			}

			//
			for(var i = 0; i < keys.length; ++i)
			{
				result += keys[i] + '(' + _object[keys[i]].join(',') + ') ';
			}
			
			return result.slice(0, -1);
		};
		
		//
		if(isArray(_object, true))
		{
			return fromArray();
		}
		else if(isObject(_object, true))
		{
			return fromObject();
		}

		throw new Error('Invalid _object (is neither Array nor Object)');
	};

	//
		
})();

//	Object.defineProperty(HTMLElement, 'renderFunctionalStyle', { value: function(_style_object)
//	Object.defineProperty(HTMLElement, 'parseFunctionalStyle', { value: function(_style_string)
