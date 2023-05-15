(function()
{

	//
	const DEFAULT_THROW = true;

	const DEFAULT_MAX = null;
	const DEFAULT_CALLBACKS = (1 | 2);
		
	const DEFAULT_VERSION_URL = 'status/version.json';
	const DEFAULT_VERSION_DELAY = (1000 * 60 * 4);
	
	const DEFAULT_COUNTER_URL = 'status/counter/' + location.host;
	const DEFAULT_COUNTER_DELAY = (1000 * 60);

	const DEFAULT_UPDATED_URL = 'status/update.now';
	const DEFAULT_UPDATED_DELAY = (1000 * 60 * 2);

	const DEFAULT_NULL = '(%)';
	
	//
	const mod = dynamic.module = {};

	//
	const setHTML = (_element, _html, _animate = _element, _duration = _element.getVariable('data-duration', true), _delay = _element.getVariable('data-delay', true)) => {
		//
		var animationElement;
		
		if(typeof _animate !== 'boolean')
		{
			if(was(_animate, 'Element'))
			{
				animationElement = _animate;
			}
			else
			{
				throw new Error('Invalid _animate argument (neither an Element nor a Boolean type)');
			}
		}
		else if(_animate)
		{
			animationElement = _element;
		}
		else
		{
			animationElement = null;
		}
		
		const blink = () => {
			if(animationElement !== null)
			{
				animationElement.blink();
			}
		};

		//
		if(! (isInt(_duration) && _duration > 0) || typeof _element.setHTML !== 'function')
		{
			_element.innerHTML = _html;
			return blink();
		}

		return _element.setHTML(blink, _html, _duration, _delay);
	};

	//
	mod.version = (_throw = DEFAULT_THROW) => {
		return dynamic.add('version', DEFAULT_VERSION_URL, DEFAULT_VERSION_DELAY, mod.version.callback, DEFAULT_MAX, DEFAULT_CALLBACKS, _throw);
	};
	
	mod.version.callback = (_event, _request, _dynamic) => {
		//
		const onload = () => {
			if(_request.statusClass !== 2)
			{
				return onfailure();
			}
			
			try
			{
				const oldest = ((typeof version === 'object' && isArray(version, true)) ? version : null);
				const newest = JSON.parse(_request.responseText);
				
				if(oldest !== null)
				{
					if(Array.equal(oldest, newest))
					{
						setHTML(VERSION, '<span class="greenArrow" style="font-size: 90%;">v</span>' + (version = newest).join('.'), true);
					}
					else
					{
						setHTML(VERSION, '<span class="redArrow" style="font-size: 85%;">v' + oldest.join('.') + '</span> <span class="greenArrow" style="font-size: 90%; margin-left: 3px; margin-right: -3px;"></span> v<b>' + newest.join('.') + '</b>', true);
					}
				}
				else
				{
					setHTML(VERSION, '<span class="greenArrow" style="font-size: 90%;">v</span>' + (version = newest).join('.'), true);
				}
			}
			catch(_error)
			{
				version = null;
				setHTML(VERSION, DEFAULT_NULL, true);
				
				if(_dynamic.throw)
				{
					throw new Error('Couldn\'t JSON.parse(\'' + _event.url + '\')');
				}
				
				return;
			}
		};
		
		const onfailure = () => {
			setHTML(VERSION, DEFAULT_NULL, true);

			if(_dynamic.throw)
			{
				throw new Error('Couldn\'t load \'' + (_event.responseURL || _dynamic.url) + '\'');
			}
		};
		
		//
		switch(_event.type)
		{
			case 'load':
				return onload();
			case 'failure':
				return onfailure();
		}
		
		//
		return null;
	};

	//
	mod.updated = (_throw = DEFAULT_THROW) => {
		return dynamic.add('updated', DEFAULT_UPDATED_URL, DEFAULT_UPDATED_DELAY, mod.updated.callback, DEFAULT_MAX, DEFAULT_CALLBACKS, _throw);
	};

	mod.updated.callback = (_event, _request, _dynamic) => {
		const onload = () => {
			if(_request.statusClass !== 2)
			{
				return onfailure();
			}
			
			const lastUpdateDataPopup = '<span style="font-size: 1.2rem; text-decoration: underline;">Last Update:</span><br><span style="font-size: 1.4rem;">' + _request.responseText + '</span>';
			const lastUpdateDataUpdate = '<span style="font-size: 0.7rem; text-decoration: underline;">Updated</span><span style="font-size: 0.9rem;"> ' + _request.responseText + '</span>';
			
			setHTML(UPDATED, lastUpdateDataUpdate, true);
			
			if(FAVICON)
			{
				FAVICON.dataset.popup = lastUpdateDataPopup;
			}
		};

		const onfailure = () => {
			if(FAVICON)
			{
				delete FAVICON.dataset.popup;
			}
		};

		switch(_event.type)
		{
			case 'load':
				return onload();
			case 'failure':
				return onfailure();
		}

		return null;
	};
	
	//
	mod.counter = (_throw = DEFAULT_THROW) => {
		//
		if(! COUNTER.prepared)
		{
			//
			const responsive = () => {
				if(window.innerWidth >= 360)
				{
					if(COUNTER.classList.contains('noArrow'))
					{
						COUNTER.classList._remove('noArrow');
					}
				}
				else
				{
					if(! COUNTER.classList.contains('noArrow'))
					{
						COUNTER.classList._add('noArrow');
					}
				}
			};

			responsive();

			var resizeTimeout = null;
			window.addEventListener('resize', () => {
				if(resizeTimeout !== null)
				{
					clearTimeout(resizeTimeout);
				}

				resizeTimeout = setTimeout(() => {
					responsive();
					resizeTimeout = null;
				}, document.getVariable('resize-timeout', true));
			});

			//
			const counter = document.createElement('span');
			counter.id = 'counter';
			
			//
			COUNTER._appendChild(COUNTER.counter = counter);
			
			//
			const haveSeen = document.createElement('span');
			haveSeen.id = 'counterInfoText';
			setHTML(haveSeen, ' have seen ', true);
			
			//
			COUNTER._appendChild(COUNTER.haveSeen = COUNTER.infoText = haveSeen);
			
			//
			const link = document.createElement('a');
			link.id = 'self';
			link.target = '#';
			const url = (isString(config.url, false) ? config.url : location.href);
			setHTML(link, uniform(link.href = url, false, null, true), true);
			
			//
			COUNTER._appendChild(COUNTER.link = COUNTER.self = link);
			
			//
			COUNTER.prepared = true;
		}
		
		//
		return dynamic.add('counter', DEFAULT_COUNTER_URL, DEFAULT_COUNTER_DELAY, mod.counter.callback, DEFAULT_MAX, DEFAULT_CALLBACKS, _throw);
	};
	
	mod.counter.callback = (_event, _request, _dynamic) => {
		//
		const onload = () => {
			if(_request.statusClass !== 2)
			{
				return onfailure();
			}

			if(isNaN(_request.responseText))
			{
				return setHTML(COUNTER.counter, DEFAULT_NULL, COUNTER);
			}
			else
			{
				counter = Number(_request.responseText);
			}
			
			return setHTML(COUNTER.counter, counter.toLocaleString(), COUNTER);
		};
		
		const onfailure = () => {
			setHTML(COUNTER, DEFAULT_NULL, true);
			
			if(_dynamic.throw)
			{
				throw new Error('Couldn\'t load \'' + (_event.responseURL || _dynamic.url) + '\'');
			}
		};
		
		//
		switch(_event.type)
		{
			case 'load':
				return onload();
			case 'failure':
				return onfailure();
		}
		
		//
		return null;
	};

	//

})();

