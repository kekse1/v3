//
const DEFAULT_ANCHOR = true;
const DEFAULT_BRACKETS = true;
const DEFAULT_ARROWS = false;

//
if(!customElements.get('a-link')) customElements.define('a-link', Uniform = HTMLUniformElement = class HTMLUniformElement extends HTMLElement
{
	constructor(_url, _options)
	{
		//
		super(... _args);

		//
		if(is(_url, 'URL'))
		{
			this.href = _url.href;
		}
		else if(typeof _url === 'string')
		{
			this.href = _url;
		}

		//
		var anchor, brackets, arrows;

		if(isObject(_options))
		{
			if(typeof _options.anchor === 'boolean')
			{
				anchor = _options.anchor;
			}
			else
			{
				anchor = DEFAULT_ANCHOR;
			}

			if(typeof _options.brackets === 'boolean')
			{
				brackets = _options.brackets;
			}
			else
			{
				brackets = DEFAULT_BRACKETS;
			}

			if(typeof _options.arrows === 'boolean')
			{
				arrows = _options.arrows;
			}
			else
			{
				arrows = DEFAULT_ARROWS;
			}
		}
		else
		{
			anchor = DEFAULT_ANCHOR;
			brackets = DEFAULT_BRACKETS;
			arrows = DEFAULT_ARROWS;
		}

		this.anchor = anchor;
		this.brackets = brackets;
		this.arrows = arrows;

		//
	}

	static render(_href, _resolve, ... _args)
	{
		return new Uniform(URL.create(_href, _resolve)).render(... _args);
	}

	static create(_href, _resolve)
	{
		return new Uniform(URL.create(_href, _resolve));
	}

	static resolve(_href)
	{
		return new Uniform(URL.resolve(_href));
	}

	get href()
	{
		if(this.hasAttribute('href'))
		{
			return this.getAttribute('href');
		}

		return '';
	}

	set href(_value)
	{
		if(typeof _value === 'string')
		{
			this.setAttribute('href', _value);
		}

		return this.href;
	}

	get target()
	{
		if(this.hasAttribute('target'))
		{
			return this.getAttribute('target');
		}

		return null;
	}

	set target(_value)
	{
		if(isString(_value, false))
		{
			this.setAttribute('target', _value);
		}
		else
		{
			this.removeAttribute('target');
		}

		return this.target;
	}

	toString(... _args)
	{
		return this.render(... _args);
	}

	render(_anchor, _brackets, _arrows)
	{
		//
		if(typeof _anchor !== 'boolean')
		{
			_anchor = this.anchor;
		}

		if(typeof _brackets !== 'boolean')
		{
			_brackets = this.brackets;
		}

		if(typeof _arrows !== 'boolean')
		{
			_arrows = this.arrows;
		}

		//
throw new Error('TODO');
	}

	get anchor()
	{
		return this.hasAttribute('anchor');
	}

	set anchor(_value)
	{
		if(typeof _value === 'boolean')
		{
			if(_value)
			{
				this.setAttribute('anchor', '');
			}
			else
			{
				this.removeAttribute('anchor');
			}
		}

		return this.anchor;
	}

	get brackets()
	{
		return this.hasAttribute('brackets');
	}

	set brackets(_value)
	{
		if(typeof _value === 'boolean')
		{
			if(_value)
			{
				this.setAttribute('brackets', '');
			}
			else
			{
				this.removeAttribute('brackets');
			}
		}

		return this.brackets;
	}

	get arrows()
	{
		return this.hasAttribute('arrows');
	}

	set arrows(_value)
	{
		if(typeof _value === 'boolean')
		{
			if(_value)
			{
				this.setAttribute('arrows');
			}
			else
			{
				this.removeAttribute('arrows');
			}
		}

		return this.arrows;
	}
});

