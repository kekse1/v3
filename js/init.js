(function()
{

	//
	if(typeof config.init !== 'object' || config.init === null)
	{
		return;
	}

	//
	if(typeof config.init.query !== 'object' || config.init.query === null)
	{
		//
		(function()
		{
			//
			var www;
			var dot;
			var tls;

			if(typeof config.init.query.www === 'boolean')
			{
				www = config.init.query.www;
			}
			else
			{
				www = null;
			}

			if(typeof config.init.query.dot === 'boolean')
			{
				dot = config.init.query.dot;
			}
			else
			{
				dot = null;
			}

			if(typeof confing.init.query.tls === 'boolean')
			{
				tls = config.init.query.tls;
			}
			else
			{
				tls = null;
			}

			//
			if(www === null && dot === null && tls === null)
			{
				return;
			}
			else
			{
				if(www)
				{
					if(! address.isHostname(location.hostname))//.isIP(location.hostname))
					{
						www = null;
					}
				}

				if(tls !== null)
				{
					if(address.isIP(location.hostname))
					{
						tls = null;
					}
					else if(address.isLocalhost(location.hostname))
					{
						tls = null;
					}
				}
			}

			//
		throw new Error('TODO');
		})();

		//
		(function()
		{
			//
			var remove;

			if(typeof config.init.query.remove === 'string')
			{
				if(config.init.query.remove.length === 0)
				{
					remove = null;
				}
				else
				{
					remove = [ config.init.query.remove ];
				}
			}
			else if(Array.isArray(config.init.query.remove))
			{
				if(config.init.query.remove.length === 0)
				{
					remove = null;
				}
				else
				{
					remove = [];

					for(var i = 0, j = 0; i < config.init.query.remove.length; ++i)
					{
						if(typeof config.init.query.remove[i] === 'string' && config.init.query.remove.length > 0)
						{
							remove[j++] = config.init.query.remove[i];
						}
					}

					if(remove.length === 0)
					{
						remove = null;
					}
				}
			}
			else if(config.init.query.remove === true)
			{
				return location.search = '';
			}
			else
			{
				remove = null;
			}

			//
			if(remove === null)
			{
				return;
			}

			//
		throw new Error('TODO');

		})();

		//
	}

	//

})();

