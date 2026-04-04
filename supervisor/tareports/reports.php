<style>


    #page-teacher-reports  .filters #wtdiv{
        text-transform:  capitalize !important;
    }
    #tableData .header{
background-image:url(<?php require_once(dirname(__FILE__) . '/../../config.php'); global $CFG ; echo $CFG->wwwroot.'/teacher/reports/pix/both.gif';?>);
background-repeat: no-repeat;
        background-position: right center;
        padding: 4px 18px 4px 4px  !important;
        white-space: normal;
        cursor: pointer;
    }
    #tableData .headerSortUp{

        background-image:url(<?php require_once(dirname(__FILE__) . '/../../config.php'); global $CFG ; echo $CFG->wwwroot.'/teacher/reports/pix/up.gif';?>);
    }
    #tableData .headerSortDown{

        background-image:url(<?php require_once(dirname(__FILE__) . '/../../config.php'); global $CFG ; echo $CFG->wwwroot.'/teacher/reports/pix/down.gif';?>);
    }

#reports-tab .link {
			        color: #E5B467 !important;
			        font-weight: bold !important;
	    }		    
#navbar{
		margin-top:2%;
		margin-left:6%;
		margin-bottom:0%;
	}
.course-list-table thead tr th {
    background-color: #ea6645;
    color: #FFF !important;
    font-size: 14px;
    font-weight: normal;
}
.box{
min-height:540px;
border: 1px solid rgb(204, 204, 204) !important;
border-radius: 5px;
margin-bottom:1%;
}
    #region-main .container{
    margin-top:4%;
}
.container:before,.container:before{
content:'' !important;
}
.labe{
font-size: 16px;
padding: 5px 5px 0px 5px;
}
.course-list-table thead tr th {
    background-color: #ea6645;
    color: #FFF !important;
text-align:center !important;
}
.course-list-table tbody tr td {
  
text-align:center !important;
}
.cell{
border-right:1px solid #ccc;
}
 .container::after,.container::before{
        display: none !important;
    }
</style>
<?php
require_once(dirname(__FILE__) . '/../../config.php');
$PAGE->requires->js('/student/jquery-latest.min.js',true);

$PAGE->requires->css('/teacher/styles.css',true);
$PAGE->requires->css('/teacher/reports/reports.css');
$PAGE->requires->css('/teacher/tareports/tastyles.css');
echo "<input id='baseurl' type='hidden' value=".$CFG->wwwroot ."/>";
require_login();
if (!(user_has_role_assignment($USER->id,2) ) ) {

            redirect($CFG->baseUrl);
}

echo $OUTPUT->header();
$PAGE->set_url('/teacher/tareports/reports.php');

function getCategories(){
    global $USER,$DB;

    $getCategoriessql="SELECT `id` , `name` FROM `mdl_course_categories` ";

    $resultset=$DB->get_records_sql($getCategoriessql, null);
    foreach ($resultset as $res) {
        $category_typeids[]=array("catid"=>$res->id,"catname"=>$res->name);
    }
//var_dump($category_typeids[0]["catid"]);
    return $category_typeids;
}

?>

<div id="navbar" class="col-md-12">
	<a id='dlink' href='<?php echo $CFG->portal.'registration/login.php'?>'>Dashboard</a> <span>/</span>
<a id='dlink' href='<?php echo $CFG->wwwroot?>'>Supervisor Dashboard</a> <span>/</span> 
<b>TA Reports</b>
</div>
<?php
echo  '<div class="container">';
$cours = enrol_get_users_courses($USER->id);
//var_dump($cours);
echo   "<div class='box'><div class='span3' style='margin-top: 10px;'>";
echo "<div class='filters'>";
echo '<p class="labe" id="">Course</p>';


echo '<select id="cateogry-dropdown">
    <option value="0">Select Course</option>';
    $categories=getCategories();
    for($i=0;$i<count($categories);$i++){
        echo '<option value="'.$categories[$i]["catid"].'">'.$categories[$i]["catname"].'</option>';
    }
    echo '</select>';


echo '<p class="labe" id="">Subject</p>';

$html.=html_writer::start_tag("select",array('class'=>'course','id'=>'courses',
    'data-url'=> $CFG->wwwroot . '/teacher/reports/showfilters.php' ));
//ADIING SELECT ONE OPTION
$html.=html_writer::start_tag("option",array('value'=>""));
 $html.="Select Subject";
$html.=html_writer::end_tag("option");
/*
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
*/
$html.=html_writer::end_tag("select");
echo $html;
echo '<p class="labe" id="">Activity</p>';
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
echo "<div><button value='Search' name='search'  class='btn' id='search' style='text-align: right !important;
margin-left: 6% !important;
background-color: rgb(234, 102, 69) !important;
background-image: none !important;'>Search</button>
		<button value='Clear' name='clear' class='btn'  id='clear' style='text-align: right !important;
margin-left: 6% !important;
background-color: rgb(234, 102, 69) !important;
background-image: none !important;'>Clear</button></div>";
echo "</div>";//end of filters
echo   "<div class='span9' style='margin-top: -1px;
border: 1px solid rgb(204, 204, 204);
min-height: 539px;
margin-right: 0px;'>";
echo '<table id="filt" class="table table-hover course-list-table ">
<tbody><tr>
<th scope="row">Course : </th><th id="scour" class="cell">--</th>
<th  scope="row">Activity : </th><th id="actvityname" class="cell">--</th>
</tr>

</tbody></table>';
echo "<div  id='res'>  <table  id='rowclick' class='table table-hover course-list-table '>
		<thead><tr><th>Assistant</th><th>Calls Answered</th><th>Calls Ignored</th><th>Rating</th></tr></thead></tr>     </div>";
echo "</thead><tbody > </tbody></table></div></div></div>";

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
	$('#res').html("<table  id='rowclick' class='table table-hover course-list-table '><thead><tr><th>Assistant</th><th>Calls Answered</th><th>Calls Ignored</th><th>Rating</th></tr></thead></tr> </thead><tbody > </tbody></table>");
});
$('#search').click(function(){
	var baseUrl=$('#baseurl').val(); // get base url
    var cid=$( "#courses option:selected" ).val();
    var aid=$( "#activities option:selected" ).val();
 if(cid)
 {
	 document.getElementById('courses').style.borderColor = "";
    $.ajax({
        url: baseUrl+"teacher/tareports/resportsUtil.php",
        data: {
            "id": 2,
           "cid":cid,
           "aid":aid
          
            
        },
        type: "GET",
        dataType: "html",
        success: function (data) {
      	$('#res').html();
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
            	var url= baseUrl+'supervisor/tareports/agentview.php?agent='+agent+"&cid="+cid+"&aid="+aid;
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

    $("#cateogry-dropdown").on("change",function(){

        getCourses($("#cateogry-dropdown").val(),'');
    });

    function getCourses(catid,ccid){
        $.ajax({
            type: "GET",
            dataType: 'html',
            data: {
                "trmid": 3,
                "trcatid": catid
            },
            url: baseUrl + "supervisor/myreports_ajax.php",
            success: function (data) {
                $("#courses").html(data);

            },
            complete:function (data) {

            }
        });
    }//end of getCourses
//var table = document.getElementById('rowclick');
/******************************TABLE SORTING AND SEARCG ********************************
$("#rowclick").tablesorter(  );
$('#search1').keyup(function()
{
	searchTable($(this).val());
});
});
function searchTable(inputVal)
{
var table = $('rowclick');
table.find('tr').each(function(index, row)
{
	var allCells = $(row).find('td');
	if(allCells.length > 0)
	{
		var found = false;
		allCells.each(function(index, td)
		{
			var regExp = new RegExp(inputVal, 'i');
			if(regExp.test($(td).text()))
			{
				found = true;
				return false;
			}
		});
		if(found == true)$(row).show();else $(row).hide();
	}
});*/
//$("#rowclick").tablesorter(  );
}); 

</script>
