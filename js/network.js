(function()
{

	//
	const DEFAULT_THROW = true;

	//
	address = {};
	network = { address };

	//
	address.isLocalhost = (_string, _throw = DEFAULT_THROW) => {
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

		//
		//TODO/!!
		//
		return (_string === 'localhost');
	};

	address.isIP = (_string, _throw = DEFAULT_THROW) => {
throw new Error('TODO');
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
	};

	address.isIPv4 = address.isIP.v4 = (_string, _throw = DEFAULT_THROW) => {
throw new Error('TODO');
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
	};

	address.isIPv6 = address.isIP.v6 = (_string, _throw = DEFAULT_THROW) => {
throw new Error('TODO');
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
	};

	address.isHost = (_string, _throw = DEFAULT_THROW) => {
throw new Error('TODO');
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
	};

	address.isHostname = (_string, _throw = DEFAULT_THROW) => {
throw new Error('TODO');
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
		else if(! URL.protocols)
		{
			throw new Error('The \'URL.protocols\' still needs to be loaded first');
		}
		else if(_string.startsWith(false, ... URL.protocols))
		{
			return true;
		}

		return false;
	};

	//
	
})();

