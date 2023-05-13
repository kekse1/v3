(function()
{

	//
	const DEFAULT_THROW = true;
	const DEFAULT_HREF = false;
	const DEFAULT_BOOL = true;
	const DEFAULT_RESOLVE = true;

	//
	path = { sep: '/', delim: ':' };

	//
	path.isAddress = (_string, _throw = DEFAULT_THROW) => {
		if(typeof _string !== 'string')
		{
			if(_throw)
			{
				throw new Error('Invalid _string argument');
			}

			return null;
		}
		else if(URL.protocols)
		{
			if(_string.startsWith(false, ... URL.protocols))
			{
				return true;
			}
		}
		else if(_string.toLowerCase().includes('://'))
		{
			return true;
		}

		return false;
	};

	path.isAbsolute = (_string, _throw = DEFAULT_THROW) => {
		if(typeof _string !== 'string')
		{
			if(_throw)
			{
				throw new Error('Invalid _string argument');
			}

			return null;
		}
		else if(_string[0] === '/')
		{
			return true;
		}
		else if(path.isAddress(_string, _throw))
		{
			return true;
		}

		return false;
	};

	const checkURL = (_string_url, _resolve = DEFAULT_RESOLVE, _throw = DEFAULT_THROW) => {
		const result = Object.create(null);

		if(was(_string_url, 'URL'))
		{
			result.url = _string_url;
			result.path = _string_url.pathname;
		}
		else if(typeof _string_url !== 'string')
		{
			if(_throw)
			{
				throw new Error('Invalid argument');
			}
			else if(_throw === null)
			{
				return null;
			}
			else
			{
				result.url = null;
				result.path = null;
			}

			return result;
		}
		else if(_string_url.length === 0)
		{
			(result.url = new URL(_string_url, new URL(_resolve !== false ? location.href : location.origin))).search = '';
			result.url.hash = '';
			result.url.pathname = '';
			result.path = '';
		}
		else if(result.url = tryURL(_string_url, _resolve, false))
		{
			result.path = result.url.pathname;
		}
		else
		{
			result.url = new URL(result.path = _string_url, new URL(_resolve !== false ? location.href : location.origin));
		}

		result.path = decodeURIComponent(result.path);
		return result;
	};

	const tryURL = (_string, _resolve = DEFAULT_RESOLVE, _bool = DEFAULT_BOOL) => {
		var result;

		try
		{
			result = new URL(_string, new URL((_resolve !== false ? location.href : location.origin)));

			if(_bool)
			{
				result = true;
			}
		}
		catch(_error)
		{
			if(_bool)
			{
				result = false;
			}
			else
			{
				result = null;
			}
		}

		return result;
	};

	//
	const normalize = (_path) => {
		//
		if(_path.length === 0)
		{
			return '.';
		}

		//
		const absolute = (_path[0] === '/');
		const directory = (_path[_path.length - 1] === '/');

		//
		_path = _path.split('/');
		const result = [];
		var minus = 0;

		//
		var item;

		while(_path.length > 0)
		{
			switch(item = _path.shift())
			{
				case '':
				case '.':
					break;
				case '..':
					if(result.length === 0)
					{
						++minus;
					}
					else
					{
						result.pop();
					}
					break;
				default:
					result.push(item);
					break;
			}
		}

		if(absolute)
		{
			result.unshift('');
		}
		else while(--minus >= 0)
		{
			result.unshift('..');
		}

		if(directory)
		{
			result.push('');
		}

		//
		return result.join('/');
	};

	path.normalize = (_path, _resolve = false, _throw = DEFAULT_THROW) => {
		//
		if(typeof _path !== 'string')
		{
			if(_throw)
			{
				throw new Error('Invalid _path argument');
			}

			return null;
		}
		else if(_path.length === 0)
		{
			return '.';
		}
		else if(! path.isAddress(_path))
		{
			return normalize(_path);
		}

		//
		const address = checkURL(_path, _resolve, _throw);
		_path = normalize(address.path);

		//
		if(address.url.origin === location.origin)
		{
			address.url = new URL(_path, new URL((_resolve !== false ? location.href : location.origin)));
			address.path = address.url.pathname;
		}
		else
		{
			address.url.pathname = _path;
			address.path = _path;
		}

		//
		var result;

		if(address.url.origin !== location.origin || _resolve === null)
		{
			result = address.url.href;
		}
		else
		{
			result = address.path;
		}

		//
		return result;
	};
	
	path.resolve = (_path, _href = DEFAULT_HREF, _throw = DEFAULT_THROW) => {
		return path.normalize(_path, (_href ? null : true), _throw);
	};

	//
	path.dirname = (_path) => {
		if(typeof _path !== 'string')
		{
			return null;
		}
		else if(_path.length === 0)
		{
			return '.';
		}
		else if(_path === path.sep)
		{
			return path.sep;
		}
		else
		{
			_path = path.normalize(_path);
		}

		var idx = _path.lastIndexOf('/');

		if(idx > -1)
		{
			if(idx < (_path.length - 1))
			{
				_path = _path.substr(0, idx);
			}
		}

		return _path;
	};

	path.name = (_path) => {
		return path.basename(_path, path.extname(_path));
	};
	
	path.extname = (_path, _count = 1) => {
		if(typeof _path !== 'string')
		{
			return null;
		}
		else if(typeof _count !== 'number')
		{
			throw new Error('Invalid _count argument');
		}
		
		var split = path.normalize(_path).split(path.sep);
		
		if(split[split.length - 1].length === 0)
		{
			return '';
		}
		else
		{
			split = split[split.length - 1].split('.');
			
			if(split[0].length === 0)
			{
				split.splice(0, 2);
			}
			else
			{
				split.splice(0, 1);
			}
		}
		
		_count = Math.min(_count, split.length);
		
		if(_count === 0)
		{
			return ('.' + split.join('.'));
		}
		
		const negative = (_count < 0);
		_count = Math.abs(_count);
		const result = [];
		
		while(result.length < _count)
		{
			if(negative)
			{
				result.push(split.shift());
			}
			else
			{
				result.unshift(split.pop());
			}
		}
		
		return ('.' + result.join('.'));
	};
	
	path.basename = (_path, _ext) => {
		if(typeof _path !== 'string')
		{
			return null;
		}
		else if(typeof _ext !== 'string')
		{
			_ext = '';
		}

		while(_path[_path.length - 1] === '/')
		{
			_path = _path.slice(0, -1);
		}
		
		if((_path = _path.substr(_path.lastIndexOf('/') + 1)).endsWith(_ext))
		{
			if(_ext.length > 0)
			{
				_path = _path.slice(0, -_ext.length);
			}
		}

		return _path;
	};

	//
	
})();

