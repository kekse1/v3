(function()
{

	//
	const DEFAULT_THROW = true;
	const DEFAULT_PARENT = HTML;
		
	//
	Popup = Box.Popup = class Popup extends Box
	{
		constructor(... _args)
		{
			//
			super(... _args);

			//
			this.identifyAs('popup');
		}

		static create(_event, _target = _event.target, _callback = null, _throw = DEFAULT_THROW)
		{
			//
			if(typeof _target.dataset.popup !== 'string')
			{
				if(_target.isPopup)
				{
					_target = _target.related;
				}
				else if(_throw)
				{
					throw new Error('Your .dataset.popup is not a String, so we can\'t create a new Popup');
				}
				
				return null;
			}
			
			if(_target.dataset.popup.length === 0)
			{
				if(_throw)
				{
					throw new Error('Your .dataset.popup string is empty.. this means, we stop finding other popup in Popup.find(); ...');
				}
				
				return null;
			}
			else if(! _target.isVisible)
			{
				if(_throw)
				{
					throw new Error('Your _target is !.isVisible');
				}
				
				return null;
			}

			//
			const result = new Popup({
				left: setValue(_event.clientX, 'px', true),
				top: setValue(_event.clientY, 'px', true)
			});

			Box._INDEX.pushUnique(result);

			//
			result.innerHTML = _target.dataset.popup;
			
			//
			result.isPopup = true;
			result.hasPopup = null;
			result.related = _target;
			_target.related = result;
			_target.isPopup = false;
			_target.hasPopup = true;
			_target.popup = result;

			//
			DEFAULT_PARENT.appendChild(result, null, () => {
				result.open(_event, _callback);
			});

			//
			return result;
		}
		
		disconnectedCallback()
		{
			if(Popup.INDEX.length <= 1)
			{
				Popup.pause = false;
			}
			
			return super.disconnectedCallback();
		}
	
		open(_event, _callback)
		{
			if(this.pause || Popup.pause)
			{
				return;
			}
			else if(_event)
			{
				this.move(_event);
			}

			if(this.isOpen)
			{
				return null;
			}
			else if(this.IN)
			{
				return this.IN;
			}
			else if(this.OUT)
			{
				return this.OUT.stop(() => {
					return this.open(_event, _callback);
				});
			}
			else
			{
				this.isClosed = false;
			}

			if(this.forceDestroy)
			{
				if(this.OUT)
				{
					return this.OUT.stop(() => {
						return this.close(_event, _callback);
					});
				}

				return this.close(_event, _callback, true);
			}

			return this.in({
				duration: this.getVariable('duration', true), delay: 0
			}, (_e, _f) => {
				this.isOpen = _f;
				call(_callback, { type: 'open', event: _e, finish: _f }, _f);
			});
		}
		
		close(_event, _callback, _force_destroy = false)
		{
			//
			if(this.pause || Popup.pause)
			{
				return;
			}
			else if(_event)
			{
				this.move(_event);
			}
			
			//
			if(this.isClosed)
			{
				return null;
			}
			else
			{
				this.forceDestroy = !!_force_destroy;
			}

			if(this.OUT)
			{
				return this.OUT;
			}
			else if(this.IN)
			{
				return this.IN.stop(() => {
					return this.close(_event, _callback, _force_destroy);
				});
			}
			else
			{
				this.isOpen = false;
			}

			//
			const callback = (_e, _f) => {
				call(_callback, { type: (_f ? 'destroy' : 'close'), event: _e, finish: _f }, _f);
			};

			const [ x, y ] = this.getPosition(_event.clientX, _event.clientY, this.offsetWidth, this.offsetHeight, this.getVariable('arrange', true), true);
			
			return this.out({
				duration: this.getVariable('duration', true), delay: 0
			}, (_e, _f) => {
				this.isClosed = _f;

				if(_f || this.forceDestroy)
				{
					this.destroy(_event, callback);
				}
				else
				{
					call(callback, _e, _f);
				}
			});
		}

		move(_event_x, _y)
		{
			//
			if(this.pause || Popup.pause)
			{
				return;
			}
			
			//
			var x, y;
			
			if(isNumber(_event_x) && isNumber(_y))
			{
				x = _event_x;
				y = _y;
			}
			else if(isObject(_event_x) && isNumber(_event_x.clientX) && isNumber(_event_x.clientY))
			{
				x = _event_x.clientX;
				y = _event_x.clientY;
			}
			else if(_throw)
			{
				throw new Error('Invalid or missing argument(s)');
			}
			else
			{
				return null;
			}
			
			//
			this.x = x;
			this.y = y;

			//
			return [ x, y ]
		}
		
		destroy(_event, _callback, ... _args)
		{
			return super.destroy(null, () => {
				//
				Box._INDEX.remove(this);

				//
				if(this.related)
				{
					delete this.related.popup;
					delete this.related.related;
					delete this.related.isPopup;
					delete this.related.hasPopup;
					delete this.related;
				}
			
				//
				call(_callback, { type: 'destroy', this: this });
			});
		}
		
		//
		//TODO/for animations..
		//
		measureData(_event)
		{
			//
throw new Error('TODO');
		}
		
		get x()
		{
			return getValue(getComputedStyle(this).left, 'px');
		}
		
		set x(_value)
		{
			if(! isNumber(_value) && typeof _value !== 'string')
			{
				return null;
			}
			else if(typeof _value === 'number')
			{
				const pos = this.getPosition(_value, this.y, this.offsetWidth, this.offsetHeight, this.getVariable('arrange', true), true);
				_value = pos[0];
			}
			
			this.style.right = 'auto';
			_value = this.style.left = setValue(_value, 'px', true);
			return _value;
		}
		
		get y()
		{
			return getValue(getComputedStyle(this).top, 'px');
		}
		
		set y(_value)
		{
			if(! isNumber(_value) && typeof _value !== 'string')
			{
				return null;
			}
			else if(typeof _value === 'number')
			{
				const pos = this.getPosition(this.x, _value, this.offsetWidth, this.offsetHeight, this.getVariable('arrange', true), true);
				_value = pos[1];
			}
			
			this.style.bottom = 'auto';
			_value = this.style.top = setValue(_value, 'px', true);
			return _value;
		}

		static lookup(_event_x, _y, _throw = DEFAULT_THROW)
		{
			var x, y;
			
			if(isNumber(_event_x) && isNumber(_y))
			{
				x = _event_x;
				y = _y;
			}
			else if(isObject(_event_x) && isNumber(_event_x.clientX) && isNumber(_event_x.clientY))
			{
				x = _event_x.clientX;
				y = _event_x.clientY;
			}
			else if(_throw)
			{
				throw new Error('Invalid or missing argument(s)');
			}
			else
			{
				return null;
			}
			
			const result = [];
			const elements = document.elementsFromPoint(x, y);

			for(var i = 0, j = 0; i < elements.length; ++i)
			{
				if(! elements[i].isVisible)
				{
					continue;
				}
				else if(typeof elements[i].dataset.popup === 'string')
				{
					if(elements[i].isPopup || elements[i].hasPopup)
					{
						continue;
					}
					else if(elements[i].dataset.popup.length > 0)
					{
						result[j++] = elements[i];
					}
					else
					{
						break;
					}
				}
			}

			return result;
		}
		
		static test(_event_x, _y, _popup, _throw = DEFAULT_THROW)
		{
			if(! _popup)
			{
				if(_throw)
				{
					throw new Error('No _popup defined');
				}
				
				return null;
			}
			
			var x, y;
			
			if(isNumber(_event_x) && isNumber(_y))
			{
				x = _event_x;
				y = _y;
			}
			else if(isObject(_event_x) && isNumber(_event_x.clientX) && isNumber(_event_x.clientY))
			{
				x = _event_x.clientX;
				y = _event_x.clientY;
			}
			else if(_throw)
			{
				throw new Error('Invalid or missing argument(s)');
			}
			else
			{
				return null;
			}

			var result;
			const r = _popup.related;
			
			if(!r)
			{
				result = false;
			}
			else if(! r.isVisible)
			{
				result = false;
			}
			else if(typeof r.dataset.popup !== 'string' || r.dataset.popup.length === 0)
			{
				result = false;
			}
			else if(x < r.offsetLeft)
			{
				result = false;
			}
			else if(y < r.offsetTop)
			{
				result = false;
			}
			else if(x > (r.offsetLeft + r.offsetWidth))
			{
				result = false;
			}
			else if(y > (r.offsetTop + r.offsetHeight))
			{
				result = false;
			}
			else
			{
				result = true;
			}
			
			return result;
		}
		
		test(_event_x, _y, _throw = DEFAULT_THROW)
		{
			return Popup.test(_event_x, _y, this, _throw);
		}

		static onpointerdown(_event, _callback)
		{
			if(Popup.pause)
			{
				return;
			}
			//TODO/
			//popup.clear(false, _callback); ..
		}

		static onpointerup(_event, _callback)
		{
			//
			if(Popup.pause)
			{
				return;
			}
			else if(_event.pointerType === 'mouse')
			{
				return;
			}
			
			var count = 0;
			const cb = () => {
				if(--count <= 0)
				{
					call(_callback, { type: 'pointerup', closed: result, event: _event }, _event);
				}
			};

			const index = Popup.INDEX;
			var result = 0;

			for(var i = 0; i < index.length; ++i)
			{
				++count;

				if(index[i].close({ clientX: _event.clientX, clientY: _event.clientY, target: index[i], type: 'pointerup', pointerType: 'mouse' }, cb))
				{
					_event.preventDefault();
					++result;
				}
			}

			return result;
		}
		
		static onpointermove(_event)
		{
			//
			if(Popup.pause)
			{
				return;
			}
			
			//
			const index = [ ... Popup.INDEX ];

			for(const p of index)
			{
				if(p.test(_event))
				{
					p.open(_event);
				}
				else
				{
					p.close(_event);
				}
			}
			
			//
			const elements = Popup.lookup(_event);

			for(const e of elements)
			{
				Popup.create({ clientX: _event.clientX, clientY: _event.clientY, target: e }, e);
			}
		}
		
		static onkeydown(_event)
		{
			switch(_event.key)
			{
				case 'Control':
					Popup.pause = true;
					break;
			}
		}

		static onkeyup(_event)
		{
			switch(_event.key)
			{
				case 'Control':
					Popup.pause = false;
					break;
			}
		}
		
		static blink(_options)
		{
			const index = Popup.INDEX;
			
			for(const p of index)
			{
				p.blink(_options);
			}
			
			return index.length;
		}

		//static clear(..

		static get pause()
		{
			return Popup.paused;
		}
		
		static set pause(_value)
		{
			if(Popup.INDEX.length === 0)
			{
				Popup.paused = false;
				return null;
			}
			else if(Popup.paused === (_value = !!_value))
			{
				return false;
			}
			else if(_value)
			{
				osd(pauseON);
			}
			else
			{
				osd(pauseOFF);
			}
			
			Popup.blink();
			return Popup.paused = _value;
		}

		get pause()
		{
			return this.hasAttribute('pause');
		}

		set pause(_value)
		{
			if(this.pause === (_value = !!_value))
			{
				return false;
			}
			else if(_value = !!_value)
			{
				this.setAttribute('pause', '');
			}
			else
			{
				this.removeAttribute('pause');
			}

			this.blink();
			return _value;
		}
	}
	
	//
	const pauseON = '<span style="font-size: 0.7em; color: green;">ON</span><span style="font-size: 0.4em; color: blue;">freeze</span>';
	const pauseOFF = '<span style="font-size: 0.7em; color: red;">OFF</span><span style="font-size: 0.4em; color: blue;">freeze</span>';
	
	//
	Popup.paused = false;
	
	//
	if(! customElements.get('a-popup'))
	{
		customElements.define('a-popup', Popup, { is: 'a-box' });
	}
	
	//
	Object.defineProperty(Popup, 'INDEX', { get: function()
	{
		const result = [];
		
		for(var i = 0, j = 0; i < Box._INDEX.length; ++i)
		{
			if(is(Box._INDEX[i], 'Popup'))
			{
				result[j++] = Box._INDEX[i];
			}
		}
		
		return result;
	}});
	
	//
	const on = {};

	on.pointermove = Popup.onpointermove.bind(Popup);
	on.pointerdown = Popup.onpointerdown.bind(Popup);
	on.pointerup = Popup.onpointerup.bind(Popup);
	on.keydown = Popup.onkeydown.bind(Popup);
	on.keyup = Popup.onkeyup.bind(Popup);

	for(const idx in on)
	{
		window.addEventListener(idx, on[idx], { passive: false, capture: true });
	}

	//
	
})();
