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
* {
    font-family: Arial,Helvetica,sans-serif;
    font-size: 15px;
}
#vmap {
width: 100%;
height: 100%;
background-color: red;
-webkit-tap-highlight-color: rgba(0,0,0,0);
}

/* div version */
.candidates { }
.candidate { width: 20%; float: left; text-align: center; }
.candidate-container { padding: 0.25em; }
.candidate-inner { border-radius:0.5em; cursor: pointer; padding-top:0.5em; padding-bottom:0.5em; border: 4px solid #999 }


.candidate-inner { transition: background-color 100ms linear; }

.candidate-party-d .candidate-inner { border: 4px solid #423bbf }
.candidate-party-r .candidate-inner { border: 4px solid #e91d0e }

.candidate-name { font-weight: bold }
.candidate-name, .candidate-delegate-count-compute { color: #333 }
/*
.candidate-party-d .candidate-name, .candidate-party-d .candidate-delegate-count-compute { color: #232066 }
.candidate-party-r .candidate-name, .candidate-party-r .candidate-delegate-count-compute { color: #e91d0e }
*/
.candidate-party-d.candidate-click-selected .candidate-inner { background-color: #232066; border: 4px solid #232066 }
.candidate-party-r.candidate-click-selected .candidate-inner { background-color: #e91d0e; }

.candidate-party-d.candidate-click-selected .candidate-name { color: white; }
.candidate-party-r.candidate-click-selected .candidate-name { color: white; }

.candidate-party-d.candidate-click-selected .candidate-delegate-count-compute { color: white; }
.candidate-party-r.candidate-click-selected .candidate-delegate-count-compute { color: white; }

.candidate-click-selected { }

.candidates-note { }
.candidates-note-all { font-size: 0.75em }
.candidates-note-d { float: right; padding-right:2em; color: #232066 }
.candidates-note-r { float: left; padding-left: 2em; color: #e91d0e }

.tooltip { width: 100% }
.tooltip * { font-size: 12px }
.tooltip-name { width: 50px }

.clearDiv { clear: both }
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

                $(".candidate").removeClass("candidate-click-selected");
                $(".candidate.candidate-" + candidate).addClass("candidate-click-selected");

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
                    $("div.candidate-delegate-count-compute.candidate-" + candidate).html( delegate_count );
                })(d, counts[d]);
            }

            function html_count(count, max, color) {
                var c_width = 100 * (parseInt(count) / parseInt(max));
                var c_inverse_width = 100 - c_width;
                return(
                    "<table style='width: 100%'>" +
                    "<tr>" +
                    "<td style='background-color: " + color + "; width:" + c_width.toString() + "%'></td>" +
                    "<td style='width:" + c_inverse_width + "%'>" + count + "</td>" +
                    "</tr>" +
                    "</table>"
                );
            }

            function html_graph(data, color) {
                var html = "<table class='tooltip'>";
                for( var i = 0; i < data.length; i++ )(function(pt) {
                    html += "<tr><td class='tooltip-name'>" + pt.name + "</td>";
                    html += "<td>" + html_count(pt.count, pt.max, color) + "</td>";
                    html += "</tr>";
                })(data[i])
                html += "</table>";

                return( html );
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
                        html_graph([
                            { max: states[code].total_delegates_gop, name: "Trump", count: states[code].trump },
                            { max: states[code].total_delegates_gop, name: "Cruz", count: states[code].cruz },
                            { max: states[code].total_delegates_gop, name: "Kasich", count: states[code].kasich }
                        ], "#e91d0e") +
                        "<hr/>" +
                        "Democratic Delegates: " + states[code].total_delegates_dem + "<br/>" +
                        html_graph([
                            { max: states[code].total_delegates_dem, name: "Clinton", count: states[code].clinton },
                            { max: states[code].total_delegates_dem, name: "Sanders", count: states[code].sanders }
                        ], "#423bbf")
                    );
                },
                onRegionClick: function(element, code, region) {

                }
            });

            $(".filter div.candidate").click(function() {
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
        <div class="candidates">
<?php foreach( $candidates as $candidate ) { ?>
            <div class="candidate candidate-party-<?php echo strtolower($candidate["party"]); ?> candidate-<?php echo strtolower($candidate["name"]); ?>" data-party="<?php echo $candidate["party"] ?>" data-color="<?php echo $candidate["color"] ?>" data-key="<?php echo strtolower($candidate["name"]) ?>">
                <div class="candidate-container">
                    <div class="candidate-inner">
                        <div class="candidate-name"><?php echo $candidate["name"] ?></div>
                        <div class="candidate-delegate-count-compute candidate-<?php echo strtolower($candidate["name"]) ?>">0</div>
                    </div>
                </div>
            </div>
<?php } ?>
        </div>
        <div class="clearDiv"></div>
        <div class="candidates-note">
            <div class="candidates-note-all candidates-note-r">Need 1,237 for nomination.</div>
            <div class="candidates-note-all candidates-note-d">Need 2,283 for nomination.</div>
        </div>
        <div class="clearDiv"></div>
    </div>

    <div id="vmap"></div>
  </body>
</html>
