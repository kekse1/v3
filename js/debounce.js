(function()
{

	//
	debounce = function(_function, _timeout, _context = undefined, ... _args)
	{
		if(typeof _function !== 'function')
		{
			throw new Error('Invalid _function argument');
		}
		else if(!isInt(_timeout))
		{
			throw new Error('Invalid _timeout argument');
		}
		else if(_timeout < 0)
		{
			_timeout = 0;
		}

		const result = function(... _a)
		{
			_a.populate(_args);

			return setTimeout(() => {
				return _function(... _a);
			}, _timeout);
		};

		if(typeof _context !== 'undefined')
		{
			if(Object.isExtensible(_context))
			{
				return result.bind(_context);
			}
			else
			{
				_args.unshift(_context);
			}
		}

		return result;
	};

})();

