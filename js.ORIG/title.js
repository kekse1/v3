(function()
{

	//
	const DEFAULT_THROW = true;
	const DEFAULT_TIMEOUT = 4000;
	const DEFAULT_DELAY = 2400;
	const DEFAULT_MUL_TIME = 0.9;
	const DEFAULT_MUL_DELETE = 0.3;
	const DEFAULT_AUTO_ANIMATION = false;

	//
	const title = (_throw = DEFAULT_THROW) => {
		if(isString(config.title, true))
		{
			return title.string(_throw);
		}
		else if(isArray(config.title, false))
		{
			return title.array(_throw);
		}
		
		return config.title = null;
	};

	title.string = (_throw = DEFAULT_THROW) => {
		animateText(config.title);
	};

	title.array = (_throw = DEFAULT_THROW) => {
		if(! title.array.isValid())
		{
			config.title = null;

			if(_throw)
			{
				throw new Error('Invalid config file (title is invalid)');
			}

			return;
		}
		else if(isString(config.title, true))
		{
			return title.string(_throw);
		}

		const array = JSON.parse(JSON.stringify(config.title));
		var index = 0;

		const check = () => {
			const item = array[index];
			index = ((index + 1) % array.length);
			animateText(item[0], item[1]);
			setTimeout(check, item[1]);
		};

		check();
	};

	title.array.isValid = () => {
		if(config.title.length === 0)
		{
			return false;
		}
		else if(config.title.length === 1)
		{
			if(isString(config.title[0], true))
			{
				config.title = config.title[0];
			}
			else if(isArray(config.title[0], false))
			{
				if(isString(config.title[0][0], true))
				{
					config.title = config.title[0][0];
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
		}
		else for(var i = 0; i < config.title.length; ++i)
		{
			if(isString(config.title[i], true))
			{
				config.title[i] = [ config.title[i], DEFAULT_TIMEOUT ];
			}
			else if(isArray(config.title[i], false))
			{
				if(config.title[i].length === 1)
				{
					if(isString(config.title[i][0], true))
					{
						config.title[i][1] = DEFAULT_TIMEOUT;
					}
					else
					{
						return false;
					}
				}
				else if(config.title[i].length === 2)
				{
					if(! (isString(config.title[i][0], true) && (isInt(config.title[i][1]) && config.title[i][1] >= 1)))
					{
						return false;
					}
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
		}

		return true;
	};

	//
	var animating = false;

	const animateText = (_text, _max_time = null, _mul = DEFAULT_MUL_TIME, _mul_delete = DEFAULT_MUL_DELETE) => {
		if(! (isInt(_max_time) && _max_time > 0))
		{
			document.title = _text;
		}
		else if(isNumber(_mul) && _mul > 0 && _mul <= 1)
		{
			_max_time *= _mul;
		}

		_text = _text.trim();

		if(animating)
		{
			animating = false;
			return document.title = _text;
		}
		else
		{
			animating = true;
		}

		const totalLength = (document.title.length + _text.length);
		const charTime = (_max_time / totalLength);
		var charTimeAdd, charTimeSub;

		if(!DEFAULT_AUTO_ANIMATION && isNumber(_mul_delete) && _mul_delete > 0 && _mul_delete < 1)
		{
			charTimeSub = (charTime * _mul_delete);
			charTimeAdd = (charTime - charTimeSub);
		}
		else
		{
			charTimeAdd = (charTime * _text.length / totalLength);
			charTimeSub = (charTime * document.title.length / totalLength);
		}

		var duration = 0, charDuration = 0, char = null;
		var delta, now, chars, sub;
		var lastNow = Date.now();

		const frame = () => {
			//
			if(! animating)
			{
				return;
			}

			//
			now = Date.now();
			delta = (now - lastNow);
			lastNow = now;
			duration += delta;
			charDuration += delta;

			//
			chars = 0;

			if(char === null)
			{
				while(charDuration >= charTimeSub)
				{
					++chars;
					charDuration -= charTimeSub;
				}

				if(animating && chars > 0)
				{
					document.title = document.title.slice(0, -chars);
				}
			}
			else
			{
				while(charDuration >= charTimeAdd)
				{
					++chars;
					charDuration -= charTimeAdd;
				}

				if(animating && chars > 0)
				{
					if(char === 0)
					{
						document.title = '';
					}

					sub = '';

					while(--chars >= 0 && char < _text.length)
					{
						sub += _text[char++];

						while(sub.endsWith(' ') && char < _text.length)
						{
							sub += _text[char++];
						}
					}

					document.title += sub;

					if(char >= _text.length)
					{
						animating = false;
					}
				}
			}

			//
			if(document.title.length === 0)
			{
				document.title = '-';

				if(char === null)
				{
					char = 0;
				}
			}

			//
			if(animating)
			{
				window.requestAnimationFrame(frame);
			}
		};

		window.requestAnimationFrame(frame);
	};

	//
	window.addEventListener('ready', () => {
		setTimeout(title, DEFAULT_DELAY);
	}, { once: true });

})();

