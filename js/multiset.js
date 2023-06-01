(function()
{

	//
	const DEFAULT_NEGATIVE = false;
	const DEFAULT_FLOATING = false;
	const DEFAULT_INC = 1;
	const DEFAULT_DEC = 1;

	//
	MultiSet = class MultiSet extends Map
	{
		constructor(... _args)
		{
			super(... _args);
		}

		get negative()
		{
			if(typeof this.NEGATIVE === 'boolean')
			{
				return this.NEGATIVE;
			}

			return DEFAULT_NEGATIVE;
		}

		set negative(_value)
		{
			if(typeof _value === 'boolean')
			{
				return this.NEGATIVE = _value;
			}
			else
			{
				delete this.NEGATIVE;
			}

			return this.negative;
		}

		get floating()
		{
			if(typeof this.FLOATING === 'boolean')
			{
				return this.FLOATING;
			}

			return DEFAULT_FLOATING;
		}

		set floating(_value)
		{
			if(typeof _value === 'boolean')
			{
				return this.FLOATING = _value;
			}
			else
			{
				delete this.FLOATING;
			}

			return this.floating;
		}

		set(_key, _value)
		{
			if(typeof _value === 'undefined')
			{
				if(super.has(_key))
				{
					_value = (this.get(_key) + 1);
				}
				else
				{
					_value = 1;
				}
			}
			else if(! isNumber(_value))
			{
				throw new Error('Invalid _value argument (not a Number)');
			}

			if(_value <= 0 && !this.negative)
			{
				_value = 0;
			}
			else if(isFloat(_value) && !this.floating)
			{
				_value = Math.int(_value);
			}

			super.set(_key, _value);
			return _value;
		}

		get add()
		{
			return this.increase;
		}

		get sub()
		{
			return this.decrease;
		}

		inc(_key, _by = DEFAULT_INC)
		{
			return this.increase(_key, _by);
		}

		increase(_key, _by = DEFAULT_INC)
		{
			if(! isNumber(_by))
			{
				_by = DEFAULT_INC;
			}

			var result;

			if(super.has(_key))
			{
				result = (super.get(_key) + _by);
			}
			else
			{
				result = _by;
			}

			if(result <= 0 && !this.negative)
			{
				result = 0;
			}
			else if(isFloat(result) && !this.floating)
			{
				result = Math.int(result);
			}

			super.set(_key, result);
			return result;
		}

		dec(_key, _by = DEFAULT_DEC)
		{
			return this.decrease(_key, _by);
		}

		decrease(_key, _by = DEFAULT_DEC)
		{
			if(! isNumber(_by))
			{
				_by = DEFAULT_DEC;
			}

			var result;

			if(super.has(_key))
			{
				result = (super.get(_key) - _by);
			}
			else
			{
				result = -_by;
			}

			if(result <= 0 && !this.negative)
			{
				result = 0;
			}
			else if(isFloat(result) && !this.floating)
			{
				result = Math.int(result);
			}

			super.set(_key, result);
			return result;
		}

		has(_key)
		{
			if(! super.has(_key))
			{
				return 0;
			}

			return super.get(_key);
		}

		get(_key)
		{
			return this.has(_key);
		}
	}

})();
