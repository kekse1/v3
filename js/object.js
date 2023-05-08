(function()
{

	//
	const DEFAULT_THROW = true;
	const DEFAULT_IS_OBJECT_KEYS_ONLY = true;

	//
	_isExtensible = Object.isExtensible.bind(Object);

	Object.defineProperty(Object, 'isExtensible', { value: function(... _args)
	{
		if(_args.length === 0)
		{
			return null;
		}
		else for(var i = 0; i < _args.length; ++i)
		{
			if(! _isExtensible(_args[i]))
			{
				return false;
			}
		}

		return true;
	}});

	isExtensible = Object.isExtensible.bind(Object);

	//
	const _assign = Object.assign;

	Object.defineProperty(Object, 'assign', { value: function(... _args)
	{
		return {}.assign(... _args);
	}});

	Object.defineProperty(Object.prototype, 'assign', { value: function(... _args)
	{
		const result = this;

		for(var i = 0; i < _args.length; ++i)
		{
			if(Object.isExtensible(_args[i]) && !was(_args[i], 'Node'))
			{
				_assign.call(result, result, _args[i]);
			}
		}

		return result;
	}});

	//
	getPathArray = (_path, _throw = true, _delim = '.') => {
		if(! (isNumber(_path) || typeof _path === 'string'))
		{
			return [];
		}
		else if(! isString(_delim, false))
		{
			throw new Error('Invalid path _delim');
		}

		const result = [];

		if(typeof _path === 'string')
		{
			if(_path.length === 0)
			{
				return [];
			}

			const split = _path.split(_delim);

			for(var i = 0, j = 0; i < split.length; ++i)
			{
				if(split[i].length === 0)
				{
					continue;
				}
				else if(! isNaN(split[i]))
				{
					result[j++] = Math.int(Number(split[i]));
				}
				else
				{
					result[j++] = split[i];
				}
			}
		}
		else if(typeof _path === 'number')
		{
			result[0] = Math.int(_path);
		}
		else if(_throw)
		{
			throw new Error('The _path needs to be a String or Number');
		}

		return result;
	};

	Object.defineProperty(Object, 'has', { value: function(_path, _context = window)
	{
		return Object.get(_path, _context, true);
	}});

	has = Object.has;

	Object.defineProperty(Object, 'get', { value: function(_path, _context = window, _has = false)
	{
		_path = getPathArray(_path, false);

		if(_path.length === 0)
		{
			if(_has)
			{
				return true;
			}

			return _context;
		}

		var obj = _context;

		for(var i = 0; i < _path.length - 1; ++i)
		{
			try
			{
				if(isInt(_path[i]) && isArray(obj, true))
				{
					if(obj.length === 0)
					{
						if(_has)
						{
							return false;
						}

						return undefined;
					}
					else if(_path[i] < 0)
					{
						if((Math.abs(_path[i]) - 1) >= obj.length)
						{
							if(_has)
							{
								return false;
							}

							return undefined;
						}
					}
					else if(_path[i] >= obj.length)
					{
						if(_has)
						{
							return false;
						}

						return undefined;
					}
					else
					{
						_path[i] = Math.getIndex(_path[i], obj.length);
					}
				}

				obj = obj[_path[i]];
			}
			catch(_error)
			{
				if(_has)
				{
					return false;
				}

				return undefined;
			}
		}

		try
		{
			if(isInt(_path[_path.length - 1]) && isArray(obj, true))
			{
				if(obj.length === 0)
				{
					if(_has)
					{
						return false;
					}

					return undefined;
				}
				else if(_path[_path.length - 1] < 0)
				{
					if((Math.abs(_path[_path.length - 1]) - 1) >= obj.length)
					{
						if(_has)
						{
							return false;
						}

						return undefined;
					}
				}
				else if(_path[_path.length - 1] >= obj.length)
				{
					if(_has)
					{
						return false;
					}

					return undefined;
				}

				if(_has)
				{
					return true;
				}

				return obj[Math.getIndex(_path[_path.length - 1], obj.length)];
			}
			else if(_has)
			{
				return (_path[_path.length - 1] in obj);
			}

			return obj[_path[_path.length - 1]];
		}
		catch(_error)
		{
			if(_has)
			{
				return false;
			}
		}

		return undefined;
	}});

	get = Object.get;

	Object.defineProperty(Object, 'set', { value: function(_path, _value, _context = window, _null_object = false)
	{
		_path = getPathArray(_path);

		if(_path.length === 0)
		{
			return _context;
		}

		var obj = _context;

		if(! Object.isExtensible(obj))
		{
			_context = obj = (_null_object ? Object.create(null) : {});
		}

		var lastObj = obj;
		var nextKey;

		const preparePathItem = (_p, _o) => {
			if(typeof _p === 'undefined' || _p === null)
			{
				return null;
			}
			else if(Number.isInt(_p))
			{
				if(Array.isArray(_o, false))
				{
					return Math.getIndex(_p, _o.length);
				}
				else if(_p <= 0)
				{
					return 0;
				}

				return _p;
			}
			else if(String.isString(_p))
			{
				return _p;
			}

			return null;
		};

		for(var i = 0; i < _path.length - 1; ++i)
		{
			_path[i] = preparePathItem(_path[i], obj);

			if(! Object.isExtensible(obj[_path[i]]))
			{
				if(Number.isInt(nextKey = preparePathItem(_path[i + 1])))
				{
					obj = lastObj[_path[i]] = [];
				}
				else if(String.isString(nextKey))
				{
					obj = lastObj[_path[i]] = (_null_object ? Object.create(null) : {});
				}
				else
				{
					break;
				}
			}
			else
			{
				obj = obj[_path[i]];
			}

			lastObj = obj;
		}

		_path[_path.length - 1] = preparePathItem(_path[_path.length - 1], obj);

		if(typeof _value === 'undefined')
		{
			if(Array.isArray(obj, true))
			{
				if(obj.length > 0)
				{
					obj.splice(_path[_path.length - 1], 1);
				}
			}
			else
			{
				delete obj[_path[_path.length - 1]];
			}
		}
		else
		{
			obj[_path[_path.length - 1]] = _value;
		}

		return _context;
	}});

	set = Object.set;

	Object.defineProperty(Object, 'remove', { value: function(_path, _context = window)
	{
		return Object.set(_path, undefined, _context);
	}});

	remove = Object.remove;

	//
	isObject = (_item, _empty = true, _array = true, _array_empty = true) => {
		if(typeof _item === 'object' && _item !== null)
		{
			if(Array.isArray(_item, true))
			{
				if(!_array)
				{
					return false;
				}
				else if(typeof _array_empty === 'boolean')
				{
					_array_empty = (_array_empty ? 0 : 1);
				}
				else if(! isInt(_array_empty))
				{
					throw new Error('Invalid _array_empty argument');
				}

				return (_item.length >= _array_empty);
			}
			else if(typeof _empty === 'boolean')
			{
				_empty = (_empty ? 0 : 1);
			}
			else if(! (isInt(_empty) && _empty >= 0))
			{
				_empty = 0;
			}

			var count;

			if(DEFAULT_IS_OBJECT_KEYS_ONLY)
			{
				count = Object.keys(_item).length;
			}
			else
			{
				count = Object.getOwnPropertyNames(_item).length;
			}

			return (count >= _empty);
		}

		return false;
	};

	Object.defineProperty(Object, 'isObject', { value: isObject.bind(Object) });

	//
	is = (_object, _name, _class = true, _case_sensitive = true) => {
		if(typeof _class !== 'boolean')
		{
			_class = true;
		}

		if(typeof _case_sensitive !== 'boolean')
		{
			_case_sensitive = true;
		}

		if(typeof _name === 'string' && _name.length > 0)
		{
			if(! _case_sensitive)
			{
				_name = _name.toLowerCase();
			}
		}
		else
		{
			_name = null;
		}

		var compare;

		const tryConstructorName = () => {
			try
			{
				if(_name)
				{
					compare = _object.constructor.name;

					if(!_case_sensitive)
					{
						compare = compare.toLowerCase();
					}

					return (compare === _name);
				}
				else if(!_case_sensitive)
				{
					return _object.constructor.name.toLowerCase();
				}

				return _object.constructor.name;
			}
			catch(_error)
			{
				if(_name)
				{
					return false;
				}

				return '';
			}
		};

		const tryClassName = () => {
			try
			{
				if(_name)
				{
					compare = _object.name;

					if(!_case_sensitive)
					{
						compare = compare.toLowerCase();
					}

					return (compare === _name)
				}
				else if(!_case_sensitive)
				{
					return _object.name.toLowerCase();
				}

				return _object.name;
			}
			catch(_error)
			{
				if(_name)
				{
					return false;
				}

				return '';
			}
		};

		var result = tryConstructorName();

		if(result)
		{
			return result;
		}
		else if(_class && (result = tryClassName()))
		{
			return result;
		}
		else if(_name)
		{
			return false;
		}

		return result;
	};

	was = (_object, ... _args) => {
		var CASE_SENSITIVE = true;

		for(var i = 0; i < _args.length; ++i)
		{
			if(typeof _args[i] === 'boolean')
			{
				CASE_SENSITIVE = _args.splice(i--, 1)[0];
			}
			else if(typeof _args[i] !== 'string' || _args[i].length === 0)
			{
				throw new Error('Invalid ..._args[' + i + '] (not a non-empty String)');
			}
		}

		if(_args.length > 0)
		{
			if(! CASE_SENSITIVE) for(var i = 0; i < _args.length; ++i)
			{
				_args[i] = _args[i].toLowerCase();

				if(_args.lastIndexOf(_args[i]) > i)
				{
					_args.splice(i--, 1);
				}
			}
		}

		const result = [];
		const prototypes = Object.getPrototypesOf(_object);
		var name;

		for(var i = 0, j = 0; i < prototypes.length; ++i)
		{
			name = Object.className(prototypes[i]);

			if(typeof name === 'string' && name.length > 0)
			{
				result[j++] = name;

				if(_args.length > 0)
				{
					if(! CASE_SENSITIVE)
					{
						name = name.toLowerCase();
					}

					for(var k = 0; k < _args.length; ++k)
					{
						if(name === _args[k])
						{
							return true;
						}
					}
				}
			}
		}

		if(_args.length > 0)
		{
			return false;
		}

		return result;
	};

	Object.defineProperty(Object, 'is', { value: is.bind(Object) });
	Object.defineProperty(Object, 'was', { value: was.bind(Object) });

	//
	typeOf = (_object, _class = false) => {
		var result;

		if(_class === true)
		{
			result = Object.className(_object);
		}

		if(!_class || result.length === 0)
		{
			result = Object.prototype.toString.call(_object).slice(8, -1);
		}

		if(_class === null && result === 'Number')
		{
			if(isNumber(_object))
			{
				if((_object % 1) === 0)
				{
					result = 'Integer';
				}
				else
				{
					result = 'Float';
				}
			}
			else if(Number.isNaN(_object))
			{
				result = 'NaN';
			}
			else if(! Number.isFinite(_object))
			{
				result = 'Infinity';
			}
			else
			{
				result = 'Unknown';
			}
		}

		return result;
	};

	Object.defineProperty(Object, 'typeOf', { value: typeOf.bind(Object) });

	//
	className = (_object, _class_name = true) => {
		if(typeof _class_name !== 'boolean')
		{
			_class_name = true;
		}

		try
		{
			if(isString(_object.constructor?.name))
			{
				return _object.constructor.name;
			}
		}
		catch(_error)
		{
		}

		if(_class_name) try
		{
			if(isString(_object.name, false))
			{
				return _object.name;
			}
		}
		catch(_error)
		{
		}

		return '';
	};

	Object.defineProperty(Object, 'className', { value: className.bind(Object) });

	//
	Object.defineProperty(Object, 'getPrototypesOf', { value: function(_object)
	{
		const result = [];
		var proto = _object;

		try
		{
			while(proto = Object.getPrototypeOf(proto))
			{
				result.push(proto);

				if(isString(proto.constructor.name, false))
				{
					result[proto.constructor.name] = proto;
				}
			};
		}
		catch(_error)
		{
			//
		}

		return result;
	}});

	//
	Object.defineProperty(Object, 'isNull', { value: function(... _args)
	{
		if(_args.length === 0)
		{
			return null;
		}
		else for(var i = 0; i < _args.length; ++i)
		{
			if(typeof _args[i] !== 'object' || _args[i] === null)
			{
				return false;
			}
			else if(Object.getPrototypeOf(_args[i]) !== null)
			{
				return false;
			}
		}

		return true;
	}});

	//
	Object.defineProperty(Object, 'null', { value: function(... _args)
	{
		const result = Object.create(null);

		for(var i = 0; i < _args.length; ++i)
		{
			if(Object.isExtensible(_args[i]))
			{
				Object.prototype.assign.call(result, _args[i]);
			}
		}

		return result;
	}});

	//
	const _keys = Object.keys.bind(Object);
	const _getOwnPropertyNames = Object.getOwnPropertyNames.bind(Object);

	Object.defineProperty(Object, '_keys', { value: _keys });
	Object.defineProperty(Object, '_getOwnPropertyNames', { value: _getOwnPropertyNames });

	const getKeysOrProperties = (_properties, _args) => {
		if(typeof _properties !== 'boolean')
		{
			throw new Error('The _properties argument is necessary');
		}
		else if(_args.length === 0)
		{
			return [];
		}
		
		var arrays = 0;
		var ARRAY = null;
		var LENGTH = null;
		var hadArrayArgument = false;
		var hadLengthArgument = false;

		for(var i = 0; i < _args.length; ++i)
		{
			if(typeof _args[i] === 'boolean')
			{
				if(hadLengthArgument)
				{
					throw new Error('Too many Boolean type arguments (maximum is two)');
				}
				else if(hadArrayArgument)
				{
					LENGTH = _args.splice(i--, 1)[0];
					hadLengthArgument = true;
				}
				else
				{
					ARRAY = _args.splice(i--, 1)[0];
					hadArrayArgument = true;
				}
			}
			else if(_args[i] === null)
			{
				ARRAY = _args.splice(i--, 1)[0];
			}
			else if(isArray(_args[i], true))
			{
				++arrays;
			}
		}
		
		if(arrays === 0)
		{
			ARRAY = LENGTH = null;
		}
		else if(ARRAY && typeof LENGTH !== 'boolean')
		{
			LENGTH = false;
		}
		else if(! ARRAY)
		{
			LENGTH = null;
		}

		const result = [];

		if(_args.length === 0)
		{
			return result;
		}

		var sub;

		for(var i = 0, j = 0; i < _args.length; ++i)
		{
			/*try
			{*/
				if(_properties)
				{
					sub = _getOwnPropertyNames(_args[i]);
				}
				else
				{
					sub = _keys(_args[i]);
				}
				
				if(sub.length === 0)
				{
					continue;
				}
				else for(k = 0; k < sub.length; ++k)
				{
					result[j++] = sub[k];
				}
			/*}
			catch(_error)
			{
				if(THROW)
				{
					throw _error;
				}

				continue;
			}*/
		}

		var countNumeric = 0;
		var countStrings = 0;

		for(var i = 0; i < result.length; ++i)
		{
			if(ARRAY === false && !isNaN(result[i]))
			{
				result.splice(i--, 1);
			}
			else if(ARRAY === null)
			{
				if(isNaN(result[i]))
				{
					++countNumeric;
				}
				else
				{
					++countStrings;
				}
			}
		}

		if(ARRAY === null && countNumeric > 0 && countStrings > 0)
		{
			for(var i = 0; i < result.length; ++i)
			{
				if(!isNaN(result[i]))
				{
					result.splice(i--, 1);
				}
			}
		}
		
		if(! LENGTH)
		{
			result.remove('length');
		}

		return result;
	};

	Object.defineProperty(Object, 'keys', { value: function(... _args)
	{
		return getKeysOrProperties(false, _args);
	}});
	
	Object.defineProperty(Object, 'getOwnPropertyNames', { value: function(... _args)
	{
		return getKeysOrProperties(true, _args);
	}});
	
	//
	Object.defineProperty(Object, 'debug', { value: function(... _args)
	{
		var result = '';

		if(_args.length === 0)
		{
			return result;
		}
		
		var value;

		for(var i = 0; i < _args.length; ++i)
		{
			if(! isObject(_args[i], true))
			{
				result += '<' + was(_args[i]).join(',') + '> (' + typeOf(value = _args.splice(i--, 1)[0]) + ')';
				
				if(typeof value === 'string')
				{
					result += ' ' + value.quote();
				}
				else if(typeof value === 'undefined' || value === null)
				{
				}
				else if(typeof value.toString === 'function')
				{
					result += ' ' + value.toString();
				}
				else
				{
					throw new Error('No .toString() or smth. similar for this object[' + i + ']');
				}

				result += '\n';
			}
		}

		var value, keys;

		for(var i = 0; i < _args.length; ++i)
		{
			if(isArray((value = _args.splice(i--, 1)[0]), false))
			{
				for(var j = 0; j < value.length; ++j)
				{
					if(! isArray(value[j], true))
					{
						result += '[' + j + '] ' + Object.debug(value[j]);
					}
					else
					{
						result += '[' + j + '] Array(' + value[j].length + ')';
					}

					result += '\n';
				}
			}

			keys = Object.getOwnPropertyNames(value, false, false);

			for(const k of keys)
			{
				if(! isObject(value[k], true))
				{
					result += '[' + k + '] ' + Object.debug(value[k]);
				}
				else
				{
					const kk = Object.keys(value[k], false, false);
					result += '[' + k + '] Object(' + kk.length + '): ' + kk.join(', ');
				}

				result += '\n';
			}
		}

		return result;
	}});

	//
	
})();

