(function()
{

	//
	const DEFAULT_CALLBACK = true;
	const DEFAULT_ANIMATE = true;
	const DEFAULT_HIDE_DIV = 3;
	
	//
	Context = Box.Context = class Context extends Box
	{
		constructor(... _args)
		{
			//
			super(... _args);

			//
			this.identifyAs('context');
		}

		reset(... _args)
		{
			//
			if(this.childNodes.length > 0)
			{
				this.hide(() => {
					this.items.length = 0;
				}, true);
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
			this.items = [];
			
			//
			return super.reset(... _args);
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
				callback = DEFAULT_CALLBACK;
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
				
				var count = items.length;
				const cb = () => {
					if(--count <= 0)
					{
						call(callback, { type: 'load', items });
					}
				};

				var item;
				
				for(var i = 0; i < items.length; ++i)
				{
					this.items[i] = Context.create({ callback: cb/*, parent: this*/ }, items[i]);
					//this.appendChild(...
				}
			};

			const result = ajax(json, (callback ? rest : null), options);

			if(callback)
			{
				return result;
			}

			return rest(null, result, null);
		}

		static oncontextmenu(_event, _target = _event.target)
		{
			//
			if(config.behavior.disableContextMenu)
			{
				_event.preventDefault();
			}

			//
			if(! (was(_target, 'Context') || was(_target, 'ContextItem')))
			{
				return false;
			}

			//
		}
		
		hide(_callback, _animate = DEFAULT_ANIMATE)
		{
			if(! this.parentNode)
			{
				const res = this.childNodes.length;
				this.clear(null, _callback);
				return res;
			}
			else if(this.childNodes.length === 0)
			{
				return 0;
			}
			else if(this.isShowing)
			{
				return this.isShowingAbort = () => {
					return this.hide(_callback, _animate);
				};
			}
			else if(typeof _animate !== 'boolean')
			{
				_animate = DEFAULT_ANIMATE;
			}
			
			const childNodes = [ ... this.childNodes ];
			var rest = childNodes.length;
			const callback = (_item) => {
				if(--rest <= 0)
				{
					call(_callback, { type: 'hide', items: [ ... this.items ], childNodes });
				}
			};
			
			const options = (_animate ? { duration: Math.round(this.getVariable('duration', true) / DEFAULT_HIDE_DIV), delay: 0, blur: '3px' } : null);
			
			for(var i = childNodes.length - 1; i >= 0; --i)
			{
				const item = childNodes[i];
				
				if(item.IN)
				{
					item.IN.cancel(null);
				}
				else if(item.OUT)
				{
					item.OUT.finish(null);
				}
				
				if(! item.wasShown || ! _animate)
				{
					this.removeChild(item, null, () => {
						call(callback, item);
					});
				}
				else
				{
					item.out(options, (_e, _f) => {
						if(item.parentNode === this)
						{
							this.removeChild(item, null, () => {
								call(callback, item);
							});
						}
					});
					
					if(options)
					{
						options.delay += Math.round(this.getVariable('delay', true) / DEFAULT_HIDE_DIV);
					}
				}

				item.wasShown = false;
				item.isShowing = false;
			}
			
			this.style.width = this.style.height = '0';
			return childNodes.length;
		}
		
		show(_event, _callback, _animate = DEFAULT_ANIMATE)
		{
			if(! this.parentNode)
			{
				return -1;
			}
			else if(this.items.length === 0)
			{
				return 0;
			}
			else if(this.isShowing)
			{
				return -1;
			}
			else
			{
				this.isShowing = true;
			}
			
			if(typeof _animate !== 'boolean')
			{
				_animate = DEFAULT_ANIMATE;
			}
			
			var hadPosition;

			if(isObject(_event) && isNumber(_event.clientX) && isNumber(_event.clientY))
			{
				this.style.left = Math.int(_event.clientX);
				this.style.top = Math.int(_event.clientY);
				hadPosition = true;
			}
			else
			{
				hadPosition = false;
			}
			
			var rest = this.items.length;
			const callback = (_item) => {
				_item.wasShown = true;
				_item.isShowing = false;
				
				if(--rest <= 0)
				{
					this.isShowing = false;
					
					if(this.isShowingAbort)
					{
						const func = this.isShowingAbort;
						delete this.isShowingAbort;
						
						if(typeof func === 'function')
						{
							setTimeout(() => {
								func.call(this, _callback, _animate);
							}, 0);
						}
					}
					else
					{
						call(_callback, { type: 'show', items: [ ... this.items ], childNodes: [ ... this.childNodes ] });
					}
					
					delete this.isShowingAbort;
				}
			};
			
			var height = 0, width = 0, total;
			//const scale = 0.5;
			const options = (_animate ? { duration: this.getVariable('duration', true), delay: 0, blur: '3px' } : null);
			this.clear(null, null);
			
			for(var i = 0; i < this.items.length; ++i)
			{
				const item = this.items[i];
				
				item.style.opacity = '0';
				//item.scaleImageSize(scale, this.max, this.min);

				this.appendChild(item, false, () => {
					//
					total = item.totalSize;
					width = Math.max(width, total.width);
					this.style.width = setValue(width);
					item.style.top = setValue(height);
					height += total.height;
					this.style.height = setValue(height);
				
					//
					if(options)
					{
						options.delay += this.getVariable('delay', true);
					
						item.wasShown = false;
						item.isShowing = true;
						
						item.in(options, () => { call(callback, item); });
					}
					else
					{
						item.isShowing = null;
						item.wasShown = true;
						
						item.style.opacity = '1';
						
						call(callback, item);
					}
				});
			}

			if(! hadPosition)
			{
				this.style.left = Math.round((this.parentNode.clientWidth - width) / 2);
				this.style.top = Math.round((this.parentNode.clientHeight - height) / 2);
			}
			
			if(!_animate)
			{
				this.isShowing = false;
				
				if(this.isShowingAbort)
				{
					const func = this.isShowingAbort;
					delete this.isShowingAbort;
					
					if(typeof func === 'function')
					{
						setTimeout(() => {
							func.call(this, _callback, _animate);
						}, 0);
					}
				}
				
				delete this.isShowingAbort;
			}
			
			return height;
		}
	}
	
	//
	if(! customElements.get('a-context'))
	{
		customElements.define('a-context', Context, { is: 'a-box' });
	}
	
	//
	Object.defineProperty(Context, 'INDEX', { get: function()
	{
		const result = [];
		
		for(var i = 0, j = 0; i < Box._INDEX.length; ++i)
		{
			if(is(Box._INDEX[i], 'Context'))
			{
				result[j++] = Box._INDEX[i];
			}
		}
		
		return result;
	}});
	
	//
	const on = {};

	on.contextmenu = Context.oncontextmenu.bind(Context);

	for(const idx in on)
	{
		window.addEventListener(idx, on[idx]);
	}

	//
	Context.ROOT = null;

	//
	var rootContext;

	if(typeof config.behavior.disableContextMenu === 'boolean')
	{
		rootContext = config.behavior.disableContextMenu;
	}
	else
	{
		rootContext = true;
	}

	if(isObject(config.context) && isString(config.context.data, false))
	{
		rootContext = config.context.data;
	}

	if(! rootContext || typeof rootContext === 'string')
	{
		window.addEventListener('ready', () => {
			if(typeof rootContext === 'string')
			{
				return Context.ROOT = Context.load(rootContext, (_event) => {
					//
				}, { id: 'CONTEXT'/*, parent: BODY*/ });
				//BODY.appendChild(..
			}

			window.addEventListener('contextmenu', (_event) => {
				if(Context.ROOT !== null)
				{
					Context.ROOT.show(_event, null, null);
				}
				
				_event.preventDefault();
				return false;
			});
		}, { once: true });
	}

	//
	
})();
