// JavaScript Document
function chtiot(_server, _key) {
	this.server = _server;
	this.key = _key;
	
	console.log(LOG_TAG+' chtiot constructor :'+this.server+", "+this.key);
}

/*
 * 設備 devices 
 */
// get all devices
chtiot.prototype.getDevices = function(_success, _error)
{
	_error = typeof _error !== 'undefined' ? _error : this.ajaxDefaultErrorHandler;
	this.ajaxRest('v1/device','GET',null,_success,_error);
};

// show all devices 
chtiot.prototype.showDevices = function(_dom, _event)
{
	this.getDevices(function(devices){
		//console.log(LOG_TAG+' getDevices result: '+JSON.stringify(devices));
		var html = '';
		$.each(devices, function(i,device){
			html += '<div class="iot-device-list" data-id="'+device.id+'" data-name="'+device.name+'">';
			html += '<img class="iot-device-list-img" src="img/image_gateway.png" />';
			html += '<div class="iot-device-list-content">';
			html += '<div class="iot-device-list-name">'+device.name+'</div>';
			html += '<div class="iot-device-list-desc">'+device.desc+'</div>';
			html += '</div>';
			html += '</div>';
		});
		_dom.html(html);
		
		$('.iot-device-list').unbind('click');
		$('.iot-device-list').bind('click', _event);
		
	});
};

chtiot.prototype.postDevice = function(_data, _success, _error)
{
	 console.log(LOG_TAG+' postDevice ');
	/* format:
	 * {
	 *  "name": "Hygrometer",
	 *  "desc": "Your Hygeometer",
	 *  "type": "general",
	 *  "uri":"http://a.b.c.d/xxx",
	 *  "lat":24.95,
	 *  "lon":121.16,
	 *  "attributes":
	 *  [
	 *    {
	 *      "key":"label",
	 *      "value":"溫濕度計"
	 *    }
	 *  ]
	 * }
	 */
	_error = typeof _error !== 'undefined' ? _error : this.ajaxDefaultErrorHandler;
	_data.type = typeof _data.type !== 'undefined' ? _data.type : "general";
	console.log(LOG_TAG+' data '+JSON.stringify(_data));
	this.ajaxRest('v1/device/','POST',JSON.stringify(_data),_success,_error);
}

// get specific device
chtiot.prototype.getDevice = function(_device_id,_success, _error)
{
	_error = typeof _error !== 'undefined' ? _error : this.ajaxDefaultErrorHandler;
	this.ajaxRest('v1/device/'+_device_id,'GET',null,_success,_error);
};

/*
 * 感測器 sensor 
 */
// get all sensors of specific device
chtiot.prototype.getSensors = function(_device_id,_success, _error)
{
	_error = typeof _error !== 'undefined' ? _error : this.ajaxDefaultErrorHandler;
	this.ajaxRest('v1/device/'+_device_id+'/sensor','GET',null,_success,_error);
};

// get specific sensor of specific device
chtiot.prototype.getSensor = function(_sensor_id,_device_id,_success, _error)
{
	_error = typeof _error !== 'undefined' ? _error : this.ajaxDefaultErrorHandler;
	this.ajaxRest('v1/device/'+_device_id+'/sensor/'+_sensor_id,'GET',null,_success,_error);
};

chtiot.prototype.postSensor = function(_device_id, _data, _success, _error)
{
	 console.log(LOG_TAG+' postSensor ');
	/* format:
	 * {
	 *  "id": "sensorid",
	 *  "name": "Hygrometer",
	 *  "desc": "Your Hygeometer",
	 *  "type": "gauge" or "counter" or "switch",
	 *  "unit": "degree",
	 *  "formula": "formula /100.0",
	 *  "attributes":
	 *  [
	 *    {
	 *      "key":"label",
	 *      "value":"溫濕度計"
	 *    }
	 *  ]
	 * }
	 */
	_error = typeof _error !== 'undefined' ? _error : this.ajaxDefaultErrorHandler;
	_data.type = typeof _data.type !== 'undefined' ? _data.type : "general";
	console.log(LOG_TAG+' data '+JSON.stringify(_data));
	this.ajaxRest('v1/device/'+_device_id+'/sensor','POST',JSON.stringify(_data),_success,_error);
}


/*
 * 數據資料 rawdata 
 */
// get latest rawdata of specific sensor
chtiot.prototype.getLatestRawdata = function(_device_id,_sensor_id,_success, _error)
{
	_error = typeof _error !== 'undefined' ? _error : this.ajaxDefaultErrorHandler;
	this.ajaxRest('v1/device/'+_device_id+'/sensor/'+_sensor_id+'/rawdata','GET',null,_success,_error);
};

// save rawdata for single sensor
chtiot.prototype.saveRawdata = function(_device_id, _sensor_ids, _value, _time, _success, _error)
{
	console.log(LOG_TAG+' saveRawdata ');
	_error = typeof _error !== 'undefined' ? _error : this.ajaxDefaultErrorHandler;
	var request = [];
	$.each(_sensor_ids, function(i,id){
		
		var data = new Object();
		data.id = id;
		if(_time !== null && _time !== ''){
			data.time = _time;
		}
		data.value = [_value];
		request.push(data);
	});
	console.log(LOG_TAG+' request '+JSON.stringify(request));
	this.ajaxRest('v1/device/'+_device_id+'/rawdata','POST',JSON.stringify(request),_success,_error);
}

// show latest rawdata
chtiot.prototype.showRawdataUI = function(_sensor_id,_rawdata, _type, _unit, _formula)
{
	var html = '';
	if(_type.match(/switch/i)) {
		//return 'switch: '+_rawdata;
		html += '<select class="selector switch-selector" data-role="slider" data-id="'+_sensor_id+'" >';
	    html += '<option value="0" >OFF</option>';
		if(_rawdata.length == 0 ){
			html += '<option value="1" >ON</option>';
		}else{
			if(_rawdata[0].match(/1/i))
			{
				html += '<option value="1" selected="selected">ON</option>';
			}else{
				html += '<option value="1" >ON</option>';
			}
		}
	    
		html += '</select>';
	}else if(_type.match(/gauge/i)) {
		var ds = '';
		if(_rawdata.length ==0){
			ds = "&nbsp;-&nbsp;";
		}else if(_rawdata.length > 1){
			ds = "[multi-value]"
		}else{
			ds = _rawdata[0];
		}
		//return 'gauge: '+_rawdata+_unit ;
			html += '<a href="javascript:setValue(\''+_sensor_id+'\');" data-rel="popup" data-position-to="window" class="ui-btn ui-corner-all setSensorValue" data-transition="pop" ><span class="gauge-text" >'+ds+'</span><span class="unit-text" >'+_unit+'</span></a>';
	}else if(_type.match(/counter/i)) {
		//return 'gauge: '+_rawdata+_unit ;
		var ds = '';
		if(_rawdata.length ==0){
			ds = "&nbsp;-&nbsp;";
		}else{
			ds = _rawdata[0];
		}
		html +=  '<a href="javascript:setValue(\''+_sensor_id+'\');" data-rel="popup" data-position-to="window" class="ui-btn ui-corner-all setSensorValue" data-transition="pop" ><span class="counter-text" >'+ds+'</span></a>' ;
		
	}
	
	return html;
};

/*
 * 影像資料 snapshot 
 */
// get latest snapshot meta of specific sensor
chtiot.prototype.getLatestSnapshotMeta = function(_device_id,_sensor_id,_success, _error)
{
	_error = typeof _error !== 'undefined' ? _error : this.ajaxDefaultErrorHandler;
	this.ajaxRest('v1/device/'+_device_id+'/sensor/'+_sensor_id+'/snapshot/meta','GET',null,_success,_error);
};

// get latest snapshot photo of specific sensor
chtiot.prototype.getLatestSnapshot = function(_device_id,_sensor_id, _success)
{
	_err_img = typeof _err_img !== 'undefined' ? _err_img : 'img/noimage.png';
	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function(){
		if (xhr.readyState == 4 && xhr.status == 200){
			//this.response is what you're looking for handler(this.response);
			//console.log(LOG_TAG+' > '+xhr.response, typeof xhr.response);
			var url = window.URL || window.webkitURL;
			_success(url.createObjectURL(xhr.response));
			console.log(LOG_TAG +' getLatestSnapshot finish!');
		}
	}
	xhr.open('GET', this.server + 'v1/device/'+_device_id+'/sensor/'+_sensor_id+'/snapshot');
	xhr.responseType = 'blob';
	xhr.setRequestHeader("CK", this.key);
	xhr.send();      
};

chtiot.prototype.getSnapshot = function(_device_id,_sensor_id,_snapshot, _success)
{
	_err_img = typeof _err_img !== 'undefined' ? _err_img : 'img/noimage.png';
	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function(){
		if (xhr.readyState == 4 && xhr.status == 200){
			//this.response is what you're looking for handler(this.response);
			//console.log(LOG_TAG+' > '+xhr.response, typeof xhr.response);
			var url = window.URL || window.webkitURL;
			_success(url.createObjectURL(xhr.response));
			console.log(LOG_TAG +' getLatestSnapshot finish!');
		}
	}
	xhr.open('GET', this.server + 'v1/device/'+_device_id+'/sensor/'+_sensor_id+'/snapshot/'+_snapshot);
	xhr.responseType = 'blob';
	xhr.setRequestHeader("CK", this.key);
	xhr.send();      
};



/* registry */
chtiot.prototype.reconfigure = function(_serial_id,_digest,_success, _error)
{
	console.log(LOG_TAG+' reconfigure ');
	_error = typeof _error !== 'undefined' ? _error : this.ajaxDefaultErrorHandler;
	
	
	var data = new Object();
	data.op = "Reconfigure";
	data.digest = _digest;
	
	
	console.log(LOG_TAG+' request: '+_serial_id + ' data: '+JSON.stringify(data));
	this.ajaxRest('v1/registry/'+_serial_id,'POST',JSON.stringify(data),_success,_error);
};

chtiot.prototype.setDeviceId = function(_serial_id,_digest,_device_id,_success, _error)
{
	console.log(LOG_TAG+' reconfigure ');
	_error = typeof _error !== 'undefined' ? _error : this.ajaxDefaultErrorHandler;
	
	var data = new Object();
	data.op = "setDeviceId";
	data.digest = _digest;
	data.deviceId = _device_id;
	
	console.log(LOG_TAG+' request '+JSON.stringify(data));
	this.ajaxRest('v1/registry/'+_serial_id,'POST',JSON.stringify(data),_success,_error);
};

/* thing */
chtiot.prototype.getThings = function(_success, _error)
{
	_error = typeof _error !== 'undefined' ? _error : this.ajaxDefaultErrorHandler;
	this.ajaxRest('v1/thing','GET',null,_success,_error);
};

chtiot.prototype.getThing = function(_sn, _digest, _success, _error)
{
	_error = typeof _error !== 'undefined' ? _error : this.ajaxDefaultErrorHandler;
	this.ajaxRest('v1/thing/'+_sn+'?digest='+_digest,'GET',null,_success,_error);
};

chtiot.prototype.getProduct = function(_id, _success, _error)
{
	_error = typeof _error !== 'undefined' ? _error : this.ajaxDefaultErrorHandler;
	this.ajaxRest('v1/product/'+_id,'GET',null,_success,_error);
};

/* ajaxRest */
chtiot.prototype.ajaxRest = function(_resource ,_method, _data ,_success, _error)
{
	console.log(LOG_TAG+' ajaxRest '+this.server+_resource+', '+_method);
	$.ajax({
			url: this.server + _resource,
			method: _method, 						//GET/POST/PUT/DELETE
			data: _data,
			contentType: 'application/json', 	//contentType: "application/json; charset=utf-8",
			headers:{
				Accept: 'application/json', 		//Accept: 'application/xml'
				CK: this.key
			},
			success: _success,
			beforeSend: function(jqXHR, settings) {
        		jqXHR.url = settings.url;
    		},
			error:_error
		});
};

chtiot.prototype.ajaxDefaultErrorHandler = function(jqXHR, textStatus, errorThrown){
		console.log(LOG_TAG+' ajax default error jqXHR > '+jqXHR.responseText);
		console.log(LOG_TAG+' ajax default error jqXHR url > '+jqXHR.url);
		console.log(LOG_TAG+' ajax default error textStatus > '+textStatus);
		console.log(LOG_TAG+' ajax default error errorThrown > '+errorThrown);
};

chtiot.prototype.getSensorIcon = function(_type){
	if(_type.match(/snapshot/i)){
		return 'image_snapshot.png';
	}else if(_type.match(/gauge/i)){
		return 'image_gauge.png';
	}else if(_type.match(/switch/i)){
		return 'image_switch.png';
	}else if(_type.match(/counter/i)){
		return 'image_counter.png';
	}else{
		return 'image_sensor.png';
	}
};