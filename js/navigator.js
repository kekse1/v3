(function()
{

	//
	Object.defineProperty(navigator, 'offLine', { get: function()
	{
		return !navigator.onLine;
	}});

	//Object.defineProperty(navigator, 'hasLine', { //

	//

})();

