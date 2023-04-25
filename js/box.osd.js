(function()
{

	//
	const DEFAULT_THROW = true;
	const DEFAULT_PARENT = document.documentElement;

	//
	osd = OSD = Box.OSD = (_data, _callback, _throw = DEFAULT_THROW) => {
		if(typeof _data !== 'string' || _data.length === 0)
		{
			return osd.close(_callback, _throw);
		}

		return osd.open(_data, _callback, _throw);
	};

	//
	osd.open = (_data, _callback, _throw = DEFAULT_THROW) => {
		if(typeof _data !== 'string' || _data.length === 0)
		{
			return osd.close(_callback, _throw);
		}

		var result;

		if(osd.box === null)
		{
			result = create(_data, _callback, _throw);
		}
		else
		{
			result = update(_data, _callback, _throw);
		}

		return result;
	};

	osd.close = (_callback, _throw = DEFAULT_THROW) => {
		if(osd.box === null)
		{
			return null;
		}
		
		return destroy(_callback, _throw);
	};

	//
	const create = (_data, _callback, _throw = DEFAULT_THROW) => {
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
			return destroy(_callback, _throw);
		}
		else if(osd.box !== null)
		{
			return update(_data, _callback, _throw);
		}
		else if(osd.timeout !== null)
		{
			clearTimeout(osd.timeout);
			osd.timeout = null;
		}
		
		const callback = (_e) => {
			osd.box = result;
			osd.timeout = setTimeout(() => { destroy(); }, result.getVariable('timeout', true));
			
			const cb = (_e, _f) => {
				delete result.IN;
				call(_callback, { type: 'create', event: _e, finish: _f, box: result, this: result, timeout: result.getVariable('timeout', true) }, _e, _f, result);
			};

			result.IN = result.animate({
				opacity: [ '1' ],
				transform: [ 'scale(0)', 'scale(1.3)', 'scale(0.7)', 'scale(1)' ]
			}, {
				duration: result.getVariable('duration', true), delay: 0
			}, cb);

			return result;
		};

		const result = Box.create({ parent: DEFAULT_PARENT, id: 'OSD', innerHTML: _data, opacity: '0', name: 'osd' });
		DEFAULT_PARENT.appendChild(result, null, callback)
		return osd.box = result;
	};

	const destroy = (_callback, _throw = DEFAULT_THROW) => {
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
						call(_callback, { type: 'destroy', event: _e, finish: _f, box, this: box }, _e, _f, box);
					});
				}
				else
				{
					call(_callback, { type: 'destroy', event: _e, finish: _f, box, this: box }, _e, _f, box);
				}

				osd.box = null;
			}
			else
			{
				call(_callback, { type: 'destroy', event: _e, finish: _f, box, this: box }, _e, _f, box);
			}
		};

		if(box.OUT)
		{
			return box;
		}
		
		const animate = () => {
			return box.OUT = box.animate({
				opacity: [ '0' ],
				transform: [ null, 'scale(0.7)', 'scale(1.3)', 'scale(0)' ]
			}, {
				duration: osd.box.getVariable('duration', true), delay: 0
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

	const update = (_data, _callback, _throw = DEFAULT_THROW) => {
		if(typeof _data !== 'string' || _data.length === 0)
		{
			return destroy(_callback, _throw);
		}
		else if(osd.box === null)
		{
			return create(_data, _callback, _throw);
		}
		else if(osd.timeout !== null)
		{
			clearTimeout(osd.timeout);
			osd.timeout = null;
		}

		//
		const box = osd.box;

		box.innerHTML = _data;
		osd.timeout = setTimeout(() => { destroy(); }, box.getVariable('timeout', true));

		//
		const cb = (_e, _f) => {
			delete box.IN;
			call(_callback, { type: 'update', event: _e, finish: _f, box, this: box }, _e, _f, box);
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
				duration: box.getVariable('duration', true), delay: 0
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
