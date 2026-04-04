
<style>
    .page-context-header.container{
    display:none;
}
    .progress-bar{
    background-image:none !important;
    }
    table{
    margin-top:20px !important;
    }
    #cbody tr[visible='false'],
    .no-result{
    display:none;
}

    #cbody tr[visible='true']{
        display:table-row;
    }

    .counter{
    padding:8px;
        color:#ccc;
    }
    #selectlist2 {
        margin-left: 5%;
    }
    table.flexible, .generaltable {
    font-size: 14px;
    }
    .pagecover-onload{
    display:none;position: absolute; width: 95%; background-color: rgb(255, 255, 255); z-index: 300; opacity: 0.9; height: 90%; top: 60px;margin-left: -10px;
    }

</style>
<?php

require_once(dirname(__FILE__) . '/../../config.php');

// $PAGE->set_url('/local/teacher/dashboard.php');

$PAGE->requires->css('/supervisor/css/supervisor.css', true);
$PAGE->requires->js('/theme/universo/javascript/jquery-2.1.0.min.js',true);

$PAGE->requires->js('/theme/universo/javascript/icheck.min.js',true);
$PAGE->requires->js('/theme/universo/javascript/selectize.min.js',true);

$PAGE->requires->js('/theme/universo/javascript/jquery.tablesorter.min.js',true);
//        //$PAGE->requires->js('/teacher/custom.js');
$PAGE->requires->js('/local/teacher/html-table-search.js',true);
// $PAGE->requires->js('/local/teacher/dashboard.js',true);
$PAGE->requires->js('/supervisor/js/moment.js',true);
$PAGE->requires->js('/supervisor/js/pikaday.js',true);
//added by anusha for class postpone popup

//$PAGE->requires->css('/portal/js/jquery.datetimepicker.css',true);
//$PAGE->requires->js('/portal/js/jquery.js',true);
//$PAGE->requires->js('/portal/js/jquery.datetimepicker.full.js');
$PAGE->set_title('Tessellator 5.0- Examinor Dashboard');
$SESSION->theme = "default";
$THEME->parents_exclude_javascripts=array();
$THEME->parents_exclude_javascripts[]='jquery-1.11.3';
$curdate=date('d-m-y');
//var_dump($date);
require_login();
if (!user_has_role_assignment($USER->id, 3)){
    redirect($CFG->wwwroot);
   
}
echo $OUTPUT->header();
//non-editing teacher block, examinor
if (!user_has_role_assignment($USER->id, 3)){
    redirect($CFG->wwwroot);
   
}

echo '<input id="baseurl" type="hidden" value="'.$CFG->wwwroot .'"/>';

echo '<div  class="pagecover-onload">
		    <div style="width: 600px; height: 45px; text-align: center; margin: 180px auto 0px;">
			<div>PLEASE WAIT </div><div><img src="'.$CFG->wwwroot.'/local/teacher/testcenter/images/loading.gif"></div>
		   </div>
		    <div style="width: 600px; margin: 10px auto; text-align: center; color: rgb(100, 100, 100);">
		    <div class="loading-msg"></div>
		    </div>
		</div>';

?>

<style>
    .pika-single {
        z-index: 9999;
        display: block;
        position: relative;
        color: #333;
        background: #fff;
        border: 1px solid #ccc;
        border-bottom-color: #bbb;
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
    }
    #datepicker {
        padding: 0px 0px 0px 20px;
        font-size: 15px;
        background: rgb(255, 255, 255) url(<?php  echo $CFG->wwwroot.'/pix/i/calendar.png';?>) no-repeat scroll left center;
    }
    .switch-button,.search-button{
        -moz-border-radius: 0px;
        -webkit-border-radius: 0px;
        border-radius: 0px;
        -moz-transition: 0.3s;
        -o-transition: 0.3s;
        -webkit-transition: 0.3s;
        transition: 0.3s;
        background-color: #ea6645;
        border: 1px solid #ddd;
        color: #fff;
        font-weight: bold;
        min-height: 30px;
        outline: none !important;
        padding: 6px 65px;
        cursor: pointer;
        float: left;
    }
    .switch-button:hover,.search-button:hover{
        -moz-border-radius: 0px;
        -webkit-border-radius: 0px;
        border-radius: 0px;
        -moz-transition: 0.3s;
        -o-transition: 0.3s;
        -webkit-transition: 0.3s;
        transition: 0.3s;
        background-color: #ea6645;
        border: 1px solid transparent;
        color: #fff;
        font-weight: bold;
        min-height: 30px;
        outline: none !important;
        padding: 6px 65px;
        cursor: pointer;
    }
    .download,.download2{
        cursor:pointer;
        background-color: #574743;
        float: right;
        padding: 8px 6px;
        color: white;
    }

    .col-md-12.status-msg {
        color: #fff;
        font-weight: bold;
        margin: 0 2%;
        padding: 0;
        width: 93%;
    }

    .selection,.selection1{
        background-color: #a1a1a1;
        font-weight: bold;
        padding-bottom: 4px;
        padding-top: 4px;
        color:#fff;
    }
    .col-md-12.status-lable {
        padding-left: 0;
        padding-right: 0;
        width: 100%;
    }
    table {
        margin-top: 0 !important;
    }
    .col-md-6.action-box {
        border: 2px solid #ddd;
        padding: 0 0 5px;
    }
    .col-md-6.action-button {
        margin-bottom: 8px;
    }
    .stdname{
        text-align: left !important;
    }
    .selection {
        border: 2px solid #ddd;
    }
    .daily-wise-hits {
        margin-top: 15px;
    }

    .col-md-2{
      margin-right:580px;
      float:right;
      margin-top:-40px
    }
    .col-md-4{
      margin-right:40px;
      float:right;
      margin-top:-38px
    }
    .moremenu {
  opacity: 1;
  }
</style>
<div id="demo" class="container">
    <div id="courss" class="wrapper">
        <!-- <ul class="breadcrumb" style="margin-top: 0px;
margin-left: 5px;
margin-bottom: 16px;">
            <li><a href="<?php echo $CFG->wwwroot?>/teacher/dashboard.php">Dashboard</a> </li>
            <li><span tabindex="0">Exam student list</span></li></ul> -->


        <div class="tab-content course-tab-content" style="padding:20px !important;border: 2px solid #e2e2e2;position:relative;top:-2px;margin-bottom: 20px;">


            <div id="container">

                <div class="abs-summary row" style="margin-bottom: 5px;">
                    <div class="col-md-12">
                        <div id="datediv" class="col-md-3" >
                            <select id="subjects-dropdown">
                                <option value="0">Select</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select id="sections-dropdown">
                                <option value="0">Select</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <!--<center><h3 style="margin-top: 8px !important;">  Daily Performance Report</h3></center>-->
						</div>
                        <div class="col-md-4">
                            <a id="getresults"  class="switch-button">Get Students</a>
                        </div>
                    </div>
                </div>

            </div>

                <div class="daily-wise-hits" >

                    <div class="col-md-12"><!--<span class="download2" id="sxls">XLS</span>-->
                        <table class="CSSTableGenerator table table-hover course-list-table tablesorter" id="myTable">
                            <thead>
                            <tr>
                                <!-- <th class="header" style="text-align:center">Course</th>-->
                                <th class="header">RollNo</th>
                                <th title="total labs" class="header">Name</th>
                                <th title="total quizs"class="header">Section</th>
                                <th class="header">Reason</th>
                                <th class="header">Credentials</th>
                            </tr>
                            </thead>
                            <tbody class="grade-info">
                            <tr>
                                <td class="header">--</td>
                                <td class="header">--</td>
                                <td class="header">--</td>
                                <td class="header">--</td>
                                <td class="header">--</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php
echo "<div id='openModal' class='modalDialog'>
<style>


.pschool td {
    padding: 4px;color: #FFF;
  }
  .pschool {
    margin-top: 1px !important;
  }
  .ps-div{
    width: 36% !important;background:#012951 !important;
    padding:25px 40px 40px 40px !important; 
  }
  .ps-div a {
    color: #FFF;text-decoration: underline;
  
  }
  .modalDialog{
    background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
   }
</style>
<div class='ps-div'>
<a href='#close' title='Close' class='close' style='font-size: 14px;width: 20px;height: 24px;opacity: 0.9;text-decoration: none;'>X</a>
	<div class='studentDiv' id='detailsDiv'>
    </div>
	<div class='studentDiv' id='reasonDiv'>
	<span id='reasonResult'></span>
	<table class='pschool'>
		<tbody>
   			<tr><td colspan='2' style='text-align: center;'><h3>Change Student Details</h3></td></tr>
			<tr><td colspan='2' style='text-align: center;'>------------------------------</td></tr>
   			<tr><td>HTNO</td><td> <span id='htno'></span></td></tr>
			<tr><td>Reason</td><td style=''>

   				<select style='color:black' id='reasonmsg'>
   					<option value='machine stopped working'>Machine stopped working</option>
					<option value='browser closed'>Browser closed / Machine restarted</option>
					
				</select>
			</td></tr>
			<tr id='machineip'><td>New Exam PC (if machine changed)</td><td style=''>
   					<input type='text' value='' id='newip' placeholder='Enter Exam PC Number'/>
			</td></tr>
			<tr><td></td><td style=''>
			<input value='save' type='button' class='btn btn-primary' id='savereason'></input>
			</td></tr>
		</tbody>
	</table>
  </div>
</div>
</div>";


            //echo $OUTPUT->footer();

            echo "</div>";

            ?>
            <script src="<?php echo $CFG->wwwroot; ?>/local/teacher/jquery.table2excel.js"></script>


            <script>

                // Render Sections Based On Course Selection
                var $ = jQuery.noConflict();
                var baseUrl=$('#baseurl').val();
                var curdate='<?php echo $curdate ?>';

                $("#user-menu-toggle").on("click",function(){
                    $("#user-action-menu").toggle();
                })
                getCourses(1);
                function getCourses(catid){
                    //alert(baseUrl);
                    $(".pagecover-onload").show();
                    $.ajax({
                        type: "GET",
                        dataType: 'html',
                        data: {
                            "trmid": 2,
                            "trcatid": catid
                        },
                        url: baseUrl + "/local/teacher/myreports_ajax.php",
                        success: function (data) {
                            $("#subjects-dropdown").html(data);
                            var subject=($('#subjects-dropdown option[value="'+$("#subjects-dropdown").val()+'"]').text());
                            $(".pagecover-onload").hide();

                        }
                    });
                }//end of getResults

                $("#subjects-dropdown").on("change", function () {
                    //$("#loading").show();
					if(parseInt($("#subjects-dropdown").val()))
                    getSections($("#subjects-dropdown").val());

                });

                $("#reasonmsg").on("change", function () {
                   var reason=($('#reasonmsg option[value="'+$("#reasonmsg").val()+'"]').text());
                  if(reason=="Machine stopped working"){
                            $("#machineip").show();
			}
		  else{
                            $("#machineip").hide();
			}
                });
                function getSections(cid){
                    $(".pagecover-onload").show();
                    $.ajax({
                        type: "GET",
                        dataType: 'html',
                        data: {
                            "trmid": 22,
                            "trcid": cid,
                        },
                        url: baseUrl + "/local/teacher/myreports_ajax.php",
                        success: function (data) {
                            $("#sections-dropdown").html(data);
                            var section=($('#sections-dropdown option[value="'+$("#sections-dropdown").val()+'"]').text());
                            $(".pagecover-onload").hide();
                        }
                    });
                }//end of getSections

                function getStudentDetails(username){
					var cid = $("#subjects-dropdown").val();
					$("#studentDiv").html('');
                    $(".pagecover-onload").show();
                    $.ajax({
                        type: "GET",
                        dataType: 'html',
                        data: {
                            "trmid": 2,
                            "username": username,
							"trcid":cid
                        },
                        url: baseUrl + "/local/teacher/examinorlib.php",
                        success: function (data) {
                            $("#detailsDiv").html(data);
                            $(".pagecover-onload").hide();
							window.location.hash = 'openModal';
                        }
                    });
                }//end of getStudentDetails

                function storeReason(username,cid,reason,newip){
					$("#studentDiv").html('');
                    $(".pagecover-onload").show();
                    $.ajax({
                        type: "GET",
                        dataType: 'html',
                        data: {
                            "trmid": 3,
                            "username": username,
				"trcid": cid,
				"reason": reason,
				"newip": newip,
                        },
                        url: baseUrl + "/local/teacher/examinorlib.php",
                        success: function (data) {
                            $("#reasonResult").html(data);
                            $(".pagecover-onload").hide();
                        }
                    });
                }//end of storeReason


                $("#getresults").on("click",function(event){

                    if(parseInt($("#subjects-dropdown").val())){
                        var section = $("#sections-dropdown").val();

                        $(".pagecover-onload").css("display", "block");
                        $.ajax({
                            type: "GET",
                            dataType: 'html',
                            data: {
                                "trmid": 1,
                                "trcid": $("#subjects-dropdown").val(),
                                "tsecid": section,
                            },
                            url: baseUrl + "/local/teacher/examinorlib.php",
                            success: function (data) {
                                $(".grade-info").html(data);

                            },
                            complete: function (xhr, status) {
                                $(".pagecover-onload").hide();
                                $("#myTable").trigger("update");
                                // set sorting column and direction, this will sort on the first and third column
                                $("#myTable").trigger([]);
                                $("#myTable").tablesorter({});

                            }
                        });
                    }else{
                        alert("please select course");
                    }
                });//end of getresults

				$(document).delegate(".reason","click",function(){
					$(".studentDiv").hide();
					$("#reasonResult").html("");
					$("#reasonDiv").show();
					$("#htno").html($(this).attr('id'));
					window.location.hash = 'openModal';
					//alert($(this).attr('id'));
				});

				$(document).delegate(".showdetails","click",function(){
					$(".studentDiv").hide();
					$("#reasonResult").html("");
					$("#detailsDiv").show();
					getStudentDetails($(this).attr('id'));
				});

				$(document).delegate("#savereason","click",function(){
					var username = $("#htno").html();
					var cid = parseInt($("#subjects-dropdown").val());
					var reason = $("#reasonmsg").val();
					var newip = $("#newip").val();

					storeReason(username,cid,reason,newip);
				});



            </script>

<style>
	/* Model Css  Start*/

.modalDialog {
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: 99999;
  opacity: 0;
  -webkit-transition: opacity 400ms ease-in;
  -moz-transition: opacity 400ms ease-in;
  transition: opacity 400ms ease-in;
  pointer-events: none;
}
.modalDialog:target {
  opacity: 1;
  pointer-events: auto;
}
.modalDialog > div {
    width: 600px;
    position: relative;
    margin: 12% auto;
    padding: 5px 20px 13px 20px;
    border-radius: 10px;
    background: #fff;
    background: #ccc;
    color: #000;
}
.close {
  background: #606061;
  color: #FFFFFF;
  line-height: 25px;
  position: absolute;
  right: -12px;
  text-align: center;
  top: -10px;
  width: 24px;
  text-decoration: none;
  font-weight: bold;
  -webkit-border-radius: 12px;
  -moz-border-radius: 12px;
  border-radius: 12px;
  -moz-box-shadow: 1px 1px 3px #000;
  -webkit-box-shadow: 1px 1px 3px #000;
  box-shadow: 1px 1px 3px #000;
}
.close:hover {
  background: #00d9ff;
}
	</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
    // Find the "Home" node
    var homeNode = document.querySelector('.nav-item[data-key="home"]');

    // Remove the "Home" node if found
    if (homeNode) {
        homeNode.parentNode.removeChild(homeNode);
    }
   
   
});
</script>