(function()
{

	//
	const DEFAULT_ARRANGE = true;
	const DEFAULT_GET_VALUE = null;
	const DEFAULT_THROW = true;

	//
	Object.defineProperty(HTMLElement, 'isStyle', { value: function(... _args)
	{
		if(_args.length === 0)
		{
			return null;
		}
		else for(var i = 0; i < _args.length; ++i)
		{
			if(isString(_args[i], false))
			{
				if(_args[i].startsWith('--'))
				{
					continue;
				}
				else if(! (_args[i] in document.documentElement.style))
				{
					return false;
				}
			}
			else
			{
				return false;
				//throw new Error('Invalid ..._args[' + i + '] (not a non-empty String)');
			}
		}

		return true;
	}});

	Object.defineProperty(HTMLElement.prototype, 'hasStyle', { value: function(... _args)
	{
		if(_args.length === 0)
		{
			return null;
		}
		
		var s;
		
		for(var i = 0; i < _args.length; ++i)
		{
			if(! isString(_args[i], false))
			{
				throw new Error('Invalid ..._args[' + i + '] (no String)');
			}
			else
			{
				if((s = this.style.getPropertyValue(_args[i])).length === 0 || s === 'auto' || s === 'none')
				{
					return false;
				}
			}
		}
		
		return true;
	}});
	
	Object.defineProperty(HTMLElement.prototype, 'getStyle', { value: function(... _args)
	{
		var computed = null;

		if(_args.length === 0)
		{
			return undefined;
		}
		else for(var i = 0; i < _args.length; ++i)
		{
			if(Object.className(_args[i]) === 'CSSStyleDeclaration')
			{
				computed = _args.splice(i--, 1)[0];
			}
		}

		if(! HTMLElement.isStyle(... _args))
		{
			return null;
		}

		const result = Object.create(null);
		var value;

		for(var i = 0; i < _args.length; ++i)
		{
			if((value = this.style.getPropertyValue(_args[i])).length === 0 || value === 'auto')
			{
				if(computed === null)
				{
					computed = getComputedStyle(this);
				}

				if((value = computed.getPropertyValue(_args[i])).length === 0)
				{
					value = null;
				}
			}

			if(value !== null)
			{
				result[_args[i]] = value;
			}
		}
		
		if(_args.length === 1)
		{
			return result[_args[0]];
		}

		return result;
	}});

	//
	Object.defineProperty(Element.prototype, 'isVisible', { get: function()
	{
		const DEBUG = false;

		const isVisible = (_elem) => {
			try
			{
				//
				if(document.isBaseElement(_elem))
				{
					if(DEBUG)
					{
						alert('a');
					}

					return true;
				}

				//
				if(_elem.clientWidth <= 0 || _elem.clientHeight <= 0)
				{
					if(DEBUG)
					{
						alert('b');
					}

					return false;
				}

				//
				const rect = _elem.getBoundingClientRect();

				if(rect.width <= 0 || rect.height <= 0)
				{
					if(DEBUG)
					{
						alert('c');
					}

					return false;
				}

				const viewportWidth = (window.innerWidth || document.documentElement.clientWidth);
				const viewportHeight = (window.innerHeight || document.documentElement.clientHeight);

				if(rect.left > viewportWidth || rect.top > viewportHeight)
				{
					if(DEBUG)
					{
						alert('d');
					}

					return false;
				}
				else if(rect.left + rect.width <= 0 || rect.top + rect.height <= 0)
				{
					if(DEBUG)
					{
						alert('e');
					}

					return false;
				}

				if(_elem.parentNode)
				{
					if(rect.left > _elem.parentNode.clientWidth)
					{
						if(DEBUG)
						{
							alert('f');
						}

						return false;
					}
					else if(rect.top > _elem.parentNode.clientHeight)
					{
						if(DEBUG)
						{
							alert('g');
						}

						return false;
					}
				}

				//
				const style = getComputedStyle(_elem);

				//
				if(style.display === 'none' || style.visibility === 'hidden')
				{
					if(DEBUG)
					{
						alert('h');
					}

					return false;
				}
				else if(style.opacity === '0')
				{
					if(DEBUG)
					{
						alert('i');
					}

					return false;
				}
			}
			catch(_error)
			{
				_alert('ERROR @ \'.isVisible\':\n\n' + _error.stack);
				return null;
			}

			return true;
		};
		
		const overlaps = (_one, _two) => {
			//
		};

		var elem = this;

		do
		{
			if(! isVisible(elem))
			{
				return false;
			}
			else if(elem.parentNode)
			{
				elem = elem.parentNode;
			}
			else
			{
				break;
			}
		}
		while(elem);

		//
		return document.isBaseElement(elem);
	}});

	//
	Object.defineProperty(HTMLElement.prototype, 'getPosition', { value: function(_x, _y, _w, _h, _arrange = this.getVariable('arrange', true), _int = false)
	{
		if(typeof _arrange !== 'boolean')
		{
			_arrange = this.getVariable('arrange', true);
		}
		
		var x, y, w, h;
		
		if(isNumber(_x))
		{
			x = (_int ? Math.int(_x) : _x);
		}
		else
		{
			x = null;
		}
		
		if(isNumber(_y))
		{
			y = (_int ? Math.int(_y) : _y);
		}
		else
		{
			y = null;
		}
		
		if(x === null)
		{
			w = null;
		}
		else if(isNumber(_w))
		{
			w = (_int ? Math.int(_w) : _w);
		}
		else
		{
			w = this.offsetWidth;
		}
		
		if(y === null)
		{
			h = null;
		}
		else if(isNumber(_h))
		{
			h = (_int ? Math.int(_h) : _h);
		}
		else
		{
			h = this.offsetHeight;
		}

		if(x === null && y === null)
		{
			return null;
		}

		if(! this.parentNode)
		{
			if(x !== null && y !== null)
			{
				return [ x, y ];
			}
			else if(x !== null)
			{
				return x;
			}
			else
			{
				return y;
			}
		}

		const pw = (x === null ? null : this.parentNode.clientWidth);
		const ph = (y === null ? null : this.parentNode.clientHeight);

		if(_arrange)
		{
			if(x !== null && ((x + w) > pw))
			{
				x -= w;
			}
			
			if(y !== null && ((y + h) > ph))
			{
				y -= h;
			}
		}
		else
		{
			const dx = (x === null ? null : (pw - (x + w)));
			const dy = (y === null ? null : (ph - (y + h)));
			
			if(dx !== null && dx < 0)
			{
				x += dx;
			}
			
			if(dy !== null && dy < 0)
			{
				y += dy;
			}
		}

		if(x !== null && x < 0)
		{
			x -= x;
		}
		
		if(y !== null && y < 0)
		{
			y -= y;
		}
		
		if(x !== null && y !== null)
		{
			return [ x, y ];
		}
		else if(x !== null)
		{
			return x;
		}
		else
		{
			return y;
		}
	}});

	//
	const heightFactors = [
		'margin-top', 'margin-bottom',
		'padding-top', 'padding-bottom',
		'border-top-width', 'border-bottom-width'
	];

	const widthFactors = [
		'margin-left', 'margin-right',
		'padding-left', 'padding-right',
		'border-left-width', 'border-right-width'
	];
	
	const leftFactors = [ 'margin-left', 'padding-left', 'border-left-width' ];
	const topFactors = [ 'margin-top', 'padding-top', 'border-top-width' ];
	const rightFactors = [ 'margin-right', 'padding-right', 'border-right-width' ];
	const bottomFactors = [ 'margin-bottom', 'padding-bottom', 'border-bottom-width' ];

	Object.defineProperty(Element.prototype, 'totalHeight', { get: function()
	{
		const style = getComputedStyle(this);
		var result = this.offsetHeight;

		for(const f of heightFactors)
		{
			result += getValue(style[f], 'px');
		}

		return result;
	}});
	
	Object.defineProperty(Element.prototype, 'totalWidth', { get: function()
	{
		const style = getComputedStyle(this);
		var result = this.offsetWidth;

		for(const f of widthFactors)
		{
			result += getValue(style[f], 'px');
		}
		
		return result;
	}});

	Object.defineProperty(Element.prototype, 'totalSize', { get: function()
	{
		const style = getComputedStyle(this);
		var width = this.offsetWidth;
		var height = this.offsetHeight;

		for(const f of widthFactors)
		{
			width += getValue(style[f], 'px');
		}

		for(const f of heightFactors)
		{
			height += getValue(style[f], 'px');
		}

		return { width, height };
	}});
	
	Object.defineProperty(Element.prototype, 'totalSpace', { get: function()
	{
		const style = getComputedStyle(this);
		var result = 0;
		
		for(const f of widthFactors)
		{
			result += getValue(style[f], 'px');
		}
		
		for(const f of heightFactors)
		{
			result += getValue(style[f], 'px');
		}
		
		return result;
	}});
	
	Object.defineProperty(Element.prototype, 'totalSpaces', { get: function()
	{
		const style = getComputedStyle(this);
		const result = Object.create(null);
		result.left = 0;
		result.top = 0;
		result.right = 0;
		result.bottom = 0;
		
		for(const f of leftFactors)
		{
			result.left += getValue(style[f], 'px');
		}
		
		for(const f of topFactors)
		{
			result.top += getValue(style[f], 'px');
		}
		
		for(const f of rightFactors)
		{
			result.right += getValue(style[f], 'px');
		}
		
		for(const f of bottomFactors)
		{
			result.bottom += getValue(style[f], 'px');
		}
		
		return result;
	}});
	
	Object.defineProperty(Element.prototype, 'totalLeft', { get: function()
	{
		const style = getComputedStyle(this);
		var result = 0;
		
		for(const f of leftFactors)
		{
			result += getValue(style[f], 'px');
		}

		return result;
	}});

	Object.defineProperty(Element.prototype, 'totalTop', { get: function()
	{
		const style = getComputedStyle(this);
		var result = 0;
		
		for(const f of topFactors)
		{
			result += getValue(style[f], 'px');
		}
		
		return result;
	}});

	Object.defineProperty(Element.prototype, 'totalRight', { get: function()
	{
		const style = getComputedStyle(this);
		var result = 0;
		
		for(const f of rightFactors)
		{
			result += getValue(style[f], 'px');
		}
		
		return result;
	}});

	Object.defineProperty(Element.prototype, 'totalBottom', { get: function()
	{
		const style = getComputedStyle(this);
		var result = 0;
		
		for(const f of bottomFactors)
		{
			result += getValue(style[f], 'px');
		}
		
		return result;
	}});

	//
	Object.defineProperty(HTMLElement.prototype, 'getVariable', { value: function(... _args)
	{
		var GET_VALUE = DEFAULT_GET_VALUE;
		var DELETE_CACHE = false;

		for(var i = 0; i < _args.length; ++i)
		{
			if(isString(_args[i], false))
			{
				if(_args[i].startsWith('--'))
				{
					_args[i] = _args[i].substr(2);
				}

				_args[i] = camel.disable(_args[i]);
			}
			else if(typeof _args[i] === 'boolean')
			{
				GET_VALUE = _args.splice(i--, 1)[0];
			}
			else if(_args[i] === null)
			{
				_args.splice(i--, 1);
				DELETE_CACHE = true;
			}
			else if(isArray(_args[i], true))
			{
				GET_VALUE = [ ... _args.splice(i--, 1)[0] ];
			}
			else
			{
				throw new Error('Invalid ..._args[' + i + ']');
			}
		}

		if(_args.length === 0)
		{
			return null;
		}

		var computedStyle;

		if(this._computedStyle && !DELETE_CACHE)
		{
			computedStyle = this._computedStyle;
		}
		else
		{
			computedStyle = this._computedStyle = getComputedStyle(this);
		}

		const result = Object.create(null);

		for(var i = 0; i < _args.length; ++i)
		{
			result[_args[i]] = css.parse(computedStyle.getPropertyValue('--' + _args[i]), GET_VALUE);
		}

		if(_args.length === 0)
		{
			return '';
		}
		else if(_args.length === 1)
		{
			return result[_args[0]];
		}

		return result;
	}});

	Object.defineProperty(HTMLElement.prototype, 'hasVariable', { value: function(... _args)
	{
		var COMPUTED = true;
		var DELETE_CACHE = false;

		for(var i = 0; i < _args.length; ++i)
		{
			if(typeof _args[i] === 'boolean')
			{
				COMPUTED = _args.splice(i--, 1)[0];
			}
			else if(_args[i] === null)
			{
				_args.splice(i--, 1);
				DELETE_CACHE = true;
			}
			else if(isString(_args[i], false))
			{
				if(_args[i].startsWith('--'))
				{
					_args[i] = _args[i].substr(2);
				}
			}
			else
			{
				throw new Error('Invalid ..._args[' + i + '] (not a non-empty String)');
			}
		}

		if(_args.length === 0)
		{
			return null;
		}
		
		var style;

		if(COMPUTED)
		{
			if(this._computedStyle && !DELETE_CACHE)
			{
				style = this._computedStyle;
			}
			else
			{
				style = this._computedStyle = getComputedStyle(this);
			}
		}
		else
		{
			style = this.style;
		}

		const result = Object.create(null);

		for(var i = 0; i < _args.length; ++i)
		{
			if(style.getPropertyValue('--' + _args[i]).length === 0)
			{
				result[_args[i]] = false;
			}
			else
			{
				result[_args[i]] = true;
			}
		}

		if(_args.length === 1)
		{
			return result[_args[0]];
		}

		return result;
	}});

	Object.defineProperty(HTMLElement.prototype, 'removeVariable', { value: function(... _args)
	{
		if(_args.length === 0)
		{
			return null;
		}
		else for(var i = 0; i < _args.length; ++i)
		{
			if(! isString(_args[i], false))
			{
				throw new Error('Invalid ..._args[' + i + '] (not a non-empty String)');
			}
		}

		delete this._computedStyle;
		const result = [];

		for(var i = 0, j = 0; i < _args.length; ++i)
		{
			if(this.style.getPropertyValue('--' + _args[i]).length > 0)
			{
				result[j++] = _args[i];
				this.style.removeProperty('--' + _args[i]);
			}
		}

		if(_args.length === 1)
		{
			if(result.length === 0)
			{
				return false;
			}

			return true;
		}

		return result;
	}});

	Object.defineProperty(HTMLElement.prototype, 'setVariable', { value: function(_variables, _value, _ext = '', _int = (_ext === true || _ext === 'px'), _throw = DEFAULT_THROW)
	{
		if(typeof _variables === 'string')
		{
			if(_variables.length === 0)
			{
				throw new Error('Invalid _variables argument (neither non-empty String nor Array of these)');
			}
			else if(_variables.startsWith('--'))
			{
				_variables = [ _variables.substr(2) ];
			}
			else
			{
				_variables = [ _variables ];
			}
		}
		else if(isArray(_variables, false))
		{
			for(var i = 0; i < _variables.length; ++i)
			{
				if(! isString(_variables[i], false))
				{
					throw new Error('Invalid _variables[' + i + '] (no non-empty String)');
				}
				else if(_variables[i].startsWith('--'))
				{
					_variables[i] = _variables[i].substr(2);
				}
			}
		}
		else
		{
			throw new Error('Invalid _variables argument (neither non-empty String nor non-empty Array)');
		}

		delete this._computedStyle;

		if(_ext === true)
		{
			_ext = 'px';
		}
		else if(typeof _ext !== 'string')
		{
			_ext = '';
		}
		
		//
		_value = css.render(_value);

		//
		const result = Object.create(null);
		var item;
		
		for(var i = 0; i < _variables.length; ++i)
		{
			item = this.style.getPropertyValue('--' + _variables[i]);
			
			if(item.length > 0)
			{
				result[_variables[i]] = item;
			}
			else
			{
				result[_variables[i]] = null;
			}
			
			this.style.setProperty('--' + _variables[i], _value);
		}
		
		if(_variables.length === 1)
		{
			return result[_variables[0]];
		}
		
		return result;
	}});

	//
	Object.defineProperty(Node.prototype, 'clear', { value: function(_options = null, _callback, ... _exclude)//TODO/!
	{
		/*if('innerHTML' in this)
		{
			this.innerHTML = '';
		}*/

		const childNodes = [ ... this.childNodes ];
		var rest = childNodes.length;

		const callback = () => {
			if(--rest <= 0)
			{
				call(_callback, { type: 'clear', this: this, childNodes });
			}
		};

		for(var i = 0; i < childNodes.length; ++i)
		{
			if(_exclude.includes(childNodes[i]))
			{
				call(callback);
			}
			else
			{
				this.removeChild(childNodes[i], _options, callback);
			}
		}

		return childNodes;
	}});

	//
	const _setProperty = CSSStyleDeclaration.prototype.setProperty;
	const _getPropertyValue = CSSStyleDeclaration.prototype.getPropertyValue;
	const _getPropertyPriority = CSSStyleDeclaration.prototype.getPropertyPriority;
	const _removeProperty = CSSStyleDeclaration.prototype.removeProperty;

	Object.defineProperty(CSSStyleDeclaration.prototype, 'setProperty', { value: function(_prop, ... _args)
	{
		if(camel)
		{
			_prop = camel.disable(_prop);
		}

		return _setProperty.call(this, camel.disable(_prop), ... _args);
	}});

	Object.defineProperty(CSSStyleDeclaration.prototype, 'getPropertyValue', { value: function(... _args)
	{
		for(var i = 0; i < _args.length; ++i)
		{
			if(isString(_args[i], false))
			{
				if(_args[i].startsWith('--'))
				{
					continue;
				}
				else if(! this.isValidProperty(_args[i]))
				{
					_args.splice(i--, 1);
				}
			}
			else
			{
				_args.splice(i--, 1);
			}
		}

		if(_args.length === 0)
		{
			throw new Error('Invalid style property defined, not available or no non-empty String');
		}

		const result = Object.create(null);
		var camelOn, camelOff, value;

		for(var i = 0; i < _args.length; ++i)
		{
			camelOn = camel.enable(_args[i]);
			camelOff = camel.disable(_args[i]);
			value = _getPropertyValue.call(this, camelOff);
			result[camelOn] = value;
			result[camelOff] = value;
		}

		if(_args.length === 1)
		{
			return result[_args[0]];
		}

		return result;
	}});

	Object.defineProperty(CSSStyleDeclaration.prototype, 'getPropertyPriority', { value: function(... _args)
	{
		for(var i = 0; i < _args.length; ++i)
		{
			if(isString(_args[i], false))
			{
				if(_args[i].startsWith('--'))
				{
					continue;
				}
				else if(! this.isValidProperty(_args[i]))
				{
					_args.splice(i--, 1);
				}
			}
			else
			{
				_args.splice(i--, 1);
			}
		}

		if(_args.length === 0)
		{
			throw new Error('Invalid style property defined, not available or no non-empty String');
		}
		
		const result = Object.create(null);
		var camelOn, camelOff, value;

		for(var i = 0; i < _args.length; ++i)
		{
			camelOn = camel.enable(_args[i]);
			camelOff = camel.disable(_args[i]);
			value = _getPropertyPriority.call(this, camelOff);
			result[camelOn] = value;
			result[camelOff] = value;
		}

		if(_args.length === 1)
		{
			return result[_args[0]];
		}

		return result;
	}});

	Object.defineProperty(CSSStyleDeclaration.prototype, 'removeProperty', { value: function(... _args)
	{
		for(var i = 0; i < _args.length; ++i)
		{
			if(isString(_args[i], false))
			{
				if(_args[i].startsWith('--'))
				{
					continue;
				}
				else if(! this.isValidProperty(_args[i]))
				{
					_args.splice(i--, 1);
				}
			}
			else
			{
				_args.splice(i--, 1);
			}
		}

		if(_args.length === 0)
		{
			throw new Error('Invalid style property defined, not available or no non-empty String');
		}

		const result = Object.create(null);
		var camelOn, camelOff, value;

		for(var i = 0; i < _args.length; ++i)
		{
			camelOn = camel.enable(_args[i]);
			camelOff = camel.disable(_args[i]);
			value = _removeProperty.call(this, camelOff);
			result[camelOn] = value;
			result[camelOff] = value;
		}
		
		if(_args.length === 1)
		{
			return result[_args[0]];
		}

		return result;
	}});

	Object.defineProperty(CSSStyleDeclaration.prototype, 'isValidProperty', { value: function(... _args)
	{
		return HTMLElement.isStyle(... _args);
	}});
	
	//
	const opacityAnimation = (_element, _type_or_value, _duration, _callback) => {
		if(! _element)
		{
			throw new Error('Invalid _element argument');
		}
		else if(typeof _callback !== 'function')
		{
			throw new Error('A _callback function is expected');
		}
		else if(! (isInt(_duration) && _duration >= 0) && _duration !== null)
		{
			if(! (isInt(_duration = _element.getVariable('class-duration', true)) && _duration >= 0))
			{
				_duration = null;
			}
		}

		if(_duration === null)
		{
			return call(_callback, { type: 'opacityAnimation', element: _element, type: _type_or_value, value: _type_or_value, duration: _duration, callback: _callback });
		}

		const keyframes = {};

		if(isNumber(_type_or_value) && _type_or_value >= 0 && _type_or_value <= 1)
		{
			keyframes.opacity = [ _type_or_value ];
		}
		else
		{
			keyframes.opacity = null;
		}

		if(typeof _element._originalOpacity !== 'string')
		{
			_element._originalOpacity = getComputedStyle(_element).opacity;
		}

		switch(_type_or_value)
		{
			case false:
			case 'in':
			case 'start':
				_type_or_value = false;
				
				if(keyframes.opacity === null)
				{
					keyframes.opacity = [ '0' ];
				}
				break;
			case true:
			case 'out':
			case 'stop':
			case 'end':
				_type_or_value = true;

				if(keyframes.opacity === null)
				{
					keyframes.opacity = [ _element._originalOpacity ];
				}
				break;
			default:
				throw new Error('Invalid _type_or_value argument');
		}

		if(_duration === null)
		{
			_element.style.setProperty('opacity', keyframes.opacity[0]);
			call(_callback, { type: 'opacityAnimation', this: _element, element: _element, opacity: keyframes.opacity[0] });
			return false;
		}
		else
		{
			_element.animate(keyframes, {
				duration: _duration,
				delay: 0,
				state: false,
				persist: true
			}, (_e, _f) => {
				//
				if(_type_or_value === true)//&& _f)
				{
					delete _element._originalOpacity;
				}

				//
				call(_callback, { type: 'opacityAnimation', this: _element, element: _element, opacity: keyframes.opacity[0] });
			});
		}

		return true;
	};

	const _classList = Object.getOwnPropertyDescriptor(Element.prototype, 'classList');

	Object.defineProperty(Element.prototype, 'classList', {
		get: function()
		{
			const result = _classList.get.call(this);
			result.element = this;
			return result;
		},
		set: function(_value)
		{
			return _classList.set.call(this, _value);
		}
	});

	const _add = DOMTokenList.prototype.add;
	const _remove = DOMTokenList.prototype.remove;
	const _replace = DOMTokenList.prototype.replace;
	const _toggle = DOMTokenList.prototype.toggle;

	Object.defineProperty(DOMTokenList.prototype, '_add', { value: _add });
	Object.defineProperty(DOMTokenList.prototype, '_remove', { value: _remove });
	Object.defineProperty(DOMTokenList.prototype, '_replace', { value: _replace });
	Object.defineProperty(DOMTokenList.prototype, '_toggle', { value: _toggle });

	Object.defineProperty(DOMTokenList.prototype, 'add', { value: function(... _args)
	{
		var animate = true;
		var callback = null;
		const add = [];

		for(var i = 0, j = 0; i < _args.length; ++i)
		{
			if(typeof _args[i] === 'function')
			{
				callback = _args.splice(i--, 1)[0];
			}
			else if(isString(_args[i], false))
			{
				add[j++] = _args.splice(i--, 1)[0];
			}
			else if(typeof _args[i] === 'boolean')
			{
				if(_args[i])
				{
					animate = true;
				}
				else
				{
					animate = null;
				}
			}
			else if(_args[i] === null)
			{
				animate = _args.splice(i--, 1)[0];
			}
			else if(isInt(_args[i]) && _args[i] >= 0)
			{
				animate = _args.splice(i--, 1)[0];
			}
			else
			{
				throw new Error('Invalid ..._args[' + i + ']; expecting [ function, boolean, string, null, integer ]');
			}
		}

		for(var i = 0; i < add.length; ++i)
		{
			if(this.contains(add[i]))
			{
				add.splice(i--, 1);
			}
		}

		if(!this.element || !this.element.parentNode)
		{
			const res = _add.call(this, ... add);
			call(callback, { type: '_add', classList: this, element: null, result: res }, res);
			return res;
		}

		const result = [];

		if(add.length === 0)
		{
			call(callback, { type: 'add', classList: this, element: this.element, result }, result, result.length);
			return result;
		}
		else if(animate === true)
		{
			if(! (isInt(animate = this.element.getVariable('class-duration', true)) && animate >= 0))
			{
				animate = 0;
			}
		}
		else if(! (isInt(animate) && animate >= 0))
		{
			animate = null;
		}

		//
		opacityAnimation(this.element, false, animate, () => {
			for(var i = 0, j = 0; i < add.length; ++i)
			{
				_add.call(this, result[j++] = add[i]);
			}

			opacityAnimation(this.element, true, animate, () => {
				call(callback, { type: 'add', classList: this, element: this.element, result }, result, result.length);
			});
		});

		//
		return add;
	}});

	Object.defineProperty(DOMTokenList.prototype, 'remove', { value: function(... _args)
	{
		var animate = true;
		var callback = null;
		const remove = [];

		for(var i = 0, j = 0; i < _args.length; ++i)
		{
			if(typeof _args[i] === 'function')
			{
				callback = _args.splice(i--, 1)[0];
			}
			else if(isString(_args[i], false))
			{
				remove[j++] = _args.splice(i--, 1)[0];
			}
			else if(typeof _args[i] === 'boolean')
			{
				animate = _args.splice(i--, 1)[0];
			}
			else if(_args[i] === null)
			{
				animate = _args.splice(i--, 1)[0];
			}
			else if(isInt(_args[i]) && _args[i] >= 0)
			{
				animate = _args.splice(i--, 1)[0];
			}
			else
			{
				throw new Error('Invalid ..._args[' + i + ']; expecting [ function, boolean, string, null, integer]');
			}
		}

		for(var i = 0; i < remove.length; ++i)
		{
			if(! this.contains(remove[i]))
			{
				remove.splice(i--, 1);
			}
		}

		if(!this.element || !this.element.parentNode)
		{
			const res = _remove.call(this, ... remove);
			call(callback, { type: '_remove', classList: this, element: null, result: res }, res);
			return res;
		}

		const result = [];

		if(remove.length === 0)
		{
			call(callback, { type: 'remove', this: this, element: this, result }, result, result.length);
			return result;
		}
		else if(animate === true)
		{
			if(! (isInt(animate = this.element.getVariable('class-duration', true)) && animate >= 0))
			{
				animate = 0;
			}
		}
		else if(! (isInt(animate) && animate >= 0))
		{
			animate = null;
		}

		//
		opacityAnimation(this.element, false, animate, () => {
			for(var i = 0, j = 0; i < remove.length; ++i)
			{
				_remove.call(this, result[j++] = remove[i]);
			}

			opacityAnimation(this.element, true, animate, () => {
				call(callback, { type: 'remove', classList: this, element: this.element, result }, result, result.length);
			});
		});

		//
		return remove;
	}});

	Object.defineProperty(DOMTokenList.prototype, 'replace', { value: function(... _args)
	{
		var animate = true;
		var callback = null;
		const replace = [];

		for(var i = 0, j = 0; i < _args.length; ++i)
		{
			if(typeof _args[i] === 'function')
			{
				callback = _args.splice(i--, 1)[0];
			}
			else if(isString(_args[i], false))
			{
				if(replace.length >= 2)
				{
					throw new Error('Only two Strings expected (no more, and no less)');
				}

				replace[j++] = _args.splice(i--, 1)[0];
			}
			else if(typeof _args[i] === 'boolean')
			{
				animate = _args.splice(i--, 1)[0];
			}
			else if(_args[i] === null)
			{
				animate = _args.splice(i--, 1)[0];
			}
			else if(isInt(_args[i]) && _args[i] >= 0)
			{
				animate = _args.splice(i--, 1)[0];
			}
			else
			{
				throw new Error('Invalid ..._args[' + i + ']; expecting [ function, boolean, string, null, integer ]');
			}
		}

		if(!this.element || !this.element.parentNode)
		{
			const res = _replace.call(this, replace[0], replace[1]);
			call(callback, { type: '_replace', classList: this, element: null, result: res }, res);
			return res;
		}

		const result = [];

		if(replace.length !== 2)
		{
			throw new Error('Invalid argument count.. expecting two non-empty Strings, at least');
		}
		else if(! this.contains(replace[0]))
		{
			throw new Error('Invalid argument: class \'' + replace[0] + '\' is not available here');
			return this.element;
		}
		else if(animate === true)
		{
			if(! (isInt(animate = this.element.getVariable('class-duration', true)) && animate >= 0))
			{
				animate = 0;
			}
		}
		else if(! (isInt(animate) && animate >= 0))
		{
			animate = null;
		}

		//
		opacityAnimation(this.element, false, animate, () => {
			_replace.call(this, ... replace);

			opacityAnimation(this.element, true, animate, () => {
				call(callback, { type: 'replace', classList: this, element: this.element, result: true }, true);
			});
		});

		//
		return this.element;
	}});

	Object.defineProperty(DOMTokenList.prototype, 'toggle', { value: function(... _args)
	{
		var animate = true;
		var force = undefined;
		var callback = null;
		const toggle = [];

		for(var i = 0, j = 0; i < _args.length; ++i)
		{
			if(typeof _args[i] === 'function')
			{
				callback = _args.splice(i--, 1)[0];
			}
			else if(typeof _args[i] === 'boolean')
			{
				if(typeof force !== 'boolean')
				{
					force = _args.splice(i--, 1)[0];
				}
				else
				{
					animate = _args.splice(i--, 1)[0];
				}
			}
			else if(typeof _args[i] === 'undefined')
			{
				force = _args.splice(i--, 1)[0];
			}
			else if(isString(_args[i], false))
			{
				toggle[j++] = _args.splice(i--, 1)[0];
			}
			else if(_args[i] === null)
			{
				animate = _args.splice(i--, 1)[0];
			}
			else if(isInt(_args[i]) && _args[i] >= 0)
			{
				animate = _args.splice(i--, 1)[0];
			}
			else
			{
				throw new Error('Invalid ..._args[' + i + ']; expecting [ function, boolean, undefined, string, null, integer ]');
			}
		}

		if(!this.element || !this.element.parentNode)
		{
			const res = _toggle.call(this, toggle[0], force);
			call(callback, { type: '_toggle', classList: this, element: null, result: res }, res);
			return res;
		}

		const result = [];

		if(toggle.length === 0)
		{
			call(callback, { type: 'toggle', this: this, element: this, result }, result, result.length);
			return result;
		}
		else if(animate === true)
		{
			if(! (isInt(animate = this.element.getVariable('class-duration', true)) && animate >= 0))
			{
				animate = 0;
			}
		}
		else if(! (isInt(animate) && animate >= 0))
		{
			animate = null;
		}

		//
		opacityAnimation(this.element, false, animate, () => {
			const res = _toggle.call(this, toggle[0], force);

			opacityAnimation(this.element, true, animate, () => {
				call(callback, { type: 'toggle', classList: this, element: this.element, result: res }, res);
			});
		});

		return toggle;
	}});

	//

})();

