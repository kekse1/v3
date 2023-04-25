(function()
{

	//
	const DEFAULT_THROW = true;
	const DEFAULT_PARSE = true;
	const DEFAULT_MARK_SELECTOR = true;
	const DEFAULT_SELECTOR_SEP = ',';
	const DEFAULT_SEP_SPACE = true;
	
	//
	css = { camel, matrix: CSSMatrix, matrix3d: CSSMatrix };
	
	//
	css.getUrl = (_string, _parse = DEFAULT_PARSE, _throw = DEFAULT_THROW) => {
		if(typeof _string !== 'string' && !isArray(_string, true))
		{
			if(_throw)
			{
				throw new Error('Invalid _string argument');
			}

			return _string;
		}
		else if(_parse === null)
		{
			return _string.trim();
		}
		else if((_string = css.parse.value(_string, false, _throw)) === null)
		{
			return null;
		}
		else if(! ('url' in _string))
		{
			if(_throw)
			{
				throw new Error('No url() contained');
			}
			
			return null;
		}

		return _string.url;
	};
	
	//
	//TODO/schichten-aufbau aller (code=>block=>line=>value)...
	//
	CSSCode = css.CSSCode = css.code = class CSSCode
	{
		constructor(_item, _parse = DEFAULT_PARSE, _throw = DEFAULT_THROW)
		{
			if(typeof _item === 'string')
			{
				if((_item = css.parse.code(_item, _parse, _throw)) === null)
				{
					return null;
				}
			}
			
			if(isObject(_item, true))
			{
				if(! CSSCode.isValidObject(_item))
				{
					if(_throw)
					{
						throw new Error('Invalid _item argument (Object not valid for a CSS Code)');
					}
					
					return null;
				}
			}
			else if(_throw)
			{
				throw new Error('Invalid _item argument (neither String nor Object)');
			}
			else
			{
				return null;
			}
			
			//
			this._selectors = Object.keys(_item, false, false);
		}
		
		static isValidObject(_item)
		{
			for(const idx in _item)
			{
				if(! isObject(_item[idx], true))
				{
					return false;
				}
			}
			
			return true;
		}
		
		toString(_throw = DEFAULT_THROW)
		{
			return css.render.code(this, _throw);
		}
		
		get selectors()
		{
			return (this._selectors || null);
		}
		
		get selector()
		{
			//
			//TODO/ ' ' sowie ',' separatoren moeglich!
		}
	}
	
	CSSBlock = css.CSSBlock = css.block = class CSSBlock
	{
		constructor(_item, _parse = DEFAULT_PARSE, _throw = DEFAULT_THROW)
		{
			if(typeof _item === 'string')
			{
				if((_item = css.parse.block(_item, _parse, _throw)) === null)
				{
					return null;
				}
			}
			else if(isObject(_item, true))
			{
				//
			}
			else if(_throw)
			{
				throw new Error('Invalid _item argument (neither String nor Object)');
			}
			else
			{
				return null;
			}
			
			for(const idx in _item)
			{
				this[idx] = new CSSLine(_item[idx], _parse, _throw);
			}
		}
	}
	
	CSSLine = css.CSSLine = css.line = class CSSLine
	{
		constructor(_item, _parse = DEFAULT_PARSE, _throw = DEFAULT_THROW)
		{
			if(typeof _item === 'string')
			{
				if((_item = css.parse.line(_item, _parse, _throw)) === null)
				{
					return null;
				}
			}
			else if(isObject(_item, true))
			{
				//_item = css.parse.code(_item, _parse
			}
			else if(_throw)
			{
				throw new Error('Invalid _item argument (neither String nor Object)');
			}
			else
			{
				return null;
			}
			
			//
			//for(...
		}
	}
	
	CSSValue = css.CSSValue = css.value = class CSSValue
	{
		constructor(_item, _parse = DEFAULT_PARSE, _throw = DEFAULT_THROW)
		{
			if(typeof _item === 'string')
			{
				if((_item = css.parse.value(_item, _parse, _throw)) === null)
				{
					return null;
				}
			}
			else if(isObject(_item, true))
			{
				//
			}
			else if(_throw)
			{
				throw new Error('Invalid _item argument (neither String nor Object/Array)');
			}
			else
			{
				return null;
			}
			
			//
		}
	}
	
	//
	css.parse = (_string, _parse = DEFAULT_PARSE, _throw = DEFAULT_THROW) => {
throw new Error('TODO');
	};
	
	css.parse.value = (_value, _parse = DEFAULT_PARSE, _throw = DEFAULT_THROW) => {
		if(typeof _value === 'string')
		{
			if(_parse === null)
			{
				return _value.trim();
			}
			else if((_value = processValue(_value, _throw)) === null)
			{
				return null;
			}
			else if(typeof _value === 'string')
			{
				return fromString(_value, _parse, _throw);
			}
		}
		else if(! isObject(_value, true))
		{
			if(_throw)
			{
				throw new Error('Invalid _value argument');
			}
			
			return null;
		}
		
		const keys = Object.keys(_value, false, false);
		
		if(_value.length === 0 && keys.length === 0)
		{
			return (_parse ? 0 : '');
		}
		else
		{
			for(var i = _value.length - 1; i >= 0; --i)
			{
				if(typeof _value[i] === 'string')
				{
					_value[i] = fromString(_value[i], _parse, _throw);
				}
				else if(isArray(_value[i], false)) for(var j = 0; j < _value[i].length; ++j)
				{
					if(typeof _value[i][j] === 'string')
					{
						_value[i][j] = fromString(_value[i][j], _parse, _throw);
					}
				}
			}
			
			for(var i = 0; i < keys.length; ++i)
			{
				if(typeof _value[keys[i]] === 'string')
				{
					_value[keys[i]] = fromString(_value[keys[i]], _parse, _throw);
				}
				else if(isArray(_value[keys[i]], false)) for(var j = 0; j < _value[keys[i]].length; ++j)
				{
					if(typeof _value[keys[i]][j] === 'string')
					{
						_value[keys[i]][j] = fromString(_value[keys[i]][j], _parse, _throw);
					}
				}
			}
		}
		
		const objLen = keys.length;
		const arrLen = _value.length;
		
		if(objLen === 1 && arrLen === 0)
		{
			return _value[keys[0]];
		}
		else if(objLen === 0 && arrLen === 1)
		{
			return _value[0];
		}
		
		return _value;
	};
	
	css.parse.line = (_value, _parse = DEFAULT_PARSE, _throw = DEFAULT_THROW) => {
		if(typeof _value === 'string')
		{
			if((_value = _value.trim()).length === 0)
			{
				return (_parse ? 0 : '');
			}
			else if((_value = _value.split(':', 2, true)).length === 1)
			{
				return css.parse.value(_value, _parse, _throw);
			}
		}
		else if(! isArray(_value, 2) && _value.length === 2)
		{
			if(_throw)
			{
				throw new Error('Invalid _value (neither String nor Array with .length == 2)');
			}
			
			return null;
		}
		else if(! isString(_value[0], false))
		{
			throw new Error('Invalid _value argument (first element is not a non-empty key string)');
		}
		else if((_value[1] = css.parse.value(_value[1], _parse, _throw)) === null)
		{
			return null;
		}
		
		return _value;
	};
	
	css.parse.block = (_value, _parse = DEFAULT_PARSE, _throw = DEFAULT_THROW) => {
		if(typeof _value === 'string')
		{
			const orig = _value;

			if((_value = (_value = _value.trim()).split(';')).length === 0)
			{
				return css.parse.value(orig, _parse, _throw);
			}
		}
		else if(isObject(_value, true))
		{
			return _value;
		}
		else if(_throw)
		{
			throw new Error('Invalid _value argument (neither String nor Object)');
		}
		else
		{
			return null;
		}
		
		const result = Object.create(null);
		
		for(var i = 0; i < _value.length; ++i)
		{
			if(typeof _value[i] !== 'string')
			{
				if(_throw)
				{
					throw new Error('Invalid _value argument');
				}
				
				return null;
			}
			else if((_value[i] = _value[i].trim()).length === 0)
			{
				_value.splice(i--, 1);
				continue;
			}
			else if((_value[i] = css.parse.line(_value[i], _parse, _throw)) === null)
			{
				return null;
			}
			else if((result[_value[i][0]] = css.parse.value(_value[i][1], _parse, _throw)) === null)
			{
				return null;
			}
		}

		return result;
	};
	
	css.parse.code = (_value, _parse = DEFAULT_PARSE, _throw = DEFAULT_THROW) => {
		if(typeof _value === 'string')
		{
			if((_value = (_value = _value.trim())).length === 0)
			{
				return (_parse ? 0 : '');
			}
			else if(! (_value.includes('{') && _value.includes('}')))
			{
				return css.parse.block(_value, _parse, _throw);
			}
		}
		else if(! isObject(_value, true))
		{
			if(_throw)
			{
				throw new Error('Invalid _value argument (neither String nor Object)');
			}
			
			return null;
		}

		const blocks = [''];
		var selectors = '';
		var open = false;
		
		for(var i = 0, j = 0; i < _value.length; ++i)
		{
			if(open)
			{
				if(_value[i] === '}')
				{
					open = false;

					if(selectors.length > 0 && blocks[j].length > 0)
					{
						if((blocks[j] = blocks[j].trim())[blocks[j].length - 1] !== ';')
						{
							blocks[j] += ';';
						}
						selectors = css.parse.selectors(selectors, true, _throw);
				alert(JSON.stringify(selectors));
				alert('\n\n\n'+css.render.selectors(selectors));
						blocks[j] = [ css.render.selectors(selectors), blocks[j] ];
						selectors = '';
					}
					
					if(blocks[j].length > 0)
					{						
						blocks[++j] = '';
					}
				}
				else
				{
					blocks[j] += _value[i];
				}
			}
			else if(_value[i] === '{')
			{
				if(selectors.length === 0)
				{
					return css.parse.block(_value, _parse, _throw);
				}
				
				open = true;
			}
			else
			{
				selectors += _value[i];
			}
		}

		if(selectors.length > 0)
		{
			if(_throw)
			{
				throw new Error('Invalid CSS _value');
			}
			
			return null;
		}
		else for(var i = blocks.length - 1; i >= 0; --i)
		{
			if(blocks[i].length === 0)
			{
				blocks.splice(i, 1);
			}
		}
		
		if(blocks.length === 0)
		{
			return (_parse ? 0 : '');
		}
		else if(blocks.length === 1 && blocks[0].length === 0)
		{
			return (_parse ? 0 : '');
		}
		
		const result = Object.create(null);
		var block;
		
		for(var i = 0; i < blocks.length; ++i)
		{
			if(! isArray(blocks[i], false))
			{
				if(_throw)
				{
					throw new Error('Invalid result');
				}
				
				return null;
			}
			else if((selectors = blocks[i][0].split(',')).length === 0)
			{
				if(_throw)
				{
					throw new Error('Invalid selector(s)');
				}
				
				return null;
			}
			else if((block = css.parse.block(blocks[i][1], _parse, _throw)) === null)
			{
				return null;
			}

			for(var j = 0; j < selectors.length; ++j)
			{
				result[selectors[j]] = block;
			}
		}
//alert('result:\n\n' + Object.debug(result));

		//
		return result;
	};
	
	css.parse.selectors = (_string, _mark = DEFAULT_MARK_SELECTOR, _throw = DEFAULT_THROW) => {
		if(typeof _string !== 'string')
		{
			if(isArray(_string, true))
			{
				if(_string.length === 0)
				{
					return [];
				}
				else for(var i = 0; i < _string.length; ++i)
				{
					if(typeof _string[i] !== 'string')
					{
						if(_throw)
						{
							throw new Error('Invalid _string selector array');
						}
						
						return null;
					}
					else if((_string[i] = _string[i].trim()).length === 0)
					{
						_string.splice(i--, 1);
					}
				}
				
				return _string;
			}
			else if(_throw)
			{
				throw new Error('Invalid _string argument');
			}
			
			return _string;
		}
		else if((_string = _string.trim()).length === 0)
		{
			return [];
		}
		
		const result = [''];
		var quote = '';
		var hadMark = false;
		
		for(var i = 0, j = 0; i < _string.length; ++i)
		{
			if(quote.length > 0)
			{
				hadMark = false;

				if(_string[i] === quote)
				{
					if(result[j].length > 0)
					{
						result[++j] = '';
					}
					
					quote = '';
				}
				else
				{
					result[j] += _string[i];
				}
			}
			else if(_string[i] === '\'' || _string[i] === '"' || _string[i] === '`')
			{
				quote = _string[i];
			}
			else if(_string[i] === ' ' || _string[i] === ',')
			{
				if(_mark && (result[j].length > 0 || hadMark))
				{
					if(! hadMark)
					{
						result[++j] = _string[i];
					}
					else if(_string[i] === ',' && result[j - 1] === ' ')
					{
						result[j - 1] = ',';
					}
					
					hadMark = true;
				}
					
				if(result[j].length > 0)
				{
					result[++j] = '';
				}
			}
			else if(! _string[i].isEmpty())
			{
				hadMark = false;
				result[j] += _string[i];
			}
		}
		
		return result;
	};
	
	css.parse.selectors.string = (_string, _throw = DEFAULT_THROW) => {
throw new Error('TODO');
	};
	
	//
	css.render = (_item, _throw = DEFAULT_THROW) => {
throw new Error('TODO');
	};
	
	css.render.value = (_item, _throw = DEFAULT_THROW) => {
throw new Error('TODO');
	};
	
	css.render.block = (_item, _throw = DEFAULT_THROW) => {
throw new Error('TODO');
	};

	css.render.code = (_item, _throw = DEFAULT_THROW) => {
throw new Error('TODO');
	};
	
	css.render.selectors = (_item, _sep = DEFAULT_SELECTOR_SEP, _space = DEFAULT_SEP_SPACE, _throw = DEFAULT_THROW) => {
		if(! isArray(_item, true))
		{
			if(typeof _item === 'string')
			{
				return _item.trim();
			}
			else if(_throw)
			{
				throw new Error('Invalid _item argument');
			}
			
			return _item;
		}
		
		var result = '';
		var lastSep = '';
		
		for(var i = 0; i < _item.length; ++i)
		{
			if(typeof _item[i] !== 'string')
			{
				if(_throw)
				{
					throw new Error('Invalid _item argument');
				}
				
				return null;
			}
			else if((_item[i] = _item[i].trim()).length === 0)
			{
				continue;
			}
			
			result += _item[i];
			
			if(i < (_item.length - 1) && typeof _item[i + 1] === 'string' && _item[i + 1].length === 1)
			{
				result += (lastSep = _item[++i]) + ((_space && lastSep !== ' ') ? ' ' : '');
			}
			else if(isString(_sep, false))
			{
				result += (lastSep = _sep) + ((_space && lastSep !== ' ') ? ' ' : '');
			}
			else if(_throw)
			{
				throw new Error('No mark found, and no correct _sep argument defined.. can\'t separate selectors');
			}
			else
			{
				return null;
			}
		}
		
		if(result.length > 0 && lastSep.length > 0)
		{
			result = result.slice(0, -(lastSep.length + ((_space && lastSep !== ' ') ? 1 : 0)));
		}
		
		return result;
	};
	
	//
	const toString = (_value, _parse = DEFAULT_PARSE, _throw = DEFAULT_THROW) => {
		if(typeof _value === 'string')
		{
			return _value.trim();
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
			_value = css.fromArray(_value, _parse, _throw);
		}
		else if(isObject(_value, true))
		{
			_value = css.fromObject(_value, _parse, _throw);
		}
		else if(_throw)
		{
			throw new Error('Unsupported type \'' + typeOf(_value) + '\'');
		}
		else
		{
			return null;//null//undefined/..;
		}

		return _value;
	};
	
	const fromItem = (_value, _parse = DEFAULT_PARSE, _throw = DEFAULT_THROW) => {
		var result;
		
		if(! _parse)
		{
			result = _value;
		}
		else if(typeof _value === 'string')
		{
			result = fromString(_value, _parse, _throw);
		}
		else if(isArray(_value, true))
		{
			result = fromArray(_value, _parse, _throw);
		}
		else if(isObject(_value, true))
		{
			result = fromObject(_value, _parse, _throw);
		}
		else if(_throw)
		{
			throw new Error('Invalid _value (neither String nor Array nor Object)');
		}
		else
		{
			return null;
		}
		
		return result;
	};
	
	const fromString = (_value, _parse = DEFAULT_PARSE, _throw = DEFAULT_THROW) => {
		if(typeof _value !== 'string')
		{
			if(_throw)
			{
				throw new Error('Is not a String');
			}
			
			return _value;
		}
		else if(_parse)
		{
			switch(_value.toLowerCase())
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
				default:
					_value = getValue(_value, _parse, null, _throw);
					break;
			}
		}

		return _value;
	};

	/*const toArray = (_string, _parse = DEFAULT_PARSE, _throw = DEFAULT_THROW) => {
		if(typeof _string !== 'string')
		{
			if(isArray(_string, true))
			{
				//
			}
			else if(_throw)
			{
				throw new Error('Neither _string nor array defined');
			}
			else
			{
				return _string;
			}
		}
		else if(typeof _string === 'string' && (_string = _string.trim()).length === 0)
		{
			return [];
		}
		
		const array = (typeof _string === 'string' ? _string.split('
	};*/
	
	const fromArray = (_array, _render = DEFAULT_PARSE, _throw = DEFAULT_THROW) => {
		if(! isArray(_array, true))
		{
			if(typeof _array === 'string')
			{
				return _array;
			}
			else if(_throw)
			{
				throw new Error('Invalid _array argument');
			}
			
			return _array;
		}
		
		var result;
		
		if(_array.length === 0)
		{
			return '';
		}
		else for(var i = 0; i < _array.length; ++i)
		{
			if(typeof _array[i] !== 'string')
			{
				if(_render)
				{
					result += toString(_array[i], _render, _throw);
				}
				else
				{
					continue;
				}
			}
			else if(_array[i].length === 0)
			{
				continue;
			}
			else if(_array[i].includes(' ') || _array[i].includes('\t') || _array[i].includes('\n') || _array[i].includes('\r'))
			{
				result += _array[i].quote('\'');
			}
			else
			{
				result += _array[i];
			}
			
			result += ' ';
		}
		
		if(result.length > 0 && result.endsWith(' '))
		{
			result = result.slice(0, -1);
		}
		
		return result;
	};
	
	const fromObject = (_object, _parse = DEFAULT_PARSE, _throw = DEFAULT_THROW) => {
		if(! isObject(_value, true))
		{
			if(_throw)
			{
				throw new Error('Is not an Object');
			}
			
			return _object;
		}
		else if(isArray(_object, true))
		{
			return css.fromArray(_object, _parse, _throw);
		}
		else if(! _parse)
		{
			return _object;
		}

		const keys = Object.keys(_value, false, false);
		var result = '';
		
		if(keys.length === 0)
		{
			return result;
		}
		else for(var i = 0; i < keys.length; ++i)
		{
			result += keys[i] + ': ' + toString(_object[keys[i]], _parse, _throw) + '\n';
		}
		
		if(result.length > 0)
		{
			result = result.slice(0, -1);
		}
		
		return result;
	};

	//
	const processValue = (_string, _throw = DEFAULT_THROW) => {
		//
		if(typeof _string !== 'string')
		{
			if(_throw)
			{
				throw new Error('Invalid _string argument');
			}
			
			return null;
		}
		else if((_string = _string.trim()).length === 0)
		{
			return '';
		}

		//
		const result = [''];
		const functions = [];
		var subQuote;
		var quote = '';
		var open = '';
		var func = '';

		for(var i = 0, j = 0, f = 0; i < _string.length; ++i)
		{
			if(quote.length > 0)
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
			else if(open.length > 0)
			{
				if(_string[i] === open)
				{
					if(open === ')' && func.length > 0)
					{
						if(func.length > 0)
						{
							var res = [''];
							subQuote = '';
							
							for(var k = 0, l = 0; k < result[j].length; ++k)
							{
								if(subQuote.length > 0)
								{
									if(result[j][k] === subQuote)
									{
										subQuote = '';
										
										if(res[l].length > 0)
										{
											res[++l] = '';
										}
									}
									else
									{
										res[l] += result[j][k];
									}
								}
								else if(result[j][k] === '\'' || result[j][k] === '"' || result[j][k] === '`')
								{
									subQuote = result[j][k];
									
									if(res[l].length > 0 && res[l][res[l].length - 1] !== ' ')
									{
										res[l] += ' ';
									}
								}
								else if(result[j][k] === ',')
								{
									if(res[l].length > 0 && res[l][res[l].length - 1] !== ' ' && res[l][res[l].length - 1] !== ',')
									{
										res[++l] = '';
									}
								}
								else if(result[j][k].isEmpty())
								{
									if(res[l].length > 0 && res[l][res[l].length - 1] !== ' ' && res[l][res[l].length - 1] !== ',')
									{
										res[++l] = '';
									}
								}
								else if(result[j][k] === '(' && res[l].length > 0)
								{
									if(_throw)
									{
										throw new Error('Invalid CSS property (no sub-functions allowed)');
									}
									
									return null;
								}
								else
								{
									res[l] += result[j][k];
								}
							}
							
							if(res.length > 0)
							{
								for(var k = res.length - 1; k >= 0; --k)
								{
									if(res[k].length === 0)
									{
										res.splice(k, 1);
									}
								}

								result[func] = (res.length === 1 ? res[0] : res);
								result[j] = [ func + '(' + res.length + ')', ... res ];
							}
							else
							{
								result[func] = '';
								result[j] = func + '(0)';
							}
							
							result[++j] = '';
							functions[f++] = func;
							func = '';
						}
						else
						{
							alert('debug');
						}
					}
					else if(result[j].length === 0)
					{
						result.splice(j--, 1);
					}
					else
					{
						result[++j] = '';
					}
					
					open = '';
				}
				else
				{
					result[j] += _string[i];
				}
			}
			else if(_string[i] === ';')
			{
				break;
			}
			else if(_string[i] === '\\')
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
			else if(_string[i] === '\'' || _string[i] === '"' || _string[i] === '`')
			{
				quote = _string[i];
				
				if(result[j].length > 0 && result[j][result[j].length - 1] !== ' ')
				{
					result[j] += ' ';
				}
			}
			else if(_string[i] === '(' && result[j].length > 0)
			{
				func = result[j];
				result[j] = '';
				open = ')';
			}
			else if(_string[i].isEmpty())
			{
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

		//
		const keys = Object.keys(result, false, false);
		const keyLen = keys.length;
		const arrLen = result.length;
		
		if(arrLen === 0 && keyLen === 0)
		{
			return (_parse ? 0 : '');
		}
		else if(arrLen === 1 && keyLen === 0)
		{
			if(typeof result[0] === 'string')
			{
				return result[0].trim();
			}
			
			return result[0];
		}
		else if(arrLen === 0 && keyLen === 1)
		{
			if(typeof result[keys[0]] === 'string')
			{
				return result[keys[0]].trim();
			}
			
			return result[keys[0]];
		}
		else
		{
			for(var i = result.length - 1; i >= 0; --i)
			{
				if(typeof result[i] === 'string')
				{
					if(result[i].length === 0)
					{
						result.splice(i--, 1);
					}
					else
					{
						result[i] = result[i].trim();
					}
				}
				else if(isArray(result[i], true))
				{
					if(result[i].length === 0)
					{
						result.splice(i--, 1);
					}
					else for(var j = result[i].length - 1; j >= 0; --j)
					{
						if(typeof result[i][j] === 'string')
						{
							result[i][j] = result[i][j].trim();
						}
					}
				}
			}
			
			for(const idx in result)
			{
				if(typeof result[idx] === 'string')
				{
					result[idx] = result[idx].trim();
				}
				else if(isArray(result[idx], true))
				{
					if(result[idx].length === 0)
					{
						result[idx] = '';
					}
					else for(var i = result[idx].length - 1; i >= 0; --i)
					{
						if(typeof result[idx][i] === 'string')
						{
							result[idx][i] = result[idx][i].trim();
						}
					}
				}
			}
		}

		//
		return result;
	};

	//

})();
