(function()
{

	//
	const DEFAULT_THROW = true;
	const DEFAULT_CHECK = true;
	const DEFAULT_EXTENDED_ALPHABET = false;//true;//TODO erstmal radix conversion implementieren, nochmal..

	//
	radix = { alphabet: Object.create(null) };

	radix.alphabet.decimal = '0123456789';
	radix.alphabet.lower = 'abcdefghijklmnopqrstuvwxyz';
	radix.alphabet.upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	radix.alphabet.half = radix.alphabet.lower + radix.alphabet.upper;
	radix.alphabet.full = radix.alphabet.decimal + radix.alphabet.half;

	//
	isRadix = radix.isValid = radix.isRadix = (_value, _extended = DEFAULT_EXTENDED_ALPHABET) => {
		if(typeof _extended !== 'boolean')
		{
			_extended = DEFAULT_EXTENDED_ALPHABET;
		}

		if(! _extended)
		{
			return (isInt(_value) && (_value >= 2 && _value <= 36));
		}
		else if(isInt(_value))
		{
			return (radix.toPositive(_value) <= 256);
		}
		else if(typeof _value === 'string')
		{
			return (_value.uniq().length >= 2);
		}

		return false;
	};

	radix.toPositive = (_value, _check = DEFAULT_CHECK) => {
		if(isInt(_value))
		{
			if(_value < 0)
			{
				_value = ((-1) - _value);
			}

			if(_check)
			{
				return ((_value <= 256) ? _value : null);
			}

			return _value;
		}

		return null;
	};

	radix.toNegative = (_value, _check = DEFAULT_CHECK) => {
		if(isInt(_value))
		{
			if(_value >= 0)
			{
				_value = ((-1) - _value);
			}

			if(_check)
			{
				return ((_value >= -257) ? _value : null);
			}

			return _value;
		}

		return null;
	};

	radix.getSpecialSigns = (_options) => {
		_options = Object.assign({
			rdx: 10,
			int: false,
			pos: true,
			neg: true,
			exp: null,
			big: false
		}, _options);

		if(isRadix(_options.radix))
		{
			_options.rdx = _options.radix;
		}

		delete _options.radix;

		if(! isRadix(_options.rdx))
		{
			_options.rdx = 10;
		}

		if(_options.big)
		{
			_options.exp = false;
			_options.int = true;
		}
		else if(_options.exp !== 'boolean')
		{
			_options.exp = (_options.rdx === 10);
		}
		else if(_options.rdx !== 10)
		{
			_options.exp = false;
		}

		var result = '';

		if(!_options.int) result += '.';
		if(_options.pos) result += '+';
		if(_options.neg) result += '-';
		if(_options.exp) result += 'e';

		return result;
	};

	//
	// this is to optimize alphabet-includes-checks.
	// zeitkomplexitaet von "O(n^2)" auf "O(n)" reduziert wohl..
	//
	radix.getAlphabetSet = (_radix, _throw = DEFAULT_THROW) => {
		return radix.getAlphabet(_radix, true, _throw);
	};

	radix.checkAlphabetSet = (_string_char_byte, _set_rdx, _throw = DEFAULT_THROW) => {
		if(typeof _string_char_byte !== 'string')
		{
			if(isInt(_string_char_byte))
			{
				_string_char_byte = String.fromCodePoint(_string_char_byte);
			}
			else if(_throw)
			{
				throw new Error('Invalid _string_char_byte argument (no non-empty String)');
			}
			else
			{
				return null;
			}
		}
		else if(_string_char_byte.length === 0)
		{
			if(_throw)
			{
				throw new Error('Expecting correct first argument');
			}

			return null;
		}

		if(! is(_set_rdx, 'Set'))
		{
			if(isRadix(_set_rdx))
			{
				_set_rdx = radix.getAlphabetSet(_set_rdx, _throw);
			}

			if(! is(_set_rdx, 'Set'))
			{
				if(_throw)
				{
					throw new Error('Invalid _set_rdx argument (maybe use \'radix.getAlphabetSet(..)\'?)');
				}

				return null;
			}
		}
		
		if(_set_rdx.size < 2)
		{
			if(_throw)
			{
				throw new Error('The _set_rdx needs at least two elements here');
			}

			return null;
		}

		for(var i = 0; i < _string_char_byte.length; ++i)
		{
			if(! _set_rdx.has(_string_char_byte[i]))
			{
				return false;
			}
		}

		return true;
	};

	//
	radix.getAlphabet = (_radix, _set = false, _throw = DEFAULT_THROW) => {
		if(! radix.isValid(_radix, true))
		{
			if(_throw)
			{
				throw new Error('Invalid _radix argument');
			}

			return null;
		}
		else if(typeof _radix === 'string')
		{
			if((_radix = _radix.uniq()).length < 2)
			{
				if(_throw)
				{
					throw new Error('Invalid _radix argument');
				}

				return null;
			}

			return _radix;
		}

		const negative = (_radix < 0);
		_radix = Math.int(Math.abs(_radix));

		if(negative)
		{
			--_radix;
		}

		var result;

		if(_radix === 0)
		{
			result = radix.alphabet.half;
		}
		else if(_radix === 1)
		{
			result = radix.alphabet.full;
		}
		else if(_radix >= 2 && _radix <= 10)
		{
			result = radix.alphabet.decimal.substr(0, _radix);
		}
		else if(_radix <= radix.alphabet.full.length)
		{
			result = radix.alphabet.full.substr(0, _radix);
		}
		else
		{
			result = '';

			for(var i = 0; i < _radix; ++i)
			{
				result += String.fromCharCode(i);
			}
		}

		if(negative)
		{
			result = result.reverse();
		}

		if(_set)
		{
			const s = new Set();

			for(var i = 0; i < result.length; ++i)
			{
				s.add(result[i]);
			}

			result = s;
		}

		return result;
	};

	//
	Object.defineProperty(radix, 'type', { get: function()
	{
		return [ 'Number', 'Integer', 'Float', 'BigInt' ];
	}});

	radix.parse = (_string, _radix = 10, _type = 'Number', _throw = DEFAULT_THROW) => {
	};

	radix.render = (_value, _radix = 10, _type = 'Number', _throw = DEFAULT_THROW) => {
	};

	//
//throw new Error('TODO');

	//
	/*
	//
	Object.defineProperty(Number, 'parseNumber', { value: function(_string, _radix = 10, _throw = DEFAULT_THROW)
	{
		return radix.parse(_string, _radix, 'Number', _throw);
	}});

	parseNumber = Number.parseNumber.bind(Number);

	Object.defineProperty(Number, 'parseInt', { value: function(_string, _radix = 10, _throw = DEFAULT_THROW)
	{
		return radix.parse(_string, _radix, 'Integer', _throw);
	}});

	parseInt = Number.parseInt.bind(Number);

	Object.defineProperty(Number, 'parseFloat', { value: function(_string, _radix = 10, _throw = DEFAULT_THROW)
	{
		return radix.parse(_string, _radix, 'Float', _throw);
	}});

	parseFloat = Number.parseFloat.bind(Number);

	Object.defineProperty(BigInt, 'parse', { value: function(_string, _radix = 10, _throw = DEFAULT_THROW)
	{
		return radix.parse(_string, _radix, 'BigInt', _throw);
	}});

	parseBigInt = BigInt.parse.bind(BigInt);*/

	//

})();
