$(document).ready(function () {
    
    var url = $("#sendConversion").attr('action');
    var engines = null;
    var fileSet = false;

    var cleanIHM = function(init) {
	if (init) {
	    $("#sendConversion button").button().hide();
	}
	$( "input[type=submit], input[type=file]" ).button();
	$("#reqErrorMessage").hide();
	$("#uploadResult button").button().hide();
	$(".panel.result").hide();
	$(".panel.error").hide();
	$("#processinfo").empty();
	$(".mimeslist").hide();
    };
    
    if (!window.FormData) {
	alert('Upgrade your browser !'); 
	return;
    }

    cleanIHM(true);
    $('#thefile').prop('disabled', true);
    $('#engine').prop('disabled', true);


    $('#thefile').on('change', function() {
	fileSet = true;
	if ($("#engine option:selected").val() != "") $("#sendConversion button").button().show();
    });

    var logInfos = function(infos) { 
	var infos = infos;
	var now = new Date();
	var hh = now.getHours();
	hh = ( hh < 10 ? "0"+hh : hh );
	var mm = now.getMinutes();
	mm = ( mm < 10 ? "0"+mm : mm );
	var ss = now.getSeconds();
	ss = ( ss < 10 ? "0"+ss : ss );
	var dateStr = '[' + + ']';
	$('#processinfo').append(
	    $('<div /> ').addClass('task info').append(function() {
		var htmlContent = '<span class="date">'+hh+':'+mm+':'+ss+'</span>';
		htmlContent  += '<span class="status status_'+infos.status+'">'+infos.status+'</span>';
		[ 'engine', 'inmime' , 'comment' ].forEach( function( index ) {
		    htmlContent += '<span class="value '+index+'">'+infos[index]+'</span>';
		});
		return htmlContent;
	    })
	);
    };
    
    var updateStatus = function( tid ) {
	var formData = new FormData();
	formData.append("tid", tid);
	formData.append("op", "info");
	$.ajax({
            url: url,
            type: "POST",
            data: formData,
            processData: false,
	    contentType: false,
            success: function(data) {
		logInfos(data.info);
		if (data.info.status == 'D') {
		    $("#uploadResult button").button().show();
		    $("#uploadResult").on('submit', function() {
			$(this).attr('action', $(this).attr('action') + '&tid=' + tid);
			$("#uploadResult button").button().hide();
		    });
		} else if (data.info.status == 'W' || data.info.status == 'P' ) {
		    setTimeout( function() {
			updateStatus(data.info.tid);
		    }, 1000);
		} 
	    },
            error: function() { alert('error'); }
        });
    };

    $("#sendConversion").on("submit", function(event) {

	cleanIHM(false);

	$("#sendConversion button").button().hide();
	$(".panel.main")
	    .append(
		$('<div />')
		    .addClass('waiting')
		    .html("[TEXT:TE:Monitor:Convert:Starting conversion... sending file...]")
	    );

	var formData = new FormData(document.getElementById('sendConversion'));
	formData.append("op", "convert");
        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            processData: false,
	    contentType: false,
            success: function(data) {
		$(".panel.main .waiting").remove();
		if (!data.success) {
		    $("#reqErrorMessage").html(data.message);
		    $(".panel.error").show();
		} else {
		    $(".panel.result").show();
		    $('#tid').html(data.info.tid);
		    logInfos(data.info);
		    updateStatus(data.info.tid);
		}
	    },
            error: function() { 		
		$(".panel.main .waiting").remove();
		$(".panel.error").html("[TEXT:TE:Monitor:Convert:Oooops, something wrong happens]").css('display', 'block');
	    }
	});
	$('#sendConversion')[0].reset();
	event.preventDefault();
    });

    serverVersion.check( function( sr ) {
	if (sr.status == 0) {
	    globalMessage.show("[TEXT:TE:Monitor:not fully supported server version, need server version ]"+" "+sr.required+".", 'warning');
	} else if (sr.status == -1) {
	    globalMessage.show("[TEXT:TE:Monitor:server communication error]"+"<br/>"+sr.message+".", 'error');
	} else {

	    $('#thefile')
		.prop('disabled', true)
		.on('change', function() {
		    if (fileSet) $("#sendConversion button").button().show();
		});
		
	    $('#engine')
		.prop('disabled', false)
		.on('change', function() {
		    $("#engine-selection-guide").hide();
		    $("#mimeslist").empty();
		    var currentEngine = $("#engine option:selected").val()
		    if (engines[currentEngine] !== undefined) {
			$("#current-engine").html(currentEngine);
			for (var im=0; im<engines[currentEngine].mimes.length; im++) {
			    $("#mimeslist").append($("<li />").html(engines[currentEngine].mimes[im]));
			}
		    }
		    $(".mimeslist").show();
		    $('#thefile').prop('disabled', false);
		});
	    $.ajax({
		url: url+"&op=engines",
		type: "GET",
		success: function(data) {
		    if (!data.success) {
			$(".panel.error").html("[TEXT:TE:Monitor:server communication error]<br/>"+data.message).css('display', 'block');
		    } else {
			engines = data.info;
			$("#engine").append('<option id="engine-selection-guide" value="">[TEXT:TE:Monitor:Convert:select an engine]</option>');
			for (var key in data.info) {
			    if (data.info.hasOwnProperty(key)) {
				$("#engine").append("<option value='"+key+"'>"+key+"</option>");
			    }
			}
			$("#engine").css('display', 'inline');
		    }
		},
		error:  function() {
		    $(".panel.error").html("[TEXT:TE:Monitor:Oooops, something wrong happens]").css('display', 'block');
		}
	    });
	    
	}
    });



});
