(function()
{

	//
	const DEFAULT_THROW = true;

	const DEFAULT_PARENT = document.documentElement;

	const DEFAULT_DURATION = 1400;
	const DEFAULT_TIMEOUT = 1800;
	const DEFAULT_DELAY = 0;

	//
	osd = OSD = Box.OSD = (_data, _options, _callback, _throw = DEFAULT_THROW) => {
		if(typeof _data !== 'string' || _data.length === 0)
		{
			return osd.close(_options, _callback, _throw);
		}

		return osd.open(_options, _data, _callback, _throw);
	};

	//
	osd.open = (_options, _data, _callback, _throw = DEFAULT_THROW) => {
		if(typeof _data !== 'string' || _data.length === 0)
		{
			return osd.close(_options, _callback, _throw);
		}

		var result;

		if(osd.box === null)
		{
			result = create(_options, _data, _callback, _throw);
		}
		else
		{
			result = update(_options, _data, _callback, _throw);
		}

		return result;
	};

	osd.close = (_options, _callback, _throw = DEFAULT_THROW) => {
		if(osd.box === null)
		{
			return null;
		}
		
		return destroy(_options, _callback, _throw);
	};

	//
	const create = (_options, _data, _callback, _throw = DEFAULT_THROW) => {
		if(typeof _data !== 'string')
		{
			if(_throw)
			{
				throw new Error('Invalid _data argument');
			}

			return null;
		}
		else if(_data.length === 0)
		{
			return destroy(_options, _callback, _throw);
		}
		else if(osd.box !== null)
		{
			return update(_options, _data, _callback, _throw);
		}
		else if(osd.timeout !== null)
		{
			clearTimeout(osd.timeout);
			osd.timeout = null;
		}

		const callback = (_e) => {
			//
			_options = getOptions(_options, result);

			//
			osd.box = result;
			osd.timeout = setTimeout(() => { destroy(); }, _options.timeout);
			
			const cb = (_e, _f) => {
				delete result.IN;
				call(_callback, { type: 'create', event: _e, finish: _f, box: result, this: result, timeout: _options.timeout, duration: _options.duration, delay: 0 }, _f, result);
				window.emit('osd', { type: 'create', event: _e, finish: _f, timeout: _options.timeout, duration: _options.duration, delay: 0 });
			};

			result.IN = result.animate({
				opacity: [ '1' ],
				transform: [ 'scale(0)', 'scale(1.3)', 'scale(0.7)', 'scale(1)' ]
			}, {
				duration: _options.duration, delay: 0
			}, cb);

			return result;
		};

		const result = Box.create({ parent: DEFAULT_PARENT, id: 'OSD', innerHTML: _data, opacity: '0', name: 'osd' });
		DEFAULT_PARENT.appendChild(result, null, callback)
		return osd.box = result;
	};

	const getOptions = (_options, _element) => {
		if(! isObject(_options, true))
		{
			_options = {};
		}

		if(! (isInt(_options.duration) && _options.duration >= 0))
		{
			_options.duration = (_element ? _element.getVariable('duration', true) : DEFAULT_DURATION);
		}

		if(! (isInt(_options.timeout) && _options.timeout >= 0))
		{
			_options.timeout = (_element ? _element.getVariable('timeout', true) : DEFAULT_TIMEOUT);
		}

		return _options;
	};

	const destroy = (_options, _callback, _throw = DEFAULT_THROW) => {
		if(osd.box === null)
		{
			return null;
		}
		else if(osd.timeout !== null)
		{
			clearTimeout(osd.timeout);
			osd.timeout = null;
		}
		
		const box = osd.box;

		const cb = (_e, _f) => {
			delete box.OUT;

			if(_f)
			{
				if(box.parentNode)
				{
					box.parentNode.removeChild(box, null, () => {
						call(_callback, { type: 'destroy', event: _e, finish: _f, box, this: box, timeout: _options.timeout, duration: _options.duration, delay: 0 }, _f, box);
					});
				}
				else
				{
					call(_callback, { type: 'destroy', event: _e, finish: _f, box, this: box, timeout: _options.timeout, duration: _options.duration, delay: 0 }, _f, box);
				}

				osd.box = null;
			}
			else
			{
				call(_callback, { type: 'destroy', event: _e, finish: _f, box, this: box, timeout: _options.timeout, duration: _options.duration, delay: 0 }, _f, box);
			}

			window.emit('osd', { type: 'destroy', event: _e, finish: _f });
		};

		if(box.OUT)
		{
			//TODO?cb()?!??
			return box;
		}
		else
		{
			_options = getOptions(_options, osd.box);
		}
		
		const animate = () => {
			return box.OUT = box.animate({
				opacity: [ '0' ],
				transform: [ null, 'scale(0.7)', 'scale(1.3)', 'scale(0)' ]
			}, {
				duration: _options.duration, delay: 0
			}, cb);
		};
		
		if(box.IN)
		{
			box.IN.stop(() => {
				animate();
			});
		}
		else
		{
			animate();
		}

		return box;
	};

	const update = (_options, _data, _callback, _throw = DEFAULT_THROW) => {
		if(typeof _data !== 'string' || _data.length === 0)
		{
			return destroy(_options, _callback, _throw);
		}
		else if(osd.box === null)
		{
			return create(_options, _data, _callback, _throw);
		}
		else if(osd.timeout !== null)
		{
			clearTimeout(osd.timeout);
			osd.timeout = null;
		}

		//
		_options = getOptions(_options, osd.box);

		//
		const box = osd.box;

		box.innerHTML = _data;
		osd.timeout = setTimeout(() => { destroy(); }, _options.timeout);

		//
		const cb = (_e, _f) => {
			delete box.IN;
			call(_callback, { type: 'update', event: _e, finish: _f, box, this: box, timeout: _options.timeout, duration: _options.duration, delay: 0 }, _f, box);
			window.emit('osd', { type: 'update', event: _e, finish: _f, timeout: _options.timeout, duration: _options.duration, delay: 0 });
		};
		
		if(box.IN)
		{
			return box;
		}
		
		const animate = () => {
			return box.IN = box.animate({
				opacity: [ null, '1' ],
				transform: [ null, 'scale(1)' ]
			}, {
				duration: _options.duration, delay: 0
			}, cb);
		};
		
		if(box.OUT)
		{
			box.OUT.stop(() => {
				animate();
			});
		}
		else
		{
			animate();
		}

		return box;
	};

	//
	osd.box = null;
	osd.timeout = null;
	
	//
	
})();
