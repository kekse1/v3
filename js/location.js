(function()
{

	//
	Object.defineProperty(location, 'argv', {
		get: function()
		{
			const result = [];

			if(location.search.length === 0 || location.search === '?')
			{
				return result;
			}

			var search = location.search;

			while(search[0] === '?')
			{
				search = search.substr(1);
			}

			search = search.split('&');
			var idx;

			for(var i = 0, j = 0; i < search.length; ++i)
			{
				if(search[i].length > 0)
				{
					idx = search[i].indexOf('=');

					if(idx === -1)
					{
						if(! isNaN(result[j] = decodeURIComponent(search[i])))
						{
							result[j] = Number(result[j]);
						}
					}
					else
					{
						result[j] = [ decodeURIComponent(search[i].substr(0, idx)), decodeURIComponent(search[i].substr(idx + 1)) ];

						if(! isNaN(result[j][0]))
						{
							result[j][0] = Number(result[j][0]);
						}

						if(! isNaN(result[j][1]))
						{
							result[j][1] = Number(result[j][1]);
						}
					}

					++j;
				}
			}

			return result;
		},
		set: function(_value)
		{
throw new Error('TODO');//bedenke '=', darf nicht escaped werden bspw., wo alles andere schon...
		}
	});

	Object.defineProperty(location, 'args', {
		get: function()
		{
			const result = [];
			const argv = location.argv;
			var idx;

			for(var i = 0, j = 0; i < argv.length; ++i)
			{
				if(isNumber(argv[i]))
				{
					result[j++] = argv[i];
				}
				else if(isString(argv[i], 1))
				{
					result[j++] = argv[i];
				}
				else if(isArray(argv[i], 1))
				{
					result[argv[i][0]] = argv[i][1];
				}
			}

			return result;
		},
		set: function(_value)
		{
throw new Error('TODO');
		}
	});

	//
	Object.defineProperty(location, 'protocols', { get: function()
	{
		return [ 'http:', 'https:', 'file:', 'blob:', 'data:', 'ftp:' ];
	}});

	Object.defineProperty(location, 'isURL', { value: function(... _args)
	{
		if(_args.length === 0)
		{
			return null;
		}

		const protocols = location.protocols;
		var found;

		for(var i = 0; i < _args.length; ++i)
		{
			if(typeof _args[i] === 'string')
			{
				if(_args[i].length === 0)
				{
					return false;
				}

				found = false;

				for(var j = 0; j < protocols.length; ++j)
				{
					if(_args[i].toLowerCase().startsWith(protocols[j] + '//'))
					{
						found = true;
						break;
					}
				}

				if(found)
				{
					if(_args[i].includes(String.fromCharCode(0)))
					{
						return false;
					}
				}
				else if(! found)
				{
					return false;
				}
			}
			else
			{
				throw new Error('Invalid ..._args[' + i + '] (no String)');
			}
		}

		return true;
	}});

	//
	
})();

