<?php
require_once(dirname(__FILE__) . '/../../config.php');
$PAGE->requires->js('/student/jquery-latest.min.js',true);
$PAGE->requires->css('/teacher/styles.css',true);
$PAGE->requires->css('/teacher/reports/reports.css');
$PAGE->requires->css('/teacher/tareports/tastyles.css');
echo "<input id='baseurl' type='hidden' value=".$CFG->wwwroot ."/>";
echo $OUTPUT->header();
/*******************************TABS *******************************/
echo '<div id="demo">


<div id="reports-tab">
<a href="'. $CFG->wwwroot.'/teacher/dashboard.php" class=" link">Courses</a>
<a href="'. $CFG->wwwroot.'/teacher/reports.php" class="  link">Reports</a>
<a href="'. $CFG->wwwroot.'/teacher/watchlist.php" class="link" >Watchlist</a>
<a href="'. $CFG->wwwroot.'/teacher/tareports/reports.php" class="current link">Assistants</a>
</div>
</div>';
echo  '<div class="yui3-skin-sam reports-page">';
$cours = enrol_get_users_courses($USER->id);
//var_dump($cours);
echo   "<div class='span3' style='margin-top: 10px;'>";
echo "<div><button value='Search' name='search'  class='btn' id='search'>Search</button>
		<button value='Clear' name='clear' class='btn'  id='clear'>Clear</button></div><div class='filters'>";
echo '<p class="" id="">Course</p>';
$html.=html_writer::start_tag("select",array('class'=>'course','id'=>'courses',
    'data-url'=> $CFG->wwwroot . '/teacher/reports/showfilters.php' ));
//ADIING SELECT ONE OPTION
$html.=html_writer::start_tag("option",array('value'=>""));
 $html.="Select Course";
$html.=html_writer::end_tag("option");

foreach($cours as $courses=>$name){

    if(is_object($name)){
        foreach($name as $courz=>$value){
            if($courz=='id'){
                $courseid=$value;


            }
            if($courz=='fullname'){
                $html.=html_writer::start_tag("option",array('value'=>$courseid));

                $html.=$value;
                $html.=html_writer::end_tag("option");

            }

        }
    }
}

$html.=html_writer::end_tag("select");
echo $html;
echo '<p class="" id="">Activity</p>';
echo "<div id='act' >";

$html=html_writer::start_tag("select",array('class'=>'course','id'=>'courses',
		'data-url'=> $CFG->wwwroot . '/teacher/reports/showfilters.php'));
$html.=html_writer::start_tag("option",array('value'=>""));
$html.="Select Activity";
$html.=html_writer::end_tag("option");
$html.=html_writer::end_tag("select");
echo $html;
		
	echo	"</div>";
echo "</div>";
echo "</div>";//end of filters
echo   "<div class='span9' style='margin-top: 10px;'>";
echo '<table id="filt" class="generaltable generalbox quizreviewsummary">
<tbody><tr>
<th class="cell" scope="row">Course</th><td id="scour" class="cell">--</td>
<th class="cell" scope="row">Activity</th><td id="actvityname" class="cell">--</td>
</tr>

</tbody></table>';
echo "<div  >  <table  id='rowclick' class='generaltable'>
		<thead><tr><th>Help Taken</th><th>Satisfied</th></tr></thead></tr>     </div>";
echo "</thead><tbody id='res'></tbody></table></div></div>";

$sendmail=html_writer::tag("button","SendMail",array('class'=>'sendmail btn','id'=>'sendmail','value'=>'click me'));



//echo $sendmail;
echo $OUTPUT->footer();
?>
<script>
$(document).ready( function() {
	var baseUrl=$('#baseurl').val();
    var url=baseUrl+'teacher/dashboard.php';
    $('#page-navbar').append("<div style='padding:6px;'> <a id='dlink'>Dashboard</a> <span>/</span> <b> Assistants</b></div>");
    $('#dlink').attr("href",url);
$('#courses').change(function(){
	if($("#courses option:selected").val())
	{
	$("#scour").text($("#courses option:selected").text());
	 document.getElementById('courses').style.borderColor = "";
	}
	else
		$("#scour").text("--");
	
	var baseUrl=$('#baseurl').val(); // get base url
    var cid=$( "#courses option:selected" ).val();
    $.ajax({
        url: baseUrl+"/teacher/tareports/resportsUtil.php",
        data: {
            "id": 1,
           "cid":cid
            
        },
        type: "GET",
        dataType: "html",
        success: function (data) {
            var result = $('#act').html(data);
           

        },
        error: function (xhr, status) {
            //alert("Sorry, there was a problem!");
        },
        complete: function (xhr, status) {
        	$('#activities').change(function(){
        		if($("#activities option:selected").val()=="0")
        			$("#actvityname").text("--");
        			
        			else
        				$("#actvityname").text($("#activities option:selected").text());
        		
        	 });
        }
    });
});
$('#clear').click(function(){
	
	$("#activities").val("0");
	$("#courses").val("");
	$("#actvityname").text("--");
	$("#scour").text("--");
	$('#res').html("");
});
$('#search').click(function(){
	var baseUrl=$('#baseurl').val(); // get base url
    var cid=$( "#courses option:selected" ).val();
    var aid=$( "#activities option:selected" ).val();
 if(cid)
 {
	 document.getElementById('courses').style.borderColor = "";
    $.ajax({
        url: baseUrl+"/webrtc/studentreviewUtil.php",
        data: {
            "id": 2,
           
           "aid":aid
          
            
        },
        type: "GET",
        dataType: "html",
        success: function (data) {
        	$('#res').html("");
       $('#res').html(data);
           

        },
        error: function (xhr, status) {
            //alert("Sorry, there was a problem!");
        },
        complete: function (xhr, status) {
        	 $('#rowclick tbody tr').click( function() {
            	 var agent=$(this).closest('tr').attr('id');
            	 var tclass=$(this).closest('tr').attr('class');
            	 if(tclass==0)
            	 {
                	 alert("There is no calls for this agent ");
                	 return false;
            	 }
            	 else{
            	 var cid=$( "#courses option:selected" ).val();
            	 var aid=$( "#activities option:selected" ).val();
            	var url= baseUrl+'teacher/tareports/agentview.php?agent='+agent+"&cid="+cid+"&aid="+aid;
            	 window.open(url, '_blank');
            	 return false;
            	 }
        	     // alert(token);
        	    }); 
     
          
        	
        }
    });
 }
 else{
	  document.getElementById('courses').style.borderColor = "red";
	 alert("Please select course");
 }
});


}); 

</script>
