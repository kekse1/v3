(function()
{

	//
	// see 'docs/css-matrix.txt'..! ^_^
	//

	//
	const DEFAULT_THROW = true;
	const DEFAULT_DEGREES = true;
	const DEFAULT_COMBINE = true;

	//
	CSSMatrix = CSSStyleDeclaration.Matrix = css.matrix = class CSSMatrix
	{
		constructor(_matrix)
		{
			//
		}

		static resolve(_matrix, _combine = DEFAULT_COMBINE, _throw = DEFAULT_THROW)
		{
	throw new Error('TODO');
		}

		static resolve2D(_matrix, _combine = DEFAULT_COMBINE, _throw = DEFAULT_THROW)
		{
	throw new Error('TODO');
		}

		static resolve3D(_matrix, _combine = DEFAULT_COMBINE, _throw = DEFAULT_THROW)
		{
	throw new Error('TODO');
		}

		static is2D(_matrix, _throw = DEFAULT_THROW)
		{
			if((_matrix = CSSMatrix.parse(_matrix, false, _throw)) === null)
			{
				return null;
			}
			
			return (_matrix.length === 4 || _matrix.length === 6);
		}

		static is3D(_matrix, _throw = DEFAULT_THROW)
		{
			if((_matrix = CSSMatrix.parse(_matrix, false, _throw)) === null)
			{
				return null;
			}

			return (_matrix.length === 16);
		}

		static hasRotation(_matrix, _throw = DEFAULT_THROW)
		{
			if(typeof _matrix === 'string' && !_matrix.includes('matrix'))
			{
				return (_matrix.includes('rotate'));
			}
			else if((_matrix = CSSMatrix.parse(_matrix, false, _throw)) === null)
			{
				return null;
			}
			else if(! CSSMatrix.isValid(_matrix))
			{
				if(_throw)
				{
					throw new Error('_matrix has not a correct .length (4/6/16)');
				}

				return null;
			}
			else if(CSSMatrix.hasRotateX(_matrix, _throw))
			{
				return true;
			}
			else if(CSSMatrix.hasRotateY(_matrix, _throw))
			{
				return true;
			}
			else if(CSSMatrix.hasRotateZ(_matrix, _throw))
			{
				return true;
			}
			else if(CSSMatrix.hasRotateXY(_matrix, _throw))
			{
				return true;
			}
			else if(CSSMatrix.hasRotateXZ(_matrix, _throw))
			{
				return true;
			}
			else if(CSSMatrix.hasRotateYZ(_matrix, _throw))
			{
				return true;
			}
			else if(CSSMatrix.hasRotateXYZ(_matrix, _throw))
			{
				return true;
			}

			return false;
		}

		static hasRotate(_matrix, _throw = DEFAULT_THROW)
		{
			if((_matrix = CSSMatrix.parse(_matrix, false, _throw)) === null)
			{
				return null;
			}
			else if(CSSMatrix.is2D(_matrix, _throw))
			{
				return false;
			}
			else if(CSSMatrix.is3D(_matrix, _throw))
			{
				if(_matrix[0] !== _matrix[10])
				{
					return false;
				}
				else if(_matrix[1] !== _matrix[6])
				{
					return false;
				}
				else if(_matrix[2] === 0)
				{
					return false;
				}
				else if(_matrix[4] !== _matrix[9])
				{
					return false;
				}
				else if(_matrix[5] === 0)
				{
					return false;
				}
				else if(_matrix[8] === 0)
				{
					return false;
				}
				else if(_matrix[9] === 0)
				{
					return false;
				}
				else if(_matrix[15] !== 1)
				{
					return false;
				}

				return true;
			}
			else if(_throw)
			{
				throw new Error('_matrix has not a correct .length (4/6/16)');
			}

			return null;
		}

		static hasRotateX(_matrix, _throw = DEFAULT_THROW)
		{
			if((_matrix = CSSMatrix.parse(_matrix, false, _throw)) === null)
			{
				return null;
			}
			else if(CSSMatrix.is2D(_matrix, _throw))
			{
				const idx = [ 5, 6, 9, 10 ];

				for(const i of idx)
				{
					if(_matrix[i] === 0)
					{
						return false;
					}
				}

				return true;
			}
			else if(CSSMatrix.is3D(_matrix, _throw))
			{
				return false;
			}
			else if(_throw)
			{
				throw new Error('_matrix has not a correct .length (4/6/16)');
			}

			return null;
		}

		static hasRotateY(_matrix, _throw = DEFAULT_THROW)
		{
			if((_matrix = CSSMatrix.parse(_matrix, false, _throw)) === null)
			{
				return null;
			}
			else if(CSSMatrix.is2D(_matrix, _throw))
			{
				const idx = [ 0, 2, 8, 10 ];

				for(const i of idx)
				{
					if(_matrix[i] === 0)
					{
						return false;
					}
				}

				return true;
			}
			else if(CSSMatrix.is3D(_matrix, _throw))
			{
				return false;
			}
			else if(_throw)
			{
				throw new Error('_matrix has not a correct .length (4/6/16)');
			}

			return null;
		}

		static hasRotateZ(_matrix, _throw = DEFAULT_THROW)
		{
			if((_matrix = CSSMatrix.parse(_matrix, false, _throw)) === null)
			{
				return null;
			}
			else if(CSSMatrix.is2D(_matrix, _throw))
			{
				for(var i = 0; i <= 3; ++i)
				{
					if(_matrix[i] === 0)
					{
						return false;
					}
				}

				return true;
			}
			else if(CSSMatrix.is3D(_matrix, _throw))
			{
				return false;
			}
			else if(_throw)
			{
				throw new Error('_matrix has not a correct .length (4/6/16)');
			}

			return null;
		}

		static hasRotateXY(_matrix, _throw = DEFAULT_THROW)
		{
			if((_matrix = CSSMatrix.parse(_matrix, false, _throw)) === null)
			{
				return null;
			}
			else if(CSSMatrix.is2D(_matrix, _throw))
			{
				return false;
			}
			else if(CSSMatrix.is3D(_matrix, _throw))
			{
				const idx = [ 0, 1, 2, 5, 6, 8, 9, 10 ];
				
				for(const i of idx)
				{
					if(_matrix[i] === 0)
					{
						return false;
					}
				}

				return true;
			}
			else if(_throw)
			{
				throw new Error('_matrix has not a correct .length (4/6/16)');
			}

			return null;
		}

		static hasRotateXZ(_matrix, _throw = DEFAULT_THROW)
		{
			if((_matrix = CSSMatrix.parse(_matrix, false, _throw)) === null)
			{
				return null;
			}
			else if(CSSMatrix.is2D(_matrix, _throw))
			{
				return false;
			}
			else if(CSSMatrix.is3D(_matrix, _throw))
			{
				const idx = [ 0, 1, 2, 4, 5, 8, 9 ];

				for(const i of idx)
				{
					if(_matrix[i] === 0)
					{
						return false;
					}
				}

				return true;
			}
			else if(_throw)
			{
				throw new Error('_matrix has not a correct .length (4/6/16)');
			}

			return null;
		}

		static hasRotateYZ(_matrix, _throw = DEFAULT_THROW)
		{
			if((_matrix = CSSMatrix.parse(_matrix, false, _throw)) === null)
			{
				return null;
			}
			else if(CSSMatrix.is2D(_matrix, _throw))
			{
				return false;
			}
			else if(CSSMatrix.is3D(_matrix, _throw))
			{
				const idx = [ 0, 1, 2, 4, 5, 7, 9 ];

				for(const i of idx)
				{
					if(_matrix[i] === 0)
					{
						return false;
					}
				}

				return true;
			}
			else if(_throw)
			{
				throw new Error('_matrix has not a correct .length (4/6/16)');
			}

			return null;
		}

		static hasRotateXYZ(_matrix, _throw = DEFAULT_THROW)
		{
			if((_matrix = CSSMatrix.parse(_matrix, false, _throw)) === null)
			{
				return null;
			}
			else if(CSSMatrix.is2D(_matrix, _throw))
			{
				return false;
			}
			else if(CSSMatrix.is3D(_matrix, _throw))
			{
				for(var i = 0; i <= 8; ++i)
				{
					if(_matrix[i] === 0)
					{
						return false;
					}
				}

				return true;
			}
			else if(_throw)
			{
				throw new Error('_matrix has not a correct .length (4/6/16)');
			}

			return null;
		}

		static hasScale(_matrix, _throw = DEFAULT_THROW)
		{
			if((_matrix = CSSMatrix.parse(_matrix, false, _throw)) === null)
			{
				return null;
			}
			else if(CSSMatrix.is2D(_matrix, _throw))
			{
				return (_matrix[0] !== 1 && _matrix[0] === _matrix[3]);
			}
			else if(CSSMatrix.is3D(_matrix, _throw))
			{
				return (_matrix[0] !== 1 && _matrix[5] === _matrix[0] && _matrix[10] === _matrix[0]);
			}
			else if(_throw)
			{
				throw new Error('_matrix has not a correct .length (4/6/16)');
			}

			return null;
		}

		static hasScales(_matrix, _throw = DEFAULT_THROW)
		{
			if(typeof _matrix === 'string' && !_matrix.includes('matrix'))
			{
				return (_matrix.includes('scale'));
			}
			else if(CSSMatrix.hasScaleX(_matrix, _throw))
			{
				return true;
			}
			else if(CSSMatrix.hasScaleY(_matrix, _throw))
			{
				return true;
			}
			else if(CSSMatrix.hasScaleXY(_matrix, _throw))
			{
				return true;
			}
			else if(CSSMatrix.hasScaleXZ(_matrix, _throw))
			{
				return true;
			}
			else if(CSSMatrix.hasScaleYZ(_matrix, _throw))
			{
				return true;
			}
			else if(CSSMatrix.hasScaleXYZ(_matrix, _throw))
			{
				return true;
			}

			return false;
		}

		static hasScaleX(_matrix, _throw = DEFAULT_THROW)
		{
			if((_matrix = CSSMatrix.parse(_matrix, false, _throw)) === null)
			{
				return null;
			}
			else if(CSSMatrix.isValid(_matrix))
			{
				return (_matrix[0] !== 1);
			}
			else if(_throw)
			{
				throw new Error('_matrix has not the correct .length (4/6/16)');
			}

			return null;
		}

		static hasScaleY(_matrix, _throw = DEFAULT_THROW)
		{
			if((_matrix = CSSMatrix.parse(_matrix, false, _throw)) === null)
			{
				return null;
			}
			else if(CSSMatrix.is2D(_matrix, _throw))
			{
				return (_matrix[3] !== 1);
			}
			else if(CSSMatrix.is3D(_matrix, _throw))
			{
				return (_matrix[0] !== 1);
			}
			else if(_throw)
			{
				throw new Error('_matrix has not the correct .length (4/6/16)');
			}

			return null;
		}

		static hasScaleXY(_matrix, _throw = DEFAULT_THROW)
		{
			if((_matrix = CSSMatrix.parse(_matrix, false, _throw)) === null)
			{
				return null;
			}
			else if(CSSMatrix.is2D(_matrix, _throw))
			{
				return (_matrix[0] !== 1 && _matrix[3] !== 1);
			}
			else if(CSSMatrix.is3D(_matrix, _throw))
			{
				return (_matrix[0] !== 1 && _matrix[5] !== 1);
			}
			else if(_throw)
			{
				throw new Error('_matrix has not a correct .length (4/6/16)');
			}

			return null;
		}

		static hasScaleXZ(_matrix, _throw = DEFAULT_THROW)
		{
			if((_matrix = CSSMatrix.parse(_matrix, false, _throw)) === null)
			{
				return null;
			}
			else if(CSSMatrix.is2D(_matrix, _throw))
			{
				return false;
			}
			else if(CSSMatrix.is3D(_matrix, _throw))
			{
				return (_matrix[0] !== 1 && _matrix[10] !== 1);
			}
			else if(_throw)
			{
				throw new Error('_matrix has not a correct .length (4/6/16)');
			}

			return null;
		}

		static hasScaleYZ(_matrix, _throw = DEFAULT_THROW)
		{
			if((_matrix = CSSMatrix.parse(_matrix, false, _throw)) === null)
			{
				return null;
			}
			else if(CSSMatrix.is2D(_matrix, _throw))
			{
				return false;
			}
			else if(CSSMatrix.is3D(_matrix, _throw))
			{
				return (_matrix[5] !== 1 && _matrix[10] !== 1);
			}
			else if(_throw)
			{
				throw new Error('_matrix has not a correct .length (4/6/16)');
			}

			return null;
		}

		static hasScaleXYZ(_matrix, _throw = DEFAULT_THROW)
		{
			if((_matrix = CSSMatrix.parse(_matrix, false, _throw)) === null)
			{
				return null;
			}
			else if(CSSMatrix.is2D(_matrix, _throw))
			{
				return false;
			}
			else if(CSSMatrix.is3D(_matrix, _throw))
			{
				return (_matrix[0] !== 1 && _matrix[5] !== 1 && _matrix[10] !== 1);
			}
			else if(_throw)
			{
				throw new Error('_matrix has not a correct .length (4/6/16)');
			}

			return null;
		}

		static isValid(_matrix)
		{
			return (CSSMatrix.type(_matrix, false).startsWith('matrix'));
		}

		static type(_matrix, _throw = DEFAULT_THROW)
		{
			var result;

			if(isArray(_matrix))
			{
				switch(_matrix.length)
				{
					case 4:
					case 6:
						result = 'matrix';
						break;
					case 16:
						result = 'matrix3d';
						break;
					default:
						result = '';
						break;
				}
			}
			else if(isString(_matrix))
			{
				_matrix = _matrix.trim();

				if(_matrix[_matrix.length - 1] !== ')')
				{
					result = '';
				}
				else if(_matrix.startsWith('matrix('))
				{
					result = 'matrix';
				}
				else if(_matrix.startsWith('matrix3d'))
				{
					result = 'matrix3d';
				}
				else
				{
					result = '';
				}
			}
			else
			{
				result = '';
			}

			if(_throw && !result)
			{
				throw new Error('Invalid _matrix argument');
			}

			return result;
		}

		static dimensions(_matrix)
		{
			const t = CSSMatrix.type(_matrix, false);

			switch(t)
			{
				case 'matrix':
					return 2;
				case 'matrix3d':
					return 3;
			}

			return 0;
		}

		static parameters(_matrix)
		{
			const t = CSSMatrix.type(_matrix, false);

			switch(t)
			{
				case 'matrix':
				case 'matrix3d':
					return _matrix.length;
			}

			return 0;
		}

		static parse(_matrix, _combine = DEFAULT_COMBINE, _throw = DEFAULT_THROW)
		{
			const t = CSSMatrix.type(_matrix, _throw);

			if(!t)
			{
				return null;
			}
			else if(isArray(_matrix))
			{
				if(_matrix.length === 4 || _matrix.length === 6 || _matrix.length === 16)
				{
					return _matrix;
				}
				else if(_throw)
				{
					throw new Error('No real _matrix array ..length needs to be 4/6/16');
				}

				return null;
			}

			const result = [];
			const parameters = css.parse(_matrix, true);
return parameters;//TODO/!!!
//FIXME/!
			switch(parameters.length)
			{
				case 4:
				case 6:
					return CSSMatrix.parse2D(parameters, _combine, _throw);
				case 16:
					return CSSMatrix.parse3D(parameters, _combine, _throw);
				default:
					if(_throw)
					{
						throw new Error('No correct CSS matrix (neither 4/6 nor 16 parameters defined)');
					}
					break;
			}

			return null;
		}

		static parse2D(_parameters, _combine = DEFAULT_COMBINE, _throw = DEFAULT_THROW)
		{
			if(! isArray(_parameters, 4) || (_parameters.length !== 4 && _parameters.length !== 6))
			{
				if(_throw)
				{
					throw new Error('The _parameters doesn\'t contain 4/6 parameters array for a 2D matrix');
				}

				return null;
			}
			else if(typeof _combine !== 'boolean')
			{
				_combine = DEFAULT_COMBINE;
			}

			const result = [ ... _parameters ];
			return result;
		}

		static parse3D(_parameters, _combine = DEFAULT_COMBINE, _throw = DEFAULT_THROW)
		{
			if(! isArray(_parameters, 16) || _parameters.length !== 16)
			{
				if(_throw)
				{
					throw new Error('The _parameters doesn\'t contain 16 parameters array for a 3D matrix');
				}

				return null;
			}
			else if(typeof _combine !== 'boolean')
			{
				_combine = DEFAULT_COMBINE;
			}

			const result = [ ... _parameters ];
		}

		static extract(_matrix, _deg = DEFAULT_DEGREES, _throw = DEFAULT_THROW)
		{
			if((_matrix = CSSMatrix.parse(_matrix, _throw)) === null)
			{
				return null;
			}
			else switch(CSSMatrix.type(_matrix, _throw))
			{
				case 'matrix':
					return CSSMatrix.extract2D(_matrix, _deg, _throw);
				case 'matrix3d':
					return CSSMatrix.extract3D(_matrix, _deg, _throw);
			}

			return null;
		}

		static extract2D(_matrix, _deg = DEFAULT_DEGREES, _throw = DEFAULT_THROW)
		{
			if((_matrix = CSSMatrix.parse(_matrix, _throw)) === null)
			{
				return null;
			}
			else if(_matrix.length !== 4 && _matrix.length !== 6)
			{
				if(_throw)
				{
					throw new Error('The _matrix is not a 2D matrix with 4/6 parameters');
				}

				return null;
			}

			const result = Object.create(null);
			
			for(var i = 0; i < _matrix.length; ++i)
			{
			}

			return result;
		}

		static extract3D(_matrix, _deg = DEFAULT_DEGREES, _throw = DEFAULT_THROW)
		{
			if((_matrix = CSSMatrix.parse(_matrix, _throw)) === null)
			{
				return null;
			}
			else if(_matrix.length !== 16)
			{
				if(_throw)
				{
					throw new Error('The _matrix is not a 3D matrix with 16 parameters');
				}

				return null;
			}

			const result = Object.create(null);

			for(var i = 0; i < _matrix.length; ++i)
			{
			}

			return result;
		}

		static rotation(_matrix, _deg = DEFAULT_DEGREES, _throw = DEFAULT_THROW)
		{
		}

		static rotation2D(_matrix, _deg = DEFAULT_DEGREES, _throw = DEFAULT_THROW)
		{
		}

		static rotation3D(_matrix, _deg = DEFAULT_DEGREES, _throw = DEFAULT_THROW)
		{
		}

		static create(_parameters, _throw = DEFAULT_THROW)
		{
		}

		static create2D(_parameters, _throw = DEFAULT_THROW)
		{
		}

		static create3D(_parameters, _throw = DEFAULT_THROW)
		{
		}

		static get matrix3D()
		{
			const result = [];

			result[0] = [ 'a1', 'a2', 'a3', 'a4' ];
			result[1] = [ 'b1', 'b2', 'b3', 'b4' ];
			result[2] = [ 'c1', 'c2', 'c3', 'c4' ];
			result[3] = [ 'd1', 'd2', 'd3', 'd4' ];

			return result;
		}

		static get matrix()
		{
			const result = [];

			result[0] = [ 'a', 'c', 'e' ];
			result[1] = [ 'b', 'd', 'f' ];
			//result[2] = [ 0, 0, 1 ];

			return result;
		}
	}

	//
	//todo/
	//
	Object.defineProperty(CSSStyleDeclaration.prototype, 'matrix', { value: function(_deg = DEFAULT_DEGREES, _throw = DEFAULT_THROW)
	{
		const transform = this.getPropertyValue('transform');
	}});

	Object.defineProperty(CSSStyleDeclaration.prototype, 'matrix3d', { value: function(_deg = DEFAULT_DEGREES, _throw = DEFAULT_THROW)
	{
		const transform = this.getPropertyValue('transform');
	}});

	//

})();

