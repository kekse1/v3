(function()
{

	//
	const DEFAULT_SCROLL_FACTOR = 1.2;
	const DEFAULT_SCROLL_FACTOR_SHIFT = 2.00;
	const DEFAULT_SCROLL_FACTOR_ALT_CTRL = 0.4;
	
	const DEFAULT_OSD_ROUND = 1;
	const DEFAULT_OSD_DURATION = 900;
	const DEFAULT_OSD_TIMEOUT = 900;
	
	const DEFAULT_FIND_ELEMENTS_FROM_POINT = true;

	//
	scrolling = { pause: false };

	//
	Object.defineProperty(Element.prototype, 'scrollBottom', {
		get: function()
		{
			return (this.scrollTop + this.clientHeight);
		},
		set: function(_value)
		{
			if(isNumber(_value))
			{
				if(Math.abs(_value) > (this.scrollHeight + this.clientHeight))
				{
					if(_value < 0)
					{
						_value = -(this.scrollHeight - this.clientHeight);
					}
					else
					{
						_value = (this.scrollHeight - this.clientHeight);
					}
				}
				
				if(_value < 0)
				{
					_value = ((this.scrollHeight - this.clientHeight) + _value);
				}

				this.scrollTop = (_value - this.clientHeight);
			}

			return this.scrollBottom;
		}
	});

	Object.defineProperty(Element.prototype, 'scrollRight', {
		get: function()
		{
			return (this.scrollLeft + this.clientWidth);
		},
		set: function(_value)
		{
			if(isNumber(_value))
			{
				if(Math.abs(_value) > (this.scrollWidth + this.clientWidth))
				{
					if(_value < 0)
					{
						_value = -(this.scrollWidth - this.clientWidth);
					}
					else
					{
						_value = (this.scrollWidth - this.clientWidth);
					}
				}

				if(_value < 0)
				{
					_value = ((this.scrollWidth - this.clientWidth) + _value);
				}

				this.scrollLeft = (_value - this.clientWidth);
			}

			return this.scrollRight;
		}
	});

	Object.defineProperty(Element.prototype, 'scrollY', {
		get: function()
		{
			if(this.scrollHeight <= this.clientHeight)
			{
				return 100;
			}

			const result = (this.scrollTop / (this.scrollHeight - this.clientHeight) * 100);
			
			if(isNaN(result))
			{
				return 0;
			}
			
			return result;
		},
		set: function(_value)
		{
			if(isNumber(_value))
			{
				if(Math.abs(_value) > 100)
				{
					if(_value < 0)
					{
						_value = -100;
					}
					else
					{
						_value = 100;
					}
				}

				if(_value < 0)
				{
					_value = (100 + _value);
				}

				this.scrollTop = ((this.scrollHeight - this.clientHeight) * _value / 100);
			}

			return this.scrollY;
		}
	});

	Object.defineProperty(Element.prototype, 'scrollX', {
		get: function()
		{
			if(this.scrollWidth <= this.clientWidth)
			{
				return 100;
			}

			const result = (this.scrollLeft / (this.scrollWidth - this.clientWidth) * 100);
			
			if(isNaN(result))
			{
				return 0;
			}
			
			return result;
		},
		set: function(_value)
		{
			if(isNumber(_value))
			{
				if(Math.abs(_value) > 100)
				{
					if(_value < 0)
					{
						_value = -100;
					}
					else
					{
						_value = 100;
					}
				}

				if(_value < 0)
				{
					_value = (100 + _value);
				}

				this.scrollLeft = ((this.scrollWidth - this.clientWidth) * _value / 100);
			}

			return this.scrollX;
		}
	});

	Object.defineProperty(Element.prototype, 'scrolling', {
		get: function()
		{
			switch(this.getVariable('scrolling', null))
			{
				case 'none':
					return false;
				case 'auto':
					return true;
			}

			return (this.scrollHeight > this.clientHeight);
		},
		set: function(_value)
		{
			if(typeof _value === 'boolean')
			{
				this.setVariable('scrolling', _value);
				return _value;
			}
			else
			{
				this.removeVariable('scrolling');
			}

			return this.scrolling;
		}
	});
	
	//
	var lastScrollElement = null;
	var pointerDownScrollElement = null;
	
	const validatePointerDownScrollElement = (_event_x, _y) => {
		if(! pointerDownScrollElement)
		{
			return null;
		}
		
		var x, y;
		
		if(isNumber(_event_x) && isNumber(_y))
		{
			x = _event_x;
			y = _y;
		}
		else if(_event_x && ('clientX' in _event_x) && ('clientY' in _event_x))
		{
			x = _event_x.clientX;
			y = _event_x.clientY;

			if(_event_x.target.scrolling)
			{
				result = _event_x.target;
			}
		}
		else
		{
			return pointerDownScrollElement;
		}

		if(x < pointerDownScrollElement.offsetLeft)
		{
			return false;
		}
		else if(y < pointerDownScrollElement.offsetTop)
		{
			return false;
		}
		else if(x > (pointerDownScrollElement.offsetLeft + pointerDownScrollElement.offsetWidth))
		{
			return false;
		}
		else if(y > (pointerDownScrollElement.offsetTop + pointerDownScrollElement.offsetHeight))
		{
			return false;
		}
		
		return true;
	};
	
	const findScrollElement = (_event_x, _y) => {
		var result = null;
		var x, y;

		if(isNumber(_event_x) && isNumber(_y))
		{
			x = _event_x;
			y = _y;
		}
		else if(_event_x && ('clientX' in _event_x) && ('clientY' in _event_x))
		{
			x = _event_x.clientX;
			y = _event_x.clientY;
		}
		else
		{
			x = y = null;
		}
		
		if(x !== null && y !== null)
		{
			const elements = document.elementsFromPoint(x, y);

			if(DEFAULT_FIND_ELEMENTS_FROM_POINT) for(const e of elements)
			{
				if(e.scrolling)
				{
					result = e;
					break;
				}
			}
			else if(elements.length > 0 && elements[0].scrolling)
			{
				result = elements[0];
			}
		}

		if(result === null)
		{
			if(validatePointerDownScrollElement(_event_x, _y))
			{
				result = pointerDownScrollElement;
			}
			else
			{
				pointerDownScrollElement = null;
			}
		}
		else
		{
			pointerDownScrollElement = null;
		}
			
		if(result === null)
		{
			if(MAIN && MAIN.scrolling)
			{
				result = MAIN;
			}
			else if(BODY && BODY.scrolling)
			{
				result = BODY;
			}
			else if(HTML && HTML.scrolling)
			{
				result = HTML;
			}
		}
		
		if(result === null && lastScrollElement && lastScrollElement.scrolling)
		{
			result = lastScrollElement;
		}

		if(result !== null)
		{
			lastScrollElement = result;
		}
		
		return result;
	};
	
	//
	const checkMultipliers = (_event) => {
		var result = DEFAULT_SCROLL_FACTOR;
		
		if(! ((_event.altKey || _event.ctrlKey) && _event.shiftKey))
		{
			if(_event.altKey || _event.ctrlKey)
			{
				result *= DEFAULT_SCROLL_FACTOR_ALT_CTRL;
			}
			
			if(_event.shiftKey)
			{
				result *= DEFAULT_SCROLL_FACTOR_SHIFT;
			}
		}
		
		return result;
	};
	
	window.addEventListener('pointerdown', (_event) => {
		const target = findScrollElement(_event);

		if(target)
		{
			pointerDownScrollElement = target;
		}
	}, { passive: true });
	
	window.addEventListener('pointermove', (_event) => {
		const target = findScrollElement(_event);
		
		if(target)
		{
			pointerDownScrollElement = target;
		}
	}, { passive: true });
	
	window.addEventListener('wheel', (_event) => {
		const target = findScrollElement(_event);
		
		if(target)
		{
			pointerDownScrollElement = target;
			_event.preventDefault();
		}
		else
		{
			return;
		}
		
		const mul = checkMultipliers(_event);
		const left = (_event.deltaX * mul);
		const top = (_event.deltaY * mul);
		
		if(left !== 0)
		{
			target.scrollLeft += left;
		}
		
		if(top !== 0)
		{
			target.scrollTop += top;
		}
	}, { passive: false });
	
	window.addEventListener('keydown', (_event) => {
		//
		if(scrolling.pause)
		{
			return;
		}

		//
		const elem = findScrollElement(_event);

		if(! elem)
		{
			return;
		}
		
		//
		const mul = checkMultipliers(_event);
		
		//
		var y = elem.scrollTop;
		var x = elem.scrollLeft;
		
		//
		switch(_event.key)
		{
			case 'ArrowLeft':
				x -= (elem.clientWidth / 4 * mul);
				break;
			case 'ArrowRight':
				x += (elem.clientWidth / 4 * mul);
				break;
			case 'ArrowUp':
				y -= (elem.clientHeight / 4 * mul);
				break;
			case 'ArrowDown':
				y += (elem.clientHeight / 4 * mul);
				break;
			case 'PageUp':
				y -= (elem.clientHeight / 1.5 * mul);
				break;
			case 'PageDown':
				y += (elem.clientHeight / 1.5 * mul);
				break;
			case 'Home':
				y = 0;
				x = 0;
				break;
			case 'End':
				y = (elem.scrollHeight - elem.clientHeight);
				x = 0;
				break;
			/*case 'Backspace':
				break;*/
			default:
				return;
		}
		
		//
		if(elem.scrollTop !== y)
		{
			elem.scrollTop = y;
		}

		if(elem.scrollLeft !== x)
		{
			elem.scrollLeft = x;
		}

		//
		_event.preventDefault();
	}, { passive: false });
	
	const showScrollingState = (_elem) => {
		//
		if(!_elem.scrolling)
		{
			return null;
		}

		//
		var xValue, yValue;

		if(_elem.clientWidth >= _elem.scrollWidth)
		{
			xValue = null;
		}
		else
		{
			xValue = _elem.scrollX;
		}

		if(_elem.clientHeight >= _elem.scrollHeight)
		{
			yValue = null;
		}
		else
		{
			yValue = _elem.scrollY;
		}

		//
		xValue = (xValue === null ? null : xValue.toFixed(DEFAULT_OSD_ROUND));
		yValue = (yValue === null ? null : yValue.toFixed(DEFAULT_OSD_ROUND));

		var x = '';
		var y = '';
			
		if(xValue !== null) for(var i = 0; i < xValue.length; ++i)
		{
			if(xValue[i] === '.')
			{
				x = x.padStart(3, ' ').replaces(' ', '&nbsp;');
				x += '<span style="font-size: 55%;">' + xValue.substr(i) + '</span>';
				break;
			}
			else
			{
				x += xValue[i];
			}
		}
			
		if(yValue !== null) for(var i = 0; i < yValue.length; ++i)
		{
			if(yValue[i] === '.')
			{
				y = y.padStart(3, ' ').replaces(' ', '&nbsp;');
				y += '<span style="font-size: 55%;">' + yValue.substr(i) + '</span>';
				break;
			}
			else
			{
				y += yValue[i];
			}
		}

		//
		var result;

		if(xValue !== null || yValue !== null)
		{
			result = '<div>';

			if(yValue !== null)
			{
				result += '<span style="font-size: 40%; font-weight: 100; color: red;">[<b style="color: blue; font-size: 150%; font-weight: 700;">y</b>]</span>';
				result += '<span style="font-weight: 700;">' + y + '</span><span style="font-size: 80%; font-weight: 100; margin-left: 16px; color: yellow;">%</span>';

				if(xValue !== null)
				{
					result += '<br>';
				}
			}

			if(xValue !== null)
			{
				result += '<span style="font-size: 40%; font-weight: 100; color: red">[<b style="color: blue; font-size: 150%; font-weight: 700;">x</b>]</span>';
				result += '<span style="font-weight: 700;">' + x + '</span><span style="font-size: 80%; font-weight: 100; margin-left: 16px; color: yellow;">%</span>';
			}

			result += '</div>';
		}
		else
		{
			return null;
		}

		//
		return osd(result);
	};

	//
	document.addEventListener('scroll', (_e) => {
		return showScrollingState(_e.target);
	}, { passive: true, capture: true });

	//
	
})();

