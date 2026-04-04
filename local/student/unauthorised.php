
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
</style>

<style>

    .coursename {
        min-height: 75px;
        background-color: #252525;
        color: #FFF;
        text-align: center;
        overflow-y: auto;
    }
    .coursename div{
        position: relative;
        top: 35%;
        margin-top:10%;
    }
    .topicname {
        width: 100%;
        height: 35px;
        background-color: #252525;
        box-shadow: 0px 0px 1px rgba(0, 0, 0, 0.6);
        font-size: 16px;
        font-weight: bold;
        color: #FFF;
        vertical-align: middle;
        padding-top: 5px;
        text-align: center;
        margin-top: 5px;
    }
    .tleft{

        background-color: #c5c5c5;
        padding: 10px;
        background-color: rgb(204, 204, 204); padding: 10px;
    }



    .tab-content{

        border: 2px solid #e2e2e2 !important;
        position: relative !important;
        top: -2px !important;
        padding-top: 5px !important;
        padding-bottom: 5px !important;
        padding-left: 10px !important;
        padding-right: 10px !important;
        margin-bottom:10px !important;

    }
    .btn.pull-right{
        text-transform:Uppercase !important;
    }
    .jsenabled .usermenu .moodle-actionmenu.show .menu.align-tr-br li:nth-child(3) {
        display: none;
    }
    .tab-content {
        min-height: 450px;
    }
    .nothingfound{
        font-size: 21px;
        text-align: center;
        margin-top: 8%;
    }
    .apilink{
        line-height: 20px;
        font-size: 18px;
        padding: 20px;
    }
</style>

<style>
    table {
        border: 1px solid #ddd;
        margin-top:10px !important;
        margin-left:20px;
        margin-bottom:10px;
    }
    tr{
        border: 1px solid #ddd;

    }
    td {
        font-size:16px;
        padding:25px;
        height:60px;
    }
    tr:hover{
        background-color:whitesmoke;
    }
    th{
        background-color:rgb(234, 102, 69);
        color:white;
        font-weight:bold;
        padding:25px;
        font-size:14px;
        height:40px;
    }
    table tr:nth-child(even)
    {
        background-color: whitesmoke;
    }
    table tr:nth-child(odd)
    {
        background-color:#fff;
    }
    #go{
        background-color:rgb(234, 102, 69);
        padding:8px 15px;
        color:white;
        font-size:14px;
        font-weight:bold;
        text-decoration: none;
    }

</style>

<style>
    .tab-content{
        border: 2px solid #e2e2e2 !important;
    }
    .tab-content {
        min-height: 430px;
        margin-bottom:10px;
    }
    .nothingfound{
        font-size: 21px;
        text-align: center;
        margin-top: 8%;
    }
    #feedbackcourse1{
        font-size: 16px;
        font-weight: bold;
        padding-bottom: 8px;
    }
    .feedback-form-div{
        width: 50%;
        margin: auto;
        border: 2px solid #e2e2e2 !important;
        padding: 2%;
    }
    .tab-content {
        border: 0px solid #e2e2e2 !important;
    }
</style>
<?php

require_once('../../config.php');


//require_once('custom_adobe.php');

//require_once('custom_tele.php');

$PAGE->set_url('/student/dashboard.php');
require_login();
if (!user_has_role_assignment($USER->id, 5)) {
    redirect($CFG->wwwroot);
}
// $PAGE->requires->js('/student/jquery-latest.min.js', true);

//$PAGE->requires->css('/student/student.css');
$PAGE->requires->js('/local/student/customchanges.js');
require_once($CFG->dirroot . '/my/lib.php');

//block courseoverview
// require_once($CFG->dirroot . '/blocks/course_overview/locallib.php');
$context = context_user::instance($USER->id);
$PAGE->set_context($context);
//course lib for getting activites
require_once($CFG->dirroot . '/course/lib.php');
/*************Bootstrap css***********/
//$PAGE->requires->css('/student/bootstrap.min.css');
//$PAGE->requires->css('/student/custom.css');
$PAGE->set_title('Tessellator 5.0.- unauthorised');
echo $OUTPUT->header();
//echo ' <link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet" type="text/css">';
echo "<input id='baseurl' type='hidden' value=" . $CFG->wwwroot . "/>";
/******************************
 ****Start of Todays Test page content********************/


/****Start of Tabs*******************/
?>

<link rel="stylesheet" href="<?php echo $CFG->wwwroot."/theme/universo/style/";?>jquery.fancybox.css?v=2.1.5" type="text/css" media="screen" />
<script type="text/javascript" src="<?php echo $CFG->wwwroot."/theme/universo/style/";?>jquery.fancybox.js?v=2.1.5"></script>

<script type="text/javascript" src="<?php echo $CFG->wwwroot."/theme/universo/style/";?>jquery.fancybox.pack.js?v=2.1.5"></script>
<div class='container'>



    <div class="tab-content course-tab-content">
        <?php

                        echo "";
       
        ?>
            <h1>You are not authorised to access. Please contact tessellator administrator.</h1>

        <?php
        echo $OUTPUT->footer();
        /* End of Today's tests Content*/
        ?>

        <script>

            var baseUrl=$j('#baseurl').val();
            var baseUrl='<?php echo $CFG->wwwroot; ?>';

            $j("#loading").hide();var baseUrl=$j('#baseurl').val();
            

            $j("#page-header").show();
            $j(".page-context-header").hide();
            $j('#page-navbar').append("<div id='navbar' style='padding: 2% 5% 2% 6%;'> <span>/</span>  <b>Not Authorized</b></div>");
            //$j('#dlink').attr("href",url);



            


        </script>

        <style>
            #page-student-dashboard #footer-bottom{
                z-index:10 !important;
            }

            #page-user-edit #id_moodle .fitem {
                width: 50% !important;
                float: left !important;
            }
            #fitem_id_submitbutton{
                width: 100%;
                text-align: center !important;
            }

            #fitem_id_submitbutton #id_submitbutton{

            }
        </style>
