 $j('#showno').change(function(){
var x=$j('#showno').val();
     var count1 =  ($j("#tableData tbody").children("tr").filter(function() {
         return $j(this).css('display') !== 'none';
     }).length);
    //alert(count1);

     $j(".tblcount").html('Found '+count1+' record(s)');
if(x=='all')
{

$j("#tableData tr:gt(0)").show();

}
else
{

$j("#tableData tr:gt("+x+")").hide();
$j("#tableData tr:lt("+x+")").show();
}
 });

    $j("#search").click(function() {

     var labs=0;
       var quizs=0;

        $j('#act > option').each(function() {
          var vals=   $j(this).val();
           if($j(this).css( "display" )=='block'){
            var n = vals.search("quiz");
            if(vals.search("quiz")>=0){
                quizs=quizs+1;
            }else if(vals.search("vpl")>=0){
                labs=labs+1;
            }else{

            }}

        });

        $j(".info").text("Results of "+labs+" Lab And "+quizs+" Quiz");
    });
  $j=$.noConflict();
    $j("#tableData").on("click",".watchlist",function(e){

        var baseUrl=$j('#baseurl').val();
      var cid=$j('#courses').val();
       var uid=this.id;
        var url=baseUrl+'teacher/reports/showfilters.php';

    url=url+ "?cid="+cid+"&uid="+uid+"&change=" + 'addstudentwatch';
        Y.io(url, {
            method: "POST",
            on: {
                success: function (id, o) {
                    var response = Y.JSON.parse(o.responseText);

                    var form = Y.Node.create(response.html);
                    var res=response.html
                    var im="#"+uid;

                    if(res==0) {
                        alert('Removed from watchlist');
                        $j("#" + uid).attr("src", baseUrl + '/teacher/testcenter/images/' + '0.png');
                        $j("#" + uid).attr("title", "Add to Watchlist?");
                    }
                    else{
                    alert('Added to watchlist');
                        $j("#"+uid).attr("src",baseUrl+'/teacher/testcenter/images/'+'1.png');
                        $j("#" + uid).attr("title", "Remove from Watchlist?");
                    }




                }
            }
        });

    });


			$j(document).ready(function()
			{
//$j('input').iCheck();

                $j("#tableData").tablesorter(  );
				$j('#search1').keyup(function()
				{
					searchTable($j(this).val());
var count =  ($j("#tableData tbody").children("tr").filter(function() {
                        return $j(this).css('display') !== 'none';
                    }).length);


                    $j(".tblcount").html('Found '+count+' record(s)');
				});
			});
			function searchTable(inputVal)
			{
				var table = $j('#tblData');
				table.find('tr').each(function(index, row)
				{
					var allCells = $j(row).find('td');
					if(allCells.length > 0)
					{
						var found = false;
						allCells.each(function(index, td)
						{
							var regExp = new RegExp(inputVal, 'i');
							if(regExp.test($j(td).text()))
							{
								found = true;
								return false;
							}
						});
						if(found == true)$j(row).show();else $j(row).hide();
					}
				});
			}
$j(function() {
		var baseUrl=$j('#baseurl').val();
            var url=baseUrl+'teacher/dashboard.php';
            //$j('#page-navbar').append("<div style='padding:6px;'> <a id='dlink'>Dashboard</a> <span>/</span> <b> Reports</b></div>");
            $j('#dlink').attr("href",url);


        $j("#courses").on('change',function(){
            alert("course changed");$j("#scour").text($j('option:selected', $j(this)).text());
		
        });
        $j("#topics").change(function(){
            $j("#stopic").text($j('option:selected', $j(this)).text());
        });
        $j("#act").change(function(){
            $j("#sact").text($j('option:selected', $j(this)).text());
        });
        $j("#grade").change(function(){
            $j("#ssub").text($j('option:selected', $j(this)).text());
        });
        $j("#dept").change(function(){
            $j("#sdept").text($j('option:selected', $j(this)).text());
        });
        $j("#section").change(function(){
            $j("#ssec").text($j('option:selected', $j(this)).text());
        });
        $j("#gcategory").change(function(){
            $j("#atype").text($j('option:selected', $j(this)).text());
        });

	$j("#clear").click(function(){

		$j("#courses").val('');
        $j("#topics").addClass('disabled');
        $j("#act").val('');
        $j("#grade").val('');
        $j("#dept").val('');
        $j("#gcategory").val('');
        $j("#section").val('');
	
    //$j("#topics").attr('disabled','disabled');
    $j("#act").attr('disabled','disabled');
    $j("#grade").attr('disabled','disabled');
    $j("#dept").attr('disabled','disabled');
    $j("#gcategory").attr('disabled','disabled');
    $j("#section").attr('disabled','disabled');
    $j("#watchlist").attr('disabled','disabled');

    $j(".rank").attr('disabled','disabled');

    $j("#activitytype").val('');
        $j("#atype").text('--');
        $j("#ssec").text('--');
        $j("#ssub").text('--');
        $j("#sact").text('--');
        $j("#atype").text('--');
        $j("#stopic").text('--');
        $j("#scour").text('--');
        $j("#sdept").text('--');
        $j("#tblData").html('');
        $j("#seam").html('');
    $j("input:checkbox").attr('checked', false);
    $j('#filt .info').val('');

    $j("#watchlist").val($j("#watchlist option:first").val());
        });


        $j('input:radio[name="watchlist"]').change(function(){
            val=$j("input[name='watchlist']:checked").val()
              if(val==0){
                  $j("#swat").text('Not Watch listed');
              }
            else  if(val==1){
                  $j("#swat").text('Watch listed');
              }
            else{
                  $j("#swat").text('Both');
              }

        });


        $j(".rank").change(function() {
            if(this.checked)
            {

                $j('#seam').append($j(this).val()+' ');

            }
            else{
                $j('#seam').text('');
            }

        });

        /*var $jtable = $j('table').tablesorter({
            theme: 'blue',
            widgets: ["zebra", "filter"],
            widgetOptions : {
                filter_columnFilters: false,
                filter_saveFilters : true,
                filter_reset: '.reset'
            }
        });
        $j.tablesorter.filter.bindSearch( $jtable, $j('.search') );


        $j('select').change(function(){

            $j('#cours tbody tr').hide(); // hiding all trs
            $j("#cou").val($j(this).val());
            var x='.'+$j(this).val();
            $j(x).fadeIn('fast');
            if($j(this).val()=='all')
                $j('#cours tbody tr').show(); // hiding all trs

            $j('.selectable').attr( 'data-column', $j(this).val() );
            $j.tablesorter.filter.bindSearch( $jtable, $j('.search'), true );
        });*/


            $j("#selecctall").change(function(){
                $j(".checkbox1").prop('checked', $j(this).prop("checked"));
            });







    });





