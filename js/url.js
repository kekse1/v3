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
	
})();
