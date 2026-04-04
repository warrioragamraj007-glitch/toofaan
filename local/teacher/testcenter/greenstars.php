<?php
require_once(dirname(__FILE__).'/../../../config.php');


$activityid = required_param('id', PARAM_INT);    // activity id
$cid = required_param('cid', PARAM_INT);    // courseid
$typeid = required_param('typeid', PARAM_INT);    // activitytypeid i.e vpl/quiz
$secname = optional_param('secname', 'All',PARAM_TEXT);
$activityName=optional_param('actname', 'All',PARAM_TEXT);
//$activityName=str_replace(' ', '_', $activityName);

require_login();
?>
<?php
if (!(user_has_role_assignment($USER->id,3) ) ) {

            redirect($CFG->baseUrl);
}
?>
<?php
echo '<style>.pagecover-onload{
	    display: none; position: absolute; width: 100%; background-color: rgb(255, 255, 255); z-index: 300; opacity: 0.9; height: 900px; top: 60px;margin-left: -10px;
	}
	body{
	background-color: #01366a;
	}
.connected-carousels .carousel-navigation
{
height:90px !important;
background:transparent !important;
border-radius:0% !important;
border:0px !important;
box-shadow:none !important;
}.connected-carousels .carousel-navigation li img{
border:0px !important;
    margin: 5px;
    height: 80px;
    width: 80px;

}.connected-carousels .carousel-stage{
box-shadow:none !important;
background-color:#f5f5f5;
}</style>';
echo '<div  class="pagecover-onload">
		    <div style="width: 600px; height: 45px; text-align: center; margin: 180px auto 0px;">
			<div>PLEASE WAIT</div><div><img src="'.$CFG->wwwroot.'/local/teacher/testcenter/images/loader.gif"></div>
		   </div>
		    <div style="width: 600px; margin: 10px auto; text-align: center; color: rgb(100, 100, 100);">
		    <div class="loading-msg"></div>
		    </div>
		</div>';
echo '<head><title>Tessellator 5.0 - Testcenter - GreenStars</title>
                        <style>
                        .image-caption {
                            color: #01366a;
                            font-family:"Montserrat","Arial",sans-serif;
                            font-size: 30px;
                            font-weight: bold;
                            text-align: center;
                            text-transform: uppercase;
	
                        }
                        .greenstar-container{
                        background-color: #012951 !important;
                        background-image: none !important;
                        color: #FFFFFF;padding: 15px;margin-top: -10px;
                        width: 100%;margin-left: -10px;
                         margin-top: -10px;
                        
                        }
                        .actname-span{
                        text-align: right;
                        }
.connected-carousels .next-navigation{
background-color: #ea6635;
border-radius: 0px !important;
box-shadow: none;
border: 1px solid transparent !important;}
                        </style>

                </head>
                <div class="greenstar-container">
                <img src="'.$CFG->wwwroot.'/theme/universo/pix/logo.png">
                <span style="float: right;
    font-weight: bold;
    line-height: 40px;
    margin-right: 20px;" class="actname-span" >ACTIVITY : '.$activityName.'</span></div>';
echo '<div id="content-div"></div>';


//var_dump($activityName);
?>

<script src="<?php echo $CFG->wwwroot; ?>/local/teacher/testcenter/js/jquery.min.js"></script>
<script>

    $j=$.noConflict();
    var baseUrl='<?php echo $CFG->wwwroot; ?>';
    var actname='<?php echo $activityName; ?>';
    var cid='<?php echo $cid; ?>';
    var current_activity_id='<?php echo $activityid; ?>';
    var section='<?php echo $secname; ?>';
    var activitytypeid='<?php echo $typeid; ?>';
    $j(document).on('ready',function() {
        $j(".pagecover-onload").css("display", "block");
        get_greenstars();
        setInterval(get_greenstars, 300000);

        function get_greenstars()
        {
           // $j(".pagecover-onload").css("display", "block");
            $j.ajax({
                url: baseUrl + "/local/teacher/testcenter/greenstar_sub_list.php",
                data: {
                    "cid": cid,
                    "secname": section,
                    "actid": current_activity_id,
                    "actname": actname,
                    "typeid": activitytypeid

                },
                type: "GET",
                dataType: "html",
                success: function (data) {
                    var result = $j('<div />').append(data).html();
                    $j('#content-div').html(result);
                },
                error: function (xhr, status) {
                    //alert("Sorry, there was a problem!");
                },
                complete: function (xhr, status) {
                    //$j("#myTable").tablesorter();$j(".pagecover").css("display","none");
                    $j(".pagecover-onload").css("display", "none");
                   /* $j('.carousel-stage')
                        .jcarousel({
                            wrap: 'circular'
                        })
                        .jcarouselAutoscroll({
                            interval: 3000,
                            target: '+=1',
                            autostart: true
                        });*/

                }
            });
        }
    });

</script>
