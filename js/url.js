(function()
{

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

	Object.defineProperty(URL, 'create', { value: function(_href, _resolve = false)
	{
		if(_resolve)
		{
			return URL.resolve(_href);
		}
		
		return new URL(_href, location.origin);
	}});

	//
	Object.defineProperty(URL, 'protocols', { get: function()
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
	//TODO/render() etc.. see below..
	//
	
})();





/*
(function()
{

	//
	const DEFAULT_TARGET = 'blank';
	const DEFAULT_BRACKETS = false;
	const DEFAULT_ANCHOR = true;
	const DEFAULT_ARROWS = false;

	const DEFAULT_SIZE_PROTOCOL = '0.90em';
	const DEFAULT_SIZE_PROTOCOL_SEP = '0.85rem';
	const DEFAULT_SIZE_HOSTNAME = '1.15em';
	const DEFAULT_SIZE_PORT = '1.00em';
	const DEFAULT_SIZE_PATHNAME = '1.05em';
	const DEFAULT_SIZE_SEARCH = '0.95em';
	const DEFAULT_SIZE_HASH = '0.95em';
	const DEFAULT_SIZE_SYMBOL = '1.20em';
	
	//
	uniform = (_string, _anchor = DEFAULT_ANCHOR, _target = DEFAULT_TARGET, _brackets = DEFAULT_BRACKETS, _arrows = DEFAULT_ARROWS) => {
		if(typeof _string === 'string')
		{
			if(_string.length === 0)
			{
				return '';
			}
		}
		else
		{
			throw new Error('Invalid _string argument');
		}

		if(! isString(_target))
		{
			_target = null;
		}
		
		if(typeof _anchor !== 'boolean')
		{
			_anchor = DEFAULT_ANCHOR;
		}

		if(typeof _brackets !== 'boolean')
		{
			_brackets = DEFAULT_BRACKETS;
		}

		if(typeof _arrows !== 'boolean')
		{
			_arrows = DEFAULT_ARROWS;
		}

		const url = new URL(_string);
		var result = '';//'<span style="white-space: nowrap;">';

		if(_brackets)
		{
			result += '&lt; ';
		}

		if(_anchor)
		{
			result += '<a href="' + _string + '"';

			if(!_arrows)
			{
				result += ' class="noArrow"';
			}

			if(_target)
			{
				result += ' target="' + _target + '"';
			}
		}
		else
		{
			result += '<span class="' + (_arrows ? '' : ' noArrow') + '" style="white-space: nowrap; pointer-events: none;">';
		}

		//
		var sub;

		if(url.protocol)
		{
			sub = '<span style="font-size: ' + uniform.size.protocol + ';">' + url.protocol.slice(0, -1) +
				'</span> <span style="font-size: ' + uniform.size.protocolSep + ';">://</span> ';
		}
		else
		{
			sub = '';
		}

		if(url.hostname)
		{
			sub += '<span style="font-size: ' + uniform.size.hostname + ';">' + url.hostname + '</span>';

			if(url.port)
			{
				sub += '<span style="font-size: ' + uniform.size.port + ';"><span style="font-size: ' +
					uniform.size.symbol + ';">:</span>' + url.port + '</span>';
			}

			sub += ' ';
		}

		if(url.pathname)
		{
			sub += '<span style="font-size: ' + uniform.size.pathname + ';">' + url.pathname + '</span>';
		}

		if(url.search && url.search !== '?')
		{
			sub += ' <span style="font-size: ' + uniform.size.search + ';"><span style="font-size: ' +
				uniform.size.symbol + ';">?</span>' + url.search.substr(1) + '</span>';
		}

		if(url.hash && url.hash !== '#')
		{
			sub += ' <span style="font-size: ' + uniform.size.hash + ';"><span style="font-size: ' +
				uniform.size.symbol + ';">?</span>' + url.hash.substr(1) + '</span>';
		}

		//
		if(sub.length === 0)
		{
			result = '';
		}
		else
		{
			result += sub;

			if(_anchor)
			{
				result += '</a>';
			}
			else
			{
				result += '</span>';
			}
		}

		if(_brackets)
		{
			result += ' &gt;';
		}

		return result;
	}
	
	//
	const resetSizes = () => {
		return uniform.size = {
			protocol: DEFAULT_SIZE_PROTOCOL,
			protocolSep: DEFAULT_SIZE_PROTOCOL_SEP,
			hostname: DEFAULT_SIZE_HOSTNAME,
			port: DEFAULT_SIZE_PORT,
			pathname: DEFAULT_SIZE_PATHNAME,
			search: DEFAULT_SIZE_SEARCH,
			hash: DEFAULT_SIZE_HASH,
			symbol: DEFAULT_SIZE_SYMBOL
		};
	};
	
	resetSizes();

	//

})();*/

