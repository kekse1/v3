(function()
{

	//
	const DEFAULT_THROW = true;

	//
	Menu = Box.Menu = class Menu extends Box
	{
		constructor(... _args)
		{
			//
			super(... _args);

			//
			this.identifyAs('menu');

			//
			this.isShowing = this.isHiding = false;
		}

		static outItems(_event, _callback, ... _exclude)
		{
			//
			const index = [ ... Menu.INDEX ];

			if(index.length === 0)
			{
				return 0;
			}
			
			var called = false;
			var count = 0;
			var result = 0;
			var total = 0;
			
			const cb = (_force = false) => {
				if(--count <= 0 || _force)
				{
					if(typeof _callback === 'function')
					{
						if(! called)
						{
							_callback({ type: 'outItems', count: result });
							called = true;
						}
					}
					else
					{
						called = true;
					}
				}
			};
			
			for(var i = 0; i < index.length; ++i)
			{
				const menu = index[i];
				
				if(_exclude.includes(menu))
				{
					continue;
				}
				else
				{
					++count;
					++total;
				}
				
				const item = index[i].outItems(_event, () => { cb(false); }, ... _exclude);
				
				if(isArray(item, false))
				{
					result += item.length;
				}
				else
				{
					cb(false);
				}
			}
			
			if(total === 0)
			{
				cb(true);
			}
			/*else
			{
				setTimeout(() => {
					if(! called)
					{
						cb(true);
					}
				}, 200);
			}*/

			return result;
		}

		outItems(_event, _callback, ... _exclude)
		{
			//
			_event = {
				clientX: (_event ? _event.clientX : null), clientY: (_event ? _event.clientY : null),
				type: 'outItems', pointerType: 'manu', target: null, event: (_event || null) };

			if(typeof _callback !== 'function')
			{
				_callback = null;
			}

			//
			if(this._outItems)
			{
				_event.finish = false;
				_event.count = -1;
				
				if(_callback)
				{
					_callback(_event);
				}
				
				return null;
			}
			else
			{
				this._outItems = true;
				_event.finish = true;
			}

			//
			const index = [ ... this.children ];
			const result = [];
			
			if(index.length === 0)
			{
				this._outItems = false;
				
				if(_callback)
				{
					_event.count = 0;
					_callback(_event);
				}
				
				return result;
			}
			
			var called = false;
			var count = 0;
			var total = 0;
			
			const cb = (_node, _force = false) => {
				if(_node)
				{
					result.push(_node);
				}

				if(--count <= 0 || _force)
				{
					this._outItems = false;
					_event.count = result.length;
					
					if(_callback)
					{
						if(! called)
						{
							_callback(_event);
							called = true;
						}
					}
					else
					{
						called = true;
					}
				}
			};
			
			for(var i = 0; i < index.length; ++i)
			{
				const node = _event.target = index[i];
				
				if(_exclude.includes(node))
				{
					continue;
				}
				else
				{
					++count;
					++total;
				}
				
				if(! Menu.Item.onpointerout(_event, index[i], () => { cb(node, false); }, false, false))
				{
					cb(null, false);
				}
			}
			
			if(total === 0)
			{
				cb(null, true);
			}
			/*else
			{
				setTimeout(() => {
					if(! called)
					{
						cb(null, true);
					}
				}, 200);
			}*/
			
			return result;
		}

		reset(... _args)
		{
			//
			if(this.childNodes.length > 0)
			{
				this.hide(() => {
					this.items.length = 0;
				}, false);
			}
			else if(this.items)
			{
				this.items.length = 0;
			}
			else
			{
				this.items = [];
			}

			//
			if(this.randomAnimations)
			{
				this.stopRandomAnimation();
				this.randomAnimations.length = 0;
			}
			else
			{
				this.randomAnimations = [];
			}

			//
			this.HEIGHT = this.offsetTop;

			//
			return super.reset(... _args);
		}
		
		hide(_callback, _animate = this.getVariable('animate', true))
		{
			if(! this.parentNode)
			{
				_animate = false;
			}
			else if(typeof _animate !== 'boolean')
			{
				_animate = this.getVariable('animate', true);
			}

			if(this.childNodes.length === 0)
			{
				return 0;
			}
			else if(this.isHiding)
			{
				return -1;
			}
			else if(this.isShowing)
			{
				return this.isShowingAbort = () => {
					return this.hide(_callback, _animate);
				};
			}
			else
			{
				this.isHiding = true;
				this.isShowing = false;

				this.stopRandomAnimation();
			}

			var rest = 0;
			const callback = (_node, _item, _text, _index) => {
				//
				_node._state = '';
				_node.isHiding = false;
				_node.isShowing = false;

				//
				if(_node)
				{
					delete _node.imageNode;
					delete _node.textNode;
					delete _node.node;

					this._removeChild(_node);
					_node.removeFromIndex();
				}

				if(_item)
				{
					delete _item.imageNode;
					delete _item.textNode;
					delete _item.node;

					_node._removeChild(_item);
				}

				if(_text)
				{
					delete _text.textNode;
					delete _text.imageNode;
					delete _text.node;

					_node._removeChild(_text);
				}

				if(--rest <= 0)
				{
					this.isHiding = false;

					window.removeEventListener('keydown', onkeydown);

					this.style.setProperty('height', setValue(this.HEIGHT = this.offsetTop, 'px'));

					call(_callback, { type: 'hide', items: [ ... this.items ], childNodes: [ ... this.childNodes ],
						node: _node, item: _item, text: _text, index: _index }, _node, _item, _text, _index);

					setTimeout(() => {
						this.stopRandomAnimation();
					}, 0);
				}

				if('isHidingAbort' in this)
				{
					const func = this.isHidingAbort;
					delete this.isHidingAbort;

					if(typeof func === 'function')
					{
						setTimeout(() => {
							return func(_callback, _animate);
						}, 0);
					}
				}
			};

			var DELAY = 0;
			var options = (_animate ? { duration: this.getVariable('duration', true), delay: this.getVariable('wait-out', true), persist: true } : null);
			var abort = false;
			const animations = [];

			const onkeydown = (_event) => {
				if(_event.key === 'Escape')
				{
					window.removeEventListener('keydown', onkeydown);
					_event.preventDefault();
					abort = true;
				}
			};

			window.addEventListener('keydown', onkeydown);

			const childNodes = [ ... this.childNodes ];

			const loop = (_index, _rest = rest) => {
				//
				const index = _index;

				const node = childNodes[index];
				const imageNode = node.imageNode;
				const textNode = node.textNode;

				node._state = '';
				node.isShowing = false;
				node.isHiding = true;

				//
				const func = () => {
					//
					imageNode.style.setProperty('transform', 'none');
					imageNode.style.setProperty('filter', 'none');
					
					//
					if(options && !abort)
					{
						//
						imageNode._makeLeftAnimation = imageNode.setStyle('left', setValue(-(imageNode.offsetWidth + imageNode.offsetLeft + 1)), { persist: true });
						textNode._makeLeftAnimation = imageNode.setStyle('left', setValue(-(textNode.offsetWidth + textNode.offsetLeft + 1)), { persist: true });
						textNode.setHTML(null, '', this.getVariable('data-duration', true), this.getVariable('data-delay', true));

						//
						imageNode.out(options, (_e, _f) => {
							imageNode.style.setProperty('filter', 'none');
							imageNode.style.setProperty('transform', 'none');
							imageNode.style.setProperty('opacity', '0');
							callback(node, imageNode, textNode, index);
						});
					}
					else
					{
						imageNode.style.setProperty('left', setValue(-(imageNode.offsetWidth + imageNode.offsetLeft + 1), 'px'));
						textNode.style.setProperty('left', setValue(-(textNode.offsetWidth + textNode.offsetLeft + 1), 'px'));
						textNode.innerHTML = '';
						callback(node, imageNode, textNode, index);
					}
				};

				if(options && !abort)
				{
					animations.push([ setTimeout(func, DELAY += this.getVariable('delay', true)), func, false ]);
				}
				else
				{
					animations.push([ null, func, false ]);
				}
			};

			switch(this.getVariable('hide').toLowerCase())
			{
				case 'forwards':
					for(var i = 0; i < childNodes.length; ++i)
					{
						/*++rest;
						const index = i;
						setTimeout(() => { loop(index); }, 0);*/
						loop(i, ++rest);
					}
					break;
				case 'backwards':
				default:
					for(var i = childNodes.length - 1; i >= 0; --i)
					{
						/*++rest;
						const index = i;
						setTimeout(() => { loop(index); }, 0);*/
						loop(i, ++rest);
					}
					break;
			}

			if(abort || !options)
			{
				for(var i = 0; i < animations.length; ++i)
				{
					//
					const [ timeout, func, done ] = [ ... animations[i] ];
					
					//
					if(done)
					{
						continue;
					}
					else
					{
						animations[i][2] = true;
					}
					
					//
					if(timeout !== null)
					{
						clearTimeout(timeout);
						animations[i][0] = null;
					}
					
					//
					func();
				}
			}

			return animations;
		}

		show(_callback, _animate = this.getVariable('animate', true))
		{
			if(! this.parentNode)
			{
				_animate = false;
			}
			else if(typeof _animate !== 'boolean')
			{
				_animate = this.getVariable('animate', true);
			}

			if(this.items.length === 0)
			{
				return 0;
			}
			else if(this.isShowing)
			{
				return -1;
			}
			else if(this.isHiding)
			{
				return this.isHidingAbort = () => {
					return this.hide(_callback, _animate);
				};
			}
			else if(this.childNodes.length > 0)
			{
				return this.hide(() => {
					return this.show(_callback, _animate);
				}, false);
			}
			else
			{
				this.isShowing = true;
				this.isHiding = false;
			}

			var rest = 0;
			const callback = (_node, _item, _text, _index) => {
				_node._state = 'out';
				_node.isShowing = false;
				_node.isHiding = false;

				if(--rest <= 0)
				{
					this.isShowing = false;

					window.removeEventListener('keydown', onkeydown);

					call(_callback, { type: 'show', items: [ ... this.items ], childNodes: [ ... this.childNodes ],
						node: _node, item: _item, text: _text, index: _index },
							_node, _item, _text, _index);

					setTimeout(() => {
						this.startRandomAnimation();
					}, 0);
				}

				if('isShowingAbort' in this)
				{
					const func = this.isShowingAbort;
					delete this.isShowingAbort;

					if(typeof func === 'function')
					{
						setTimeout(() => {
							return func(_callback, _animate);
						}, 0);
					}
				}
			};
			
			const IMG = this.getVariable('image-sin', ['px']);
			const TXT = this.getVariable('text-sin', ['px']);
			const SIN = this.getVariable('sin', true);

			const SCALE = 0.5;
			var DELAY = 0;
			var options = (_animate ? { duration: this.getVariable('duration', true), delay: this.getVariable('wait-in', true), persist: true } : null);
			this.clear(null, null);
			var abort = false;
			const animations = [];
			const WIDTH = MAIN.offsetLeft;
			this.HEIGHT = this.offsetTop;

			const onkeydown = (_event) => {
				if(_event.key === 'Escape')
				{
					window.removeEventListener('keydown', onkeydown);
					_event.preventDefault();
					abort = true;
				}
			};

			window.addEventListener('keydown', onkeydown);

			for(var i = 0; i < this.items.length; ++i)
			{
				//
				++rest;

				//
				const index = i;
				const currentItem = this.items[index];

				const node = currentItem.getItemNode(SCALE, this);
				const imageNode = node.imageNode;
				const textNode = node.textNode;

				node._state = '';
				node.isHiding = false;
				node.isShowing = true;

				imageNode.leftOver = ((imageNode.leftOut = IMG) * 2);
				textNode.leftOver = ((textNode.leftOut = TXT) * 2);

				imageNode.style.setProperty('opacity', '0');
				
				//
				const func = () => {
					//
					node.psin = (Math.psin(index / this.items.length * 2.5 * SIN * Math.PI - 1.25));

					imageNode.leftOut = Math.round(imageNode.leftOut * node.psin);
					imageNode.leftOver = Math.round(imageNode.leftOver * node.psin);

					textNode.leftOut = Math.round(textNode.leftOut * (1 - node.psin));
					textNode.leftOver = Math.round(textNode.leftOver * (1 - node.psin));

					imageNode.style.setProperty('left', setValue(-(imageNode.offsetWidth + imageNode.offsetLeft + 1), 'px'));
					textNode.style.setProperty('left', setValue(-(textNode.offsetWidth + textNode.offsetLeft + 1), 'px'));

					//
					node.style.setProperty('width', setValue(WIDTH, 'px'));
					//node.style.setProperty('left', '0');
					
					//
					imageNode.style.setProperty('transform', 'none');
					imageNode.style.setProperty('filter', 'none');

					//
					if(options && !abort)
					{
						imageNode._makeOpacityAnimation = imageNode.setStyle('opacity', '1', Math.round(DELAY * 1.25));
						imageNode._makeLeftAnimation = imageNode.setStyle('left', setValue(imageNode.leftOut), Math.round(DELAY / 4));
						textNode._makeLeftAnimation = textNode.setStyle('left', setValue(textNode.leftOut), Math.round(DELAY / 10));

						imageNode.in(options, () => {
							imageNode.style.setProperty('opacity', '1');

							if(abort)
							{
								textNode.innerHTML = imageNode.data;
							}
							else
							{
								textNode.setHTML(null, imageNode.data, this.getVariable('data-duration', true), this.getVariable('data-delay', true));
							}

							callback(node, imageNode, textNode, index);
						});
					}
					else
					{
						imageNode.style.setProperty('opacity', '1');
						imageNode.style.setProperty('left', setValue(imageNode.leftOut, 'px'));
						imageNode.style.setProperty('filter', 'none');
						textNode.style.setProperty('left', setValue(textNode.leftOut, 'px'));
						textNode.innerHTML = imageNode.data;
						callback(node, imageNode, textNode, index);
					}
				};
				
				if(options && !abort)
				{
					animations.push([ setTimeout(func, DELAY += this.getVariable('delay', true)), func, false ]);
				}
				else
				{
					animations.push([ null, func, false ]);
				}
			}

			if(abort || !options)
			{
				for(var i = 0; i < animations.length; ++i)
				{
					//
					const [ timeout, func, done ] = [ ... animations[i] ];
					
					//
					if(done)
					{
						continue;
					}
					else
					{
						animations[i][2] = true;
					}
					
					//
					if(timeout !== null)
					{
						clearTimeout(timeout);
						animations[i][0] = null;
					}
					
					//
					func();
				}
			}

			return animations;
		}
		
		stopRandomAnimation()
		{
			if(this.randomAnimations.length === 0)
			{
				return false;
			}
			else while(this.randomAnimations.length > 0)
			{
				clearTimeout(this.randomAnimations.shift());
			}
			
			return true;
		}

		startRandomAnimation()
		{
			if(! this.getVariable('random-animation', true))
			{
				return false;
			}
			else if(this.randomAnimations.length > 0)
			{
				this.stopRandomAnimation();
			}
			
			const callback = () => {
				var duration = this.getVariable('random-animation-duration', true);
				var delay = this.getVariable('random-animation-delay', true);

				if(isArray(duration))
				{
					duration = Math.random.int(duration[0], duration[1], true);
				}
				else if(! (isInt(duration) && duration >= 0))
				{
					throw new Error('Unexpected');
				}

				if(isArray(delay))
				{
					delay = Math.random.int(delay[0], delay[1], true);
				}
				else if(! (isInt(delay) && delay >= 0))
				{
					throw new Error('Unexpected');
				}

				if(this.childNodes.length === 0)
				{
					return this.randomAnimations = false;
				}

				var item;

				do
				{
					if((item = this.childNodes[Math.random.int(this.childNodes.length, 0, false)])._state === 'out')
					{
						item._state = 'animating';
					}
				}
				while(item._state !== 'animating');

				return item.imageNode.randomAnimation = item.imageNode.animate({
					transform: [ 'none', 'rotateZ(' + (Math.random.bool() ? '+' : '-') + '720deg)' ]
				}, {
					duration, delay: 0, state: false, persist: false, origin: 'center'
				}, (_e, _f) => {
					//
					if(item)
					{
						if(item._state === 'animating')
						{
							item._state = 'out';
						}

						if(item.imageNode)
						{
							delete item.imageNode.randomAnimation;
						}
					}

					//
					if(this.randomAnimations.length > 0)
					{
						this.stopRandomAnimation();
						this.randomAnimations.push(setTimeout(callback, delay));
					}
				});
			};
			
			this.randomAnimations.push(setTimeout(callback, 0));
		}
		
		connectedCallback()
		{
			//
			super.connectedCallback();

			//
			if(typeof this._windowResizeListener !== 'function')
			{
				var resizeTimeout = null;

				window.addEventListener('resize', this._windowResizeListener = () => {
					if(resizeTimeout !== null)
					{
						clearTimeout(resizeTimeout);
					}

					resizeTimeout = setTimeout(() => {
						resizeTimeout = null;

						if(this.isShowing)
						{
							this.isShowingAbort = () => {
								this.show(null, false);
							};
						}
						else
						{
							this.show(null, false);
						}
					}, document.getVariable('resize-timeout'));
				});
			}
			
			//
			setTimeout(() => {
				this.show();
			}, this.getVariable('wait', true));
		}

		disconnectedCallback()
		{
			//
			if(typeof this._windowResizeListener === 'function')
			{
				window.removeEventListener('resize', this._windowResizeListener);
			}
			
			//
			this.stopRandomAnimation();

			//
			return super.disconnectedCallback();
		}
		
		static load(... _args)
		{
			const options = Object.assign(... _args);
			var json = (isString(options.json, false) ? options.json : null);
			var callback = ((typeof options.callback === 'function' || typeof options.callback === 'boolean') ? options.callback : null);
			delete options.json;
			delete options.callback;
			
			for(var i = 0; i < _args.length; ++i)
			{
				if(typeof _args[i] === 'boolean')
				{
					callback = _args.splice(i--, 1)[0];
				}
				else if(typeof _args[i] === 'function')
				{
					callback = _args.splice(i--, 1)[0];
				}
				else if(isString(_args[i], false))
				{
					json = _args.splice(i--, 1)[0];
				}
				else if(was(_args[i], 'Element'))
				{
					options.parent = _args.splice(i--, 1)[0];
				}
				else if(isObject(_args[i]))
				{
					_args.splice(i--, 1);
				}
			}
			
			const result = this.create(... _args, options);
			result.load(json, callback);
			return result;
		}
		
		load(... _args)
		{
			const options = Object.assign(... _args);
			var json = (isString(options.json, false) ? options.json : null);
			var callback = ((typeof options.callback === 'function' || typeof options.callback === 'boolean') ? options.callback : null);
			
			for(var i = 0; i < _args.length; ++i)
			{
				if(typeof _args[i] === 'boolean')
				{
					callback = _args.splice(i--, 1)[0];
				}
				else if(typeof _args[i] === 'function')
				{
					callback = _args.splice(i--, 1)[0];
				}
				else if(isString(_args[i], false))
				{
					json = _args.splice(i--, 1)[0];
				}
				else if(isObject(_args[i]))
				{
					_args.splice(i--, 1);
				}
			}

			if(json === null)
			{
				throw new Error('Missing JSON (String) argument');
			}
			else
			{
				json = path.resolve(json);
			}
			
			if(callback === null)
			{
				callback = this.getVariable('callback', true);
			}

			//
			const rest = (_event, _request, _options) => {
				if(_request.statusClass !== 2)
				{
					throw new Error('Couldn\'t load \'' + (_request.responseURL || json) + '\': [' + _request.status + '] ' + _request.statusText);
				}
				
				const items = JSON.parse(_request.responseText);
				
				if(! isArray(items, true))
				{
					throw new Error('The file \'' + (_request.responseURL || json) + '\' contains no Array');
				}
				else if(items.length === 0)
				{
					return;
				}
				
				var abort = false;
				var count = items.length;
				const cb = (_event) => {
					if(_event.type === 'error')
					{
						abort = true;
						this.items.length = 0;
						const favicon = Menu.getFavicon();

						if(favicon)
						{
							this.items[0] = Menu.Item.create({ image: favicon }, this, (_e, _f) => {
								if(_e.type === 'error')
								{
									this.items.length = 0;
								}
								else
								{
									this.show();
								}
							});
						}
					}
					else if(--count <= 0)
					{
						//
						if(this.isConnected)
						{
							setTimeout(() => {
								this.show();
							}, this.getVariable('wait', true));
						}

						//
						call(callback, { type: 'load', items });
					}
				};

				var item;
				
				for(var i = 0; i < items.length; ++i)
				{
					if(abort)
					{
						break;
					}

					this.items[i] = Menu.Item.create(items[i], this, cb);
				}
			};
			
			const result = ajax(json, (callback ? rest : null), options, { osd: false, console: !__INIT });
			
			if(callback)
			{
				return result;
			}

			return rest(null, result, null);
		}
		
		static get scale()
		{
			return Menu.Item.scale;
		}

		scaleItemSize(_value, _max, _min, _throw = DEFAULT_THROW)
		{
			for(var i = 0; i < this.items.length; ++i)
			{
				this.items[i].scaleImageSize(_value, _max, _min, _throw);
			}

			return this.items.length;
		}
		
		setItemSize(_size, _throw = DEFAULT_THROW)
		{
			for(var i = 0; i < this.items.length; ++i)
			{
				this.items[i].setImageSize(_size, _throw);
			}

			return this.items.length;
		}

		setItemOpacity(_opacity)
		{
			if(! isNumber(_opacity))
			{
				throw new Error('Invalid _opacity value');
			}
			
			if(_opacity > 1)
			{
				_opacity = 1;
			}
			else if(_opacity < -1)
			{
				_opacity = -1;
			}

			if(_opacity < 0)
			{
				_opacity = (1 + _opacity);
			}

			for(var i = 0; i < this.items.length; ++i)
			{
				this.items[i].setStyle('opacity', _opacity.toString(), true);
			}

			return this.items.length;
		}

		static get getFavicon()
		{
			return Menu.Item.getFavicon;
		}
	}

	//
	Menu.Item = class MenuItem extends HTMLImageElement
	{
		constructor(_options, _menu, _callback)
		{
			//
			super();
			
			//
			if(was(_menu, 'Menu'))
			{
				this.menu = _menu;
			}
			else if(was(_options.menu, 'Menu'))
			{
				this.menu = _options.menu;
			}
			
			delete _options.menu;
			
			//
			if(typeof _callback === 'function')
			{
				this.callback = _callback;
			}
			else if(typeof _options.callback === 'function')
			{
				this.callback = _options.callback;
			}
			/*else if(typeof _callback === 'boolean')//FIXME/TODO/!?!?!
			{
				this.callback = _callback;
			}
			else if(typeof _options.callback === 'boolean')
			{
				this.callback = _options.callback;
			}*/
			
			delete _options.callback;

			//
			this.name = 'menuItem';
			this.className = 'menuItemImage';
			this.id = randomID();
			
			//
			this.draggable = false;

			//
			this.checkOptions(_options);
		}

		getItemNode(_scale, _parent)
		{
			//
			if(! _parent)
			{
				if(this.menu)
				{
					_parent = this.menu;
				}
				else
				{
					throw new Error('Invalid _parent argument');
				}
			}
			else if(this.menu && this.menu !== _parent)
			{
				throw new Error('DEBUG (unexpected)');
			}

			if(isNumber(_scale))
			{
				this.scaleImageSize(_scale, this.menu.getVariable('max', ['px']), this.menu.getVariable('min', ['px']));
			}

			//
			const result = document.createElement('div');
			result.className = 'menuItem';
			result.name = 'item';
			result.id = randomID();

			//
			Box._INDEX.pushUnique(result);

			result.removeFromIndex = () => {
				return Box._INDEX.remove(result);
			};

			//
			const textNode = document.createElement('div');
			textNode.className = 'menuItemText';
			textNode.innerHTML = this.data;

			this.style.setProperty('opacity', '0');

			result.node = result;
			result.textNode = textNode;
			const imageNode = result.imageNode = this;

			this.node = result;
			this.textNode = result.textNode;
			this.imageNode = result.imageNode;

			this.node.style.setProperty('transformOrigin', this.textNode.style.transformOrigin = this.imageNode.style.transformOrigin = this.getVariable('transform-origin'));

			textNode.node = result;
			textNode.imageNode = result.imageNode;
			textNode.textNode = textNode;

			result._appendChild(result.imageNode);
			result._appendChild(result.textNode);

			_parent._appendChild(result)

			//
			const textHeight = result.textNode.totalHeight;
			const itemHeight = result.imageNode.totalHeight;
			const totalHeight = (textHeight + itemHeight);

			//
			textNode.innerHTML = '';

			//
			result.style.setProperty('top', setValue(_parent.HEIGHT, 'px'));
			//result.style.setProperty('left', '0');

			//
			result.style.setProperty('top', result.imageNode.style.top = setValue(_parent.HEIGHT, 'px'));
			result.style.setProperty('height', setValue(totalHeight, 'px'));

			//
			_parent.HEIGHT += totalHeight;

			//
			result.textNode.style.setProperty('top', setValue(_parent.HEIGHT - textHeight, 'px'));

			//
			_parent.HEIGHT += result.getVariable('space', ['px']);

			//
			result.removeEvents = (... _args) => {
				return Menu.Item.removeItemNodeEvents(result, ... _args);
			};

			//
			result.setAttribute('href', result.href = this.href);

			if(typeof this.dataset.popup === 'string')
			{
				result.dataset.popup = this.dataset.popup;
			}

			//
			if(Menu.Item.addItemNodeEvents(result))
			{
				return result;
			}

			return null;
		}

		static onpointerdown(_event, _target = _event.target, _callback, _out_items = true, _force = false)
		{
			if(_event.pointerType === 'mouse')
			{
				return;
			}

			return Menu.Item.onpointerover({ type: 'pointerdown', pointerType: 'manu', target: _target }, _target, _callback, _out_items, _force);
		}

		static onpointerup(_event, _target = _event.target, _callback, _out_items = true, _force = false)
		{
			if(_event.pointerType === 'mouse')
			{
				return;
			}

			return Menu.Item.onpointerout({ type: 'pointerup', pointerType: 'manu', target: _target }, _target, _callback, _out_items, _force);
		}

		static onpointerover(_event, _target = _event.target, _callback, _out_items = true, _force = false)
		{
			/*if(_event.pointerType !== 'mouse' && _event.pointerType !== 'manu' && !_force)
			{
				return false;
			}
			else*/ if(_target.parentNode.isShowing || _target.parentNode.isHiding)
			{
				return false;
			}
			else if(_target._state.startsWith('over') && !_force)
			{
				return null;
			}
			else if(_target.OVER)
			{
				return _target.OVER;
			}
			else if(_target.OUT)
			{
				return _target.OUT.stop(() => {
					return Menu.Item.onpointerover(_event, _target, _callback, _out_items, _force);
				});
			}
			else if(_out_items)
			{
				return Menu.outItems(_event, () => {
					return Menu.Item.onpointerover(_event, _target, _callback, false, _force);
				}, _target);
			}
			else
			{
				_target._state = 'overing';
			}

			if(typeof _callback !== 'function')
			{
				_callback = null;
			}

			//
			if(! _target._originalPointerStyle)
			{
				_target._originalPointerStyle = getComputedStyle(_target.imageNode, 'color', 'text-shadow', 'background-color', 'border-radius', 'border' );
			}

			//
			const options = { duration: _target.getVariable('duration', true), persist: true, state: false, delay: 0 };
			const keyframes = {};

			//
			_target.imageNode.style.setProperty('transform-origin', _target.getVariable('transform-origin'));
			_target.textNode.style.setProperty('transform-origin', _target.getVariable('transform-origin'));

			//
			keyframes.transform = [ null, `scale(${_target.getVariable('pointer-over-scale')})` ];
			keyframes.color = [ null, _target.getVariable('pointer-over-color') ];
			keyframes.textShadow = [ null, _target.getVariable('pointer-over-text-shadow') ];
			keyframes.backgroundColor = [ null, _target.getVariable('pointer-over-background-color') ];
			keyframes.opacity = [ null, ... _target.getVariable('pointer-over-opacity', null).split(' ') ];
			keyframes.borderColor = [ null, _target.getVariable('pointer-over-border-color', null) ];

			//
			var count = 2;
			var fin = 0;

			const cb = (_e, _f) => {
				if(_f)
				{
					++fin;
				}

				if(--count <= 0)
				{
					if(fin >= 2)
					{
						_target._state = 'over';//'half'?
					}

					delete _target.OUT;
					delete _target.OVER;

					if(_callback)
					{
						_callback({ type: 'pointerover', target: _target, imageNode: _target.imageNode, textNode: _target.textNode, event: _event, finish: fin >= 2 }, fin >= 2, _target, _event);
					}
				}
			};

			//
			_target.OVER = [ _target.imageNode.animate(keyframes, options, cb) ];
			
			delete keyframes.backgroundColor;
			delete keyframes.borderRadius;
			delete keyframes.border;
			keyframes.left = [ null, setValue(_target.textNode.leftOver, 'px', true) ];
			
			_target.OVER[1] = _target.textNode.animate(keyframes, options, cb);

			//
			var stopped = false;

			_target.OVER.stop = (_cb) => {
				if(stopped)
				{
					return false;
				}
				else
				{
					stopped = true;
				}

				const over = [ ... _target.OVER ];
				var rest = _target.OVER.length;
				const cb = () => {
					if(--rest <= 0)
					{
						delete _target.OVER;

						if(typeof _cb === 'function')
						{
							//setTimeout(() => {
								_cb();
							//}, 0);
						}
					}
				};

				for(var i = 0; i < over.length; ++i)
				{
					over[i].stop(cb);
				}

				return true;
			};

			//
			return _target.OVER;
		}

		static onpointerout(_event, _target = _event.target, _callback, _out_items = true, _force = false)
		{
			/*if(_event.pointerType !== 'mouse' && _event.pointerType !== 'manu' && !_force)
			{
				return false;
			}
			else*/ if(_target.parentNode.isShowing || _target.parentNode.isHiding)
			{
				return false;
			}
			else if(_target._state.startsWith('out') && !_force)
			{
				return null;
			}
			else if(_target.OUT)
			{
				return _target.OUT;
			}
			else if(_target.OVER)
			{
				return _target.OVER.stop(() => {
					return Menu.Item.onpointerout(_event, _target, _callback, _out_items, _force);
				});
			}
			else if(_out_items)
			{
				return Menu.outItems(_event, () => {
					return Menu.Item.onpointerout(_event, _target, _callback, false, _force);
				}, _target, _event.relatedTarget);
			}
			else
			{
				_target._state = 'outing';
			}

			if(typeof _callback !== 'function')
			{
				_callback = null;
			}

			//
			if(! _target._originalPointerStyle)
			{
				_target._originalPointerStyle = getComputedStyle(_target.imageNode, 'color', 'text-shadow', 'background-color', 'border-radius', 'border' );
			}

			//
			const options = { duration: _target.getVariable('duration', true), persist: true, state: false, delay: 0 };
			const keyframes = {};

			//
			_target.imageNode.style.setProperty('transform-origin', _target.getVariable('transform-origin'));
			_target.textNode.style.setProperty('transform-origin', _target.getVariable('transform-origin'));

			//
			keyframes.transform = [ null, 'none' ];
			keyframes.filter = [ null, 'none' ];
			keyframes.opacity = [ null, '1' ];
			keyframes.color = [ null, _target._originalPointerStyle.color ];
			keyframes.textShadow = [ null, _target._originalPointerStyle.textShadow ];
			keyframes.backgroundColor = [ null, _target._originalPointerStyle.backgroundColor ];
			keyframes.border = [ null, _target._originalPointerStyle.border ];
			keyframes.borderRadius = [ null, _target._originalPointerStyle.borderRadius ];

			//
			var count = 2;
			var fin = 0;

			const cb = (_e, _f) => {
				//
				if(_f)
				{
					++fin;
				}

				//
				if(--count <= 0)
				{
					if(fin >= 2)
					{
						delete _target._originalPointerStyle;
					}

					_target._state = 'out';

					delete _target.OUT;
					delete _target.OVER;

					if(_callback)
					{
						_callback({ type: 'pointerout', target: _target, imageNode: _target.imageNode, textNode: _target.textNode, event: _event, finish: fin >= 2 }, fin >= 2, _target, _event);
					}
				}
			};

			//
			_target.OUT = new Array(2);

			_target.OUT[0] = _target.imageNode.animate(keyframes, options, cb);

			delete keyframes.backgroundColor;
			delete keyframes.borderRadius;
			delete keyframes.border;
			delete keyframes.right;
			keyframes.left = [ null, setValue(_target.textNode.leftOut, 'px', true) ];
			
			_target.OUT[1] = _target.textNode.animate(keyframes, options, cb);

			//
			var stopped = false;

			_target.OUT.stop = (_cb) => {
				if(stopped)
				{
					return false;
				}
				else
				{
					stopped = true;
				}

				const out = [ ... _target.OUT ];
				var rest = _target.OUT.length;
				const cb = () => {
					if(--rest <= 0)
					{
						delete _target.OUT;

						if(typeof _cb === 'function')
						{
							//setTimeout(() => {
								_cb();
							//}, 0);
						}
					}
				};

				for(var i = 0; i < out.length; ++i)
				{
					out[i].stop(cb);
				}

				return true;
			};

			//
			return _target.OUT;
		}

		static onclick(_event, _target = _event.target, _callback, _out_items = true, _force = false)
		{
			if(_target.isShowing || _target.isHiding)
			{
				return false;
			}
			else if(_target.BLINK)
			{
				return _target.BLINK;
			}
			else if(_out_items)
			{
				return Menu.outItems(_event, () => {
					return Menu.Item.onclick(_event, _target, _callback, false, _force);
				}, _target);
			}
			else
			{
				_target._originalState = _target._state;
				_target._state = 'blinking';
			}

			if(typeof _callback !== 'function')
			{
				_callback = null;
			}
			
			//
			if(! _target._originalPointerStyle)
			{
				_target._originalPointerStyle = getComputedStyle(_target.imageNode, 'color', 'text-shadow', 'background-color', 'border-radius', 'border' );
			}

			const opts = {
				count: _target.node.getVariable('blink-count', true),
				duration: _target.node.getVariable('blink-duration', true),
				delay: 0,
				colors: true,
				border: true,
				persist: false,
				state: false,
				scale: [ null, 1.0, 2.5 ],
				origin: _target.getVariable('transform-origin')
			};

			var count = 2;
			var fin = 0;

			const cb = (_e, _f, _index) => {

				if(_e.element)
				{
					_e.element.style.setProperty('transform', 'scale(2.5)');
					delete _e.element.BLINK;
				}
				else if(_e.style || _e.BLINK)
				{
					_e.style.setProperty('transform', 'scale(2.5)');
					delete _e.BLINK;
				}

				if(_f)
				{
					++fin;
				}

				if(--count <= 0)
				{
					setTimeout(() => {
						Menu.outItems(_event, null);
						//Menu.Item.onpointerout(_event, _target, null, false, true);
					}, 0);

					if(_target._state === 'blinking')
					{
						_target._state = _target._originalState;
						delete _target._originalState;
					}
					
					delete _target.BLINK;

					if(_callback)
					{
						_callback({ type: 'click', e: _e, event: _event, finish: fin >= 2,
						target: _target, imageNode: _target.imageNode, textNode: _target.textNode }, _f, _target, _event);
					}
				}
			};

			_target.BLINK = [ _target.imageNode.blink(opts, (_e, _f) => { call(cb, _e, _f); }) ];
			opts.backgroundColor = false;
			_target.BLINK[1] = _target.textNode.blink(opts, (_e, _f) => { call(cb, _e, _f); });

			return _target.BLINK;
		}

		static addItemNodeEvents(_item_node)
		{
			//
			if(! _item_node)
			{
				throw new Error('Invalid _item_node argument');
			}
			else if(('_pointerOverEvent' in _item_node) || ('_pointerOutEvent' in _item_node) || ('_clickEvent' in _item_node))
			{
				return false;
			}
			else
			{
				_item_node.addEventListener('pointerdown', _item_node._pointerDownEvent = (_e) => { return Menu.Item.onpointerdown(_e, _item_node); }, { capture: true, passive: true });
				_item_node.addEventListener('pointerover', _item_node._pointerOverEvent = (_e) => { return Menu.Item.onpointerover(_e, _item_node); }, { capture: true, passive: true });
				_item_node.addEventListener('pointerout', _item_node._pointerOutEvent = (_e) => { return Menu.Item.onpointerout(_e, _item_node); }, { capture: true, passive: true });
				_item_node.addEventListener('pointerup', _item_node._pointerUpEvent = (_e) => { return Menu.Item.onpointerup(_e, _item_node); }, { capture: true, passive: true });
				_item_node.addEventListener('click', _item_node._clickEvent = (_e) => { return Menu.Item.onclick(_e, _item_node); }, { capture: true });
			}

			//
			return true;
		}

		static removeItemNodeEvents(_item_node)
		{
			//
			if(! _item_node)
			{
				throw new Error('Invalid _item_node argument');
			}
			else if(! (('_pointerDownEvent' in _item_node) || ('_pointerOverEvent' in _item_node) || ('_pointerOutEvent' in _item_node) || ('_clickEvent' in _item_node)))
			{
				return false;
			}
			else
			{
				_item_node.removeEventListener('pointerdown', _item_node._pointerDownEvent);
				delete _item_node._pointerDownEvent;
				
				_item_node.removeEventListener('pointerover', _item_node._pointerOverEvent);
				delete _item_node._pointerOverEvent;

				_item_node.removeEventListener('pointerout', _item_node._pointerOutEvent);
				delete _item_node._pointerOutEvent;

				_item_node.removeEventListener('pointerup', _item_node._pointerUpEvent);
				delete _item_node._pointerUpEvent;

				_item_node.removeEventListener('click', _item_node._clickEvent);
				delete _item_node._clickEvent;
			}

			//
			return true;
		}

		static scale(_value, _max, _min)
		{
			if(! isNumber(_value))
			{
				throw new Error('Invalid _value argument');
			}
			else if(! isNumber(_max))
			{
				throw new Error('Invalid _max argument');
			}
			else if(! isNumber(_min))
			{
				throw new Error('Invalid _min argument');
			}

			return Math.scale(_value, _max, _min);
		}

		scale(_value, _max, _min)
		{
			if(! isNumber(_value))
			{
				throw new Error('Invalid _value argument');
			}
			
			if(! isNumber(_max))
			{
				_max = this.menu.getVariable('max', ['px']);
			}

			if(! isNumber(_min))
			{
				_min = this.menu.getVariable('min', ['px']);
			}

			return Menu.Item.scale(_value, _max, _min);
		}
		
		scaleImageSize(_value, _max, _min, _throw = DEFAULT_THROW)
		{
			if(! isNumber(_value))
			{
				throw new Error('Invalid _value argument');
			}

			return this.setImageSize(this.scale(_value, _max, _min), _throw);
		}
		
		setImageSize(_size, _throw = DEFAULT_THROW)
		{
			//
			if(! this.complete)
			{
				const cb = () => {
					this.removeEventListener('load', cb);
					this.removeEventListener('error', cb);

					return this.setImageSize(_size, _throw);
				};

				this.addEventListener('load', cb, { once: true });
				this.addEventListener('error', cb, { once: true });

				return null;
			}
			else if(! isNumber(_size) && _size >= 0)
			{
				throw new Error('Invalid _size argument');
			}

			//
			const factor = (_size / Math.max(this.naturalWidth, this.naturalHeight));
			const width = (this.naturalWidth * factor);
			const height = (this.naturalHeight * factor);
			
			//
			this.style.setProperty('width', setValue(width, 'px'));
			this.style.setProperty('height', setValue(height, 'px'));
			
			//
			return { width, height, factor, naturalWidth: this.naturalWidth, naturalHeight: this.naturalHeight,
				size: { width, height }, naturalSize: { width: this.naturalWidth, height: this.naturalHeight } };
		}
		
		static create(... _args)
		{
			const options = Object.assign(... _args);
			var menu = (was(options.menu, 'Menu') ? options.menu : null);
			var callback = (typeof options.callback === 'function' ? options.callback : null);
			var json = null;
			
			for(var i = 0; i < _args.length; ++i)
			{
				if(typeof _args[i] === 'function')
				{
					callback = _args.splice(i--, 1)[0];
				}
				else if(typeof _args[i] === 'boolean')
				{
					callback = _args.splice(i--, 1)[0];
				}
				else if(isString(_args[i], false))
				{
					json = _args.splice(i--, 1)[0];
				}
				else if(was(_args[i], 'Menu'))
				{
					menu = _args.splice(i--, 1)[0];
				}
				else if(isObject(_args[i]))
				{
					_args.splice(i--, 1);
				}
			}
			
			const result = new Menu.Item(options, menu, callback);
			
			if(json !== null)
			{
				result.load(json, callback);
			}
			
			return result;
		}
		
		load(_json, _callback = this.callback)
		{
			//
		}
		
		static getFavicon()
		{
			if(document.getVariable('favicon') && document.getVariable('favicon-image'))
			{
				return css.getUrl(document.getVariable('favicon-image'));
			}

			return null
		}
		
		get menu()
		{
			if(this.MENU)
			{
				return this.MENU;
			}
			
			return null;
		}
		
		set menu(_value)
		{
			if(was(_value, 'Menu'))
			{
				return this.MENU = _value;
			}
			else
			{
				delete this.MENU;
			}
			
			return this.menu;
		}

		get callback()
		{
			if(typeof this.CALLBACK === 'function')
			{
				return this.CALLBACK;
			}
			else if(typeof this.CALLBACK === 'boolean')
			{
				return this.CALLBACK;
			}
			
			return null;
		}
		
		set callback(_value)
		{
			if(typeof _value === 'function')
			{
				return this.CALLBACK = _value;
			}
			else if(typeof _value === 'boolean')
			{
				return this.CALLBACK = _value;
			}
			else
			{
				delete this.CALLBACK;
			}
			
			return this.callback;
		}

		static get options()
		{
			return [ 'image', 'data', 'description', 'href', 'popup' ];

		}
		
		checkOptions(_options)
		{
			const options = Menu.Item.options;

			if(! isObject(_options))
			{
				for(const idx of options)
				{
					this[idx] = null;
				}

				return -1;
			}
			
			var result = 0;

			for(const idx of options)
			{
				if((this[idx] = _options[idx]) !== null)
				{
					++result;
				}
			}

			return result;
		}
		
		//scale
		
		connectedCallback()
		{
		}
		
		disconnectedCallback()
		{
		}
		
		onload(_event)
		{
		}
		
		onerror(_event)
		{
			//
		}
		
		imageCallback(_event)
		{
			//
			this.removeEventListener('load', this._imageCallbackLoad);
			delete this._imageCallbackLoad;
			this.removeEventListener('error', this._imageCallbackError);
			delete this._imageCallbackError;
			
			//
			switch(_event.type)
			{
				case 'load':
					this.onload(_event);
					break;
				case 'error':
					this.onerror(_event);
					break;
				default:
					throw new Error('Invalid _event.type \'' + _event.type + '\' [ "load", "error" ]');
			}
			
			//
			call(this.callback, _event);
		}
		
		get image()
		{
			return this.src;
		}
		
		set image(_value)
		{
			if(! isString(_value, false))
			{
				if((_value = Menu.Item.getFavicon()) === null)
				{
					return this.src = '';
				}
			}
			else if(! _value)
			{
				return this.src = '';
			}

			this.addEventListener('load', this._imageCallbackLoad = this.imageCallback.bind(this), { once: true });
			this.addEventListener('error', this._imageCallbackError = this.imageCallback.bind(this), { once: true });
			
			return this.src = _value;
		}
		
		get data()
		{
			if(this.hasAttribute('alt'))
			{
				return this.getAttribute('alt');
			}
			
			return null;
		}
		
		set data(_value)
		{
			if(typeof _value === 'string')
			{
				this.setAttribute('alt', this.alt = _value);
			}
			else
			{
				this.alt = '';
				this.removeAttribute('alt');
			}
			
			delete this.related;
			return this.data;
		}
		
		get description()
		{
			if(this.hasAttribute('description'))
			{
				return this.getAttribute('description');
			}

			return null;
		}
		
		set description(_value)
		{
			if(typeof _value === 'string')
			{
				this.setAttribute('description', _value);
			}
			else
			{
				this.removeAttribute('description');
			}
			
			return this.description;
		}
		
		get href()
		{
			if(this.hasAttribute('href'))
			{
				return this.getAttribute('href');
			}

			return null;
		}
		
		set href(_value)
		{
			if(typeof _value === 'string')
			{
				this.setAttribute('href', _value);
			}
			else
			{
				this.removeAttribute('href');
			}
			
			return this.href;
		}
		
		get popup()
		{
			if(typeof this.dataset.popup === 'string')
			{
				return this.dataset.popup;
			}
			
			return null;
		}
		
		set popup(_value)
		{
			if(typeof _value === 'string')
			{
				return this.dataset.popup = _value;
			}
			else
			{
				delete this.dataset.popup;
			}
			
			return this.popup;
		}
	}
	
	//
	Menu.activeItem = null;

	//
	if(! customElements.get('a-menu'))
	{
		customElements.define('a-menu', Menu, { is: 'a-box' });
	}

	if(! customElements.get('a-menu-item'))
	{
		customElements.define('a-menu-item', Menu.Item, { extends: 'img' });
	}

	//
	Object.defineProperty(Menu, 'INDEX', { get: function()
	{
		const result = [];

		for(var i = 0, j = 0; i < Box._INDEX.length; ++i)
		{
			if(is(Box._INDEX[i], 'Menu'))
			{
				result[j++] = Box._INDEX[i];
			}
		}

		return result;
	}});

	Object.defineProperty(Menu.Item, 'INDEX', { get: function()
	{
		const result = [];

		for(var i = 0, j = 0; i < Box._INDEX.length; ++i)
		{
			if(Box._INDEX[i].name === 'item')
			{
				result[j++] = Box._INDEX[i];
			}
		}

		return result;
	}});
	
	//
	Menu.ROOT = null;

	//
	if(document.getVariable('menu', true) === true)
  	{
		const data = document.getVariable('menu-data');
	
		if(data.length > 0)
		{
			Menu.ROOT = Menu.load(data, { id: 'MENU', parent: BODY });
		}
		else
		{
			MAIN.style.setProperty('left', '-1px');
		}
	}
	else
	{
		MAIN.style.setProperty('left', '-1px');
	}

})();

