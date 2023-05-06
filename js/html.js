(function()
{

	//
	const DEFAULT_THROW = true;

	//
	html = { entities: String.entities };

	//
	html.extract = (_data, _tag, _depth, _throw = DEFAULT_THROW) => {
		if(typeof _data !== 'string')
		{
			if(_throw)
			{
				throw new Error('Invalid _data argument (not a String)');
			}

			return null;
		}
		else if(isString(_tag, false))
		{
			_tag = [ _tag.toLowerCase() ];
		}
		else if(isArray(_tag, false))
		{
			for(var i = 0; i < _tag.length; ++i)
			{
				if(isString(_tag[i], false))
				{
					_tag[i] = _tag[i].toLowerCase();
				}
				else if(_throw)
				{
					throw new Error('Invalid _tag[' + i + '] (no non-empty String)');
				}
				else
				{
					_tag.splice(i--, 1);
				}
			}

			if(_tag.length === 0)
			{
				return null;
			}
		}
		else if(_throw)
		{
			throw new Error('Invalid _tag argument');
		}
		else
		{
			return null;
		}

		//
		if(! (isInt(_depth) && _depth >= 0))
		{
			_depth = 1;
		}
		else if(_depth === 0)
		{
			return [ _data ];
		}

		//
		const result = [''];
		var outer = '';
		var open = 0;
		var c;

		//
		for(var i = 0; i < _data.length; ++i)
		{
			if(_data[i] === '\\')
			{
				if(i < (_data.length - 1))
				{
					c = _data[++i];
				}
				else
				{
					c = '\\';
				}

				if(open > 0)
				{
					result[j] += c;
				}
				else
				{
					outer += c;
				}
			}
			else if(open > 0)
			{
	throw new Error('TODO');
				if(_data[i] === '>')
				{
					--open;
					//fixme/zusammen klappen?
				}
				else if(_data[i] === '<' && open < _depth)
				{
					result[++open] = '';
				}
				else
				{
					result[open] += _data[i];
				}
			}
			else if(_data[i] === '<')
			{
				if(result.length <= (open = 1))
				{
					result[open] = '';
				}
			}
			else
			{
				result[0] += _data[i];
			}
		}

		//
		if(_throw && open > 0)
		{
			throw new Error('Invalid _data (malformed HTML: opening bracket \'<\' has not been closed)');
		}
		else for(var i = 1; i < result.length; ++i)
		{
	throw new Error('TODO (strings between \'<\' and \'>\' to data objects!)');
		}

		//
		return result;
	};

	//
	html.extract.script = (_data, _depth = 1, _throw = DEFAULT_THROW) => {
		return html.extract(_data, 'script', _depth, _throw);
	};
	
	html.extract.style = (_data, _depth = 1, _throw = DEFAULT_THROW) => {
		return html.extract(_data, ['style','link'], _depth, _throw);
	};
	
	//

})();
	
