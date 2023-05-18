(function()
{

	//
	const DEFAULT_THROW = true;

	//
	address = {};
	network = { address };

	//
	address.isLocalhost = (_string, _radix, _throw = DEFAULT_THROW) => {
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
			return false;
		}
		else if(address.isHost(_string, _throw))
		{
			if(_string === 'localhost' || _string.startsWith('localhost:'))
			{
				return true;
			}

			return false;
		}
		else if(address.isIPv4(_string, _radix, _throw))
		{
			return !!_string.startsWith('127.');
		}
		else if(address.isIPv6(_string, _radix, _throw))
		{
			if(_string === '::1')
			{
				return true;
			}
			else if(_string === '::ffff:7f00:1')
			{
				return true;
			}
			else if(_string.startsWith('::ffff:127.'))
			{
				return true;
			}
		}

		return false;
	};

	address.isIP = (_string, _radix, _throw = DEFAULT_THROW) => {
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
			return false;
		}
		else if(address.isIPv4(_string, _radix, _throw))
		{
			return true;
		}
		else if(address.isIPv6(_string, _radix, _throw))
		{
			return true;
		}

		return false;
	};

	address.isIPv4 = address.isIP.v4 = (_string, _radix = 10, _throw = DEFAULT_THROW) => {
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
			return false;
		}
		else if(_string[0] === '.' || _string[_string.length - 1] === '.')
		{
			return false;
		}
		
		if(! (isInt(_radix) && _radix >= 2 && _radix <= 16) && _radix !== null)
		{
			_radix = 10;
		}

		const alpha = (_radix === null ? null : radix.getAlphabetSet(_radix, _throw));
		var count = 0;
		var sub = '';
		var elem;

		if(alpha) for(var i = 0; i < _string.length; ++i)
		{
			if(_string[i] === '.')
			{
				if(sub.length === 0)
				{
					return false;
				}
				else if(++count > 3)
				{
					return false;
				}
				else if((elem = parseInt(sub, _radix)) < 0 || elem > 255)
				{
					return false;
				}
				
				sub = '';
			}
			else if(radix.checkAlphabetSet(_string[i], alpha, _throw))
			{
				if((sub += _string[i]).length > 8)
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}
		else throw new Error('TODO (*any* radix)');

		return (count === 1 || count === 3);
	};

	address.isIPv6 = address.isIP.v6 = (_string, _radix = 16, _throw = DEFAULT_THROW) => {
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
			return false;
		}
		else if(_string[0] === '[' && _string[_string.length - 1] === ']')
		{
			_string = _string.slice(1, -1);
		}

		if(_string === '::')
		{
			return true;
		}
		else if(_string[_string.length - 1] === ':')
		{
			return false;
		}
		else if(_string.startsWith('::ffff:'))
		{
			if(address.isIPv4(_string.substr(7), 10, _throw))
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		if(! (isInt(_radix) && _radix >= 2 && _radix <= 16) && _radix !== null)
		{
			_radix = 16;
		}

		const alpha = (_radix === null ? null : radix.getAlphabetSet(_radix, _throw));
		var count = 0;
		var sub = '';
		var double = -1;

		if(alpha) for(var i = 0; i < _string.length; ++i)
		{
			if(_string[i] === ':')
			{
				if(sub.length === 0)
				{
					if(count === 0 && i < (_string.length - 1) && _string[i + 1] !== ':')
					{
						return false;
					}
					else if(double > -1)
					{
						return false;
					}
					else if(++count > 7)
					{
						return false;
					}
					else
					{
						++i;
						double = count;
					}
				}
				else if(++count > 7)
				{
					return false;
				}
				else if((elem = parseInt(sub, _radix)) < 0 || elem > 65535)
				{
					return false;
				}

				sub = '';
			}
			else if(radix.checkAlphabetSet(_string[i], alpha, _throw))
			{
				if((sub += _string[i]).length > 16)
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}
		else throw new Error('TODO (*any* radix)');

		if(count < 7)
		{
			return (double > -1);
		}

		return true;
	};

	address.isHost = (_string, _throw = DEFAULT_THROW) => {
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
			return false;
		}
		else if(_string.length >= 256)
		{
			return false;
		}

		var hadPort = false;
		var portLen;

		for(var i = 0; i < _string.length; ++i)
		{
			if(_string[i].isDecimal(false))
			{
				if(hadPort && ++portLen > 5)
				{
					return false;
				}
			}
			else if(_string[i].isLetter)
			{
				if(hadPort)
				{
					return false;
				}
			}
			else if(_string[i] === '.' || _string[i] === '_')
			{
				if(hadPort)
				{
					return false;
				}
			}
			else if(_string[i] === ':')
			{
				if(hadPort)
				{
					return false;
				}

				hadPort = true;
				portLen = 0;
			}
			else
			{
				return false;
			}
		}

		return true;
	};

	address.isHostname = (_string, _throw = DEFAULT_THROW) => {
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
			return false;
		}
		else if(_string.includes(':'))
		{
			return false;
		}

		return address.isHost(_string, _throw);
	};

	address.isKnownProtocol = (_string, _throw = DEFAULT_THROW) => {
		if(typeof _string !== 'string')
		{
			if(_throw)
			{
				throw new Error('Invalid _string argument');
			}

			return null;
		}
		else if(! URL.knownProtocols)
		{
			throw new Error('The \'URL.knownProtocols\' still needs to be loaded first');
		}
		else if(_string.startsWith(false, ... URL.knownProtocols))
		{
			return true;
		}

		return false;
	};

	//
	
})();

