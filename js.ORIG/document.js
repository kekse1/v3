(function()
{

	//
	const DEFAULT_SAME_SITE_COOKIE = 'Strict';
	const DEFAULT_SECURE_COOKIE = null;//(location.protocol === 'https:');

	//
	Object.defineProperty(document, 'setCookie', { value: function(_name, _value, _hours, _path = '/', _same_site = DEFAULT_SAME_SITE_COOKIE, _secure = DEFAULT_SECURE_COOKIE)
	{
		if(typeof _secure === 'boolean')
		{
			_secure = (_secure ? ' Secure;' : '');
		}
		else if(location.protocol === 'https:')
		{
			_secure = ' Secure;';
		}
		else
		{
			_secure = '';
		}

		if(! isString(_same_site, false))
		{
			_same_site = DEFAULT_SAME_SITE_COOKIE;
		}

		_same_site = ' SameSite=' + _same_site + ';';

		if(isString(_name))
		{
			_name = encodeURIComponent(_name);
		}
		else
		{
			throw new Error('Invalid _name argument (not a non-empty String)');
		}

		if(isNumeric(_value))
		{
			_value = _value.toString();
		}
		else if(typeof _value === 'string')
		{
			_value = encodeURIComponent(_value);
		}
		else if(_value === null)
		{
			_value = '';
		}
		else
		{
			throw new Error('Invalid _value argument (neither a String nor numeric type, nor null)');
		}

		var result;

		if(is(_hours, 'Date'))
		{
			result = ' Expires=' + _hours.toUTCString() + ';';
		}
		else if(isNumber(_hours) && _hours >= 0)
		{
			result = ' Expires=' + new Date(Math.ceil(Date.now() + (_hours * 3600000))).toUTCString() + ';';
		}
		else if(typeof _hours === 'bigint')
		{
			result = ' Max-Age=' + _hours + ';';
		}
		else
		{
			result = '';
		}

		if(! isString(_path))
		{
			_path = '/';
		}

		document.cookie = (_name + '=' + _value + ';' + result + '; Path=' + _path + ';' + _same_site + _secure);
		return result;
	}});

	Object.defineProperty(document, 'removeCookie', { value: function(_name, _seconds, _path = '/')
	{
		if(isString(_name))
		{
			if(! document.isCookie(_name))
			{
				return null;
			}
			else
			{
				_name = encodeURIComponent(_name);
			}
		}
		else
		{
			throw new Error('Invalid _name argument (not a non-empty String)');
		}

		if(! isString(_path))
		{
			_path = '/';
		}

		if(! (isNumber(_seconds) && _seconds > 0))
		{
			var result;

			if(is(_seconds, 'Date'))
			{
				result = _seconds;
				_seconds = _seconds.toUTCString();
			}
			else
			{
				result = new Date();
				_seconds = 'Thu, 01 Jan 1970 00:00:00 UTC';
			}

			document.cookie = (_name + '=; expires=' + _seconds + '; path=' + _path);
			return result;
		}

		const expires = new Date(Math.ceil(Date.now() + (_seconds * 1000)));
		document.cookie = (_name + '=; expires=' + expires.toUTCString() + '; path=' + _path);
		return expires;
	}});

	Object.defineProperty(document, 'getCookie', { value: function(_name)
	{
		if(! isString(_name))
		{
			throw new Error('Invalid _name argument (not a non-empty String)');
		}

		const cookies = document.cookie.trim().split(';');

		for(var i = 0; i < cookies.length; ++i)
		{
			if((cookies[i] = cookies[i].trim().split('=')).length > 2)
			{
				throw new Error('UNEXPECTED');
			}
			else for(var j = 0; j < cookies[i].length; ++j)
			{
				cookies[i][j] = decodeURIComponent(cookies[i][j].trim());
			}

			if(cookies[i][0] !== _name)
			{
				continue;
			}
			else if(cookies[i].length < 2)
			{
				cookies[i][1] = '';
			}
			else if(cookies[i][1].isDecimal(true))
			{
				cookies[i][1] = Number(cookies[i][1]);
			}

			return cookies[i][1];
		}

		return null;
	}});

	Object.defineProperty(document, 'isCookie', { value: function(_name)
	{
		return (document.getCookie(_name) !== null);
	}});

	Object.defineProperty(document, 'isNumericCookie', { value: function(_name)
	{
		const cookie = document.getCookie(_name);

		if(cookie === null)
		{
			return null;
		}

		return isNumber(cookie);
	}});

	Object.defineProperty(document, 'clearCookies', { value: function(_seconds)
	{
		const cookies = document.listCookies();
		var result = 0;

		for(const c of cookies)
		{
			if(document.removeCookie(c.key, _seconds) !== null)
			{
				++result;
			}
		}

		return result;
	}});

	Object.defineProperty(document, 'getCookies', { value: function()
	{
		const cookies = document.listCookies();
		const result = Object.create(null);

		for(const c of cookies)
		{
			result[c.key] = c.value;
		}

		return result;
	}});

	Object.defineProperty(document, 'listCookies', { value: function()
	{
		const cookies = document.cookie.trim().split(';');
		const result = [];

		for(var i = 0, j = 0; i < cookies.length; ++i)
		{
			if((cookies[i] = cookies[i].trim().split('=')).length > 2)
			{
				throw new Error('UNEXPECTED');
			}
			else if(! isString(cookies[i][0]))
			{
				continue;
			}
			else for(var k = 0; k < cookies[i].length; ++k)
			{
				cookies[i][k] = decodeURIComponent(cookies[i][k].trim());
			}

			if(cookies[i].length < 2)
			{
				cookies[i][1] = '';
			}
			else if(cookies[i][1].isDecimal(true))
			{
				cookies[i][1] = Number(cookies[i][1]);
			}

			result[j] = Object.create(null);

			result[j].key = cookies[i][0];
			result[j].value = cookies[i][1];

			++j;
		}

		return result;
	}});

	//
	document.traverse = (_element, _depth = null, _computed_style = false) => {
		if(! _element)
		{
			_element = document.documentElement;
		}
		
		if(! (isInt(_depth) && _depth >= 0) && _depth !== null)
		{
			_depth = null;
		}
		
		const result = [];
		var index = 0;
		
		const traverse = (_elem, _current_depth = 0) => {
			//
			if(! was(_elem, 'Node'))
			{
				return result;
			}
			else for(var i = 0; i < _elem.childNodes.length; ++i)
			{
				//
				(result[index] = _elem.childNodes[i]).INDEX = i;
				result[index].DEPTH = _current_depth;

				if(_computed_style)
				{
					result[index].STYLE = getComputedStyle(result[index]);
				}
				else
				{
					result[index].STYLE = null;
				}

				result[index].DATE = new Date();
				result[index++].NOW = Date.now();

				//				
				if(_depth === null || _current_depth < _depth)
				{
					if('childNodes' in _elem)
					{
						traverse(_elem.childNodes[i], _current_depth + 1);
					}
				}
			}
			
			//
			return result;
		};
		
		return traverse(_element);
	};

	//
	Object.defineProperty(document, 'isBaseElement', { value: function(_elem)
	{
		const t = is(_elem);

		switch(t)
		{
			case 'HTMLDocument':
			case 'HTMLHtmlElement':
			case 'HTMLHeadElement':
			case 'HTMLBodyElement':
				return true;
		}

		return false;
	}});

	//
	Object.defineProperty(document, 'getVariable', { value: function(... _args)
	{
		return document.documentElement.getVariable(... _args);
	}});

	Object.defineProperty(document, 'hasVariable', { value: function(... _args)
	{
		return document.documentElement.hasVariable(... _args);
	}});

	Object.defineProperty(document, 'removeVariable', { value: function(... _args)
	{
		return document.documentElement.removeVariable(... _args);
	}});

	Object.defineProperty(document, 'setVariable', { value: function(... _args)
	{
		return document.documentElement.setVariable(... _args);
	}});

	//

})();

