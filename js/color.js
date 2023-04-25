(function()
{

	//
	const DEFAULT_THROW = true;
	const DEFAULT_COLOR_MAP = 'json/color.json';
	const DEFAULT_COMPACT_HEX = true;
	const DEFAULT_COMPLEMENT_BASE = 255;

	//
	color = { MAP: null };
	
	Object.defineProperty(color, 'type', { get: function()
	{
		return [ 'value', 'rgba', 'rgb', 'hex', 'array' ];
	}});
	
	Object.defineProperty(color, 'map', { get: function()
	{
		if(! color.MAP)
		{
			return null;
		}
		
		return [ ... color.MAP ];
	}});
	
	//
	color.isValid = (_color) => {
		return (color.typeOf(_color, false).length > 0);
	};

	color.typeOf = (_color, _throw = DEFAULT_THROW) => {
		var result;

		if(isNumber(_color))
		{
			if(_color.isFloat)
			{
				if(_throw)
				{
					throw new Error('Numeric color value may not be a floating point; only integers allowed');
				}
				
				result = '';
			}
			else if(_color >= (2**32))
			{
				if(_throw)
				{
					throw new Error('Maximum numeric color value is ((2**32)-1)');
				}
				
				result = '';
			}
			else if(_color < 0)
			{
				if(_throw)
				{
					throw new Error('Minimum numeric color value is zero');
				}
				
				result = '';
			}
			else
			{
				result = 'value';
			}
		}
		else if(typeof _color !== 'string')
		{
			if(isArray(_color, 3))
			{
				var stop = false;
				
				for(var i = 0; i < _color.length; ++i)
				{
					if(! isInt(_color[i]))
					{
						stop = true;
						break;
					}
				}
				
				if(stop)
				{
					if(_throw)
					{
						throw new Error('Invalid array color (not only Integers set)');
					}

					result = '';
				}
				else
				{
					result = 'array';
				}
			}
			else if(_throw)
			{
				throw new Error('Invalid _color argument (not a String)');
			}
			else
			{
				result = '';
			}
		}
		else if(_color.length === 0)
		{
			if(_throw)
			{
				throw new Error('Invalid _color argument (empty String)');
			}
			
			result = '';
		}
		else
		{
			_color = _color.trim().replaces('  ', '').replaces('\t', '').trim();
		}

		if(! result && typeof _color === 'string')
		{
			if(_color.startsWith('rgba('))
			{
				if(_color[_color.length - 1] === ')')
				{
					result = 'rgba';
				}
				else if(_throw)
				{
					throw new Error('Invalid \'rgba()\' color');
				}
				else
				{
					result = '';
				}
			}
			else if(_color.startsWith('rgb('))
			{
				if(_color[_color.length - 1] === ')')
				{
					result = 'rgb';
				}
				else if(_throw)
				{
					throw new Error('Invalid \'rgb()\' color');
				}
				else
				{
					result = '';
				}
			}
			else if(_color[0] === '#')
			{
				switch(_color.length)
				{
					case 4: case 5: case 7: case 9:
						result = 'hex';
						break;
					default:
						if(_throw)
						{
							throw new Error('Invalid \'hex\' color');
						}

						result = '';
						break;
				}
			}
			else if(! isNaN(_color))
			{
				const num = Number(_color);
				
				if(num.isFloat)
				{
					if(_throw)
					{
						throw new Error('Numeric color value may not be a floating point; only integers allowed');
					}
					
					result = '';
				}
				else if(num >= (2**32))
				{
					if(_throw)
					{
						throw new Error('Maximum numeric color value is ((2**32)-1)');
					}
					
					result = '';
				}
				else if(num < 0)
				{
					if(_throw)
					{
						throw new Error('Minimum numeric color value is zero');
					}
					
					result = '';
				}
				else
				{
					result = 'value';
				}
			}
			else if(_throw)
			{
				throw new Error('Invalid _color argument (type not recognized)');
			}
			else
			{
				result = '';
			}
		}
		else if(! result)
		{
			if(_throw)
			{
				throw new Error('Invalid _color argument (neither String nor Array nor Integer)');
			}

			result = '';
		}
		
		return result;
	};

	color.from = (_color, _throw = DEFAULT_THROW) => {
		//
		const type = color.typeOf(_color, _throw);
		
		if(! type)
		{
			return null;
		}
		
		const result = [];
		
		switch(type)
		{
			case 'rgba':
				return color.from.rgba(_color, _throw);
			case 'rgb':
				return color.from.rgb(_color, _throw);
			case 'hex':
				return color.from.hex(_color, _throw);
			case 'value':
				return color.from.value(_color, _throw);
			case 'array':
				return color.from.array(_color, _throw);
		}
		
		if(_throw)
		{
			throw new Error('Unexpected error (couldn\'t match the color type(s)');
		}

		return null;
	};
	
	color.from.rgba = (_string, _throw = DEFAULT_THROW, _alpha = true) => {
		if(typeof _string !== 'string')
		{
			if(_throw)
			{
				throw new Error('Invalid _string argument');
			}
			
			return null;
		}
		else if(_string.length === 0)
		{
			return null;
		}
		else if(typeof _alpha !== 'boolean')
		{
			if(_string.startsWith('rgba('))
			{
				_alpha = true;
			}
			else if(_string.startsWith('rgb('))
			{
				_alpha = false;
			}
			else if(_throw)
			{
				throw new Error('Can\'t detect type or the color\'s alpha state');
			}
			else
			{
				return null;
			}
		}
		
		_string = _string.remove('\t', null).remove(' ', null).substr((_alpha ? 5 : 4)).slice(0, -1);
		const split = _string.split(',', (_alpha ? 4 : 3), false);
		
		if(split.length < (_alpha ? 4 : 3))
		{
			if(_throw)
			{
				throw new Error('Invalid _string color');
			}
			
			return null;
		}

		const result = new Array(split.length);
		
		for(var i = 0; i < split.length; ++i)
		{
			if(isNaN(split[i]))
			{
				if(_throw)
				{
					throw new Error('Invalid _string color (split[' + i + '] is not a Number)');
				}
				
				return null;
			}
			else
			{
				result[i] = Math.int(Number(split[i]));
			}
		}
		
		return result;
	};
	
	color.from.rgb = (_string, _throw = DEFAULT_THROW) => {
		return color.from.rgba(_string, _throw, false);
	};
	
	color.from.hex = (_string, _throw = DEFAULT_THROW) => {
		if(typeof _string !== 'string')
		{
			if(_throw)
			{
				throw new Error('Invalid _string argument');
			}
			
			return null;
		}
		else if(_string.length === 0)
		{
			return null;
		}
		else if(_string[0] === '#')
		{
			_string = _string.substr(1);
		}
		
		if(_string.length === 3)
		{
			return color.from.hex.short(_string, false, _throw);
		}
		else if(_string.length === 4)
		{
			return color.from.hex.short(_string, true, _throw);
		}
		else if(_string.length === 6)
		{
			return color.from.hex.long(_string, false, _throw);
		}
		else if(_string.length === 8)
		{
			return color.from.hex.long(_string, true, _throw);
		}
		else if(_throw)
		{
			throw new Error('Invalid _string color (wrong length)');
		}

		return null;
	};
	
	color.from.hex.short = (_string, _alpha, _throw = DEFAULT_THROW) => {
		//
		if(_string[0] === '#')
		{
			_string = _string.substr(1);
		}

		if(_string.length === 3)
		{
			if(typeof _alpha !== 'boolean')
			{
				_alpha = false;
			}
		}
		else if(_string.length === 4)
		{
			if(typeof _alpha !== 'boolean')
			{
				_alpha = true;
			}
		}
		else if(_throw)
		{
			throw new Error('Invalid _string argument (wrong length)');
		}
		else
		{
			return null;
		}
		
		//
		const result = new Array(_alpha ? 4 : 3);
		var component;
		
		for(var i = 0, j = 0; i < (_alpha ? 4 : 3); ++i)
		{
			if(isNaN(component = parseInt(_string[i] + _string[i], 16)))
			{
				if(_throw)
				{
					throw new Error('Couldn\'t parse component[' + i + ' & ' + (i + 1) + ']');
				}
				
				return null;
			}
			else
			{
				result[i] = component;
			}
		}
		
		return result;
	};
	
	color.from.hex.long = (_string, _alpha, _throw = DEFAULT_THROW) => {
		//
		if(_string[0] === '#')
		{
			_string = _string.substr(1);
		}
		
		if(_string.length === 6)
		{
			if(typeof _alpha !== 'boolean')
			{
				_alpha = false;
			}
		}
		else if(_string.length === 8)
		{
			if(typeof _alpha !== 'boolean')
			{
				_alpha = true;
			}
		}
		else if(_throw)
		{
			throw new Error('Invalid _string argument (wrong length)');
		}
		else
		{
			return null;
		}

		//
		const result = new Array(_alpha ? 4 : 3);
		var component;
		
		for(var i = 0, j = 0; i < _string.length; i += 2, ++j)
		{
			if(isNaN(component = parseInt(_string[i] + _string[i + 1], 16)))
			{
				if(_throw)
				{
					throw new Error('Couldn\'t parse component[' + i + ']');
				}
				
				return null;
			}
			else
			{
				result[j] = component;
			}
		}
		
		return result;
	};
	
	color.from.value = (_string_number, _throw = DEFAULT_THROW) => {
		var value;
		
		if(typeof _string_number === 'string')
		{
			if(isNaN(_string_number))
			{
				if(_throw)
				{
					throw new Error('Invalid _string_number argument (not a valid numeric String)');
				}
				
				return null;
			}
			else if((value = Number(_string_number)).isFloat)
			{
				value = Math.int(value);
			}
		}
		else if(isNumber(_string_number))
		{
			value = Math.int(_string_number);
		}
		else if(_throw)
		{
			throw new Error('Invalid _string_number argument');
		}
		else
		{
			return null;
		}
		
		if(value >= (2**32))
		{
			if(_throw)
			{
				throw new Error('Value is too high (maximum is ((2**32)-1))');
			}
			
			return null;
		}
		else if(value < 0)
		{
			if(_throw)
			{
				throw new Error('Value is too low (minimum is zero)');
			}
			
			return null;
		}
		
		const hasAlpha = (value >= (2**24));
		const result = new Array(hasAlpha ? 4 : 3);
		var rest = value;
		
		for(var i = result.length - 1; i >= 0; --i)
		{
			result[i] = (rest % 256);
			rest = Math.int(rest / 256);
		}
		
		if(rest >= 256)
		{
			throw new Error('Unexpected');
		}
		else if(rest >= 1 && result.length < (hasAlpha ? 4 : 3))
		{
			result.unshift(rest);
		}
		
		return result;
	};
	
	color.from.array = (_color, _throw = DEFAULT_THROW) => {
		if(! isArray(_color, 3))
		{
			if(_throw)
			{
				throw new Error('Invalid _color argument (not an Array with three or four byte values)');
			}
			
			return null;
		}
		else if(_color.length > 4)
		{
			_color.length = 4;
		}
		
		for(var i = 0; i < _color.length; ++i)
		{
			if(isInt(_color[i]))
			{
				if(_color[i] < 0)
				{
					_color[i] = 0;
				}
				else if(_color[i] > 255)
				{
					_color[i] %= 256;
				}
			}
			else if(_throw)
			{
				throw new Error('Invalid _color argument (Array contains not only Integers)');
			}
			else
			{
				return null;
			}
		}
		
		return [ ... _color ];
	};
	
	//
	color.rgba = (_color, _throw = DEFAULT_THROW, _alpha = true) => {
		//
		const array = color.from(_color, _throw);
		
		if(array.length === 3)
		{
			if(typeof _alpha !== 'boolean')
			{
				_alpha = false;
			}
		}
		else if(array.length === 4)
		{
			if(typeof _alpha !== 'boolean')
			{
				_alpha = true;
			}
		}
		else if(_throw)
		{
			throw new Error('Invalid array calculated (wrong length)');
		}
		else
		{
			return null;
		}
		
		//
		var result = (_alpha ? 'rgba(' : 'rgb(');
		
		//
		for(var i = 0; i < array.length; ++i)
		{
			result += array[i].toString(10) + ', ';
		}
		
		//
		if(_alpha && array.length === 3)
		{
			result += '255, ';
		}
		
		//
		return (result.slice(0, -2) + ')');
	};

	color.rgb = (_color, _throw = DEFAULT_THROW) => {
		return color.rgba(_color, _throw, false);
	};

	color.hex = (_color, _compact = DEFAULT_COMPACT_HEX, _throw = DEFAULT_THROW) => {
		//
		const array = color.from(_color, _throw);
		var result = '#';
		
		//
		for(var i = 0; i < array.length; ++i)
		{
			result += array[i].toString(16).padStart(2, '0');
		}

		//
		if(_compact)
		{
			var tmp = '';

			for(var i = 1; i < result.length; i += 2)
			{
				if(result[i] === result[i + 1])
				{
					tmp += result[i];
				}
				else
				{
					tmp = null;
					break;
				}
			}
			
			if(tmp)
			{
				result = '#' + tmp;
			}
		}
		
		//
		return result;
	};
	
	color.value = (_color, _throw = DEFAULT_THROW) => {
		//
		const array = color.from(_color, _throw);
		var result = 0;
		
		for(var i = array.length - 1, j = 1; i >= 0; --i, j *= 256)
		{
			result += (array[i] * j);
		}
		
		return result;
	};
	
	color.array = (_color, _throw = DEFAULT_THROW) => {
		return color.from(_color, _throw);
	};

	//
	color.textColor = (_color, _type, _throw = DEFAULT_THROW, ... _args) => {
		//
		if(! isString(_type, false))
		{
			if(! (_type = color.typeOf(_color, _throw)))
			{
				if(_throw)
				{
					throw new Error('Invalid _type argument');
				}

				return null;
			}
		}
		
		if(typeof color.textColor[_type = _type.toLowerCase()] !== 'function')
		{
			throw new Error('Invalid _type argument [supported types in \'color.type[]\']');
		}

		//
		_color = color.from(_color, _throw);

		//
		const contrast = { black: ((299 * _color[0] + 587 * _color[1] + 114 * _color[2]) / 1000 / 255),
			white: ((299 * (255 - _color[0]) + 587 * (255 - _color[1]) + 114 * (255 - _color[2])) / 1000 / 255) };
		
		//	
		if(contrast.black >= contrast.white)
		{
			return color[_type]('#000', _throw, ... _args);
		}
		
		return color[_type]('#fff', _throw, ... _args);
	};

	color.textColor.value = (_color, _throw = DEFAULT_THROW) => {
		return color.textColor(_color, 'value', _throw);
	};

	color.textColor.rgba = (_color, _throw = DEFAULT_THROW) => {
		return color.textColor(_color, 'rgba', _throw);
	};

	color.textColor.rgb = (_color, _throw = DEFAULT_THROW) => {
		return color.textColor(_color, 'rgb', _throw);
	};

	color.textColor.hex = (_color, _throw = DEFAULT_THROW) => {
		return color.textColor(_color, 'hex', _throw);
	};

	color.textColor.array = (_color, _throw = DEFAULT_THROW) => {
		return color.textColor(_color, 'array', _throw);
	};
	//
	color.contrast = (_color, _type, _throw = DEFAULT_THROW, ... _args) => {
		//
		if(! isString(_type, false))
		{
			if(! (_type = color.typeOf(_color, _throw)))
			{
				if(_throw)
				{
					throw new Error('Invalid _type argument');
				}

				return null;
			}
		}
		
		if(typeof color.contrast[_type = _type.toLowerCase()] !== 'function')
		{
			throw new Error('Invalid _type argument [supported types in \'color.type[]\']');
		}

/*#  0.2989 *R + 0.5870 *G + 0.1140 *B    # [1] NTSC
		#  0.2126 *R + 0.7152 *G + 0.0722 *B    # [2] luminance signal EY
		#  0.2627 *R + 0.6780 *G + 0.0593 *B    # [3] UHDTV
		[1] BT.601 https://www.itu.int/rec/R-REC-BT.601-7-201103-I/en
		[2] BT.709 https://www.itu.int/rec/R-REC-BT.709-6-201506-I/en
		[3] BT.2020 https://www.itu.int/rec/R-REC-BT.2020-2-201510-I/en
*/		//
		_color = color.from(_color, _throw);
		const NTSC = { red: 0.299, green: 0.587, blue: 0.114 };
		return color[_type](Math.round(128 - ((_color[0] * NTSC.red + _color[1] * NTSC.green + _color[2] * NTSC.blue) / 2)), _throw, ... _args);
	};

	color.contrast.value = (_color, _throw = DEFAULT_THROW) => {
		return color.contrast(_color, 'value', _throw);
	};

	color.contrast.rgba = (_color, _throw = DEFAULT_THROW) => {
		return color.contrast(_color, 'rgba', _throw);
	};

	color.contrast.rgb = (_color, _throw = DEFAULT_THROW) => {
		return color.contrast(_color, 'rgb', _throw);
	}

	color.contrast.hex = (_color, _throw = DEFAULT_THROW, _compact = DEFAULT_COMPACT_HEX) => {
		return color.contrast(_color, 'hex', _throw, _compact);
	};

	color.contrast.array = (_color, _throw = DEFAULT_THROW) => {
		return color.contrast(_color, 'array', _throw);
	};

	color.complement = (_color, _type, _throw = DEFAULT_THROW, ... _args) => {
		if(! isString(_type, false))
		{
			if(_type = color.typeOf(_color, _throw))
			{
				//
			}
			else if(_throw)
			{
				throw new Error('Invalid _type argument');
			}
			else
			{
				return null;
			}
		}

		if(typeof color.complement[_type = _type.toLowerCase()] !== 'function')
		{
			throw new Error('Invalid _type argument [supported types in \'color.type[]\']');
		}

		_color = color.from(_color, _throw);

		for(var i = 0; i < _color.length; ++i)
		{
			_color[i] = (DEFAULT_COMPLEMENT_BASE - _color[i]);
		}
		
		return color[_type](_color, _throw, ... _args);
	};
	
	color.complement.value = (_color, _throw = DEFAULT_THROW) => {
		return color.complement(_color, 'value', _throw);
	};
	
	color.complement.rgba = (_color, _throw = DEFAULT_THROW) => {
		return color.complement(_color, 'rgba', _throw);
	};
	
	color.complement.rgb = (_color, _throw = DEFAULT_THROW) => {
		return color.complement(_color, 'rgb', _throw);
	};
	
	color.complement.hex = (_color, _throw = DEFAULT_THROW, _compact = DEFAULT_COMPACT_HEX) => {
		return color.complement(_color, 'hex', _throw, _compact);
	};
	
	color.complement.array = (_color, _throw = DEFAULT_THROW) => {
		return color.complement(_color, 'array', _throw);
	};

	//
	window.addEventListener('ready', () => {
		require(DEFAULT_COLOR_MAP, (_e) => {
			if(isArray(_e.module, false))
			{
				color.MAP = _e.module;
			}
			else if(DEFAULT_THROW)
			{
				throw new Error('Couldn\'t load the \'color.map\' .json array (HTTP ' + _e.status + ': ' + _e.statusText + ')');
			}
			else
			{
				return null;
			}

			window.emit('color', { type: 'color', map: [ ... color.MAP ] });
			return color.MAP;
		}, null, false, false, null, null);
	}, { once: true });

	//
	
})();

