<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sebaran Kecamatan di Kabupaten Sleman</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            padding: 20px;
            margin: 0;
            line-height: 1.6;
        }

        h1 {
            text-align: center;
            color: #007bff;
        }

        #map {
            width: 100%;
            height: 400px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            background-color: #ffffff;
        }

        th,
        td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .btn {
            padding: 8px 12px;
            cursor: pointer;
            border: none;
            color: white;
            font-weight: bold;
            transition: 0.3s;
            border-radius: 4px;
        }

        .btn-delete {
            background-color: #dc3545;
        }

        .btn-delete:hover {
            background-color: #c82333;
        }

        .btn-edit {
            background-color: #007bff;
        }

        .btn-edit:hover {
            background-color: #0056b3;
        }

        .edit-form {
            display: none;
            margin-top: 20px;
            padding: 20px;
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .edit-form input[type="text"] {
            width: 100%;
            padding: 8px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <h1>Sebaran Kecamatan di Kabupaten Sleman</h1>
    <div id="map"></div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        var map = L.map("map").setView([-7.6245674, 110.4167175], 12);
        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
    </script>

    <script>
        <?php
        $conn = new mysqli("localhost", "root", "", "acara8");

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "SELECT kecamatan, latitude, longitude, luas, jumlah_penduduk FROM jumlah_penduduk";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "L.marker([{$row['latitude']}, {$row['longitude']}]).addTo(map).bindPopup('<b>{$row['kecamatan']}</b><br>Luas: {$row['luas']} km²<br>Jumlah Penduduk: {$row['jumlah_penduduk']}');\n";
            }
        } else {
            echo "console.log('Tidak ada data ditemukan');";
        }

        $conn->close();
        ?>
    </script>

    <h2>Data Kecamatan</h2>
    <div id="edit-form" class="edit-form">
        <h3>Edit Data Kecamatan</h3>
        <form action="" method="post">
            <input type="hidden" name="old_kecamatan" id="old_kecamatan">
            <label for="kecamatan">Kecamatan:</label>
            <input type="text" name="kecamatan" id="kecamatan" required>
            <label for="longitude">Longitude:</label>
            <input type="text" name="longitude" id="longitude" required>
            <label for="latitude">Latitude:</label>
            <input type="text" name="latitude" id="latitude" required>
            <label for="luas">Luas (km²):</label>
            <input type="text" name="luas" id="luas" required>
            <label for="jumlah_penduduk">Jumlah Penduduk:</label>
            <input type="text" name="jumlah_penduduk" id="jumlah_penduduk" required>
            <input type="submit" name="update" value="Update" class="btn-edit">
            <input type="button" value="Batal" onclick="document.getElementById('edit-form').style.display='none'" class="btn">
        </form>
    </div>

    <?php
    $conn = new mysqli("localhost", "root", "", "acara8");
    $sql = "SELECT kecamatan, longitude, latitude, luas, jumlah_penduduk FROM jumlah_penduduk";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table>
                <tr>
                    <th>Kecamatan</th>
                    <th>Longitude</th>
                    <th>Latitude</th>
                    <th>Luas (km²)</th>
                    <th>Jumlah Penduduk</th>
                    <th>Aksi</th>
                </tr>";

        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['kecamatan']}</td>
                    <td>{$row['longitude']}</td>
                    <td>{$row['latitude']}</td>
                    <td>{$row['luas']}</td>
                    <td align='right'>{$row['jumlah_penduduk']}</td>
                    <td>
                        <form action='' method='post' style='display:inline;'>
                            <input type='hidden' name='kecamatan' value='{$row['kecamatan']}'>
                            <input type='submit' name='delete' value='Hapus' class='btn btn-delete'>
                        </form>
                        <button class='btn btn-edit' onclick=\"editRow('{$row['kecamatan']}', '{$row['longitude']}', '{$row['latitude']}', '{$row['luas']}', '{$row['jumlah_penduduk']}')\">Edit</button>
                    </td>
                  </tr>";
        }

        echo "</table>";
    } else {
        echo "Tidak ada data yang ditemukan.";
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
        $kecamatan = $_POST['kecamatan'];
        $stmt = $conn->prepare("DELETE FROM jumlah_penduduk WHERE kecamatan = ?");
        $stmt->bind_param("s", $kecamatan);
        $stmt->execute();
        $stmt->close();
        echo "<meta http-equiv='refresh' content='0'>";
    }
    $conn->close();
    ?>

    <script>
        function editRow(kecamatan, longitude, latitude, luas, jumlahPenduduk) {
            document.getElementById("edit-form").style.display = "block";
            document.getElementById("kecamatan").value = kecamatan;
            document.getElementById("longitude").value = longitude;
            document.getElementById("latitude").value = latitude;
            document.getElementById("luas").value = luas;
            document.getElementById("jumlah_penduduk").value = jumlahPenduduk;
            document.getElementById("old_kecamatan").value = kecamatan;
        }
    </script>
</body>
</html>
