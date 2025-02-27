<script type="text/javascript">
function toggleRatingView(course_id) {
  $('#course_info_view_'+course_id).toggle();
  $('#course_rating_view_'+course_id).toggle();
  $('#edit_rating_btn_'+course_id).toggle();
  $('#cancel_rating_btn_'+course_id).toggle();
}

function publishRating(course_id) {
    var review = $('#review_of_a_course_'+course_id).val();
    var starRating = 0;
    starRating = $('#star_rating_of_course_'+course_id).val();
    if (starRating > 0) {
        $.ajax({
            type : 'POST',
            url  : '<?php echo site_url('home/rate_course'); ?>',
            data : {course_id : course_id, review : review, starRating : starRating},
            success : function(response) {
                location.reload();
            }
        });
    }else{

    }
}

function isTouchDevice() {
  return (('ontouchstart' in window) ||
     (navigator.maxTouchPoints > 0) ||
     (navigator.msMaxTouchPoints > 0));
}

function viewMore(element, visibility) {
  if (visibility == "hide") {
    $(element).parent(".view-more-parent").addClass("expanded");
    $(element).remove();
  } else if ($(element).hasClass("view-more")) {
    $(element).parent(".view-more-parent").addClass("expanded has-hide");
    $(element)
      .removeClass("view-more")
      .addClass("view-less")
      .html("- <?php echo site_phrase('view_less'); ?>");
  } else if ($(element).hasClass("view-less")) {
    $(element).parent(".view-more-parent").removeClass("expanded has-hide");
    $(element)
      .removeClass("view-less")
      .addClass("view-more")
      .html("+ <?php echo site_phrase('view_more'); ?>");
  }
}

function redirect_to(url){
  if(!isTouchDevice() && $(window).width() > 767){
    window.location.replace(url);
  }
}

//Event call after loading page
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function(){
        $('.animated-loader').hide();
        $('.shown-after-loading').show();
    });
}, false);


function check_action(e, url){
  var tag = $(e).prop("tagName").toLowerCase();
  if(tag == 'a'){
    return true;
  }else if(tag != 'a' && url){
    $(location).attr('href', url);
    return false;
  }else{
    return true;
  }
}
</script>


<!-- Google analytics -->
<?php if(!empty(get_settings('google_analytics_id'))): ?>
  <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo get_settings('google_analytics_id'); ?>"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', '<?php echo get_settings('google_analytics_id'); ?>');
  </script>
<?php endif; ?>
<!-- Ended Google analytics -->

<!-- Meta pixel -->
<?php if(!empty(get_settings('meta_pixel_id'))): ?>
  <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '<?php echo get_settings('meta_pixel_id'); ?>');
    fbq('track', 'PageView');
  </script>
  <noscript>
    <img height="1" width="1" style="display:none" 
         src="https://www.facebook.com/tr?id=<?php echo get_settings('meta_pixel_id'); ?>&ev=PageView&noscript=1"/>
  </noscript>
<?php endif; ?>
<!-- Ended Meta pixel -->