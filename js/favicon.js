(function()
{

	//
	const DEFAULT_THROW = true;

	//
	FAVICON = null;

	//
	const createFavicon = (_throw = DEFAULT_THROW) => {
		if(FAVICON !== null)
		{
			return false;
		}
		
		const image = document.createElement('img');
		
		const callback = (_event) => {
			//
			image.removeEventListener('load', callback);
			image.removeEventListener('error', callback);
			
			//
			if(_event.type === 'error')
			{
				if(_throw)
				{
					throw new Error('Favicon ' + (css.parse.url(document.getVariable('favicon-image'), false) || '').quote('\'') + ' couldn\'t be loaded');
				}
			}
			else
			{
				BODY._appendChild(FAVICON = image);
				
				if(document.getVariable('favicon-animation', true))
				{
					FAVICON.style.transform = 'scale(0)';

					const process = () => {
						//
						if(! FAVICON)
						{
							return null;
						}

						//
						var DURATION = FAVICON.getVariable('favicon-duration', true);
						var DELAY = FAVICON.getVariable('favicon-delay', true);

						if(isArray(DURATION))
						{
							DURATION = Math.random.int(DURATION[0], DURATION[1]);
						}

						if(isArray(DELAY))
						{
							DELAY = Math.random.int(DELAY[0], DELAY[1]);
						}

						setTimeout(() => {
							//
							var lastNow = Date.now();
							var time = 0;
							var delta;
							var scale;
							var now;

							const animationFrame = () => {
								//
								if(! FAVICON)
								{
									return;
								}
								else
								{
									now = Date.now();
									delta = (now - lastNow);
									time += delta;
									lastNow = now;
								}

								scale = Math.sin(Math.min(1, time / DURATION) * 2 * Math.PI);

								if(scale < 0)
								{
									scale = -scale;
								}

								FAVICON.style.transform = `scale(${scale})`;

								//
								if(time < DURATION)
								{
									requestAnimationFrame(animationFrame);
								}
								else
								{
									setTimeout(process, FAVICON.getVariable('favicon-restart', true));
								}
							};

							requestAnimationFrame(animationFrame);
						}, DELAY);
					}

					setTimeout(process, FAVICON.getVariable('favicon-restart', true));
				}
			}
		};
		
		//
		image.id = 'FAVICON';
		image.animation = null;
		image.addEventListener('load', callback, { once: true });
		image.addEventListener('error', callback, { once: true });
		image.src = css.parse.url(document.getVariable('favicon-image'));
		image.draggable = false;
		
		//
		image.remove = function()
		{
			if(FAVICON)
			{
				if(FAVICON.parentNode)
				{
					FAVICON.parentNode.removeChild(FAVICON);
				}

				return resetFavicon();
			}
			
			return false;
		}
		
		//
		return true;
	};
	
	const resetFavicon = () => {
		if(!FAVICON)
		{
			return false;
		}
		else if(FAVICON.animation !== null)
		{
throw new Error('TODO');
			FAVICON.animation.cancel();
			FAVICON.animation = null;
		}
		
		FAVICON = null;
		return true;
	};
	
	//
	window.addEventListener('ready', () => {
		if(document.getVariable('favicon', true) && document.getVariable('favicon-image'))
		{
			createFavicon();
		}
	}, { once: true });
	
	//

})();
