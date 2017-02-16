<?php
include_once "auth.php";
$auth = new Auth;
$APIKEY = $auth->getApiKey();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>我的 IoT 設備</title>

    <!-- Bootstrap Core CSS -->
    <link href="./bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href="./bower_components/metisMenu/dist/metisMenu.min.css" rel="stylesheet">

    <!-- Timeline CSS -->
    <link href="./css/timeline.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="./css/sb-admin-2.css" rel="stylesheet">

    <!-- Morris Charts CSS -->
    <link href="./bower_components/morrisjs/morris.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="./bower_components/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

	<link href="./css/chtiot.css" rel="stylesheet">
	
	<link href="./css/jquery.switchButton.css" rel="stylesheet">
	
	<link rel="stylesheet" type="text/css" href="./bower_components/bootstrap-daterangepicker/daterangepicker.css" />
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
	<style type="text/css">
    </style>
</head>

<body>
    <div id="wrapper">
        <!-- Navigation -->
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <?php include_once('header.php');?>

            <?php include_once('menu.php'); ?>
        </nav>
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">我的 IoT 設備</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            
            <div class="row">
            	<div class="col-lg-3">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <i class="fa fa-ticket fa-fw"></i> 設備列表
                            <div class="pull-right">
                            	<div class="btn-group">
                            		
                            	</div>
                            </div>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                        	<div id="devicescontainer"> 
                        	</div>
                            <div class="list-group">
                            </div>
                            <!-- /.list-group --> 
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-4 -->
                
                <div class="col-lg-9">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <i class="fa fa-ticket fa-fw"></i> <span id="selectedDeviceName"></span>
                            <div class="pull-right">
                            	<div class="btn-group">
                            		
                            	</div>
                            </div>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body" id="sensors-container">
                        </div>
                        <!-- /.panel-body -->
                        <div class="panel-footer">
                        	
                        </div>
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-8 -->
            </div>
            
        </div>
        <!-- /#page-wrapper -->
		
    </div>
    <!-- /#wrapper -->

    <!-- jQuery -->
    <script src="./bower_components/jquery/dist/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="./bower_components/bootstrap/dist/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="./bower_components/metisMenu/dist/metisMenu.min.js"></script>

    <!-- Morris Charts JavaScript -->
    <script src="./bower_components/raphael/raphael-min.js"></script>
    <script src="./bower_components/morrisjs/morris.min.js"></script>
    <!--<script src="./js/morris-data.js"></script>-->  
    <!-- Custom Theme JavaScript --> 
    <script src="./js/sb-admin-2.js"></script>
    
    <script type="text/javascript" src="./js/global.js"></script>
    <script type="text/javascript" src="./js/md5.min.js"></script>
    <script type="text/javascript" src="./js/util.js"></script>
    
    <!-- Switch button -->
    <script type="text/javascript" src="./js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="./js/jquery.switchButton.js"></script>
    
    <script type="text/javascript" src="./js/chtiot.js"></script>
    <script type="text/javascript" src="./js/chtiotws.js"></script>
    
    <script type="text/javascript" src="./js/Chart.js"></script>
    
    <script type="text/javascript" src="./bower_components/bootstrap-daterangepicker/moment.min.js"></script>
    <script type="text/javascript" src="./bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>

    
    <script type="text/javascript">
    var API_SERVICE = 'https://iot.cht.com.tw/iot/';
    var WS_SERVICE = 'wss://iot.cht.com.tw:443/iot/ws/rawdata';
    //var API_SERVICE = 'http://211.20.181.196/iot/';
    //var WS_SERVICE = 'ws://211.20.181.196:80/iot/ws/rawdata';
    var API_KEY = '<?=$APIKEY?>';
    
    //
    var CHART_PREFIX = 'chart-';
    var SWITCH_PREFIX = 'switch-';
    var SENSOR_PREFIX = 'sesnor-';
    
    var iot;
    var iotws;
    var currentDeviceId;
    var currentSensorRawdata = [];
    
	$(document).ready(function() {
		iot = new chtiot(API_SERVICE, API_KEY);
		iot.showDevices($('#devicescontainer'), function(event){  //add click event
				event.preventDefault();
				
				currentSensorRawdata = [];
				iotws.close(); // 關閉iotws
				
				window.localStorage.removeItem("DEVICE_DATA_ID");
				window.localStorage.removeItem("DEVICE_DATA_NAME");
				currentDeviceId = $(this).data('id');
				window.localStorage.setItem("DEVICE_DATA_ID", currentDeviceId);
				window.localStorage.setItem("DEVICE_DATA_NAME", $(this).data('name'));
				
				$('.iot-device-list-selected').removeClass('iot-device-list-selected');
				$(this).addClass('iot-device-list-selected');
				$('#selectedDeviceName').html($(this).data('name'));
				iot.getSensors($(this).data('id'), function(data){
					showSensor(data);
				});
			}, function(devices) {
				if(devices.length > 0){
					currentDeviceId = devices[0].id;
					iot.getSensors(currentDeviceId, function(data){
						showSensor(data);
					});
				}
			});		
	});

	// websocekt
	var wsUpdateSeneor = function(result){
		if(result != null){
			var needUpdate = false;
			if(typeof currentSensorRawdata[result.id] === 'undefined'){
				needUpdate = true;
			}else{
				if(currentSensorRawdata[result.id].time != result.time){
					needUpdate = true;
				}
			}
			
			if(needUpdate) {
				currentSensorRawdata[result.id] = result;
				if($('#'+SENSOR_PREFIX+result.id).data('type').match(/switch/i)){
					updateSwitch(result);
				}else if($('#'+SENSOR_PREFIX+result.id).data('type').match(/gauge/i)){
					updateChart(result);
				}else if($('#'+SENSOR_PREFIX+result.id).data('type').match(/text/i)){
					$('#'+SENSOR_PREFIX+result.id).html(result.value.toString());	
				}
			}
		}
	}
	
	var showSensor = function(sensors){
		// init. websocket
		iotws = new chtiotws(WS_SERVICE, API_KEY);
		iotws.setHandle(wsUpdateSeneor);
		
		console.log(JSON.stringify(sensors));
		$('#sensors-container').empty();
		$.each(sensors , function(i,sensor){
		
			// websocket add sensor
			iotws.addResource(currentDeviceId, sensor.id);
		
			//{"id":"ip","name":"ip","desc":"記錄開機wlan0的ip位置","type":"gauge","uri":"XCUP4WUR937XUH4U","unit":"","formula":""}
			var widthSensor = 'col-lg-4';
			if(sensor.type.match(/gauge/i)){
				widthSensor = 'col-lg-9';
			}else if(sensor.type.match(/snapshot/i)){
				widthSensor = 'col-lg-8';
			}
			html = '';
			html += '<div class="'+widthSensor+'">';
			html += '<div class="panel panel-default">';
			/* .panel-heading */
			html += '<div class="panel-heading">';
			html += '<i class="fa fa-ticket fa-fw"></i> <span id="sensor-'+sensor.id+'-title">' + sensor.name + '</span>';
			html += '<div class="pull-right">';
			html += '<div class="btn-group">';
			html += '</div>';
			html += '</div>';
			html += '</div>';
			
			/* .panel-body */
			html += '<div class="panel-body">';
			html += '<div id="sensor-'+ sensor.id +'-value" style="text-align:center;">';
			
			if(sensor.type.match(/gauge/i)){
				html += '<div class="col-lg-6 col-lg-offset-3" ><input type="text" name="daterange-'+sensor.id+'" id="daterange-'+sensor.id+'" class="form-control" value="" /></div>';
				html += '<canvas id="'+SENSOR_PREFIX + sensor.id +'" data-type="'+sensor.type+'" width="400" height="250"></canvas>';
			}else if(sensor.type.match(/switch/i)){
				html += '<input type="checkbox" id="'+SENSOR_PREFIX + sensor.id +'" data-type="'+sensor.type+'" data-from="" value="1" checked>';
			}else if(sensor.type.match(/text/i)){
				html += '<span id="'+SENSOR_PREFIX + sensor.id +'" data-type="'+sensor.type+'" ></span>';
			}else if(sensor.type.match(/snapshot/i)){
				html += '<img id="'+SENSOR_PREFIX + sensor.id +'" data-type="'+sensor.type+'" src="img/noimage.png" style="max-width:100%; max-height:300px; margin:0px auto;"/>';
			}
			
			html += '</div>';
			html += '</div>';
			
			/* .panel-footer */
			html += '<div class="panel-footer">';
			html += '</div>';
			
			html += '</div>';
			html += '</div>';
			
			$('#sensors-container').append(html);
			
			if(sensor.type.match(/gauge/i)){
				var m = moment();    // get "now" as a moment
				var isoCurrentDateTime = moment.utc(m).format();
				isoStartDateTime  = isoCurrentDateTime.substring(0, 11)+'00:00:00Z';
				$('#daterange-'+sensor.id).daterangepicker({
					"timePicker": true,
					"timePicker24Hour": true,
					//timePickerIncrement: 30,
					locale: {
					            format: 'YYYY-MM-DD HH:mm:ss'
					        },
				    "startDate": moment( isoStartDateTime, "YYYY-MM-DDTHH:mm:ssZ" ).format("YYYY-MM-DD HH:mm:ss"),
				    "endDate": moment( isoCurrentDateTime, "YYYY-MM-DDTHH:mm:ssZ" ).format("YYYY-MM-DD HH:mm:ss")
				    
				}, function(start, end, label) {
					console.log("New date range selected: " + moment.utc(start).format() + " to " + moment.utc(end).format());
					initChart(sensor.id, moment.utc(start).format(), moment.utc(end).format());
				});
				
				initChart(sensor.id,isoStartDateTime,isoCurrentDateTime);
			}else if(sensor.type.match(/switch/i)) {
				initSwitch(sensor.id);
			}else if(sensor.type.match(/text/i)) {
				
			}else if(sensor.type.match(/snapshot/i)) {
				iot.getLatestSnapshot(window.localStorage.getItem("DEVICE_DATA_ID"), sensor.id, function(_url){
					var img = document.getElementById(SENSOR_PREFIX+sensor.id);
					img.src = _url;
				});
			}
		});	
		
		// websocket start
		iotws.start();
	};
	
	var returnData = function(data){
	    if(typeof data.ok != 'undefined'){
			if(data.ok.match(/false/i)){
			alert(data.message);
			}else{
				alert(JSON.stringify(data));
			}
		}else{
			alert("Null");
		}
	}
	
	var imgError = function(image) {
		image.onerror = "";
		image.src = "./img/noimage.png";
		return true;
	}	
	
	
	// switch part
	var initSwitch = function(sensorId) {
		$('#'+SENSOR_PREFIX+sensorId).switchButton({
		  width: 100,
		  height: 40,
		  button_width: 50,
		  on_label: '開',
  		  off_label: '關'
		});
		
		$('#'+SENSOR_PREFIX+sensorId).on('change', function(){
			if($(this).data("from").match(/ws/i)){
				$(this).data("from","");
			}else{
				var value = $(this).val();
				
				if( value == 0 ) {
					value = 1;
				}else{
					value = 0;
				}
				var state = false;
				if(value == 1){
					state = true;
				}
				$(this).val(value);
				
				iot.saveRawdata(currentDeviceId, sensorId, value);
			}
		});
	};
	
	var updateSwitch = function(result){
		var state = false;
		var cValue = $('#'+SENSOR_PREFIX+result.id).val();
		if(cValue != result.value[0]){
			if(result.value[0] == "1" || result.value[0] == "on"){
				state = true;
			}
			$('#'+SENSOR_PREFIX+result.id).val(result.value[0]);
			$('#'+SENSOR_PREFIX+result.id).data("from", "ws");
			$('#'+SENSOR_PREFIX+result.id).switchButton({
				checked: state
			});
		}
	};

	// chart part
	var charts = {};
	var optionChart = {
		showLines: true
	};

	var initChart = function(sensorId, startTime, endTime) {
		//alert('initChart:'+currentDeviceId+', '+sensorId);
		$('#'+SENSOR_PREFIX + sensorId).empty();
		var canvas = document.getElementById(SENSOR_PREFIX + sensorId);
		
		var isoEndDateTime = typeof endTime !== 'undefined' ?  endTime: null;
		var isoStartDateTime = typeof startTime !== 'undefined' ?  startTime: null;
		if(isoStartDateTime == null){
			var m = moment();    // get "now" as a moment
			var isoCurrentDateTime = moment.utc(m).format();
			isoStartDateTime  = isoCurrentDateTime.substring(0, 11)+'00:00:00Z';
		}
	
		//$('input[name="daterange-'+sensorId+'"]').val(isoStartDateTime + "~"+isoCurrentDateTime);
		// 要先取得sensor rawdata, create label and data
		var rlabel = [];
		var rdata = [];
		iot.getRawdata(currentDeviceId, sensorId, isoStartDateTime, isoEndDateTime, function(data){
			$.each(data , function(i,rawdata){
				if(rawdata.value.length == 1) {
					if(!isNaN(rawdata.value[0])){
						rdata.push(rawdata.value[0]);
						//moment( isoStartDateTime, "YYYY-MM-DDTHH:mm:ssZ" ).format("YYYY-MM-DD HH:mm:ss")
						rlabel.push(timelabel(moment( rawdata.time, "YYYY-MM-DDTHH:mm:ssZ" ).format("YYYY-MM-DD HH:mm:ss")));
					}
				}
				if(i==0) currentSensorRawdata[sensorId] = rawdata;
			});
			
			var chartdata = {
			    //labels: rlabel.reverse(),
			    labels: rlabel,
			    datasets: [
			        {
			            label: $('#sensor-'+sensorId+'-title').html(),
			            fill: false,
			            lineTension: 0.1,
			            backgroundColor: "rgba(75,192,192,0.4)",
			            borderColor: "rgba(75,192,192,1)",
			            borderCapStyle: 'butt',
			            borderDash: [],
			            borderDashOffset: 0.0,
			            borderJoinStyle: 'miter',
			            pointBorderColor: "rgba(75,192,192,1)",
			            pointBackgroundColor: "#fff",
			            pointBorderWidth: 1,
			            pointHoverRadius: 5,
			            pointHoverBackgroundColor: "rgba(75,192,192,1)",
			            pointHoverBorderColor: "rgba(220,220,220,1)",
			            pointHoverBorderWidth: 2,
			            pointRadius: 5,
			            pointHitRadius: 10,
			           // data: rdata.reverse()
			            data: rdata
			        }
			    ]
			};
			var chartoption = {
				showLines: true
			};
			var sensorChart = Chart.Line(canvas,{
				data:chartdata,
			  	options:chartoption
			});
			charts[sensorId] = sensorChart;
		});	
	};
	
	var updateChart = function(result){
		var pChart = charts[result.id];
		if(typeof pChart.data !== 'undefined'){
			//alert(pChart.data.datasets[0].data.length);
			pChart.data.datasets[0].data.push(result.value[0]);
			pChart.data.labels.push(timelabel(moment( result.time, "YYYY-MM-DDTHH:mm:ssZ" ).format("YYYY-MM-DD HH:mm:ss")));
			pChart.update();
		}
	};
	
	var timelabel = function(iso){
		return iso.substring(11,19);
	}
    </script>
</body>

</html>
