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

})();

