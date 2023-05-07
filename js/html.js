(function()
{

	//
	const DEFAULT_THROW = true;
	const DEFAULT_PARSE = true;

	//
	html = { entities: String.entities };
	
	//
	html.parse = (_data, _throw = DEFAULT_THROW) => {
		//
		if(typeof _data !== 'string')
		{
			if(_throw)
			{
				throw new Error('Invalid _data argument (not a String)');
			}
			
			return null;
		}
		else if(_data.length === 0)
		{
			return {};
		}
		else
		{
			_data = _data.slice(1, -1);
		}
		
		//
		var tag = '';
		
		for(var i = 0; i < _data.length; ++i)
		{
			if(_data[i].isEmpty || _data[i] === '>')
			{
				break;
			}
			else
			{
				tag += _data[i];
			}
		}

		if(_data.endsWith('</' + tag, false))
		{
			_data = _data.slice(0, -(2 + tag.length));
		}

		//
		const sub = { key: '', value: '' };
		const result = {};
		var type = 'key';
		var quote = '';
		var pause = 0;
		var c;
		
		//
		if(_data.at(0, '<' + tag + '>', false))
		{
			pause = 1;
			_data = _data.substr(2 + tag.length);
		}
		
		//
		result['*'] = tag;
		result[tag] = '';
		
		//
		for(var i = 0; i < _data.length; ++i)
		{
			//
			if(pause > 0)
			{
				if(_data[i] === '<')
				{
					++pause;
				}
				else if(_data[i] === '>')
				{
					if(--pause <= 0)
					{
						pause = 0;
					}
				}
				
				result[tag] += _data[i];
			}
			else if(_data[i] === '\\')
			{
				if(i < (_data.length - 1))
				{
					sub[type] += _data[++i];
				}
				else
				{
					sub[type] += _data[i];
				}
			}
			else if(quote.length > 0)
			{
				if(_data[i] === quote)
				{
					quote = '';
				}
				else
				{
					sub[type] += _data[i];
				}
			}
			else if(_data[i] === '\'' || _data[i] === '"' || _data[i] === '`')
			{
				quote = _data[i];
			}
			else if(_data[i] === '>')
			{
				++pause;
				type = 'value';

				if((sub.key = sub.key.trim()).length > 0)
				{
					result[sub.key] = sub.value;
					sub.key = sub.value = '';
				}
			}
			else if(_data[i] === '<')
			{
				++pause;
				result[tag] += '<';
				type = 'key';
			}
			else if(_data[i] === '=')
			{
				type = 'value';
			}
			else if(_data[i].isEmpty)
			{
				type = 'key';
				result[sub.key.trim()] = sub.value.trim();
				sub.key = sub.value = '';
			}
			else
			{
				sub[type] += _data[i];
			}
		}

		//
		return result;
	};

	//
	html.extract = (_data, _tag, _parse = DEFAULT_PARSE, _depth = 1, _throw = DEFAULT_THROW, _current_depth = 0) => {
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
		if(_depth !== null && !(isInt(_depth) && _depth >= 0))
		{
			_depth = 1;
		}
		else if(_depth === 0)
		{
			return [ _data ];
		}

		//
		var result = [];
		const data = [''];
		var open = 0;
		var c, tag;
		var quote = '';

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

				data[open] += c;
			}
			else if(quote.length > 0)
			{
				data[open] += _data[i];
				
				if(_data[i] === quote)
				{
					quote = '';
				}
			}
			else if(_data[i] === '\'' || _data[i] === '"' || _data[i] === '`')
			{
				quote = _data[i];
				data[open] += _data[i];
			}
			else if(open > 0)
			{
				if(_data.at(i, '</' + tag + '>', false))
				{
					data[open] += _data.substr(i, 3 + tag.length);
					result[j++] = data.splice(open--, 1)[0];
					i += 2 + tag.length;
				}
				else if(_data.at(i, '/>'))
				{
					data[open] += '/>';
					result[j++] = data.splice(open--, 1)[0];
					++i;
				}
				else
				{
					data[open] += _data[i];
				}
			}
			else if(_data[i] === '<')
			{
				tag = '';
				
				if(_tag) for(var k = 0; k < _tag.length; ++k)
				{
					if(_data.at(i + 1, _tag[k], false))
					{
						tag = _tag[k];
						break;
					}
				}
				else
				{
					for(var k = i + 1; k < _data.length; ++k)
					{
						if(_data[k].isEmpty)
						{
							break;
						}
						else if(_data[k] === '>')
						{
							break;
						}
						else
						{
							tag += _data[k];
						}
					}
				}

				if(tag.length === 0)
				{
					data[open = 0] += '<';
				}
				else
				{
					if(data.length <= (open = 1))
					{
						data[open] = '';
					}
					
					data[open] += '<' + tag;
					i += tag.length;
				}
			}
			else
			{
				data[open = 0] += _data[i];
			}
		}

		//
		if(open > 0)
		{
			if(_throw && document.getVariable('data-error', true))
			{
				throw new Error('Invalid _data (malformed HTML: opening bracket \'<\' has not been closed)');
			}
			
			result = [ _data ];
		}
		else while(data.length > 0)
		{
			result.unshift(data.pop());
		}

		if(_parse) for(var i = 1; i < result.length; ++i)
		{
			result[i] = html.parse(result[i], _throw);
		}
		
		return result;
	};

	//
	html.extract.script = (_data, _parse = DEFAULT_PARSE, _depth = 1, _throw = DEFAULT_THROW) => {
		return html.extract(_data, 'script', _parse, _depth, _throw);
	};
	
	html.extract.style = (_data, _parse = DEFAULT_PARSE, _depth = 1, _throw = DEFAULT_THROW) => {
		return html.extract(_data, ['style','link'], _parse, _depth, _throw);
	};
	
	//

})();
	
