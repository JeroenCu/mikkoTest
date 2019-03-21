<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Choose a year</title>
    <script>
        function getCsv(){
            const year = document.getElementById('yearInput').value
            const url = '/lastDate/' + parseInt(year)
            window.open(url, '_blank')
            return false
        }
    </script>
</head>
<body>
    <form onSubmit="return getCsv()" method="GET">
        <input type="text" name="year" id="yearInput">
        <input type="submit" value="Ok">
    </form>
</body>
</html>