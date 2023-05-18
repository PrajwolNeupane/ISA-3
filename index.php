<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
    <title>Weather App</title>
</head>
<script>
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
</script>

<body>

    <!-- Prajwol Neupane -->
    <!-- 2329252 -->
    <?php
    //Variables for Database 
    $servername = "127.0.0.1:3307";
    $username = "root";
    $userpassword = "";
    $dbname = "weatherapp";

    //Connection for Data Base
    $conn = mysqli_connect($servername, $username, $userpassword, $dbname);

    if (!$conn) {
        die("Connection failed : " . mysqli_connect_errno());
    }
    function setPastData($conn)
    {
        function getData($search): object
        {
            //Url for weather api
            $weather_api = "https://api.openweathermap.org/data/2.5/weather?q=$search&exclude=minutely,hourly&units=metric&appid=b26c79aaab8dc734c4a06a2b8f4593d0";


            // Reads the JSON file.
            $weather_json_data = file_get_contents($weather_api);


            // Decodes the JSON data into a PHP array.
            $weather_response_data = json_decode($weather_json_data);


            // All the users data exists in 'data' object
            $weather_data = $weather_response_data;
            return  $weather_data;
        }

        $weather_data =  getData("Renfrewshire");
        //Getting required values and storing in variables
        $city = $weather_data->name;
        $icon = $weather_data->weather[0]->icon;
        $max_temp = $weather_data->main->temp_max;
        $min_temp = $weather_data->main->temp_min;
        $current_temp = $weather_data->main->temp;
        $wind = $weather_data->wind->speed;
        $humidity = $weather_data->main->humidity;
        $description = $weather_data->weather[0]->description;
        $lat = $weather_data->coord->lat;
        $lon = $weather_data->coord->lon;

        function getTime($lat, $lon): string
        {
            $time_api = "https://timeapi.io/api/TimeZone/coordinate?latitude=$lat&longitude=$lon";


            //Getting required values and storing in variables
            $time_json_data = file_get_contents($time_api);
            $time_data = json_decode($time_json_data);

            $time_data = $time_data->currentLocalTime;

            return   $time_data;
        }
        $day_list = array("Sun" => "Sunday", "Mon" => "Monday", "Tue" => "Tuesday", "Wed" => "Wednesday", "Thu" => "Thursday", "Fri" => "Friday", "Sat" => "Saturday");
        $time_data = getTime($lat, $lon);
        $current_time = new DateTime($time_data);
        $year = $current_time->format('Y');
        $month = $current_time->format('M');
        $month_count = $current_time->format('m');
        $day = $day_list[$current_time->format('D')];
        $day_count = $current_time->format('d');
        $hour = $current_time->format('H');
        $minute = $current_time->format('m');

        //Query for updating data into mysql
        $upate_sql = "UPDATE pastdays SET maxtemp='$max_temp', mintemp='$min_temp' ,city='Renfrewshire' , wind='$wind' , icon='$icon' , description='$description' , humidity='$humidity' , date='$year-$month_count-$day_count' WHERE day='$day'";
        $conn->query($upate_sql);

        // //Query for getting data from database
        $sql = "SELECT * FROM pastdays ORDER BY date DESC;";
        //Storing data into result
        $result = $conn->query($sql);
        $data = array();
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($data, json_encode($row));
        }
        $pastdata = json_encode(array(json_encode($data)));


        echo "<script>";
        echo "localStorage.setItem('Renfrewshire Past Data', $pastdata)";
        echo "</script>";
    }
    if (array_key_exists('update-database', $_POST)) {
        setPastData($conn);
        echo "<script>";
        echo "console.log('Fetching Api to get latest data and storing it on My SQL'))";
        echo "</script>";
    }
    ?>
    <div class="flex-col content">
        <input placeholder="Search City" id="search-bar" onchange="changed()" />
        <div class="flex-col current-content">
            <h1 id="city"></h1>
            <img id="logo" />
            <h2 id="description"></h2>
            <h3 id="temperature"></h3>
            <h3 id="time"></h3>
            <h4 id="min_temp"></h4>
            <h4 id="max_temp"></h4>
            <h4 id="humidity"></h4>
            <h4 id="wind"></h4>
            <button onclick="fetchCityData()">Fetch Latest Data</button>
        </div>
        <button onclick="showPastData()" id="show-button">Show Past Data of Renfrewshire, GB</button>
        <div class="flex-col" style="align-items: center;gap:20px;display:none" id="show-past">
            <h2>Past Data at Renfrewshire, GB </h2>
            <form method="post"> <button name="update-database" id="fetch">Fetch Latest Past Data</button></form>
            <table id="table">
                <tr>
                    <th>Day</th>
                    <th>Date</th>
                    <th>Max Temp</th>
                    <th>Min Temp</th>
                    <th>Icon</th>
                    <th>Description</th>
                    <th>Humidity</th>
                    <th>Wind Speed</th>
                </tr>
            </table>
        </div>
        <div class="footer flex-col">
            <h3>Prajwol Neupane</h3>
            <h3>2329252 </h3>
        </div>
    </div>
    <div class="bg-image" id="bg"></div>
</body>
<script defer type="text/javascript">
    var city = document.getElementById("city");
    var temp = document.getElementById("temperature");
    var logo = document.getElementById("logo");
    var description = document.getElementById("description");
    var maxTemp = document.getElementById("max_temp");
    var minTemp = document.getElementById("min_temp");
    var humidity = document.getElementById("humidity");
    var wind = document.getElementById("wind");
    var time = document.getElementById("time");

    var bg = document.getElementById("bg");

    var searchBar = document.getElementById("search-bar");

    function convertTime(offset) {
        //Variables for months
        var monthNames = [
            "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
        ];
        var day = [
            "Sunday", "Monday", "Tuesday", "Wenesday,Thursday", "Friday", "Saturday"
        ];
        d = new Date()
        localTime = d.getTime()
        localOffset = d.getTimezoneOffset() * 60000
        utc = localTime + localOffset
        var time = utc + (1000 * offset)
        date3 = new Date(time);
        var month = monthNames[date3.getMonth()]
        var day = day[date3.getDay()];
        var date = date3.getDate();
        var year = date3.getFullYear();
        var hour = date3.getHours();
        var min = date3.getMinutes();
        //Returning time
        return `${hour}:${min}, ${month} ${date}, ${year}`;
    }

    const fetchCityData = async () => {
        const search = document.getElementById("city").innerHTML;
        try {
            //Fetching URL
            const response = await fetch(`https://api.openweathermap.org/data/2.5/weather?q=${search}&exclude=minutely,hourly&units=metric&appid=b26c79aaab8dc734c4a06a2b8f4593d0`);
            //Converting data into json
            const data = await response.json();
            //Manipulating  DOM

            time.innerText = convertTime(data.timezone);
            wind.innerHTML = `Wind Speed : ${data.wind.speed} m/s`
            city.innerText = `${data.name}`;
            description.innerHTML = data.weather[0].description;
            maxTemp.innerText = `Max Temperature : ${data.main.temp_max} °C`;
            minTemp.innerText = `Min Temperature : ${data.main.temp_min} °C`;
            humidity.innerText = `Humidity : ${data.main.humidity} %`
            temp.innerHTML = `Current Temperature : ${data.main.temp} °C`;
            logo.src = `http://openweathermap.org/img/wn/${data.weather[0].icon}@4x.png`;
            bg.style = `background: url(https://openweathermap.org/img/wn/${data.weather[0].icon}@4x.png);background-size: 25% ;`
            localStorage.setItem(data.name.toLowerCase(), JSON.stringify(data));
            console.log("Got City Data from API");
        } catch (e) {
            //Alerting user about envalid location
            alert("Network Error");
            city.innerText = '';
            description.innerText = ''
            maxTemp.innerText = ``;
            minTemp.innerText = ``;
            humidity.innerText = ``
            temp.innerHTML = ``;
            logo.src = ``;
        }
    }

    //Fetching data from API
    const getWeatherResponse = async (search) => {
        if (localStorage.getItem(search.toLowerCase())) {
            const data = JSON.parse(localStorage.getItem(search.toLowerCase()));
            time.innerText = convertTime(data.timezone);
            wind.innerHTML = `Wind Speed : ${data.wind.speed} m/s`
            city.innerText = `${data.name}`;
            description.innerHTML = data.weather[0].description;
            maxTemp.innerText = `Max Temperature : ${data.main.temp_max} °C`;
            minTemp.innerText = `Min Temperature : ${data.main.temp_min} °C`;
            humidity.innerText = `Humidity : ${data.main.humidity} %`
            temp.innerHTML = `Current Temperature : ${data.main.temp} °C`;
            logo.src = `http://openweathermap.org/img/wn/${data.weather[0].icon}@4x.png`;
            bg.style = `background: url(https://openweathermap.org/img/wn/${data.weather[0].icon}@4x.png);background-size: 25% ;`
            console.log("Got City Data from Local Storage");
        } else {
            try {
                //Fetching URL
                const response = await fetch(`https://api.openweathermap.org/data/2.5/weather?q=${search}&exclude=minutely,hourly&units=metric&appid=b26c79aaab8dc734c4a06a2b8f4593d0`);
                //Converting data into json
                const data = await response.json();
                //Manipulating  DOM

                time.innerText = convertTime(data.timezone);
                wind.innerHTML = `Wind Speed : ${data.wind.speed} m/s`
                city.innerText = `${data.name}`;
                description.innerHTML = data.weather[0].description;
                maxTemp.innerText = `Max Temperature : ${data.main.temp_max} °C`;
                minTemp.innerText = `Min Temperature : ${data.main.temp_min} °C`;
                humidity.innerText = `Humidity : ${data.main.humidity} %`
                temp.innerHTML = `Current Temperature : ${data.main.temp} °C`;
                logo.src = `http://openweathermap.org/img/wn/${data.weather[0].icon}@4x.png`;
                bg.style = `background: url(https://openweathermap.org/img/wn/${data.weather[0].icon}@4x.png);background-size: 25% ;`
                localStorage.setItem(data.name.toLowerCase(), JSON.stringify(data));
                console.log("Got City Data from API");
            } catch (e) {
                //Alerting user about envalid location
                alert("Please Enter valid Location");
                city.innerText = '';
                description.innerText = ''
                maxTemp.innerText = ``;
                minTemp.innerText = ``;
                humidity.innerText = ``
                temp.innerHTML = ``;
                logo.src = ``;
            }
        }
    }
    //Calling fetch function with default city
    getWeatherResponse("Renfrewshire");
    const changed = () => {

        getWeatherResponse(searchBar.value);
        settingButton(searchBar.value.toLowerCase());
    }

    const getPastDataFromSQL = async () => {
        var table = document.getElementById("table");
        const rawData = localStorage.getItem("Renfrewshire Past Data");
        if (rawData) {
            const data = JSON.parse(rawData);
            data.forEach(element => {
                table.innerHTML += `<tr>
                <td>${JSON.parse(element).day}</td>
                <td>${JSON.parse(element).date}</td>
                <td>${JSON.parse(element).maxtemp} C</td>
                <td>${JSON.parse(element).mintemp} C</td>
                <td><img src="https://openweathermap.org/img/wn/${JSON.parse(element).icon}@2x.png"/></td>
                <td>${JSON.parse(element).description}</td>
                <td>${JSON.parse(element).humidity} %</td>
                <td>${JSON.parse(element).wind} m/s</td>
            </tr>`;
            });
            console.log("Got Past Data from Local Storage");
        } else {
            document.getElementById('fetch').click();
            alert("Got Past Data from My SQL");
        }
    }
    getPastDataFromSQL();

    const settingButton = (city) => {
        var show = document.getElementById("show-past");
        var showButton = document.getElementById("show-button");
        if (city != "renfrewshire") {
            showButton.style.display = 'none';
            show.style.display = "none";
        } else {
            showButton.style.display = 'block';
            show.style.display = "flex";
        }
    }

    const showPastData = async () => {
        var show = document.getElementById("show-past");
        var showButton = document.getElementById("show-button");
        if (show.style.display == "none") {
            showButton.innerText = "Hide Past Data of Renfrewshire, GB"
            show.style.display = "flex";
        } else {
            showButton.innerText = "Show Past Data of Renfrewshire, GB"
            show.style.display = "none";
        }
    }
</script>

</html>