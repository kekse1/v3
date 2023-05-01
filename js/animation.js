(function()
{

	//
	const DEFAULT_THROW = true;
	const DEFAULT_ROTATE_X_MIN = -90;
	const DEFAULT_ROTATE_X_MAX = 90;
	const DEFAULT_ROTATE_Y_MIN = -90;
	const DEFAULT_ROTATE_Y_MAX = 90;
	const DEFAULT_ROTATE_Z_MIN = -90;
	const DEFAULT_ROTATE_Z_MAX = 90;

	//
	window.animation = [];

	Object.defineProperty(window, 'animations', { get: function()
	{
		return window.animation.length;
	}});

	//
	Object.defineProperty(Element, 'animateOptions', { get: function()
	{
		return [ 'delay', 'direction', 'duration', 'easing', 'endDelay', 'fill', 'iterationStart', 'iterations', 'composite', 'iterationComposite', 'pseudoElement' ];
	}});

	const _animate = Element.prototype.animate;

	Object.defineProperty(Element.prototype, '_animate', { value: _animate });
	Object.defineProperty(Element.prototype, 'animate', { value: function(_keyframes, _options, _callback, _throw = DEFAULT_THROW)
	{
		//
		if(typeof _callback !== 'function')
		{
			_callback = null;
		}

		if(typeof _throw !== 'boolean')
		{
			_throw = DEFAULT_THROW;
		}

		//
		var none;

		if((_keyframes = keyframes(_keyframes, this, _options = this.getAnimateOptions(_options), _throw)) === null)
		{
			none = true;
		}
		else
		{
			none = false;
		}

		var resolvedStyle, cssProperties, originalStyle, style = null;
		var computedStyle = getComputedStyle(this);
		const originalComputedStyle = computedStyle;

		if(_keyframes === null)
		{
			resolvedStyle = null;
			cssProperties = null;
			originalStyle = null;
		}
		else
		{
			resolvedStyle = this.resolveKeyframeStyle(_keyframes, false, _throw);
			cssProperties = Object.keys(resolvedStyle);
			originalStyle = {};

			for(const idx of cssProperties)
			{
				originalStyle[idx] = computedStyle[idx];
			}
		}

		//
		if((_options.none && this.getVariable('none', true)) || none)
		{
			if(_callback) _callback({
				this: this, element: this,
				type: 'animate',
				finish: true, 
				animation: null,
				options: _options,
				style: null,
				cssProperties,
				resolvedStyle,
				originalStyle,
				cssProperties,
				computedStyle,
				originalComputedStyle,
				keyframes: _keyframes
			});

			return null;
		}
		else if(isFloat(_options.duration) || isFloat(_options.delay))
		{
			throw new Error('Options .duration and .delay need to be Integers');
		}
		else if(_options.duration < 0 || _options.delay < 0)
		{
			throw new Error('Options .duration and .delay need to be greater than or equal to zero (0)');
		}

		//
		var removed;
		var multiple;
		const removeMultiple = this.getVariable('remove-multiple').toLowerCase();

		if(isString(removeMultiple, false) && isArray(this.animation, false))
		{
			multiple = {};

			for(var i = 0; i < this.animation.length; ++i)
			{
				if(this.animation[i].properties !== null) for(var j = 0; j < this.animation[i].properties.length; ++j)
				{
					if(cssProperties.includes(this.animation[i].properties[j]))
					{
						if(! (this.animation[i].properties[j] in multiple))
						{
							multiple[this.animation[i].properties[j]] = [ this.animation[i] ];
						}
						else
						{
							multiple[this.animation[i].properties[j]].pushUnique(this.animation[i]);
						}
					}
				}
			}

			if(Object.keys(multiple).length === 0)
			{
				removed = null;
				multiple = null;
			}
			else
			{
				removed = [];

				for(const idx in multiple)
				{
					var done = true;

					for(const m of multiple[idx]) switch(removeMultiple)
					{
						case 'own':
							if(HTMLElement.removeKeyframeStyle(_keyframes, _throw, idx).length === 0)
							{
								done = false;
							}
							break;
						case 'stop':
							m.stop();
							break;
						case 'cancel':
							m.cancel();
							break;
						case 'finish':
							m.finish();
							break;
						case 'pause':
							m.pause();
							break;
						default:
							if(_throw)
							{
								throw new Error('Invalid \'DEFAULT_MULTIPLE_REMOVE\' value [ ""/.., "stop", "cancel", "finish", "pause" ]');
							}

							done = false;
							break;
					}

					if(done)
					{
						removed.pushUnique(idx);
					}
				}
			}
		}
		else
		{
			multiple = removed = null;
		}

		//
		var originalTransformOrigin;

		if(isString(_options.origin, false))
		{
			originalTransformOrigin = computedStyle.transformOrigin;
			this.style.setProperty('transform-origin', _options.origin);
		}
		else
		{
			originalTransformOrigin = null;
		}

		//
		const animateOptions = Element.animateOptions;
		const tempOptions = {};
		
		for(const idx of animateOptions)
		{
			if(idx in _options)
			{
				tempOptions[idx] = _options[idx];
			}
		}

		//
		result = _animate.call(this, _keyframes, tempOptions);
		const orig = result;

		result.element = this;
		result.options = _options;
		result.keyframes = _keyframes;
		result.properties = (cssProperties === null ? null : [ ... cssProperties ]);
		result.removed = (removed === null ? null : [ ... removed ]);
		result.multiple = (multiple === null ? null : { ... multiple });

		//
		if(! this.animation)
		{
			this.animation = [];
		}

		this.animation.push(result);
		window.animation.push(result);

		//
		var fin = false;

		const ending = (_event, _style = null) => {
			//
			if(fin)
			{
				return false;
			}
			else
			{
				fin = true;
			}

			//
			if(_style) for(const idx in _style)
			{
				this.style.setProperty(idx, _style[idx]);
			}

			//
			if(originalTransformOrigin !== null)
			{
				this.style.setProperty('transform-origin', originalTransformOrigin);
			}

			//
			this.animation.remove(orig);
			window.animation.remove(orig);

			if(this.animation.length === 0)
			{
				delete this.animation;
			}

			//
			result.removeEventListener('finish', listener);
			result.removeEventListener('cancel', listener);
			//result.removeEventListener('remove', listener);

			//
			if(typeof this._originalTransformOrigin === 'string')
			{
				this.style.setProperty('transform-origin', this._originalTransformOrigin);

				if(! this.animation)
				{
					delete this._originalTransformOrigin;
				}
			}

			//
			return fin;
		};

		const listener = (_event) => {
			//
			if(fin)
			{
				return;
			}
			else
			{
				style = getStyle();
				ending(_event, (_event.type === 'finish' ? resolvedStyle : (_options.persist !== false ? style : null)));
			}

			//
			if(_callback)
			{
				//
				_event.animation = result;
				_event.animations = this.animation || null;
				_event.finish = (_event.type === 'finish' || (result.currentTime >= _options.duration));
				_event.keyframes = _keyframes;
				_event.cssProperties = cssProperties;
				_event.originalStyle = originalStyle;
				_event.resolvedStyle = resolvedStyle;
				_event.style = style;
				_event.this = _event.element = this;
				_event.options = _options;
				_event.persist = _options.persist;
				_event.computedStyle = computedStyle;
				_event.originalComputedStyle = originalComputedStyle;
				_event.currentTime = result.currentTime;
				_event.duration = _options.duration;
				_event.time = Math.min(1, result.currentTime / _options.duration);

				//
				_callback(_event, _event.finish);
			}

			//
		};

		//
		result.addEventListener('finish', listener, { once: true });
		result.addEventListener('cancel', listener, { once: true });
		//result.addEventListener('remove', listener, { once: true });

		//
		const matchStyle = (_props, _computed = null) => {
			_computed = (isObject(_computed) ? _computed : getComputedStyle(this));
			const res = {};

			for(const k of _props)
			{
				res[k] = _computed[k];
			}

			return res;
		};

		const getStyle = () => {
			return matchStyle(keyFrameCSSProperties(_keyframes),
				computedStyle = getComputedStyle(this));
		};

		//
		const _cancel = result.cancel.bind(result);
		const _finish = result.finish.bind(result);
		const _pause = result.pause.bind(result);

		result.stop = (_cb = null, _orig_cb = true, _throw = DEFAULT_THROW, ... _args) => {
			//
			if(fin)
			{
				return style = getStyle();
			}
			else if(result.playState === 'finished')
			{
				return style = getStyle();
			}
			else
			{
				_pause(... _args);
				ending(null, style = getStyle());
				_cancel(... _args);
			}

			//
			const e = {
				type: 'stop', this: this, element: this,
				animation: result, animations: this.animation,
				finish: (result.currentTime >= _options.duration),
				options: _options, persist: _options.persist,
				style, cssProperties, resolvedStyle, originalStyle,
				computedStyle, originalComputedStyle,
				keyframes: _keyframes,
				currentTime: result.currentTime, duration: _options.duration,
				time: Math.min(1, (result.currentTime / _options.duration))
			};

			//
			if(_orig_cb && _callback)
			{
				setTimeout(() => {
					_callback(e, e.finish);
				}, 0);
			}

			if(typeof _cb === 'function')
			{
				setTimeout(() => {
					_cb(e, e.finish);
				}, 0);
			}

			//
			return style;
		};

		result.cancel = (_cb = null, _orig_cb = true, _throw = DEFAULT_THROW, ... _args) => {
			//
			if(fin)
			{
				return style = getStyle();
			}
			else if(result.playState === 'finished')
			{
				return style = getStyle();
			}
			else
			{
				_pause(... _args);
				style = getStyle();
				ending(null, null);
				_cancel(... _args);
			}

			//
			const e = {
				type: 'cancel', this: this, element: this,
				animation: result, animations: this.animation,
				finish: (result.currentTime >= _options.duration),
				options: _options, persist: _options.persist,
				style, cssProperties, resolvedStyle, originalStyle,
				computedStyle, originalComputedStyle,
				keyframes: _keyframes,
				currentTime: result.currentTime, duration: _options.duration,
				time: Math.min(1, (result.currentTime / _options.duration))
			};

			//
			if(_orig_cb && _callback)
			{
				setTimeout(() => {
					_callback(e, e.finish);
				}, 0);
			}

			if(typeof _cb === 'function')
			{
				setTimeout(() => {
					_cb(e, e.finish);
				}, 0);
			}

			//
			return style;			
		};
		
		result.finish = (_cb = null, _orig_cb = true, _throw = DEFAULT_THROW, ... _args) => {
			//
			if(fin)
			{
				return null;
			}
			else if(result.playState === 'finished')
			{
				return null;
			}
			else
			{
				style = getStyle();
				ending(null, resolvedStyle);
				_finish(... _args);
			}
			
			//
			const e = {
				type: 'finish', this: this, element: this,
				animation: result, animations: this.animation,
				finish: true,
				options: _options, persist: _options.persist,
				style, cssProperties, resolvedStyle, originalStyle,
				computedStyle, originalComputedStyle,
				keyframes: _keyframes,
				currentTime: result.currentTime, duration: _options.duration,
				time: Math.min(1, (result.currentTime / _options.duration))
			};

			//
			if(_orig_cb && _callback)
			{
				setTimeout(() => {
					_callback(e, e.finish);
				}, 0);
			}

			if(typeof _cb === 'function')
			{
				setTimeout(() => {
					_cb(e, e.finish);
				}, 0);
			}
			
			//
			return style;
		};

		result.pause = (_cb = null, _throw = DEFAULT_THROW, ... _args) => {
			//
			if(result.playState === 'finished')
			{
				return style = getStyle();
			}
			else if(result.playState === 'paused')
			{
				return style = getStyle();
			}
			else
			{
				_pause(... _args);
				style = getStyle();
			}

			//
			const e = {
				this: this, element: this,
				type: 'pause',
				animation: result, animations: this.animation,
				finish: (result.currentTime >= _options.duration),
				options: _options, persist: _options.persist,
				style, cssProperties, resolvedStyle, originalStyle,
				computedStyle, originalComputedStyle,
				keyframes: _keyframes,
				currentTime: result.currentTime, duration: _options.duration,
				time: Math.min(1, (result.currentTime / _options.duration))
			};

			//
			if(typeof _cb === 'function')
			{
				setTimeout(() => {
					_cb(e, e.finish);
				}, 0);
			}

			//
			return style;
		};

		//
		return result;
	}});

	Object.defineProperty(HTMLElement, 'removeKeyframeStyle', { value: function(_keyframes, ... _styles)
	{
		if(_styles.length === 0)
		{
			return 0;
		}
		else if(! _keyframes)
		{
			if(_throw)
			{
				throw new Error('Missing _keyframes argument');
			}

			return null;
		}

		var THROW = DEFAULT_THROW;

		for(var i = 0; i < _styles.length; ++i)
		{
			if(typeof _styles[i] === 'boolean')
			{
				THROW = _styles.splice(i--, 1)[0];
			}
			else if(! isString(_styles[i], false))
			{
				throw new Error('Invalid ..._styles[' + i + '] (no non-empty String)');
			}
			else switch(_styles[i])
			{
				case 'easing':
				case 'composite':
					_styles.splice(i--, 1);
					break;
			}
		}

		const result = [];

		if(_styles.length === 0)
		{
			return result;
		}

		const keyframeArray = () => {
			for(var i = 0; i < _keyframes.length; ++i)
			{
				for(var j = 0; j < _styles.length; ++j)
				{
					if(_styles[j] in _keyframes[i])
					{
						delete _keyframes[i][styles[j]];
						result.pushUnique(styles[j]);
					}
				}
			}
		};

		const keyframeObject = () => {
			for(var i = 0; i < _styles.length; ++i)
			{
				if(_styles[i] in _keyframes)
				{
					delete _keyframes[_styles[i]];
					result.pushUnique(_styles[i]);
				}
			}
		};

		if(isArray(_keyframes, true))
		{
			keyframeArray();
		}
		else if(isObject(_keyframes, true))
		{
			keyframeObject();
		}
		else if(THROW)
		{
			throw new Error('Invalid _keyframes array/object');
		}
		else
		{
			return null;
		}

		return result;
	}});

	Object.defineProperty(HTMLElement.prototype, 'resolveKeyframeStyle', { value: function(_keyframes, _apply = true, _throw = DEFAULT_THROW)
	{
		if(typeof _apply !== 'boolean')
		{
			throw new Error('Invalid _apply argument (need a Boolean)');
		}
		else if(typeof _throw !== 'boolean')
		{
			_throw = DEFAULT_THROW;
		}

		const computedStyle = getComputedStyle(this);
		const result = {};
		var next;

		const keyframeObject = () => {
			const keys = Object.keys(_keyframes);

			for(var i = 0; i < keys.length; ++i)
			{
				switch(keys[i])
				{
					case 'easing':
					case 'composite':
						next = true;
						break;
					case 'cssOffset':
						next = 'offset';
						break;
					case 'cssFloat':
						next = 'float';
						break;
					default:
						next = camel.enable(keys[i]);
						break;
				}

				if(next === true)
				{
					continue;
				}
				else if(! HTMLElement.isStyle(next))
				{
					if(_throw)
					{
						throw new Error('Invalid CSS style \'' + next + '\'');
					}

					continue;
				}

				if(! isArray(_keyframes[keys[i]], true))
				{
					_keyframes[keys[i]] = [ _keyframe[keys[i]] ];
				}

				for(var j = 0; j < _keyframes[keys[i]].length; ++j)
				{
					if(_keyframes[keys[i]][j] === null)
					{
						_keyframes[keys[i]][j] = computedStyle.getPropertyValue(keys[i]);
					}
					else if(isNumeric(_keyframes[keys[i]][j]))
					{
						_keyframes[keys[i]][j] = _keyframes[keys[i]][j].toString();
					}
					else if(typeof _keyframes[keys[i]][j] !== 'string')
					{
						if(_throw)
						{
							throw new Error('The _keyframes[' + keys[i] + '][' + j + '] is not a String');
						}

						continue;
					}
					else
					{
						result[next] = _keyframes[keys[i]][j];
					}
				}
			}
		};

		const keyframeArray = () => {
			for(var i = 0; i < _keyframes.length; ++i)
			{
				if(! isObject(_keyframes[i]))
				{
					if(_throw)
					{
						throw new Error('Invalid _keyframes[' + i + '] (no Object)');
					}

					continue;
				}
				else for(const idx in _keyframes[i])
				{
					switch(idx)
					{
						case 'easing':
						case 'composite':
							next = true;
							break;
						case 'cssOffset':
							next = 'offset';
							break;
						case 'cssFloat':
							next = 'float';
							break;
						default:
							next = camel.enable(idx);
							break;
					}

					if(next === true)
					{
						continue;
					}
					else if(! HTMLElement.isStyle(next))
					{
						if(_throw)
						{
							throw new Error('Invalid CSS style \'' + next + '\'');
						}

						continue;
					}

					result[next] = _keyframes[i][idx];
				}
			}
		};

		if(isArray(_keyframes))
		{
			keyframeArray(_keyframes = [ ... _keyframes ]);
		}
		else if(isObject(_keyframes))
		{
			keyframeObject(_keyframes = { ... _keyframes });
		}
		else if(typeof _keyframes === 'object')
		{
			return null;
		}
		else if(_throw)
		{
			throw new Error('Invalid _keyframes argument (expecting Object or Array)');
		}
		else
		{
			return null;
		}

		if(_apply) for(const idx in result)
		{
			this.style.setProperty(camel.disable(idx), result[idx]);
		}

		return result;
	}});

	const keyFrameCSSProperties = (_keyframes, _throw = DEFAULT_THROW) => {
		const result = [];

		const keyFrameArray = () => {
			var next;

			for(var i = 0, j = 0; i < _keyframes.length; ++i)
			{
				if(! isObject(_keyframes[i]))
				{
					if(_throw)
					{
						throw new Error('Invalid _keyframes[' + keys[i] + '] (not an Object)');
					}

					continue;
				}
				else for(const idx in _keyframes[i])
				{
					switch(idx)
					{
						case 'easing':
							next = true;
							break;
						case 'composite':
							next = true;
							break;
						case 'cssOffset':
							next = 'offset';
							break;
						case 'cssFloat':
							next = 'float';
							break;
						default:
							next = idx;
							break;
					}
				}

				if(next === true)
				{
					continue;
				}
				else if(result.includes(next))
				{
					continue;
				}
				else
				{
					result[j++] = next;
				}
			}
		};

		const keyFrameObject = () => {
			const keys = Object.keys(_keyframes);
			var next;

			for(var i = 0, j = 0; i < keys.length; ++i)
			{
				switch(keys[i])
				{
					case 'easing':
						next = true;
						break;
					case 'composite':
						next = true;
						break;
					case 'cssOffset':
						next = 'offset';
						break;
					case 'cssFloat':
						next = 'float';
						break;
					default:
						next = keys[i];
						break;
				}

				if(next === true)
				{
					continue;
				}
				else if(result.includes(next))
				{
					continue;
				}
				else
				{
					result[j++] = next;
				}
			}
		};

		if(isArray(_keyframes, true))
		{
			keyFrameArray();
		}
		else if(isObject(_keyframes, true))
		{
			keyFrameObject();
		}
		else if(typeof _keyframes === 'object')
		{
			return null;
		}
		else if(_throw)
		{
			throw new Error('Invalid _keyframes Object/Array (it\'s none of these)');
		}
		else
		{
			return null;
		}

		return result;
	};

	const keyframes = (_keyframes, _element, _options, _throw = DEFAULT_THROW) => {
		//
		_options = _element.getAnimateOptions(_options);

		//
		const computedStyle = getComputedStyle(_element);

		const isCSSProperty = (_key) => {
			return (_key in computedStyle);
		};
		
		const getStyle = (_key) => {
			/*if(_element.style.getPropertyValue(_key).length > 0)
			{
				return _element.style.getPropertyValue(_key);
			}*/
			
			return computedStyle[_key];
		};

		const fromArray = (_array) => {
			const keys = [];
			const result = [];
			var cssKey, camelKey;
			var res;

			for(var i = 0, j = 0, k = 0; i < _array.length; ++i)
			{
				if(! isObject(_array[i], null, false))
				{
					if(_throw)
					{
						throw new Error('Invalid _keyframes[' + i + '] argument (not an Object)');
					}
				}
				else
				{
					res = {};

					for(const idx in _keyframes[i])
					{
						if(! isString(_array[i][idx], false) && _array[i][idx] !== null && !isNumeric(_array[i][idx]))
						{
							if(_throw)
							{
								throw new Error('Invalid _keyframes[' + i + '][' + idx + '] (not non-empty String)');
							}

							continue;
						}
						else
						{
							camelKey = camel.enable(idx, '-', false);
						}
						
						switch(camelKey)
						{
							case 'easing':
							case 'composite':
								cssKey = null;
								break;
							case 'cssOffset':
								cssKey = 'offset';
								break;
							case 'cssFloat':
								cssKey = 'float';
								break;
							default:
								cssKey = camelKey;
								break;
						}

						if(cssKey === null)
						{
							continue;
						}
						else if(! isCSSProperty(cssKey))
						{
							if(_throw && idx !== 'easing' && idx !== 'composite')
							{
								throw new Error('Invalid _keyframes[' + i + '][' + cssKey + '/' + idx + '] (no valid CSS property)');
							}
						}
						else if(_array[i][idx] === null)
						{
							res[camelKey] = getStyle(cssKey);
						}
						else if(isNumeric(_array[i][idx]))
						{
							res[camelKey] = _array[i][idx].toString();
						}
						else
						{
							res[camelKey] = _array[i][idx];
						}
					}

					result[j++] = res;
				}
			}

			if(_options.state)
			{
				var s;
				keys.length = 0;

				for(var i = 0; i < result.length; ++i)
				{
					for(const idx in result[i])
					{
						if(keys.includes(idx))
						{
							continue;
						}
						else
						{
							keys.push(idx);
						}

						if(result[i][idx] !== (s = getStyle(idx)))
						{
							result.splice(0, 0, { [idx]: s });
						}
					}
				}
			}

			return result;
		}; 

		const fromObject = (_object) => {
			const result = {};
			var cssKey, camelKey;
			var res;

			for(var idx in _object)
			{
				camelKey = camel.enable(idx);

				switch(camelKey)
				{
					case 'easing':
					case 'composite':
						cssKey = null;
						break;
					case 'cssOffset':
						cssKey = 'offset';
						break;
					case 'cssFloat':
						cssKey = 'float';
						break;
					default:
						cssKey = camelKey;
						break;
				}
				
				if(cssKey === null)
				{
					continue;
				}
				else if(! isCSSProperty(cssKey))
				{
					if(_throw && idx !== 'easing' && idx !== 'composite')
					{
						throw new Error('Invalid _keyframes[' + idx + '] (no valid CSS property)');
					}
				}
				else if(typeof _object[idx] === 'string')
				{
					_object[idx] = [ _object[idx] ];
				}
				else if(isArray(_object[idx], true))
				{
					if(_object[idx].length === 0)
					{
						if(_throw)
						{
							throw new Error('Invalid _keyframes[' + idx + '] (is an empty Array)');
						}
					}
				}
				else if(isNumeric(_object[idx]))
				{
					_object[idx] = [ _object[idx].toString() ];
				}
				else if(_object[idx] === null)
				{
					_object[idx] = [ getStyle(cssKey) ];
				}
				else if(_throw)
				{
					throw new Error('Invalid _keyframes[' + idx + '] (neither String nor Array)');
				}
				else
				{
					continue;
				}

				res = [];
				
				for(var i = 0, j = 0; i < _object[idx].length; ++i, ++j)
				{
					if(_object[idx][i] !== null)
					{
						res[j] = _object[idx][i];
					}
					else
					{
						res[j] = getStyle(cssKey);
					}
				}

				result[camelKey] = res;
			}

			if(_options.state)
			{
				var s;
				
				for(const idx in result)
				{
					if(result[idx][0] !== (s = getStyle(idx)))
					{
						result[idx].splice(0, 0, s);
					}
				}
			}

			return result;
		};

		if(isArray(_keyframes))
		{
			return fromArray(_keyframes);
		}
		else if(isObject(_keyframes))
		{
			return fromObject(_keyframes);
		}
		else if(typeof _keyframes === 'object')
		{
			return null;
		}
		else if(_throw)
		{
			throw new Error('Invalid _keyframes argument (neiher Array nor Object)');
		}

		return null;
	};

	//
	const tryValue = (_value, _default = '0', _px = true) => {
		var result;
		
		const getDefault = () => {
			if(typeof _default === 'string')
			{
				return setValue(_default, _px);
			}
			else if(isNumber(_default))
			{
				return setValue(_default, _px);
			}

			return '';
		};

		if(isNumber(_value))
		{
			return setValue(_value, _px);
		}
		else if(typeof _value === 'string')
		{
			return _value;
		}
		else if(_value === null)
		{
			return _value;
		}

		return getDefault();
	};
	
	//
	Object.defineProperty(Node.prototype, 'remove', { value: function(_options, _callback, _throw = DEFAULT_THROW)
	{
		if(typeof _throw !== 'boolean')
		{
			_throw = DEFAULT_THROW;
		}

		if(! this.parentNode)
		{
			if(_throw)
			{
				throw new Error('You can\'t .remove() this element, because there\'s no .parentNode carrier for it');
			}
			else
			{
				call(_callback, { type: 'remove', this: this, error: true, animated: null });
			}

			return null;
		}

		return this.parentNode.removeChild(this, _options, _callback, _throw);
	}});

	Object.defineProperty(Element.prototype, 'remove', { value: Node.prototype.remove });

	const _appendChild = Node.prototype.appendChild;
	const _removeChild = Node.prototype.removeChild;
	const _insertBefore = Node.prototype.insertBefore;
	const _replaceChild = Node.prototype.replaceChild;

	Object.defineProperty(Node.prototype, '_appendChild', { value: _appendChild });
	Object.defineProperty(Element.prototype, '_appendChild', { value: Element.prototype.appendChild });

	Object.defineProperty(Node.prototype, '_removeChild', { value: _removeChild });
	Object.defineProperty(Element.prototype, '_removeChild', { value: Element.prototype.removeChild });

	Object.defineProperty(Node.prototype, '_insertBefore', { value: _insertBefore });
	Object.defineProperty(Element.prototype, '_insertBefore', { value: Element.prototype.insertBefore });
	
	Object.defineProperty(Node.prototype, '_replaceChild', { value: _replaceChild });
	Object.defineProperty(Element.prototype, '_replaceChild', { value: Element.prototype.replaceChild });

	Object.defineProperty(Node.prototype, 'appendChild', { value: function(_child, _options, _callback, _throw = DEFAULT_THROW)
	{
		if(typeof _throw !== 'boolean')
		{
			_throw = DEFAULT_THROW;
		}
		
		if(! _child || _child.parentNode === this)
		{
			if(_throw && !_child)
			{
				throw new Error('Invalid _child, or it\'s .parentNode is not this element');
			}
			else
			{
				call(_callback, { type: 'appendChild', this: this, child: (_child || null), error: true, animated: null });
			}
			
			return null;
		}
		else if('getAnimateOptions' in _child)
		{
			_options = _child.getAnimateOptions(_options);
		}
		else if('getAnimateOptions' in this)
		{
			_options = this.getAnimateOptions(_options);
		}
		else
		{
			_options = null;
		}

		if(_child.parentNode)
		{
			return _child.parentNode.removeChild(_child, _options, () => {
				return this.appendChild(_child, _options, _callback);
			});
		}
		
		const result = _appendChild.call(this, _child);
		
		if(_options === null || _options.none || typeof _child.in !== 'function')
		{
			call(_callback, { type: 'appendChild', this: this, child: (_child || null), error: false, animated: false });
			return result;
		}

		return _child.in(_options, (... _args) => {
			call(_callback, { type: 'appendChild', this: this, child: (_child || null), error: false, animated: true }, ... _args);
		}, _throw);
	}});

	Object.defineProperty(Element.prototype, 'appendChild', { value: Node.prototype.appendChild });

	Object.defineProperty(Node.prototype, 'removeChild', { value: function(_child, _options, _callback, _throw = DEFAULT_THROW)
	{
		if(typeof _throw !== 'boolean')
		{
			_throw = DEFAULT_THROW;
		}
		
		if(! _child || _child.parentNode !== this)
		{
			if(_throw)
			{
				throw new Error('Invalid _child, or it\'s .parentNode is not this element');
			}
			else
			{
				call(_callback, { type: 'removeChild', this: this, child: (_child || null), error: true, animated: null });
			}
			
			return null;
		}
		else if('getAnimateOptions' in _child)
		{
			_options = _child.getAnimateOptions(_options);
		}
		else if('getAnimateOptions' in this)
		{
			_options = this.getAnimateOptions(_options);
		}
		else
		{
			_options = null;
		}
		
		if(_options === null || _options.none || typeof _child.out !== 'function')
		{
			const result = _removeChild.call(this, _child);
			call(_callback, { type: 'removeChild', this: this, child: _child, error: false, animated: false });
			return result;
		}
		
		return _child.out(_options, (... _args) => {
			//
			if(_child.parentNode === this)
			{
				_removeChild.call(this, _child);
			}
			
			//
			call(_callback, { type: 'removeChild', this: this, child: (_child || null), error: false, animated: true }, ... _args);
		}, _throw);
	}});

	Object.defineProperty(Element.prototype, 'removeChild', { value: Node.prototype.removeChild });

	Object.defineProperty(Node.prototype, 'insertBefore', { value: function(_child, _reference, _options, _callback, _throw = DEFAULT_THROW)
	{
		if(typeof _throw !== 'boolean')
		{
			_throw = DEFAULT_THROW;
		}
		
		if(! (_child && _reference) || _reference.parentNode !== this)
		{
			if(_throw)
			{
				throw new Error('Invalid _child or _reference, or the _reference\'s .parentNode is not this element');
			}

			call(_callback, { type: 'insertBefore', this: this, child: (_child || null), reference: (_reference || null), error: true, animated: null });
			return null;
		}
		else if('getAnimateOptions' in _child)
		{
			_options = _child.getAnimateOptions(_options);
		}
		else if('getAnimateOptions' in this)
		{
			_options = this.getAnimateOptions(_options);
		}
		else
		{
			_options = null;
		}

		if(_options === null || _options.none || !('in' in _child))
		{
			//
			const result = _insertBefore.call(this, _child, _reference);
			
			//
			call(_callback, { type: 'insertBefore', this: this, child: _child, reference: _reference, error: false, animated: false });
			
			//
			return result;
		}
		else if(_child.parentNode)
		{
			return _child.out(_options, (... _args) => {
				//
				_insertBefore.call(this, _child, _reference);
				
				//
				_child.in(_options, (... _a) => {
					call(_callback, { type: 'insertBefore', this: this, child: _child, reference: _reference, error: false, animated: true }, ... _a, ... _args);
				}, _throw);
			}, _throw);
		}

		const result = _insertBefore.call(this, _child, _reference);
		call(_callback, { type: 'insertBefore', this: this, child: _child, reference: _reference, error: false, animated: true });
		return result;
	}});

	Object.defineProperty(Element.prototype, 'insertBefore', { value: Node.prototype.insertBefore });

	Object.defineProperty(Node.prototype, 'replaceChild', { value: function(_child, _reference, _options, _callback, _throw = DEFAULT_THROW)
	{
		if(typeof _throw !== 'boolean')
		{
			_throw = DEFAULT_THROW;
		}
		
		if(! (_child && _reference) || _reference.parentNode !== this)
		{
			if(_throw)
			{
				throw new Error('Invalid _child or _reference, or the reference\'s .parentNode is not this element');
			}

			call(_callback, { type: 'replaceChild', this: this, child: (_child || null), reference: (_reference || null), error: true, animated: null });
			return null;
		}
		else if('getAnimateOptions' in _child)
		{
			_options = _child.getAnimateOptions(_options);
		}
		else if('getAnimateOptions' in this)
		{
			_options = this.getAnimateOptions(_options);
		}
		else
		{
			_options = null;
		}
		
		if(_options === null || _options.none || !('out' in _reference))
		{
			const result = _replaceChild.call(this, _child, _reference);
			call(_callback, { type: 'replaceChild', this: this, child: _child, reference: _reference, error: false, animated: false });
			return result;
		}
throw new Error('TODO');
	}});

	Object.defineProperty(Element.prototype, 'replaceChild', { value: Node.prototype.replaceChild });

	//
	const getBlinkStartColor = (_current, _bg) => {
		if(typeof _current !== 'string' || typeof _bg !== 'boolean')
		{
			throw new Error('Invalid argument(s)');
		}
		
		var result;

		if(_current === 'rgb(0, 0, 0)')
		{
			result = 'white';
		}
		else if(_current === 'rgb(255, 255, 255)')
		{
			result = 'black';
		}
		else if(_current.startsWith('rgba(0, 0, 0'))
		{
			result = 'white';
		}
		else if(_current.startsWith('rgba(255, 255, 255'))
		{
			result = 'black';
		}
		else if(_bg)
		{
			result = 'black';
		}
		else
		{
			result = 'white';
		}
		
		return result;
	};
	
	const getBlinkStopColor = (_start_color, _bg) => {
		if(typeof _start_color !== 'string' || typeof _bg !== 'boolean')
		{
			throw new Error('Invalid argument(s)');
		}
		else if(_start_color === 'black')
		{
			return 'white';
		}
		else if(_start_color === 'white')
		{
			return 'black';
		}
		else if(_bg)
		{
			return 'white';
		}
		
		return 'black';
	};
	
	Object.defineProperty(HTMLElement, 'blinkProps', { get: function()
	{
		return [ 'overflow', 'opacity', 'transform', 'filter', 'color', 'backgroundColor', 'borderRadius', 'borderWidth', 'borderColor' ];
	}});
	
	Object.defineProperty(Element.prototype, 'blink', { value: function(_options, _callback)
	{
		//
		if(this.BLINK)
		{
			return this.BLINK;
		}

		//
		const computedStyle = getComputedStyle(this);
		var transform;

			//FIXME/!?
		if(computedStyle.transform.length === 0 || computedStyle.transform === 'none')
		{
			transform = null;//'scale(1)';
		}
		else if(CSSMatrix.hasScales(computedStyle.transform, false))
		{
			transform = computedStyle.transform;
		}
		else
		{
			transform = null;// 'scale(1)';
		}

		if(typeof this._originalBlinkTransform !== 'string')
		{
			this._originalBlinkTransform = transform;
		}
		else
		{
			transform = this._originalBlinkTransform;
		}

		//
		_options = this.getAnimateOptions(Object.assign({
			delay: this.getVariable('blink-delay', true),
			duration: this.getVariable('blink-duration', true),
			count: this.getVariable('blink-count', true),
			persist: false, state: false,
			opacity: true, transform: true, scale: true,
			colors: true, color: true, backgroundColor: true, complement: true,
			border: true, borderRadius: true, borderWidth: true, borderColor: true,
			filter: true, blur: true,
			show: Math.random.bool()
		}, _options));

		if(_options.count >= 1)
		{
			_options.count = Math.int(_options.count);
		}
		else if(! _options.show)
		{
			call(_callback, { type: 'blink', options: _options });
			return 0;
		}

		//
		const origCount = _options.count;
		
		//
		/*if(! this._originalBeforeBlink)
		{
			this._originalBeforeBlink = { ... computedStyle };
			this._originalBeforeBlink.only(... HTMLElement.blinkProps);
		}*/

		//
		//const originalOverflow = this.style.overflow;
		//this.style.overflow = 'hidden';
		this.scrolling = false;
		
		//
		const callback = (_e, _f) => {
			const keyframes = {};
			
			if(_options.opacity !== false)
			{
				keyframes.opacity = [ computedStyle.opacity, '0.32', computedStyle.opacity ];
			}
			
			if(_options.transform !== false && _options.scale !== false)
			{
				keyframes.transform = [ transform ];

				if(isArray(_options.scale, false))
				{
					for(var i = 0; i < _options.scale.length; ++i)
					{
						if(isNumber(_options.scale[i]))
						{
							keyframes.transform[i + 1] = 'scale(' + _options.scale[i] + ')';
						}
						else
						{
							keyframes.transform[i + 1] = _options.scale[i];
						}
					}
				}
				else if(_options.show)
				{
					keyframes.transform.push('scale(1.25)', 'scale(0.75)', 'scale(1)');
				}
				else
				{
					keyframes.transform.push('scale(0.75)', 'scale(1.25)', 'scale(1)');
				}

				/*if(! isArray(_options.scale, false) && transform !== 'scale(1)')
				{
					keyframes.transform.push(transform);
				}*/
				//keyframes.transform.push(null);
			}

			if(_options.colors !== false)
			{
				var bg = computedStyle.backgroundColor;
				var fg = computedStyle.color;

				if(_options.backgroundColor !== false)
				{
					if(_options.complement !== false && bg.length > 0)
					{
						keyframes.backgroundColor = [ 
							computedStyle.backgroundColor,
							color.complement(bg),
							computedStyle.backgroundColor
						];
					}
					else
					{
						const start = getBlinkStartColor(bg, true);
						const stop = getBlinkStopColor(start, true);

						keyframes.backgroundColor = [ computedStyle.backgroundColor ];
						
						if(_options.show)
						{
							keyframes.backgroundColor.push(start, stop);
						}
						else
						{
							keyframes.backgroundColor.push(stop, start);
						}
						
						keyframes.backgroundColor.push(computedStyle.backgroundColor);
					}
				}

				if(_options.color !== false)
				{
					if(_options.complement !== false && fg.length > 0)
					{
						keyframes.color = [ computedStyle.color, color.complement(fg), computedStyle.color ];
					}
					else
					{
						const start = getBlinkStartColor(fg, false);
						const stop = getBlinkStopColor(start, false);
						
						keyframes.color = [ computedStyle.color ];
						
						if(_options.show)
						{
							keyframes.color.push(start, stop);
						}
						else
						{
							keyframes.color.push(stop, start);
						}
						
						keyframes.color.push(computedStyle.color);
					}
				}
			}
				
			if(_options.border !== false)
			{
				if(_options.borderRadius !== false)
				{
					keyframes.borderRadius = [ computedStyle.borderRadius, tryValue(_options.borderRadius, this.getVariable('blink-border-radius')),
						computedStyle.borderRadius ];
				}
				
				if(_options.borderWidth !== false)
				{
					keyframes.borderWidth = [ computedStyle.borderWidth, tryValue(_options.borderWidth, this.getVariable('blink-border-width')), computedStyle.borderWidth ];
				}
				
				if(_options.borderColor !== false)
				{
					const start = getBlinkStartColor(computedStyle.borderColor, false);
					const stop = getBlinkStopColor(start, false);
					
					keyframes.borderColor = [ computedStyle.borderColor ];
					
					if(_options.show)
					{
						keyframes.borderColor.push(start, stop);
					}
					else
					{
						keyframes.borderColor.push(stop, start);
					}
					
					keyframes.borderColor.push(computedStyle.borderColor);
				}
			}

			if(_options.filter !== false)
			{
				if(_options.blur !== false)
				{
					var end;
					
					if(this._originalBeforeBlink)
					{
						if(this._originalBeforeBlink.filter.length > 0 && this._originalBeforeBlink.filter !== 'none' && this._originalBeforeBlink.filter.includes('blur'))
						{
							end = this._originalBeforeBlink.filter;
						}
						else
						{
							end = 'blur(0)';
						}
					}
					else
					{
						end = 'blur(0)';
					}

					keyframes.filter = [ 'blur(' + tryValue(_options.blur, this.getVariable('blink-blur')) + ')', end ];
				}
			}

			//
			_options.show = !_options.show;

			//
			if(--_options.count >= 0)
			{
				return this.BLINK = this.animate(keyframes, _options, callback);
			}
			else
			{
				//
				delete this.BLINK;
				
				//
				this.scrolling = null;
				//this.style.overflow = originalOverflow;
			}

			//
			/*if(this._originalBeforeBlink)
			{
				const blinkProps = HTMLElement.blinkProps;
					
				for(const k of blinkProps)
				{
					this.style[k] = this._originalBeforeBlink[k];
				}
				
				delete this._originalBeforeBlink;
			}*/

			//
			call(_callback, { type: 'blink', event: _e, count: origCount, this: this, options: _options, finish: (_options.count <= 0) }, _options.count <= 0);
		};
		
		//
		call(callback, null, null);

		//
		return origCount;
	}});

	//
	const animateData = (_callback, _data, _duration, _delete_mul, _property, _element, _intermediate_callback, _throw = DEFAULT_THROW) => {
		//
		if(typeof _throw !== 'boolean')
		{
			_throw = DEFAULT_THROW;
		}

		if(! _element)
		{
			if(_throw)
			{
				throw new Error('No valid _element');
			}
			
			return null;
		}
		else if(typeof _data !== 'string')
		{
			if(_throw)
			{
				throw new Error('No valid _data');
			}
			
			return null;
		}
		else if(! isString(_property, false))
		{
			if(_throw)
			{
				throw new Error('Invalid _property argument');
			}
			
			return null;
		}
		else if(typeof _element[_property] !== 'string')
		{
			if(_throw)
			{
				throw new Error('Your _element[_property] is not a String');
			}
			
			return null;
		}
		else if(! (isInt(_duration) && _duration > 0) && !(isInt((_duration = _element.getVariable('data-duration', true)) && _duration >= 0)))
		{
			return _element[_property] = _data;
		}
		else if(_element._dataAnimation)
		{
			delete _element._dataAnimation;

			setTimeout(() => {
				return animateData(_callback, _data, _duration, _delete_mul, _property, _element, _intermediate_callback, _throw);
			}, 0);

			return _data;
		}
		else
		{
			_element._dataAnimation = _data;
		}
		
		//
		if(! (isNumber(_delete_mul) && _delete_mul > 0 && _delete_mul < 1) && !(isNumber((_delete_mul = _element.getVariable('data-delete-mul', true)) && _delete_mul > 0)))
		{
			_delete_mul = null;
		}
		
		//
		var isHTML;
		
		switch(_property)
		{
			case 'textContent':
			case 'innerText':
				isHTML = false;
				break;
			case 'innerHTML':
				isHTML = true;
				break;
			default:
				if(_throw)
				{
					throw new Error('Invalid _property argument (that\'s no string in this _element)');
				}
				
				return null;
		}

		//
		if(isObject(config.animation))
		{
			if(isNumber(config.animation.textDuration))
			{
				_duration = Math.round(_duration * config.animation.textDuration);
			}
			else if(isNumber(config.animation.duration))
			{
				_duration = Math.round(_duration * config.animation.duration);
			}
			else
			{
				_duration = Math.round(_duration);
			}
		}
		else
		{
			_duration = Math.round(_duration);
		}

		//
		const original = _element[_property];
		const totalTextLength = (original.textLength + _data.textLength);
		const dataLength = _data.length;
		const charTime = (_duration / totalTextLength);
		var charTimeAdd, charTimeSub;
		
		if(_delete_mul === null)
		{
			charTimeSub = (charTime * original.textLength / totalTextLength);
			charTimeAdd = (charTime * _data.textLength / totalTextLength);
		}
		else
		{
			charTimeSub = (charTime * _delete_mul);
			charTimeAdd = (charTime - charTimeSub);
		}

		//
		var duration = 0, charDuration = 0, removed = false;
		var delta, now, open, pos = 0;
		var lastNow = Date.now();
		var script = '';
		
		//
		const checkForEscapeKey = (_event) => {
			switch(_event.key)
			{
				case 'Escape':
					window.removeEventListener('keydown', checkForEscapeKey);
					stopAnimation(false, false);
					return _event.stop();
			}
		}

		window.addEventListener('keydown', checkForEscapeKey);

		const stopAnimation = (_finish = true, _call = true) => {
			//
			if('_dataAnimation' in _element)
			{
				const all = _element._dataAnimation;
				delete _element._dataAnimation;
				_element.innerHTML = all;
			}

			//
			window.removeEventListener('keydown', checkForEscapeKey);
var c=0;
			//
			if(_call)
			{
				call(_callback, { type: (_finish ? 'finish' : 'stop'), property: _property, this: _element, data: _data, originalData: original, charTime, charTimeAdd, charTimeSub, html: isHTML }, _finish);
			}
		};
		
		const frame = () => {
			//
			now = Date.now();
			delta = (now - lastNow);
			lastNow = now;
			duration += delta;
			charDuration += delta;

			//
			if(removed)
			{
				open = '';

				while(charDuration >= charTimeAdd && pos < _data.length)
				{
					++pos;

					if(! _element._dataAnimation)
					{
						break;
					}
					else if(isHTML)
					{
						if(open)
						{
							if(_data[pos] === open)
							{
								open = '';
							}
						}
						else if(_data[pos] === '<')
						{
							open = '>';
						}
						else if(_data[pos] === '&')
						{
							open = ';';
						}
					}

					if(! open)
					{
						charDuration -= charTimeAdd;
					}
				}

				if(_element._dataAnimation)
				{
					_element[_property] = _data.substr(0, pos);

					if(_intermediate_callback)
					{
						call(_intermediate_callback, { type: 'intermediate', original, this: _element, property: _property, type: _property, data: _data, removed: true });
					}

					if(_element[_property].length >= _data.length)
					{
						return stopAnimation(true);
					}
				}
			}
			else
			{
				pos = 0;
				
				while(charDuration >= charTimeSub && pos < _element[_property].length)
				{
					++pos;
					
					if(! _element._dataAnimation)
					{
						break;
					}
					else if(isHTML)
					{
						if(open)
						{
							if(_element[_property][_element[_property].length - pos] === open)
							{
								open = '';
							}
						}
						else if(_element[_property][_element[_property].length - pos] === '>')
						{
							open = '<';
						}
						else if(_element[_property][_element[_property].length - pos] === ';')
						{
							open = '&';
						}
						else
						{
							charDuration -= charTimeSub;
						}
					}
					else
					{
						charDuration -= charTimeSub;
					}
				}
				
				if(_element._dataAnimation)
				{
					_element[_property] = _element[_property].slice(0, -pos);
					
					if(_intermediate_callback)
					{
						call(_intermediate_callback, { type: 'intermediate', original, this: _element, property: _property, type: _property, data: _data, removed: (_element[_property].length === 0) });
					}
				}
					
				if(_element[_property].length === 0)
				{
					removed = true;
					pos = -1;
				}
			}

			//
			if(_element._dataAnimation)
			{
				return window.requestAnimationFrame(frame);
			}

			return stopAnimation(false);
		};
		
		//
		window.requestAnimationFrame(frame);
		
		//
		return _data;
	};

	//
	Object.defineProperty(Node.prototype, 'setText', { value: function(_callback, _data, _duration = this.getVariable('data-duration', true), _delete_mul = this.getVariable('data-delete-mul', true), _intermediate_callback, _property = 'textContent', _throw = DEFAULT_THROW)
	{
		if(! isString(_property, false))
		{
			_property = 'textContent';
		}

		if(typeof _data !== 'string')
		{
			return this[_property];
		}
		
		return animateData(_callback, _data, _duration, _delete_mul, _property, this, _intermediate_callback, _throw);
	}});
	
	Object.defineProperty(Element.prototype, 'setText', { value: Node.prototype.setText });
	
	Object.defineProperty(Element.prototype, 'setHTML', { value: function(_callback, _data, _duration = this.getVariable('data-duration', true), _delete_mul = this.getVariable('data-delete-mul', true), _intermediate_callback, _property = 'innerHTML', _throw = DEFAULT_THROW)
	{
		if(! isString(_property, false))
		{
			_property = 'innerHTML';
		}

		if(typeof _data !== 'string')
		{
			return this[_property];
		}
		
		return animateData(_callback, _data, _duration, _delete_mul, _property, this, _intermediate_callback, _throw);
	}});
	
	Object.defineProperty(HTMLElement.prototype, 'setHTML', { value: Element.prototype.setHTML });
	
	Object.defineProperty(HTMLElement.prototype, 'setText', { value: function(_callback, _data, _duration = this.getVariable('data-duration', true), _delete_mul = this.getVariable('data-delete-mul', true), _intermediate_callback, _property = 'innerText', _throw = DEFAULT_THROW)
	{
		if(! isString(_property, false))
		{
			_property = 'innerText';
		}

		if(arguments.length === 0)
		{
			return this[_property];
		}
		
		return animateData(_callback, _data, _duration, _delete_mul, _property, this, _intermediate_callback, _throw);
	}});

	Object.defineProperty(HTMLElement.prototype, 'setStyle', { value: function(_key, _value, _options, _callback, _throw = DEFAULT_THROW)
	{
		if(! isString(_key, false))
		{
			throw new Error('Invalid _key argument (not a non-empty String)');
		}
		else if(typeof _value !== 'string')
		{
			throw new Error('Invalid _value argument (not a String)');
		}
		else if(! HTMLElement.isStyle(_key))
		{
			throw new Error('Invalid _key argument (not a CSS style property)');
		}

		return this.animate({ [_key]: [ null, _value ] }, _options, _callback, _throw);
	}});

	//
	Object.defineProperty(HTMLElement, 'inOutProps', { get: function()
	{
		return [ 'opacity', 'transform', 'filter', 'borderWidth', 'borderRadius', 'borderColor', 'fontSize', 'overflow', 'backgroundColor', 'color', 'left', 'top', 'right', 'bottom', 'width', 'height' ];
	}});
	
	Object.defineProperty(HTMLElement.prototype, 'in', { value: function(_options, _callback, _throw = DEFAULT_THROW)
	{
		//
		if(this.IN)
		{
			return this.IN.stop((_e, _f) => {
				return this.in(_options, _callback, _throw);
				//return this.in(Object.assign(_options, { fontSize: null }), _callback, _throw);
			});
		}
		else if(this.OUT)
		{
			return this.OUT.stop((_e, _f) => {
				return this.in(_options, _callback, _throw);
				//return this.in(Object.assign(_options, { fontSize: null }), _callback, _throw);
			});
		}
		else if(typeof _callback !== 'function')
		{
			_callback = null;
		}

		//
		const computedStyle = getComputedStyle(this);

		if(! this._originalInOutStyle)
		{
			this._originalInOutStyle = computedStyle.getPropertyValue(... HTMLElement.inOutProps);
		}

		//		
		_options = this.getAnimateOptions(Object.assign({
			persist: true, state: false,
			opacity: this.getVariable('opacity', true),
			transform: this.getVariable('transform', true),
			scale: this.getVariable('scale', true),
			rotate: this.getVariable('rotate', true),
			rotateX: this.getVariable('rotate-x', true),
			rotateY: this.getVariable('rotate-y', true),
			rotateZ: this.getVariable('rotate-z', true),
			filter: this.getVariable('filter', true),
			blur: this.getVariable('blur'),
			border: this.getVariable('border', true),
			borderWidth: this.getVariable('border-width', ['px']),
			borderRadius: this.getVariable('border-radius', ['px']),
			borderColor: this.getVariable('border-color', null),
			font: this.getVariable('font', true),
			fontSize: this.getVariable('font-size'),
			colors: this.getVariable('colors', true),
			color: this.getVariable('color'),
			backgroundColor: this.getVariable('background-color', null),
			size: this.getVariable('size', true),
			position: this.getVariable('position', true),
			left: this.getVariable('left', false),
			top: this.getVariable('top', false),
			right: this.getVariable('right', false),
			bottom: this.getVariable('bottom', false),
			width: this.getVariable('width', false),
			height: this.getVariable('height', false)
		}, _options));

		//
		const keyframes = {};

		//
		if(_options.position !== false)
		{
			if(_options.left)
			{
				keyframes.left = [ computedStyle.left, _options.left = setValue(_options.left, 'px') ];
			}

			if(_options.top)
			{
				keyframes.top = [ computedStyle.top, _options.top = setValue(_options.top, 'px') ];
			}

			if(_options.right)
			{
				keyframes.right = [ computedStyle.right, _options.right = setValue(_options.right, 'px') ];
			}

			if(_options.bottom)
			{
				keyframes.bottom = [ computedStyle.bottom, _options.bottom = setValue(_options.bottom, 'px') ];
			}
		}

		if(_options.size !== false)
		{
			if(_options.width)
			{
				keyframes.width = [ computedStyle.width, _options.width = setValue(_options.width, 'px') ];
			}

			if(_options.height)
			{
				keyframes.height = [ computedStyle.height, _options.height = setValue(_options.height, 'px') ];
			}
		}
		
		//
		if(_options.opacity !== false)
		{
			keyframes.opacity = new Array(2);

			if(computedStyle.opacity.length === 0 || computedStyle.opacity === '1')
			{
				keyframes.opacity[0] = '0';
			}
			else
			{
				keyframes.opacity[0] = computedStyle.opacity;
			}

			keyframes.opacity[1] = '1';
		}

		if(_options.transform !== false)
		{
			if(_options.scale !== false)
			{
				keyframes.transform = [ computedStyle.transform || 'scale(0)' ];
				keyframes.transform[1] = 'scale(1.2)';
				keyframes.transform[2] = 'scale(0.8)';
				keyframes.transform[3] = 'scale(1)';
			}
			else if(_options.rotate !== false && (_options.rotateX !== false || _options.rotateY !== false || _options.rotateZ !== false))
			{
				keyframes.transform = [ computedStyle.transform || 'scale(1)' ];
				keyframes.transform[1] = '';
				keyframes.transform[2] = '';
				keyframes.transform[3] = '';
			}

			if(_options.rotate !== false)
			{
				const rot = { X: _options.rotateX, Y: _options.rotateY, Z: _options.rotateZ };

				for(const idx in rot)
				{
					if(isInt(rot[idx]))
					{
						rot[idx] = Math.random.int(rot[idx], -rot[idx], true) + 'deg';
					}
					else if(isString(rot[idx], false))
					{
						//
					}
					else if(isArray(rot[idx], false))
					{
						if(rot[idx][0][1] === rot[idx][1][1])
						{
							rot[idx] = Math.random.int(rot[idx][0][0], rot[idx][1][0], true) + rot[idx][0][1];
						}
						else
						{
							throw new Error('Your \'' + rotate + idx + '\' option differs in it\'s unit suffix');
						}
					}
					else
					{
						delete rot[idx];
					}
				}

				var rotate = '';

				for(const idx in rot)
				{
					rotate += (rotate.length === 0 ? '' : ' ') + `rotate${idx}(${rot[idx]})`;
				}

				if(rotate.length > 0)
				{
					keyframes.transform[2] += (keyframes.transform[2] ? ' ' : '') + rotate;
					keyframes.transform[3] += (keyframes.transform[3] ? ' ' : '') + 'rotateX(0) rotateY(0) rotateZ(0)';
				}
			}
		}

		if(_options.filter !== false)
		{
			if(_options.blur !== false)
			{
				keyframes.filter = [ computedStyle.filter, 'blur(' + tryValue(_options.blur, this.getVariable('blur', null)) + ')', 'blur(0)' ];
			}
		}
		
		if(_options.border !== false)
		{
			if(_options.borderWidth !== false)
			{
				keyframes.borderWidth = [ computedStyle.borderWidth, tryValue(_options.borderWidth, this.getVariable('border-width', null)), this._originalInOutStyle.borderWidth ];
			}
			
			if(_options.borderRadius !== false)
			{
				keyframes.borderRadius = [ computedStyle.borderRadius, tryValue(_options.borderRadius, this.getVariable('border-radius', null)), this._originalInOutStyle.borderRadius ];
			}

			if(_options.colors !== false && _options.borderColor !== false)
			{
				keyframes.borderColor = [ computedStyle.borderColor, (color.isValid(_options.borderColor) ? _options.borderColor : color.contrast.hex(computedStyle.backgroundColor)), this._originalInOutStyle.borderColor ];
			}
		}
		
		if(_options.font !== false)
		{
			if(_options.fontSize !== false)
			{
				keyframes.fontSize = [ computedStyle.fontSize, tryValue(_options.fontSize, this.getVariable('font-size', null)), this._originalInOutStyle.fontSize ];
			}
		}
		
		if(_options.colors !== false)
		{
			if(_options.color !== false)
			{
				keyframes.color = [ computedStyle.color, (color.isValid(_options.color) ? _options.color : color.textColor.rgb(computedStyle.color)), this._originalInOutStyle.color ];
			}
			
			if(_options.backgroundColor !== false)
			{
				keyframes.backgroundColor = [ computedStyle.backgroundColor, (color.isValid(_options.backgroundColor) ? _options.backgroundColor : color.textColor.rgb(computedStyle.backgroundColor)), this._originalInOutStyle.backgroundColor ];
			}
		}
		
		//
		this.style.overflow = 'hidden';
		this.scrolling = false;
		
		this.isOpen = false;
		this.isClosed = false;

		//
		return this.IN = this.animate(keyframes, _options, (_e, _f) => {
			//
			this.isOpen = _f;
			this.isClosed = false;

			//
			delete this.IN;
			delete this.OUT;

			//
			this.scrolling = null;
			this.style.overflow = this._originalInOutStyle.overflow;
			
			//
			if(_callback)
			{
				call(_callback, { type: 'in', event: _e, finish: _f, this: this }, _f);
			}

			//
			this.emit('in', { type: 'in', subType: _e.type, options: _options, event: _e, finish: _f });
			this.emit(_e.type, { type: _e.type, baseType: 'in', options: _options, event: _e, finish: _f });
		}, _throw);
	}});
	
	Object.defineProperty(HTMLElement.prototype, 'out', { value: function(_options, _callback, _throw = DEFAULT_THROW)
	{
		//
		if(this.OUT)
		{
			return this.OUT.stop((_e, _f) => {
				return this.out(_options, _callback, _throw);
			});
		}
		else if(this.IN)
		{
			return this.IN.stop((_e, _f) => {
				return this.out(_options, _callback, _throw);
			});
		}
		else if(typeof _callback !== 'function')
		{
			_callback = null;
		}

		//
		const computedStyle = getComputedStyle(this);

		//
		if(! this._originalInOutStyle)
		{
			this._originalInOutStyle = computedStyle.getPropertyValue(... HTMLElement.inOutProps);
		}

		//
		_options = this.getAnimateOptions(Object.assign({
			persist: true, state: false,
			opacity: this.getVariable('opacity', true),
			transform: this.getVariable('transform', true),
			scale: this.getVariable('scale', true),
			rotate: this.getVariable('rotate', true),
			rotateX: this.getVariable('rotate-x', true),
			rotateY: this.getVariable('rotate-y', true),
			rotateZ: this.getVariable('rotate-z', true),
			filter: this.getVariable('filter', true),
			blur: this.getVariable('blur'),
			border: this.getVariable('border', true),
			borderWidth: this.getVariable('border-width'),
			borderRadius: this.getVariable('border-radius'),
			borderColor: this.getVariable('border-color'),
			font: this.getVariable('font', true),
			fontSize: this.getVariable('font-size'),
			colors: this.getVariable('colors', true),
			color: this.getVariable('color'),
			backgroundColor: this.getVariable('background-color'),
			size: this.getVariable('size', true),
			position: this.getVariable('position', true),
			left: this.getVariable('left', false),
			top: this.getVariable('top', false),
			right: this.getVariable('right', false),
			bottom: this.getVariable('bottom', false),
			width: this.getVariable('width', false),
			height: this.getVariable('height', false)
		}, _options));

		//
		const keyframes = {};

		//
		if(_options.position !== false)
		{
			if(_options.left)
			{
				keyframes.left = [ computedStyle.left, _options.left = setValue(_options.left, 'px') ];
			}

			if(_options.top)
			{
				keyframes.top = [ computedStyle.top, _options.top = setValue(_options.top, 'px') ];
			}

			if(_options.right)
			{
				keyframes.right = [ computedStyle.right, _options.right = setValue(_options.right, 'px') ];
			}

			if(_options.bottom)
			{
				keyframes.bottom = [ computedStyle.bottom, _options.bottom = setValue(_options.bottom, 'px') ];
			}
		}

		if(_options.size !== false)
		{
			if(_options.width)
			{
				keyframes.width = [ computedStyle.width, _options.width = setValue(_options.width, 'px') ];
			}

			if(_options.height)
			{
				keyframes.height = [ computedStyle.height, _options.height = setValue(_options.height, 'px') ];
			}
		}

		//
		if(_options.opacity !== false)
		{
			keyframes.opacity = new Array(2);

			if(computedStyle.opacity.length === 0 || computedStyle.opacity === '0')
			{
				keyframes.opacity[0] = '1';
			}
			else
			{
				keyframes.opacity[0] = computedStyle.opacity;
			}

			keyframes.opacity[1] = '0';
		}

		if(_options.transform !== false)
		{
			if(_options.scale !== false)
			{
				keyframes.transform = [ computedStyle.transform || 'scale(1)' ];
				keyframes.transform[1] = 'scale(0.8)';
				keyframes.transform[2] = 'scale(1.2)';
				keyframes.transform[3] = 'scale(0)';
			}
			else if(_options.rotate !== false && (_options.rotateX !== false || _options.rotateY !== false || _options.rotateZ !== false))
			{
				keyframes.transform = [ computedStyle.transform || 'none' ];
				keyframes.transform[1] = '';
				keyframes.transform[2] = '';
				keyframes.transform[3] = '';
			}

			if(_options.rotate !== false)
			{
				const rot = { X: _options.rotateX, Y: _options.rotateY, Z: _options.rotateZ };

				for(const idx in rot)
				{
					if(isInt(rot[idx]))
					{
						rot[idx] = Math.random.int(rot[idx], -rot[idx], true) + 'deg';
					}
					else if(isString(rot[idx], false))
					{
						//
					}
					else if(isArray(rot[idx], false))
					{
						if(rot[idx][0][1] === rot[idx][1][1])
						{
							rot[idx] = Math.random.int(rot[idx][0][0], rot[idx][1][0], true) + rot[idx][0][1];
						}
						else
						{
							throw new Error('Your \'' + rotate + idx + '\' option differs in it\'s unit suffix');
						}
					}
					else
					{
						delete rot[idx];
					}
				}

				var rotate = '';

				for(const idx in rot)
				{
					rotate += (rotate.length === 0 ? '' : ' ') + `rotate${idx}(${rot[idx]})`;
				}

				if(rotate.length > 0)
				{
					keyframes.transform[2] += (keyframes.transform[2] ? ' ' : '') + rotate;
					keyframes.transform[3] += (keyframes.transform[3] ? ' ' : '') + 'rotateX(0) rotateY(0) rotateZ(0)';
				}
			}
		}
		
		if(_options.filter !== false)
		{
			if(_options.blur !== false)
			{
				keyframes.filter = [ computedStyle.filter, 'blur(' + tryValue(_options.blur, this.getVariable('blur', null)) + ')', this._originalInOutStyle.filter ];
			}
		}
		
		if(_options.border !== false)
		{
			if(_options.borderWidth !== false)
			{
				keyframes.borderWidth = [ computedStyle.borderWidth, tryValue(_options.borderWidth, this.getVariable('border-width', null)), this._originalInOutStyle.borderWidth ];
			}
			
			if(_options.borderRadius !== false)
			{
				keyframes.borderRadius = [ computedStyle.borderRadius, tryValue(_options.borderRadius, this.getVariable('border-radius', null)), this._originalInOutStyle.borderRadius ];
			}
			
			if(_options.colors !== false && _options.borderColor !== false)
			{
				keyframes.borderColor = [ computedStyle.borderColor, (color.isValid(_options.borderColor) ? _options.borderColor : color.contrast.hex(computedStyle.backgroundColor)), this._originalInOutStyle.borderColor ];
			}
		}

		if(_options.font !== false)
		{
			if(_options.fontSize !== false)
			{
				keyframes.fontSize = [ computedStyle.fontSize, tryValue(_options.fontSize, this.getVariable('font-size', null)), this._originalInOutStyle.fontSize ];
			}
		}
		
		if(_options.colors !== false)
		{
			if(_options.color !== false)
			{
				keyframes.color = [ computedStyle.color, (color.isValid(_options.color) ? _options.color : color.textColor.rgb(computedStyle.color)), this._originalInOutStyle.color ];
			}
			
			if(_options.backgroundColor !== false)
			{
				keyframes.backgroundColor = [ computedStyle.backgroundColor, (color.isValid(_options.backgroundColor) ? _options.backgroundColor : color.textColor.rgb(computedStyle.backgroundColor)), this._originalInOutStyle.backgroundColor ];
			}
		}

		//
		this.style.overflow = 'hidden';
		this.scrolling = false;

		//
		this.isOpen = false;
		this.isClosed = false;

		//
		return this.OUT = this.animate(keyframes, _options, (_e, _f) => {
			//
			this.isOpen = false;
			this.isClosed = _f;

			//
			delete this.IN;
			delete this.OUT;

			//
			this.scrolling = null;
			this.style.overflow = this._originalInOutStyle.overflow;

			//
			if(_f)
			{
				delete this._originalInOutStyle;
			}
			
			//
			call(_callback, { type: 'out', event: _e, finish: _f, this: this }, _f);
			
			//
			this.emit('out', { type: 'out', subType: _e.type, options: _options, event: _e, finish: _f });
			this.emit(_e.type, { type: _e.type, baseType: 'out', options: _options, event: _e, finish: _f });
		}, _throw);
	}});

	//
	Object.defineProperty(HTMLElement.prototype, 'getAnimateOptions', { value: function(... _args)
	{
		if(_args[0] === false)
		{
			return null;
		}

		const result = Object.assign(... _args);
		var hadDuration = false;

		for(var i = 0; i < _args.length; ++i)
		{
			if(isInt(_args[i]) && _args[i] >= 0)
			{
				if(hadDuration === null)
				{
					throw new Error('Too many numeric arguments (only need .duration and .delay)');
				}
				else if(hadDuration === true)
				{
					result.delay = _args.splice(i--, 1)[0];
					hadDuration = null;
				}
				else if(hadDuration === false)
				{
					result.duration = _args.splice(i--, 1)[0];
					hadDuration = true;
				}
			}
			else if(typeof _args[i] === 'boolean')
			{
				const bool = _args.splice(i--, 1)[0];
				
				if(hadDuration === null)
				{
					throw new Error('Too many boolean arguments (only need a single one)');
				}
				else if(hadDuration === false)
				{
					result.duration = (bool ? this.getVariable('duration', true) : 0);
					hadDuration = true;
				}
				else if(hadDuration === true)
				{
					result.delay = (bool ? this.getVariable('delay', true) : 0);
					hadDuration = null;
				}
			}
			else if(_args[i] === null)
			{
				_args.splice(i--, 1);

				if(hadDuration === null)
				{
					throw new Error('Too many null arguments (can only process two maximum)');
				}
				else if(hadDuration === false)
				{
					result.duration = 0;
					hadDuration = true;
				}
				else if(hadDuration === true)
				{
					result.delay = 0;
					hadDuration = null;
				}
			}
			else if(isObject(_args[i]))
			{
				_args.splice(i--, 1);
			}
		}
		
		if(! (isInt(result.duration) && result.duration >= 0))
		{
			result.duration = this.getVariable('duration', true);
		}
		
		if(! (isInt(result.delay) && result.delay >= 0) || result.delay === true)
		{
			result.delay = this.getVariable('delay', true);
		}
		else if(result.delay === false)
		{
			result.delay = 0;
		}
		
		if(typeof result.persist !== 'boolean')
		{
			result.persist = this.getVariable('persist', true);
		}
		
		if(typeof result.state !== 'boolean')
		{
			result.state = this.getVariable('state', true);
		}
		
		if(typeof result.origin !== 'string')
		{
			if((result.origin = this.getVariable('transform-origin')).length === 0)
			{
				delete result.origin;
			}
		}
		else if(result.origin.length === 0)
		{
			delete result.origin;
		}

		if(typeof result.easing === 'string' && result.easing.length === 0)
		{
			delete result.easing;
		}
		else if(typeof result.easing !== 'string')
		{
			if((result.easing = this.getVariable('easing')).length === 0)
			{
				delete result.easing;
			}
		}

		if(typeof result.none !== 'boolean')
		{
			if(result.duration <= 0 && result.delay <= 0)
			{
				result.none = true;
			}
			else
			{
				result.none = false;
			}
		}

		if(isObject(config.animation) && (isNumber(result.duration) || isNumber(result.delay)))
		{
			if(isNumber(result.duration) && isNumber(config.animation.duration))
			{
				result.duration = Math.round(result.duration * config.animation.duration);
			}

			if(isNumber(result.delay) && isNumber(config.animation.delay))
			{
				result.delay = Math.round(result.delay * config.animation.delay);
			}
		}

		return result;
	}});
	
	//

})();

