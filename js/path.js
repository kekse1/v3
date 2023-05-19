(function()
{

	//
	const DEFAULT_THROW = true;
	const DEFAULT_ORIGIN = true;

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
		else if(_string.includes('://'))
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
		else if(_string[0] === path.sep)
		{
			return true;
		}
		else if(path.isAddress(_string, _throw))
		{
			return true;
		}

		return false;
	};
	
	path.resolve = (_path, _origin = DEFAULT_ORIGIN, _throw = DEFAULT_THROW) => {
		if(is(_path, 'URL'))
		{
			_path = _path.pathname;
		}
		else if(typeof _path !== 'string')
		{
			if(_throw)
			{
				throw new Error('Invalid _path argument');
			}
			
			return null;
		}
		
		const url = new URL(_path, location.href);
		
		if(_origin && url.origin !== location.origin)
		{
			return url.href;
		}
		
		return url.pathname;
	};

	path.normalize = (_path, _origin = DEFAULT_ORIGIN, _throw = DEFAULT_THROW) => {
		if(is(_path, 'URL'))
		{
			return _path.pathname;
		}
		else if(typeof _path !== 'string')
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
		else if(_path.includes('://'))
		{
			const url = new URL(_path);

			if(_origin && url.origin !== location.origin)
			{
				return url.href;
			}

			return url.pathname;
		}

		//
		const absolute = (_path[0] === path.sep);
		const directory = (_path[_path.length - 1] === path.sep);

		//
		_path = _path.split(path.sep);
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
		return result.join(path.sep);
	};

	//
	//TODO from here below..
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
			_path = path.normalize(_path, false);
		}

		var idx = _path.lastIndexOf(path.sep);

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
		else if(_path.length === 0)
		{
			return '';
		}
		else if(_path === path.sep)
		{
			return '';
		}
		else if(_path[_path.length - 1] === path.sep)
		{
			return '';
		}
		else
		{
			_path = path.normalize(_path, false);
			const idx = _path.lastIndexOf(path.sep);
			
			if(idx > -1)
			{
				_path = _path.substr(idx + 1);
			}

			if(_path[0] === '.')
			{
				_path = _path.substr(1);
			}
		}

		if(typeof _count !== 'number')
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
		else if(_path.length === 0)
		{
			return '';
		}
		else if(_path === path.sep)
		{
			return '';
		}
		else if(typeof _ext !== 'string')
		{
			_ext = '';
		}

		while(_path[_path.length - 1] === path.sep)
		{
			_path = _path.slice(0, -1);
		}
		
		if((_path = _path.substr(_path.lastIndexOf(path.sep) + 1)).endsWith(_ext))
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

