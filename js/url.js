(function()
{

	//
	const DEFAULT_RESOLVE = false;

	//
	Object.defineProperty(URL.prototype, 'base', {
		get: function()
		{
			var result = this.href;
			var idx = result.indexOf('?');

			if(idx === -1)
			{
				if((idx = result.indexOf('#')) > -1)
				{
					result = result.substring(0, idx);
				}
			}
			else
			{
				result = result.substring(0, idx);
			}

			return result;
		},
		set: function(_value)
		{
			if(typeof _value !== 'string' || _value.length === 0)
			{
				return this.base;
			}
			else
			{
				this.href = (_value + this.param);
			}

			return this.base;
		}
	});

	Object.defineProperty(URL.prototype, 'param', {
		get: function()
		{
			var result = '';

			if(this.search.length > 1)
			{
				result += this.search;
			}

			if(this.hash.length > 1)
			{
				result += this.hash;
			}

			return result;
		},
		set: function(_value)
		{
			var href = this.href;
			var idx = href.indexOf('?');

			if(idx === -1)
			{
				idx = href.indexOf('#');
			}

			if(idx > -1)
			{
				href = href.substring(0, idx);
			}

			if(typeof _value === 'string')
			{
				if(_value[0] === '?' || _value[0] === '#')
				{
					href += _value;
				}
			}

			this.href = href;
			return this.param;
		}
	});

	//
	Object.defineProperty(URL, 'resolve', { value: function(_href)
	{
		return new URL(_href, location.href);
	}});

	Object.defineProperty(URL, 'create', { value: function(_href, _resolve = DEFAULT_RESOLVE)
	{
		if(_resolve)
		{
			return URL.resolve(_href);
		}
		
		return new URL(_href, location.origin);
	}});

	//
	Object.defineProperty(URL, 'knownProtocols', { get: function()
	{
		return [ 'http:', 'https:', 'file:', 'blob:', 'data:', 'ftp:', 'ftps:', 'ws:', 'wss:' ];
	}});

	Object.defineProperty(URL.prototype, 'isKnownProtocol', { get: function()
	{
		return address.isKnownProtocol(this.protocol);
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

	//
	Object.defineProperty(URL, 'render', { value: function(_url, _options, _resolve = DEFAULT_RESOLVE)
	{
		if(is(_url, 'URL'))
		{
			return _url.render(_options);
		}
		else if(typeof _url === 'string')
		{
			if(_url.length === 0)
			{
				return '';
			}
			
			return new URL(_url, (_resolve ? location.href : location.origin)).render(_options);
		}
		
		throw new Error('Invalid _url argument (expecting String or URL)');
	}});
	
	Object.defineProperty(URL.prototype, 'render', { value: function(_options)
	{
		//
		_options = getRenderOptions(_options);
		
		//
		var result = '<span style="white-space: nowrap;"';
		
		if(! _options.arrows)
		{
			result += ' class="noArrow"';
		}
		
		result += '>';
		
		//
		if(_options.brackets)
		{
			result += '&lt; ';
		}
		
		if(_options.anchor)
		{
			result += `<a href="${this.href}"`;
			
			if(typeof _options.target === 'string')
			{
				result += ` target="${_options.target}"`;
			}
			
			if(! _options.arrows)
			{
				result += ' class="noArrow"';
			}
			
			result += '>';
		}
		
		//
		var sub;
		
		if(this.protocol)
		{
			sub = `<span style="font-size: ${_options.protocol};">${this.protocol.slice(0, -1)}</span>`
				+ ` <span style="font-size: ${_options.protocolSep};">://</span> `;
		}
		else
		{
			sub = '';
		}
		
		if(this.hostname)
		{
			sub += `<span style="font-size: ${_options.hostname};">${this.hostname}</span>`;
			
			if(this.port)
			{
				sub += `<span style="font-size: ${_options.param};">:</span>`
					+ `<span style="font-size: ${_options.port};">${this.port}</span>`;
			}
			
			sub += ' ';
		}
		
		if(this.pathname)
		{
			sub += `<span style="font-size: ${_options.pathname};">${this.pathname}</span>`;
		}
		
		if(this.search.length > 1)
		{
			sub += ` <span style="font-size: ${_options.param};">?</span>`
				+ `<span style="font-size: ${_options.search};">${this.search.substr(1)}</span>`;
		}
		
		if(this.hash.length > 1)
		{
			sub += ` <span style="font-size: ${_options.param};">#</span>`
				+ `<span style="font-size: ${_options.hash};">${this.hash.substr(1)}</span>`;
		}
		
		//
		if(sub.length === 0)
		{
			result = '';
		}
		else
		{
			//
			result += sub;
			
			//
			if(_options.anchor)
			{
				result += '</a>';
			}
			
			if(_options.brackets)
			{
				result += ' &gt;';
			}
			
			//
			result += '</span>';
		}
		
		//
		return result;
	}});
	
	const getRenderOptions = (_options) => {
		if(! isObject(_options))
		{
			_options = {};
		}
		
		const result = {};
		
		if(typeof _options.target === 'string')
		{
			result.target = _options.target;
		}
		else
		{
			result.target = null;
		}
		
		if(typeof _options.brackets === 'boolean')
		{
			result.bracktets = _options.brackets;
		}
		else
		{
			result.brackets = document.getVariable('url-brackets', true);
		}
		
		if(typeof _options.anchor === 'boolean')
		{
			result.anchor = _options.anchor;
		}
		else
		{
			result.anchor = document.getVariable('url-anchor', true);
		}
		
		if(typeof _options.arrows === 'boolean')
		{
			result.arrows = _options.arrows;
		}
		else
		{
			result.arrows = document.getVariable('url-arrows', true);
		}
		
		if(isString(_options.protocol, false))
		{
			result.protocol = _options.protocol;
		}
		else
		{
			result.protocol = document.getVariable('url-font-size-protocol');
		}
		
		if(isString(_options.protocolSep, false))
		{
			result.protocolSep = _options.protocolSep;
		}
		else
		{
			result.protocolSep = document.getVariable('url-font-size-protocol-sep');
		}
		
		if(isString(_options.hostname, false))
		{
			result.hostname = _options.hostname;
		}
		else
		{
			result.hostname = document.getVariable('url-font-size-hostname');
		}
		
		if(isString(_options.port, false))
		{
			result.port = _options.port;
		}
		else
		{
			result.port = document.getVariable('url-font-size-port');
		}
		
		if(isString(_options.pathname, false))
		{
			result.pathname = _options.pathname;
		}
		else
		{
			result.pathname = document.getVariable('url-font-size-pathname');
		}
		
		if(isString(_options.search, false))
		{
			result.search = _options.search;
		}
		else
		{
			result.search = document.getVariable('url-font-size-search');
		}
		
		if(isString(_options.hash, false))
		{
			result.hash = _options.hash;
		}
		else
		{
			result.hash = document.getVariable('url-font-size-hash');
		}
		
		if(isString(_options.param, false))
		{
			result.param = _options.param;
		}
		else
		{
			result.param = document.getVariable('url-font-size-param');
		}
		
		return result;
	};

	//
	Object.defineProperty(URL.prototype, 'args', {
		get: function()
		{
throw new Error('TODO');
		},
		set: function(_value)
		{
throw new Error('TODO');
		}
	});

	Object.defineProperty(URL.prototype, 'argv', {
		get: function()
		{
throw new Error('TODO');
		},
		set: function(_value)
		{
throw new Error('TODO');
		}
	});

	//
	
})();

