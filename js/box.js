(function()
{

	//
	const DEFAULT_THROW = true;
	const DEFAULT_DURATION_IN = 3000;
	const DEFAULT_DURATION_OUT = 1500;
	const DEFAULT_DELAY_IN = 0;
	const DEFAULT_DELAY_OUT = 0;
	const DEFAULT_ANIMATE_STYLE = true;
	const DEFAULT_PARENT_ANIMATION = true;

	//
	Box = class Box extends HTMLElement
	{
		constructor(... _args)
		{
			//
			super();

			//
			this.__INIT = true;

			//
			this.options = Object.assign(... _args);

			//
			var callback = (typeof this.options.callback === 'function' ? this.options.callback : null);
			var sleep = ((isInt(this.options.sleep) && this.options.sleep >= 0) ? this.options.sleep : 0);
			var parent = (was(this.options.parent, 'HTMLElement') ? this.options.parent : null);
			var animate = ((typeof this.options.animate === 'boolean' || (isInt(this.options.animate) && this.options.animate >= 0)) ? this.options.animate : null);
			delete this.options.sleep;
			delete this.options.callback;
			delete this.options.parent;

			for(var i = 0; i < _args.length; ++i)
			{
				if(typeof _args[i] === 'function')
				{
					callback = _args.splice(i--, 1)[0];
				}
				else if(typeof _args[i] === 'boolean' || _args[i] === null)
				{
					animate = _args.splice(i--, 1)[0];
				}
				else if(isInt(_args[i]) && _args[i] >= 0)
				{
					sleep = _args.splice(i--, 1)[0];
				}
				else if(was(_args[i], 'HTMLElement'))
				{
					parent = _args.splice(i--, 1)[0];
				}
			}

			//
			this.reset();

			//
			this.identifyAs('box');

			//
			setTimeout(() => {
				this.apply();
				this.__INIT = false;

				if(parent)
				{
					return parent.appendChild(this, this.animateStyle && this.parentAnimation, () => {
						call(callback, { type: 'create', parent });
					});
				}
				else
				{
					call(callback, { type: 'create', this: this, parent: null });
				}
			}, ((isInt(sleep) && sleep >= 0) ? sleep : 0));
		}

		get parentAnimation()
		{
			if(typeof this.options.parentAnimation === 'boolean')
			{
				return this.options.parentAnimation;
			}

			return DEFAULT_PARENT_ANIMATION;
		}

		set parentAnimation(_value)
		{
			if(typeof _value === 'boolean')
			{
				return this.options.parentAnimation = _value;
			}
			else
			{
				delete this.options.parentAnimation;
			}

			return this.parentAnimation;
		}

		pointerWithin(_x, _y)
		{
			if(! (isNumber(_x) && isNumber(_y)))
			{
				throw new Error('At least one parameter is wrong (expecting two Numbers)');
			}

			var within;

			if(_x < this.offsetLeft)
			{
				within = false;
			}
			else if(_y < this.offsetTop)
			{
				within = false;
			}
			else if(_x > (this.offsetLeft + this.offsetWidth))
			{
				within = false;
			}
			else if(_y > (this.offsetTop + this.offsetHeight))
			{
				within = false;
			}
			else
			{
				within = true;
			}

			return within;
		}

		destroy(_animate = true, _callback)
		{
			if(this.parentNode)
			{
				return this.parentNode.removeChild(this, _animate, (_e, _f) => {
					this.destroyed = true;
					call(_callback, { type: 'destroy', event: _e, finish: _f }, _f);
				});
			}
			else
			{
				this.destroyed = true;
			}
			
			call(_callback, { type: 'destroy' });
		}

		get help()
		{
			if(typeof this.dataset.popup === 'string')
			{
				return this.dataset.popup;
			}

			return null;
		}

		set help(_value)
		{
			if(typeof _value === 'string')
			{
				return this.dataset.popup = _value;
			}
			else
			{
				delete this.dataset.popup;
			}

			return this.help;
		}

		get animateStyle()
		{
			if(this.__INIT)
			{
				return false;
			}
			else if(typeof this.options.animateStyle === 'boolean')
			{
				return this.options.animateStyle;
			}

			return DEFAULT_ANIMATE_STYLE;
		}

		set animateStyle(_value)
		{
			if(typeof _value === 'boolean')
			{
				this.options.animateStyle = _value;
			}
			else
			{
				delete this.options.animateStyle;
			}

			return this.animateStyle;
		}

		apply(_options = this.options, _throw = DEFAULT_THROW)
		{
			if(! _options)
			{
				if(_throw)
				{
					throw new Error('Invalid _options argument');
				}
				else if(! (_options = this.options))
				{
					return null;
				}
			}

			const keys = Object.keys(_options);
			
			if(keys.includes('center'))
			{
				if(! keys.includes('centerX'))
				{
					_options.centerX = _options.center;
				}
				
				if(! keys.includes('centerY'))
				{
					_options.centerY = _options.center;
				}
				
				delete _options.center;
				keys.remove('center');
			}
			
			if(keys.includes('top') && keys.includes('bottom'))
			{
				delete _options.height;
				keys.remove('height');
				delete _options.centerY;
				keys.remove('centerY');
			}
			else if(keys.includes('top') || keys.includes('bottom'))
			{
				delete _options.centerY;
				keys.remove('centerY');
			}
			else if(_options.centerY && !keys.includes('height'))
			{
				_options.height = this.getVariable('height', ['px']);
				keys.push('height');
			}
			
			if(keys.includes('left') && keys.includes('right'))
			{
				delete _options.width;
				keys.remove('width');
				delete _options.centerX;
				keys.remove('centerX');
			}
			else if(keys.includes('left') || keys.includes('right'))
			{
				delete _options.centerX;
				keys.remove('centerX');
			}
			else if(_options.centerX && !keys.includes('width'))
			{
				_options.width = this.getVariable('width', ['px']);
				keys.push('width');
			}

			//			
			for(var i = 0; i < keys.length; ++i)
			{
				if(keys[i] in this)
				{
					this[keys[i]] = _options[keys[i]];
				}
				else if(keys[i] in this.style)
				{
					this.style.setProperty(keys[i], css.render(_options[keys[i]]));
				}
				else if(keys[i].startsWith('--'))
				{
					this.setVariable(keys[i], _options[keys[i]]);
				}
				else
				{
					delete _options[keys[i]];
				}
			}

			if(this.parentNode && (this.centerX || this.centerY))
			{
				this.centerFunction(this.centerX, this.centerY);
			}

			return this.options;
		}
		
		centerFunction(_x = this.centerX, _y = this.centerY)
		{
			var result = 0;
			
			if(! this.parentNode)
			{
				return result;
			}
			
			if(_x)
			{
				this.right = null;
				this.left = Math.round((this.parentNode.clientWidth - this.offsetWidth) / 2);
				++result;
			}
			
			if(_y)
			{
				this.bottom = null;
				this.top = Math.round((this.parentNode.clientHeight - this.offsetHeight) / 2);
				++result;
			}
			
			return result;
		}

		get arrange()
		{
			return false;
		}

		get width()
		{
			var result = getComputedStyle(this).getPropertyValue('width');
			
			if(result.length === 0 || result === 'auto')
			{
				result = 0;
			}
			else
			{
				result = getValue(result, 'px');
			}

			return result;
		}
		
		set width(_value)
		{
			if(isNumber(_value))
			{
				_value = setValue(_value, true, true);
			}
			else if(typeof _value !== 'string' || _value === 'auto')
			{
				_value = null;
			}
			
			if(_value === null)
			{
				if(this.animateStyle)
				{
					this.setStyle('width', 'auto');//, true);
				}
				else
				{
					this.style.setProperty('width', 'auto');
				}
			}
			else
			{
				if(this.animateStyle)
				{
					this.setStyle('width', _value);
				}
				else
				{
					this.style.setProperty('width', _value);
				}
			}

			if(this.hasStyle('left') && !this.hasStyle('right'))
			{
				this.left = this.getPosition(this.left, null, this.offsetWidth, this.offsetHeight, this.getVariable('arrange', true), true);
			}

			return _value;
		}
		
		get height()
		{
			var result = getComputedStyle(this).getPropertyValue('height');
			
			if(result.length === 0 || result === 'auto')
			{
				result = 0;
			}
			else
			{
				result = getValue(result, 'px');
			}
			
			return result;
		}
		
		set height(_value)
		{
			if(isNumber(_value))
			{
				_value = setValue(_value, true, true);
			}
			else if(typeof _value !== 'string' || _value === 'auto')
			{
				_value = null;
			}
			
			if(_value === null)
			{
				if(this.animateStyle)
				{
					this.setStyle('height', 'auto');
				}
				else
				{
					this.style.setProperty('height', 'auto');
				}
			}
			else
			{
				if(this.animateStyle)
				{
					this.setStyle('height', _value);
				}
				else
				{
					this.style.setProperty('height', _value);
				}
			}

			if(this.hasStyle('top') && !this.hasStyle('bottom'))
			{
				this.top = this.getPosition(null, this.top, this.offsetWidth, this.offsetHeight, this.getVariable('arrange', true), true);
			}
			
			return _value;
		}

		get hasSize()
		{
			return (this.width !== null && this.height !== null);
		}

		get hasPosition()
		{
			return ((this.left !== null || this.right !== null) && (this.top !== null || this.bottom !== null));
		}

		get left()
		{
			var result = getComputedStyle(this).getPropertyValue('left');
			
			if(result.length === 0 || result === 'auto')
			{
				result = 0;
			}
			else if(isNumber(result = getValue(result, 'px')))
			{
				result = this.getPosition(result, null, this.offsetWidth, this.offsetHeight, this.getVariable('arrange', true), true);
			}
			
			return result;
		}
		
		set left(_value)
		{
	//TODO/.animate();
			if(isNumber(_value))
			{
				_value = setValue(_value, true, true);
			}
			else if(typeof _value !== 'string' || _value === 'auto')
			{
				_value = null;
			}

			const value = getValue(_value, 'px');

			if(isNumber(value))
			{
				_value = setValue(this.getPosition(value, null, this.offsetWidth, this.offsetHeight, this.getVariable('arrange', true), true), true, true);
			}
			
			if(_value === null)
			{
				if(this.animateStyle)
				{
					this.setStyle('left', 'auto');
				}
				else
				{
					this.style.setProperty('left', 'auto');
				}
			}
			else
			{
				if(this.animateStyle)
				{
					this.setStyle('left', _value);
				}
				else
				{
					this.style.setProperty('left', _value);
				}
			}
			
			return _value;
		}
		
		get top()
		{
			var result = getComputedStyle(this).getPropertyValue('top');
			
			if(result.length === 0 || result === 'auto')
			{
				result = 0;
			}
			else if(isNumber(result = getValue(result, 'px')))
			{
				result = this.getPosition(null, result, this.offsetWidth, this.offsetHeight, this.getVariable('arrange', true), true);
			}
			
			return result;
		}
		
		set top(_value)
		{
	//TODO/.animate();
			if(isNumber(_value))
			{
				_value = setValue(_value, true, true);
			}
			else if(typeof _value !== 'string' || _value === 'auto')
			{
				_value = null;
			}

			const value = getValue(_value, 'px');

			if(isNumber(value))
			{
				_value = setValue(this.getPosition(null, value, this.offsetWidth, this.offsetHeight, this.getVariable('arrange', true), true), true, true);
			}
			
			if(_value === null)
			{
				if(this.animateStyle)
				{
					this.setStyle('top', 'auto');
				}
				else
				{
					this.style.setProperty('top', 'auto');
				}
			}
			else
			{
				if(this.animateStyle)
				{
					this.setStyle('top', _value);
				}
				else
				{
					this.style.setProperty('top', _value);
				}
			}
			
			return _value;
		}
		
		get right()
		{
			var result = getComputedStyle(this).getPropertyValue('right');
			
			if(result.length === 0 || result === 'auto')
			{
				result = 0;
			}
			else
			{
				result = getValue(result, 'px');
			}
			
			return result;
		}
		
		set right(_value)
		{
	//TODO/.animate();
			if(isNumber(_value))
			{
				_value = setValue(_value, true, true);
			}
			else if(typeof _value !== 'string' || _value === 'auto')
			{
				_value = null;
			}
			
			if(_value === null)
			{
				if(this.animateStyle)
				{
					this.setStyle('right', 'auto');
				}
				else
				{
					this.style.setProperty('right', 'auto');
				}
			}
			else
			{
				if(this.animateStyle)
				{
					this.setStyle('right', _value);
				}
				else
				{
					this.style.setProperty('right', _value);
				}
			}
			
			return _value;
		}
		
		get bottom()
		{
			var result = getComputedStyle(this).getPropertyValue('bottom');
			
			if(result.length === 0 || result === 'auto')
			{
				result = 0;
			}
			else
			{
				result = getValue(result, 'px');
			}
			
			return result;
		}
		
		set bottom(_value)
		{
	//TODO/.animate();
			if(isNumber(_value))
			{
				_value = setValue(_value, true, true);
			}
			else if(typeof _value !== 'string' || _value === 'auto')
			{
				_value = null;
			}
			
			if(_value === null)
			{
				if(this.animateStyle)
				{
					this.setStyle('bottom', 'auto');
				}
				else
				{
					this.style.setProperty('bottom', 'auto');
				}
			}
			else
			{
				if(this.animateStyle)
				{
					this.setStyle('bottom', _value);
				}
				else
				{
					this.style.setProperty('bottom', _value);
				}
			}
			
			return _value;
		}

		static create(... _args)
		{
			return new this(... _args);
		}

		static getAnimationOptions(_type)
		{
			if(isString(_type, false)) switch(_type = _type.toLowerCase())
			{
				case 'in':
					return { duration: DEFAULT_DURATION_IN, delay: DEFAULT_DELAY_IN };
				case 'out':
					return { duration: DEFAULT_DURATION_OUT, delay: DEFAULT_DELAY_OUT };
			}

			return null;
		}

		get centerX()
		{
			return this.hasAttribute('centerX');
		}
		
		set centerX(_value)
		{
			if(_value === true || _value === 'auto')
			{
				this.setAttribute('centerX', '');
			}
			else
			{
				this.removeAttribute('centerX');
			}

			this.centerFunction(this.centerX, this.centerY);
			return this.centerX;
		}
		
		get centerY()
		{
			return this.hasAttribute('centerY');
		}
		
		set centerY(_value)
		{
			if(_value === true || _value === 'auto')
			{
				this.setAttribute('centerY', '');
			}
			else
			{
				this.removeAttribute('centerY');
			}
			
			this.centerFunction(this.centerX, this.centerY);
			return this.centerY;
		}
		
		get center()
		{
			if(this.centerX && this.centerY)
			{
				return true;
			}
			else if(this.centerX || this.centerY)
			{
				return null;
			}
			
			return false;
		}
		
		set center(_value)
		{
			if(typeof _value === 'boolean' || _value === 'auto')
			{
				this.centerX = this.centerY = _value;
			}
			else
			{
				this.centerX = this.centerY = null;
			}
			
			return this.center;
		}

		get movable()
		{
			if(this.centerX && this.centerY)
			{
				return false;
			}
			
			return this.hasAttribute('movable');
		}
		
		set movable(_value)
		{
			if(_value === true || _value === 'auto')
			{
				this.setAttribute('movable', '');
			}
			else
			{
				this.removeAttribute('movable');
			}
			
			return this.movable;
		}
		
		static onpointerdown(_event, _target = _event.target)
		{
			/*if(was(_target, 'Box'))
			{
				if(! _target.classList.contains('move'))
				{
					_target.startMove(_target.updatePointer(_event));
				}
			}*/
		}
		
		static onpointerup(_event, _target = _event.target)
		{
			/*for(const box of Box._INDEX)
			{
				if(box.classList.contains('move'))
				{
					box.stopMove(box.updatePointer(_event));
				}
			}*/
		}
		
		static onpointermove(_event, _target = _event.target)
		{
			/*for(const box of Box._INDEX)
			{
				if(box.classList.contains('move'))
				{
					box.move(box.updatePointer(_event));
				}
			}*/
		}

		static onkeydown(_event, _target = _event.target)
		{
			/*switch(_event.key)
			{
				case 'Escape':
					for(const box of Box._INDEX)
					{
						box.stopMove(box.updatePointer(_event));
					}
					break;
				default:
					return;
			}

			_event.preventDefault();*/
		}
		
		/*startMove(_pointer, _event = _pointer.event)
		{
			if(! _pointer.down)
			{
				return this.stopMove(_pointer, _event);
			}
			else if(! this.movable)
			{
				return this.stopMove(_pointer, _event);
			}
			else if(! this.classList.contains('move'))
			{
				this.classList._add('move');
			}

			if(this.popup)
			{
				this.popup.close(_event, false);
			}

			return true;
		}
		
		stopMove(_pointer, _event = _pointer.event)
		{
			if(_pointer.down)
			{
				return false;
			}
			else if(this.classList.contains('move'))
			{
				this.classList._remove('move');
			}

			return true;
		}
		
		moveBox(_pointer, _event = _pointer.event)
		{
			if(! this.classList.contains('move'))
			{
				return false;
			}

			if(! this.centerX)
			{
				this.style.right = 'auto';
				this.style.left = setValue(this.left + _pointer.dx);
			}
			
			if(! this.centerY)
			{
				this.style.bottom = 'auto';
				this.style.top = setValue(this.top + _pointer.dy);
			}
			
			return true;
		}

		updatePointer(_event, ... _actions)
		{
			//
			var pointer;
			
			if(this.pointer.has(_event.pointerId))
			{
				pointer = this.pointer.get(_event.pointerId);
			}
			else
			{
				this.pointer.set(_event.pointerId, pointer = Object.create(null));
			}
			
			//
			if(! isNumber(pointer.x))
			{
				pointer.x = _event.clientX;
				pointer.xx = null;
			}
			else
			{
				pointer.xx = pointer.x;
			}
			
			if(! isNumber(pointer.y))
			{
				pointer.y = _event.clientY;
				pointer.yy = null;
			}
			else
			{
				pointer.yy = pointer.y;
			}
			
			if(! isNumber(pointer.rx))
			{
				pointer.rx = 0;
			}
			
			if(! isNumber(pointer.ry))
			{
				pointer.ry = 0;
			}
			
			pointer.dx = (_event.clientX - pointer.x);
			pointer.dy = (_event.clientY - pointer.y);

			pointer.dx += pointer.rx;
			pointer.dy += pointer.ry;

			pointer.rx = 0;
			pointer.ry = 0;

			pointer.x = _event.clientX;
			pointer.y = _event.clientY;

			pointer.button = _event.button;

			if((pointer.buttons = _event.buttons) <= 0)
			{
				pointer.down = false;
				pointer.downButton = null;
				pointer.downButtons = 0;
			}
			else
			{
				pointer.down = true;
				pointer.downButton = _event.button;
				pointer.downButtons = _event.buttons;
			}

			//
			pointer.event = _event;
			pointer.removed = false;
			
			//
			for(var i = 0; i < _actions.length; ++i)
			{
				if(isString(_actions[i], false) && typeof this[_actions[i]] === 'function')
				{
					this[_actions[i]](pointer, _event);
				}
			}
			
			//
			return pointer;
		}
		
		removePointer(_event, ... _actions)
		{
			if(! this.pointer.has(_event.pointerId))
			{
				return null;
			}
			
			const pointer = this.pointer.get(_event.pointerId);
			this.pointer.delete(_event.pointerId);

			pointer.event = _event;
			pointer.removed = true;
			
			//
			for(var i = 0; i < _actions.length; ++i)
			{
				if(isString(_actions[i], false) && typeof this[_actions[i]] === 'function')
				{
					this[_actions[i]](pointer, _event);
				}
			}
			
			return pointer;
		}*/
		
		reset()
		{
			//
			this.pointer = new Map();
			
			//
		}

		identifyAs(_type)
		{
			this.className = this.name = _type;

			if(! this.id)
			{
				this.id = randomID();
				return true;
			}

			return false;
		}

		connectedCallback()
		{
			//
			Box.addToIndex(this);

			//
			this.apply();

			//
		}

		disconnectedCallback()
		{
			//
			Box.removeFromIndex(this);
		}

		static addToIndex(_box)
		{
			Box._INDEX.pushUnique(_box);
		}

		static removeFromIndex(_box)
		{
			Box._INDEX.remove(_box);
		}
		
		get parent()
		{
			if(this.parentNode)
			{
				return this.parentNode;
			}
			
			return null;
		}
		
		set parent(_value)
		{
			if(_value)
			{
				if(this.parentNode === _value)
				{
					return _value;
				}
				else if(this.parentNode)
				{
					this.parentNode.removeChild(this, this.animateStyle && this.parentAnimation, () => {
						_value.appendChild(this, this.animateStyle && this.parentAnimation);
					});
				}
				else
				{
					_value.appendChild(this, this.animateStyle && this.parentAnimation);
				}

				return _value;
			}
			else if(this.parentNode)
			{
				this.parentNode.removeChild(this, this.animateStyle && this.parentAnimation);
			}

			return null;
		}
	}

	//
	if(! customElements.get('a-box'))
	{
		customElements.define('a-box', Box);
	}

	//
	Box._INDEX = [];

	Object.defineProperty(Box, 'INDEX', { get: function()
	{
		const result = [];

		for(var i = 0, j = 0; i < Box._INDEX.length; ++i)
		{
			if(is(Box._INDEX[i], 'Box'))
			{
				result[j++] = Box._INDEX[i];
			}
		}

		return result;
	}});

	//
	const on = {};
	
	on.pointerdown = Box.onpointerdown.bind(Box);
	on.pointerup = Box.onpointerup.bind(Box);
	on.pointermove = Box.onpointermove.bind(Box);
	on.keydown = Box.onkeydown.bind(Box);
	
	for(const idx in on)
	{
		window.addEventListener(idx, on[idx]);
	}
	
	//

})();

