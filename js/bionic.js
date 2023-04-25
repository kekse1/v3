(function()
{

	//
	bionic = (... _args) => {
		return bionic.render(... _args);
	};

	//
	bionic.getOption = (_type) => {
		const args = location.args;
		var result;

		switch(_type)
		{
			case 'enabled':
				if('bionic' in args)
				{
					result = !!args.bionic;
				}
				else
				{
					result = document.getVariable('bionic', true);
				}
				break;
			case 'fixation':
				if(isNumber(args.bionicFixation))
				{
					result = args.bionicFixation;
				}
				else
				{
					result = document.getVariable('bionic-fixation', true);
				}

				if(result <= 0)
				{
					result = 0;
				}
				else if(result > 1)
				{
					result = 1;
				}

				break;
			case 'sakkadeWords':
				if(isNumber(args.bionicSakkadeWords))
				{
					result = args.bionicSakkadeWords;
				}
				else
				{
					result = document.getVariable('bionic-sakkade-words', true);
				}

				if(result <= 1)
				{
					result = 1;
				}
				else if(result > document.getVariable('bionic-sakkade-words-max', true))
				{
					result = document.getVariable('bionic-sakkade-words-max', true);
				}

				break;
			case 'sakkadeChars':
				if(isNumber(args.bionicSakkadeChars))
				{
					result = args.bionicSakkadeChars;
				}
				else
				{
					result = document.getVariable('bionic-sakkade-chars', true);
				}

				if(result <= 1)
				{
					result = 1;
				}
				else if(result > document.getVariable('bionic-sakkade-chars-max', true))
				{
					result = document.getVariable('bionic-sakkade-chars-max', true);
				}

				break;
			default:
				throw new Error('Invalid option _type at bionic');
		}

		return result;
	};

	bionic.getOptions = () => {
		const result = {
			enabled: bionic.getOption('enabled'),
			fixation: bionic.getOption('fixation'),
			sakkade: {
				words: bionic.getOption('sakkadeWords'),
				chars: bionic.getOption('sakkadeChars')
			}
		};

		if(typeof result.enabled !== 'boolean')
		{
			result.enabled = document.getVariable('bionic', true);
		}

		return result;
	};

	//
	const bold = (_string, _fixation) => {
		if(_fixation === 0)
		{
			return _string;
		}

		const fix = Math.round(_string.length * _fixation);
		return ('<b>' + _string.substr(0, fix) + '</b>' + _string.substr(fix));
	};

	bionic.render = (_text, _options = bionic.getOptions()) => {
		//
		if(typeof _text !== 'string')
		{
			throw new Error('Invalid _text argument (not a String)');
		}
		else if(_text.length === 0)
		{
			return _text;
		}
		else if(! isObject(_options))
		{
			_options = bionic.getOptions();
		}

		if(! _options.enabled)
		{
			return _text;
		}

		//
		var result = '';
		var word = '';
		var words = _options.sakkade.words;
		var chars = 0;
		var open = '';

		for(var i = 0; i < _text.length; ++i)
		{
			if(open.length > 0)
			{
				if(_text[i] === open)
				{
					open = '';
				}

				if(word.length > 0)
				{
					if(chars >= _options.sakkade.chars)
					{
						result += bold(word, _options.fixation);
					}
					else
					{
						result += word;
					}

					word = '';
					words = chars = 0;
				}

				result += _text[i];
			}
			else if(_text.at(i, '&nbsp;', false) || _text.at(i, ' ') || _text.at(i, '\t') || _text.at(i, '-') || _text.at(i, '_') || _text.at(i, '.') || _text.at(i, ',') || _text.at(i, ';') || _text.at(i, '<') || _text.at(i, '&'))
			{
				if(word.length > 0)
				{
					if(++words >= _options.sakkade.words || i <= word.length)
					{
						result += bold(word, _options.fixation);
						words = 0;
					}
					else if(chars >= _options.sakkade.chars)
					{
						result += bold(word, _options.fixation);
						words = 0;
					}
					else
					{
						result += word;
					}

					word = '';
					chars = 0;
				}

				if(_text.at(i, '&nbsp;', false))
				{
					result += '&nbsp;';
					i += 5;
				}
				else
				{
					if(_text[i] === '<')
					{
						open = '>';
					}
					else if(_text[i] === '&')
					{
						open = ';';
					}

					result += _text[i];
				}
			}
			else
			{
				word += _text[i];
				++chars;
			}
		}

		if(word.length > 0)
		{
			if(++words >= _options.sakkade.words)
			{
				result += bold(word, _options.fixation);
			}
			else if(chars >= _options.sakkade.chars)
			{
				result += bold(word, _options.fixation);
			}
			else
			{
				result += word;
			}
		}

		return result;
	};

	//
	
})();

