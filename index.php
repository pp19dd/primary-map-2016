<?php
$candidates = array(
    // array( "name" => "Trump",      "color" => "#e91d0e", "party" => "r" ),
    // array( "name" => "Cruz",       "color" => "#e91d0e", "party" => "r" ),
    // array( "name" => "Kasich",     "color" => "#e91d0e", "party" => "r" ),
    array( "name" => "Clinton",    "color" => "#232066", "party" => "d" ),
    array( "name" => "Sanders",    "color" => "#232066", "party" => "d" ),
);

?>
<!doctype html>
<html>
  <head>
    <title>Primary Map 2016</title>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type">

    <!-- Mobile Specific Meta Tags -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge" />
    <link href="jqvmap.css" media="screen" rel="stylesheet" type="text/css">

<style>
html, body {
padding: 0;
margin: 0;
width: 100%;
height: 100%;
overflow: hidden;
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
.filter { margin-bottom: 1.25%; }
.candidates { }
.candidate { width: 19.75%; float: left; text-align: center; }
.candidate-container { }
.candidate-inner { cursor: pointer; padding-top:0.5em; padding-bottom:0.5em; }


.candidate-inner { transition: background-color 100ms linear; }

.candidate-party-d { background-color: #232066; }
.candidate-party-r { background-color: #e91d0e; }

.candidate.candidate-clinton { margin-left: 1.25%; }

.candidate-party-d .candidate-inner { border-bottom: 8px solid #232066; }
.candidate-party-r .candidate-inner { border-bottom: 8px solid #e91d0e; }

.candidate-name { font-weight: bold }
.candidate-name, .candidate-delegate-count-compute { color: white; }
.candidate-party-d.candidate-click-selected .candidate-inner { background-color: gray; border-bottom: 8px solid silver; }
.candidate-party-r.candidate-click-selected .candidate-inner { background-color: #c4180c; border-bottom: 8px solid silver; }

.candidate-party-d.candidate-click-selected .candidate-name,
.candidate-party-r.candidate-click-selected .candidate-name { color: white; }

.candidate-party-d.candidate-click-selected .candidate-delegate-count-compute,
.candidate-party-r.candidate-click-selected .candidate-delegate-count-compute { color: white; }

.candidate-click-selected { }

.candidates-note { }
.candidates-note-all { color: #f8f8f8; font-size: 0.8em; padding: 0 1% 1em 1%; text-align: left; }
    .candidates-note-all .candidates-note-text { padding-top: 1em; }
.candidates-note-d { /*background-color: #232066;*/ margin-left: 1.25%; /*width: 37.5%;*/ color: black; }
    .candidates-note-d .candidates-note-text {  }
.candidates-note-r { float: left; background-color: #e91d0e; width: 57.25%; }
    .candidates-note-r .candidates-note-text { }




.tooltip { width: 200px }
.tooltip * { font-size: 12px }
.tooltip-name { width: 50px }

.clearDiv { clear: both }

@media (max-width: 500px) {
    /*.mobile-hide { display: none; }*/
    .candidate { width: 30% }
}

/* 2016-05-06 */
.candidate-container { border:2px solid white }
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

                    if( did_state_vote(state) === false ) {
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
                // var k = ["clinton", "cruz", "kasich", "sanders", "trump"];
                var k = ["clinton", "sanders"];
                for( var i = 0; i < k.length; i++ ) {
                    count += parseInt(state[k[i]]);
                }
                if( count === 0 ) return( false );
                return(true);
            }

            function did_candidate_win_state(candidate, state, party) {

                // an editor can override an outcome that'd normally be computed
                var override = (party === 'd' ? state.winner_dem : state.winner_gop );
                if( override.trim().length > 0 ) {

                    // state does not have a clear outright winner
                    if( override === "undecided" ) {
                        return( false );
                    }

                    // candidate is the winner
                    if( candidate === override ) {
                        return( true );
                    } else {
                        return( false );
                    }
                }

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
                        /*
                        "Republican Delegates: " + states[code].total_delegates_gop + "<br/>" +
                        html_graph([
                            { max: states[code].total_delegates_gop, name: "Trump", count: states[code].trump },
                            { max: states[code].total_delegates_gop, name: "Cruz", count: states[code].cruz },
                            { max: states[code].total_delegates_gop, name: "Kasich", count: states[code].kasich }
                        ], "#e91d0e") +
                        "<hr/>" +*/
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
                var r = $(this).attr("data-reset");

                if( typeof r != "undefined" && r == "yes" ) {
                    $(".candidate").removeClass("candidate-click-selected");
                    highlight_states_that_voted();
                    $(this).addClass("candidate-click-selected");
                    return;
                }

                var k = $(this).attr("data-key");
                var c = $(this).attr("data-color");
                var p = $(this).attr("data-party");

                if ( $(this).hasClass("candidate-click-selected") ) {
                    // $(".candidate").removeClass("candidate-click-selected");
                    // highlight_states_that_voted();
                } else {
                    highlight_candidate(k, c, p);
                }

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
<div class="candidate candidate-party-d candidate-reset candidate-click-selected" data-reset="yes">
    <div class="candidate-container">
        <div class="candidate-inner">
            <div class="candidate-name">Remaining</div>
            <div class="candidate-delegate-count-compute">Votes</div>
        </div>
    </div>
</div>


        </div>
        <div class="clearDiv"></div>
        <div class="candidates-note">
            <!--<div class="candidates-note-all candidates-note-r"><div class="candidates-note-text">Need 1,237 for nomination.</div></div>-->
            <div class="candidates-note-all candidates-note-d"><div class="candidates-note-text">Need 2,283<span class="mobile-hide"> for nomination</span>.</div></div>
        </div>
        <div class="clearDiv"></div>
    </div>

    <div id="vmap"></div>
  </body>
</html>
