<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <title>Popular Lombas</title>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Sirekom</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#">Lomba</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Kategori</a>
                    </li>

                </ul>
            </div>
        </div>
    </nav>

    <!-- Lomba Cards -->
    <div class="container my-4">
        <h1>Top Lomba</h1>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php
            // Inisialisasi cURL
            $ch = curl_init();

            // Set URL tujuan
            curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1:5000/recommend_popular"); // Menyesuaikan URL untuk menambahkan '/recommend'

            // Set metode permintaan menjadi POST
            curl_setopt($ch, CURLOPT_POST, 1);

            // Set opsi lainnya
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Menyimpan respons dalam variabel daripada mencetaknya langsung

            // Eksekusi cURL dan simpan respons
            $response = curl_exec($ch);

            // Cek apakah ada kesalahan
            if (curl_errno($ch)) {
                echo '<p>Error: ' . curl_error($ch) . '</p>';
            } else {
                // Tampilkan respons (jika berhasil)
                $recommendations = json_decode($response, true);
                // var_dump($recommendations);

                if (!empty($recommendations)) {

                    // Database connection
                    $conn = new mysqli('localhost', 'root', '', 'sirekom_ai');
                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    // Query untuk mendapatkan detail film berdasarkan ID rekomendasi
                    $ids = implode(',', $recommendations); // Ubah daftar ID menjadi format yang sesuai untuk kueri SQL
                    $sql = "SELECT id, namaLomba, posterLomba, deskripsiLomba FROM lombas WHERE id IN ($ids)";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
            ?>
                            <div class="col">
                                <div class="card">
                                    <img src="img/<?php echo $row['posterLomba']; ?>" class="card-img-top" alt="<?php echo $row['namaLomba']; ?>" style="max-width: 100%; max-height: 300px;">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <a href="detail.php?id=<?= $row['id'] ?>"><?php echo $row['namaLomba']; ?></a>

                                        </h5>
                                        <p class="card-text overflow-hidden text-truncate"><?php echo $row['deskripsiLomba']; ?></p>
                                    </div>
                                </div>
                            </div>
            <?php
                        }
                    } else {
                        echo '<p>No recommended movies found in the database.</p>';
                    }

                    // Tutup koneksi database
                    $conn->close();

                    echo '</div></section>';
                } else {
                    echo '<p>No recommendations available.</p>';
                }
            }

            // Tutup koneksi cURL
            curl_close($ch);
            ?>
        </div>
    </div>

    <!-- List Lomba -->
    <div class="list-lomba">

    </div>

    <!-- List Lomba -->
    <div class="container my-4">
        <h1>All Lombas</h1>
        <div class="row row-cols-1 row-cols-md-3 g-4 list-lomba">
            <?php
            // Database connection
            $conn = new mysqli('localhost', 'root', '', 'sirekom_ai');
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Query untuk mendapatkan semua lomba
            $sql = "SELECT id, namaLomba, posterLomba, deskripsiLomba FROM lombas";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
            ?>
                    <div class="col">
                        <div class="card">
                            <img src="img/<?php echo $row['posterLomba']; ?>" class="card-img-top" alt="<?php echo $row['namaLomba']; ?>" style="max-width: 100%; max-height: 300px;">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="detail.php?id=<?= $row['id'] ?>"><?php echo $row['namaLomba']; ?></a>
                                </h5>
                                <p class="card-text overflow-hidden text-truncate"><?php echo $row['deskripsiLomba']; ?></p>
                            </div>
                        </div>
                    </div>
            <?php
                }
            } else {
                echo '<p>No lombas found in the database.</p>';
            }

            // Tutup koneksi database
            $conn->close();
            ?>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>

</html>