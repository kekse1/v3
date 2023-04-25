(function()
{

	//
	const DEFAULT_THROW = true;
	const DEFAULT_CHECK = true;
	const DEFAULT_EXTENDED_ALPHABET = true;

	//
	radix = { alphabet: Object.create(null) };

	radix.alphabet.decimal = '0123456789';
	radix.alphabet.lower = 'abcdefghijklmnopqrstuvwxyz';
	radix.alphabet.upper = radix.alphabet.lower.toUpperCase();
	radix.alphabet.chars = (radix.alphabet.lower + radix.alphabet.upper);
	radix.alphabet.whole = (radix.alphabet.decimal + radix.alphabet.chars);

	//
	isRadix = radix.isValid = (_value, _extended = DEFAULT_EXTENDED_ALPHABET) => {
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

	radix.getAlphabet = (_radix, _throw = DEFAULT_THROW) => {
		if(! radix.isValid(_radix))
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
			result = radix.alphabet.chars;
		}
		else if(_radix === 1)
		{
			result = radix.alphabet.lower;
		}
		else if(_radix >= 2 && _radix <= 10)
		{
			result = radix.alphabet.decimal.substr(0, _radix);
		}
		else if(_radix <= radix.alphabet.whole.length)
		{
			result = radix.alphabet.whole.substr(0, _radix);
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