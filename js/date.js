(function()
{

	//
	const DEFAULT_FORMAT_HTML = true;
	const DEFAULT_FORMAT_PARENTHESIS = true;

	//
	(function()
	{
		//
		Object.defineProperty(Date, 'toText', { value: function(_date = new Date(), _html = DEFAULT_FORMAT_HTML, _parenthesis = DEFAULT_FORMAT_PARENTHESIS)
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

		Object.defineProperty(Date.prototype, 'toText', { value: function(_html = DEFAULT_FORMAT_HTML, _parenthesis = DEFAULT_FORMAT_PARENTHESIS)
		{
			return Date.toText(this, _html, _parenthesis);
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

			if(is(_date, 'Date'))
			{
				year = _date.getFullYear();
			}
			else if(isInt(_date))
			{
				year = _date;
			}
			else if(typeof _date === 'bigint')
			{
				year = Number(_date);
			}
			else if(typeof _date === 'string' && !isNaN(_date))
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
	
	})();

	//
	(function()
	{
	
		//
		Object.defineProperty(Date, 'unix', { value: function(_date = new Date())
		{
			try
			{
				return _date.unix;
			}
			catch(_error)
			{
				throw new Error('Invalid _date argument');
			}
			
			return null;
		}});
		
		Object.defineProperty(Date.prototype, 'unix', { get: function()
		{
			return Math.round(this.getTime() / 1000);
		}});
		
		//
		Object.defineProperty(Date.prototype, 'toGMT', { value: function()
		{
			const ZONE = 'GMT';

			//
			const mins = (this.getTime() / 1000 / 60);
			const gmt = new Date(((this.getTime() / 1000 / 60) + this.getTimezoneOffset()) * 60 * 1000);

			return Date.WEEKDAY(gmt.getDay()) + ', '
				+ gmt.getDate().toString().padStart(2, '0') + ' '
				+ Date.MONTH(gmt.getMonth()) + ' '
				+ gmt.getFullYear().toString() + ' '
				+ gmt.getHours().toString().padStart(2, '0') + ':'
				+ gmt.getMinutes().toString().padStart(2, '0') + ':'
				+ gmt.getSeconds().toString().padStart(2, '0') + ' ' + ZONE;
		}});

		Object.defineProperty(Date, 'GMT', { value: function(_date = new Date())
		{
			try
			{
				return _date.toGMT();
			}
			catch(_error)
			{
				throw new Error('Invalid _date argument');
			}
			
			return null;
		}});
	
		//
		//
		Object.defineProperty(Date, 'modifiers', { get: function()
		{
			const modifiers = Object.keys(Date.format).sort(true);
			const result = [];

			for(var i = 0, j = 0; i < modifiers.length; i++)
			{
				if(modifiers[i].length === 1)
				{
					result[j++] = modifiers[i];
				}
			}

			return result;
		}});

		Object.defineProperty(Date.prototype, 'format', { value: function(_format = date.getDefaultDateFormat())
		{
			if(typeof _format !== 'string')
			{
				_format = date.getDefaultDateFormat();
			}
			else if(_format.length === 0)
			{
				return '';
			}
			else
			{
				_format = date.getDateFormat(_format);
			}

			//
			const formats = Date.modifiers;
			var result = '';

			for(var i = 0; i < _format.length; i++)
			{
				if(_format[i] === '\\')
				{
					if(i < (_format.length - 1))
					{
						result += _format[++i];
					}
					else
					{
						result += '\\';
					}
				}
				else if(_format[i] === '%')
				{
					if(i < (_format.length - 1))
					{
						const f = _format[i + 1];

						if(formats.indexOf(f) > -1)
						{
							result += Date.format[f](this);
							i++;
						}
						else
						{
							result += '%';
						}
					}
					else
					{
						result += '%';
					}
				}
				else
				{
					result += _format[i];
				}
			}

			return result;
		}});

		Object.defineProperty(Date, 'format', { value: function(_format = date.getDefaultDateFormat(), _date = new Date())
		{
			try
			{
				return _date.format(_format);
			}
			catch(_error)
			{
				throw new Error('Invalid _date argument');
			}
			
			return null;
		}});

		Date.format['D'] = function(_date = new Date())
		{
			return Date.dayInYear(_date).toString();
		}

		Date.format['y'] = function(_date = new Date())
		{
			return _date.getFullYear().toString();
		}

		Date.format['m'] = function(_date = new Date())
		{
			return (_date.getMonth() + 1).toString().padStart(2, '0');
		}

		Date.format['d'] = function(_date = new Date())
		{
			return _date.getDate().toString().padStart(2, '0');
		}

		Date.format['k'] = function(_date = new Date())
		{
			return Date.weekInYear(_date).toString();
		}

		Date.format['H'] = function(_date = new Date())
		{
			return _date.getHours().toString().padStart(2, '0');
		}

		Date.format['h'] = function(_date = new Date())
		{
			var twelve = _date.getHours() % 12;

			if(twelve === 0)
			{
				twelve = 12;
			}

			return twelve.toString().padStart(2, '0');
		}

		Date.format['M'] = function(_date = new Date())
		{
			return _date.getMinutes().toString().padStart(2, '0');
		}

		Date.format['S'] = function(_date = new Date())
		{
			return _date.getSeconds().toString().padStart(2, '0');
		}

		Date.format['s'] = function(_date = new Date())
		{
			return _date.getMilliseconds().toString().padStart(3, '0');
		}

		Date.format['X'] = function(_date = new Date())
		{
			return Math.round(_date.getTime() / 1000).toString();
		}

		Date.format['x'] = function(_date = new Date())
		{
			return _date.getTime().toString();
		}

		Date.format['t'] = function(_date = new Date())
		{
			if(_date.getHours() < 12)
			{
				return 'am';
			}
			else
			{
				return 'pm';
			}
		}

		Date.format['T'] = function(_date = new Date())
		{
			if(_date.getHours() < 12)
			{
				return 'AM';
			}
			else
			{
				return 'PM';
			}
		}

		Date.format['N'] = function(_date = new Date())
		{
			return Date.MONTH(_date, false);
		}

		Date.format['n'] = function(_date = new Date())
		{
			return Date.MONTH(_date, true);
		}

		Date.format['W'] = function(_date = new Date())
		{
			return Date.WEEKDAY(_date, false);
		}

		Date.format['w'] = function(_date = new Date())
		{
			return Date.WEEKDAY(_date, true);
		}

		//
		Object.defineProperty(Date, 'age', { value: function(_year, _month, _day, _hour, _minute, _second, _millisecond)
		{
			if(typeof _month === 'number')
			{
				_month--;
			}

			const result = Object.create(null);

			const now = new Date();
			const bday = new Date(... arguments);
			var rest = false, oldRest = false;
			var diff;

			for(var i = 0; i < Date.age.units.length; i++)
			{
				const [ unit, base ] = Date.age.units[i];
				diff = (now[unit] - bday[unit]);

				/*if(diff < 0 && base !== 1)
				{
					diff = (base + diff);
				}*/

				result[unit] = diff;
			}

			return result;
		}});

		Date.age.units = [
			[ 'years', 1 ],
			[ 'months', 12 ],
			[ 'days', 1 ],
			[ 'hours', 24 ],
			[ 'minutes', 60 ],
			[ 'seconds', 60 ]
		];

		//
	
	})();

	//
	(function()
	{
	
		//
		Object.defineProperty(Date, 'WEEKDAY', { value: function(_day, _short = false, _lang = navigator.language)
		{
			if(typeof _short !== 'boolean')
			{
				return x('Invalid % argument (not a %)', null, '_short', 'Boolean');
			}
			else if(isArray(_lang, false))
			{
				for(var i = 0; i < _lang.length; ++i)
				{
					if(isString(_lang[i], false))
					{
						_lang = _lang[i];
						break;
					}
				}
			}

			if(! isString(_lang, false))
			{
				_lang = navigator.language;
			}

			if(isNumber(_day))
			{
				//_day = Math.floor(_day) % 7;
				_day = Math.getIndex(_day, 7);
			}
			else if(is(_day, 'Date'))
			{
				_day = _day.getDay();
			}
			else if(_day !== null)
			{
				_day = new Date().getDay();
			}

			const dtf = intl(_lang, 'DateTimeFormat', { weekday: (_short ? 'short' : 'long') });

			if(_day !== null)
			{
				return dtf.format(new Date(86400000*(3+_day)));
			}

			const result = new Array(7);

			for(var i = 0, j = 3; i < 7; ++i, ++j)
			{
				result[i] = dtf.format(new Date(86400000 * j));
			}

			return result;
		}});

		Object.defineProperty(Date, 'MONTH', { value: function(_month, _short = false, _lang = navigator.language)
		{
			if(typeof _short !== 'boolean')
			{
				return x('Invalid % argument (not a %)', null, '_short', 'Boolean');
			}
			else if(isArray(_lang, false))
			{
				for(var i = 0; i < _lang.length; ++i)
				{
					if(isString(_lang[i], false))
					{
						_lang = _lang[i];
						break;
					}
				}
			}

			if(! isString(_lang, false))
			{
				_lang = navigator.language;
			}

			if(isNumber(_month))
			{
				//_month = Math.floor(_month) % 12;
				_month = Math.getIndex(_month, 12);
			}
			else if(is(_month, 'Date'))
			{
				_month = _month.getMonth();
			}
			else if(_month !== null)
			{
				_month = new Date().getMonth();
			}

			const dtf = intl(_lang, 'DateTimeFormat', { month: (_short ? 'short' : 'long') });

			if(_month !== null)
			{
				return dtf.format(new Date(2419200000*(_month+1)));
			}

			const result = new Array(12);

			for(var i = 0; i < 12; ++i)
			{
				result[i] = dtf.format(new Date(2419200000*(i+1)));
			}

			return result;
		}});

		//
	
	})();

	//
	(function()
	{

		//
		date = Date.date = function(_which = date.getDefaultDateFormat(), _date = new Date())
		{
			if(! isString(_which, false))
			{
				return null;
			}
			else if(isInt(_date))
			{
				_date = new Date(_date);
			}
			else if(! is(_date, 'Date'))
			{
				_date = new Date();
			}

			return _date.format(date.getDateFormat(_which));
		}

		//
		date.getDateFormat = (_format = date.getDefaultDateFormat) => {
			if(typeof _format !== 'string')
			{
				_format = date.getDefaultDateFormat();
			}
			else
			{
				_format = _format.toLowerCase();
			}
			
			const result = document.getVariable('date' + (_format.length === 0 ? '' : '-' + _format), true);
			
			if(result.length > 0)
			{
				return result;
			}
			
			return _format;
		};
		
		date.getDefaultDateFormat = (_variable = 'date') => {
			if(! isString(_variable, false))
			{
				_variable = 'date';
			}
			
			return document.getVariable(_variable, true);
		};
	
		//
		date['gmt'] = (_date = new Date()) => {
			try
			{
				if(isInt(_date))
				{
					_date = new Date(_date);
				}
				
				return _date.toGMT();
			}
			catch(_error)
			{
				alert(_error.stack);
				throw new Error('Invalid _date argument');
			}
			
			return null;
		};

		//
		//not necessary but for your info in here.. [will automatically ask the css @ 'date.css'..! ^_^
		////date.formats = [ 'now', 'time', 'date', 'default', 'best', 'console', 'full', 'text-full', 'year', 'ms', 'unix' ];
		//

		//

	})();

	//

})();
