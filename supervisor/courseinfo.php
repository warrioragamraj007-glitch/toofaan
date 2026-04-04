
<?php
require_once(dirname(__FILE__) . '/../config.php');
$PAGE->set_url('/supervisor/dashboard.php');
require_once("myreports_ajax.php");
require_login();
$PAGE->requires->js('/student/jquery-latest.min.js', true);
require_once($CFG->dirroot . '/my/lib.php');
$context = context_user::instance($USER->id);
$PAGE->set_context($context);
require_once($CFG->dirroot . '/course/lib.php');
$PAGE->set_title('Tessellator 4.0 - Supervisor Course Info');
echo $OUTPUT->header();
?>
<style>
    .courseinfo-header {
        padding-top: 15px;
    }
    .table-responsive {
        min-height: 0.01%;
        overflow-x: hidden;
    }
    #myTable{
        border: 1px solid #e4e1da;
    }
    #myTable thead tr th{
        background-color: #ea6645;
        background-image: linear-gradient(to bottom, #ea6645, #ea6645);
        background-repeat: repeat-x;
        color: #fff !important;
        font-weight: bold !important;
        padding: 3px !important;
        text-align: center;
        border: 1px solid #e4e1da;
        cursor: pointer;
    }
    #myTable tbody tr td {
        border: 1px solid #e4e1da;
        vertical-align: middle;
        text-align: center;
    }
    .col-md-12.course-details {
        margin-top: 10px;
    }
    .assistant{
        cursor: pointer;
    }
    .dropdown-lable {
        float: left;
        font-size: 14px;
        padding: 5px;
        text-transform: uppercase;
        width: 25%;
    }
    #cateogry-dropdown {
        width: 65%;
    }
    .download{
        background-color: #574743;
        color: white;
        cursor: pointer;
        float: right;
        padding: 5px;
    }
    .student{
        cursor: pointer;
    }
    #loading {
        float:right;display:none;
    }
</style>
<div class='container'>
<h2 class="courseinfo-header">Course Info</h2>
<div class="courseinfo-page-content">

<?php
//var_dump(getCoursesByEachCategory(7));
?>
    <div class="col-md-4"></div>
    <div class="col-md-4">
        <span id="loading">
           <i class="fa fa-refresh fa-spin fa-2x fa-fw" style=" color: #ea6645 "></i>
        </span>
    </div>
    <div class="col-md-4" >
        <div class="dropdown-lable">Courses</div>
        <select id="cateogry-dropdown">
            <option value="0">Select</option>
            <?php
            $courses=getCategories();
            for($i=1;$i<count($courses);$i++):
                ?>
                <option value="<?php echo $courses[$i]['catid'] ?>"><?php echo $courses[$i]['catname'] ?></option>
            <?php endfor; ?>
        </select>
        <span class="download" id="sxls">XLS</span>
    </div>


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
</div>





</div>
<?php
echo $OUTPUT->footer();
?>
<script src="<?php echo $CFG->wwwroot; ?>/teacher/jquery.table2excel.js"></script>
<script src="<?php echo $CFG->wwwroot; ?>/teacher/testcenter/js/bootstrap.min.js"></script>
<script>
    var $j= jQuery.noConflict();
    var baseUrl='<?php echo $CFG->baseUrl ?>';



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



    getCourses('<?php echo $courses[1]['catid'] ?>');


    $j("#cateogry-dropdown").val('<?php echo $courses[1]['catid'] ?>');




    $j("#cateogry-dropdown").on("change",function(){
        getCourses($j("#cateogry-dropdown").val());
    });



    var $rows = $j('.course-list-table tbody tr');

    $j('.search').keyup(function() {
        var val = $j.trim($j(this).val()).replace(/ +/g, ' ').toLowerCase();

        $rows.show().filter(function() {
            var text = $j(this).text().replace(/\s+/g, ' ').toLowerCase();
            return !~text.indexOf(val);
        }).hide();
    });


    function getCourses(catid){
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


</script>