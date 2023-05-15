(function()
{

	//
	const DEFAULT_THROW = true;
	const DEFAULT_PARSE = false;

	//
	css = { camel, matrix: CSSMatrix, matrix3d: CSSMatrix };

	//
	css.url = (_string, _throw = DEFAULT_THROW) => {
		if(! isString(_string, false))
		{
			if(_throw)
			{
				throw new Error('Invalid _string argument');
			}

			return null;//_string;
		}
		else if(! _string.toLowerCase().includes('url'))
		{
			if(_throw)
			{
				throw new Error('Contains no url()');
			}

			return null;//_string;
		}
return 'favicon.png';
throw new Error('TODO');
	};

	//
	css.parse = (_string, _parse = DEFAULT_PARSE) => {
		if(typeof _string !== 'string')
		{
			return _string;
		}
		else if((_string = _string.trim()).length === 0 || _string.isEmpty)
		{
			return '';
		}
throw new Error('TODO');//also str..unit(.., ['deg']), etc.. soon!
	};

	css.render = (_value) => {
		if(typeof _value === 'string')
		{
			if((_value = _value.trim()).hasEmpty)
			{
				_value = _value.quote('\'');
			}

			return _value;
		}

		var result;

		if(typeof _value === 'undefined')
		{
			result = '';
		}
		else if(_value === null)
		{
			result = 'auto';
		}
		else if(typeof _value === 'boolean')
		{
			result = (_value ? 'auto' : 'none');
		}

throw new Error('TODO');
	};
	
})();

