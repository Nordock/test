
$(window).on('load', function () {
    $('#loading').fadeOut();
});

// Init Slick
$(".food-slider").slick({
  slidesToShow   : 3,
  slidesToScroll : 3,
  infinite : true,
  dots     : false,
  arrows   : false,
  autoplay : true,
  responsive : [
    {
      breakpoint : 960,
      settings : {
        slidesToShow   : 3,
        slidesToScroll : 3
      }
    }
  ]
});

$(".service-slider").slick({
  slidesToShow   : 3,
  slidesToScroll : 3,
  infinite : true,
  dots     : false,
  arrows   : false,
  autoplay : true,
  responsive : [
    {
      breakpoint : 960,
      settings : {
        slidesToShow   : 3,
        slidesToScroll : 3
      }
    }
  ]
});


$('.carousel[data-type="multi"] .item').each(function(){
  var next = $(this).next();
  if (!next.length) {
    next = $(this).siblings(':first');
  }
  next.children(':first-child').clone().appendTo($(this));

  for (var i=0;i<4;i++) {
    next=next.next();
    if (!next.length) {
    	next = $(this).siblings(':first');
  	}

    next.children(':first-child').clone().appendTo($(this));
  }
});

// scroll page
$("#nav a[href^='#'], .btn-service").on('click', function(e) {
 // prevent default anchor click behavior
 e.preventDefault();
 // store hash
 const hash = this.hash;

 // animate
 $('html, body').animate({
     scrollTop: $(hash).offset().top
   }, 500, function(){
     // when done, add hash to url
     // (default click behaviour)
     window.location.hash = hash;
   });
});

// add/remmove active class
$(function() {
  $('.service-list').hide();
  $('#service-buffet').fadeIn();

  $(".service-category a").on('click', function(e) {
    e.preventDefault();

    const hash = this.hash;

    if (hash !== '#catering') {
      // Hide Subcategory
      $('.service-subcategory').hide();

      // Hide Subcategory menu
      $('.service-list').hide();

      // Show Category services
      $(hash).fadeIn();
    } else {
      // Show Subcategory
      $('.service-subcategory').show();

      // Show Subcategory Buffet as default
      $('.service-list').hide();
      $('#service-buffet').fadeIn();

      // Add active class to subcategory buffet
      $(".service-subcategory a").removeClass("active");
      $(".service-subcategory a:first").addClass("active");
    }

    // remove classes from all
    $(".service-category a").removeClass("active");
    // add class to the one we clicked
    $(this).addClass("active");
  });

  $(".service-subcategory a").on('click', function(e) {
    e.preventDefault();

    const hash = this.hash;

    $('.service-list').hide();
    $(hash).fadeIn();

    // remove classes from all
    $(".service-subcategory a").removeClass("active");
    // add class to the one we clicked
    $(this).addClass("active");
  });
});
