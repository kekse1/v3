
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

			//
			this._mode = 'closed';
		}

		static create(_event, _target = _event.target, _callback = null, _throw = DEFAULT_THROW)
		{
			//
			if(typeof _target.dataset.popup !== 'string')
			{
				if(_throw)
				{
					throw new Error('Your .dataset.popup is not a String, so we can\'t create a new Popup');
				}
				
				return null;
			}
			else if(_target.dataset.popup.length === 0)
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
			result.related = _target;
			_target.related = result;
			_target.isPopup = false;
			_target.popup = result;
			
			//
			DEFAULT_PARENT.appendChild(result, null, () => {
				result.style.opacity = '0';
				result.open(_event, false, _callback);
			});

			//
			return result;
		}
	
		open(_event, _update = true, _callback)
		{
			if(! _event)
			{
				return null;
			}
			else if(this.forceDestroy)
			{
				return false;
			}
			else if(_update)
			{
				return this.update(_event);
			}
		
			[ this.x, this.y ] = this.getPosition(_event.clientX, _event.clientY, this.offsetWidth, this.offsetHeight, this.getVariable('arrange', true), true);

			if(this._mode.startsWith('open'))
			{
				return this;
			}
			else
			{
				this._mode = 'opening';
			}
			
			return this.in({
				duration: this.getVariable('duration', true), delay: 0
			}, (_e, _f) => {
				if(_f)
				{
					this._mode = 'open';
				}
				else
				{
					this._mode = 'half';
				}

				call(_callback, { type: 'open', event: _e, finish: _f }, _e, _f);
			});
		}
		
		close(_event, _callback, _force_destroy = false)
		{
			//
			this.forceDestroy = _force_destroy = !!_force_destroy;

			//
			const callback = (_e, _f) => {
				call(_callback, { type: (_f ? 'destroy' : 'close'), event: _e, finish: _f }, _e, _f);
			};

			[ this.x, this.y ] = this.getPosition(_event.clientX, _event.clientY, this.offsetWidth, this.offsetHeight, this.getVariable('arrange', true), true);
			
			if(this._mode.startsWith('clos'))
			{
				return this;
			}
			else
			{
				this._mode = 'closing';
			}

			return this.out({
				duration: this.getVariable('duration', true), delay: 0//,
			}, (_e, _f) => {
				if(_f)
				{
					this._mode = 'closed';
				}
				else
				{
					this._mode = 'half';
				}

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

		update(_event, _move = true, _callback)
		{
			if(_event && _move)
			{
				this.move(_event);
			}
			
			if(!this.forceDestroy && typeof this.related.dataset.popup === 'string' && this.related.dataset.popup.length > 0)
			{
				if(this.related.dataset.popup !== (this._dataAnimation || this.innerHTML))
				{
					this.innerHTML = this.related.dataset.popup;
				}

				return this.open(_event, false, _callback);
			}
			else
			{
				return this.close(_event, _callback, !!this.forceDestroy);
			}
			
			return this;
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
			[ x, y ] = this.getPosition(x, y, this.offsetWidth, this.offsetHeight, this.getVariable('arrange', true), true);

			//
			if(!!_animate)
			{
				const keyframes = {
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
			//
			return super.destroy(null, () => {
				//
				this._mode = '';
			
				//
				Box._INDEX.remove(this);

				//
				if(this.related)
				{
					delete this.related.popup;
					delete this.related.related;
					delete this.related.isPopup;
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
		}
		
		get x()
		{
			return getComputedStyle(this).left;
		}
		
		set x(_value)
		{
			if(! isNumber(_value) && typeof _value !== 'string')
			{
				return null;
			}
			
			this.style.right = 'auto';
			_value = this.style.left = setValue(_value, 'px', true);
			return _value;
		}
		
		get y()
		{
			return getComputedStyle(this).top;
		}
		
		set y(_value)
		{
			if(! isNumber(_value) && typeof _value !== 'string')
			{
				return null;
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
				
				if(elements[i].isPopup && elements[i].related)
				{
					result[j++] = elements[i].related;
					continue;
				}
				
				if(elements[i].popup && elements[i].popup.isPopup)
				{
					result[j++] = elements[i];
					continue;
				}
				
				if(typeof elements[i].dataset.popup === 'string')
				{
					if(elements[i].dataset.popup.length === 0)
					{
						break;
					}

					result[j++] = elements[i];
					continue;
				}

				if(elements[i].getVariable('popup', true) === false)
				{
					break;
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
			
			if(! _popup.isVisible)
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
			const index = [ ... Popup.INDEX ];

			for(var i = 0, j = 0; i < index.length; ++i)
			{
				if(! index[i].test(_event))
				{
					index.splice(i--, 1)[0].close(_event);
				}
			}
			
			const popup = Popup.lookup(_event);
			
			if(popup) for(const p of popup)
			{
				if(p.popup?.isPopup)
				{
					if(p.popup.update(_event))
					{
						_event.preventDefault();
					}
				}
				else if(typeof p.dataset.popup === 'string' && p.dataset.popup.length > 0)
				{
					if(Popup.create({ clientX: _event.clientX, clientY: _event.clientY, target: p }, p))
					{
						_event.preventDefault();
					}
				}
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
