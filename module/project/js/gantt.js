$(function()
{
    if(typeof ganttTasks === 'undefined' || !ganttTasks.length) return;

    if(typeof echarts === 'undefined')
    {
        var script = document.createElement('script');
        script.src = config.webRoot + 'js/echarts/echarts.min.js';
        script.onload = renderGantt;
        document.head.appendChild(script);
    }
    else
    {
        renderGantt();
    }

    function renderGantt()
    {
        var dom = document.getElementById('ganttChart');
        if(!dom) return;

        /* 状态配色，与系统内标签色保持一致。 */
        var colors = {wait: '#838a9d', doing: '#0c64eb', done: '#00b0a0', pause: '#f1a325', closed: '#00b0a0', cancel: '#9e9e9e'};

        var names = ganttTasks.map(function(t){ return t.name; });
        var data  = ganttTasks.map(function(t, index)
        {
            return {
                value: [index, t.start, t.end, t.progress, t.statusName, t.assignedTo],
                itemStyle: {color: colors[t.status] || '#838a9d'}
            };
        });

        var chart = echarts.init(dom);
        chart.setOption(
        {
            animation: false,
            tooltip:
            {
                formatter: function(params)
                {
                    var v = params.value;
                    return '<b>' + v[4] + '</b><br/>' + names[v[0]] +
                           '<br/>' + v[1] + ' ~ ' + v[2] +
                           '<br/>进度 ' + v[3] + '%' + (v[5] ? '<br/>指派 ' + v[5] : '');
                }
            },
            grid: {left: 12, right: 24, top: 16, bottom: 28, containLabel: true},
            xAxis: {type: 'time', position: 'top', axisLine: {show: false}, splitLine: {show: true, lineStyle: {color: '#eee'}}},
            yAxis: {type: 'category', data: names, inverse: true, axisTick: {show: false}, axisLine: {show: false}, splitLine: {show: false}},
            series:
            [{
                type: 'custom',
                renderItem: function(params, api)
                {
                    var categoryIndex = api.value(0);
                    var start = api.coord([api.value(1), categoryIndex]);
                    var end   = api.coord([api.value(2), categoryIndex]);
                    var height = api.size([0, 1])[1] * 0.5;
                    var width  = Math.max(end[0] - start[0], 2);

                    return {
                        type: 'rect',
                        shape: echarts.graphic.clipRectByRect(
                            {x: start[0], y: start[1] - height / 2, width: width, height: height},
                            {x: params.coordSys.x, y: params.coordSys.y, width: params.coordSys.width, height: params.coordSys.height}
                        ),
                        style: api.style()
                    };
                },
                encode: {x: [1, 2], y: 0},
                data: data
            }]
        });

        window.addEventListener('resize', function()
        {
            var instance = echarts.getInstanceByDom(dom);
            if(instance) instance.resize();
        });
    }
});
