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
	Object.defineProperty(URL, 'protocols', { get: function()
	{
		return [ 'http:', 'https:', 'file:', 'blob:', 'data:', 'ftp:' ];
	}});

	//
	Object.defineProperty(URL.prototype, 'isLocalhost', { get: function()
	{
		return address.isLocalhost(this.hostname);
	}});

	Object.defineProperty(URL.prototype, 'isIP', { get: function()
	{
		return address.isIP(this.hostname);
	}});
	
	Object.defineProperty(URL.prototype, 'isIPv4', { get: function()
	{
		return address.isIPv4(this.hostname);
	}});

	Object.defineProperty(URL.prototype, 'isIPv6', { get: function()
	{
		return address.isIPv6(this.hostname);
	}});

	Object.defineProperty(URL.prototype, 'isKnownProtocol', { get: function()
	{
		return address.isKnownProtocol(this.protocol);
	}});

	//
	//TODO/@ path.js!!
	//
	
})();
