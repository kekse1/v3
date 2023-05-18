(function()
{

	//
	Object.defineProperty(navigator, 'offLine', { get: function()
	{
		return !navigator.onLine;
	}});

	Object.defineProperty(navigator, 'hasLine', { get: function()
	{
		if(navigator.onLine)
		{
			return true;
		}
		else if(location.isLocalhost)
		{
			return true;
		}

		return false;
	}});

	//

})();

