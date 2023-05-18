(function()
{

	//
	const DEFAULT_BASE = 1024;
	var __BASE = DEFAULT_BASE;

	//
	Object.defineProperty(window, 'BASE', {
		get: function()
		{
			return __BASE;
		},
		set: function(_value)
		{
			if(isInt(_value) && _value > 0)
			{
				return __BASE = _value;
			}
			
			return __BASE = DEFAULT_BASE;
		}
	});

	//
	window.addEventListener('ready', () => {
		const res = document.getVariable('base', true);

		if(isInt(res) && res > 0)
		{
			return __BASE = res;
		}

		return __BASE = DEFAULT_BASE;
	}, { once: true });

	//
	Object.defineProperty(Math, 'time', { value: function(_value, _int = false, _absolute = true, _relative = true)
	{
		var bigint;

		if(typeof _value === 'bigint')
		{
			_value = Math.abs(_value);
			bigint = true;
		}
		else if(isNumber(_value))
		{
			_value = Math.floor(Math.abs(_value));
			bigint = false;
		}
		else
		{
			throw new Error('Expecting _value as milliseconds');
		}

		//
		const result = Object.null({ value: _value });
		const units = Math.time.UNIT;

		//
		const relative = (_rest = _value) => {
			const big = (typeof _rest === 'bigint');
			const res = Object.create(null);
			const int = (!big && _int);
			var base = (big ? 1n : 1);
			var value = _rest;
			var name;

			for(var i = 0; i < units.length; i++)
			{
				_rest /= base;
				[ name, base ] = units[i];
				if(big) base = BigInt.from(base);
				value = (base === (big ? 1n : 1) ? _rest : (_rest % base));
				res[name] = result[name] = (int ? Math.int(value) : value);
			}

			return res;
		};

		const absolute = (_rest = _value) => {
			const big = (typeof _rest === 'bigint');
			const res = Object.create(null);
			const int = (!big && _int);
			var value, name, base;

			for(var i = 0, mul = (big ? 1n : 1); i < units.length; i++)
			{
				[ name, base ] = units[i];
				value = (_rest / mul);
				res[name + 's'] = result[name + 's'] = (int ? Math.int(value) : value);
				mul *= (big ? BigInt.from(base) : base);
			}

			return res;
		};

		//
		if(_absolute)
		{
			result.absolute = absolute();
		}

		if(_relative)
		{
			result.relative = relative();
		}

		//
		Object.defineProperty(result, 'toObject', { value: (_round = 0) => {
			delete result.value;
			delete result.absolute;
			delete result.relative;

			if(isInt(_round)) for(var idx in result)
			{
				if(! isNumber(result[idx]))
				{
					continue;
				}
				else if(_round === 0)
				{
					result[idx] = Math.int(result[idx]);
				}
				else
				{
					result[idx] = Math.round(result[idx], _round);
				}
			}

			return result;
		}});

		//
		return result;
	}});

	//
	time = Math.time;

	//
	Object.defineProperty(Math.time, 'UNIT', { get: () => {
		return [
			// nanoseconds?? 1000000???
			[ 'millisecond', 1000 ],
			[ 'second', 60 ],
			[ 'minute', 60 ],
			[ 'hour', 24 ],
			[ 'day', 7 ],
			[ 'week', 4 ],
			[ 'month', 12 ],
			[ 'year', 1 ]
		];
	}});

	Object.defineProperty(Math.time, 'unit', { get: () => {
		const unit = Math.time.UNIT;
		const result = new Array(unit.length);

		for(var i = 0; i < unit.length; ++i)
		{
			result[i] = unit[i][0];
		}

		return result;
	}});

	Object.defineProperty(Math.time, 'short', { get: () => {
		return [ 'ms', 'sec', 'm', 'h', 'd', 'w', 'mon', 'y' ];
	}});

	(function()
	{
		var MUL = 1;

		for(var i = 0; i < Math.time.UNIT.length; i++)
		{
			const [ name, base ] = Math.time.UNIT[i];
			const mul = Math.time.UNIT[i][2] = MUL;

			Object.defineProperty(Math.time, (name + 's'), { value: (_value) => {
				if(! isNumber(_value))
				{
					return null;
				}

				return (mul * _value);
			}});

			MUL *= base;
		}
	})();

	Math.time.absolute = (_value, _int = true) => {
		return Math.time(_value, _int, true, false);
	};

	Math.time.relative = (_value, _int = true) => {
		return Math.time(_value, _int, false, true);
	};

	Math.time.from = (_object) => {
		if(! isObject(_object))
		{
			throw new Error('Invalid _object argument');
		}

		const keys = Object.keys(_object, false, false);
		const values = {};

		for(var i = 0; i < keys.length; i++)
		{
			if(keys[i].last() === 's')
			{
				keys[i] = keys[i].pop();
				values[keys[i]] = _object[keys[i] + 's'];
			}
			else
			{
				values[keys[i]] = _object[keys[i]];
			}
		}

		var result = 0;

		for(var i = 0; i < keys.length; i++)
		{
			if(! ((keys[i] + 's') in Math.time))
			{
				continue;
			}

			result += Math.time[keys[i] + 's'](values[keys[i]]);
		}

		return result;
	};

	Math.time.render = (_value, _sep = ' ', _space = true, _short = false) => {
		if(! isNumeric(_value))
		{
			throw new Error('Invalid _value argument');
		}
		else if(typeof _short !== 'boolean')
		{
			throw new Error('Invalid _short argument');
		}
		else if(! _short)
		{
			_space = false;
		}

		if(typeof _sep !== 'string')
		{
			_sep = ' ';
		}

		if(typeof _space !== 'boolean')
		{
			_space = _short;
		}

		const shorts = (_short ? Math.time.short.reverse() : null);
		const units = Math.time.unit.reverse();
		const relative = Math.time(_value, true, false, true);

		var result = '';

		for(var i = 0; i < units.length; ++i)
		{
			if(relative[units[i]] < 1)
			{
				continue;
			}
			else if(shorts)
			{
				result += relative[units[i]] + (_space ? ' ' : '') + shorts[i];
			}
			else
			{
				result += relative[units[i]] + ' ' + units[i] + (Math.int(relative[units[i]]) === 1 ? '' : 's');
			}

			result += _sep;
		}

		return result.slice(0, -_sep.length);
	};

	Math.time.render.short = (_value, _sep = ' ', _space = false) => {
		return Math.time.render(_value, _sep, _space, true);
	};

	//
	const SIZE = Object.create(null);
	SIZE['1000'] = [ 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' ];
	SIZE['1024'] = [ 'B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB' ];
	SIZE[''] = [ 'B', 'K', 'M', 'G', 'T', 'P', 'E', 'Z', 'Y' ];

	const SIZE_RELATIVE_REST = false;
	const SIZE_ABSOLUTE_REST = true;

	Object.defineProperty(Math, 'size', { value: (_size, _int = false, _base, _absolute = true, _relative = true, _relative_float = true) => {
		if(typeof _size === 'string')
		{
			if(_size.length === 0)
			{
				return 0;
			}
			else if(! isInt(_size = Math.size.parse(_size)))
			{
				throw new Error('Invalid _size argument');
			}
		}
		else if(! (isInt(_size) || typeof _size === 'bigint'))
		{
			throw new Error('Expecting numeric/string _size argument');
		}
		else if(_size < 0)
		{
			throw new Error('Negative sizes are not possible here');
		}

		if(isNumber(_base))
		{
			_base = [ _base ];
		}
		else if(isArray(_base))
		{
			for(var i = 0; i < _base.length; i++)
			{
				if(! isNumber(_base[i]))
				{
					_base.splice(i--, 1);
				}
			}

			if(_base.length === 0)
			{
				_base = Math.size.base;
			}
		}
		else
		{
			_base = Math.size.base;
		}

		const rendered = Math.size.render(_size);
		const result = Object.null({
			base: [ ... _base ],
			length: _size,
			string: rendered.toString(false),
			findUnit: Math.size.findUnit
		});

		if(_absolute)
		{
			result.absolute = Object.create(null);
		}

		if(_relative)
		{
			result.relative = Object.create(null);
		}

		const unit = Math.size.unit;
		const getKey = (_base, _index) => {
			if((_base in unit) && (_index < unit[_base].length))
			{
				return unit[_base][_index];
			}

			return _base;
		};
		const keys = (_base) => {
			if(_base in unit)
			{
				return unit[_base].length;
			}

			return 0;
		};

		const relative = (_rest = _size, _base) => {
			const big = (typeof _rest === 'bigint');
			const base = (big ? BigInt.from(_base) : _base);
			const res = Object.create(null);
			const int = ((!big && _int) || !!big);
			var key, had = false;
			var value, index = 0;

			do
			{
				value = (_base === 1 ? _rest : (_rest % base));
				if(typeof (key = getKey(_base, index)) === 'string')
				{
					had = true;
				}
				res[key] = (int ? Math.round(value) : value);
				index++;
				_rest /= base;
			}
			while(_rest >= (big ? 1n : 1));

			if(SIZE_RELATIVE_REST && had && _relative_float && !int)
			{
				const len = keys(_base);

				for(; index < len; index++)
				{
					value = (_base === 1 ? _rest : (_rest % base));
					res[getKey(_base, index)] = (int ? Math.round(value) : value);
					_rest /= base;
				}
			}

			return result.relative[_base] = res;
		};

		const absolute = (_rest = _size, _base) => {
			const big = (typeof _rest === 'bigint');
			const base = (big ? BigInt.from(_base) : _base);
			const res = Object.create(null);
			const int = ((!big && _int) || !!big);
			var mul = (big ? 1n : 1);
			var key, had = false;
			var value = _rest;
			var index = 0;

			do
			{
				if(typeof (key = getKey(_base, index)) === 'string')
				{
					had = true;
				}
				res[key] = (int ? Math.int(value) : value);
				index++;
				mul *= base;
				value = (_rest / mul);
			}
			while(value >= (big ? 1n : 1));

			if(SIZE_ABSOLUTE_REST && had && !int)
			{
				const len = keys(_base);

				for(index, mul *= base; index < len; index++)
				{
					res[getKey(_base, index)] = (int ? Math.int(value) : value);
					value = (_rest / mul);
					mul *= base;
				}
			}

			return result.absolute[_base] = res;
		};

		if(_absolute) for(var i = 0; i < _base.length; i++)
		{
			absolute(_size, _base[i]);
		}

		if(_relative) for(var i = 0; i < _base.length; i++)
		{
			relative(_size, _base[i]);
		}

		if(_relative && !_absolute)
		{
			Object.prototype.assign.call(result, result.relative);

			for(const b in result.relative)
			{
				for(const i in result.relative[b])
				{
					result[i] = result.relative[b][i];
				}
			}
		}
		else if(_absolute && !_relative)
		{
			Object.prototype.assign.call(result, result.absolute);

			for(const b in result.absolute)
			{
				for(const i in result.absolute[b])
				{
					result[i] = result.absolute[b][i];
				}
			}
		}

		return result;
	}});

	//
	size = Math.size;

	//
	Math.size.absolute = (_size, _int = true, _base) => {
		return Math.size(_size, _int, _base, true, false);
	};

	Math.size.relative = (_size, _int = true, _base, _relative_float = false) => {
		return Math.size(_size, _int, _base, false, true, _relative_float);
	};

	const DEFAULT_BASES = [ 1024, 1000 ];
	var BASES = [ 1024, 1000 ];

	Object.defineProperty(Math.size, 'base', {
		get: function()
		{
			return [ ... BASES ];
		},
		set: function(_value)
		{
			if(isArray(_value, true))
			{
				const value = [];

				for(var i = 0, j = 0; i < _value.length; i++)
				{
					if(isNumber(_value[i]))
					{
						value[j++] = _value[i];
					}
				}

				if(value.length > 0)
				{
					return BASES = value;
				}

				return BASES = [ ... DEFAULT_BASES ];
			}
			else if(isNumber(_value))
			{
				return BASES = [ _value ];
			}

			return BASES = DEFAULT_BASES;
		}
	});

	Object.defineProperty(Math.size, 'unit', { get: () => {
		const result = Object.create(null);

		for(var idx in SIZE)
		{
			result[idx] = new Array(SIZE[idx].length);

			for(var i = 0; i < SIZE[idx].length; i++)
			{
				result[idx][i] = SIZE[idx][i];
			}
		}

		return result;
	}});

	getSizes = (_unit, _base_default = BASE) => {
		//
		var unit = Math.size.findUnit(_unit);
		var base = null;
		var index = null;

		//
		if(unit === 'B')
		{
			base = (isNumber(_base_default) ? _base_default : BASE);
			index = 0;
		}
		else
		{
			var stop = false;

			for(var b in Math.size.unit)
			{
				for(var i = 1; i < Math.size.unit[b].length; i++)
				{
					if(Math.size.unit[b][i] === unit)
					{
						if(b === '')
						{
							base = null;
						}
						else
						{
							base = parseInt(b);
						}

						index = i;
						stop = true;
						break;
					}
				}

				if(stop)
				{
					break;
				}
			}
		}

		//
		return [ unit, base, index ];
	};

	Object.defineProperty(Math.size, 'parse', { value: (_value, _unit = null, _base = BASE, _bigint = false, _throw = true) => {
		if(isObject(_value))
		{
			if(typeof _value.unit === 'string')
			{
				_unit = _value.unit;
			}

			if(typeof _value.base === 'number')
			{
				_base = _value.base;
			}

			if(typeof _value.bigint === 'boolean')
			{
				_bigint = _value.bigint;
			}

			if(typeof _value.throw === 'boolean')
			{
				_throw = _value.throw;
			}

			_value = _value.value;
		}

		if(typeof _value === 'number' || typeof _value === 'bigint')
		{
			_value = _value.toString();
		}
		else if(typeof _value === 'string')
		{
			if(_value.length === 0)
			{
				return (_bigint ? 0n : 0);
			}
			else if(! isNaN(_value))
			{
				return Math.int(Number(_value));
			}
		}
		else
		{
			throw new Error('Invalid _value argument');
		}

		if(typeof _unit === 'string')
		{
			_unit = Math.size.findUnit(_unit);
		}
		else
		{
			_unit = null;
		}

		if(typeof _base !== 'number')
		{
			_base = BASE;
		}

		if(typeof _bigint !== 'boolean')
		{
			_bigint = false;
		}

		if(typeof _throw !== 'boolean')
		{
			_throw = true;
		}

		//
		if(_value.length === 0)
		{
			return 0;
		}

		//
		var value = '';
		var unit = (_unit === null ? '' : _unit);
		var unitStarted = false;
		var c;

		for(var i = 0; i < _value.length; i++)
		{
			if(_value[i].isEmpty)
			{
				continue;
			}
			else if(unitStarted)
			{
				if(_unit !== null)
				{
					continue;
				}
				else if(_value[i].isLetter)
				{
					unit += _value[i];
				}
			}
			else if(_value[i].isDecimal(true))
			{
				value += _value[i];
			}
			else if(_value[i].isLetter)
			{
				if(_unit !== null)
				{
					break;
				}

				unit += _value[i];
				unitStarted = true;
			}
		}

		if(value.length === 0)
		{
			return (_bigint ? 0n : 0);
		}
		else if(_bigint)
		{
			value = parseBigInt(value);
		}
		else
		{
			value = parseFloat(value);
		}

		var base, index;
		[ unit, base, index ] = getSizes(unit, _base);

		if(index === 0)
		{
			return value;
		}

		//
		var result = value;

		if(_bigint)
		{
			base = BigInt.from(base);
		}

		for(var i = 0; i < index; i++)
		{
			result *= base;
		}

		return result;
	}});

	Object.defineProperty(Math.size, 'render', { value: (_value, _unit = null, _precision, _base = BASE, _long = true, _throw = true) => {
		//
		if(isObject(_value))
		{
			if('precision' in _value)
			{
				_precision = _value.precision;
			}

			if(isNumber(_value.base))
			{
				_base = _value.base;
			}

			if(typeof _value.long === 'boolean')
			{
				_long = _value.long;
			}

			if(typeof _value.throw === 'boolean')
			{
				_throw = _value.throw;
			}

			_unit = _value.unit;
			_value = _value.value;
		}

		//
		const withUnit = (typeof _unit === 'string' && _unit.length > 0);

		//
		if(isObject(_precision))
		{
			if(!(isInt(_precision.min) && _precision.min >= 0))
			{
				_precision.min = null;
			}

			if(!(isInt(_precision.max) && _precision.max >= 0))
			{
				_precision.max = null;
			}

			if(_precision.min === null && _precision.max === null)
			{
				_precision = null;
			}
		}
		else if(!(isInt(_precision) && _precision >= 0))
		{
			_precision = null;
		}

		if(! isNumber(_base))
		{
			_base = BASE;
		}

		if(typeof _long !== 'boolean')
		{
			_long = true;
		}

		if(typeof _throw !== 'boolean')
		{
			_throw = true;
		}

		if(typeof _value === 'string')
		{
			_value = Math.size.parse(_value, _unit, _base, null, _throw);
		}
		else if(typeof _value === 'bigint')
		{
			_value = Number(_value);
		}
		else if(typeof _value !== 'number')
		{
			throw new Error('Invalid _value argument');
		}
		else
		{
			_unit = Math.size.findUnit(_unit, _throw);
		}

		//
		var value = _value;

		if(typeof value !== 'string' && typeof value !== 'number' && typeof value !== 'bigint')
		{
			if(_throw)
			{
				throw new Error('Invalid _value argument');
			}

			return null;
		}
		else if(typeof _value === 'string' && _value.length === 0)
		{
			return (0 + (withUnit ? 0 : (_long ? ' Bytes' : ' B')));
		}

		if(withUnit)
		{
			const [ unit, base, index ] = getSizes(_unit, _base);

			for(var i = 0; i < index; i++)
			{
				value /= base;
			}

			//
			if(isInt(_precision))
			{
				value = Math.round(value, _precision);
			}
			else if(isObject(_precision))
			{
				if(_precision.max !== null)
				{
					value = Math.round(value, _precision.max);
				}
				else if(_precision.min !== null)
				{
					value = Math.round(value, _precision.min);
				}
			}

			//
			return value;
		}

		const units = ((_base in Math.size.unit) ? Math.size.unit[_base] : Math.size.unit['']);
		var index = 0;

		if(value >= _base)
		{
			while(index < (units.length - 1) && (value /= _base) >= _base)
			{
				index++;
			}

			if(index < (units.length - 1))
			{
				index++;
			}
		}

		//
		const result = [ value, units[index] ];

		if(isInt(_precision))
		{
			result[0] = Math.round(result[0], _precision);
		}
		else if(isObject(_precision))
		{
			if(_precision.max !== null)
			{
				result[0] = Math.round(result[0], _precision.max);
			}
			else if(_precision.min !== null)
			{
				result[0] = Math.round(result[0], _precision.min);
			}
		}

		if(_long && index === 0)
		{
			result[1] += 'yte';

			if(result[0] !== 1)
			{
				result[1] += 's';
			}
		}

		result[2] = _value;
		result[3] = index;

		//
		Object.defineProperty(result, 'toString', { value: (_opts) => {
			const opts = {};

			if(isObject(_opts))
			{
				opts.merge(_opts);
			}
			else if(typeof _opts === 'object' || (isInt(_opts) && _opts >= 0))
			{
				opts.precision = _opts;
			}
			else if(isString(_opts, false))
			{
				opts.language = _opts;
			}

			if(typeof opts.locale !== 'boolean')
			{
				opts.locale = true;
			}

			if(opts.precision !== null && !(isInt(opts.precision) && opts.precision >= 0))
			{
				opts.precision = _precision;
			}

			if(typeof opts.round !== 'boolean')
			{
				opts.round = true;
			}

			if(isInt(opts.space))
			{
				opts.space = String.repeat(opts.space, ' ');
			}
			else if(typeof opts.space !== 'string')
			{
				opts.space = ' ';
			}

			if(! isString(opts.language, false))
			{
				if(isString(opts.lang, false))
				{
					opts.language = opts.lang;
					delete opts.lang;
				}
				else
				{
					opts.language = navigator.language;
				}
			}

			var value = result[0];
			var unit = result[1];

			if(isObject(opts.precision))
			{
				if(opts.precision.max !== null)
				{
					value = Math.round(value, opts.precision.max);
				}
				else if(opts.precision.min !== null)
				{
					value = Math.round(value, opts.precision.min);
				}
			}
			else if(opts.precision !== null)
			{
				value = Math.round(value, opts.precision);
			}
			else if(opts.locale)
			{
				value = value.toLocaleString(opts.language, opts.locale);
			}
			else if(opts.locale)
			{
				const opts = {};

				if(opts.precision !== null)
				{
					if(isInt(opts.precision))
					{
						opts.minimumFractionDigits = opts.precision;
						opts.maximumFractionDigits = opts.precision;
					}
					else
					{
						opts.assign(opts.precision);
					}
				}

				value = value.toLocaleString(opts.language, opts);
			}
			else
			{
				value = value.toString();
			}

			return (value + opts.space + unit);
		}});

		//
		return result;
	}});

	Object.defineProperty(Math.size, 'findUnit', { value: (_unit, _throw = true) => {
		var result;

		if(typeof _unit === 'string')
		{
			switch(_unit = _unit.toLowerCase())
			{
				case '':
				case 'b':
				case 'byte':
				case 'bytes':
					result = 'B';
					break;
				case 'k':
				case 'kb':
					result = 'KB';
					break;
				case 'ki':
				case 'kib':
					result = 'KiB';
					break;
				case 'm':
				case 'mb':
					result = 'MB';
					break;
				case 'mi':
				case 'mib':
					result = 'MiB';
					break;
				case 'g':
				case 'gb':
					result = 'GB';
					break;
				case 'gi':
				case 'gib':
					result = 'GiB';
					break;
				case 't':
				case 'tb':
					result = 'TB';
					break;
				case 'ti':
				case 'tib':
					result = 'TiB';
					break;
				case 'p':
				case 'pb':
					result = 'PB';
					break;
				case 'pi':
				case 'pib':
					result = 'PiB';
					break;
				case 'e':
				case 'eb':
					result = 'EB';
					break;
				case 'ei':
				case 'eib':
					result = 'EiB';
					break;
				case 'z':
				case 'zb':
					result = 'ZB';
					break;
				case 'zi':
				case 'zib':
					result = 'ZiB';
					break;
				case 'y':
				case 'yb':
					result = 'YB';
					break;
				case 'yi':
				case 'yib':
					result = 'YiB';
					break;
				default:
					if(_throw)
					{
						throw new Error('Invalid _unit ~[ b, k, m, g, t, p, e, z, y ]');
					}

					result = 'B';
					break;
			}
		}
		else
		{
			result = 'B';
		}

		return result;
	}});

	//

})();

