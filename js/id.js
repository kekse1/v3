(function()
{

	//
	const DEFAULT_CRYPTO = false;
	const DEFAULT_SEP = '/';

	//
	id = uuid = {};

	//
	randomID = id.random = id.create = (_crypto = DEFAULT_CRYPTO, _radix = 36, _sep = DEFAULT_SEP) => {
		//
		if(typeof _crypto !== 'boolean')
		{
			_crypto = DEFAULT_CRYPTO;
		}

		if(! isInt(_radix))
		{
			_radix = 36;
		}

		if(typeof _sep !== 'string' || _sep.length === 0)
		{
			_sep = DEFAULT_SEP;
		}

		//
		var result = uuid.random(_crypto);
		return (result + _sep + Date.now().toString(_radix));
	};

	isID = id.isID = (_string) => {
		if(typeof _string !== 'string')
		{
			return false;
		}
		else if(_string.length <= 36)
		{
			return false;
		}

		return uuid.isUUID(_string.substr(0, 36));
	};

	isUUID = uuid.isUUID = (_string) => {
		if(typeof _string !== 'string')
		{
			return false;
		}
		else if(_string.length !== 36)
		{
			return false;
		}

		const scheme = [ ... uuid.scheme ];
		const symbol = _string[scheme[0]];

		if(typeof symbol !== 'string')
		{
			return false;
		}
		else
		{
			const p = parseInt(symbol, uuid.radix);

			if(! isNaN(p))
			{
				p = false;
			}
		}

		for(var i = 1, mul = scheme[0]; i < scheme.length - 1; ++i)
		{
			if(_string[(mul += scheme[i]) + i] !== symbol)
			{
				return false;
			}
		}

		var string = '';

		for(var i = 0, mul = 0; i < scheme.length; ++i)
		{
			string += _string.substr(mul + i, scheme[i]);
			mul += scheme[i];
		}

		const isValidRadixChar = (_char, _radix = uuid.radix) => {
			return !isNaN(parseInt(_char, _radix));
		};

		for(var i = 0; i < string.length; ++i)
		{
			if(! isValidRadixChar(string[i]))
			{
				return false;
			}
		}

		return true;
	};

	//
	randomUUID = uuid.random = uuid.create = (_crypto = DEFAULT_CRYPTO, ... _args) => {
		if(typeof _crypto !== 'boolean')
		{
			_crypto = DEFAULT_CRYPTO;
		}

		if(_crypto && typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function')
		{
			return crypto.randomUUID(... _args);
		}

		var result = '';

		for(var i = 0; i < uuid.scheme.length; ++i)
		{
			for(var j = 0; j < uuid.scheme[i]; ++j)
			{
				result += uuid.alphabet[Math.random.int(uuid.alphabet.length, 0, false)];
			}

			result += uuid.sep;
		}

		return result.slice(0, -1);
	};

	//
	uuid.alphabet = '0123456789abcdef';
	uuid.scheme = [ 8, 4, 4, 4, 12 ];
	uuid.radix = 16;
	uuid.sep = '-';

	//

})();

