//
// u.a. besucher-zaehler, uhrwerk(!! siehe 24h-analog, sin/cos, .. @ APP(!)),
// UPTIME-zaehlungen mit cookies (und TODO: neben besucher-count auch deren
// uptime-summe; die ja btw. je ++timing.second erneut als cookie gesetzt...)
// .. evtl. server dazu? RestFUL/API!??. ...
//
(function()
{

	//
	const DEFAULT_THROW = true;
	const DEFAULT_CALLBACKS = (1 | 2);
	const DEFAULT_INSTANT = true;

	//
	dynamic = (_throw = DEFAULT_THROW) => {
		var result = 0;

		if(dynamic.module) for(const idx in dynamic.module)
		{
			if(typeof dynamic.module[idx] === 'function')
			{
				dynamic.module[idx](_throw);
				++result;
			}
		}

		return result;
	};

	//
	dynamic.type = Object.create(null);
	dynamic.id = Object.create(null);
	dynamic.url = Object.create(null);

	//
	dynamic.call = (_type, _raw = true, _throw = DEFAULT_THROW) => {
		//
		if(! isString(_type, false))
		{
			if(_throw)
			{
				throw new Error('Invalid _type argument');
			}

			return undefined;
		}
		else if(! (_type in dynamic.type))
		{
			if(_throw)
			{
				throw new Error('Type \'' + _type + '\' not found');
			}

			return undefined;
		}

		//
		const item = dynamic.type[_type];

		//
		call(item._callback, _raw);

		//
		return null;
	};

	//
	dynamic.add = (_type, _url, _delay, _callback, _max, _callbacks = DEFAULT_CALLBACKS, _instant = DEFAULT_INSTANT, _throw = DEFAULT_THROW) => {
		if(! isString(_type, false))
		{
			throw new Error('Invalid _type argument');
		}
		else if(_type in dynamic.type)
		{
			if(_throw)
			{
				throw new Error('The type \'' + _type + '\' is already defined');
			}

			return null;
		}
		else if(! isString(_url, false))
		{
			throw new Error('Invalid _url argument');
		}
		else
		{
			_url = path.resolve(_url);
		}

		if(! (isInt(_delay) && _delay > 0))
		{
			throw new Error('Invalid _delay argument');
		}

		if(typeof _throw !== 'boolean')
		{
			_throw = DEFAULT_THROW;
		}
		
		if(isInt(_max))
		{
			if(_max <= 0)
			{
				return null;
			}
		}
		else if(typeof _max === 'boolean')
		{
			if(_max)
			{
				_max = 1;
			}
			else
			{
				_max = null;
			}
		}

		if(typeof _callback !== 'function')
		{
			throw new Error('Invalid _callback argument');
		}

		if(! (isInt(_callbacks) && _callbacks >= 0))
		{
			_callbacks = DEFAULT_CALLBACKS;
		}

		if(typeof _instant !== 'boolean')
		{
			_instant = DEFAULT_INSTANT;
		}

		const result = Object.create(null);

		result.id = randomID();
		result.type = _type;
		result.url = _url;
		result.delay = _delay
		result.callback = _callback;
		result.callbacks = _callbacks;
		result.max = _max;
		result.count = 0;
		result.timeout = null;
		result.paused = false;
		result.busy = false;
		result.skipped = 0;
		result.errors = 0;
		result.last = null;
		result.throw = _throw;

		result.destroy = () => {
			if(result.destroyed)
			{
				return false;
			}
			else
			{
				result.destroyed = true;
			}

			delete result.delay;
			delete result.callback;
			delete result.timeout;
			delete result.paused;
			delete result.busy;
			delete result.throw;

			return true;
		};

		result.stop = () => {
			return dynamic.remove(result.id, result.throw);
		};

		result.pause = (_value = !result.paused) => {
			if(_value === result.paused)
			{
				return false;
			}
			else if(result.paused = _value)
			{
				if(result.timeout !== null)
				{
					clearTimeout(result.timeout);
					result.timeout = null;
				}
			}
			else if(result.timeout === null)
			{
				result.timeout = setTimeout(result._callback, result.delay);
			}

			return true;
		};

		result.resume = () => {
			return result.pause(false);
		};

		Object.defineProperty(result, 'rest', {
			get: function()
			{
				if(result.max === null)
				{
					return null;
				}

				return (result.max - result.count);
			},
			set: function(_value)
			{
				if(isInt(_value) && _value >= 0)
				{
					result.max = (_value + result.count);
				}

				return result.rest;
			}
		});

		result._callback = (... _args) => {
			//
			if(result.busy)
			{
				return ++result.skipped;
			}
			else
			{
				result.busy = true;
			}

			//
			ajax(result.url, result.callbacks, (_event, _request) => {
				//
				result.last = Date.now();
				++result.count;

				//
				_event.dynamic = result;
				_request.dynamic = result;

				//
				try
				{
					call(result.callback, _event, _request, { ... result });
				}
				catch(_error)
				{
					++result.errors;

					if(result.throw)
					{
						throw _error;
					}
				}

				//
				result.busy = false;
			});

			//
			if(_args[0] !== true)
			{
				if(result.max !== null && result.max >= 0)
				{
					if(result.count >= result.max)
					{
						return dynamic.remove(result.type, result.throw);
					}
				}
			}

			//
			return result.timeout = setTimeout(result._callback, result.delay);
		};

		//
		const delay = (_instant ? 0 : result.delay);
		result.timeout = setTimeout(result._callback, delay);

		//
		dynamic.type[result.type] = result;
		dynamic.id[result.id] = result;
		dynamic.url[result.url] = result;

		//
		return result;
	};

	dynamic.remove = (_type, _throw = DEFAULT_THROW) => {
		//
		if(! isString(_type, false))
		{
			throw new Error('Invalid _type argument');
		}

		const byID = isID(_type);

		if(! byID && !(_type in dynamic.type))
		{
			if(_throw)
			{
				throw new Error('The type \'' + _type + '\' is not available');
			}

			return null;
		}
		else if(byID && !(_type in dynamic.id))
		{
			if(_throw)
			{
				throw new Error('The ID \'' + _type + '\' is not available');
			}

			return null;
		}
		
		//
		var result;

		if(byID) result = dynamic.id[_type];
		else result = dynamic.type[_type];

		//
		if(result.timeout !== null)
		{
			clearTimeout(result.timeout);
			result.timeout = null;
		}

		//
		result.destroy();

		//
		delete dynamic.type[result.type];
		delete dynamic.id[result.id];
		delete dynamic.url[result.url];

		//
		return result;
	};

	//
	window.addEventListener('ready', dynamic, { once: true });
	
	//

})();

