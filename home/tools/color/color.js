(function()
{

	//
	box = null;
	table = null;

	//
	const onready = (_event) => {
		if(location.hash.length > 1)
		{
			return;
		}

		setTimeout(() => {
			MAIN.textContent = JSON.stringify(_event.map)
			MAIN.style.fontSize = '0.8rem';
			MAIN.blink({ count: 1, transform: false, duration: 2400 });

			createBox();
		}, 0);
	};

	const createBox = () => {
		return createTable(box = Box.create({
			width: 'auto',
			height: 'auto',
			right: 200,
			top: '200px',
			bottom: 200,
			left: '200px',
			//center: true,
			id: 'colorBox',
			zIndex: 16,
			parent: MAIN,
			//help: 'All the default CSS colors',
			sleep: 800,
			help: '<span style="font-size: 1.39rem;"><i>CLICK</i> on color code to<br>COPY it to <b>Clipboard</b>! :)~</span>'
		}));
	}

	const createTable = (_box = box) => {
		//
		table = document.createElement('table');
		table.id = 'color';
		table.className = 'a';

		//
		const PADDING_RIGHT = '10vw';
		const WIDTH = '4vw';

		const hr = document.createElement('tr');
		hr.style.textAlign = 'center';
		var th = document.createElement('th');
		th.style.paddingRight = PADDING_RIGHT;
		th.innerHTML = 'Name';
		th.style.width = WIDTH;
		hr.appendChild(th, null);
		th = document.createElement('th');
		th.style.paddingRight = PADDING_RIGHT;
		th.innerHTML = '&Uuml;bersetzung';
		th.style.width = WIDTH;
		hr.appendChild(th, null);
		th = document.createElement('th');
		th.style.paddingRight = PADDING_RIGHT;
		th.style.width = WIDTH;
		th.innerHTML = 'Hexadezimal';
		hr.appendChild(th, null);
		th = document.createElement('th');
		th.style.paddingRight = PADDING_RIGHT;
		th.style.width = WIDTH;
		th.innerHTML = 'RGB';
		hr.appendChild(th, null);
		table.appendChild(hr);
		
		//
		const TIMEOUT = 2600;
		const DURATION = 2200;
		var tr, td;
		var osdInvalidate = true;

		window.addEventListener('osd', (_e) => {
			if(_e.type === 'destroy')
			{
				osdInvalidate = true;
			}
			else if(_e.type === 'update')
			{
				osdInvalidate = true;
			}
		});

		const map = color.map;
		for(var i = 0; i < map.length; ++i)
		{
			//
			const hexColor = color.hex(map[i].value);
			const rgbColor = color.rgb(map[i].value);
			const complement = color.complement.rgb(rgbColor);
			
			//
			tr = document.createElement('tr');

			//
			td = document.createElement('td');
			const name = td.innerHTML = map[i].name;
			td.addEventListener('pointerover', () => {
				const data = '<span style="font-size: 4rem; color: ' + rgbColor + ';">' + name + '</span>';
				
				if(osdInvalidate)
				{
					osd(data, { timeout: TIMEOUT, duration: DURATION });
				}
			});
			tr.appendChild(td, null);

			//
			td = document.createElement('td');
			const de = td.innerHTML = map[i].lang.de;
			td.addEventListener('pointerover', () => {
				const data = '<span style="font-size: 4rem; color: ' + rgbColor + ';">' + de + '</span>';

				if(osdInvalidate)
				{
					osd(data, { timeout: TIMEOUT, duration: DURATION });
				}
			});
			tr.appendChild(td, null);

			//
			td = document.createElement('td');
			td.innerHTML = hexColor;
			td.style.backgroundColor = hexColor;
			td.style.color = complement;
			td.style.textAlign = 'center';
			td.addEventListener('click', () => {
				navigator.clipboard.writeText(hexColor)
					.catch((_error) => {
						alert(_error);
					}).then(() => {
						if(osdInvalidate)
						{
							osd('<span style="font-size: 7rem; color: ' + rgbColor + ';">Clipboard!</span>', {
								timeout: TIMEOUT, duration: DURATION });
						}
					});
			});
			td.addEventListener('pointerover', () => {
				const data = '<span style="font-size: 4rem; color: ' + rgbColor + ';">' + hexColor + '</span>';

				if(osdInvalidate)
				{
					osd(data, { timeout: TIMEOUT, duration: DURATION });
				}
			});
			tr.appendChild(td, null);
			
			//
			td = document.createElement('td');
			td.innerHTML = rgbColor;
			td.style.color = rgbColor;
			td.style.backgroundColor = complement;
			td.style.textAlign = 'center';
			td.addEventListener('click', () => {
				navigator.clipboard.writeText(rgbColor)
					.catch((_error) => {
						alert(_error);
					}).then(() => {
						if(osdInvalidate)
						{
							osd('<span style="font-size: 7rem; color: ' + rgbColor + ';">Clipboard!</span>', {
								timeout: TIMEOUT, duration: DURATION });
						}
					});
			});
			td.addEventListener('pointerover', () => {
				const data = '<span style="font-size: 4rem; color: ' + rgbColor + ';">' + rgbColor + '</span>';

				if(osdInvalidate)
				{
					osd(data, { timeout: TIMEOUT, duration: DURATION });
				}
			});
			tr.appendChild(td, null);

			//
			table.appendChild(tr, null);
		}

		//
		box.appendChild(table, null);
	}

	//
	//window.addEventListener('ready', onready, { once: true });
	window.addEventListener('color.json', onready, { once: true });

})();

