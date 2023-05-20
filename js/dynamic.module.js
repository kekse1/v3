(function()
{

	//
	const DEFAULT_THROW = true;

	const DEFAULT_MAX = null;
	const DEFAULT_CALLBACKS = (1 | 2);
		
	const DEFAULT_VERSION_URL = 'status/version.json';
	const DEFAULT_VERSION_DELAY = (1000 * 60 * 4);
	
	const DEFAULT_COUNT_URL = 'status/count/' + location.host.toLowerCase();
	const DEFAULT_COUNT_DELAY = (1000 * 60);

	const DEFAULT_UPDATED_URL = 'status/update.now';
	const DEFAULT_UPDATED_DELAY = (1000 * 60 * 2);
	const DEFAULT_UPDATED_FORMAT = 'text';

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

			if(isNaN(_request.responseText))
			{
				return onfailure();
			}

			const updated = new Date(Number(_request.responseText)).format(DEFAULT_UPDATED_FORMAT);
			const lastUpdateDataPopup = '<span style="font-size: 1.2rem; text-decoration: underline;">Last Update:</span><br><span style="font-size: 1.4rem;">' + updated + '</span>';
			const lastUpdateDataUpdate = '<span style="font-size: 0.7rem; text-decoration: underline;">Updated</span><span style="font-size: 0.9rem;"> ' + updated + '</span>';
			
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

			setHTML(UPDATED, '[' + _event.status + '] ' + (_event.statusText || 'error'));
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
	mod.count = (_throw = DEFAULT_THROW) => {
		//
		if(! COUNT.prepared)
		{
			//
			const responsive = () => {
				if(window.innerWidth >= 360)
				{
					if(COUNT.classList.contains('noArrow'))
					{
						COUNT.classList._remove('noArrow');
					}
				}
				else
				{
					if(! COUNT.classList.contains('noArrow'))
					{
						COUNT.classList._add('noArrow');
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
			const count = document.createElement('span');
			count.id = 'count';
			
			//
			COUNT._appendChild(COUNT.count = count);
			
			//
			const haveSeen = document.createElement('span');
			haveSeen.id = 'countInfoText';
			setHTML(haveSeen, ' have seen ', true);
			
			//
			COUNT._appendChild(COUNT.haveSeen = COUNT.infoText = haveSeen);
			
			//
			const link = document.createElement('a');
			link.id = 'self';
			link.target = '#';

			//
			setHTML(link, (isString(config.url, false) ? config.url : location.origin), true);
			
			//
			COUNT._appendChild(COUNT.link = COUNT.self = link);
			
			//
			COUNT.prepared = true;
		}
		
		//
		return dynamic.add('count', DEFAULT_COUNT_URL, DEFAULT_COUNT_DELAY, mod.count.callback, DEFAULT_MAX, DEFAULT_CALLBACKS, _throw);
	};
	
	mod.count.callback = (_event, _request, _dynamic) => {
		//
		const onload = () => {
			if(_request.statusClass !== 2)
			{
				return onfailure();
			}

			if(isNaN(_request.responseText))
			{
				return setHTML(COUNT.count, DEFAULT_NULL, COUNT);
			}
			else
			{
				count = Number(_request.responseText);
			}
			
			return setHTML(COUNT.count, count.toLocaleString(), COUNT);
		};
		
		const onfailure = () => {
			setHTML(COUNT, DEFAULT_NULL, true);
			
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

