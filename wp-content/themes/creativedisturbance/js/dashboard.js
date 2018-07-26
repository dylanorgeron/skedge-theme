// files we are using
var baseurl = "http://dylanorgeron.com/stats/json.php?url=http://dylanorgeron.com/stats/data/";
//var baseurl = "http://creativedisturbance.org/stats/json.php?url=http://creativedisturbance.org/stats/data/";
var monthly_file = baseurl+"monthly.csv";
var weekly_file = baseurl+"weekly.csv";
var country_file = baseurl+"countries.csv";
var total_file = baseurl +"total.csv";
var regions_file = baseurl+"regions.csv";
window.episode_file = baseurl+"episode.csv";
//var episode_file = "/stats/data/episode.json";

var tech_file = baseurl+"technology.csv";

var window_height = $(window).height();
$("#glow").height(window_height);

// function to get max and min
function getMax(arr, prop) {
    var max;
    for (var i=0 ; i<arr.length ; i++) {
        if (!max || parseInt(arr[i][prop]) > parseInt(max[prop]))
            max = arr[i];
    }
    return max;
}

// sort function
var sort_by = function(field, reverse, primer){

   var key = primer ?
       function(x) {return primer(x[field])} :
       function(x) {return x[field]};

   reverse = !reverse ? 1 : -1;

   return function (a, b) {
       return a = key(a), b = key(b), reverse * ((a > b) - (b > a));
     }
}
// sums
var sumByKey = function(array, keyField, keyValue, valueField){
  var sum = 0;
  for(var i=0, len=array.length; i<len; i++)
    if(array[i][keyField] == keyValue)
      sum += parseFloat(array[i][valueField])
    return sum;
}

// TOTAL STATS
var jqxhr = $.getJSON( total_file, function(json) {
  var total_downloads = json[3].downloads;
  $("#total-downloads").append(Number(total_downloads).toLocaleString('en'));
})

// MONTHLY STATS
var jqxhr = $.getJSON( monthly_file, function(json) {

  var newm = json[json.length-1].total_downloads;
  var old = json[json.length-2].total_downloads;
  var month_change = (((newm - old) /old) * 100).toFixed(2);
  var month_class = 'normal';

  if (month_change > 0) {
    month_change_direction = '▲';
    month_class = 'green';
  }
  else if (month_change < 0) {
    month_change_direction = '▼';
    month_class = 'red'
  }
  else if (month_change = 0) {
    month_change_direction = '0';
  }

  month_change = month_change_direction + month_change;
  var month_max = (getMax(json, 'total_downloads')).total_downloads;
  var podcast_width;

  /*build with chart.js*/
  var monthJson = json;
  var monthData = {
    labels: [],
    datasets: [{
      label: "Downloads by Month",
      fillColor: "rgba(151,187,205,0.2)",
      strokeColor: "rgba(151,187,205,1)",
      pointColor: "rgba(151,187,205,1)",
      pointStrokeColor: "#fff",
      pointHighlightFill: "#fff",
      pointHighlightStroke: "rgba(151,187,205,1)",
      data: []
    }]
  };

  $.each(json, function(i,v){
    if(i >= (json.length - 6)){
      monthData.labels.push(v.month);
      monthData.datasets[0].data.push(v.total_downloads);
    }
  })

  var ctx = $("#monthChart").get(0).getContext("2d");
  var myLineChart = new Chart(ctx).Line(monthData, options);

  /*build with js*/
  /*
  $.each(json.reverse(), function (index, value) {
        podcast_width = ((json[index].total_downloads)/month_max) * 88;
        $("#month-table").append('<div class="date">'+ json[index].month.slice(0,3) +'</div><div class="bar" style="width:'+podcast_width+'% " data-toggle="tooltip" data-placement="right" title="'+ json[index].total_downloads+'"></div>');
    })
  */

  $.each(json.reverse(), function (i, v) {
    $("#monthly-list .list-container").append('<div class="month-row"><b>' + v.month + ' </b> - ' + v.year + '<div style="float:right">' + Number(v.total_downloads).toLocaleString('en') + '</div></div>');
  })

  $("#current-month").append(Number(newm).toLocaleString('en'));
  $("#current-month-change").prepend(month_change).addClass(month_class);
});

// WEEKLY STATS
var jqxhr = $.getJSON( weekly_file, function(json) {
  //set our vars
  var neww = json[json.length-1].total_downloads;
  var old = json[json.length-2].total_downloads;
  var week_change = (((neww - old) /old) * 100).toFixed(2);
  var week_change_direction = 0;
  var week_class = 'normal';
  if (week_change > 0) {
    week_change_direction = '▲';
    week_class = 'green';
  }
  else if (week_change < 0) {
    week_change_direction = '▼';
    week_class = 'red'
  }
  else if (week_change = 0) {
    week_change_direction = '0'
  }
  week_change = week_change_direction + week_change;
  var week_max = (getMax(json, 'total_downloads')).total_downloads;
  var podcast_width = 0;
  var current_downloads = 0;
  var previous_downloads = 0;
  var change = 0;
  var next = 0;

  /*via chart.js*/
  var weekJson = json;
  var weekData = {
    labels: [],
    datasets: [{
      label: "Downloads by Week",
      fillColor: "rgba(151,187,205,0.2)",
      strokeColor: "rgba(151,187,205,1)",
      pointColor: "rgba(151,187,205,1)",
      pointStrokeColor: "#fff",
      pointHighlightFill: "#fff",
      pointHighlightStroke: "rgba(151,187,205,1)",
      data: []
    }]
  };

  $.each(json, function(i,v){
    if(i >= (json.length - 12)){
      //weekData.labels.push(v.month + " " + v.day);
      weekData.labels.push(v.month + " " + v.day);
      weekData.datasets[0].data.push(v.total_downloads);
    }
  })

  var ctx = $("#weekChart").get(0).getContext("2d");
  var weekLineChart = new Chart(ctx).Line(weekData, options);

  $.each(json.reverse(), function (i, v) {
      $("#weekly-list .list-container").append('<div class="month-row"><b>' + v.month + ' </b> - ' + v.year + '<div style="float:right">' + Number(v.total_downloads).toLocaleString('en') + '</div></div>');
  })

  $("#current-week").append(Number(neww).toLocaleString('en'));
  $("#current-week-change").prepend(week_change).addClass(week_class);
  //$("[data-toggle='tooltip']").tooltip();
  $( ".bar" ).mouseover(function() {
      $(this).addClass("current-bar");
      $(this).prev().addClass("text-hover");
    })
    .mouseout(function() {
      $(this).removeClass("current-bar");
      $(this).prev().removeClass("text-hover");
    });
});

// COUNTRY STATS
var jqxhr = $.getJSON( country_file, function(json) {
  //set our vars
  var country_max = (getMax(json, 'total_downloads')).total_downloads;
  json.sort(sort_by('total_downloads', false, parseInt));
  var country_sum = 0;
  var country_percent = 0;
  var total_number_country = Object.keys(json).length;


  $.each(json, function (index, value) {
      country_sum += parseInt((json[index].total_downloads), 10);
      // get the sum for the percentage
  });
  $.each(json.reverse(), function (index, value) {
      country_percent = (((json[index].total_downloads) / country_sum) * 100).toFixed(2) ;
      $("#country-table ul").append('<li><span class="country-number">'+ (index+1) +'</span><span class="country-name">'+ json[index].country_name + '</span><span class="country-downloads">'+ json[index].total_downloads + '</span><span class="country-percent">'+ country_percent.substring(0, country_percent.length-1) +'%</span></li>');
      //return index < 10
  });
  $("#total-countries").prepend(total_number_country);
});

// REGIONAL STATS
var jqxhr = $.getJSON( regions_file, function(json) {
  //set our vars
  var region_max = (getMax(json, 'total_downloads')).total_downloads;
  json.sort(sort_by('total_downloads', false, parseInt));
  var region_sum = 0;
  var region_percent = 0;
  var total_number_region = Object.keys(json).length;
  // get the sum for the percentage
  $.each(json, function (index, value) {
      region_sum += parseInt((json[index].total_downloads), 10);
  });
  // need stats, stat!
  $.each(json.reverse(), function (index, value) {
      region_percent = (((json[index].total_downloads) / region_sum) * 100).toFixed(2) ;
     // $("#region-table").append('<div>'+ json[index].region +'</div>');
      $("#region-table ul").append('<li><span class="region-number">'+ (index+1) +'</span><span class="region-name">'+ json[index].region + '</span><span class="region-downloads">'+ json[index].total_downloads + '</span><span class="region-percent">'+ region_percent.substring(0, region_percent.length-1) +'%</span></li>');
      //return index < 10
      0; // take this out if you want all
  });
  $("#region #total-regions").prepend(total_number_region);
});


// EPISODE STATS
var jqxhr = $.getJSON( episode_file, function(json) {
  //set our vars
  var episode_max = (getMax(json, 'total_downloads')).total_downloads;
  json.sort(sort_by('downloads__total', false, parseInt)).reverse();
  var total_number_episode = Object.keys(json).length;
  $.each(json, function (index, value) {
      if(value.release_date){
        $("#episode-table ul").append('<li><span class="episode-number">'+ (index+1) +'</span><span class="episode-name">'+ json[index].item_title + '</span><span class="episode-downloads">'+ json[index].downloads__total +'</span><p class="episode-date">' + (json[index].release_date).slice(0, 10) +'</p></li>');
      }
  });
  $("#total-episodes").append(total_number_episode);
    //$("#current-week-change").prepend(week_change).addClass(week_class);
});

// enable tooltip
//$('[data-toggle="tooltip"]').tooltip();


/***********************/
/*   Redraws charts    */
/***********************/
window.drawMonth = function(time){

  $(".month-btn").removeClass('active')
  $("#"+time).addClass('active')
  $("#month-buttons .btn-primary").addClass("btn-default");
  $("#month-buttons .btn-primary").removeClass("btn-primary");
  $("#" + time).addClass("btn-primary");

  $.getJSON( monthly_file, function(json) {
    var monthJson = json;
    var monthData = {
      labels: [],
      datasets: [{
        label: "Downloads by Month",
        fillColor: "rgba(151,187,205,0.2)",
        strokeColor: "rgba(151,187,205,1)",
        pointColor: "rgba(151,187,205,1)",
        pointStrokeColor: "#fff",
        pointHighlightFill: "#fff",
        pointHighlightStroke: "rgba(151,187,205,1)",
        data: []
      }]
    };

    $.each(json, function(i,v){
      if(i >= (json.length - time)){
        monthData.labels.push(v.month);
        monthData.datasets[0].data.push(v.total_downloads);
      }
    })

    $("#monthChart").remove();
    $("#month").prepend("<canvas id='monthChart'></canvas>");
    var ctx = $("#monthChart").get(0).getContext("2d");
    var myLineChart = new Chart(ctx).Line(monthData, options);
  })
}

window.drawWeek = function(time){
  $("#week-buttons .btn-primary").addClass("btn-default");
  $("#week-buttons .btn-primary").removeClass("btn-primary");
  $("." + time).addClass("btn-primary");

  $.getJSON( weekly_file, function(json){
    var weekJson = json;
    var weekData = {
      labels: [],
      datasets: [{
        label: "Downloads by Week",
        fillColor: "rgba(151,187,205,0.2)",
        strokeColor: "rgba(151,187,205,1)",
        pointColor: "rgba(151,187,205,1)",
        pointStrokeColor: "#fff",
        pointHighlightFill: "#fff",
        pointHighlightStroke: "rgba(151,187,205,1)",
        data: []
      }]
    };

    $.each(json, function(i,v){
      if(i >= (json.length - time)){
        //weekData.labels.push(v.month + " " + v.day);
        weekData.labels.push(v.month + " " + v.day);
        weekData.datasets[0].data.push(v.total_downloads);
      }
    })

    $("#weekChart").remove();
    $("#weekly").prepend("<canvas id='weekChart'></canvas>");
    var ctx = $("#weekChart").get(0).getContext("2d");
    var weekLineChart = new Chart(ctx).Line(weekData, options);
  })
}


/*chart config*/
options = {
    bezierCurve : true,

    // Boolean - Whether to animate the chart
    animation: true,

    // Number - Number of animation steps
    animationSteps: 60,

    // String - Animation easing effect
    // Possible effects are:
    // [easeInOutQuart, linear, easeOutBounce, easeInBack, easeInOutQuad,
    //  easeOutQuart, easeOutQuad, easeInOutBounce, easeOutSine, easeInOutCubic,
    //  easeInExpo, easeInOutBack, easeInCirc, easeInOutElastic, easeOutBack,
    //  easeInQuad, easeInOutExpo, easeInQuart, easeOutQuint, easeInOutCirc,
    //  easeInSine, easeOutExpo, easeOutCirc, easeOutCubic, easeInQuint,
    //  easeInElastic, easeInOutSine, easeInOutQuint, easeInBounce,
    //  easeOutElastic, easeInCubic]
    animationEasing: "easeOutQuart",

    // Boolean - If we should show the scale at all
    showScale: true,

    // Boolean - If we want to override with a hard coded scale
    scaleOverride: false,

    // ** Required if scaleOverride is true **
    // Number - The number of steps in a hard coded scale
    scaleSteps: null,
    // Number - The value jump in the hard coded scale
    scaleStepWidth: null,
    // Number - The scale starting value
    scaleStartValue: null,

    // String - Colour of the scale line
    scaleLineColor: "rgba(0,0,0,.1)",

    // Number - Pixel width of the scale line
    scaleLineWidth: 1,

    // Boolean - Whether to show labels on the scale
    scaleShowLabels: true,

    // Interpolated JS string - can access value
    scaleLabel: "<%=value%>",

    // Boolean - Whether the scale should stick to integers, not floats even if drawing space is there
    scaleIntegersOnly: true,

    // Boolean - Whether the scale should start at zero, or an order of magnitude down from the lowest value
    scaleBeginAtZero: false,

    // String - Scale label font declaration for the scale label
    scaleFontFamily: "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif",

    // Number - Scale label font size in pixels
    scaleFontSize: 12,

    // String - Scale label font weight style
    scaleFontStyle: "normal",

    // String - Scale label font colour
    scaleFontColor: "#666",

    // Boolean - whether or not the chart should be responsive and resize when the browser does.
    responsive: true,

    // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
    maintainAspectRatio: false,

    // Boolean - Determines whether to draw tooltips on the canvas or not
    showTooltips: true,

    // Function - Determines whether to execute the customTooltips function instead of drawing the built in tooltips (See [Advanced - External Tooltips](#advanced-usage-custom-tooltips))
    customTooltips: false,

    // Array - Array of string names to attach tooltip events
    tooltipEvents: ["mousemove", "touchstart", "touchmove"],

    // String - Tooltip background colour
    tooltipFillColor: "rgba(0,0,0,0.8)",

    // String - Tooltip label font declaration for the scale label
    tooltipFontFamily: "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif",

    // Number - Tooltip label font size in pixels
    tooltipFontSize: 14,

    // String - Tooltip font weight style
    tooltipFontStyle: "normal",

    // String - Tooltip label font colour
    tooltipFontColor: "#fff",

    // String - Tooltip title font declaration for the scale label
    tooltipTitleFontFamily: "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif",

    // Number - Tooltip title font size in pixels
    tooltipTitleFontSize: 14,

    // String - Tooltip title font weight style
    tooltipTitleFontStyle: "bold",

    // String - Tooltip title font colour
    tooltipTitleFontColor: "#fff",

    // Number - pixel width of padding around tooltip text
    tooltipYPadding: 6,

    // Number - pixel width of padding around tooltip text
    tooltipXPadding: 6,

    // Number - Size of the caret on the tooltip
    tooltipCaretSize: 8,

    // Number - Pixel radius of the tooltip border
    tooltipCornerRadius: 6,

    // Number - Pixel offset from point x to tooltip edge
    tooltipXOffset: 10,

    // String - Template string for single tooltips
    tooltipTemplate: "<%if (label){%><%=label%>: <%}%><%= value %>",

    // String - Template string for multiple tooltips
    multiTooltipTemplate: "<%= value %>",

    // Function - Will fire on animation progression.
    onAnimationProgress: function(){},

    // Function - Will fire on animation completion.
    onAnimationComplete: function(){}
}
