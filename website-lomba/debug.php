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
            echo '<section class="recommendations">';
            echo '<h1>Recommended Lombas</h1>';
            echo '<div class="movies">';

            // Database connection
            $conn = new mysqli('localhost', 'root', '', 'sirekom_ai');
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Query untuk mendapatkan detail film berdasarkan ID rekomendasi
            $ids = implode(',', $recommendations); // Ubah daftar ID menjadi format yang sesuai untuk kueri SQL
            $sql = "SELECT id, namaLomba, posterLomba, namaDeskripsi FROM lombas WHERE id IN ($ids)";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="lomba">';
                    echo '<a href="detail.php?id=' . $row['id'] . '">';
                    echo '<img src="' . $row['posterLomba'] . '" alt="' . $row['namaLomba'] . '">';
                    echo '<h3>' . $row['namaLomba'] . '</h3>';
                    echo '</a>';
                    echo '</div>';
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