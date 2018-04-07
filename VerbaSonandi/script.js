var w = 350,
	h = 350;

var colorscale = d3.scale.category10();

//Data
var param1 = p1;
	
var d = [];

function tax(axis, value) {
    this.axis = axis;
    this.value = value;
}

function make_axis(param) {
	var dict = [];
	for (var key in param) {
		dict.push({
			axis: key,
			value: param[key]
		});
	}
	return dict;
}

d.push(make_axis(param1));

//Options for the Radar chart, other than default
var mycfg = {
  w: w,
  h: h,
  maxValue: 0.4,
  levels: 6,
  ExtraWidthX: 200
}

//Call function to draw the Radar chart
//Will expect that data is in %'s
RadarChart.draw("#chart", d, mycfg);

////////////////////////////////////////////
/////////// Initiate legend ////////////////
////////////////////////////////////////////

var svg = d3.select('#body')
	.selectAll('svg')
	.append('svg')
	.attr("width", w)
	.attr("height", h)
