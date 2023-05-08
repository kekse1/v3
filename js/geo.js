(function()
{

	//

	//
	// Note in these scripts, I generally use lat/lon for latitude/longitude in degrees,
	// and φ/λ for latitude/longitude in radians – having found that mixing degrees & radians
	// is often the easiest route to head-scratching bugs...
	//

	//
	geo = module.exports = { RADIUS: 6371e3 }; // w/ earth's radius, in metres.. ^_^

	Object.defineProperty(geo, 'algorithm', { get: function()
	{
		return document.getVariable('geo-distance');
	}});

	//
	geo.distance = (_lat1, _lon1, _lat2, _lon2, _geo_distance = geo.algorithm) => {
		if(! isString(_geo_distance, false))
		{
			_geo_distance = geo.algorithm;
		}

		if(isString(_geo_distance, false))
		{
			if(_geo_distance in geo.distance)
			{
				return geo.distance[_geo_distance](_lat1, _lon1, _lat2, _lon2);
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
	geo.distance.haversine = (_lat1, _lon1, _lat2, _lon2) => {
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
		const d = (geo.RADIUS * c); // in metres, too.

		//
		// in metres, too (see earth's radius R above)
		//
		return d;
	};

	geo.distance.sphericalLawOfCosines = (_lat1, _lon1, _lat2, _lon2) => {
		//
		// d = acos( sin φ1 ⋅ sin φ2 + cos φ1 ⋅ cos φ2 ⋅ cos Δλ ) ⋅ R
		//
		const φ1 = (_lat1 * Math.PI / 180);
		const φ2 = (_lat2 * Math.PI / 180);
		const Δλ = ((_lon2 - _lon1) * Math.PI / 180);

		const d = ((Math.acos(Math.sin(φ1) * Math.sin(φ2)
			+ Math.cos(φ1) * Math.cos(φ2) * Math.cos(Δλ))) * geo.RADIUS);

		//
		return d;
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

