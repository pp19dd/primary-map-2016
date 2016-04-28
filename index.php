<?php
$candidates = array(
    array( "name" => "Trump",      "color" => "#e91d0e", "party" => "r" ),
    array( "name" => "Cruz",       "color" => "#e91d0e", "party" => "r" ),
    array( "name" => "Kasich",     "color" => "#e91d0e", "party" => "r" ),
    array( "name" => "Clinton",    "color" => "#232066", "party" => "d" ),
    array( "name" => "Sanders",    "color" => "#232066", "party" => "d" ),
);



?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
  <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <title>Primary Map 2016</title>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type">

    <!-- Mobile Specific Meta Tags -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">

    <link href="jqvmap.css" media="screen" rel="stylesheet" type="text/css">

<style>
html, body {
padding: 0;
margin: 0;
width: 100%;
height: 100%;
}
#vmap {
width: 100%;
height: 100%;
background-color: red;
-webkit-tap-highlight-color: rgba(0,0,0,0);
}

.candidate-name { text-align: center }
.candidate-click { }
.candidate-click-selected {};

.candidate-party-d.candidate-click-selected { background-color: #e91d0e !important; }
.candidate-party-r.candidate-click-selected { background-color: #e91d0e !important; }

td.candidate-name { }
td.candidate-name a { color: black; font-size: 18px; }
td.candidate-picture { }
.candidate-delegate-count { text-align: center }
td.candidate-delegate-count a { color: black; font-size: 32px; color:#333 }
</style>

<script type="text/javascript" src="http://code.jquery.com/jquery-1.11.3.min.js"></script>
<script type="text/javascript" src="jquery.vmap.js"></script>
<script type="text/javascript" src="jquery.vmap.usa.js" charset="utf-8"></script>

<script type="text/javascript">
var all_data = null;

jQuery(document).ready(function () {
    jQuery.ajax({
        url: "http://projects.voanews.com/gdoc-data/?project=primaries_webdesk",
        dataType: "jsonp",
        success: function(data) {
            all_data = data;

            var states = {};
            for( var i = 0; i < data.length; i++ ) {
                states[data[i].abbr.toLowerCase()] = data[i];
            }

            function highlight_candidate(candidate, color, party) {

                $(".candidate-click").removeClass("candidate-click-selected");
                $(".candidate-click.candidate-" + candidate).addClass("candidate-click-selected");

                for( var k in states )(function(code, state) {

                    var x = {};
                    x[code] = color;

                    if( state[candidate] == 0 ) {
                        x[code] = "#ffffff";
                    }

                    if( did_candidate_win_state(candidate, state, party) === false ) {
                        x[code] = "#ffffff";
                    }

                    jQuery('#vmap').vectorMap('set', 'colors', x);
                })(k, states[k])

            }

            function highlight_states_that_voted() {
                for( var k in states )(function(code, state) {

                    var x = {};

                    if( did_state_vote(state) === false ) {
                        x[code] = "#ffffff";
                    } else {
                        x[code] = "#333";
                    }

                    jQuery('#vmap').vectorMap('set', 'colors', x);
                })(k, states[k]);
            }

            function did_state_vote( state ) {
                var count = 0;
                var k = ["clinton", "cruz", "kasich", "sanders", "trump"];
                for( var i = 0; i < k.length; i++ ) {
                    count += parseInt(state[k[i]]);
                }
                if( count === 0 ) return( false );
                return(true);
            }

            function did_candidate_win_state(candidate, state, party) {

                var votes_for_candidate = parseInt(state[candidate]);

                var k = {
                    "d": ["clinton", "sanders"],
                    "r": ["cruz", "kasich", "trump"]
                };

                for( var i = 0; i < k[party].length; i++ ) {
                    var compare_to = parseInt(state[k[party][i]]);
                    if( votes_for_candidate < compare_to ) return( false );
                }

                return(true);
            }

            function show_delegate_counts() {
                var counts = {
                    "clinton": 0,
                    "cruz": 0,
                    "kasich": 0,
                    "sanders": 0,
                    "trump": 0
                };

                // compute totals
                for( var k in states )(function(code, state) {
                    for( var d in counts )(function(key, count) {
                        counts[key] += parseInt(count);
                    })(d, state[d]);
                })(k, states[k]);

                // display totals
                for( var d in counts )(function(candidate, delegate_count) {
                    $("span.candidate-delegate-count-compute.candidate-" + candidate).html( delegate_count );
                })(d, counts[d]);
            }

            // debug: did_candidate_win_state("trump", all_data[47], "r");

            jQuery('#vmap').vectorMap({
                map: 'usa_en',
                enableZoom: true,
                showTooltip: true,
                onLabelShow: function(event, label, code) {

                    if( did_state_vote(states[code]) === false ) {
                        label.html(
                            "<strong>" + states[code].state + "</strong><br/><hr/>" +
                            states[code].note
                        );
                        return;
                    }

                    label.html(
                        "<strong>" + states[code].state + "</strong><br/><hr/>" +
                        "Republican Delegates: " + states[code].total_delegates_gop + "<br/>" +
                        "Trump: " + states[code].trump + "<br/>" +
                        "Cruz: " + states[code].cruz + "<br/>" +
                        "Kasich: " + states[code].kasich + "<br/>" +
                        "<hr/>" +
                        "Democratic Delegates: " + states[code].total_delegates_dem + "<br/>" +
                        "Clinton: " + states[code].clinton + "<br/>" +
                        "Sanders: " + states[code].sanders + "<br/>"
                    );
                },
                onRegionClick: function(element, code, region) {

                }
            });

            $(".filter a").click(function() {
                var k = $(this).attr("data-key");
                var c = $(this).attr("data-color");
                var p = $(this).attr("data-party");

                highlight_candidate(k, c, p);
                return(false);
            });

            // on startup
            highlight_states_that_voted();
            show_delegate_counts();

        }
    });
});
</script>
  </head>

  <body>
    <div class="filter">
<table style="width:100%">
<tr>
<?php foreach( $candidates as $candidate ) { ?>
    <td class="candidate-name">
        <a class="candidate-click candidate-party-<?php echo strtolower($candidate["party"]); ?> candidate-<?php echo strtolower($candidate["name"]); ?>" data-party="<?php echo $candidate["party"] ?>" data-color="<?php echo $candidate["color"] ?>" data-key="<?php echo strtolower($candidate["name"]) ?>" href="#">
            <?php echo $candidate["name"] ?>
        </a>
    </td>
<?php } ?>
</tr>
<tr>
<?php foreach( $candidates as $candidate ) { ?>
    <td class="candidate-delegate-count">
        <a class="candidate-click candidate-party-<?php echo strtolower($candidate["party"]); ?> candidate-<?php echo strtolower($candidate["name"]); ?>" data-party="<?php echo $candidate["party"] ?>" data-color="<?php echo $candidate["color"] ?>" data-key="<?php echo strtolower($candidate["name"]) ?>" href="#">
            <span class="candidate-delegate-count-compute candidate-<?php echo strtolower($candidate["name"]) ?>">0</span>
        </a>
    </td>
<?php } ?>
</tr>
</table>
    </div>
    <div id="vmap"></div>
  </body>
</html>
