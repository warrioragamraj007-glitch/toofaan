<style>
#filt  th.cell {
    font-size: 14px!important;
    line-height: 25px!important;
    font-weight: normal!important;
    text-align: left !important;
    padding: 2px !important;
}
table.quizreviewsummary td.cell {
    font-size: 14px!important;
    line-height: 25px!important;
    font-weight: normal!important;
    text-align: left !important;
}
.reports-page #filt {
    margin-bottom: 0px !important;
}


#close {
    float:right;
    display:inline-block;
    padding:2px 6px;
    
    color:white;
    font-weight:bold;
   margin-right: 5px;
}
.highlighted  {
  border:2px solid #E5B467 !important;
}
#navbar{
		margin-top:2%;
		margin-left:6%;
		margin-bottom:1%;
	}
#close{
cursor:pointer;
font-size: 14px;
}

.course-list-table thead tr th {
    background-color: #ea6645;
    color: #FFF !important;
text-align:center !important;
}
.course-list-table tbody tr td {
  
text-align:center !important;
}
  span.studentdiv
            {
                float: left;
		background-color: #d7d5d5 !important;
		width: 96%;
		word-wrap: break-word;
		margin-bottom: 5px;
		padding: 3px;
		border-radius: 3px;
		background-color: #dbedfe !important;

            }
#chatview{
display: block;
float: right;
width: 25%;
position: absolute;
background-color: white;
left: 40%;
top:25%;
}
           span.agentdiv
            {
                float: right;
background-color: #d7d5d5 !important;
width: 96%;
word-wrap: break-word;
margin-bottom: 5px;
padding: 3px;
border-radius: 3px;
            }

            .clear
            {
                clear: both;
            }
		#messages{
		background-color: #f2f2f2;
		}


</style>
<?php
require_once(dirname(__FILE__) . '/../../config.php');
$PAGE->requires->js('/student/jquery-latest.min.js',true);

$PAGE->requires->css('/teacher/tareports/tastyles.css');
$PAGE->requires->css('/teacher/reports/reports.css');
$PAGE->set_url('/teacher/tareports/reports.php');
require_once($CFG->dirroot .'/webrtc/webrtcutil.php');
echo "<input id='baseurl' type='hidden' value=".$CFG->wwwroot ."/>";
/*****************************GETTING AGENT NAME COURSE NAME AND ACTIVITY NAME **************/
require_login();
if (!(user_has_role_assignment($USER->id,2) ) ) {

            redirect($CFG->baseUrl);
}

$agent_Id=required_param('agent',PARAM_INT);
$course_Id=required_param('cid',PARAM_INT);
$activity_Id=required_param('aid',PARAM_INT);
$user_obj=get_complete_user_data('id', $agent_Id);

$agentName= $user_obj->firstname." ".$user_obj->lastname;
$course_name=get_course_name($course_Id);
if($activity_Id)
	$activity_name=get_tast_name($activity_Id);
	else 
		$activity_name='--';
echo $OUTPUT->header();
?>
<div id="navbar" class="col-md-12">
	<a id='dlink' href='<?php echo $CFG->portal.'registration/login.php'?>'>Dashboard</a> <span>/</span>
<a id='dlink' href='<?php echo $CFG->wwwroot?>'>Supervisor Dashboard</a> <span>/</span> <a id='dlink' href='<?php echo $CFG->wwwroot.'/supervisor/tareports/reports.php'?>'>TA Reports</a> <span>/</span> <b>TA Activity Report</b>
</div>
<?php

echo  '<div class="container">';
echo   "<div class='span12 content' >";
echo '
		<div class="stepout" style="width: 57%;display:inline-block;border-right: 2px solid rgb(204, 204, 204);">
<div style="border-bottom: 2px solid rgb(204, 204, 204);">
<div class="title" style="width:15%;display:inline-block;">Agent : </div>
		<div style="width:84%;display:inline-block;" id="agentname" class="cvalue">'.$agentName.'</div></div>
		<div style="border-bottom: 2px solid rgb(204, 204, 204);">	
<div class="title" style="width:15%;display:inline-block;">Course : </div> 
		<div  style="width:84%;display:inline-block;" id="scour"  class="cvalue">'.$course_name.'</div>
	</div>
<div class="title" style="width:15%;display:inline-block;">Activity : </div> 
		<div  style="width:84%;display:inline-block;"  id="actvityname" class="cvalue" >'.$activity_name.'</div>
</div>
		';
		echo "<div style='float:right;width: 42% !important;padding-top: 10px;padding-left: 7px;padding-right: 2px;'>";

echo "<div  style='float:left;width:30% !important;font-size: 16px;'>Course : </div><div id='activitiesdiv' style='margin-bottom: 5px;'></div>";
echo '<div  style="float:left;width:30% !important;font-size: 16px;">Call Status :</div><div> <select class="state" id="state" ><option value="0">Select Status</option><option value="1">
Ignored</option><option  value="2">Answered</option><option  value="3">Dropped</option><option  value="0">All</option></select>';
echo   "</div></div></div>";
echo   "<div class='span12' style='margin-top: 10px;margin-bottom:20px;margin-left: 0%;width:100%;border:1px solid #ccc;border-radius:3px;'>";
echo "<div id='res' ></div>";
echo "<div id='chatview' style='display:none;float:right'>
		<span id='close'>x</span>
		<div id='chattrs'></div></div></div>";


echo   "</div>";
echo   "</div></div>";
echo $OUTPUT->footer();
?>
<script>
$(document).ready( function() {
	var baseUrl=$('#baseurl').val();
	
    var url=baseUrl+'teacher/dashboard.php';
    var urla=baseUrl+'teacher/tareports/reports.php';
    $('#page-navbar').append("<div style='padding:6px;'> <a id='dlink'>Dashboard</a> <span>/</span> <a id='adlink'>Assistants </a> <span>/</span><b> <?php echo $agentName;?></b></div>");
    $('#dlink').attr("href",url);
    $('#adlink').attr("href",urla);

var ds = (function(a) {
    if (a == "") return {};
    var b = {};
    for (var i = 0; i < a.length; ++i)
    {
        var p=a[i].split('=', 2);
        if (p.length == 1)
            b[p[0]] = "";
        else
            b[p[0]] = decodeURIComponent(p[1].replace(/\+/g, " "));
    }
    return b;
})(window.location.search.substr(1).split('&'));

var agent=ds["agent"]; // 1337
var cid=ds["cid"]; // 1337
var aid=ds["aid"]; // 1337


$.ajax({
    url: baseUrl+"/teacher/tareports/resportsUtil.php",
    data: {
        "id": 1,
       "cid":cid
      
        
    },
    type: "GET",
    dataType: "html",
    success: function (data) {
        var result = $('#activitiesdiv').html(data);
       

    },
    error: function (xhr, status) {
        //alert("Sorry, there was a problem!");
    },
    complete: function (xhr, status) {
    	$("#activities").val(aid);
    
         // 

    	$('#activities').change(function(){

    		
    		var agent=ds["agent"]; // 1337
    		var cid=ds["cid"]; // 1337
    		var aid=$("#activities option:selected" ).val();
    		var state=$("#state option:selected" ).val();
    		//getting activities under course 

    		    	 $("#actvityname").html($("#activities option:selected" ).text());
    		$.ajax({
    		    url: baseUrl+"/teacher/tareports/resportsUtil.php",
    		    data: {
    		        "id": 3,
    		       "agent":agent,
    		       "aid":aid,
    		       "state":state
    		        
    		    },
    		    type: "GET",
    		    dataType: "html",
    		    success: function (data) {
    		      
    		       
    		    	var result = $('#res').html(data);
    		    },
    		    error: function (xhr, status) {
    		        //alert("Sorry, there was a problem!");
    		    },
    		    complete: function (xhr, status) {
    		    	 
    		    	
    		    }
    		});
    	});
    }
});

$('#state').change(function(){

	
	var agent=ds["agent"]; // 1337
	var cid=ds["cid"]; // 1337
	var aid=$("#activities option:selected" ).val();
	var state=$("#state option:selected" ).val();
	//getting activities under course 

	$.ajax({
	    url: baseUrl+"/teacher/tareports/resportsUtil.php",
	    data: {
	        "id": 3,
	       "agent":agent,
	       "aid":aid,
	       "state":state
	        
	    },
	    type: "GET",
	    dataType: "html",
	    success: function (data) {
	      
	       
	    	var result = $('#res').html(data);
	    },
	    error: function (xhr, status) {
	        //alert("Sorry, there was a problem!");
	    },
	    complete: function (xhr, status) {
	    	 
	    	  $(document).delegate("#rowclick tbody tr","click", function() {
	        		 var id = $(this).attr('class');
	   
	        		if(id==0)
	        		{
	            		alert("Sorry There is no chat histroy for the dropped call");
	            		 return false;
	        		}
	        		else if(id==1)
	        		{
	        			alert("Sorry There is no chat histroy for the Ignored call");
	        			 return false;
	        		}
	        		else{
	        		
	           	 var token=$(this).closest('tr').attr('id');
	           	var url= baseUrl+'teacher/tareports/chat.php?token='+token;
	           	 //window.open(url, '_blank');
	           		var token=$(this).closest('tr').attr('id');
           	viewchat(token);
	           	return false;
	        		}
	       	    }); 
	          
	    }
	});
});
$.ajax({
        url: baseUrl+"teacher/tareports/resportsUtil.php",
        data: {
            "id": 3,
            "agent":agent,
 	       "aid":0,
 	       "state":0
          
          
            
        },
        type: "GET",
        dataType: "html",
        success: function (data) {
            var result = $('#res').html(data);
       


        },
        error: function (xhr, status) {
            //alert("Sorry, there was a problem!");
        },
        complete: function (xhr, status) {
        	$('#rowclick tbody tr').click( function() {
        		 var id = $(this).attr('class');
        		  $('#rowclick tbody tr').removeClass('highlighted');
          	    $(this).addClass('highlighted');
        		if(id==0)
        		{
            		alert("Sorry There is no chat histroy for the dropped call");
            		 return false;
        		}
        		else if(id==1)
        		{
        			alert("Sorry There is no chat histroy for the Ignored call");
        			 return false;
        		}
        		else{
        		
           	 var token=$(this).closest('tr').attr('id');
           	viewchat(token);
          	var url= baseUrl+'teacher/tareports/chat.php?token='+token;
        // window.open(url, '_blank');
           	
           	 return false;
       	   
        		}
        	 	 return false;
       	    });   

              
        }
    });
    function viewchat(token)
    {
    	var selected = $(this).hasClass("highlight");
    	
    	
    	 
       
    	//alert(selected);
    if(!selected)
    	{
    	$(this).addClass("highlight");
        //alert(token);
    	$.ajax({
            url: baseUrl+"teacher/tareports/chat.php",
            data: {
                "token": token        
            },
            type: "GET",
            dataType: "html",
            success: function (data) {
                //alert(data);
            $('#chattrs').html(data);
                


            },
            error: function (xhr, status) {
                //alert("Sorry, there was a problem!");
            },
            complete: function (xhr, status) {
            },
    	 });
    	$('#chatview').css("display","block");
    	
    	
    	
    	}
    	
    	
    }
$("#rowclick").tablesorter(  );
$('#showno').change(function(){
	var x=$('#showno').val();

	if(x=='all')
	{

	$("#rowclick tr:gt(0)").show();

	}
	else
	{

	$("#rowclick tr:gt("+x+")").hide();
	$("#rowclick tr:lt("+x+")").show();
	}
	 });

	
}); 

document.getElementById('close').addEventListener('click', function(e) {
    e.preventDefault();
    this.parentNode.style.display = 'none';
    $('#res').css("width","100%");
    $('#rowclick tbody tr').removeClass('highlighted');
}, false)
</script>
