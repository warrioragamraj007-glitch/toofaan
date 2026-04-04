
<?php
require_once(dirname(__FILE__) . '/../config.php');
$PAGE->set_url('/supervisor/dashboard.php');
require_once("myreports_ajax.php");
require_login();
$PAGE->requires->js('/student/jquery-latest.min.js', true);
$PAGE->requires->js('/supervisor/js/supervisor.js', true);

$PAGE->requires->css('/supervisor/css/supervisor.css', true);

require_once($CFG->dirroot . '/my/lib.php');
$context = context_user::instance($USER->id);
$PAGE->set_context($context);
require_once($CFG->dirroot . '/course/lib.php');
$PAGE->set_title('Tessellator 4.0 - Supervisor Student Info');
echo $OUTPUT->header();

$cid=$_GET['scid'];
$categoryid=$_GET['scatid'];

?>
<link rel="stylesheet" type="text/css"  href="<?php echo $CFG->wwwroot ?>/teacher/reports/c3.css">
<style>


</style>

<div class='container'>
    <h2 class="studentsinfo-header">Students Info</h2>
    <div class="main-content">

        <div class="current-selection">
            <span class="category">Finishing School</span>
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



        <div class="results col-md-12" style="padding:0px;">
            <div class="students col-md-3"  style="padding:0px;">
                <table style="width:100%;" id="student-table">
                    <thead>
                    <tr><th><input type="checkbox" name="selectstudents" id="checkAll"></th>
                        <th>
                            <span>Students</span>
                            <span id="loading">
                               <i class="fa fa-refresh fa-spin fa-1x fa-fw" style=" color: #ea6645 "></i>
                            </span>
                        </th></tr>
                    </thead>
                    <tbody class="students-list">
                    </tbody>
                </table>
            </div>

            <div class="info col-md-9" >
                <div class="col-md-12" style="padding:5px;">
                    <ul class="nav nav-tabs">
                        <li id="performance" class="active">
                            <a href="#tab1">Performance</a>
                        </li>
                        <li id="profile">
                            <a href="#tab2">Profile</a>
                        </li>

                    </ul>
                    <section id="tab1" class="tab-content active">
                        <div>

                            <div class="col-md-12 status-box">

                                <div class="status-count">
                                    <div class="meangrade-label status-count-label">Mean Grade</div>
                                    <div class="status-values">0.00</div>
                                </div>
                                <div class="status-count">
                                    <div class="status-count-label attandance-label">Attandance</div>
                                    <div class="status-values">0/0</div>
                                </div>
                                <div class="status-count">
                                    <div class="status-count-label labs-label">Labs</div>
                                    <div class="status-values">0/0</div>
                                </div>
                                <div class="status-count">
                                    <div class="status-count-label quiz-lable">Quiz</div>
                                    <div class="status-values">0/0</div>
                                </div>

                            </div><!-- end of status box -->
                            <div class="student-results-div col-md-12">

                                <div class="table-responsive">
                                    <table class="table table-hover course-list-table tablesorter" id="myTable">
                                        <thead>
                                        <tr>
                                            <th style="">Subject Name</th>
                                            <th title="Average  Mean Grade" style="">Average  Mean Grade</th>
                                            <th title="Average Class Mean Grade" style="">Average  Class Mean Grade</th>
                                        </tr>
                                        </thead>

                                        <tbody class="student-courseinfo-table">

                                        </tbody>
                                    </table>
                                </div><!-- table responsiv end -->

                                <div class="col-md-12 graph-view" style=" margin-bottom: 25px;display:none">
                                    <div id="chart" class="col-md-9"><div class="no-data">No Graph is generated yet.</div> </div>
                                    <div class="col-md-1"></div>
                                </div>


                            </div><!-- end of course results-->

                        </div>
                    </section>
                    <section id="tab2" class="tab-content hide">

                        <div style="background: whitesmoke none repeat scroll 0% 0%;
margin: 9px 20px;" class="row prof">
                            <div class="col-md-2">
                                <figure class="course-image">
                                    <div class="image-wrapper">
                                        <img src="<?php echo $CFG->wwwroot ?>/user/pix.php/150/f1.jpg" style="margin:15px;"></div>
                                </figure>
                            </div>
                            <div class="col-md-10">
                                <header>
                                    <h2 class="course-date">--</h2>
                                    <div class="course-category">
                                        <div class="course-category pull-right" style="font-size: 16px; margin-top: -5px;">Email:<a href="#">
                                                --</a></div></div>


                                </header><hr style="margin-top: 5px;
margin-bottom: 13px;">
                                <div class="course-count-down pull-left">
                                    <figure class="course-start">Overall Grade:</figure>
                                    <!-- /.course-start -->
                                    <div style="" class="count-down-wrapper">0
                                    </div><!-- /.count-down-wrapper -->

                                </div>



                            </div>

                        </div>


                        <div class="col-md-12" style="border-bottom: 1px solid #e3e3e3; margin-bottom: 15px; padding-bottom: 8px; padding-right: 0;
    text-align: center;">

                            <span style="text-align: center;font-size: 16px;color:#000">Profile Information</span>

                        </div>



                        <div style=" margin-bottom: 25px;" class="student-profile-div col-md-12">
                            <table style="width: 100%;" class="my-profile-table">
                                <tbody>

                                <tr>
                                    <td style="font-family: &quot;Montserrat&quot;;" class="title">First Name</td>
                                    <td>
                                        <div class="input-group">
                                            --
                                        </div><!-- /input-group -->
                                    </td>
                                </tr>
                                <tr>
                                    <td class="title">Last Name</td>
                                    <td>

                                        <div class="input-group">

                                            --
                                        </div><!-- /input-group -->
                                    </td>
                                </tr>
                                <tr>
                                    <td class="title">Email</td>
                                    <td>
                                        <div class="input-group">
                                           --
                                        </div><!-- /input-group -->
                                    </td>
                                </tr>


                                </tbody>
                            </table>
                        </div>


                    </section><!-- end of tab2 -->
                </div>
                <div class="col-md-12">
                    <div class="col-md-6" style="text-align:center">
                        <input id="sendmail" type="button" name="search" class="btn" value="Send Mail">
                    </div>
                    <div class="col-md-6" style="text-align:center">
                        <input id="sendsms" type="button" name="search" class="btn" value="Send SMS">
                    </div>

                </div>
            </div>
        </div>
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
<script src="<?php echo $CFG->wwwroot ?>/teacher/reports/js/d3-v3.min.js" charset="utf-8"></script>
<script src="<?php echo $CFG->wwwroot ?>/teacher/reports/js/c3.js"></script>
    <?php
    echo $OUTPUT->footer();


    ?>

    <script>

        var baseUrl='<?php echo $CFG->baseUrl ?>';

        var cid='<?php echo $cid ?>';
        var catid='<?php echo $categoryid ?>';

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

        /*$j("#subjects-dropdown").on("change",function(){
            //getStudents($j("#subjects-dropdown").val(),$j("#cateogry-dropdown").val());
        });*/

        $j(document).delegate(".student","click",function(){
            //$j("#profile a").click();
            $j(".student").removeClass("activestudent");
            $j(this).addClass("activestudent");
            getStudentinfo($j(this).data("id"),$j("#cateogry-dropdown").val());
            getStudentPerformance($j(this).data("id"),$j("#cateogry-dropdown").val());
        });



        $j(document).delegate("#search","click",function(){


            if(parseInt($j("#cateogry-dropdown").val())==0){
                alert("please select category");
            }else{
                var subject=($j('#subjects-dropdown option[value="'+$j("#subjects-dropdown").val()+'"]').text());
                if(subject=='Select Subject'){
                    getCourses($j("#cateogry-dropdown").val());
                }
                var fname=$("#firstname").val();
                var lname=$("#lastname").val();
                var email=$("#email").val();
                getStudents($j("#subjects-dropdown").val(),$j("#cateogry-dropdown").val(),fname,lname,email);
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
                    $j(".students-list").html(data);
                    $j("#loading").hide();
                    $j(".current-selection").slideDown();
                    $j(".search-content").slideUp();
                }
            });
        }//end of getStudents

        function getStudentinfo(sid,catid){
            $j.ajax({
                type: "GET",
                dataType: 'html',
                data: {
                    "trmid": 5,
                    "student": sid,
                    "trcatid":catid
                },
                url: baseUrl +  "supervisor/myreports_ajax.php",
                success: function (data) {
                    $j("#tab2").html(data);
                }
            });
        }//end of getStudentinfo

        function getStudentPerformance(sid,catid){
            $j("#loading").show();
            $j.ajax({
                type: "GET",
                dataType: 'html',
                data: {
                    "trmid": 6,
                    "student": sid,
                    "trcatid":catid
                },
                url: baseUrl +  "supervisor/myreports_ajax.php",
                success: function (data) {
                   $j(".status-box").html(data);
                    getResults(sid,catid);
                }
            });
        }//end of getStudentPerformance


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