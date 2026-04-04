<?php



$currentyearfield=$DB->get_field('user_info_field', 'id', array('shortname'=>'currentyear'));

if ((user_has_role_assignment($USER->id, 5))||(user_has_role_assignment($USER->id,3))) {
    $enrolledcourses = enrol_get_users_courses($USER->id);
    $sonetflag=0;
    $enrolledsonetcourses=array();
    foreach ($enrolledcourses as $key => $value) {
        if (is_object($value)) {
            if(in_array($value->id,$CFG->sonetcids)){
                $sonetflag=1;
                $enrolledsonetcourses[]=$value->id;
            }
        }
    }

}
$globalcid=0;
$globalsecid=0;
//teacher
if(user_has_role_assignment($USER->id,3)){
    $userrole=3;$cuserid=$USER->id;
    $currentyear=0;
}
//student
if(user_has_role_assignment($USER->id,5)){
    $userrole=5;$cuserid=$USER->id;
    //$currentyear=getTCStudentData($USER->id,$currentyearfield);
    $sql="SELECT `data` FROM `mdl_user_info_data` WHERE `userid` ='".$USER->id."' AND `fieldid` ='".$currentyearfield."'";
    $fielddata=$DB->get_record_sql($sql);
    //var_dump($sql);
    $currentyear=$fielddata->data;
    if($currentyear==4)$currentyear=2;

}

if($sonetflag && ($userrole==3 || $userrole==5)):


$baseUrl=$CFG->wwwroot;

    if($userrole==5){//student

    echo '<div  class="pagecover-onload1">
		    <div style="width: 600px; height: 45px; text-align: center; margin: 180px auto 0px;">
			<div>PLEASE WAIT SAVING YOUR FEEDBACK</div><div><img width="50px" src="'.$baseUrl.'/teacher/testcenter/images/orange-loader.gif"></div>
		   </div>
		    <div style="width: 600px; margin: 10px auto; text-align: center; color: rgb(100, 100, 100);">
		    <div class="loading-msg"></div>
		    </div>
		</div>';
    }
?>

<script>
    var host = "<?php echo $CFG->websocketurl ?>";// SET THIS TO YOUR SERVER
    var baseUrl='<?php echo $CFG->wwwroot; ?>';
    var currentuserrole='<?php echo $userrole ?>';
    var currentuserid='<?php echo $cuserid ?>';
    var currentyear='<?php echo $currentyear ?>';


    try {
        socket = new WebSocket(host);
        console.log(socket);
        console.log('WebSocket - status '+socket.readyState);
        socket.onopen    = function(msg) {
            console.log("Welcome - status "+this.readyState);

        };
        socket.onmessage = function(msg) {
            console.log("Received: "+msg.data);
            receiveMessage(msg.data);
        };
        socket.onclose   = function(msg) {
            console.log("Disconnected - status "+this.readyState);
        };
    }
    catch(ex){
        console.log(ex);
    }


    function receiveMessage(message){
        var obj = JSON.parse(message);
        console.log(obj);
        var role=obj['role'];
        var cidsecid=obj['message'];
        if (currentuserrole==role){
            askfeedback(cidsecid);
        }

    }//end of receiveMessage

</script>

<?php

/************** teacher functionality *******************/

if ((user_has_role_assignment($USER->id,3)))  : ?>
<script>

    $j(document).delegate("#askfeedback","click",function(){
        //alert("asking for feedback"+$j(this).data("cid")+$j(this).data("secid"));
        var cidsecid=$j(this).data("cid")+'-'+$j(this).data("secid")+'-'+$j(this).data("cyear");
        if (confirm('Do you want to request for feedback now?')) {
            msg = {mid: 4, message: cidsecid, role: 5};//sending message to role 5
            msg = JSON.stringify(msg);
            if (msg) {
                try {
                    socket.send(msg);
                    console.log('Sent: ' + msg);
                } catch (ex) {
                    console.log(ex);
                }
            }
        }//confirming about feedback

        /* updating sonet topic table */

        var fcid=parseInt($j(this).data("cid"));
        var fsecid=parseInt($j(this).data("secid"));
        var cyear=parseInt($j(this).data("cyear"));

        $j.ajax({
            url: baseUrl+"/teacher/testcenter/testcenterutil.php",
            data: {
                "mid": 21,
                "cid" :fcid,
                "secid":fsecid,
                "cyear":cyear
            },
            type: "GET",
            dataType: "html",
            success: function (data) {
                //var result = $j('<div />').append(data).html();
                //$j('#sub_list').html(result);

            },
            error: function (xhr, status) {
                //alert("Sorry, there was a problem!");
            },
            complete: function (xhr, status) {
                    $j("#askfeedback").attr("disabled",true);
                    $j("#askfeedback").attr("title","Already taken for this session");
            }
        });

        /* updating sonet topic table */

    });
</script>
<?php
/************** teacher functionality *******************/
endif; ?>


<?php
/************** student functionality *******************/

if ((user_has_role_assignment($USER->id,5)))  : ?>
    <script>
        var sonetcourse1='<?php echo $enrolledsonetcourses[0]; ?>';
        var sonetcourse2='<?php echo $enrolledsonetcourses[1]; ?>';
        var sonetcourses=<?php echo json_encode($enrolledsonetcourses); ?>;
        function askfeedback(cidsecid){
            //alert(currentuserid+" teacher is asking for feedback "+cidsecid);

            var cidsecidArray = cidsecid.split('-');
            $j("#fcid").val(cidsecidArray[0]);
            $j("#fsecid").val(cidsecidArray[1]);
            var enrollmentCheck=sonetcourses.indexOf(cidsecidArray[0]);
            if(parseInt(enrollmentCheck)!=-1){
                checkforfeedback(cidsecidArray[0],cidsecidArray[1]);
            }

            //$j("#modal_open").click();
        }
        //checkforfeedback(0,0);
        function checkforfeedback(cid,secid){
            $j.ajax({
                url: baseUrl+"/teacher/testcenter/testcenterutil.php",
                data: {
                    "mid": 22,
                    "uid" :currentuserid,
                    "cid":cid,
                    "secid":secid
                },
                type: "GET",
                dataType: "html",
                success: function (data) {
                    //var result = $j('<div />').append(data).html();
                    //$j('#sub_list').html(result);
                    var obj = JSON.parse(data);
                    //alert(obj['cid']);

                    if(obj['cid']&&obj['secid']&&obj['cname']){
                        $j("#fcid").val(obj['cid']);
                        $j("#fsecid").val(obj['secid']);
                        $j("#feedbackcourse").text("Feedback for '"+obj['cname']+"' on "+obj['takenon']);
                        $j("#modal_open").click();
                    }

                },
                error: function (xhr, status) {
                    //alert("Sorry, there was a problem!");
                },
                complete: function (xhr, status) {

                }
            });
        }


    </script>


<style type="text/css">
    .pagecover-onload1{
         position: absolute; width: 95%; background-color: rgb(255, 255, 255); z-index: 300; opacity: 0.9; height: 800px; top: 60px;margin-left: -10px;
        display: none;
    }

    #modal_wrapper.overlay::before {
        content: " ";
        width: 100%;
        height: 100%;
        position: fixed;
        z-index: 5000;
        top: 0;
        left: 0;
        background: #000;
        background: rgba(0,0,0,0.7);
    }

    #modal_window {
        display: none;
        z-index: 5001;
        position: fixed;
        left: 50%;
        top: 50%;
        width: 450px; /*360px;*/
        overflow: auto;
        padding: 10px 20px;
        background: #fff;
        border: 5px solid rgb(204, 204, 204);/*#999;*/
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.5);
    }

    #modal_wrapper.overlay #modal_window {
        display: block;
    }
    div.stars {
        width: 270px;
        display: inline-block;
    }

    input.star { display: none; }

    label.star {
        float: right;
        padding: 10px;
        font-size: 30px;
        color: #444;
        transition: all .2s;
    }

    input.star:checked ~ label.star:before {
        content: '\f005';
        color:#ffeb59;
        transition: all .25s;
    }

    input.star-5:checked ~ label.star:before {
        color: #FD4;
        /*text-shadow: 0 0 20px #efff94;*/
        text-shadow: 0 0 2px rgba(0,0,0,0.7);
    }

    input.star-1:checked ~ label.star:before { color: #F62; }

    label.star:hover { transform: rotate(-15deg) scale(1.3); }

    label.star:before {
        content: '\f006';
        font-family: FontAwesome;
    }

    input.star1 { display: none; }

    label.star1 {
        float: right;
        padding: 10px;
        font-size: 30px;
        color: #444;
        transition: all .2s;
    }

    input.star1:checked ~ label.star1:before {
        content: '\f005';
        color: #ffeb59;
        transition: all .25s;
    }

    input.star-10:checked ~ label.star1:before {
        color: #FD4;
        /*text-shadow: 0 0 20px #efff94;*/
        text-shadow: 0 0 2px rgba(0,0,0,0.7);

    }

    input.star-6:checked ~ label.star1:before { color: #F62; }

    label.star1:hover { transform: rotate(-15deg) scale(1.3); }

    label.star1:before {
        content: '\f006';
        font-family: FontAwesome;
    }
    #feedbackcourse{
        font-size: 16px;
        font-weight: bold;
        border-bottom: 1px solid #767676;
        padding-bottom: 8px;
    }
    .questions{
        font-size: 15px;
        font-weight: bold;
    }
</style>

<p style="display: none"><button  id="modal_open">Give Current Session Feedback</button></p>

<div id="modal_wrapper">
    <div id="modal_window">

        <div style="text-align: right;display: none"><a id="modal_close" href="#">close <b>X</b></a></div>

        <div style=""><p id="feedbackcourse"></p></div>

        <form id="modal_feedback"  accept-charset="UTF-8">
            <center>
            <div class="stars">
            <p class="questions">How was the session ? (<span class="star-lable" >0</span>)</p>

            <input class="star star-5" id="star-5" type="radio" name="star" value="5" required />
            <label class="star star-5" for="star-5"></label>
            <input class="star star-4" id="star-4" type="radio" name="star" value="4" />
            <label class="star star-4" for="star-4"></label>
            <input class="star star-3" id="star-3" type="radio" name="star" value="3" />
            <label class="star star-3" for="star-3"></label>
            <input class="star star-2" id="star-2" type="radio" name="star"  value="2" />
            <label class="star star-2" for="star-2"></label>
            <input class="star star-1" id="star-1" type="radio" name="star"  value="1" />
            <label class="star star-1" for="star-1"></label>

            <p class="questions" style="clear:both">How was the infrastructure ? (<span class="star1-lable" >0</span>)</p>

            <input class="star1 star-10" id="star-10" type="radio" name="star1" value="5"  required />
            <label class="star1 star-10" for="star-10"></label>
            <input class="star1 star-9" id="star-9" type="radio" name="star1"  value="4" />
            <label class="star1 star-9" for="star-9"></label>
            <input class="star1 star-8" id="star-8" type="radio" name="star1"  value="3" />
            <label class="star1 star-8" for="star-8"></label>
            <input class="star1 star-7" id="star-7" type="radio" name="star1"  value="2" />
            <label class="star1 star-7" for="star-7"></label>
            <input class="star1 star-6" id="star-6" type="radio" name="star1"  value="1" />
            <label class="star1 star-6" for="star-6"></label>
            </div>

            <p style="clear:both; margin-top: 10px;    margin-bottom: 0px;">
                <input class="btn" type="button" id="feedbackForm" value="Submit Feedback">
            </p>
            <input  id="fcid" type="hidden" value="<?php echo $globalcid; ?>" />
            <input  id="fsecid" type="hidden" value="<?php echo $globalsecid; ?>" />
            </center>
        </form>


    </div> <!-- #modal_window -->
</div> <!-- #modal_wrapper -->



<script type="text/javascript">

    // Original JavaScript code by Chirp Internet: www.chirp.com.au
    // Please acknowledge use of this code by including this header.

    var modal_init = function() {

        var modalWrapper = document.getElementById("modal_wrapper");
        var modalWindow  = document.getElementById("modal_window");

        var openModal = function(e)
        {
            modalWrapper.className = "overlay";
            var overflow = modalWindow.offsetHeight - document.documentElement.clientHeight;
            if(overflow > 0) {
                modalWindow.style.maxHeight = (parseInt(window.getComputedStyle(modalWindow).height) - overflow) + "px";
            }
            modalWindow.style.marginTop = (-modalWindow.offsetHeight)/2 + "px";
            modalWindow.style.marginLeft = (-modalWindow.offsetWidth)/2 + "px";
            e.preventDefault ? e.preventDefault() : e.returnValue = false;
        };

        var closeModal = function(e)
        {
            modalWrapper.className = "";
            e.preventDefault ? e.preventDefault() : e.returnValue = false;
        };

        var clickHandler = function(e) {
            if(!e.target) e.target = e.srcElement;
            if(e.target.tagName == "DIV") {
                if(e.target.id != "modal_window") closeModal(e);
            }
        };

        var keyHandler = function(e) {
            if(e.keyCode == 27) closeModal(e);
        };

        if(document.addEventListener) {
            document.getElementById("modal_open").addEventListener("click", openModal, false);
            document.getElementById("modal_close").addEventListener("click", closeModal, false);
            //document.addEventListener("click", clickHandler, false);
            //document.addEventListener("keydown", keyHandler, false);
        } else {
            document.getElementById("modal_open").attachEvent("onclick", openModal);
            document.getElementById("modal_close").attachEvent("onclick", closeModal);
            //document.attachEvent("onclick", clickHandler);
            //document.attachEvent("onkeydown", keyHandler);
        }

    };

</script>

<script type="text/javascript">

    document.addEventListener("DOMContentLoaded", modal_init, false);


    $j("input:radio[name='star']").change(function () {
        var me = $j(this);
        $j(".star-lable").text(me.attr('value'));
        console.log(me.attr('value'));
    });
    $j("input:radio[name='star1']").change(function () {
        var me = $j(this);
        $j(".star1-lable").text(me.attr('value'));
        console.log(me.attr('value'));
    });


    $j(document).delegate("#feedbackForm","click",function(){

            //alert($j("input:radio[name='star']:checked").val()+ ' '+
        // $j("input:radio[name='star1']:checked").val()+$j("#fcid").val()+' '+$j("#fsecid").val());

        var f1=parseInt($j("input:radio[name='star']:checked").val());
        var f2=parseInt($j("input:radio[name='star1']:checked").val());

        if(f1&&f2){

            var fcid=parseInt($j("#fcid").val());
            var fsecid=parseInt($j("#fsecid").val());
            var fuid=parseInt(currentuserid);

            document.getElementById("modal_close").click();
            $j(".pagecover-onload1").css("display", "block");
            $j.ajax({
                url: baseUrl+"/teacher/testcenter/testcenterutil.php",
                data: {
                    "mid": 20,
                    "cid" :fcid,
                    "secid":fsecid,
                    "uid":fuid,
                    "f1":f1,
                    "f2":f2
                },
                type: "GET",
                dataType: "html",
                success: function (data) {
                    //var result = $j('<div />').append(data).html();
                    //$j('#sub_list').html(result);
                    $j(".pagecover-onload1").css("display", "none");
                    alert(data);

                },
                error: function (xhr, status) {
                    //alert("Sorry, there was a problem!");
                },
                complete: function (xhr, status) {

                }
            });

        }else{
            alert("please select feedback");
        }


    })



</script>

    <?php
    /************** student functionality *******************/
endif; ?>


<?php endif; //end of checking for sonet user if($sonetflag): ?>
