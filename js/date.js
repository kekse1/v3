(function()
{

	//
	const DEFAULT_FORMAT_HTML = true;
	const DEFAULT_FORMAT_PARENTHESIS = true;

	//
	(function()
	{
		//
		Object.defineProperty(Date, 'format', { value: function(_date = new Date(), _html = DEFAULT_FORMAT_HTML, _parenthesis = DEFAULT_FORMAT_PARENTHESIS)
		{
			if(typeof _html !== 'boolean')
			{
				_html = DEFAULT_FORMAT_HTML;
			}

			if(typeof _parenthesis !== 'boolean')
			{
				_parenthesis = DEFAULT_FORMAT_PARENTHESIS;
			}

			if(! is(_date, 'Date'))
			{
				_date = new Date();
			}

			var result = (_html ? '<span class="date">' : '');

			result += _date.getFullYear() + '-';
			result += (_date.getMonth() + 1).toString().padStart(2, '0') + '-';
			result += _date.getDate().toString().padStart(2, '0') + ' ';

			if(_parenthesis)
			{
				result += '(';
			}

			result += _date.getHours().toString().padStart(2, '0') + ':';
			result += _date.getMinutes().toString().padStart(2, '0') + ':';
			result += _date.getSeconds().toString().padStart(2, '0');

			if(_parenthesis)
			{
				result += ')';
			}

			return (result + (_html ? '</span>' : ''));
		}});

		Object.defineProperty(Date.prototype, 'format', { value: function(_html = DEFAULT_FORMAT_HTML, _parenthesis = DEFAULT_FORMAT_PARENTHESIS)
		{
			return Date.format(this, _html, _parenthesis);
		}});

	//
	
	})();
	
	//
	(function()
	{
		Object.defineProperty(Date, 'dayByYear', { value: function(_date = new Date())
		{ return _date.dayByYear; }});

		Object.defineProperty(Date.prototype, 'dayByYear', { get: function()
		{
			const start = new Date(this.getFullYear(), 0, 0);
			const diff = (this - start) + ((start.getTimezoneOffset() - this.getTimezoneOffset()) * 60 * 1000);
			const oneDay = (1000 * 60 * 60 * 24);
			return (diff / oneDay);//Math.floor(diff / oneDay);//ohne nackomma siehe 'dayInYear' (w/ *In*!)!
		}});

		Object.defineProperty(Date, 'dayInYear', { value: function(_date = new Date())
		{ return Math.floor(_date.dayByYear); }});

		Object.defineProperty(Date.prototype, 'dayInYear', { get: function()
		{ return Math.floor(this.dayByYear); }});

		//
		Object.defineProperty(Date, 'daysInYear', { value: function(_date = new Date())
		{ return _date.daysInYear; }});

		Object.defineProperty(Date.prototype, 'daysInYear', { get: function()
		{
			if(this.isLeapYear)
			{
				return 366;
			}

			return 365;
		}});

		Object.defineProperty(Date, 'weekInYear', { value: function(_date = new Date())
		{
			const start = new Date(_date.getFullYear(), 0, 1);
			const today = new Date(_date.getFullYear(), _date.getMonth(), _date.getDate());
			const dayInYear = ((today.getTime() - start.getTime() + 1) / 86400000);

			return Math.ceil(dayInYear / 7);
		}});

		Object.defineProperty(Date.prototype, 'weekInYear', { get: function()
		{
			return Date.weekInYear(this);
		}});

		Object.defineProperty(Date, 'daysInMonth', { value: function(_date = new Date())
		{
			const year = _date.getFullYear();
			const month = _date.getMonth();

			if(month === 1)
			{
				if(_date.isLeapYear)
				{
					return 29;
				}

				return 28;
			}

			const negate = (month >= 7);
			return ((month % 2) === 0 ? (negate ? 30 : 31) : (negate ? 31 : 30));
		}});

		Object.defineProperty(Date.prototype, 'daysInMonth', { get: function()
		{
			return Date.daysInMonth(this);
		}});

		Object.defineProperty(Date, 'isLeapYear', { value: function(_date = new Date())
		{
			//
			var year;

			if(Date.isDate(_date))
			{
				year = _date.getFullYear();
			}
			else if(Number.isInt(_date))
			{
				year = _date;
			}
			else if(BigInt.isBigInt(_date))
			{
				year = Number(_date);
			}
			else if(typeof _date === 'string' && _date.isInt())
			{
				year = parseInt(_date);
			}
			else
			{
				throw new Error('Not a valid year or Date object');
			}

			//
			var result;

			if(DEFAULT_EFFICIENT_LEAP_YEAR === true)
			{
				result = (((year & 3) === 0) && (((year % 25) !== 0) || ((year & 15) === 0)));
			}
			else if(DEFAULT_EFFICIENT_LEAP_YEAR === false)
			{
				result = ((((year % 4) === 0) && ((year % 100) !== 0)) || (year % 400) === 0);
			}
			else if(DEFAULT_EFFICIENT_LEAP_YEAR === null)
			{
				return (new Date(year, 1, 29).getDate() === 29);
			}
			else
			{
				throw new Error('Invalid % configuration (may only be %, % or %)', null, 'DEFAULT_EFFICIENT_LEAP_YEAR', true, false, null);
			}

			return result;
			/*** ORIGINAL version *** /
			if(year % 4 === 0)
			{
				if(year % 100 === 0)
				{
					if(year % 400 === 0)
					{
						return true;
					}

					return false;
				}

				return true;
			}

			return false;*/
		}});

		Object.defineProperty(Date.prototype, 'isLeapYear', { get: function()
		{
			return Date.isLeapYear(this);
		}});

		//
		Object.defineProperty(Date, 'yearPercent', { value: function(_date = new Date())
		{
			return (_date.year * 100);
		}});

		Object.defineProperty(Date.prototype, 'yearPercent', { get: function()
		{
			return (this.year * 100);
		}});

		Object.defineProperty(Date, 'years', { value: function(_date = new Date())
		{
			return _date.years;
		}});

		Object.defineProperty(Date.prototype, 'years', { get: function()
		{
			return (this.getFullYear() + this.year);
		}});

		Object.defineProperty(Date, 'year', { value: function(_date = new Date())
		{
			return _date.year;
		}});

		Object.defineProperty(Date.prototype, 'year', { get: function()
		{
			return ((this.months - 1) / 12);
		}});

		Object.defineProperty(Date, 'months', { value: function(_date = new Date())
		{
			return _date.months;
		}});

		Object.defineProperty(Date.prototype, 'months', { get: function()
		{
			return (this.getMonth() + 1 + this.month);
		}});

		Object.defineProperty(Date, 'month', { value: function(_date = new Date())
		{
			return _date.month;
		}});

		Object.defineProperty(Date.prototype, 'month', { get: function()
		{
			return (this.days / (this.daysInMonth + 1));
		}});

		Object.defineProperty(Date, 'days', { value: function(_date = new Date())
		{
			return _date.days;
		}});

		Object.defineProperty(Date.prototype, 'days', { get: function()
		{
			return (this.getDate() - 1 + this.day);
		}});

		Object.defineProperty(Date, 'day', { value: function(_date = new Date())
		{
			return _date.day;
		}});

		Object.defineProperty(Date.prototype, 'day', { get: function()
		{
			return (this.hours / 24);
		}});

		Object.defineProperty(Date, 'hours', { value: function(_date = new Date())
		{
			return _date.hours;
		}});

		Object.defineProperty(Date.prototype, 'hours', { get: function()
		{
			return (this.getHours() + this.hour);
		}});

		Object.defineProperty(Date, 'hour', { value: function(_date = new Date())
		{
			return _date.hour;
		}});

		Object.defineProperty(Date.prototype, 'hour', { get: function()
		{
			return (this.minutes / 60);
		}});

		Object.defineProperty(Date, 'minutes', { value: function(_date = new Date())
		{
			return _date.minutes;
		}});

		Object.defineProperty(Date.prototype, 'minutes', { get: function()
		{
			return (this.getMinutes() + this.minute);
		}});

		Object.defineProperty(Date, 'minute', { value: function(_date = new Date())
		{
			return _date.minute;
		}});

		Object.defineProperty(Date.prototype, 'minute', { get: function()
		{
			return (this.seconds / 60);
		}});

		Object.defineProperty(Date, 'seconds', { value: function(_date = new Date())
		{
			return _date.seconds;
		}});

		Object.defineProperty(Date.prototype, 'seconds', { get: function()
		{
			return (this.getSeconds() + this.second);
		}});

		Object.defineProperty(Date, 'second', { value: function(_date = new Date())
		{
			return _date.second;
		}});

		Object.defineProperty(Date.prototype, 'second', { get: function()
		{
			return (this.getMilliseconds() / 1000);
		}});

		Object.defineProperty(Date, 'milliseconds', { value: function(_date = new Date())
		{
			return _date.milliseconds;
		}});

		Object.defineProperty(Date.prototype, 'milliseconds', { get: function()
		{
			return this.getTime();
		}});

		Object.defineProperty(Date, 'millisecond', { value: function(_date = new Date())
		{
			return _date.millisecond;
		}});

		Object.defineProperty(Date.prototype, 'millisecond', { get: function()
		{
			return (this.getTime() % 1000);
		}});
	
	//
	
	})();
	
	//

})();

