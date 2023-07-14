(function()
{

	//
	debounce = function(_function, _timeout, ... _args)
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

		return function(... _a)
		{
			_a.populate(_args);

			return setTimeout(() => {
				return _function.apply(this, _a);
			}, _timeout);
		};
	};

})();

