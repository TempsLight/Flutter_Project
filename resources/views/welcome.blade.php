<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Dashboard</title>

</head>
<body>
    
    <header>
        <div class="header">
            <h1>Dashboard</h1>
        </div>
    </header>
    <div class="container">
        <div class="chart1">
            <label>Label</label>
            <canvas id="myChart"></canvas>
        </div>
    </div>
    

</body>
</html>
<style>
    .header {
        display: flex;
        justify-content: center;
    }
    .container {
		display: flex;
		justify-content: center;
	}
</style>
<script>
    window.onload = function loadChartData() {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                var data = JSON.parse(this.responseText);
                console.log(data);
                var pie = document.getElementById('pie').getContext('2d');
                var labels = response.user.map(function(item) { return item.name; });
                var data = response.user.map(function(item) { return item.count; });
                new Chart(pie, {
                    type: 'pie',
                    data: {
                        labels: ['Nursery Students', 'Kinder Students'],
                        datasets: [{
                            label: '',
                            data: data,
                            backgroundColor: ['beige', 'gray'],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        plugins: {
                            legend: {
                                labels: {
                                    color: 'white'
                                }
                            }
                        }
                    }
                });
            }
        };

        xhttp.open("GET", "http://192.168.31.97/api/users", true);
        xhttp.send();
    }
</script>