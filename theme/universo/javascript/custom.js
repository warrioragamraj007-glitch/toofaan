var _days = 'Days';
var _hours = 'Hours';
var _minutes = 'Minutes';
var _seconds = 'Seconds';
var _messageAfterCount = 'The course has Started!';
//alert("hello");
var $j = jQuery.noConflict();
$j(document).ready(function($j) {
    "use strict";
	//alert("helloinside");
    if (location.hash) {
        window.scrollTo(0, 0);
        setTimeout(function() {
            window.scrollTo(0, 0);
        }, 1);
    }
	//  Selectize
	//alert("hello1");
    //$j('select').selectize();
if($j("body").hasClass("path-login")){   
$j('input').iCheck();
    }
//alert("hello2");

//  Checkbox styling
   //alert("out");
   
//alert("exit");
//*/

//  Homepage Slider (Flex Slider)

    if ($j('.flexslider').length > 0) {
        $j('.flexslider').flexslider({
            controlNav: false,
            prevText: "",
            nextText: ""
        });
    }

//  Open tab from another page            (commented by mahesh)
/*
    $j('a[data-toggle="tab"]').on('show.bs.tab', function(e) {});

    $j('#tabs a[href=' + location.hash +']').tab('show');

    $j('.secondary-navigation li a').on('click',function (e) {
        $j('#tabs a[href=' + this.hash +']').tab('show');
    });
*/

//  Table Sorter
    if ($j('.tablesorter').length > 0) {
        $j(".course-list-table").tablesorter();
    }

//  Rating

    if ($j('.rating-individual').length > 0) {
        $j('.rating-individual').raty({
            path: 'assets/img',
            readOnly: true,
            score: function() {
                return $j(this).attr('data-score');
            }
        });
    }

    if ($j('.rating-user').length > 0) {
        $j('.rating-user .inner').raty({
            path: 'assets/img',
            starOff : 'big-star-off.png',
            starOn  : 'big-star-on.png',
            width: 180,
            target : '#hint',
            targetType : 'number',
            targetFormat : 'Rating: {score}',
            click: function(score, evt) {
                alert("Your Rating: " + score + "\nThank You!");
            }
        });
    }

//  Checkbox styling

    if ($j('.checkbox').length > 0) {
       // $j('input').iCheck();
    }

// Disable input on count down

    $j('.knob').prop("disabled", true);


//  Count Down - Landing Page

    if ($j('.count-down').length > 0) {
        $j(".count-down").ccountdown(2014,12,24,'18:00');
    }


//  Center Slide Vertically

    $j('.flexslider').each(function () {
        var slideHeight = $j(this).height();
        var contentHeight = $j('.flexslider .slides li .slide-wrapper').height();
        var padTop = (slideHeight / 2) - (contentHeight / 2);
        $j('.flexslider .slides li .slide-wrapper').css('padding-top', padTop);
    });

//  Slider height on small screens

    if (document.documentElement.clientWidth < 991) {
        $j('#landing-page-head-image').css('height', $j(window).height());
        $j('.flexslider').css('height', $j(window).height());
    }

//  Homepage Carousel       (commented by mahesh)
/*
    $j(".image-carousel").owlCarousel({
        items: 1,
        autoPlay: true,
        stopOnHover: true,
        navigation: true,
        navigationText : false,
        responsiveBaseWidth: ".image-carousel-slide"
        //responsiveBaseWidth: ".author"
    });
*/

//  Smooth Scroll

    $j('.navigation-wrapper .nav a[href^="#"], a[href^="#"].roll').on('click',function (e) {
        e.preventDefault();
        var target = this.hash,
            $jtarget = $j(target);
        $j('html, body').stop().animate({
            'scrollTop': $jtarget.offset().top
        }, 2000, 'swing', function () {
            window.location.hash = target;
        });
    });

//  Fixed Navigation After Scroll

//    if (document.documentElement.clientWidth > 768) {
//        $j(window).scroll(function () {
//            if ($j(window).scrollTop() > 50) {
//                $j('.page-landing-page .primary-navigation-wrapper').addClass('navigation-fixed');
//            } else {
//                $j('.page-landing-page .primary-navigation-wrapper').removeClass('navigation-fixed');
//            }
//        });
//    }


/*  author Carousel (Owl Carousel)		 (commented by mahesh)

    $j(".author-carousel").owlCarousel({
        items: 1,
        autoPlay: false,
        stopOnHover: true,
        responsiveBaseWidth: ".author"
    });
*/
//  Equal Rows

    if(document.documentElement.clientWidth > 991) {
        $j('.row').equalHeights();
    }

    $j( document.body ).on( 'click', '.dropdown-menu li', function( event ) {
        var $jtarget = $j( event.currentTarget );
        $jtarget.closest( '.btn-group' )
            .find( '[data-bind="label"]' ).text( $jtarget.text() )
            .end()
            .children( '.dropdown-toggle' ).dropdown( 'toggle' );
        return false;
    });

//  Slider Subscription Form

    $j("#slider-submit").bind("click", function(event){
        $j("#slider-form").validate({
            submitHandler: function() {
                $j.post("slider-form.php", $j("#slider-form").serialize(),  function(response) {
                    $j('#form-status').html(response);
                    $j('#submit').attr('disabled','true');
                });
                return false;
            }
        });
    });

//  Contact Form with validation

    $j("#submit").bind("click", function(event){
        $j("#contactform").validate({
            submitHandler: function() {
                $j.post("contact.php", $j("#contactform").serialize(),  function(response) {
                    $j('#form-status').html(response);
                    $j('#submit').attr('disabled','true');
                });
                return false;
            }
        });
    });

//  Landing Page Form

    $j("#landing-page-submit").bind("click", function(event){
        $j("#form-landing-page").validate({
            submitHandler: function() {
                $j.post("landing-page-form.php", $j("#form-landing-page").serialize(),  function(response) {
                    $j('#form-status').html(response);
                    $j('#submit').attr('disabled','true');
                });
                return false;
            }
        });
    });

//  Vanilla Box

    if ($j('.image-popup').length > 0) {
        $j('a.image-popup').vanillabox({
            animation: 'default',
            type: 'image',
            closeButton: true,
            repositionOnScroll: true
        });
    }

//  Calendar

    if ($j('.calendar').length > 0) {
        $j('.calendar').fullCalendar({
            firstDay: 1,
            weekMode: 'variable',
            contentHeight: 700,
            header: {
                right: 'month,basicWeek,basicDay prev,next'
            },

            events: "events.php"

        });
    }

//  Event title shorting

    $j('.fc-view-month .fc-event-title').each(function(){
        $j(this).text($j(this).text().substring(0,25));
    }); 
   
   
});


// Remove button function for "join to course" button after count down is over

function disableJoin() {
    // Find "join to course" button
    var buttonToBeRemoved = document.getElementById("btn-course-join");
    // Find "join to course" button on bottom of course detail
    var buttonToBeRemovedBottom = document.getElementById("btn-course-join-bottom");
    // Remove button
    buttonToBeRemoved.remove();
    // Remove button on the bottom
    buttonToBeRemovedBottom.remove();
    // Give the ".course-count-down" element new class to hide date
    document.getElementById("course-count-down").className += " disable-join";
    document.getElementById("course-start").className += " disable-join";
}

//  Count Down - Course Detail

if (typeof _date != 'undefined') { // run function only if _date is defined
    var Countdown = new Countdown({
        dateEnd: new Date(_date),
        msgAfter: _messageAfterCount,
        onEnd: function() {
            disableJoin(); // Run this function after count down is over
        }
    });
}
