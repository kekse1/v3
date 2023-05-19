(function()
{

	//
	const DEFAULT_THROW = true;
	const DEFAULT_BUBBLES = true;
	const DEFAULT_CANCELABLE = true;
	const DEFAULT_COMPOSED = true;
	const DEFAULT_PASSIVE = false;
	//const DEFAULT_NEXT_FRAME = false;

	//
	Event.create = Event.createTarget = () => {
		return Object.create(EventTarget.prototype);
	};

	//
	Event.initialize = (_name, _options, _which) => {
		//
		if(typeof _name !== 'string' || _name.length === 0)
		{
			throw new Error('Invalid _name argument');
		}
		else
		{
			_name = _name.toLowerCase();
		}

		//
		if(typeof _options !== 'object' || _options === null)
		{
			_options = {};
		}

		//
		if(typeof _options.bubbles !== 'boolean')
		{
			_options.bubbles = DEFAULT_BUBBLES;
		}

		if(typeof _options.cancelable !== 'boolean')
		{
			_options.cancelable = DEFAULT_CANCELABLE;
		}

		if(typeof _options.composed !== 'boolean')
		{
			_options.composed = DEFAULT_COMPOSED;
		}

		if(typeof _options.passive !== 'boolean')
		{
			_options.passive = DEFAULT_PASSIVE;
		}

		//
		if(typeof _which === 'undefined' || _which === null)
		{
			_which = Event;
		}
		else if(typeof _which === 'boolean')
		{
			_which = (_which ? CustomEvent : Event);
		}
		else if(typeof _which === 'string' && _which.length > 0)
		{
			if((_which in window) && window[_which].name.endsWith('Event'))
			{
				_which = window[_which];
			}
			else if(((_which.capitalize() + 'Event') in window) && window[_which.capitalize() + 'Event'].name.endsWith('Event'))
			{
				_which = window[_which.capitalize() + 'Event'];
			}
			else
			{
				throw new Error('Event not found');
				_which = CustomEvent;
			}
		}
		else if(! (_which && _which.name && (_which.name.endsWith('Event') || (_which.constructor && _which.constructor.name.endsWith('Event')))))
		{
			throw new Error('No matching _which (maybe \'Event\' or \'CustomEvent\'?)');
		}

		//
		const result = new _which(_name, _options);

		for(const idx in _options)
		{
			try
			{
				result[idx] = _options[idx];
			}
			catch(_error)
			{
				continue;
			}
		}

		return result;
	};

	Event.emit = (_element = DEFAULT_ELEMENT, _event, _options, _which/*, _next_frame = DEFAULT_NEXT_FRAME*/) => {
		//
		if(! _element)
		{
			throw new Error('Invalid _element argument');
		}

		if(typeof _options !== 'object' || _options === null)
		{
			_options = {};
		}
		
		if(typeof _event === 'string' && _event.length > 0)
		{
			_event = Event.initialize(_event, _options, _which);
		}
		else if((typeof _event === 'object' && _event !== null) && _event.name.endsWith('Event'))
		{
			_event.assign(_options);
		}
		else
		{
			throw new Error('Invalid _event argument');
		}

		//
		if(typeof _options.type === 'string' && _options.type !== _event.type)
		{
			//
			//todo/check if(_event.type) is already defined here!
			//
			if(typeof (_event.originalType = _event.type) !== 'string' || _event.originalType.length === 0)
			{
				throw new Error('DEBUG (is _event.type defined here??)');
			}

			Object.defineProperty(_event, 'type', { value: _options.type });
		}
		else
		{
			_event.originalType = null;
		}

		//
		/*if(typeof _options.nextFrame === 'boolean')
		{
			_next_frame = _options.nextFrame;
		}
		else if(typeof _next_frame === 'boolean')
		{
			_options.nextFrame = _next_frame;
		}
		else
		{
			_options.nextFrame = _next_frame = DEFAULT_NEXT_FRAME;
		}

		//
		if(_next_frame)
		{
			return setTimeout(() => {
				//EventTarget.prototype.dispatchEvent.call(_element, _event);
				_element.dispatchEvent(_event);
			}, 0);
		}

		//return EventTarget.prototype.dispatchEvent.call(_element, _event);
		return _element.dispatchEvent(_event);*/
		call(() => {
			return _element.dispatchEvent(_event);
		});
	};

	Event.stop = (_event, _prevent_default = true, _stop_propagation = true, _throw = DEFAULT_THROW) => {
		try
		{
			if(_prevent_default)
			{
				_event.preventDefault();
			}

			if(_stop_propagation)
			{
				_event.stopPropagation();
			}
		}
		catch(_error)
		{
			if(_throw)
			{
				throw _error;
			}
		}
		finally
		{
			return false;
		}
	};

	//
	Object.defineProperty(EventTarget.prototype, 'emit', { value: function(_event, _options, _which/*, _next_frame = DEFAULT_NEXT_FRAME*/)
	{
		return Event.emit(this, _event, _options, _which/*, _next_frame*/);
	}});

	Object.defineProperty(Event.prototype, 'stop', { value: function(_prevent_default = true, _stop_propagation = true, _throw = DEFAULT_THROW)
	{
		return Event.stop(this, _prevent_default, _stop_propagation, _throw);
	}});

	//
	createEventTarget = Event.create.bind(Event);
	createEvent = Event.initialize.bind(Event);
	emitEvent = Event.emit.bind(Event);

	//

})();
