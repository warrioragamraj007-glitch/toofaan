
<?php
require_once(dirname(__FILE__) . '/../config.php');
$PAGE->set_url('/supervisor/dashboard.php');
require_login();
require_once("myreports_ajax.php");
$PAGE->requires->css('/supervisor/css/supervisor.css', true);
$PAGE->requires->js('/student/jquery-latest.min.js', true);
$PAGE->requires->js('/supervisor/js/supervisor.js', true);
$PAGE->requires->js('/teachingagent/moment.js',true);
$PAGE->requires->js('/teachingagent/pikaday.js',true);
require_once($CFG->dirroot . '/my/lib.php');
$context = context_user::instance($USER->id);
$PAGE->set_context($context);
require_once($CFG->dirroot . '/course/lib.php');
$PAGE->set_title('Tessellator 4.0 - Supervisor Dashboard');
echo $OUTPUT->header();
$date=date('Y-m-d');
?>
<style>
	h2.supervisor-dashboard{
		padding-top: 10px;
	}
	.action-list,.action-content{
		border: 1px solid #ddd;
		min-height: 400px;
		margin-bottom: 10px;
		padding: 0;
	}
	.tabs-left > .nav-tabs{
		border: none; padding: 5px;margin-right: 5px;
		width:100%;
	}
	.tab-content {
		border-left: 1px solid #ddd;
	}
	.tabs-left > .nav-tabs a{
		border-color: #c3c3c3 !important;
		border-style: solid !important;
		border-width: 1px !important;
		background-color: #FFF;

	}
	.tabs-left > .nav-tabs > li > a {
		border-radius: 0;
		color: #01366a !important;
		padding-left: 30px;
	}


	.tabs-left > .nav-tabs .active > a, .tabs-left > .nav-tabs .active > a:hover, .tabs-left > .nav-tabs .active > a:focus {

		border-left: 4px solid rgb(234, 102, 69) !important;
		color: #ea6645 !important;

	}
	.tabs-left > .nav-tabs > li > a:hover{
		background-color: #FFF !important;
		color:#ea6645 !important;
	}
	.tabs-left > .nav-tabs > li > a > i,.tabs-left > .nav-tabs > li > span > i{
		padding-left: 5px;
	}
	.nav.nav-tabs.tabs-left li.active {
		border-left: 4px solid rgb(234, 102, 69) !important;
	}
	.nav.nav-tabs.tabs-left li.active a {
		color: #ea6645 !important;
	}
	.tab-content {
		padding-top: 10px;
	}
	.filtersdiv{
		height:auto;
	}
	.cstudent{
		cursor: pointer;
	}
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

		background: rgb(255, 255, 255) url(<?php require_once(dirname(__FILE__) . '/../config.php'); global $CFG ; echo $CFG->wwwroot.'/pix/i/calendar.png';?>) no-repeat scroll left center;
	}
	.tab-content {
		padding-top: 11px;
	}
	.action-list, .action-content {
		border: 1px solid #ddd;
		min-height: 550px;
	}
	.students {
		border: 0px solid #ccc;
	}
	#student-table td:nth-child(2),#student-table td:nth-child(3){
		text-align: left;
	}
	#student-table thead th:nth-child(2),#student-table thead th:nth-child(3) {
		text-align: left;
		padding-left:0px;
	}
	#student-table thead tr {
		font-size: 14px;
	}
	.students {
		margin-top: 0px;
		min-height: 399px;
	}
	.tabs-left > .nav-tabs {
		border: none;
		padding: 0px;
		margin-right: 5px;
		width: 100%;
	}
	.results {
		border: 1px solid #ccc;
		min-height: 350px;
		margin-bottom: 0px;
	}
.tabs-left{
	border-right: 0px;
}
	#sendmail{
		margin-top: 8% ;
	}
	.tab-content > .active {
		display: block;
		margin-top: -9px;
	}
	.nav-tabs > li {
		margin-bottom: -3px;
	}
	 #student-attandance-table thead tr th, #student-grade-table thead tr th {
	padding: 0px !important;
	}
	tbody {
		height: 200px;
		overflow: auto;
	}
	input[type="radio"], input[type="checkbox"] {
		margin: -4px 0 0;
		margin-top: 1px;
		line-height: normal;
	}
	.results {
		border: 0px solid #ccc;
	}
	#myTable thead tr th {
		padding: 5px !important;
	}
	.course-details{
		padding-right: 0px;
		padding-left: 0px;
	}
	.overlay1 {
		top: 10%;
	}
	.popup {
		top: 5%;
	}
</style>

<div class='container'>
<h2 class="supervisor-dashboard">Supervisor Dashboard</h2>
	<div class="main-content ">
		<div class="col-md-12">
				<div class="col-md-2 action-list tabbable tabs-left">
					<ul class="testcenter-tabs nav nav-tabs ">
						<li id="student-status" class="active"><a id="student-tab" href="#a" data-toggle="tab" >Students
								<i class="fa fa-angle-double-right" aria-hidden="true"></i>
							</a></li>
						<li id="courses-status"><a href="#b" data-toggle="tab">Courses
								<i class="fa fa-angle-double-right" aria-hidden="true"></i>
							</a>
						</li>
						<li id="agent-status" ><a href="#c" data-toggle="tab">Attendance
								<i class="fa fa-angle-double-right" aria-hidden="true"></i>
							</a></li>
						<li id="agent-status" ><a href="#d" data-toggle="tab">Performance
								<i class="fa fa-angle-double-right" aria-hidden="true"></i>
							</a></li>

					</ul>
					<div class="col-md-12">
						<div class="col-md-12" style="text-align:center">
							<input id="sendmail" type="button" name="search" class="btn" value="Send Mail">
						</div>
						<div class="col-md-12" style="text-align:center">
							<input id="sendsms" type="button" name="search" class="btn" value="Send SMS">
						</div>

					</div>
				</div><!-- end of main col-md-2 -->
				<div class="col-md-10 action-content">
					<div class="tab-content">
						<div class="tab-pane active" id="a">
							<div class="filtersdiv">
								<div class="current-selection">
									<span class="category"></span>
									<span class="showsearch" style="float:right"><i  class="fa fa-arrow-down" aria-hidden="true"></i></span>
								</div>
								<div class="search-content">
									<div class="col-md-12 search-box">
										<div class="student-filters col-md-12">


											<div class="col-md-3" style='margin-bottom:5px'>
												<div class="dropdown-lable">Course</div>
												<select id="cateogry-dropdown">
													<option value="0">Select Category</option>
													<?php
													$courses=getCategories();
													for($i=1;$i<count($courses);$i++):
														?>
														<option value="<?php echo $courses[$i]['catid'] ?>"><?php echo $courses[$i]['catname'] ?></option>
													<?php endfor; ?>
												</select>
											</div>


											<div class="col-md-3" style='margin-bottom:5px'>
												<div class="dropdown-lable">Subject</div>
												<select id="subjects-dropdown">
													<option value="0">Select Subject</option>
												</select>
											</div>

											<div class="col-md-3" style='margin-bottom:5px'>
												<div class="text-label">First Name</div>
												<input type="text" id="firstname" name="FirstName" placeholder="First Name"/>
											</div>

											<div class="col-md-3" style='margin-bottom:5px'>
												<div class="text-label">Last Name</div>
												<input type="text" id="lastname"  name="LastName" placeholder="Last Name"/>
											</div>

										</div><!-- course-results-header-top end -->

										<div class="col-md-12">


											<div class="col-md-3" style='margin-bottom:5px'>
												<div class="text-label">College Name</div>
												<input type="text" name="college" placeholder="College Name"/>
											</div>

											<div class="col-md-3" style='margin-bottom:5px'>
												<div class="text-label">Mobile No</div>
												<input type="text" length="10" name="MobileNo" placeholder="Mobile No"/>
											</div>

											<div class="col-md-3" style='margin-bottom:5px'>
												<div class="text-label">Email Id</div>
												<input type="email" id="email" name="Emailid" placeholder="Email Id"/>
											</div>
											<div class="col-md-3" style='margin-top:15px;text-align:center;'>
												<input id="search" type="button" name="search" class="btn" value="Search" style="padding: 5px 25%;"/>
											</div>


										</div><!-- col-md-12-->

									</div><!-- searchbox -->

								</div><!-- search content-->
							</div><!-- end of filters div -->


							<div class="students "  style="padding:0px;">
								<table style="width:100%;" id="student-table">
									<thead>
									<tr><th>SNO</th>

										<th>
											<span>Student</span>

										</th>
									<th>Email</th>
										<th><input type="checkbox" name="selectstudents" id="checkAll"></th>
									</tr>
									</thead>
									<tbody class="students-list " id="student-list">
									</tbody>
								</table>
							</div>


						</div>
						<div class="tab-pane " id="b">

							<div class="filtersdiv">
								<div class="search-content">
									<div class="col-md-12 search-box">
										<div class="student-filters col-md-12">


											<div class="col-md-3" style='margin-bottom:5px'>
												<div class="dropdown-lable">Course</div>
												<select id="course-cateogry-dropdown">
													<option value="0">Select Category</option>
													<?php
													$courses=getCategories();
													for($i=1;$i<count($courses);$i++):
														?>
														<option value="<?php echo $courses[$i]['catid'] ?>"><?php echo $courses[$i]['catname'] ?></option>
													<?php endfor; ?>
												</select>
											</div>
											<div class="col-md-3"></div>
											<div class="col-md-3" style='margin-top:15px'>
												<span id="loading">
													<i class="fa fa-refresh fa-spin fa-2x fa-fw" style=" color: #ea6645 "></i>
												</span>
											</div>
											<div class="col-md-3" style='margin-top:15px'>
												<span class="download" id="sxls">XLS</span>
											</div>
										</div><!-- end of student-filters -->
									</div><!-- end of search-box -->
								</div><!-- search-content -->

							</div><!-- end of tab b filter div -->

							<div class="col-md-12 course-details">
								<div class="table-responsive">
									<table class="table table-hover course-list-table tablesorter" id="myTable">
										<thead>
										<tr>
											<th style="">Subject Name</th>
											<th style="">Teacher</th>
											<th style="">Assistants</th>
											<th title="Average Class Mean Attandance" style="">AAT(%)</th>
											<th title="Average Class Mean Grade" style="">AMG(%)</th>
											<th style="">Students</th>
										</tr>
										</thead>

										<tbody class="courseinfo-table">

										</tbody>
									</table>
								</div>
							</div>


						</div><!-- end of tab b -->

						<div class="tab-pane " id="c">

							<div class="current-selection attendance-current-selection">
								<span class="category"></span>
								<span class="showsearch" style="float:right"><i  class="fa fa-arrow-down" aria-hidden="true"></i></span>
							</div>

							<div class="search-content">
								<div class="col-md-12 search-box">
									<div class="student-filters col-md-12">


										<div class="col-md-4" style='margin-bottom:5px'>
											<div class="dropdown-lable">Course</div>
											<select id="attandance-cateogry-dropdown">
												<option value="0">Select Category</option>
												<?php
												$courses=getCategories();
												for($i=1;$i<count($courses);$i++):
													?>
													<option value="<?php echo $courses[$i]['catid'] ?>"><?php echo $courses[$i]['catname'] ?></option>
												<?php endfor; ?>
											</select>
										</div>


										<div class="col-md-4" style='margin-bottom:5px'>
											<div class="dropdown-lable">Subject</div>
											<select id="attandance-subjects-dropdown">
												<option value="0">Select Subject</option>
											</select>
										</div>

										<div class="col-md-4" style='margin-bottom:5px'>
											<div class="text-label">Attandance</div>
											<select id="attandance">
												<option value="0">Absent</option>
												<option value="1">Present</option>
												<option value="2">Absent & Present</option>
												<option value="3">Average Attandance</option>
											</select>
										</div>


									</div><!-- course-results-header-top end -->


									<div class="col-md-12">
										<div id="datediv" class="col-md-4" style='margin-bottom:5px'>
											<div class="text-label">Date</div>
											<input type="text" id="datepicker" placeholder="Select Date" value="<?php echo $date;?>">
										</div>
										<div id="avg-attandance-div" class="col-md-4" style='display:none;margin-bottom:5px'>
											<div class="text-label">Average Attandance</div>
											<select id="avgattandance">
												<option value="0">All</option>
												<option value="1">100%</option>
												<option value="2">99%-80%</option>
												<option value="3">79%-60%</option>
												<option value="4">59%-40%</option>
												<option value="5">39%-1%</option>
												<option value="6">0%</option>
											</select>
										</div>
										<div class="col-md-4" style='margin-bottom:5px'>
											<div class="text-label">Name</div>
											<input type="text" name="studentname" id="astudentname" placeholder="Student Name"/>
										</div>
										<div class="col-md-1" style='margin-top:15px;text-align:center;'></div>

										<div class="col-md-3" style='margin-top:15px;text-align:center;float:right'>
											<input id="attandancesearch" type="button" name="search" class="btn" value="Search" style="padding: 5px 25%;"/>
										</div>
									</div>



								</div><!-- searchbox -->

							</div><!-- search content-->



							<div class="results col-md-12" style="padding:0px;">
								<div class="students col-md-12"  style="padding:0px;">
									<table style="width:100%;" id="student-attandance-table">
										<thead>
										<tr>
											<th>SNO</th>
											<th>
												<span>Students</span>

											</th>
											<th>Attandance</th>
											<th><input type="checkbox" name="attandancestudents" id="attandancecheckAll"></th>
										</tr>
										</thead>
										<tbody class="students-list" id="attandance-list">
										</tbody>
									</table>
								</div><!-- students -->
								</div><!-- results -->


						</div><!-- end of tab c -->
						<div class="tab-pane" id="d">

							<div class="current-selection attendance-current-selection">
								<span class="category"></span>
								<span class="showsearch" style="float:right"><i  class="fa fa-arrow-down" aria-hidden="true"></i></span>
							</div>

							<div class="search-content">
								<div class="col-md-12 search-box">
									<div class="student-filters col-md-12">


										<div class="col-md-4" style='margin-bottom:5px'>
											<div class="dropdown-lable">Course</div>
											<select id="grade-cateogry-dropdown">
												<option value="0">Select Category</option>
												<?php
												$courses=getCategories();
												for($i=1;$i<count($courses);$i++):
													?>
													<option value="<?php echo $courses[$i]['catid'] ?>"><?php echo $courses[$i]['catname'] ?></option>
												<?php endfor; ?>
											</select>
										</div>


										<div class="col-md-4" style='margin-bottom:5px'>
											<div class="dropdown-lable">Subject</div>
											<select id="grade-subjects-dropdown">
												<option value="0">Select Subject</option>
											</select>
										</div>

										<div class="col-md-4" style='margin-bottom:5px'>
											<div class="text-label">Grade</div>
											<select id="grade">
												<option value="0">All</option>
												<option value="1">100%</option>
												<option value="2">99%-80%</option>
												<option value="3">79%-60%</option>
												<option value="4">59%-40%</option>
												<option value="5">39%-1%</option>
												<option value="6">0%</option>
											</select>
										</div>


									</div><!-- course-results-header-top end -->


									<div class="col-md-12">

										<div class="col-md-4" style='margin-bottom:5px'>
											<div class="text-label">Name</div>
											<input type="text" name="studentname" id="pstudentname" placeholder="Student Name"/>
										</div>
										<div class="col-md-5" style='margin-top:15px;text-align:center;'></div>

										<div class="col-md-3" style='margin-top:15px;text-align:center;float:right'>
											<input id="performancesearch" type="button" name="search" class="btn" value="Search" style="padding: 5px 25%;"/>
										</div>
									</div>


								</div><!-- searchbox -->

							</div><!-- search content-->



							<div class="results col-md-12" style="padding:0px;">
								<div class="students col-md-12"  style="padding:0px;">
									<table style="width:100%;" id="student-grade-table">
										<thead>
										<tr>
											<th>SNO</th>
											<th>
												<span>Students</span>

											</th>
											<th>Grade</th>
											<th><input type="checkbox" name="gradestudents" id="gradecheckAll"></th>
										</tr>
										</thead>
										<tbody class="students-list" id="grade-list">
										</tbody>
									</table>
								</div><!-- students -->


							</div><!-- results -->

						</div>
					</div><!-- end of all tabs -->
				</div><!-- end of main col-md-10-->
		</div><!-- end of main col-md-12 -->

	</div><!-- end of main content -->
</div><!-- end of container -->

<!-- mail and sms communication -->
<div id="popup1" class="overlay">
	<div class="popup">
		<a class="close">×</a>
		<div class="content" style="width: 85%;margin: auto;max-height: 500px; padding: 1%; font-size: 14px;">
			<center>
                 <span id="email-loading">
                    <i class="fa fa-refresh fa-spin fa-3x fa-fw" style=" color: #ea6645 "></i>
                 </span>
			</center>
			<div id="notification"></div>
			<form role="form" method="POST" class="clearfix" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="createuser" name="createuser">
				To  <input type="text" name="mailids" id="mailids"/>
				Subject <input type="text" id="subject" name="subject" /><br/>

				Message
				<div style="width: 100%;"><textarea style="width: 100%; height: 150px;" id="area1" name="area1" cols="92" rows="12" ></textarea></div>
				<br/>
				<button type="button"  class="btn pull-right" id="sendemail">Send Mail</button>

			</form>
		</div>
	</div>
</div>





<div id="popup2" class="overlay">
	<div class="popup">
		<a class="close">×</a>
		<div class="content" style="width: 85%;margin: auto;max-height: 500px; padding: 1%; font-size: 14px;">
			<center>
                 <span id="sms-loading">
                    <i class="fa fa-refresh fa-spin fa-3x fa-fw" style=" color: #ea6645 "></i>
                 </span>
			</center>
			<div id="smsnotification"></div>
			<form role="form" method="POST" class="clearfix" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="smsform" name="createuser">
				To  <input type="text" name="numbers" id="numbers"/><br/>
				Message
				<div style="width: 100%;"><textarea style="width: 100%; height: 150px;" id="area2" name="area2" cols="92" rows="12" ></textarea>
					<div id="charNum">140</div>
				</div>
				<br/>
				<button type="button"  class="btn pull-right" id="sendmessage">Send SMS</button>

			</form>
		</div>
	</div>
</div>


<script type="text/javascript" src="<?php echo $CFG->wwwroot ?>/teacher/testcenter/js/nicEdit-latest.js"></script> <script type="text/javascript">
	//<![CDATA[
	bkLib.onDomLoaded(function() {
		new nicEditor({maxHeight : 260}).panelInstance('area1');

	});
	//]]>
</script>
<?php
echo $OUTPUT->footer();
?>
<script src="<?php echo $CFG->wwwroot; ?>/teacher/jquery.table2excel.js"></script>
<script>

	var baseUrl='<?php echo $CFG->baseUrl ?>';

	$j("#checkAll").change(function () {
		var name=$j(this).attr("name");
		$j("input[name="+name+"]:checkbox").prop('checked', $j(this).prop("checked"));
	});

	$j("#attandancecheckAll").change(function () {
		var name=$j(this).attr("name");
		$j("input[name="+name+"]:checkbox").prop('checked', $j(this).prop("checked"));
	});

	$j("#gradecheckAll").change(function () {
		var name=$j(this).attr("name");
		$j("input[name="+name+"]:checkbox").prop('checked', $j(this).prop("checked"));
	});


	$j(document).delegate(".testcenter-tabs li a","click",function(){
		if($j(".category").text()){
			$j(".current-selection").slideUp();
			$j(".search-content").slideDown();
			//$j(".students-list").html('');
			//$j($j(this).attr("href")+" .btn").click();
		}
	});

	var picker = new Pikaday({
		field: document.getElementById('datepicker'),
		format: 'YYYY-MM-D',
		onSelect: function() {
			document.getElementById('datepicker').value=this.getMoment().format('YYYY-MM-DD');
		}
	});

	$j(document).delegate(".cstudent","click",function(){
		//$j(this).data("scid");
		//$j(this).data("scatid");
		$j("#cateogry-dropdown").val($j(this).data("scatid"));
		getCourses($j("#cateogry-dropdown").val(),$j(this).data("scid"));



	});


	var d = new Date();
	var dat = d.getDate()+'-'+d.getMonth()+'-'+d.getFullYear();

	$j("#sxls").click(function(){
		$j("#myTable").table2excel({
			// exclude CSS class
			exclude: ".noExl",
			name: "Table2Excel",
			filename: dat+" coursewise report" //do not include extension
		});
	});



	$j("#cateogry-dropdown").on("change",function(){

			getCourses($j("#cateogry-dropdown").val(),'');
	});

	$j("#course-cateogry-dropdown").on("change",function(){
		getCourseInformation($j("#course-cateogry-dropdown").val());
	});

	$j("#attandance-cateogry-dropdown").on("change",function(){
		getAttandanceCourses($j("#attandance-cateogry-dropdown").val());
	});

	$j("#grade-cateogry-dropdown").on("change",function(){

		getGradeCourses($j("#grade-cateogry-dropdown").val());
	});

	$j("#attandance").on("change",function(){

		if(parseInt($j("#attandance").val())==3){
			$j("#datediv").hide();
			$j("#avg-attandance-div").show();
		}else{
			$j("#datediv").show();
			$j("#avg-attandance-div").hide();
		}
	});


	$j(document).delegate("#search","click",function(){


		if(parseInt($j("#cateogry-dropdown").val())==0){
			alert("please select category");
		}else{
			var subject=($j('#subjects-dropdown option[value="'+$j("#subjects-dropdown").val()+'"]').text());
			if(subject=='Select Subject'){
				getCourses($j("#cateogry-dropdown").val(),'');
			}
			var fname=$("#firstname").val();
			var lname=$("#lastname").val();
			var email=$("#email").val();
			getStudents($j("#subjects-dropdown").val(),$j("#cateogry-dropdown").val(),fname,lname,email);
		}


	});


	$j(document).delegate("#attandancesearch","click",function(){


		if(parseInt($j("#attandance-cateogry-dropdown").val())==0){
			alert("please select category");
		}else{
			var subject=($j('#attandance-subjects-dropdown option[value="'+$j("#attandance-subjects-dropdown").val()+'"]').text());
			if(subject=='Select Subject'){
				getAttandanceCourses($j("#attandance-cateogry-dropdown").val());
			}
			if(parseInt($j("#attandance-subjects-dropdown").val())==0){
				alert("please select subject");
			}else{
				var attandance=$j("#attandance").val();
				var sdate=$j("#datepicker").val();
				var student=$j("#astudentname").val();
				var avgattandance=$j("#avgattandance").val();
				getAttandanceStudents($j("#attandance-subjects-dropdown").val(),$j("#attandance-cateogry-dropdown").val(),attandance,sdate,student,avgattandance);
			}

		}


	});

	$j(document).delegate("#performancesearch","click",function(){


		if(parseInt($j("#grade-cateogry-dropdown").val())==0){
			alert("please select category");
		}else{
			var subject=($j('#grade-subjects-dropdown option[value="'+$j("#grade-subjects-dropdown").val()+'"]').text());
			if(subject=='Select Subject'){
				getGradeCourses($j("#grade-cateogry-dropdown").val());
			}
			if(parseInt($j("#grade-subjects-dropdown").val())==0){
				alert("please select subject");
			}else{
				var grade=$j("#grade").val();
				var student=$j("#pstudentname").val();
				getGradeStudents($j("#grade-subjects-dropdown").val(),$j("#grade-cateogry-dropdown").val(),grade,student);
			}

		}


	});



	$("#area2").keyup(function(){
		el = $(this);
		if(el.val().length >= 140){
			el.val( el.val().substr(0, 140) );
		} else {
			$("#charNum").text(140-el.val().length);
		}
	});

	$j("#sendsms").on("click",function(){
		$j("#smsform").show();

		$j("#smsnotification").html("");
		var selected = [];
		$('.student input:checked').each(function() {
			selected.push($(this).data('mobile'));
		});
		$j("#numbers").val(selected);
		$j("#popup2").addClass("overlay1");
		//alert(selected);

	});


	$j("#sendmessage").on("click",function(){
		var numbers=$j("#numbers").val();
		var mess=$j("#area2").val();
		//alert(mailids+subject+message);
		sendSms(numbers,mess);
	});

	$j("#sendmail").on("click",function(){
		$j("#notification").html("");
		$j("#createuser").show();
		var selected = [];
		$('.student input:checked').each(function() {
			selected.push($(this).data('email'));
		});
		$j("#mailids").val(selected);
		$j("#popup1").addClass("overlay1");
		//alert(selected);

	});

	$j(".close").on("click",function(){
		$j("#popup1").removeClass("overlay1");
		$j("#popup1").addClass("overlay");
		$j("#popup2").removeClass("overlay1");
		$j("#popup2").addClass("overlay");
		$j("#smsform").hide();
	});

	$j("#sendemail").on("click",function(){
		var mailids=$j("#mailids").val();
		var subject=$j("#subject").val();
		var message=$j(".nicEdit-main").html();
		//alert(mailids+subject+message);
		sendMail(mailids,subject,message);
	});

	$j(document).delegate(".current-selection","click",function(){

		$j(".current-selection").slideUp();
		$j(".search-content").slideDown();
	});

	function getCourses(catid,ccid){
		$j.ajax({
			type: "GET",
			dataType: 'html',
			data: {
				"trmid": 3,
				"trcatid": catid
			},
			url: baseUrl + "supervisor/myreports_ajax.php",
			success: function (data) {
				$j("#subjects-dropdown").html(data);

			},
			complete:function (data) {

				if(ccid){
					$j("#subjects-dropdown").val(ccid);
					$j("#courses-status").removeClass("active");
					$j("#b").removeClass("active");
					$j("#a").removeClass("hide");
					$j("#student-status").addClass("active");
					$j("#a").addClass("active");
					var fname=$j("#firstname").val();
					var lname=$j("#lastname").val();
					var email=$j("#email").val();
					getStudents($j("#subjects-dropdown").val(),$j("#cateogry-dropdown").val(),fname,lname,email);
				}


			}
		});
	}//end of getCourses

	function getAttandanceCourses(catid){
		$j.ajax({
			type: "GET",
			dataType: 'html',
			data: {
				"trmid": 3,
				"trcatid": catid
			},
			url: baseUrl + "supervisor/myreports_ajax.php",
			success: function (data) {
				$j("#attandance-subjects-dropdown").html(data);

			}
		});
	}//end of getCourses

	function getGradeCourses(catid){
		$j.ajax({
			type: "GET",
			dataType: 'html',
			data: {
				"trmid": 3,
				"trcatid": catid
			},
			url: baseUrl + "supervisor/myreports_ajax.php",
			success: function (data) {
				$j("#grade-subjects-dropdown").html(data);

			}
		});
	}//end of getCourses


	function getCourseInformation(catid){
		$j("#loading").show();
		$j.ajax({
			type: "GET",
			dataType: 'html',
			data: {
				"trmid": 2,
				"trcatid": catid
			},
			url: baseUrl + "supervisor/myreports_ajax.php",
			success: function (data) {
				$(".courseinfo-table").html(data);
			},
			complete: function (xhr, status) {
				$j("#loading").hide();
				$j("#myTable").trigger("update");
				// set sorting column and direction, this will sort on the first and third column

				$j("#myTable").trigger([]);

				// $j("#studentTable").tablesorter({});
				var $rows = $j('.course-list-table tbody tr');

				$j('.search').keyup(function() {
					var val = $j.trim($j(this).val()).replace(/ +/g, ' ').toLowerCase();

					$rows.show().filter(function() {
						var text = $j(this).text().replace(/\s+/g, ' ').toLowerCase();
						return !~text.indexOf(val);
					}).hide();
				});
			}
		});
	}//end of getResults

	$j("#sms-loading").hide();

	function sendSms(numbers,mess){
		$j("#sms-loading").show();

		$j.ajax({
			type: "GET",
			dataType: 'html',
			data: {
				"mid":7 ,
				"numbers": numbers,
				"message":mess
			},
			url: baseUrl + "portal/courseutils.php",

			success: function (data) {
				$j("#smsnotification").html(data);
				$j("#sms-loading").hide();
				$j("#smsform").hide();
				$j("#numbers").val('');
				$j("#area2").val('');


			}
		});
	}//end of getCourses


	function getStudents(cid,catid,fname,lname,email){
		var subject=($j('#cateogry-dropdown option[value="'+$j("#cateogry-dropdown").val()+'"]').text());
		$j(".category").text(subject);
		$j("#loading").show();
		$j.ajax({
			type: "GET",
			dataType: 'html',
			data: {
				"trmid": 4,
				"trcid": cid,
				"trcatid":catid,
				"firstname":fname,
				"lastname":lname,
				"email":email
			},
			url: baseUrl + "supervisor/myreports_ajax.php",
			success: function (data) {
				$j("#student-list").html(data);
				$j("#loading").hide();
				$j(".current-selection").slideDown();
				$j(".search-content").slideUp();
			}
		});
	}//end of getStudents

	function getAttandanceStudents(cid,catid,attandance,sdate,student,avgattandance){
		var subject=($j('#attandance-cateogry-dropdown option[value="'+$j("#attandance-cateogry-dropdown").val()+'"]').text());
		$j(".category").text(subject);
		$j("#loading").show();
		$j.ajax({
			type: "GET",
			dataType: 'html',
			data: {
				"trmid": 9,
				"trcid": cid,
				"trcatid":catid,
				"attandance":attandance,
				"sdate":sdate,
				"student":student,
				"avgattandance":avgattandance
			},
			url: baseUrl + "supervisor/myreports_ajax.php",
			success: function (data) {
				$j("#attandance-list").html(data);
				$j("#loading").hide();
				$j(".current-selection").slideDown();
				$j(".search-content").slideUp();
			}
		});
	}//end of getStudents



	function getGradeStudents(cid,catid,grade,student){
		var subject=($j('#grade-cateogry-dropdown option[value="'+$j("#grade-cateogry-dropdown").val()+'"]').text());
		$j(".category").text(subject);
		$j("#loading").show();
		$j.ajax({
			type: "GET",
			dataType: 'html',
			data: {
				"trmid": 10,
				"trcid": cid,
				"trcatid":catid,
				"grade":grade,
				"student":student
			},
			url: baseUrl + "supervisor/myreports_ajax.php",
			success: function (data) {
				$j("#grade-list").html(data);
				$j("#loading").hide();
				$j(".current-selection").slideDown();
				$j(".search-content").slideUp();
			}
		});
	}//end of getStudents

	function getResults(sid,catid){

		$j.ajax({
			type: "GET",
			dataType: "html",
			data: {
				"trmid": 7,
				"student": sid,
				"trcatid":catid
			},
			url: baseUrl +  "supervisor/myreports_ajax.php",
			success: function(data) {
				$j(".student-courseinfo-table").html(data);
			},
			complete: function (xhr, status) {
				$j("#loading").hide();
				$j("#myTable").trigger("update");
				// set sorting column and direction, this will sort on the first and third column

				$j("#myTable").trigger([]);

				var $rows = $j('.course-list-table tbody tr');

				$j('.search').keyup(function() {
					var val = $j.trim($j(this).val()).replace(/ +/g, ' ').toLowerCase();

					$rows.show().filter(function() {
						var text = $j(this).text().replace(/\s+/g, ' ').toLowerCase();
						return !~text.indexOf(val);
					}).hide();
				});
			},error: function(jqXHR, textStatus, errorThrown) {
				console.log("Error, textStatus: " + textStatus + " errorThrown: "+ errorThrown);


			}

		});
	}//end of getResults




	function sendMail(mailids,subject,message){
		$j("#email-loading").show();
		$j.ajax({
			type: "GET",
			dataType: 'html',
			data: {
				"trmid": 8,
				"mailids": mailids,
				"mailsubject":subject,
				"message":message
			},
			url: baseUrl + "supervisor/myreports_ajax.php",
			success: function (data) {

				$j("#notification").html(data);
				$j("#createuser").hide();
				$j("#email-loading").hide();
				$j("#mailids").val('');
				$j("#subject").val('');
				$j(".nicEdit-main").html('');
			}
		});
	}//end of sendMail



</script>