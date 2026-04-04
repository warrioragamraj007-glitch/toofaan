


<style>


      #region-main{
        margin-top:15px !important;
    }
    .refresh-icons-half2{
        padding-top: 0px !important;
    }
    /* custom inclusion of right, left and below tabs */

    .tabs-below > .nav-tabs,
    .tabs-right > .nav-tabs,
    .tabs-left > .nav-tabs {
        border-bottom: 0;
    }
    .tab-content > .tab-pane,
    .pill-content > .pill-pane {
        display: none;
    }

    .tab-content > .active,
    .pill-content > .active {
        display: block;
        margin-left: 1px
    }
    .tabs-below > .nav-tabs {
        border-top: 1px solid #ddd;
    }

    .tabs-below > .nav-tabs > li {
        margin-top: -1px;
        margin-bottom: 0;
    }

    .tabs-below > .nav-tabs > li > a {
        -webkit-border-radius: 0 0 4px 4px;
        -moz-border-radius: 0 0 4px 4px;
        border-radius: 0 0 4px 4px;
    }
  
    .tabs-left > .nav-tabs > li,
    .tabs-right > .nav-tabs > li {
        float: none;
    }

    .tabs-left > .nav-tabs > li > a,
    .tabs-right > .nav-tabs > li > a {
        min-width: 74px;
        margin-right: 0;
        margin-bottom: 3px;
    }

     .tabs-left > .nav-tabs {
        float: left;
        margin-right: 19px;
        border-right: 1px solid #ddd;
        font-size:14px;
    }

    .tabs-left > .nav-tabs > li > a {
        margin-right: -1px;
        -webkit-border-radius: 4px 0 0 4px;
        -moz-border-radius: 4px 0 0 4px;
        border-radius: 4px 0 0 4px;
    }

    .tabs-right > .nav-tabs {
        float: right;
        margin-left: 19px;
        border: 1px solid #ddd;
    }
       .tabs-right > .nav-tabs > li > a {
        margin-left: -1px;
        -webkit-border-radius: 0 4px 4px 0;
        -moz-border-radius: 0 4px 4px 0;
        border-radius: 0 4px 4px 0;
    }

    .status-row{
        margin-bottom:15px
    }
    .tright{
        width:100%;

    }
    .tabbable,.tabs-left{

        padding:0px !important;
        box-shadow: 0 1px 3px rgba(253, 250, 243, 0.45) !important;
        margin-top: 5px;
        padding-left:0px;
        margin-left:0;
    }
    .fa-angle-double-right{
        margin-left: 20px;
    display: inline-block;
    /* align-items: center; */
   
    }
    #btngreenStars i.fa.fa-angle-double-right {
    margin-left: 3px; /* Adjust the value as needed */
}
    .testcenter-tabs{
        border:2px solid #E4E1DA;
        border-radius: 4px 0 0 4px;
    }
    
.current-activity{
        background-color: #012951;
        color: #fff;
        font-size: 16px;
        height: 40px;
        margin: 0 !important;
        border-bottom: 3px solid #e8e8ea;
        width:100%;
    }
    .act-label,.act-name{
        margin-left:5%; margin-top: 8px;
    }

    .status-row{
        background-color: #b2beca;
        padding: 5px;
        width: 100%;
        margin:0px;
    }
    .row.status-row {
        margin: 0;
        height:60px;
    }
    #refresh{
        float:right;
    }
    .refresh-icons {
        float: right;
        width: 50%;
    }
    #filterdiv {
        width: 100%;
        height: auto;
        z-index: 1;
        display:none;
    }
    .fa{
        cursor:pointer;
    }
    #filterdiv > span {
        display: block;
        padding: 5px;
        text-align: center;
        cursor:pointer;
    }
    .status-divs{
        float: left;
        font-size: 16px;
        margin-left: 2%;
        margin-top: 6px;
        text-align: center;
        vertical-align: middle;
        width: 12% !important;

    }
    .of-lable {
        font-size: 12px;
    }
    .status-numbers span {
        /*padding-top: 14px;*/
        vertical-align: middle;
    }
    .csubCount-indicator{
        background-color: #00b0f0;
    }
    .cgradeCount-indicator {
        background-color: #00b050;
    }
    .cstarCount-indicator{
        background-color: #0f0;
    }
    .crstarCount-indicator{
        background-color: #c30;
    }
    .indicator {

display: inline-block;
float: left;
height: 38px;
width: 25%;
padding: 10px;
}
.login-count-indicator{
background-color: #FF6B3C;
}
.status-numbers.logincount-status-numbers {
background-color: #ffe1d8;
}
.status-numbers.csubCount-status-numbers {
background-color: #cceffc;
}
.status-numbers.cgradeCount-status-numbers {
background-color: #ccefdc;
}
.status-numbers.cstarCount-status-numbers {
background-color: #ccffcc;
}
.status-numbers.crstarCount-status-numbers {
background-color: #f4d6cc;
}
.status-numbers {
background-color: #fff;
float: right;
width: 100%;
}
.status-div {
margin-left: 0%;
margin-right: 0%;
width: 100%;
}
#status-div {
margin-top: 15px;
width: 10% !important;
}
#rowclick th {
text-align: center;
}
    .status-label {
        color: #fff;
    }
    .star-img{
        color:#fff;
    }
    .statuscounts {
        padding-top: 8px;
    }
    .kmit-status,#switch-label{
        display: none;
    }
    #switch {
        float: right;
    }
    #reset-logins {
        margin-top: 0px !important;
    }
 #complete{
        display:none;
    }
#show{
width:45%;margin-left: 2%;
}

#hide{
width:45%;margin-left: 4%;
}

</style>


<style>
    .nav-tabs:nth-child(1) {
        display: block;
    }
    .tabs-left > .nav-tabs{
        border: 1px solid #ddd; padding: 5px;margin-right: 5px;
    }
    .tabs-left > .nav-tabs a{
        border:none;
    }
    .tabs-left > .nav-tabs .active > a, .tabs-left > .nav-tabs .active > a:hover, .tabs-left > .nav-tabs .active > a:focus {
        border:none;
        border-bottom: 1px solid #ddd;
        border-right: none;

    }

    /*added from myreports page of teacher*/

    .tabs-left > .nav-tabs{
        border: none; padding: 5px;margin-right: 5px;
    }
    .tab-content {
        box-sizing: border-box;
        border: 1px solid #ddd;
        top:5px;
        /* padding-left: 150px; */
        /* padding-right: 5px; */
        margin-left: 150px;
        padding-left:3px;
        /* height:1500px; */
         /* transition: none; */
        /* position: relative; */
    }
    .tabs-left > .nav-tabs a{
        border-color: #c3c3c3 !important;
        border-style: solid !important;
        border-width: 1px !important;
        background-color: #FFF;
    }
    .tabs-left > .nav-tabs > li > a {
        border-radius: 0;
        color: #01366a !important;
        padding-left: 30px;
    }


    .tabs-left > .nav-tabs .active > a, .tabs-left > .nav-tabs .active > a:hover, .tabs-left > .nav-tabs .active > a:focus {

        border-left: 4px solid rgb(234, 102, 69) !important;
        color: #ea6645 !important;

    }
    .tabs-left > .nav-tabs > li > a:hover{
        background-color: #FFF !important;
        color:#ea6645 !important;
    }
    .tabs-left > .nav-tabs > li > a > i,.tabs-left > .nav-tabs > li > span > i{
        padding-left: 5px;
    }
    .nav.nav-tabs.tabs-left li.active {
        border-left: 4px solid rgb(234, 102, 69) !important;
    }
    .nav.nav-tabs.tabs-left li.active a {
        color: #ea6645 !important;
    }
    .testcenter-tabs.nav-tabs > li > a {
    font-size: 15px; /* Adjust the font size to your preference */
    height:45px;
    width:140px;
}
.testcenter-tabs.nav-tabs > li > a {
    display: flex;
    align-items: center;
    /* justify-content: center; */
    text-align: center;
}

/* Center text within the nested list items (dropdown items) */
.testcenter-tabs.nav-tabs > li > ul > li > a {
    display: flex;
    align-items: center;
    /* justify-content: center; */
    /* text-align: center;  */
}


 /* .nav-tabs.course-detail-tabs {  
      margin-bottom:-5px;
    }  */

    .nav-tabs.course-detail-tabs li {
        list-style: none;
      padding: 10px;
      display: inline-block;
      margin-top: 10px; 
    }

    .nav-tabs.course-detail-tabs a {
    color: grey;
    } 
    .course-detail-tabs a.current {
    /*  styles for the active tab link */
    /* color:orange; */
    border: 2px solid #ddd;
    border-radius:0;
      border-width:3px; 
      padding:12px;
      color: #333;
}
.course-detail-tabs a:hover {
    border-radius:0;
    border: 2px solid #ddd;
    border-width: 1px; 
    padding :12px;
   
}
    /* end of styles for teacher tabs css */

  
</style>


<style>

select, textarea, input[type="text"], input[type="password"], input[type="datetime"],
input[type="datetime-local"], input[type="date"], input[type="month"], input[type="time"],
input[type="week"], input[type="number"], input[type="email"], input[type="url"],
input[type="search"], input[type="tel"], input[type="color"], .uneditable-input {
    display: inline-block;
    height: 30px !important;
    padding: 4px 6px;
    margin-bottom: 9px;
    font-size: 13px;
    line-height: 18px;
    color: #555;
    border-radius: 4px;
    vertical-align: middle;
}
.tleft{ float:left;width:49.5%;height:130px;}

.sta{border:1px solid gray;  margin-top:10px;float:left;width:100%;height:75px;}
.sub{
    width: 23%;
    border-right: 1px solid #808080;
    padding: 15px 10px;
    font-size: 16px;
    float: left;
    height: 45px;

}
.subp{margin-top:5px;font-size: 16px;padding-top: 0px;}
.subp2{margin-top:2px;font-size: 14px;}
.report{
    border: 1px solid #808080;
    margin-top: 3px;
    padding: 0px;
    float: right;
    width: 100%;}
.con{width:100%;}

.container-demo{
    width: 1270px;
    height:600px;
    margin-right:0;
    margin-left:0px;
    padding-left:0;
    padding-right:0;
}
.container-demo {
max-width: 100vw!important;
}

.repo{margin-top:10px;width:100%;
    /* height:350px; */
    float:left;padding-top:10px;}
#panel, #flip {
    padding: 5px;
    text-align: center
}
p, fieldset, table, pre {
    margin-bottom: 0em !important;
}

meter {
    font-size: 6px;
    margin-top: 5px;
    width: 85%;
}
#flip{
    color: #FFF;
    text-shadow: 0px -1px 0px rgba(0, 0, 0, 0.25);
    background-color: #048EC7;
    background-image: linear-gradient(to bottom, #049CDB, #0378A9);
    background-repeat: repeat-x;
    border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
    text-align: center;
    border-width: 0px 0px 1px 1px;
    font-size: 15px;
    font-family: Arial;
    font-weight: bold;
    color: #FFF;
    cursor: pointer;
}

#fl{
    text-align:right;padding-right:20px;margin-top:5px
}
.fa-angle-double-up,.fa-angle-double-down{
    font-size:18px;
}
#ccourse{
    float:left;margin-right:180px;margin-left:40px;
}
#ctopic{
    float:left;margin-right:180px;
}
#cactivity{
    float:left;margin-right:180px;
}
#panel {
    display: block;
}

#t01 th, td {
    padding: 5px;
    text-align: left;
    border: 1px solid black;
font-size:12px;
}
table#t01 {
    width: 100%;
    border: 0px solid gray;
    border-collapse: collapse;
}



#progressBar {
    width: 400px;
    height: 22px;
    border: 1px solid #111;
    background-color: #292929;
}
#progressBar div {
    height: 100%;
    color: #fff;
    text-align: right;
    line-height: 22px; /* same as #progressBar height if we want text middle aligned */
    width: 0;
    background-color: #0099ff;
}
/* .btn {
    background: #3498db;
    background-image: -webkit-linear-gradient(top, #3498db, #2980b9);
    background-image: -moz-linear-gradient(top, #3498db, #2980b9);
    background-image: -ms-linear-gradient(top, #3498db, #2980b9);
    background-image: -o-linear-gradient(top, #3498db, #2980b9);
    background-image: linear-gradient(to bottom, #3498db, #2980b9);
    font-family: Arial;
    color: #ffffff;
    font-size: 15px;
    padding: 5px 18px 6px 18px;
    text-decoration: none;
} */

/* .btn:hover {
    background: #3cb0fd;
    background-image: -webkit-linear-gradient(top, #3cb0fd, #3498db);
    background-image: -moz-linear-gradient(top, #3cb0fd, #3498db);
    background-image: -ms-linear-gradient(top, #3cb0fd, #3498db);
    background-image: -o-linear-gradient(top, #3cb0fd, #3498db);
    background-image: linear-gradient(to bottom, #3cb0fd, #3498db);
    text-decoration: none;
} */
.CSSTableGenerator { overflow: auto; }
.CSSTableGenerator tbody { height: auto; }


tbody tr td:first-child{
    text-align: center;
}
tbody tr td:last-child{
    text-align: center;
}
tbody tr th:first-child{
    text-align: center !important;
}
tbody tr th:last-child{
    text-align: center !important;
}

.navbar {
    margin-bottom: 0px;
}
thead{
    cursor: pointer;
}
td{height: 30px;}
#t01 img{
    padding:5px;
}
.sta {
    border: 1px solid #808080;
    margin-top: 0px;
    float: left;
    width: 100%;
    height: 45px;
}
.sub {
    width: 23%;
    border-right: 1px solid #808080;
    padding: 2px 10px;
    font-size: 16px;
    float: left;
    height: 42px;
}
.repo {
    margin-top: 0px;
    width: 100%;
    /* height: 350px; */
    float: left;
    padding-top: 5px;
    padding-left:5px;
    padding-right:5px;
}
.report {
    border: 1px solid #808080;
    margin-top: 3px;
    padding: 0px;
    float: right;
    width: 100%;
    height: 750px;
    margin-bottom: 30px;
}
#fl{
    height:20px;
}
.showhide{
    border: none;
    background: none;
    cursor: pointer;
}
.show{
    cursor: pointer !important;
}
button[disabled], html input[disabled] {
    cursor: not-allowed !important;
}
.pagecover-onload{
    display: none; position: absolute; width: 95%; background-color: rgb(255, 255, 255); z-index: 300; opacity: 0.9; height: 800px; top: 60px;margin-left: -10px;
}
.pagecover{
    display: none;
}
.watchlist {
    cursor: pointer;
}

#current-activity{
    font-weight:bold;
}

/*#current-activity {
  animation: blinker 2.7s cubic-bezier(.5, 0, 1, 1) infinite alternate;
}
@keyframes blinker {
  from { opacity: 1; }
  to { opacity: 0; }
}*/


/* Test Center Css */
table tbody tr {
    border-bottom: 1px grey ;
}
.sta {
    margin:5px;
    border:0px;
}
table tbody tr td {
    border: 1px solid  #E4E1DA ;
}
.sub {
    border: 1px solid  #E4E1DA ;
}
#show {
    background: transparent;
    background-image: none;
    background-repeat: repeat-x;
}
table{width:100%;}

#myTable thead tr th{
    padding:8px !important;
    font-size:15px;
}
table thead tr th{
    padding:8px !important;
    font-size:15px;
}
#myTable tbody tr td{
    border:0px;
}
table thead tr th, table thead tr td {
    font-size: 13px !important;
    font-style: normal;
    text-align: left;
}
.container, .navbar-static-top .container, .navbar-fixed-top .container, .navbar-fixed-bottom .container {
    width: 100% !important;
}
.tleft {
    float: left;
    height:auto;
    width: 30%;
}
.tright {
padding-left:5px;
padding-right:5px;

    height: auto;

}
.report {
    border: 0px solid #808080;
    margin-top: 3px;
    padding: 0px;
    float: right;
    width: 100%;
    height: 695px;
    margin-bottom: 30px;
}
#stu-section{
    width: 100px !important;
    margin-bottom: 0px !important;
}

</style>
<style>
.sub{
    width:10% !important;
}

.tright table tr td:nth-child(2){
    font-weight: bold;
}
.tleft tr td:first-child{
    width: 20%;
    font-weight: bold;

}
.tright table tr:hover td{
    background-color: transparent !important;
}
.tleft table tr:hover td{
    background-color: transparent !important;
}
.tleft tr td{
    padding: 5px;
    line-height: 12px;
    vertical-align: top;
    font-size: 12px !important;
}
.coursetab tbody tr:first-child td{
    background-color: rgb(228, 228, 228) !important;
}
.tleft tbody tr td:last-child {
    text-align: left;
}
#show{
    background-color: rgb(70, 165, 70);
    color: #FFF;
    font-size: 12px;
    padding: 6px 22px;
    display: inline-block;
    float: left;
}
#hide{
    color: #FFF;
    display: inline-block;
    font-size: 12px;
    padding: 6px 22px;
    float: left;
    background-color: rgb(157, 38, 29);
    margin-left: 8%;
}
#complete{
    background-color:red;
    color:#FFF;
}
.stopclose{
    background-color: red;color: rgb(255, 255, 255); display: inline-block; font-size: 12px; padding: 2px 20px; float: left; margin-left: 8%;
}
.stopclose:hover{background-color: red;}
.showhide:hover{
    color:#fff;
}
#titile-sta{
    text-align: center ! important; float: left;width: 95%;
}
input[type:"button"]:hover, input[type:"button"]:focus{
                                                    -moz-border-bottom-colors: none;
                                                    -moz-border-left-colors: none;
                                                    -moz-border-right-colors: none;
                                                    -moz-border-top-colors: none;
                                                    background-color: #F5F5F5;
                                                    background-image: linear-gradient(to bottom, #FFF, #E6E6E6);
                                                    background-repeat: repeat-x;
                                                    border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) #B3B3B3;
                                                    border-image: none;

                                                }
.tleft{
    width:29% !important;
}
#stas td{
    text-align: center !important;
    font-weight: bold;
}

#stas td{
    line-height: 16px;
    font-size: 12px !important;
    font-style: normal;
    border: 0px solid #DDD;
    padding: 0px;
    color: #655C5C;
    text-align: center;
    color:#655C5C;
}
#stas tr:hover td{
    background-color: transparent !important;
}
#panel{
    height:140px;
}
#stas{
    /*margin:5px 5px 5px 5px;*/
    width:99%;
}
#coursename{
    width: 100%; height: 75px;
    border: 0px solid rgb(243, 240, 240);
    background-color: rgb(118, 139, 149);
    box-shadow: 0px 0px 1px rgba(0, 0, 0, 0.6);
    font-size: 24px; font-weight: bold;
    color: white; vertical-align: middle; line-height: 75px;
}
#topicname{
    margin-top: 5px;
    width: 100%;
    height: 35px; border: 0px solid rgb(243, 240, 240);
    background-color: rgb(118, 139, 149);
    box-shadow: 0px 0px 1px rgba(0, 0, 0, 0.6);
    font-size: 16px; font-weight: bold;
    color: white; vertical-align: middle;
    padding-top: 4px;
}
#fl{
    height: 16px !important;
    line-height: 16px !important;
    vertical-align: middle !important;
    margin-top:0px !important;
}
#myTable{
    padding: 2px !important;

    line-height: 18px !important;
    font-size: 12px !important;
    font-style: normal !important;
    vertical-align: middle !important;

}
#myTable thead tr th,#t01 thead tr th,.teacheragents-table thead tr th {
    padding: 3px !important;
    background-color: #ea6645;
    background-image: linear-gradient(to bottom, #ea6645, #ea6645);
    color:#FFF !important;
    background-repeat: repeat-x;

    font-weight: bold !important;
}
#myTable thead tr th {
    padding: 3px !important;
    font-size: 15px;
}
#myTable tbody tr td,.teacheragents-table tbody tr td {

    padding: 2px;
    border: 1px solid #e4e1da;
    vertical-align: middle;
}
#head td {
    color: #000 !important;
    background-color: #D3E0EA !important;
}
#stas tr:hover td{
    background-color:inherit !important;
}
#stas td {
    line-height: 18px;
    font-size: 12px !important;
    font-style: normal;
    border: 0px solid #DDD;
    padding: 0px;
    color: #655C5C;
    vertical-align: middle !important;
    background-color: #F8F2E5;
}
#fil{
    color: #000 !important;
    background-color: white !important;
}
#ref{
    vertical-align: middle;
    width: 21%;
    color: #787676 !important;
    background-color: #FFF !important;
}
.status-text{
    display: block; font-size: 9px ! important; text-align: center; font-weight: normal;line-height: 12px;
}

button.disabled,
input.form-submit.disabled, input.disabled[type="button"],
input.disabled[type="submit"], input.disabled[type="reset"], button[disabled],
input.form-submit[disabled], input[type="button"][disabled], input[type="submit"][disabled],
input[type="reset"][disabled] {
    box-shadow: none;
    opacity: 0.25 !important;
    background-image:none !important;
}

button, input.form-submit, input[type="button"], input[type="submit"], input[type="reset"] {
    background-image:none !important;
}

.activity-status-img{
    margin-right: 5px;
    padding-bottom: 2px;
    width: 10px;
}
#warn-msg{
    font-size: 8px;
    display: block;
    margin-top: 10px;
}

/*tabs css*/

.example {
    height: auto;
    width: 100%;
    background-color: white;
    margin-bottom: 15px;
    margin-top: 2px;
}
#bar{
    display:none;
}
#btnFoo,#btnBar,#btngreenStars,#btnredStars{

    cursor:pointer;
}
#btnFoo:hover,#btnBar:hover{
    text-decoration:none;
}

.current {
    color: #555;
    border: 2px solid !important;
    background-color: #FFF !important;
    border-width: 2px !important;
    border-style: solid;
    border-color: #DDD #DDD transparent !important;
    -moz-border-top-colors: none;
    -moz-border-right-colors: none;
    -moz-border-bottom-colors: none;
    -moz-border-left-colors: none;
    border-image: none;
    cursor: default;
    border-radius: 4px 4px 0px 0px;
}

.tleft {
    width: 22% !important;
}
#flip{
    display:none;
}
#t01 th {
    padding: 5px;
    text-align: center;
    border: 0px solid #ccc !important;
    padding: 3px !important;
    background-color: #e5b467  !important;
}

#bar{
    /* margin-top: 2px;border:0px;border-top: 1px solid #E4E1DA; */
}
#bar .tright{
    margin-top: 10px;width:100% !important;
    /* height: 150px !important; */
}
#foo .tright{
    margin-top: 10px;width:100% !important;
    /* height: 150px !important; */

}
.stopped{
    color:rgb(157, 38, 29);
}
.started{
    color:rgb(70, 165, 70);
}
.closed{
    color:black;
}
.current-progress {

    font-weight: bold;
    color:#A17C43;
    float: left;
    padding-left: 2%;
    width: 100%;
}
.progress-activity{
    color:rgb(70, 165, 70);

}
#current-activity{
    display:none;
}
.act-refresh{
    cursor: pointer;
}
#page-content {
    margin-bottom: 40px;
    margin-top: 5px;
}

#panel .tright{
    width: 100%;
}
.course-topic{
    float:right;
    text-align: right;
    margin-right: 20px;
    font-weight: bold;
    color: #FFFFFF;
    font-size: 10px;
    margin-top: -5px;
}
.course-topic span{
    background-color: rgb(118, 139, 149);
    border-radius: 5px;
    padding: 5px;
}
.current-progress{
    background-color: rgb(118, 139, 149);
    padding: 1px;color:#FFFFFF;
}
.progress-activity{
    color:#FFFFFF;
}
.current-progress-div{
    font-weight: bold;
    background-color: rgb(204, 204, 204); padding: 4px;height: 20px;
    text-align: center;
    margin-top: 3px;

}
.status-info {
    height: 40px;
    width: 100%;
}
.control-panel{
    float: right;
    margin-right: 10px;
    /* padding-left:5px; */
    /* padding-right:5px; */
}
.control-panel-text{
    cursor:pointer;
}
#ctrl-panel{
    font-size: 9px;
    font-weight:bold;
    cursor: pointer;
}
#greenstar_sub_list{
    position: absolute;
}
.act-sorter {
    float: right;
    position: relative;
    z-index: 99;
}
.act-sorter-select{
    height: 25px !important;
    width: 80% !important;
}
.arrow-symbol{
    cursor: pointer;
}
/*end tabs css*/

</style>