(function()
{

	//
	const DEFAULT_RADIUS = 'feet';
	const DEFAULT_ALGORITHM = 'haversine';

	//
	geo = module.exports = {
		radius: {
			meter: 6371000,
			kilometer: 6371,
			mile: 3958.75,
			yard: 6967389.31,
			feet: 20902231.64,
			inch: 250826907.1,
			nautical: 3440.07
		}
	};

	const getRadius = (_name_lang) => {
		//
		if(_name_lang === null)
		{
			return null;
		}
		else if(! isString(_name_lang, false))
		{
			_name_lang = navigator.language;
		}
		
		//
		_name_lang = _name_lang.toLowerCase();

		//
		var result;
		
		//
		if(_name_lang.toLowerCase() in geo.radius)
		{
			result = geo.radius[_name_lang.toLowerCase()];
		}
		else if(_name_lang.startsWith('de'))
		{
			result = geo.radius.meter;
		}
		else if(_name_lang.startsWith('en'))
		{
			if(_name_lang.includes('gb'))
			{
				result = geo.radius.yard;
			}
			else
			{
				result = geo.radius.feet;
			}
		}
		else
		{
			result = geo.radius[DEFAULT_RADIUS];
		}

		return result;
	};

	Object.defineProperty(geo, 'algorithm', { get: function()
	{
		const result = document.getVariable('geo-distance');

		if(! isString(result, false))
		{
			return DEFAULT_ALGORITHM;
		}

		return result;
	}});

	Object.defineProperty(geo, 'algorithms', { get: function()
	{
		return Object.keys(geo.distance);
	}});

	//
	geo.distance = (_lat1, _lon1, _lat2, _lon2, _geo_distance = geo.algorithm, _unit_lang = null) => {
		if(! isString(_geo_distance, false))
		{
			_geo_distance = geo.algorithm;
		}

		if(isString(_geo_distance, false))
		{
			if(_geo_distance in geo.distance)
			{
				return geo.distance[_geo_distance](_lat1, _lon1, _lat2, _lon2, _unit_lang);
			}
			
			throw new Error('The distance algorithm \'' + _geo_distance + '\' is not available');
		}
		
		throw new Error('Invalid _geo_distance argument (not a String)');
	};
	
	Object.defineProperty(geo, 'distances', { get: function()
	{
		return Object.keys(geo.distance);
	}});

	//
	geo.distance.haversine = (_lat1, _lon1, _lat2, _lon2, _unit_lang = null) => {
		//
		// φ is latitude, λ is longitude
		// note that angles need to be in radians to pass to trig functions!
		//
		const φ1 = (_lat1 * Math.PI / 180); // φ, λ in radians
		const φ2 = (_lat2 * Math.PI / 180);
		const Δφ = ((_lat2 - _lat1) * (Math.PI / 180));
		const Δλ = ((_lon2 - _lon1) * (Math.PI / 180));

		//
		// a = sin²(Δφ/2) + cos φ1 ⋅ cos φ2 ⋅ sin²(Δλ/2)
		//
		const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2)
			+ Math.cos(φ1) * Math.cos(φ2)
			* Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
		//
		// c = 2 ⋅ atan2( √a, √(1−a) )
		//
		const c = (2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)));

		//
		// d = R ⋅ c
		//
		//const d = (getRadius(_unit_lang) * c);
		
		//
		var result = getRadius(_unit_lang);

		if(isNumber(result))
		{
			result *= c;
		}
		else
		{
			result = Object.create(null);

			for(const idx in geo.radius)
			{
				result[idx] = (c * geo.radius[idx]);
			}
		}

		//
		return result;
	};

	geo.distance.sphericalLawOfCosines = (_lat1, _lon1, _lat2, _lon2, _unit_lang = null) => {
		//
		// d = acos( sin φ1 ⋅ sin φ2 + cos φ1 ⋅ cos φ2 ⋅ cos Δλ ) ⋅ R
		//
		const φ1 = (_lat1 * Math.PI / 180);
		const φ2 = (_lat2 * Math.PI / 180);
		const Δλ = ((_lon2 - _lon1) * Math.PI / 180);

		const d = (Math.acos(Math.sin(φ1) * Math.sin(φ2)
			+ Math.cos(φ1) * Math.cos(φ2) * Math.cos(Δλ)));

		//
		var result = getRadius(_unit_lang);

		if(isNumber(result))
		{
			result *= d;
		}
		else
		{
			result = Object.create(null);

			for(const idx in geo.radius)
			{
				result[idx] = (d * geo.radius[idx]);
			}
		}

		//
		return result;
	};

	//
	//TODO/
	/*
	geo.bearing = function(_lat1, _lon1, _lat2, _lon2)
	{
		// 
		// θ = atan2( sin Δλ ⋅ cos φ2 , cos φ1 ⋅ sin φ2 − sin φ1 ⋅ cos φ2 ⋅ cos Δλ )
		// ... where φ1,λ1 is the start point, φ2,λ2 the end point (Δλ is the difference in longitude)
		//
		const y = Math.sin(_lon2 - _lon1) * Math.cos(_lat2);
		const x = Math.cos(_lat1) * Math.sin(_lat2) - Math.sin(_lat1) * Math.cos(_lat2) * Math.cos(_lon2 - _lat1);
		const θ = Math.atan2(y, x);
		const bearing = (θ * 180 / Math.PI + 360) % 360; // in degrees

		//
		return bearing;
	}*/
	
	//
	//TODO/
	/*
	geo.midpoint = function(_lat1, _lon1, _lat2, _lon2)
	{
	}*/

	//

})();

