(function()
{

	//
	const DEFAULT_THROW = true;

	//
	isNumeric = (_item, _radix = null) => {
		if(typeof _item === 'bigint')
		{
			return true;
		}
		else if(isNumber(_item))
		{
			return true;
		}
		else if(typeof _item === 'string')
		{
			if(typeof _radix === 'boolean')
			{
				_radix = (_radix ? 10 : null);
			}

			if(_radix !== null)
			{
				if(_item.isNumber(_radix))
				{
					return true;
				}
				else if(_item.isBigInt(_radix))
				{
					return true;
				}
			}
		}

		return false;
	}

	isByte = (_item, _radix = null) => {
		if(isInt(_item))
		{
			return (_item >= 0 && _item <= 255);
		}
		else if(typeof _item === 'string')
		{
			if(typeof _radix === 'boolean')
			{
				_radix = (_radix ? 10 : null);
			}

			if(_radix === 10)
			{
				if(! isNaN(_item = Number(_item)))
				{
					return ((_item % 1) === 0
						&& _item >= 0
						&& _item < 256);
				}
			}
			else if(_radix === 256 || _radix === -257)
			{
				return (_item.length <= 1);
			}
			else if(_radix !== null)
			{
	throw new Error('TODO');
				return _item.isByte(_radix);
			}
		}

		return false;
	};

	isNumber = (_item, _radix = null) => {
		if(typeof _item === 'number')
		{
			if(isNaN(_item))
			{
				return false;
			}
			else if(! Number.isFinite(_item))
			{
				return false;
			}
			else if(_item.valueOf() !== _item.valueOf())
			{
				return false;
			}
		}
		else if(typeof _item === 'string')
		{
			if(typeof _radix === 'boolean')
			{
				_radix = (_radix ? 10 : null);
			}

			if(_radix !== null)
			{
				return _item.isNumber(_radix);
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}

		return true;
	};

	Object.defineProperty(Number, 'isNumber', { value: isNumber.bind(Number) });
	Object.defineProperty(Number.prototype, 'isNumber', { get: function()
	{
		return isNumber(this.valueOf());
	}});

	isInt = (_item, _radix = null) => {
		if(typeof _item === 'string')
		{
			if(typeof _radix === 'boolean')
			{
				_radix = (_radix ? 10 : null);
			}

			if(_radix !== null)
			{
				return _item.isInt(_radix);
			}
		}
		else if(isNumber(_item, null))
		{
			return ((_item % 1) === 0);
		}

		return false;
	};

	Object.defineProperty(Number, 'isInt', { value: isInt.bind(Number) });
	Object.defineProperty(Number.prototype, 'isInt', { get: function()
	{
		return isInt(this.valueOf());
	}});

	isFloat = (_item, _radix = null) => {
		if(typeof _item === 'string')
		{
			if(typeof _radix === 'boolean')
			{
				_radix = (_radix ? 10 : null);
			}

			if(_radix !== null)
			{
				return _item.isFloat(_radix);
			}
		}
		else if(isNumber(_item, null))
		{
			return ((_item % 1) !== 0);
		}

		return false;
	};

	Object.defineProperty(Number, 'isFloat', { value: isFloat.bind(Number) });
	Object.defineProperty(Number.prototype, 'isFloat', { get: function()
	{
		return isFloat(this.valueOf());
	}});

	//
	Object.defineProperty(String.prototype, 'isNumber', { value: function(_radix = 10, _throw = DEFAULT_THROW, _int = false)
	{
		//
		if(typeof _int !== 'boolean')
		{
			_int = false;
		}

		if(! isRadix(_radix, true))
		{
			if(_throw)
			{
				throw new Error('Invalid _radix argument');
			}

			_radix = 10;
		}
		else if(_radix === 256 || _radix === -257)
		{
			return true;
		}

		//
		var points;

		if((points = this.indicesOf('.').length) > 0)
		{
			if(points > 1)
			{
				return false;
			}
			else if(_int)
			{
				return false;
			}
		}
		else if(this.indicesOf('+').length > 1)
		{
			return false;
		}
		else if(this.indicesOf('-').length > 1)
		{
			return false;
		}
		else if(_radix === 10 && this.indicesOf('e').length > 1)
		{
			return false;
		}
		else
		{
			const minus = this.indicesOf('-').length;
			const plus = this.indicesOf('+').length;

			if(minus > 0 && plus > 0)
			{
				if(_radix === 10 && this.indicesOf('e').length === 1)
				{
					return (minus === 1 && plus === 1);
				}

				return false;
			}
		}

		//
		const alphabetString = (typeof _radix === 'string' ? _radix : (radix.getSpecialSigns({ radix: _radix }) + radix.getAlphabet(_radix, false, _throw)));

		if(alphabetString === null)
		{
			if(_throw)
			{
				throw new Error('Couldn\'t create alphabet string');
			}

			return null;
		}

		var text;

		if(alphabetString.isLowerCase)
		{
			text = this.toLowerCase();
		}
		else if(alphabetString.isUpperCase)
		{
			text = this.toUpperCase();
		}
		else
		{
			text = this.valueOf();
		}

		const alphabetSet = new Set();

		for(var i = 0; i < alphabetString.length; ++i)
		{
			alphabetSet.add(alphabetString[i]);
		}

		for(var i = 0; i < text.length; ++i)
		{
			if(! radix.checkAlphabetSet(text[i], alphabetSet, _throw))
			{
				return false;
			}
		}

		return true;
	}});

	Object.defineProperty(String.prototype, 'isInt', { value: function(_radix = 10, _throw = DEFAULT_THROW)
	{
		return this.isNumber(_radix, _throw, true);
	}});

	Object.defineProperty(String.prototype, 'isFloat', { value: function(_radix = 10, _throw = DEFAULT_THROW)
	{
		if(this.isNumber(_radix, _throw, false))
		{
			return (this.indicesOf('.').length === 1 && this[this.length - 1] !== '.');
		}

		return false;
	}});

	Object.defineProperty(String.prototype, 'isBigInt', { value: function(_radix = 10, _throw = DEFAULT_THROW)
	{
		if(this.slice(0, -1).isNumber((radix.getSpecialSigns({ radix: _radix, big: true }) + radix.getAlphabet(_radix, false, _throw)), _throw, true))
		{
			return (this[this.length - 1] === 'n');
		}

		return false;
	}});

	//
	Object.defineProperty(Number, 'from', { value: function(_item, _throw = DEFAULT_THROW)
	{
		try
		{
			return Number(_item);
		}
		catch(_error)
		{
			if(_throw)
			{
				throw _error;
			}
		}

		return 0;
	}});

	Object.defineProperty(BigInt, 'from', { value: function(_item, _throw = DEFAULT_THROW)
	{
		if(typeof _item === 'string')
		{
			if(_item[_item.length - 1] === 'n')
			{
				_item = _item.slice(0, -1);
			}

			const idx = _item.indexOf('.');

			if(idx > -1)
			{
				_item = _item.substr(0, idx);
			}
		}

		try
		{
			return BigInt(_item);
		}
		catch(_error)
		{
			if(_throw)
			{
				throw _error;
			}
		}

		return 0n;
	}});

	//

})();

