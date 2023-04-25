(function()
{

	//
	const DEFAULT_THROW = true;

	const DEFAULT_EXT = true;
	const DEFAULT_UNIT = true;

	const DEFAULT_CONTEXT = undefined;

	//
	Object.defineProperty(window, 'mobileDevice', { get: function()
	{
		return (window.devicePixelRatio !== 1);
	}});

	Object.defineProperty(window, 'touchDevice', { get: function()
	{
		return ('ontouchstart' in window);
	}});

	//
	Object.defineProperty(window, 'orientation', { get: function()
	{
		if(window.innerWidth === window.innerHeight)
		{
			return '';
		}
		else if(window.innerWidth > window.innerHeight)
		{
			return 'horizontal';
		}

		return 'vertical';
	}});

	Object.defineProperty(window, 'horizontal', { get: function()
	{
		return (window.orientation === 'horizontal');
	}});

	Object.defineProperty(window, 'vertical', { get: function()
	{
		return (window.orientation === 'vertical');
	}});

	//
	Object.defineProperty(window, 'setValue', { value: function(_item, _ext = DEFAULT_EXT, _int = (_ext === 'px'), _throw = DEFAULT_THROW)
	{
		if(typeof _item === 'string')
		{
			return _item;
		}
		else if(isNumeric(_item))
		{
			if(_int && typeof _item === 'number')
			{
				_item = Math.int(_item);
			}
			else if(typeof _item === 'bigint')
			{
				_item += 'n';
			}
			else
			{
				_item = _item.toString();
			}
			
			if(_ext === true)
			{
				_ext = 'px';
			}
			else if(_ext === false)
			{
				_ext = '';
			}
			else if(isArray(_ext, false))
			{
				for(var i = 0; i < _ext.length; ++i)
				{
					if(isString(_ext[i], false))
					{
						_ext = _ext[i];
						break;
					}
				}

				if(typeof _ext !== 'string')
				{
					_ext = '';
				}
			}
			else if(typeof _ext !== 'string')
			{
				_ext = '';
			}

			return (_item + _ext);
		}
		else if(typeof _item === 'boolean')
		{
			return (_item ? 'auto' : 'none');
		}
		else if(_item === null)
		{
			return 'null';
		}
		else if(typeof _item === 'undefined')
		{
			return 'undefined';
		}
		else if(_throw)
		{
			throw new Error('Invalid _item argument (expecting String or Number/BigInt)');
		}

		return null;
	}});

	Object.defineProperty(window, 'getValue', { value: function(_item, _unit = DEFAULT_UNIT, _int = (_unit === 'px'), _throw = DEFAULT_THROW)
	{
		if(isNumeric(_item))
		{
			if(_int && typeof _item !== 'bigint')
			{
				_item = Math.int(_item);
			}
		}
		else if(typeof _item === 'string')
		{
			if((_item = _item.trim()).length === 0)
			{
				if(_unit === null)
				{
					return _item;
				}

				return 0;
			}

			if(typeof _unit !== 'boolean' && _unit !== null)
			{
				if(isString(_unit, false))
				{
					_unit = [ _unit ];
				}
				else if(! isArray(_unit, false))
				{
					_unit = false;
				}
			}

			if(_unit)
			{
				if(! isArray(_unit))
				{
					if(_item.length === 0)
					{
						_item = 0;
					}
					else if(_item[_item.length - 1] === 'n' && !_item.includes('.') && !isNaN(_item.slice(0, -1)))
					{
						_item = BigInt.from(_item.slice(0, -1));
					}
					else if(!isNaN(_item))
					{
						_item = Number(_item);
					}
				}

				if(typeof _item === 'string')
				{
					const r = _item.unit();

					if(isArray(_unit, false) && _unit.length === 1 && r[1] === _unit[0])
					{
						_item = r[0];
					}
					else
					{
						_item = r;
					}
				}
			}

			if(_int && isNumber(_item))
			{
				_item = Math.int(_item);
			}
		}
		else if(_throw)
		{
			throw new Error('Invalid _item argument (expecting String or Number/BigInt)');
		}

		return _item;
	}});

	//
	const _computedStyle = window.getComputedStyle.bind(window);

	Object.defineProperty(window, 'getComputedStyle', { value: function(_element, ... _args)
	{
		const computedStyle = _computedStyle(_element, ((_args.length === 0 || isString(_args[0], false)) ? null : _args.shift()));

		if(_args.length === 0)
		{
			return computedStyle;
		}

		var THROW = DEFAULT_THROW;

		for(var i = 0; i < _args.length; ++i)
		{
			if(typeof _args[i] === 'boolean')
			{
				THROW = _args.splice(i--, 1)[0];
			}
		}

		const result = {};
		var enabled, disabled;

		for(var i = 0; i < _args.length; ++i)
		{
			if(isString(_args[i], false))
			{
				if(_args[i] in computedStyle)
				{
					enabled = camel.enable(_args[i]);
					disabled = camel.disable(_args[i]);

					result[enabled] = result[disabled] = computedStyle.getPropertyValue(_args[i]);
				}
				else if(THROW)
				{
					throw new Error('There\'s no such style ' + _args[i].quote());
				}
			}
			else if(THROW)
			{
				throw new Error('Invalid ..._args[' + i + '] (not a non-empty String)');
			}
		}

		return result;
	}});

	//
	const doCall = (_callback, _context = undefined, ... _args) => {
		if(typeof _context === 'undefined')
		{
			return _callback(... _args);
		}

		return _callback.apply(_context, _args);
	};

	Object.defineProperty(window, 'call', { value: function(_callback, ... _args)
	{
		if(typeof _callback !== 'function')
		{
			return _callback;
		}
		
		const context = (typeof _callback.context === 'undefined' ? DEFAULT_CONTEXT : _callback.context);
		const timeout = (('timeout' in _callback) ? _callback.timeout : document.getVariable('callback-timeout', true));

		if(isInt(timeout) && timeout >= 0)
		{
			return setTimeout(() => {
				return doCall(_callback, context, ... _args);
			}, timeout);
		}

		return doCall(_callback, context, ... _args);
	}});

	//
	
})();

