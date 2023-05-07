// wichtig: 'Page.Column'-klasse und die inhalts-einteilung dynamisch an (wnd-/*parent*-)groesze angepasst,
// w/ dynamic change @ window.onload(), etc...
//
(function()
{

	//
	const DEFAULT_THROW = true;
	const DEFAULT_CONTEXT = null;
	
	//
	Page = class Page
	{
		static getPath(_link, _throw = DEFAULT_THROW)
		{
			if(! isString(_link, false))
			{
				if(_throw)
				{
					throw new Error('Invalid _link argument');
				}
				
				return null;
			}
			else if(_link[0] === '#')
			{
				return _link;
			}
			else if(_link.startsWith('javascript:'))
			{
				return _link;
			}
			else if(_link[0] === '/' || _link.startsWith('./') || _link.startsWith('../'))
			{
				return _link;
			}
			else if(location.isURL(_link))
			{
				return _link;
			}
			else if(_link[0] === '~')
			{
				const homeConfig = Page.checkHomeConfig();
				
				if(homeConfig)
				{
					var link = homeConfig.path + '/' + _link.substr(1);

					if(homeConfig.extension)
					{
						if(typeof homeConfig.extension === 'string')
						{
							homeConfig.extension = [ homeConfig.extension ];
						}

						var found = false;

						for(var i = 0; i < homeConfig.extension.length; ++i)
						{
							if(link.endsWith(homeConfig.extension[i]))
							{
								found = true;
								break;
							}
						}

						if(! found)
						{
							//
							//verlass auf '.htaccess/httpd-conf' @ 'DirectoryIndex'! ^_^
							//=> set to 'main.html main.txt' first! :)~
							//
							link += '/';

							/*if(link[link.length - 1] === '.')
							{
								link += homeConfig.extension[0].substr(1);
							}
							else
							{
								link += homeConfig.extension[0];
							}*/
						}
					}

					return link.replaces('//', '/');
				}
				/*else if(! DEFAULT_PAGES_FALLBACK)
				{
					if(_throw)
					{
						throw new Error('Missing/invalid config.home configuration, etc.');
					}
					
					return null;
				}*/
			}
			
			return _link;
		}
		
		static checkHomeConfig()
		{
			if(! isObject(config.home))
			{
				return null;
			}
			else if(! config.home.enabled)
			{
				return null;
			}
			else if(typeof config.home.path !== 'string')
			{
				return null;
			}
			
			const result = Object.create(null);
			result.path = config.home.path;
			
			if(typeof config.home.extension === 'string')
			{
				config.home.extension = [ config.home.extension ];
			}

			if(isArray(config.home.extension, false))
			{
				for(var i = 0; i < config.home.extension.length; ++i)
				{
					if(config.home.extension[i][0] !== '.')
					{
						config.home.extension[i] = '.' + config.home.extension[i];
					}
				}
				
				result.extension = config.home.extension;
			}
			else
			{
				result.extension = config.home.extension = [];
			}
			
			return result;
		}

		static get(_link, _target = Page.target, _callback, _options, _type = document.getVariable('page-fallback-type'), _animate = document.getVariable('page-data-duration', true), _delay = document.getVariable('data-delay', true), _delete_mul = document.getVariable('page-data-delete-mul', true), _throw = DEFAULT_THROW)
		{
			if(typeof _throw !== 'boolean')
			{
				_throw = DEFAULT_THROW;
			}
			
			if(typeof _callback !== 'function' && typeof _callback !== 'boolean')
			{
				_callback = document.getVariable('page-callback', true);
			}
			
			if(! (_link = Page.getPath(_link, _throw)))
			{
				return null;
			}
			else if(_link[0] === '#')
			{
				return Page.getHash(_link.substr(1), _callback, null, _throw);
			}
			else if(_link.startsWith('javascript:'))
			{
				return Page.executeJavaScript(_link, DEFAULT_CONTEXT, _throw);
			}

			return Page.getLink(_link, _target, _callback, _options, _type, _animate, _delay, _delete_mul, _throw);
		}

		static getHash(_link, _callback, _reload = document.getVariable('page-reload-hash', true), _throw = DEFAULT_THROW)
		{
			if(typeof _throw !== 'boolean')
			{
				_throw = DEFAULT_THROW;
			}
			
			if(! isString(_link, false))
			{
				if(_throw)
				{
					throw new Error('Invalid _link argument');
				}

				return '';
			}
			else if(typeof _callback !== 'function')
			{
				_callback = null;
			}

			if(typeof _reload !== 'boolean')
			{
				_reload = document.getVariable('page-reload-hash', true);
			}

			if(_link === location.hash.substr(1))
			{
				if(_reload)
				{
					location.hash = '';
				}
				else
				{
					return _link;
				}
			}

			//
			location.hash = ('#' + _link);

			//
			call(_callback, { type: 'getHash', link: _link, reload: _reload });

			//
			return _link;
		}
		
		static executeJavaScript(_string, _context = DEFAULT_CONTEXT, _throw = DEFAULT_THROW)
		{
			if(! document.getVariable('page-scripting', true))
			{
				if(_throw)
				{
					throw new Error('JavaScript execution is not allowed here, this way');
				}
				
				return undefined;
			}
			else if(typeof _string !== 'string')
			{
				throw new Error('Invalid _string argument');
			}
			else if(_string.startsWith('javascript:'))
			{
				_string = _string.substr(11);
			}
			
			var result;
			
			try
			{
				result = eval.call(_context, _string);
			}
			catch(_error)
			{
				if(_throw)
				{
					throw _error;
				}
				
				result = _error;
			}
			
			return result;
		}

		static getLink(_link, _target = Page.target, _callback, _options, _type = document.getVariable('page-fallback-type'), _animate = document.getVariable('page-data-duration', true), _delay = document.getVariable('data-delay', true), _delete_mul = document.getVariable('page-data-delete-mul', true), _throw = DEFAULT_THROW)
		{
			if(typeof _throw !== 'boolean')
			{
				_throw = DEFAULT_THROW;
			}
			
			if(! _target)
			{
				_target = Page.target;
			}
			
			if(isString(_target, false))
			{
				_target = document.getElementById(_target);
			}
			
			if(! _target)
			{
				if(_throw)
				{
					throw new Error('Invalid _target argument');
				}
				
				return undefined;
			}
			
			if(isString(_type, false)) switch(_type = _type.toLowerCase())
			{
				case 'html':
				case 'text':
					break;
				default:
					_type = document.getVariable('page-fallback-type').toLowerCase();
					break;
			}
			else
			{
				_type = document.getVariable('page-fallback-type').toLowerCase();
			}

			const originalLink = (isString(_link, false) ? _link : null);
			
			if(originalLink === null)
			{
				if(_throw)
				{
					throw new Error('Invalid _link argument');
				}

				return null;
			}
			else
			{
				_link = Page.getPath(_link, _throw);
			}

			if(_link[0] === '#')
			{
				return Page.getHash(_link, _callback, null, _throw);
			}
			else if(_link.startsWith('javascript:'))
			{
				return Page.executeJavaScript(_link.substr(11), DEFAULT_CONTEXT, _throw);
			}

			if(typeof _callback !== 'function' && typeof _callback !== 'boolean')
			{
				_callback = document.getVariable('page-callback', true);
			}
			
			var doAnimate;
			
			if(! (isInt(_animate) && _animate >= 0))
			{
				if(_animate === false)
				{
					_animate = null;
					doAnimate = false;
				}
				else
				{
					_animate = document.getElement('page-data-duration');
					doAnimate = true;
				}
			}
			else
			{
				doAnimate = true;
			}

			if(! (isInt(_delay) && _delay >= 0))
			{
				_delay = 0;
			}
			
			if(_delete_mul !== null && !(isNumber(_delete_mul) && _delete_mul > 0))
			{
				_delete_mul = document.getVariable('page-data-delete-mul', true);
			}
			
			const animateIf = (_request) => {
				//
				var local = location.protocol + '//' + location.host;
				
				if(_request.responseURL.length > local)
				{
					local += '/';
				}
				
				local = !!_request.responseURL.startsWith(local);

				//
				//setValue(_request, _type, '', false, null);
				_target.innerHTML = '';
				
				//
				var data = _request.responseText;
				
				//
				var script = '';
				var style = '';
				const scripts = [];
				const styles = [];
				var extracted;
				
				if(_type === 'html' && local)
				{
					extracted = html.extract(data, [ 'script', 'style', 'link' ], true, 1, _throw);
					data = extracted.shift();
					var item;
					
					for(var i = 0, src = 0, href = 0; i < extracted.length; ++i)
					{
						switch(extracted[i]['*'])
						{
							case 'link':
								if(! isString(extracted[i].href, false))
								{
									if(_throw)
									{
										throw new Error('A <link> tag needs to have a .href value');
									}
									else
									{
										extracted.splice(i--, 1);
									}
								}
								else
								{
									styles[href++] = extracted[i];
								}
								break;
							case 'style':
								if(isString((item = extracted.splice(i--, 1)[0]).style, false))
								{
									style += item.style;
								}
								else if(_throw)
								{
									throw new Error('A <style> tag needs to have a payload');
								}
								break;
							case 'script':
								if(! isString(extracted[i].src, false))
								{
									if(isString((item = extracted.splice(i--, 1)[0]).script, false))
									{
										script += item.script;
									}
									else if(_throw)
									{
										throw new Error('Invalid <script> (got neither a .src nor data)');
									}
								}
								else
								{
									scripts[src++] = extracted[i];
								}
								break;
							default:
								if(_throw)
								{
									throw new Error('Only <link/style/script> is allowed to be extracted, not \'' + extracted[i]['*'] + '\')');
								}

								script = style = '';
								scripts.length = 0;
								styles.length = 0;
								break;
						}
					}
					
					extracted = true;
				}
				else
				{
					extracted = false;
					script = style = '';
					scripts.length = 0;
					styles.length = 0;
				}
if(extracted) {
console.warn('scripts:\n' + Object.debug(scripts));
console.warn('styles:\n' + Object.debug(styles));
console.warn('script: ' + script.quote('"'));
console.warn('style: ' + style.quote('"'));
}
				//
				if(extracted)
				{
					//
					//todo/resolve, v.a. aber pfade ab jew.
					//home-sub-path beginnen.. dass in den
					//home-pages codierte .src sowie .href
					//von den jew. sub-pfaden ausgehen! ^_^
					//
					/*for(var i = 0; i < extracted.length; ++i)
					{
						if(isString(extracted[i].src, false))
						{
							extracted[i].src = path.resolve(extracted[i].src);
						}

						if(isString(extracted[i].href, false))
						{
							extracted[i].href = path.resolve(extracted[i].href);
						}
					}*/

					// erzeuge nodes mit attribs by extraction
					var node, test;

					for(var i = 0; i < styles.length; ++i)
					{
						node = document.createElement('link');

						for(const idx in styles[i])
						{
							if(idx === '*')
							{
								continue;
							}
							else if(idx === styles[i]['*'])
							{
								node.innerHTML = styles[i][styles[i]['*']];
							}
							else
							{
								node.setAttribute(idx, styles[i][idx]);
							}
						}

						if(! node.id)
						{
							node.id = _request.responseURL + '[' + i + '.css]';
						}

						if(test = document.getElementById(node.id))
						{
							test.parentNode.removeChild(test);
							delete Page.ID[node.id];
						}

						styles[i] = node;
					}

					for(var i = 0; i < scripts.length; ++i)
					{
						node = document.createElement('script');

						for(const idx in scripts[i])
						{
							if(idx === '*')
							{
								continue;
							}
							else if(idx === scripts[i]['*'])
							{
								node.innerHTML = scripts[i][scripts[i]['*']];
							}
							else
							{
								node.setAttribute(idx, styles[i][idx]);
							}
						}

						if(! node.id)
						{
							node.id = _request.responseURL + '[' + i + '.js]';
						}

						if(test = document.getElementById(node.id))
						{
							test.parentNode.removeChild(test);
							delete Page.ID[node.id];
						}

						scripts[i] = node;
					}

					if(style.length > 0)
					{
						node = document.createElement('style');
						node.id = _request.responseURL + '.css';
						node.innerHTML = style;
						styles.unshift(node);
						style = null;
					}

					if(script.length > 0)
					{
						node = document.createElement('script');
						node.id = _request.responseURL + '.js';
						node.innerHTML = script;
						scripts.unshift(node);
						script = null;
					}
				}

				//
				if(document.getVariable('bionic', true) && _type === 'html')
				{
					data = bionic(data);
				}

				//
				if(doAnimate)
				{
					_target.blink({
						count: document.getVariable('page-blink-count', true),
						border: false,
						duration: document.getVariable('page-blink-duration', true),
						delay: 0,
						transform: false
					});
				}

				//
				setTimeout(() => {
					//
					for(const idx in Page.ID)
					{
						if(Page.ID[idx].parentNode)
						{
							Page.ID[idx].parentNode.removeChild(Page.ID[idx]);
						}

						delete Page.ID[idx];
					}

					//
					for(var i = 0; i < styles.length; ++i)
					{
						HEAD.appendChild(Page.ID[styles[i].id] = styles[i]);
					}

					//
					setValue(_request, _type, data, doAnimate, (_e) => {
						//
						for(var i = 0; i < scripts.length; ++i)
						{
							HEAD.appendChikd(Page.ID[scripts[i].id] = scripts[i]);
						}

						//
						call(_callback, { type: 'page', href: _request.responseURL, event: _e, type: _type, local });
						window.emit('page', { type: 'page', event: _e, href: _request.responseURL, type: _type, local });
					});
				});

				//
				return _request.responseText;
			};

			const checkType = (_request, _throw = DEFAULT_THROW) => {
				var result = _request.getResponseHeader('Content-Type');

				if(result === null)
				{
					switch(_type = _type.toLowerCase())
					{
						case 'html':
						case 'text':
							result = _type;
							break;
						default:
							result = null;
							break;
					}
				}
				else
				{
					result = result.toLowerCase();
					
					if(result.startsWith('text/html'))
					{
						result = 'html';
					}
					else if(result.startsWith('text/plain'))
					{
						result = 'text';
					}
					else switch(_type = _type.toLowerCase())
					{
						case 'html':
						case 'text':
							result = _type;
							break;
						default:
							result = null;
							break;
					}
				}
				
				if(! result)
				{
					if(_throw)
					{
						throw new Error('Invalid _type [ "html", "text" ], and no \'Content-Type\' header defined');
					}
					
					return document.getVariable('page-fallback-type').toLowerCase();
				}

				return result;
			};
			
			const setValue = (_request, _type, _value = _request.responseText, _anim = doAnimate, _cb) => {
				switch(_type)
				{
					case 'html':
						if(document.getVariable('page-text-white-space'))
						{
							if('_originalWhiteSpace' in _target)
							{
								_target.style.setProperty('white-space', _target._originalWhiteSpace);
								delete _target._originalWhiteSpace;
							}
						}

						if(! ('innerHTML' in _target))
						{
							throw new Error('Invalid _type (no .innerHTML defined in _target)');
						}
						else if(_anim)
						{
							_target.innerHTML = '';
							_target.setHTML(_cb, _value, _animate, _delay, _delete_mul, 'innerHTML', _throw);
						}
						else
						{
							_target.innerHTML = _value;
							
							if(typeof _cb === 'function')
							{
								call(_cb, { type: 'setValue', data: _value, type: _type, animated: _anim });
							}
						}
						break;
					case 'text':
						if(document.getVariable('page-text-white-space'))
						{
							if(! ('_originalWhiteSpace' in _target))
							{
								_target._originalWhiteSpace = _target.style.whiteSpace;
							}

							_target.style.setProperty('white-space', document.getVariable('page-text-white-space'));
						}

						if('textContent' in _target)
						{
							if(_anim)
							{
								_target.textContent = '';
								_target.setText(_cb, _value, _animate, _delay, _delete_mul, 'textContent', _throw);
							}
							else
							{
								_target.textContent = _value;

								if(typeof _cb === 'function')
								{
									call(_cb, { type: 'setValue', data: _value, type: _type, animated: _anim });
								}
							}
						}
						else if('innerText' in _target)
						{
							if(_anim)
							{
								_target.innerText = '';
								_target.setText(_cb, _value, _animate, _delay, _delete_mul, 'innerText', _throw);
							}
							else
							{
								_target.innerText = _value;

								if(typeof _cb === 'function')
								{
									call(_cb, { type: 'setValue', data: _value, type: _type, animated: _anim });
								}
							}
						}
						else
						{
							throw new Error('Invalid _type (neither .innerText nor .textContent defined in _target)');
						}
						break;
					default:
						throw new Error('Invalid _type [ "html", "text" ]');
				}
				
				return _value;
			};
			
			const callback = (_event, _request, _options) => {
				if(_request.statusClass !== 2)
				{
					if(originalLink[0] === '~')
					{
						location.hash = '';
					}
					else if(_throw)
					{
						throw new Error('Couldn\'t load link \'' + (_request.responseURL || _link) + '\': [' + _request.status + '] ' + _request.statusText);
					}
					
					return result.status;
				}
				else if(_type = checkType(_request, _throw))
				{
					Page.nextURL(_request.responseURL || _link);
				}
				else
				{
			throw new Error('DEBUG');
					return;
				}
				
				if(_type === 'html')
				{
				}
				
				return animateIf(_request);
			};

			const result = Page.loadFile(_link, (_callback ? callback : null), _options, _throw);
			
			if(_callback)
			{
				return result;
			}
			else if(result.statusClass !== 2)
			{
				if(_throw)
				{
					throw new Error('Couldn\'t load link \'' + (result.responseURL || _link) + '\': [' + result.status + '] ' + result.statusText);
				}
				
				return result.status;
			}
			else if(_type = checkType(result, _throw))
			{
				Page.nextURL(result.responseURL || _link);
			}
			else
			{
	throw new Error('DEBUG');
				return;
			}

			//
			return animateIf(result);
		}

		static loadFile(_url, _callback, _options)
		{
			if(typeof _callback !== 'function')
			{
				_callback = null;
			}
			
			if(! isObject(_options))
			{
				_options = {};
			}
			
			return ajax({ url: _url, callback: _callback, ... _options });
		}

		static get target()
		{
			const result = document.getElementById(document.getVariable('page-target'));

			if(!result)
			{
				throw new Error('--page-target ' + document.getVariable('page-target').quote() + ' not available (ID)');
			}

			return result;
		}
		
		static onhashchange(_event)
		{
			//
			const href = { new: _event.newURL, old: (Page.History.length === 0 ? _event.oldURL : Page.History[Page.History.length - 1]) };
			const hash = { new: Page.extractHash(href.new), old: Page.extractHash(href.old) };
			const link = { new: Page.extractURL(href.new), old: Page.extractURL(href.old) };

			//
			if(hash.new === '#' || hash.new.length === 0)
			{
				Page.target.scrollTo(0, 0);
				return '#';
			}
			else if(hash.new[1] === '~' && hash.new.length > 1)
			{
				return Page.getLink(hash.new.substr(1));
			}

			//
			const elem = document.getElementById(hash.new.substr(1));

			if(! elem)
			{
				if(document.getVariable('page-invalid-hash-clear', true))
				{
					return location.hash = '';
				}

				return location.hash = hash.old;
			}
			else
			{
				Page.nextURL(hash.new);
			}
			
			//
			elem.scrollIntoView({
				block: document.getVariable('page-scroll-block'),
				inline: document.getVariable('page-scroll-inline')
			});

			if(! (elem.IN || elem.OUT))
			{
				elem.blink({ count: document.getVariable('page-blink-count', true), border: false });
			}
			
			//
			return hash.new;
		}
		
		static extractURL(_url, _throw = DEFAULT_THROW)
		{
			if(typeof _throw !== 'boolean')
			{
				_throw = DEFAULT_THROW;
			}
			
			if(typeof _url !== 'string')
			{
				if(_throw)
				{
					throw new Error('Invalid _url argument');
				}
				
				return null;
			}
			else if(_url.length === 0 || _url === '#')
			{
				return '';
			}
			else if(_url[0] === '#')
			{
				return '';
			}
			
			const idx = _url.lastIndexOf('#');
			
			if(idx === -1)
			{
				return _url;
			}
			
			return _url.substr(0, idx);
		}
		
		static extractHash(_url, _throw = DEFAULT_THROW)
		{
			if(typeof _throw !== 'boolean')
			{
				_throw = DEFAULT_THROW;
			}
			
			if(typeof _url !== 'string')
			{
				if(_throw)
				{
					throw new Error('Invalid _url argument');
				}
				
				return '#';
			}
			else if(_url.length === 0 || _url === '#')
			{
				return '#';
			}
			else if(_url[0] === '#')
			{
				return _url;
			}

			const idx = _url.lastIndexOf('#');
			
			if(idx === -1)
			{
				return '#';
			}
			
			return _url.substr(idx);
		}
		
		static nextURL(_value, _max_length = document.getVariable('page-history-length', true), _throw = DEFAULT_THROW)
		{
			if(typeof _throw !== 'boolean')
			{
				_throw = DEFAULT_THROW;
			}
			
			if(! (isInt(_max_length) && _max_length >= 0))
			{
				_max_length = document.getVariable('page-history-length', true);
			}

			if(! document.getVariable('page-history', true))
			{
				_max_length = 0;
			}
			
			if(_max_length <= 0)
			{
				Page.History.length = 0;
				return false;
			}
			else
			{
				Page.History.remove(_value);
			}

			if(Page.History.length > (_max_length - 1))
			{
				Page.History.splice(0, Page.History.length - _max_length + 1)
			}
			
			Page.History.push(_value);
			return true;
		}

		static onclick(_event, _target = _event.target)
		{
			return Page.click(_event, _target);
		}

		static click(_event, _target = _event.target)
		{
			if(! (isString(_target.href, false) || isString(_target.getAttribute('href'), false)))
			{
				if(_target.related && (isString(_target.related.href, false) || isString(_target.related.getAttribute('href'), false)))
				{
					_target = _target.related;
				}
				else
				{
					return;
				}
			}

			if(isString(_target.target, false))
			{
				if(_target.target === '#')
				{
					_target.target = '';
				}

				return;
			}
			else if(! (isString(_target.href, false) || isString(_target.getAttribute('href'), false)))
			{
				return;
			}

			const href = _target.href;
			const attr = _target.getAttribute('href');
			var url, link;

			if(href.length === 0)
			{
				url = attr;
			}
			else if(attr.length === 0)
			{
				url = href;
			}
			else if(href === attr)
			{
				url = href;
			}
			else
			{
	throw new Error('DEBUG: both, and not the same:\n\nhref: ' + href + '\nattr[href]: ' + attr);
			}

			var local = location.protocol + '//' + location.host;

			if(url.length > local)
			{
				local += '/';
			}

			if(url.startsWith(local))
			{
				link = Page.getPath(url.substr(local.length));
			}
			else
			{
				link = Page.getPath(url);
			}

			_event.preventDefault();
			return Page.get(link);
		}
	}

	//
	Page.History = [];

	//
	Page.ID = {};

	//
	window.addEventListener('hashchange', Page.onhashchange.bind(Page));
	window.addEventListener('click', Page.onclick.bind(Page));

	//
	window.addEventListener('ready', () => {
		const hash = location.hash;
		location.hash = '';
		var loc = location.href;

		if(loc[loc.length - 1] !== '#')
		{
			loc += '#';
		}

		loc += hash.substr(1);
		location.href = loc;
		//Page.onhashchange({ oldURL: '', newURL: location.href });
	}, { once: true });

	//
	
})();

