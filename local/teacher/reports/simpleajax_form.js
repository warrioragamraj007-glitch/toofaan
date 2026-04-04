var $jj=jQuery.noConflict();

//alert(jQuery.fn.jquery);
//var $select = $jj(".selectcourse").selectize({
    //options here
//});
//$jj("select").selectize;
//var selectizeControl = $select[0].selectize
//$jj(".selectcourse").on('change', function() {
//	alert("ap");
//});

M.simpleajax_form = {

    // params from PHP
    Y : null,
    root : null,

    init: function(Y) {
        this.Y  =   Y;
        this.root = M.cfg.wwwroot;

        M.simpleajax_form.prepare_clickme_for_ajax();
    },
    prepare_clickme_for_ajax: function() {
        var Y = this.Y;

 $jj('.search').on('click', function() {
    
    var courseid= Y.one('#courses').get('value');
  
    if(courseid==''){
alert("Please select course");
    }
    else
    {
//alert("atleast");
var act_values="";
   var act_selected_value=$jj( "select.act option:selected").val();

if(act_selected_value=='all'||act_selected_value=='0'||act_selected_value=="")//user not selected any activity
{

    $jj("#act option").each(function()
    {
        var act_option=$jj(this).val();
        if(act_option=='all'|| act_option=='0'|| act_option=="")
        {

        }else {
           
                act_values=act_values+"@"+act_option;
        }
    });
}
        else{
    act_values=act_selected_value;
}


   var url = $jj('#baseUrl').val()+'/teacher/reports/showreports.php';//this.getData('url');



var gt= Y.one('.grade').get('value');
//var watchlist=$jj('input[name=watchlist]:checked').val();
var watchlist=$jj("select.watchlist option:selected").val();
var rank="";
 $jj('input[name=rank]:checked').each(function() {
   rank+=":"+$jj(this).val();
});
 var dept= Y.one('#dept').get('value');
//alert("ss");
var section= Y.one('#section').get('value');
url=url+"?cid="+courseid+"&values="+act_values+"&gt="+gt+"&dept="+dept+"&section="+section+"&wl="+watchlist+"&rank="+rank;


        Y.io(url, {
                method: "POST",
                on: {
                    success: function(id, r) {
				//alert("success");
                       var response = Y.JSON.parse(r.responseText);

                       var form = Y.Node.create(response.html);

                        var formarea = Y.one('.tb');

                        formarea.setHTML("");
                        formarea.setHTML(form);
			

var rowCount = $jj('#tableData tr').length;
rowCount=rowCount-1;
//$jj(".course-list-table").tablesorter();

          $jj(".tblcount").text("Found " + rowCount + " Record(s)");

  $jj('.rail').show();



                    },
                    complete: function(){

        
			/*$jj('.tb').show();
                        $jj("#tableData").trigger("update");
                                   
                        var sorting = $jj("#tableData")[1].config.sortList;
                        setTimeout(function () {
                            $jj("#tableData").trigger("sorton", [sorting]);
                        }, 100);*/
			 //$jj(".course-list-table").tablesorter();alert("ssa");
    			





                    }

                }
            });


}
            });




 /**************** START OF SEND MAIL ***************

        Y.one('.rail').on('click', function() {
    $jj('#loading').show();
 courseid= Y.one('#courses').get('value');
            if(courseid==''){
                alert("select Course");
            }
            else
            {
                var url = this.getData('url');
                var topicid= Y.one('#topics').get('value');
                var act_values="";
                var act_selected_value=$jj("select.act option:selected").val();

                if(act_selected_value=='all'||act_selected_value=='0'||act_selected_value=="")//user not selected any activity
                {

                    $jj("#act option").each(function()
                    {
                        var act_option=$jj(this).val();
                        if(act_option=='all'|| act_option=='0'|| act_option=="")
                        {

                        }else {
                            if(act_values=="")
                                act_values=act_option+"@";
                            else
                                act_values=act_values+"@"+act_option;
                        }
                    });
                }
                else{
                    act_values=act_selected_value;
                }

                var categoryid = Y.one('#gcategory').get('value');

                var gt= Y.one('.grade').get('value');
                //var watchlist=$jj('input[name=watchlist]:checked').val();
                var watchlist=$jj("select.watchlist option:selected").val();
                var rank="";
                $jj('input[name=rank]:checked').each(function() {
                    rank+=":"+$jj(this).val();
                });
                var dept= Y.one('#dept').get('value');

                var section= Y.one('#section').get('value');
                url=url+"?cid="+courseid+"&sid="+topicid+"&gcid="+categoryid+"&values="+act_values+"&gt="+gt+"&dept="+dept+"&section="+section+"&wl="+watchlist+"&rank="+rank;




                Y.io(url, {
                    method: "POST",
                    on: {
                        success: function(id, r) {

                            var response = Y.JSON.parse(r.responseText);

                            var form = Y.Node.create(response.html);

                            var formarea = Y.one('.sendmailres');

                            $jj('.sendmailres').show();
                            formarea.setHTML(form);
                            $jj('#loading').hide();
                        },

                    }
                });


            }
        });
        /************** END OF SEND MAIL ********/
/*************************EXPORTS ***********************/
Y.one('.export').on('click', function() {


var courseid= Y.one('#courses').get('value');
 if(courseid==''){
     alert("Please select course13");
    }
    else
    {
 var url = this.getData('url');
    var   exportvalue=    Y.one('#exportvalue').get('value');

       if(  exportvalue=='pdf')
       {
           url=url+"/grade_export.php";
       }
        else if(exportvalue=='csv')
        {
            url=url+"/csv_export.php";
        }
    else {

    }
        var topicid= Y.one('#topics').get('value');
        var act_values="";
        var act_selected_value=$jj("select.act option:selected").val();

        if(act_selected_value=='all'||act_selected_value=='0'||act_selected_value=="")//user not selected any activity
        {

            $jj("#act option").each(function()
            {
                var act_option=$jj(this).val();
                if(act_option=='all'|| act_option=='0'|| act_option=="")
                {

                }else {
                    if(act_values=="")
                        act_values=act_option+"@";
                    else
                        act_values=act_values+"@"+act_option;
                }
            });
        }
        else{
            act_values=act_selected_value;
        }

        var categoryid = Y.one('#gcategory').get('value');

        var gt= Y.one('.grade').get('value');
       // var watchlist=$jj('input[name=watchlist]:checked').val();
        var watchlist=$jj("select.watchlist option:selected").val();
        var rank="";
        $jj('input[name=rank]:checked').each(function() {
            rank+=":"+$jj(this).val();
        });
        var dept= Y.one('#dept').get('value');

        var section= Y.one('#section').get('value');
        url=url+"?cid="+courseid+"&sid="+topicid+"&gcid="+categoryid+"&values="+act_values+"&gt="+gt+"&dept="+dept+"&section="+section+"&wl="+watchlist+"&rank="+rank;


var redirectWindow = window.open(url, '_blank');
    redirectWindow.location;

}
       });

/***********************For GRADE TYPE *********************/
Y.one('.act').on('change', function() {


            var activity= Y.one('#act').get('value');
var act_values=activity.split("-");

     if(act_values[1]=='vpl')
     {

     $jj('.sub').show();
     $jj('.nsub').show();
 }
 else{

     $jj('.sub').hide();
     $jj('.nsub').hide();
 }

        });


/*********************END OF EXPORTS ********************/
        
$jj('.selectcourse').on('change', function() {
        	if(courseid==''){
                alert("Please select course12");
            }
            else {
                $jj("#gcategory").removeAttr('disabled');
                $jj("#topics").removeAttr('disabled');
                $jj("#act").removeAttr('disabled');
                $jj("#grade").removeAttr('disabled');
                $jj("#dept").removeAttr('disabled');
                $jj("#section").removeAttr('disabled');
                $jj("#watchlist").removeAttr('disabled');
                $jj(".rank").removeAttr('disabled');
		$jj("#scour").text($jj('.selectcourse .item').text());
		
            }
            var url = $jj('#baseUrl').val()+'/teacher/reports/showfilters.php';
            var courseid= Y.one('#courses').get('value');
	    var change='course';
            url=url+"?cid="+courseid+"&change="+change;
            Y.io(url, {
                method: "POST",
                on: {
                    success: function(id, o) {
                        var response = Y.JSON.parse(o.responseText);


                        var res=response.html;

                        var values= res.split("#");
                        var  gcategory = Y.one('.gcategory');
                        var topic = Y.one('.topic');
                        var act = Y.one('.act');
			$jj('.catin').html('');
			$jj('.catin').html(values[0]);
                        $jj('.catin select').selectize();
                        //alert("finally");
			//gcategory.setHTML(values[0]);
			$jj('.catopic').html('');
			$jj('.catopic').html(values[1]);
                        $jj('.catopic select').selectize();
                      // topic.setHTML(values[1]);
			$jj('#acdiv').html('');
			$jj('#acdiv').html(values[2]);
                        $jj('#acdiv select').selectize();
                     
                        //act.setHTML(values[2]);
			//alert("dskjh");
			//$jj('#topics').val("all");
                        //$jj('#gcategory').val("all");
                        //$jj('#act').val("all");
                        //$jj('#topics option:first-child').attr("selected", "selected");
                       // $jj('#gcategory option:first-child').attr("selected", "selected");
                        //$jj('#act option:first-child').attr("selected", "selected");
                    },
		   complete: function(id, o) {
                        //alert("dskjh");
			//$jj('.gcategory').selectize();	//alert("dskjh1");
                        //$jj('#topics').val("all");
                        //$jj('#gcategory').val("all");
                        //$jj('#act').val("all");
                        $jj('#topics option:first-child').attr("selected", "selected");
                       // $jj('#gcategory option:first-child').attr("selected", "selected");
                        $jj('#act option:first-child').attr("selected", "selected");
                    }
			
                }
            });
        });

$jj('.catin .gcategory').on('change', function() {
		alert("changed");
		$jj("#atype").text($jj('.topic .item').text());
	
            var url = this.getData('url');
            var courseid = Y.one('#courses').get('value');
            var categoryid = Y.one('#gcategory').get('value');
            var sectionid = Y.one('#topics').get('value');
            if (sectionid != "" || sectionid != "0" || sectionid != 'all'){

                var change = 'gt';
                url = url + "?cid=" + courseid + "&sid=" + sectionid +"&gid="+categoryid+ "&change=" + change;
                Y.io(url, {
                    method: "POST",
                    on: {
                        success: function (id, o) {
                            var response = Y.JSON.parse(o.responseText);

                            var form = Y.Node.create(response.html);

                            var formarea = Y.one('.act');
                            //alert(formarea);
                            formarea.setHTML(form);


                            $jj('#act').val("both");

                            $jj('#grade').val("");
                        }
                    }
                });
            }
        });


$jj('.topic').on('change', function() {
		
    var url = this.getData('url');
    var courseid = Y.one('#courses').get('value');
    var categoryid = Y.one('#gcategory').get('value');
    var sectionid = Y.one('#topics').get('value');
    if (sectionid != "" || sectionid != "0" || sectionid != 'all'){

        var change = 'topic';
    url = url + "?cid=" + courseid + "&sid=" + sectionid +"&gid="+categoryid+ "&change=" + change;
    Y.io(url, {
        method: "POST",
        on: {
            success: function (id, o) {
                var response = Y.JSON.parse(o.responseText);

                var form = Y.Node.create(response.html);

                var formarea = Y.one('.act');
                //alert(formarea);
                formarea.setHTML(form);


                $jj('#act').val("both");

                $jj('#grade').val("");
            }
        }
    });
}
        });

        
        /***********************For GRADE TYPE *******************
        Y.one('.activitytype').on('change', function() {
            var acttype= Y.one('#activitytype').get('value');
if(acttype=='quiz')
{
    $jj(".vpl").hide();
    $jj(".quiz").show();
    $jj('#act').val("all");
}

            if(acttype=='vpl'){
                $jj(".quiz").hide();
                $jj(".vpl").show();
                $jj('#act').val("all");
            }


            if(acttype=='both') {
                $jj(".vpl").show();
                $jj(".quiz").show();
                $jj('#act').val("all");
            }
        });
        */
        Y.one('.dept').on('change', function() {
            var dept= Y.one('#dept').get('value');




            $jj("#section option").each(function()
            {

                var s=$jj(this).val();
                if(s==""){

                }
                else {
                    if (dept == "it") {
                        $jj(this).val("");
                        $jj("#section").val("");

                        $jj(".dept-val").css("display", "none");
                    }
                    else {
                        var ds = dept + "-" + s;
                        $jj(".dept-val").css("display", "block");
                        $jj(this).val("");
                        $jj(this).val(ds);
                    }
                }
            });




        });

    },


}

