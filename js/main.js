(function()
{

	//
	const DEFAULT_PATH = 'js';

	//
	const DEFAULT_CONSOLE_STYLE = true;
	const DEFAULT_CONSOLE_SIZE_BEFORE = '80%';
	const DEFAULT_CONSOLE_SIZE_AFTER = '120%';
	const DEFAULT_CONSOLE_COLOR_BEFORE = '#325a6e';
	const DEFAULT_CONSOLE_COLOR_AFTER = { log: null, info: '#5a7828', warn: '#be780a', error: '#b43c1e', debug: '#326e96' };
	const DEFAULT_CONSOLE_DEBUG_TO_LOG = true;

	//
	const DEFAULT_THROW = true;
	const DEFAULT_THROW_REQUIRE = false;
	const DEFAULT_CHARSET = 'utf-8';
	const DEFAULT_DEFER = true;
	const DEFAULT_TIMEOUT = 10000;
	const DEFAULT_AJAX_CALLBACKS = null;
	const DEFAULT_AJAX_CALLBACKS_TRUE = (1 | 2 | 4 | 8 | 16 | 32 | 64 | 128 | 256);
	const DEFAULT_AJAX_CALLBACKS_FALSE = (1 | 2 | 4);
	const DEFAULT_AJAX_CALLBACKS_NULL = (1 | 2);
	const DEFAULT_STACK_TRACE_LIMIT = 32;

	//
	Error.stackTraceLimit = DEFAULT_STACK_TRACE_LIMIT;

	//
	var SIZE = (DEFAULT_CONSOLE_SIZE_BEFORE || '10px');
	var COLOR = (DEFAULT_CONSOLE_COLOR_BEFORE || '#325a6e');

	const _log = console.log.bind(console);
	const _info = console.info.bind(console);
	const _warn = console.warn.bind(console);
	const _error = console.error.bind(console);
	const _debug = console.debug.bind(console);
	
	const getStyle = (_stream) => {
		var result = 'font-size: ' + SIZE + '; color: ';
		
		if(typeof COLOR === 'string')
		{
			result += COLOR;
		}
		else switch(_stream)
		{
			case 'log':
			case 'info':
			case 'warn':
			case 'error':
			case 'debug':
				result += (COLOR[_stream.toLowerCase()] || '');
				break;
			default:
				throw new Error('Invalid _stream argument');
		}
		
		return (result + ';');
	};
	
	const checkForStyle = (_args, _stream) => {
		if(! DEFAULT_CONSOLE_STYLE)
		{
			return null;
		}
		else if(typeof _args[0] === 'string' && _args[0].length > 0 && !_args[0].includes('%c'))
		{
			_args[0] = '%c' + _args[0];
			_args.splice(1, 0, getStyle(_stream));
			
			return true;
		}
		
		return false;
	};
	
	console.log = (... _args) => {
		checkForStyle(_args, 'log');
		return _log(... _args);
	};
	
	console._log = _log;
	
	console.info = (... _args) => {
		checkForStyle(_args, 'info');
		return _info(... _args);
	};
	
	console._info = _info;
	
	console.warn = (... _args) => {
		checkForStyle(_args, 'warn');
		return _warn(... _args);
	};
	
	console._warn = _warn;
	
	console.error = (... _args) => {
		checkForStyle(_args, 'error');
		return _error(... _args);
	};
	
	console._error = _error;
	
	console.debug = (... _args) => {
		checkForStyle(_args, 'debug');

		if(DEFAULT_CONSOLE_DEBUG_TO_LOG)
		{
			return _log(... _args);
		}
		
		return _debug(... _args);
	};
	
	console._debug = _debug;

	//
	sleep = (_delay) => {
		const end = (Date.now() + _delay);
		while(Date.now() < end) {};
		return _delay;
	};

	// provisorisch, vor numeric.js.
	isNumeric = (_value) => {
		if(isNumber(_value))
		{
			return true;
		}
		else if(typeof _value === 'bigint')
		{
			return true;
		}

		return false;
	};

	isNumber = (_value) => {
		if(typeof _value === 'number')
		{
			return (_value.valueOf() === _value.valueOf());
		}
		
		return false;
	};

	isInt = (_value) => {
		if(isNumber(_value))
		{
			return ((_value % 1) === 0);
		}

		return false;
	};

	isFloat = (_value) => {
		if(isNumber(_value))
		{
			return ((_value % 1) !== 0);
		}

		return false;
	};

	//
	__INIT = true;
	__START = Date.now();
	window.ready = false;

	window.addEventListener('ready', () => {
		window.ready = true;
		__TIME = ((__STOP = Date.now()) - __START);
	}, { once: true });

	//
	document.setFullScreen = (_value = true, _throw = DEFAULT_THROW) => {
		var result, element;

		if(_value = !!_value)
		{
			element = document.documentElement;

			result = (element.requestFullscreen ||
				element.webkitRequestFullscreen ||
				element.mozRequestFullScreen ||
				element.msRequestFullscreen);
		}
		else
		{
			element = document;

			result = (element.exitFullscreen ||
				element.webkitExitFullscreen ||
				element.mozCancelFullScreen ||
				element.msExitFullscreen);
		}
		
		if(typeof result !== 'function')
		{
			if(_throw)
			{
				throw new Error('Feature not available');
			}

			return null;
		}
		
		return result.call(element);
	};

	//
	window.addEventListener('DOMContentLoaded', (_event) => {
		//
		(HTML = document.documentElement).id = 'HTML';

		//
		if(! (HEAD = document.head))
		{
			HTML.appendChild(HEAD = document.createElement('head'));
		}

		HEAD.id = 'HEAD';

		if(! (BODY = document.body))
		{
			HTML.appendChild(BODY = document.createElement('body'));
		}
		
		BODY.id = 'BODY';

		//
		if(! (MAIN = document.getElementById('MAIN')))
		{
			BODY.appendChild(MAIN = document.createElement('main'));
		}

		MAIN.id = 'MAIN';

		//
		if(! (COPYRIGHT = document.getElementById('COPYRIGHT')))
		{
			BODY.appendChild(COPYRIGHT = document.createElement('div'));
		}

		COPYRIGHT.id = 'COPYRIGHT';

		//
		if(! (UPDATED = document.getElementById('UPDATED')))
		{
			BODY.appendChild(UPDATED = document.createElement('div'));
		}

		UPDATED.id = 'UPDATED';

		//
		if(! (INFO = document.getElementById('INFO')))
		{
			BODY.appendChild(INFO = document.createElement('div'));
		}

		INFO.id = 'INFO';

		//
		if(! (VERSION = document.getElementById('VERSION')))
		{
			BODY.appendChild(VERSION = document.createElement('div'));
		}

		VERSION.id = 'VERSION';

		//
		if(! (COUNTER = document.getElementById('COUNTER')))
		{
			BODY.appendChild(COUNTER = document.createElement('div'));
		}

		COUNTER.id = 'COUNTER';
	}, { once: true });

	//
	const ajaxCallbackIndices = [ 'load', 'progress', 'failure' ];

	//
	ajax = (... _args) => {
		if('hasLine' in navigator)
		{
			if(! navigator.hasLine)
			{
				return null;
			}
		}

		const options = Object.assign(... _args);
		var result;
		
		try
		{
			result = ajax.request(... _args);
		}
		catch(_error)
		{
			if(options.throw)
			{
				throw _error;
			}
			
			result = null;
		}
		
		return result;
	};

	//
	ajax.osd = (_method, _status, _status_text, _url, _callback) => {
		//
		if(! (isInt(_status) && _status >= 0))
		{
			return null;
		}
		else if(typeof _status_text !== 'string' || _status_text.length === 0)
		{
			_status_text = (_status.toString()[0] === '2' ? 'OK' : 'Error');
		}
		
		if(typeof _method !== 'string')
		{
			_method = '';
		}
		else
		{
			_method = _method.toLowerCase();
		}

		if(typeof _url !== 'string')
		{
			_url = '';
		}
		else if(Page)
		{
			_url = Page.renderHomePath(_url);
		}
		
		//
		const options = ajax.osd.getOptions();
		const textColor = (_status.toString()[0] === '2' ? '' : options.color.error);
		const statusColor = options.color.status;
		const urlColor = options.color.url;
		delete options.color;
		
		//
		var result = `<span style="font-size: ${options.fontSize.url}; color: ${urlColor};">${_url}</span><br>`;
		
		if(_method)
		{
			result += `<span style="font-size: ${options.fontSize.method}; color: ${textColor};">${_method}</span>`;
		}
		
		result += `<span style="color: ${textColor}; font-size: 60%;">[</span>`;
		result += `<span style="font-size: ${options.fontSize.status}; font-weight: bold; color: ${statusColor};">${_status}</span>`;
		result += `<span style="color: ${textColor}; font-size: 60%;">]</span>`;

		if(_status_text)
		{
			result += `<span style="font-size: ${options.fontSize.statusText}; color: ${textColor};"> ${_status_text}</span>`;
		}

		//
		osd(result, options, _callback, false);
		
		//
		return result;
	};
	
	ajax.osd.duration = 1200;
	ajax.osd.timeout = 3600;
	ajax.osd.fontSize = { status: '80%', statusText: '60%', method: '40%', url: '20%' };
	ajax.osd.color = { error: 'red', status: 'blue', url: 'rgb(96, 96, 96)' };
	
	ajax.osd.getOptions = () => {
		const result = { duration: null, timeout: null,
			fontSize: { status: null, statusText: null, method: null, url: null },
			color: { error: null, status: null, url: null }
		};
		
		if(document.hasVariable('ajax-osd-duration'))
		{
			result.duration = document.getVariable('ajax-osd-duration', true);
		}
		else
		{
			result.duration = ajax.osd.duration;
		}
		
		if(document.hasVariable('ajax-osd-timeout'))
		{
			result.timeout = document.getVariable('ajax-osd-timeout', true);
		}
		else
		{
			result.timeout = ajax.osd.timeout;
		}
		
		if(document.hasVariable('ajax-osd-font-size-status'))
		{
			result.fontSize.status = document.getVariable('ajax-osd-font-size-status');
		}
		else
		{
			result.fontSize.status = ajax.osd.fontSize.status;
		}
		
		if(document.hasVariable('ajax-osd-font-size-status-text'))
		{
			result.fontSize.statusText = document.getVariable('ajax-osd-font-size-status-text');
		}
		else
		{
			result.fontSize.statusText = ajax.osd.fontSize.statusText;
		}
		
		if(document.hasVariable('ajax-osd-font-size-method'))
		{
			result.fontSize.method = document.getVariable('ajax-osd-font-size-method');
		}
		else
		{
			result.fontSize.method = ajax.osd.fontSize.method;
		}

		if(document.hasVariable('ajax-osd-font-size-url'))
		{
			result.fontSize.url = document.getVariable('ajax-osd-font-size-url');
		}
		else
		{
			result.fontSize.url = ajax.osd.fontSize.url;
		}

		if(document.hasVariable('ajax-osd-color-error'))
		{
			result.color.error = document.getVariable('ajax-osd-color-error');
		}
		else
		{
			result.color.error = ajax.osd.color.error;
		}
		
		if(document.hasVariable('ajax-osd-color-status'))
		{
			result.color.status = document.getVariable('ajax-osd-color-status');
		}
		else
		{
			result.color.status = ajax.osd.color.status;
		}

		if(document.hasVariable('ajax-osd-color-url'))
		{
			result.color.url = document.getVariable('ajax-osd-color-url');
		}
		else
		{
			result.color.url = ajax.osd.color.url;
		}
		
		return result;
	};

	//	
	ajax.request = (... _args) => {
		const options = Object.assign(... _args);

		for(var i = 0; i < _args.length; ++i)
		{
			if(typeof _args[i] === 'string' && _args[i].length > 0)
			{
				options.url = _args.splice(i--, 1)[0];
			}
			else if(typeof _args[i] === 'function')
			{
				options.callback = _args.splice(i--, 1)[0];
			}
			else if(typeof _args[i] === 'boolean')
			{
				//options.async = _args.splice(i--, 1)[0];
				options.callbacks = _args.splice(i--, 1);
			}
			else if(_args[i] === null)
			{
				options.callbacks = null;
			}
			else if(isInt(_args[i]) && _args[i] >= 0)
			{
				if(typeof options.callbacks === 'boolean')
				{
					options.async = options.callbacks;
				}

				options.callbacks = _args.splice(i--, 1)[0];
			}
		}
		
		if(typeof options.throw !== 'boolean')
		{
			options.throw = DEFAULT_THROW;
		}

		if(typeof options.osd !== 'boolean')
		{
			options.osd = (document.getVariable('ajax-osd', true) && !__INIT);
		}

		if(typeof options.console !== 'boolean')
		{
			options.console = !__INIT;
		}

		if(! (isInt(options.callbacks) && options.callbacks >= 0))
		{
			if(typeof options.callbacks === 'boolean')
			{
				if(options.callbacks)
				{
					options.callbacks = DEFAULT_AJAX_CALLBACKS_TRUE;
				}
				else
				{
					options.callbacks = DEFAULT_AJAX_CALLBACKS_FALSE;
				}
			}
			else if(options.callbacks === null)
			{
				options.callbacks = DEFAULT_AJAX_CALLBACKS_NULL;
			}
			else if(typeof DEFAULT_AJAX_CALLBACKS === 'boolean')
			{
				if(DEFAULT_AJAX_CALLBACKS)
				{
					options.callbacks = DEFAULT_AJAX_CALLBACKS_TRUE;
				}
				else
				{
					options.callbacks = DEFAULT_AJAX_CALLBACKS_FALSE;
				}
			}
			else if(DEFAULT_AJAX_CALLBACKS === null)
			{
				options.callbacks = DEFAULT_AJAX_CALLBACKS_NULL;
			}
			else if(typeof DEFAULT_AJAX_CALLBACKS === 'number')
			{
				options.callbacks = DEFAULT_AJAX_CALLBACKS;
			}
			else
			{
				options.callbacks = DEFAULT_AJAX_CALLBACKS_NULL;
			}
		}

		if(typeof options.url !== 'string' || options.url.length === 0)
		{
			if(typeof options.path === 'string' && options.path.length > 0)
			{
				options.url = options.path;
			}
			else
			{
				throw new Error('Missing URL');
			}
		}

		delete options.path;
		
		options.url = require.resolve(options.url);

		if(typeof options.async !== 'boolean')
		{
			options.async = (typeof options.callback === 'function');
		}

		if(typeof options.callback !== 'function' || !options.async)
		{
			options.callback = null;
		}

		if(typeof options.data !== 'string')
		{
			options.data = null;
		}

		if(typeof options.method === 'string' && options.method.length > 0)
		{
			options.method = options.method.toUpperCase();
		}
		else
		{
			options.method = 'GET';
		}

		if(! options.async)
		{
			options.timeout = null;
		}
		else if(typeof options.timeout !== 'number' || options.timeout < 1)
		{
			options.timeout = DEFAULT_TIMEOUT;
		}

		if(typeof options.header !== 'object' || options.header === null)
		{
			options.header = {};
		}
		else for(const idx in options.headers)
		{
			if(typeof options.headers[idx] === 'number' || typeof options.headers[idx] === 'bigint')
			{
				options.headers[idx] = options.headers[idx].toString();
			}
			else if(typeof options.headers[idx] !== 'string')
			{
				delete options.headers[idx];
			}
		}

		if(typeof options.withCredentials !== 'boolean')
		{
			options.withCredentials = false;
		}

		if(typeof options.mime !== 'string' || options.mime.length === 0)
		{
			options.mime = null;
		}
		
		if(typeof options.responseType !== 'string')
		{
			options.responseType = '';
		}

		if(! (typeof options.username === 'string' && options.username.length > 0))
		{
			if(typeof options.user === 'string' && options.user.length > 0)
			{
				options.username = options.user;
			}
			
			delete options.user;
		}
		
		if(! (typeof options.password === 'string' && options.password.length > 0))
		{
			if(typeof options.pass === 'string' && options.pass.length > 0)
			{
				options.password = options.pass;
			}
			
			delete options.pass;
		}
		
		if(typeof options.token === 'string' && options.token.length > 0)
		{
			delete options.username;
			delete options.password;
		}
		else if(typeof options.username === 'string' && typeof options.password === 'string' && options.username.length > 0 && options.password.length > 0)
		{
			options.token = ajax.getAuthorization(options.username, options.password);
		}
		else
		{
			delete options.username;
			delete options.password;
			delete options.token;
		}
		
		if(typeof options.token === 'string' && options.token.length > 0)
		{
			options.headers.Authorization = ('Basic ' + options.token);
		}
		
		//
		//TODO/lengths.. range, etc..
		//
		
		if(options.async)
		{
			if(typeof options.callback !== 'function')
			{
				throw new Error('At least a regular callback function is necessary for .async');
			}
			
			if(typeof options.failureCallback === 'function' && typeof options.onfailure !== 'function')
			{
				options.onfailure = options.failureCallback;
			}
			
			if(typeof options.abortCallback === 'function' && typeof options.onabort !== 'function')
			{
				options.onabort = options.abortCallback;
			}
			
			if(typeof options.errorCallback === 'function' && typeof options.onerror !== 'function')
			{
				options.onerror = options.errorCallback;
			}
			
			if(typeof options.loadCallback === 'function' && typeof options.onload !== 'function')
			{
				options.onload = options.loadCallback;
			}
			
			if(typeof options.loadendCallback === 'function' && typeof options.onloadend !== 'function')
			{
				options.onloadend = options.loadendCallback;
			}
			
			if(typeof options.loadstartCallback === 'function' && typeof options.onloadstart !== 'function')
			{
				options.onloadstart = options.loadstartCallback;
			}
			
			if(typeof options.progressCallback === 'function' && typeof options.onprogress !== 'function')
			{
				options.onprogress = options.progressCallback;
			}
			
			if(typeof options.readystatechangeCallback === 'function' && typeof options.onreadystatechange !== 'function')
			{
				options.onreadystatechange = options.readystatechangeCallback;
			}
			
			if(typeof options.timeoutCallback === 'function' && typeof options.ontimeout !== 'function')
			{
				options.ontimeout = options.timeoutCallback;
			}
			
			if(typeof options.onfailure !== 'function')
			{
				delete options.onfailure;
			}
			
			if(typeof options.onabort !== 'function')
			{
				delete options.onabort;
			}
			
			if(typeof options.onerror !== 'function')
			{
				delete options.onerror;
			}
			
			if(typeof options.onload !== 'function')
			{
				delete options.onload;
			}
			
			if(typeof options.onloadend !== 'function')
			{
				delete options.onloadend;
			}
			
			if(typeof options.onloadstart !== 'function')
			{
				delete options.onloadstart;
			}
			
			if(typeof options.onprogress !== 'function')
			{
				delete options.onprogress;
			}
			
			if(typeof options.onreadystatechange !== 'function')
			{
				delete options.onreadystatechange;
			}
			
			if(typeof options.ontimeout !== 'function')
			{
				delete options.ontimeout;
			}
			
			delete options.failureCallback;
			delete options.abortCallback;
			delete options.errorCallback;
			delete options.loadCallback;
			delete options.loadendCallback;
			delete options.loadstartCallback;
			delete options.progressCallback;
			delete options.readystatechangeCallback;
			delete options.timeoutCallback;
		}
		else
		{
			delete options.callback;
			
			delete options.failureCallback;
			delete options.onfailure;
			
			delete options.abortCallback;
			delete options.onabort;
			
			delete options.errorCallback;
			delete options.onerror;
			
			delete options.loadCallback;
			delete options.onload;
			
			delete options.loadendCallback;
			delete options.onloadend;
			
			delete options.loadstartCallback;
			delete options.onloadstart;
			
			delete options.progressCallback;
			delete options.onprogress;
			
			delete options.readystatechangeCallback;
			delete options.onreadystatechange;
			
			delete options.timeoutCallback;
			delete options.ontimeout;
		}
		
		//
		var result;
		
		try
		{
			result = new XMLHttpRequest();
			result.start = Date.now();
			result.open(options.method, options.url, options.async, options.username, options.password);
		}
		catch(_error)
		{
			if(options.throw)
			{
				throw _error;
			}
			
			return null;
		}
		
		if(options.async)
		{
			if(typeof options.timeout === 'number')
			{
				result.timeout = options.timeout;
			}
			
			if(typeof options.responseType === 'string')
			{
				result.responseType = options.responseType;
			}
		}
		
		//
		result.withCredentials = options.withCredentials;
		
		//
		if(typeof options.mime === 'string')
		{
			result.overrideMimeType(options.mime);
		}
		
		//
		for(const idx in options.headers)
		{
			result.setRequestHeader(idx, options.headers[idx]);
		}
		

		//
		try
		{
			handleRequest(result, options).send(options.data);
		}
		catch(_error)
		{
			if(_options.throw !== false)
			{
				throw _error;
			}
			
			return null;
		}
		
		//
		if(! options.async)
		{
			//
			result.stop = Date.now();
			result.time = (result.stop - result.start);
			
			//
			responseStatusClass(result);
		}
		
		//
		return result;
	};

	Object.defineProperty(ajax, 'callbacks', { get: function()
	{
		const result = Object.create(null);

		result.load = 1;
		result.failure = 2;
		result.progress = 4;
		result.timeout = 8;
		result.error = 16;
		result.abort = 32;
		result.readystatechange = 64;
		result.loadend = 128;
		result.loadstart = 256;

		return result;
	}});
	
	ajax.getAuthorization = (_username, _password) => {
		if(typeof username !== 'string' || typeof _password !== 'string')
		{
			throw new Error('Invalid _username and/or _password argument (only Strings allowed)');
		}
		
		return btoa(_username + ':' + _password);
	};

	ajax.rangeSupport = (_callback, _url, _type = 'bytes') => {
		if(typeof _url !== 'string' || _url.length === 0)
		{
			_url = '/';
		}
		
		if(typeof _type !== 'string' || _type.length === 0)
		{
			_type = 'bytes';
		}
		else
		{
			_type = _type.toLowerCase();
		}
		
		if(typeof _callback !== 'function')
		{
			_callback = null;
		}
		
		const handle = (_e) => {
			var acceptRanges = _e.request.getResponseHeader('Accept-Ranges');
			var contentRange = _e.request.getResponseHeader('Content-Range');

			if(typeof acceptRanges === 'string')
			{
				acceptRanges = (acceptRanges.toLowerCase() === _type);
			}
			else
			{
				acceptRanges = false;
			}
			
			if(typeof contentRange === 'string')
			{
				contentRange = !!(contentRange.toLowerCase().startsWith(_type + ' '));
			}
			else
			{
				contentRange = false;
			}
			
			const result = (acceptRanges || contentRange);
			
			if(_callback)
			{
				_callback(result);
			}
			
			return (acceptRanges || contentRange);
		};

		const request = ajax({
			url: _url,
			method: 'HEAD',
			callback: (_callback ? handle : null),
			throw: false, console: false, osd: false });//, null, { method: 'HEAD', range: '0-0' }, null);

		if(! request)
		{
	throw new Error('DEBUG/TODO');
		}
		else if(_callback)
		{
			return;
		}
		
		return handle({ request });
	};

	ajax.size = (_url, _callback) => {
		if(typeof _url !== 'string')
		{
			throw new Error('Invalid _url argument');
		}
		else if(typeof _callback !== 'function')
		{
			_callback = null;
		}

		const handle = (_e) => {
			var result, error;
			
			if('type' in _e)
			{
				result = (_e.type === 'load');
			}
			else
			{
				result = true;
			}
			
			if(result)
			{
				if(_e.request.statusClass !== 2)
				{
					result = null;
					error = _e.request.status;
				}
				else
				{
					const length = _e.request.getResponseHeader('Content-Length');
					
					if(typeof length !== 'string' || length.length === 0 || isNaN(length))
					{
						error = true;
						result = length;
					}
					else
					{
						error = false;
						result = Number(length);
					}
				}
			}
			else
			{
				error = true;
				result = null;
			}
			
			if(_callback)
			{
				_callback(result, error, _e.request, _e.request.responseURL);
			}
			
			return result;
		};
		
		const request = ajax({
			url: _url,
			method: 'HEAD',
			callback: (_callback ? handle : null),
			throw: false, console: false, osd: false });
		
		if(! request)
		{
	throw new Error('DEBUG/TODO');
		}
		else if(_callback)
		{
			return;
		}

		return handle({ request });
	};

	ajax.exists = (_url, _callback) => {
		if(typeof _url !== 'string')
		{
			throw new Error('Invalid _url argument');
		}
		else if(typeof _callback !== 'function')
		{
			_callback = null;
		}

		const handle = (_e) => {
			var result;

			if('type' in _e)
			{
				result = (_e.type === 'load');
			}
			else
			{
				result = true;
			}

			if(result)
			{
				result = (_e.request.statusClass === 2);
			}

			if(_callback)
			{
				_callback(result, _e.request, _e.request.responseURL);
			}
			
			return result;
		};

		const request = ajax({
			url: _url,
			method: 'HEAD',
			callback: (_callback ? handle : null),
			throw: false, console: false, osd: false });

		if(! request)
		{
	throw new Error('DEBUG/TODO');
		}
		else if(_callback)
		{
			return;
		}
		
		return handle({ request });
	};

	const responseStatusClass = (_request) => {
		if(isNaN(_request.status))
		{
			return _request.statusClass = null;
		}

		var result = _request.status.toString()[0];

		if(!result || isNaN(result))
		{
			return _request.statusClass = null;
		}
		else
		{
			const url = require.resolve(_request.responseURL || _request.options.url);

			if(_request.options.osd !== false)
			{
				ajax.osd(_request.options.method, _request.status, _request.statusText, url, null);
			}

			if(_request.options.console !== false)
			{
				if(result[0] === '2')
				{
					console.info('[' + (_request.options.async ? 'async' : 'sync') + '] ' + _request.options.method.toLowerCase() + '(' + url + '): ' + _request.status + ' (' + (_request.statusText || 'OK') + ')');
				}
				else
				{
					console.error('[' + (_request.options.async ? 'async' : 'sync') + '] ' + _request.options.method.toLowerCase() + '(' + url + '): ' + _request.status + ' (' + (_request.statusText || 'Error') + ')');
				}
			}
		}

		return _request.statusClass = Number(result);
	};
	
	const handleRequest = (_request, _options) => {
		var result;
		
		try
		{
			result = handleRequest.handle(_request, _options);
		}
		catch(_error)
		{
			if(_options.throw !== false)
			{
				throw _error;
			}
			
			result = null;
		}
		
		return result;
	};
	
	handleRequest.handle = (_request, _options) => {
		//
		_request.options = _options;
		
		//
		if(! _options.async)
		{
			return _request;
		}
		
		//
		var hadProgress = false;
		var hadComputableProgress = null;
		var hasSize = false;
		var stopped = false;
		var size = -1;
		/*var lastLoaded = 0;
		var loaded = 0;*///see progress-event..

		_request.stop = () => {
			if(stopped)
			{
				return false;
			}
			
			return stopped = true;
		};
		
		//
		const prepareEvent = (_event) => {
			//
			_event.request = _request;
			_event.options = _options;
			
			_event.hadProgress = hadProgress;
			_event.hadComputableProgress = hadComputableProgress;
			_event.hasSize = hasSize;
			_event.size = size;
			
			_event.stop = _request.stop;
			_event.stopped = stopped;
			
			//_event.loaded = loaded;//see onprogress() etc..
			
			//
			if(enabledEvent(_event.type))
			{
				return _event;
			}

			return null;
		};

		const enabledEvent = (_name) => {
			if(! (isInt(_options.callbacks) && _options.callbacks >= 0))
			{
				return true;
			}
			else if(_options.callbacks === 0)
			{
				return false;
			}
			else if(! ((_name = _name.toLowerCase()) in ajax.callbacks))
			{
				return null;
			}

			return ((_options.callbacks & ajax.callbacks[_name]) > 0);
		};
		
		//
		const onfailure = (_event) => {
			if(prepareEvent(_event) === null)
			{
				return null;
			}
			
			_event.originalType = _event.original = _event.type;
			_event.type = 'failure';
			
			if(typeof _options.onfailure === 'function')
			{
				_options.onfailure(_event, _request, _options);
			}
			else// if(typeof _options.callback === 'function')
			{
				_options.callback(_event, _request, _options);
			}
		};
		
		//
		_request.addEventListener('abort', (_event) => {
			if(! stopped)
			{
				if(prepareEvent(_event) === null)
				{
					return null;
				}
				
				if(typeof _options.onabort === 'function')
				{
					_options.onabort(_event, _request, _options);
				}
				else// if(typeof _options.callback === 'function')
				{
					_options.callback(_event, _request, _options);
				}
				
				onfailure(_event);
			}
		});
		
		_request.addEventListener('error', (_event) => {
			if(! stopped)
			{
				if(prepareEvent(_event) === null)
				{
					return null;
				}
				
				if(typeof _options.onerror === 'function')
				{
					_options.onerror(_event, _request, _options);
				}
				else// if(typeof _options.callback === 'function')
				{
					_options.callback(_event, _request, _options);
				}
				
				onfailure(_event);
			}
		});
		
		_request.addEventListener('load', (_event) => {
			if(! hasSize && _request.statusClass === 2 && typeof _request.responseText === 'string')
			{
				hasSize = true;
				size = _request.responseText.length;
			}
			
			if(! stopped)
			{
				if(prepareEvent(_event) === null)
				{
					return null;
				}
				
				if(typeof _options.onload === 'function')
				{
					_options.onload(_event, _request, _options);
				}
				else// if(typeof _options.callback === 'function')
				{
					_options.callback(_event, _request, _options);
				}
			}
		});
		
		_request.addEventListener('loadend', (_event) => {
			_request.stop = Date.now();
			_request.time = (_request.stop - _request.start);
			
			if(! stopped)
			{
				if(prepareEvent(_event) === null)
				{
					return null;
				}
				
				if(typeof _options.onloadend === 'function')
				{
					_options.onloadend(_event, _request, _options);
				}
				else// if(typeof _options.callback === 'function')
				{
					_options.callback(_event, _request, _options);
				}
			}
		});
		
		_request.addEventListener('loadstart', (_event) => {
			if(! stopped)
			{
				if(prepareEvent(_event) === null)
				{
					return null;
				}
				
				if(typeof _options.onloadstart === 'function')
				{
					_options.onloadstart(_event, _request, _options);
				}
				else// if(typeof _options.callback === 'function')
				{
					_options.callback(_event, _request, _options);
				}
			}
		});
		
		_request.addEventListener('progress', (_event) => {
			//
			hadProgress = true;
			
			//
			if(_event.lengthComputable)
			{
				//
				hadComputableProgress = true;
				
				//
				if(! hasSize)
				{
					hasSize = true;
					total = _event.total;
				}
				
				//
				//_event.delta = ((lastLoaded = _event.loaded) - lastLoaded);
				
				//
			}
			else
			{
				//
				hadComputableProgress = false;
			}
			
			if(! stopped)
			{
				if(prepareEvent(_event) === null)
				{
					return null;
				}
				
				if(typeof _options.onprogress === 'function')
				{
					_options.onprogress(_event, _request, _options);
				}
				else// if(typeof _options.callback === 'function')
				{
					_options.callback(_event, _request, _options);
				}
			}
		});
		
		_request.addEventListener('readystatechange', (_event) => {
			if(_request.readyState === 2) // === XMLHttpRequest.HEADERS_RECEIVED
			{
				responseStatusClass(_request);
				
				if(! hasSize)
				{
					var contentLength = _request.getResponseHeader('Content-Length');
					
					if(typeof contentLength === 'string')
					{
						if(isNaN(contentLength = Number(contentLength)))
						{
							hasSize = false;
							size = null;
						}
						else
						{
							hadSize = true;
							size = contentLength;
						}
					}
					else
					{
						hasSize = false;
						size = null;
					}
				}
			}
			
			if(! stopped)
			{
				if(prepareEvent(_event) === null)
				{
					return null;
				}
				
				if(typeof _options.onreadystatechange === 'function')
				{
					_options.onreadystatechange(_event, _request, _options);
				}
				else// if(typeof _options.callback === 'function')
				{
					_options.callback(_event, _request, _options);
				}
			}
		});
		
		_request.addEventListener('timeout', (_event) => {
			if(! stopped)
			{
				if(prepareEvent(_event) === null)
				{
					return null;
				}
				
				if(typeof _options.ontimeout === 'function')
				{
					_options.ontimeout(_event, _request, _options);
				}
				else// if(typeof _options.callback === 'function')
				{
					_options.callback(_event, _request, _options);
				}
				
				onfailure(_event);
			}
		});
		
		//
		return _request;
	};
	
	//
	module = {
		id: undefined,
		exports: undefined
	};

	//
	library = (_id, _callback, _reload = true, _throw = DEFAULT_THROW_REQUIRE, _options = null) => {
		return require(_id, _callback, _reload, _throw, _options, true);
	};

	require = (_id, _callback, _reload = true, _throw = DEFAULT_THROW_REQUIRE, _options = null, _eval = false) => {
		if(typeof _callback !== 'function')
		{
			_callback = null;
		}

		if(typeof _reload !== 'boolean')
		{
			_reload = true;
		}

		if(typeof _throw !== 'boolean')
		{
			_throw = DEFAULT_THROW_REQUIRE;
		}

		if(typeof _eval !== 'boolean')
		{
			_eval = false;
		}

		if(Array.isArray(_id))
		{
			for(var i = 0, j = require.QUEUE.length; i < _id.length; ++i)
			{
				if(typeof _id[i] !== 'string' || _id[i].length === 0)
				{
					if(_throw)
					{
						throw new Error('Invalid _id[' + i + '] argument (not a non-empty String in Array)');
					}
				}
				else
				{
					require.QUEUE[j++] = _id[i];
				}
			}

			return require.QUEUE.length;
		}
		else if(typeof _id !== 'string')
		{
			if(_id === null)
			{
				return require.progress(_callback, _reload, _throw, _options, _eval);
			}
			else if(_throw)
			{
				throw new Error('Invalid _id argument (neither non-empty String nor Array)');
			}

			return null;
		}

		//
		const mark = (_id[_id.length - 1] === '!');
		var ext, type;

		if(mark)
		{
			_id = _id.slice(0, -1);
		}
		
		const lower = _id.toLowerCase();

		if(lower.endsWith('.json'))
		{
			ext = _id.slice(-5);
			type = 'json';
		}
		else if(lower.endsWith('.js'))
		{
			ext = _id.slice(-3);
			type = 'js';
		}
		else
		{
			_id += (ext = '.js');
			type = 'js';
		}

		if(__INIT)
		{
			_id = require.resolve(DEFAULT_PATH + '/' + _id);
		}
		else
		{
			_id = require.resolve(_id);
		}

		if(mark)
		{
			_id += '!';
		}

		//
		if(typeof require[type] !== 'function')
		{
			throw new Error('You can\'t require with type \'' + type + '\'');
		}
		else switch(type)
		{
			case 'js':
				if(! HEAD)
				{
					throw new Error('No <head> element available');
				}
				break;
			case 'json':
				break;
		}

		//
		return require[type](_id, _callback, _reload, _throw, _options, _eval);
	};

	//
	require.resolve = (_path) => {
		const url = new URL(_path, location.href);

		if(url.origin === location.origin)
		{
			return url.pathname;
		}
		
		return url.href;
	};

	require.resolve

	//
	require.QUEUE = [];
	require.CACHE = Object.create(null);

	//
	library.progress = (_callback, _reload = true, _throw = DEFAULT_THROW_REQUIRE, _options = null, _eval = false) => {
		return require.progress(_callback, _reload, _throw, _options, _eval);
	};

	require.progress = (_callback, _reload = true, _throw = DEFAULT_THROW_REQUIRE, _options = null, _eval = false) => {
		//
		if(typeof _callback !== 'function')
		{
			_callback = null;
		}

		if(typeof _reload !== 'boolean')
		{
			_reload = true;
		}

		if(typeof _throw !== 'boolean')
		{
			_throw = DEFAULT_THROW_REQUIRE;
		}

		if(typeof _eval !== 'boolean')
		{
			_eval = false;
		}

		if(require.QUEUE.length === 0)
		{
			if(_throw)
			{
				throw new Error('The \'require.QUEUE[]\' is empty');
			}

			return null;
		}

		const queue = [ ... require.QUEUE ];
		const length = queue.length;

		if(__INIT) for(var i = 0; i < queue.length; ++i)
		{
			if(! queue[i].startsWith(DEFAULT_PATH))
			{
				queue[i] = (DEFAULT_PATH + '/' + queue[i]);
			}
		}

		//
		var loaded = 0;
		var errors = 0;
		const result = new Array(length);

		const callback = (_event) => {
			if(_event.error)
			{
				return ++errors;
			}

			result[loaded++] = _event.module;
			result[_event.id] = _event.module;

			if(loaded >= length)
			{
				if(_callback)
				{
					_callback({ loaded, errors,
						modules: result,
						ids: queue,
						error: (errors > 0)
					});
				}
			}
		};

		//
		while(require.QUEUE.length > 0)
		{
			require(require.QUEUE.shift(), callback, '', _reload, _throw, _options, _eval);
		}

		//
		return length;
	};

	//
	module = {
		exports: undefined,
		id: null
	};

	//
	library.js = (_id, _callback, _reload = true, _throw = DEFAULT_THROW_REQUIRE, _options = null) => {
		return require.js(_id, _callback, _reload, _throw, _options, true);
	};

	require.js = (_id, _callback, _reload = true, _throw = DEFAULT_THROW_REQUIRE, _options = null, _eval = false) => {
		//
		if(typeof _callback !== 'function')
		{
			_callback = null;
		}

		//
		var defer;

		if(_id[_id.length - 1] === '!')
		{
			_id = _id.slice(0, -1);
			defer = false;
		}
		else
		{
			defer = DEFAULT_DEFER;
		}

		//
		if(_id in require.CACHE)
		{
			const res = require.CACHE[_id];

			if(_reload)
			{
				if(typeof res === 'object' && res !== null)
				{
					if(! res.__EVAL)
					{
						if(res.parentNode && res.parentNode === HEAD)
						{
							res.parentNode.removeChild(res, null);
						}
						else
						{
							if(_throw)
							{
								throw new Error('Unexpected .parentNode');
							}

							return undefined;
						}
					}
				}

				delete require.CACHE[_id];
				console.warn('[' + (_callback ? 'async' : 'sync') + '] require(' + _id + '): cache pruned');
			}
			else
			{
				console.info('[' + (_callback ? 'async' : 'sync') + '] require(' + _id + '): loaded from cache');

				if(_callback)
				{
					_callback({ type: 'require', id: _id,
						type: 'js', cached: true,
						module: res,
						error: false
					}, res);
				}

				return res;
			}
		}

		//
		var callback;
		var result;

		if(_eval)
		{
			//
			const handle = (_request, _event = null) => {
				if(_request.statusClass !== 2)
				{
					console.error('[' + (_callback ? 'async' : 'sync') + '] require(' + _id + '): http error [' +  _request.status + ']' + (_request.statusText ? ': ' + _request.statusText : ''));

					if(_throw)
					{
						throw new Error('Unable to require(\'' + _request.responseURL + '\' (HTTP ' + _request.status + ': ' + _request.statusText + ')');
					}

					return _request.status;
				}

				//
				var res = _request.responseText;

				//
				const originalModule = module;
				module = { id: _id };
				var doThrow = null;

				//
				try
				{
					module.exports = undefined;
					res = eval.call(null, res);

					if(typeof module.exports !== 'undefined')
					{
						res = module.exports;
					}
					
					module.exports = res;
					require.CACHE[_id] = res;
					require.CACHE[_id].__EVAL = true;
					
					console.info('[ ' + (_callback ? 'async' : 'sync') + '] require(' + _id + '): ok');
				}
				catch(_error)
				{
					if(_throw)
					{
						doThrow = _error;
					}
					
					delete require.CACHE[_id];
					res = undefined;
					console.error('[' + (_callback ? 'async' : 'sync') + '] require(' + _id + '): couldn\'t evaluate');
				}
				finally
				{
					module = originalModule;
				}

				if(doThrow !== null)
				{
					throw doThrow;
				}
				
				return res;
			};
			
			//
			callback = (_event, _request) => {
				var res, err;
				
				if(_event.type === 'load')
				{
					res = handle(_request, _event);
					err = null;
				}
				else if(_event.type === 'failure')
				{
					res = undefined;
					err = new Error('Unable to require(\'' + _request.responseURL + '\' (HTTP ' + _request.status + ': ' + _request.statusText + ')');
				}
				
				if(err)
				{
					if(_throw)
					{
						throw err;
					}
					else if(_callback)
					{
						_callback({ id: _id, url: _request.responseURL,
							type: 'js', module: null, error: err,
							request: _request, start: _request.start, stop: _request.stop, time: _request.time,
							status: _request.status, statusClass: _request.statusClass, statusText: _request.statusText
						});
					}
				}
				else if(_callback)
				{
					_callback({ id: _id, url: _request.responseURL,
						type: 'js', module: res, error: null,
						request: _request, start: _request.start, stop: _request.stop, time: _request.time,
						status: _request.status, statusClass: _request.statusClass, statusText: _request.statusText }, res);
				}
				
				return res;
			};

			//
			result = ajax({ url: _id,
				callback: (_callback ? callback : null),
				mime: 'application/javascript',
				console: false, osd: false });

			if(!_callback)
			{
				result = handle(result, null);
			}

			return result;
		}

		//
		callback = (_event) => {
			result.removeEventListener('load', callback);
			result.removeEventListener('error', callback);

			result.stop = Date.now();
			result.time = (result.stop - result.start);

			const onload = () => {
				//
				require.CACHE[_id] = result;
				require.CACHE[_id].__EVAL = false;

				//
				console.info('[' + (_callback ? 'async' : 'sync') + '] require(' + _id + '): ok');

				//
				if(_callback)
				{
					_callback({ id: _id,
						type: 'js',
						module: result,
						error: false,
						start: result.start, stop: result.stop, time: result.time
					}, result);
				}

				//
				if(typeof window.emit === 'function')
				{
					window.emit('require', {
						type: 'require',
						id: _id, type: 'js',
						module: result,
						start: result.start, stop: result.stop, time: result.time
					});

					window.emit(_id, {
						type: 'require',
						id: _id, type: 'js',
						module: result,
						start: result.start, stop: result.stop, time: result.time
					});
				}
			};

			const onerror = () => {
				//
				result.parentNode.removeChild(result);
				delete require.CACHE[_id];

				//
				console.error('[' + (_callback ? 'async' : 'sync') + '] require(' + _id + '): error loading <script>');

				//
				if(_throw)
				{
					throw new Error('Unable to require(\'' + _id + '\'');
				}
				else if(_callback)
				{
					_callback({ id: _id,
						type: 'js',
						module: null,
						error: true,
						start: result.start,
						stop: result.stop,
						time: result.time
					});
				}
			};

			switch(_event.type)
			{
				case 'load':
					return onload();
				case 'error':
					return onerror();
				default:
					return null;
			}
		};

		result = document.createElement('script');

		result.addEventListener('load', callback, { once: true });
		result.addEventListener('error', callback, { once: true });

		result.async = false;
		result.defer = !!defer;

		result.start = Date.now();
		result.charset = DEFAULT_CHARSET;
		result.src = _id;
		result.id = _id;
		result.name = 'required';

		//
		HEAD.appendChild(result);

		//
		return;
	};

	require.json = (_id, _callback, _reload = true, _throw = DEFAULT_THROW_REQUIRE, _options, _eval) => {
		_eval = null;

		if(typeof _callback !== 'function')
		{
			_callback = null;
		}

		if(_id[_id.length - 1] === '!')
		{
			_id = _id.slice(0, -1);
		}

		if(_id in require.CACHE)
		{
			if(_reload)
			{
				delete require.CACHE[_id];
				console.warn('[' + (_callback ? 'async' : 'sync') + '] require(' + _id + '): cache pruned');
			}
			else
			{
				const res = require.CACHE[_id];

				if(_callback)
				{
					_callback({ id: _id,
						type: 'json', module: res,
						request: null, error: false }, res);
				}

				if(typeof window.emit === 'function')
				{
					window.emit('require', { type: 'require', id: _id, type: 'json', module: res });
					window.emit(_id, { type: 'require', id: _id, type: 'json', module: res });
				}

				console.info('[' + (_callback ? 'async' : 'sync') + '] require(' + _id + '): loaded from cache');
				return res;
			}
		}

		const handle = (_event, _request = _event.request) => {
			var module, error;
			
			if(_event.type === 'failure' || result.statusClass !== 2)
			{
				delete require.CACHE[_id];

				console.error('[' + (_callback ? 'async' : 'sync') + '] require(' + _id + '): error occured on request');
				error = new Error('Couldn\'t fetch \'' + _id + '\' (HTTP ' + result.status + ': ' + result.statusText + ')');
				
				if(_throw)
				{
					throw error;
				}
				else
				{
					module = undefined;
				}
			}
			else try
			{
				require.CACHE[_id] = module = JSON.parse(result.responseText);
				error = null;
				console.info('[' + (_callback ? 'async' : 'sync') + '] require(' + _id + '): ok');
			}
			catch(_error)
			{
				console.error('[' + (_callback ? 'async' : 'sync') + '] require(' + _id + '): couldn\'t parse JSON data');

				if(_throw)
				{
					throw _error;
				}
				else
				{
					error = _error;
					module = undefined;
				}
			}

			if(_callback)
			{
				_callback({ id: _id, type: 'json', module, data: result.responseText, error, request: _request, time: _request.time,
					start: _request.start, stop: _request.stop, status: _request.status, statusClass: _request.statusClass, statusText: _request.statusText }, module);
			}

			if(! error && typeof window.emit === 'function')
			{
				window.emit('require', { type: 'require', id: _id, type: 'json', module, data: result.responseText, error: null, request: _request,
					start: _request.start, stop: _request.stop, time: _request.time,
					status: _request.status, statusClass: _request.statusClass, statusText: _request.statusText });

				window.emit(_id, { type: 'require', id: _id, type: 'json', module, data: result.responseText, error: null, request: _request,
					start: _request.start, stop: _request.stop, time: _request.time,
					status: _request.status, statusClass: _request.statusClass, statusText: _request.statusText });
			}

			return module;
		};
		
		const callback = (_event, _request = _event.request) => {
			switch(_event.type)
			{
				case 'failure':
				case 'load':
					return handle(_event, _request);
			}
		};

		var result = ajax(Object.assign({}, _options, {
			url: _id,
			callback: (_callback ? callback : null),
			mime: 'application/json',
			console: false, osd: false
		}));

		if(! _callback)
		{
			return handle({ type: 'none' }, result);
		}

		return result;
	};

	require.reset = require.clear = () => {
		//TODO/
		//require.CACHE = Object.create(null);
		//and all <script> to be removed..
		//etc.?
	};

	//
	const afterAutoload = (_autoload, _event, _event_original) => {
		setTimeout(() => {
			__INIT = null;
			window.emit('ready', { type: 'ready', autoload: _autoload, config });
			__INIT = false;
			SIZE = DEFAULT_CONSOLE_SIZE_AFTER;
			COLOR = DEFAULT_CONSOLE_COLOR_AFTER;
		}, 0);
	};

	const onAutoload = (_event) => {
		//
		if(! Array.isArray(_event.module))
		{
			throw new Error('Invalid \'autoload.json\'');
		}
		else
		{
			require(_event.module);
			require(null, (_e) => {
				afterAutoload(_event.module, _e, _event);
			});
		}
	};

	const onConfig = () => {
		//
		if(typeof config !== 'object' || config === null)
		{
			throw new Error('Couldn\'t load \'config.json\'');
		}

		//
		if(config.behavior.hideAddressBar)
		{
			window.addEventListener('ready', () => {
				setTimeout(() => {
					window.scrollTo(0, 1);
				}, 0);
			}, { once: true });
		}

		//
		/*if(typeof config.behavior.fullScreen === 'boolean')
		{
			document.setFullScreen(config.behavior.fullScreen);
		}*/

		//
		require('autoload.json', onAutoload);
	};

	//
	window.addEventListener('load', (_event) => {
		require('js/event.js', (_ev1) => {
			require('config.json!', (_ev2) => {
				if(typeof _ev2.module === 'object' && _ev2.module !== null)
				{
					window.emit('config', { type: 'config', config: (config = _ev2.module) });
			
					require('network!', () => {
						require('init!', onConfig);
					});
				}
				else
				{
					throw new Error('Couldn\'t load \'config.json\'');
				}
			});
		});
	}, { once: true });

})();

