(function()
{

	//
	const DEFAULT_EQUAL_DEPTH = 0;

	//
	Object.defineProperty(Array.prototype, 'remove', { value: function(... _args)
	{
		const result = [];

		if(_args.length === 0)
		{
			return result;
		}
		else for(var i = 0, j = 0; i < this.length; ++i)
		{
			for(var k = 0; k < _args.length; ++k)
			{
				if(this[i] === _args[k])
				{
					this.splice(i, 1);
					result[j] = ((i--) + (j++));
				}
			}
		}

		return result;
	}});

	//
	Object.defineProperty(Array.prototype, 'pushUnique', { value: function(... _args)
	{
		const result = [];

		for(var i = 0, j = this.length, k = 0; i < _args.length; ++i)
		{
			if(! this.includes(_args[i]))
			{
				result[k++] = this[j++] = _args[i];
			}
		}

		return result;
	}});

	Object.defineProperty(Array.prototype, 'unshiftUnique', { value: function(... _args)
	{
		const result = [];

		for(var i = 0, j = 0; i < _args.length; ++i)
		{
			if(! this.includes(_args[i]))
			{
				result[j++] = _args[i];
				this.splice(0, 0, _args[i]);
			}
		}

		return result;
	}});

	//
	Object.defineProperty(Array.prototype, 'uniq', { value: function()
	{
		const result = [];
		var idx;

		for(var i = this.length - 1, j = 0; i >= 0; --i)
		{
			if((idx = this.indexOf(this[i])) > -1 && idx < i)
			{
				result[j++] = this.splice(i, 1)[0];
			}
		}

		return result.reverse();
	}});

	//
	Object.defineProperty(Array.prototype, 'lengthSort', { value: function(_asc = true)
	{
		return this.sort('length', _asc);
	}});

	//
	const _sort = Array.prototype.sort;

	Object.defineProperty(Array.prototype, 'sort', { value: function(_path = null, _asc = true)
	{
		if(typeof _path === 'boolean')
		{
			_asc = _path;
			_path = null;
		}

		if(typeof _path === 'function')
		{
			_sort.call(this, _path);
			return this;
		}

		_sort.call(this, (_a, _b) => {
			var a, b;

			if(typeof _path === 'string' || isNumber(_path))
			{
				if(! (Object.has(_path, _a) && Object.has(_path, _b)))
				{
					return 0;
				}

				a = Object.get(_path, _a);
				b = Object.get(_path, _b);
			}
			else
			{
				a = _a;
				b = _b;
			}

			if(typeof a === 'number' && typeof b === 'number')
			{
				return (_asc ? a - b : b - a);
			}
			else if(typeof a === 'bigint' && typeof b === 'bigint')
			{
				if(a < b) return (_asc ? -1 : 1);
				if(a > b) return (_asc ? 1 : -1);
				return 0;
			}
			else if(typeof a === 'string' && typeof b === 'string')
			{
				if(_asc) return a.localeCompare(b);
				return b.localeCompare(a);
			}
			else if(is(a, 'Date') && is(b, 'Date'))
			{
				if(_asc) return (a.getTime() - b.getTime());
				return (b.getTime() - a.getTime());
			}
			else if(Object.isObject(a) && Object.isObject(b))
			{
				if(Array.isArray(a, true))
				{
					a = a.length;
				}
				else
				{
					a = Object.getOwnPropertyNames(a).length;
				}

				if(Array.isArray(b, true))
				{
					b = b.length;
				}
				else
				{
					b = Object.getOwnPropertyNames(b).length;
				}

				return (_asc ? a - b : b - a);
			}

			try
			{
				if(a < b) return (_asc ? -1 : 1);
				if(a > b) return (_asc ? 1 : -1);
				return 0;
			}
			catch(_error)
			{
				throw new Error('Comparing different types, or trying to sort unknown types..');
			}
		});

		//
		return this;
	}});

	//
	Object.defineProperty(Array.prototype, 'indicesOf', { value: function(_needle)
	{
		const result = [];
		var index = 0;
		var last = -1;
		
		do
		{
			if((last = this.indexOf(_needle, last + 1)) > -1)
			{
				result[index++] = last;
			}
		}
		while(last > -1);
		
		return result;
	}});
	
	//
	const _isArray = Array.isArray.bind(Array);

	Object.defineProperty(Array, '_isArray', { value: _isArray });

	isArray = (_item, _min = 1) => {
		if(typeof _min === 'boolean')
		{
			_min = (_min ? 0 : 1);
		}
		else if(! (isInt(_min) && _min >= 0))
		{
			_min = 1;
		}

		if(_isArray(_item))
		{
			return (_item.length >= _min);
		}

		return false;
	};

	Object.defineProperty(Array, 'isArray', { value: isArray.bind(Array) });

	//
	Object.defineProperty(Array, 'equal', { value: function(... _args)
	{
		var DEPTH = DEFAULT_EQUAL_DEPTH;

		for(var i = 0; i < _args.length; ++i)
		{
			if(isInt(_args[i]) && _args[i] >= 0)
			{
				DEPTH = _args.splice(i--, 1)[0];
			}
			else if(_args[i] === null || _args[i] === true)
			{
				_args.splice(i--, 1);
				DEPTH = null;
			}
			else if(_args[i] === false)
			{
				_args.splice(i--, 1);
				DEPTH = 0;
			}
			else if(! isArray(_args[i], true))
			{
				throw new Error('Invalid ..._args[' + i + '] (no Array)');
			}
		}

		const len = _args[0].length;
		var same = 0;

		for(var i = 1; i < _args.length; ++i)
		{
			if(_args[i] === _args[0])
			{
				++same;
			}
			else if(_args[i].length !== len)
			{
				return false;
			}
		}

		if(same === _args.length)
		{
			return true;
		}

		for(var i = 0; i < len; ++i)
		{
			for(var j = 1; j < _args.length; ++j)
			{
				if(_args[j][i] !== _args[0][i])
				{
					return false;
				}
			}
		}

		return true;
	}});

	//
	Object.defineProperty(Array.prototype, 'contains', { value: function(... _args)
	{
		if(_args.length === 0)
		{
			if(this.length === 0)
			{
				return true;
			}

			return null;
		}

		const copy = [ ... this.valueOf() ];
		var has;

		for(var i = 0; i < _args.length; ++i)
		{
			has = false;

			for(var j = 0; j < copy.length; ++j)
			{
				if(_args[i] === copy[j])
				{
					copy.splice(j, 1);
					has = true;
					break;
				}
			}

			if(has)
			{
				_args.splice(i--, 1);

				if(_args.length === 0)
				{
					break;
				}
			}
			else
			{
				return false;
			}
		}

		return (_args.length === 0);
	}});

	//
	Object.defineProperty(Array.prototype, 'getIndex', { value: function(... _args)
	{
		if(_args.length === 0)
		{
			return this.length - 1;
		}

		const result = new Array(_args.length);

		for(var i = 0; i < _args.length; ++i)
		{
			if(isNumber(_args[i]))
			{
				result[i] = Math.getIndex(_args[i], this.length);
			}
			else
			{
				throw new Error('Invalid ..._args[' + i + '] (no Number)');
			}
		}

		if(result.length === 1)
		{
			return result[0];
		}

		return result;
	}});

	//
	Object.defineProperty(Array.prototype, 'search', { value: function(_depth = null, ... _args)
	{
		if(_args.length === 0)
		{
			return null;
		}
		else if(! (isInt(_depth) && _depth >= 0))
		{
			_depth = null;
		}

		const result = [];
		var index = 0;
		var found;

		const traverse = (_array, ... _path) => {
			for(var i = 0; i < _array.length; ++i)
			{
				found = false;

				for(var j = 0; j < _args.length; ++j)
				{
					if(_array[i] === _args[j])
					{
						result[index++] = [ _array[i], [ ... _path, i ] ];
						found = true;
					}
				}

				if(! found && isArray(_array[i], false))
				{
					if(_depth === null || _path.length < _depth)
					{
						traverse(_array[i], ... _path, i);
					}
				}
			}

			return result;
		};

		return traverse(this);
	}});

	//
	Object.defineProperty(Array.prototype, 'populate', { value: function()
	{
		var base;

		if(arguments.length === 1 && isArray(arguments[0], true))
		{
			base = arguments[0];
		}
		else
		{
			base = Array.from(arguments);
		}

		if(this.length >= base.length)
		{
			return 0;
		}

		var result = 0;

		for(var i = this.length; i < base.length; ++i, ++result)
		{
			this[i] = base[i];
		}

		return result;
	}});

	//

})();

