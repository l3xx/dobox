
function Flash () {
	this._swf = '';
	this._width = 0;
	this._height = 0;
	this._params = new Array();
}

Flash.prototype.setSWF = function (_swf, _width, _height) {
	this._swf 	= _swf;
	this._width 	= _width;
	this._height 	= _height;
}

Flash.prototype.setParam = function (paramName, paramValue) {
	this._params[this._params.length] = paramName+'|||'+paramValue;
}

Flash.prototype.display = function () {
	var _txt = '';
	var params = '';
	_txt += '<object >\n';
	_txt += '<param width="'+this._width+'" height="'+this._height+'" name="movie" value="'+this._swf+'" />\n'
	_txt += '<param name="quality" value="high" />\n';
	for ( i=0;i<this._params.length;i++ ) {
		_param = this._params[i].split ('|||');
		_txt += '\t<param name="'+_param[0]+'" value="'+_param[1]+'" />\n';
		params += _param[0]+'="'+_param[1]+'" ';
	}

	_txt += '<embed width="'+this._width+'" height="'+this._height+'" src="'+this._swf+'" '+params+' quality="high" type="application/x-shockwave-flash"></embed>';
	_txt += '</object>';
	document.write (_txt);
}
