if(!customElements.get('a-code')) customElements.define('a-code', HTMLCodeElement = class HTMLCodeElement extends HTMLElement
{
	constructor(... _args)
	{
		super(... _args);
	}

	connectedCallback(... _args)
	{
		setTimeout(() => {
			this.innerHTML = this.createInnerHTML();
		}, 0);
	}
	
	attributeChangedCallback(_name, _value_old, _value_new)
	{
		switch(_name)
		{
			case 'href':
				this.href = _value_new;
				break;
			case 'target':
				this.target = _value_new;
				break;
		}
	}

	static get observedAttributes()
	{
		return [ 'href', 'target' ];
	}

	createInnerHTML(_string = this.innerHTML, _href = this.href, _target = this.target)
	{
		if(typeof _string !== 'string' || _string.length === 0)
		{
			return '';
		}

		return HTMLCodeElement.createInnerHTML(_string, _href, _target);
	}

	static createInnerHTML(_string, _href, _target)
	{
		if(typeof _string !== 'string')
		{
			throw new Error('Invalid _string argument (not a String)');
		}
		else if(_string.length === 0)
		{
			return '';
		}
		else if(typeof _href === 'string' && _href.length > 0)
		{
			if(typeof _target !== 'string' || _target.length === 0)
			{
				_target = '_blank';
			}
			else if(_href[0] === '#')
			{
				_target = null;
			}
		}
		else
		{
			_href = null;
		}

		if(_string.toLowerCase().startsWith('<a '))
		{
			const err = 'Invalid HTML string in \'HTMLCodeElement\'';
			var idx = _string.indexOf('>');

			if(idx === -1)
			{
				throw new Error(err + ' (anchor tag not complete)');
			}

			_string = _string.substr(idx + 1);
			idx = _string.lastIndexOf('</a>');

			if(idx === -1)
			{
				throw new Error(err + ' (no closing tag for anchor found)');
			}

			_string = _string.substr(0, idx);
		}

		if(_string.length === 0)
		{
			return '';
		}

		var result = '';

		if(_href)
		{
			result += `<a style="text-shadow: 2px 2px 6px white;" href="${_href}"`;

			if(_target)
			{
				result += ` target="${_target}"`;
			}

			result += '>';
		}
		else
		{
			result += `<span>`;
		}

		result += _string;

		if(_href)
		{
			result += '</a>';
		}
		else
		{
			result += '</span>';
		}

		return result;
	}

	get innerHTML()
	{
		return super.innerHTML;
	}

	set innerHTML(_value)
	{
		return super.innerHTML = this.createInnerHTML();
	}

	get href()
	{
		if(typeof this._href === 'string' && this._href.length > 0)
		{
			return this._href;
		}

		return null;
	}

	set href(_value)
	{
		const orig = this.href;

		if(typeof _value === 'string' && _value.length > 0)
		{
			this._href = _value;
		}
		else
		{
			delete this._href;
		}

		const result = this.href;

		if(orig !== result)
		{
			super.innerHTML = this.createInnerHTML();
		}

		return result;
	}

	get target()
	{
		if(typeof this._target === 'string' && this._target.length > 0)
		{
			return this._target;
		}

		return '_blank';
	}

	set target(_value)
	{
		const orig = this.target;

		if(typeof _value === 'string' && _value.length > 0)
		{
			this._target = _value;
		}
		else
		{
			delete this._target;
		}

		const result = this.target;

		if(orig !== result)
		{
			super.innerHTML = this.createInnerHTML();
		}

		return result;
	}
});
