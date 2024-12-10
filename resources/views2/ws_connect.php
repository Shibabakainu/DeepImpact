<html>

<body>
    <script>
        var ws = new WebSocket('ws://192.168.1.100:8080');
        ws.onopen = function() {
            console.log('Connected to the server');
            ws.send(JSON.stringify({
                type: 'join',
                username: 'Player1'
            }));
        };
    </script>
</body>

</html>