<style>
        .duplicate-ip {
            color: red;
            font-weight:bold;
        }
        
        
    </style>
<?php

	$params = explode("-", $_POST['topics']);
	$id=(int)$params[0];
	$cid=$id;   // courseid
	$secid = optional_param('secid',0, PARAM_INT);
	if(!$cid){
		$cid = optional_param('cid',0, PARAM_INT);
	}
	if($cid){

	}
	else{
		redirect($CFG->wwwroot.'/local/teacher/dashboard.php');
	}
	?>
	<!--including necessary libraries for the javascript-->

	<script src="<?php echo $CFG->wwwroot; ?>/local/teacher/testcenter/js/jquery.min.js"></script>
	<script type="text/javascript" src="<?php echo $CFG->wwwroot; ?>/local/teacher/testcenter/js/jquery.tablesorter.js"></script>


            <script>
                 var $j= jQuery.noConflict();

                $j(document).ready(function () {
//initializing script variables

        var lab='<?php echo $activityTypeIds['vpl']; ?>';
		var quiz='<?php echo $activityTypeIds['quiz']; ?>';
		var adobeconnect='<?php echo $activityTypeIds['adobeconnect']; ?>';
		var teleconnect='<?php echo $activityTypeIds['teleconnect']; ?>';
		var setIntervalId=0;
		var cid='<?php echo $cid; ?>';
		var baseUrl='<?php echo $CFG->wwwroot; ?>';
		var portal='<?php //echo $CFG->portal?>';
		var hideshowurl='<?php echo $CFG->wwwroot."/course/mod.php?sesskey=".sesskey()."&sr=0&" ?>';
		var websoc = '<?php echo $CFG->websocket; ?>';
		const socket = new WebSocket(websoc);
		// $j("#myTable").tablesorter();
			// $j("#page-header").show();
			// var url=baseUrl+'/teacher/dashboard.php';
			// $j('#page-navbar').append("<div id='navbar' style='padding: 2% 2% 0%;'> <a id='dlink'>Dashboard</a> <span>/</span><a id='clink'>Class Room</a> <span>/</span><b> Test center</b></div>");
			// $j('#dlink').attr("href",url);
			// $j('#clink').attr("href",url);



			get_loggedin_users();
			$j("#t01 tr").css("opacity","0.1");
			$j("#stas tr").css("opacity","0.1");
			$j(".pagecover").css("display", "block");
			get_activity_id();
			//on load call the activity
			call_Activity();

		$j('.avatar').removeClass('current'); 
		// $j('#myTab a').click(function (e) {
        //             e.preventDefault();

        //             $j(this).tab('show');
        //         });

		
// When "Current Activities" tab is clicked
      $j("#btnBar").click(function () {
        // Hide "foo" and show "bar"
          $j("#foo").hide("fast");
          $j("#bar").show("fast");
		  $j('#btnBar').addClass("current");
		  $j('#btnFoo').removeClass("current");

           });

 // When "Activity Status" tab is clicked
      $j("#btnFoo").click(function () {
          // Hide "bar" and show "foo"
          $j("#bar").hide("fast");
          $j("#foo").show("fast");
		  $j('#btnFoo').addClass("current");
		  $j('#btnBar').removeClass("current");

      });

 // fa fa users students logo click action
       $j('.students').click(function () {
            var modid=$j(this).data('mid');
            $j('#students-status a').click();
            $j("#filterdiv").slideDown();
            highlightDuplicateIPs();
        });
        



// CODE BY RAMMOHAN TO HIGHLISH DUPLICATE IPS
setInterval(highlightDuplicateIPs, 5000);
 function highlightDuplicateIPs() {
            var ipElements = document.querySelectorAll("#myTable td.ip");
            var ipCounts = {};
            ipElements.forEach(function(td) {
                var ip = td.textContent.trim();
                if (ipCounts[ip]) {
                    ipCounts[ip]++;
                } else {
                    ipCounts[ip] = 1;
                }
            });

            ipElements.forEach(function(td) {
                var ip = td.textContent.trim();
                if (ipCounts[ip] > 1) {
                    td.classList.add("duplicate-ip");
                } else {
                    td.classList.remove("duplicate-ip");
                }
            });
            
            
	var urlElements = document.querySelectorAll("#myTable td.url a");
	var urlCounts = {};

	// Count occurrences of each href
	urlElements.forEach(function(a) {
	    var href = a.getAttribute('href').trim();
	    if (urlCounts[href]) {
		urlCounts[href]++;
	    } else {
		urlCounts[href] = 1;
	    }
	});

	// Apply or remove the color red based on the count
	urlElements.forEach(function(a) {
	    var href = a.getAttribute('href').trim();
	    if (urlCounts[href] > 1) {
		a.style.color = "red";
		
	    } else {
		//a.style.color = "green";
		
	    }
	});


        }
        
  // end CODE BY RAMMOHAN TO HIGHLISH DUPLICATE IPS  

$j('.no-submission').click(function () {
			alert('This activity does not have submissions');

        });
		       
    $j('.tab-pane').not('.active').hide();    // hide students list  tabpane b
// side menu student button click action
        $j('#students-status').click(function () {
          $j('#bar').hide();
          $j('#foo').hide();
          $j('.tab-pane').show();
          $j('#students-status').addClass('active');
          $j('#activities-info').removeClass('active');
          $j(".course-detail-tabs").hide();
          $j("#filterdiv").slideDown();
     });

 // back button in students table
   $j('#goto-back').click(function () {
	  // console.log("activties clicked with goto back")
     $j('#activities-info a').click();
 });

// side menu activities button
     $j('#activities-info a').click(function () {
		// console.log("activties clicked")
        $j(".course-detail-tabs").show();
        $j('#activities-info').addClass('active');
        $j('#students-status').removeClass('active');
         $j("#filterdiv").slideUp();
        $j('#btnFoo').click();
         $j('#b').hide();
        $j('#a').show();
     });
            
//refresh button functionality
	$j(document).delegate("#refresh","click",function(){
     //   location.reload(true); // Reloads the page, forcing a cache refresh
		get_activity_id();
		if(parseInt($j('#hcactivity-id').val())==0) {
		  get_students_by_section(cid,$j('#stu-section').val());
		}
		else{
		if(($j('#modtypeid').val()==adobeconnect)||($j('#modtypeid').val()==teleconnect)){
			get_students_by_section(cid,$j('#stu-section').val());
		}
		else
			if(parseInt($j('#hcactivity-status').val())==0){
				getResults();
			}
			else{
				call_Activity();
			}
		}
	}); // end of refresh button

//refresh button functionality
	$j(document).delegate(".act-refresh","click",function(){
         //alert($j(this).data("id"));
         var activitytypeid=parseInt($j(this).data("mid"));
         var section=$j('#stu-section').val();
         var current_activity_id=parseInt($j(this).data("id"));
           $j(".refresh-load-img"+current_activity_id).show();
     $j.ajax({
	    url: baseUrl+"/local/teacher/testcenter/sub_list.php",
	   data: {
		      "id": current_activity_id,
		      "secname":section,
		      "typeid":activitytypeid,
		      "cid":cid
	        },
	    type: "GET",
	    dataType: "html",
	    success: function (data) {
		 var result = $j('<div />').append(data).html();
		 //$j('#sub_list').html(result);

		var subCount = $j($j.parseHTML(data)).filter("#subCount").text();
		var gradeCount = $j($j.parseHTML(data)).filter("#gradeCount").text();
		var loggedinusers = $j($j.parseHTML(data)).filter("#loggedinusers").text();
		var cstarCount = $j($j.parseHTML(data)).filter("#cstarCount").text();
		var crstarCount = $j($j.parseHTML(data)).filter("#crstarCount").text();
		var watchCount = $j($j.parseHTML(data)).filter("#watchCount").text();

		$j('.csubCount'+current_activity_id).text(subCount);
		$j('.cgradeCount'+current_activity_id).text(gradeCount);
		$j('.cstarCount'+current_activity_id).text(cstarCount);
		$j('.crstarCount'+current_activity_id).text(crstarCount);
		$j('.watchCount'+current_activity_id).text(watchCount);
		$j('.loggedinCount').text(loggedinusers);


	},
	error: function (xhr, status) {
		//alert("Sorry, there was a problem!");
	},
	complete: function (xhr, status) {
		$j(".refresh-load-img"+current_activity_id).hide();

		//updating activity status message
		if(parseInt($j('#hcactivity-status').val())==0){
			$j(".progress-activity-status").text("STOPPED");
		}else if(parseInt($j('#hcactivity-status').val())==1){
			$j(".progress-activity-status").text("STARTED");
		}else if(parseInt($j('#hcactivity-status').val())==2){
			$j(".progress-activity-status").text("CLOSED");
		}

	}     //end of complete statement
});       //end of ajax

});  // end of refresh button


// students list based on sections
      $j("#filterdiv > span").click(function(){
		// if(alert('hi')){}
			//alert($j(this).data("id")+'--'+$j(this).text());
		$j('#stu-section').val($j(this).text());
            //fetch current section count based on selection
			$j('#studentCount').val($j(this).data("id"));
			var studentCount = parseInt($j('#studentCount').val());
			$j('.studentCount').text(studentCount);
			//alert($j(this).text());

			if((parseInt($j('#hcactivity-status').val())==0)||(parseInt($j('#hcactivity-status').val())==2)){
				if(parseInt($j('#hcactivity-id').val())==0) {
					get_students_by_section(cid,$j(this).text());
				}
				else if(($j('#modtypeid').val()==adobeconnect)||($j('#modtypeid').val()==teleconnect)){
					get_students_by_section(cid,$j(this).text());
                 }
					else{
						getResults();
					}
				}
				else{
					if(($j('#modtypeid').val()==adobeconnect)||($j('#modtypeid').val()==teleconnect)){
						get_students_by_section(cid,$j(this).text());
					}
					else{
						call_Activity();
					}
				}

			}); // end of student list 


//this will perform retriving of current activity id from activity status table
			function get_activity_id(){
				var flag=1;

				$j.ajax({
					url: baseUrl+"/local/teacher/testcenter/testcenterutil.php",
					type: "GET",
					data: {
						"mid":4,
					},
					dataType: "json",
					success: function (data) {
						//alert(data.length);
						if(data.length>0) {
							$j.each(data, function (index, activity) {
								//checking whether the element is present in current doc or not
								if($j('.show'+activity.aid).length) {
									if(!$j("input[name='radio-button']:checked").val()){
										if(activity.status!=2) {
											if(flag==1){
												$j('#hcactivity-status').val(activity.status);
												$j('#hcactivity').val($j('.activitymod' + activity.aid).text());
												$j('#hcactivity-id').val(activity.aid);
												$j('#modtypeid').val($j(".show" + activity.aid).data('mid'));
												$j("#cactivity").text('Activity: ' + $j("#hcactivity").val());
												$j('.ca-radio-activity' + activity.aid).prop('checked', true);
												$j('.radio-activity' + activity.aid).prop('checked', true);
												flag=0;
											}
										}
									}
									else if($j('.radio-activity'+activity.aid).is(':checked')) {
										$j('#hcactivity-status').val(activity.status);
										$j('#hcactivity').val($j('.activitymod' + activity.aid).text());
										$j('#hcactivity-id').val(activity.aid);
										$j('#modtypeid').val($j(".show" + activity.aid).data('mid'));
										$j("#cactivity").text('Activity: ' + $j("#hcactivity").val());
										$j('.ca-radio-activity' + activity.aid).prop('checked', true);
										$j('.radio-activity' + activity.aid).prop('checked', true);

										//alert(activity.status==0);
									}//current activity selected
									if(activity.status==1){
										$j(".actstatus"+activity.aid).html("<b>STARTED </b><br/>on "+activity.start);
										$j(".actstatus"+activity.aid).removeClass('stopped');
										$j(".actstatus"+activity.aid).addClass('started');
										$j('.hide'+activity.aid).attr("disabled",false);
										$j('.hide'+activity.aid).css("cursor","pointer");
										$j('.show'+activity.aid).attr("disabled",true);
										$j('.show'+activity.aid).css("cursor","not-allowed");
										$j('.complete'+activity.aid).attr("disabled",true);
										$j('.complete'+activity.aid).css("cursor","not-allowed");
										$j('.crow' + activity.aid).show();
									}
									if(activity.status==0){
										$j(".actstatus"+activity.aid).html("<b>STOPPED </b><br/>on "+activity.stop);
										$j(".actstatus"+activity.aid).removeClass('started');
										$j(".actstatus"+activity.aid).addClass('stopped');
										$j('.hide'+activity.aid).attr("disabled",true);
										$j('.hide'+activity.aid).css("cursor","not-allowed");
										$j('.show'+activity.aid).css("cursor","pointer");
										$j('.show'+activity.aid).attr("disabled",false);
										$j('.complete'+activity.aid).attr("disabled",false);
										$j('.complete'+activity.aid).css("cursor","pointer");
										$j('.crow' + activity.aid).show();
									}
									if(activity.status==2){
										$j(".actstatus"+activity.aid).html("<b>CLOSED </b><br/>on "+activity.close);
										$j(".actstatus"+activity.aid).removeClass('stopped');
										$j(".actstatus"+activity.aid).addClass('closed');
										$j('.hide'+activity.aid).attr("disabled",true);
										$j('.hide'+activity.aid).css("cursor","not-allowed");
										$j('.show'+activity.aid).attr("disabled",true);
										$j('.show'+activity.aid).css("cursor","not-allowed");
										$j('.complete'+activity.aid).attr("disabled",true);
										$j('.complete'+activity.aid).css("cursor","not-allowed");
										$j('.crow' + activity.aid).show();

									}
								}//end of element checking
							});
						}

					},//end of success
					error: function (xhr, status) {
						//alert("Sorry, there was a problem!");
					},
					complete: function (xhr, status) {
						$j("#stas tr").css("opacity","1");
						$j("#t01 tr").css("opacity","1");
						$j(".pagecover").css("display", "none");
						// $j('.show'+startbtnid).hide();
						//$j('.hide'+stopbtnid).show();
					}
				});//ajax call end
			}   //end of the get_activity_id() function



//green stared students images display logic

	//call the results and start fetching results if activity status is available
	$j(document).delegate("#btngreenStars","click",function(){

   //if current page reloads or not stopped activities lead to call this logic
   var activitytypeid=parseInt($j('#modtypeid').val());
   var section=$j('#stu-section').val();
   var current_activity_id=parseInt($j('#hcactivity-id').val());
   var current_activity_status=parseInt($j('#hcactivity-status').val());
   if(current_activity_status){
	if((activitytypeid&&current_activity_id)&&(activitytypeid==quiz||activitytypeid==lab)){

		greenstar_getResults();

	}//end of checking all conditions
	else{
		alert("Greenstars cannot be available for this activity");
	}

  } //end of current_activity_status
 else{
	alert("No Green Stars found !!!");

  }
});

//this will get the list of students with submission and grade status based on student section
  function greenstar_getResults(){

   var activitytypeid=parseInt($j('#modtypeid').val());
   var section=$j('#stu-section').val();
   var current_activity_id=parseInt($j('#hcactivity-id').val());
   var actname=$j('.activitymod'+$j('#hcactivity-id').val()).text();
   var win = window.open(baseUrl+'/local/teacher/testcenter/greenstars.php?actname='+actname+'&id='+current_activity_id+'&secname='+section+'&typeid='+activitytypeid+'&cid='+cid+'', '_blank');
  if(win){
	//Browser has allowed it to be opened
	win.focus();
   }else{
	//Broswer has blocked it
	alert('No Red Stars found !!!');
  }

  }  //greenstar_getResults()



//red stared students images display logic

		//call the results and start fetching results if activity status is available
		$j(document).delegate("#btnredStars","click",function(){

      //if current page reloads or not stopped activities lead to call this logic
     var activitytypeid=parseInt($j('#modtypeid').val());
     var section=$j('#stu-section').val();
     var current_activity_id=parseInt($j('#hcactivity-id').val());
    var current_activity_status=parseInt($j('#hcactivity-status').val());
    if(current_activity_status){
	if((activitytypeid&&current_activity_id)&&(activitytypeid==quiz||activitytypeid==lab)){

		redstar_getResults();

	}  //end of checking all conditions
	else{
		alert("Redstars cannot be available for this activity");
	}

    }//end of current_activity_status
  else{
	   alert("No Red Stars found !!!");
   }
 });

//this will get the list of students with submission and grade status based on student section
   function redstar_getResults(){

     var activitytypeid=parseInt($j('#modtypeid').val());
     var section=$j('#stu-section').val();
     var current_activity_id=parseInt($j('#hcactivity-id').val());
     var actname=$j('.activitymod'+$j('#hcactivity-id').val()).text();
     var win = window.open(baseUrl+'/local/teacher/testcenter/redstars.php?actname='+actname+'&id='+current_activity_id+'&secname='+section+'&typeid='+activitytypeid+'&cid='+cid+'', '_blank');
    if(win){
    	//Browser has allowed it to be opened
	  win.focus();
    }else{
	//Broswer has blocked it
	alert('Please allow popups for this site');
  }

 }   //redstar_getResults()

// sidemenu similarity button
   $j(document).delegate("#btnSimilarity","click",function(){
			var baseu='<?php echo $CFG->wwwroot; ?>';
			var current_activity_id=parseInt($j('#hcactivity-id').val());
		var url = baseu+"/mod/vpl/similarity/similarity_form.php?id="+parseInt($j('#hcactivity-id').val());
			window.open(url);
		});


// start or stop a activity button logic
           
           $j(".showhide").click(function(){
                   
            var status;
            if($j(this).attr('id') == 'show'){
               status="start";
              }
            else{
              status="stop";
               }

        if (confirm('Do you want to "'+status+'" ?')) {

          var clickvalue = 'mod' + $j(this).attr('value');
           var modtypeid = $j(this).data('mid');
           var modid = $j(this).attr('value');

         $j('.ca-radio-activity' + modid).prop('checked', true);
         $j('.radio-activity' + modid).prop('checked', true);
         $j('.complete' + modid).attr("disabled", true);
       //changing and storing current activity id and name based on selection
         $j("#hcactivity").val($j(".activity" + clickvalue).text());
         $j("#hcactivity-id").val(modid);
         $j('#remote-activity').val($j(this).data('rmodid'));
         $j(".progress-activity").text($j(".activity" + clickvalue).text());
         $j('#cactivity').text('Activity: ' + $j('#hcactivity').val());
         $j('#modtypeid').val(modtypeid);
           var id = $j(this).attr('id');
             var value = $j(this).attr('value');
            var hideshowajax = hideshowurl + id + '=' + value;

          //this will perform storing of current activity id and time
         if ($j(this).attr('id') == 'show') {

          $j("#current-activity").text("STARTED");
          $j("#current-activity").css("color", "rgb(70, 165, 70)");
          $j('.show' + modid).css("cursor", 'not-allowed');
          $j('.complete' + modid).css("cursor", 'not-allowed');
          $j('.show' + modid).css('background-color', '#d1d3d3 !important');
          $j('.hide' + modid).attr("disabled", false);
          $j('.crow' + modid).show();
          $j('.hide' + modid).css("cursor", 'pointer');
          $j('.show' + modid).attr("disabled", true);
          $j('#hcactivity-status').val(1);

          record_activity_start_date(modid);

        }
    if ($j(this).attr('id') == 'hide') {

        $j("#current-activity").text("STOPPED");
        $j('#current-activity').css("color", 'rgb(157, 38, 29)');
        $j('.hide' + modid).css("cursor", 'not-allowed');
        $j('.hide' + modid).css('background-color', '#d1d3d3 !important');
        $j('.hide' + modid).attr("disabled", true);
        $j('.show' + modid).attr("disabled", false);
        $j('.complete' + modid).attr("disabled", false);
        $j('.show' + modid).css("cursor", 'pointer');
        $j('.complete' + modid).css("cursor", 'pointer');
        //$j('.hide'+modid).hide();$j('.complete'+modid).show();$j('.show'+modid).show();
        record_activity_stop_date(modid);
   
        }

      //ajax call to show or hide the activity to the student
              $j.ajax({
                 url: hideshowajax,
                 type: "GET",
                 dataType: "html",
                success: function (data) {

                  },
                 error: function (xhr, status) {
                 alert("Sorry, there was a problem!");
                  },
                 complete: function (xhr, status) {

                        if (id == 'show') {
                          //intimate that activity is started
                            call_Activity();
							// var coursestarted = "activity started";
                          //intimate that activity is started
						  const messageObject = {
                userId: cid, 
                username: 'Teacher', 
                content: "started",
            };

            socket.send(JSON.stringify(messageObject));
                         if(modtypeid==adobeconnect){
                           var adobeurl=$j('#adobe-'+value).val();
                              //alert(adobeurl);
                             window.open(adobeurl);
                          }

                          if(modtypeid==teleconnect){
                            var teleurl=$j('#tele-'+value).val();
teleurl=baseUrl + "/local/teacher/adobelogin.php?connect-name=teleconnect&mlink="+teleurl
                               window.open(teleurl);
                            }

                         }
                     if (id == 'hide') {
                        var setint = parseInt($j("#setinterval-id").val());
                       clearInterval(setint);
                       $j("#setinterval-id").val(0);
                       $j("#hcactivity-status").val(0);
                       getResults();
                       $j("#cactivity").text('Activity: ' + $j("#hcactivity").val() + " :STOPED");
					//    var coursestopped = "activity stopped";
					   const messageObject = {
                userId: cid, 
                username: 'Teacher', 
                content:`stopped`,
            };

            socket.send(JSON.stringify(messageObject));
                      } 

                   }
             });    //hide or show ajax call end


          }    //confirmation message

      });     //showhide click function end

//this will perform storing of current activity id and time in a table
			function record_activity_start_date(modid){
				var statustime='actstatus'+modid;
				$j.ajax({
					url: baseUrl+"/local/teacher/testcenter/testcenterutil.php",
					type: "GET",
					data: {
						"aid": modid,
						"mid":2,
					},
					dataType: "html",
					success: function (data) {
						$j("."+statustime).html("<b>STARTED </b><br/>on " + data);
						$j(".actstatus"+modid).removeClass('stopped');
						$j(".actstatus"+modid).addClass('started');
					},
					error: function (xhr, status) {
						//alert("Sorry, there was a problem!");
					},
					complete: function (xhr, status) {
					}
				});     //ajax call end
			}     //end of the record_activity_start_date() function

//this will perform storing of current activity id and stop time in a table
			function record_activity_stop_date(modid){
				var statustime='actstatus'+modid;

				$j.ajax({
					url: baseUrl+"/local/teacher/testcenter/testcenterutil.php",
					type: "GET",
					data: {
						"aid": modid,
						"mid":16,
					},
					dataType: "html",
					success: function (data) {
						//alert(data);
						$j("."+statustime).html("<b>STOPPED </b><br/>on " + data);
						$j(".actstatus"+modid).removeClass('started');
						$j(".actstatus"+modid).addClass('stopped');
					},
					error: function (xhr, status) {
						//alert("Sorry, there was a problem!");
					},
					complete: function (xhr, status) {
					}
				});    //ajax call end
			}     //end of the record_activity_stop_date() function


//call the results and start fetching results if activity status is available
	  		function call_Activity(){

       //if current page reloads or not stopped activities lead to call this logic
          var activitytypeid=parseInt($j('#modtypeid').val());
           var section=$j('#stu-section').val();
          var current_activity_id=parseInt($j('#hcactivity-id').val());
          var current_activity_status=parseInt($j('#hcactivity-status').val());
          if(current_activity_status && (current_activity_status!=2)){
            if((activitytypeid&&current_activity_id)&&(activitytypeid==quiz||activitytypeid==lab)){

               //$j('.actstatus'+current_activity_id).html("<b>STARTED </b><br/>on "+getCurrentDateTime());

              $j("#current-activity").text("STARTED");
              $j("#current-activity").css("color","rgb(70, 165, 70)");
              $j(".actstatus"+current_activity_id).removeClass('stopped');
              $j(".actstatus"+current_activity_id).addClass('started');
              $j('.show'+current_activity_id).css("cursor",'not-allowed');
                $j('.complete'+current_activity_id).css("cursor",'not-allowed');
              $j('.show'+current_activity_id).css('background-color','#d1d3d3 !important');
               $j('.hide'+current_activity_id).attr("disabled",false);
                $j('.hide'+current_activity_id).css("cursor",'pointer');
                $j('.show'+current_activity_id).attr("disabled",true);
                 //$j('#current_activity').text($j('#hcactivity').val());
                 $j(".progress-activity").text($j('#hcactivity').val());

                $j('.complete'+current_activity_id).attr("disabled",true);


                var setInt=parseInt($j("#setinterval-id").val());
                  if(setInt){
                     clearInterval(setInt);
                     $j("#setinterval-id").val(0);
                          }
                    getResults();


                 if(parseInt($j('#refresh-status').val())) {
                     $j("#setinterval-id").val(setInterval(getResults, 30000));

                     }
               else{
                var setInt=parseInt($j("#setinterval-id").val());
                if(setInt){
                clearInterval(setInt);
                $j("#setinterval-id").val(0);
               }
            }

         }//end of checking all conditions
      else{
             //alert("something went wrong, Refresh Page and Continue..");
          }
        }//end of current_activity_status
       else{
         $j('.stop').attr("disabled",'true');
         $j('.complete').attr("disabled",'true');
        $j('.stop').css("cursor",'not-allowed');
        $j('.complete').css("cursor",'not-allowed');

            }
         }   // end call activity

        
//this will get the list of students with submission and grade status based on student section
	function getResults(){

      var activitytypeid=parseInt($j('#modtypeid').val());
      var section=$j('#stu-section').val();
       var current_activity_id=parseInt($j('#hcactivity-id').val());

    if(activitytypeid==lab||activitytypeid==quiz){

	  //check whether the auto refresh is on or off

	if(parseInt($j('#refresh-status').val())) {
		get_activity_id();
	}
	else{
		var setInt=parseInt($j("#setinterval-id").val());
		if(setInt){
			clearInterval(setInt);
			$j("#setinterval-id").val(0);
		}
	}

	//pre-cheking all the variable to start getting results

	$j(".loading-img").css("display","block");
	$j("#stas tr").css("opacity","0.1");
	$j.ajax({
		url: baseUrl+"/local/teacher/testcenter/sub_list.php",
		data: {
			"id": current_activity_id,
			"secname":section,
			"typeid":activitytypeid,
			"cid":cid
		},
		type: "GET",
		dataType: "html",
		success: function (data) {
			var result = $j('<div />').append(data).html();
			$j('#sub_list').html(result);

			var subCount = $j($j.parseHTML(data)).filter("#subCount").text();
			var gradeCount = $j($j.parseHTML(data)).filter("#gradeCount").text();
			var activity_status = $j($j.parseHTML(data)).filter("#acivitystatus").text();
			var loggedinusers = $j($j.parseHTML(data)).filter("#loggedinusers").text();
			var statusstopdate=$j($j.parseHTML(data)).filter("#statusstopdate").text();
			var cstarCount=$j($j.parseHTML(data)).filter("#cstarCount").text();
			var crstarCount=$j($j.parseHTML(data)).filter("#crstarCount").text();
			var watchCount=$j($j.parseHTML(data)).filter("#watchCount").text();

			$j('#csubCount').text(subCount);
			$j('#cgradeCount').text(gradeCount);
			$j('#cstarCount').text(cstarCount);
			$j('#crstarCount').text(crstarCount);
			$j('#watchCount').text(watchCount);
			$j('.csubCount'+current_activity_id).text(subCount);
			$j('.cgradeCount'+current_activity_id).text(gradeCount);
			$j('.cstarCount'+current_activity_id).text(cstarCount);
			$j('.crstarCount'+current_activity_id).text(crstarCount);
			$j('.watchCount'+current_activity_id).text(watchCount);
			$j('.loggedinCount').text(loggedinusers);
			$j('#hcactivity-status').val(activity_status);
		},
		error: function (xhr, status) {
			//alert("Sorry, there was a problem!");
		},
		complete: function (xhr, status) {
			if(parseInt($j('#modtypeid').val())==quiz){
				var totalRows = ($j("#myTable")[0].tBodies[0] && $j("#myTable")[0].tBodies[0].rows.length) || 0;
				if(totalRows>0){
					$j("#myTable").tablesorter({
						// sort on the fourth column , order asc
						sortList: [[4,1]]
					});
				}
			}
			if(parseInt($j('#modtypeid').val())==lab){
				$j("#myTable").tablesorter();
			}


			$j(".loading-img").css("display","none");
			$j("#stas tr").css("opacity","1");

			if(parseInt($j('#hcactivity-status').val())){

				$j("#current-activity").text("STARTED");
				$j("#current-activity").css("color","rgb(70, 165, 70)");

				$j(".actstatus"+$j('#hcactivity-id').val()).removeClass('stopped');
				$j(".actstatus"+$j('#hcactivity-id').val()).addClass('started');
				$j('.radio-activity' + $j('#hcactivity-id').val()).attr("checked","checked");
				$j('.ca-radio-activity' + $j('#hcactivity-id').val()).attr("checked","checked");
				//$j('#hcactivity-status').val(1);

			}
			else{
				//disable stop button

				if(parseInt(statusstopdate)){
					$j('.actstatus'+$j('#hcactivity-id').val()).html("<b>STOPPED </b><br/>on "+statusstopdate);
				}
				$j(".actstatus"+$j('#hcactivity-id').val()).removeClass('started');
				$j(".actstatus"+$j('#hcactivity-id').val()).addClass('stopped');
				$j('.hide'+$j('#hcactivity-id').val()).css("cursor",'not-allowed');
				$j("#current-activity").text("STOPPED");
				$j('#current-activity').css("color",'rgb(157, 38, 29)');
				$j('.hide'+$j('#hcactivity-id').val()).css('background-color','#d1d3d3 !important');
				$j('.hide'+$j('#hcactivity-id').val()).attr("disabled",true);
				$j('.show'+$j('#hcactivity-id').val()).attr("disabled",false);
				$j('.complete'+$j('#hcactivity-id').val()).attr("disabled",false);

				$j('.show'+$j('#hcactivity-id').val()).css("cursor",'pointer');
				$j('.complete'+$j('#hcactivity-id').val()).css("cursor",'pointer');
				$j('.radio-activity' + $j('#hcactivity-id').val()).attr("checked","checked");
				$j('.ca-radio-activity' + $j('#hcactivity-id').val()).attr("checked","checked");

				var setInt=parseInt($j("#setinterval-id").val());
				if(setInt){
					clearInterval(setInt);
					$j("#setinterval-id").val(0);
				}
				//need to make enable start and complete buttons
			}


			//updating activity status message
			if(parseInt($j('#hcactivity-status').val())==0){
				$j(".progress-activity-status").text("STOPPED");
			}else if(parseInt($j('#hcactivity-status').val())==1){
				$j(".progress-activity-status").text("STARTED");
			}else if(parseInt($j('#hcactivity-status').val())==2){
				$j(".progress-activity-status").text("CLOSED");
			}



		  }//end of complete statement
	  });
   }//end of checking activity id
  } //get_results()
   
//this will get the list of students based on section
			function get_students_by_section(cid,section){
				$j(".pagecover").css("display","block");
				$j("#stas tr").css("opacity","0.1");
				$j.ajax({
					url: baseUrl+"/local/teacher/testcenter/enrolledstudent.php",
					data: {
						"cid": cid,
						"secname":section

					},
					type: "GET",
					dataType: "html",
					success: function (data) {
						var result = $j('<div />').append(data).html();
						$j('#sub_list').html(result);

						var loggedinusers = $j($j.parseHTML(data)).filter("#loggedinusers").text();
						var watchCount=$j($j.parseHTML(data)).filter("#watchCount").text();
						$j('.loggedinCount').text(loggedinusers);

						$j('.watchCount').text(watchCount);
						$j('#watchCount').text(watchCount);

					},
					error: function (xhr, status) {
						//alert("Sorry, there was a problem!");
					},
					complete: function (xhr, status) {
						$j("#myTable").tablesorter();$j(".pagecover").css("display","none");
						$j("#stas tr").css("opacity","1");
					}
				});


			}//end of get_students_by_section() function

//this used to add student into watchlist
			$j(document).delegate(".watchlist","click",function(){
           
				var watchlistStatus;
				if(parseInt($j(this).data('ref'))){
					watchlistStatus="Remove from";
				}
				else{
					watchlistStatus="Add to";
				}


				if (confirm('Do you  want to  '+watchlistStatus+' watchlist?')) {
					if(parseInt($j(this).data('ref'))){
						$j(this).attr("src",baseUrl+"/local/teacher/testcenter/images/unwatch-512.png");
						$j(this).data('ref',0);
						$j('watchlist-status'+$j(this).attr("id")).text('');
					}
					else{
						$j(this).attr("src",baseUrl+"/local/teacher/testcenter/images/eye-24-512.png");
						$j(this).data('ref',1);
						$j('watchlist-status'+$j(this).attr("id")).text(1);
					}

					var stuid=$j(this).attr('id');

					$j.ajax({
						url: baseUrl+"/local/teacher/testcenter/testcenterutil.php",
						type: "GET",
						data: {
							"uid": stuid,
							"cid":cid,
							"mid":6,
						},
						dataType: "html",
						success: function (data) {
							//var result = $j('<div />').append(data).html();
							/* $j('#sub_list').html(result);*/
							//alert(data);

						},
						error: function (xhr, status) {
							//alert("Sorry, there was a problem!");
						},
						complete: function (xhr, status) {

							//$j('.complete'+modid).attr('disabled','true');
						}
					});//ajax call end
				}//end of checking confirmation message
			});//end of watchlist action function



// switch between activities  functionality by clicking radio button

 $j(document).delegate(".radio-activity,.ca-radio-activity","click",function(){
				// if (confirm('Do you want to Switch Activity ?')) {
          var clickvalue = 'mod' + $j(this).data('id');
				var modtypeid = $j(this).data('mid');
				var modid =$j(this).data('id');

				$j('.ca-radio-activity' + modid).prop('checked', true);
				$j('.radio-activity' + modid).prop('checked', true);
				//changing and storing current activity id and name based on selection
				$j("#hcactivity").val($j(".activity" + clickvalue).text());
				$j(".progress-activity").text($j(".activity" + clickvalue).text());
				$j("#hcactivity-id").val(modid);
				$j('#remote-activity').val($j(this).data('rmodid'));
				//$j("#current-activity").text($j(".activity" + clickvalue).text());
				$j('#cactivity').text('Activity: ' + $j('#hcactivity').val());
				$j('#modtypeid').val(modtypeid);

				//get_activity_id();
				if((parseInt($j('#hcactivity-id').val())==0)) {
					get_students_by_section(cid,$j('#stu-section').val());
				}
				if(($j('#modtypeid').val()==adobeconnect)||($j('#modtypeid').val()==teleconnect)){
					get_students_by_section(cid,$j('#stu-section').val());
				}

				else{
					if(parseInt($j('#hcactivity-status').val())==0&&(($j(this).data('status')===0)||($j(this).data('status')===1))){
						getResults();
					}
					else{
						call_Activity();
					}

				}
			});

//this will get the number of students logged in based on section
		function get_loggedin_users(){

          var sec=$j('#stu-section').val();
          $j.ajax({
	      url: baseUrl+"/local/teacher/testcenter/enrolledstudent.php",
	     data: {
		  "cid": cid,
		  "secname":sec

	       },
	      type: "GET",
	      dataType: "html",
	success: function (data) {
		var result = $j('<div />').append(data).html();
		//$j('#sub_list').html(result);

		var loggedinusers = $j($j.parseHTML(data)).filter("#loggedinusers").text();
		var watchCount=$j($j.parseHTML(data)).filter("#watchCount").text();
		$j('.loggedinCount').text(loggedinusers);

		$j('.watchCount').text(watchCount);
		$j('#watchCount').text(watchCount);

	},
	error: function (xhr, status) {
		//alert("Sorry, there was a problem!");
	},
	complete: function (xhr, status) {
		//$j("#myTable").tablesorter();$j(".pagecover").css("display","none");
	}
});

}//end of get_loggedin_users() function


//check reset logins button functionality
$j(document).delegate("#reset-logins","click",function(){

if (confirm('Do you want to Refresh-All-Loggins ?')) {
	$j(".pagecover-onload").css("display", "block");
	$j("#stas tr").css("opacity","0.1");
	$j.ajax({
		url: baseUrl+"/local/teacher/testcenter/testcenterutil.php",
		data: {
			"mid": 15,
			"cid" :cid
		},
		type: "GET",
		dataType: "html",
		success: function (data) {
			var result = $j('<div />').append(data).html();
			//$j('#sub_list').html(result);

		},
		error: function (xhr, status) {
			//alert("Sorry, there was a problem!");
		},
		complete: function (xhr, status) {
			$j(".pagecover-onload").css("display", "none");
			$j("#stas tr").css("opacity","1");
		}
	});
}

});




    

                // function getAgentInfo(){
                //     var baseUrl='< echo $CFG->wwwroot; ?>';

                //     var cid='< echo $cid; ?>';
                //     var aid=$j('.radio-activity:checked').data('id');
                //     //alert(aid);
                //     $j.ajax({
                //         url: baseUrl+"/webrtc/webrtcutil.php",
                //         data: {
                //             "mid": 4,

                //             "aid":aid
                //         },
                //         type: "GET",
                //         dataType: "html",
                        // success: function (data) {
                        //     $j("#activity-name").html(data);
                        // },
                        // error: function (xhr, status) {
                        //     //alert("Sorry, there was a problem!");
                        // },
                        // complete: function (xhr, status) {

                    //     }
                    // });
                    // $j.ajax({
                    //     url: baseUrl+"/teacher/tareports/resportsUtil.php",
                    //     data: {
                    //         "id": 4,
                    //         "cid":cid,
                    //         "aid":aid
                        // },
                //         type: "GET",
                //         dataType: "html",
                //         success: function (data) {
                //             $j("#popup-data").html(data);
                //         },
                //         error: function (xhr, status) {
                //             //alert("Sorry, there was a problem!");
                //         },
                //         complete: function (xhr, status) {
                //             $j("#rowclick").tablesorter();
                //             //custom_popup();
                //         }
                //     });
                // }

                // /*tabs script start*/
                // $('#myTab a').click(function (e) {
                //     e.preventDefault();

                //     $(this).tab('show');
                // });

                /*tabs script end*/


















                
    });  // end of ready function
</script>
