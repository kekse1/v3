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
				_tag = null;
			}
		}
		else
		{
			_tag = null;
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
		const result = [];
		const data = [''];
		var open = 0;
		var c;

		//
		for(var i = 0, j = 0; i < _data.length; ++i)
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

				data[Math.min(open, _depth)] += c;
			}
			else if(open > 0)
			{
				c = open;
				
				if(_data[i] === '<')
				{
					data[Math.min(open++, _depth)] += '<';
					
					if(data.length <= open)
					{
						data[open] = '';
					}
				}
				else if(_data[i] === '>')
				{
					data[Math.min(open--, _depth)] += '>';
				}
				else
				{
					data[Math.min(open, _depth)] += _data[i];
				}

				if(open < c)
				{
					result[j++] = data.splice(i--, 1)[0];
				}
			}
			else if(_data[i] === '<')
			{
				c = -1;
				
				if(_tag) for(var k = 0; k < _tag.length; ++k)
				{
					if(_data.at(i + 1, _tag[k], false))
					{
						c = _tag[k].length;
						break;
					}
				}
				else
				{
					c = 0;
				}
				
				if(c > -1)
				{
					if(data.length <= (open = 1))
					{
						data[open] = '<';
					}
					else
					{
						data[open] += '<';
					}
				}
				else
				{
					open = 0;
				}
			}
			else
			{
				data[0] += _data[i];
			}
		}
alert('(open: ' + open + ')\n\n\n' + Object.debug(result));
		//
		if(open > 0)
		{
			if(_throw && document.getVariable('data-error', true))
			{
				throw new Error('Invalid _data (malformed HTML: opening bracket \'<\' has not been closed)');
			}
			
			return null;
		}
		else for(var i = data.length - 1; i >= 0; --i)
		{
			result.unshift(data[i]);
		}

		/*
		for(var i = 1; i < result.length; ++i)
		{
			//
		}*/

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
	
