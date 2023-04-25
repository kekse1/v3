(function()
{

	//
	isError = (... _args) => {
		if(_args.length === 0)
		{
			return null;
		}
		else for(var i = 0; i < _args.length; ++i)
		{
			if(! was(_args[i], 'Error'))
			{
				return false;
			}
		}

		return true;
	};

	Object.defineProperty(Error, 'isError', { value: isError.bind(Error) });

	//
	
})();

