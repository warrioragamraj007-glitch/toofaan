var $j= jQuery.noConflict();
$j(document).ready(function() {

             $j('input').iCheck();
       //alert("after input");
             
             //$('input').iCheck();

               /*code modified by mahesh -- start*/
                var rooturl='<?php echo $CFG->wwwroot; ?>';
                //set default coursename to dropdown when page loads
                var cid='<?php echo $courid; ?>';
                $j("#selectlist option").filter(function() {
                    return this.value == cid;
                }).prop('selected', true);
		 
                //goto testcenter button functionality

                $j('#gtt').click(function() {
//alert("hel");
                    //$idString=$("#cform .checked .rdo").val();
			var idString=$j('input[name=topics]:checked').val();//$j(".checked .rdo").val();
                    	 var baseUrl=$j('#baseurl').val();
			//alert(idString);
                    $ids=idString.split("-");//alert($ids);
                    var testcenterUrl=baseUrl+'/local/teacher/testcenter/index.php?cid='+$ids[0]+'&secid='+$ids[1];
                    //alert();
                    location.href =testcenterUrl;
                });
                /*code modified by mahesh -- end*/


             var   op = $j('#cstatus').val();
             arr = op.split(",");
                if(arr[1]!=0 && arr[0]!=''){
                val = parseFloat(arr[0]) / parseFloat(arr[1]);
                $j('#cousta').val(val);
                $j('#cousta').attr('title',Math.ceil(val*100)/100*100+'%');
                    $j("#gtt").removeAttr("disabled");
                }
                else{
                    $j('#cousta').val(0);
                $j('#cousta').attr('title','0%');
                    $j('#gtt').attr('disabled',true);
                }
                var baseUrl=$j('#baseurl').val();
                    var url=baseUrl+'/local/teacher/dashboard.php';
      //     $j('#page-navbar').append("<div style='padding:6px;'> <a id='dlink'>Dashboard</a> <span>/</span> <b> Courses</b></div>");
                    $j('#dlink').attr("href",url);
	$j("#wsearch").keyup(function() {
	//alert("help");
    var searchTerm = $j("#wsearch").val();
	//alert(searchTerm);
//$j(".cmptd").text(searchTerm);
    var listItem = $j('table tbody#cbody').children('tr');
	//alert(listItem);
	
    var searchSplit = searchTerm.replace(/ /g, "'):containsi('")

    $j.extend($j.expr[':'], {
      'containsi': function(elem, i, match, array) {
        return (elem.textContent || elem.innerText || '').toLowerCase().indexOf((match[3] || "").toLowerCase()) >= 0;
      }
    });

    $j("table tbody tr").not(":containsi('" + searchSplit + "')").each(function(e) {
      $j(this).attr('visible', 'false');
    });

    $j("table tbody tr:containsi('" + searchSplit + "')").each(function(e) {
      $j(this).attr('visible', 'true');
    });

    var jobCount = $j('table tbody tr[visible="true"]').length;
    $j('.tabres').text('('+jobCount + ') Results found');

    if (jobCount == '0') {
      $j('.no-result').show();
    } else {
      $j('.no-result').hide();
    }
  });

            });

            $j('form').bind("keypress", function(e) {
                if (e.keyCode == 13) {
                    e.preventDefault();
                    return false;
                }
            });


