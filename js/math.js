(function()
{

	//
	const DEFAULT_BASE = 1024;
	const DEFAULT_PRECISION = { min: 0, max: 2 };//null;
	const DEFAULT_LONG = true;
	const DEFAULT_KNUTH = true;

	//
	const SIZE = Object.create(null);
	SIZE['1000'] = [ 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' ];
	SIZE['1024'] = [ 'B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB' ];
	SIZE[''] = [ 'B', 'K', 'M', 'G', 'T', 'P', 'E', 'Z', 'Y' ];

	//
	Math.size = (_value, _precision = DEFAULT_PRECISION, _base = DEFAULT_BASE, _long = DEFAULT_LONG) => {
		//
		if(isObject(_value))
		{
			if('precision' in _value)
			{
				_precision = _value.precision;
			}

			if(isInt(_value.base))
			{
				_base = _value.base;
			}
			
			if(typeof _value.long === 'boolean')
			{
				_long = _value.long;
			}

			_value = _value.value;
		}
		
		//
		if(! (isInt(_value) && _value >= 0))
		{
			throw new Error('Invalid _value (not a positive Integer)');
		}

		//
		if(isObject(_precision))
		{
			if(_precision.min !== null && !(isInt(_precision.min) && _precision.min >= 0))
			{
				_precision.min = null;
			}

			if(_precision.max !== null && !(isInt(_precision.max) && _precision.max >= 0))
			{
				_precision.max = null;
			}

			if(_precision.min === null && _precision.max === null)
			{
				_precision = undefined;
			}
		}
		else if(isInt(_precision) && _precision >= 0)
		{
			_precision = { min: _precision, max: _precision };
		}
		else
		{
			_precision = DEFAULT_PRECISION;
		}
		
		if(typeof _precision === 'undefined')
		{
			if(isInt(_precision = DEFAULT_PRECISION) && _precision >= 0)
			{
				_precision = { min: _precision, max: _precision };
			}
			else
			{
				_precision = null;
			}
		}

		if(! isInt(_base))
		{
			_base = DEFAULT_BASE;
		}

		if(typeof _long !== 'boolean')
		{
			_long = DEFAULT_LONG;
		}

		//
		if(_value < _base)
		{
			return (_value + (_long ? (' Byte' + (_value === 1 ? '' : 's')) : ' B'));
		}

		const units = ((_base in Math.size.unit) ? Math.size.unit[_base] : Math.size.unit['']);
		var index = 0;

		if(_value >= _base)
		{
			while(index < (units.length - 1) && (_value /= _base) >= _base)
			{
				index++;
			}

			if(index < (units.length - 1))
			{
				index++;
			}
		}

		//
		var unit = units[index];
		
		if(_precision !== null)
		{
			const opts = {};
			
			if(_precision.min !== null)
			{
				opts.minimumFractionDigits = _precision.min;
			}
			
			if(_precision.max !== null)
			{
				opts.maximumFractionDigits = _precision.max;
			}
			
			_value = _value.toLocaleString(LANGUAGE, opts);
		}
		else
		{
			_value = _value.toLocaleString(LANGUAGE);
		}
		
		if(_long && index === 0)
		{
			unit += 'yte';
			
			if(_value !== 1)
			{
				unit += 's';
			}
		}
		
		//
		return (_value + ' ' + unit);
	};

	Object.defineProperty(Math.size, 'unit', { get: function()
	{
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

	//
	const _round = Math.round;
	const _floor = Math.floor;
	const _ceil = Math.ceil;

	Math.round = (_value, _precision = 0) => {
		if(! isNumeric(_value))
		{
			throw new Error('Invalid _value argument');
		}
		else if(typeof _value === 'bigint')
		{
			return _value;
		}

		if(! (isInt(_precision) && _precision >= 0))
		{
			_precision = 0;
		}

		const coefficient = Math.pow(10, _precision);
		return ((_round(_value * coefficient) / coefficient) || 0);
	};

	Math.floor = (_value, _precision = 0) => {
		if(! isNumeric(_value))
		{
			throw new Error('Invalid _value argument');
		}
		else if(typeof _value === 'bigint')
		{
			return _value;
		}

		if(! (isInt(_precision) && _precision >= 0))
		{
			_precision = 0;
		}

		const coefficient = Math.pow(10, _precision);
		return ((_floor(_value * coefficient) / coefficient) || 0);
	};

	Math.ceil = (_value, _precision = 0) => {
		if(! isNumeric(_value))
		{
			throw new Error('Invalid _value argument');
		}
		else if(typeof _value === 'bigint')
		{
			return _value;
		}

		if(! (isInt(_precision) && _precision >= 0))
		{
			_precision = 0;
		}

		const coefficient = Math.pow(10, _precision);
		return ((_ceil(_value * coefficient) / coefficient) || 0);
	};

	Math.int = (_value, _precision = 0, _inverse = false) => {
		if(! isNumeric(_value))
		{
			throw new Error('Invalid _value argument');
		}
		else if(typeof _value === 'bigint')
		{
			return _value;
		}

		if(! (isInt(_precision) && _precision >= 0))
		{
			_precision = 0;
		}

		if(typeof _inverse !== 'boolean')
		{
			_inverse = false;
		}

		const a = (_value < 0);
		const b = (_inverse);

		return (((((a&&b)||!(a||b)) ? Math.floor : Math.ceil)(_value, _precision)) || 0);
	};

	//
	Math.min = (... _args) => {
		for(var i = 0; i < _args.length; ++i)
		{
			if(! isNumeric(_args[i]))
			{
				_args.splice(i--, 1);
			}
		}
		
		if(_args.length === 0)
		{
			return null;
		}
		
		var result = _args.shift();
		var orig;
		
		for(var i = 0; i < _args.length; ++i)
		{
			if(typeof result === 'bigint')
			{
				orig = _args[i];

				if(typeof _args[i] !== 'bigint')
				{
					_args[i] = BigInt.from(_args[i]);
				}
				
				if(_args[i] < result)
				{
					result = orig;
				}
			}
			else if(typeof _args[i] === 'bigint')
			{
				orig = _args[i];
				_args[i] = Number.from(_args[i]);
				
				if(_args[i] < result)
				{
					result = orig;
				}
			}
			else if(_args[i] < result)
			{
				result = _args[i];
			}
		}
		
		return (result || 0);
	};
	
	Math.max = (... _args) => {
		for(var i = 0; i < _args.length; ++i)
		{
			if(! isNumeric(_args[i]))
			{
				_args.splice(i--, 1);
			}
		}
		
		if(_args.length === 0)
		{
			return null;
		}

		var result = _args.shift();
		var orig;
				
		for(var i = 0; i < _args.length; ++i)
		{
			if(typeof result === 'bigint')
			{
				orig = _args[i];
				
				if(typeof _args[i] !== 'bigint')
				{
					_args[i] = BigInt.from(_args[i]);
				}
				
				if(_args[i] > result)
				{
					result = orig;
				}
			}
			else if(typeof _args[i] === 'bigint')
			{
				orig = _args[i];
				_args[i] = Number.from(_args[i]);
				
				if(_args[i] > result)
				{
					result = orig;
				}
			}
			else if(_args[i] > result)
			{
				result = _args[i];
			}
		}
		
		return (result || 0);
	};

	//
	Math.random.int = (_max = (2**32) - 1, _min = 0, _max_inclusive = true) => {
		if(! isNumber(_max))
		{
			_max = (2**32)-1;
		}

		if(! isNumber(_min))
		{
			_min = 0;
		}

		if(typeof _max_inclusive !== 'boolean')
		{
			_max_inclusive = true;
		}

		const min = Math.int(Math.min(_min, _max));
		const max = Math.int(Math.max(_max, _min));

		return ((Math.floor(Math.random() * (max - min + (_max_inclusive ? 1 : 0))) + min) || 0);
	};

	Math.random.float = (_max = (2**32) - 1, _min = 0, _max_inclusive = true) => {
		if(! isNumber(_max))
		{
			_max = (2**32)-1;
		}

		if(! isNumber(_min))
		{
			_miin = 0;
		}

		if(typeof _max_inclusive !== 'boolean')
		{
			_max_inclusive = true;
		}

		const min = Math.min(_min, _max);
		const max = Math.max(_max, _min);

		return ((Math.random() * (max - min) + min) || 0);
	};

	Math.random.byte = () => {
		return Math.random.int(255, 0);
	};

	Math.random.bool = () => {
		return (Math.random() >= 0.5);
	};

	//
	Math.psin = (_value) => {
		if(! isNumber(_value))
		{
			throw new Error('Invalid _value argument (not a Number)');
		}

		return (((Math.sin(_value) + 1) / 2) || 0);
	};

	Math.pcos = (_value) => {
		if(! isNumber(_value))
		{
			throw new Error('Invalid _value argument (not a Number)');
		}

		return (((Math.cos(_value) + 1) / 2) || 0);
	};

	//
	Math.sin.deg = (_degrees) => {
		return (Math.sin(Math.deg2rad(_degrees)) || 0);
	};

	Math.cos.deg = (_degrees) => {
		return (Math.cos(Math.deg2rad(_degrees)) || 0);
	};

	Math.tan.deg = (_degrees) => {
		return (Math.tan(Math.deg2rad(_degrees)) || 0);
	};

	//
	Math.deg2rad = (_degrees) => {
		if(! isNumber(_degrees))
		{
			throw new Error('Invalid _degrees argument (not a Number)');
		}

		return ((_degrees * Math.PI / 180) || 0);
	};

	Math.rad2deg = (_radians) => {
		if(! isNumber(_radians))
		{
			throw new Error('Invalid _radians argument (not a Number)');
		}

		return ((_radians * 180 / Math.PI) || 0);
	};

	//
	Math.fraction = (_float, _radix = 10, _tolerance = 1.0E-6) => {
		//
		if(! isNumber(_float))
		{
			throw new Error('Invalid _float argument (not a Number)');
		}
		else if(_float < 0)
		{
			return ('-' + Math.fraction(-_float, _radix, _tolerance));
		}
		else if(isInt(_float))
		{
			return (_float.toString(_radix) + '/1');
		}

		//
		if(! isNumber(_tolerance))
		{
			_tolerance = 1.0E-6;
		}
		
		//
		var h1 = 1, h2 = 0;
		var k1 = 0, k2 = 1;
		var b = _float;

		do
		{
			var a = Math.floor(b);
			var aux = h1;

			h1 = a * h1 + h2;
			h2 = aux;
			aux = k1;

			k1 = a * k1 + k2;
			k2 = aux;

			b = 1 / (b - a);
		}
		while(Math.abs(_float - h1 / k1) > _float * _tolerance);

		//
		return (h1.toString(_radix) + '/' + k1.toString(_radix));
	};

	//
	Math.scale = (_value, _max, _min) => {
		if(! isNumber(_value))
		{
			throw new Error('Invalid _value argument (not a Number)');
		}
		
		if(! isNumber(_max))
		{
			_max = 1;
		}
		
		if(! isNumber(_min))
		{
			_min = 0;
		}

		return (((_value * (_max - _min)) + _min) || 0);
	};
	
	//
	Math.getIndex = (_index, _length) => {
		if(isFloat(_index))
		{
			_index = Math.int(_index);
		}
		else if(! isInt(_index))
		{
			throw new Error('Invalid _index argument');
		}
		
		if(isFloat(_length))
		{
			_length = Math.int(_length);
		}
		else if(! isInt(_length))
		{
			throw new Error('Invalid _length argument');
		}
		
		if(_length < 1)
		{
			return null;
		}
		else if((_index %= _length) < 0)
		{
			_index = ((_length + _index) % _length);
		}
		
		return (_index || 0);
	};

	//
	const bigIntLogBase = (_base, _value) => {
		var result = 0;
		var rest = _value;

		while(rest >= _base)
		{
			rest /= _base;
			++result;
		}

		return (result || 0);
	}

	Math.logBase = (_base, _value) => {
		if(! isNumeric(_base))
		{
			throw new Error('Invalid _base argument');
		}

		if(typeof _value === 'bigint')
		{
			return bigIntLogBase(BigInt(_base), _value);
		}
		else if(isNumber(_value))
		{
			_base = Number(_base);
		}
		else
		{
			throw new Error('The _value argument needs to be a Number or BigInt');
		}

		return ((Math.log(_value) / Math.log(_base)) || 0);
	};

	//
	Math.gcd = (_a, _b, _knuth = DEFAULT_KNUTH) => {
		//
		if(! isNumeric(_a))
		{
			throw new Error('Invalid _a argument (not numeric)');
		}
		else if(! isNumeric(_b))
		{
			throw new Error('Invalid _b argument (not numeric)');
		}
		else if(typeof _a !== typeof _b)
		{
			throw new Error('Invalid _a/_b arguments (not the same types)');
		}
		else if(typeof _knuth !== 'boolean')
		{
			_knuth = DEFAULT_KNUTH;
		}

		//
		if(_knuth)
		{
			return Math.gcd.binary(_a, _b);
		}

		return Math.gcd.euclidean(_a, _b);
	};

	// greatest common divisor - iterative function (binary algorithm, aka Stein's algorithm)
	Math.gcd.binary = (_a, _b) => {
		//
		const bigint = (typeof _a === 'bigint' && typeof _b === 'bigint');
		const zero = (bigint ? 0n : 0);
		const one = (bigint ? 1n : 1);

		//
		if(_a === zero)
		{
			return (_b || 0);
		}
		else if(_b === zero)
		{
			return (_a || 0);
		}

		var shift = zero;
		while((_a & one) === zero && (_b & one) === zero)
		{
			_a >>= one;
			_b >>= one;
			++shift;
		}

		while((_a & one) === zero)
		{
			_a >>= one;
		}

		do
		{
			while((_b & one) === zero)
			{
				_b >>= one;
			}

			if(_a > _b)
			{
				[ _a, _b ] = [ _b, _a ];
			}

			_b -= _a;
		}
		while(_b !== zero)

		return ((_a << shift) || 0);
	};

	Math.gcd.knuth = Math.gcd.binary;

	// greatest common divisor - recursive function (euclidean algorithm)
	Math.gcd.euclidean = (_a, _b) => {
		const bigint = (typeof _a === 'bigint' && typeof _b === 'bigint');
		const zero = (bigint ? 0n : 0);

		if(_a === zero)
		{
			return (_b || 0);
		}
		else if(_b === zero)
		{
			return (_a || 0);
		}

		return (Math.gcd.euclidean(_b, _a % _b) || 0);
	};

	// least common multiple
	Math.lcm = (_a, _b, _knuth = DEFAULT_KNUTH) => {
		if(! isNumeric(_a))
		{
			throw new Error('Invalid _a argument (not numeric)');
		}
		else if(! isNumeric(_b))
		{
			throw new Error('Invalid _b argument (not numeric)');
		}
		else if(typeof _a !== typeof _b)
		{
			throw new Error('Invalid _a/_b arguments (not the same types)');
		}
		else if(typeof _knuth !== 'boolean')
		{
			_knuth = DEFAULT_KNUTH;
		}

		const func = (_knuth ? Math.gcd.knuth : Math.gcd.euclidean);
		return (((_a * _b) / func(_a, _b)) || 0);
	};

	//
	
})();

