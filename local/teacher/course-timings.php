<?php
if (!user_has_role_assignment($USER->id, 3)){
    redirect($CFG->wwwroot);
   
}
$courseTimingsArray=array(
                            'Mon'=>
                                array(
                                        array('cid'=>9,'stime'=>'9.00','etime'=>'13.00'),//cpc attendance,performance
                                        array('cid'=>8,'stime'=>'13.00','etime'=>'18.00')//fs bottom 25 sms
                                ),
                            'Tue'=>
                                array(
                                    array('cid'=>7,'stime'=>'9.00','etime'=>'13.00'),//java performance
                                    array('cid'=>8,'stime'=>'13.00','etime'=>'18.00')//fs bottom 25 sms
                                ),
                            'Wed'=>
                                array(
                                    array('cid'=>9,'stime'=>'9.00','etime'=>'13.00'),//cpc attendance,performance
                                    array('cid'=>8,'stime'=>'13.00','etime'=>'18.00')//fs bottom 25 sms
                                ),
                            'Thu'=>
                                array(
                                    array('cid'=>7,'stime'=>'9.00','etime'=>'13.00'),//java performance
                                    array('cid'=>8,'stime'=>'13.00','etime'=>'18.00')//fs bottom 25 sms
                                ),
                            'Fri'=>
                                array(
                                    array('cid'=>8,'stime'=>'13.00','etime'=>'18.00')//fs bottom 25 sms
                                ),
                            'Sat'=>
                                array(
                                    array('cid'=>9,'stime'=>'13.00','etime'=>'18.00')//cpc attendance,performance
                                ),
                            'Sun'=>
                                array(array())
                        );


//var_dump(getCourseTimings(9,'Thu'));

function getCourseTimings($course,$dayname){
return array("today 9.00","today 16.00");
    global $courseTimingsArray;
            $courseTime=$courseTimingsArray[$dayname];
            foreach($courseTime as $ckey => $cval){
                if($cval['cid']==$course){
                    return array($cval['stime'],$cval['etime']);
                }

            }
    return 0;
}
