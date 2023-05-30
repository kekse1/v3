(function()
{

	//
	const DEFAULT_CAMEL = '-';
	const DEFAULT_FIX = true;

	/** Camel case functions
	 * @module camel
	 */

	/** Tries to detect whether a string is camel-cased or not.
	 * Doesn't work in all cases, as some values can be both types.
	 * @param {string} _string
	 * @param {string} [DEFAULT_CAMEL] _camel - The separator, usually '-'
	 * @returns {boolean}
	 */
	camel = (_string, _camel = DEFAULT_CAMEL) => {
		if(typeof _camel === 'string')
		{
			if(_camel.length !== 1)
			{
				throw new Error('Invalid _camel argument');
			}
		}
		else
		{
			_camel = DEFAULT_CAMEL;
		}

		if(typeof _string !== 'string')
		{
			return undefined;
		}
		else if(_string.length === 0)
		{
			return null;
		}
		else if(_string.includes(_camel + _camel))
		{
			return null;
		}
		else if(_string.includes(_camel))
		{
			return false;
		}
		else if(! _string.isLowerCase)
		{
			return true;
		}

		return null;
	};

	/** Converts a string to it's camel-case enabled form.
	 * @param {string} _string - Input string to convert (can also be already camel-cased)
	 * @param {string} [DEFAULT_CAMEL] _camel - The separator, usually '-'
	 * @param {boolean} [DEFAULT_FIX] _fix - if(true), the rest of every component will be converted to lower-case
	 * @returns {string}
	 */
	camel.enable = (_string, _camel = DEFAULT_CAMEL, _fix = DEFAULT_FIX) => {
		const c = camel(_string, _camel);

		if(c !== false)
		{
			return _string;
		}

		const split = _string.split(_camel);
		var result = split.shift();

		for(const s of split)
		{
			if(s.length > 0)
			{
				if(_fix)
				{
					result += s[0].toUpperCase() + s.substr(1).toLowerCase();
				}
				else
				{
					result += s[0].toUpperCase() + s.substr(1);
				}
			}
		}

		return result;
	};

	/** Disables camel-case form in a string.
	 * @param {string} _string - The input string (w/ or w/o enabled camel-case)
	 * @param {string} [DEFAULT_CAMEL] _camel - Separator, usually '-'
	 * @param {boolean} [DEFAULT_FIX] _fix - handles the case of the rest of every separate component
	 * @returns {string}
	 */
	camel.disable = (_string, _camel = DEFAULT_CAMEL, _fix = DEFAULT_FIX) => {
		const c = camel(_string, _camel);

		if(c === false)
		{
			return _string;
		}

		var result = '';
		var lastWasUpper = null;

		for(var i = 0; i < _string.length; ++i)
		{
			if(_string[i].isUpperCase)
			{
				if(lastWasUpper)
				{
					result += _string[i];
				}
				else if(_fix && i < (_string.length - 1))
				{
					if(_string[i + 1].isUpperCase)
					{
						result += _camel + _string[i];
					}
					else
					{
						result += _camel + _string[i].toLowerCase();
					}
				}
				else
				{
					result += _camel + _string[i].toLowerCase();
				}

				lastWasUpper = true;
			}
			else
			{
				lastWasUpper = false;
				result += _string[i];
			}
		}

		return result;
	};

	//

})();

