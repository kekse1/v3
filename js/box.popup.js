
	//
	//TODO/..
	//
	//--arrange: auto
	//--duration: 1600
	//--delay: 0
	//--pointer-duration: 1200
	//--data-duration: 1200
	//

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
			const result = new Popup();

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
			result.x = _event.clientX;
			result.y = _event.clientY;

			//
			DEFAULT_PARENT.appendChild(result, null, () => {
				result.open(_event, _callback);
			});

			//
			return result;
		}
	
		open(_event, _callback)
		{
			if(this.isOpen)
			{
				return this.move(_event);
			}
			else if(this.IN)
			{
				return this.move(_event);
			}
			else if(this.OUT)
			{
				return this.OUT.stop(() => {
					return this.open(_event, _callback);
				});
			}
			else if(this.forceDestroy)
			{
				if(! this.OUT)
				{
					return this.close(_event, _callback, true);
				}
				
				return this.move(_event);
			}

			const [ x, y ] = this.getPosition(_event.clientX, _event.clientY, this.offsetWidth, this.offsetHeight, this.getVariable('arrange', true), true);

			return this.in({
				duration: this.getVariable('duration', true), delay: 0,
				position: true, left: setValue(x, 'px'), top: setValue(y, 'px')
			}, (_e, _f) => {
				call(_callback, { type: 'open', event: _e, finish: _f }, _e, _f);
			});
		}
		
		close(_event, _callback, _force_destroy = false)
		{
			//
			if(this.isClosed)
			{
				return null;
			}
			else if(this.forceDestroy)
			{
				this.move(_event);

				if(!this.OUT && !this.isClosed)
				{
					return this.close(_event, _callback);
				}

				return null;
			}
			else
			{
				this.forceDestroy = !!_force_destroy;
			}

			if(this.OUT)
			{
				return this.move(_event);
			}
			else if(this.IN)
			{
				return this.IN.stop(() => {
					return this.close(_event, _callback, _force_destroy);
				});
			}

			//
			const callback = (_e, _f) => {
				call(_callback, { type: (_f ? 'destroy' : 'close'), event: _e, finish: _f }, _e, _f);
			};

			const [ x, y ] = this.getPosition(_event.clientX, _event.clientY, this.offsetWidth, this.offsetHeight, this.getVariable('arrange', true), true);
			
			return this.out({
				duration: this.getVariable('duration', true), delay: 0,
				position: true, left: setValue(x), top: setValue(y)
			}, (_e, _f) => {
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

		move(_event_x, _y, _animate = this.getVariable('pointer-animation', true), _callback, _throw = DEFAULT_THROW)
		{
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
			this.style.right = this.style.bottom = 'auto';

			if(!! _animate)
			{
				[ x, y ] = this.getPosition(x, y, this.offsetWidth, this.offsetHeight, this.getVariable('arrange', true), true);

				const keyframes = {
					right: [ 'auto' ],
					bottom: [ 'auto' ],
					left: [ setValue(x, 'px', true) ],
					top: [ setValue(y, 'px', true) ]
				};
				
				const options = {
					duration: ((isInt(_animate) && _animate >= 0) ? _animate : this.getVariable('pointer-duration', true)),
					delay: 0, persist: true, state: false
				};
				
				const moveIt = () => {
					return this.MOVE = this.animate(keyframes, options, (_e, _f) => {
						delete this.MOVE;
						call(_callback, { type: 'move', event: _e, finish: _f }, _e, _f);
					});
				}
				
				if(this.MOVE)
				{
					this.MOVE.finish(moveIt);
				}
				else
				{
					moveIt();
				}
			}
			else
			{
				this.x = x;
				this.y = y;
			}

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

		static onpointerup(_event, _callback)
		{
			if(_event.pointerType === 'mouse')
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
			const index = [ ... Popup.INDEX ];
			
			for(const p of index)
			{
				if(p.test(_event))
				{
					if(p.isOpen)
					{
						p.move(_event);
					}
					else
					{
						p.open(_event);
					}
				}
				else
				{
					if(! p.isClosed)
					{
						p.close(_event);
					}
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
			//
			//TODO/!!
			//w/ osd, see altes popup..js..
			//
			switch(_event.key)
			{
				case 'Control':
					Popup.pause = true;
					break;
			}
		}

		static onkeyup(_event)
		{
			//
			//TODO/!!
			//w/ osd, see altes popup..js..
			//
			switch(_event.key)
			{
				case 'Control':
					Popup.pause = false;
					break;
			}
		}

		static get pause()
		{
throw new Error('TODO');
		}

		static set pause(_value)
		{
throw new Error('TODO');
		}

		get pause()
		{
			return this.hasAttribute('pause');
		}

		set pause(_value)
		{
			if(_value = !!_value)
			{
				this.setAttribute('pause', '');
			}
			else
			{
				this.removeAttribute('pause');
			}

			return _value;
		}
	}
	
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
	on.pointerup = Popup.onpointerup.bind(Popup);
	on.keydown = Popup.onkeydown.bind(Popup);
	on.keyup = Popup.onkeyup.bind(Popup);

	for(const idx in on)
	{
		window.addEventListener(idx, on[idx], { passive: false, capture: true });
	}

	//
	
})();
