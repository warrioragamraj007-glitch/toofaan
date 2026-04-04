
<?php
require_once(dirname(__FILE__) . '/../config.php');
$PAGE->set_url('/supervisor/performanceinfo.php');
require_once("myreports_ajax.php");
require_login();
$PAGE->requires->js('/student/jquery-latest.min.js', true);
$PAGE->requires->js('/supervisor/js/supervisor.js', true);
$PAGE->requires->js('/teachingagent/moment.js',true);
$PAGE->requires->js('/teachingagent/pikaday.js',true);

$PAGE->requires->css('/supervisor/css/supervisor.css', true);

require_once($CFG->dirroot . '/my/lib.php');
$context = context_user::instance($USER->id);
$PAGE->set_context($context);
require_once($CFG->dirroot . '/course/lib.php');
$PAGE->set_title('Tessellator 4.0 - Supervisor Performance Info');
echo $OUTPUT->header();

$cid=$_GET['scid'];
$categoryid=$_GET['scatid'];
$date=date('Y-m-d');
?>
<link rel="stylesheet" type="text/css"  href="<?php echo $CFG->wwwroot ?>/teacher/reports/c3.css">
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

        background: rgb(255, 255, 255) url(<?php require_once(dirname(__FILE__) . '/../config.php'); global $CFG ; echo $CFG->wwwroot.'/pix/i/calendar.png';?>) no-repeat scroll left center;
    }

</style>

<div class='container'>
    <h2 class="studentsinfo-header">Performance Info</h2>
    <div class="main-content ">

        <div class="current-selection attendance-current-selection">
            <span class="category">Finishing School</span>
            <span class="showsearch" style="float:right"><i  class="fa fa-arrow-down" aria-hidden="true"></i></span>
        </div>

        <div class="search-content">
            <div class="col-md-12 search-box">
                <div class="student-filters col-md-12">


                    <div class="col-md-4" style='margin-bottom:5px'>
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


                    <div class="col-md-4" style='margin-bottom:5px'>
                        <div class="dropdown-lable">Subject</div>
                        <select id="subjects-dropdown">
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
                   <!-- <div class="col-md-4" style='margin-bottom:5px'>
                        <div class="text-label">Date</div>
                        <input type="text" id="datepicker" placeholder="Select Date" value="<?php echo $date;?>">
                    </div>-->

                    <div class="col-md-4" style='margin-bottom:5px'>
                        <div class="text-label">Name</div>
                        <input type="text" name="studentname" id="studentname" placeholder="Student Name"/>
                    </div>
                    <div class="col-md-5" style='margin-top:15px;text-align:center;'></div>

                    <div class="col-md-3" style='margin-top:15px;text-align:center;float:right'>
                        <input id="search" type="button" name="search" class="btn" value="Search" style="padding: 5px 25%;"/>
                    </div>
                </div>

                <!--<div class="col-md-12">


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



        <div class="results col-md-12" style="padding:0px;">
            <div class="students col-md-12"  style="padding:0px;">
                <table style="width:100%;" id="student-grade-table">
                    <thead>
                    <tr><th><input type="checkbox" name="selectstudents" id="checkAll"></th>
                        <th>
                            <span>Students</span>
                            <span id="loading">
                               <i class="fa fa-refresh fa-spin fa-1x fa-fw" style=" color: #ea6645 "></i>
                            </span>
                        </th>
                        <th>Grade</th></tr>
                    </thead>
                    <tbody class="students-list">
                    </tbody>
                </table>
            </div><!-- students -->

            <div class="col-md-12">
                <div class="col-md-6" style="text-align:center">
                    <input id="sendmail" type="button" name="search" class="btn" value="Send Mail">
                </div>
                <div class="col-md-6" style="text-align:center">
                    <input id="sendsms" type="button" name="search" class="btn" value="Send SMS">
                </div>

            </div>
        </div><!-- results -->
    </div>

</div>




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
</div><!-- popup1 -->

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
</div><!-- popup2 -->

<script type="text/javascript" src="<?php echo $CFG->wwwroot ?>/teacher/testcenter/js/nicEdit-latest.js"></script> <script type="text/javascript">
    //<![CDATA[
    bkLib.onDomLoaded(function() {
        new nicEditor({maxHeight : 260}).panelInstance('area1');

    });
    //]]>




</script>
<script src="<?php echo $CFG->wwwroot ?>/teacher/reports/js/d3-v3.min.js" charset="utf-8"></script>
<script src="<?php echo $CFG->wwwroot ?>/teacher/reports/js/c3.js"></script>
<?php
echo $OUTPUT->footer();


?>

<script>



    var picker = new Pikaday({
        field: document.getElementById('datepicker'),
        format: 'YYYY-MM-D',
        onSelect: function() {
            document.getElementById('datepicker').value=this.getMoment().format('YYYY-MM-DD');
        }
    });

    var baseUrl='<?php echo $CFG->baseUrl ?>';

    var cid='<?php echo $cid ?>';
    var catid='<?php echo $categoryid ?>';



    //alert($("#datepicker").val());
    if(parseInt(catid)&&parseInt(cid)){

        //alert(catid+'-'+cid);

        $j("#cateogry-dropdown").val(catid);
        getCourses($j("#cateogry-dropdown").val());
        var fname=$("#firstname").val();
        var lname=$("#lastname").val();
        var email=$("#email").val();
        getStudents($j("#subjects-dropdown").val(),$j("#cateogry-dropdown").val(),fname,lname,email);
    }



    $j("#cateogry-dropdown").on("change",function(){
        getCourses($j("#cateogry-dropdown").val());
    });


    $j(document).delegate("#search","click",function(){


        if(parseInt($j("#cateogry-dropdown").val())==0){
            alert("please select category");
        }else{
            var subject=($j('#subjects-dropdown option[value="'+$j("#subjects-dropdown").val()+'"]').text());
            if(subject=='Select Subject'){
                getCourses($j("#cateogry-dropdown").val());
            }
            if(parseInt($j("#subjects-dropdown").val())==0){
                alert("please select subject");
            }else{
                var grade=$j("#grade").val();
                var student=$j("#studentname").val();
                getStudents($j("#subjects-dropdown").val(),$j("#cateogry-dropdown").val(),grade,student);
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

    function getCourses(catid){
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
                if(cid){
                    $j("#subjects-dropdown").val(cid);
                }

            }
        });
    }//end of getCourses


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
    }//end of sendSms


    function getStudents(cid,catid,grade,student){
        var subject=($j('#cateogry-dropdown option[value="'+$j("#cateogry-dropdown").val()+'"]').text());
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
                $j(".students-list").html(data);
                $j("#loading").hide();
                $j(".current-selection").slideDown();
                $j(".search-content").slideUp();
            }
        });
    }//end of getStudents




    function getResults(sid,catid){

        $.ajax({
            type: "GET",
            dataType: "html",
            data: {
                "trmid": 7,
                "student": sid,
                "trcatid":catid
            },
            url: baseUrl +  "supervisor/myreports_ajax.php",
            success: function(data) {
                $(".student-courseinfo-table").html(data);
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