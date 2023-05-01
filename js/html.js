(function()
{

	//
	html = {};

	//
	html.extract = (_data, _tag) => {
		if(isString(_tag, false))
		{
			_tag = [ _tag ];
		}
		else if(! isArray(_tag, false))
		{
			throw new Error('Invalid _tag argument');
		}
		
		if(typeof _data !== 'string')
		{
			throw new Error('Invalid _data argument');
		}
		else if(_data.length === 0)
		{
			return [ '', '', {} ];
		}
		
		//
		//const result = ...
		//
		
		//
		//return result;
	};

	//
	html.extract.script = (_data) => {
		return html.extract(_data, 'script');
	};

	html.extract.style = (_data) => {
		return html.extract(_data, ['style', 'link']);
	};

})();
