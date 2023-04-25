(function()
{

	//
	const DEFAULT_THROW = true;

	//
	timing = {
		seconds: 0,
		listener: [],
		timeout: null
	};

	//
	timing.seconds = 0;
	timing.listener = [];
	timing.timeout = null;
	timing.paused = 0;

	Object.defineProperty(timing, 'isRunning', { get: function()
	{
		return (timing.timeout !== null);
	}});

	//
	const secondFunction = () => {
		//
		++timing.seconds;
		
		//
		var stopped = false;

		//
		for(var i = 0; i < timing.listener.length; ++i)
		{
			if(timing.listener[i].paused)
			{
				++timing.listener[i].skipped;
			}
			else if((timing.seconds % timing.listener[i].second) === 0)
			{
				//
				if(timing.listener[i].max === 0)
				{
					timing.listener.splice(i--, 1);
					continue;
				}
				else
				{
					++timing.listener[i].count;
					timing.listener[i].last = Date.now();

					try
					{
						call(timing.listener[i].callback, { seconds: timing.seconds, ... timing.listener[i], rest: timing.listener[i].rest });
					}
					catch(_error)
					{
						++timing.listener[i].errors;

						if(timing.listener[i].throw)
						{
							throw _error;
						}
					}
				}

				if(timing.listener[i].max > 0 && timing.listener[i].count >= timing.listener[i].max)
				{
					timing.listener.splice(i--, 1);
				}
			}

			if(timing.listener.length === 0)
			{
				stopTimeout();
				stopped = true;
			}
			else if(timing.paused === timing.listener.length)
			{
				stopTimeout();
				stopped = true;
			}
		}

		//
		if(stopped)
		{
			return timing.seconds;
		}
		else if(timing.timeout !== null && timing.listener.length > 0)
		{
			doTimeout();
		}
		else
		{
			if(timing.timeout !== null)
			{
				clearTimeout(timing.timeout);
			}

			timing.timeout = null;
		}

		//
		return timing.seconds;
	};

	const doTimeout = () => {
		return timing.timeout = setTimeout(secondFunction, (1000 - (Date.now() % 1000)));
	};

	const stopTimeout = () => {
		if(timing.timeout === null)
		{
			return false;
		}
		else
		{
			clearTimeout(timing.timeout);
			timing.timeout = null;
		}

		return true;
	};

	//
	timing.add = (_callback, _second = 1, _max = null, _throw = DEFAULT_THROW) => {
		if(! (isInt(_second) && _second >= 1))
		{
			throw new Error('Invalid _second argument');
		}
		else if(typeof _callback !== 'function')
		{
			throw new Error('Invalid _callback argument');
		}
		else if(! isInt(_max))
		{
			if(_max === true)
			{
				_max = 1;
			}
			else
			{
				_max = null;
			}
		}

		if(typeof _throw !== 'boolean')
		{
			_throw = DEFAULT_THROW;
		}

		if(! timing.isRunning)
		{
			doTimeout();
		}

		const result = {
			id: randomID(),
			callback: _callback,
			second: _second,
			count: 0,
			max: _max,
			paused: false,
			skipped: 0,
			destroyed: false,
			last: null,
			errors: 0,
			throw: _throw
		};

		result.destroy = () => {
			if(result.destroyed)
			{
				return false;
			}
			else
			{
				result.destroyed = true;
			}

			delete result.callback;
			delete result.second;
			delete result.paused;
			delete result.last;

			return true;
		};

		result.stop = () => {
			return timing.remove(result.id, result.callback, result.second)
		};

		result.pause = (_value = !result.paused) => {
			if(result.paused === _value)
			{
				return false;
			}
			else if(result.paused = _value)
			{
				++timing.paused;
			}
			else
			{
				//
				--timing.paused;

				//
				if(! timing.isRunning)
				{
					doTimeout();
				}
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
					return result.max = (result.count + _value);
				}

				return result.rest;
			}
		});

		return timing.listener.push(result);
	};

	timing.remove = (... _args) => {
		_options = Object.assign(... _args);

		for(var i = 0; i < _args.length; ++i)
		{
			if(typeof _args[i] === 'function')
			{
				_options.callback = _args.splice(i--, 1)[0];
			}
			else if(isInt(_args[i]) && _args[i] >= 0)
			{
				_options.second = _args.splice(i--, 1)[0];
			}
			else if(isString(_args[i], false))
			{
				_options.id = _args.splice(i--, 1);
			}
		}

		return timing.clear(... Object.values(_options));
	};

	timing.clear = (... _params) => {
		const options = Object.assign(... _params);

		var second = options.second;
		var callback = options.callback;
		var id = options.id;

		for(var i = 0; i < _params.length; ++i)
		{
			if(isInt(_params[i]) && _params[i] >= 1)
			{
				second = _params.splice(i--, 1)[0];
			}
			else if(typeof _params[i] === 'function')
			{
				callback = _params.splice(i--, 1)[0];
			}
			else if(isString(_params[i], false))
			{
				id = _params.splice(i--, 1)[0];
			}
		}

		if(! (isInt(second) && second >= 1))
		{
			second = null;
		}

		if(typeof callback !== 'function')
		{
			callback = null;
		}

		if(! isString(id, false))
		{
			id = null;
		}

		const result = [];
		var count = 0;
		var rest;

		if(second !== null)
		{
			++count;
		}

		if(callback !== null)
		{
			++count;
		}

		if(id !== null)
		{
			++count;
		}

		for(var i = timing.listener.length, j = 0; i >= 0; --i)
		{
			rest = count;

			if(second !== null)
			{
				if(timing.listener[i].second === second)
				{
					--rest;
				}
			}

			if(callback !== null)
			{
				if(timing.listener[i].callback === callback)
				{
					--rest;
				}
			}

			if(id !== null)
			{
				if(timing.listener[i].id === id)
				{
					--rest;
				}
			}

			if(rest <= 0)
			{
				timing.listener[i].destroy();
				result[j++] = timing.listener.splice(i, 1)[0];
			}
		}

		if(timing.listener.length === 0 && timing.isRunning)
		{
			stopTimeout();
		}

		return result;
	};

	//
	
})();

