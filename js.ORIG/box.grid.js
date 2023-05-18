(function()
{

	//
	const DEFAULT_THROW = true;
		
	//
	Grid = Box.Grid = class Grid extends Box
	{
		constructor(... _args)
		{
			//
			super(... _args);

			//
			this.identifyAs('grid');
		}

		reset()
		{
			if(this.cards)
			{
				this.cards.length = 0;
			}
			else
			{
				this.cards = [];
			}
			
			return super.reset();
		}

		static onclick(_event)
		{
			if(was(_event.target, 'Grid') || was(_event.target, 'GridCard'))
			{
				_event.target.click(_event);
			}
		}

		click(_event)
		{
			const index = Grid.INDEX;

			for(const g of index)
			{
				g.classList.remove('selected');
			}

			this.classList.add('selected');
		}
	}

	GridCard = Grid.Card = class GridCard extends Grid
	{
		constructor(... _args)
		{
			//
			super(... _args);

			//
			this.identifyAs('gridCard');
		}
	}
	
	//
	if(! customElements.get('a-grid'))
	{
		customElements.define('a-grid', Grid, { is: 'a-box' });
	}

	if(! customElements.get('a-grid-card'))
	{
		customElements.define('a-grid-card', Grid.Card, { is: 'a-grid' });
	}
	
	//
	Object.defineProperty(Grid, 'INDEX', { get: function()
	{
		const result = [];
		
		for(var i = 0, j = 0; i < Box._INDEX.length; ++i)
		{
			if(is(Box._INDEX[i], 'Grid'))
			{
				result[j++] = Box._INDEX[i];
			}
		}
		
		return result;
	}});

	Object.defineProperty(Grid.Card, 'INDEX', { get: function()
	{
		const result = [];

		for(var i = 0, j = 0; i < Box._INDEX.length; ++i)
		{
			if(is(Box._INDEX[i], 'GridCard'))
			{
				result[j++] = Box._INDEX[i];
			}
		}

		return result;
	}});

	//
	const on = {};

	on.click = Grid.onclick.bind(Grid);

	for(const idx in on)
	{
		window.addEventListener(idx, on[idx], { passive: false, capture: true });
	}
	
	//
	
})();

