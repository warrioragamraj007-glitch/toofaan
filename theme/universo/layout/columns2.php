
<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * A two column layout for the Bootstrapbase theme.
 *
 * @package   theme_bootstrapbase
 * @copyright 2012 Bas Brands, www.basbrands.nl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Set default (LTR) layout mark-up for a two column page (side-pre-only).
$regionmain = 'span9 pull-right';
$sidepre = 'span3 desktop-first-column';
// Reset layout mark-up for RTL languages.
if (right_to_left()) {
    $regionmain = 'span9';
    $sidepre = 'span3 pull-right';
}


$userid=$USER->id;
global $DB;
//echo $DB->get_field('userinfo_tsl', 'id', array('userid' => $userid));
if(user_has_role_assignment($USER->id, 5)){

//if the user is student then
//login status storing in a tables
    //$section=get_complete_user_data(id,$userid)->profile['section'];

    $sectionfield=$DB->get_field('user_info_field', 'id', array('shortname'=>'section'));
    $sql="SELECT `data` FROM `mdl_user_info_data` WHERE `userid` ='".$userid."' AND `fieldid` ='".$sectionfield."'";
    $fielddata=$DB->get_record_sql($sql);
    $studata=$fielddata->data;

    $section=$studata;//get_complete_user_data(id,$userid)->profile['section'];

    $user_status = new stdClass();
    $user_status->loginstatus=2;
    $user_status->userid=$userid;
    $user_status->studentsection=$section;

    try {
        if($DB->get_field('userinfo_tsl', 'id', array('userid' => $userid))){
            $user_status->id=$DB->get_field('userinfo_tsl', 'id', array('userid' => $userid));
            $DB->update_record_raw('userinfo_tsl', $user_status, false);
        }
        else{
            $DB->insert_record_raw('userinfo_tsl', $user_status, false);

        }
        //echo 'executed';

    } catch (dml_write_exception $e) {
        // During a race condition we can fail to find the data, then it appears.
        // If we still can't find it, rethrow the exception.

        throw $e;

    }


}


echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <title><?php echo $OUTPUT->page_title(); ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->favicon(); ?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
    <!--<link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet' type='text/css'>-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body <?php echo $OUTPUT->body_attributes('two-column'); ?>>

<?php echo $OUTPUT->standard_top_of_body_html() ?>

<header role="banner" class="navbar navbar-fixed-top moodle-has-zindex page-landing-page">
    <nav role="navigation" class="navbar-inner  navigation-wrapper">
        <div class="container-fluid navigation-wrapper">
            <a class="brand" style="background-color: #012951 !important;" href="<?php echo $CFG->wwwroot;?>">
                <img src="<?php
                global $USER;$sesskey = $USER->sesskey;
                echo $CFG->wwwroot.'/theme/universo/pix/logo.png';?>" >
            </a>
            
            <nav style="width: auto; float: right;">
                <ul style=" margin: 0px;">
                    <?php if($USER->id) :?>
			<?php if(!user_has_role_assignment($USER->id, 5)): ?>
                    		<li class="lik"><a href="<?php echo $CFG->wwwroot?>">Dashboard</a></li>
                    	<?php endif;?>
                    <li class='lik' ><a href="<?php echo $CFG->wwwroot.'/login/logout.php?sesskey='.$sesskey.''?>">Logout (<?php echo fullname($USER) ?>)</a></li></ul>
                <?php endif;?>
            </nav>
            <div class="nav-collapse collapse">
                <?php echo $OUTPUT->custom_menu(); ?>
                <ul class="nav pull-right">
                    <li><?php echo $OUTPUT->page_heading_menu(); ?></li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<div id="page" class="wrapper">
    <?php echo $OUTPUT->full_header(); ?>
    <div id="page-content" class="container">
        <section id="region-main" style="padding-right: 4em;" class="<?php echo $regionmain; ?>">
            <?php
            echo $OUTPUT->course_content_header();
            echo $OUTPUT->main_content();
            echo $OUTPUT->course_content_footer();
            ?>
        </section>
        <?php echo $OUTPUT->blocks('side-pre', $sidepre); ?>
    </div>

   
<section id="footer-bottom">
        <div class="container">
            <div class="footer-inner">
                <div class="copyright">Copyright © <?php echo date("Y",time()) ?> Teleparadigm Networks Pvt. Ltd. All Rights Reserved.<span style="float:right">Version <?php echo $CFG->currentversion ?></span></div><!-- /.copyright -->
            </div><!-- /.footer-inner -->
        </div><!-- /.container -->
    </section>
    <?php echo $OUTPUT->standard_end_of_body_html() ?>
<?php include('websocketcode.php'); ?>
</div>

</body>
</html>




