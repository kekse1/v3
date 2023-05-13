(function()
{

	//
	Object.defineProperty(URL.prototype, 'render', { value: function(_options, ... _args)
	{
		if(! isObject(_options))
		{
			_args.unshift(_options);
			_options = null;
		}

		return new Uniform(this, _options).render(... _args);
	}});

	Object.defineProperty(URL, 'render', { value: function(_url, _resolve, ... _args)
	{
		return Uniform.create(_url, _resolve).render(... _args);
	}});

	//
	Object.defineProperty(URL, 'resolve', { value: function(_href)
	{
		return new URL(_href, location.href);
	}});

	Object.defineProperty(URL, 'create', { value: function(_href, _resolve)
	{
		if(!!_resolve)
		{
			return URL.resolve(_href);
		}
		
		return new URL(_href, location.origin);
	}});

	//
	Object.defineProperty(URL, 'isKnownProtocol', { value: function(... _args)
	{
		if(_args.length === 0)
		{
			return null;
		}

		const protocols = URL.protocols;
		var found;

		for(var i = 0; i < _args.length; ++i)
		{
			if(typeof _args[i] === 'string')
			{
				if(_args[i].length === 0)
				{
					return false;
				}

				found = false;

				for(var j = 0; j < protocols.length; ++j)
				{
					if(_args[i].toLowerCase().startsWith(protocols[j] + '//'))
					{
						found = true;
						break;
					}
				}

				if(found)
				{
					if(_args[i].includes(String.fromCharCode(0)))
					{
						return false;
					}
				}
				else if(! found)
				{
					return false;
				}
			}
			else
			{
				throw new Error('Invalid ..._args[' + i + '] (no String)');
			}
		}

		return true;
	}});

	//
	Object.defineProperty(URL, 'protocols', { get: function()
	{
		return [ 'http:', 'https:', 'file:', 'blob:', 'data:', 'ftp:' ];
	}});

	//
	
})();
