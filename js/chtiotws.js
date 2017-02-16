// JavaScript Document
function chtiotws(_wsUri, _key) {
	this.ws = _wsUri;  //'wss://iot.cht.com.tw:443/iot/ws/rawdata';
	this.key = _key;
	//this.key = '';
	this.resources = [];
	this.websocket = null;
	this.startTime = null;
	this.endTime = null;
	this.updateSensorHandle = null;
	this.isClosed = false;
	console.log(LOG_TAG+' chtiotws constructor :'+this.server+", "+this.key);
}

// get all devices
chtiotws.prototype.start = function(_onOpen, _onClose, _onMessage, _onError)
{
	var self = this;
	console.log(self.ws);
	this.websocket = new WebSocket(self.ws);
	this.websocket.onopen = function(evt) { 
		self.onOpen(evt);
	};
    this.websocket.onclose = function(evt) {
        self.onClose(evt);
        if(!self.isClosed) setTimeout(self.start.bind(self), 1000);
    };
    this.websocket.onmessage = function(evt) {
        self.onMessage(evt);
    };
    this.websocket.onerror = function(evt) {
        self.onError(evt);
    };
};

chtiotws.prototype.onOpen = function(evt) {
	console.log(LOG_TAG+' websocket is connected');
	this.startTime = new Date().getTime();
	var wsMessage = {
		"ck": this.key,
		"resources":this.resources
	};
	console.log(LOG_TAG+' ws send message: '+JSON.stringify(wsMessage));
	this.send(JSON.stringify(wsMessage));
};

chtiotws.prototype.onClose = function(evt) {
	this.endTime = new Date().getTime();
	
	console.log(LOG_TAG+' websocket is closed');
	console.log("socket run time:"+ this.endTime +","+ this.startTime);
};

chtiotws.prototype.onMessage = function(evt) {
	console.log(LOG_TAG+' websocket receives message:'+evt.data);
	var result = JSON.parse(evt.data);
	if(this.updateSensorHandle != null) {
		this.updateSensorHandle(result);
	}
};

chtiotws.prototype.onError = function(evt) {
	console.log(LOG_TAG+' websocket has error' + evt.data);
};

chtiotws.prototype.send = function(message) {
	this.websocket.send(message);
};

chtiotws.prototype.addResource = function(_deviceId, _sensorId) {
	this.resources.push('/v1/device/'+_deviceId+'/sensor/'+_sensorId+'/rawdata');
};

chtiotws.prototype.setHandle = function (_handle) {
	this.updateSensorHandle = _handle;
};

chtiotws.prototype.close = function() 
{
	this.isClosed = true;
	this.websocket.close(); 
};

